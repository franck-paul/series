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

$new_version = dcCore::app()->plugins->moduleInfo('series', 'version');
$old_version = dcCore::app()->getVersion('series');

if (version_compare($old_version, $new_version, '>=')) {
    return;
}

try {
    if (version_compare($old_version, '1.0', '<')) {
        // Remove js/jquery.autocomplete.js
        @unlink(__DIR__ . '/' . 'js/jquery.autocomplete.js');
    }

    if (version_compare($old_version, '1.2', '<')) {
        // Remove default-templates/currwurst
        @unlink(__DIR__ . '/' . 'default-templates/currwurst/serie.html');
        @unlink(__DIR__ . '/' . 'default-templates/currwurst/series.html');
        @rmdir(__DIR__ . '/' . 'default-templates/currwurst');
    }

    dcCore::app()->setVersion('series', $new_version);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
