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
use Dotclear\Core\Backend\Listing\ListingPosts;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Exception;
use form;

class ManagePosts extends Process
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && (($_REQUEST['m'] ?? 'series') === 'serie_posts'));
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::backend()->serie = $_REQUEST['serie'] ?? '';

        App::backend()->page        = !empty($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        App::backend()->nb_per_page = 30;

        // Get posts

        $params               = [];
        $params['limit']      = [((App::backend()->page - 1) * App::backend()->nb_per_page), App::backend()->nb_per_page];
        $params['no_content'] = true;
        $params['meta_id']    = App::backend()->serie;
        $params['meta_type']  = 'serie';
        $params['post_type']  = '';

        App::backend()->posts     = null;
        App::backend()->post_list = null;

        try {
            App::backend()->posts     = App::meta()->getPostsByMeta($params);
            $counter                  = App::meta()->getPostsByMeta($params, true);
            App::backend()->post_list = new ListingPosts(App::backend()->posts, $counter->f(0));
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        App::backend()->posts_actions_page = new BackendActions(
            App::backend()->url()->get('admin.plugin'),
            ['p' => My::id(), 'm' => 'serie_posts', 'series' => App::backend()->serie]
        );

        App::backend()->posts_actions_page_rendered = null;
        if (App::backend()->posts_actions_page->process()) {
            App::backend()->posts_actions_page_rendered = true;

            return true;
        }

        if (isset($_POST['new_serie_id'])) {
            // Rename a serie

            $new_id = App::meta()->sanitizeMetaID($_POST['new_serie_id']);

            try {
                if (App::meta()->updateMeta(App::backend()->serie, $new_id, 'serie')) {
                    Notices::addSuccessNotice(sprintf(__('The serie “%s” has been successfully renamed to “%s”'), Html::escapeHTML(App::backend()->serie), Html::escapeHTML($new_id)));
                    My::redirect([
                        'm'     => 'serie_posts',
                        'serie' => $new_id,
                    ]);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        if (!empty($_POST['delete']) && App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_PUBLISH,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            // Delete a serie

            try {
                App::meta()->delMeta(App::backend()->serie, 'serie');
                Notices::addSuccessNotice(sprintf(__('The serie “%s” has been successfully deleted'), Html::escapeHTML(App::backend()->serie)));
                My::redirect([
                    'm' => 'series',
                ]);
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

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

        if (App::backend()->posts_actions_page_rendered) {
            App::backend()->posts_actions_page->render();

            return;
        }

        $this_url = App::backend()->getPageURL() . '&amp;m=serie_posts&amp;serie=' . rawurlencode(App::backend()->serie);

        $head = My::cssLoad('style.css') .
        Page::jsLoad('js/_posts_list.js') .
        Page::jsJson('posts_series_msg', [
            'confirm_serie_delete' => sprintf(__('Are you sure you want to remove serie: “%s”?'), Html::escapeHTML(App::backend()->serie)),
        ]) .
        My::jsLoad('posts.js') .
        Page::jsConfirmClose('serie_rename');

        Page::openModule(My::name(), $head);

        echo Page::breadcrumb(
            [
                Html::escapeHTML(App::blog()->name())                                          => '',
                __('Series')                                                                   => App::backend()->getPageURL() . '&amp;m=series',
                __('Serie') . ' &ldquo;' . Html::escapeHTML(App::backend()->serie) . '&rdquo;' => '',
            ]
        );
        echo Notices::getNotices();

        // Form
        echo '<p><a class="back" href="' . App::backend()->getPageURL() . '&amp;m=series">' . __('Back to series list') . '</a></p>';

        if (!App::error()->flag()) {
            /* @phpstan-ignore-next-line */
            if (!App::backend()->posts->isEmpty()) {
                echo
                '<div class="series-actions vertical-separator">' .
                '<h3>' . Html::escapeHTML(App::backend()->serie) . '</h3>' .
                '<form action="' . $this_url . '" method="post" id="serie_rename">' .
                '<p><label for="new_serie_id" class="classic">' . __('Rename:') . '</label> ' .
                form::field('new_serie_id', 40, 255, Html::escapeHTML(App::backend()->serie)) .
                '<input type="submit" value="' . __('OK') . '" />' .
                My::parsedHiddenFields() .
                '</p></form>';
                # Remove serie
                /* @phpstan-ignore-next-line */
                if (!App::backend()->posts->isEmpty() && App::auth()->check(App::auth()->makePermissions([
                    App::auth()::PERMISSION_CONTENT_ADMIN,
                ]), App::blog()->id())) {
                    echo
                    '<form id="serie_delete" action="' . $this_url . '" method="post">' .
                    '<p>' . '<input type="submit" class="delete" name="delete" value="' . __('Delete this serie') . '" />' .
                    My::parsedHiddenFields() .
                    '</p></form>';
                }
                echo '</div>';
            }

            # Show posts
            echo '<h4 class="vertical-separator pretty-title">' . __('List of entries in this serie') . '</h4>';
            /* @phpstan-ignore-next-line */
            App::backend()->post_list->display(
                App::backend()->page,
                App::backend()->nb_per_page,
                '<form action="' . App::backend()->getPageURL() . '" method="post" id="form-entries">' .

                '%s' .

                '<div class="two-cols">' .
                '<p class="col checkboxes-helpers"></p>' .

                '<p class="col right"><label for="action" class="classic">' . __('Selected entries action:') . '</label> ' .
                form::combo('action', App::backend()->posts_actions_page->getCombo()) .
                '<input type="submit" value="' . __('ok') . '" /></p>' .
                My::parsedHiddenFields([
                    'post_type' => '',
                    'm'         => 'serie_posts',
                    'serie'     => App::backend()->serie,
                ]) .
                '</div>' .
                '</form>'
            );
        }

        Page::closeModule();
    }
}
