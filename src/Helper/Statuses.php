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
use Dotclear\Helper\Stack\Status;

/**
 * @todo switch Helper/Html/Form/...
 */
class Statuses
{
    /**
     * Return list of statuses
     */
    public static function render(): string
    {
        // Affichage de la liste des status
        $str = '<table id="statuses" class="sysinfo"><caption>' . __('Statuses') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Type') . '</th>' .
            '<th scope="col">' . __('ID') . '</th>' .
            '<th scope="col">' . __('Value') . '</th>' .
            '<th scope="col" class="maximal">' . __('Name') . '</th>' .
            '<th scope="col">' . __('Hidden') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        $statuses = App::status()->blog()->dump(true);
        $type     = 'App::status()->blog()';
        foreach ($statuses as $status) {
            $str .= self::renderRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        $statuses = App::status()->user()->dump(true);
        $type     = 'App::status()->user()';
        foreach ($statuses as $status) {
            $str .= self::renderRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        $statuses = App::status()->post()->dump(true);
        $type     = 'App::status()->post()';
        foreach ($statuses as $status) {
            $str .= self::renderRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        $statuses = App::status()->comment()->dump(true);
        $type     = 'App::status()->comment()';
        foreach ($statuses as $status) {
            $str .= self::renderRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        return $str . '</tbody></table>';
    }

    protected static function renderRow(Status $status, string $type = ''): string
    {
        $str = '<tr>';
        if ($type !== '') {
            $str .= '<td class="nowrap">' . $type . '</td>';
        } else {
            $str .= '<td></td>';
        }
        $str .= '<td>' . $status->id() . '</td>' .
            '<td class="right"><code>' . $status->level() . '</code></td>' .
            '<td class="maximal">' . $status->name() . '</td>' .
            '<td>' . ($status->hidden() ? 'true' : 'false') . '</td>'
        ;

        return $str . '</tr>';
    }
}
