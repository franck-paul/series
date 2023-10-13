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
use dcUrlHandlers;

class FrontendUrl extends dcUrlHandlers
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
                dcCore::app()->public->setPageNumber($n);
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
