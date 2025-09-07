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

use Dotclear\Helper\Process\TraitProcess;

class Install
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        $settings = My::settings();
        $settings->put('http_cache', true, 'boolean', 'HTTP cache', false, true);
        $settings->put('redact', '', 'string', '', false, true);
        $settings->put('public_debug', false, 'boolean', 'Display debug information on each public page', false, true);
        $settings->put('public_debug_adminonly', true, 'boolean', 'Display debug information but only if an administrator is connected', false, true);

        return true;
    }
}
