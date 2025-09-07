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

use Dotclear\App;
use Dotclear\Core\Frontend\Ctx;
use Dotclear\Helper\Process\TraitProcess;

class Frontend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehaviors([
            'publicBreadcrumb' => static function (?string $context): string {
                if ($context == 'sysinfo') {
                    return __('System Information');
                }

                return '';
            },
            'urlHandlerBeforeGetData' => static function (Ctx $ctx): string {
                $ctx->http_cache = (bool) My::settings()->http_cache;

                return '';
            },
        ]);

        App::frontend()->template()->addValue('SysInfoPageTitle', FrontendTemplate::sysInfoPageTitle(...));
        App::frontend()->template()->addValue('SysInfoBehaviours', FrontendTemplate::sysInfoBehaviours(...));
        App::frontend()->template()->addValue('SysInfoTemplatetags', FrontendTemplate::sysInfoTemplatetags(...));

        App::behavior()->addBehaviors([
            'publicHeadContent'     => FrontendBehaviors::publicHeadContent(...),
            'publicAfterDocumentV2' => FrontendBehaviors::publicAfterDocument(...),
        ]);

        return true;
    }
}
