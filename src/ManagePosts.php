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

use adminPostList;
use dcCore;
use dcMeta;
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;
use Exception;
use form;

class ManagePosts extends dcNsProcess
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::MANAGE) && (($_REQUEST['m'] ?? 'series') === 'serie_posts');

        return static::$init;
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->admin->serie = $_REQUEST['serie'] ?? '';

        dcCore::app()->admin->page        = !empty($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        dcCore::app()->admin->nb_per_page = 30;

        // Get posts

        $params               = [];
        $params['limit']      = [((dcCore::app()->admin->page - 1) * dcCore::app()->admin->nb_per_page), dcCore::app()->admin->nb_per_page];
        $params['no_content'] = true;
        $params['meta_id']    = dcCore::app()->admin->serie;
        $params['meta_type']  = 'serie';
        $params['post_type']  = '';

        dcCore::app()->admin->posts     = null;
        dcCore::app()->admin->post_list = null;

        try {
            dcCore::app()->admin->posts     = dcCore::app()->meta->getPostsByMeta($params);
            $counter                        = dcCore::app()->meta->getPostsByMeta($params, true);
            dcCore::app()->admin->post_list = new adminPostList(dcCore::app()->admin->posts, $counter->f(0));
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        dcCore::app()->admin->posts_actions_page = new BackendActions(
            dcCore::app()->adminurl->get('admin.plugin'),
            ['p' => My::id(), 'm' => 'serie_posts', 'series' => dcCore::app()->admin->serie]
        );

        dcCore::app()->admin->posts_actions_page_rendered = null;
        if (dcCore::app()->admin->posts_actions_page->process()) {
            dcCore::app()->admin->posts_actions_page_rendered = true;

            return true;
        }

        if (isset($_POST['new_serie_id'])) {
            // Rename a serie

            $new_id = dcMeta::sanitizeMetaID($_POST['new_serie_id']);

            try {
                if (dcCore::app()->meta->updateMeta(dcCore::app()->admin->serie, $new_id, 'serie')) {
                    dcPage::addSuccessNotice(sprintf(__('The serie “%s” has been successfully renamed to “%s”'), Html::escapeHTML(dcCore::app()->admin->serie), Html::escapeHTML($new_id)));
                    Http::redirect(dcCore::app()->admin->getPageURL() . '&m=serie_posts&serie=' . $new_id);
                }
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        if (!empty($_POST['delete']) && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_PUBLISH,
            dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            // Delete a serie

            try {
                dcCore::app()->meta->delMeta(dcCore::app()->admin->serie, 'serie');
                dcPage::addSuccessNotice(sprintf(__('The serie “%s” has been successfully deleted'), Html::escapeHTML(dcCore::app()->admin->serie)));
                Http::redirect(dcCore::app()->admin->getPageURL() . '&m=series');
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        if (dcCore::app()->admin->posts_actions_page_rendered) {
            dcCore::app()->admin->posts_actions_page->render();

            return;
        }

        $this_url = dcCore::app()->admin->getPageURL() . '&amp;m=serie_posts&amp;serie=' . rawurlencode(dcCore::app()->admin->serie);

        $head = dcPage::cssModuleLoad(My::id() . '/css/style.css', 'screen', dcCore::app()->getVersion(My::id())) .
        dcPage::jsLoad('js/_posts_list.js') .
        dcPage::jsJson('posts_series_msg', [
            'confirm_serie_delete' => sprintf(__('Are you sure you want to remove serie: “%s”?'), Html::escapeHTML(dcCore::app()->admin->serie)),
        ]) .
        dcPage::jsModuleLoad(My::id() . '/js/posts.js', dcCore::app()->getVersion(My::id())) .
        dcPage::jsConfirmClose('serie_rename');

        dcPage::openModule(__('series'), $head);

        echo dcPage::breadcrumb(
            [
                Html::escapeHTML(dcCore::app()->blog->name)                                          => '',
                __('Series')                                                                         => dcCore::app()->admin->getPageURL() . '&amp;m=series',
                __('Serie') . ' &ldquo;' . Html::escapeHTML(dcCore::app()->admin->serie) . '&rdquo;' => '',
            ]
        );
        echo dcPage::notices();

        // Form
        echo '<p><a class="back" href="' . dcCore::app()->admin->getPageURL() . '&amp;m=series">' . __('Back to series list') . '</a></p>';

        if (!dcCore::app()->error->flag()) {
            /* @phpstan-ignore-next-line */
            if (!dcCore::app()->admin->posts->isEmpty()) {
                echo
                '<div class="series-actions vertical-separator">' .
                '<h3>' . Html::escapeHTML(dcCore::app()->admin->serie) . '</h3>' .
                '<form action="' . $this_url . '" method="post" id="serie_rename">' .
                '<p><label for="new_serie_id" class="classic">' . __('Rename:') . '</label> ' .
                form::field('new_serie_id', 40, 255, Html::escapeHTML(dcCore::app()->admin->serie)) .
                '<input type="submit" value="' . __('OK') . '" />' .
                dcCore::app()->formNonce() .
                    '</p></form>';
                # Remove serie
                /* @phpstan-ignore-next-line */
                if (!dcCore::app()->admin->posts->isEmpty() && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                    dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
                ]), dcCore::app()->blog->id)) {
                    echo
                    '<form id="serie_delete" action="' . $this_url . '" method="post">' .
                    '<p>' . '<input type="submit" class="delete" name="delete" value="' . __('Delete this serie') . '" />' .
                    dcCore::app()->formNonce() .
                        '</p></form>';
                }
                echo '</div>';
            }

            # Show posts
            echo '<h4 class="vertical-separator pretty-title">' . __('List of entries in this serie') . '</h4>';
            /* @phpstan-ignore-next-line */
            dcCore::app()->admin->post_list->display(
                dcCore::app()->admin->page,
                dcCore::app()->admin->nb_per_page,
                '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="form-entries">' .

                '%s' .

                '<div class="two-cols">' .
                '<p class="col checkboxes-helpers"></p>' .

                '<p class="col right"><label for="action" class="classic">' . __('Selected entries action:') . '</label> ' .
                form::combo('action', dcCore::app()->admin->posts_actions_page->getCombo()) .
                '<input type="submit" value="' . __('ok') . '" /></p>' .
                form::hidden('post_type', '') .
                form::hidden('m', 'serie_posts') .
                form::hidden('serie', dcCore::app()->admin->serie) .
                dcCore::app()->formNonce() .
                '</div>' .
                '</form>'
            );
        }

        dcPage::closeModule();
    }
}
