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

namespace Dotclear\Plugin\sysInfo;

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
}
