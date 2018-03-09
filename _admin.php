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
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
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

//$core->addBehavior('adminPostFormSidebar',array('seriesBehaviors','seriesField'));
$core->addBehavior('adminPostFormItems', array('seriesBehaviors', 'seriesField'));

$core->addBehavior('adminAfterPostCreate', array('seriesBehaviors', 'setSeries'));
$core->addBehavior('adminAfterPostUpdate', array('seriesBehaviors', 'setSeries'));

$core->addBehavior('adminPostHeaders', array('seriesBehaviors', 'postHeaders'));

$core->addBehavior('adminPostsActionsPage', array('seriesBehaviors', 'adminPostsActionsPage'));

$core->addBehavior('adminPreferencesForm', array('seriesBehaviors', 'adminUserForm'));
$core->addBehavior('adminBeforeUserOptionsUpdate', array('seriesBehaviors', 'setSerieListFormat'));

$core->addBehavior('adminUserForm', array('seriesBehaviors', 'adminUserForm'));
$core->addBehavior('adminBeforeUserCreate', array('seriesBehaviors', 'setSerieListFormat'));
$core->addBehavior('adminBeforeUserUpdate', array('seriesBehaviors', 'setSerieListFormat'));

$core->addBehavior('adminPostEditor', array('seriesBehaviors', 'adminPostEditor'));
$core->addBehavior('ckeditorExtraPlugins', array('seriesBehaviors', 'ckeditorExtraPlugins'));

/* Register favorite */
$core->addBehavior('adminDashboardFavorites', array('seriesBehaviors', 'adminDashboardFavorites'));

$core->addBehavior('adminSimpleMenuAddType', array('seriesBehaviors', 'adminSimpleMenuAddType'));
$core->addBehavior('adminSimpleMenuSelect', array('seriesBehaviors', 'adminSimpleMenuSelect'));
$core->addBehavior('adminSimpleMenuBeforeEdit', array('seriesBehaviors', 'adminSimpleMenuBeforeEdit'));
