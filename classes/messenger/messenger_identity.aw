<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/messenger_identity.aw,v 1.2 2003/11/26 16:27:28 duke Exp $
// messenger_identity.aw - Messengeri identiteet 
/*

@classinfo syslog_type=ST_MESSENGER_IDENTITY 

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property email type=textbox 
@caption E-post

@property reply_to type=textbox
@caption Reply To

@property organization type=textbox
@caption Organisatsioon

@property signature type=textarea cols=40 rows=5
@caption Signatuur

*/

class messenger_identity extends class_base
{
	function messenger_identity()
	{
		$this->init(array(
			"clid" => CL_MESSENGER_IDENTITY
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "comment":
			case "status":
				$retval = PROP_IGNORE;
				break;


		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {
			case "status":
				$data["value"] = STAT_ACTIVE;
				break;

		}
		return $retval;
	}	
}
?>
