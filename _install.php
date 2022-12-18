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

if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

try {
    $old_version = dcCore::app()->getVersion(basename(__DIR__));

    if (version_compare((string) $old_version, '1.0', '<')) {
        // Remove js/jquery.autocomplete.js
        @unlink(dcUtils::path([__DIR__, 'js/jquery.autocomplete.js']));
    }

    if (version_compare((string) $old_version, '1.2', '<')) {
        // Remove default-templates/currwurst
        @unlink(dcUtils::path([__DIR__, 'default-templates','currwurst','serie.html']));
        @unlink(dcUtils::path([__DIR__, 'default-templates','currwurst','series.html']));
        @rmdir(dcUtils::path([__DIR__, 'default-templates','currwurst']));
    }

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
