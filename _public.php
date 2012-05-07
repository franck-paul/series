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
if (!defined('DC_RC_PATH')) { return; }

# Localized string we find in template
__("This serie's comments Atom feed");
__("This serie's entries Atom feed");


require dirname(__FILE__).'/_widgets.php';

$core->tpl->addBlock('Series',array('tplSeries','Series'));
$core->tpl->addBlock('SeriesHeader',array('tplSeries','SeriesHeader'));
$core->tpl->addBlock('SeriesFooter',array('tplSeries','SeriesFooter'));
$core->tpl->addBlock('EntrySeries',array('tplSeries','EntrySeries'));
$core->tpl->addValue('SerieID',array('tplSeries','SerieID'));
$core->tpl->addValue('SeriePercent',array('tplSeries','SeriePercent'));
$core->tpl->addValue('SerieRoundPercent',array('tplSeries','SerieRoundPercent'));
$core->tpl->addValue('SerieURL',array('tplSeries','SerieURL'));
$core->tpl->addValue('SerieCloudURL',array('tplSeries','SerieCloudURL'));
$core->tpl->addValue('SerieFeedURL',array('tplSeries','SerieFeedURL'));


$core->addBehavior('templateBeforeBlock',array('behaviorsSeries','templateBeforeBlock'));
$core->addBehavior('tplSysIfConditions',array('behaviorsSeries','tplSysIfConditions'));
$core->addBehavior('publicBeforeDocument',array('behaviorsSeries','addTplPath'));

class behaviorsSeries
{
	public static function templateBeforeBlock($core,$b,$attr)
	{
		if (($b == 'Entries' || $b == 'Comments') && isset($attr['serie']))
		{
			return
			"<?php\n".
			"@\$params['from'] .= ', '.\$core->prefix.'meta META ';\n".
			"@\$params['sql'] .= 'AND META.post_id = P.post_id ';\n".
			"\$params['sql'] .= \"AND META.meta_type = 'serie' \";\n".
			"\$params['sql'] .= \"AND META.meta_id = '".$core->con->escape($attr['serie'])."' \";\n".
			"?>\n";
		}
		elseif (empty($attr['no_context']) && ($b == 'Entries' || $b == 'Comments'))
		{
			return
			'<?php if ($_ctx->exists("meta")) { '.
				"@\$params['from'] .= ', '.\$core->prefix.'meta META ';\n".
				"@\$params['sql'] .= 'AND META.post_id = P.post_id ';\n".
				"\$params['sql'] .= \"AND META.meta_type = 'serie' \";\n".
				"\$params['sql'] .= \"AND META.meta_id = '\".\$core->con->escape(\$_ctx->meta->meta_id).\"' \";\n".
			"} ?>\n";
		}
	}
	
	public static function tplSysIfConditions($serie, $attr,$content,$if)
	{
		if ($serie == 'Sys' && isset($attr['in_serie'])) {
			$sign = '';
			if (substr($attr['in_serie'],0,1) == '!') {
				$sign = '!';
				$attr['in_serie'] = substr($attr['in_serie'],1);
			}
			$if[] =  $sign."(\$core->tpl->serieExists('".addslashes($attr['in_serie'])."') )";
		}
	}
	
	public static function addTplPath($core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}

}

class tplSeries
{
	public static function Series($attr,$content)
	{
		$type = isset($attr['type']) ? addslashes($attr['type']) : 'serie';
		
		$limit = isset($attr['limit']) ? (integer) $attr['limit'] : 'null';
		
		$sortby = 'meta_id_lower';
		if (isset($attr['sortby']) && $attr['sortby'] == 'count') {
			$sortby = 'count';
		}
		
		$order = 'asc';
		if (isset($attr['order']) && $attr['order'] == 'desc') {
			$order = 'desc';
		}
		
		$res =
		"<?php\n".
		"\$_ctx->meta = \$core->meta->computeMetaStats(\$core->meta->getMetadata(array('meta_type'=>'"
			.$type."','limit'=>".$limit."))); ".
		"\$_ctx->meta->sort('".$sortby."','".$order."'); ".
		'?>';
		
		$res .=
		'<?php while ($_ctx->meta->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->meta = null; ?>';
		
		return $res;
	}
	
	public static function SeriesHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->meta->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function SeriesFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->meta->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function EntrySeries($attr,$content)
	{
		$type = isset($attr['type']) ? addslashes($attr['type']) : 'serie';
		
		$sortby = 'meta_id_lower';
		if (isset($attr['sortby']) && $attr['sortby'] == 'count') {
			$sortby = 'count';
		}
		
		$order = 'asc';
		if (isset($attr['order']) && $attr['order'] == 'desc') {
			$order = 'desc';
		}
		
		$res =
		"<?php\n".
		"\$_ctx->meta = \$core->meta->getMetaRecordset(\$_ctx->posts->post_meta,'".$type."'); ".
		"\$_ctx->meta->sort('".$sortby."','".$order."'); ".
		'?>';
		
		$res .=
		'<?php while ($_ctx->meta->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->meta = null; ?>';
		
		return $res;
	}
	
