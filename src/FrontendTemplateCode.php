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

class FrontendTemplateCode
{
    /**
     * PHP code for tpl:SysInfoBehaviours value
     */
    public static function sysInfoPageTitle(
        string $_tplset_,
    ): void {
        echo trim((string) (new \Dotclear\Helper\Html\Form\Text('span', __('System Information')))
            ->class('dc-tpl-' . $_tplset_)
        ->render());
    }

    /**
     * PHP code for tpl:SysInfoBehaviours value
     */
    public static function sysInfoBehaviours(
    ): void {
        echo (new \Dotclear\Helper\Html\Form\Set())
            ->items([
                (new \Dotclear\Helper\Html\Form\Text('h3', \Dotclear\Plugin\sysInfo\FrontendTemplate::publicBehavioursTitle())),
                (new \Dotclear\Helper\Html\Form\Text(null, \Dotclear\Plugin\sysInfo\FrontendTemplate::publicBehavioursList())),
            ])
        ->render();
    }

    /**
     * PHP code for tpl:sysInfoTemplatetags value
     */
    public static function sysInfoTemplatetags(
    ): void {
        echo (new \Dotclear\Helper\Html\Form\Set())
            ->items([
                (new \Dotclear\Helper\Html\Form\Text('h3', \Dotclear\Plugin\sysInfo\FrontendTemplate::publicTemplatetagsTitle())),
                (new \Dotclear\Helper\Html\Form\Text(null, \Dotclear\Plugin\sysInfo\FrontendTemplate::publicTemplatetagsList())),
            ])
        ->render();
    }
}
