<?php
// html_popup.aw - a class to deal with javascript popups
// $Header: /home/cvs/automatweb_dev/classes/Attic/html_popup.aw,v 2.7 2004/02/16 09:54:55 duke Exp $

/*
	@classinfo relationmgr=yes
	
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property show_obj type=relpicker reltype=RELTYPE_OBJ
	@caption Sisu 

	@property width type=textbox size=4 maxlength=4
	@caption Laius

	@property height type=textbox size=4 maxlength=4
	@caption Kõrgus
	
	@property only_once type=checkbox ch_value=1
	@caption Ainult &uuml;he korra sessiooni jooksul
	
	@property menus type=text callback=callback_get_menus method=serialize
	@caption Menüüd



*/

define("RELTYPE_FOLDER", 1);
define("RELTYPE_OBJ", 2);

class html_popup extends class_base
{
	function html_popup($args = array())
	{
		$this->init(array(
			"tpldir" => "automatweb/html_popup",
			"clid" => CL_HTML_POPUP,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
		};
		return $retval;
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_FOLDER)
		{
			return array(CL_PSEUDO);
		}
		else
		if ($args["reltype"] == RELTYPE_OBJ)
		{
			$cls = array();
			foreach($this->cfg["classes"] as $clid => $cl)
			{
				if ($cl["alias"] != "")
				{
					$cls[$clid] = $cl["name"];
				}
			}
			asort($cls);
			return array_keys($cls);
		}
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_FOLDER => "n&auml;ita selle menu&uuml;&uuml; all",
			RELTYPE_OBJ => "sisu objekt",
		);
	}

	function callback_get_menus($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();
		$section_include_submenus = $args["obj_inst"]->meta("section_include_submenus");
		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "pup_menus",
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

		if (is_oid($args["obj_inst"]->id()))
		{
			$obj = $args["obj_inst"];
			$conns = $obj->connections_from(array(
				"type" => RELTYPE_FOLDER
			));
			foreach($conns as $c)
			{
				$c_o = $c->to();

				$this->t->define_data(array(
					"oid" => $c_o->id(),
					"name" => $c_o->path_str(array(
						"max_len" => 3
					)),
					"check" => html::checkbox(array(
						"name" => "include_submenus[".$c_o->id()."]",
						"value" => $c_o->id(),
						"checked" => $section_include_submenus[$c_o->id()],
					)),
				));
			}
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
			"type" => RELTYPE_FOLDER
		));
	
		$section = array();

		foreach($oldaliases as $alias)
		{
			if ($alias->prop("reltype") == RELTYPE_FOLDER)
			{
				$section[$alias->prop("target")] = $alias->prop("target");
			};
		};

		$obj->set_meta("menus",$section);
		$obj->save();
	}

	function callback_on_addalias($args = array())
	{
		$obj =&obj($args["id"]);
		$data = $obj->meta("menus");

		$obj_list = explode(",",$args["alias"]);
		foreach($obj_list as $val)
		{
			$data[$val] = $val;
		};

		$obj->set_meta("menus",$data);
		$obj->save();
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		if ($data["name"] == "menus")
		{
			$args["obj_inst"]->set_meta("section_include_submenus",$args["request"]["include_submenus"]);
		};
		return $retval;
	}
}
?>
