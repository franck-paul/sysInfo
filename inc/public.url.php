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

use dcCore;
use dcPublic;
use dcUrlHandlers;

class urlSysInfo extends dcUrlHandlers
{
    /**
     * Output the SysInfo page
     *
     * @param      null|string  $args   The arguments
     */
    public static function sysInfo(?string $args)
    {
        if ($args == 'behaviours') {
            $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
            if (!empty($tplset) && is_dir(__DIR__ . '/../' . dcPublic::TPL_ROOT . '/' . $tplset)) {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/..' . '/' . dcPublic::TPL_ROOT . '/' . $tplset);
            } else {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/..' . '/' . dcPublic::TPL_ROOT . '/' . DC_DEFAULT_TPLSET);
            }
            self::serveDocument('behaviours.html');
            exit;
        } elseif ($args == 'templatetags') {
            $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
            if (!empty($tplset) && is_dir(__DIR__ . '/../' . dcPublic::TPL_ROOT . '/' . $tplset)) {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/..' . '/' . dcPublic::TPL_ROOT . '/' . $tplset);
            } else {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/..' . '/' . dcPublic::TPL_ROOT . '/' . DC_DEFAULT_TPLSET);
            }
            self::serveDocument('templatetags.html');
            exit;
        }
        self::p404();
    }
}
