<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/config/config_login_menus.aw,v 1.2 2003/12/24 11:13:08 kristo Exp $
// config_login_menus.aw - Login men&uuml;&uuml;d 
/*

@classinfo syslog_type=ST_CONFIG_LOGIN_MENUS relationmgr=yes
@classinfo no_status=1

@default table=objects
@default group=general

@property login_menus type=callback callback=callback_get_login_menus store=no

@groupinfo activity caption=Aktiivsus

@property activity type=table group=activity no_caption=1
@caption Aktiivsus

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

*/

class config_login_menus extends class_base
{
	function config_login_menus()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "admin/config/config_login_menus",
			"clid" => CL_CONFIG_LOGIN_MENUS
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "activity":
				$this->mk_activity_table($arr);
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
			case "login_menus":
				$arr["obj_inst"]->set_meta("lm", $arr["request"]["lm"]);
				if ($arr["obj_inst"]->flag(OBJ_FLAG_IS_SELECTED))
				{
					$this->_set_active_menus($arr["obj_inst"]);
				}
				break;

			case "activity":
				$ol = new object_list(array(
					"class_id" => CL_CONFIG_LOGIN_MENUS,
				));
				for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
				{
					if ($o->flag(OBJ_FLAG_IS_SELECTED) && $o->id() != $arr["request"]["active"])
					{
						$o->set_flag(OBJ_FLAG_IS_SELECTED, false);
						$o->save();
					}
					else
					if ($o->id() == $arr["request"]["active"] && !$o->flag(OBJ_FLAG_IS_SELECTED))
					{
						$o->set_flag(OBJ_FLAG_IS_SELECTED, true);
						$o->save();
						$this->_set_active_menus($o);
					}
				}
				break;
		}
		return $retval;
	}	

	function _set_active_menus($o)
	{
		$o_lm = $o->meta("lm");
		
		$us = get_instance("users");
		$gl = $us->get_group_list(array("type" => array(GRP_DYNAMIC, GRP_REGULAR)));

		$conf = get_instance("config");
		$lm = $conf->_get_login_menus();

		foreach($gl as $gid => $gdat)
		{
			$lm[aw_global_get("lang_id")][$gid]["menu"] = $o_lm[$gid]["menu"];
			$lm[aw_global_get("lang_id")][$gid]["pri"] = $o_lm[$gid]["pri"];
		}

		$data = aw_serialize($lm);
		$this->quote($data);
		$conf->set_simple_config("login_menus_".aw_ini_get("site_id"),$data);
		
	}

	function callback_get_login_menus($arr)
	{
		// foreach group add relpicker
		$ret = array();

		$us = get_instance("users");
		$gl = $us->get_group_list(array("type" => array(GRP_DYNAMIC, GRP_REGULAR)));

		$lm = $arr["obj_inst"]->meta("lm");

		$node = array();
		$node["caption"] = "<b>Grupp</b>";
		$node["store"] = "no";
		$node["name"] = "grp_txaa";
		$node["items"] = array();
		$node["group"] = "general";

		$tmp = array(
			"type" => "text",
			"value" => "Prioriteet"
		);
		array_push($node["items"], $tmp);

		$tmp = array(
			"type" => "text",
			"value" => "Login men&uuml;&uuml;",
		);
		array_push($node["items"], $tmp);

		$ret[] = $node;

		foreach($gl as $gid => $gdat)
		{
			$node = array();
			$node["caption"] = $gdat["name"];
			$node["store"] = "no";
			$node["name"] = "grp_tx_".$gid;
			$node["items"] = array();
			$node["group"] = "general";

			$tmp = array(
				"type" => "textbox",
				"size" => 4,
				"name" => "lm[$gid][pri]",
				"value" => $lm[$gid]["pri"]
			);
			array_push($node["items"], $tmp);

			$tmp = array(
				"type" => "relpicker",
				"name" => "lm[$gid][menu]",
				"value" => $lm[$gid]["menu"],
				"reltype" => RELTYPE_FOLDER
			);
			array_push($node["items"], $tmp);

			$ret[] = $node;
		}

		return $ret;
	}

	function mk_activity_table($arr)
	{
		// this is supposed to return a list of all active polls
		// to let the user choose the active one
		$table = &$arr["prop"]["vcl_inst"];
		$table->parse_xml_def("activity_list");

		$pl = new object_list(array(
			"class_id" => CL_CONFIG_LOGIN_MENUS
		));	
		for($o = $pl->begin(); !$pl->end(); $o = $pl->next())
		{
			$actcheck = checked($o->flag(OBJ_FLAG_IS_SELECTED));
			$act_html = "<input type='radio' name='active' $actcheck value='".$o->id()."'>";
			$row = $o->arr();
			$row["active"] = $act_html;
			$table->define_data($row);
		};
	}

	function find_active_edit($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CONFIG_LOGIN_MENUS,
			"flags" => array("mask" => OBJ_FLAG_IS_SELECTED, "flags" => OBJ_FLAG_IS_SELECTED)
		));
		// if none found, go to the old interface
		if ($ol->count() < 1)
		{
			return $this->mk_my_orb("login_menus", array(), "config");
		}
		$o = $ol->begin();
		return $this->mk_my_orb("change", array("id" => $o->id()));
	}
}
?>
