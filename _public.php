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

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('publicBreadcrumb', ['extSysInfo', 'publicBreadcrumb']);
$core->addBehavior('urlHandlerBeforeGetData', ['extSysInfo', 'urlHandlerBeforeGetData']);

$core->tpl->addValue('SysInfoPageTitle', ['tplSysInfo', 'SysInfoPageTitle']);
$core->tpl->addValue('SysInfoBehaviours', ['tplSysInfo', 'SysInfoBehaviours']);

class extSysInfo
{
    public static function publicBreadcrumb($context, $separator)
    {
        if ($context == 'sysinfo') {
            return __('System Information');
        }
    }

    public static function urlHandlerBeforeGetData($ctx)
    {
        global $core;

        $core->blog->settings->addNamespace('sysinfo');
        $ctx->http_cache = (boolean) $core->blog->settings->sysinfo->http_cache;
    }
}

class urlSysInfo extends dcUrlHandlers
{
    public static function sysInfo($args)
    {
        global $core, $_ctx;

        if ($args == 'behaviours') {

            $tplset = $core->themes->moduleInfo($core->blog->settings->system->theme, 'tplset');
            if (!empty($tplset) && is_dir(dirname(__FILE__) . '/default-templates/' . $tplset)) {
                $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__) . '/default-templates/' . $tplset);
            } else {
                $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__) . '/default-templates/' . DC_DEFAULT_TPLSET);
            }
            self::serveDocument('behaviours.html');
            exit;

        } else {
            self::p404();
            exit;
        }
    }
}

class tplSysInfo
{
    public static function SysInfoPageTitle($attr)
    {
        return '<?php echo \'' . __('System Information') . '\'; ?>';
    }

    public static function SysInfoBehaviours($attr)
    {
        global $core;

        $code = '<h3>' . '<?php echo __(\'Public behaviours list\'); ?>' . '</h3>' . "\n";
        $code .= '<?php echo tplSysInfo::publicBehavioursList(); ?>';

        return $code;
    }

    public static function publicBehavioursList()
    {
        $code = '<ul>' . "\n";

        $bl = $GLOBALS['core']->getBehaviors('');
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
}
