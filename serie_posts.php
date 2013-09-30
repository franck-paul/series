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

$serie = (!empty($_REQUEST['serie']) || $_REQUEST['serie'] == '0') ? $_REQUEST['serie'] : '';

$this_url = $p_url.'&amp;m=serie_posts&amp;serie='.rawurlencode($serie);


$page = !empty($_GET['page']) ? max(1,(integer) $_GET['page']) : 1;
$nb_per_page =  30;

# Rename a serie
if (!empty($_POST['new_serie_id']) || $_POST['new_serie_id'] == '0')
{
	$new_id = dcMeta::sanitizeMetaID($_POST['new_serie_id']);
	try {
		if ($core->meta->updateMeta($serie,$new_id,'serie')) {
			http::redirect($p_url.'&m=serie_posts&serie='.$new_id.'&renamed=1');
		}
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Delete a serie
if (!empty($_POST['delete']) && $core->auth->check('publish,contentadmin',$core->blog->id))
{
	try {
		$core->meta->delMeta($serie,'serie');
		http::redirect($p_url.'&m=series&del=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

$params['meta_id'] = $serie;
$params['meta_type'] = 'serie';
$params['post_type'] = '';

# Get posts
try {
	$posts = $core->meta->getPostsByMeta($params);
	$counter = $core->meta->getPostsByMeta($params,true);
	$post_list = new adminPostList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

$posts_actions_page = new dcPostsActionsPage($core,'plugin.php',array('p'=>'series', 'm'=>'serie_posts', 'serie'=> $serie));

if ($posts_actions_page->process()) {
	return;
}

?>
<html>
<head>
  <title><?php echo __('Series'); ?></title>
  <link rel="stylesheet" type="text/css" href="index.php?pf=series/style.css" />
  <script type="text/javascript" src="js/_posts_list.js"></script>
  <script type="text/javascript">
  //<![CDATA[
  dotclear.msg.confirm_serie_delete = '<?php echo html::escapeJS(sprintf(__('Are you sure you want to remove this %s?'),__('serie'))) ?>';
  $(function() {
    $('#serie_delete').submit(function() {
      return window.confirm(dotclear.msg.confirm_serie_delete);
    });
  });
  //]]>
  </script>
</head>
<body>

<?php
echo dcPage::breadcrumb(
	array(
		html::escapeHTML($core->blog->name) => '',
		__('Series') => $p_url.'&amp;m=series',
		__('Serie').' &ldquo;'.html::escapeHTML($serie).'&rdquo;' => ''
	));
?>

<?php
if (!empty($_GET['renamed'])) {
	dcPage::success(__('Serie has been successfully renamed'));
}

echo '<p><a class="back" href="'.$p_url.'&amp;m=series">'.__('Back to series list').'</a></p>';

if (!$core->error->flag())
{
	if (!$posts->isEmpty())
	{
		echo
		'<div class="series-actions vertical-separator">'.
		'<h3>'.html::escapeHTML($serie).'</h3>'.
		'<form action="'.$this_url.'" method="post">'.
		'<p><label for="new_serie_id" class="classic">'.__('Rename:').'</label> '.
		form::field('new_serie_id',40,255,html::escapeHTML($serie)).
		'<input type="submit" value="'.__('OK').'" />'.
		$core->formNonce().
		'</p></form>';
		# Remove serie
		if (!$posts->isEmpty() && $core->auth->check('contentadmin',$core->blog->id)) {
			echo
			'<form id="serie_delete" action="'.$this_url.'" method="post">'.
			'<p>'.'<input type="submit" class="delete" name="delete" value="'.__('Delete this serie').'" />'.
			$core->formNonce().
			'</p></form>';
		}
		echo '</div>';
	}

	# Show posts
	echo '<h4 class="vertical-separator pretty-title">'.__('List of entries in this serie').'</h4>';
	$post_list->display($page,$nb_per_page,
	'<form action="plugin.php" method="post" id="form-entries">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right"><label for="action" class="classic">'.__('Selected entries action:').'</label> '.
	form::combo('action',$posts_actions_page->getCombo()).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden('post_type','').
	form::hidden('p','series').
	form::hidden('m','serie_posts').
	form::hidden('serie',$serie).
	$core->formNonce().
	'</div>'.
	'</form>');
}
?>
</body>
</html>