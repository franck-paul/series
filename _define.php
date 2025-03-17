<?php

/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0
 */
$this->registerModule(
    'Series',
    'Series of posts',
    'Franck Paul',
    '7.0',
    [
        'date'     => '2025-03-17T19:19:26+0100',
        'requires' => [
            ['core', '2.34'],
            ['TemplateHelper'],
        ],
        'permissions' => 'My',
        'priority'    => 1010,  // Must be higher than dcLegacyEditor/dcCKEditor priority (ie 1000)
        'type'        => 'plugin',
        'settings'    => [
            'pref' => '#user-options.series_prefs',
        ],

        'details'    => 'https://open-time.net/?q=series',
        'support'    => 'https://github.com/franck-paul/series',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/series/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
