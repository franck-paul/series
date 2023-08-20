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
use Dotclear\Core\Process;

class Frontend extends Process
{
    public static function init(): bool
    {
        // Localized string we find in template
        __("This serie's comments Atom feed");
        __("This serie's entries Atom feed");

        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'templateBeforeBlockV2'  => FrontendBehaviors::templateBeforeBlock(...),
            'publicBeforeDocumentV2' => FrontendBehaviors::addTplPath(...),

            'publicBreadcrumb' => FrontendBehaviors::publicBreadcrumb(...),

            'initWidgets'        => Widgets::initWidgets(...),
            'initDefaultWidgets' => Widgets::initDefaultWidgets(...),
        ]);

        dcCore::app()->tpl->addBlock('Series', FrontendTemplate::Series(...));
        dcCore::app()->tpl->addBlock('SeriesHeader', FrontendTemplate::SeriesHeader(...));
        dcCore::app()->tpl->addBlock('SeriesFooter', FrontendTemplate::SeriesFooter(...));
        dcCore::app()->tpl->addBlock('EntrySeries', FrontendTemplate::EntrySeries(...));
        dcCore::app()->tpl->addValue('SerieID', FrontendTemplate::SerieID(...));
        dcCore::app()->tpl->addValue('SeriePercent', FrontendTemplate::SeriePercent(...));
        dcCore::app()->tpl->addValue('SerieRoundPercent', FrontendTemplate::SerieRoundPercent(...));
        dcCore::app()->tpl->addValue('SerieURL', FrontendTemplate::SerieURL(...));
        dcCore::app()->tpl->addValue('SerieCloudURL', FrontendTemplate::SerieCloudURL(...));
        dcCore::app()->tpl->addValue('SerieFeedURL', FrontendTemplate::SerieFeedURL(...));

        return true;
    }
}
