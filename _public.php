<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2011 Franck Paul
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------

$core->addBehavior('publicBreadcrumb',array('extSysInfo','publicBreadcrumb'));
$core->addBehavior('urlHandlerBeforeGetData',array('extSysInfo','urlHandlerBeforeGetData'));

$core->tpl->addValue('SysInfoPageTitle',array('tplSysInfo','SysInfoPageTitle'));
$core->tpl->addValue('SysInfoBehaviours',array('tplSysInfo','SysInfoBehaviours'));

class extSysInfo
{
	public static function publicBreadcrumb($context,$separator)
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

			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
		return '<?php echo \''.__('System Information').'\'; ?>';
	}
	
	public static function SysInfoBehaviours($attr)
	{
		global $core;
		
		$code = '<h3>'.'<?php echo __(\'Public behaviours list\'); ?>'.'</h3>'."\n";
		$code .= '<ul>'."\n";
		
		$bl = $core->getBehaviors('');
		foreach ($bl as $b => $f) {
			$code .= '<li>'.$b.' : ';
			if (is_array($f)) {
				$code .= "\n".'<ul>';
				foreach ($f as $fi) {
					$code .= '<li><code>';
					if (is_array($fi)) {
						$code .= $fi[0].'::'.$fi[1].'()';
					} else {
						$code .= $fi.'()';
					}
					$code .= '</code></li>';
				}
				$code .= '</ul>'."\n";
			} else {
				$code .= $f.'()';
			}
			$code .= '</li>'."\n";
		}
		$code .= '</ul>'."\n";

		return $code;
	}
}
?>