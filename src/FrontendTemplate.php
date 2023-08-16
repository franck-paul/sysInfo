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
use ReflectionFunction;

class FrontendTemplate
{
    public static function SysInfoPageTitle()
    {
        $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
        if (empty($tplset)) {
            $tplset = DC_DEFAULT_TPLSET . '-default';
        }

        return '<?php echo \'<span class="dc-tpl-' . $tplset . '">' . __('System Information') . '</span>\'; ?>';
    }

    public static function SysInfoBehaviours()
    {
        $bl = dcCore::app()->getBehaviors('');

        $code = '<h3>' . '<?php echo \'' . __('Public behaviours list') . '\'; ?>' . ' (' . sprintf('%d', is_countable($bl) ? count($bl) : 0) . ')' . '</h3>' . "\n";
        $code .= '<?php echo ' . self::class . '::publicBehavioursList(); ?>';

        return $code;
    }

    public static function publicBehavioursList()
    {
        $code = '<ul>' . "\n";

        $bl = dcCore::app()->getBehaviors('');
        foreach ($bl as $b => $f) {
            $code .= '<li>' . $b . ' : ';
            if (is_array($f)) {
                $code .= "\n" . '<ul>';
                foreach ($f as $fi) {
                    $code .= '<li><code>';
                    if (is_array($fi)) {
                        if (is_object($fi[0])) {
                            $code .= get_class($fi[0]) . '-&gt;' . $fi[1] . '()';
                        } else {
                            $code .= $fi[0] . '::' . $fi[1] . '()';
                        }
                    } elseif ($fi instanceof \Closure) {
                        $r  = new ReflectionFunction($fi);
                        $ns = $r->getNamespaceName() ? $r->getNamespaceName() . '::' : '';
                        $fn = $r->getShortName() ? $r->getShortName() : '__closure__';
                        if ($ns === '') {
                            // Cope with class::method(...) forms
                            $c  = $r->getClosureScopeClass();
                            $ns = $c->getNamespaceName() ? $c->getNamespaceName() . '::' : '';
                        }
                        $code .= $ns . $fn;
                    } else {
                        $code .= $fi . '()';
                    }
                    $code .= '</code></li>';
                }
                $code .= '</ul>' . "\n";
            } else {
                $code .= $f . '()';
            }
            $code .= '</li>' . "\n";
        }
        $code .= '</ul>' . "\n";

        return $code;
    }

    public static function SysInfoTemplatetags()
    {
        $code = '<h3>' . '<?php echo \'' . __('Template tags list') . '\'; ?>' . '</h3>' . "\n";
        $code .= '<?php echo ' . self::class . '::publicTemplatetagsList(); ?>';

        return $code;
    }

    public static function publicTemplatetagsList()
    {
        $code = '<ul>' . "\n";

        $tplblocks = array_values(dcCore::app()->tpl->getBlockslist());
        $tplvalues = array_values(dcCore::app()->tpl->getValueslist());

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
