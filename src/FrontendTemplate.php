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

class FrontendTemplate
{
    public static function sysInfoPageTitle(): string
    {
        $tplset = App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'tplset');
        if (empty($tplset)) {
            $tplset = App::config()->defaultTplset() . '-default';
        }

        return '<?= \'<span class="dc-tpl-' . $tplset . '">' . __('System Information') . '</span>\' ?>';
    }

    public static function sysInfoBehaviours(): string
    {
        $bl = App::behavior()->getBehaviors();

        $code = '<h3><?= \'' . __('Public behaviours list') . '\' ?>' . ' (' . sprintf('%d', count($bl)) . ')' . '</h3>' . "\n";

        return $code . ('<?= ' . self::class . '::publicBehavioursList() ?>');
    }

    public static function publicBehavioursList(): string
    {
        $code = '<ul>' . "\n";

        $bl = App::behavior()->getBehaviors();
        foreach ($bl as $b => $f) {
            $code .= '<li>' . $b . ' : ' . "\n" . '<ul>';
            // List of behavior's callback(s)
            foreach ($f as $fi) {
                $code .= '<li><code>' . CoreHelper::callableName($fi) . '</code></li>';
            }

            $code .= '</ul>' . "\n" . '</li>' . "\n";
        }

        return $code . ('</ul>' . "\n");
    }

    public static function sysInfoTemplatetags(): string
    {
        $code = '<h3><?= \'' . __('Template tags list') . '\' ?>' . '</h3>' . "\n";

        return $code . ('<?= ' . self::class . '::publicTemplatetagsList() ?>');
    }

    public static function publicTemplatetagsList(): string
    {
        $code = '<div class="sysinfo"><ul>' . "\n";

        $tplblocks = array_values(App::frontend()->template()->getBlockslist());
        $tplvalues = array_values(App::frontend()->template()->getValueslist());

        sort($tplblocks, SORT_STRING);
        sort($tplvalues, SORT_STRING);

        $code .= '<li>' . __('Blocks') . ' (' . count($tplblocks) . ')' . '<ul>' . "\n";
        foreach ($tplblocks as $elt) {
            $callback = App::frontend()->template()->getBlockCallback($elt);
            $code .= '<li>' . $elt . ' - <code>' . CoreHelper::callableName($callback) . '</code></li>' . "\n";
        }

        $code .= '</ul></li>' . "\n";

        $code .= '<li>' . __('Values') . ' (' . count($tplvalues) . ')' . '<ul>' . "\n";
        foreach ($tplvalues as $elt) {
            $callback = App::frontend()->template()->getValueCallback($elt) ;
            $code .= '<li>' . $elt . ' - <code>' . CoreHelper::callableName($callback) . '</code></li>' . "\n";
        }

        $code .= '</ul></li>' . "\n";

        return $code . ('</ul></div>' . "\n");
    }
}
