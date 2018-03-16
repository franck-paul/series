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

if (!defined('DC_RC_PATH')) {return;}

$core->url->register('serie', 'serie', '^serie/(.+)$', array('urlSeries', 'serie'));
$core->url->register('series', 'series', '^series$', array('urlSeries', 'series'));
$core->url->register('serie_feed', 'feed/serie', '^feed/serie/(.+)$', array('urlSeries', 'serieFeed'));

$__autoload['seriesBehaviors'] = dirname(__FILE__) . '/inc/series.behaviors.php';

$core->addBehavior('coreInitWikiPost', array('seriesBehaviors', 'coreInitWikiPost'));
