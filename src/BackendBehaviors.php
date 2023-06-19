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
use dcPage;
use dcPostsActions;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Html;
use Exception;
use form;

class BackendBehaviors
{
    public static function adminDashboardFavorites($favs)
    {
        $favs->register('series', [
            'title'      => __('Series'),
            'url'        => My::makeUrl(),
            'small-icon' => My::icons(),
            'large-icon' => My::icons(),
            My::checkContext(My::MENU),
        ]);
    }

    public static function adminSimpleMenuGetCombo()
    {
        $series_combo = [];

        try {
            $rs                             = dcCore::app()->meta->getMetadata(['meta_type' => 'serie']);
            $series_combo[__('All series')] = '-';
            while ($rs->fetch()) {
                $series_combo[$rs->meta_id] = $rs->meta_id;
            }
            unset($rs);
        } catch (Exception $e) {
            // Ignore exceptions
        }

        return $series_combo;
    }

    public static function adminSimpleMenuAddType($items)
    {
        $series_combo = self::adminSimpleMenuGetCombo();
        if ((is_countable($series_combo) ? count($series_combo) : 0) > 1) {
            $items['series'] = new ArrayObject([__('Series'), true]);
        }
    }

    public static function adminSimpleMenuSelect($item_type)
    {
        if ($item_type == 'series') {
            $series_combo = self::adminSimpleMenuGetCombo();

            return '<p class="field"><label for="item_select" class="classic">' . __('Select serie (if necessary):') . '</label>' .
            form::combo('item_select', $series_combo);
        }
    }

    public static function adminSimpleMenuBeforeEdit($item_type, $item_select, $menu_item)
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
    }

    public static function seriesField($main, $sidebar, $post)
    {
        if (!empty($_POST['post_series'])) {
            $value = $_POST['post_series'];
        } else {
            $value = ($post) ? dcCore::app()->meta->getMetaStr($post->post_meta, 'serie') : '';
        }

        $sidebar['metas-box']['items']['post_series'] = '<h5><label class="s-series" for="post_series">' . __('Series:') . '</label></h5>' .
        '<div class="p s-series" id="series-edit">' . form::textarea('post_series', 20, 3, $value, 'maximal') . '</div>';
    }

    public static function setSeries($cur, $post_id)
    {
        $post_id = (int) $post_id;

        if (isset($_POST['post_series'])) {
            $series = $_POST['post_series'];
            dcCore::app()->meta->delPostMeta($post_id, 'serie');

            foreach (dcCore::app()->meta->splitMetaValues($series) as $serie) {
                dcCore::app()->meta->setPostMeta($post_id, 'serie', $serie);
            }
        }
    }

    public static function adminPostsActions(dcPostsActions $ap)
    {
        $ap->addAction(
            [__('Series') => [__('Add series') => 'series']],
            [static::class, 'adminAddSeries']
        );

        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_DELETE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Series') => [__('Remove series') => 'series_remove']],
                [static::class, 'adminRemoveSeries']
            );
        }
    }

    public static function adminAddSeries(dcPostsActions $ap, ArrayObject $post)
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
            dcPage::addSuccessNotice(
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
                dcPage::breadcrumb(
                    [
                        Html::escapeHTML(dcCore::app()->blog->name) => '',
                        __('Entries')                               => $ap->getRedirection(true),
                        __('Add series to this selection')          => '',
                    ]
                ),
                dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
                dcPage::jsMetaEditor() .
                dcPage::jsJson('editor_series_options', $editor_series_options) .
                dcPage::jsJson('editor_series_msg', $msg) .
                dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
                dcPage::jsModuleLoad('series/js/posts_actions.js', dcCore::app()->getVersion('series')) .
                dcPage::cssModuleLoad('series/css/style.css', 'screen', dcCore::app()->getVersion('series'))
            );
            echo
            '<form action="' . $ap->getURI() . '" method="post">' .
            $ap->getCheckboxes() .
            '<div><label for="new_series" class="area">' . __('Series to add:') . '</label> ' .
            form::textarea('new_series', 60, 3) .
            '</div>' .
            dcCore::app()->formNonce() . $ap->getHiddenFields() .
            form::hidden(['action'], 'series') .
            '<p><input type="submit" value="' . __('Save') . '" ' .
                'name="save_series" /></p>' .
                '</form>';
            $ap->endPage();
        }
    }

    public static function adminRemoveSeries(dcPostsActions $ap, ArrayObject $post)
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
                dcPage::breadcrumb(
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
                    form::checkbox(['meta_id[]'], Html::escapeHTML($k)),
                    Html::escapeHTML($k)
                ) . '</p>';
            }

            echo
            '<p><input type="submit" value="' . __('ok') . '" />' .
            dcCore::app()->formNonce() . $ap->getHiddenFields() .
            form::hidden(['action'], 'series_remove') .
                '</p></div></form>';
            $ap->endPage();
        }
    }

    public static function postHeaders()
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
        dcPage::jsJson('editor_series_options', $editor_series_options) .
        dcPage::jsJson('editor_series_msg', $msg) .
        dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
        dcPage::jsModuleLoad(My::id() . '/js/post.js', dcCore::app()->getVersion(My::id())) .
        dcPage::cssModuleLoad(My::id() . '/css/style.css', 'screen', dcCore::app()->getVersion(My::id()));
    }

    public static function adminPostEditor($editor = '', $context = '')
    {
        if (($editor != 'dcLegacyEditor' && $editor != 'dcCKEditor') || $context != 'post') {
            return;
        }

        $serie_url = dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie');

        if ($editor == 'dcLegacyEditor') {
            return
            dcPage::jsJson('legacy_editor_series', [
                'serie' => [
                    'title' => __('Serie'),
                    'url'   => $serie_url,
                    'icon'  => urldecode(dcPage::getPF(My::id() . '/icon.svg')),
                ],
            ]) .
            dcPage::jsModuleLoad(My::id() . '/js/legacy-post.js', dcCore::app()->getVersion(My::id()));
        } elseif ($editor == 'dcCKEditor') {
            return
            dcPage::jsJson('ck_editor_series', [
                'serie_title' => __('Serie'),
                'serie_url'   => $serie_url,
            ]);
        }
    }

    public static function ckeditorExtraPlugins(ArrayObject $extraPlugins, $context)
    {
        if ($context != 'post') {
            return;
        }
        $extraPlugins[] = [
            'name'   => 'dcseries',
            'button' => 'dcSeries',
            'url'    => urldecode(DC_ADMIN_URL . dcPage::getPF(My::id() . '/js/ckeditor-series-plugin.js')),
        ];
    }

    public static function adminPreferencesForm()
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
    }

    public static function adminUserForm($rs)
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
    }

    public static function setSerieListFormat($cur, $user_id = null)
    {
        if (!is_null($user_id)) {
            $cur->user_options['serie_list_format'] = $_POST['user_serie_list_format'];
        }
    }
}
