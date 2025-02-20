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
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Exception;

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

        App::backend()->page        = empty($_GET['page']) ? 1 : max(1, (int) $_GET['page']);
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
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        App::backend()->posts_actions_page = new BackendActions(
            App::backend()->url()->get('admin.plugin'),
            [
                'p'     => My::id(),
                'm'     => 'serie_posts',
                'serie' => App::backend()->serie,
            ]
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
        echo (new Para())
            ->items([
                (new Link())
                    ->href(App::backend()->getPageURL() . '&m=series')
                    ->class('back')
                    ->text(__('Back to series list')),
            ])
        ->render();

        if (!App::error()->flag()) {
            if (!App::backend()->posts?->isEmpty()) {
                // Remove serie
                $delete = '';
                if (!App::backend()->posts->isEmpty() && App::auth()->check(App::auth()->makePermissions([
                    App::auth()::PERMISSION_CONTENT_ADMIN,
                ]), App::blog()->id())) {
                    $delete = (new Form('serie_delete'))
                        ->action($this_url)
                        ->method('post')
                        ->fields([
                            (new Para())
                                ->items([
                                    (new Submit('delete', __('Delete this serie')))
                                        ->class('delete'),
                                    ...My::hiddenFields(),
                                ]),
                        ])
                    ->render();
                }

                echo (new Div())
                    ->class(['series-actions', 'vertical-separator'])
                    ->items([
                        (new Text('h3', Html::escapeHTML(App::backend()->serie))),
                        (new Form('serie_rename'))
                            ->action($this_url)
                            ->method('post')
                            ->fields([
                                (new Para())
                                    ->items([
                                        (new Input('new_serie_id'))
                                            ->value(Html::escapeHTML(App::backend()->serie))
                                            ->size(40)
                                            ->maxlength(255)
                                            ->label((new Label(__('Rename:'), Label::INSIDE_LABEL_BEFORE))->class('classic')),
                                        (new Submit('sub_new_serie_id', __('OK'))),
                                        ...My::hiddenFields(),
                                    ]),
                            ]),
                        (new Text(null, $delete)),
                    ])
                ->render();
            }

            // Show posts
            echo (new Text('h4', __('List of entries in this serie')))
                ->class('vertical-separator pretty-title')
            ->render();

            if (App::backend()->post_list) {
                $form = (new Form('form-entries'))
                    ->action(App::backend()->getPageURL())
                    ->method('post')
                    ->fields([
                        (new Text(null, '%s')), // List of posts will be rendered here
                        (new Div())
                            ->class('two-cols')
                            ->items([
                                (new Para())
                                    ->class(['col', 'checkboxes-helpers']),
                                (new Para())
                                    ->class(['col', 'right', 'form-buttons'])
                                    ->items([
                                        (new Select('action'))
                                            ->items(App::backend()->posts_actions_page->getCombo())
                                            ->label((new Label(__('Selected entries action:'), Label::INSIDE_LABEL_BEFORE))->class('classic')),
                                        (new Submit('do_action', __('ok'))),
                                        ...My::hiddenFields([
                                            'post_type' => '',
                                            'm'         => 'serie_posts',
                                            'serie'     => App::backend()->serie,
                                        ]),
                                    ]),
                            ]),
                    ])
                ->render();

                App::backend()->post_list->display(
                    App::backend()->page,
                    App::backend()->nb_per_page,
                    $form
                );
            }
        }

        Page::closeModule();
    }
}
