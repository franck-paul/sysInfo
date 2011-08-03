<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2011 Olivier Meunier and dcTeam
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['System']->addItem(__('sysInfo'),'plugin.php?p=sysinfo','index.php?pf=sysinfo/icon.png',
		preg_match('/plugin.php\?p=sysinfo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

$core->addBehavior('adminDashboardFavs','sysInfoDashboardFavs');

function sysInfoDashboardFavs($core,$favs)
{
	$favs['sysinfo'] = new ArrayObject(array('sysinfo','sysInfo','plugin.php?p=sysinfo',
		'index.php?pf=sysinfo/icon.png','index.php?pf=sysinfo/icon-big.png',
		null,null,null));
}
?>