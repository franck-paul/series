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

class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::url()->register('serie', 'serie', '^serie/(.+)$', FrontendUrl::serie(...));
        App::url()->register('series', 'series', '^series$', FrontendUrl::series(...));
        App::url()->register('serie_feed', 'feed/serie', '^feed/serie/(.+)$', FrontendUrl::serieFeed(...));

        App::behavior()->addBehavior('coreInitWikiPost', CoreBehaviors::coreInitWikiPost(...));

        return true;
    }
}
