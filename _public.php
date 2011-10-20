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
		$code =
			'echo \'<h3>'.__('Behaviours list').'</h3><ul>\';'."\n".
			'$bl = $core->getBehaviours();'."\n".
			'foreach ($bl as $b => $f) {'."\n".
			'	echo \'<li>\'.$b.\' : \';'."\n".
			'	if (is_array($f)) {'."\n".
			'		echo \'<ul>\';'."\n".
			'		foreach ($f as $fi) {'."\n".
			'			echo \'<li><code>\';'."\n".
			'			if (is_array($fi)) {'."\n".
			'				echo $fi[0].\'::\'.$fi[1].\'()\';'."\n".
			'			} else {'."\n".
			'				echo $fi.\'()\';'."\n".
			'			}'."\n".
			'			echo \'</code></li>\';'."\n".
			'		}'."\n".
			'		echo \'</ul>\';'."\n".
			'	} else {'."\n".
			'		echo $f.\'()\';'."\n".
			'	}'."\n".
			'	echo \'</li>\';'."\n".
			'}'."\n".
			'echo \'</ul>\';'."\n";
		return '<?php '.$code.' ?>';
	}
}
?>