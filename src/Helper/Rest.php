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
class Rest
{
    /**
     * Return list of REST methods
     */
    public static function render(): string
    {
        /**
         * @var        \Dotclear\Helper\RestServer
         */
        $rest    = App::rest();
        $methods = $rest->functions;

        $str = '<table id="restmethods" class="sysinfo"><caption>' . __('REST methods') . ' (' . sprintf('%d', count($methods)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Method') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        App::lexical()->lexicalKeySort($methods, App::lexical()::ADMIN_LOCALE);
        foreach ($methods as $method => $callback) {
            $str .= '<tr><td class="nowrap">' . $method . '</td><td class="maximal"><code>';
            $str .= CoreHelper::callableName($callback);
            $str .= '</code></td></tr>';
        }

        return $str . '</tbody></table>';
    }
}
