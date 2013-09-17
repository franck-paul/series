<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2013 Franck Paul
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['core']->url->register('serie','serie','^serie/(.+)$',array('urlSeries','serie'));
$GLOBALS['core']->url->register('series','series','^series$',array('urlSeries','series'));
$GLOBALS['core']->url->register('serie_feed','feed/serie','^feed/serie/(.+)$',array('urlSeries','serieFeed'));
?>