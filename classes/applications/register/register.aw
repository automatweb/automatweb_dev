<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/register/register.aw,v 1.4 2004/05/21 11:06:55 kristo Exp $
// register.aw - Register 
/*

@classinfo syslog_type=ST_REGISTER relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property data_cfgform type=relpicker reltype=RELTYPE_CFGFORM multiple=1 field=meta method=serialize
@caption Andmete seadete vorm

@property data_rootmenu type=relpicker reltype=RELTYPE_MENU field=meta method=serialize
@caption Andmete kataloog

@property data_tree_field type=select field=meta method=serialize
@caption Andmete puu struktuuri v&auml;li

@property search_o type=relpicker reltype=RELTYPE_SEARCH field=meta method=serialize
@caption Otsingu konfiguratsioon

@groupinfo data caption=Andmed
@default group=data

@property data_tb type=toolbar store=no no_caption=1

@layout datalt type=hbox group=data

@property data_tree type=text store=no no_caption=1 parent=datalt
@property data type=table store=no no_caption=1 parent=datalt


@groupinfo search caption="Otsing" submit_method=get submit=no
@default group=search

@property search type=text store=no no_caption=1

@reltype CFGFORM value=1 clid=CL_CFGFORM
@caption andmete seadete vorm

@reltype MENU value=2 clid=CL_MENU
@caption andmete kataloog

@reltype SEARCH value=3 clid=CL_REGISTER_SEARCH
@caption registri otsing
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
			case "data_tree_field":
				$prop["options"] = $this->get_chooser_elements($arr["obj_inst"]);
				break;

			case "data_tb":
				$this->do_data_toolbar($arr);
				break;

			case "data":
				$this->do_data_tbl($arr);
				break;

			case "data_tree":
				if (!$arr["obj_inst"]->prop("data_tree_field"))
				{
					return PROP_IGNORE;
				}
				$prop["value"] = $this->get_data_tree($arr["obj_inst"]);
				break;

			case "search":
				if (!$arr["obj_inst"]->prop("search_o"))
				{
					$prop["value"] = "Otsingu konfiguratsioon valimatta!";
				}
				else
				{
					$s = get_instance(CL_REGISTER_SEARCH);
					$prop["value"] = $s->show(array(
						"id" => $arr["obj_inst"]->prop("search_o"),
						"no_form" => 1
					));
				}
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

		$filt = array(
			"class_id" => CL_REGISTER_DATA,
			"register_id" => $arr["obj_inst"]->id()
		);

		if (($dtf = $arr["obj_inst"]->prop("data_tree_field")))
		{
			if ($arr["request"]["treefilter"])
			{
				if ($arr["request"]["treefilter"] != "__all__")
				{
					$filt[$dtf] = $arr["request"]["treefilter"];
				}
				$ol = new object_list($filt);
			}
			else
			{
				$ol = new object_list();
			}
		}
		else
		{
			$ol = new object_list($filt);
		}

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

	function get_chooser_elements($o)
	{
		$rs = get_instance(CL_REGISTER_SEARCH);
		$ps = $rs->get_props_from_reg($o);
		$clid = $rs->get_clid_from_reg($o);

		// load props for entire class, cause from cfgform we don't get all dat
		$cfgu = get_instance("cfg/cfgutils");
		$f_props = $cfgu->load_properties(array(
			"clid" => $clid
		));

		$ret = array("" => "");
		foreach($ps as $pn => $pd)
		{
			if ($f_props[$pn]["type"] == "classificator")
			{
				$ret[$pn] = $pd["caption"];
			}
		}

		return $ret;
	}

	function get_data_tree($o)
	{
		$t = get_instance("vcl/treeview");
		$t->start_tree(array(
			"root_name" => "K&otilde;ik",
			"root_url" => aw_url_change_var("treefilter", "__all__"),
			"has_root" => 1,
			"tree_id" => "register".$o->id(),
			"type" => TREE_DHTML,
			"persist_state" => 1
		));

		// get values from prop
		$clsf = get_instance(CL_CLASSIFICATOR);
		$vals = $clsf->get_options_for(array(
			"name" => $o->prop("data_tree_field"),
			"clid" => CL_REGISTER_DATA
		));
		
		// insert into tree
		foreach($vals as $v_id => $v_name)
		{
			$t->add_item(0, array(
				"id" => $v_id,
				"name" => $v_name,
				"url" => aw_url_change_var("treefilter", $v_id)
			));
		}

		return $t->finalize_tree(array(
		));
	}
}
?>
