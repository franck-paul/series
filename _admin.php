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

dcCore::app()->addBehavior('adminPostFormItems', [seriesBehaviors::class, 'seriesField']);

dcCore::app()->addBehavior('adminAfterPostCreate', [seriesBehaviors::class, 'setSeries']);
dcCore::app()->addBehavior('adminAfterPostUpdate', [seriesBehaviors::class, 'setSeries']);

dcCore::app()->addBehavior('adminPostHeaders', [seriesBehaviors::class, 'postHeaders']);

dcCore::app()->addBehavior('adminPostsActions', [seriesBehaviors::class, 'adminPostsActions']);

dcCore::app()->addBehavior('adminPreferencesFormV2', [seriesBehaviors::class, 'adminPreferencesForm']);
dcCore::app()->addBehavior('adminBeforeUserOptionsUpdate', [seriesBehaviors::class, 'setSerieListFormat']);

dcCore::app()->addBehavior('adminUserForm', [seriesBehaviors::class, 'adminUserForm']);
dcCore::app()->addBehavior('adminBeforeUserCreate', [seriesBehaviors::class, 'setSerieListFormat']);
dcCore::app()->addBehavior('adminBeforeUserUpdate', [seriesBehaviors::class, 'setSerieListFormat']);

dcCore::app()->addBehavior('adminPostEditor', [seriesBehaviors::class, 'adminPostEditor']);
dcCore::app()->addBehavior('ckeditorExtraPlugins', [seriesBehaviors::class, 'ckeditorExtraPlugins']);

/* Register favorite */
dcCore::app()->addBehavior('adminDashboardFavoritesV2', [seriesBehaviors::class, 'adminDashboardFavorites']);

dcCore::app()->addBehavior('adminSimpleMenuAddType', [seriesBehaviors::class, 'adminSimpleMenuAddType']);
dcCore::app()->addBehavior('adminSimpleMenuSelect', [seriesBehaviors::class, 'adminSimpleMenuSelect']);
dcCore::app()->addBehavior('adminSimpleMenuBeforeEdit', [seriesBehaviors::class, 'adminSimpleMenuBeforeEdit']);
