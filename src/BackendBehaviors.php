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
use Dotclear\App;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Textarea;
use Dotclear\Helper\Html\Html;
use Exception;

class BackendBehaviors
{
    public static function adminDashboardFavorites(Favorites $favs): string
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
            $rs                             = App::meta()->getMetadata(['meta_type' => 'serie']);
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

            return
            (new Para())
                ->items([
                    (new Select($id))
                        ->label(new Label(__('Select serie (if necessary):')))
                        ->items($series_combo),
                ])
            ->render();
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
            $menu_item[3] = array_search($item_select, $series_combo, true);
            if ($item_select == '-') {
                $menu_item[0] = __('All series');
                $menu_item[1] = '';
                $menu_item[2] .= App::url()->getURLFor('series');
            } else {
                $menu_item[0] = $menu_item[3];
                $menu_item[1] = sprintf(__('Recent posts for %s serie'), $menu_item[3]);
                $menu_item[2] .= App::url()->getURLFor('serie', $item_select);
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
            $value = ($post instanceof \Dotclear\Database\MetaRecord) ? App::meta()->getMetaStr($post->post_meta, 'serie') : '';
        }

        $sidebar['metas-box']['items']['post_series'] = (new Para(null, 'h5'))
            ->items([
                (new Label(__('Series:'), Label::OUTSIDE_LABEL_BEFORE))
                    ->for('post_series')
                    ->class('s-series'),
            ])
        ->render() .
            (new Div('series-edit'))
                ->class('p s-series')
                ->items([
                    (new Textarea('post_series', $value))
                        ->cols(20)
                        ->rows(3)
                        ->class('maximal'),
                ])
        ->render();

