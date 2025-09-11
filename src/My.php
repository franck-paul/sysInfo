<?php

/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo;

use Dotclear\App;
use Dotclear\Core\Frontend\Utility;
use Dotclear\Module\MyPlugin;

/**
 * Plugin definitions
 */
class My extends MyPlugin
{
    /**
     * Check permission depending on given context
     *
     * @param      int   $context  The context
     *
     * @return     bool  true if allowed, else false
     */
    public static function checkCustomContext(int $context): ?bool
    {
        return match ($context) {
            // Limit to super admin
            self::MODULE => App::auth()->isSuperAdmin(),
            default      => null,
        };
    }

    /**
     * Return template path to use
     */
    public static function tplPath(): string
    {
        $tplset = App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'tplset');
        if (!empty($tplset) && is_dir(implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT, $tplset]))) {
            // a sub-dir exists for my plugin with this tplset
            return implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT, $tplset]);
        }

        $tplset = App::config()->defaultTplset();
        if (!empty($tplset) && is_dir(implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT, $tplset]))) {
            // a sub-dir exists for my plugin with the default tplset
            return implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT, $tplset]);
        }

        // return base tpl dir
        return implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT]);
    }
}
