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

$new_version = $core->plugins->moduleInfo('sysInfo', 'version');
$old_version = $core->getVersion('sysInfo');

if (version_compare($old_version, $new_version, '>=')) {
    return;
}

try {
    $core->blog->settings->addNamespace('sysinfo');
    $core->blog->settings->sysinfo->put('http_cache', true, 'boolean', 'HTTP cache', false, true);

    $core->setVersion('sysInfo', $new_version);

    return true;
} catch (Exception $e) {
    $core->error->add($e->getMessage());
}

return false;
