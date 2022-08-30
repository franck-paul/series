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

if (!empty($_REQUEST['m']) && in_array($_REQUEST['m'], ['series','serie_posts'])) {
    require __DIR__ . '/' . $_REQUEST['m'] . '.php';
}
