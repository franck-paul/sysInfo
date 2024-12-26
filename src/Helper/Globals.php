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

class Globals
{
    /**
     * Return list of global variables
     */
    public static function render(): string
    {
        $max_length = 1024 * 4;     // 4Kb max

        $variables = array_map(fn ($v): string => (string) $v, array_keys($GLOBALS));
        App::lexical()->lexicalSort($variables, App::lexical()::ADMIN_LOCALE);

        $deprecated = [
            '__autoload'     => '2.23',
            '__l10n'         => '2.24',
            '__l10n_files'   => '2.24',
            '__parent_theme' => '2.23',
            '__resources'    => '2.23',
            '__smilies'      => '2.23',
            '__theme'        => '2.23',
            '__widgets'      => '2.23',

            '_ctx'          => '2.23',
            '_lang'         => '2.23',
            '_menu'         => '2.23',
            '_page_number'  => '2.23',
            '_search'       => '2.23',
            '_search_count' => '2.23',

            'core'      => '2.23',
            'mod_files' => '2.23',
            'mod_ts'    => '2.23',
            'p_url'     => '2.23',
        ];

        $str = '<table id="globals" class="sysinfo"><caption>' . __('Global variables') . ' (' . sprintf('%d', count($variables)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col" class="maximal">' . __('Content') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        // First loop for non deprecated variables
        foreach ($variables as $variable) {
            if (!in_array($variable, array_keys($deprecated))) {
                $str .= '<tr><td class="nowrap">' . $variable . '</td>';
                if (is_array($GLOBALS[$variable])) {
                    $values = $GLOBALS[$variable];
                    App::lexical()->lexicalKeySort($values, App::lexical()::ADMIN_LOCALE);
                    $content = '<ul>';
                    foreach ($values as $key => $value) {
                        $type = '';
                        $content .= '<li><strong>' . $key . '</strong> = ' . '<code>' . CoreHelper::simplifyFilename(print_r($value, true)) . '</code>' . $type . '</li>';
                    }

                    $content .= '</ul>';
                } else {
                    $content = CoreHelper::simplifyFilename(print_r($GLOBALS[$variable], true));
                    if (mb_strlen($content) > $max_length) {
                        $content = mb_substr($content, 0, $max_length) . ' …';
                    }
                }

                $str .= '<td class="maximal">' . $content . '</td>';
                $str .= '</tr>';
            }
        }

        // Second loop for deprecated variables
        foreach ($variables as $variable) {
            if (in_array($variable, array_keys($deprecated))) {
                $str .= '<tr><td class="nowrap">' . $variable . '</td>';
                $str .= '<td class="maximal deprecated">' . sprintf(__('*** deprecated since %s ***'), $deprecated[$variable]) . '</td>';
                $str .= '</tr>';
            }
        }

        return $str . '</tbody></table>';
    }
}
