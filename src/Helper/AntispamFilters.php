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

use Dotclear\Plugin\antispam\Antispam;

class AntispamFilters
{
    /**
     * Return list of antispam filters
     *
     * @return     string
     */
    public static function render(): string
    {
        // Get antispam filters
        Antispam::initFilters();
        $fs = Antispam::$filters->getFilters();

        $str = '<table id="chk-table-result" class="sysinfo"><caption>' . __('Antispam filters') . ' (' . sprintf('%d', count($fs)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('ID') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('GUI') . '</th>' .
            '<th scope="col" class="maximal">' . __('URL') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        foreach ($fs as $f) {
            $str .= '<tr>' .
                '<td class="nowrap">' . $f->id . '</td>' .
                '<td class="nowrap">' . $f->name . '</td>' .
                '<td>' . ($f->hasGUI() ? __('yes') : __('no')) . '</td>' .
                '<td class="maximal"><code>' . $f->guiURL() . '</code></td>' .
            '</tr>';
        }

        return $str . '</tbody></table>';
    }
}
