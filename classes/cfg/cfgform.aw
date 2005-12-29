<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.74 2005/12/29 12:35:53 kristo Exp $
// cfgform.aw - configuration form
// adds, changes and in general manages configuration forms

/*
	@default table=objects
	@default group=general

	@property subclass type=select newonly=1
	@caption Klass

	@property ctype type=text editonly=1 field=subclass
	@caption Tüüp

	@default field=meta
	@default method=serialize

	@property use_output type=relpicker reltype=RELTYPE_OUTPUT
	@caption Väljundvorm

	@property xml_definition type=fileupload editonly=1
	@caption Uploadi vormi fail

	@property preview type=text store=no editonly=1
	@caption Definitsioon

	@property classinfo_fixed_toolbar type=checkbox ch_value=1 field=meta method=serialize
	@caption Fix. toolbar

	@property classinfo_allow_rte type=checkbox ch_value=1 field=meta method=serialize
	@caption Luba RTE kasutamist

	@property classinfo_allow_rte_toggle type=checkbox ch_value=1 field=meta method=serialize
	@caption Näita RTE/HTML nuppu

	@property classinfo_disable_relationmgr type=checkbox ch_value=1 field=meta method=serialize
	@caption Ära kasuta seostehaldurit

	@property edit_groups type=callback callback=callback_edit_groups group=groupdata
	@caption Muuda gruppe

	@property navtoolbar type=toolbar group=layout store=no no_caption=1 editonly=1
	@caption Toolbar

	@property layout type=callback callback=callback_gen_layout store=no group=layout no_caption=1
	@caption Layout
	
	@property availtoolbar type=toolbar group=avail store=no no_caption=1 editonly=1
	@caption Av. Toolbar

	@property availprops type=table store=no group=avail no_caption=1
	@caption Kõik omadused

	@property cfg_proplist type=hidden field=meta method=serialize
	@caption Omadused
	
	@property cfg_groups type=hidden field=meta method=serialize
	@caption Grupid

	@property subaction type=hidden store=no group=layout,avail
	@caption Subaction (sys)
	
	@property gen_submit_controllers type=callback callback=gen_controller_props group=set_controllers
	@caption Kontrollerid
	
	@property gen_view_controllers type=callback callback=gen_view_controller_props group=get_controllers
	@caption Kontrollerid

	@property default_table type=table group=defaults no_caption=1
	@caption Vaikimisi väärtused

	@property sysdefault type=table group=system no_caption=1
	@caption Süsteemi seaded

	@property trans_tbl_capt type=text subtitle=1 group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12 
	@caption Omadused

	@property trans_tbl type=table group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12 no_caption=1

	@property trans_tbl_grp_capt type=text group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12 subtitle=1
	@caption Grupid

	@property trans_tbl_grps type=table group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12 no_caption=1
	
	@groupinfo groupdata caption=Grupid 
	@groupinfo layout caption=Layout submit=no
	@groupinfo avail caption="Kõik omadused" submit=no
	@groupinfo controllers caption="Kontrollerid"
	
	@groupinfo set_controllers caption=Salvestamine parent=controllers
	@groupinfo get_controllers caption=Näitamine parent=controllers

	@groupinfo settings caption="Seaded"
	@groupinfo defaults caption="Omaduste väärtused" parent=settings
	@groupinfo system caption="Vormi seaded" parent=settings
	@groupinfo translate caption="T&otilde;lgi" 
		@groupinfo lang_1 caption="lang" parent=translate
		@groupinfo lang_2 caption="lang" parent=translate
		@groupinfo lang_3 caption="lang" parent=translate
		@groupinfo lang_4 caption="lang" parent=translate
		@groupinfo lang_5 caption="lang" parent=translate
		@groupinfo lang_6 caption="lang" parent=translate
		@groupinfo lang_7 caption="lang" parent=translate
		@groupinfo lang_8 caption="lang" parent=translate
		@groupinfo lang_9 caption="lang" parent=translate
		@groupinfo lang_10 caption="lang" parent=translate
		@groupinfo lang_11 caption="lang" parent=translate
		@groupinfo lang_12 caption="lang" parent=translate


	@classinfo relationmgr=yes syslog_type=ST_CFGFORM

	@reltype PROP_GROUP value=1 clid=CL_MENU
	@caption omaduste kataloog

	@reltype ELEMENT value=2 clid=CL_RTE
	@caption element

	@reltype CONTROLLER value=3 clid=CL_CFGCONTROLLER
	@caption Salvestamise kontroller

	
	@reltype VIEWCONTROLLER value=5 clid=CL_CFG_VIEW_CONTROLLER
	@caption N&auml;itamise kontroller
	
	@reltype OUTPUT value=4 clid=CL_CFGFORM
	@caption Väljund

	// so, how da fuck do I implement the grid layout thingie?
	// add_item (item, row, col)

	// so .. first I have to implement a new attribute for layout thingie

	// and then I want to be able to add new widgets in the same order they are arriving

*/
class cfgform extends class_base
{
	function cfgform($arr = array())
	{
		$this->init(array(
			"clid" => CL_CFGFORM,
			"tpldir" => "cfgform",
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

			case "sysdefault":
				$this->do_sysdefaults($arr);
				break;

			case "xml_definition":
				// I don't want to show the contents of the file here
				$data["value"] = "";
				break;

			case "preview":
				$data["value"] = "";
				break;

			case "subclass":
				$cx = get_instance("cfg/cfgutils");
				$class_list = new aw_array($cx->get_classes_with_properties());
				$cp = get_class_picker(array("field" => "def"));

				foreach($class_list->get() as $key => $val)
				{
					$data["options"][$key] = $val;
				};	
				break;

			case "ctype":
				classload("core/icons");
				$iu = html::img(array(
					"url" => icons::get_icon_url($arr["obj_inst"]->prop("subclass"),""),
				));
				$tmp = aw_ini_get("classes");
				$data["value"] = $iu . " " . $tmp[$arr["obj_inst"]->prop("subclass")]["name"];
				break;

			case "navtoolbar":
				$this->gen_navtoolbar($arr);
				break;
			
			case "availtoolbar":
				$this->gen_availtoolbar($arr);
				break;

			case "availprops":
				$this->gen_avail_props($arr);
				break;

			case "default_table":
				$this->gen_default_table($arr);
				break;

			case "trans_tbl":
				$this->_trans_tbl($arr);
				break;

			case "trans_tbl_grps":
				$this->_trans_tbl_grps($arr);
				break;
		};
		return $retval;
	}

	function _init_trans_tbl(&$t, $o, $req, $str = "Omadus")
	{
		$l = get_instance("languages");
		$ld = $l->fetch($o->lang_id(), false);

		$t->define_field(array(
			"name" => "property",
			"caption" => $str,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "orig_str",
			"caption" => $ld["name"],
			"align" => "center"
		));

		$lid = substr($req["group"], 5);
		$tmp = $l->get_list(array("ignore_status" => 1));
		unset($tmp[$o->lang_id()]);
		$this->lang_inf = array(
			"ids" => array_keys($tmp),
			"names" => array_values($tmp)
		);
		$lid = $this->lang_inf["ids"][max((int)$lid-1,0)];
		$ld = $l->fetch($lid, false);
		$t->define_field(array(
			"name" => "trans_str",
			"caption" => $ld["name"],
			"align" => "center"
		));
		return $ld;
	}

	function _trans_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ld = $this->_init_trans_tbl($t, $arr["obj_inst"], $arr["request"]);
		$lid = $ld["acceptlang"];

		$trans = $arr["obj_inst"]->meta("translations");

		$ps = $arr["obj_inst"]->meta("cfg_proplist");
		foreach($ps as $pn => $pd)
		{
			$t->define_data(array(
				"property" => $pn,
				"orig_str" => $pd["caption"],
				"trans_str" => html::textbox(array(
					"name" => "dat[".$lid."][$pn]",
					"value" => $trans[$lid][$pn]
				))
			));
		}
	}

