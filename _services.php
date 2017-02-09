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

class sysInfoRest
{
	public static function getCompiledTemplate($core,$get) {
		// Return compiled template file content
		$file = !empty($get['file']) ? $get['file'] : '';
		$rsp = new xmlTag('sysinfo');
		$ret = false;
		$content = '';

		if ($file != '') {
			// Load content of compiled template file (if exist and if is readable)
			$subpath = sprintf('%s/%s',substr($file,0,2),substr($file,2,2));
			$fullpath = path::real(DC_TPL_CACHE).'/cbtpl/'.$subpath.'/'.$file;
			if (file_exists($fullpath) && is_readable($fullpath)) {
				$content = file_get_contents($fullpath);
				$ret = true;
			}
		}

		$rsp->ret = $ret;
		// Escape file content (in order to avoid further parsing error)
		// JSON encode to preserve UTF-8 encoding
		// Base 64 encoding to preserve line breaks
		$rsp->msg = base64_encode(json_encode(html::escapeHTML($content)));

		return $rsp;
	}

	public static function getStaticCacheFile($core,$get) {
		// Return compiled static cache file content
		$file = !empty($get['file']) ? $get['file'] : '';
		$rsp = new xmlTag('sysinfo');
		$ret = false;
		$content = '';

		if ($file != '') {
			if (file_exists($file) && is_readable($file)) {
				$content = file_get_contents($file);
				$ret = true;
			}
		}

		$rsp->ret = $ret;
		// Escape file content (in order to avoid further parsing error)
		// JSON encode to preserve UTF-8 encoding
		// Base 64 encoding to preserve line breaks
		$rsp->msg = base64_encode(json_encode(html::escapeHTML($content)));

		return $rsp;
	}
}
