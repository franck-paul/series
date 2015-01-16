<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of series, a plugin for Dotclear 2.
#
# Copyright (c) Franck Paul and contributors
# carnet.franck.paul@gmail.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

// dead but useful code, in order to have translations
__('Series').__('Series of posts');

$_menu['Blog']->addItem(__('Series'),'plugin.php?p=series&amp;m=series','index.php?pf=series/icon.png',
		preg_match('/plugin.php\?p=series&m=serie(s|_posts)?(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

require dirname(__FILE__).'/_widgets.php';

//$core->addBehavior('adminPostFormSidebar',array('seriesBehaviors','seriesField'));
$core->addBehavior('adminPostFormItems',array('seriesBehaviors','seriesField'));

$core->addBehavior('adminAfterPostCreate',array('seriesBehaviors','setSeries'));
$core->addBehavior('adminAfterPostUpdate',array('seriesBehaviors','setSeries'));

$core->addBehavior('adminPostHeaders',array('seriesBehaviors','postHeaders'));

$core->addBehavior('adminPostsActionsPage',array('seriesBehaviors','adminPostsActionsPage'));

$core->addBehavior('adminPreferencesForm',array('seriesBehaviors','adminUserForm'));
$core->addBehavior('adminBeforeUserOptionsUpdate',array('seriesBehaviors','setSerieListFormat'));

$core->addBehavior('adminUserForm',array('seriesBehaviors','adminUserForm'));
$core->addBehavior('adminBeforeUserCreate',array('seriesBehaviors','setSerieListFormat'));
$core->addBehavior('adminBeforeUserUpdate',array('seriesBehaviors','setSerieListFormat'));

$core->addBehavior('adminPostEditor', array('seriesBehaviors','adminPostEditor'));
$core->addBehavior('ckeditorExtraPlugins', array('seriesBehaviors', 'ckeditorExtraPlugins'));

/* Register favorite */
$core->addBehavior('adminDashboardFavorites',array('seriesBehaviors','adminDashboardFavorites'));

$core->addBehavior('adminSimpleMenuAddType',array('seriesBehaviors','adminSimpleMenuAddType'));
$core->addBehavior('adminSimpleMenuSelect',array('seriesBehaviors','adminSimpleMenuSelect'));
$core->addBehavior('adminSimpleMenuBeforeEdit',array('seriesBehaviors','adminSimpleMenuBeforeEdit'));
