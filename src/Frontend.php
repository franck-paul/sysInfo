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

use context;
use dcCore;
use dcNsProcess;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        self::$init = defined('DC_RC_PATH');

        return self::$init;
    }

    public static function process(): bool
    {
        if (!self::$init) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'publicBreadcrumb'        => function (?string $context) {
                if ($context == 'sysinfo') {
                    return __('System Information');
                }
            },
            'urlHandlerBeforeGetData' => function (context $ctx) {
                $ctx->http_cache = (bool) dcCore::app()->blog->settings->sysinfo->http_cache;
            },
        ]);

        dcCore::app()->tpl->addValue('SysInfoPageTitle', [FrontendTemplate::class, 'SysInfoPageTitle']);
        dcCore::app()->tpl->addValue('SysInfoBehaviours', [FrontendTemplate::class, 'SysInfoBehaviours']);
        dcCore::app()->tpl->addValue('SysInfoTemplatetags', [FrontendTemplate::class, 'SysInfoTemplatetags']);

        return true;
    }
}
