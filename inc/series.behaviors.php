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

class seriesBehaviors
{
	public static function adminDashboardFavorites($core,$favs)
	{
		$favs->register('series', array(
			'title' => __('Series'),
			'url' => 'plugin.php?p=series&amp;m=series',
			'small-icon' => 'index.php?pf=series/icon.png',
			'large-icon' => 'index.php?pf=series/icon-big.png',
			'permissions' => 'usage,contentadmin'
		));
	}

	public static function adminSimpleMenuGetCombo()
	{
		global $core;

		$series_combo = array();
		try {
			$rs = $core->meta->getMetadata(array('meta_type' => 'serie'));
			$series_combo[__('All series')] = '-';
			while ($rs->fetch()) {
				$series_combo[$rs->meta_id] = $rs->meta_id;
			}
			unset($rs);
		} catch (Exception $e) { }

		return $series_combo;
	}

	public static function adminSimpleMenuAddType($items)
	{
		$series_combo = self::adminSimpleMenuGetCombo();
		if (count($series_combo) > 1)
			$items['series'] = new ArrayObject(array(__('Series'),true));
	}

	public static function adminSimpleMenuSelect($item_type,$input_name)
	{
		if ($item_type == 'series') {
			$series_combo = self::adminSimpleMenuGetCombo();
			return '<p class="field"><label for="item_select" class="classic">'.__('Select serie (if necessary):').'</label>'.
				form::combo('item_select',$series_combo,'');
		}
	}

	public static function adminSimpleMenuBeforeEdit($item_type,$item_select,$menu_item)
	{
		global $core;

		if ($item_type == 'series') {
			$series_combo = self::adminSimpleMenuGetCombo();
			$menu_item[3] = array_search($item_select,$series_combo);
			if ($item_select == '-') {
				$menu_item[0] = __('All series');
				$menu_item[1] = '';
				$menu_item[2] .= $core->url->getURLFor('series');
			} else {
				$menu_item[0] = $menu_item[3];
				$menu_item[1] = sprintf(__('Recent posts for %s serie'),$menu_item[3]);
				$menu_item[2] .= $core->url->getURLFor('serie',$item_select);
			}
		}
	}

	public static function wiki2xhtmlSerie($url,$content)
	{
		$url = substr($url,6);
		if (strpos($content,'serie:') === 0) {
			$content = substr($content,6);
		}

		$serie_url = html::stripHostURL($GLOBALS['core']->blog->url.$GLOBALS['core']->url->getURLFor('serie'));
		$res['url'] = $serie_url.'/'.rawurlencode(dcMeta::sanitizeMetaID($url));
		$res['content'] = $content;

		return $res;
	}

	public static function seriesField($main,$sidebar,$post)
	{
		$meta =& $GLOBALS['core']->meta;

		if (!empty($_POST['post_series'])) {
			$value = $_POST['post_series'];
		} else {
			$value = ($post) ? $meta->getMetaStr($post->post_meta,'serie') : '';
		}

		$sidebar['metas-box']['items']['post_series'] =
		'<h5><label class="s-series" for="post_series">'.__('Series:').'</label></h5>'.
		'<div class="p s-series" id="series-edit">'.form::textarea('post_series',20,3,$value,'maximal').'</div>';
	}

	public static function setSeries($cur,$post_id)
	{
		$post_id = (integer) $post_id;

		if (isset($_POST['post_series'])) {
			$series = $_POST['post_series'];
			$meta =& $GLOBALS['core']->meta;
			$meta->delPostMeta($post_id,'serie');

			foreach ($meta->splitMetaValues($series) as $serie) {
				$meta->setPostMeta($post_id,'serie',$serie);
			}
		}
	}

	public static function adminPostsActionsPage($core,$ap)
	{
		$ap->addAction(
			array(__('Series') => array(__('Add series') => 'series')),
			array('seriesBehaviors','adminAddSeries')
		);

		if ($core->auth->check('delete,contentadmin',$core->blog->id)) {
			$ap->addAction(
				array(__('Series') => array(__('Remove series') => 'series_remove')),
				array('seriesBehaviors','adminRemoveSeries')
			);
		}
	}

