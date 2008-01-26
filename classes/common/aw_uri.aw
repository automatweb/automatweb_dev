<?php
// aw_uri.aw - URI
/*

@classinfo relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property string type=textbox
@caption URI

@property scheme type=text
@property user type=text
@property pass type=text
@property host type=text
@property port type=text
@property path type=text
@property query type=text
@property fragment type=text
@property args type=text

*/

class aw_uri extends class_base
{
	function __construct()
	{
		$this->init(array(
			"tpldir" => "common/aw_uri",
			"clid" => CL_AW_URI
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}

?>
