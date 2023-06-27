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
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::FRONTEND);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'publicBreadcrumb' => function (?string $context) {
                if ($context == 'sysinfo') {
                    return __('System Information');
                }
            },
            'urlHandlerBeforeGetData' => function (context $ctx) {
                $ctx->http_cache = (bool) dcCore::app()->blog->settings->get(My::id())->http_cache;
            },
        ]);

        dcCore::app()->tpl->addValue('SysInfoPageTitle', [FrontendTemplate::class, 'SysInfoPageTitle']);
        dcCore::app()->tpl->addValue('SysInfoBehaviours', [FrontendTemplate::class, 'SysInfoBehaviours']);
        dcCore::app()->tpl->addValue('SysInfoTemplatetags', [FrontendTemplate::class, 'SysInfoTemplatetags']);

        return true;
    }
}
