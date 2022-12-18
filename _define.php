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
    'Series',
    'Series of posts',
    'Franck Paul',
    '2.1',
    [
        'requires'    => [['core', '2.24']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
        'priority' => 1001,                      // Must be higher than dcLegacyEditor/dcCKEditor priority (ie 1000)
        'type'     => 'plugin',
        'settings' => [
            'pref' => '#user-options.series_prefs',
        ],

        'details'    => 'https://open-time.net/?q=series',
        'support'    => 'https://github.com/franck-paul/series',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/series/master/dcstore.xml',
    ]
);
