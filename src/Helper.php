<?php
/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo;

use dcCore;
use dcModuleDefine;
use dcPage;
use dcPublic;
use dcStaticCache;
use dcStoreReader;
use dcTemplate;
use dcThemes;
use dcUtils;
use Dotclear\App;
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Template\Template;
use Dotclear\Helper\Network\Http;
use Exception;

class Helper
{
    /**
     * Display full report in a textarea, ready to copy'n'paste
     *
     * @return     string
     */
    public static function report(): string
    {
        // Capture everything
        ob_start();

        echo self::quoteVersions(false);

        echo self::dcConstants();
        echo self::folders();
        echo self::globals();

        echo self::URLHandlers();

        echo self::tplPaths();

        echo self::plugins();

        // Get capture content
        $buffer = ob_get_clean();

        // Transform HTML to text

        $form = '<h3>' . __('Report') . '</h3>' .

        (new Form('report'))
            ->action(dcCore::app()->admin->getPageURL())
            ->method('post')
            ->fields([
                (new Submit(['getreport']))
                    ->value(__('Download report')),
                (new Hidden(['htmlreport']))
                    ->value(Html::escapeHTML($buffer)),
                dcCore::app()->formNonce(false),
            ])->render() .

        '<pre>' . $buffer . '</pre>';

        return $form;
    }

