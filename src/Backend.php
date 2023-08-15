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
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('sysInfo') . __('System Information');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Register menu
        My::addBackendMenuItem(Menus::MENU_SYSTEM);

        /* Register favorite */
        dcCore::app()->addBehavior('adminDashboardFavoritesV2', function (Favorites $favs) {
            $favs->register('sysInfo', [
                'title'      => My::name(),
                'url'        => My::manageUrl(),
                'small-icon' => My::icons(),
                'large-icon' => My::icons(),
            ]);
        });

        // Register REST methods
        dcCore::app()->rest->addFunction('getCompiledTemplate', [BackendRest::class, 'getCompiledTemplate']);
        dcCore::app()->rest->addFunction('getStaticCacheFile', [BackendRest::class, 'getStaticCacheFile']);
        dcCore::app()->rest->addFunction('getStaticCacheDir', [BackendRest::class, 'getStaticCacheDir']);
        dcCore::app()->rest->addFunction('getStaticCacheList', [BackendRest::class, 'getStaticCacheList']);
        dcCore::app()->rest->addFunction('getStaticCacheName', [BackendRest::class, 'getStaticCacheName']);

        return true;
    }
}
