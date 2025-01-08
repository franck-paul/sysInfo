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
use Dotclear\Plugin\sysInfo\CoreHelper;

/**
 * @todo switch Helper/Html/Form/...
 */
class Behaviors
{
    /**
     * Return list of registered behaviours
     */
    public static function render(): string
    {
        // Affichage de la liste des behaviours inscrits
        $bl = App::behavior()->getBehaviors();

        $str = '<p><a id="sysinfo-preview" href="' . App::blog()->url() . App::url()->getURLFor('sysinfo') . '/behaviours' . '">' . __('Display public behaviours') . '</a></p>';

        $str .= '<table id="behaviours" class="sysinfo"><caption>' . __('Behaviours list') . ' (' . sprintf('%d', count($bl)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Behavior') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        App::lexical()->lexicalKeySort($bl, App::lexical()::ADMIN_LOCALE);
        foreach ($bl as $b => $f) {
            $str .= '<tr><td class="nowrap">' . $b . '</td>';
            $newline = false;
            if (is_array($f)) {
                foreach ($f as $fi) {
                    $str .= ($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal"><code>' . CoreHelper::callableName($fi) . '</code></td>';
                    $newline = true;
                }
            } else {
                $str .= '<td><code>' . $f . '()</code></td>';
            }

            $str .= '</tr>';
        }

        return $str . '</tbody></table>';
    }
}