    /**
     * Cope with form versions action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     *
     * @return  string
     */
    public static function doReport(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['getreport'])) {
            // Cope with report download
            try {
                if (empty($_POST['htmlreport'])) {
                    throw new Exception(__('Report empty'));
                }
                $path = Path::real(implode(DIRECTORY_SEPARATOR, [DC_TPL_CACHE, 'sysinfo']), false);
                if (!is_dir($path)) {
                    Files::makeDir($path, true);
                }

                $filename  = date('Y-m-d') . '-' . dcCore::app()->blog->id . '-report';
                $extension = '.html';
                $file      = implode(DIRECTORY_SEPARATOR, [$path, $filename . $extension]);

                // Prepare report
                if (file_exists($file)) {
                    unlink($file);
                }
                $fp = fopen($file, 'wt');

                // Begin HTML Document
                $report = Html::decodeEntities($_POST['htmlreport']);
                $report = str_replace('<img src="images/check-on.png" />', '✅', $report, $count);
                $report = str_replace('<img src="images/check-off.png" />', '⛔️', $report);
                $report = str_replace(DC_ROOT, '<code>DC_ROOT</code> ', $report);
                fwrite($fp, '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">' .
                    '<title>Dotclear sysInfo report: ' . date('Y-m-d') . '-' . dcCore::app()->blog->id . '</title></head><body>');
                fwrite($fp, Html::decodeEntities($report));
                fwrite($fp, '</body></html>');
                fclose($fp);

                // Download zip report
                $gzip = implode(DIRECTORY_SEPARATOR, [$path, $filename . '.tar.gz']);
                if (file_exists($gzip)) {
                    unlink($gzip);
                }
                $tar = implode(DIRECTORY_SEPARATOR, [$path, $filename . '.tar']);
                if (file_exists($tar)) {
                    unlink($tar);
                }
                $a = new \PharData($tar, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS, null, \Phar::TAR);
                $a->addFile($file, $filename . $extension);
                $a->compress(\Phar::GZ);
                unlink($tar);
                unlink($file);

                header('Content-Disposition: attachment;filename=' . $filename . '.tar.gz');
                header('Content-Type: application/x-gzip');
                readfile($gzip);

                exit;
            } catch (Exception $e) {
                $checklist = 'report';
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return $nextlist;
    }

    /**
     * Return autoloader infos
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function autoloader(): string
    {
        $autoloader = App::autoload();
        $ns         = array_keys($autoloader->getNamespaces());

        $str = '<p>' . __('Properties:') . '</p>' .
            '<ul>' .
            '<li>' . __('Root prefix:') . ' ' . ($autoloader->getRootPrefix() !== '' ? $autoloader->getRootPrefix() : __('Empty')) . '</li>' .
            '<li>' . __('Root basedir:') . ' ' . ($autoloader->getRootBaseDir() !== '' ? $autoloader->getRootBaseDir() : __('Empty')) . '</li>' .
            '</ul>';

        $str .= '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Namespaces') . ' (' . sprintf('%d', is_countable($ns) ? count($ns) : 0) . ')' . '</caption>' . // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        // Second loop for deprecated variables
        foreach ($ns as $n) {
            $str .= '<tr>' . '<td class="nowrap">' . $n . '</td>';
            $str .= '</tr>';
        }

        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of registered versions (core, plugins, themes, …)
     *
     * @return     string
     */
    public static function versions(): string
    {
        $versions    = dcCore::app()->getVersions();
        $distributed = explode(',', DC_DISTRIB_PLUGINS);
        $paths       = explode(PATH_SEPARATOR, DC_PLUGINS_ROOT);

        // Some plugins may have registered their version with a different case reprensetation of their name
        // which is not a very good idea, but we need to cope with legacy code ;-)
        // Ex: dcRevisions store it as 'dcrevisions'
        // So we will check by ignoring case
        $plugins  = array_map(fn ($name): string => mb_strtolower($name), array_values(array_keys(dcCore::app()->plugins->getModules())));
        $disabled = array_map(fn ($name): string => mb_strtolower($name), array_values(array_keys(dcCore::app()->plugins->getDisabledModules())));

        $str = '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="verform">' .
            '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('List of versions registered in the database') . ' (' . sprintf('%d', is_countable($versions) ? count($versions) : 0) . ')' . '</caption>' .   // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="">' . __('Module') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Version') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Status') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        dcUtils::lexicalKeySort($versions);
        foreach ($versions as $module => $version) {
            $status   = [];
            $class    = [];
            $name     = $module;
            $checkbox = (new Checkbox(['ver[]'], false))->value($module);
            $input    = (new Input(['m[' . $module . ']']))->value($version);

            if ($module === 'core') {
                $class[]  = 'version-core';
                $name     = '<strong>' . $module . '</strong>';
                $status[] = __('Core');
                $checkbox->disabled(true);  // Do not delete core version
                $input->disabled(true);     // Do not modify core version
            } else {
                if (in_array($module, $distributed)) {
                    $class[]  = 'version-distrib';
                    $status[] = __('Distributed');
                    $checkbox->disabled(true);  // Do not delete distributed module version
                    $input->disabled(true);     // Do not modify distributed module version
                }
                if (!in_array(mb_strtolower($module), $plugins)) {
                    // Not in activated plugins list
                    if (in_array(mb_strtolower($module), $disabled)) {
                        // In disabled plugins list
                        $exists = true;
                    } else {
                        $exists = false;
                        // Look if the module exists in one of specified plugins paths
                        foreach ($paths as $path) {
                            if (is_dir($path . DIRECTORY_SEPARATOR . $module)) {
                                $exists = true;

                                break;
                            }
                        }
                    }
                    if ($exists) {
                        $class[]  = 'version-disabled';
                        $status[] = __('Disabled');
                    } else {
                        $class[]  = 'version-unknown';
                        $status[] = __('Not found but may exist');
                    }
                }
            }
            $str .= '<tr class="' . implode(' ', $class) . '">' .
                '<td class="">' . $checkbox->render() . ' ' . $name . '</td>' .
                '<td class="nowrap">' . $input->render() . '</td>' .
                '<td class="nowrap">' . implode(', ', $status) . '</td>' .
                '</tr>';
        }
        $str .= '</tbody></table>' .
            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .
            '<p class="col right">' .
            dcCore::app()->formNonce() .
            (new Submit('updveraction', __('Update versions')))->render() . ' ' .
            (new Submit('delveraction', __('Delete selected versions')))->class('delete')->render() .
            '</p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with form versions action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     *
     * @return  string
     */
    public static function doFormVersions(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['delveraction'])) {
            // Cope with versions deletion
            try {
                if (empty($_POST['ver'])) {
                    throw new Exception(__('No version selected'));
                }
                $list = [];
                foreach ($_POST['ver'] as $v) {
                    $list[] = $v;
                }
                $sql = new DeleteStatement();
                $sql
                    ->from(dcCore::app()->prefix . dcCore::VERSION_TABLE_NAME)
                    ->where('module' . $sql->in($list));
                $sql->delete();
            } catch (Exception $e) {
                $checklist = 'versions';
                dcCore::app()->error->add($e->getMessage());
            }
            if (!dcCore::app()->error->flag()) {
                dcPage::addSuccessNotice(__('Selected versions have been deleted.'));
                Http::redirect(dcCore::app()->admin->getPageURL() . '&ver=1');
            }
        }

        if (!empty($_POST['updveraction'])) {
            // Cope with versions update
            try {
                $sql = new UpdateStatement();
                $sql
                    ->ref(dcCore::app()->prefix . dcCore::VERSION_TABLE_NAME);
                foreach ($_POST['m'] as $module => $version) {
                    $sql
                        ->set('version = ' . $sql->quote($version), true)   // Reset value
                        ->where('module = ' . $sql->quote($module), true)   // Reset condition
                        ->update();
                }
            } catch (Exception $e) {
                $nextlist = 'versions';
                dcCore::app()->error->add($e->getMessage());
            }
            if (!dcCore::app()->error->flag()) {
                dcPage::addSuccessNotice(__('Versions have been updated.'));
                Http::redirect(dcCore::app()->admin->getPageURL() . '&ver=1');
            }
        }

        return $nextlist;
    }

    public static function doCheckVersions(string $checklist): string
    {
        return !empty($_GET['ver']) ? 'versions' : $checklist;
    }

    /**
     * Return list of global variables
     *
     * @return     string
     */
    public static function globals(): string
    {
        $max_length = 1024 * 4;     // 4Kb max

        $variables = array_keys($GLOBALS);
        dcUtils::lexicalSort($variables);

        $deprecated = [
            '__autoload'     => '2.23',
            '__l10n'         => '2.24',
            '__l10n_files'   => '2.24',
            '__parent_theme' => '2.23',
            '__resources'    => '2.23',
            '__smilies'      => '2.23',
            '__theme'        => '2.23',
            '__widgets'      => '2.23',

            '_ctx'          => '2.23',
            '_lang'         => '2.23',
            '_menu'         => '2.23',
            '_page_number'  => '2.23',
            '_search'       => '2.23',
            '_search_count' => '2.23',

            'core'      => '2.23',
            'mod_files' => '2.23',
            'mod_ts'    => '2.23',
            'p_url'     => '2.23',
        ];

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Global variables') . ' (' . sprintf('%d', is_countable($variables) ? count($variables) : 0) . ')' . '</caption>' . // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col" class="maximal">' . __('Content') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        // First loop for non deprecated variables
        foreach ($variables as $variable) {
            if (!in_array($variable, array_keys($deprecated))) {
                $str .= '<tr>' . '<td class="nowrap">' . $variable . '</td>';
                if (is_array($GLOBALS[$variable])) {
                    $values = $GLOBALS[$variable];
                    dcUtils::lexicalKeySort($values);
                    $content = '<ul>';
                    foreach ($values as $key => $value) {
                        $type = ' (' . gettype($value) . ')';
                        $type = '';     // All values are string
                        $content .= '<li><strong>' . $key . '</strong> = ' . '<code>' . print_r($value, true) . '</code>' . $type . '</li>';
                    }
                    $content .= '</ul>';
                } else {
                    $content = print_r($GLOBALS[$variable], true);
                    if (mb_strlen($content) > $max_length) {
                        $content = mb_substr($content, 0, $max_length) . ' …';
                    }
                }
                $str .= '<td class="maximal">' . $content . '</td>';
                $str .= '</tr>';
            }
        }
        // Second loop for deprecated variables
        foreach ($variables as $variable) {
            if (in_array($variable, array_keys($deprecated))) {
                $str .= '<tr>' . '<td class="nowrap">' . $variable . '</td>';
                $str .= '<td class="maximal deprecated">' . sprintf(__('*** deprecated since %s ***'), $deprecated[$variable]) . '</td>';
                $str .= '</tr>';
            }
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of registered permissions
     *
     * @return     string
     */
    public static function permissions(): string
    {
        $permissions = dcCore::app()->auth->getPermissionsTypes();

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Types of permission') . ' (' . sprintf('%d', is_countable($permissions) ? count($permissions) : 0) . ')' . '</caption>' . // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Type') . '</th>' .
            '<th scope="col" class="maximal">' . __('Label') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($permissions as $n => $l) {
            $str .= '<tr>' .
                '<td class="nowrap">' . $n . '</td>' .
                '<td class="maximal">' . __($l) . '</td>' .
                '</tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of REST methods
     *
     * @return     string
     */
    public static function restMethods(): string
    {
        $methods = dcCore::app()->rest->functions;

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('REST methods') . ' (' . sprintf('%d', is_countable($methods) ? count($methods) : 0) . ')' . '</caption>' .    // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Method') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        dcUtils::lexicalKeySort($methods);
        foreach ($methods as $method => $callback) {
            $str .= '<tr><td class="nowrap">' . $method . '</td><td class="maximal"><code>';
            if (is_array($callback)) {
                if (count($callback) > 1) {
                    if (is_string($callback[0])) {
                        $str .= $callback[0] . '::' . $callback[1];
                    } else {
                        $str .= get_class($callback[0]) . '->' . $callback[1];
                    }
                } else {
                    $str .= $callback[0];
                }
            } else {
                $str .= $callback;
            }
            $str .= '()</code></td></tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of plugins
     *
     * @return     string
     */
    public static function plugins(): string
    {
        // Affichage de la liste des plugins (et de leurs propriétés)
        $plugins = dcCore::app()->plugins->getDefines(['state' => dcModuleDefine::STATE_ENABLED], true);

        $count = count($plugins) ? ' (' . sprintf('%d', count($plugins)) . ')' : '';

        $str = '<h3>' . __('Plugins (in loading order)') . $count . '</h3>';
        $str .= '<details id="expand-all"><summary>' . __('Plugin id') . __(' (priority, name)') . '</summary></details>';
        foreach ($plugins as $id => $m) {
            $info = sprintf(' (%s, %s)', number_format($m['priority'] ?? 1000, 0, '.', '&nbsp;'), $m['name'] ?? $id);
            $str .= '<details id="p-' . $id . '"><summary><strong>' . $id . '</strong>' . $info . '</summary>';
            $str .= '<ul>';
            foreach ($m as $key => $val) {  // @phpstan-ignore-line
                $value = print_r($val, true);
                if (in_array($key, ['requires', 'implies', 'cannot_enable', 'cannot_disable'])) {
                    if ((is_countable($val) ? count($val) : 0) > 0) {
                        $value = [];
                        foreach ($val as $module) {
                            if (is_array($module)) {
                                $version = ' (' . $module[1] . ')';
                                $module  = $module[0];
                            } else {
                                $version = '';
                            }
                            $value[] = $module !== 'core' ? ('<a href="#p-' . $module . '"/>' . $module . '</a>') : 'Dotclear' . $version;
                        }
                        $value = implode(', ', $value);
                    }
                } elseif (in_array($key, ['support', 'details', 'repository'])) {
                    $value = '<a href="' . $value . '"/>' . $value . '</a>';
                }
                $str .= '<li>' . $key . ' = ' . $value . '</li>';
            }
            $str .= '</ul>';
            $str .= '</details>';
        }

        return $str;
    }

    /**
     * Return list of formaters (syntaxes coped by installed editors)
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function formaters(): string
    {
        // Affichage de la liste des éditeurs et des syntaxes par éditeur
        $formaters = dcCore::app()->getFormaters();

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Editors and their supported syntaxes') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Editor') . '</th>' .
            '<th scope="col" class="maximal">' . __('Syntax') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($formaters as $e => $s) {
            $str .= '<tr><td class="nowrap">' . $e . '</td>';
            $newline = false;
            if (is_array($s)) {
                foreach ($s as $f) {
                    $str .= ($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal">' . $f . '</td>';
                    $newline = true;
                }
            }
            $str .= '</tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of Dotclear constants
     *
     * @return     string
     */
    public static function dcConstants(): string
    {
        [$undefined, $constants] = self::getConstants();

        // Affichage des constantes remarquables de Dotclear
        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Dotclear constants') . ' (' . sprintf('%d', is_countable($constants) ? count($constants) : 0) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Constant') . '</th>' .
            '<th scope="col" class="maximal">' . __('Value') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        dcUtils::lexicalKeySort($constants);
        foreach ($constants as $c => $v) {
            $str .= '<tr><td class="nowrap">' .
                '<img src="images/' . ($v != $undefined ? 'check-on.png' : 'check-off.png') . '" /> <code>' . $c . '</code></td>' .
                '<td class="maximal">';
            if ($v != $undefined) {
                $str .= $v;
            }
            $str .= '</td></tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of registered behaviours
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function behaviours(): string
    {
        // Affichage de la liste des behaviours inscrits
        $bl = dcCore::app()->getBehaviors('');

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Behaviours list') . ' (' . sprintf('%d', is_countable($bl) ? count($bl) : 0) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Behavior') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        dcUtils::lexicalKeySort($bl);
        foreach ($bl as $b => $f) {
            $str .= '<tr><td class="nowrap">' . $b . '</td>';
            $newline = false;
            if (is_array($f)) {
                foreach ($f as $fi) {
                    $str .= ($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal"><code>';
                    if (is_array($fi)) {
                        if (is_object($fi[0])) {
                            $str .= get_class($fi[0]) . '-&gt;' . $fi[1];
                        } else {
                            $str .= $fi[0] . '::' . $fi[1];
                        }
                    } else {
                        if ($fi instanceof \Closure) {
                            $str .= '__closure__';
                        } else {
                            $str .= $fi;
                        }
                    }
                    $str .= '()</code></td>';
                    $newline = true;
                }
            } else {
                $str .= '<td><code>' . $f . '()</code></td>';
            }
            $str .= '</tr>';
        }
        $str .= '</tbody></table>';

        $str .= '<p><a id="sysinfo-preview" href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('sysinfo') . '/behaviours' . '">' . __('Display public behaviours') . '</a></p>';

        return $str;
    }

    /**
     * Return list of registered URLs
     *
     * @return     string
     */
    public static function URLHandlers(): string
    {
        // Récupération des types d'URL enregistrées
        $urls = dcCore::app()->url->getTypes();

        // Tables des URLs non gérées par le menu
        //$excluded = ['xmlrpc','preview','trackback','feed','spamfeed','hamfeed','pagespreview','tag_feed'];
        $excluded = [];

        $str = '<table id="urls" class="sysinfo"><caption>' . __('List of known URLs') . ' (' . sprintf('%d', is_countable($urls) ? count($urls) : 0) . ')' . '</caption>' .    // @phpstan-ignore-line
            '<thead><tr><th scope="col">' . __('Type') . '</th>' .
            '<th scope="col">' . __('base URL') . '</th>' .
            '<th scope="col">' . __('Regular expression') . '</th>' .
            '<th scope="col">' . __('Callback') . '</th>' .
            '</tr></thead>' .
            '<tbody>' .
            '<tr>' .
            '<td scope="row">' . 'home' . '</td>' .
            '<td>' . '' . '</td>' .
            '<td><code>' . '^$' . '</code></td>' .
            '<td><code>' . '(default)' . '</code></td>' .
            '</tr>';
        foreach ($urls as $type => $param) {
            if (!in_array($type, $excluded)) {
                $fi = $param['handler'];
                if (is_array($fi)) {
                    if (is_object($fi[0])) {
                        $handler = get_class($fi[0]) . '-&gt;' . $fi[1];
                    } else {
                        $handler = $fi[0] . '::' . $fi[1];
                    }
                } else {
                    if ($fi instanceof \Closure) {
                        $handler = '__closure__';
                    } else {
                        $handler = $fi;
                    }
                }
                $str .= '<tr>' .
                    '<td scope="row">' . $type . '</td>' .
                    '<td>' . $param['url'] . '</td>' .
                    '<td><code>' . $param['representation'] . '</code></td>' .
                    '<td><code>' . $handler . '()</code></td>' .
                    '</tr>';
            }
        }
        $str .= '</tbody>' .
            '</table>';

        return $str;
    }

    /**
     * Return list of admin registered URLs
     *
     * @return     string
     */
    public static function adminURLs(): string
    {
        // Récupération de la liste des URLs d'admin enregistrées
        $urls = dcCore::app()->adminurl->dumpUrls();

        $str = '<table id="urls" class="sysinfo"><caption>' . __('Admin registered URLs') . ' (' . sprintf('%d', is_countable($urls) ? count($urls) : 0) . ')' . '</caption>' . // @phpstan-ignore-line
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('URL') . '</th>' .
            '<th scope="col" class="maximal">' . __('Query string') . '</th></tr></thead>' .
            '<tbody>';
        foreach ($urls as $name => $url) {
            $str .= '<tr>' .
                '<td scope="row" class="nowrap">' . $name . '</td>' .
                '<td><code>' . $url['url'] . '</code></td>' .
                '<td class="maximal"><code>' . http_build_query($url['qs']) . '</code></td>' .
                '</tr>';
        }
        $str .= '</tbody>' .
            '</table>';

        return $str;
    }

    /**
     * Return PHP info
     *
     * @return     string
     */
    public static function phpInfo(): string
    {
        ob_start();
        phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES + INFO_ENVIRONMENT + INFO_VARIABLES);
        $phpinfo = ['phpinfo' => []];
        if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $keys = array_keys($phpinfo);
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = [];
                } elseif (isset($match[3])) {
                    @$phpinfo[end($keys)][$match[2]] = isset($match[4]) ? [$match[3], $match[4]] : $match[3];
                } else {
                    @$phpinfo[end($keys)][] = $match[2];
                }
            }
        }
        $str = '';
        foreach ($phpinfo as $name => $section) {
            $str .= "<h3>$name</h3>\n<table class=\"sysinfo\">\n";
            foreach ($section as $key => $val) {
                if (is_array($val)) {
                    $str .= "<tr><td>$key</td><td>$val[0]</td><td>$val[1]</td></tr>\n";
                } elseif (is_string($key)) {
                    $str .= "<tr><td>$key</td><td>$val</td></tr>\n";
                } else {
                    $str .= "<tr><td>$val</td></tr>\n";
                }
            }
            $str .= "</table>\n";
        }

        return $str;
    }

    /**
     * Return list of compiled template's files
     *
     * @return     string
     */
    public static function templates(): string
    {
        $tplset = self::publicPrepend();

        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
        $cache_path    = Path::real(DC_TPL_CACHE);
        if (substr($cache_path, 0, strlen($document_root)) == $document_root) {
            $cache_path = substr($cache_path, strlen($document_root));
        } elseif (substr($cache_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
            $cache_path = substr($cache_path, strlen(DC_ROOT));
        }
        $blog_host = dcCore::app()->blog->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $blog_url = dcCore::app()->blog->url;
        if (substr($blog_url, 0, strlen($blog_host)) == $blog_host) {
            $blog_url = substr($blog_url, strlen($blog_host));
        }

        $paths = dcCore::app()->tpl->getPath();

        $str = '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="tplform">' .
            '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('List of compiled templates in cache') . ' ' . $cache_path . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col">' . __('Template path') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Template file') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Cache subpath') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Cache file') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        // Template stack
        $stack = [];
        // Loop on template paths
        foreach ($paths as $path) {
            $sub_path = Path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
                $sub_path = substr($sub_path, strlen(DC_ROOT));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            $path_displayed = false;
            // Don't know exactly why but need to cope with */dcPublic::TPL_ROOT !
            $md5_path = (!strstr($path, '/' . dcPublic::TPL_ROOT . '/' . $tplset) ? $path : Path::real($path));
            $files    = Files::scandir($path);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (preg_match('/^(.*)\.(html|xml|xsl)$/', $file, $matches) && isset($matches[1]) && !in_array($file, $stack)) {
                        $stack[]        = $file;
                        $cache_file     = md5($md5_path . DIRECTORY_SEPARATOR . $file) . '.php';
                        $cache_subpath  = sprintf('%s/%s', substr($cache_file, 0, 2), substr($cache_file, 2, 2));
                        $cache_fullpath = Path::real(DC_TPL_CACHE) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR . $cache_subpath;
                        $file_check     = $cache_fullpath . DIRECTORY_SEPARATOR . $cache_file;
                        $file_exists    = file_exists($file_check);
                        $str .= '<tr>' .
                            '<td>' . ($path_displayed ? '' : $sub_path) . '</td>' .
                            '<td class="nowrap">' . $file . '</td>' .
                            '<td class="nowrap">' . '<img src="images/' . ($file_exists ? 'check-on.png' : 'check-off.png') . '" /> ' . $cache_subpath . '</td>' .
                            '<td class="nowrap">' .
                            \form::checkbox(
                                ['tpl[]'],
                                $cache_file,
                                false,
                                ($file_exists) ? 'tpl_compiled' : '',
                                '',
                                !($file_exists)
                            ) . ' ' .
                            '<label class="classic">' .
                            ($file_exists ? '<a class="tpl_compiled" href="' . '#' . '">' : '') .
                            $cache_file .
                            ($file_exists ? '</a>' : '') .
                            '</label></td>' .
                            '</tr>';
                        $path_displayed = true;
                    }
                }
            }
        }
        $str .= '</tbody></table>' .
            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .
            '<p class="col right">' . dcCore::app()->formNonce() . '<input type="submit" class="delete" id="deltplaction" name="deltplaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with form templates action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     *
     * @return  string
     */
    public static function doFormTemplates(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['deltplaction'])) {
            // Cope with cache file deletion
            try {
                if (empty($_POST['tpl'])) {
                    throw new Exception(__('No cache file selected'));
                }
                $root_cache = Path::real(DC_TPL_CACHE) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR;
                foreach ($_POST['tpl'] as $v) {
                    $cache_file = $root_cache . sprintf('%s' . DIRECTORY_SEPARATOR . '%s', substr($v, 0, 2), substr($v, 2, 2)) . DIRECTORY_SEPARATOR . $v;
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (Exception $e) {
                $nextlist = 'templates';
                dcCore::app()->error->add($e->getMessage());
            }
            if (!dcCore::app()->error->flag()) {
                dcPage::addSuccessNotice(__('Selected cache files have been deleted.'));
                Http::redirect(dcCore::app()->admin->getPageURL() . '&tpl=1');
            }
        }

        return $nextlist;
    }

    public static function doCheckTemplates(string $checklist): string
    {
        return !empty($_GET['tpl']) ? 'templates' : $checklist;
    }

    /**
     * Return list of template paths
     *
     * @return     string
     */
    public static function tplPaths(): string
    {
        self::publicPrepend();
        $paths         = dcCore::app()->tpl->getPath();
        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('List of template paths') . ' (' . sprintf('%d', is_countable($paths) ? count($paths) : 0) . ')' . '</caption>' . // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col">' . __('Path') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($paths as $path) {
            $sub_path = Path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
                $sub_path = substr($sub_path, strlen(DC_ROOT));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            $str .= '<tr><td>' . $sub_path . '</td><tr>';
        }
        $str .= '</tbody></table>';

        $str .= '<p><a id="sysinfo-preview" href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('sysinfo') . '/templatetags' . '">' . __('Display template tags') . '</a></p>';

        return $str;
    }

    /**
     * Return list of available modules
     *
     * @param      bool    $use_cache  The use cache
     * @param      string  $url        The url
     * @param      string  $title      The title
     * @param      string  $label      The label
     *
     * @return     string
     */
    private static function repoModules(bool $use_cache, string $url, string $title, string $label): string
    {
        $cache_path = Path::real(DC_TPL_CACHE);
        $xml_url    = $url;
        $in_cache   = false;

        if ($use_cache) {
            // Get XML cache file for modules
            $ser_file = sprintf(
                '%s/%s/%s/%s/%s.ser',
                $cache_path,
                'dcrepo',
                substr(md5($xml_url), 0, 2),
                substr(md5($xml_url), 2, 2),
                md5($xml_url)
            );
            if (file_exists($ser_file)) {
                $in_cache = true;
            }
        }
        $parser    = dcStoreReader::quickParse($xml_url, DC_TPL_CACHE, !$in_cache);
        $raw_datas = !$parser ? [] : $parser->getModules();     // @phpstan-ignore-line
        dcUtils::lexicalKeySort($raw_datas, dcUtils::ADMIN_LOCALE);

        $count = $parser ? ' (' . sprintf('%d', is_countable($raw_datas) ? count($raw_datas) : 0) . ')' : '';

        $str = '<h3>' . $title . __(' from: ') . ($in_cache ? __('cache') : $xml_url) . $count . '</h3>';
        if (!$parser) {     // @phpstan-ignore-line
            $str .= '<p>' . __('Repository is unreachable') . '</p>';
        } else {
            $str .= '<details id="expand-all"><summary>' . $label . '</summary></details>';
            $url_fmt = '<a href="%1$s">%1$s</a>';
            dcUtils::lexicalKeySort($raw_datas);
            foreach ($raw_datas as $id => $infos) {
                $str .= '<details><summary>' . $id . '</summary>';
                $str .= '<ul>';
                foreach ($infos as $key => $value) {
                    $val = (in_array($key, ['file', 'details', 'support', 'sshot']) && $value ? sprintf($url_fmt, $value) : $value);
                    $str .= '<li>' . $key . ' = ' . $val . '</li>';
                }
                $str .= '</ul>';
                $str .= '</details>';
            }
        }

        return $str;
    }

    /**
     * Return list of available plugins
     *
     * @param      bool    $use_cache  Use cache if available
     *
     * @return     string
     */
    public static function repoPlugins(bool $use_cache = false): string
    {
        return self::repoModules(
            $use_cache,
            dcCore::app()->blog->settings->system->store_plugin_url,
            __('Repository plugins list'),
            __('Plugin ID')
        );
    }

    /**
     * Return list of available themes
     *
     * @param      bool    $use_cache  Use cache if available
     *
     * @return     string
     */
    public static function repoThemes(bool $use_cache = false): string
    {
        return self::repoModules(
            $use_cache,
            dcCore::app()->blog->settings->system->store_theme_url,
            __('Repository themes list'),
            __('Theme ID')
        );
    }

    /**
     * Return a quote and PHP and DB driver version
     *
     * @param   bool    $quote include quote
     *
     * @return     string
     */
    public static function quoteVersions(bool $quote = true): string
    {
        // Display a quote and PHP and DB version
        $quotes = [
            __('Live long and prosper.'),
            __('To infinity and beyond.'),
            __('So long, and thanks for all the fish.'),
            __('Find a needle in a haystack.'),
            __('A clever person solves a problem. A wise person avoids it.'),
            __('I\'m sorry, Dave. I\'m afraid I can\'t do that.'),
            __('With great power there must also come great responsibility.'),
            __('It\'s great, we have to do it all over again!'),
            __('Have You Tried Turning It Off And On Again?'),
        ];
        $q = random_int(0, count($quotes) - 1);

        // Get cache info
        $caches = [];
        if (function_exists('\opcache_get_status') && is_array(\opcache_get_status())) {
            $caches[] = 'OPCache';
        }
        if (function_exists('\apcu_cache_info') && is_array(\apcu_cache_info())) {
            $caches[] = 'APC';
        }
        if (class_exists('\Memcache')) {
            $memcache     = new \Memcache();
            $isMemcacheOn = @$memcache->connect('localhost', 11211, 1);
            if ($isMemcacheOn) {
                $caches[] = 'Memcache';
            }
        }
        if (count($caches) === 0) {
            $caches[] = __('None');
        }

        // Server info
        $server = ($quote ? '<blockquote class="sysinfo"><p>' . $quotes[$q] . '</p></blockquote>' : '') .
            '<details open><summary>' . __('System info') . '</summary>' .
            '<ul>' .
            '<li>' . __('PHP Version: ') . '<strong>' . phpversion() . '</strong></li>' .
            '<li>' .
                __('DB driver: ') . '<strong>' . dcCore::app()->con->driver() . '</strong> ' .
                __('version') . ' <strong>' . dcCore::app()->con->version() . '</strong> ' .
                sprintf(__('using <strong>%s</strong> syntax'), dcCore::app()->con->syntax()) . '</li>' .
            '<li>' . __('Error reporting: ') . '<strong>' . error_reporting() . '</strong>' . ' = ' . self::errorLevelToString(error_reporting(), ', ') . '</li>' .
            '<li>' . __('PHP Cache: ') . '<strong>' . implode('</strong>, <strong>', $caches) . '</strong></li>' .
            '<li>' . __('Temporary folder: ') . '<strong>' . sys_get_temp_dir() . '</strong></li>' .
            '</ul>' .
            '</details>';

        // Dotclear info
        $dotclear = '<details open><summary>' . __('Dotclear info') . '</summary>' .
            '<ul>' .
            '<li>' . __('Dotclear version: ') . '<strong>' . DC_VERSION . '</strong></li>' .
            '<li>' . __('Clearbricks version: ') . '<strong>' . CLEARBRICKS_VERSION . '</strong></li>' .
            '</ul>' .
            '</details>';

        // Update info

        $versions = '';
        $path     = Path::real(DC_TPL_CACHE . '/versions');
        if ($path && is_dir($path)) {
            $channels = ['stable', 'testing', 'unstable'];
            foreach ($channels as $channel) {
                $file = $path . '/dotclear-' . $channel;
                if (file_exists($file)) {
                    if ($content = @unserialize(@file_get_contents($file))) {
                        if (is_array($content)) {
                            $versions .= '<li>' . __('Channel: ') . '<strong>' . $channel . '</strong>' .
                                ' (' . date(DATE_ATOM, filemtime($file)) . ')' .
                                '<ul>' .
                                '<li>' . __('version: ') . '<strong>' . $content['version'] . '</strong></li>' .
                                '<li>' . __('href: ') . '<a href="' . $content['href'] . '">' . $content['href'] . '</a></li>' .
                                '<li>' . __('checksum: ') . '<code>' . $content['checksum'] . '</code></li>' .
                                '<li>' . __('info: ') . '<a href="' . $content['info'] . '">' . $content['info'] . '</a></li>' .
                                '<li>' . __('PHP min: ') . '<strong>' . $content['php'] . '</strong></li>' .
                                (isset($content['warning']) ?
                                    '<li>' . __('Warning: ') . '<strong>' . ($content['warning'] ? __('Yes') : __('No')) . '</strong></li>' :
                                    '') .
                                '</ul>' .
                                '</li>';
                        }
                    }
                }
            }
        }
        if ($versions !== '') {
            $versions = '<details open><summary>' . __('Update info') . ' ' . __('(from versions cache)') . '</summary><ul>' . $versions . '</ul></details>';
        }

        return $server . $dotclear . $versions;
    }

    /* --- helpers --- */

    /**
     * Emulate public prepend
     *
     * @return     string  template set name
     */
    private static function publicPrepend(): string
    {
        // Emulate public prepend
        define('DC_CONTEXT_PUBLIC', true);
        dcCore::app()->public = new dcPublic();

        dcCore::app()->tpl    = new dcTemplate(DC_TPL_CACHE, 'dcCore::app()->tpl');
        dcCore::app()->themes = new dcThemes();
        dcCore::app()->themes->loadModules(dcCore::app()->blog->themes_path);
        if (!isset(dcCore::app()->public->theme)) {     // @phpstan-ignore-line
            dcCore::app()->public->theme = dcCore::app()->blog->settings->system->theme;
        }
        if (!dcCore::app()->themes->moduleExists(dcCore::app()->public->theme)) {
            dcCore::app()->public->theme = dcCore::app()->blog->settings->system->theme = DC_DEFAULT_THEME;
        }
        $tplset                             = dcCore::app()->themes->moduleInfo(dcCore::app()->public->theme, 'tplset');
        dcCore::app()->public->parent_theme = dcCore::app()->themes->moduleInfo(dcCore::app()->public->theme, 'parent');
        if (dcCore::app()->public->parent_theme && !dcCore::app()->themes->moduleExists(dcCore::app()->public->parent_theme)) {
            dcCore::app()->public->theme        = dcCore::app()->blog->settings->system->theme = DC_DEFAULT_THEME;
            dcCore::app()->public->parent_theme = null;
        }
        $tpl_path = [
            dcCore::app()->blog->themes_path . '/' . dcCore::app()->public->theme . '/tpl',
        ];
        if (dcCore::app()->public->parent_theme) {
            $tpl_path[] = dcCore::app()->blog->themes_path . '/' . dcCore::app()->public->parent_theme . '/tpl';
            if (empty($tplset)) {
                $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->public->parent_theme, 'tplset');
            }
        }
        if (empty($tplset)) {
            $tplset = DC_DEFAULT_TPLSET;
        }
        $main_plugins_root = explode(PATH_SEPARATOR, DC_PLUGINS_ROOT);
        dcCore::app()->tpl->setPath(
            $tpl_path,
            $main_plugins_root[0] . '/../inc/public' . '/' . dcPublic::TPL_ROOT . '/' . $tplset,
            dcCore::app()->tpl->getPath()
        );

        // Looking for dcPublic::TPL_ROOT in each plugin's dir
        $plugins = array_keys(dcCore::app()->plugins->getModules());
        foreach ($plugins as $k) {
            $plugin_root = dcCore::app()->plugins->moduleInfo($k, 'root');
            if ($plugin_root) {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), $plugin_root . '/' . dcPublic::TPL_ROOT . '/' . $tplset);
                // To be exhaustive add also direct directory (without templateset)
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), $plugin_root . '/' . dcPublic::TPL_ROOT);
            }
        }

        return $tplset;
    }

    /**
     * Get current list of Dotclear constants and their values
     *
     * @return     array  array[0] = undefined value, array[1] = list of constants
     */
    private static function getConstants(): array
    {
        $undefined = '<!-- undefined -->';
        $constants = [
            'DC_ADMIN_CONTEXT'        => defined('DC_ADMIN_CONTEXT') ? (DC_ADMIN_CONTEXT ? 'true' : 'false') : $undefined,
            'DC_ADMIN_MAILFROM'       => defined('DC_ADMIN_MAILFROM') ? DC_ADMIN_MAILFROM : $undefined,
            'DC_ADMIN_SSL'            => defined('DC_ADMIN_SSL') ? (DC_ADMIN_SSL ? 'true' : 'false') : $undefined,
            'DC_ADMIN_URL'            => defined('DC_ADMIN_URL') ? DC_ADMIN_URL : $undefined,
            'DC_AKISMET_SUPER'        => defined('DC_AKISMET_SUPER') ? (DC_AKISMET_SUPER ? 'true' : 'false') : $undefined,
            'DC_ALLOW_MULTI_MODULES'  => defined('DC_ALLOW_MULTI_MODULES') ? (DC_ALLOW_MULTI_MODULES ? 'true' : 'false') : $undefined,
            'DC_ALLOW_REPOSITORIES'   => defined('DC_ALLOW_REPOSITORIES') ? (DC_ALLOW_REPOSITORIES ? 'true' : 'false') : $undefined,
            'DC_ANTISPAM_CONF_SUPER'  => defined('DC_ANTISPAM_CONF_SUPER') ? (DC_ANTISPAM_CONF_SUPER ? 'true' : 'false') : $undefined,
            'DC_AUTH_PAGE'            => defined('DC_AUTH_PAGE') ? DC_AUTH_PAGE : $undefined,
            'DC_AUTH_SESS_ID'         => defined('DC_AUTH_SESS_ID') ? DC_AUTH_SESS_ID : $undefined,
            'DC_AUTH_SESS_UID'        => defined('DC_AUTH_SESS_UID') ? DC_AUTH_SESS_UID : $undefined,
            'DC_BACKUP_PATH'          => defined('DC_BACKUP_PATH') ? DC_BACKUP_PATH : $undefined,
            'DC_BLOG_ID'              => defined('DC_BLOG_ID') ? DC_BLOG_ID : $undefined,
            'DC_CONTEXT_ADMIN'        => defined('DC_CONTEXT_ADMIN') ? (DC_CONTEXT_ADMIN ? 'true' : 'false') : $undefined,
            'DC_CONTEXT_MODULE'       => defined('DC_CONTEXT_MODULE') ? (DC_CONTEXT_MODULE ? 'true' : 'false') : $undefined,
            'DC_CRYPT_ALGO'           => defined('DC_CRYPT_ALGO') ? DC_CRYPT_ALGO : $undefined,
            'DC_CSP_LOGFILE'          => defined('DC_CSP_LOGFILE') ? DC_CSP_LOGFILE : $undefined,
            'DC_STORE_NOT_UPDATE'     => defined('DC_STORE_NOT_UPDATE') ? (DC_STORE_NOT_UPDATE ? 'true' : 'false') : $undefined,
            'DC_DBDRIVER'             => defined('DC_DBDRIVER') ? DC_DBDRIVER : $undefined,
            'DC_DBHOST'               => defined('DC_DBHOST') ? DC_DBHOST : $undefined,
            'DC_DBNAME'               => defined('DC_DBNAME') ? DC_DBNAME : $undefined,
            'DC_DBPASSWORD'           => defined('DC_DBPASSWORD') ? '********* ' . __('(see inc/config.php)') /* DC_DBPASSWORD */ : $undefined,
            'DC_DBPREFIX'             => defined('DC_DBPREFIX') ? DC_DBPREFIX : $undefined,
            'DC_DBUSER'               => defined('DC_DBUSER') ? DC_DBUSER : $undefined,
            'DC_DEBUG'                => defined('DC_DEBUG') ? (DC_DEBUG ? 'true' : 'false') : $undefined,
            'DC_DEFAULT_JQUERY'       => defined('DC_DEFAULT_JQUERY') ? DC_DEFAULT_JQUERY : $undefined,
            'DC_DEFAULT_THEME'        => defined('DC_DEFAULT_THEME') ? DC_DEFAULT_THEME : $undefined,
            'DC_DEFAULT_TPLSET'       => defined('DC_DEFAULT_TPLSET') ? DC_DEFAULT_TPLSET : $undefined,
            'DC_DEV'                  => defined('DC_DEV') ? (DC_DEV ? 'true' : 'false') : $undefined,
            'DC_DIGESTS'              => defined('DC_DIGESTS') ? DC_DIGESTS : $undefined,
            'DC_DISTRIB_PLUGINS'      => defined('DC_DISTRIB_PLUGINS') ? DC_DISTRIB_PLUGINS : $undefined,
            'DC_DISTRIB_THEMES'       => defined('DC_DISTRIB_THEMES') ? DC_DISTRIB_THEMES : $undefined,
            'DC_DNSBL_SUPER'          => defined('DC_DNSBL_SUPER') ? (DC_DNSBL_SUPER ? 'true' : 'false') : $undefined,
            'DC_FAIRTRACKBACKS_FORCE' => defined('DC_FAIRTRACKBACKS_FORCE') ? (DC_FAIRTRACKBACKS_FORCE ? 'true' : 'false') : $undefined,
            'DC_FORCE_SCHEME_443'     => defined('DC_FORCE_SCHEME_443') ? (DC_FORCE_SCHEME_443 ? 'true' : 'false') : $undefined,
            'DC_L10N_ROOT'            => defined('DC_L10N_ROOT') ? DC_L10N_ROOT : $undefined,
            'DC_L10N_UPDATE_URL'      => defined('DC_L10N_UPDATE_URL') ? DC_L10N_UPDATE_URL : $undefined,
            'DC_MASTER_KEY'           => defined('DC_MASTER_KEY') ? '********* ' . __('(see inc/config.php)') /* DC_MASTER_KEY */ : $undefined,
            'DC_MAX_UPLOAD_SIZE'      => defined('DC_MAX_UPLOAD_SIZE') ? DC_MAX_UPLOAD_SIZE : $undefined,
            'DC_NEXT_REQUIRED_PHP'    => defined('DC_NEXT_REQUIRED_PHP') ? DC_NEXT_REQUIRED_PHP : $undefined,
            'DC_NOT_UPDATE'           => defined('DC_NOT_UPDATE') ? (DC_NOT_UPDATE ? 'true' : 'false') : $undefined,
            'DC_PLUGINS_ROOT'         => defined('DC_PLUGINS_ROOT') ? DC_PLUGINS_ROOT : $undefined,
            'DC_QUERY_TIMEOUT'        => defined('DC_QUERY_TIMEOUT') ? DC_QUERY_TIMEOUT . ' ' . __('seconds') : $undefined,
            'DC_RC_PATH'              => defined('DC_RC_PATH') ? DC_RC_PATH : $undefined,
            'DC_REST_SERVICES'        => defined('DC_REST_SERVICES') ? (DC_REST_SERVICES ? 'true' : 'false') : $undefined,
            'DC_ROOT'                 => defined('DC_ROOT') ? DC_ROOT : $undefined,
            'DC_SESSION_NAME'         => defined('DC_SESSION_NAME') ? DC_SESSION_NAME : $undefined,
            'DC_SESSION_TTL'          => defined('DC_SESSION_TTL') ? DC_SESSION_TTL : $undefined,
            'DC_SHOW_HIDDEN_DIRS'     => defined('DC_SHOW_HIDDEN_DIRS') ? (DC_SHOW_HIDDEN_DIRS ? 'true' : 'false') : $undefined,
            'DC_START_TIME'           => defined('DC_START_TIME') ? DC_START_TIME : $undefined,
            'DC_TPL_CACHE'            => defined('DC_TPL_CACHE') ? DC_TPL_CACHE : $undefined,
            'DC_UPDATE_URL'           => defined('DC_UPDATE_URL') ? DC_UPDATE_URL : $undefined,
            'DC_UPDATE_VERSION'       => defined('DC_UPDATE_VERSION') ? DC_UPDATE_VERSION : $undefined,
            'DC_UPGRADE'              => defined('DC_UPGRADE') ? DC_UPGRADE : $undefined,
            'DC_VAR'                  => defined('DC_VAR') ? DC_VAR : $undefined,
            'DC_VENDOR_NAME'          => defined('DC_VENDOR_NAME') ? DC_VENDOR_NAME : $undefined,
            'DC_VERSION'              => defined('DC_VERSION') ? DC_VERSION : $undefined,
            'DC_XMLRPC_URL'           => defined('DC_XMLRPC_URL') ? DC_XMLRPC_URL : $undefined,
            'CLEARBRICKS_VERSION'     => defined('CLEARBRICKS_VERSION') ? CLEARBRICKS_VERSION : $undefined,
        ];

        if (dcCore::app()->plugins->moduleExists('staticCache')) {
            $constants['DC_SC_CACHE_ENABLE']    = defined('DC_SC_CACHE_ENABLE') ? (DC_SC_CACHE_ENABLE ? 'true' : 'false') : $undefined;
            $constants['DC_SC_CACHE_DIR']       = defined('DC_SC_CACHE_DIR') ? DC_SC_CACHE_DIR : $undefined;
            $constants['DC_SC_CACHE_BLOGS_ON']  = defined('DC_SC_CACHE_BLOGS_ON') ? DC_SC_CACHE_BLOGS_ON : $undefined;
            $constants['DC_SC_CACHE_BLOGS_OFF'] = defined('DC_SC_CACHE_BLOGS_OFF') ? DC_SC_CACHE_BLOGS_OFF : $undefined;
            $constants['DC_SC_EXCLUDED_URL']    = defined('DC_SC_EXCLUDED_URL') ? DC_SC_EXCLUDED_URL : $undefined;
        }

        return [$undefined, $constants];
    }

    /**
     * Check generic Dotclear folders
     *
     * @return     string
     */
    public static function folders(): string
    {
        // Check generic Dotclear folders
        $folders = [
            'root'   => DC_ROOT,
            'config' => DC_RC_PATH,
            'cache'  => [
                DC_TPL_CACHE,
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . 'cbfeed',
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER,
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . 'dcrepo',
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . 'versions',
            ],
            'digest'  => DC_DIGESTS,
            'l10n'    => DC_L10N_ROOT,
            'plugins' => explode(PATH_SEPARATOR, DC_PLUGINS_ROOT),
            'public'  => dcCore::app()->blog->public_path,
            'themes'  => dcCore::app()->blog->themes_path,
            'var'     => DC_VAR,
        ];

        if (defined('DC_SC_CACHE_DIR')) {
            $folders += ['static' => DC_SC_CACHE_DIR];
        }

        $str = '<table id="urls" class="sysinfo"><caption>' . __('Dotclear folders and files') . '</caption>' .
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('Path') . '</th>' .
            '<th scope="col" class="maximal">' . __('Status') . '</th></tr></thead>' .
            '<tbody>';

        foreach ($folders as $name => $subfolder) {
            if (!is_array($subfolder)) {
                $subfolder = [$subfolder];
            }
            foreach ($subfolder as $folder) {
                $err = '';
                if ($path = Path::real($folder)) {
                    $writable = is_writable($path);
                    $touch    = true;
                    if ($writable && is_dir($path)) {
                        // Try to create a file, inherit dir perms and then delete it
                        $void = '';

                        try {
                            $void  = $path . (substr($path, -1) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) . 'tmp-' . str_shuffle(MD5(microtime()));
                            $touch = false;
                            Files::putContent($void, '');
                            if (file_exists($void)) {
                                Files::inheritChmod($void);
                                unlink($void);
                                $touch = true;
                            }
                        } catch (Exception $e) {
                            $err = $void . ' : ' . $e->getMessage();
                        }
                    }
                    $status = $writable && $touch ?
                    '<img src="images/check-on.png" alt="" /> ' . __('Writable') :
                    '<img src="images/check-wrn.png" alt="" /> ' . __('Readonly');
                } else {
                    $status = '<img src="images/check-off.png" alt="" /> ' . __('Unknown');
                }
                if ($err !== '') {
                    $status .= '<div style="display: none;"><p>' . $err . '</p></div>';
                }

                if (substr($folder, 0, strlen(DC_ROOT)) === DC_ROOT) {
                    $folder = substr_replace($folder, '<code>DC_ROOT</code> ', 0, strlen(DC_ROOT));
                }

                $str .= '<tr>' .
                '<td class="nowrap">' . $name . '</td>' .
                '<td class="maximal">' . $folder . '</td>' .
                '<td class="nowrap">' . $status . '</td>' .
                '</tr>';

                $name = '';     // Avoid repeating it if multiple lines
            }
        }

        $str .= '</tbody>' .
            '</table>';

        return $str;
    }

    /**
     * Check Dotclear digest integrity
     *
     * @return     string
     */
    public static function integrity(): string
    {
        $str = '<table id="urls" class="sysinfo"><caption>' . __('Dotclear digest integrity') . '</caption>' .
            '<thead><tr>' .
            '<th scope="col">' . __('File') . '</th>' .
            '<th scope="col">' . __('digest') . '</th>' .
            '<th scope="col">' . __('md5') . '</th>' .
            '<th scope="col">' . __('md5 (experimental)') . '</th>' .
            '</tr></thead>' .
            '<tbody>';

        $digests_file = implode(DIRECTORY_SEPARATOR, [DC_ROOT, 'inc', 'digests']);
        if (is_readable($digests_file)) {
            $opts     = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
            $contents = file($digests_file, $opts);
            $count    = 0;

            foreach ($contents as $digest) {
                if (!preg_match('#^([\da-f]{32})\s+(.+?)$#', $digest, $m)) {
                    continue;
                }

                $md5      = $m[1];
                $filename = DC_ROOT . '/' . $m[2];

                $md5_std    = '';
                $md5_exp    = '';
                $std_status = '';
                $exp_status = '';

                if (!is_readable($filename)) {
                    $md5_std = __('Not readable');
                } else {
                    // Direct
                    $md5_std = md5_file($filename);

                    if ($md5_std !== $md5) {
                        // Remove EOL
                        $filecontent = file_get_contents($filename);
                        $filecontent = str_replace("\r\n", "\n", $filecontent);
                        $filecontent = str_replace("\r", "\n", $filecontent);

                        $md5_std    = md5($filecontent);
                        $std_status = $md5_std === $md5 ? '' : ' class="version-disabled"';
                    }

                    // Experimental
                    // Remove EOL
                    $filecontent = file_get_contents($filename);
                    $filecontent = preg_replace('/(*BSR_ANYCRLF)\R/', '\n', $filecontent);

                    if ($filecontent) {
                        $md5_exp    = md5($filecontent);
                        $exp_status = $md5_exp === $md5 ? '' : ' class="version-disabled"';
                    }
                }

                if ($std_status) {
                    $count++;
                    $str .= '<tr>' .
                    '<td class="maximal">' . $filename . '</td>' .
                    '<td>' . $md5 . '</td>' .
                    '<td' . $std_status . '>' . $md5_std . '</td>' .
                    '<td' . $exp_status . '>' . $md5_exp . '</td>' .
                    '</tr>';
                }
            }
            if (!$count) {
                $str .= '<tr><td>' . __('Everything is fine.') . '</td></tr>';
            }
        } else {
            $str .= '<tr><td>' . __('Unable to read digests file.') . '</td></tr>';
        }

        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * PHP error_reporting to string
     *
     * @param      int     $intval     The intval
     * @param      string  $separator  The separator
     *
     * @return     string
     */
    private static function errorLevelToString(int $intval, string $separator = ','): string
    {
        $errorlevels = [
            E_ALL               => 'E_ALL',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_STRICT            => 'E_STRICT',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_NOTICE            => 'E_NOTICE',
            E_PARSE             => 'E_PARSE',
            E_WARNING           => 'E_WARNING',
            E_ERROR             => 'E_ERROR',
        ];
        $result = [];
        foreach ($errorlevels as $number => $name) {
            if (($intval & $number) === $number) {
                $result[] = $name;
            }
        }

        return implode($separator, $result);
    }

    /* --- 3rd party plugins specific --- */

    /**
     * Return list of files in static cache
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function staticCache()
    {
        $blog_host = dcCore::app()->blog->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $cache_dir = Path::real(DC_SC_CACHE_DIR, false);
        $cache_key = md5(Http::getHostFromURL($blog_host));
        $cache     = new dcStaticCache(DC_SC_CACHE_DIR, $cache_key);
        $pattern   = implode(DIRECTORY_SEPARATOR, array_fill(0, 5, '%s'));

        if (!is_dir($cache_dir)) {
            return '<p>' . __('Static cache directory does not exists') . '</p>';
        }
        if (!is_readable($cache_dir)) {
            return '<p>' . __('Static cache directory is not readable') . '</p>';
        }
        $k          = str_split($cache_key, 2);
        $cache_root = $cache_dir;
        $cache_dir  = sprintf($pattern, $cache_dir, $k[0], $k[1], $k[2], $cache_key);

        // Add a static cache URL convertor
        $str = '<p class="fieldset">' .
            '<label for="sccalc_url" class="classic">' . __('URL:') . '</label>' . ' ' .
            \form::field('sccalc_url', 50, 255, Html::escapeHTML(dcCore::app()->blog->url)) . ' ' .
            '<input type="button" id="getscaction" name="getscaction" value="' . __(' → ') . '" />' .
            ' <span id="sccalc_res"></span><a id="sccalc_preview" href="#" data-dir="' . $cache_dir . '"></a>' .
            '</p>';

        // List of existing cache files
        $str .= '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="scform">';

        $str .= '<table id="chk-table-result" class="sysinfo">';
        $str .= '<caption>' . __('List of static cache files in') . ' ' . substr($cache_dir, strlen($cache_root)) .
           ', ' . __('last update:') . ' ' . date('Y-m-d H:i:s', $cache->getMtime()) . '</caption>';
        $str .= '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap" colspan="3">' . __('Cache subpath') . '</th>' .
            '<th scope="col" class="nowrap maximal">' . __('Cache file') . '</th>' .
            '</tr>' .
            '</thead>';
        $str .= '<tbody>';

        $files = Files::scandir($cache_dir);
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                    $cache_fullpath = $cache_dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($cache_fullpath)) {
                        $str .= '<tr>' .
                            '<td class="nowrap">' .
                            '<a class="sc_dir" href="#">' . $file . '</a>' .
                            '</td>' .                                     // 1st level
                            '<td class="nowrap">' . __('…') . '</td>' . // 2nd level (loaded via getStaticCacheDir REST)
                            '<td class="nowrap"></td>' .                  // 3rd level (loaded via getStaticCacheList REST)
                            '<td class="nowrap maximal"></td>' .          // cache file (loaded via getStaticCacheList REST too)
                            '</tr>' . "\n";
                    }
                }
            }
        }

        $str .= '</tbody></table>';
        $str .= '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .
            '<p class="col right">' . dcCore::app()->formNonce() . '<input type="submit" class="delete" id="delscaction" name="delscaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with static cache form action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception  (description)
     *
     * @return  string
     */
    public static function doFormStaticCache(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['delscaction'])) {
            // Cope with static cache file deletion
            try {
                if (empty($_POST['sc'])) {
                    throw new Exception(__('No cache file selected'));
                }
                foreach ($_POST['sc'] as $cache_file) {
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (Exception $e) {
                $nextlist = 'sc';
                dcCore::app()->error->add($e->getMessage());
            }
            if (!dcCore::app()->error->flag()) {
                dcPage::addSuccessNotice(__('Selected cache files have been deleted.'));
                Http::redirect(dcCore::app()->admin->getPageURL() . '&sc=1');
            }
        }

        return $nextlist;
    }

    public static function doCheckStaticCache(string $checklist): string
    {
        return !empty($_GET['sc']) ? 'sc' : $checklist;
    }
}
