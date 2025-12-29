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
use Dotclear\Core\Url;

class FrontendUrl extends Url
{
    /**
     * Output the SysInfo page
     *
     * @param      null|string  $args   The arguments
     */
    public static function sysInfo(?string $args): void
    {
        if ($args === 'behaviours') {
            App::frontend()->template()->appendPath(My::tplPath());
            self::serveDocument('behaviours.html');
            exit;
        }

        if ($args === 'templatetags') {
            App::frontend()->template()->appendPath(My::tplPath());
            self::serveDocument('templatetags.html');
            exit;
        }

        self::p404();
    }
}
