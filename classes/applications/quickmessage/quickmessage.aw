<?php

// quickmessage.aw - Kiirteade
/*

@classinfo syslog_type=ST_QUICKMESSAGE no_status=1 no_comment=1 maintainer=voldemar
@tableinfo quickmessages index=id master_table=objects master_index=brother_of

@default table=quickmessages
@default group=general

@property msg_status type=hidden datatype=int
@property msg_type type=hidden datatype=int
@property box type=hidden datatype=int

@property user_to type=relpicker store=no
@caption Kellele

@property msg type=textarea
@caption Teade

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
		}

		return $retval;
	}
}
?>
