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

namespace Dotclear\Plugin\SysInfo;

use Clearbricks;
use dcCore;
use dcUtils;

if (!defined('DC_RC_PATH')) {
    return;
}

Clearbricks::lib()->autoload([
    \Dotclear\Plugin\SysInfo\sysInfoRest::class => dcUtils::path([__DIR__, 'inc', 'admin.rest.php']),
    \Dotclear\Plugin\SysInfo\libSysInfo::class  => dcUtils::path([__DIR__, 'inc', 'lib.sysinfo.php']),
    'Dotclear\\Plugin\\SysInfo\\urlSysInfo'  => dcUtils::path([__DIR__, 'inc', 'public.url.php']),
    \Dotclear\Plugin\SysInfo\tplSysInfo::class  => dcUtils::path([__DIR__, 'inc', 'public.tpl.php']),
]);

dcCore::app()->url->register('sysinfo', 'sysinfo', '^sysinfo(?:/(.+))?$', [urlSysInfo::class, 'sysInfo']);

if (!defined('DC_CONTEXT_ADMIN')) {
    return false;
}

// Register REST methods
dcCore::app()->rest->addFunction('getCompiledTemplate', [sysInfoRest::class, 'getCompiledTemplate']);
dcCore::app()->rest->addFunction('getStaticCacheFile', [sysInfoRest::class, 'getStaticCacheFile']);
dcCore::app()->rest->addFunction('getStaticCacheDir', [sysInfoRest::class, 'getStaticCacheDir']);
dcCore::app()->rest->addFunction('getStaticCacheList', [sysInfoRest::class, 'getStaticCacheList']);
dcCore::app()->rest->addFunction('getStaticCacheName', [sysInfoRest::class, 'getStaticCacheName']);
