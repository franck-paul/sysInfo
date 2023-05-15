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

class Autoloader
{
    /**
     * Return autoloader infos
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function render(): string
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
}
