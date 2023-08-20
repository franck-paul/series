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

        dcCore::app()->url->register('serie', 'serie', '^serie/(.+)$', FrontendUrl::serie(...));
        dcCore::app()->url->register('series', 'series', '^series$', FrontendUrl::series(...));
        dcCore::app()->url->register('serie_feed', 'feed/serie', '^feed/serie/(.+)$', FrontendUrl::serieFeed(...));

        dcCore::app()->addBehavior('coreInitWikiPost', CoreBehaviors::coreInitWikiPost(...));

        return true;
    }
}
