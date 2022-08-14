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
if (!defined('DC_RC_PATH')) {
    return;
}

# Localized string we find in template
__("This serie's comments Atom feed");
__("This serie's entries Atom feed");

require __DIR__ . '/_widgets.php';

dcCore::app()->tpl->addBlock('Series', ['tplSeries', 'Series']);
dcCore::app()->tpl->addBlock('SeriesHeader', ['tplSeries', 'SeriesHeader']);
dcCore::app()->tpl->addBlock('SeriesFooter', ['tplSeries', 'SeriesFooter']);
dcCore::app()->tpl->addBlock('EntrySeries', ['tplSeries', 'EntrySeries']);
dcCore::app()->tpl->addValue('SerieID', ['tplSeries', 'SerieID']);
dcCore::app()->tpl->addValue('SeriePercent', ['tplSeries', 'SeriePercent']);
dcCore::app()->tpl->addValue('SerieRoundPercent', ['tplSeries', 'SerieRoundPercent']);
dcCore::app()->tpl->addValue('SerieURL', ['tplSeries', 'SerieURL']);
dcCore::app()->tpl->addValue('SerieCloudURL', ['tplSeries', 'SerieCloudURL']);
dcCore::app()->tpl->addValue('SerieFeedURL', ['tplSeries', 'SerieFeedURL']);

dcCore::app()->addBehavior('templateBeforeBlock', ['behaviorsSeries', 'templateBeforeBlock']);
dcCore::app()->addBehavior('publicBeforeDocument', ['behaviorsSeries', 'addTplPath']);

dcCore::app()->addBehavior('publicBreadcrumb', ['behaviorsSeries', 'publicBreadcrumb']);

class behaviorsSeries
{
    public static function publicBreadcrumb($context, $separator)
    {
        if ($context == 'series') {

            // All series
            return __('All series');
        } elseif ($context == 'serie') {
            // Serie

            // Get current page if set
            $page = isset($GLOBALS['_page_number']) ? (int) $GLOBALS['_page_number'] : 0;
            $ret  = '<a href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('series') . '">' . __('All series') . '</a>';
            if ($page == 0) {
                $ret .= $separator . dcCore::app()->ctx->meta->meta_id;
            } else {
                $ret .= $separator . '<a href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie') . '/' . rawurlencode(dcCore::app()->ctx->meta->meta_id) . '">' . dcCore::app()->ctx->meta->meta_id . '</a>';
                $ret .= $separator . sprintf(__('page %d'), $page);
            }

            return $ret;
        }
    }

    public static function templateBeforeBlock($core, $b, $attr)
    {
        if (($b == 'Entries' || $b == 'Comments') && isset($attr['serie'])) {
            return
            "<?php\n" .
            "if (!isset(\$params)) { \$params = []; }\n" .
            "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
            "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
            "\$params['from'] .= ', '.dcCore::app()->prefix.'meta METAS ';\n" .
            "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
            "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
            "\$params['sql'] .= \"AND METAS.meta_id = '" . dcCore::app()->con->escape($attr['serie']) . "' \";\n" .
                "?>\n";
        } elseif (empty($attr['no_context']) && ($b == 'Entries' || $b == 'Comments')) {
            return
                '<?php if (dcCore::app()->ctx->exists("meta") && dcCore::app()->ctx->meta->rows() && (dcCore::app()->ctx->meta->meta_type == "serie")) { ' .
                "if (!isset(\$params)) { \$params = []; }\n" .
                "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
                "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
                "\$params['from'] .= ', '.dcCore::app()->prefix.'meta METAS ';\n" .
                "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
                "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
                "\$params['sql'] .= \"AND METAS.meta_id = '\".dcCore::app()->con->escape(dcCore::app()->ctx->meta->meta_id).\"' \";\n" .
                "} ?>\n";
        }
    }

    public static function addTplPath($core = null)
    {
        $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
        if (!empty($tplset) && is_dir(__DIR__ . '/default-templates/' . $tplset)) {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/default-templates/' . $tplset);
        } else {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/default-templates/' . DC_DEFAULT_TPLSET);
        }
    }
}

class tplSeries
{
    public static function Series($attr, $content)
    {
        $type  = isset($attr['type']) ? addslashes($attr['type']) : 'serie';
        $limit = isset($attr['limit']) ? (int) $attr['limit'] : 'null';
        $combo = ['meta_id_lower', 'count', 'latest', 'oldest'];

        $sortby = 'meta_id_lower';
        if (isset($attr['sortby']) && in_array($attr['sortby'], $combo)) {
            $sortby = strtolower($attr['sortby']);
        }

        $order = 'asc';
        if (isset($attr['order']) && $attr['order'] == 'desc') {
            $order = 'desc';
        }

        $res = "<?php\n" .
            "dcCore::app()->ctx->meta = dcCore::app()->meta->computeMetaStats(dcCore::app()->meta->getMetadata(['meta_type'=>'" .
            $type . "','limit'=>" . $limit .
            ($sortby != 'meta_id_lower' ? ",'order'=>'" . $sortby . ' ' . ($order == 'asc' ? 'ASC' : 'DESC') . "'" : '') .
            '])); ' .
            "dcCore::app()->ctx->meta->sort('" . $sortby . "','" . $order . "'); " .
            '?>';

        $res .= '<?php while (dcCore::app()->ctx->meta->fetch()) : ?>' . $content . '<?php endwhile; ' .
            'dcCore::app()->ctx->meta = null; ?>';

        return $res;
    }

