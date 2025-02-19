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
use Dotclear\Helper\Html\Form\Details;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Summary;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Ul;
use Dotclear\Module\ModuleDefine;
use Dotclear\Plugin\sysInfo\CoreHelper;

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

        $lines = [];

        foreach ($plugins as $id => $m) {
            $info  = sprintf(' (%s, %s)', number_format($m['priority'] ?? 1000, 0, '.', '&nbsp;'), $m['name'] ?? $id);
            $infos = [];
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

                            $value[] = $module !== 'core' ?
                                (new Link())
                                    ->href('#p-' . $module)
                                    ->text($module)
                                ->render() . $version :
                                'Dotclear';
                        }

                        $value = implode(', ', $value);
                    }
                } elseif (in_array($key, ['support', 'details', 'repository'])) {
                    $value = (new Link())
                        ->href($value)
                        ->text($value)
                    ->render();
                } elseif ($key === 'root') {
                    $value = CoreHelper::simplifyFilename($value, true);
                } elseif ($key === 'date') {
                    $value = Date::dt2str(App::blog()->settings()->get('system')->get('date_format'), $value, App::auth()->getInfo('user_tz')) . ' ' . Date::dt2str(App::blog()->settings()->get('system')->get('time_format'), $value, App::auth()->getInfo('user_tz'));
                }

                $infos[] = (new Li())
                    ->text($key . ' = ' . $value);
            }

            $lines[] = (new Set())
                ->items([
                    (new Details('p-' . $id))
                        ->summary(new Summary((new Text('strong', (string) $id))->render() . $info))
                        ->items([
                            (new Ul())
                                ->items($infos),
                        ]),
                ]);
        }

        return (new Set())
            ->items([
                (new Text('h3', __('Plugins (in loading order)') . $count)),
                (new Details('expand-all'))
                    ->summary(new Summary(__('Plugin id') . __(' (priority, name)'))),
                (new Set())
                    ->items($lines),
            ])
        ->render();
    }
}
