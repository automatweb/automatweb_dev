<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/register/register.aw,v 1.1 2004/05/17 14:15:21 kristo Exp $
// register.aw - Register 
/*

@classinfo syslog_type=ST_REGISTER relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property data_cfgform type=relpicker reltype=RELTYPE_CFGFORM field=meta method=serialize
@caption Andmete seadete vorm

@property data_rootmenu type=relpicker reltype=RELTYPE_MENU field=meta method=serialize
@caption Andmete kataloog

@groupinfo data caption=Andmed
@default group=data

@property data_tb type=toolbar store=no no_caption=1

@property data type=table store=no no_caption=1


@reltype CFGFORM value=1 clid=CL_CFGFORM
@caption andmete seadete vorm

@reltype MENU value=2 clid=CL_MENU
@caption andmete kataloog
*/

class register extends class_base
{
	function register()
	{
		$this->init(array(
			"tpldir" => "applications/register/register",
			"clid" => CL_REGISTER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "data_tb":
				$this->do_data_toolbar($arr);
				break;

			case "data":
				$this->do_data_tbl($arr);
				break;

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "data":
				$awa = new aw_array($arr["request"]["select"]);
				foreach($awa->get() as $k => $v)
				{
					if ($k == $v)
					{
						$o = obj($k);
						$o->delete();
					}
				}
				break;
		}
		return $retval;
	}	

	function do_data_toolbar($arr)
	{
		$tb =& $arr["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "new",
			"tooltip" => "Uus"
		));

		$awa = new aw_array($arr["obj_inst"]->prop("data_cfgform"));
		foreach($awa->get() as $cfid)
		{
			$o = obj($cfid);
			$tb->add_menu_item(array(
				"parent" => "new",
				"text" => $o->name(),
				"link" => $this->mk_my_orb("new", array(
					"cfgform" => $o->id(),
					"parent" => $arr["obj_inst"]->prop("data_rootmenu"),
					"return_url" => urlencode(aw_global_get("REQUEST_URI")),
					"cfgform" => $cfid,
					"set_register_id" => $arr["obj_inst"]->id()
				), CL_REGISTER_DATA)
			));
		}
	}

	function _init_data_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "createdby",
			"caption" => "Kes l&otilde;i",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "created",
			"caption" => "Millal loodi",
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Kes muutis",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => "Millal muudeti",
			"sortable" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => "Muuda",
			"align" => "center"
		));

		$t->define_chooser(array(
			"name" => "select",
			"field" => "oid"
		));
	}

	function do_data_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_data_tbl($t);
		
		$ol = new object_list(array(
			"class_id" => CL_REGISTER_DATA,
			"register_id" => $arr["obj_inst"]->id()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$cby = $o->createdby();
			$mby = $o->modifiedby();
			$t->define_data(array(
				"oid" => $o->id(),
				"name" => $o->name(),
				"createdby" => $cby->name(),
				"created" => $o->created(),
				"modifiedby" => $mby->name(),
				"modified" => $o->modified(),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
					"caption" => "Muuda"
				))
			));
		}
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !shows the register
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
