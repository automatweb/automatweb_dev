<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_search/site_search_content_grp.aw,v 1.17 2005/03/18 12:23:19 ahti Exp $
// site_seaarch_content_grp.aw - Saidi sisu otsingu grupp 
/*

@classinfo syslog_type=ST_SITE_SEARCH_CONTENT_GRP relationmgr=yes

@default table=objects
@default group=general

@property users_only type=checkbox ch_value=1 field=meta method=serialize
@caption Ainult sisse logitud kasutajatele

@property menus type=table editonly=1
@caption Vali men&uuml;&uuml;d

@reltype SEARCH_LOCATION value=1 clid=CL_MENU
@caption Otsingu lähtekoht

*/

class site_search_content_grp extends class_base
{
	function site_search_content_grp()
	{
		$this->init(array(
			"clid" => CL_SITE_SEARCH_CONTENT_GRP
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "menus":
				$this->do_submenus($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "menus":
				$arr["obj_inst"]->set_meta("section_include_submenus", $arr["request"]["include_submenus"]);
				$arr["obj_inst"]->set_meta("notact", $arr["request"]["notact"]);
				break;
		}
		return $retval;
	}	

	function do_submenus($arr)
	{
		if (!is_oid($arr["obj_inst"]->id()))
		{
			return;
		}
		$prop = $arr["prop"];
		$obj = $arr["obj_inst"];
		$section_include_submenus = $obj->meta("section_include_submenus");
		$notact = $obj->meta("notact");
		// now I have to go through the process of setting up a generic table once again
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "class",
			"caption" => "Klass",
		));			
		$t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
		$t->define_field(array(
			"name" => "check",
			"caption" => "k.a. alammenüüd",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));
		
		$t->define_field(array(
			"name" => "check_na",
			"caption" => "mitteaktiivsed",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));
		
		$clinf = aw_ini_get("classes");

		$conns = $obj->connections_from(array(
			"type" => "RELTYPE_SEARCH_LOCATION",
		));


		foreach($conns as $c)
		{
			$c_o = $c->to();
			$cid = $c_o->id();
			$clid = $c_o->class_id();

			$el_arr = array(
				"oid" => $cid,
				"name" => $c_o->path_str(array(
					"max_len" => 3
				)),
				"class" => $clinf[$clid]["name"],
				"check" => html::checkbox(array(
					"name" => "include_submenus[".$cid."]",
					"value" => $cid,
					"checked" => $section_include_submenus[$cid],
				)),
				"check_na" => html::checkbox(array(
					"name" => "notact[".$cid."]",
					"value" => $cid,
					"checked" => $notact[$cid],
				)),
			);
			$t->define_data($el_arr);
		}
	}

	////
	// !returns all the menus that are a part of this search group
	// params
	//	id - group id
	function get_menus($arr)
	{
		if (!is_oid($arr["id"]) || !$this->can("view", $arr["id"]))
		{
			return array();
		}
		$o = obj($arr["id"]);

		$conns = $o->connections_from(array(
			"reltype" => "RELTYPE_SEARCH_LOCATION",
		));
		$se = array();
		foreach($conns as $conn)
		{
			$se[] = $conn->prop("to");
		};

		$sub = $o->meta("section_include_submenus");
		$notact = $o->meta("notact");

		// bloody hell .. this thing should differentiate menus and event searches ..
		// and possibly other objects as well. HOW?

		$ret = array();

		foreach($se as $m)
		{
			if ($sub[$m])
			{
				$ret[$m] = $m;
				$ot = new object_tree(array(
					"class_id" => array(CL_MENU, CL_PROMO),
					"parent" => $m,
					"status" => ($notact[$m] ? array(STAT_ACTIVE,STAT_NOTACTIVE) : STAT_ACTIVE),
					"sort_by" => "objects.parent",
					"lang_id" => array(),
					"site_id" => array(),
					new object_list_filter(array(
						"logic" => "OR",
						"conditions" => array(
							"lang_id" => aw_global_get("lang_id"),
							"type" => MN_CLIENT
						)
					)),
					"sort_by" => "objects.parent, objects.jrk"
				));
				$ids = $ot->ids();

				foreach($ids as $id)
				{
					$ret[$id] = $id;
				}
			}
			else
			{
				$ret[$m] = $m;
			}
		}

		$gidlist = aw_global_get("gidlist");
		$ol = new object_list(array(
			"class_id" => array(CL_PROMO),
			"oid" => $ret,
			"site_id" => array(),
			"lang_id"  => array()
		));
		foreach($ol->arr() as $o)
		{
			// filter list by groups to whom the promo can be shown
			$found = false;
			$groups = $o->meta("groups");
			if (!is_array($groups) || count($groups) < 1)
			{
				$found = true;
			}
			else
			{
				foreach($groups as $gid)
				{
					if (isset($gidlist[$gid]) && $gidlist[$gid] == $gid)
					{
						$found = true;
					}
				}
			}

			if (!$found)
			{
				unset($ret[$o->id()]);
			}
		}
		
		// if no user is logged on, then filter the list by "users_only"
		if (aw_global_get("uid") == "")
		{
			$ol = new object_list(array(
				"class_id" => array(CL_MENU, CL_PROMO),
				"oid" => $ret,
				"site_id" => array(),
				"lang_id"  => array()
			));
			$ret = array();
			foreach($ol->arr() as $o)
			{
				if (!$o->prop("users_only"))
				{
					$ret[$o->id()] = $o->id();
				}
			}
		}
		return $ret;
	}
	
}
?>
