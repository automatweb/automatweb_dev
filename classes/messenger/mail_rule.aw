<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/mail_rule.aw,v 1.2 2003/10/28 16:30:27 duke Exp $
// mail_rule.aw - Maili ruul 
/*

@classinfo syslog_type=ST_MAIL_RULE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property rule_from type=textbox
@caption From

@property rule_subject type=textbox
@caption Subject

@property target_folder type=select
@caption Liiguta folderisse

*/

class mail_rule extends class_base
{
	function mail_rule()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "messenger/mail_rule",
			"clid" => CL_MAIL_RULE
		));
	}

	function callback_pre_edit($arr)
	{
		$return_url = $arr["request"]["return_url"];
		$this->folders = array();
		if (!empty($return_url))
		{
			parse_str($return_url,$tmp);
			if (!empty($tmp["id"]))
			{
				$msgr_obj = new object($tmp["id"]);
				if ($msgr_obj->class_id() == CL_MESSENGER_V2)
				{
					$msgr = get_instance("messenger/messenger_v2");
					$msgr->_connect_server(array("msgr_id" => $msgr_obj->id()));
					$this->folders = $msgr->drv_inst->list_folders();
				};
			};
		};
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "target_folder":
				$tmp = array();
				foreach($this->folders as $key => $fld)
				{
					$tmp[$key] = $fld["name"];
				};
				$data["options"] = $tmp;
				break;

		};
		return $retval;
	}

	/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

}
?>
