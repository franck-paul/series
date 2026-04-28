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

use ArrayObject;
use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Plugin\TemplateHelper\Code;

class FrontendBehaviors
{
    public static function publicBreadcrumb(string $context, string $separator): string
    {
        if ($context === 'series') {
            // All series
            return __('All series');
        }

        if ($context === 'serie' && App::frontend()->context()->meta instanceof MetaRecord) {
            // Serie
            $meta_id = is_string($meta_id = App::frontend()->context()->meta->meta_id) ? $meta_id : '';
            if ($meta_id !== '') {
                // Get current page if set
                $page = App::frontend()->getPageNumber();
                $ret  = '<a href="' . App::blog()->url() . App::url()->getURLFor('series') . '">' . __('All series') . '</a>';
                if ($page === 0) {
                    $ret .= $separator . $meta_id;
                } else {
                    $ret .= $separator . '<a href="' . App::blog()->url() . App::url()->getURLFor('serie') . '/' . rawurlencode($meta_id) . '">' . $meta_id . '</a>';
                    $ret .= $separator . sprintf(__('page %d'), $page);
                }

                return $ret;
            }
        }

        return '';
    }

    /**
     * @param      string                                               $b      The block
     * @param      array<string, string>|ArrayObject<string, string>    $attr   The attribute
     */
    public static function templateBeforeBlock(string $b, array|ArrayObject $attr): string
    {
        if (($b === 'Entries' || $b === 'Comments') && isset($attr['serie'])) {
            return
            '<?php if (!isset($params)) { $params = []; }' .
            "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
            "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
            "\$params['from'] .= ', '.App::db()->con()->prefix().'meta METAS ';\n" .
            "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
            "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
            "\$params['sql'] .= \"AND METAS.meta_id = '" . App::db()->con()->escapeStr($attr['serie']) . "' \";\n" .
            "?>\n";
        }

        if (empty($attr['no_context']) && ($b === 'Entries' || $b === 'Comments')) {
            return
            '<?php if (App::frontend()->context()->exists("meta") && App::frontend()->context()->meta->rows() && (App::frontend()->context()->meta->meta_type == "serie")) { ' .
            "if (!isset(\$params)) { \$params = []; }\n" .
            "if (!isset(\$params['from'])) { \$params['from'] = ''; }\n" .
            "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
            "\$params['from'] .= ', '.App::db()->con()->prefix().'meta METAS ';\n" .
            "\$params['sql'] .= 'AND METAS.post_id = P.post_id ';\n" .
            "\$params['sql'] .= \"AND METAS.meta_type = 'serie' \";\n" .
            "\$params['sql'] .= \"AND METAS.meta_id = '\".App::db()->con()->escapeStr(App::frontend()->context()->meta->meta_id).\"' \";\n" .
            "} ?>\n";
        }

        return '';
    }

    public static function addTplPath(): string
    {
        App::frontend()->template()->appendPath(My::tplPath());

        return '';
    }

    /**
     * Extends tpl:EntryIf attributes.
     *
     * attributes:
     *
     *      has_series  (0|1)   Entry is in one or several series (if 1), or not (if 0)
     *
     * @param   string                      $tag        The current tag
     * @param   ArrayObject<string, mixed>  $attr       The attributes
     * @param   string                      $content    The content
     * @param   ArrayObject<int, string>    $if         The conditions stack
     */
    public static function tplIfConditions($tag, $attr, $content, $if): string
    {
        if ($tag === 'EntryIf' && isset($attr['has_series'])) {
            $sign = (bool) $attr['has_series'] ? '' : '!';
            $if->append($sign . rtrim(Code::getPHPCode(
                self::tplIfConditionsCode(...),
                [],
                false
            ), ';'));
        }

        return '';
    }

    // Template code for tplIfConditions

    protected static function tplIfConditionsCode(
    ): void {
        (App::frontend()->context()->posts instanceof \Dotclear\Database\MetaRecord && is_string(App::frontend()->context()->posts->post_meta) && App::meta()->getMetaRecordset(App::frontend()->context()->posts->post_meta, 'serie')->count() > 0);
    }
}
