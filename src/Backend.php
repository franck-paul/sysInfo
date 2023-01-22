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
use dcPage;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        self::$init = defined('DC_CONTEXT_ADMIN');

        // dead but useful code, in order to have translations
        __('sysInfo') . __('System Information');

        return self::$init;
    }

    public static function process(): bool
    {
        if (!self::$init) {
            return false;
        }

        dcCore::app()->menu[dcAdmin::MENU_SYSTEM]->addItem(
            __('System info'),
            dcCore::app()->adminurl->get('admin.plugin.sysInfo'),
            [urldecode(dcPage::getPF('sysInfo/icon.svg')), urldecode(dcPage::getPF('sysInfo/icon-dark.svg'))],
            preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.sysInfo')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
            dcCore::app()->auth->isSuperAdmin()
        );

        /* Register favorite */
        dcCore::app()->addBehavior('adminDashboardFavoritesV2', function (dcFavorites $favs) {
            $favs->register('sysInfo', [
                'title'      => __('System Information'),
                'url'        => dcCore::app()->adminurl->get('admin.plugin.sysInfo'),
                'small-icon' => [urldecode(dcPage::getPF('sysInfo/icon.svg')), urldecode(dcPage::getPF('sysInfo/icon-dark.svg'))],
                'large-icon' => [urldecode(dcPage::getPF('sysInfo/icon.svg')), urldecode(dcPage::getPF('sysInfo/icon-dark.svg'))],
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
