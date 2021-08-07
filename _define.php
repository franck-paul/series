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
    '1.1',             // Version
    [
        'requires'    => [['core', '2.19']],                          // Dependencies
        'permissions' => 'usage,contentadmin',                        // Permissions
        'priority'    => 1001,                                        // Must be higher than dcLegacyEditor/dcCKEditor priority (ie 1000)
        'type'        => 'plugin',                                    // Type
        'details'     => 'https://open-time.net/docs/plugins/series', // Details URL
        'support'     => 'https://github.com/franck-paul/series',     // Support URL
        'settings'    => [
            'pref' => '#user-options.series_prefs'
        ]
    ]
);
