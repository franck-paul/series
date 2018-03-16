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

if (!empty($_REQUEST['m'])) {
    switch ($_REQUEST['m']) {
        case 'series':
        case 'serie_posts':
            require dirname(__FILE__) . '/' . $_REQUEST['m'] . '.php';
            break;
    }
}
