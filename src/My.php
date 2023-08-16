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

use dcCore;
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
     * @return     bool  true if allowed, else false, null if undefined here
     */
    protected static function checkCustomContext(int $context): ?bool
    {
        return match ($context) {
            self::BACKEND,
            self::MANAGE,
            self::MENU => defined('DC_CONTEXT_ADMIN') && dcCore::app()->auth->isSuperAdmin(),
            default    => null,
        };
    }
}
