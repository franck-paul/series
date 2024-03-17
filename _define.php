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
    '5.7',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'priority'    => 1001,                      // Must be higher than dcLegacyEditor/dcCKEditor priority (ie 1000)
        'type'        => 'plugin',
        'settings'    => [
            'pref' => '#user-options.series_prefs',
        ],

        'details'    => 'https://open-time.net/?q=series',
        'support'    => 'https://github.com/franck-paul/series',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/series/master/dcstore.xml',
    ]
);