	public static function SerieID($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->meta->meta_id').'; ?>';
	}
	
	public static function SeriePercent($attr)
	{
		return '<?php echo $_ctx->meta->percent; ?>';
	}
	
	public static function SerieRoundPercent($attr)
	{
		return '<?php echo $_ctx->meta->roundpercent; ?>';
	}
	
	public static function SerieURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("serie",'.
		'rawurlencode($_ctx->meta->meta_id))').'; ?>';
	}
	
	public static function SerieCloudURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("series")').'; ?>';
	}
	
	public static function SerieFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'rss2';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'rss2';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("serie_feed",'.
		'rawurlencode($_ctx->meta->meta_id)."/'.$type.'")').'; ?>';
	}
	
	# Widget function
	public static function seriesWidget($w)
	{
		global $core;
		
		$params = array('meta_type' => 'serie');
		
		if ($w->limit !== '') {
			$params['limit'] = abs((integer) $w->limit);
		}
		
		$rs = $core->meta->computeMetaStats(
			$core->meta->getMetadata($params));
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$sort = $w->sortby;
		if (!in_array($sort,array('meta_id_lower','count'))) {
			$sort = 'meta_id_lower';
		}
		
		$order = $w->orderby;
		if ($order != 'asc') {
			$order = 'desc';
		}
		
		$rs->sort($sort,$order);
		
		$res =
		'<div class="series">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch())
		{
			$res .=
			'<li><a href="'.$core->blog->url.$core->url->getURLFor('serie',rawurlencode($rs->meta_id)).'" '.
			'class="serie'.$rs->roundpercent.'" rel="serie">'.
			$rs->meta_id.'</a> </li>';
		}
		
		$res .= '</ul>';
		
		if ($core->url->getBase('series') && !is_null($w->allserieslinktitle) && $w->allserieslinktitle !== '')
		{
			$res .=
			'<p><strong><a href="'.$core->blog->url.$core->url->getURLFor("series").'">'.
			html::escapeHTML($w->allserieslinktitle).'</a></strong></p>';
		}
		
		$res .= '</div>';
		
		return $res;
	}
}

class urlSeries extends dcUrlHandlers
{
	public static function serie($args)
	{
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n)
		{
			self::p404();
		}
		elseif (preg_match('%(.*?)/feed/(rss2|atom)?$%u',$args,$m))
		{
			$type = $m[2] == 'atom' ? 'atom' : 'rss2';
			$mime = 'application/xml';
			$comments = !empty($m[3]);
			
			$GLOBALS['_ctx']->meta = $GLOBALS['core']->meta->computeMetaStats(
				$GLOBALS['core']->meta->getMetadata(array(
					'meta_type' => 'serie',
					'meta_id' => $m[1])));
			
			if ($GLOBALS['_ctx']->meta->isEmpty()) {
				self::p404();
			}
			else
			{
				$tpl = $type;
				
				if ($type == 'atom') {
					$mime = 'application/atom+xml';
				}
				
				self::serveDocument($tpl.'.xml',$mime);
			}
		}
		else
		{
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}
			
			$GLOBALS['_ctx']->meta = $GLOBALS['core']->meta->computeMetaStats(
				$GLOBALS['core']->meta->getMetadata(array(
					'meta_type' => 'serie',
					'meta_id' => $args)));
			
			if ($GLOBALS['_ctx']->meta->isEmpty()) {
				self::p404();
			} else {
				self::serveDocument('serie.html');
			}
		}
	}
	
	public static function series($args)
	{
		self::serveDocument('series.html');
	}
	
	public static function serieFeed($args)
	{
		if (!preg_match('#^(.+)/(atom|rss2)(/comments)?$#',$args,$m))
		{
			self::p404();
		}
		else
		{
			$serie = $m[1];
			$type = $m[2];
			$comments = !empty($m[3]);
			
			$GLOBALS['_ctx']->meta = $GLOBALS['core']->meta->computeMetaStats(
				$GLOBALS['core']->meta->getMetadata(array(
					'meta_type' => 'serie',
					'meta_id' => $serie)));
			
			if ($GLOBALS['_ctx']->meta->isEmpty()) {
				# The specified serie does not exist.
				self::p404();
			}
			else
			{
				$GLOBALS['_ctx']->feed_subtitle = ' - '.__('Serie').' - '.$GLOBALS['_ctx']->meta->meta_id;
				
				if ($type == 'atom') {
					$mime = 'application/atom+xml';
				} else {
					$mime = 'application/xml';
				}
				
				$tpl = $type;
				if ($comments) {
					$tpl .= '-comments';
					$GLOBALS['_ctx']->nb_comment_per_page = $GLOBALS['core']->blog->settings->system->nb_comment_per_feed;
				} else {
					$GLOBALS['_ctx']->nb_entry_per_page = $GLOBALS['core']->blog->settings->system->nb_post_per_feed;
					$GLOBALS['_ctx']->short_feed_items = $GLOBALS['core']->blog->settings->system->short_feed_items;
				}
				$tpl .= '.xml';
				
				self::serveDocument($tpl,$mime);
			}
		}
	}
}
?>