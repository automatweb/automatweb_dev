<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/timing.aw,v 1.10 2005/04/21 08:22:10 kristo Exp $
// timing.aw - Ajaline aktiivsus
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_DOCUMENT, on_tconnect_from)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_MENU, on_tconnect_from)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_DOCUMENT, on_tconnect_to)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_MENU, on_tconnect_to)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_DOCUMENT, on_tdisconnect_from)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_MENU, on_tdisconnect_from)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_TO, CL_DOCUMENT, on_tdisconnect_to)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_TO, CL_MENU, on_tdisconnect_to)

@classinfo syslog_type=ST_TIMING relationmgr=yes no_status=1

@default table=objects
@default group=general

@property activate type=datetime_select year_from=2004 year_to=2010 field=meta method=serialize
@caption Aktiveerida

@property deactivate type=datetime_select year_from=2004 year_to=2010 field=meta method=serialize
@caption Deaktiveerida

@groupinfo objects caption="Seotud objektid" submit=no

@property objects_toolbar type=toolbar no_caption=1 group=objects
@caption Objektide toolbar

@property objects type=table no_caption=1 group=objects
@caption Seotud objektid

@reltype TIMING_OBJECT value=1 clid=CL_DOCUMENT,CL_MENU
@caption Seotud objekt

*/

class timing extends class_base
{
	function timing()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/timing",
			"clid" => CL_TIMING,
		));
	}
	
	function on_tconnect_to($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("from.class_id") == CL_TIMING)
		{
			$to = $con->to();
			$to->connect(array(
				"to" => $con->prop("from"),
				"reltype" => "RELTYPE_TIMING",
			));
		}
	}
	
	function on_tconnect_from($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("to.class_id") == CL_TIMING)
		{
			$to = $con->to();
			$to->connect(array(
				"to" => $con->prop("from"),
				"reltype" => "RELTYPE_TIMING_OBJECT",
			));
		}
	}

	function on_tdisconnect_to($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("from.class_id") == CL_TIMING)
		{
			$to = $con->to();
			$to->disconnect(array(
				"from" => $con->prop("from"),
				"reltype" => "RELTYPE_TIMING",
				"errors" => false,
			));
		}
	}
	
	function on_tdisconnect_from($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("to.class_id") == CL_TIMING)
		{
			$to = $con->to();
			$to->disconnect(array(
				"from" => $con->prop("from"),
				"reltype" => "RELTYPE_TIMING_OBJECT",
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
			case "activate":
				if($arr["new"])
				{
					$prop["value"] = -1;
				}
				break;
			case "deactivate":
				if($arr["new"])
				{
					$prop["value"] = -1;
				}
				break;
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
		$atrue = true;
		$datrue = true;
		$act = $arr["request"]["activate"];
		$deact = $arr["request"]["deactivate"];
		foreach(safe_array($act) as $value)
		{
			if($value == "---")
			{
				$atrue = false;
			}
		}
		foreach(safe_array($deact) as $value)
		{
			if($value == "---")
			{
				$datrue = false;
			}
		}
		if($atrue)
		{
			$event = $this->mk_my_orb("init_action", array(
				"subaction" => "activate", 
				"id" => $arr["obj_inst"]->id(),
			));
			$scheduler->remove(array("event" => $event));
			$scheduler->add(array(
				"time" => mktime($act["hour"], $act["minute"], 0, $act["month"], $act["day"], $act["year"]),
				"event" =>  $event,
				"uid" => aw_global_get("uid"),
				"auth_as_local_user" => true,
			));
		}
		if($datrue)
		{
			$event = $this->mk_my_orb("init_action", array(
				"subaction" => "deactivate", 
				"id" => $arr["obj_inst"]->id(),
			));
			$scheduler->remove(array("event" => $event));
			$scheduler->add(array(
				"time" => mktime($deact["hour"], $deact["minute"], 0, $deact["month"], $deact["day"], $deact["year"]), 
				"event" => $event,
				"uid" => aw_global_get("uid"),
				"auth_as_local_user" => true,
			));
		}
		//$time, $event, $uid = "", $password = "", $rep_id = 0, $event_id = "", $sessid ="")
	}
	
	function objects_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Eemalda valitud objektid"),
			"action" => "disconnect",
			"confirm" => t("Oled kindel, et soovid valitud objektid ajastamiselt eemaldada?"),
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
				"name" => html::get_change_url($obj->prop("to"), array(), $obj->prop("to.name")),
				"type" => $classes[$obj->prop("to.class_id")]["name"],
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
				"reltype" => "RELTYPE_TIMING_OBJECT",
				"errors" => false,
			));
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
	
	/**
		@attrib name=init_action
		
		@param id required type=int acl=view
		@param subaction required
	**/
	function init_action($arr)
	{
		$obj_inst = obj($arr["id"]);
		$objs = $obj_inst->connections_from(array(
			"type" => "RELTYPE_TIMING_OBJECT",
		));
		foreach($objs as $obz)
		{
			$obj = $obz->to();
			$obj->set_status(($arr["subaction"] == "activate" ? STAT_ACTIVE : STAT_NOTACTIVE));
			$obj->save();
		}
		$mait = get_instance("cache");
		$mait->full_flush();
		return "done";
	}
}
?>
