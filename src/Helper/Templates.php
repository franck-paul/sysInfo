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
use Dotclear\Core\Frontend\Utility;
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
     *
     * @return     string
     */
    public static function render(): string
    {
        $tplset = CoreHelper::publicPrepend();

        $document_root = (empty($_SERVER['DOCUMENT_ROOT']) ? '' : $_SERVER['DOCUMENT_ROOT']);
        $cache_path    = (string) Path::real(App::config()->cacheRoot());
        if (str_starts_with($cache_path, $document_root)) {
            $cache_path = substr($cache_path, strlen($document_root));
        } elseif (str_starts_with($cache_path, App::config()->dotclearRoot())) {
            $cache_path = substr($cache_path, strlen(App::config()->dotclearRoot()));
        }

        $blog_host = App::blog()->host();
        if (!str_ends_with($blog_host, '/')) {
            $blog_host .= '/';
        }

        $blog_url = App::blog()->url();
        if (str_starts_with($blog_url, $blog_host)) {
            $blog_url = substr($blog_url, strlen($blog_host));
        }

        $paths = App::frontend()->template()->getPath();

        $str = '<form action="' . App::backend()->getPageURL() . '" method="post" id="tplform">' .
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
            $sub_path = (string) Path::real($path, false);
            if (str_starts_with($sub_path, $document_root)) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (str_starts_with($sub_path, '/')) {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (str_starts_with($sub_path, App::config()->dotclearRoot())) {
                $sub_path = substr($sub_path, strlen(App::config()->dotclearRoot()));
                if (str_starts_with($sub_path, '/')) {
                    $sub_path = substr($sub_path, 1);
                }
            }

            $path_displayed = false;
            // Don't know exactly why but need to cope with */Utility::TPL_ROOT !
            $md5_path = (strstr($path, '/' . Utility::TPL_ROOT . '/' . $tplset) ? Path::real($path) : $path);
            $files    = Files::scandir($path);
            foreach ($files as $file) {
                if (preg_match('/^(.*)\.(html|xml|xsl)$/', $file, $matches) && isset($matches[1]) && !in_array($file, $stack)) {
                    $stack[]        = $file;
                    $cache_file     = md5($md5_path . DIRECTORY_SEPARATOR . $file) . '.php';
                    $cache_subpath  = sprintf('%s/%s', substr($cache_file, 0, 2), substr($cache_file, 2, 2));
                    $cache_fullpath = Path::real(App::config()->cacheRoot()) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR . $cache_subpath;
                    $file_check     = $cache_fullpath . DIRECTORY_SEPARATOR . $cache_file;
                    $file_exists    = file_exists($file_check);
                    $title          = CoreHelper::simplifyFilename($sub_path) . DIRECTORY_SEPARATOR . $file;
                    $str .= '<tr><td>' . ($path_displayed ? '' : CoreHelper::simplifyFilename($sub_path)) . '</td>' .
                        '<td class="nowrap">' . $file . '</td>' .
                        '<td class="nowrap">' . '<img src="images/' . ($file_exists ? 'check-on.png' : 'check-off.png') . '"> ' . $cache_subpath . '</td>' .
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
     *
     * @return  string
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
                    $cache_file = $root_cache . sprintf('%s' . DIRECTORY_SEPARATOR . '%s', substr($v, 0, 2), substr($v, 2, 2)) . DIRECTORY_SEPARATOR . $v;
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
