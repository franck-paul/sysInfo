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
use Dotclear\Helper\File\Path;
use Dotclear\Plugin\sysInfo\CoreHelper;

class TplPaths
{
    /**
     * Return list of template paths
     *
     * @return     string
     */
    public static function render(): string
    {
        CoreHelper::publicPrepend();
        $paths         = App::frontend()->template()->getPath();
        $document_root = (empty($_SERVER['DOCUMENT_ROOT']) ? '' : $_SERVER['DOCUMENT_ROOT']);

        $str = '<table id="chk-table-result" class="sysinfo"><caption>' . __('List of template paths') . ' (' . sprintf('%d', count($paths)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col">' . __('Path') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
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

            $str .= '<tr><td>' . CoreHelper::simplifyFilename($sub_path) . '</td><tr>';
        }

        $str .= '</tbody></table>';

        return $str . ('<p><a id="sysinfo-preview" href="' . App::blog()->url() . App::url()->getURLFor('sysinfo') . '/templatetags' . '">' . __('Display template tags') . '</a></p>');
    }
}
