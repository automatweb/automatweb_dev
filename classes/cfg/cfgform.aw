<?php
// cfgform.aw - configuration form
// adds, changes and in general manages configuration forms

//!!! todo. default cfgformi m22ramine editonlyna yldise alla. koos lingiga current defauldile.

// cfgview -- cfgform is embedded as alias and requested object is shown as configured in that cfgform
// awcb -- automatweb class_base

/*
	@classinfo relationmgr=yes syslog_type=ST_CFGFORM

	@groupinfo groupdata caption=Tabid
		@groupinfo groupdata_a caption=Tabid parent=groupdata
		@groupinfo groupdata_b caption=Liikumine parent=groupdata

	@groupinfo layout caption=Layout
	@groupinfo avail caption="K&otilde;ik omadused"
	@groupinfo controllers caption="Kontrollerid"

	@groupinfo set_controllers caption=Salvestamine parent=controllers
	@groupinfo get_controllers caption=N&auml;itamine parent=controllers

	@groupinfo settings caption="Seaded"
	@groupinfo defaults caption="Omaduste v&auml;&auml;rtused" parent=settings
	@groupinfo system caption="Vormi seaded" parent=settings
	@groupinfo cfgview_settings caption="Klass aliasena" parent=settings
	@groupinfo orb_settings caption="ORB seaded" parent=settings
	@groupinfo view_settings caption="Liidese kuvamine" parent=settings

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
	@groupinfo show_props caption="Omaduste maha keeramine"
	@groupinfo transl caption="T&otilde;lgi nime"


	@default table=objects
		@property cfg_proplist type=hidden field=meta method=serialize
		@caption Omadused

		@property cfg_groups type=hidden field=meta method=serialize
		@caption Tabid


	@default group=general
		@property subclass type=select newonly=1
		@caption Klass

		@property ctype type=text editonly=1 field=subclass
		@caption T&uuml;&uuml;p

	@default field=meta
	@default method=serialize

		@property use_output type=relpicker reltype=RELTYPE_OUTPUT
		@caption V&auml;ljundvorm

		@property xml_definition type=fileupload editonly=1
		@caption Uploadi vormi fail

		@property preview type=text store=no editonly=1
		@caption Definitsioon


	@default group=groupdata_a
		@property edit_groups_tb type=toolbar no_caption=1 store=no
		@caption Tabide toolbar

		@property edit_groups type=table no_caption=1 store=no
		@caption Muuda tabe

		@layout add_grp type=vbox
		@caption Lisa uus tab
			@property add_grp_return type=text store=no parent=add_grp
			@caption

			@property add_grp_name type=textbox store=no parent=add_grp
			@comment Süsteemne identifitseerija tabile. Ainult ladina tähestiku tähed ning alakriips ( _ ) lubatud. Vähim pikkus 2.
			@caption Nimi

			@property add_grp_caption type=textbox store=no parent=add_grp
			@caption Pealkiri

			@property add_grp_parent type=select store=no parent=add_grp
			@caption Millise tabi alla


	@default group=groupdata_b
		@property group_movement type=table store=no no_caption=1
		@caption Tabide vaheline liikumine


	@default group=layout
		@property navtoolbar type=toolbar store=no no_caption=1 editonly=1
		@caption Toolbar

		@property layout type=callback callback=callback_gen_layout store=no no_caption=1
		@caption Layout


	@property availtoolbar type=toolbar group=avail store=no no_caption=1 editonly=1
	@caption Av. Toolbar

	@property availprops type=table store=no group=avail no_caption=1
	@caption K&otilde;ik omadused

	@property subaction type=hidden store=no group=layout,avail
	@caption Subaction (sys)

	@property post_save_controllers type=relpicker multiple=1 size=3 group=set_controllers reltype=RELTYPE_CONTROLLER
	@caption Salvestamisj&auml;rgne kontroller

	@property prop_submit_controllers type=text subtitle=1 group=set_controllers
	@caption Omaduste kontrollerid

	@property gen_submit_controllers type=callback callback=gen_controller_props group=set_controllers no_caption=1


	@property gen_view_controllers type=callback callback=gen_view_controller_props group=get_controllers
	@caption Kontrollerid

	@property default_table type=table group=defaults no_caption=1
	@caption Vaikimisi väärtused

	@property trans_tbl_capt type=text subtitle=1 group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12
	@caption Omadused

	@property trans_tbl type=table group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12 no_caption=1

	@property trans_tbl_grp_capt type=text group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12 subtitle=1
	@caption Tabid

	@property trans_tbl_grps type=table group=lang_1,lang_2,lang_3,lang_4,lang_5,lang_6,lang_7,lang_8,lang_9,lang_10,lang_11,lang_12 no_caption=1


@default group=system
	@property sysdefault type=table no_caption=1
	@caption S&uuml;steemi seaded


@default group=show_props
@layout mlist type=hbox width=20%:80%
	@property treeview type=treeview store=no parent=mlist no_caption=1
	@property props_list type=table store=no parent=mlist no_caption=1


@default group=transl
	@property transl type=callback callback=callback_get_transl
	@caption T&otilde;lgi


@default group=cfgview_settings
	@property cfgview_action type=select field=meta method=serialize
	@caption N&auml;itamise meetod

	@property cfgview_view_params type=textbox field=meta method=serialize
	@caption Parameetrid vaatamisele (view)
	@comment Lisatakse iga kord p&auml;ringu url-ile. Formaat: param_nimi=param_v&auml;&auml;rtus&...

	@property cfgview_change_params type=textbox field=meta method=serialize
	@caption Parameetrid muutmisele (change)
	@comment Lisatakse iga kord p&auml;ringu url-ile. Formaat: param_nimi=param_v&auml;&auml;rtus&...

	@property cfgview_new_params type=textbox field=meta method=serialize
	@caption Parameetrid lisamisele (new)
	@comment Lisatakse iga kord p&auml;ringu url-ile. Formaat: param_nimi=param_v&auml;&auml;rtus&...

	@property cfgview_grps type=select multiple=1 size=10 field=meta method=serialize
	@caption N&auml;idatavad tabid

	@property cfgview_ru type=textbox field=meta method=serialize
	@caption Aadress kuhu suunata


@default group=orb_settings
	@property orb_settings type=table store=no no_capton=12


@default group=view_settings
	@property classinfo_fixed_toolbar type=checkbox ch_value=1
	@caption Fix. toolbar

	@property classinfo_allow_rte type=chooser
	@caption RTE

	@property classinfo_disable_relationmgr type=checkbox ch_value=1
	@caption Peida seostehaldur

	@property awcb_add_id type=checkbox ch_value=1 default=0
	@caption Lisa id
	@comment Lisa klassi objekti kuvamisel konteineri id-le seadete vormi id

	@property awcb_form_only type=checkbox ch_value=1 default=0
	@caption N&auml;ita ainult vormi



// ---------- RELATIONS -------------
	@reltype PROP_GROUP value=1 clid=CL_MENU
	@caption omaduste kataloog

	@reltype CONTROLLER value=3 clid=CL_CFGCONTROLLER
	@caption Salvestamise kontroller

	@reltype VIEWCONTROLLER value=5 clid=CL_CFG_VIEW_CONTROLLER
	@caption N&auml;itamise kontroller

	@reltype OUTPUT value=4 clid=CL_CFGFORM
	@caption V&auml;ljund

	@reltype VIEW_DFN_GRP value=6 clid=CL_GROUP
	@caption Kasutajagrupp omaduste lubamiseks/keelamiseks


	// so, how da fuck do I implement the grid layout thingie?
	// add_item (item, row, col)

	// so .. first I have to implement a new attribute for layout thingie

	// and then I want to be able to add new widgets in the same order they are arriving

*/
class cfgform extends class_base
{
	var $cfgview_actions = array();

