<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/quickmessage/quickmessage.aw,v 1.8 2004/11/18 17:21:47 ahti Exp $
// quickmessage.aw - Kiirteade  
/*

@classinfo syslog_type=ST_QUICKMESSAGE relationmgr=yes no_status=1 no_comment=1
@tableinfo quickmessages index=id master_table=objects master_index=brother_of

@default table=quickmessages
@default group=general


@property mstatus type=hidden table=objects field=meta method=serialize datatype=int
@caption 

@property user_from type=text
@caption Kellelt

@property user_to type=textbox
@caption Kellele

@property subject type=textbox
@caption Teema

@property content type=textarea
@caption Sisu

*/

class quickmessage extends class_base
{
	function quickmessage()
	{
		$this->init(array(
			"clid" => CL_QUICKMESSAGE
		));
	}
	function get_property($arr)
	{
		//arr($arr);
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "user_from":
				$prop["value"] = aw_global_get("uid");
				break;
			case "name":
				$retval = PROP_IGNORE;
				break;

		};
		return $retval;
	}

}
?>