	function _trans_tbl_grps($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ld = $this->_init_trans_tbl($t, $arr["obj_inst"], $arr["request"], "Grupp");
		$lid = $ld["acceptlang"];

		$trans = $arr["obj_inst"]->meta("grp_translations");

		$ps = $arr["obj_inst"]->meta("cfg_groups");
		foreach($ps as $pn => $pd)
		{
			$t->define_data(array(
				"property" => $pn,
				"orig_str" => $pd["caption"],
				"trans_str" => html::textbox(array(
					"name" => "dat[".$lid."][$pn]",
					"value" => $trans[$lid][$pn]
				))
			));
		}
	}

	function gen_default_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Omadus"),
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Tüüp"),
		));
		$t->define_field(array(
			"name" => "value",
			"caption" => t("Väärtus"),
		));

		$props = $this->get_props_from_cfgform(array(
			"id" => $arr["obj_inst"]->id(),
		));


		foreach($props as $prop)
		{
			if ($prop["type"] == "checkbox")
			{
				$prx = $this->prplist[$prop["name"]];
				// so, how do I determine whether this property has a default or not?
				// kas ma pean kuhugi kirja panema selle asja?

				// eino, asi on ikka näitamises ju eksole. mitte salvestamises
				$pname = $arr["prop"]["name"];
				$t->define_data(array(
					"name" => $prop["caption"] . "(" . $prop["name"] . ")",
					"type" => $prop["type"],
					"value" => html::checkbox(array(
						"name" => $pname."[".$prop["name"]."]",
						"value" => 1,
						//"checked" => $this->prplist[$prop["name"]]["default"] == "" ? $prop["default"] : ($this->prplist[$prop["name"]]["default"] == 1),
						"checked" => $this->prplist[$prop["name"]]["default"] == 1,
					)),
				));
			};


		};


	}

	function gen_controller_props($arr)
	{
		$controllers = $arr["obj_inst"]->meta("controllers");
		$retval = array();
		foreach ($this->prplist as $prop)
		{
			$caption = $prop["caption"];
			if(!$caption)
			{
				$caption = $prop["name"];
			}
			$retval[] = array(
				"name" => "controllers[".$prop["name"]."]",
				"caption" => $caption,
				"type" => "relpicker",
				"multiple" => 1,
				"size" => 2,
				"reltype" => "RELTYPE_CONTROLLER",
				"value" => $controllers[$prop["name"]],
			);
		}
		return  $retval;
	}
	
	function gen_view_controller_props($arr)
	{
		$controllers = $arr["obj_inst"]->meta("view_controllers");
		$retval = array();
		foreach ($this->prplist as $prop)
		{
			$caption = $prop["caption"];
			if(!$caption)
			{
				$caption = $prop["name"];
			}
			$retval[] = array(
				"name" => "view_controllers[".$prop["name"]."]",
				"caption" => $caption,
				"type" => "relpicker",
				"multiple" => 1,
				"size" => 2,
				"reltype" => "RELTYPE_VIEWCONTROLLER",
				"value" => $controllers[$prop["name"]],
			);
		}
		return  $retval;
	}
	
	function callback_pre_edit($arr)
	{
		$this->_init_cfgform_data($arr["obj_inst"]);
	}

	function do_sysdefaults($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "act",
			"caption" => t("Süsteemi default"),
			"align" => "center",
			"width" => 85,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));

		$t->define_field(array(
			"name" => "group",
			"caption" => t("Grupp"),
		));

		$o = $arr["obj_inst"];
		$active = 0;

		$active = $this->get_sysdefault(array("clid" => $o->subclass()));

		$ol = new object_list(array(
			"class_id" => $this->clid,
			"subclass" => $o->subclass(),
			"lang_id" => array(),
		));

		$t->set_sortable(false);

		$t->define_data(array(
			"act" => html::radiobutton(array(
				"name" => "sysdefault",
				"value" => 0,
				"checked" => 0 == $active,
			)),
			"name" => t("Ära kasuta vormi"),
		));

		foreach($ol->arr() as $o)
		{
			$oid = $o->id();
			$t->define_data(array(
				"act" => html::radiobutton(array(
					"name" => "sysdefault",
					"value" => $oid,
					"checked" => $oid == $active,
				)),
				"name" => $o->name(),
			));
		};
			
	}
	

	function _init_cfgform_data($obj)
	{
		$this->_init_properties($obj->prop("subclass"));

		$this->grplist = safe_array($obj->meta("cfg_groups"));
		$this->prplist = safe_array($obj->meta("cfg_proplist"));

	}

	function _init_properties($class_id)
	{

		error::raise_if(empty($class_id),(array(
			"id" => ERR_ABSTRACT,
			"msg" => t("this is not a valid config form - class_id not specified")
		)));

		$tmp = aw_ini_get("classes");
		$fl = $tmp[$class_id]["file"];
		if ($fl == "document")
		{
			$fl = "doc";
		};
		$inst = get_instance($fl);
		$cfgu = get_instance("cfg/cfgutils");
		$_all_props = $cfgu->load_properties(array(
			"file" => $fl,
		));
		$this->all_props = $inst->get_all_properties();
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$o = $arr["obj_inst"];
		$retval = PROP_OK;

		switch($data["name"])
		{
			case "gen_submit_controllers":
				$arr["obj_inst"]->set_meta("controllers", $arr["request"]["controllers"]);		
				break;
			
			case "gen_view_controllers":
				$arr["obj_inst"]->set_meta("view_controllers", $arr["request"]["view_controllers"]);		
				break;
			
			case "sysdefault":
				$ol = new object_list(array(
					"class_id" => $this->clid,
					"subclass" => $o->subclass(),
					"lang_id" => array(),
				));
				foreach ($ol->arr() as $item)
				{
					if ($item->flag(OBJ_FLAG_IS_SELECTED) && $item->id() != $data["value"])
					{
						$item->set_flag(OBJ_FLAG_IS_SELECTED, false);
						$item->save();
					}
					elseif ($item->id() == $data["value"] && !$item->flag(OBJ_FLAG_IS_SELECTED))
					{
						$item->set_flag(OBJ_FLAG_IS_SELECTED, true);
						$item->save();
					};
				};
				break;

			case "cfg_proplist":
			
			case "cfg_groups":
				if (empty($data["value"]))
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "xml_definition":
				if ($_FILES[$data["name"]]["type"] !== "text/xml")
				{
					$retval = PROP_IGNORE;
				}
				else
				if (!is_uploaded_file($_FILES[$data["name"]]["tmp_name"]))
				{
					$retval = PROP_IGNORE;
				}
				else
				{
					$contents = $this->get_file(array(
						"file" => $_FILES[$data["name"]]["tmp_name"],
					));
					if ($contents)
					{
						$data["value"] = $contents;
					};
					$retval = $this->_load_xml_definition($contents);
				};
				break;

			case "subclass":
				// do not overwrite subclass if it was not in the form
				// hum .. this is temporary fix of course. yees --duke
				if (empty($arr["request"]["subclass"]))
				{
					$retval = PROP_IGNORE;
				}
				// cfg_proplist is in "formdata" only if this a serialized object
				// being unserialized. for example, if we are copying this object
				// over xml-rpc
				elseif ($arr["new"] && empty($arr["request"]["cfg_proplist"]))
				{
					// fool around a bit to get the correct data
					$subclass = $arr["request"]["subclass"];

					// now that's the tricky part ... this thingsbum overrides
					// all the settings in the document config form
					$this->_init_properties($subclass);
					$cfgu = get_instance("cfg/cfgutils");
					if ($subclass == CL_DOCUMENT)
					{
						$def = join("",file(aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml"));
						list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
						$this->cfg_proplist = $proplist;
						$this->cfg_groups = $grplist;
					}
					else
					{
						$tmp = aw_ini_get("classes");
						$fname = $tmp[$subclass]["file"];
						$def = join("",file(aw_ini_get("basedir") . "/xml/properties/class_base.xml"));
						list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
						$this->cfg_proplist = $proplist;
						$this->cfg_groups = $grplist;
						$fname = basename($fname);
						$def = join("",file(aw_ini_get("basedir") . "/xml/properties/$fname.xml"));
						list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
						// nono. It needs to fucking merge those things with classbase 
						$this->cfg_proplist = $this->cfg_proplist + $proplist;
						$this->cfg_groups = $this->cfg_groups + $grplist;


					};
				};
				break;

			case "availprops":
				$this->add_new_properties($arr);
				break;

			case "layout":
				$this->save_layout($arr);
				break;

			case "edit_groups":
				$this->_init_cfgform_data($arr["obj_inst"]);
				$this->update_groups($arr);
				break;

			case "default_table":
				$this->default_values = $data["value"];
				$this->_init_cfgform_data($arr["obj_inst"]);
				$this->cfg_proplist = $this->prplist;
				break;

			case "trans_tbl":
				$trans = safe_array($arr["obj_inst"]->meta("translations"));
				foreach(safe_array($arr["request"]["dat"]) as $lid => $ldat)
				{
					$trans[$lid] = $ldat;
				}
				$arr["obj_inst"]->set_meta("translations", $trans);
				break;

			case "trans_tbl_grps":
				$trans = safe_array($arr["obj_inst"]->meta("grp_translations"));
				foreach(safe_array($arr["request"]["dat"]) as $lid => $ldat)
				{
					$trans[$lid] = $ldat;
				}
				$arr["obj_inst"]->set_meta("grp_translations", $trans);
				break;
		}
		return $retval;
	}

	function _load_xml_definition($contents)
	{
		// right now I can load whatever I want, but I really should validate that stuff
		// first .. and keep in mind that I want to have as many relation pickers
		// as I want to.
		$cfgu = get_instance("cfg/cfgutils");
		list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $contents));
		$this->cfg_proplist = $proplist;
		$this->cfg_groups = $grplist;
	}
		
	function callback_pre_save($arr)
	{
		$obj_inst = &$arr["obj_inst"];
		// if we are unzerializing the object, then we need to set the 
		// subclass as well.
		if (isset($arr["request"]["subclass"]))
		{
			$obj_inst->set_prop("subclass",$arr["request"]["subclass"]);
		};
		if (isset($this->cfg_proplist) && is_array($this->cfg_proplist))
		{
			$tmp = array();
			$cnt = 0;
			foreach($this->cfg_proplist as $key => $val)
			{
				if (empty($val["ord"]))
				{
					$cnt++;
					$val["tmp_ord"] = $cnt;
				};	
				$tmp[$key] = $val;
			};
			uasort($tmp,array($this,"__sort_props_by_ord"));
			$cnt = 0;
			$this->cfg_proplist = array();

			foreach($tmp as $key => $val)
			{
				unset($val["tmp_ord"]);
				if ($this->default_values[$key])
				{
					$val["default"] = $this->default_values[$key];	
				}
				else
				{
					unset($val["default"]);
				};
				$this->cfg_proplist[$key] = $val;
			};
			$obj_inst->set_meta("cfg_proplist",$this->cfg_proplist);
		};
		if (isset($this->cfg_groups))
		{
			$obj_inst->set_meta("cfg_groups",$this->cfg_groups);
		};
		return true;
	}

	function callback_mod_tab($arr)
	{
		if (!isset($this->lang_inf))
		{
			$l = get_instance("languages");
			$tmp = $l->get_list(array("ignore_status" => 1));
			unset($tmp[$arr["obj_inst"]->lang_id()]);
			$this->lang_inf = array(
				"ids" => array_keys($tmp),
				"names" => array_values($tmp)
			);
		}

		if (substr($arr["id"], 0, 5) == "lang_")
		{
			$num = substr($arr["id"], 5);

			$arr["caption"] = $this->lang_inf["names"][$num-1];
			if ($num > count($this->lang_inf["ids"])) 
			{
				return false;
			}
		}
		return true;
	}

	////
	// !
	function callback_gen_layout($arr = array())
	{
		$this->read_template("layout.tpl");
		$used_props = $by_group = array();

		if (is_array($this->grplist))
		{
			foreach($this->grplist as $key => $val)
			{
				// we should not have numeric group id-s
				// actually it's more about a few ghosts I had lying 
				// around, and this will get rid of them but we
				// really don't NEED numeric group id-s
				// /me does the jedi mind trick - duke
				if (!is_numeric($key))
				{
					$by_group[$key] = array();
				};
			};
		};

		if (is_array($this->prplist))
		{
			foreach($this->prplist as $property)
			{
				if (!empty($property["group"]))
				{
					if (!is_array($property["group"]))
					{
						$by_group[$property["group"]][] = $property;
					}
					else
					{
						foreach($property["group"] as $gkey)
						{
							//list(,$first) = each($property["group"]);
							$by_group[$gkey][] = $property;
						};
					};

				};
			};
		};

		$c = "";
		$cnt = 0;
		foreach($by_group as $key => $proplist)
		{
			$this->vars(array(
				"grp_caption" => $this->grplist[$key]["caption"]." ($key)",
				"grpid" => $key,
			));

			$sc = "";
			foreach($proplist as $property)
			{
				$cnt++;
				$prpdata = $this->all_props[$property["name"]];
				if (!$prpdata)
				{
					continue;
				};
				$used_props[$property["name"]] = 1;
				$this->vars(array(
					"bgcolor" => $cnt % 2 ? "#EEEEEE" : "#FFFFFF",
					"prp_caption" => $property["caption"],
					"prp_type" => $prpdata["type"],
					"prp_key" => $prpdata["name"],
					"prp_order" => $property["ord"],
				));
				$sc .= $this->parse("property");
				if ($this->is_template($prpdata["type"]."_options"))
				{
					$this->vars(array(
						"richtext_checked" => checked($property["richtext"] == 1),
						"richtext" => $property["richtext"],
					));
					$sc .= $this->parse($prpdata["type"]."_options");
				};
			};
			$this->vars(array(
				"property" => $sc,
			));
			$c .= $this->parse("group");
		};

		$this->vars(array(
			"group" => $c,
		));

		$item = $arr["prop"];
		$item["value"] = $this->parse();
		return array($item);
	}

	function __sort_props_by_ord($el1,$el2)
	{
		if (empty($el1["ord"]) && empty($el2["ord"]))
		{
			return (int)($el1["tmp_ord"] - $el2["tmp_ord"]);
			//return 0;
		};
		return (int)($el1["ord"] - $el2["ord"]);
	}

	////
	// !
	function gen_avail_props($arr = array())
	{
		$t = &$arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Tüüp"),
		));

		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Pealkiri"),
		));

		$t->define_chooser(array(
			"name" => "mark",
			"field" => "name",
		));

		$t->set_sortable(false);

		$used_props = array();

		if (is_array($this->prplist))
		{
			foreach($this->prplist as $property)
			{
				$prpdata = $this->all_props[$property["name"]];
				$used_props[$property["name"]] = 1;
			};
		};

		foreach($this->all_props as $key => $property)
		{
			// A single property might be located in multiple groups
			//if (empty($used_props[$property["name"]]))
			//{
				$t->define_data(array(
					"caption" => $property["caption"],
					"type" => $property["type"],
					"name" => $property["name"],
				));
			//};
		}
	}

	function gen_navtoolbar($arr)
	{
		// which links do I need on the toolbar?
		// 1- lisa grupp
		$toolbar = &$arr["prop"]["toolbar"];

		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"url" => "javascript:submit_changeform()",
			"img" => "save.gif",
		));
		
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta valitud omadused"),
			"url" => "javascript:document.changeform.subaction.value='delete';submit_changeform();",
			"img" => "delete.gif",
		));

		$toolbar->add_separator();
		
		$toolbar->add_cdata(t("<small>Liiguta omadused gruppi:</small>"));
		$opts = array();
		if (is_array($this->grplist))
		{
			foreach($this->grplist as $key => $grpdata)
			{
				$opts[$key] = $grpdata["caption"];
			};
		}
		else
		{
			$opts["none"] = t("Ühtegi gruppi pole veel!");
		};
		
		$toolbar->add_cdata(html::select(array(
			"options" => $opts,
			"name" => "target",
		)));
		
		$toolbar->add_button(array(
			"name" => "move",
			"tooltip" => t("Liiguta"),
			"url" => "javascript:document.changeform.subaction.value='move';submit_changeform();",
			"img" => "save.gif",
		));
		
		$toolbar->add_separator();

		$toolbar->add_cdata(t("<small>Lisa grupp:</small>"));
		$toolbar->add_cdata(html::textbox(array(
			"name" => "newgrpname",
			"size" => "20",
		)));
		
		$toolbar->add_button(array(
			"name" => "addgrp",
			"tooltip" => t("Lisa grupp"),
			"url" => "javascript:document.changeform.subaction.value='addgrp';submit_changeform()",
			"img" => "new.gif",
		));
	}
	
	function gen_availtoolbar($arr)
	{
		$toolbar = &$arr["prop"]["toolbar"];
		$opts = array();
		if (is_array($this->grplist))
		{
			foreach($this->grplist as $key => $grpdata)
			{
				$opts[$key] = $grpdata["caption"];
			};
		}
		else
		{
			$opts["none"] = "Ühtegi gruppi pole veel!";
		};

		$toolbar->add_cdata(html::select(array(
			"options" => $opts,
			"name" => "target",
		)));

		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"url" => "javascript:submit_changeform()",
			"img" => "save.gif",
		));
	}

	function add_new_properties($arr)
	{
		$target = $arr["request"]["target"];
		// first check, whether a group with that id exists
		$_tgt = $arr["obj_inst"]->meta("cfg_groups");
		if (isset($_tgt[$target]))
		{
			$this->_init_cfgform_data($arr["obj_inst"]);
			// and now I just have to modify the proplist, eh?
			$prplist = $this->prplist;
			$mark = $arr["request"]["mark"];
			if (is_array($mark))
			{
				foreach($mark as $pkey => $pval)
				{
					// if this is a valid property, then add it to the list
					if ($this->all_props[$pkey])
					{
						// need to add another group
						if ($prplist[$pkey])
						{
							$groups = $prplist[$pkey]["group"];
							if (is_array($groups))
							{
								$prplist[$pkey]["group"][$target] = $target;
							}
							else
							{	
								$prplist[$pkey]["group"] = array(
									$groups => $groups,
									$target => $target,
								);
							};

						}
						else
						{
							// add for the very first time
							$prplist[$pkey] = array(
								"name" => $pkey,
								"caption" => $this->all_props[$pkey]["caption"],
								"group" => array($target => $target),
							);
						};
					};
				};
				$this->cfg_proplist = $prplist;
			};
		};

	}

	function save_layout($arr)
	{
		$subaction = $arr["request"]["subaction"];
		$this->_init_cfgform_data($arr["obj_inst"]);
		switch($subaction)
		{
			// aww .. I need to fix that!
			case "addgrp":
				$newgrpname =$arr["request"]["newgrpname"];
				$grpid = strtolower(preg_replace("/\W/","",$newgrpname));
				if ((strlen($grpid) > 2) && empty($this->grplist[$grpid]))
				{
					$grplist = $this->grplist;
					$grplist[$grpid] = array(
						"caption" => $newgrpname,
					);
					$this->cfg_groups = $grplist;
				};
				break;

			case "delete":
				$mark = $arr["request"]["mark"];
				$prplist = $this->prplist;
				if (is_array($mark))
				{
					foreach($mark as $pkey => $val)
					{
						unset($prplist[$pkey]);
					};
					$this->cfg_proplist = $prplist;
				};
				break;

			case "move":
				$mark = $arr["request"]["mark"];
				$target = $arr["request"]["target"];
				$prplist = $this->prplist;
				if (is_array($mark))
				{
					foreach($mark as $pkey => $val)
					{
						$prplist[$pkey]["group"] = $target;
					};
					$this->cfg_proplist = $prplist;
				};
				break;
			default:
				// well, save the names then
				//$grplist = $this->grplist;
				$prplist = $this->prplist;
				/*
				if (is_array($arr["form_data"]["grpnames"]))
				{
					foreach($arr["form_data"]["grpnames"] as $key => $val)
					{
						$grplist[$key]["caption"] = $val;
					};
				};
				*/

				if (is_array($arr["request"]["prpnames"]))
				{
					foreach($arr["request"]["prpnames"] as $key => $val)
					{
						$prplist[$key]["caption"] = $val;
						$prplist[$key]["ord"] = $arr["request"]["prop_ord"][$key];
					};
				};

				if (is_array($arr["request"]["prpconfig"]))
				{
					foreach($arr["request"]["xconfig"] as $key => $val)
					{
						foreach($val as $key2 => $val2)
						{
							if ($val2 != $arr["request"]["prpconfig"][$key][$key2])
							{
								$prplist[$key][$key2] = $arr["request"]["prpconfig"][$key][$key2];
							};
						};
					};
				};
				
				// if there's any additional properties concerning object, then save them too just in case..

				//$this->cfg_groups = $grplist;
				$this->cfg_proplist = $prplist;

				break;

			// järjekorranumbritega on muidugi natuke raskem, ma peaksin neile
			// mingid default väärtused andma. Or it won't work. Or perhaps it will?
				
		};
	}	

	function callback_edit_groups($arr)
	{
		// hua, here I have to generate the list of tha groups
		$grps = new aw_array($arr["obj_inst"]->meta("cfg_groups"));
		$rv = array();
		$tps = array(
			"" => t("vaikestiil"),
			"stacked" => t("pealkiri yleval, sisu all"),
		);
		foreach($grps->get() as $key => $item)
		{
			$res = array();
			$rv["grpcaption[$key]"] = array(
				"name" => "grpcaption[".$key."]",
				"type" => "textbox",
				"size" => 40,
				"caption" => t("Pealkiri"),
				"value" => $item["caption"],
				"parent" => "b".$key,
			);
			$rv["grpstyle[$key]"] = array(
				"name" => "grpstyle[$key]",
				"type" => "select",
				"options" => $tps,
				"caption" => t("Stiil"),
				"selected" => $item["grpstyle"],
				"parent" => "b".$key,
			);
			$rv["grpview[$key]"] = array(
				"name" => "grpview[$key]",
				"type" => "checkbox",
				"no_caption" => 1,
				"caption" => t("Vaikimisi view vaade"),
				"value" => $item["grpview"],
				"parent" => "b$key",
			);
			$rv["b".$key] = array(
				"name" => "b".$key,
				"type" => "layout",
				"rtype" => "hbox",
			);
			/*
			$items = array(
				"type" => "text",
				"name" => ,
				"caption" => t("ab"),
				"items" => $res,
				"no_caption" => 1,
			);		

			$rv["b".$key] = $items;
			*/
		};
		return $rv;
	}

	function update_groups($arr)
	{
		$grplist = $this->grplist;
		if (is_array($arr["request"]["grpcaption"]))
		{
			foreach($arr["request"]["grpcaption"] as $key => $val)
			{
				//$grplist[$key] = array("caption" => $val);
				$grplist[$key]["caption"] = $val;
				$styl = $arr["request"]["grpstyle"][$key];
				$view = $arr["request"]["grpview"][$key];
				if(!empty($view))
				{
					$grplist[$key]["grpview"] = $view;
				}
				if (!empty($styl))
				{
					$grplist[$key]["grpstyle"] = $styl;
				};
			};
		};
		$this->cfg_groups = $grplist;
	}

	/** returns array of properties defined in the config form given

		@attrib api=1

		@param id required

		@comment
			id - oid of the config form to return the props for

	**/
	function get_props_from_cfgform($arr)
	{
		$cf = obj($arr["id"]);

		$cfgx = get_instance("cfg/cfgutils");
		$ret = $cfgx->load_properties(array(
			"clid" => $cf->prop("subclass"),
		));

		$subclass = $cf->prop("subclass");
		// XXX: can be removed once doc and document are merged
		$inst_name = ($subclass == CL_DOCUMENT) ? "doc" : $subclass;

		$class_i = get_instance($inst_name);
		$tmp = $class_i->load_from_storage(array(
			"id" => $cf->id()
		));
		
		$dat = array();
		foreach($tmp as $pn => $pd)
		{
			if ($pn == "needs_translation" || $pn == "is_translated" || $pn == "")
			{
				continue;
			}
			if($ret[$pn])
			{
				$dat[$pn] = $ret[$pn];
				$dat[$pn]["caption"] = $pd["caption"];
			}
		}

		return $dat;
	}

	/** draws a config form from the given object type object

		@attrib api=1

		@comment
			$ot - object type object's id 
			$reforb - reforb for the config form
			$errors - array returned from validate_data
			$values - array of property name => property value pairs

	**/
	function draw_cfgform_from_ot($arr)
	{
		// get all props
		$els = $this->get_props_from_ot($arr);

		$errs = new aw_array($arr["errors"]);
		$errs = $errs->get();

		$ret = array();
		foreach($els as $pn => $pd)
		{
			if (isset($errs[$pn]))
			{
				$ret[$pn."_err"] = array(
					"name" => $pn."_err",
					"type" => "text",
					"store" => "no",
					"value" => "<font color=red>".$errs[$pn]["msg"]."</font>",
					"no_caption" => 1
				);
			}
			$ret[$pn] = $pd;
		}
		$els = $ret;
		$els["__submit"] = array(
			"name" => "__submit",
			"type" => "submit",
			"value" => t("Salvesta")	
		);
		$rd = get_instance(CL_REGISTER_DATA);
		$els = $rd->parse_properties(array(
			"properties" => $els,
			"name_prefix" => ""
		));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($els as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));

		$this->read_template("show_form.tpl");
		$this->vars(Array(
			"form" => $html,
			"reforb" => $arr["reforb"]
		));
		return $this->parse();
	}

	/** returns array of properties given object type object

		@attrib api=1

		@comment
			$ot - object type object's id 
			$values - array of property name => property value pairs
			$for_show - if true, classificator values are resolved
			$site_lang - if true, translations are read from site language, not admin
		
	**/
	function get_props_from_ot($arr)
	{
		$ot = obj($arr["ot"]);
		$class_id = $ot->prop("type");

		$cfgx = get_instance("cfg/cfgutils");
		$els = $cfgx->load_properties(array(
			"clid" => $class_id,
		));


		if ($ot->prop("use_cfgform"))
		{
			$cff = obj($ot->prop("use_cfgform"));

			$v_ctr = safe_array($cff->meta("view_controllers"));
			$ctr = safe_array($cff->meta("controllers"));

			$tmp = array();
			foreach(safe_array($cff->meta("cfg_proplist")) as $pn => $pd)
			{
				$tmp[$pn] = $els[$pn];
				foreach($pd as $k => $v)
				{
					$tmp[$pn][$k] = $v;
				}
				$tmp[$pn]["controllers"] = $ctr[$pn];
				$tmp[$pn]["view_controllers"] = $v_ctr[$pn];
			}
			$els = $tmp;

			uasort($els, create_function('$a, $b','if ($a["ord"] == $b["ord"]) { return 0;} else {return $a["ord"] > $b["ord"] ? 1 : -1;}'));
	

			$trans = $cff->meta("translations");

			if ($arr["site_lang"])
			{
				$lc = aw_global_get("LC");
			}
			else
			{
				$lc = aw_ini_get("user_interface.default_language");
			}

			if (isset($trans[$lc]) && is_array($trans[$lc]) && count($trans[$lc]))
			{
				$tc = $trans[$lc];
				foreach($els as $pn => $pd)
				{
					if ($tc[$pn] != "")
					{
						$els[$pn]["caption"] = $tc[$pn];
					}
				}
			}
		}
		$tmp = array();
		foreach($els as $pn => $pd)
		{
			if ($pn == "is_translated" || $pn == "needs_translation")
			{
				continue;
			}

			if (isset($arr["values"]) && isset($arr["values"][$pn]))
			{
				$pd["value"] = $arr["values"][$pn];
			}
			if ($pd["type"] == "classificator")
			{
				$pd["object_type_id"] = $arr["ot"];
				if ($arr["for_show"] && is_oid($pd["value"]) && $this->can("view", $pd["value"]))
				{
					$tmpo = obj($pd["value"]);
					$pd["value"] = $tmpo->name();
				}
			}
			if ($pd["type"] == "textarea" && $arr["for_show"])
			{
				$pd["value"] = nl2br($pd["value"]);
			}
			$tmp[$pn] = $pd;
		}
		$ret = $tmp;
		return $ret;
	}

	function on_site_init($dbi, $site, &$ini_opts, &$log, &$osi_vars)
	{
		// create the default document config form 
		$form = obj($osi_vars["doc_conf_form"]);

		// Üldine (general)
		// 100, navtoolbar
		// 200, status
		// 300, title		
		// 400, tm
		// 500, lead		
		// 600, content
		// 700, moreinfo
		// 800, sbt
		// 900, aliasmgr
		// Seadistused (settings)
		// 100, show_title
		// 200, showlead
		// 300, show_print
		// 400, title_clickable
		
		// elements: 
		$els = array(
			"general" => array(
				"navtoolbar" => array(100, t("T&ouml;&ouml;riistariba")),
				"status" => array(200,t("Aktiivne")),
				"title" => array(300, t("Pealkiri")),
				"tm" => array(400, t("Kuup&auml;ev")),
				"lead" => array(500, t("Sissejuhatus")),
				"content" => array(600, t("Sisu")),
				"moreinfo" => array(700, t("Toimetamata")),
				"sbt" => array(800, t("Salvesta")),
				"aliasmgr" => array(900, t("Seostehaldur"))
			),
			"settings" => array(
				"show_title" => array(100, t("N&auml;ita pealkirja")),
				"showlead" => array(200, t("N&auml;ita sissejuhatust")),
				"show_print" => array(300, t("N&auml;ita prindi nuppu")),
				"title_clickable" => array(400, t("Pealkiri klikitav"))
			)
		);

		$this->cff_init_from_class($form, CL_DOCUMENT);

		$this->cff_remove_all_props($form);
		
		foreach($els as $grp => $gels)
		{
			foreach($gels as $el => $ord)
			{
				$this->cff_add_prop($form, $el, array("ord" => $ord[0], "group" => $grp, "caption" => $ord[1]));
			}
		}

		$form->save();
	}

	/**

		@attrib api=1

		@comment
			Initializes given cfgform object to contain all groups and props from the given class

			$o - cfgform object
			$clid - class id to init from
	**/
	function cff_init_from_class($o, $clid)
	{
		$o->set_prop("subclass", $clid);
		// fool around a bit to get the correct data
		$subclass = $clid;

		// now that's the tricky part ... this thingsbum overrides
		// all the settings in the document config form
		$this->_init_properties($subclass);
		$cfgu = get_instance("cfg/cfgutils");
		if ($subclass == CL_DOCUMENT)
		{
			$def = join("",file(aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml"));
			list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
			$this->cfg_proplist = $proplist;
			$this->cfg_groups = $grplist;
		}
		else
		{
			$tmp = aw_ini_get("classes");
			$fname = $tmp[$subclass]["file"];
			$def = join("",file(aw_ini_get("basedir") . "/xml/properties/class_base.xml"));
			list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
			$this->cfg_proplist = $proplist;
			$this->cfg_groups = $grplist;
			$fname = basename($fname);
			$def = join("",file(aw_ini_get("basedir") . "/xml/properties/$fname.xml"));
			list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
			// nono. It needs to fucking merge those things with classbase 
			$this->cfg_proplist = $this->cfg_proplist + $proplist;
			$this->cfg_groups = $this->cfg_groups + $grplist;
		};

		if (isset($this->cfg_proplist) && is_array($this->cfg_proplist))
		{
			$tmp = array();
			$cnt = 0;
			foreach($this->cfg_proplist as $key => $val)
			{
				if (empty($val["ord"]))
				{
					$cnt++;
					$val["tmp_ord"] = $cnt;
				};	
				$tmp[$key] = $val;
			};
			uasort($tmp,array($this,"__sort_props_by_ord"));
			$cnt = 0;
			$this->cfg_proplist = array();

			foreach($tmp as $key => $val)
			{
				unset($val["tmp_ord"]);
				if ($this->default_values[$key])
				{
					$val["default"] = $this->default_values[$key];	
				}
				else
				{
					unset($val["default"]);
				};
				$this->cfg_proplist[$key] = $val;
			};
			$o->set_meta("cfg_proplist",$this->cfg_proplist);
		};
		if (isset($this->cfg_groups))
		{
			$o->set_meta("cfg_groups",$this->cfg_groups);
		};
	}

	/**

		@attrib api=1

		@comment
			Removes all properties from the given config form

			$o - cfgform object
	**/
	function cff_remove_all_props($o)
	{
		$o->set_meta("cfg_proplist", array());
	}

	/**

		@attrib api=1

		@comment
			Adds the given property to the given config form object

			$o - cfgform object
			$pn - name of property to add
			$pd - array(caption, group, ord) for the property
	**/
	function cff_add_prop($o, $pn, $pd)
	{
		$pl = $o->meta("cfg_proplist");
		$pl[$pn] = array(
			"name" => $pn,
			"caption" => $pd["caption"],
			"group" => $pd["group"],
			"ord" => $pd["ord"]
		);

		uasort($pl,array($this,"__sort_props_by_ord"));
		$o->set_meta("cfg_proplist", $pl);
	}

	function get_cfg_proplist($id)
	{
		$o = obj($id);
		$ret = $o->meta("cfg_proplist");
		$lc = aw_ini_get("user_interface.default_language");
		$trans = $o->meta("translations");

		// okay, here, if there is no translation for the requested language, then 
		// read the captions from the translations file.

		$read_from_trans = true;
		if (isset($trans[$lc]) && is_array($trans[$lc]) && count($trans[$lc]))
		{
			$tc = $trans[$lc];
			foreach($ret as $pn => $pd)
			{
				if ($tc[$pn] != "")
				{
					$ret[$pn]["caption"] = $tc[$pn];
					$read_from_trans = false;
				}
			}
		}
		if ($read_from_trans && aw_global_get("LC") != $o->lang())
		{
			// get all props from class
			$tmp = obj();
			$tmp->set_class_id($o->subclass());
			foreach($tmp->get_property_list() as $pn => $pd)
			{
				// trick here is, that we do not need to redo the t() calls, because the translations are already loaded
				// so we just copy the captions
				if (isset($ret[$pn]))
				{
					$ret[$pn]["caption"] = $pd["caption"];
				}
			}
		}
		return $ret;
	}

	function get_cfg_groups($id)
	{
		$o = obj($id);
		$ret = $o->meta("cfg_groups");
		$lc = aw_ini_get("user_interface.default_language");
		$trans = $o->meta("grp_translations");
		$read_from_trans = true;
		if (isset($trans[$lc]) && is_array($trans[$lc]) && count($trans[$lc]))
		{
			$tc = $trans[$lc];
			foreach($ret as $pn => $pd)
			{
				if ($tc[$pn] != "")
				{
					$ret[$pn]["caption"] = $tc[$pn];
					$read_from_trans = false;
				}
			}
		}

		if ($read_from_trans && aw_global_get("LC") != $o->lang())
		{
			$tmp = obj();
			$tmp->set_class_id($o->subclass());
			foreach($tmp->get_group_list() as $gn => $gd)
			{
				// trick here is, that we do not need to redo the t() calls, because the translations are already loaded
				// so we just copy the captions
				if (isset($ret[$gn]))
				{
					$ret[$gn]["caption"] = $gd["caption"];
				}
			}
		}
		return $ret;
	}

	function get_sysdefault($arr = array())
	{
		// 2 passes, because I need to know which element is active before 
		// doing the table
		$active = false;
		$ol = new object_list(array(
			"class_id" => $this->clid,
			"subclass" => $arr["clid"],
			"lang_id" => array(),
			"flags" => array(
				"mask" => OBJ_FLAG_IS_SELECTED,
				"flags" => OBJ_FLAG_IS_SELECTED
			)
		));
		if (sizeof($ol->ids()) > 0)
		{
			$first = $ol->begin();
			$active = $first->id();
		};
		return $active;
	}
};
?>
