<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_v2.aw,v 1.1 2003/06/04 13:57:13 duke Exp $
// forum.aw - forum management
class forum_v2 extends class_base
{
	function forum_v2()
	{
		$this->init(array(
			"clid" => CL_FORUM,
			"tpldir" => "forum",
		));
	}

};
?>
