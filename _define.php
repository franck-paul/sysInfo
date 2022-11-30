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
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'sysInfo',
    'System Information',
    'Franck Paul',
    '2.0',
    [
        'requires' => [['core', '2.24']],
        'type'     => 'plugin',
        'priority' => 99999999999,

        'details'    => 'https://open-time.net/docs/plugins/sysInfo',
        'support'    => 'https://github.com/franck-paul/sysInfo',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/sysInfo/main/dcstore.xml',
    ]
);
