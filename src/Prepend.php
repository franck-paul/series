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

class Prepend extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::PREPEND);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->url->register('serie', 'serie', '^serie/(.+)$', [FrontendUrl::class, 'serie']);
        dcCore::app()->url->register('series', 'series', '^series$', [FrontendUrl::class, 'series']);
        dcCore::app()->url->register('serie_feed', 'feed/serie', '^feed/serie/(.+)$', [FrontendUrl::class, 'serieFeed']);

        dcCore::app()->addBehavior('coreInitWikiPost', [CoreBehaviors::class, 'coreInitWikiPost']);

        return true;
    }
}
