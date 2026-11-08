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
declare(strict_types=1);

if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Series',
        'Series of posts',
        'Franck Paul',
        '12.0',
        [
            'date'     => '2026-11-08T10:21:12+0100',
            'requires' => [
                ['core', '2.40'],
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
}
