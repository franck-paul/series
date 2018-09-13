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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

// dead but useful code, in order to have translations
__('Series') . __('Series of posts');

$_menu['Blog']->addItem(__('Series'),
    'plugin.php?p=series&amp;m=series',
    urldecode(dcPage::getPF('series/icon.png')),
    preg_match('/plugin.php\?p=series&m=serie(s|_posts)?(&.*)?$/', $_SERVER['REQUEST_URI']),
    $core->auth->check('usage,contentadmin', $core->blog->id));

require dirname(__FILE__) . '/_widgets.php';

$core->addBehavior('adminPostFormItems', ['seriesBehaviors', 'seriesField']);

$core->addBehavior('adminAfterPostCreate', ['seriesBehaviors', 'setSeries']);
$core->addBehavior('adminAfterPostUpdate', ['seriesBehaviors', 'setSeries']);

$core->addBehavior('adminPostHeaders', ['seriesBehaviors', 'postHeaders']);

$core->addBehavior('adminPostsActionsPage', ['seriesBehaviors', 'adminPostsActionsPage']);

$core->addBehavior('adminPreferencesForm', ['seriesBehaviors', 'adminUserForm']);
$core->addBehavior('adminBeforeUserOptionsUpdate', ['seriesBehaviors', 'setSerieListFormat']);

$core->addBehavior('adminUserForm', ['seriesBehaviors', 'adminUserForm']);
$core->addBehavior('adminBeforeUserCreate', ['seriesBehaviors', 'setSerieListFormat']);
$core->addBehavior('adminBeforeUserUpdate', ['seriesBehaviors', 'setSerieListFormat']);

$core->addBehavior('adminPostEditor', ['seriesBehaviors', 'adminPostEditor']);
$core->addBehavior('ckeditorExtraPlugins', ['seriesBehaviors', 'ckeditorExtraPlugins']);

/* Register favorite */
$core->addBehavior('adminDashboardFavorites', ['seriesBehaviors', 'adminDashboardFavorites']);

$core->addBehavior('adminSimpleMenuAddType', ['seriesBehaviors', 'adminSimpleMenuAddType']);
$core->addBehavior('adminSimpleMenuSelect', ['seriesBehaviors', 'adminSimpleMenuSelect']);
$core->addBehavior('adminSimpleMenuBeforeEdit', ['seriesBehaviors', 'adminSimpleMenuBeforeEdit']);
