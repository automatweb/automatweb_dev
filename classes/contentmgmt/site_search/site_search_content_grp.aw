<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_search/site_search_content_grp.aw,v 1.5 2004/02/13 16:13:13 duke Exp $
// site_seaarch_content_grp.aw - Saidi sisu otsingu grupp 
/*

@classinfo syslog_type=ST_SITE_SEARCH_CONTENT_GRP relationmgr=yes

@default table=objects
@default group=general

@property users_only type=checkbox ch_value=1 field=meta method=serialize
@caption Ainult sisse logitud kasutajatele

@property menus type=text store=no callback=callback_get_menus edit_only=1
@caption Vali men&uuml;&uuml;d

@reltype MENU value=1 clid=CL_MENU
@caption menüü, mille alt otsitakse

*/

class site_search_content_grp extends class_base
{
	function site_search_content_grp()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/site_search/site_search_content_grp",
			"clid" => CL_SITE_SEARCH_CONTENT_GRP
		));
	}

/*	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "menus":
				
				break;
		};
		return $retval;
	}*/

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "menus":
				$args["obj_inst"]->set_meta("section_include_submenus", $args["request"]["include_submenus"]);
				break;
		}
		return $retval;
	}	

	function callback_get_menus($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();
		$section_include_submenus = $args["obj_inst"]->meta("section_include_submenus");
		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "sgrp_menus",
			"layout" => "generic"
		));
		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
		));
		$this->t->define_field(array(
			"name" => "check",
			"caption" => "k.a. alammenüüd",
			"talign" => "center",
			"width" => 80,
			"align" => "center",
		));

		$obj = $args["obj_inst"];
		$conns = $obj->connections_from(array(
			"type" => RELTYPE_MENU
		));
		foreach($conns as $c)
		{
			$c_o = $c->to();

			$this->t->define_data(array(
				"oid" => $c_o->id(),
				"name" => $c_o->path_str(array(
					"max_len" => 3
				))."/".$c_o->name(),
				"check" => html::checkbox(array(
					"name" => "include_submenus[".$c_o->id()."]",
					"value" => $c_o->id(),
					"checked" => $section_include_submenus[$c_o->id()],
				)),
			));
		}
 
		$nodes[$prop["name"]] = array(
			"type" => "text",
			"caption" => $prop["caption"],
			"value" => $this->t->draw(),
		);
		return $nodes;
	}

	function callback_on_submit_relation_list($args = array())
	{
		// this is where we put data back into object metainfo, for backwards compatibility
		$obj =& obj($args["id"]);

		$oldaliases = $obj->connections_from(array(
			"type" => RELTYPE_MENU
		));
		
		$section = array();
		foreach($oldaliases as $alias)
		{
			$section[$alias->prop("to")] = $alias->prop("to");
		}

		$obj->set_meta("section",$section);
		$obj->save();
	}

	function callback_on_addalias($args = array())
	{
		$obj_list = explode(",",$args["alias"]);
		$obj =&obj($args["id"]);

		$data = $obj->meta("section");

		foreach($obj_list as $val)
		{
			$data[$val] = $val;
		};

		$obj->set_meta("section",$data);
		$obj->save();
	}

	////
	// !returns all the menus that are a part of this search group
	// params
	//	id - group id
	function get_menus($arr)
	{
		$o = obj($arr["id"]);

		$se = $o->meta("section");
		$sub = $o->meta("section_include_submenus");

		$ret = array();

		foreach($se as $m)
		{
			if ($sub[$m])
			{
				$ret[$m] = $m;
				/*$tr = new object_tree(array(
					"parent" => $m,
					"status" => STAT_ACTIVE,
					"class_id" => CL_MENU,
					"lang_id" => aw_global_get("lang_id"),
				));
				$ids = $tr->ids();*/
				$ids = array_keys($this->get_menu_list(false, false, $m, 1));
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

		return $ret;
	}
}
?>
