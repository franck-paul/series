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

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    "Series",          // Name
    "Series of posts", // Description
    "Franck Paul",     // Author
    '1.0',             // Version
    [
        'requires'    => [['core', '2.16']],   // Dependencies
        'permissions' => 'usage,contentadmin', // Permissions
        'priority'    => 1001,                 // Must be higher than dcLegacyEditor/dcCKEditor priority (ie 1000) // Priority
        'type'        => 'plugin',             // Type
        'settings'    => [
            'pref' => '#user-options.series_prefs'
        ]
    ]
);
