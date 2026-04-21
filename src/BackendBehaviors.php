<?php

/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo;

use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Interface\Core\BlogSettingsInterface;
use Dotclear\Plugin\sysInfo\MaintenanceTask\ReportCache;
use Dotclear\Plugin\maintenance\Maintenance;

class BackendBehaviors
{
    /**
     * dcMaintenanceInit Add cache emptying maintenance task
     */
    public static function dcMaintenanceInit(Maintenance $maintenance): string
    {
        $maintenance->addTask(ReportCache::class);

        return '';
    }

    public static function adminBlogPreferencesForm(BlogSettingsInterface $settings): string
    {
        // Blog settings
        $public_tpl_use_cache = is_bool($public_tpl_use_cache = $settings->system->tpl_use_cache) && $public_tpl_use_cache;

        // sysInfo settings
        $settings               = My::settings();
        $public_debug           = is_bool($public_debug = $settings->public_debug)                     && $public_debug;
        $public_debug_adminonly = is_bool($public_debug_adminonly = $settings->public_debug_adminonly) && $public_debug_adminonly;

        // Add fieldset for plugin options
        echo
        (new Fieldset('sysinfo'))
            ->legend((new Legend(__('System Information'))))
            ->fields([
                (new Para())
                    ->items([
                        (new Checkbox('sysinfo_public_debug', $public_debug))
                            ->value(1)
                            ->label((new Label(__('Display debug information on each public page'), Label::INSIDE_TEXT_AFTER))),
                    ]),
                (new Para())
                    ->items([
                        (new Checkbox('sysinfo_public_debug_adminonly', $public_debug_adminonly))
                            ->value(1)
                            ->label((new Label(__('Only if an administrator is connected'), Label::INSIDE_TEXT_AFTER))),
                    ]),
                (new Note())
                    ->class(['form-note', 'info'])
                    ->text(__('You may use FrontendSession plugin to permit administrator connection on public page.')),
                (new Para())
                    ->items([
                        (new Checkbox('sysinfo_tpl_use_cache', $public_tpl_use_cache))
                            ->value(1)
                            ->label((new Label(__('Use cache for template engine'), Label::INSIDE_TEXT_AFTER))),
                    ]),
            ])
        ->render();

        return '';
    }

    public static function adminBeforeBlogSettingsUpdate(BlogSettingsInterface $settings): string
    {
        // Blog settings
        $settings->system->put('tpl_use_cache', !empty($_POST['sysinfo_tpl_use_cache']), 'boolean');

        // sysInfo settings
        $settings = My::settings();
        $settings->put('public_debug', !empty($_POST['sysinfo_public_debug']), 'boolean');
        $settings->put('public_debug_adminonly', !empty($_POST['sysinfo_public_debug_adminonly']), 'boolean');

        return '';
    }
}
