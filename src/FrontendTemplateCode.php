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

class FrontendTemplateCode
{
    /**
     * PHP code for tpl:Series block
     */
    public static function Series(
        string $_type_,
        mixed $_limit_,
        string $_sortby_,
        string $_order_,
        string $_content_HTML,
    ): void {
        $series_options = [
            'meta_type' => $_type_,
            'limit'     => $_limit_,
        ];
        if ($_sortby_ !== 'meta_id_lower') {
            $series_options['order'] = $_sortby_ . ' ' . ($_order_ === 'asc' ? 'ASC' : 'DESC');
        }
        App::frontend()->context()->meta = App::meta()->computeMetaStats(App::meta()->getMetadata($series_options));
        if ($_sortby_ === 'meta_id_lower') {
            App::frontend()->context()->meta->lexicalSort($_sortby_, $_order_);
        } else {
            App::frontend()->context()->meta->sort($_sortby_, $_order_);
        }
        while (App::frontend()->context()->meta->fetch()) : ?>
            $_content_HTML
        <?php endwhile;
        App::frontend()->context()->meta = null;
        unset($series_options);
    }

    /**
     * PHP code for tpl:SeriesHeader block
     */
    public static function SeriesHeader(
        string $_content_HTML
    ): void {
        if (App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && App::frontend()->context()->meta->isStart()) : ?>
            $_content_HTML
        <?php endif;
    }

    /**
     * PHP code for tpl:SeriesFooter block
     */
    public static function SeriesFooter(
        string $_content_HTML
    ): void {
        if (App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && App::frontend()->context()->meta->isEnd()) : ?>
            $_content_HTML
        <?php endif;
    }

    /**
     * PHP code for tpl:Series block
     */
    public static function EntrySeries(
        string $_type_,
        string $_sortby_,
        string $_order_,
        string $_content_HTML,
    ): void {
        if (App::frontend()->context()->posts instanceof \Dotclear\Database\MetaRecord) {
            $series_post_meta = is_string($series_post_meta = App::frontend()->context()->posts->post_meta) ? $series_post_meta : '';
            if ($series_post_meta !== '') {
                App::frontend()->context()->meta = App::meta()->getMetaRecordset($series_post_meta, $_type_);
                if ($_sortby_ === 'meta_id_lower') {
                    App::frontend()->context()->meta->lexicalSort($_sortby_, $_order_);
                } else {
                    App::frontend()->context()->meta->sort($_sortby_, $_order_);
                }
                while (App::frontend()->context()->meta->fetch()) : ?>
                $_content_HTML
                <?php endwhile;
                App::frontend()->context()->meta = null;
            }
            unset($series_post_meta);
        }
    }

    /**
     * PHP code for tpl:SerieID value
     *
     * @param      array<int|string, mixed>     $_params_  The parameters
     */
    public static function SerieID(
        array $_params_,
        string $_tag_
    ): void {
        $series_meta_id = App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && is_string($series_meta_id = App::frontend()->context()->meta->meta_id) ? $series_meta_id : '';
        echo App::frontend()->context()::global_filters(
            $series_meta_id,
            $_params_,
            $_tag_
        );
        unset($series_meta_id);
    }

    /**
     * PHP code for tpl:SerieCount value
     *
     * @param      array<int|string, mixed>     $_params_  The parameters
     */
    public static function SerieCount(
        array $_params_,
        string $_tag_
    ): void {
        $series_count = App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && is_string($series_count = App::frontend()->context()->meta->count) ? (int) $series_count : 0;
        echo App::frontend()->context()::global_filters(
            (string) $series_count,
            $_params_,
            $_tag_
        );
        unset($series_count);
    }

    /**
     * PHP code for tpl:SeriePercent value
     *
     * @param      array<int|string, mixed>     $_params_  The parameters
     */
    public static function SeriePercent(
        array $_params_,
        string $_tag_
    ): void {
        $series_percent = App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && is_string($series_percent = App::frontend()->context()->meta->percent) ? (int) $series_percent : 0;
        echo App::frontend()->context()::global_filters(
            (string) $series_percent,
            $_params_,
            $_tag_
        );
        unset($series_percent);
    }

    /**
     * PHP code for tpl:SerieRoundPercent value
     *
     * @param      array<int|string, mixed>     $_params_  The parameters
     */
    public static function SerieRoundPercent(
        array $_params_,
        string $_tag_
    ): void {
        $series_roundpercent = App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && is_string($series_roundpercent = App::frontend()->context()->meta->roundpercent) ? (int) $series_roundpercent : 0;
        echo App::frontend()->context()::global_filters(
            (string) $series_roundpercent,
            $_params_,
            $_tag_
        );
        unset($series_roundpercent);
    }

    /**
     * PHP code for tpl:SerieURL value
     *
     * @param      array<int|string, mixed>     $_params_  The parameters
     */
    public static function SerieURL(
        array $_params_,
        string $_tag_
    ): void {
        $series_meta_id = App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && is_string($series_meta_id = App::frontend()->context()->meta->meta_id) ? $series_meta_id : '';
        echo App::frontend()->context()::global_filters(
            App::blog()->url() . App::url()->getURLFor('serie', rawurlencode($series_meta_id)),
            $_params_,
            $_tag_
        );
        unset($series_meta_id);
    }

    /**
     * PHP code for tpl:SerieCloudURL value
     *
     * @param      array<int|string, mixed>     $_params_  The parameters
     */
    public static function SerieCloudURL(
        array $_params_,
        string $_tag_
    ): void {
        echo App::frontend()->context()::global_filters(
            App::blog()->url() . App::url()->getURLFor('series'),
            $_params_,
            $_tag_
        );
    }

    /**
     * PHP code for tpl:SerieFeedURL value
     *
     * @param      array<int|string, mixed>     $_params_  The parameters
     */
    public static function SerieFeedURL(
        string $_type_,
        array $_params_,
        string $_tag_
    ): void {
        $series_meta_id = App::frontend()->context()->meta instanceof \Dotclear\Database\MetaRecord && is_string($series_meta_id = App::frontend()->context()->meta->meta_id) ? $series_meta_id : '';
        echo App::frontend()->context()::global_filters(
            App::blog()->url() . App::url()->getURLFor('serie_feed', rawurlencode($series_meta_id) . '/' . $_type_),
            $_params_,
            $_tag_
        );
        unset($series_meta_id);
    }
}
