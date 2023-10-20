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
use Dotclear\Interface\Core\LexicalInterface;
use ReflectionFunction;

class Rest
{
    /**
     * Return list of REST methods
     *
     * @return     string
     */
    public static function render(): string
    {
        $methods = App::rest()->functions;  // @phpstan-ignore-line

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('REST methods') . ' (' . sprintf('%d', count($methods)) . ')' . '</caption>' .    // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Method') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        App::lexical()->lexicalKeySort($methods, LexicalInterface::ADMIN_LOCALE);
        foreach ($methods as $method => $callback) {
            $str .= '<tr><td class="nowrap">' . $method . '</td><td class="maximal"><code>';
            if (is_array($callback)) {
                if (count($callback) > 1) {
                    if (is_string($callback[0])) {
                        $str .= $callback[0] . '::' . $callback[1];
                    } else {
                        $str .= get_class($callback[0]) . '->' . $callback[1];
                    }
                } else {
                    $str .= $callback[0];
                }
            } else {
                if ($callback instanceof \Closure) {
                    $r  = new ReflectionFunction($callback);
                    $ns = $r->getNamespaceName() ? $r->getNamespaceName() . '::' : '';
                    $fn = $r->getShortName() ? $r->getShortName() : '__closure__';
                    if ($ns === '') {
                        // Cope with class::method(...) forms
                        $c = $r->getClosureScopeClass();
                        if (!is_null($c)) {
                            $ns = $c->getNamespaceName() ? $c->getNamespaceName() . '::' : '';
                        }
                    }
                    $str .= $ns . $fn;
                } else {
                    $str .= $callback;
                }
            }
            $str .= '()</code></td></tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }
}
