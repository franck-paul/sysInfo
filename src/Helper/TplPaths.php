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
use Dotclear\Helper\File\Path;
use Dotclear\Plugin\sysInfo\Helper;

class TplPaths
{
    /**
     * Return list of template paths
     *
     * @return     string
     */
    public static function render(): string
    {
        Helper::publicPrepend();
        $paths         = dcCore::app()->tpl->getPath();
        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('List of template paths') . ' (' . sprintf('%d', count($paths)) . ')' . '</caption>' . // @phpstan-ignore-line
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
}
