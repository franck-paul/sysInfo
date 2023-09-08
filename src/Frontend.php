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
use Dotclear\Core\Process;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'publicBreadcrumb' => function (?string $context) {
                if ($context == 'sysinfo') {
                    return __('System Information');
                }
            },
            'urlHandlerBeforeGetData' => function (context $ctx) {
                $ctx->http_cache = (bool) My::settings()->http_cache;
            },
        ]);

        dcCore::app()->tpl->addValue('SysInfoPageTitle', FrontendTemplate::sysInfoPageTitle(...));
        dcCore::app()->tpl->addValue('SysInfoBehaviours', FrontendTemplate::sysInfoBehaviours(...));
        dcCore::app()->tpl->addValue('SysInfoTemplatetags', FrontendTemplate::sysInfoTemplatetags(...));

        return true;
    }
}
