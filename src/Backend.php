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

use Dotclear\App;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('sysInfo');
        __('System Information');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Register menu
        My::addBackendMenuItem(App::backend()->menus()::MENU_SYSTEM);

        /* Register favorite */
        App::behavior()->addBehavior('adminDashboardFavoritesV2', static function (Favorites $favs): string {
            $favs->register('sysInfo', [
                'title'      => My::name(),
                'url'        => My::manageUrl(),
                'small-icon' => My::icons(),
                'large-icon' => My::icons(),
            ]);

            return '';
        });

        App::behavior()->addBehaviors([
            'dcMaintenanceInit'             => BackendBehaviors::dcMaintenanceInit(...),
            'adminBlogPreferencesFormV2'    => BackendBehaviors::adminBlogPreferencesForm(...),
            'adminBeforeBlogSettingsUpdate' => BackendBehaviors::adminBeforeBlogSettingsUpdate(...),
        ]);

        // Register REST methods
        App::rest()->addFunction('getCompiledTemplate', BackendRest::getCompiledTemplate(...));
        App::rest()->addFunction('getStaticCacheFile', BackendRest::getStaticCacheFile(...));
        App::rest()->addFunction('getStaticCacheDir', BackendRest::getStaticCacheDir(...));
        App::rest()->addFunction('getStaticCacheList', BackendRest::getStaticCacheList(...));
        App::rest()->addFunction('getStaticCacheName', BackendRest::getStaticCacheName(...));
        App::rest()->addFunction('getAtticVersions', BackendRest::getAtticVersions(...));

        return true;
    }
}
