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
    'System Information',
    'System Information',
    'Franck Paul',
    '13.1',
    [
        'date'     => '2025-07-20T18:00:31+0200',
        'requires' => [
            ['core', '2.35'],
            ['TemplateHelper'],
        ],
        'type'     => 'plugin',
        'priority' => 99_999_999_999,

        'settings' => [
            'blog' => '#params.sysinfo',
        ],

        'details'    => 'https://open-time.net/?q=sysinfo',
        'support'    => 'https://github.com/franck-paul/sysInfo',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/sysInfo/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
