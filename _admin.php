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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

// dead but useful code, in order to have translations
__('sysInfo') . __('System Information');

$_menu['System']->addItem(__('System info'),
    $core->adminurl->get('admin.plugin.sysInfo'),
    urldecode(dcPage::getPF('sysInfo/icon.png')),
    preg_match('/' . preg_quote($core->adminurl->get('admin.plugin.sysInfo')) . '(&.*)/', $_SERVER['REQUEST_URI']),
    $core->auth->isSuperAdmin());

/* Register favorite */
$core->addBehavior('adminDashboardFavorites', array('sysInfoAdmin', 'adminDashboardFavorites'));

class sysInfoAdmin
{
    public static function adminDashboardFavorites($core, $favs)
    {
        $favs->register('sysInfo', array(
            'title'       => __('System Information'),
            'url'         => $core->adminurl->get('admin.plugin.sysInfo'),
            'small-icon'  => urldecode(dcPage::getPF('sysInfo/icon.png')),
            'large-icon'  => urldecode(dcPage::getPF('sysInfo/icon-big.png')),
            'permissions' => $core->auth->isSuperAdmin()
        ));
    }
}

// Register REST methods
$core->rest->addFunction('getCompiledTemplate', array('sysInfoRest', 'getCompiledTemplate'));
$core->rest->addFunction('getStaticCacheFile', array('sysInfoRest', 'getStaticCacheFile'));
$core->rest->addFunction('getStaticCacheDir', array('sysInfoRest', 'getStaticCacheDir'));
$core->rest->addFunction('getStaticCacheList', array('sysInfoRest', 'getStaticCacheList'));
$core->rest->addFunction('getStaticCacheName', array('sysInfoRest', 'getStaticCacheName'));
