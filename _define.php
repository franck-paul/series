<?php

/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0
 */
$this->registerModule(
    'Series',
    'Series of posts',
    'Franck Paul',
    '9.5',
    [
        'date'     => '2026-04-09T14:54:21+0200',
        'requires' => [
            ['core', '2.37'],
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