	function cfgform($arr = array())
	{
		$this->init(array(
			"clid" => CL_CFGFORM,
			"tpldir" => "cfgform",
		));
		$this->trans_props = array(
			"name"
		);
		$this->cfgview_actions = array(
			"view" => t("Vaatamine (view)"),
			"change" => t("Muutmine (change)"),
			"new" => t("Lisamine (new)"),
			"cfgview_change_new" => t("Muutmine (ka lisamine lubatud)"),
			"cfgview_view_new" => t("Vaatamine (ka lisamine lubatud)"),
		);
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;

		if (!empty($arr["request"]["cfgform_add_grp"]) and "add_grp" !== substr($data["name"], 0, 7))
		{ // exclude other props from add grp form
			return PROP_IGNORE;
		}

		if (empty($arr["request"]["cfgform_add_grp"]) and "add_grp" === substr($data["name"], 0, 7))
		{ // exclude add grp form props
			return PROP_IGNORE;
		}

		switch($data["name"])
		{
			case "edit_groups_tb":
				$this->_edit_groups_tb($arr);
				break;

			case "orb_settings":
				$this->_get_orb_settings($arr);
				break;

			case "edit_groups":
				$this->_edit_groups_tbl($arr);
				break;

			// add grp form props
			case "add_grp_parent":
				$data["options"][0] = t("Peatasemele");

				foreach ($this->grplist as $grp_name => $grp_data)
				{
					if (empty($grp_data["parent"]))
					{
						$data["options"][$grp_name] = $grp_data["caption"] . " [" . $grp_name . "]";
					}
				}
				break;

			case "add_grp_return":
				$data["value"] = html::href(array(
					"caption" => t("[ Tagasi ]"),
					"url" => aw_url_change_var("cfgform_add_grp", NULL),
				));
				break;
			// END add grp form props


			case "group_movement":
				$this->_group_movement($arr);
				break;

			case "classinfo_allow_rte":
				$data["options"] = array(
					0 => t("Ei kuva"),
					1 => t("AW RTE"),
					2 => t("FCKeditor"),
				);
				$data["type"] = "select";
				break;

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

			case "treeview":
				$this->do_meta_tree($arr);
				break;
			case "props_list":
				$retval = $this->do_table($arr);
				break;

			case "cfgview_action":
				$data["options"] = $this->cfgview_actions;
				break;

			case "cfgview_view_params":
				$applicable_methods = array("view", "cfgview_view_new");
				if (!in_array($arr["obj_inst"]->prop("cfgview_action"), $applicable_methods))
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "cfgview_change_params":
				$applicable_methods = array("change", "cfgview_change_new");
				if (!in_array($arr["obj_inst"]->prop("cfgview_action"), $applicable_methods))
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "cfgview_new_params":
				$applicable_methods = array("new", "cfgview_view_new", "cfgview_change_new");
				if (!in_array($arr["obj_inst"]->prop("cfgview_action"), $applicable_methods))
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "cfgview_grps":
				$parent_grps = array();

				foreach ($this->grplist as $grp_name => $grp_data)
				{
					$data["options"][$grp_name] = $grp_data["caption"] . " [" . $grp_name . "]";

					if (!empty($grp_data["parent"]))
					{
						$parent_grps[] = $grp_data["parent"];
					}
				}

				foreach ($parent_grps as $name)
				{
					unset($data["options"][$name]);
				}
				break;
		}

		return $retval;
	}

	function do_meta_tree($arr)
	{
		if(!$arr["request"]["meta"]) $arr["request"]["meta"] = $arr["obj_inst"]->meta("group_to_show");

		$tree = &$arr["prop"]["vcl_inst"];
		$obj = $arr["obj_inst"];
		$grps = new aw_array($arr["obj_inst"]->meta("cfg_groups"));

		foreach($grps->get() as $name => $grp)
		{
			$parent = empty($grp["parent"]) ? 0 : $grp["parent"];
			$tree->add_item($parent,array(
				"name" => $grp["caption"],
				"id" => $name,
				"url" => aw_url_change_var(array("meta" => $name)),
			));
		}

		$tree->set_selected_item($arr["request"]["meta"]);

		// hm .. now I also need to create an object_tree, eh?
		//$arr["prop"]["value"] = $tree->finalize_tree();
	}

