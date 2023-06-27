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
use dcNsProcess;

class Frontend extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::FRONTEND);

        // Localized string we find in template
        __("This serie's comments Atom feed");
        __("This serie's entries Atom feed");

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'templateBeforeBlockV2'  => [FrontendBehaviors::class, 'templateBeforeBlock'],
            'publicBeforeDocumentV2' => [FrontendBehaviors::class, 'addTplPath'],

            'publicBreadcrumb' => [FrontendBehaviors::class, 'publicBreadcrumb'],

            'initWidgets'        => [Widgets::class, 'initWidgets'],
            'initDefaultWidgets' => [Widgets::class, 'initDefaultWidgets'],
        ]);

        dcCore::app()->tpl->addBlock('Series', [FrontendTemplate::class, 'Series']);
        dcCore::app()->tpl->addBlock('SeriesHeader', [FrontendTemplate::class, 'SeriesHeader']);
        dcCore::app()->tpl->addBlock('SeriesFooter', [FrontendTemplate::class, 'SeriesFooter']);
        dcCore::app()->tpl->addBlock('EntrySeries', [FrontendTemplate::class, 'EntrySeries']);
        dcCore::app()->tpl->addValue('SerieID', [FrontendTemplate::class, 'SerieID']);
        dcCore::app()->tpl->addValue('SeriePercent', [FrontendTemplate::class, 'SeriePercent']);
        dcCore::app()->tpl->addValue('SerieRoundPercent', [FrontendTemplate::class, 'SerieRoundPercent']);
        dcCore::app()->tpl->addValue('SerieURL', [FrontendTemplate::class, 'SerieURL']);
        dcCore::app()->tpl->addValue('SerieCloudURL', [FrontendTemplate::class, 'SerieCloudURL']);
        dcCore::app()->tpl->addValue('SerieFeedURL', [FrontendTemplate::class, 'SerieFeedURL']);

        return true;
    }
}
