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

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Frontend\Utility;

class FrontendBehaviors
{
    public static function publicBreadcrumb(string $context, string $separator): string
    {
        if ($context == 'series') {
            // All series
            return __('All series');
        } elseif ($context == 'serie') {
            // Serie

            // Get current page if set
            $page = App::frontend()->getPageNumber();
            $ret  = '<a href="' . App::blog()->url() . App::url()->getURLFor('series') . '">' . __('All series') . '</a>';
            if ($page == 0) {
                $ret .= $separator . App::frontend()->context()->meta->meta_id;
            } else {
                $ret .= $separator . '<a href="' . App::blog()->url() . App::url()->getURLFor('serie') . '/' . rawurlencode(App::frontend()->context()->meta->meta_id) . '">' . App::frontend()->context()->meta->meta_id . '</a>';
                $ret .= $separator . sprintf(__('page %d'), $page);
            }

            return $ret;
        }

        return '';
    }

    /**
     * @param      string                                               $b      The block
     * @param      array<string, string>|ArrayObject<string, string>    $attr   The attribute
     *
     * @return     string
     */
    public static function templateBeforeBlock(string $b, array|ArrayObject $attr): string
    {
        if (($b == 'Entries' || $b == 'Comments') && isset($attr['serie'])) {
            return
            "<?php\n" .
            "if (!isset(\$params)) { \$params = []; }\n" .
            "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
            "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
            "\$params['from'] .= ', '.App::con()->prefix().'meta METAS ';\n" .
            "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
            "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
            "\$params['sql'] .= \"AND METAS.meta_id = '" . App::con()->escapeStr($attr['serie']) . "' \";\n" .
                "?>\n";
        } elseif (empty($attr['no_context']) && ($b == 'Entries' || $b == 'Comments')) {
            return
                '<?php if (App::frontend()->context()->exists("meta") && App::frontend()->context()->meta->rows() && (App::frontend()->context()->meta->meta_type == "serie")) { ' .
                "if (!isset(\$params)) { \$params = []; }\n" .
                "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
                "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
                "\$params['from'] .= ', '.App::con()->prefix().'meta METAS ';\n" .
                "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
                "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
                "\$params['sql'] .= \"AND METAS.meta_id = '\".App::con()->escapeStr(App::frontend()->context()->meta->meta_id).\"' \";\n" .
                "} ?>\n";
        }

        return '';
    }

    public static function addTplPath(): string
    {
        $tplset = App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'tplset');
        if (!empty($tplset) && is_dir(implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT, $tplset]))) {
            App::frontend()->template()->appendPath(implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT, $tplset]));
        } else {
            App::frontend()->template()->appendPath(implode(DIRECTORY_SEPARATOR, [My::path(), Utility::TPL_ROOT, App::config()->defaultTplset()]));
        }

        return '';
    }
}