	function do_table(&$arr)
	{
		$groups_list = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_VIEW_DFN_GRP")));

		if (!$groups_list->count())
		{
			if ($arr["obj_inst"]->meta("has_show_to_groups_by_rel")) // backward compatibility check
			{
				$arr["prop"]["error"] = t("Seostatud kasutajagruppe omaduste lubamiseks/keelamiseks ei leidund");
				return PROP_ERROR;
			}
			else
			{ // backward compatibility
				$groups_list = new object_list(array(
					"class_id" => array(CL_GROUP),
					"lang_id" => "%",
				));
			}
		}

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"callback" => array(&$this, "callb_name"),
			"callb_pass_row" => true,
		));

		foreach($groups_list->arr() as  $group_obj)
		{
			$t->define_field(array(
				"name" => $group_obj->id(),
				"caption" => html::href(array(
					"caption" => $group_obj->name(),
					"url" => "#",
					"onClick" => "aw_sel_chb(document.changeform,\"[".$group_obj->id()."]\");"
				))
			));
		}

		$by_group = array();

		if (is_array($this->grplist))
		{
			foreach($this->grplist as $key => $val)
			{
				if (!is_numeric($key))
				{
					$by_group[$key] = array();
				}
			}
		}

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
							$by_group[$gkey][] = $property;
						};
					};

				};
			};
		}

		$meta = $arr["obj_inst"]->meta("show_to_groups");
		$html = get_instance("html");

		if(!$arr["request"]["meta"]) $arr["request"]["meta"] = $arr["obj_inst"]->meta("group_to_show");

		if (!is_array($meta))
		{ // no backward compatibility needed
			$arr["obj_inst"]->set_meta("has_show_to_groups_by_rel", true);
		}

		$arr["obj_inst"]->set_meta("group_to_show",$arr["request"]["meta"]);
		$arr["obj_inst"]->save();

		foreach($by_group[$arr["request"]["meta"]] as $property)
		{
			$row = array(
				"id" => $property["name"],
				"name" => $property["caption"],
			);

			foreach($groups_list->ids() as  $gid)
			{
				$checked = 1;
				if($meta[$property["name"]] && !array_key_exists($gid , $meta[$property["name"]])) $checked = null;
				$row[$gid] = $html->checkbox($args = array(
					"name" => "show_to_groups[".$property["name"]."][".$gid."]",
					"value" => 1,
					"checked" => $checked ,
				));
			}
			$t ->define_data($row);
		}

		$t->set_sortable(false);

		return PROP_OK;
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
		uasort($ps, create_function('$a, $b','return $a["ord"] - $b["ord"];'));
		foreach($ps as $pn => $pd)
		{
			$t->define_data(array(
				"property" => $pn,
				"orig_str" => $pd["type"] == "text" ? $pd["value"] : $pd["caption"],
				"trans_str" => html::textbox(array(
					"name" => "dat[".$lid."][$pn]",
					"value" => $trans[$lid][$pn]
				))
			));
		}
		$t->set_sortable(false);
	}

	function _trans_tbl_grps($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$ld = $this->_init_trans_tbl($t, $arr["obj_inst"], $arr["request"], "Tab");
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
			"caption" => t("Tab"),
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
		$this->layout = safe_array($obj->meta("cfg_layout"));
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
		}

		$inst = get_instance($fl);
		$this->all_props = $inst->get_all_properties();
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$o = $arr["obj_inst"];
		$retval = PROP_OK;

		switch($data["name"])
		{
			// add grp form props
			case "orb_settings":
				$this->_set_orb_settings($arr);
				break;

			case "add_grp_parent":
				if (!empty($arr["request"]["cfgform_add_grp"]))
				{
					$this->cfg_groups = $o->meta("cfg_groups");
					$name = strtolower($arr["request"]["add_grp_name"]);
					$parent = $data["value"];
					$caption = $arr["request"]["add_grp_caption"];

					if ((strlen($name) < 2) or preg_match("/[^a-z_]/", $name))
					{
						$data["error"] = t("Ebasobiv tabi nimi.");
						$retval = PROP_ERROR;
					}
					elseif (array_key_exists($name, $this->cfg_groups))
					{
						$data["error"] = t("Selle nimega tab juba olemas.");
						$retval = PROP_ERROR;
					}
					elseif ($parent and (!array_key_exists($parent, $this->cfg_groups) or !empty($this->cfg_groups[$parent]["parent"])))
					{
						$data["error"] = t("Selle tabi alla pole võimalik luua.");
						$retval = PROP_ERROR;
					}
					else
					{
						$this->cfg_groups[$name] = array(
							"caption" => $caption,
							"parent"  => $parent ? $parent : "",
							"user_defined"  => 1,
						);
						$this->cfgform_add_grp_ok = true;
					}
				}
				break;

			case "add_grp_name":
			case "add_grp_caption":
				$retval = PROP_IGNORE;
				break;
			// END add grp form props

			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "group_movement":
				$arr["obj_inst"]->set_meta("buttons", $arr["request"]["bts"]);
				break;

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
				$data["value"] = urldecode($data["value"]);
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
				}
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
					$this->cff_init_from_class($arr["obj_inst"], $arr["request"]["subclass"], false);
				}
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
			case "props_list":
				$this->submit_meta(&$arr);
				break;
		}
		return $retval;
	}

	function submit_meta($arr = array())
	{
		$show_to_groups = $arr["obj_inst"]->meta("show_to_groups");
		foreach($arr["request"]["show_to_groups"] as $key => $val)
		{
			$show_to_groups[$key] = $val;
		}
		$arr["obj_inst"]->set_meta("show_to_groups" , $show_to_groups);
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
		}

		$this->_save_cfg_groups($obj_inst);
		$this->_save_cfg_props($obj_inst);
		$this->_save_cfg_layout($obj_inst);

		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
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

	function callback_mod_reforb($arr, $request)
	{
		$arr["cfgform_add_grp"] = $request["cfgform_add_grp"];
	}

	function callback_mod_retval($arr)
	{
		if (!empty($this->cfgform_add_grp_ok))
		{
			unset($arr["args"]["cfgform_add_grp"]);
		}
	}

/* replaced by self::_sort_groups*/
	function sort_grplist()
	{
		$order = array();
		$grps = array();
		foreach($this->grplist as $key => $item)
		{
			$order[$key] = $item["ord"];
		}
		asort($order);
		foreach($order as $key => $val)
		{
			$grps[$key] = $this->grplist[$key];
		}
		$this->grplist = $grps;
	}
