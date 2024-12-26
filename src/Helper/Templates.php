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

namespace Dotclear\Plugin\sysInfo\Helper;

use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Template\Template;
use Dotclear\Plugin\sysInfo\CoreHelper;
use Dotclear\Plugin\sysInfo\My;
use Exception;

class Templates
{
    /**
     * Return list of compiled template's files
     */
    public static function render(): string
    {
        CoreHelper::publicPrepend();

        $document_root = (empty($_SERVER['DOCUMENT_ROOT']) ? '' : $_SERVER['DOCUMENT_ROOT']);
        $cache_path    = (string) Path::real(App::config()->cacheRoot());
        if (str_starts_with($cache_path, (string) $document_root)) {
            $cache_path = substr($cache_path, strlen((string) $document_root));
        } elseif (str_starts_with($cache_path, (string) App::config()->dotclearRoot())) {
            $cache_path = substr($cache_path, strlen((string) App::config()->dotclearRoot()));
        }

        $blog_host = App::blog()->host();
        if (!str_ends_with((string) $blog_host, '/')) {
            $blog_host .= '/';
        }

        $blog_url = App::blog()->url();
        if (str_starts_with((string) $blog_url, (string) $blog_host)) {
            $blog_url = substr((string) $blog_url, strlen((string) $blog_host));
        }

        $paths = App::frontend()->template()->getPath();

        $str = '<form action="' . App::backend()->getPageURL() . '" method="post" id="tplform">' .
            '<table id="templates" class="sysinfo">' .
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
            $sub_path = (string) Path::real($path, false);
            if (str_starts_with($sub_path, (string) $document_root)) {
                $sub_path = substr($sub_path, strlen((string) $document_root));
                if (str_starts_with($sub_path, '/')) {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (str_starts_with($sub_path, (string) App::config()->dotclearRoot())) {
                $sub_path = substr($sub_path, strlen((string) App::config()->dotclearRoot()));
                if (str_starts_with($sub_path, '/')) {
                    $sub_path = substr($sub_path, 1);
                }
            }

            $path_displayed = false;
            $md5_path       = $path;
            if (str_starts_with((string) Path::real($path), (string) App::config()->dotclearRoot())) {
                $md5_path = Path::real($path);
            }
            $files = Files::scandir($path);
            foreach ($files as $file) {
                if (preg_match('/^(.*)\.(html|xml|xsl)$/', $file, $matches) && !in_array($file, $stack)) {
                    $stack[]        = $file;
                    $cache_file     = md5($md5_path . DIRECTORY_SEPARATOR . $file) . '.php';
                    $cache_subpath  = sprintf('%s/%s', substr($cache_file, 0, 2), substr($cache_file, 2, 2));
                    $cache_fullpath = Path::real(App::config()->cacheRoot()) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR . $cache_subpath;
                    $file_check     = $cache_fullpath . DIRECTORY_SEPARATOR . $cache_file;
                    $file_exists    = file_exists($file_check);
                    $title          = CoreHelper::simplifyFilename($sub_path) . DIRECTORY_SEPARATOR . $file;
                    $str .= '<tr><td>' . ($path_displayed ? '' : CoreHelper::simplifyFilename($sub_path)) . '</td>' .
                        '<td class="nowrap">' . $file . '</td>' .
                        '<td class="nowrap">' . '<img class="mark mark-' . ($file_exists ? 'check-on' : 'check-off') . '" src="images/' . ($file_exists ? 'check-on.svg' : 'check-off.svg') . '"> ' . $cache_subpath . '</td>' .
                        '<td class="nowrap">' .
                        (new Checkbox(['tpl[]'], false))
                            ->value($cache_file)
                            ->class(($file_exists) ? 'tpl_compiled' : '')
                            ->disabled(!($file_exists))
                            ->render() . ' ' .
                        '<label class="classic">' .
                        ($file_exists ? '<a class="tpl_compiled" title="' . $title . '" href="#">' : '') .
                        $cache_file .
                        ($file_exists ? '</a>' : '') .
                        '</label></td>' .
                        '</tr>';
                    $path_displayed = true;
                }
            }
        }

        return $str . ('</tbody></table><div class="two-cols"><p class="col checkboxes-helpers"></p><p class="col right">' . My::parsedHiddenFields() . '<input type="submit" class="delete" id="deltplaction" name="deltplaction" value="' . __('Delete selected cache files') . '"></p>' .
            '</div>' .
            '</form>');
    }

    /**
     * Cope with form templates action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     */
    public static function process(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['deltplaction'])) {
            // Cope with cache file deletion
            try {
                if (empty($_POST['tpl'])) {
                    throw new Exception(__('No cache file selected'));
                }

                $root_cache = Path::real(App::config()->cacheRoot()) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR;
                foreach ($_POST['tpl'] as $v) {
                    $cache_file = $root_cache . sprintf('%s' . DIRECTORY_SEPARATOR . '%s', substr((string) $v, 0, 2), substr((string) $v, 2, 2)) . DIRECTORY_SEPARATOR . $v;
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (Exception $e) {
                $nextlist = 'templates';
                App::error()->add($e->getMessage());
            }

            if (!App::error()->flag()) {
                Notices::addSuccessNotice(__('Selected cache files have been deleted.'));
                My::redirect([
                    'tpl' => 1,
                ]);
            }
        }

        return $nextlist;
    }

    public static function check(string $checklist): string
    {
        return empty($_GET['tpl']) ? $checklist : 'templates';
    }
}
