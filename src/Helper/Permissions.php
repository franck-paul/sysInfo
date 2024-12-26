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

class Permissions
{
    /**
     * Return list of registered permissions
     */
    public static function render(): string
    {
        $permissions = App::auth()->getPermissionsTypes();

        $str = '<table id="permissions" class="sysinfo"><caption>' . __('Types of permission') . ' (' . sprintf('%d', count($permissions)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Type') . '</th>' .
            '<th scope="col" class="maximal">' . __('Label') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($permissions as $n => $l) {
            $str .= '<tr><td class="nowrap">' . $n . '</td>' .
                '<td class="maximal">' . __($l) . '</td>' .
                '</tr>';
        }

        return $str . '</tbody></table>';
    }
}
