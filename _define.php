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
$this->registerModule(
    'sysInfo',
    'System Information',
    'Franck Paul',
    '5.1',
    [
        'requires' => [
            ['core', '2.27'],
            ['php', '8.1'],
            ['staticCache?', '2.3'],
        ],
        'type'     => 'plugin',
        'priority' => 99_999_999_999,

        'details'    => 'https://open-time.net/?q=sysinfo',
        'support'    => 'https://github.com/franck-paul/sysInfo',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/sysInfo/main/dcstore.xml',
    ]
);
