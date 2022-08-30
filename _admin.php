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

dcCore::app()->menu['Blog']->addItem(
    __('Series'),
    'plugin.php?p=series&amp;m=series',
    urldecode(dcPage::getPF('series/icon.svg')),
    preg_match('/plugin.php\?p=series&m=serie(s|_posts)?(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check('usage,contentadmin', dcCore::app()->blog->id)
);

require __DIR__ . '/_widgets.php';

dcCore::app()->addBehavior('adminPostFormItems', ['seriesBehaviors', 'seriesField']);

dcCore::app()->addBehavior('adminAfterPostCreate', ['seriesBehaviors', 'setSeries']);
dcCore::app()->addBehavior('adminAfterPostUpdate', ['seriesBehaviors', 'setSeries']);

dcCore::app()->addBehavior('adminPostHeaders', ['seriesBehaviors', 'postHeaders']);

dcCore::app()->addBehavior('adminPostsActionsPage', ['seriesBehaviors', 'adminPostsActionsPage']);

dcCore::app()->addBehavior('adminPreferencesForm', ['seriesBehaviors', 'adminUserForm']);
dcCore::app()->addBehavior('adminBeforeUserOptionsUpdate', ['seriesBehaviors', 'setSerieListFormat']);

dcCore::app()->addBehavior('adminUserForm', ['seriesBehaviors', 'adminUserForm']);
dcCore::app()->addBehavior('adminBeforeUserCreate', ['seriesBehaviors', 'setSerieListFormat']);
dcCore::app()->addBehavior('adminBeforeUserUpdate', ['seriesBehaviors', 'setSerieListFormat']);

dcCore::app()->addBehavior('adminPostEditor', ['seriesBehaviors', 'adminPostEditor']);
dcCore::app()->addBehavior('ckeditorExtraPlugins', ['seriesBehaviors', 'ckeditorExtraPlugins']);

/* Register favorite */
dcCore::app()->addBehavior('adminDashboardFavorites', ['seriesBehaviors', 'adminDashboardFavorites']);

dcCore::app()->addBehavior('adminSimpleMenuAddType', ['seriesBehaviors', 'adminSimpleMenuAddType']);
dcCore::app()->addBehavior('adminSimpleMenuSelect', ['seriesBehaviors', 'adminSimpleMenuSelect']);
dcCore::app()->addBehavior('adminSimpleMenuBeforeEdit', ['seriesBehaviors', 'adminSimpleMenuBeforeEdit']);
