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

use dcCore;
use dcUrlHandlers;
use Dotclear\App;
use Dotclear\Core\Frontend\Utility;

class FrontendUrl extends dcUrlHandlers
{
    /**
     * Output the SysInfo page
     *
     * @param      null|string  $args   The arguments
     */
    public static function sysInfo(?string $args): void
    {
        if ($args == 'behaviours') {
            $tplset = dcCore::app()->themes->moduleInfo(App::blog()->settings()->system->theme, 'tplset');
            if (!empty($tplset) && is_dir(__DIR__ . '/../' . Utility::TPL_ROOT . '/' . $tplset)) {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), My::path() . '/' . Utility::TPL_ROOT . '/' . $tplset);
            } else {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), My::path() . '/' . Utility::TPL_ROOT . '/' . DC_DEFAULT_TPLSET);
            }
            self::serveDocument('behaviours.html');
            exit;
        } elseif ($args == 'templatetags') {
            $tplset = dcCore::app()->themes->moduleInfo(App::blog()->settings()->system->theme, 'tplset');
            if (!empty($tplset) && is_dir(__DIR__ . '/../' . Utility::TPL_ROOT . '/' . $tplset)) {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), My::path() . '/' . Utility::TPL_ROOT . '/' . $tplset);
            } else {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), My::path() . '/' . Utility::TPL_ROOT . '/' . DC_DEFAULT_TPLSET);
            }
            self::serveDocument('templatetags.html');
            exit;
        }
        self::p404();
    }
}
