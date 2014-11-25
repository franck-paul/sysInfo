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

// Register admin URL base of plugin
$core->adminurl->registercopy('admin.plugin.sysinfo','admin.plugin',array('p' => 'sysInfo'));

$_menu['System']->addItem(__('System info'),
		$core->adminurl->get('admin.plugin.sysinfo'),
		$core->adminurl->get('load.plugin.file',array('pf' => 'sysInfo/icon.png')),
		preg_match('/'.preg_quote($core->adminurl->get('admin.plugin.sysinfo')).'(&.*)/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

/* Register favorite */
$core->addBehavior('adminDashboardFavorites',array('sysInfoAdmin','adminDashboardFavorites'));

class sysInfoAdmin
{
	public static function adminDashboardFavorites($core,$favs)
	{
		$favs->register('sysInfo', array(
			'title' => __('System Information'),
			'url' => $core->adminurl->get('admin.plugin.sysinfo'),
			'small-icon' => $core->adminurl->get('load.plugin.file',array('pf' => 'sysInfo/icon.png')),
			'large-icon' => $core->adminurl->get('load.plugin.file',array('pf' => 'sysInfo/icon-big.png')),
			'permissions' => $core->auth->isSuperAdmin()
		));
	}
}
