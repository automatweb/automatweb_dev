<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/Attic/reminder.aw,v 1.3 2004/12/09 18:18:38 ahti Exp $
// reminder.aw - Meeldetuletus 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_DOCUMENT, on_rconnect_from)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_DOCUMENT, on_rconnect_to)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_DOCUMENT, on_rdisconnect_from)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_TO, CL_DOCUMENT, on_rdisconnect_to)

@classinfo syslog_type=ST_REMINDER relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property remind type=datetime_select year_from=2004 year_to=2010
@caption Millal meelde tuletab

@property emails type=relpicker reltype=RELTYPE_CON_OBJECT multiple=1
@caption E-mail(id), millele meeldetuletus saadetakse

@property subject type=textbox
@caption Teade


@groupinfo objects caption="Seotud objektid" submit=no

@property objects_toolbar type=toolbar no_caption=1 group=objects
@caption Objektide toolbar

@property objects type=table no_caption=1 group=objects
@caption Seotud objektid

@reltype REMINDER_OBJECT value=1 clid=CL_DOCUMENT,CL_MENU
@caption Seotud objekt

*/

class reminder extends class_base
{
	function reminder()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/reminder",
			"clid" => CL_REMINDER
		));
	}
	
	function on_rconnect_to($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("from.class_id") == CL_REMINDER)
		{
			$to = $con->to();
			$to->connect(array(
				"to" => $con->prop("from"),
				"reltype" => "RELTYPE_REMINDER",
			));
		}
	}
	
	function on_rconnect_from($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("to.class_id") == CL_REMINDER)
		{
			$to = $con->to();
			$to->connect(array(
				"to" => $con->prop("from"),
				"reltype" => "RELTYPE_REMINDER_OBJECT",
			));
		}
	}
	
	function on_rdisconnect_to($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("from.class_id") == CL_REMINDER)
		{
			$to = $con->to();
			$to->disconnect(array(
				"from" => $con->prop("from"),
				"reltype" => "RELTYPE_REMINDER",
				"errors" => false,
			));
		}
	}
	
	function on_rdisconnect_from($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("to.class_id") == CL_REMINDER)
		{
			$to = $con->to();
			$to->disconnect(array(
				"from" => $con->prop("from"),
				"reltype" => "RELTYPE_REMINDER_OBJECT",
				"errors" => false,
			));
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "objects_toolbar":
				$this->objects_toolbar($arr);
				break;
			case "objects":
				$this->objects_table($arr);
				break;
		};
		return $retval;
	}
	
	function callback_post_save($arr)
	{
		$scheduler = get_instance("scheduler");
		$rtrue = true;
		$rem = $arr["request"]["remind"];
		foreach(safe_array($rem) as $value)
		{
			if($value == "---")
			{
				$rtrue = false;
			}
		}
		if($rtrue)
		{
			$event = str_replace("/automatweb", "", $this->mk_my_orb("init_action", array(
				"id" => $arr["obj_inst"]->id(),
			)));
			$scheduler->remove(array("event" => $event));
			$scheduler->evnt_add(mktime($rem["hour"], $rem["minute"], 0, $rem["month"], $rem["day"], $rem["year"]), $event);
		}
	}
	
	function objects_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Eemalda valitud objektid"),
			"action" => "disconnect",
			"confirm" => "Oled kindel, et soovid valitud objektid ajastamiselt eemaldada?",
			"img" => "delete.gif",
		));
	}
	
	function objects_table($arr)
	{
		$classes = aw_ini_get("classes");
		$t = &$arr["prop"]["vcl_inst"];
		$var = array(
			"id" => t("ID"),
			"name" => t("Nimi"),
			"type" => t("Tüüp"),
			"change" => t("Muuda"),
		);
		foreach($var as $key => $val)
		{
			$t->define_field(array(
				"name" => $key,
				"caption" => $val,
			));
		}
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
		$objs = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_TIMING_OBJECT",
		));
		foreach($objs as $obj)
		{
			$t->define_data(array(
				"id" => $obj->prop("to"),
				"name" => $obj->prop("to.name"),
				"type" => $classes[$obj->prop("to.class_id")]["name"],
				"change" => html::get_change_url($obj->prop("to"), array(), "Muuda"),
			));
		}
	}

	/**
		@attrib name=disconnect
		
		@param id required type=int acl=edit
		@param group optional
		@param sel required
	**/
	function disconnect($arr)
	{
		$obj_inst = obj($arr["id"]);
		foreach(safe_array($arr["sel"]) as $key => $value)
		{
			$obj = obj($value);
			$obj_inst->disconnect(array(
				"from" => $value,
				"reltype" => "RELTYPE_REMINDER_OBJECT",
			));
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
	
	/**
		@attrib name=init_action no_login=1
		
		@param id required type=int acl=view
	**/
	function init_action($arr)
	{
		//aw_disable_acl();
		$obj_inst = obj($arr["id"]);
		if($obj_inst->status() == STAT_ACTIVE)
		{
			$msg = $obj_inst->prop("subject");
			$subject = !empty($msg) ? $msg : "Meeldetuletus saidilt:";
			$awm = get_instance("protocols/mail/aw_mail");
			$objs = $obj_inst->connections_from(array(
				"type" => "RELTYPE_REMINDER_OBJECT",
			));
			$emls = array();
			$emails = safe_array($obj_inst->prop("emails"));
			foreach($emails as $id)
			{
				if(is_oid($id) && $this->can("view", $id))
				{
					$eml = obj($id);
					$emls[$id] = $eml->prop("mail");
				}
			}
			foreach($objs as $obz)
			{
				$body = $subject." ".$this->mk_my_orb("change", array("id" => $obz->prop("to")), $obz->prop("to.class_id"), true);
				foreach($emls as $addr)
				{
					$awm->create_message(array(
						"froma" => t("aw").str_replace("http://", "@", aw_ini_get("baseurl")),
						"subject" => t("Meeldetuletus"),
						"to" => $addr,
						"body" => $body,
					));
					$awm->gen_mail();
				}
			}
		}
		//aw_enable_acl();
		return "done";
	}
}
?>
