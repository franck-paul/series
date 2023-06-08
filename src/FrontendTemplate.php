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

use dcCore;

class FrontendTemplate
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

    public static function SeriePercent()
    {
        return '<?php echo dcCore::app()->ctx->meta->percent; ?>';
    }

    public static function SerieRoundPercent()
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
        $type = !empty($attr['type']) ? (string) $attr['type'] : 'rss2';

        if (!preg_match('#^(rss2|atom)$#', $type)) {
            $type = 'rss2';
        }

        $f = dcCore::app()->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'dcCore::app()->blog->url.dcCore::app()->url->getURLFor("serie_feed",' .
            'rawurlencode(dcCore::app()->ctx->meta->meta_id)."/' . $type . '")') . '; ?>';
    }
}