    public static function SeriesHeader($attr, $content)
    {
        return
            '<?php if (dcCore::app()->ctx->meta->isStart()) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    public static function SeriesFooter($attr, $content)
    {
        return
            '<?php if (dcCore::app()->ctx->meta->isEnd()) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    public static function EntrySeries($attr, $content)
    {
        $type   = isset($attr['type']) ? addslashes($attr['type']) : 'serie';
        $combo  = ['meta_id_lower', 'count', 'latest', 'oldest'];
        $sortby = 'meta_id_lower';
        if (isset($attr['sortby']) && in_array($attr['sortby'], $combo)) {
            $sortby = strtolower($attr['sortby']);
        }
        $order = 'asc';
        if (isset($attr['order']) && $attr['order'] == 'desc') {
            $order = 'desc';
        }

        $res = "<?php\n" .
            "dcCore::app()->ctx->meta = dcCore::app()->meta->getMetaRecordset(dcCore::app()->ctx->posts->post_meta,'" . $type . "'); " .
            "dcCore::app()->ctx->meta->sort('" . $sortby . "','" . $order . "'); " .
            '?>';

        $res .= '<?php while (dcCore::app()->ctx->meta->fetch()) : ?>' . $content . '<?php endwhile; ' .
            'dcCore::app()->ctx->meta = null; ?>';

        return $res;
    }

    public static function SerieID($attr)
    {
        $f = dcCore::app()->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'dcCore::app()->ctx->meta->meta_id') . '; ?>';
    }

    public static function SeriePercent($attr)
    {
        return '<?php echo dcCore::app()->ctx->meta->percent; ?>';
    }

    public static function SerieRoundPercent($attr)
    {
        return '<?php echo dcCore::app()->ctx->meta->roundpercent; ?>';
    }

    public static function SerieURL($attr)
    {
        $f = dcCore::app()->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'dcCore::app()->blog->url.dcCore::app()->url->getURLFor("serie",' .
            'rawurlencode(dcCore::app()->ctx->meta->meta_id))') . '; ?>';
    }

    public static function SerieCloudURL($attr)
    {
        $f = dcCore::app()->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'dcCore::app()->blog->url.dcCore::app()->url->getURLFor("series")') . '; ?>';
    }

    public static function SerieFeedURL($attr)
    {
        $type = !empty($attr['type']) ? $attr['type'] : 'rss2';

        if (!preg_match('#^(rss2|atom)$#', $type)) {
            $type = 'rss2';
        }

        $f = dcCore::app()->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'dcCore::app()->blog->url.dcCore::app()->url->getURLFor("serie_feed",' .
            'rawurlencode(dcCore::app()->ctx->meta->meta_id)."/' . $type . '")') . '; ?>';
    }

    # Widget function
    public static function seriesWidget($w)
    {
        if ($w->offline) {
            return;
        }

        if (($w->homeonly == 1 && !dcCore::app()->url->isHome(dcCore::app()->url->type)) || ($w->homeonly == 2 && dcCore::app()->url->isHome(dcCore::app()->url->type))) {
            return;
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
            return;
        }

        if ($sort == 'meta_id_lower') {
            // Sort resulting recordset on cleaned id
            $rs->sort($sort, $order);
        }

        $res = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '') . '<ul>';

        if (dcCore::app()->url->type == 'post' && dcCore::app()->ctx->posts instanceof record) {
            dcCore::app()->ctx->meta = dcCore::app()->meta->getMetaRecordset(dcCore::app()->ctx->posts->post_meta, 'serie');
        }
        while ($rs->fetch()) {
            $class = '';
            if (dcCore::app()->url->type == 'post' && dcCore::app()->ctx->posts instanceof record) {
                while (dcCore::app()->ctx->meta->fetch()) {
                    if (dcCore::app()->ctx->meta->meta_id == $rs->meta_id) {
                        $class = ' class="serie-current"';

                        break;
                    }
                }
            }
            $res .= '<li' . $class . '><a href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie', rawurlencode($rs->meta_id)) . '" ' .
            'class="serie' . $rs->roundpercent . '">' .
            $rs->meta_id . '</a></li>';
        }

        $res .= '</ul>';

        if (dcCore::app()->url->getURLFor('series') && !is_null($w->allserieslinktitle) && $w->allserieslinktitle !== '') {
            $res .= '<p><strong><a href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('series') . '">' .
            html::escapeHTML($w->allserieslinktitle) . '</a></strong></p>';
        }

        return $w->renderDiv($w->content_only, 'series ' . $w->class, '', $res);
    }

    public static function seriePostsWidget($w)
    {
        if ($w->offline) {
            return;
        }

        if (dcCore::app()->url->type != 'post') {
            return;
        }

        if (!dcCore::app()->ctx->posts->post_meta) {
            return;
        }

        $metas = unserialize(dcCore::app()->ctx->posts->post_meta);
        if (isset($metas['serie'])) {
            $sql = 'SELECT * FROM ' .
            dcCore::app()->prefix . 'meta as m,' .
            dcCore::app()->prefix . 'post as p ' .
            ' WHERE m.post_id = p.post_id ' .
            ' AND post_type = \'post\' ' .
            ' AND post_status = 1 ' .
            ' AND blog_id = \'' . dcCore::app()->blog->id . '\'' .
                ' AND meta_type = \'serie\' AND ( ';
            foreach ($metas['serie'] as $key => $meta) {
                $sql .= " meta_id = '" . $meta . "' ";
                if ($key < count($metas['serie']) - 1) {
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
            $rs = dcCore::app()->con->select($sql);
            if ($rs->isEmpty()) {
                return;
            }
        } else {
            return;
        }

        $res = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) . "\n" : '');

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
                    $list .= '<h3><a href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie', rawurlencode($rs->meta_id)) . '">' .
                    $rs->meta_id . '</a></h3>' . "\n";
                }
                $list .= '<ul>' . "\n";
                $serie = $rs->meta_id;
            }

            $list .= '<li' . $class . '>' .
            ($link ? '<a href="' . dcCore::app()->blog->url . dcCore::app()->getPostPublicURL($rs->post_type, html::sanitizeURL($rs->post_url)) . '">' : '') .
            html::escapeHTML($rs->post_title) .
                ($link ? '</a>' : '') .
                '</li>' . "\n";
        }
        if ($list == '') {
            return;
        }
        $res .= $list . '</ul>' . "\n";

        return $w->renderDiv($w->content_only, 'series-posts ' . $w->class, '', $res);
    }
}

