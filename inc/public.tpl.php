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
class tplSysInfo
{
    public static function SysInfoPageTitle()
    {
        return '<?php echo \'' . __('System Information') . '\'; ?>';
    }

    public static function SysInfoBehaviours()
    {
        $bl = dcCore::app()->getBehaviors('');

        $code = '<h3>' . '<?php echo \'' . __('Public behaviours list') . '\'; ?>' . ' (' . sprintf('%d', count($bl)) . ')' . '</h3>' . "\n";
        $code .= '<?php echo tplSysInfo::publicBehavioursList(); ?>';

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
                        $code .= '__Closure__';
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
        $code .= '<?php echo tplSysInfo::publicTemplatetagsList(); ?>';

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
