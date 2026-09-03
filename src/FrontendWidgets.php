<?php

/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\series;

use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @todo switch to SqlStatement
 */
class FrontendWidgets
{
    public static function seriesWidget(WidgetsElement $widgetsElement): string
    {
        if ($widgetsElement->offline) {
            return '';
        }

        if (($widgetsElement->homeonly == 1 && !App::url()->isHome(App::url()->getType()))
            || ($widgetsElement->homeonly == 2 && App::url()->isHome(App::url()->getType()))
        ) {
            return '';
        }

        $combo = ['meta_id_lower', 'count', 'latest', 'oldest'];

        $sort = is_string($sort = $widgetsElement->get('sortby')) ? $sort : '';
        if (!in_array($sort, $combo, true)) {
            $sort = 'meta_id_lower';
        }

        $order = is_string($order = $widgetsElement->get('orderby')) ? $order : '';
        if ($order !== 'asc') {
            $order = 'desc';
        }

        $params = ['meta_type' => 'serie'];

        if ($sort !== 'meta_id_lower') {
            // As optional limit may restrict result, we should set order (if not computed after)
            $params['order'] = $sort . ' ' . ($order === 'asc' ? 'ASC' : 'DESC');
        }

        $limit = is_numeric($limit = $widgetsElement->get('limit')) ? abs((int) $limit) : 0;
        if ($limit > 0) {
            $params['limit'] = $limit;
        }

        $rs = App::meta()->computeMetaStats(
            App::meta()->getMetadata($params)
        );

        if ($rs->isEmpty()) {
            return '';
        }

        if ($sort === 'meta_id_lower') {
            // Sort resulting recordset on cleaned id
            $rs->lexicalSort($sort, $order);
        }

        $res = ($widgetsElement->title ? $widgetsElement->renderTitle(Html::escapeHTML($widgetsElement->title)) : '') . '<ul>';

        if (App::url()->isType('post') && App::frontend()->context()->posts instanceof MetaRecord) {
            $post_meta = App::frontend()->context()->posts->strField('post_meta', true);

            App::frontend()->context()->meta = App::meta()->getMetaRecordset($post_meta, 'serie');
        }

        while ($rs->fetch()) {
            $class   = '';
            $meta_id = $rs->strField('meta_id');
            if ($meta_id !== '') {
                $roundpercent = $rs->intField('roundpercent');
                if (App::url()->isType('post') && App::frontend()->context()->meta instanceof MetaRecord) {
                    while (App::frontend()->context()->meta->fetch()) {
                        if (App::frontend()->context()->meta->strField('meta_id') === $meta_id) {
                            $class = ' class="serie-current"';

                            break;
                        }
                    }
                }

                $res .= '<li' . $class . '><a href="' . App::blog()->url() . App::url()->getURLFor('serie', rawurlencode((string) $meta_id)) . '" ' . 'class="serie' . $roundpercent . '">' . $meta_id . '</a></li>';
            }
        }

        $res .= '</ul>';

        if (App::url()->getURLFor('series')
            && !is_null($widgetsElement->get('allserieslinktitle'))
            && $widgetsElement->get('allserieslinktitle') !== ''
        ) {
            $allserieslinktitle = is_string($allserieslinktitle = $widgetsElement->get('allserieslinktitle')) ? $allserieslinktitle : '';
            if ($allserieslinktitle !== '') {
                $res .= '<p><strong><a href="' . App::blog()->url() . App::url()->getURLFor('series') . '">' . Html::escapeHTML($allserieslinktitle) . '</a></strong></p>';
            }
        }

        return $widgetsElement->renderDiv((bool) $widgetsElement->content_only, 'series ' . $widgetsElement->class, '', $res);
    }

