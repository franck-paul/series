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

$core->addBehavior('initWidgets', array('seriesWidgets', 'initWidgets'));
$core->addBehavior('initDefaultWidgets', array('seriesWidgets', 'initDefaultWidgets'));

class seriesWidgets
{
    public static function initWidgets($w)
    {
        $combo = array(
            __('Serie name')     => 'meta_id_lower',
            __('Entries count')  => 'count'
        );
        if (version_compare(DC_VERSION, '2.14-dev', '>=')) {
            $combo[__('Newest entry')] = 'latest';
            $combo[__('Oldest entry')] = 'oldest';
        }

        // Widget for all series
        $w->create('series', __('Series'), array('tplSeries', 'seriesWidget'), null, __('List of series'));
        $w->series->setting('title', __('Title:'), __('Series'));
        $w->series->setting('limit', __('Limit (empty means no limit):'), '20');
        $w->series->setting('sortby', __('Order by:'), 'meta_id_lower', 'combo', $combo);
        $w->series->setting('orderby', __('Sort:'), 'asc', 'combo',
            array(__('Ascending') => 'asc', __('Descending') => 'desc')
        );
        $w->series->setting('allserieslinktitle', __('Link to all series:'), __('All series'));
        $w->series->setting('homeonly', __('Display on:'), 0, 'combo',
            array(
                __('All pages')           => 0,
                __('Home page only')      => 1,
                __('Except on home page') => 2
            )
        );
        $w->series->setting('content_only', __('Content only'), 0, 'check');
        $w->series->setting('class', __('CSS class:'), '');
        $w->series->setting('offline', __('Offline'), 0, 'check');

        // Widget for currently displayed post
        $w->create('seriesPosts', __('Siblings'), array('tplSeries', 'seriePostsWidget'), null, __('Other posts of the same serie(s)'));
        $w->seriesPosts->setting('title', __('Title:'), __('Siblings'));
        $w->seriesPosts->setting('serietitle', __('Show titles of series'), 1, 'check');
        $w->seriesPosts->setting('orderseriesby', __('Order series by:'), 'asc', 'combo',
            array(__('Ascending') => 'asc', __('Descending') => 'desc')
        );
        $w->seriesPosts->setting('current', __('Include current entry:'), 'std', 'combo',
            array(__('Standard') => 'std', __('With link') => 'link', __('None') => 'none')
        );
        $w->seriesPosts->setting('sortentriesby', __('Order entries by:'), 'date', 'combo',
            array(__('Date') => 'date', __('Entry title') => 'title')
        );
        $w->seriesPosts->setting('orderentriesby', __('Sort:'), 'asc', 'combo',
            array(__('Ascending') => 'asc', __('Descending') => 'desc')
        );
        $w->seriesPosts->setting('content_only', __('Content only'), 0, 'check');
        $w->seriesPosts->setting('class', __('CSS class:'), '');
        $w->seriesPosts->setting('offline', __('Offline'), 0, 'check');
    }

    public static function initDefaultWidgets($w, $d)
    {
        $d['nav']->append($w->series);
    }
}
