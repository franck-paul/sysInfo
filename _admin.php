<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of sysInfo, a plugin for Dotclear 2.
#
# Copyright (c) Franck Paul and contributors
# carnet.franck.paul@gmail.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

// dead but useful code, in order to have translations
__('sysInfo').__('System Information');

$_menu['System']->addItem(__('System info'),'plugin.php?p=sysInfo','index.php?pf=sysInfo/icon.png',
		preg_match('/plugin.php\?p=sysInfo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

/* Register favorite */
$core->addBehavior('adminDashboardFavorites',array('sysInfoAdmin','adminDashboardFavorites'));

class sysInfoAdmin
{
	public static function adminDashboardFavorites($core,$favs)
	{
		$favs->register('sysInfo', array(
			'title' => __('System Information'),
			'url' => 'plugin.php?p=sysInfo',
			'small-icon' => 'index.php?pf=sysInfo/icon.png',
			'large-icon' => 'index.php?pf=sysInfo/icon-big.png',
			'permissions' => $core->auth->isSuperAdmin()
		));
	}
}