    public static function seriePostsWidget(WidgetsElement $widgetsElement): string
    {
        if ($widgetsElement->offline) {
            return '';
        }

        if (!App::url()->isType('post')) {
            return '';
        }

        $post_meta = App::frontend()->context()->posts instanceof MetaRecord ? App::frontend()->context()->posts->strField('post_meta') : '';

        if ($post_meta === '') {
            return '';
        }

        $metas = unserialize($post_meta);
        if (is_array($metas)
            && isset($metas['serie'])
            && is_array($metas['serie'])
        ) {
            /**
             * @var array<int, string> $metas_serie
             */
            $metas_serie = $metas['serie'];
            $sql         = 'SELECT * FROM ' .
            App::db()->con()->prefix() . App::meta()::META_TABLE_NAME . ' as m,' .
            App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME . ' as p ' .
            ' WHERE m.post_id = p.post_id ' .
            ' AND post_type = \'post\' ' .
            ' AND post_status = ' . App::status()->post()::PUBLISHED . ' ' .
            ' AND blog_id = \'' . App::blog()->id() . '\'' .
                ' AND meta_type = \'serie\' AND ( ';
            foreach ($metas_serie as $key => $meta) {
                $sql .= " meta_id = '" . $meta . "' ";
                if ($key < count($metas['serie']) - 1) {
                    $sql .= ' OR ';
                }
            }

            $sql .= ')';

            $order = $widgetsElement->get('orderseriesby');
            if ($order != 'desc') {
                $order = 'asc';
            }

            $sql .= ' ORDER BY meta_id ' . ($order == 'asc' ? 'ASC' : 'DESC') . ', ';

            $sort = $widgetsElement->get('sortentriesby');
            if (!in_array($sort, ['date', 'title'])) {
                $sort = 'date';
            }

            $order = $widgetsElement->get('orderentriesby');
            if ($order != 'desc') {
                $order = 'asc';
            }

            $sql .= ($sort == 'date' ? 'p.post_dt' : 'p.post_title') . ' ' . ($order == 'asc' ? 'ASC' : 'DESC');
            $metaRecord = new MetaRecord(App::db()->con()->select($sql));
            if ($metaRecord->isEmpty()) {
                return '';
            }
        } else {
            return '';
        }

        $res = ($widgetsElement->title ? $widgetsElement->renderTitle(Html::escapeHTML($widgetsElement->title)) . "\n" : '');

        $serie = '';
        $list  = '';
        if (App::frontend()->context()->posts instanceof MetaRecord) {
            while ($metaRecord->fetch()) {
                $class   = '';
                $meta_id = $metaRecord->strField('meta_id');
                if ($meta_id !== '') {
                    $link = true;
                    if ($metaRecord->intField('post_id') === App::frontend()->context()->posts->intField('post_id')) {
                        if ($widgetsElement->get('current') == 'none') {
                            continue;
                        }

                        $class = ' class="current"';
                        if ($widgetsElement->get('current') == 'std') {
                            $link = false;
                        }
                    }

                    $suffix = $widgetsElement->get('folded') ? '</details>' . "\n" : '';
                    if ($meta_id !== $serie) {
                        if ($serie !== '') {
                            $list .= '</ul>' . "\n" . $suffix;
                        }

                        if ($widgetsElement->get('serietitle')) {
                            $list .= '<h3><a href="' . App::blog()->url() . App::url()->getURLFor('serie', rawurlencode($meta_id)) . '">' .
                            $meta_id . '</a></h3>' . "\n";
                        }

                        $serie  = $meta_id;
                        $prefix = $widgetsElement->get('folded') ? '<details><summary>' . $serie . '</summary>' . "\n" : '';

                        $list .= $prefix . '<ul>' . "\n";
                    }

                    $post_type  = $metaRecord->strField('post_type');
                    $post_url   = $metaRecord->strField('post_url');
                    $post_title = $metaRecord->strField('post_title');
                    $href       = App::blog()->url() . App::postTypes()->get($post_type)->publicUrl(Html::sanitizeURL($post_url));

                    $list .= '<li' . $class . '>' . ($link ? '<a href="' . $href . '">' : '') . Html::escapeHTML($post_title) . ($link ? '</a>' : '') . '</li>' . "\n";
                }
            }
        }

        if ($list === '') {
            return '';
        }

        $res .= $list . '</ul>' . "\n";

        return $widgetsElement->renderDiv((bool) $widgetsElement->content_only, 'series-posts ' . $widgetsElement->class, '', $res);
    }
}
