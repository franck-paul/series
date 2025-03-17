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
use Dotclear\Plugin\TemplateHelper\Code;

class FrontendTemplate
{
    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     * @param      string                                            $content   The content
     */
    public static function Series(array|ArrayObject $attr, string $content): string
    {
        $type  = isset($attr['type']) ? addslashes((string) $attr['type']) : 'serie';
        $limit = isset($attr['limit']) ? (int) $attr['limit'] : null;
        $combo = ['meta_id_lower', 'count', 'latest', 'oldest'];

        $sortby = 'meta_id_lower';
        if (isset($attr['sortby']) && in_array($attr['sortby'], $combo)) {
            $sortby = mb_strtolower((string) $attr['sortby']);
        }

        $order = 'asc';
        if (isset($attr['order']) && $attr['order'] == 'desc') {
            $order = 'desc';
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
        $type   = isset($attr['type']) ? addslashes((string) $attr['type']) : 'serie';
        $combo  = ['meta_id_lower', 'count', 'latest', 'oldest'];
        $sortby = 'meta_id_lower';
        if (isset($attr['sortby']) && in_array($attr['sortby'], $combo)) {
            $sortby = mb_strtolower((string) $attr['sortby']);
        }

        $order = 'asc';
        if (isset($attr['order']) && $attr['order'] == 'desc') {
            $order = 'desc';
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
        $type = empty($attr['type']) ? 'rss2' : (string) $attr['type'];

        if (!preg_match('#^(rss2|atom)$#', $type)) {
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
