<?php
/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @copyright Olivier Meunier & Association Dotclear
 * @copyright GPL-2.0-only
 */
declare(strict_types=1);

namespace Dotclear\Plugin\series;

use Dotclear\Core\Backend\Action\ActionsPosts;

class BackendActions extends ActionsPosts
{
    /**
     * Use render method.
     *
     * @var     bool    $use_render
     */
    protected bool $use_render = true;
}
