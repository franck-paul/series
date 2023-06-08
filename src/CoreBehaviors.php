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

class CoreBehaviors
{
    public static function coreInitWikiPost($wiki)
    {
        $wiki->registerFunction('url:serie', [static::class, 'wikiSerie']);
    }

    public static function wikiSerie($url, $content)
    {
        $res = [];
        $url = substr($url, 6);
        if (strpos($content, 'serie:') === 0) {
            $content = substr($content, 6);
        }

        $serie_url      = Html::stripHostURL(dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie'));
        $res['url']     = $serie_url . '/' . rawurlencode(dcMeta::sanitizeMetaID($url));
        $res['content'] = $content;

        return $res;
    }
}
