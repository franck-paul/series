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

$core->addBehavior('xmlrpcGetPostInfo',array('seriesXMLRPCbehaviors','getPostInfo'));
$core->addBehavior('xmlrpcAfterNewPost',array('seriesXMLRPCbehaviors','editPost'));
$core->addBehavior('xmlrpcAfterEditPost',array('seriesXMLRPCbehaviors','editPost'));

class seriesXMLRPCbehaviors
{
	public static function getPostInfo($x,$type,$res)
	{
		$res =& $res[0];
		
		$rs = $x->core->meta->getMetadata(array(
			'meta_type' => 'serie',
			'post_id' => $res['postid']));
		
		$m = array();
		while($rs->fetch()) {
			$m[] = $rs->meta_id;
		}
		
		$res['mt_keywords'] = implode(', ',$m);
	}
	
	# Same function for newPost and editPost
	public static function editPost($x,$post_id,$cur,$content,$struct,$publish)
	{
		# Check if we have mt_keywords in struct
		if (isset($struct['mt_keywords']))
		{
			$x->core->meta->delPostMeta($post_id,'serie');
			
			foreach ($x->core->meta->splitMetaValues($struct['mt_keywords']) as $m) {
				$x->core->meta->setPostMeta($post_id,'serie',$m);
			}
		}
	}
}
?>