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

use context;
use dcCore;

if (!defined('DC_RC_PATH')) {
    return;
}

dcCore::app()->addBehavior('publicBreadcrumb', function (?string $context) {
    if ($context == 'sysinfo') {
        return __('System Information');
    }
});
dcCore::app()->addBehavior('urlHandlerBeforeGetData', function (context $ctx) {
    dcCore::app()->blog->settings->addNamespace('sysinfo');
    $ctx->http_cache = (bool) dcCore::app()->blog->settings->sysinfo->http_cache;
});

dcCore::app()->tpl->addValue('SysInfoPageTitle', [tplSysInfo::class, 'SysInfoPageTitle']);
dcCore::app()->tpl->addValue('SysInfoBehaviours', [tplSysInfo::class, 'SysInfoBehaviours']);
dcCore::app()->tpl->addValue('SysInfoTemplatetags', [tplSysInfo::class, 'SysInfoTemplatetags']);
