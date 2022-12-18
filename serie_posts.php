<?php
/**
 * @brief series, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0
 */
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

$serie = $_REQUEST['serie'] ?? '';

$this_url = dcCore::app()->admin->getPageURL() . '&amp;m=serie_posts&amp;serie=' . rawurlencode($serie);

$page        = !empty($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$nb_per_page = 30;

# Rename a serie
if (isset($_POST['new_serie_id'])) {
    $new_id = dcMeta::sanitizeMetaID($_POST['new_serie_id']);

    try {
        if (dcCore::app()->meta->updateMeta($serie, $new_id, 'serie')) {
            dcPage::addSuccessNotice(sprintf(__('The serie “%s” has been successfully renamed to “%s”'), html::escapeHTML($serie), html::escapeHTML($new_id)));
            http::redirect(dcCore::app()->admin->getPageURL() . '&m=serie_posts&serie=' . $new_id);
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

# Delete a serie
if (!empty($_POST['delete']) && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
    dcAuth::PERMISSION_USAGE,
    dcAuth::PERMISSION_CONTENT_ADMIN,
]), dcCore::app()->blog->id)) {
    try {
        dcCore::app()->meta->delMeta($serie, 'serie');
        dcPage::addSuccessNotice(sprintf(__('The serie “%s” has been successfully deleted'), html::escapeHTML($serie)));
        http::redirect(dcCore::app()->admin->getPageURL() . '&m=series');
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

$params               = [];
$params['limit']      = [(($page - 1) * $nb_per_page), $nb_per_page];
$params['no_content'] = true;

$params['meta_id']   = $serie;
$params['meta_type'] = 'serie';
$params['post_type'] = '';

# Get posts
try {
    $posts     = dcCore::app()->meta->getPostsByMeta($params);
    $counter   = dcCore::app()->meta->getPostsByMeta($params, true);
    $post_list = new adminPostList($posts, $counter->f(0));
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

$posts_actions_page = new dcPostsActions('plugin.php', ['p' => 'series', 'm' => 'serie_posts', 'serie' => $serie]);

if ($posts_actions_page->process()) {
    return;
}

?>
<html>
<head>
  <title><?php echo __('Series'); ?></title>
<?php
echo dcPage::cssModuleLoad('series/style.css', 'screen', dcCore::app()->getVersion('series')) .
dcPage::jsLoad('js/_posts_list.js') .
dcPage::jsJson('posts_series_msg', [
    'confirm_serie_delete' => sprintf(__('Are you sure you want to remove serie: “%s”?'), html::escapeHTML($serie)),
]) .
dcPage::jsModuleLoad('series/js/posts.js', dcCore::app()->getVersion('serie')) .
dcPage::jsConfirmClose('serie_rename');
?>
</head>
<body>

<?php
echo dcPage::breadcrumb(
    [
        html::escapeHTML(dcCore::app()->blog->name)                     => '',
        __('Series')                                                    => dcCore::app()->admin->getPageURL() . '&amp;m=series',
        __('Serie') . ' &ldquo;' . html::escapeHTML($serie) . '&rdquo;' => '',
    ]
);
echo dcPage::notices();

echo '<p><a class="back" href="' . dcCore::app()->admin->getPageURL() . '&amp;m=series">' . __('Back to series list') . '</a></p>';

if (!dcCore::app()->error->flag()) {
    /* @phpstan-ignore-next-line */
    if (!$posts->isEmpty()) {
        echo
        '<div class="series-actions vertical-separator">' .
        '<h3>' . html::escapeHTML($serie) . '</h3>' .
        '<form action="' . $this_url . '" method="post" id="serie_rename">' .
        '<p><label for="new_serie_id" class="classic">' . __('Rename:') . '</label> ' .
        form::field('new_serie_id', 40, 255, html::escapeHTML($serie)) .
        '<input type="submit" value="' . __('OK') . '" />' .
        dcCore::app()->formNonce() .
            '</p></form>';
        # Remove serie
        /* @phpstan-ignore-next-line */
        if (!$posts->isEmpty() && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            echo
            '<form id="serie_delete" action="' . $this_url . '" method="post">' .
            '<p>' . '<input type="submit" class="delete" name="delete" value="' . __('Delete this serie') . '" />' .
            dcCore::app()->formNonce() .
                '</p></form>';
        }
        echo '</div>';
    }

    # Show posts
    echo '<h4 class="vertical-separator pretty-title">' . __('List of entries in this serie') . '</h4>';
    /* @phpstan-ignore-next-line */
    $post_list->display(
        $page,
        $nb_per_page,
        '<form action="plugin.php" method="post" id="form-entries">' .

        '%s' .

        '<div class="two-cols">' .
        '<p class="col checkboxes-helpers"></p>' .

        '<p class="col right"><label for="action" class="classic">' . __('Selected entries action:') . '</label> ' .
        form::combo('action', $posts_actions_page->getCombo()) .
        '<input type="submit" value="' . __('ok') . '" /></p>' .
        form::hidden('post_type', '') .
        form::hidden('p', 'series') .
        form::hidden('m', 'serie_posts') .
        form::hidden('serie', $serie) .
        dcCore::app()->formNonce() .
        '</div>' .
        '</form>'
    );
}
?>
</body>
</html>
