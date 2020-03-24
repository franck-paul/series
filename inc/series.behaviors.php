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
            'small-icon'  => urldecode(dcPage::getPF('series/icon.png')),
            'large-icon'  => urldecode(dcPage::getPF('series/icon-big.png')),
            'permissions' => 'usage,contentadmin'
        ]);
    }

    public static function adminSimpleMenuGetCombo()
    {
        global $core;

        $series_combo = [];
        try {
            $rs                             = $core->meta->getMetadata(['meta_type' => 'serie']);
            $series_combo[__('All series')] = '-';
            while ($rs->fetch()) {
                $series_combo[$rs->meta_id] = $rs->meta_id;
            }
            unset($rs);
        } catch (Exception $e) {}

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
        global $core;

        if ($item_type == 'series') {
            $series_combo = self::adminSimpleMenuGetCombo();
            $menu_item[3] = array_search($item_select, $series_combo);
            if ($item_select == '-') {
                $menu_item[0] = __('All series');
                $menu_item[1] = '';
                $menu_item[2] .= $core->url->getURLFor('series');
            } else {
                $menu_item[0] = $menu_item[3];
                $menu_item[1] = sprintf(__('Recent posts for %s serie'), $menu_item[3]);
                $menu_item[2] .= $core->url->getURLFor('serie', $item_select);
            }
        }
    }

    public static function wiki2xhtmlSerie($url, $content)
    {
        $url = substr($url, 6);
        if (strpos($content, 'serie:') === 0) {
            $content = substr($content, 6);
        }

        $serie_url      = html::stripHostURL($GLOBALS['core']->blog->url . $GLOBALS['core']->url->getURLFor('serie'));
        $res['url']     = $serie_url . '/' . rawurlencode(dcMeta::sanitizeMetaID($url));
        $res['content'] = $content;

        return $res;
    }

    public static function seriesField($main, $sidebar, $post)
    {
        $meta = &$GLOBALS['core']->meta;

        if (!empty($_POST['post_series'])) {
            $value = $_POST['post_series'];
        } else {
            $value = ($post) ? $meta->getMetaStr($post->post_meta, 'serie') : '';
        }

        $sidebar['metas-box']['items']['post_series'] =
        '<h5><label class="s-series" for="post_series">' . __('Series:') . '</label></h5>' .
        '<div class="p s-series" id="series-edit">' . form::textarea('post_series', 20, 3, $value, 'maximal') . '</div>';
    }

    public static function setSeries($cur, $post_id)
    {
        $post_id = (integer) $post_id;

        if (isset($_POST['post_series'])) {
            $series = $_POST['post_series'];
            $meta   = &$GLOBALS['core']->meta;
            $meta->delPostMeta($post_id, 'serie');

            foreach ($meta->splitMetaValues($series) as $serie) {
                $meta->setPostMeta($post_id, 'serie', $serie);
            }
        }
    }

    public static function adminPostsActionsPage($core, $ap)
    {
        $ap->addAction(
            [__('Series') => [__('Add series') => 'series']],
            ['seriesBehaviors', 'adminAddSeries']
        );

        if ($core->auth->check('delete,contentadmin', $core->blog->id)) {
            $ap->addAction(
                [__('Series') => [__('Remove series') => 'series_remove']],
                ['seriesBehaviors', 'adminRemoveSeries']
            );
        }
    }

    public static function adminAddSeries($core, dcPostsActionsPage $ap, $post)
    {
        if (!empty($post['new_series'])) {
            $meta   = &$core->meta;
            $series = $meta->splitMetaValues($_POST['new_series']);
            $posts  = $ap->getRS();

            while ($posts->fetch()) {
                # Get series for post
                $post_meta = $meta->getMetadata([
                    'meta_type' => 'serie',
                    'post_id'   => $posts->post_id]);
                $pm = [];
                while ($post_meta->fetch()) {
                    $pm[] = $post_meta->meta_id;
                }

                foreach ($series as $s) {
                    if (!in_array($s, $pm)) {
                        $meta->setPostMeta($posts->post_id, 'serie', $s);
                    }
                }
            }
            dcPage::addSuccessNotice(sprintf(
                __(
                    'Serie has been successfully added to selected entries',
                    'Series have been successfully added to selected entries',
                    count($series))
            )
            );
            $ap->redirect(true, ['upd' => 1]);
        } else {
            $opts = $core->auth->getOptions();
            $type = isset($opts['serie_list_format']) ? $opts['serie_list_format'] : 'more';

            $editor_series_options = [
                'meta_url'            => 'plugin.php?p=series&m=serie_posts&amp;serie=',
                'list_type'           => $type,
                'text_confirm_remove' => __('Are you sure you want to remove this serie?'),
                'text_add_meta'       => __('Add a serie to this entry'),
                'text_choose'         => __('Choose from list'),
                'text_all'            => __('all series'),
                'text_separation'     => __('Enter series separated by comma')
            ];

            $msg = [
                'series_autocomplete' => __('used in %e - frequency %p%'),
                'entry'               => __('entry'),
                'entries'             => __('entries')
            ];

            $ap->beginPage(
                dcPage::breadcrumb(
                    [
                        html::escapeHTML($core->blog->name) => '',
                        __('Entries')                       => $ap->getRedirection(true),
                        __('Add series to this selection')  => ''
                    ]),
                dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
                dcPage::jsMetaEditor() .
                dcPage::jsJson('editor_series_options', $editor_series_options) .
                dcPage::jsJson('editor_series_msg', $msg) .
                dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
                dcPage::jsLoad(urldecode(dcPage::getPF('series/js/posts_actions.js')), $core->getVersion('series')) .
                dcPage::cssLoad(urldecode(dcPage::getPF('series/style.css')), 'screen', $core->getVersion('series'))
            );
            echo
            '<form action="' . $ap->getURI() . '" method="post">' .
            $ap->getCheckboxes() .
            '<div><label for="new_series" class="area">' . __('Series to add:') . '</label> ' .
            form::textarea('new_series', 60, 3) .
            '</div>' .
            $core->formNonce() . $ap->getHiddenFields() .
            form::hidden(['action'], 'series') .
            '<p><input type="submit" value="' . __('Save') . '" ' .
                'name="save_series" /></p>' .
                '</form>';
            $ap->endPage();
        }
    }

    public static function adminRemoveSeries($core, dcPostsActionsPage $ap, $post)
    {
        if (!empty($post['meta_id']) &&
            $core->auth->check('delete,contentadmin', $core->blog->id)) {
            $meta  = &$core->meta;
            $posts = $ap->getRS();
            while ($posts->fetch()) {
                foreach ($_POST['meta_id'] as $v) {
                    $meta->delPostMeta($posts->post_id, 'serie', $v);
                }
            }
            $ap->redirect(true, ['upd' => 1]);
        } else {
            $meta   = &$core->meta;
            $series = [];

            foreach ($ap->getIDS() as $id) {
                $post_series = $meta->getMetadata([
                    'meta_type' => 'serie',
                    'post_id'   => (integer) $id])->toStatic()->rows();
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
                        html::escapeHTML($core->blog->name)              => '',
                        __('Entries')                                    => 'posts.php',
                        __('Remove selected series from this selection') => ''
                    ]));
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
                echo '<p>' . sprintf($label,
                    form::checkbox(['meta_id[]'], html::escapeHTML($k)),
                    html::escapeHTML($k)) .
                    '</p>';
            }

            echo
            '<p><input type="submit" value="' . __('ok') . '" />' .
            $core->formNonce() . $ap->getHiddenFields() .
            form::hidden(['action'], 'series_remove') .
                '</p></div></form>';
            $ap->endPage();
        }
    }

    public static function postHeaders()
    {
        global $core;

        $opts = $GLOBALS['core']->auth->getOptions();
        $type = isset($opts['serie_list_format']) ? $opts['serie_list_format'] : 'more';

        $editor_series_options = [
            'meta_url'            => 'plugin.php?p=series&m=serie_posts&amp;serie=',
            'list_type'           => $type,
            'text_confirm_remove' => __('Are you sure you want to remove this serie?'),
            'text_add_meta'       => __('Add a serie to this entry'),
            'text_choose'         => __('Choose from list'),
            'text_all'            => __('all series'),
            'text_separation'     => __('Enter series separated by comma')
        ];

        $msg = [
            'series_autocomplete' => __('used in %e - frequency %p%'),
            'entry'               => __('entry'),
            'entries'             => __('entries')
        ];

        return
        dcPage::jsJson('editor_series_options', $editor_series_options) .
        dcPage::jsJson('editor_series_msg', $msg) .
        dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
        dcPage::jsLoad(urldecode(dcPage::getPF('series/js/post.js')), $core->getVersion('series')) .
        dcPage::cssLoad(urldecode(dcPage::getPF('series/style.css')), 'screen', $core->getVersion('series'));
    }

    public static function coreInitWikiPost($wiki2xhtml)
    {
        $wiki2xhtml->registerFunction('url:serie', ['seriesBehaviors', 'wiki2xhtmlSerie']);
    }

    public static function adminPostEditor($editor = '', $context = '', array $tags = [], $syntax = '')
    {
        global $core;

        if (($editor != 'dcLegacyEditor' && $editor != 'dcCKEditor') || $context != 'post') {
            return;
        }

        $serie_url = $GLOBALS['core']->blog->url . $GLOBALS['core']->url->getURLFor('serie');

        if ($editor == 'dcLegacyEditor') {
            return
            dcPage::jsJson('legacy_editor_series', [
                'serie' => [
                    'title' => __('Serie'),
                    'url'   => $serie_url
                ]
            ]) .
            dcPage::jsLoad(urldecode(dcPage::getPF('series/js/legacy-post.js')), $core->getVersion('series'));
        } elseif ($editor == 'dcCKEditor') {
            return
            dcPage::jsJson('ck_editor_series', [
                'serie_title' => __('Serie'),
                'serie_url'   => $serie_url
            ]);
        }
        return;
    }

    public static function ckeditorExtraPlugins(ArrayObject $extraPlugins, $context)
    {
        global $core;

        if ($context != 'post') {
            return;
        }
        $extraPlugins[] = [
            'name'   => 'dcseries',
            'button' => 'dcSeries',
            'url'    => DC_ADMIN_URL . 'index.php?pf=series/js/ckeditor-series-plugin.js'
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
