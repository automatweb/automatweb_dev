<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/mail_rule.aw,v 1.7 2004/03/25 19:40:05 duke Exp $
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
	// !Returns the owner (messenger) of the current rule object
	function get_owner($arr)
	{
		$from = aw_global_get("from_obj");
		if (!empty($from))
		{
			$msgr_id = $from;
		}
		else
		{
			$msgr = $arr["obj_inst"]->connections_to(array(
				"from.class_id" => CL_MESSENGER_V2,
			));
			$msgr_id = false;
			foreach($msgr as $item)
			{
				$msgr_id = $item->prop("from");
			};
		};
		return $msgr_id;
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "target_folder":
				$tmp = array();
				$folders = array();
				$msgr_id = $this->get_owner(array(
					"obj_inst" => $arr["obj_inst"],
				));


				if (!empty($msgr_id))
				{
					$msgr_obj = new object($msgr_id);
					if ($msgr_obj->class_id() == CL_MESSENGER_V2)
					{
						$msgr = $msgr_obj->instance();
						$msgr->_connect_server(array("msgr_id" => $msgr_obj->id()));
						$tmp = $msgr->drv_inst->list_folders();
						foreach($tmp as $item)
						{
							$folders[$item["fullname"]] = $item;
						};
					};
				};
				foreach($folders as $key => $fld)
				{
					$tmp[$key] = $fld["name"];
				};
				$data["options"] = $tmp;
				break;

		};
		return $retval;
	}

	function callback_pre_save($arr)
	{
		$name = $arr["obj_inst"]->name();
		$subj = $arr["obj_inst"]->prop("rule_subject");
		$from = $arr["obj_inst"]->prop("rule_from");
		$name_parts = array();
		if (empty($name))
		{
			if (!empty($subj))
			{
				$name_parts[] = "Subject: $subj";
			};

			if (!empty($from))
			{
				$name_parts[] = "From: $from";
			};
		};
		if (empty($name) && count($name_parts) > 0)
		{
			$arr["obj_inst"]->set_name(join(", ",$name_parts));
		};
	}
};
?>
