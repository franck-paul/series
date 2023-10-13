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
use dcMeta;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\WikiToHtml;

class CoreBehaviors
{
    public static function coreInitWikiPost(WikiToHtml $wiki): string
    {
        $wiki->registerFunction('url:serie', static::wikiSerie(...));

        return '';
    }

    /**
     * @param      string  $url      The url
     * @param      string  $content  The content
     *
     * @return     array<string, string>
     */
    public static function wikiSerie(string $url, string $content): array
    {
        $res = [];
        $url = substr($url, 6);
        if (str_starts_with($content, 'serie:')) {
            $content = substr($content, 6);
        }

        $serie_url      = Html::stripHostURL(dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie'));
        $res['url']     = $serie_url . '/' . rawurlencode(dcMeta::sanitizeMetaID($url));
        $res['content'] = $content;

        return $res;
    }
}
