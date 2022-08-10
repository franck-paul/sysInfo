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

dcCore::app()->url->register('sysinfo', 'sysinfo', '^sysinfo(?:/(.+))?$', ['urlSysInfo', 'sysInfo']);

if (!defined('DC_CONTEXT_ADMIN')) {
    return false;
}

// Admin mode only

$__autoload['sysInfoRest'] = __DIR__ . '/_services.php';
$__autoload['libSysInfo']  = __DIR__ . '/inc/lib.sysinfo.php';
