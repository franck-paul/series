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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

// dead but useful code, in order to have translations
__('Series') . __('Series of posts');

dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
    __('Series'),
    'plugin.php?p=series&amp;m=series',
    urldecode(dcPage::getPF('series/icon.svg')),
    preg_match('/plugin.php\?p=series&m=serie(s|_posts)?(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
        dcAuth::PERMISSION_USAGE,
        dcAuth::PERMISSION_CONTENT_ADMIN,
    ]), dcCore::app()->blog->id)
);

require_once __DIR__ . '/_widgets.php';

dcCore::app()->addBehaviors([
    'adminPostFormItems'           => [seriesBehaviors::class, 'seriesField'],

    'adminAfterPostCreate'         => [seriesBehaviors::class, 'setSeries'],
    'adminAfterPostUpdate'         => [seriesBehaviors::class, 'setSeries'],

    'adminPostHeaders'             => [seriesBehaviors::class, 'postHeaders'],

    'adminPostsActions'            => [seriesBehaviors::class, 'adminPostsActions'],

    'adminPreferencesFormV2'       => [seriesBehaviors::class, 'adminPreferencesForm'],
    'adminBeforeUserOptionsUpdate' => [seriesBehaviors::class, 'setSerieListFormat'],

    'adminUserForm'                => [seriesBehaviors::class, 'adminUserForm'],
    'adminBeforeUserCreate'        => [seriesBehaviors::class, 'setSerieListFormat'],
    'adminBeforeUserUpdate'        => [seriesBehaviors::class, 'setSerieListFormat'],

    'adminPostEditor'              => [seriesBehaviors::class, 'adminPostEditor'],
    'ckeditorExtraPlugins'         => [seriesBehaviors::class, 'ckeditorExtraPlugins'],

    // Register favorite
    'adminDashboardFavoritesV2'    => [seriesBehaviors::class, 'adminDashboardFavorites'],

    'adminSimpleMenuAddType'       => [seriesBehaviors::class, 'adminSimpleMenuAddType'],
    'adminSimpleMenuSelect'        => [seriesBehaviors::class, 'adminSimpleMenuSelect'],
    'adminSimpleMenuBeforeEdit'    => [seriesBehaviors::class, 'adminSimpleMenuBeforeEdit'],
]);
