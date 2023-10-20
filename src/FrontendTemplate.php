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

use ArrayObject;
use Dotclear\App;

class FrontendTemplate
{
    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     *
     * @return     string
     */
    public static function Series(array|ArrayObject $attr, string $content): string
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
            "App::frontend()->context()->meta = App::meta()->computeMetaStats(App::meta()->getMetadata(['meta_type'=>'" .
            $type . "','limit'=>" . $limit .
            ($sortby != 'meta_id_lower' ? ",'order'=>'" . $sortby . ' ' . ($order == 'asc' ? 'ASC' : 'DESC') . "'" : '') .
            '])); ' .
            "App::frontend()->context()->meta->sort('" . $sortby . "','" . $order . "'); " .
            '?>';

        $res .= '<?php while (App::frontend()->context()->meta->fetch()) : ?>' . $content . '<?php endwhile; ' .
            'App::frontend()->context()->meta = null; ?>';

        return $res;
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     *
     * @return     string
     */
    public static function SeriesHeader(array|ArrayObject $attr, string $content): string
    {
        return
            '<?php if (App::frontend()->context()->meta->isStart()) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     *
     * @return     string
     */
    public static function SeriesFooter(array|ArrayObject $attr, string $content): string
    {
        return
            '<?php if (App::frontend()->context()->meta->isEnd()) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     *
     * @return     string
     */
    public static function EntrySeries(array|ArrayObject $attr, string $content): string
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
            "App::frontend()->context()->meta = App::meta()->getMetaRecordset(App::frontend()->context()->posts->post_meta,'" . $type . "'); " .
            "App::frontend()->context()->meta->sort('" . $sortby . "','" . $order . "'); " .
            '?>';

        $res .= '<?php while (App::frontend()->context()->meta->fetch()) : ?>' . $content . '<?php endwhile; ' .
            'App::frontend()->context()->meta = null; ?>';

        return $res;
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     *
     * @return     string
     */
    public static function SerieID(array|ArrayObject $attr): string
    {
        $f = App::frontend()->template()->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'App::frontend()->context()->meta->meta_id') . '; ?>';
    }

    public static function SeriePercent(): string
    {
        return '<?php echo App::frontend()->context()->meta->percent; ?>';
    }

    public static function SerieRoundPercent(): string
    {
        return '<?php echo App::frontend()->context()->meta->roundpercent; ?>';
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     *
     * @return     string
     */
    public static function SerieURL(array|ArrayObject $attr): string
    {
        $f = App::frontend()->template()->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'App::blog()->url().App::url()->getURLFor("serie",' .
            'rawurlencode(App::frontend()->context()->meta->meta_id))') . '; ?>';
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     *
     * @return     string
     */
    public static function SerieCloudURL(array|ArrayObject $attr): string
    {
        $f = App::frontend()->template()->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'App::blog()->url().App::url()->getURLFor("series")') . '; ?>';
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     *
     * @return     string
     */
    public static function SerieFeedURL(array|ArrayObject $attr): string
    {
        $type = !empty($attr['type']) ? (string) $attr['type'] : 'rss2';

        if (!preg_match('#^(rss2|atom)$#', $type)) {
            $type = 'rss2';
        }

        $f = App::frontend()->template()->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'App::blog()->url().App::url()->getURLFor("serie_feed",' .
            'rawurlencode(App::frontend()->context()->meta->meta_id)."/' . $type . '")') . '; ?>';
    }
}
