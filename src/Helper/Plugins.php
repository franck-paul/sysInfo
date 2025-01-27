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
use Dotclear\Helper\Date;
use Dotclear\Module\ModuleDefine;
use Dotclear\Plugin\sysInfo\CoreHelper;

/**
 * @todo switch Helper/Html/Form/...
 */
class Plugins
{
    /**
     * Return list of plugins
     */
    public static function render(): string
    {
        // Affichage de la liste des plugins (et de leurs propriétés)
        $plugins = App::plugins()->getDefines(['state' => ModuleDefine::STATE_ENABLED], true);

        $count = count($plugins) > 0 ? ' (' . sprintf('%d', count($plugins)) . ')' : '';

        $str = '<h3>' . __('Plugins (in loading order)') . $count . '</h3>';
        $str .= '<details id="expand-all"><summary>' . __('Plugin id') . __(' (priority, name)') . '</summary></details>';
        foreach ($plugins as $id => $m) {
            $info = sprintf(' (%s, %s)', number_format($m['priority'] ?? 1000, 0, '.', '&nbsp;'), $m['name'] ?? $id);
            $str .= '<details id="p-' . $id . '"><summary><strong>' . $id . '</strong>' . $info . '</summary>';
            $str .= '<ul>';
            foreach ($m as $key => $val) {
                $value = print_r($val, true);
                if (in_array($key, ['requires', 'implies', 'cannot_enable', 'cannot_disable'])) {
                    if ((is_countable($val) ? count($val) : 0) > 0) {
                        $value = [];
                        foreach ($val as $module) {
                            $version = '';
                            if (is_array($module)) {
                                if (isset($module[1])) {
                                    $version = ' (' . $module[1] . ')';
                                }

                                $module = $module[0];
                            }

                            $value[] = $module !== 'core' ? ('<a href="#p-' . $module . '">' . $module . '</a>' . $version) : 'Dotclear' . $version;
                        }

                        $value = implode(', ', $value);
                    }
                } elseif (in_array($key, ['support', 'details', 'repository'])) {
                    $value = '<a href="' . $value . '">' . $value . '</a>';
                } elseif ($key == 'root') {
                    $value = CoreHelper::simplifyFilename($value, true);
                } elseif ($key === 'date') {
                    $value = Date::dt2str(App::blog()->settings()->get('system')->get('date_format'), $value, App::auth()->getInfo('user_tz')) . ' ' . Date::dt2str(App::blog()->settings()->get('system')->get('time_format'), $value, App::auth()->getInfo('user_tz'));
                }

                $str .= '<li>' . $key . ' = ' . $value . '</li>';
            }

            $str .= '</ul>';
            $str .= '</details>';
        }

        return $str;
    }
}
