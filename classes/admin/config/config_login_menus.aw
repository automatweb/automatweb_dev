<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/config/config_login_menus.aw,v 1.11 2004/12/01 13:21:59 kristo Exp $
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

		$lm = $this->_get_login_menus();

		foreach($gl as $gid => $gdat)
		{
			$lm[aw_global_get("lang_id")][$gid]["menu"] = $o_lm[$gid]["menu"];
			$lm[aw_global_get("lang_id")][$gid]["pri"] = $o_lm[$gid]["pri"];
		}

		$data = aw_serialize($lm);
		$this->quote($data);
		$this->set_cval("login_menus_".aw_ini_get("site_id"),$data);
		
	}

	function callback_get_login_menus($arr)
	{
		// foreach group add relpicker
		$ret = array();

		$us = get_instance("users");
		$gl = $us->get_group_list(array("type" => array(GRP_DYNAMIC, GRP_REGULAR)));

		$lm = $arr["obj_inst"]->meta("lm");


		foreach($gl as $gid => $gdat)
		{
			/*
			$node = array();
			$node["caption"] = $gdat["name"];
			$node["store"] = "no";
			$node["name"] = "grp_tx_".$gid;
			$node["items"] = array();
			$node["group"] = "general";

			$ret[] = $node;
			*/

			$tmp = array(
				"type" => "textbox",
				"caption" => $gdat["name"],
				"size" => 4,
				"name" => "lm[$gid][pri]",
				"value" => $lm[$gid]["pri"]
			);
			//array_push($node["items"], $tmp);
			$ret[] = $tmp;

			$tmp = array(
				"caption" => $gdat["name"] . t(" menyy"),
				"type" => "relpicker",
				"name" => "lm[$gid][menu]",
				"value" => $lm[$gid]["menu"],
				"reltype" => "RELTYPE_FOLDER"
			);
			//array_push($node["items"], $tmp);

			//$ret[] = $node;
			$ret[] = $tmp;
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

	/**  
		
		@attrib name=find_active_edit params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	function on_site_init(&$dbi, &$site, &$ini_opts, &$log, $vars)
	{
		// we are using the new db as the default, so we can create objects
		$oid = $dbi->db_fetch_field("SELECT oid FROM objects WHERE site_id = '".$ini_opts["site_id"]."' AND class_id = ".CL_CONFIG_LOGIN_MENUS, "oid");
		$o = obj($oid);
		$o->set_flag(OBJ_FLAG_IS_SELECTED, true);

		$o->connect(array(
			"to" => $vars["logged_users"],
			"reltype" => RELTYPE_FOLDER
		));

		$o->connect(array(
			"to" => $vars["logged_admins"],
			"reltype" => RELTYPE_FOLDER
		));

		$o->connect(array(
			"to" => $vars["logged_editors"],
			"reltype" => RELTYPE_FOLDER
		));

		// and manually set login menu conf as correct
		$data = array();
		for ($lid = 1; $lid < 5; $lid++)
		{
			$data[$lid][2]["menu"] = $vars["logged_admins"];
			$data[$lid][2]["pri"] = 1000;
	
			$data[$lid][1]["menu"] = $vars["logged_users"];
			$data[$lid][1]["pri"] = 100;

			$data[$lid][3]["menu"] = $vars["logged_editors"];
			$data[$lid][3]["pri"] = 120;

			$data[$lid][4]["menu"] = $vars["logged_users"];
			$data[$lid][4]["pri"] = 110;

			$data[$lid][5]["menu"] = $vars["logged_users"];
			$data[$lid][5]["pri"] = 110;

			if ($lid == 1)
			{
				$o->set_meta("lm", $data[$lid]);
			}
		}
		$o->save();

		$str = aw_serialize($data);
		$this->quote(&$str);
		$dbi->db_query("INSERT INTO config(ckey,content) values('login_menus_".$ini_opts["site_id"]."','$str')");
	}

	////
	// Votab argumentidena gidlisti, ning üritab tagastada oige login menüü
	// aadressi.
	function get_login_menus($args = array())
	{
		$_data = $this->_get_login_menus();
		$data = $_data[aw_global_get("lang_id")];
		if (!is_array($data))
		{
			if (is_array($_data))
			{
				foreach($_data as $k => $v)
				{
					if (is_array($v))
					{
						$data = $v;
					}
				}
			}
		};

		if (!is_array($data))
		{
			return;
		}

		$gids = aw_global_get("gidlist");
		$cur_pri = -1;
		$cur_menu = -1;

		if (!is_array($gids))
		{
			return;
		};

		foreach($gids as $gid)
		{
			if (($data[$gid]["pri"] > $cur_pri) && ($data[$gid]["menu"]))
			{
				$cur_pri = $data[$gid]["pri"];
				$cur_menu = $data[$gid]["menu"];
			}
		};

		return $cur_menu;
	}

	function _get_login_menus($args = array())
	{
		$sid = aw_ini_get("site_id");
		$res = $this->get_cval("login_menus_".$sid);
		if (!$res)
		{
			$res = $this->get_cval("login_menus");
		}
		return aw_unserialize($res);
	}
}
?>
