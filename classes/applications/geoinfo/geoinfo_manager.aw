<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/geoinfo/geoinfo_manager.aw,v 1.1 2007/12/21 08:49:17 robert Exp $
// geoinfo_manager.aw - Geoinfo haldus 
/*

@classinfo syslog_type=ST_GEOINFO_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property general_tb type=toolbar store=no no_caption=1

	@property name type=textbox table=objects
	@caption Nimi

	@property xml_source type=chooser field=meta method=serialize
	@caption XML-i allikas

	@property xml_url type=textbox field=meta method=serialize
	@caption XML faili url

	@property xml_file type=relpicker reltype=RELTYPE_XML field=meta method=serialize store=connect
	@caption XML fail

	@property xml_schema type=relpicker reltype=RELTYPE_SCHEMA field=meta method=serialize store=connect
	@caption XML-i skeem

	@property xml_unique type=select field=meta method=serialize
	@caption XML-i unikaalne väli

@default group=rels_mgr

	@property rels_mgr_tb type=toolbar store=no no_caption=1

	@property rels_mgr_table type=table store=no no_caption=1

@default group=data_mgr

	@property data_mgr_tb type=toolbar store=no no_caption=1

	@property data_mgr_table type=table store=no no_caption=1

@groupinfo rels_mgr caption=V&auml;ljad
@groupinfo data_mgr caption=Andmed

@reltype XML value=1 clid=CL_FILE
@caption XML sisend

@reltype SCHEMA value=1 clid=CL_FILE
@caption XML skeem
*/

class geoinfo_manager extends class_base
{
	function geoinfo_manager()
	{
		$this->init(array(
			"tpldir" => "applications/geoinfo/geoinfo_manager",
			"clid" => CL_GEOINFO_MANAGER
		));
	}

