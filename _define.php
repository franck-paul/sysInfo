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

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"sysInfo",
	/* Description*/		"System Information",
	/* Author */			"Franck Paul",
	/* Version */			'1.2',
	array(
		/* Type */			'type' =>			'plugin',
		/* Priority */		'priority' =>		99999999999
	)
);
