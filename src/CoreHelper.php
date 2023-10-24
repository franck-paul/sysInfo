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

use Dotclear\App;
use Dotclear\Core\Frontend\Utility;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Html;
use Dotclear\Module\ModuleDefine;
use Dotclear\Plugin\sysInfo\Helper\Constants;
use Dotclear\Plugin\sysInfo\Helper\Folders;
use Dotclear\Plugin\sysInfo\Helper\Globals;
use Dotclear\Plugin\sysInfo\Helper\Plugins;
use Dotclear\Plugin\sysInfo\Helper\System;
use Dotclear\Plugin\sysInfo\Helper\TplPaths;
use Dotclear\Plugin\sysInfo\Helper\UrlHandlers;
use Exception;

class CoreHelper
{
    public static string $redact;

    /**
     * Display full report in a textarea, ready to copy'n'paste
     *
     * @return     string
     */
    public static function renderReport(): string
    {
        // Capture everything
        ob_start();

        echo System::render(false);

        echo Constants::render();
        echo Folders::render();
        echo Globals::render();

        echo UrlHandlers::render();

        echo TplPaths::render();

        echo Plugins::render();

        // Get capture content
        $buffer = (string) ob_get_clean();

        // Transform HTML to text

        return '<h3>' . __('Report') . '</h3>' .

        (new Form('report'))
            ->action(App::backend()->getPageURL())
            ->method('post')
            ->fields([
                (new Submit(['getreport']))
                    ->value(__('Download report')),
                (new Hidden(['htmlreport']))
                    ->value(Html::escapeHTML($buffer)),
                ... My::hiddenFields(),
            ])->render() .

        '<pre>' . $buffer . '</pre>';
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
    public static function downloadReport(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['getreport'])) {
            // Cope with report download
            try {
                if (empty($_POST['htmlreport'])) {
                    throw new Exception(__('Report empty'));
                }
                $path = Path::real(implode(DIRECTORY_SEPARATOR, [App::config()->cacheRoot(), 'sysinfo']), false);
                if ($path !== false) {
                    if (!is_dir($path)) {
                        Files::makeDir($path, true);
                    }

                    $filename  = date('Y-m-d') . '-' . App::blog()->id() . '-report';
                    $extension = '.html';
                    $file      = implode(DIRECTORY_SEPARATOR, [$path, $filename . $extension]);

                    // Prepare report
                    if (file_exists($file)) {
                        unlink($file);
                    }

                    if ($fp = fopen($file, 'wt')) {
                        // Begin HTML Document
                        $report = Html::decodeEntities($_POST['htmlreport']);
                        $report = str_replace('<img src="images/check-on.png" />', '✅', $report, $count);
                        $report = str_replace('<img src="images/check-off.png" />', '⛔️', $report);
                        $report = str_replace(App::config()->dotclearRoot(), '<code>DC_ROOT</code> ', $report);
                        fwrite($fp, '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">' .
                            '<title>Dotclear sysInfo report: ' . date('Y-m-d') . '-' . App::blog()->id() . '</title></head><body>');
                        fwrite($fp, Html::decodeEntities($report));
                        fwrite($fp, '</body></html>');
                        fclose($fp);
                    }

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
                }
            } catch (Exception $e) {
                $checklist = 'report';
                App::error()->add($e->getMessage());
            }
        }

        return $nextlist;
    }

    /**
     * Emulate public prepend
     *
     * @return     string  template set name
     */
    public static function publicPrepend(): string
    {
        // Emulate public prepend
        define('DC_CONTEXT_PUBLIC', true);
        App::task()->addContext('FRONTEND');

        new Utility();

        App::themes()->loadModules(App::blog()->themesPath());
        if (!isset(App::frontend()->theme)) {     // @phpstan-ignore-line
            App::frontend()->theme = App::blog()->settings()->system->theme;
        }
        if (!App::themes()->moduleExists(App::frontend()->theme)) {
            App::frontend()->theme = App::blog()->settings()->system->theme = App::config()->defaultTheme();
        }
        $tplset                       = App::themes()->moduleInfo(App::frontend()->theme, 'tplset');
        App::frontend()->parent_theme = App::themes()->moduleInfo(App::frontend()->theme, 'parent');
        if (App::frontend()->parent_theme && !App::themes()->moduleExists(App::frontend()->parent_theme)) {
            App::frontend()->theme        = App::blog()->settings()->system->theme = App::config()->defaultTheme();
            App::frontend()->parent_theme = null;
        }
        $tpl_path = [
            App::blog()->themesPath() . '/' . App::frontend()->theme . '/tpl',
        ];
        if (App::frontend()->parent_theme) {
            $tpl_path[] = App::blog()->themesPath() . '/' . App::frontend()->parent_theme . '/tpl';
            if (empty($tplset)) {
                $tplset = App::themes()->moduleInfo(App::frontend()->parent_theme, 'tplset');
            }
        }
        if (empty($tplset)) {
            $tplset = App::config()->defaultTplset();
        }
        $main_plugins_root = explode(PATH_SEPARATOR, App::config()->pluginsRoot());
        App::frontend()->template()->setPath(
            $tpl_path,
            $main_plugins_root[0] . '/../inc/public' . '/' . Utility::TPL_ROOT . '/' . $tplset,
            App::frontend()->template()->getPath()
        );

        // Looking for Utility::TPL_ROOT in each plugin's dir
        $plugins = array_keys(App::plugins()->getDefines(['state' => ModuleDefine::STATE_ENABLED], true));
        foreach ($plugins as $k) {
            $plugin_root = App::plugins()->moduleInfo((string) $k, 'root');
            if ($plugin_root) {
                App::frontend()->template()->appendPath(implode(DIRECTORY_SEPARATOR, [$plugin_root, Utility::TPL_ROOT, $tplset]));
                // To be exhaustive add also direct directory (without templateset)
                App::frontend()->template()->appendPath(implode(DIRECTORY_SEPARATOR, [$plugin_root, Utility::TPL_ROOT]));
            }
        }

        return $tplset;
    }

    /**
     * Simplify filename
     *
     * @param      string        $file   The file
     * @param      bool          $real   Compute the real path if possible
     *
     * @return     string
     */
    public static function simplifyFilename(string $file, bool $real = false): string
    {
        if (!isset(static::$redact)) {
            $settings       = My::settings();
            static::$redact = $settings->redact ?? '';
        }

        $bases = array_map(fn ($path) => Path::real($path), [
            App::config()->dotclearRoot(),                  // Core
            App::blog()->themesPath(),                      // Theme
            ...explode(PATH_SEPARATOR, App::config()->pluginsRoot()),    // Plugins
        ]);
        $prefixes = ['[core]', '[theme]', '[plugin]'];

        if ($real && ($new = Path::real($file))) {
            $file = $new;
        }

        foreach ($bases as $index => $base) {
            // Filter bases (beginning of path) of file
            if (strstr($file, (string) $base)) {
                $file = str_replace((string) $base, $prefixes[min($index, 2)], $file);
            }
        }

        if (static::$redact !== '') {
            $file = str_replace(static::$redact, '[***]', $file);
        }

        return $file;
    }
}
