<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/webform.aw,v 1.1 2004/11/26 06:31:45 ahti Exp $
// webform.aw - Veebivorm 
/*

@classinfo syslog_type=ST_WEBFORM relationmgr=yes

@default table=objects
@default group=general

@property on_init type=hidden newonly=1 value=1 store=no
@caption Initsialiseeri objekt

@property style_folder type=relpicker reltype=RELTYPE_STYLE_FOLDER field=meta method=serialize
@caption Stiilide kataloog

@property redirect type=textbox field=meta method=serialize
@caption Kuhu suunata peale täitmist

@groupinfo form caption="Vorm" submit=no
@groupinfo props caption="Omadused" submit=no
@groupinfo preview caption="Eelvaade" submit=no
@groupinfo entries caption="Sisestused"
@groupinfo styles caption="Stiilid"

@groupinfo controllers caption="Kontrollerid" submit=no
@groupinfo set_controllers caption="Salvestamine" parent=controllers
@groupinfo get_controllers caption="Näitamine" parent=controllers

@property navtoolbar type=toolbar group=form no_caption=1
@caption Toolbar

@property form type=callback callback=callback_form group=form no_caption=1
@caption Vorm

@property availtoolbar type=toolbar group=props no_caption=1
@caption Toolbar

@property props type=callback callback=callback_props group=props no_caption=1
@caption Omadused

@property preview type=callback callback=callback_preview group=preview no_caption=1
@caption Eelvaade

@property entries type=table group=entries no_caption=1
@caption Sisestused

@property styles type=callback callback=callback_styles group=styles no_caption=1
@caption Stiilid

@property view_controllers type=callback callback=callback_view_controllers group=get_controllers no_caption=1
@caption Kontrollerid

@property submit_controllers type=callback callback=callback_submit_controllers group=set_controllers no_caption=1
@caption Kontrollerid

@property subaction type=hidden store=no group=form,props
@caption Subaction

@reltype METAMGR value=1 clid=CL_METAMGR
@caption Muutujate haldur

@reltype CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

@reltype CONTROLLER value=3 clid=CL_CFGCONTROLLER
@caption Salvestamise kontroller

@reltype VIEWCONTROLLER value=4 clid=CL_CFG_VIEW_CONTROLLER
@caption N&auml;tamise kontroller

@reltype EMAIL value=5 clid=CL_EMAIL
@caption Meiliaadress

@reltype OBJECT_TYPE value=6 clid=CL_OBJECT_TYPE
@caption Objekti tüüp

@reltype REGISTER value=7 clid=CL_REGISTER
@caption Register

@reltype STYLE value=8 clid=CL_CSS
@caption Stiil

@reltype STYLE_FOLDER value=9 clid=CL_MENU
@caption Stiilide kataloog

*/

