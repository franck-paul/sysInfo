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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

/* Name */			__('sysInfo');
/* Description*/	__('System Information');

$_menu['System']->addItem(__('System info'),'plugin.php?p=sysInfo','index.php?pf=sysInfo/icon.png',
		preg_match('/plugin.php\?p=sysInfo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

$core->addBehavior('adminDashboardFavs','sysInfoDashboardFavs');

function sysInfoDashboardFavs($core,$favs)
{
	$favs['sysInfo'] = new ArrayObject(array('sysInfo','System info','plugin.php?p=sysInfo',
		'index.php?pf=sysInfo/icon.png','index.php?pf=sysInfo/icon-big.png',
		null,null,null));
}
?>