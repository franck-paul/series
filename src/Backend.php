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
use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('Series') . __('Series of posts');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem(Menus::MENU_BLOG, ['m' => 'series'], '&m=serie(s|_posts)?(&.*)?$');

        dcCore::app()->addBehaviors([
            'adminPostFormItems' => BackendBehaviors::seriesField(...),

            'adminAfterPostCreate' => BackendBehaviors::setSeries(...),
            'adminAfterPostUpdate' => BackendBehaviors::setSeries(...),

            'adminPostHeaders' => BackendBehaviors::postHeaders(...),

            'adminPostsActions' => BackendBehaviors::adminPostsActions(...),

            'adminPreferencesFormV2'       => BackendBehaviors::adminPreferencesForm(...),
            'adminBeforeUserOptionsUpdate' => BackendBehaviors::setSerieListFormat(...),

            'adminUserForm'         => BackendBehaviors::adminUserForm(...),
            'adminBeforeUserCreate' => BackendBehaviors::setSerieListFormat(...),
            'adminBeforeUserUpdate' => BackendBehaviors::setSerieListFormat(...),

            'adminPostEditor'      => BackendBehaviors::adminPostEditor(...),
            'ckeditorExtraPlugins' => BackendBehaviors::ckeditorExtraPlugins(...),

            // Register favorite
            'adminDashboardFavoritesV2' => BackendBehaviors::adminDashboardFavorites(...),

            'adminSimpleMenuAddType'    => BackendBehaviors::adminSimpleMenuAddType(...),
            'adminSimpleMenuSelect'     => BackendBehaviors::adminSimpleMenuSelect(...),
            'adminSimpleMenuBeforeEdit' => BackendBehaviors::adminSimpleMenuBeforeEdit(...),
        ]);

        if (My::checkContext(My::WIDGETS)) {
            dcCore::app()->addBehaviors([
                'initWidgets'        => Widgets::initWidgets(...),
                'initDefaultWidgets' => Widgets::initDefaultWidgets(...),
            ]);
        }

        return true;
    }
}
