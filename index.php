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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!empty($_REQUEST['m'])) {
	switch ($_REQUEST['m']) {
		case 'series' :
		case 'serie_posts' :
			require dirname(__FILE__).'/'.$_REQUEST['m'].'.php';
			break;
	}
}
?>