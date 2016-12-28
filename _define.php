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

$this->registerModule(
	/* Name */			"Series",
	/* Description*/		"Series of posts",
	/* Author */			"Franck Paul",
	/* Version */			'0.7',
	array(
		/* Dependencies */	'requires' =>		array(array('core','2.10')),
		/* Permissions */	'permissions' =>	'usage,contentadmin',
		/* Priority */		'priority' =>		1001,	// Must be higher than dcLegacyEditor/dcCKEditor priority (ie 1000)
		/* Type */			'type' =>			'plugin',
		'settings'	=>		array(
								'pref' => '#user-options.series_prefs'
							)
	)
);
