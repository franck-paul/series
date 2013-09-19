<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of series, a plugin for Dotclear 2.
#
# Copyright (c) Franck Paul and contributors
# carnet.franck.paul@gmail.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

?>
<html>
<head>
  <title><?php echo __('Series'); ?></title>
  <link rel="stylesheet" type="text/css" href="index.php?pf=series/style.css" />
</head>

<body>
<?php
echo dcPage::breadcrumb(
	array(
		html::escapeHTML($core->blog->name) => '',
		'<span class="page-title">'.__('Series').'</span>' => ''
	));
?>

<?php
if (!empty($_GET['del'])) {
	dcPage::success(__('Serie has been successfully removed'));
}

$series = $core->meta->getMetadata(array('meta_type' => 'serie'));
$series = $core->meta->computeMetaStats($series);
$series->sort('meta_id_lower','asc');

$last_letter = null;
$cols = array('','');
$col = 0;
while ($series->fetch())
{
	$letter = mb_strtoupper(mb_substr($series->meta_id,0,1));

	if ($last_letter != $letter) {
		if ($series->index() >= round($series->count()/2)) {
			$col = 1;
		}
		$cols[$col] .= '<tr class="serieLetter"><td colspan="2"><span>'.$letter.'</span></td></tr>';
	}

	$cols[$col] .=
	'<tr class="line">'.
		'<td class="maximal"><a href="'.$p_url.
		'&amp;m=serie_posts&amp;serie='.rawurlencode($series->meta_id).'">'.$series->meta_id.'</a></td>'.
		'<td class="nowrap"><strong>'.$series->count.'</strong> '.
		(($series->count==1) ? __('entry') : __('entries')).'</td>'.
	'</tr>';

	$last_letter = $letter;
}

$table = '<div class="col"><table class="series">%s</table></div>';

if ($cols[0])
{
	echo '<div class="two-cols clearfix">';
	printf($table,$cols[0]);
	if ($cols[1]) {
		printf($table,$cols[1]);
	}
	echo '</div>';
}
else
{
	echo '<p>'.__('No series on this blog.').'</p>';
}
?>

</body>
</html>