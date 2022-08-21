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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

// dead but useful code, in order to have translations
__('sysInfo') . __('System Information');

dcCore::app()->menu['System']->addItem(
    __('System info'),
    dcCore::app()->adminurl->get('admin.plugin.sysInfo'),
    [urldecode(dcPage::getPF('sysInfo/icon.svg')), urldecode(dcPage::getPF('sysInfo/icon-dark.svg'))],
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.sysInfo')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->isSuperAdmin()
);

/* Register favorite */
dcCore::app()->addBehavior('adminDashboardFavorites', ['sysInfoAdmin', 'adminDashboardFavorites']);

class sysInfoAdmin
{
    public static function adminDashboardFavorites($core, $favs)
    {
        $favs->register('sysInfo', [
            'title'       => __('System Information'),
            'url'         => dcCore::app()->adminurl->get('admin.plugin.sysInfo'),
            'small-icon'  => [urldecode(dcPage::getPF('sysInfo/icon.svg')), urldecode(dcPage::getPF('sysInfo/icon-dark.svg'))],
            'large-icon'  => [urldecode(dcPage::getPF('sysInfo/icon.svg')), urldecode(dcPage::getPF('sysInfo/icon-dark.svg'))],
            'permissions' => dcCore::app()->auth->isSuperAdmin(),
        ]);
    }
}

// Register REST methods
dcCore::app()->rest->addFunction('getCompiledTemplate', ['sysInfoRest', 'getCompiledTemplate']);
dcCore::app()->rest->addFunction('getStaticCacheFile', ['sysInfoRest', 'getStaticCacheFile']);
dcCore::app()->rest->addFunction('getStaticCacheDir', ['sysInfoRest', 'getStaticCacheDir']);
dcCore::app()->rest->addFunction('getStaticCacheList', ['sysInfoRest', 'getStaticCacheList']);
dcCore::app()->rest->addFunction('getStaticCacheName', ['sysInfoRest', 'getStaticCacheName']);
