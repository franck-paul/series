<?php
/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0
 */
class seriesBehaviors
{
    public static function adminDashboardFavorites($core, $favs)
    {
        $favs->register('series', [
            'title'       => __('Series'),
            'url'         => 'plugin.php?p=series&amp;m=series',
            'small-icon'  => urldecode(dcPage::getPF('series/icon.svg')),
            'large-icon'  => urldecode(dcPage::getPF('series/icon.svg')),
            'permissions' => 'usage,contentadmin',
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
        }

        return $series_combo;
    }

    public static function adminSimpleMenuAddType($items)
    {
        $series_combo = self::adminSimpleMenuGetCombo();
        if (count($series_combo) > 1) {
            $items['series'] = new ArrayObject([__('Series'), true]);
        }
    }

    public static function adminSimpleMenuSelect($item_type, $input_name)
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

    public static function wiki2xhtmlSerie($url, $content)
    {
        $url = substr($url, 6);
        if (strpos($content, 'serie:') === 0) {
            $content = substr($content, 6);
        }

        $serie_url      = html::stripHostURL(dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie'));
        $res['url']     = $serie_url . '/' . rawurlencode(dcMeta::sanitizeMetaID($url));
        $res['content'] = $content;

        return $res;
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

    public static function adminPostsActionsPage($core, $ap)
    {
        $ap->addAction(
            [__('Series') => [__('Add series') => 'series']],
            ['seriesBehaviors', 'adminAddSeries']
        );

        if (dcCore::app()->auth->check('delete,contentadmin', dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Series') => [__('Remove series') => 'series_remove']],
                ['seriesBehaviors', 'adminRemoveSeries']
            );
        }
    }

    public static function adminAddSeries($core, dcPostsActionsPage $ap, $post)
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
                'meta_url'            => 'plugin.php?p=series&m=serie_posts&amp;serie=',
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
                        html::escapeHTML(dcCore::app()->blog->name) => '',
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
                dcPage::cssModuleLoad('series/style.css', 'screen', dcCore::app()->getVersion('series'))
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

    public static function adminRemoveSeries($core, dcPostsActionsPage $ap, $post)
    {
        if (!empty($post['meta_id']) && dcCore::app()->auth->check('delete,contentadmin', dcCore::app()->blog->id)) {
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
                        html::escapeHTML(dcCore::app()->blog->name)      => '',
                        __('Entries')                                    => 'posts.php',
                        __('Remove selected series from this selection') => '',
                    ]
                )
            );
            $posts_count = count($_POST['entries']);

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
                    form::checkbox(['meta_id[]'], html::escapeHTML($k)),
                    html::escapeHTML($k)
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
            'meta_url'            => 'plugin.php?p=series&m=serie_posts&amp;serie=',
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
        dcPage::jsModuleLoad('series/js/post.js', dcCore::app()->getVersion('series')) .
        dcPage::cssModuleLoad('series/style.css', 'screen', dcCore::app()->getVersion('series'));
    }

    public static function coreInitWikiPost($wiki2xhtml)
    {
        $wiki2xhtml->registerFunction('url:serie', ['seriesBehaviors', 'wiki2xhtmlSerie']);
    }

    public static function adminPostEditor($editor = '', $context = '', array $tags = [], $syntax = '')
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
                ],
            ]) .
            dcPage::jsModuleLoad('series/js/legacy-post.js', dcCore::app()->getVersion('series'));
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
            'url'    => DC_ADMIN_URL . 'index.php?pf=series/js/ckeditor-series-plugin.js',
        ];
    }

    public static function adminUserForm($args)
    {
        if ($args instanceof dcCore) {
            $opts = $args->auth->getOptions();
        } elseif ($args instanceof record) {
            $opts = $args->options();
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
