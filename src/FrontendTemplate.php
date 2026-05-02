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

use ArrayObject;
use Dotclear\Plugin\TemplateHelper\Code;

class FrontendTemplate
{
    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     */
    public static function Series(array|ArrayObject $attr, string $content): string
    {
        $combo_sort  = ['meta_id_lower', 'count', 'latest', 'oldest'];
        $combo_order = ['asc', 'desc'];

        $type   = isset($attr['type'])   && is_string($type = $attr['type']) ? addslashes($type) : 'serie';
        $limit  = isset($attr['limit'])  && is_numeric($limit = $attr['limit']) ? (int) $limit : null;
        $sortby = isset($attr['sortby']) && is_string($sortby = $attr['sortby']) ? mb_strtolower($sortby) : 'meta_id_lower';
        $order  = isset($attr['order'])  && is_string($order = $attr['order']) ? mb_strtolower($order) : 'asc';

        if (!in_array($sortby, $combo_sort, true)) {
            $sortby = 'meta_id_lower';
        }

        if (!in_array($order, $combo_order, true)) {
            $order = 'asc';
        }

        return Code::getPHPTemplateBlockCode(
            FrontendTemplateCode::Series(...),
            [
                $type,
                $limit,
                $sortby,
                $order,
            ],
            $content,
            $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     */
    public static function SeriesHeader(array|ArrayObject $attr, string $content): string
    {
        return Code::getPHPTemplateBlockCode(
            FrontendTemplateCode::SeriesHeader(...),
            [],
            $content,
            $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     */
    public static function SeriesFooter(array|ArrayObject $attr, string $content): string
    {
        return Code::getPHPTemplateBlockCode(
            FrontendTemplateCode::SeriesFooter(...),
            [],
            $content,
            $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     */
    public static function EntrySeries(array|ArrayObject $attr, string $content): string
    {
        $combo_sort  = ['meta_id_lower', 'count', 'latest', 'oldest'];
        $combo_order = ['asc', 'desc'];

        $type   = isset($attr['type'])   && is_string($type = $attr['type']) ? addslashes($type) : 'serie';
        $sortby = isset($attr['sortby']) && is_string($sortby = $attr['sortby']) ? mb_strtolower($sortby) : 'meta_id_lower';
        $order  = isset($attr['order'])  && is_string($order = $attr['order']) ? mb_strtolower($order) : 'asc';

        if (!in_array($sortby, $combo_sort, true)) {
            $sortby = 'meta_id_lower';
        }

        if (!in_array($order, $combo_order, true)) {
            $order = 'asc';
        }

        return Code::getPHPTemplateBlockCode(
            FrontendTemplateCode::EntrySeries(...),
            [
                $type,
                $sortby,
                $order,
            ],
            $content,
            $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function SerieID(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::SerieID(...),
            attr: $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function SerieCount(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::SerieCount(...),
            attr: $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function SeriePercent(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::SeriePercent(...),
            attr: $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function SerieRoundPercent(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::SerieRoundPercent(...),
            attr: $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function SerieURL(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::SerieURL(...),
            attr: $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function SerieCloudURL(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::SerieCloudURL(...),
            attr: $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function SerieFeedURL(array|ArrayObject $attr): string
    {
        $combo_type = ['rss2', 'atom'];

        $type = isset($attr['type']) && is_string($type = $attr['type']) ? mb_strtolower($type) : 'serie';

        if (!in_array($type, $combo_type, true)) {
            $type = 'rss2';
        }

        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::SerieFeedURL(...),
            [
                $type,
            ],
            attr: $attr,
        );
    }
}
