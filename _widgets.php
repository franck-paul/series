<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2012 Franck Paul
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('seriesWidgets','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('seriesWidgets','initDefaultWidgets'));

class seriesWidgets
{
	public static function initWidgets($w)
	{
		// Widget for all series
		$w->create('series',__('Series'),array('tplSeries','seriesWidget'));
		$w->series->setting('title',__('Title:'),__('Series'));
		$w->series->setting('limit',__('Limit (empty means no limit):'),'20');
		$w->series->setting('sortby',__('Order by:'),'meta_id_lower','combo',
			array(__('Serie name') => 'meta_id_lower', __('Entries count') => 'count')
			);
		$w->series->setting('orderby',__('Sort:'),'asc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc')
			);
		$w->series->setting('allserieslinktitle',__('Link to all series:'),__('All series'));

		// Widget for currently displayed post
		$w->create( 'seriesPosts', __( 'Current serie(s)' ), array( 'tplSeries', 'seriePostsWidget' ) );
		$w->seriesPosts->setting( 'title', __( 'Title:' ),__( 'Current serie(s)' ) );
		$w->seriesPosts->setting('serietitle',__('Show titles of series'),1,'check');
		$w->seriesPosts->setting('orderseriesby',__('Order series by:'),'asc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc')
			);
		$w->seriesPosts->setting('current',__('Include current entry:'),'std','combo',
			array(__('Standard') => 'std', __('With link') => 'link', __('None') => 'none')
			);
		$w->seriesPosts->setting('sortentriesby',__('Order entries by:'),'date','combo',
			array(__('Date') => 'date', __('Entry title') => 'title')
			);
		$w->seriesPosts->setting('orderentriesby',__('Sort:'),'asc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc')
			);
	}
	
	public static function initDefaultWidgets($w,$d)
	{
		$d['nav']->append($w->series);
	}
}
?>