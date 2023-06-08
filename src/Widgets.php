<?php
/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\series;

use Dotclear\Plugin\widgets\Widgets as dcWidgets;
use Dotclear\Plugin\widgets\WidgetsStack;

class Widgets
{
    public static function initWidgets(WidgetsStack $w)
    {
        // Widget for all series
        $w
            ->create('series', __('Series'), [FrontendWidgets::class, 'seriesWidget'], null, __('List of series'))
            ->addTitle(__('Series'))
            ->setting('limit', __('Limit (empty means no limit):'), '20')
            ->setting(
                'sortby',
                __('Order by:'),
                'meta_id_lower',
                'combo',
                [
                    __('Serie name')    => 'meta_id_lower',
                    __('Entries count') => 'count',
                    __('Newest entry')  => 'latest',
                    __('Oldest entry')  => 'oldest',
                ]
            )
            ->setting(
                'orderby',
                __('Sort:'),
                'asc',
                'combo',
                [
                    __('Ascending')  => 'asc',
                    __('Descending') => 'desc',
                ]
            )
            ->setting('allserieslinktitle', __('Link to all series:'), __('All series'))
            ->addHomeOnly()
            ->addContentOnly()
            ->addClass()
            ->addOffline();

        // Widget for currently displayed post
        $w
            ->create('seriesPosts', __('Siblings'), [FrontendWidgets::class, 'seriePostsWidget'], null, __('Other posts of the same serie(s)'))
            ->addTitle(__('Siblings'))
            ->setting('serietitle', __('Show titles of series'), 1, 'check')
            ->setting(
                'orderseriesby',
                __('Order series by:'),
                'asc',
                'combo',
                [
                    __('Ascending')  => 'asc',
                    __('Descending') => 'desc',
                ]
            )
            ->setting(
                'current',
                __('Include current entry:'),
                'std',
                'combo',
                [
                    __('Standard')  => 'std',
                    __('With link') => 'link',
                    __('None')      => 'none',
                ]
            )
            ->setting(
                'sortentriesby',
                __('Order entries by:'),
                'date',
                'combo',
                [
                    __('Date')        => 'date',
                    __('Entry title') => 'title',
                ]
            )
            ->setting(
                'orderentriesby',
                __('Sort:'),
                'asc',
                'combo',
                [
                    __('Ascending')  => 'asc',
                    __('Descending') => 'desc',
                ]
            )
            ->addContentOnly()
            ->addClass()
            ->addOffline();
    }

    public static function initDefaultWidgets($w, $d)
    {
        $d[dcWidgets::WIDGETS_NAV]->append($w->series);
    }
}
