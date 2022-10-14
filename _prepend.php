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
if (!defined('DC_RC_PATH')) {
    return;
}

Clearbricks::lib()->autoload([
    'sysInfoRest' => __DIR__ . '/_services.php',
    'libSysInfo'  => __DIR__ . '/inc/lib.sysinfo.php',
    'urlSysInfo'  => __DIR__ . '/inc/public.url.php',
    'tplSysInfo'  => __DIR__ . '/inc/public.tpl.php',
]);

dcCore::app()->url->register('sysinfo', 'sysinfo', '^sysinfo(?:/(.+))?$', [urlSysInfo::class, 'sysInfo']);
