<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/mail_rule.aw,v 1.5 2004/02/25 15:46:40 duke Exp $
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
		$this->init(array(
			"clid" => CL_MAIL_RULE
		));
	}

	////
	// !Initialize object. Keep in mind that this will only be called for new objects,
	// so you need to save the rule object before it starts working.
	function callback_pre_edit($arr)
	{
		$return_url = $arr["request"]["return_url"];
		$this->folders = array();
		if (!empty($return_url))
		{
			// XXX: get messenger id from return url, bad idea really
			// but I do need a list of folders to make filtering work
			parse_str($return_url,$tmp);
			if (!empty($tmp["id"]))
			{
				$msgr_obj = new object($tmp["id"]);
				if ($msgr_obj->class_id() == CL_MESSENGER_V2)
				{
					$msgr = $msgr_obj->instance();
					$msgr->_connect_server(array("msgr_id" => $msgr_obj->id()));
					$tmp = $msgr->drv_inst->list_folders();
					foreach($tmp as $item)
					{
						$this->folders[$item["name"]] = $item;
					};
				};
			};
		};
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "target_folder":
				$tmp = array();
				$folders = new aw_array($this->folders);
				foreach($folders->get() as $key => $fld)
				{
					$tmp[$key] = $fld["name"];
				};
				$data["options"] = $tmp;
				break;

		};
		return $retval;
	}
};
?>