/**/

	// Sorts meta cfg_groups grplist by ord, retains original order where ord not defined.
	// Places unordered subgroups after their parents
	// Separately handles 2-level structure -- parent groups and subgroups.
	function _sort_groups()
	{
		// separate unordered, parent and subgroups
		$pg = $sg = $pgo = $sgo = array(); // (unordered parent groups, unordered subgroups, ordered parent groups, ordered subgroups)

		foreach($this->cfg_groups as $grp_name => $grp_data)
		{
			$parent = empty($grp_data["parent"]) ? 0 : $grp_data["parent"];

			if (empty($grp_data["ord"]))
			{
				if (0 === $parent)
				{
					$pg[] = $grp_name;
				}
				else
				{
					$sg[$parent][] = $grp_name;
				}
			}
			else
			{
				if (0 === $parent)
				{
					$pgo[$grp_data["ord"]] = $grp_name;
				}
				else
				{
					$sgo[$parent][$grp_data["ord"]] = $grp_name;
				}
			}
		}

		// sort groups
		ksort($pgo);

		foreach ($sgo as $parent => $tmp)
		{
			ksort($sgo[$parent]);
		}

		// merge groups back together
		$grplist_tmp_sorted = array();

		foreach ($pg as $grp_name)
		{
			$grplist_tmp_sorted[$grp_name] = $this->cfg_groups[$grp_name];

			if (count($sg[$grp_name]))
			{
				foreach ($sg[$grp_name] as $grp_name2)
				{
					$grplist_tmp_sorted[$grp_name2] = $this->cfg_groups[$grp_name2];
				}
			}

			if (count($sgo[$grp_name]))
			{
				foreach ($sgo[$grp_name] as $grp_name2)
				{
					$grplist_tmp_sorted[$grp_name2] = $this->cfg_groups[$grp_name2];
				}
			}
		}

		foreach ($pgo as $grp_name)
		{
			$grplist_tmp_sorted[$grp_name] = $this->cfg_groups[$grp_name];

			if (count($sg[$grp_name]))
			{
				foreach ($sg[$grp_name] as $grp_name2)
				{
					$grplist_tmp_sorted[$grp_name2] = $this->cfg_groups[$grp_name2];
				}
			}

			if (count($sgo[$grp_name]))
			{
				foreach ($sgo[$grp_name] as $grp_name2)
				{
					$grplist_tmp_sorted[$grp_name2] = $this->cfg_groups[$grp_name2];
				}
			}
		}

		$this->cfg_groups = $grplist_tmp_sorted;
	}

	function _save_cfg_groups($o)
	{
		if (!empty($this->cfg_groups))
		{
			$this->_sort_groups();
			$o->set_meta("cfg_groups", $this->cfg_groups);
			$o->set_meta("cfg_groups_sorted", 1);
		}
	}

	function _save_cfg_props($o)
	{
		if (!empty($this->cfg_proplist))
		{
			$tmp = array();
			$cnt = 0;
			foreach($this->cfg_proplist as $key => $val)
			{
				if (empty($val["ord"]))
				{
					$cnt++;
					$val["tmp_ord"] = $cnt;
				}

				$tmp[$key] = $val;
			}

			uasort($tmp, array($this, "__sort_props_by_ord"));

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
				}

				$this->cfg_proplist[$key] = $val;
			}

			$o->set_meta("cfg_proplist",$this->cfg_proplist);
		}
	}

	function _save_cfg_layout($o)
	{
		$o->set_meta("cfg_layout", $this->cfg_layout);
	}

	function _init_layout_tbl($t)
	{
		$t->define_field(array(
			"name" => "ord",
			"sortable" => false,
			"caption" => t("Jrk."),
		));

		$t->define_field(array(
			"name" => "name",
			"sortable" => false,
			"caption" => t("Nimi"),
		));

		$t->define_field(array(
			"name" => "caption",
			"sortable" => false,
			"caption" => t("Pealkiri"),
		));

		$t->define_field(array(
			"name" => "type",
			"sortable" => false,
			"caption" => t("T&uuml;&uuml;p"),
		));

		$t->define_field(array(
			"name" => "options",
			"sortable" => false,
			"caption" => t("Valikud"),
		));

		$t->define_field(array(
			"name" => "selection",
			"sortable" => false,
		));
	}

	function _layout_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_layout_tbl($t);
		$this_o = $arr["obj_inst"];

		$t->define_data(array(
			// "ord" => ,
			// "name" => ,
			// "caption" => ,
			// "type" => ,
			// "options" => ,
			// "selection" =>
		));
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
				}
			}
		}

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
						}
					}
				}
			}
		}

		$c = "";
		$cnt = 0;
		foreach($by_group as $key => $proplist)
		{
			$grp_id = str_replace("_", "-", $key);
			$caption = $this->grplist[$key]["caption"]." ($key)";

			$this->vars(array(
				"grp_caption" => empty($this->grplist[$key]["parent"]) ? "<b>" . $caption . "</b>" : $caption,
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
				}
				switch ($prpdata["type"])
				{
					case "textarea":
						$this->vars(array(
							"richtext_caption" => t("RTE"),
							"richtext_checked" => checked($property["richtext"] == 1),
							"richtext" => $property["richtext"],
							"rows_caption" => t("Kõrgus"),
							"rows" => $property["rows"],
							"cols_caption" => t("Laius"),
							"cols" => $property["cols"],
							"prp_key" => $property["name"],
						));
						$property["cfgform_additional_options"] = $this->parse("textarea_options");
						$this->vars(array("textarea_options" => ""));
						break;

					case "textbox":
						$this->vars(array(
							"size_caption" => t("Laius"),
							"size" => $property["size"],
							"prp_key" => $property["name"],
						));
						$property["cfgform_additional_options"] = $this->parse("textbox_options");
						$this->vars(array("textbox_options" => ""));
						break;

					case "relpicker":
						$this->vars(array(
							"no_edit_caption" => t("Nuppudeta"),
							"no_edit_checked" => checked($property["no_edit"] == 1),
							"no_edit" => $property["no_edit"],
							"prp_key" => $property["name"],
						));
						$property["cfgform_additional_options"] = $this->parse("relpicker_options");
						$this->vars(array("relpicker_options" => ""));
						break;

					default:
						$property["cfgform_additional_options"] = "";
				}

				if (!empty($property["cfgform_additional_options"]))
				{
					$this->vars(array(
						"prp_options" => $property["cfgform_additional_options"] ,
						"prp_opts_caption" => t("Lisavalikud"),
						"tmp_id" => $cnt,
					));
					$options = $this->parse("options");
					$this->vars(array("options" => ""));
				}
				else
				{
					$options = "";
					$this->vars(array(
						"prp_options" => "",
						"prp_opts_caption" => "",
						"tmp_id" => ""
					));
				}

				$used_props[$property["name"]] = 1;
				$this->vars(array(
					"bgcolor" => $cnt % 2 ? "#EEEEEE" : "#FFFFFF",
					"prp_caption" => $property["caption"],
					"prp_type" => $prpdata["type"],
					"prp_key" => $prpdata["name"],
					"prp_order" => $property["ord"],
					"options" => $options,
					"grp_id" => $grp_id,
				));
				$sc .= $this->parse("property");
			}

			$this->vars(array(
				"property" => $sc,
				"grp_id" => $grp_id,
				"capt_prp_mark" => t("Inverteeri valik")
			));
			$c .= $this->parse("group");
		}

		$this->vars(array(
			"group" => $c,
			"capt_legend_tbl" => t("Tabi pealkiri (tabi_nimi)"),
			"capt_prp_order" => t("Jrk."),
			"capt_prp_key" => t("Nimi"),
			"capt_prp_caption" => t("Pealkiri"),
			"capt_prp_type" => t("Tüüp")
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
		// init table
		$t = &$arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "name",
			"sortable" => true,
			"caption" => t("Nimi"),
		));

		$t->define_field(array(
			"name" => "type",
			"sortable" => true,
			"caption" => t("Tüüp"),
		));

		$t->define_field(array(
			"name" => "caption",
			"sortable" => true,
			"caption" => t("Pealkiri"),
		));

		$t->define_field(array(
			"name" => "default_grp",
			"sortable" => true,
			"caption" => t("Vaikimisi tab"),
			"filter" => "automatic"
		));

		$t->define_field(array(
			"name" => "in_use",
			"sortable" => true,
			"caption" => t("K"),
			"tooltip" => t("Kasutusel"),
		));

		$t->define_chooser(array(
			"name" => "mark",
			"field" => "name",
		));

		if (empty($arr["request"]["sortby"]))
		{
			$t->set_sortable(false);
		}

		// get props in use
		$used_props = array();

		if (is_array($this->prplist))
		{
			foreach($this->prplist as $property)
			{
				if (!empty($property["group"]))
				{
					if (is_array($property["group"]))
					{
						$used_props[$property["name"]] = $property["group"];
					}
					else
					{
						$used_props[$property["name"]][] = $property["group"];
					}
				}
			}
		}

		foreach($this->all_props as $property)
		{
			if (count($used_props[$property["name"]]))
			{
				$groups = implode(", ", $used_props[$property["name"]]);
			}
			else
			{
				$groups = "";
			}

			$t->define_data(array(
				"caption" => $property["caption"],
				"type" => $property["type"],
				"name" => $property["name"],
				"default_grp" => $property["group"],
				"in_use" => $groups ? html::img(array(
					"url" => aw_ini_get("icons.server")."/check.gif",
					"alt" => $groups,
					"title" => $groups
				)) : ""
			));
		}
	}

	function gen_navtoolbar($arr)
	{
		// which links do I need on the toolbar?
		// 1- lisa tab
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

		$toolbar->add_cdata(t("<small>Liiguta omadused tabi:</small>"));
		$opts = array();

		if (is_array($this->grplist))
		{
			foreach($this->grplist as $key => $grpdata)
			{
				$opts[$key] = $grpdata["caption"] . " [" . $key . "]";
			}
		}
		else
		{
			$opts["none"] = t("Ühtegi tabi pole veel!");
		}

		$toolbar->add_cdata(html::select(array(
			"options" => $opts,
			"textsize" => "12px",
			"name" => "target_grp",
		)));

		$toolbar->add_button(array(
			"name" => "move",
			"tooltip" => t("Liiguta"),
			"url" => "javascript:document.changeform.subaction.value='move';submit_changeform();",
			"img" => "save.gif",
		));

		$toolbar->add_separator();

		$toolbar->add_cdata(t("<small>Lisa tab:</small>"));
		$toolbar->add_cdata(html::textbox(array(
			"name" => "newgrpname",
			"textsize" => "12px",
			"size" => "20",
		)));

		$toolbar->add_cdata(t("<small>Millise tabi alla:</small>"));
		$tabs = array();
		$tabs[""] = t("");
		if (is_array($this->grplist))
		{
			foreach($this->grplist as $key => $grpdata)
			{
				if (empty($grpdata["parent"]))
				{
					$tabs[$key] = $grpdata["caption"] . " [" . $key . "]";
				}
			}
		}

		$toolbar->add_cdata(html::select(array(
			"options" => $tabs,
			"textsize" => "12px",
			"name" => "target",
		)));

		$toolbar->add_button(array(
			"name" => "addgrp",
			"tooltip" => t("Lisa tab"),
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
				$opts[$key] = $grpdata["caption"] . " [" . $key . "]";
			}
		}
		else
		{
			$opts["none"] = t("&Uuml;htegi tabi pole veel!");
		}

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
							}

						}
						else
						{
							// add for the very first time
							$prplist[$pkey] = array(
								"name" => $pkey,
								"caption" => $this->all_props[$pkey]["caption"],
								"group" => array($target => $target),
							);
						}
					}
				}

				$this->cfg_proplist = $prplist;
			}
		}

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
						"parent"  => $arr["request"]["target"],
						"user_defined"  => 1,
					);
					$this->cfg_groups = $grplist;
				}
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
				$target = $arr["request"]["target_grp"];
				$prplist = $this->prplist;

				if (is_array($mark))
				{
					foreach($mark as $pkey => $val)
					{
						$prplist[$pkey]["group"] = $target;
					}

					$this->cfg_proplist = $prplist;
				}
				break;

			default:
				// well, save the names then
				//$grplist = $this->grplist;
				$prplist = $this->prplist;

				if (is_array($arr["request"]["prpnames"]))
				{
					foreach($arr["request"]["prpnames"] as $key => $val)
					{
						$prplist[$key]["caption"] = $val;
						$prplist[$key]["ord"] = $arr["request"]["prop_ord"][$key];
					}
				}
				if (is_array($arr["request"]["prpconfig"]))
				{
					foreach($arr["request"]["prpconfig"] as $key => $val)
					{
						foreach($val as $key2 => $val2)
						{
							if (true || $val2 != $arr["request"]["prpconfig"][$key][$key2])
							{
								$prplist[$key][$key2] = $val2;
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
		$order = array();
		$grps_array = array();
		foreach($grps->get() as $key => $item)
		{
			$grps_array[$key] = $item;
			$order[$key] = $item["ord"];
		}

		asort($order);
		foreach($order as $key => $val)
		{
			$grps_[$key] = $grps_array[$key];
		}

		$ctr_list = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_VIEWCONTROLLER")));

		foreach($grps_ as $key => $item)
		{
			$rv["grpcaption[$key]"] = array(
				"name" => "grpcaption[".$key."]",
				"type" => "textbox",
				"size" => 30,
				"caption" => t("Pealkiri"),
				"value" => $item["caption"],
				"parent" => "b".$key,
			);
			$rv["grpord[$key]"] = array(
				"name" => "grpord[".$key."]",
				"type" => "textbox",
				"size" => 2,
				"caption" => t("J&auml;rjekord"),
				"value" => $item["ord"],
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
			$rv["grpctl[$key]"] = array(
				"name" => "grpctl[$key]",
				"type" => "select",
				"caption" => t("Sisu kontroller"),
				"value" => $item["grpctl"],
				"parent" => "b$key",
				"options" => array("" => t("--vali--")) + $ctr_list->names()
			);
			$rv["grp_d_ctl[$key]"] = array(
				"name" => "grp_d_ctl[$key]",
				"type" => "select",
				"caption" => t("N&auml;itamise kontroller"),
				"value" => $item["grp_d_ctl"],
				"parent" => "b$key",
				"options" => array("" => t("--vali--")) + $ctr_list->names()
			);
			$rv["b".$key] = array(
				"name" => "b".$key,
				"type" => "layout",
				"rtype" => "hbox",
				"caption" => $key,
			);
		}

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
				$grplist[$key]["grpview"] = $arr["request"]["grpview"][$key];
				$grplist[$key]["grphide"] = (int) !$arr["request"]["grphide"][$key];
				$grplist[$key]["grpctl"] = $arr["request"]["grpctl"][$key];
				$grplist[$key]["grp_d_ctl"] = $arr["request"]["grp_d_ctl"][$key];
				$grplist[$key]["ord"] = $arr["request"]["grpord"][$key];

				$styl = $arr["request"]["grpstyle"][$key];
				if (!empty($styl))
				{
					$grplist[$key]["grpstyle"] = $styl;
				}
			}
		}

		$this->cfg_groups = $grplist;
	}

	/** returns array of properties defined in the config form given

		@attrib api=1 params=name

		@param id required type=oid
			the id of the config form object to read the properties from


		@errors
			error is thrown if the given config form object does not exist or the user has no view access to it

		@returns array of properties that are included in the config form,
			array contains all the property information for each property


		@examples

			$cf = get_instance(CL_CFGFORM);
			$props = $cf->get_props_for_cfgform(array(
				"id" => $_GET["cfgform"]
			));

			foreach($props as $pn => $pd)
			{
				echo "property name = $pd , caption = ".$pd["caption"]." <br>";
			}

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
				if($pd["richtext"])
				{
					$dat[$pn]["richtext"] = $pd["richtext"];
				}
			}
		}

		return $dat;
	}

	/** draws a config form from the given object type object

		@attrib api=1 params=name

		@param ot required type=oid
			object type object's id

		@param reforb required type=text
			the orb action (made by mk_reforb) to submit the form to

		@param errors optional type=array
			array returned from validate_data, containing errors from submit controllers/set_property

		@param values optional type=array
			array of property name => property value pairs that will be used when drawing the form

		@returns
			The html containing the form, including the <form tag. form is submitted to the reforb argument given

		@examples
			$cff = get_instance(CL_CFGFORM);
			$html = $cff->draw_cfgform_from_ot(array(
				"ot" => $object_type_id,
				"reforb" => $this->mk_reforb("handle_form_submit")
			));
			echo $html; // displays the form
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

		@param ot required type=oid
			object type object's id

		@param values optional type=array
			array of property name => property value pairs

		@param for_show optional type=bool
			if true, classificator values are resolved

		@param site_lang optional type=bool
			if true, translations are read from site language, not admin

		@returns
			array of properties from the config form selected in the object type.
			array key is property name, value is property data

		@examples
			$cff = get_instance(CL_CFGFORM);
			$props = $cff->get_props_from_ot(array("ot" => $object_type_id));
			foreach($props as $property_name => $property_data)
			{
				echo "prop = $property_name , caption = $property_data[caption] \n";
			}
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
				if (aw_ini_get("user_interface.full_content_trans"))
				{
					$lc = aw_global_get("ct_lang_lc");
				}
				else
				{
					$lc = aw_global_get("LC");
				}
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
						if ($pd["type"] == "text")
						{
							$els[$pn]["value"] = $tc[$pn];
						}
						else
						{
							$els[$pn]["caption"] = $tc[$pn];
						}
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
				"status" => array(200, t("Aktiivne")),
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

	/** Initializes given cfgform object to contain all groups and props from the given class
		@attrib api=1

		@param o required type=object
			cfgform object

		@param clid required type=int
			class id to init from

		@examples
			$cff = get_instance(CL_CFGFORM);
			$cf_obj = obj($config_form_id);
			$cff->cff_init_from_class($cf_obj, CL_MENU);
			$cff->cff_add_prop($cf_obj, "bujaka", array("caption" => t("Bujaka"), "group" => "general"));
			$cf_obj->save();
	**/
	// $save parameter for internal use
	function cff_init_from_class($o, $clid, $save = true)
	{
		// now that's the tricky part ... this thingsbum overrides
		// all the settings in the document config form
		$this->_init_properties($clid);
		$cfgu = get_instance("cfg/cfgutils");

		if ($clid == CL_DOCUMENT)
		{
			$def = join("",file(aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml"));
			list($proplist,$grplist, $layout) = $cfgu->parse_cfgform(array("xml_definition" => $def), true);
			$this->cfg_proplist = $proplist;
			$this->cfg_groups = $grplist;
			$this->cfg_layout = $layout;
		}
		else
		{
			$tmp = aw_ini_get("classes");

			$fname = $tmp[$clid]["file"];
			$def = join("",file(aw_ini_get("basedir") . "/xml/properties/class_base.xml"));
			list($proplist,$grplist, $layout) = $cfgu->parse_cfgform(array("xml_definition" => $def), true);
			$this->cfg_proplist = $proplist;
			$this->cfg_groups = $grplist;
			$this->cfg_layout = $layout;

			$fname = basename($fname);
			$def = join("",file(aw_ini_get("basedir") . "/xml/properties/$fname.xml"));
			list($proplist,$grplist, $layout) = $cfgu->parse_cfgform(array("xml_definition" => $def), true);
			// nono. It needs to fucking merge those things with classbase
			$this->cfg_proplist = $this->cfg_proplist + $proplist;
			$this->cfg_groups = $this->cfg_groups + $grplist;
			$this->cfg_layout = $this->cfg_layout + $layout;
		}

		if ($save)
		{
			$o->set_prop("subclass", $clid);
			$this->_save_cfg_groups($o);
			$this->_save_cfg_props($o);
			$this->_save_cfg_layout($o);
		}
	}

	/** Removes all properties from the given config form
		@attrib api=1

		@param o required type=object
			cfgform object

		@examples
			$cff = get_instance(CL_CFGFORM);
			$cf_obj = obj($config_form_id);
			$cff->cff_remove_all_props();
			$cff->cff_add_prop($cf_obj, "bujaka", array("caption" => t("Bujaka"), "group" => "general"));
			$cf_obj->save();
	**/
	function cff_remove_all_props($o)
	{
		$o->set_meta("cfg_proplist", array());
	}

	/** Adds the given property to the given config form object
		@attrib api=1

		@param o required type=object
			cfgform object

		@param pn required type=string
			name of property to add

		@param pd required type=array
			array(caption, group, ord) for the property

		@examples
			$cff = get_instance(CL_CFGFORM);
			$cf_obj = obj($config_form_id);
			$cff->cff_init_from_class($cf_obj, CL_MENU);
			$cff->cff_add_prop($cf_obj, "bujaka", array("caption" => t("Bujaka"), "group" => "general"));
			$cf_obj->save();
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
		$this->_save_cfg_props($o);
	}

	/** Returns the properties for the config form
		@attrib api=1

		@param id required type=oid
			The oid if the config form to load the props from

		@returns
			array of property info for the config form

		@examples
			$cff = get_instance(CL_CFGFORM);
			foreach($cff->get_cfg_proplist($cfgform_oid) as $pn => $pd)
			{
				echo "prop = $pn , caption = $pd[caption] \n";
			}
	**/
	function get_cfg_proplist($id)
	{
		$o = obj($id);
		$show_to_groups = $o->meta("show_to_groups");

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
					if ($pd["type"] == "text")
					{
						$ret[$pn]["value"] = $tc[$pn];
					}
					else
					{
						$ret[$pn]["caption"] = $tc[$pn];
					}
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
					//$ret[$pn]["caption"] = $pd["caption"];
				}
			}
		}

		// also eval all controllers
		foreach((array)$o->prop("cfg_groups") as $grpn => $grpdat)
		{
			if ($this->can("view", $grpdat["grpctl"]))
			{
				$ctl = obj($grpdat["grpctl"]);
				$ctl_i = $ctl->instance();
				$ps = $ctl_i->check_property($grpdat, $ctl->id(), $_GET, $grpdat);
				foreach(safe_array($ps) as $pn => $pd)
				{
					$pd["group"] = $grpn;
					$pd["force_display"] = 1;
					$ret[$pn] = $pd;
				}
			}
		}

		//see värk siis kontrollib, kas miskile kasutajale on mingi omadus äkki maha keeratud
		$user_group_list = aw_global_get("gidlist_oid");
		foreach($ret as $key=>$val)
		{
			if($show_to_groups[$key])
			{
				$allowed_to_see = 0;
				foreach($user_group_list as $user_group)
				{
					if($show_to_groups[$key][$user_group])
					{
						$allowed_to_see = 1;
						break;
					}
				}
				if(!$allowed_to_see) $ret[$key] = null;
			}
		}
		return $ret;
	}

/* replaced by self::_sort_groups()
	function __grp_s($a, $b)
	{
		if ($a["ord"] == $b["ord"])
		{
			return 0;
		}
		return $a["ord"] > $b["ord"];
	}
 */

	/** Returns the groups defined in the config form
		@attrib api=1

		@param id required type=oid
			The oid of the config form to load

		@returns
			array of group data for the config form

		@examples
			$cff = get_instance(CL_CFGFORM);
			foreach($cff->get_cfg_groups($cfgform_oid) as $group_name => $group_data)
			{
				echo "prop = $group_name , caption = $group_data[caption] \n";
			}
	**/
	function get_cfg_groups($id)
	{
		$o = obj($id);
		$this->cfg_groups = $o->meta("cfg_groups");
		$this->cfgview_grps = safe_array($o->prop("cfgview_grps"));

		// backward compatibility.
		if (!$o->meta("cfg_groups_sorted"))
		{
			$this->_sort_groups();
			$o->set_meta("cfg_groups", $this->cfg_groups);
			$o->set_meta("cfg_groups_sorted", 1);
			aw_disable_acl();
			$o->save();
			aw_restore_acl();
		}
		//

		$ret = $this->cfg_groups;

/* sorting is now done before saving meta.cfg_groups
		$has = false;
		foreach(safe_array($ret) as $k => $v)
		{
			if (isset($v["ord"]) && !empty($v["ord"]))
			{
				$has = true;
			}
		}

		if ($has)
		{
			uasort($ret, array(&$this, "__grp_s"));
		}
 */

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

		$si = __get_site_instance();
		$has_cb = method_exists($si, "callback_get_group_display");
		foreach($ret as $gn => $gd)
		{
			if ($this->can("view", $gd["grp_d_ctl"]))
			{
				$ctl = obj($gd["grp_d_ctl"]);
				$ctli = $ctl->instance();
				$rv = $ctli->check_property($ret[$gn], $ctl->id(), $gd);
				if ($rv == PROP_IGNORE)
				{
					unset($ret[$gn]);
				}
			}
			elseif ($has_cb)
			{
				if ($si->callback_get_group_display($o, $gn) == PROP_IGNORE)
				{
					unset($ret[$gn]);
				}
			}
			elseif ("cfg_embed" === $_GET["awcb_display_mode"] and !in_array($gn, $this->cfgview_grps))
			{
				unset($ret[$gn]);
			}
		}

		if ("cfg_embed" === $_GET["awcb_display_mode"])
		{ // set first available grp as default for cfgview group selection
			reset($ret);
			$gn = key($ret);
			$ret[$gn]["default"] = true;
		}

		foreach(safe_array($o->meta("buttons")) as $gn => $bts)
		{
			if (isset($ret[$gn]))
			{
				$ret[$gn]["back_button"] = $bts["back"];
				$ret[$gn]["forward_button"] = $bts["next"];
			}
		}
		return $ret;
	}

	/** Returns the site-wide default config form for the given class
		@attrib api=1 params=name

		@param clid required type=int
			The class id to return the default form for

		@returns
			The oid of the system default config form for the class or false if no form exists

		@examples
			$cf = get_instance(CL_CFGFORM);
			if (($form_oid = $cf->get_sysdefault(array("clid" => CL_MENU))) !== false)
			{
				echo "default cfgorm for CL_MENU is ".$form_oid."<br>";
			}
	**/
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


/** Deletes user defined group.
	@attrib name=delete_userdfn_grp
	@param id required type=int acl=view
	@param name required
	@param return_url optional
**/
	function delete_userdfn_grp($arr)
	{
		$this_o = obj ($arr["id"]);
		$name = $arr["name"];
		$this->cfg_groups = $this_o->meta("cfg_groups");

		if (!empty($this->cfg_groups[$name]["user_defined"]))
		{
			$this->cfg_proplist = $this_o->meta("cfg_proplist");
			$deleted_groups = array($name);

			// delete group
			unset($this->cfg_groups[$name]);

			// delete children if any
			foreach($this->cfg_groups as $key => $data)
			{
				if ($name === $data["parent"])
				{
					$deleted_groups[] = $key;
					unset($this->cfg_groups[$key]);
				}
			}

			// remove properties from deleted groups
			foreach ($this->cfg_proplist as $key => $data)
			{
				if (in_array($data["group"], $deleted_groups))
				{
					unset($this->cfg_proplist[$key]);
				}
			}

			// save
			$this->_save_cfg_groups($this_o);
			$this->_save_cfg_props($this_o);
			$this->_save_cfg_layout($this_o);

			$this_o->save();
		}

		return aw_url_change_var("just_saved", "1", $arr["return_url"]);
	}

	function _edit_groups_tb($arr)
	{
		$this_o = $arr["obj_inst"];
		$toolbar =& $arr["prop"]["vcl_inst"];

		// add groups
		$toolbar->add_button(array(
			"name" => "add",
			"url" => aw_url_change_var("cfgform_add_grp", "1"),
			"img" => "new.gif",
			"tooltip" => t("Lisa tab"),
		));

		// save
		$toolbar->add_button(array(
			"name" => "save",
			"url" => "javascript:submit_changeform()",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
		));

		// delete for user defined groups
		$user_dfn_grps = array();
		$delete_url = $this->mk_my_orb("delete_userdfn_grp", array(
			"id" => $this_o->id (),
			"name" => "cfgform_delete_grp_name",
			"return_url" => get_ru(),
		));

		foreach ($this->grplist as $name => $data)
		{
			if (!empty($data["user_defined"]))
			{
				$user_dfn_grps[$name] = $data;
			}
		}

		if (count($user_dfn_grps))
		{
			$toolbar->add_menu_button(array(
				"name" => "delete",
				"img" => "delete.gif",
				"tooltip" => t("Kustuta kasutajaloodud tab"),
			));

			foreach ($user_dfn_grps as $name => $data)
			{
				$toolbar->add_menu_item(array(
					"parent" => "delete",
					"text" => t("Kustuta ") . $data["caption"] . " ($name)",
					"link" => str_replace("cfgform_delete_grp_name", $name, $delete_url),
				));
			}
		}
	}

	function _init_edit_groups_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "grp",
			"caption" => t("Tab"),
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Pealkiri"),
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("Jrk."),
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "style",
			"caption" => t("Stiil"),
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "ctrl",
			"caption" => t("Sisu kontroller"),
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "view_ctrl",
			"caption" => t("Näitamise kontroller"),
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "opt_view",
			"caption" => t("V"),
			"tooltip" => t("Vaikimisi view vaade"),
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "opt_show",
			"caption" => t("N"),
			"tooltip" => t("Näita tabi"),
			"chgbgcolor" => "bg_colour",
		));
	}

	function _edit_groups_tbl($arr)
	{
		$this_o = $arr["obj_inst"];
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_edit_groups_tbl($t);
		$grps = new aw_array($this_o->meta("cfg_groups"));

		$tps = array(
			"" => t("vaikestiil"),
			"stacked" => t("pealkiri yleval, sisu all"),
		);
		$ctr_list = new object_list($this_o->connections_from(array("type" => "RELTYPE_VIEWCONTROLLER")));

		foreach($grps->get() as $gn => $gd)
		{
			$bg_colour = empty($gd["parent"]) ? "silver" : false;
			$t->define_data(array(
				"grp" => $gn . " <small>(" . $gd["caption"] . ")</small>",
				"caption" => html::textbox(array(
					"name" => "grpcaption[".$gn."]",
					"size" => 25,
					"value" => $gd["caption"],
				)),
				"ord" => html::textbox(array(
					"name" => "grpord[".$gn."]",
					"size" => 2,
					"value" => $gd["ord"],
				)),
				"style" => html::select(array(
					"name" => "grpstyle[$gn]",
					"options" => $tps,
					"selected" => $gd["grpstyle"],
				)),
				"ctrl" => html::select(array(
					"name" => "grpctl[$gn]",
					"value" => $gd["grpctl"],
					"options" => array("" => t("--vali--")) + $ctr_list->names(),
				)),
				"view_ctrl" => html::select(array(
					"name" => "grp_d_ctl[$gn]",
					"value" => $gd["grp_d_ctl"],
					"options" => array("" => t("--vali--")) + $ctr_list->names()
				)),
				"opt_view" => html::checkbox(array(
					"name" => "grpview[$gn]",
					"value" => 1,
					"checked" => $gd["grpview"],
				)),
				"opt_show" => html::checkbox(array(
					"name" => "grphide[$gn]",
					"value" => 1,
					"checked" => (int) !$gd["grphide"],
				)),
				"bg_colour" => $bg_colour,
			));
		}

		$t->set_sortable(false);
	}

	function _init_group_movement_t(&$t)
	{
		$t->define_field(array(
			"name" => "grp",
			"caption" => t("Tab"),
			"align" => "left",
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "back_button",
			"caption" => t("Tagasi nupp"),
			"align" => "center",
			"chgbgcolor" => "bg_colour",
		));
		$t->define_field(array(
			"name" => "next_button",
			"caption" => t("Edasi nupp"),
			"align" => "center",
			"chgbgcolor" => "bg_colour",
		));
	}

	function _group_movement($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_group_movement_t($t);

		// list groups and let the user select groups that you go forward/back
		$grps = new aw_array($arr["obj_inst"]->meta("cfg_groups"));

		$sel = array("" => t("Ei ole nuppu"));
		foreach($grps->get() as $gn => $gd)
		{
			$sel[$gn] = $gd["caption"];
		}

		$buttons = $arr["obj_inst"]->meta("buttons");
		foreach($grps->get() as $gn => $gd)
		{
			$bg_colour = empty($gd["parent"]) ? "silver" : false;
			$t->define_data(array(
				"grp" => ($gd["parent"] != "" ? "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" : "").$gd["caption"],
				"back_button" => html::select(array(
					"name" => "bts[$gn][back]",
					"options" => $sel,
					"value" => $buttons[$gn]["back"]
				)),
				"next_button" => html::select(array(
					"name" => "bts[$gn][next]",
					"options" => $sel,
					"value" => $buttons[$gn]["next"]
				)),
				"bg_colour" => $bg_colour,
			));
		}
		$t->set_sortable(false);
	}

	function parse_alias($args)
	{
		return $this->get_class_cfgview(array(
			"id" => $args["alias"]["target"],
			"display_mode" => "cfg_embed",
		));
	}

	// embed cfgform as alias and display configured class interface
	function get_class_cfgview($args)
	{
		enter_function("cfg_form::get_class_cfgview");

		// request vars
		if (empty($this->cfgview_vars))
		{
			$this->cfgview_vars = (array) $_GET + (array) $_POST + (array) $AW_GET_VARS;
		}

		// get view
		$content = "";
		$main_view = (empty($this->cfgview_vars["class"]) or $class === $this->cfgview_vars["class"]);

		if ($main_view)
		{
			$this_o = obj($args["id"]);
			$classes = aw_ini_get("classes");
			$class = strtolower(substr($classes[$this_o->prop("subclass")]["def"], 3));
			$this->_get_cfgview_params($this_o);
			$action = $this->cfgview_vars["action"];

			if ("new" !== $action and !is_oid($this->cfgview_vars["id"]))
			{
				return "";
			}

			if ("new" === $action and empty($this->cfgview_vars["parent"]))
			{
				$this->cfgview_vars["parent"] = $this_o->id();
			}

			if ("new" === $action && !$this->can("add", $this->cfgview_vars["parent"]))
			{
				return "";
			}

			$this->cfgview_vars["cfgform"] = $args["id"];
			$_GET["awcb_cfgform"] = $args["id"];// sest $vars-i ei kasutata tegelikult orbis miskip2rast
			$_GET["awcb_display_mode"] = $args["display_mode"];// sest $this->cfgview_vars-i ei kasutata tegelikult orbis miskip2rast

			// make request
			classload("core/orb/orb");
			$orb = new orb();
			$orb->process_request(array(
				"class" => $class,
				"action" => $action,
				"reforb" => $this->cfgview_vars["reforb"],
				"user"	=> 1,//!!! whats that for?
				"vars" => $this->cfgview_vars,
				"silent" => false,
			));
			$content = $orb->get_data();
		}

		exit_function("cfg_form::get_class_cfgview");
		return $content;
	}

	function _get_cfgview_params($this_o)
	{
		$action = array_key_exists($this_o->prop("cfgview_action"), $this->cfgview_actions) ? $this_o->prop("cfgview_action") : "view";

		if ("cfgview_change_new" === $action or "cfgview_view_new" === $action)
		{
			$this->_load_cfgview_params($this_o, "new");

			if (!is_oid($this->cfgview_vars["id"]))
			{
				$action = "new";
			}
			elseif ("cfgview_change_new" === $action)
			{
				$action = "change";
				$this->_load_cfgview_params($this_o, $action);
			}
			elseif ("cfgview_view_new" === $action)
			{
				$action = "view";
				$this->_load_cfgview_params($this_o, $action);
			}
		}
		else
		{
			$this->_load_cfgview_params($this_o, $action);
		}

		$this->cfgview_vars["action"] = $action;
	}

	function _load_cfgview_params($this_o, $action)
	{
		$prop_name_indic = "cfgview_params_" . $action . "_loaded";

		if (!$this->$prop_name_indic)
		{
			$params = explode("&", $this_o->prop("cfgview_" . $action . "_params"));

			foreach ($params as $param)
			{
				$param = explode("=", $param, 2);
				$this->cfgview_vars[$param[0]] = $param[1];
				$_GET[$param[0]] = $param[1];// sest $this->cfgview_vars-i ei kasutata tegelikult orbis miskip2rast
			}

			$this->$prop_name_indic = true;
		}
	}

	/// submenus from object interface methods
	function make_menu_item($this_o, $level, $parent_o, $site_show_i)
	{
		if (empty($this->awcb_request_vars))
		{
			$this->awcb_request_vars = (array) $_GET + (array) $_POST + (array) $AW_GET_VARS;
		}

		if (empty($this->awcb_request_vars["class"]))
		{
			// init
			if (!isset($this->grplist))
			{
				$this->_init_cfgform_data($this_o);
				$this->cfgview_grps = safe_array($this_o->prop("cfgview_grps"));
				$this->_get_cfgview_params($this_o);
			}

			// no groups for new object form
			if (!is_oid($this->awcb_request_vars["id"]))
			{
				return false;
			}

			// get next group
			do
			{
				if (!isset($this->make_menu_item_counter))
				{
					$this->make_menu_item_counter = 0;
					$grp_name = current($this->cfgview_grps);
				}
				else
				{
					$grp_name = next($this->cfgview_grps);
				}
			}
			while (!empty($this->grplist[$grp_name]["grphide"]));

			++$this->make_menu_item_counter;

			// selected grp
			if ($this->awcb_request_vars["group"] == $grp_name)
			{
			}

			//
			if (false === $grp_name)
			{
				$this->make_menu_item_counter = NULL;
				return false;
			}
			else
			{
				$vars = array (
					"just_saved" => NULL,
					"group" => $grp_name
				);
				$link = aw_url_change_var($vars);

				return array(
					"text" => $this->grplist[$grp_name]["caption"],
					"link" => $link,
					// "section" => $o_91_2->id(),
					// "menu_edit" => $this->__helper_menu_edit($o_91_2),
					// "parent_section" => is_object($o_91_1) ? $o_91_1->id() : $o_91_2->parent(),
					// "comment" => "komment",
				);
			}
		}
		else
		{
			$this->make_menu_item_counter = null;
			return false;
		}
	}


	function _get_orb_settings($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "action",
			"caption" => "action",
			"align" => "center"
		));

		$o = $arr["obj_inst"];
		$groups_list = new object_list($o->connections_from(array("type" => "RELTYPE_VIEW_DFN_GRP")));

		if (!$groups_list->count())
		{
			$groups_list = new object_list(array(
				"class_id" => array(CL_GROUP),
				"type" => new obj_predicate_compare(OBJ_COMP_LESS, 1),
				"lang_id" => "%",
			));
		}

		foreach($groups_list->arr() as  $group_obj)
		{
			$t->define_field(array(
				"name" => $group_obj->id(),
				"caption" => html::href(array(
					"caption" => $group_obj->name(),
					"url" => "#",
					"onClick" => "aw_sel_chb(document.changeform,\"[".$group_obj->id()."]\");"
				))
			));
		}

		$orb = get_instance("core/orb/orb");
		$clss = aw_ini_get("classes");
		$methods = $orb->get_class_actions(array(
			"class" => basename($clss[$arr["obj_inst"]->subclass()]["file"])
		));
		$dd = $o->meta("orb_acl");
		foreach($methods as $method)
		{
			$d = array(
				"action" => $method
			);
			foreach($groups_list->arr() as  $group_obj)
			{
				$d[$group_obj->id()] = html::checkbox(array(
					"name" => "d[$method][".$group_obj->id()."]",
					"value" => 1,
					"checked" => $dd[$method][$group_obj->id()] == 1
				));
			}

			$t->define_data($d);
		}
		$t->set_caption(t("Vali, millised kasutajagrupid ei tohi milliseid actione kasutada."));
	}

	function _set_orb_settings($arr)
	{
		$arr["obj_inst"]->set_meta("orb_acl", $arr["request"]["d"]);
	}

	/** Checks if the current user has access to the given method in the given cfgform
		@attrib api=1 params=name
		@param action required type=string
		@param cfgform required type=int
	**/
	function check_user_orb_access($arr)
	{
		$cf = obj($arr["cfgform"]);
		$orb_data = $cf->meta("orb_acl");

		$gl = aw_global_get("gidlist_pri_oid");
                asort($gl);
                $gl = array_keys($gl);
                $grp = $gl[1];
                if (count($gl) == 1)
                {
                        $grp = $gl[0];
                }

		if ($orb_data[$arr["action"]][$grp] == 1)
		{
			return false;
		}
		return true;
	}
}

?>
