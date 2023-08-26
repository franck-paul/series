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
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;

class Manage extends Process
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && ($_REQUEST['m'] ?? 'series') === 'serie_posts' ? ManagePosts::init() : true);
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (($_REQUEST['m'] ?? 'series') === 'serie_posts') {
            return ManagePosts::process();
        }

        dcCore::app()->admin->series = dcCore::app()->meta->getMetadata(['meta_type' => 'serie']);
        dcCore::app()->admin->series = dcCore::app()->meta->computeMetaStats(dcCore::app()->admin->series);
        dcCore::app()->admin->series->sort('meta_id_lower', 'asc');

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        if (($_REQUEST['m'] ?? 'series') === 'serie_posts') {
            ManagePosts::render();

            return;
        }

        $head = My::cssLoad('style.css');

        Page::openModule(My::name(), $head);

        echo Page::breadcrumb(
            [
                Html::escapeHTML(dcCore::app()->blog->name) => '',
                __('series')                                => '',
            ]
        );
        echo Notices::getNotices();

        $last_letter = null;
        $cols        = ['', ''];
        $col         = 0;
        while (dcCore::app()->admin->series->fetch()) {
            $letter = mb_strtoupper(mb_substr(dcCore::app()->admin->series->meta_id_lower, 0, 1));

            if ($last_letter != $letter) {
                if (dcCore::app()->admin->series->index() >= round(dcCore::app()->admin->series->count() / 2)) {
                    $col = 1;
                }
                $cols[$col] .= '<tr class="serieLetter"><td colspan="2"><span>' . $letter . '</span></td></tr>';
            }

            $cols[$col] .= '<tr class="line">' .
            '<td class="maximal"><a href="' . dcCore::app()->admin->getPageURL() .
            '&amp;m=serie_posts&amp;serie=' . rawurlencode(dcCore::app()->admin->series->meta_id) . '">' . dcCore::app()->admin->series->meta_id . '</a></td>' .
            '<td class="nowrap count"><strong>' . dcCore::app()->admin->series->count . '</strong> ' .
                ((dcCore::app()->admin->series->count == 1) ? __('entry') : __('entries')) . '</td>' .
                '</tr>';

            $last_letter = $letter;
        }

        $table = '<div class="col"><table class="series">%s</table></div>';

        if ($cols[0]) {
            echo '<div class="two-cols clearfix">';
            printf($table, $cols[0]);
            if ($cols[1]) {
                printf($table, $cols[1]);
            }
            echo '</div>';
        } else {
            echo '<p>' . __('No series on this blog.') . '</p>';
        }

        Page::closeModule();
    }
}