class urlSeries extends dcUrlHandlers
{
    public static function serie($args)
    {
        $n = self::getPageNumber($args);

        if ($args == '' && !$n) {
            self::p404();
        } elseif (preg_match('%(.*?)/feed/(rss2|atom)?$%u', $args, $m)) {
            $type = $m[2] == 'atom' ? 'atom' : 'rss2';
            $mime = 'application/xml';

            dcCore::app()->ctx->meta = dcCore::app()->meta->computeMetaStats(
                dcCore::app()->meta->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $m[1],
                ])
            );

            if (dcCore::app()->ctx->meta->isEmpty()) {
                self::p404();
            } else {
                $tpl = $type;

                if ($type == 'atom') {
                    $mime = 'application/atom+xml';
                }

                self::serveDocument($tpl . '.xml', $mime);
            }
        } else {
            if ($n) {
                $GLOBALS['_page_number'] = $n;
            }

            dcCore::app()->ctx->meta = dcCore::app()->meta->computeMetaStats(
                dcCore::app()->meta->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $args,
                ])
            );

            if (dcCore::app()->ctx->meta->isEmpty()) {
                self::p404();
            } else {
                self::serveDocument('serie.html');
            }
        }
    }

    public static function series($args)
    {
        self::serveDocument('series.html');
    }

    public static function serieFeed($args)
    {
        if (!preg_match('#^(.+)/(atom|rss2)(/comments)?$#', $args, $m)) {
            self::p404();
        } else {
            $serie    = $m[1];
            $type     = $m[2];
            $comments = !empty($m[3]);

            dcCore::app()->ctx->meta = dcCore::app()->meta->computeMetaStats(
                dcCore::app()->meta->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $serie,
                ])
            );

            if (dcCore::app()->ctx->meta->isEmpty()) {
                # The specified serie does not exist.
                self::p404();
            } else {
                dcCore::app()->ctx->feed_subtitle = ' - ' . __('Serie') . ' - ' . dcCore::app()->ctx->meta->meta_id;

                if ($type == 'atom') {
                    $mime = 'application/atom+xml';
                } else {
                    $mime = 'application/xml';
                }

                $tpl = $type;
                if ($comments) {
                    $tpl .= '-comments';
                    dcCore::app()->ctx->nb_comment_per_page = dcCore::app()->blog->settings->system->nb_comment_per_feed;
                } else {
                    dcCore::app()->ctx->nb_entry_per_page = dcCore::app()->blog->settings->system->nb_post_per_feed;
                    dcCore::app()->ctx->short_feed_items  = dcCore::app()->blog->settings->system->short_feed_items;
                }
                $tpl .= '.xml';

                self::serveDocument($tpl, $mime);
            }
        }
    }
}
