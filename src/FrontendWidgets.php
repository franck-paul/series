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

use dcBlog;
use dcCore;
use dcMeta;
use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsElement;

class FrontendWidgets
{
    public static function seriesWidget(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (($w->homeonly == 1 && !dcCore::app()->url->isHome(dcCore::app()->url->type)) || ($w->homeonly == 2 && dcCore::app()->url->isHome(dcCore::app()->url->type))) {
            return '';
        }

        $combo = ['meta_id_lower', 'count', 'latest', 'oldest'];

        $sort = $w->sortby;
        if (!in_array($sort, $combo)) {
            $sort = 'meta_id_lower';
        }

        $order = $w->orderby;
        if ($order != 'asc') {
            $order = 'desc';
        }

        $params = ['meta_type' => 'serie'];

        if ($sort != 'meta_id_lower') {
            // As optional limit may restrict result, we should set order (if not computed after)
            $params['order'] = $sort . ' ' . ($order == 'asc' ? 'ASC' : 'DESC');
        }

        if ($w->limit !== '') {
            $params['limit'] = abs((int) $w->limit);
        }

        $rs = dcCore::app()->meta->computeMetaStats(
            dcCore::app()->meta->getMetadata($params)
        );

        if ($rs->isEmpty()) {
            return '';
        }

        if ($sort == 'meta_id_lower') {
            // Sort resulting recordset on cleaned id
            $rs->sort($sort, $order);
        }

        $res = ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') . '<ul>';

        if (dcCore::app()->url->type == 'post' && dcCore::app()->ctx->posts instanceof MetaRecord) {
            dcCore::app()->ctx->meta = dcCore::app()->meta->getMetaRecordset(dcCore::app()->ctx->posts->post_meta, 'serie');
        }
        while ($rs->fetch()) {
            $class = '';
            if (dcCore::app()->url->type == 'post' && dcCore::app()->ctx->posts instanceof MetaRecord) {
                while (dcCore::app()->ctx->meta->fetch()) {
                    if (dcCore::app()->ctx->meta->meta_id == $rs->meta_id) {
                        $class = ' class="serie-current"';

                        break;
                    }
                }
            }
            $res .= '<li' . $class . '><a href="' . App::blog()->url() . dcCore::app()->url->getURLFor('serie', rawurlencode($rs->meta_id)) . '" ' .
            'class="serie' . $rs->roundpercent . '">' .
            $rs->meta_id . '</a></li>';
        }

        $res .= '</ul>';

        if (dcCore::app()->url->getURLFor('series') && !is_null($w->allserieslinktitle) && $w->allserieslinktitle !== '') {
            $res .= '<p><strong><a href="' . App::blog()->url() . dcCore::app()->url->getURLFor('series') . '">' .
            Html::escapeHTML($w->allserieslinktitle) . '</a></strong></p>';
        }

        return $w->renderDiv((bool) $w->content_only, 'series ' . $w->class, '', $res);
    }

    public static function seriePostsWidget(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (dcCore::app()->url->type != 'post') {
            return '';
        }

        if (!dcCore::app()->ctx->posts->post_meta) {
            return '';
        }

        $metas = unserialize(dcCore::app()->ctx->posts->post_meta);
        if (isset($metas['serie'])) {
            $sql = 'SELECT * FROM ' .
            dcCore::app()->prefix . dcMeta::META_TABLE_NAME . ' as m,' .
            dcCore::app()->prefix . dcBlog::POST_TABLE_NAME . ' as p ' .
            ' WHERE m.post_id = p.post_id ' .
            ' AND post_type = \'post\' ' .
            ' AND post_status = ' . dcBlog::POST_PUBLISHED . ' ' .
            ' AND blog_id = \'' . App::blog()->id() . '\'' .
                ' AND meta_type = \'serie\' AND ( ';
            foreach ($metas['serie'] as $key => $meta) {
                $sql .= " meta_id = '" . $meta . "' ";
                if ($key < (is_countable($metas['serie']) ? count($metas['serie']) : 0) - 1) {
                    $sql .= ' OR ';
                }
            }
            $sql .= ')';

            $order = $w->orderseriesby;
            if ($order != 'desc') {
                $order = 'asc';
            }
            $sql .= ' ORDER BY meta_id ' . ($order == 'asc' ? 'ASC' : 'DESC') . ', ';

            $sort = $w->sortentriesby;
            if (!in_array($sort, ['date', 'title'])) {
                $sort = 'date';
            }
            $order = $w->orderentriesby;
            if ($order != 'desc') {
                $order = 'asc';
            }
            $sql .= ($sort == 'date' ? 'p.post_dt' : 'p.post_title') . ' ' . ($order == 'asc' ? 'ASC' : 'DESC');
            $rs = new MetaRecord(dcCore::app()->con->select($sql));
            if ($rs->isEmpty()) {
                return '';
            }
        } else {
            return '';
        }

        $res = ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) . "\n" : '');

        $serie = '';
        $list  = '';
        while ($rs->fetch()) {
            $class = '';
            $link  = true;
            if ($rs->post_id == dcCore::app()->ctx->posts->post_id) {
                if ($w->current == 'none') {
                    continue;
                }
                $class = ' class="current"';
                if ($w->current == 'std') {
                    $link = false;
                }
            }

            if ($rs->meta_id != $serie) {
                if ($serie != '') {
                    $list .= '</ul>' . "\n";
                }
                if ($w->serietitle) {
                    $list .= '<h3><a href="' . App::blog()->url() . dcCore::app()->url->getURLFor('serie', rawurlencode($rs->meta_id)) . '">' .
                    $rs->meta_id . '</a></h3>' . "\n";
                }
                $list .= '<ul>' . "\n";
                $serie = $rs->meta_id;
            }

            $list .= '<li' . $class . '>' .
            ($link ? '<a href="' . App::blog()->url() . dcCore::app()->getPostPublicURL($rs->post_type, Html::sanitizeURL($rs->post_url)) . '">' : '') .
            Html::escapeHTML($rs->post_title) .
                ($link ? '</a>' : '') .
                '</li>' . "\n";
        }
        if ($list == '') {
            return '';
        }
        $res .= $list . '</ul>' . "\n";

        return $w->renderDiv((bool) $w->content_only, 'series-posts ' . $w->class, '', $res);
    }
}
