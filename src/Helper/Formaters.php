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

/**
 * @todo switch Helper/Html/Form/...
 */
class Formaters
{
    /**
     * Return list of formaters (syntaxes coped by installed editors)
     */
    public static function render(): string
    {
        // Affichage de la liste des éditeurs et des syntaxes par éditeur
        $formaters = App::formater()->getFormaters();

        $str = '<table id="formaters" class="sysinfo"><caption>' . __('Editors and their supported syntaxes') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Editor') . '</th>' .
            '<th scope="col">' . __('Code') . '</th>' .
            '<th scope="col" class="maximal">' . __('Syntax') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($formaters as $e => $s) {
            $str .= '<tr><td class="nowrap">' . $e . '</td>';
            $newline = false;
            foreach ($s as $f) {
                $l = App::formater()->getFormaterName($f);
                $str .= ($newline ? '</tr><tr><td></td>' : '') . '<td>' . $f . '</td><td class="maximal">' . $l . '</td>' ;
                $newline = true;
            }

            $str .= '</tr>';
        }

        return $str . '</tbody></table>';
    }
}