	public static function adminAddSeries($core,dcPostsActionsPage $ap,$post)
	{
		if (!empty($post['new_series']))
		{
			$meta =& $core->meta;
			$series = $meta->splitMetaValues($_POST['new_series']);
			$posts = $ap->getRS();

			while ($posts->fetch())
			{
				# Get series for post
				$post_meta = $meta->getMetadata(array(
					'meta_type' => 'serie',
					'post_id' => $posts->post_id));
				$pm = array();
				while ($post_meta->fetch()) {
					$pm[] = $post_meta->meta_id;
				}

				foreach ($series as $s) {
					if (!in_array($s,$pm)) {
						$meta->setPostMeta($posts->post_id,'serie',$s);
					}
				}
			}
			$ap->redirect(true,array('upd' => 1));
		}
		else
		{
			$opts = $core->auth->getOptions();
			$type = isset($opts['serie_list_format']) ? $opts['serie_list_format'] : 'more';

			$ap->beginPage(
				dcPage::breadcrumb(
					array(
						html::escapeHTML($core->blog->name) => '',
						__('Entries') => $ap->getRedirection(true),
						__('Add series to this selection') => ''
				)),
				dcPage::jsLoad('js/jquery/jquery.autocomplete.js').
				dcPage::jsMetaEditor().
				'<script type="text/javascript">'."\n".
				"//<![CDATA[\n".
				"var editor_series_options = {\n".
					"meta_url : 'plugin.php?p=series&m=serie_posts&amp;serie=',\n".
					"list_type : '".html::escapeJS($type)."',\n".
					"text_confirm_remove : '".html::escapeJS(__('Are you sure you want to remove this serie?'))."',\n".
					"text_add_meta : '".html::escapeJS(__('Add a serie to this entry'))."',\n".
					"text_choose : '".html::escapeJS(__('Choose from list'))."',\n".
					"text_all : '".html::escapeJS(__('all'))."',\n".
					"text_separation : '".html::escapeJS(__('Enter series separated by coma'))."',\n".
				"};\n".
				"\n//]]>\n".
				"</script>\n".
				'<script type="text/javascript" src="index.php?pf=series/js/jquery.autocomplete.js"></script>'.
				'<script type="text/javascript" src="index.php?pf=series/js/posts_actions.js"></script>'.
				'<script type="text/javascript">'."\n".
				"//<![CDATA[\n".
				"dotclear.msg.series_autocomplete = '".html::escapeJS(__('used in %e - frequency %p%'))."';\n".
				"dotclear.msg.entry = '".html::escapeJS(__('entry'))."';\n".
				"dotclear.msg.entries = '".html::escapeJS(__('entries'))."';\n".
				"\n//]]>\n".
				"</script>\n".
				'<link rel="stylesheet" type="text/css" href="index.php?pf=series/style.css" />'
			);
			echo
				'<form action="'.$ap->getURI().'" method="post">'.
				$ap->getCheckboxes().
				'<div><label for="new_series" class="area">'.__('Series to add:').'</label> '.
				form::textarea('new_series',60,3).
				'</div>'.
				$core->formNonce().$ap->getHiddenFields().
				form::hidden(array('action'),'series').
				'<p><input type="submit" value="'.__('Save').'" '.
				'name="save_series" /></p>'.
				'</form>';
			$ap->endPage();
		}
	}

	public static function adminRemoveSeries($core,dcPostsActionsPage $ap,$post)
	{
		if (!empty($post['meta_id']) &&
			$core->auth->check('delete,contentadmin',$core->blog->id))
		{
			$meta =& $core->meta;
			$posts = $ap->getRS();
			while ($posts->fetch())
			{
				foreach ($_POST['meta_id'] as $v)
				{
					$meta->delPostMeta($posts->post_id,'serie',$v);
				}
			}
			$ap->redirect(true,array('upd' => 1));
		}
		else
		{
			$meta =& $core->meta;
			$series = array();

			foreach ($ap->getIDS() as $id) {
				$post_series = $meta->getMetadata(array(
					'meta_type' => 'serie',
					'post_id' => (integer) $id))->toStatic()->rows();
				foreach ($post_series as $v) {
					if (isset($series[$v['meta_id']])) {
						$series[$v['meta_id']]++;
					} else {
						$series[$v['meta_id']] = 1;
					}
				}
			}
			if (empty($series)) {
				throw new Exception(__('No series for selected entries'));
			}
			$ap->beginPage(
				dcPage::breadcrumb(
						array(
							html::escapeHTML($core->blog->name) => '',
							__('Entries') => 'posts.php',
							__('Remove selected series from this selection') => ''
			)));
			$posts_count = count($_POST['entries']);

			echo
			'<form action="'.$ap->getURI().'" method="post">'.
			$ap->getCheckboxes().
			'<div><p>'.__('Following series have been found in selected entries:').'</p>';

			foreach ($series as $k => $n) {
				$label = '<label class="classic">%s %s</label>';
				if ($posts_count == $n) {
					$label = sprintf($label,'%s','<strong>%s</strong>');
				}
				echo '<p>'.sprintf($label,
						form::checkbox(array('meta_id[]'),html::escapeHTML($k)),
						html::escapeHTML($k)).
					'</p>';
			}

			echo
			'<p><input type="submit" value="'.__('ok').'" />'.
			$core->formNonce().$ap->getHiddenFields().
			form::hidden(array('action'),'series_remove').
			'</p></div></form>';
			$ap->endPage();
		}
	}

