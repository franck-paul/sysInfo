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

class Behaviors
{
    /**
     * Return list of registered behaviours
     *
     * @return     string
     */
    public static function render(): string
    {
        // Affichage de la liste des behaviours inscrits
        $bl = App::behavior()->getBehaviors();

        $str = '<p><a id="sysinfo-preview" href="' . App::blog()->url() . App::url()->getURLFor('sysinfo') . '/behaviours' . '">' . __('Display public behaviours') . '</a></p>';

        $str .= '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Behaviours list') . ' (' . sprintf('%d', count($bl)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Behavior') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        App::lexical()->lexicalKeySort($bl, LexicalInterface::ADMIN_LOCALE);
        foreach ($bl as $b => $f) {
            $str .= '<tr><td class="nowrap">' . $b . '</td>';
            $newline = false;
            if (is_array($f)) {
                foreach ($f as $fi) {
                    $str .= ($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal"><code>';
                    if (is_array($fi)) {
                        if (is_object($fi[0])) {
                            $str .= get_class($fi[0]) . '-&gt;' . $fi[1];
                        } else {
                            $str .= $fi[0] . '::' . $fi[1];
                        }
                    } else {
                        if ($fi instanceof \Closure) {
                            $r  = new ReflectionFunction($fi);
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
                            $str .= $fi;
                        }
                    }
                    $str .= '()</code></td>';
                    $newline = true;
                }
            } else {
                $str .= '<td><code>' . $f . '()</code></td>';
            }
            $str .= '</tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }
}
