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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

?>
<html>
<head>
  <title><?php echo __('Series'); ?></title>
  <?php echo dcPage::cssLoad(urldecode(dcPage::getPF('series/style.css')), 'screen', $core->getVersion('series')); ?>
</head>

<body>
<?php
echo dcPage::breadcrumb(
    [
        html::escapeHTML($core->blog->name) => '',
        __('Series')                        => ''
    ]);
echo dcPage::notices();
?>

<?php
$series = $core->meta->getMetadata(['meta_type' => 'serie']);
$series = $core->meta->computeMetaStats($series);
$series->sort('meta_id_lower', 'asc');

$last_letter = null;
$cols        = ['', ''];
$col         = 0;
while ($series->fetch()) {
    $letter = mb_strtoupper(mb_substr($series->meta_id_lower, 0, 1));

    if ($last_letter != $letter) {
        if ($series->index() >= round($series->count() / 2)) {
            $col = 1;
        }
        $cols[$col] .= '<tr class="serieLetter"><td colspan="2"><span>' . $letter . '</span></td></tr>';
    }

    $cols[$col] .=
    '<tr class="line">' .
    '<td class="maximal"><a href="' . $p_url .
    '&amp;m=serie_posts&amp;serie=' . rawurlencode($series->meta_id) . '">' . $series->meta_id . '</a></td>' .
    '<td class="nowrap"><strong>' . $series->count . '</strong> ' .
        (($series->count == 1) ? __('entry') : __('entries')) . '</td>' .
        '</tr>';

    $last_letter = $letter;
}

$table = '<div class="col"><table class="series">%s</table></div>';

if ($cols[0]) {
    echo '<div class="two-cols clearfix">';
    printf($table, $cols[0]);
    if ($cols[1]) {
        printf($table, $cols[1]);
    }
    echo '</div>';
} else {
    echo '<p>' . __('No series on this blog.') . '</p>';
}
?>

</body>
</html>
