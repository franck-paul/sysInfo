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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

try {
    dcCore::app()->blog->settings->addNamespace('sysinfo');
    dcCore::app()->blog->settings->sysinfo->put('http_cache', true, 'boolean', 'HTTP cache', false, true);

    // Cleanup
    $old_version = dcCore::app()->getVersion(basename(__DIR__));

    if (version_compare((string) $old_version, '2.2', '<')) {
        // Remove moved css/js
        @unlink(dcUtils::path([__DIR__, 'sysinfo.js']));
        @unlink(dcUtils::path([__DIR__, 'sysInfo.css']));
    }

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
