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
use Dotclear\Core\Frontend\Utility;

class FrontendBehaviors
{
    public static function publicBreadcrumb($context, $separator)
    {
        if ($context == 'series') {
            // All series
            return __('All series');
        } elseif ($context == 'serie') {
            // Serie

            // Get current page if set
            $page = dcCore::app()->public->getPageNumber();
            $ret  = '<a href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('series') . '">' . __('All series') . '</a>';
            if ($page == 0) {
                $ret .= $separator . dcCore::app()->ctx->meta->meta_id;
            } else {
                $ret .= $separator . '<a href="' . dcCore::app()->blog->url . dcCore::app()->url->getURLFor('serie') . '/' . rawurlencode(dcCore::app()->ctx->meta->meta_id) . '">' . dcCore::app()->ctx->meta->meta_id . '</a>';
                $ret .= $separator . sprintf(__('page %d'), $page);
            }

            return $ret;
        }
    }

    public static function templateBeforeBlock($b, $attr)
    {
        if (($b == 'Entries' || $b == 'Comments') && isset($attr['serie'])) {
            return
            "<?php\n" .
            "if (!isset(\$params)) { \$params = []; }\n" .
            "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
            "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
            "\$params['from'] .= ', '.dcCore::app()->prefix.'meta METAS ';\n" .
            "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
            "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
            "\$params['sql'] .= \"AND METAS.meta_id = '" . dcCore::app()->con->escape($attr['serie']) . "' \";\n" .
                "?>\n";
        } elseif (empty($attr['no_context']) && ($b == 'Entries' || $b == 'Comments')) {
            return
                '<?php if (dcCore::app()->ctx->exists("meta") && dcCore::app()->ctx->meta->rows() && (dcCore::app()->ctx->meta->meta_type == "serie")) { ' .
                "if (!isset(\$params)) { \$params = []; }\n" .
                "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
                "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
                "\$params['from'] .= ', '.dcCore::app()->prefix.'meta METAS ';\n" .
                "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
                "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
                "\$params['sql'] .= \"AND METAS.meta_id = '\".dcCore::app()->con->escape(dcCore::app()->ctx->meta->meta_id).\"' \";\n" .
                "} ?>\n";
        }
    }

    public static function addTplPath()
    {
        $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
        if (!empty($tplset) && is_dir(__DIR__ . '/' . Utility::TPL_ROOT . '/' . $tplset)) {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), My::path() . '/' . Utility::TPL_ROOT . '/' . $tplset);
        } else {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), My::path() . '/' . Utility::TPL_ROOT . '/' . DC_DEFAULT_TPLSET);
        }
    }
}
