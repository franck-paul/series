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

require dirname(__FILE__) . '/_widgets.php';

$core->tpl->addBlock('Series', ['tplSeries', 'Series']);
$core->tpl->addBlock('SeriesHeader', ['tplSeries', 'SeriesHeader']);
$core->tpl->addBlock('SeriesFooter', ['tplSeries', 'SeriesFooter']);
$core->tpl->addBlock('EntrySeries', ['tplSeries', 'EntrySeries']);
$core->tpl->addValue('SerieID', ['tplSeries', 'SerieID']);
$core->tpl->addValue('SeriePercent', ['tplSeries', 'SeriePercent']);
$core->tpl->addValue('SerieRoundPercent', ['tplSeries', 'SerieRoundPercent']);
$core->tpl->addValue('SerieURL', ['tplSeries', 'SerieURL']);
$core->tpl->addValue('SerieCloudURL', ['tplSeries', 'SerieCloudURL']);
$core->tpl->addValue('SerieFeedURL', ['tplSeries', 'SerieFeedURL']);

$core->addBehavior('templateBeforeBlock', ['behaviorsSeries', 'templateBeforeBlock']);
$core->addBehavior('tplSysIfConditions', ['behaviorsSeries', 'tplSysIfConditions']);
$core->addBehavior('publicBeforeDocument', ['behaviorsSeries', 'addTplPath']);

$core->addBehavior('publicBreadcrumb', ['behaviorsSeries', 'publicBreadcrumb']);

