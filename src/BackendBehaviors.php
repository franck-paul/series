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
use dcAuth;
use dcCore;
use dcFavorites;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Html;
use Exception;
use form;

class BackendBehaviors
{
    public static function adminDashboardFavorites(dcFavorites $favs): string
    {
        $favs->register('series', [
            'title'       => __('Series'),
            'url'         => My::manageUrl(),
            'small-icon'  => My::icons(),
            'large-icon'  => My::icons(),
            'permissions' => My::checkContext(My::MENU),
        ]);

        return '';
    }

    /**
     * @return     array<string, string>
     */
    public static function adminSimpleMenuGetCombo(): array
    {
        /**
         * @var        array<string, string>
         */
        $series_combo = [];

        try {
            $rs                             = dcCore::app()->meta->getMetadata(['meta_type' => 'serie']);
            $series_combo[__('All series')] = '-';
            while ($rs->fetch()) {
                $series_combo[(string) $rs->meta_id] = (string) $rs->meta_id;
            }
            unset($rs);
        } catch (Exception) {
        }

        return $series_combo;
    }

    /**
     * @param      ArrayObject<string, ArrayObject<string, bool>>  $items  The items
     *
     * @return     string
     */
    public static function adminSimpleMenuAddType(ArrayObject $items): string
    {
        $series_combo = self::adminSimpleMenuGetCombo();
        if (count($series_combo) > 1) {
            /**
             * @var        ArrayObject<string, bool>
             */
            $menu            = new ArrayObject([__('Series'), true]);
            $items['series'] = $menu;
        }

        return '';
    }

    public static function adminSimpleMenuSelect(string $item_type, string $id): string
    {
        if ($item_type == 'series') {
            $series_combo = self::adminSimpleMenuGetCombo();

            return '<p class="field"><label for="item_select" class="classic">' . __('Select serie (if necessary):') . '</label>' .
            form::combo($id, $series_combo);
        }

        return '';
    }

    /**
     * @param      string               $item_type    The item type
     * @param      string               $item_select  The item select
     * @param      array<int, string>   $menu_item    The menu item
     */
    public static function adminSimpleMenuBeforeEdit(string $item_type, string $item_select, array $menu_item): string
    {
        if ($item_type == 'series') {
            $series_combo = self::adminSimpleMenuGetCombo();
            $menu_item[3] = array_search($item_select, $series_combo);
            if ($item_select == '-') {
                $menu_item[0] = __('All series');
                $menu_item[1] = '';
                $menu_item[2] .= dcCore::app()->url->getURLFor('series');
            } else {
                $menu_item[0] = $menu_item[3];
                $menu_item[1] = sprintf(__('Recent posts for %s serie'), $menu_item[3]);
                $menu_item[2] .= dcCore::app()->url->getURLFor('serie', $item_select);
            }
        }

        return '';
    }

    /**
     * @param      ArrayObject<string, mixed>           $main     The main
     * @param      ArrayObject<string, mixed>           $sidebar  The sidebar
     * @param      \Dotclear\Database\MetaRecord|null   $post     The post
     */
    public static function seriesField(ArrayObject $main, ArrayObject $sidebar, ?MetaRecord $post): string
    {
        if (!empty($_POST['post_series'])) {
            $value = $_POST['post_series'];
        } else {
            $value = ($post) ? dcCore::app()->meta->getMetaStr($post->post_meta, 'serie') : '';
        }

        $sidebar['metas-box']['items']['post_series'] = '<h5><label class="s-series" for="post_series">' . __('Series:') . '</label></h5>' .
        '<div class="p s-series" id="series-edit">' . form::textarea('post_series', 20, 3, $value, 'maximal') . '</div>';

        return '';
    }

    public static function setSeries(Cursor $cur, int $post_id): string
    {
        if (isset($_POST['post_series'])) {
            $series = $_POST['post_series'];
            dcCore::app()->meta->delPostMeta($post_id, 'serie');

            foreach (dcCore::app()->meta->splitMetaValues($series) as $serie) {
                dcCore::app()->meta->setPostMeta($post_id, 'serie', $serie);
            }
        }

        return '';
    }

