<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2012 Franck Paul
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$serie = (!empty($_REQUEST['serie']) || $_REQUEST['serie'] == '0') ? $_REQUEST['serie'] : '';

$this_url = $p_url.'&amp;m=serie_posts&amp;serie='.rawurlencode($serie);


$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
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

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('Status')] = array(
		__('Publish') => 'publish',
		__('Unpublish') => 'unpublish',
		__('Schedule') => 'schedule',
		__('Mark as pending') => 'pending'
	);
}
$combo_action[__('Mark')] = array(
	__('Mark as selected') => 'selected',
	__('Mark as unselected') => 'unselected'
);
$combo_action[__('Change')] = array(__('Change category') => 'category');
if ($core->auth->check('admin',$core->blog->id))
{
	$combo_action[__('Change')] = array_merge($combo_action[__('Change')],
		array(__('Change author') => 'author'));
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('Delete')] = array(__('Delete') => 'delete');
}

# --BEHAVIOR-- adminPostsActionsCombo
$core->callBehavior('adminPostsActionsCombo',array(&$combo_action));

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

<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo;
<span class="page-title"><?php echo __('Edit serie').' &ldquo;'.html::escapeHTML($serie).'&rdquo;'; ?></span></h2>

<?php
if (!empty($_GET['renamed'])) {
	echo '<p class="message">'.__('Serie has been successfully renamed').'</p>';
}

echo '<p><a href="'.$p_url.'&amp;m=series">'.__('Back to series list').'</a></p>';

if (!$core->error->flag())
{
	if (!$posts->isEmpty())
	{
		echo
		'<form action="'.$this_url.'" method="post">'.
		'<div class="fieldset"><h3>'.__('Actions for this serie').'</h3>'.
		'<p><label for="new_serie_id">'.__('Edit serie name:').'</label>'.
		form::field('new_serie_id',20,255,html::escapeHTML($serie)).
		'<input type="submit" value="'.__('Rename').'" />'.
		$core->formNonce().
		'</form>';
		# Remove serie
		if (!$posts->isEmpty() && $core->auth->check('contentadmin',$core->blog->id)) {
			echo
			'<form id="serie_delete" action="'.$this_url.'" method="post">'.
			'<p class="no-margin">'.__('Delete this serie:').'</p>'.
			'<input type="submit" class="delete" name="delete" value="'.__('Delete').'" />'.
			$core->formNonce().
			'</form>';
		}
		echo '</p></div>';
	}
	
	# Show posts
	echo '<h3>'.__('List of entries in this serie').'</h3>';
	$post_list->display($page,$nb_per_page,
	'<form action="posts_actions.php" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right"><label for="action" class="classic">'.__('Selected entries action:').'</label> '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden('post_type','').
	form::hidden('redir',$p_url.'&amp;m=serie_posts&amp;serie='.
		str_replace('%','%%',rawurlencode($serie)).'&amp;page='.$page).
	$core->formNonce().
	'</div>'.
	'</form>');
}
?>
</body>
</html>