class behaviorsSeries
{
    public static function publicBreadcrumb($context, $separator)
    {
        global $core, $_ctx;

        if ($context == 'series') {

            // All series
            return __('All series');
        } elseif ($context == 'serie') {
            // Serie

            // Get current page if set
            $page = isset($GLOBALS['_page_number']) ? (int) $GLOBALS['_page_number'] : 0;
            $ret  = '<a href="' . $core->blog->url . $core->url->getURLFor('series') . '">' . __('All series') . '</a>';
            if ($page == 0) {
                $ret .= $separator . $_ctx->meta->meta_id;
            } else {
                $ret .= $separator . '<a href="' . $core->blog->url . $core->url->getURLFor('serie') . '/' . rawurlencode($_ctx->meta->meta_id) . '">' . $_ctx->meta->meta_id . '</a>';
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
            "\$params['from'] .= ', '.\$core->prefix.'meta METAS ';\n" .
            "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
            "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
            "\$params['sql'] .= \"AND METAS.meta_id = '" . $core->con->escape($attr['serie']) . "' \";\n" .
                "?>\n";
        } elseif (empty($attr['no_context']) && ($b == 'Entries' || $b == 'Comments')) {
            return
                '<?php if ($_ctx->exists("meta") && $_ctx->meta->rows() && ($_ctx->meta->meta_type == "serie")) { ' .
                "if (!isset(\$params)) { \$params = []; }\n" .
                "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
                "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
                "\$params['from'] .= ', '.\$core->prefix.'meta METAS ';\n" .
                "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
                "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
                "\$params['sql'] .= \"AND METAS.meta_id = '\".\$core->con->escape(\$_ctx->meta->meta_id).\"' \";\n" .
                "} ?>\n";
        }
    }

    public static function tplSysIfConditions($serie, $attr, $content, $if)
    {
        if ($serie == 'Sys' && isset($attr['in_serie'])) {
            $sign = '';
            if (substr($attr['in_serie'], 0, 1) == '!') {
                $sign             = '!';
                $attr['in_serie'] = substr($attr['in_serie'], 1);
            }
            $if[] = $sign . "(\$core->tpl->serieExists('" . addslashes($attr['in_serie']) . "') )";
        }
    }

    public static function addTplPath($core)
    {
        $tplset = $core->themes->moduleInfo($core->blog->settings->system->theme, 'tplset');
        if (!empty($tplset) && is_dir(dirname(__FILE__) . '/default-templates/' . $tplset)) {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__) . '/default-templates/' . $tplset);
        } else {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__) . '/default-templates/' . DC_DEFAULT_TPLSET);
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
            "\$_ctx->meta = \$core->meta->computeMetaStats(\$core->meta->getMetadata(['meta_type'=>'" .
            $type . "','limit'=>" . $limit .
            ($sortby != 'meta_id_lower' ? ",'order'=>'" . $sortby . ' ' . ($order == 'asc' ? 'ASC' : 'DESC') . "'" : '') .
            '])); ' .
            "\$_ctx->meta->sort('" . $sortby . "','" . $order . "'); " .
            '?>';

        $res .= '<?php while ($_ctx->meta->fetch()) : ?>' . $content . '<?php endwhile; ' .
            '$_ctx->meta = null; ?>';

        return $res;
    }

    public static function SeriesHeader($attr, $content)
    {
        return
            '<?php if ($_ctx->meta->isStart()) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    public static function SeriesFooter($attr, $content)
    {
        return
            '<?php if ($_ctx->meta->isEnd()) : ?>' .
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
            "\$_ctx->meta = \$core->meta->getMetaRecordset(\$_ctx->posts->post_meta,'" . $type . "'); " .
            "\$_ctx->meta->sort('" . $sortby . "','" . $order . "'); " .
            '?>';

        $res .= '<?php while ($_ctx->meta->fetch()) : ?>' . $content . '<?php endwhile; ' .
            '$_ctx->meta = null; ?>';

        return $res;
    }

    public static function SerieID($attr)
    {
        $f = $GLOBALS['core']->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, '$_ctx->meta->meta_id') . '; ?>';
    }

    public static function SeriePercent($attr)
    {
        return '<?php echo $_ctx->meta->percent; ?>';
    }

    public static function SerieRoundPercent($attr)
    {
        return '<?php echo $_ctx->meta->roundpercent; ?>';
    }

    public static function SerieURL($attr)
    {
        $f = $GLOBALS['core']->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, '$core->blog->url.$core->url->getURLFor("serie",' .
            'rawurlencode($_ctx->meta->meta_id))') . '; ?>';
    }

    public static function SerieCloudURL($attr)
    {
        $f = $GLOBALS['core']->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, '$core->blog->url.$core->url->getURLFor("series")') . '; ?>';
    }

    public static function SerieFeedURL($attr)
    {
        $type = !empty($attr['type']) ? $attr['type'] : 'rss2';

        if (!preg_match('#^(rss2|atom)$#', $type)) {
            $type = 'rss2';
        }

        $f = $GLOBALS['core']->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, '$core->blog->url.$core->url->getURLFor("serie_feed",' .
            'rawurlencode($_ctx->meta->meta_id)."/' . $type . '")') . '; ?>';
    }

    # Widget function
    public static function seriesWidget($w)
    {
        global $core, $_ctx;

        if ($w->offline) {
            return;
        }

        if (($w->homeonly == 1 && !$core->url->isHome($core->url->type)) || ($w->homeonly == 2 && $core->url->isHome($core->url->type))) {
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

        $rs = $core->meta->computeMetaStats(
            $core->meta->getMetadata($params)
        );

        if ($rs->isEmpty()) {
            return;
        }

        if ($sort == 'meta_id_lower') {
            // Sort resulting recordset on cleaned id
            $rs->sort($sort, $order);
        }

        $res = ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '') . '<ul>';

        if ($core->url->type == 'post' && $_ctx->posts instanceof record) {
            $_ctx->meta = $core->meta->getMetaRecordset($_ctx->posts->post_meta, 'serie');
        }
        while ($rs->fetch()) {
            $class = '';
            if ($core->url->type == 'post' && $_ctx->posts instanceof record) {
                while ($_ctx->meta->fetch()) {
                    if ($_ctx->meta->meta_id == $rs->meta_id) {
                        $class = ' class="serie-current"';

                        break;
                    }
                }
            }
            $res .= '<li' . $class . '><a href="' . $core->blog->url . $core->url->getURLFor('serie', rawurlencode($rs->meta_id)) . '" ' .
            'class="serie' . $rs->roundpercent . '">' .
            $rs->meta_id . '</a></li>';
        }

        $res .= '</ul>';

        if ($core->url->getURLFor('series') && !is_null($w->allserieslinktitle) && $w->allserieslinktitle !== '') {
            $res .= '<p><strong><a href="' . $core->blog->url . $core->url->getURLFor('series') . '">' .
            html::escapeHTML($w->allserieslinktitle) . '</a></strong></p>';
        }

        return $w->renderDiv($w->content_only, 'series ' . $w->class, '', $res);
    }

    public static function seriePostsWidget($w)
    {
        global $core, $_ctx;

        if ($w->offline) {
            return;
        }

        if ($core->url->type != 'post') {
            return;
        }

        $metas = unserialize($_ctx->posts->post_meta);
        if (isset($metas['serie'])) {
            $sql = 'SELECT * FROM ' .
            $core->prefix . 'meta as m,' .
            $core->prefix . 'post as p ' .
            ' WHERE m.post_id = p.post_id ' .
            ' AND post_type = \'post\' ' .
            ' AND post_status = 1 ' .
            ' AND blog_id = \'' . $core->blog->id . '\'' .
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
            $rs = $core->con->select($sql);
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
            if ($rs->post_id == $_ctx->posts->post_id) {
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
                    $list .= '<h3><a href="' . $core->blog->url . $core->url->getURLFor('serie', rawurlencode($rs->meta_id)) . '">' .
                    $rs->meta_id . '</a></h3>' . "\n";
                }
                $list .= '<ul>' . "\n";
                $serie = $rs->meta_id;
            }

            $list .= '<li' . $class . '>' .
            ($link ? '<a href="' . $core->blog->url . $core->getPostPublicURL($rs->post_type, html::sanitizeURL($rs->post_url)) . '">' : '') .
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
            $type     = $m[2] == 'atom' ? 'atom' : 'rss2';
            $mime     = 'application/xml';
            $comments = !empty($m[3]);

            $GLOBALS['_ctx']->meta = $GLOBALS['core']->meta->computeMetaStats(
                $GLOBALS['core']->meta->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $m[1],
                ])
            );

            if ($GLOBALS['_ctx']->meta->isEmpty()) {
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

            $GLOBALS['_ctx']->meta = $GLOBALS['core']->meta->computeMetaStats(
                $GLOBALS['core']->meta->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $args,
                ])
            );

            if ($GLOBALS['_ctx']->meta->isEmpty()) {
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

            $GLOBALS['_ctx']->meta = $GLOBALS['core']->meta->computeMetaStats(
                $GLOBALS['core']->meta->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $serie,
                ])
            );

            if ($GLOBALS['_ctx']->meta->isEmpty()) {
                # The specified serie does not exist.
                self::p404();
            } else {
                $GLOBALS['_ctx']->feed_subtitle = ' - ' . __('Serie') . ' - ' . $GLOBALS['_ctx']->meta->meta_id;

                if ($type == 'atom') {
                    $mime = 'application/atom+xml';
                } else {
                    $mime = 'application/xml';
                }

                $tpl = $type;
                if ($comments) {
                    $tpl .= '-comments';
                    $GLOBALS['_ctx']->nb_comment_per_page = $GLOBALS['core']->blog->settings->system->nb_comment_per_feed;
                } else {
                    $GLOBALS['_ctx']->nb_entry_per_page = $GLOBALS['core']->blog->settings->system->nb_post_per_feed;
                    $GLOBALS['_ctx']->short_feed_items  = $GLOBALS['core']->blog->settings->system->short_feed_items;
                }
                $tpl .= '.xml';

                self::serveDocument($tpl, $mime);
            }
        }
    }
}
