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

use dcCore;
use dcPage;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;
use Exception;

class StaticCache
{
    /**
     * Return list of files in static cache
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function render()
    {
        $blog_host = dcCore::app()->blog->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $cache_dir = Path::real(DC_SC_CACHE_DIR, false);
        $cache_key = md5(Http::getHostFromURL($blog_host));
        $cache     = new \Dotclear\Plugin\staticCache\StaticCache(DC_SC_CACHE_DIR, $cache_key);
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
    public static function process(string $checklist): string
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

    public static function check(string $checklist): string
    {
        return !empty($_GET['sc']) ? 'sc' : $checklist;
    }
}