	function get_data_clids($arr)
	{
		$ol = new object_list(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => CL_GEOINFO_DATA,
		));
		$clids = array();
		foreach($ol->arr() as $o)
		{
			if(is_oid($o->prop("obj_oid")))
			{
				$obj = obj($o->prop("obj_oid"));
				$clid = $obj->class_id();
				$clids[$clid] = $clid;
			}
		}
		return $clids;
	}

	function get_prop_list($clid)
	{
		$goodprops = array(0=>"");
		$cff = get_instance(CL_CFGFORM);
		$manager_form = $cff->get_sysdefault(array("clid"=>$clid));
		if($manager_form)
		{
			foreach($cff->get_cfg_proplist($manager_form) as $pn=>$pd)
			{
				if($pd["caption"])
				{
					$goodprops[$pn] = $pd["caption"];
				}
			}
		}
		else
		{
			$o = obj();
			$o->set_class_id($clid);
			$list = $o->get_property_list();
			foreach($list as $lid => $li)
			{
				if($li["caption"])
				{
				//if(strpos($li["type"], "text")>-1 && $li["caption"])
					$goodprops[$lid] = $li["caption"];
				}
			}
		}
		return $goodprops;
	}

	/**
	@attrib name=import all_args=1
	**/
	function import_from_xml($arr)
	{
		$arr["obj_inst"] = obj($arr["id"]);
		if($arr["obj_inst"]->prop("xml_source") == "url")
		{
			$xmldata = @file_get_contents($fname);
		}
		elseif($fid = $arr["obj_inst"]->prop("xml_file"))
		{
			$fo = obj($fid);
			$tmp = $this->get_file(array("file" => $fo->prop("file")));
			if ($tmp !== false)
			{
				$xmldata = $tmp;
			}
		}
		if($xmldata)
		{
			$x = xml_parser_create();
			xml_parse_into_struct($x, $xmldata, $vals, $index);
			xml_parser_free($x);
			$allvars = array();
			$rels = $arr["obj_inst"]->meta("rels");
			$arr["t"] = "xml";
			$pfv = $this->props_for_var($arr);
			foreach($vals as $val)
			{
				$curvars = array();
				if($val["level"] == 2 && $val["complete"] == "complete")
				{
				}
			}
		}
		return $arr["ru"];
	}

	function xml_get_fields($arr)
	{
		if($fid = $arr["obj_inst"]->prop("xml_schema"))
		{
			$fo = obj($fid);
			$tmp = $this->get_file(array("file" => $fo->prop("file")));
			if ($tmp !== false)
			{
				$xmldata = $tmp;
			}
			$x = xml_parser_create();
			xml_parse_into_struct($x, $xmldata, $vals, $index);
			xml_parser_free($x);
			$fields = array(0=>"");
			foreach($vals as $val)
			{
				if($val["type"] == "complete")
				{
					$fields[$val["tag"]] = $val["tag"];
				}
			}
			return $fields;
		}
		else
		{
			return array();
		}
	}

	function _get_general_tb($arr)
	{
		foreach($vals as $val)
		{
			if($val["type"] == "complete")
			{
				echo $val["tag"].'<br />';
			}
		}
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "submit",
			"img" => "save.gif",
			"tooltip" => "Impordi XML-ist",
			"url" => $this->mk_my_orb("import",array(
				"id" => $arr["obj_inst"]->id(),
				"ru" => get_ru()
			))
		));
	}

	function _get_rels_mgr_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_save_button();
	}

	function _set_rels_mgr_table($arr)
	{
		$tmp = array();
		foreach($arr["request"] as $var => $val)
		{
			if($var == "MAX_FILE_SIZE")
				continue;
			$tmpvar = explode('--', $var);
			$tmp[$tmpvar[1]]["fields"][$val][] = $tmpvar[0];
			$tmp[$tmpvar[1]]["props"][$tmpvar[0]] = $val;
		}
		$arr["obj_inst"]->set_meta("rels", $tmp);
	}

	function init_rels_mgr_table(&$t, $arr)
	{
		$t->set_caption(t("Väljade seostamine"));
		/*$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
			"numeric" => 1,
			"align" => "center",
		));*/
		$t->define_field(array(
			"name" => "field",
			"caption" => t("Väli"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "xml",
			"caption" => "XML",
			"align" => "center",
		));
		$cldata = aw_ini_get("classes");
		$clids = $this->get_data_clids($arr);
		foreach($clids as $clid)
		{
			$cl_name = $cldata[$clid]["name"];
			$t->define_field(array(
				"name" => "cl".$clid,
				"caption" => t($cl_name),
				"align" => "center",
			));
		}
		$t->sort_by();
		$t->set_default_sortby("field");
	}

	function get_rels_mgr_fields($arr)
	{
		extract($arr);
		$rels = $obj_inst->meta("rels");
		foreach($prop as $pn=>$pd)
		{
			//$data["id"] = $id;
			$data["field"] = $pd;
			$data["xml"] = html::select(array(
				"name" => $pn."--xml",
				"options" => $xmlfields,
				"value" => $rels["xml"]["props"][$pn],
			));
			foreach($clids as $clid)
			{
				$data["cl".$clid] = html::select(array(
					"name" => $pn."--cl".$clid,
					"options" => $cl_proplist[$clid],
					"value" => $rels["cl".$clid]["props"][$pn]
				));
			}
		}
		return $data;
	}

	function _get_rels_mgr_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->init_rels_mgr_table($t, $arr);

		$args["clids"] = $this->get_data_clids($arr);
		foreach($args["clids"] as $clid)
		{
			$args["cl_proplist"][$clid] = $this->get_prop_list($clid);
		}
		$args["xmlfields"] = $this->xml_get_fields($arr);
		$list = $this->get_prop_list(CL_GEOINFO_DATA);
		unset($list[0]);
		$args["obj_inst"] = $arr["obj_inst"];
		foreach($list as $pn=>$pd)
		{
			$args["prop"] = array($pn => $pd);
			$data = $this->get_rels_mgr_fields($args);
			$t->define_data($data);
		}
	}

	function _get_data_mgr_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_GEOINFO_DATA), $arr["obj_inst"]->id(), '', array());
		$tb->add_search_button(array(
			"pn" => "add_data",
			"multiple" => 1,
			"clid" => CL_GEOINFO_DATA
		));
		$tb->add_delete_button();
		$tb->add_save_button();
	}

	function init_data_mgr_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "coord_x",
			"caption" => "X koordinaat",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "coord_y",
			"caption" => "Y koordinaat",
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "address",
			"caption" => "Aadress",
			"align" => "center",
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
		$t->set_caption(t("Andmeobjektid"));
	}

	function _get_data_mgr_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->init_data_mgr_table($t);
		$ol = new object_list(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => CL_GEOINFO_DATA
		));
		foreach($ol->arr() as $o)
		{
			$t->define_data(array(
				"oid" => $o->id(),
				"name" => html::obj_change_url($o->id()),
				"coord_x" => html::textbox(array(
					"name" => "coord_x-".$o->id(),
					"value" => $o->prop("coord_x"),
					"size" => 20
				)),
				"coord_y" => html::textbox(array(
					"name" => "coord_y-".$o->id(),
					"value" => $o->prop("coord_y"),
					"size" => 20
				)),
				"address" => $o->prop("address"),
			));
		}
	}

	function _set_data_mgr_table($arr)
	{
		if($arr["request"]["add_data"])
		{
			$ids = explode(",",$arr["request"]["add_data"]);
			foreach($ids as $oid)
			{
				$data = obj($oid);
				$data->set_parent($arr["obj_inst"]->id());
				$data->save();
			}
		}
		$ol = new object_list(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => CL_GEOINFO_DATA
		));
		foreach($ol->arr() as $o)
		{
			$cx = $arr["request"]["coord_x-".$o->id()];
			$cy = $arr["request"]["coord_y-".$o->id()];
			$o->set_prop("coord_x", $cx);
			$o->set_prop("coord_y", $cy);
			$o->save();
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "xml_source":
				$prop["options"] = array(
					"file" => "Uploaditud fail",
					"url" => "Url"
				);
				if(!$prop["value"])
					$prop["value"] = "file";
				break;
			case "xml_unique":
				$prop["options"] = $this->xml_get_fields($arr);
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
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["add_data"] = "0";
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
