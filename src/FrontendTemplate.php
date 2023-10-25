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

        return '<?php echo \'<span class="dc-tpl-' . $tplset . '">' . __('System Information') . '</span>\'; ?>';
    }

    public static function sysInfoBehaviours(): string
    {
        $bl = App::behavior()->getBehaviors();

        $code = '<h3>' . '<?php echo \'' . __('Public behaviours list') . '\'; ?>' . ' (' . sprintf('%d', count($bl)) . ')' . '</h3>' . "\n";
        $code .= '<?php echo ' . self::class . '::publicBehavioursList(); ?>';

        return $code;
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
        $code .= '</ul>' . "\n";

        return $code;
    }

    public static function sysInfoTemplatetags(): string
    {
        $code = '<h3>' . '<?php echo \'' . __('Template tags list') . '\'; ?>' . '</h3>' . "\n";
        $code .= '<?php echo ' . self::class . '::publicTemplatetagsList(); ?>';

        return $code;
    }

    public static function publicTemplatetagsList(): string
    {
        $code = '<ul>' . "\n";

        $tplblocks = array_values(App::frontend()->template()->getBlockslist());
        $tplvalues = array_values(App::frontend()->template()->getValueslist());

        sort($tplblocks, SORT_STRING);
        sort($tplvalues, SORT_STRING);

        $code .= '<li>' . __('Blocks') . '<ul>' . "\n";
        foreach ($tplblocks as $elt) {
            $code .= '<li>' . $elt . '</li>' . "\n";
        }
        $code .= '</ul></li>' . "\n";

        $code .= '<li>' . __('Values') . '<ul>' . "\n";
        foreach ($tplvalues as $elt) {
            $code .= '<li>' . $elt . '</li>' . "\n";
        }
        $code .= '</ul></li>' . "\n";

        $code .= '</ul>' . "\n";

        return $code;
    }
}