class webform extends class_base
{
	function webform()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/webform",
			"clid" => CL_WEBFORM
		));
	}
	
	function callback_on_load($arr)
	{
		$this->cfgform_i = get_instance(CL_CFGFORM);
		if(is_oid($arr["request"]["id"]) && $this->can("view", $arr["request"]["id"]))
		{
			$obj_inst = obj($arr["request"]["id"]);
			if($this->cfgform = $obj_inst->get_first_obj_by_reltype("RELTYPE_CFGFORM"))
			{
				$this->cfgform_i->_init_cfgform_data($this->cfgform);
			}
		}
	}
	
	function callback_pre_save($arr)
	{
		if($this->cfgform)
		{
			$this->cfgform_i->callback_pre_save(array(
				"obj_inst" => &$this->cfgform,
				"request" => array("subclass" => CL_REGISTER_DATA),
			));
			$this->cfgform->save();
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "entries":
				if(!$register = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_REGISTER"))
				{
					return PROP_IGNORE;
				}
				$register_i = get_instance(CL_REGISTER);
				$register_i->do_data_tbl(array(
					"obj_inst" => $register,
					"prop" => &$arr["prop"],
					"request" => &$arr["request"],
				));
				break;
			case "navtoolbar":
				$this->cfgform_i->gen_navtoolbar($arr);
				break;
			case "availtoolbar":
				$this->cfgform_i->gen_availtoolbar($arr);
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
			case "on_init":
				$arr["obj_inst"]->save();
				$register_data = obj();
				$register_data->set_parent($arr["obj_inst"]->id());
				$register_data->set_class_id(CL_REGISTER_DATA);
				$register_data->set_name("register_data_".$arr["obj_inst"]->id());
				$register_data->set_status(STAT_ACTIVE);
				$register_data->save();
				$cfgform = obj();
				$cfgform->set_parent($arr["obj_inst"]->id());
				$cfgform->set_class_id(CL_CFGFORM);
				$cfgform->set_name("cfgform_".$arr["obj_inst"]->id());
				$cfgform->set_status(STAT_ACTIVE);
				$cfgform->save();
				// well, seems that this is the only way -- ahz
				$this->cfgform_i->set_property(array(
					"new" => 1,
					"prop" => array("name" => "subclass"),
					"request" => array("subclass" => CL_REGISTER_DATA),
				));
				// so, we to reverse the property adding of cfgform also -- ahz
				$cfgform->set_prop("subclass", CL_REGISTER_DATA);
				$cfgform->set_meta("cfg_groups", array("data" => array("caption" => "Andmed")));
				$cfgform->save();
				$arr["obj_inst"]->connect(array(
					"to" => $cfgform->id(),
					"reltype" => "RELTYPE_CFGFORM",
				));
				$object_type = obj();
				$object_type->set_parent($arr["obj_inst"]->id());
				$object_type->set_class_id(CL_OBJECT_TYPE);
				$object_type->set_name("object_type_".$arr["obj_inst"]->id());
				$object_type->set_status(STAT_ACTIVE);
				$object_type->set_prop("use_cfgform", $cfgform->id());
				$object_type->set_prop("type", CL_REGISTER_DATA);
				$object_type->save();
				$arr["obj_inst"]->connect(array(
					"to" => $object_type->id(),
					"reltype" => "RELTYPE_OBJECT_TYPE",
				));
				$object_type->connect(array(
					"to" => $cfgform->id(),
					"reltype" => "RELTYPE_OBJECT_CFGFORM",
				));
				$metamgr = obj();
				$metamgr->set_parent($arr["obj_inst"]->id());
				$metamgr->set_class_id(CL_METAMGR);
				$metamgr->set_name("metamgr_".$arr["obj_inst"]->id());
				$metamgr->set_status(STAT_ACTIVE);
				$metamgr->save();
				$arr["obj_inst"]->connect(array(
					"to" => $metamgr->id(),
					"reltype" => "RELTYPE_METAMGR",
				));
				$dir = obj();
				$dir->set_parent($arr["obj_inst"]->parent());
				$dir->set_class_id(CL_MENU);
				$dir->set_name("dir_".$arr["obj_inst"]->id());
				$dir->set_status(STAT_ACTIVE);
				$dir->save();
				$register = obj();
				$register->set_parent($arr["obj_inst"]->parent());
				$register->set_class_id(CL_REGISTER);
				$register->set_name("register_".$arr["obj_inst"]->id());
				$register->set_status(STAT_ACTIVE);
				$register->set_prop("data_cfgform", $cfgform->id());
				$register->set_prop("default_cfgform", 1);
				$register->set_prop("data_rootmenu", $dir->id());
				$register->set_prop("show_all", 1);
				$register->set_prop("per_page", 50);
				$register->save();
				$arr["obj_inst"]->connect(array(
					"to" => $register->id(),
					"reltype" => "RELTYPE_REGISTER",
				));
				$register_data->connect(array(
					"to" => $register->id(),
					"reltype" => "RELTYPE_REGISTER",
				));
				$register->connect(array(
					"to" => $dir->id(),
					"reltype" => "RELTYPE_MENU",
				));
				$register->connect(array(
					"to" => $cfgform->id(),
					"reltype" => "RELTYPE_CFGFORM",
				));
				break;
				
			case "view_controllers":
				if($this->cfgform)
				{
					$this->cfgform->set_meta("controllers", $arr["request"]["controllers"]);
				}
				break;
			
			case "submit_controllers":
				if($this->cfgform)
				{
					$this->cfgform->set_meta("controllers", $arr["request"]["view_controllers"]);
				}
				break;
				
			case "props":
				if($this->cfgform)
				{
					$this->cfgform_i->add_new_properties(array(
						"obj_inst" => &$this->cfgform,
						"request" => &$arr["request"],
					));
					if(($metamgr = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_METAMGR")) && ($object_type = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE")))
					{
						$classificator = $object_type->meta("classificator");
						foreach($this->cfgform_i->cfg_proplist as $key => $val)
						{
							if($this->cfgform_i->all_props[$key]["type"] == "classificator" && !$classificator[$key])
							{
								$no = obj();
								$no->set_class_id(CL_META);
								$no->set_status(STAT_ACTIVE);
								$no->set_parent($metamgr->id());
								$no->set_name($val["name"]);
								$no->save();
								$classificator[$key] = $no->id();
							}
						}
						$object_type->set_meta("classificator", $classificator);
						$object_type->save();
					}
				}
				break;
				
			case "form":
				if($this->cfgform)
				{
					if(!$object_type = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE"))
					{
						return PROP_IGNORE;
					}
					$classificator = $object_type->meta("classificator");
					$clf_type = $arr["request"]["clf_type"];
					$this->cfgform_i->save_layout(array(
						"obj_inst" => &$this->cfgform,
						"request" => &$arr["request"],
					));
					$prplist = $this->cfgform_i->cfg_proplist;
					if(is_array($arr["request"]["prp_opts"]))
					{
						foreach($arr["request"]["prp_opts"] as $key => $val)
						{
							if($prplist[$key])
							{
								foreach($val as $key2 => $val2)
								{
									$prplist[$key][$key2] = $arr["request"]["prp_opts"][$key][$key2];
								}
							}
						}
					}
					if($arr["request"]["subaction"] == "delete")
					{
						$mark = $arr["request"]["mark"];
						if(is_array($mark))
						{
							foreach($mark as $pkey => $val)
							{
								if(is_oid($classificator[$pkey]) && $this->can("delete", $classificator[$pkey]))
								{
									$meta = obj($classificator[$pkey]);
									$meta->delete();
								}
								unset($classificator[$pkey]);
								unset($clf_type[$key]);
							}
						}
					}
					if(is_array($arr["request"]["prp_metas"]))
					{
						foreach($arr["request"]["prp_metas"] as $key => $val)
						{
							$prps = explode(";", $val);
							if($prplist[$key] && !empty($prps) && is_oid($classificator[$key]) && $this->can("view", $classificator[$key]))
							{
								$e_prp_list = new object_list(array(
									"parent" => $classificator[$key],
									"class_id" => CL_META,
									"status"=> STAT_ACTIVE,
								));
								$e_prps = $e_prp_list->names();
								$e_prp_list->delete();
								foreach($prps as $prp)
								{
									$prp = trim($prp);
									if(in_array($prp, $e_prps))
									{
										continue;
									}
									$no = obj();
									$no->set_class_id(CL_META);
									$no->set_status(STAT_ACTIVE);
									$no->set_parent($classificator[$key]);
									$no->set_name($prp);
									$no->save();
								}
							}
						}
						$object_type->set_meta("clf_type", $clf_type);
						$object_type->set_meta("classificator", $classificator);
						$object_type->save();
					}
					$this->cfgform_i->cfg_proplist = $prplist;
				}
				break;
			case "entries":
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
			case "styles":
				$arr["obj_inst"]->set_meta("styles", safe_array($arr["request"]["style"]));
				$arr["obj_inst"]->set_meta("m_styles", safe_array($arr["request"]["m_style"]));
				$arr["obj_inst"]->save();
				break;
		}
		return $retval;
	}
	
	function callback_form($arr)
	{
		$this->read_template("layout.tpl");
		$used_props = $by_group = array();

		if (is_array($this->cfgform_i->grplist))
		{
			foreach($this->cfgform_i->grplist as $key => $val)
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

		if (is_array($this->cfgform_i->prplist))
		{
			foreach($this->cfgform_i->prplist as $property)
			{
				if (!empty($property["group"]))
				{
					if (!is_array($property["group"]))
					{
						$by_group[$property["group"]][] = $property;
					}
					else
					{
						list(,$first) = each($property["group"]);
						$by_group[$first][] = $property;
					}
				}
			}
		}
		$c = "";
		$cnt = 0;
		$capt_opts = array(
			0 => "default",
			"top" => "Peal",
			"bottom" => "All",
			"left" => "Vasakul",
			"right" => "Paremal",
			//"in" => "Sees",
		);
		$prp_types = array(
			"" => "-- vali --",
			"mselect" => "Mitmerealine rippmenüü",
			"select" => "Rippmenüü",
			"checkboxes" => "Märkeruut",
			"radiobuttons" => "Raadionupp",
		);
		$object_type = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");
		$metamgr = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_METAMGR");
		$clf_type = $object_type->meta("clf_type");
		$classificator = $object_type->meta("classificator");
		foreach($by_group as $key => $proplist)
		{
			$this->vars(array(
				"grp_caption" => $this->cfgform_i->grplist[$key]["caption"]." ($key)",
				"grpid" => $key,
			));
			$sc = "";
			foreach($proplist as $property)
			{
				$cnt++;
				$prpdata = $this->cfgform_i->all_props[$property["name"]];
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
					"capt_ord" => html::select(array(
						"name" => "prp_opts[".$prpdata["name"]."][wf_capt_ord]",
						"style" => "border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;",
						"options" => $capt_opts,
						"selected" => $property["wf_capt_ord"],
					)),
				));
				if($prpdata["type"] == "classificator")
				{
					$metas = new object_list(array(
						"parent" => $classificator[$prpdata["name"]],
						"class_id" => CL_META,
						"status" => STAT_ACTIVE,
						"sort_by" => "objects.name DESC",
					));
					$prp_metas = implode(";", $metas->names());
					$this->vars(array(
						"clf_type" => html::select(array(
							"name" => "clf_type[".$prpdata["name"]."]",
							"options" => $prp_types,
							"selected" => $clf_type[$prpdata["name"]],
						)),
						"prp_metas" => $prp_metas,
						"metamgr_link" => html::get_change_url($metamgr->id(), array(
							"group" => "manager",
							"meta" => $classificator[$prpdata["name"]],
						)),
					));
					$this->vars(array(
						"clf1" => $this->parse("clf1"),
						"clf3" => $this->parse("clf3"),
					));
				}
				else
				{
					$this->vars(array(
						"clf_type" => "",
						"prp_metas" => "",
						"metamgr_link" => "",
						"clf1" => "",
						"clf3" => "",
					));
				}
				$sc .= $this->parse("property");
				/*
				if ($this->is_template($prpdata["type"]."_options"))
				{
					$this->vars(array(
						"richtext_checked" => checked($property["richtext"] == 1),
						"richtext" => $property["richtext"],
					));
					$sc .= $this->parse($prpdata["type"]."_options");
				};
				*/
			}
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
	
	function load_styles($obj_inst)
	{
		classload("layout/active_page_data");
		$style_folders = $obj_inst->connections_from(array(
			"type" => "RELTYPE_STYLE_FOLDER",
		));
		$styles = $obj_inst->connections_from(array(
			"type" => "RELTYPE_STYLE",
		));
		$this->all_styles = array();
		foreach($style_folders as $style_folder)
		{
			$styles = new object_list(array(
				"parent" => $style_folder->prop("to"),
				"class_id" => CL_CSS,
			));
			$this->all_styles = $this->all_styles + $styles->names();
		}
		foreach($this->all_styles as $key => $val)
		{
			active_page_data::add_site_css_style($key);
		}
		foreach($styles as $style)
		{
			$this->all_styles[$style->prop("to")] = $style->prop("to.name");
		}
	}
	
	function callback_styles($arr)
	{
		//$object_type = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");
		$sel_styles = safe_array($arr["obj_inst"]->meta("styles"));
		$m_styles = safe_array($arr["obj_inst"]->meta("m_styles"));
		$this->load_styles($arr["obj_inst"]);
		$this->all_styles = array(0 => "-- Vali --") + $this->all_styles;
		$props = array();
		$props["error"] = array(
			"name" => "m_style[error]",
			"caption" => "Veateate stiil",
			"type" => "select",
			"options" => $this->all_styles,
			"selected" => $m_styles["error"],
		);
		foreach($this->cfgform_i->prplist as $key => $val)
		{
			$props[$key] = array(
				"name" => "style[$key][prop]",
				"caption" => $val["caption"]." stiil",
				"type" => "select",
				"options" => $this->all_styles,
				"selected" => $sel_styles[$key]["prop"],
			);
			$props[$key."_capt"] = array(
				"name" => "style[$key][caption]",
				"caption" => $val["caption"]." pealkirja stiil",
				"type" => "select",
				"options" => $this->all_styles,
				"selected" => $sel_styles[$key]["caption"],
			);
		}
		return $props;
		//foreach($this->cfg_proplist
	}
	
	function callback_preview($arr)
	{
		return array("prop1" => array(
			"name" => "prop1",
			"type" => "text",
			"no_caption" => 1,
			"value" => $this->show(array("id" => $arr["obj_inst"]->id())),
		));
	}
	
	function callback_view_controllers($arr)
	{
		if($this->cfgform)
		{
			return $this->cfgform_i->gen_view_controller_props(array("obj_inst" => $this->cfgform));
		}
	}
	
	function callback_submit_controllers($arr)
	{
		if($this->cfgform)
		{
			return $this->cfgform_i->gen_controller_props(array("obj_inst" => $this->cfgform));
		}
	}
	
	function callback_props($arr)
	{
		return $this->cfgform_i->callback_gen_avail_props($arr);
		//arr($rval);
	}
	
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$obj_inst = obj($arr["id"]);
		$this->load_styles($obj_inst);
		$object_type = $obj_inst->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE");
		$rval = $this->draw_cfgform_from_ot(array(
			"ot" => $object_type->id(),
			"reforb" => $this->mk_reforb("save_form_data", array(
				"id" => $arr["id"],
				"return_url" => $obj_inst->prop("redirect"),
				"errors" => aw_global_get("wf_errors"),
				"values" => aw_global_get("wf_data"),
			)),
			"obj_inst" => $obj_inst,
		));
		aw_session_del("wf_errors");
		aw_session_del("wf_data");
		return $rval;
	}
	
	function draw_cfgform_from_ot($arr)
	{
		// get all props
		$cfgform_i = get_instance(CL_CFGFORM);
		$els = $cfgform_i->get_props_from_ot($arr);
		$cfgform = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CFGFORM");
		$sel_styles = safe_array($arr["obj_inst"]->meta("styles"));
		$errs = new aw_array($arr["errors"]);
		$errs = $errs->get();
		$all_props = safe_array($cfgform->meta("cfg_proplist"));
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
		$rd = get_instance(CL_REGISTER_DATA);
		$els = $rd->parse_properties(array(
			"properties" => $els,
		));
		foreach($els as $key => $val)
		{
			$els[$key]["capt_ord"] = $all_props[$key]["wf_capt_ord"];
			$els[$key]["style"] = $sel_styles[$key];
		}
		classload("cfg/htmlclient");
		$htmlc = new htmlclient(array(
			"template" => "webform.tpl",
			"styles" => safe_array($arr["obj_inst"]->meta("m_styles")),
		));
		$htmlc->start_output();
		foreach($els as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1,
		));

		$this->read_template("show_form.tpl");
		$this->vars(Array(
			"form" => $html,
			"reforb" => $arr["reforb"],
		));
		return $this->parse();
	}
	
	/**

		@attrib name=save_form_data nologin=1 all_args=1
		
		@param id required type=int acl=view
		@param return_url optional
	**/
	function save_form_data($arr)
	{
		$rval = aw_ini_get("baseurl").$arr["return_url"];
		$obj_inst = obj($arr["id"]);
		if(!$object_type = $obj_inst->get_first_obj_by_reltype("RELTYPE_OBJECT_TYPE"))
		{
			return $rval;
		}
		if(!$cfgform = $obj_inst->get_first_obj_by_reltype("RELTYPE_CFGFORM"))
		{
			return $rval;
		}
		$is_valid = $this->validate_data(array(
			"cfgform_id" => $cfgform->id(),
			"request" => $arr,
		));
		if(!empty($is_valid))
		{
			aw_session_set("wf_errors", $is_valid);
			aw_session_set("wf_data", $arr);
		}
		else
		{
			$o = obj();
			$parent = $obj_inst->parent();
			if($register = $obj_inst->get_first_obj_by_reltype("RELTYPE_REGISTER"))
			{
				$prop = $register->prop("data_rootmenu");
				if(!empty($prop))
				{
					$parent = $prop;
				}
			}
			$o->set_class_id(CL_REGISTER_DATA);
			$o->set_parent($parent);
			foreach($o->get_property_list() as $pn => $pd)
			{
				$o->set_prop($pn, $arr[$pn]);
			}
			$o->set_meta("object_type", $object_type->id());
			$o->set_meta("cfgform_id", $cfgform->id());
			$o->set_name("entry ".date("d-m-Y H:i"));
			$o->save();
		}
		return $rval;
	}
}
?>
