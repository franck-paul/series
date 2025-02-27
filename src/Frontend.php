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

        App::behavior()->addBehaviors([
            'templateBeforeBlockV2'  => FrontendBehaviors::templateBeforeBlock(...),
            'publicBeforeDocumentV2' => FrontendBehaviors::addTplPath(...),

            'publicBreadcrumb' => FrontendBehaviors::publicBreadcrumb(...),

            'initWidgets'        => Widgets::initWidgets(...),
            'initDefaultWidgets' => Widgets::initDefaultWidgets(...),
        ]);

        App::frontend()->template()->addBlock('Series', FrontendTemplate::Series(...));
        App::frontend()->template()->addBlock('SeriesHeader', FrontendTemplate::SeriesHeader(...));
        App::frontend()->template()->addBlock('SeriesFooter', FrontendTemplate::SeriesFooter(...));
        App::frontend()->template()->addBlock('EntrySeries', FrontendTemplate::EntrySeries(...));
        App::frontend()->template()->addValue('SerieID', FrontendTemplate::SerieID(...));
        App::frontend()->template()->addValue('SerieCount', FrontendTemplate::SerieCount(...));
        App::frontend()->template()->addValue('SeriePercent', FrontendTemplate::SeriePercent(...));
        App::frontend()->template()->addValue('SerieRoundPercent', FrontendTemplate::SerieRoundPercent(...));
        App::frontend()->template()->addValue('SerieURL', FrontendTemplate::SerieURL(...));
        App::frontend()->template()->addValue('SerieCloudURL', FrontendTemplate::SerieCloudURL(...));
        App::frontend()->template()->addValue('SerieFeedURL', FrontendTemplate::SerieFeedURL(...));

        return true;
    }
}
