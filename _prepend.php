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

use Dotclear\Helper\Clearbricks;

dcCore::app()->url->register('serie', 'serie', '^serie/(.+)$', ['urlSeries', 'serie']);
dcCore::app()->url->register('series', 'series', '^series$', ['urlSeries', 'series']);
dcCore::app()->url->register('serie_feed', 'feed/serie', '^feed/serie/(.+)$', ['urlSeries', 'serieFeed']);

Clearbricks::lib()->autoload(['seriesBehaviors' => __DIR__ . '/inc/series.behaviors.php']);

dcCore::app()->addBehavior('coreInitWikiPost', [seriesBehaviors::class, 'coreInitWikiPost']);