        return '';
    }

    public static function setSeries(Cursor $cur, int $post_id): string
    {
        if (isset($_POST['post_series'])) {
            $series = $_POST['post_series'];
            App::meta()->delPostMeta($post_id, 'serie');

            foreach (App::meta()->splitMetaValues($series) as $serie) {
                App::meta()->setPostMeta($post_id, 'serie', $serie);
            }
        }

        return '';
    }

    public static function adminPostsActions(ActionsPosts $ap): string
    {
        $ap->addAction(
            [__('Series') => [__('Add series') => 'series_add']],
            static::adminAddSeries(...)
        );

        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_DELETE,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
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
            $series = App::meta()->splitMetaValues($_POST['new_series']);
            $posts  = $ap->getRS();

            while ($posts->fetch()) {
                # Get series for post
                $post_meta = App::meta()->getMetadata([
                    'meta_type' => 'serie',
                    'post_id'   => $posts->post_id,
                ]);
                $pm = [];
                while ($post_meta->fetch()) {
                    $pm[] = $post_meta->meta_id;
                }

                foreach ($series as $s) {
                    if (!in_array($s, $pm)) {
                        App::meta()->setPostMeta($posts->post_id, 'serie', $s);
                    }
                }
            }

            Notices::addSuccessNotice(
                __(
                    'Serie has been successfully added to selected entries',
                    'Series have been successfully added to selected entries',
                    count($series)
                )
            );
            $ap->redirect(true, ['upd' => 1]);
        } else {
            $opts = App::auth()->getOptions();
            $type = $opts['serie_list_format'] ?? 'more';

            $editor_series_options = [
                'meta_url' => App::backend()->url()->get('admin.plugin.' . My::id(), [
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
                        Html::escapeHTML(App::blog()->name()) => '',
                        __('Entries')                         => $ap->getRedirection(true),
                        __('Add series to this selection')    => '',
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

            echo (new Form('frm_new_series'))
                ->action($ap->getURI())
                ->method('post')
                ->items([
                    $ap->checkboxes(),
                    (new Div())
                        ->items([
                            (new Textarea('new_series'))
                                ->label(new Label(__('Series to add:'), Label::INSIDE_LABEL_AFTER, 'new_series'))
                                ->cols(60)
                                ->rows(3),
                        ]),
                    ...$ap->hiddenFields(),
                    ...My::hiddenFields([
                        'action' => 'series_add',
                    ]),
                    (new Para())
                        ->items([
                            (new Submit(['save_series'], __('Save'))),
                        ]),
                ])
            ->render();

            $ap->endPage();
        }
    }

    /**
     * @param      ActionsPosts                 $ap     Actions
     * @param      ArrayObject<string, mixed>   $post   The post
     */
    public static function adminRemoveSeries(ActionsPosts $ap, ArrayObject $post): void
    {
        if (!empty($post['meta_id']) && App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_DELETE,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $posts = $ap->getRS();
            while ($posts->fetch()) {
                foreach ($_POST['meta_id'] as $v) {
                    App::meta()->delPostMeta($posts->post_id, 'serie', $v);
                }
            }

            $ap->redirect(true, ['upd' => 1]);
        } else {
            $series = [];

            foreach ($ap->getIDS() as $id) {
                $post_series = App::meta()->getMetadata([
                    'meta_type' => 'serie',
                    'post_id'   => (int) $id, ])->toStatic()->rows();
                foreach ($post_series as $v) {
                    if (isset($series[$v['meta_id']])) {
                        ++$series[$v['meta_id']];
                    } else {
                        $series[$v['meta_id']] = 1;
                    }
                }
            }

            if ($series === []) {
                throw new Exception(__('No series for selected entries'));
            }

            $ap->beginPage(
                Page::breadcrumb(
                    [
                        Html::escapeHTML(App::blog()->name())            => '',
                        __('Entries')                                    => App::backend()->url()->get('admin.posts'),
                        __('Remove selected series from this selection') => '',
                    ]
                )
            );
            $posts_count = is_countable($_POST['entries']) ? count($_POST['entries']) : 0;

            $list = [];
            $i    = 0;
            foreach ($series as $name => $number) {
                $label  = sprintf($posts_count == $number ? '<strong>%s</strong>' : '%s', Html::escapeHTML((string) $name));
                $list[] = (new Para())
                    ->items([
                        (new Checkbox(['meta_id[]','meta_id-' . ++$i]))
                            ->value(Html::escapeHTML((string) $name))
                            ->label(new Label($label, Label::INSIDE_TEXT_AFTER)),
                    ]);
            }

            echo (new Form('frm_rem_series'))
                ->action($ap->getURI())
                ->method('post')
                ->items([
                    $ap->checkboxes(),
                    (new Div())
                        ->items([
                            (new Para())
                                ->items([
                                    (new Text(null, __('Following series have been found in selected entries:'))),
                                ]),
                            ...$list,
                        ]),
                    ...$ap->hiddenFields(),
                    ...My::hiddenFields([
                        'action' => 'series_remove',
                    ]),
                    (new Para())
                        ->items([
                            (new Submit(['rem_series'], __('ok'))),
                        ]),
                ])
            ->render();

            $ap->endPage();
        }
    }

    public static function postHeaders(): string
    {
        $opts = App::auth()->getOptions();
        $type = $opts['serie_list_format'] ?? 'more';

        $editor_series_options = [
            'meta_url' => App::backend()->url()->get('admin.plugin.' . My::id(), [
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

        $serie_url = App::blog()->url() . App::url()->getURLFor('serie');

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
        } elseif ($editor === 'dcCKEditor') {
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
            'url'    => urldecode(App::config()->adminUrl() . Page::getPF(My::id() . '/js/ckeditor-series-plugin.js')),
        ];

        return '';
    }

    public static function adminPreferencesForm(): string
    {
        $opts = App::auth()->getOptions();

        $combo                 = [];
        $combo[__('Short')]    = 'more';
        $combo[__('Extended')] = 'all';

        $value = array_key_exists('serie_list_format', $opts) ? $opts['serie_list_format'] : 'more';

        echo
        (new Fieldset('series_prefs'))
            ->legend((new Legend(__('Series'))))
            ->fields([
                (new Para())
                    ->items([
                        (new Select('user_serie_list_format'))
                            ->label(new Label(__('Series list format:'), Label::INSIDE_LABEL_BEFORE))
                            ->default($value)
                            ->items($combo),
                    ]),
            ])
        ->render();

        return '';
    }

    public static function adminUserForm(?MetaRecord $rs): string
    {
        $opts = $rs instanceof MetaRecord ? $rs->options() : [];

        $combo                 = [];
        $combo[__('Short')]    = 'more';
        $combo[__('Extended')] = 'all';

        $value = array_key_exists('serie_list_format', $opts) ? $opts['serie_list_format'] : 'more';

        echo
        (new Fieldset('series_prefs'))
            ->legend((new Legend(__('Series'))))
            ->fields([
                (new Para())
                    ->items([
                        (new Select('user_serie_list_format'))
                            ->label(new Label(__('Series list format:'), Label::INSIDE_LABEL_BEFORE))
                            ->default($value)
                            ->items($combo),
                    ]),
            ])
        ->render();

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