    public static function adminPostsActions(ActionsPosts $ap): string
    {
        $ap->addAction(
            [__('Series') => [__('Add series') => 'series']],
            static::adminAddSeries(...)
        );

        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_DELETE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Series') => [__('Remove series') => 'series_remove']],
                static::adminRemoveSeries(...)
            );
        }

        return '';
    }

    /**
     * @param      ActionsPosts                 $ap     Actions
     * @param      ArrayObject<string, mixed>   $post   The post
     */
    public static function adminAddSeries(ActionsPosts $ap, ArrayObject $post): void
    {
        if (!empty($post['new_series'])) {
            $series = dcCore::app()->meta->splitMetaValues($_POST['new_series']);
            $posts  = $ap->getRS();

            while ($posts->fetch()) {
                # Get series for post
                $post_meta = dcCore::app()->meta->getMetadata([
                    'meta_type' => 'serie',
                    'post_id'   => $posts->post_id,
                ]);
                $pm = [];
                while ($post_meta->fetch()) {
                    $pm[] = $post_meta->meta_id;
                }

                foreach ($series as $s) {
                    if (!in_array($s, $pm)) {
                        dcCore::app()->meta->setPostMeta($posts->post_id, 'serie', $s);
                    }
                }
            }
            Notices::addSuccessNotice(
                sprintf(
                    __(
                        'Serie has been successfully added to selected entries',
                        'Series have been successfully added to selected entries',
                        count($series)
                    )
                )
            );
            $ap->redirect(true, ['upd' => 1]);
        } else {
            $opts = dcCore::app()->auth->getOptions();
            $type = $opts['serie_list_format'] ?? 'more';

            $editor_series_options = [
                'meta_url' => dcCore::app()->adminurl->get('admin.plugin.' . My::id(), [
                    'm'     => 'serie_posts',
                    'serie' => '',
                ]),
                'list_type'           => $type,
                'text_confirm_remove' => __('Are you sure you want to remove this serie?'),
                'text_add_meta'       => __('Add a serie to this entry'),
                'text_choose'         => __('Choose from list'),
                'text_all'            => __('all series'),
                'text_separation'     => __('Enter series separated by comma'),
            ];

            $msg = [
                'series_autocomplete' => __('used in %e - frequency %p%'),
                'entry'               => __('entry'),
                'entries'             => __('entries'),
            ];

            $ap->beginPage(
                Page::breadcrumb(
                    [
                        Html::escapeHTML(dcCore::app()->blog->name) => '',
                        __('Entries')                               => $ap->getRedirection(true),
                        __('Add series to this selection')          => '',
                    ]
                ),
                Page::jsLoad('js/jquery/jquery.autocomplete.js') .
                Page::jsMetaEditor() .
                Page::jsJson('editor_series_options', $editor_series_options) .
                Page::jsJson('editor_series_msg', $msg) .
                Page::jsLoad('js/jquery/jquery.autocomplete.js') .
                My::jsLoad('posts_actions.js') .
                My::cssLoad('style.css')
            );
            echo
            '<form action="' . $ap->getURI() . '" method="post">' .
            $ap->getCheckboxes() .
            '<div><label for="new_series" class="area">' . __('Series to add:') . '</label> ' .
            form::textarea('new_series', 60, 3) .
            '</div>' .
            $ap->getHiddenFields() .
            My::parsedHiddenFields([
                'action' => 'series',
            ]) .
            '<p><input type="submit" value="' . __('Save') . '" ' .
                'name="save_series" /></p>' .
                '</form>';
            $ap->endPage();
        }
    }

    /**
     * @param      ActionsPosts                 $ap     Actions
     * @param      ArrayObject<string, mixed>   $post   The post
     */
    public static function adminRemoveSeries(ActionsPosts $ap, ArrayObject $post): void
    {
        if (!empty($post['meta_id']) && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_DELETE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $posts = $ap->getRS();
            while ($posts->fetch()) {
                foreach ($_POST['meta_id'] as $v) {
                    dcCore::app()->meta->delPostMeta($posts->post_id, 'serie', $v);
                }
            }
            $ap->redirect(true, ['upd' => 1]);
        } else {
            $series = [];

            foreach ($ap->getIDS() as $id) {
                $post_series = dcCore::app()->meta->getMetadata([
                    'meta_type' => 'serie',
                    'post_id'   => (int) $id, ])->toStatic()->rows();
                foreach ($post_series as $v) {
                    if (isset($series[$v['meta_id']])) {
                        $series[$v['meta_id']]++;
                    } else {
                        $series[$v['meta_id']] = 1;
                    }
                }
            }
            if (empty($series)) {
                throw new Exception(__('No series for selected entries'));
            }
            $ap->beginPage(
                Page::breadcrumb(
                    [
                        Html::escapeHTML(dcCore::app()->blog->name)      => '',
                        __('Entries')                                    => dcCore::app()->adminurl->get('admin.posts'),
                        __('Remove selected series from this selection') => '',
                    ]
                )
            );
            $posts_count = is_countable($_POST['entries']) ? count($_POST['entries']) : 0;

            echo
            '<form action="' . $ap->getURI() . '" method="post">' .
            $ap->getCheckboxes() .
            '<div><p>' . __('Following series have been found in selected entries:') . '</p>';

            foreach ($series as $k => $n) {
                $label = '<label class="classic">%s %s</label>';
                if ($posts_count == $n) {
                    $label = sprintf($label, '%s', '<strong>%s</strong>');
                }
                echo '<p>' . sprintf(
                    $label,
                    form::checkbox(['meta_id[]'], Html::escapeHTML((string) $k)),
                    Html::escapeHTML((string) $k)
                ) . '</p>';
            }

            echo
            '<p><input type="submit" value="' . __('ok') . '" />' .
            $ap->getHiddenFields() .
            My::parsedHiddenFields([
                'action' => 'series_remove',
            ]) .
            '</p></div></form>';
            $ap->endPage();
        }
    }

    public static function postHeaders(): string
    {
        $opts = dcCore::app()->auth->getOptions();
        $type = $opts['serie_list_format'] ?? 'more';

        $editor_series_options = [
            'meta_url' => dcCore::app()->adminurl->get('admin.plugin.' . My::id(), [
                'm'     => 'serie_posts',
                'serie' => '',
            ]),
            'list_type'           => $type,
            'text_confirm_remove' => __('Are you sure you want to remove this serie?'),
            'text_add_meta'       => __('Add a serie to this entry'),
            'text_choose'         => __('Choose from list'),
            'text_all'            => __('all series'),
            'text_separation'     => __('Enter series separated by comma'),
        ];

        $msg = [
            'series_autocomplete' => __('used in %e - frequency %p%'),
            'entry'               => __('entry'),
            'entries'             => __('entries'),
        ];

        return
        Page::jsJson('editor_series_options', $editor_series_options) .
        Page::jsJson('editor_series_msg', $msg) .
        Page::jsLoad('js/jquery/jquery.autocomplete.js') .
        My::jsLoad('post.js') .
        My::cssLoad('style.css');
    }

    public static function adminPostEditor(string $editor = '', string $context = ''): string
    {
        if (($editor != 'dcLegacyEditor' && $editor != 'dcCKEditor') || $context != 'post') {
            return '';
        }

        $serie_url = dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie');

        if ($editor == 'dcLegacyEditor') {
            return
            Page::jsJson('legacy_editor_series', [
                'serie' => [
                    'title' => __('Serie'),
                    'url'   => $serie_url,
                    'icon'  => urldecode(Page::getPF(My::id() . '/icon.svg')),
                ],
            ]) .
            My::jsLoad('legacy-post.js');
        } elseif ($editor == 'dcCKEditor') {
            return
            Page::jsJson('ck_editor_series', [
                'serie_title' => __('Serie'),
                'serie_url'   => $serie_url,
            ]);
        }
    }

    /**
     * @param      ArrayObject<int, mixed>  $extraPlugins  The extra plugins
     * @param      string                   $context       The context
     *
     * @return     string
     */
    public static function ckeditorExtraPlugins(ArrayObject $extraPlugins, string $context): string
    {
        if ($context !== 'post') {
            return '';
        }
        $extraPlugins[] = [
            'name'   => 'dcseries',
            'button' => 'dcSeries',
            'url'    => urldecode(DC_ADMIN_URL . Page::getPF(My::id() . '/js/ckeditor-series-plugin.js')),
        ];

        return '';
    }

    public static function adminPreferencesForm(): string
    {
        $opts = dcCore::app()->auth->getOptions();

        $combo                 = [];
        $combo[__('Short')]    = 'more';
        $combo[__('Extended')] = 'all';

        $value = array_key_exists('serie_list_format', $opts) ? $opts['serie_list_format'] : 'more';

        echo
        '<div class="fieldset"><h5 id="series_prefs">' . __('Series') . '</h5>' .
        '<p><label for="user_serie_list_format" class="classic">' . __('Series list format:') . '</label> ' .
        form::combo('user_serie_list_format', $combo, $value) .
        '</p></div>';

        return '';
    }

    public static function adminUserForm(?MetaRecord $rs): string
    {
        if ($rs instanceof MetaRecord) {
            $opts = $rs->options();
        } else {
            $opts = [];
        }

        $combo                 = [];
        $combo[__('Short')]    = 'more';
        $combo[__('Extended')] = 'all';

        $value = array_key_exists('serie_list_format', $opts) ? $opts['serie_list_format'] : 'more';

        echo
        '<div class="fieldset"><h5 id="series_prefs">' . __('Series') . '</h5>' .
        '<p><label for="user_serie_list_format" class="classic">' . __('Series list format:') . '</label> ' .
        form::combo('user_serie_list_format', $combo, $value) .
        '</p></div>';

        return '';
    }

    public static function setSerieListFormat(Cursor $cur, ?string $user_id = null): string
    {
        if (!is_null($user_id)) {
            $cur->user_options['serie_list_format'] = $_POST['user_serie_list_format'];
        }

        return '';
    }
}
