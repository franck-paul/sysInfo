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
use dcTemplate;
use dcThemes;
use Dotclear\App;
use Dotclear\Core\Frontend\Utility;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Html;
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
        $buffer = ob_get_clean();

        // Transform HTML to text

        return '<h3>' . __('Report') . '</h3>' .

        (new Form('report'))
            ->action(dcCore::app()->admin->getPageURL())
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
     * Emulate public prepend
     *
     * @return     string  template set name
     */
    public static function publicPrepend(): string
    {
        // Emulate public prepend
        define('DC_CONTEXT_PUBLIC', true);

        if (version_compare(DC_VERSION, '2.28-dev', '>=')) {
            App::task()->addContext('FRONTEND');
        }

        dcCore::app()->public = new Utility();

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
            $main_plugins_root[0] . '/../inc/public' . '/' . Utility::TPL_ROOT . '/' . $tplset,
            dcCore::app()->tpl->getPath()
        );

        // Looking for Utility::TPL_ROOT in each plugin's dir
        $plugins = array_keys(dcCore::app()->plugins->getDefines(['state' => dcModuleDefine::STATE_ENABLED], true));
        foreach ($plugins as $k) {
            $plugin_root = dcCore::app()->plugins->moduleInfo($k, 'root');
            if ($plugin_root) {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), $plugin_root . '/' . Utility::TPL_ROOT . '/' . $tplset);
                // To be exhaustive add also direct directory (without templateset)
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), $plugin_root . '/' . Utility::TPL_ROOT);
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
            DC_ROOT,                                        // Core
            dcCore::app()->blog->themes_path,               // Theme
            ...explode(PATH_SEPARATOR, DC_PLUGINS_ROOT),    // Plugins
        ]);
        $prefixes = ['[core]', '[theme]', '[plugin]'];

        if ($real && ($new = Path::real($file))) {
            $file = $new;
        }

        foreach ($bases as $index => $base) {
            // Filter bases (beginning of path) of file
            if (strstr($file, $base)) {
                $file = str_replace($base, $prefixes[min($index, 2)], $file);
            }
        }

        if (static::$redact !== '') {
            $file = str_replace(static::$redact, '[***]', $file);
        }

        return $file;
    }
}
