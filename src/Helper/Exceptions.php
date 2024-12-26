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

class Exceptions
{
    /**
     * Return list of known exceptions
     */
    public static function render(): string
    {
        // Récupération de la liste des exceptions connues
        $list = [];
        foreach (\Dotclear\Exception\ExceptionEnum::cases() as $enum) {
            $list[$enum->name] = [
                'value' => $enum->value,
                'code'  => $enum->code(),
                'label' => $enum->label(),
            ];
        }

        App::lexical()->lexicalKeySort($list, App::lexical()::ADMIN_LOCALE);

        $str = '<table id="exceptions" class="sysinfo"><caption>' . __('Registered Exceptions') . ' (' . sprintf('%d', count($list)) . ')' . '</caption>' .
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('Value') . '</th>' .
            '<th scope="col">' . __('Code') . '</th>' .
            '<th scope="col">' . __('Label') . '</th></tr></thead>' .
            '<tbody>';
        foreach ($list as $name => $info) {
            $str .= '<tr><td scope="row" class="nowrap">' . $name . '</td>' .
                '<td><code>' . $info['value'] . '</code></td>' .
                '<td><code>' . $info['code'] . '</code></td>' .
                '<td><code>' . $info['label'] . '</code></td>' .
                '</tr>';
        }

        return $str . '</tbody></table>';
    }
}
