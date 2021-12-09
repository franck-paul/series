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
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Series',          // Name
    'Series of posts', // Description
    'Franck Paul',     // Author
    '1.2',             // Version
    [
        'requires'    => [['core', '2.20']],        // Dependencies
        'permissions' => 'usage,contentadmin',      // Permissions
        'priority'    => 1001,                      // Must be higher than dcLegacyEditor/dcCKEditor priority (ie 1000)
        'type'        => 'plugin',                  // Type
        'settings'    => [
            'pref' => '#user-options.series_prefs',
        ],

        'details'    => 'https://open-time.net/docs/plugins/series',       // Details URL
        'support'    => 'https://github.com/franck-paul/series',            // Support URL
        'repository' => 'https://raw.githubusercontent.com/franck-paul/series/master/dcstore.xml',
    ]
);