	public static function postHeaders()
	{
		$opts = $GLOBALS['core']->auth->getOptions();
		$type = isset($opts['serie_list_format']) ? $opts['serie_list_format'] : 'more';

		return
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"var editor_series_options = {\n".
			"meta_url : 'plugin.php?p=series&m=serie_posts&amp;serie=',\n".
			"list_type : '".html::escapeJS($type)."',\n".
			"text_confirm_remove : '".html::escapeJS(__('Are you sure you want to remove this serie?'))."',\n".
			"text_add_meta : '".html::escapeJS(__('Add a serie to this entry'))."',\n".
			"text_choose : '".html::escapeJS(__('Choose from list'))."',\n".
			"text_all : '".html::escapeJS(__('all series'))."',\n".
			"text_separation : '".html::escapeJS(__('Enter series separated by coma'))."',\n".
		"};\n".
		"\n//]]>\n".
		"</script>\n".
		'<script type="text/javascript" src="index.php?pf=series/js/jquery.autocomplete.js"></script>'.
		'<script type="text/javascript" src="index.php?pf=series/js/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"dotclear.msg.series_autocomplete = '".html::escapeJS(__('used in %e - frequency %p%'))."';\n".
		"dotclear.msg.entry = '".html::escapeJS(__('entry'))."';\n".
		"dotclear.msg.entries = '".html::escapeJS(__('entries'))."';\n".
		"\n//]]>\n".
		"</script>\n".
		'<link rel="stylesheet" type="text/css" href="index.php?pf=series/style.css" />';
	}

	public static function coreInitWikiPost($wiki2xhtml)
	{
		$wiki2xhtml->registerFunction('url:serie',array('seriesBehaviors','wiki2xhtmlSerie'));
	}

	public static function adminPostEditor($editor='',$context='',array $tags=array())
	{
		if ($editor != 'dcLegacyEditor' || $context != 'post') return;

		$serie_url = $GLOBALS['core']->blog->url.$GLOBALS['core']->url->getURLFor('serie');

		return
		'<script type="text/javascript" src="index.php?pf=series/js/legacy-post.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"jsToolBar.prototype.elements.serie.title = '".html::escapeJS(__('Serie'))."';\n".
		"jsToolBar.prototype.elements.serie.url = '".html::escapeJS($serie_url)."';\n".
		"\n//]]>\n".
		"</script>\n";
	}

    public static function ckeditorExtraPlugins(ArrayObject $extraPlugins, $context)
    {
        global $core;

        if ($context!='post') {
            return;
        }
        $extraPlugins[] = array(
            'name' => 'dcseries',
            'button' => 'dcSeries',
            'url' => DC_ADMIN_URL.'index.php?pf=series/js/ckeditor-series-plugin.js'
        );
    }

	public static function adminUserForm($args)
	{
		if ($args instanceof dcCore) {
			$opts = $args->auth->getOptions();
		}
		elseif ($args instanceof record) {
			$opts = $args->options();
		}
		else {
			$opts = array();
		}

		$combo = array();
		$combo[__('Short')] = 'more';
		$combo[__('Extended')] = 'all';

		$value = array_key_exists('serie_list_format',$opts) ? $opts['serie_list_format'] : 'more';

		echo
		'<p><label for="user_serie_list_format" class="classic">'.__('Series list format:').'</label> '.
		form::combo('user_serie_list_format',$combo,$value).
		'</p>';
	}

	public static function setSerieListFormat($cur,$user_id = null)
	{
		if (!is_null($user_id)) {
			$cur->user_options['serie_list_format'] = $_POST['user_serie_list_format'];
		}
	}
}