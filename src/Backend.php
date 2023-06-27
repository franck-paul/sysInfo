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

use dcAdmin;
use dcCore;
use dcFavorites;
use dcNsProcess;

class Backend extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::BACKEND);

        // dead but useful code, in order to have translations
        __('sysInfo') . __('System Information');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->menu[dcAdmin::MENU_SYSTEM]->addItem(
            __('System info'),
            My::makeUrl(),
            My::icons(),
            preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
            My::checkContext(My::MENU)
        );

        /* Register favorite */
        dcCore::app()->addBehavior('adminDashboardFavoritesV2', function (dcFavorites $favs) {
            $favs->register('sysInfo', [
                'title'      => __('System Information'),
                'url'        => My::makeUrl(),
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
