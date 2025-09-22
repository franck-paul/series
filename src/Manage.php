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
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\None;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Span;
use Dotclear\Helper\Html\Form\Strong;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Process\TraitProcess;

class Manage
{
    use TraitProcess;

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

        App::backend()->series = App::meta()->getMetadata(['meta_type' => 'serie']);
        App::backend()->series = App::meta()->computeMetaStats(App::backend()->series);
        App::backend()->series->lexicalSort('meta_id_lower', 'asc');

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

        App::backend()->page()->openModule(My::name(), $head);

        echo App::backend()->page()->breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                __('series')                          => '',
            ]
        );
        echo App::backend()->notices()->getNotices();

        $last_letter = '';
        $lines       = [[], []];
        $column      = 0;
        while (App::backend()->series->fetch()) {
            $letter = mb_strtoupper(mb_substr((string) App::backend()->series->meta_id_lower, 0, 1));

            if ($last_letter !== $letter) {
                if (App::backend()->series->index() >= round(App::backend()->series->count() / 2)) {
                    $column = 1;
                }

                $lines[$column][] = (new Tr())
                    ->class('serieLetter')
                    ->cols([
                        (new Td())
                            ->colspan(2)
                            ->items([
                                (new Span($letter)),
                            ]),
                    ]);
            }

            $lines[$column][] = (new Tr())
                ->class('line')
                ->cols([
                    (new Td())
                        ->class('maximal')
                        ->items([
                            (new Link())
                                ->href(App::backend()->getPageURL() . '&m=serie_posts&serie=' . rawurlencode((string) App::backend()->series->meta_id))
                                ->text(App::backend()->series->meta_id),
                        ]),
                    (new Td())
                        ->class(['nowrap', 'count'])
                        ->items([
                            (new Strong(App::backend()->series->count)),
                            (new Text(null, App::backend()->series->count === 1 ? __('entry') : __('entries'))),
                        ]),
                ]);

            $last_letter = $letter;
        }

        if ($lines[0] !== []) {
            echo (new Div())
                ->class('two-cols')
                ->items([
                    (new Div())
                        ->class('col')
                        ->items([
                            (new Table())
                                ->class('series')
                                ->tbody((new Tbody())
                                    ->rows($lines[0])),
                        ]),
                    $lines[1] !== [] ?
                    (new Div())
                        ->class('col')
                        ->items([
                            (new Table())
                                ->class('series')
                                ->tbody((new Tbody())
                                    ->rows($lines[1])),
                        ]) :
                    (new None()),
                ])
            ->render();
        } else {
            echo (new Note())
                ->text(__('No series on this blog.'))
            ->render();
        }

        App::backend()->page()->closeModule();
    }
}
