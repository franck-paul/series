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

use dcAdmin;
use dcCore;
use dcNsProcess;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = My::checkContext(My::BACKEND);

        // dead but useful code, in order to have translations
        __('Series') . __('Series of posts');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
            __('Series'),
            My::makeUrl(),
            My::icons(),
            preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
            My::checkContext(My::MENU)
        );

        dcCore::app()->addBehaviors([
            'adminPostFormItems' => [BackendBehaviors::class, 'seriesField'],

            'adminAfterPostCreate' => [BackendBehaviors::class, 'setSeries'],
            'adminAfterPostUpdate' => [BackendBehaviors::class, 'setSeries'],

            'adminPostHeaders' => [BackendBehaviors::class, 'postHeaders'],

            'adminPostsActions' => [BackendBehaviors::class, 'adminPostsActions'],

            'adminPreferencesFormV2'       => [BackendBehaviors::class, 'adminPreferencesForm'],
            'adminBeforeUserOptionsUpdate' => [BackendBehaviors::class, 'setSerieListFormat'],

            'adminUserForm'         => [BackendBehaviors::class, 'adminUserForm'],
            'adminBeforeUserCreate' => [BackendBehaviors::class, 'setSerieListFormat'],
            'adminBeforeUserUpdate' => [BackendBehaviors::class, 'setSerieListFormat'],

            'adminPostEditor'      => [BackendBehaviors::class, 'adminPostEditor'],
            'ckeditorExtraPlugins' => [BackendBehaviors::class, 'ckeditorExtraPlugins'],

            // Register favorite
            'adminDashboardFavoritesV2' => [BackendBehaviors::class, 'adminDashboardFavorites'],

            'adminSimpleMenuAddType'    => [BackendBehaviors::class, 'adminSimpleMenuAddType'],
            'adminSimpleMenuSelect'     => [BackendBehaviors::class, 'adminSimpleMenuSelect'],
            'adminSimpleMenuBeforeEdit' => [BackendBehaviors::class, 'adminSimpleMenuBeforeEdit'],
        ]);

        if (My::checkContext(My::WIDGETS)) {
            dcCore::app()->addBehaviors([
                'initWidgets'        => [Widgets::class, 'initWidgets'],
                'initDefaultWidgets' => [Widgets::class, 'initDefaultWidgets'],
            ]);
        }

        return true;
    }
}
