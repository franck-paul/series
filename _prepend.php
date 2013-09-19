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

if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['core']->url->register('serie','serie','^serie/(.+)$',array('urlSeries','serie'));
$GLOBALS['core']->url->register('series','series','^series$',array('urlSeries','series'));
$GLOBALS['core']->url->register('serie_feed','feed/serie','^feed/serie/(.+)$',array('urlSeries','serieFeed'));
?>