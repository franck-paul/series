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

use Dotclear\App;
use Dotclear\Core\Frontend\Url;

class FrontendUrl extends Url
{
    /**
     * @param      null|string  $args   The arguments
     */
    public static function serie(?string $args): void
    {
        $n = self::getPageNumber($args);

        if ($args == '' && !$n) {
            self::p404();
        } elseif (preg_match('%(.*?)/feed/(rss2|atom)?$%u', (string) $args, $m)) {
            $type = $m[2] == 'atom' ? 'atom' : 'rss2';
            $mime = 'application/xml';

            App::frontend()->context()->meta = App::meta()->computeMetaStats(
                App::meta()->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $m[1],
                ])
            );

            if (App::frontend()->context()->meta->isEmpty()) {
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
                App::frontend()->setPageNumber($n);
            }

            App::frontend()->context()->meta = App::meta()->computeMetaStats(
                App::meta()->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $args,
                ])
            );

            if (App::frontend()->context()->meta->isEmpty()) {
                self::p404();
            } else {
                self::serveDocument('serie.html');
            }
        }
    }

    public static function series(): void
    {
        self::serveDocument('series.html');
    }

    /**
     * @param      null|string  $args   The arguments
     */
    public static function serieFeed(?string $args): void
    {
        if (!preg_match('#^(.+)/(atom|rss2)(/comments)?$#', (string) $args, $m)) {
            self::p404();
        } else {
            $serie    = $m[1];
            $type     = $m[2];
            $comments = !empty($m[3]);

            App::frontend()->context()->meta = App::meta()->computeMetaStats(
                App::meta()->getMetadata([
                    'meta_type' => 'serie',
                    'meta_id'   => $serie,
                ])
            );

            if (App::frontend()->context()->meta->isEmpty()) {
                # The specified serie does not exist.
                self::p404();
            } else {
                App::frontend()->context()->feed_subtitle = ' - ' . __('Serie') . ' - ' . App::frontend()->context()->meta->meta_id;

                $mime = $type == 'atom' ? 'application/atom+xml' : 'application/xml';

                $tpl = $type;
                if ($comments) {
                    $tpl .= '-comments';
                    App::frontend()->context()->nb_comment_per_page = App::blog()->settings()->system->nb_comment_per_feed;
                } else {
                    App::frontend()->context()->nb_entry_per_page = App::blog()->settings()->system->nb_post_per_feed;
                    App::frontend()->context()->short_feed_items  = App::blog()->settings()->system->short_feed_items;
                }

                $tpl .= '.xml';

                self::serveDocument($tpl, $mime);
            }
        }
    }
}
