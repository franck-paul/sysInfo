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

use dcCore;
use dcNsProcess;
use Dotclear\Plugin\Uninstaller\Uninstaller;

class Uninstall extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_RC_PATH');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init || !dcCore::app()->plugins->moduleExists('Uninstaller')) {
            return false;
        }

        $module = My::id();

        Uninstaller::instance()

            // User actions

            ->addUserAction('caches', 'empty', $module)
            ->addUserAction('caches', 'delete', $module)

            //->addUserAction('vars', 'delete', implode(DIRECTORY_SEPARATOR, ['plugins', $module]))   // No var

            //->addUserAction('settings', 'delete_local', 'sysinfo')    // No settings
            //->addUserAction('settings', 'delete_global', 'sysinfo')   // No settings
            //->addUserAction('settings', 'delete_all', 'sysinfo')      // No settings

            ->addUserAction('versions', 'delete', $module)

            //->addUserAction('tables', 'empty', 'dc_sysinfo')    // No table
            //->addUserAction('tables', 'delete', 'dc_sysinfo')   // No table

            ->addUserAction('plugins', 'delete', $module) // Same as Delete button

            //->addUserAction('themes', 'delete', $module) // Same as Delete button

            // Direct actions — warning: will delete without user confirmation !!!

            //->addDirectAction('plugins', 'delete', $module)
            //->addDirectAction('versions', 'delete', $module)

        ;

        return true;
    }
}
