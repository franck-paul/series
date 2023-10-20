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

use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Html;
use Dotclear\Interface\Core\BlogInterface;
use Dotclear\Interface\Core\MetaInterface;
use Dotclear\Plugin\widgets\WidgetsElement;

class FrontendWidgets
{
    public static function seriesWidget(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (($w->homeonly == 1 && !App::url()->isHome(App::url()->type)) || ($w->homeonly == 2 && App::url()->isHome(App::url()->type))) {
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

        $rs = App::meta()->computeMetaStats(
            App::meta()->getMetadata($params)
        );

        if ($rs->isEmpty()) {
            return '';
        }

        if ($sort == 'meta_id_lower') {
            // Sort resulting recordset on cleaned id
            $rs->sort($sort, $order);
        }

        $res = ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') . '<ul>';

        if (App::url()->type == 'post' && App::frontend()->context()->posts instanceof MetaRecord) {
            App::frontend()->context()->meta = App::meta()->getMetaRecordset(App::frontend()->context()->posts->post_meta, 'serie');
        }
        while ($rs->fetch()) {
            $class = '';
            if (App::url()->type == 'post' && App::frontend()->context()->posts instanceof MetaRecord) {
                while (App::frontend()->context()->meta->fetch()) {
                    if (App::frontend()->context()->meta->meta_id == $rs->meta_id) {
                        $class = ' class="serie-current"';

                        break;
                    }
                }
            }
            $res .= '<li' . $class . '><a href="' . App::blog()->url() . App::url()->getURLFor('serie', rawurlencode($rs->meta_id)) . '" ' .
            'class="serie' . $rs->roundpercent . '">' .
            $rs->meta_id . '</a></li>';
        }

        $res .= '</ul>';

        if (App::url()->getURLFor('series') && !is_null($w->allserieslinktitle) && $w->allserieslinktitle !== '') {
            $res .= '<p><strong><a href="' . App::blog()->url() . App::url()->getURLFor('series') . '">' .
            Html::escapeHTML($w->allserieslinktitle) . '</a></strong></p>';
        }

        return $w->renderDiv((bool) $w->content_only, 'series ' . $w->class, '', $res);
    }

    public static function seriePostsWidget(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (App::url()->type != 'post') {
            return '';
        }

        if (!App::frontend()->context()->posts->post_meta) {
            return '';
        }

        $metas = unserialize(App::frontend()->context()->posts->post_meta);
        if (isset($metas['serie'])) {
            $sql = 'SELECT * FROM ' .
            App::con()->prefix() . MetaInterface::META_TABLE_NAME . ' as m,' .
            App::con()->prefix() . BlogInterface::POST_TABLE_NAME . ' as p ' .
            ' WHERE m.post_id = p.post_id ' .
            ' AND post_type = \'post\' ' .
            ' AND post_status = ' . BlogInterface::POST_PUBLISHED . ' ' .
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
            $rs = new MetaRecord(App::con()->select($sql));
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
            if ($rs->post_id == App::frontend()->context()->posts->post_id) {
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
                    $list .= '<h3><a href="' . App::blog()->url() . App::url()->getURLFor('serie', rawurlencode($rs->meta_id)) . '">' .
                    $rs->meta_id . '</a></h3>' . "\n";
                }
                $list .= '<ul>' . "\n";
                $serie = $rs->meta_id;
            }

            $href = App::blog()->url() . App::postTypes()->get($rs->post_type)->publicUrl(Html::sanitizeURL($rs->post_url));

            $list .= '<li' . $class . '>' .
            ($link ? '<a href="' . $href . '">' : '') .
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
