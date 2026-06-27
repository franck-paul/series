<?php

/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\series;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Interface\Core\UserWorkspaceInterface;

class Install
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!App::auth()->prefs()->get('interface')->prefExists('serie_list_format', true)) {
            // Migrate old option if possible
            $format = App::auth()->getOption('serie_list_format') ?? 'more';
            App::auth()->prefs()->get('interface')->put(
                'serie_list_format',
                $format,
                UserWorkspaceInterface::WS_STRING,
                'Serie list format',
                true,
                true
            );
        }

        return true;
    }
}
