<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/releditor.aw,v 1.107 2008/03/20 14:29:39 markop Exp $
/*
	Displays a form for editing one connection
	or alternatively provides an interface to edit
	multiple connections
@classinfo  maintainer=kristo
*/

class releditor extends core
{
	var $auto_fields;
	function releditor()
	{
		$this->init();
	}

	function init_new_rel_table($arr)
	{
		classload("vcl/table");
		$awt = new vcl_table(array(
			"layout" => "generic",
		));
		$awt->define_chooser(array(
			"field" => "conn_id",
			"name" => "check",
		));
		
		if(!is_object($arr["obj_inst"]))
		{
			$arr["obj_inst"] = obj($arr["request"]["id"]);
		} 

		$htmlclient = get_instance("cfg/htmlclient");

		$parent_inst = get_instance($arr["obj_inst"]->class_id());
		$parent_property_list = $arr["obj_inst"]->get_property_list();
		$arr["prop"] = $parent_property_list[$arr["prop"]["name"]];
		$tb_fields = $arr["prop"]["table_fields"];

		if(!$arr["new"] && is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
		{
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => $arr["prop"]["reltype"],
			));
			$name = $arr["prop"]["name"];
			$return_url = get_ru();
			foreach($conns as $conn)
			{
				$c_to = $conn->prop("to");
				$target = $conn->to();
				$clinst = $target->instance();
				$rowdata = array(
					"id" => $c_to,
					"parent" => $target->parent(),
					"conn_id" => $conn->id(),
					"name" => $conn->prop("to.name"),
					"edit" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $url,
					)),
					"_sort_jrk" => $conn->prop("to.jrk"),
					"_sort_name" => $conn->prop("to.name"),
					"_active" => ($arr["request"][$this->elname] == $c_to),
					"delete" => html::submit(array(
						"name" => "del_button",
						"value" => t("Kustuta"),
						"onclick" => "releditor_delete_".$c_to,
						)).html::submit(array(
						"name" => "del_button",
						"value" => t("Muuda"),
						"onclick" => "releditor_change_".$c_to,
						)),
				);

				$property_list = $target->get_property_list();
				$export_props = array();

				foreach($property_list as $_pn => $_pd)
				{
					if (!in_array($_pn,$tb_fields))
					{
						continue;
					};

					if(!$fields_defined)
					{
						$awt->define_field(array(
							"name" => $_pn,
							"caption" => $property_list[$_pn]["caption"],
						));
					}

					/*
					if (empty($fdata[$_pn]))
					{
						continue;
					};
					*/
					$prop = $_pd;
					$prop["value"] = $target->prop($_pn);
					// now lets call get_property on that beast
					if(method_exists($clinst, "get_property"))
					{
						$test = $clinst->get_property(array(
							"prop" => &$prop,
							"obj_inst" => $target,
							"called_from" => "releditor"
						));
						if (PROP_OK != $test)
						{
							continue;
						};
					}
					if ($_pd["type"] == "chooser" && is_array($prop["options"]))
					{
						$prop["value"] = $prop["options"][$prop["value"]];
					}
					if ($_pd["type"] == "date_select")
					{
						$prop["value"] = date("d.m.Y", $prop["value"]);
					}
					else
					if ($_pd["type"] == "datetime_select")
					{
						$prop["value"] = date("d.m.Y", $prop["value"]);
					}
					if (($_pd["type"] == "relpicker" || $_pd["type"] == "classificator") && $this->can("view", $prop["value"]))
					{
						$_tmp = obj($prop["value"]);
						$prop["value"] = $_tmp->name();
					}
					else
					if ($_pd["type"] == "select" && is_array($prop["options"]))
					{
						$prop["value"] = $prop["options"][$prop["value"]];
					};
					if($arr["prop"]["filt_edit_fields"] == 1)
					{
						if($prop["value"] != "" && $prop["type"] == "textbox")
						{
							$ed_fields[$_pn] = $_pn;
						}
					}

					$get_prop_arr = $arr;
					$get_prop_arr["called_from"] = "releditor_table";
					$get_prop_arr["prop"] = $prop;
					$get_prop_arr["prop"]["name"] = str_replace("[0]" , "" , $this->elname)."[".$get_prop_arr["prop"]["name"]."]";
					$parent_inst->get_property($get_prop_arr);
					$prop = $get_prop_arr["prop"];
/*
					$hidden_input = $this->all_props[$_pn];//arr($hidden_input);//arr($prop["name"]); //arr($this->all_props);
					$hidden_input["value"] = $target->prop($_pn);
					$get_prop_arr = $arr;
					$get_prop_arr["prop"] = $hidden_input;
					$get_prop_arr["prop"]["name"] = str_replace("[0]" , "" , $get_prop_arr["prop"]["name"]);
					$parent_inst->get_property($get_prop_arr);
					$get_prop_arr["prop"]["name"] = $prop["name"];
					$hidden_input = $get_prop_arr["prop"];
*/
					$export_props[$_pn] = $prop["value"];//.$htmlclient->draw_element($hidden_input);
				}
				$fields_defined = 1;
				$rowdata = $export_props + $rowdata;
				// This one defines the display table data. Just a reminder for myself. - Kaarel
				$awt->define_data($rowdata);
			}
		}
		$awt->define_field(array(
			"name" => "delete",
			"caption" => "",
		));
		$awt->set_sortable(true);
		$awt->set_default_sortby(array("_sort_jrk"=>"_sort_jrk", "_sort_name"=>"_sort_name"));
		$awt->sort_by();
		$awt->set_sortable(false);

		return '<div id="releditor_'.str_replace("[0]" , "" , $this->elname).'_table_wrapper">'.$awt->draw()."</div>";
	}


	function init_new_manager($arr)
	{
		//arr($arr);
		enter_function("init-rel-editor-new");
		$prop = $arr["prop"];
		$this->elname = $prop["name"];
		$obj = $arr["obj_inst"];
		$obj_inst = $obj;
		$clid = $arr["prop"]["clid"][0];
		if (empty($clid) && is_object($arr["obj_inst"]))
		{
			$relinfo = $arr["obj_inst"]->get_relinfo();
			$clid = $relinfo[$prop["reltype"]]["clid"][0];

		}

		$props = $arr["prop"]["props"];
		if (!is_array($props) && !empty($props))
		{
			$props = array($props);
		};

		$xprops = array();


		if ($clid == 7)
		{
			$use_clid = "doc";
		}
		else
		{
			$use_clid = $clid;
		};
		$t = get_instance($use_clid);

		$parent_inst = get_instance($obj_inst->class_id());

		$t->init_class_base();
		$emb_group = "general";

		$all_props = array();

		// generate a list of all properties. Needed to display edit form
		// and to customize table display in manager mode
		//$all_props = $t->get_property_group($filter);
		$all_props = $t->load_defaults();
//		$this->clid = $use_clid;
		$act_props = array();
		$use_form = $prop["use_form"];


		if (!empty($use_form))
		{
			foreach($all_props as $key => $_prop)
			{
				if (is_array($_prop["form"]) && in_array($use_form,$_prop["form"]))
				{
					$props[$key] = $key;
				};
			};
		};

		$form_type = $arr["request"][$this->elname];
		if (($arr["prop"]["always_show_add"] == 1 && !is_oid($edit_id)))
		{
			$form_type = "new";
		}
		$this->form_type = $form_type;

		#$this->all_props = $act_props;
		$pcount = sizeof($props);

		foreach($all_props as $key => $_prop)
		{
			if ($all_props[$key] && is_array($props) && in_array($key,$props))
			{
				if (!empty($form_type) || $visual != "manager")
				{
					if (1 == $pcount and "manager" != $visual)
					{
						$_prop["caption"] = $prop["caption"];
					};
					//saadab asja get_property'sse
					$act_props[$key] = $_prop;
				};
			};
			$this->all_props[$key] = $_prop;
		};

		$cfgform_id = $arr["prop"]["cfgform_id"];
		if(is_oid($cfgform_id) && $this->can("view", $cfgform_id))
		{
			$cfg = get_instance(CL_CFGFORM);
			$this->cfg_act_props = $cfg->get_props_from_cfgform(array("id" => $cfgform_id));
			//$act_props = $act_props + $this->cfg_act_props;
		}

		if (!empty($prop["choose_default"]))
		{
			$this->choose_default = 1;
		};

		$obj_inst = false;
		if ($form_type != "new" && is_object($arr["obj_inst"]) &&  is_oid($arr["obj_inst"]->id()))
		{
			if ($edit_id)
			{
				$obj_inst = new object($edit_id);
			}
			else if (!empty($prop["rel_id"]) || $prop["rel_id"] == "first")
			{
				$o = $arr["obj_inst"];
				if (is_object($o) && is_oid($o->id()))
				{
					$conns = $o->connections_from(array(
						"type" => $prop["reltype"],
					));
					// take the first
					if ($prop["rel_id"] == "first")
					{
						$key = reset($conns);
						if ($key)
						{
							$obj_inst = $key->to();
						};
					}
					else
					if ($conns[$prop["rel_id"]])
					{
						$obj_inst = $conns[$prop["rel_id"]]->to();
					};
				};
			};
		};

		if (is_object($obj_inst) && empty($arr["view"]))
		{
			$act_props["id"] = array(
				"type" => "hidden",
				"name" => "id",
				"value" => $obj_inst->id(),
			);

		};

		if (!$obj_inst)
		{
			$obj_inst = new object();
		};

		// so that the object can access the source object
		if (is_object($arr["obj_inst"]))
		{
			aw_global_set("from_obj",$arr["obj_inst"]->id());
		};

		// maybe I can use the property name itself
		if ($arr["cb_values"])
		{
			$t->cb_values = $arr["cb_values"];
		};

		$this->elname = $this->elname."[0]";
		$xprops = $t->parse_properties(array(
			"properties" => $act_props,
			"name_prefix" => $this->elname,
			"obj_inst" => $obj_inst,
		));

		exit_function("init-rel-editor-new");

		$tb = get_instance("vcl/toolbar");
//		$tb->add_button(array(
//			"name" => "new",
//			"tooltip" => t("Lisa uus")." " . $prop["caption"],
//			"caption" => t("Lisa uus")." " . $prop["caption"],
//			"url" => $this->mk_my_orb("add_row", array("id" => $arr["obj_inst"]->id(), "retu" => get_ru()))
//		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"url" => "javascript:crap();",
		));


		$xprops[$prop["name"]."[0]break"] = array(
			"type" => "text",
			"value" => '<br>',
			"store" => "no",
			"name" => $this->elname."_break",
			"caption" => $this->elname."_break",
			"no_caption" => 1,
		);

		if(is_array($arr["prop"]["clid"]))$arr["prop"]["clid"] = reset($arr["prop"]["clid"]);
		$xprops[$prop["name"]."[0]add_button"] = array(
			"type" => "text",
			"value" => '
			<input type="submit" value="Lisa" name="'.$prop["name"].'" id="button" onchange="null;set_changed();"/>
			<script>
				$.aw_releditor({
					"releditor_name" : "'.$prop["name"].'",
					"id" : "'.$arr["obj_inst"]->id().'",
					"reltype" : "'.$arr["prop"]["reltype"].'",
					"clid" : "'.$arr["prop"]["clid"].'",
					"del_url" : "'.aw_ini_get("baseurl").'/?class=releditor&action=delo&id=",
					});
			</script></input><br>',
			"store" => "no",
			"name" => $this->elname."_add_button",
			"caption" => $this->elname."_add_button",
			"no_caption" => 1,
 		);

		$xprops[$prop["name"]."[0]toolbar"] = array(
			"type" => "text",
			"value" => $tb->get_toolbar(),
			"store" => "no",
			"name" => $this->elname."_toolbar",
			"caption" => $this->elname."_toolbar",
			"no_caption" => 1,
 		);

		$xprops[$prop["name"]."[0]table"] = array(
			"type" => "text",
			"value" => $this->init_new_rel_table($arr),
			"store" => "no",
			"name" => $this->elname."_table",
			"caption" => $this->elname."_table",
			"no_caption" => 1,
 		);

//		arr($xprops);
		foreach($xprops as $key => $prop)
		{
			$get_prop_arr = $arr;
			$get_prop_arr["prop"] = $prop;
			$get_prop_arr["prop"]["name"] = str_replace("[0]" , "" , $get_prop_arr["prop"]["name"]);
			$parent_inst->get_property($get_prop_arr);
			$get_prop_arr["prop"]["name"] = $prop["name"];
			$xprops[$key] = $get_prop_arr["prop"];
		}
//		arr($xprops);

		return $xprops;

	}

	function init_rel_editor($arr)
	{
		if($arr["prop"]["mode"] == "manager2")
		{
			return $this->init_new_manager($arr);
		}
		enter_function("init-rel-editor");
		$prop = $arr["prop"];
		$this->elname = $prop["name"];
		$obj = $arr["obj_inst"];
		$obj_inst = $obj;
		$clid = $arr["prop"]["clid"][0];
		if (empty($clid) && is_object($arr["obj_inst"]))
		{
			$relinfo = $arr["obj_inst"]->get_relinfo();
			$clid = $relinfo[$prop["reltype"]]["clid"][0];

		}

		$props = $arr["prop"]["props"];
		if (!is_array($props) && !empty($props))
		{
			$props = array($props);
		};

		$xprops = array();

		$errors = false;


		// Automatic fields for manager
		$this->auto_fields = array(
			'class_id' => t("Klassi ID"),
			'class_name' => t("Klass"),
		);


		// manager is a kind of small aliasmgr, it has a table, rows can be clicked
		// 	to edit items, new items can be added, existing ones can be deleted

		// form is a single form, which can be used to edit a single connection. It
		// is also the default
		$visual = isset($prop["mode"]) && $prop["mode"] == "manager" ? "manager" : "form";


		if (!is_array($props) && empty($prop["use_form"]))
		{
			$errors = true;
			$xprops[] = array(
				"type" => "text",
				"caption" => t(" "),
				"error" => sprintf(t("Viga %s definitsioonis (omadused defineerimata!)"), $prop["name"]),
			);
		};

		if (empty($clid))
		{
			$errors = true;
			$xprops[] = array(
				"type" => "text",
				"caption" => t(" "),
				"error" => sprintf(t("Viga %s definitsioonis (seose t&uuml;&uuml;p defineerimata!)"), $prop["name"])
			);
		};

		// now check whether a relation was requested from url
		$edit_id = $arr["request"][$this->elname];

		$found = true;

		$cache_inst = get_instance("cache");

		if (!empty($edit_id) && is_oid($edit_id) && is_oid($arr["obj_inst"]->id()))
		{
			// check whether this connection exists
			$found = false;
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => $arr["prop"]["reltype"],
			));



			foreach($conns as $conn)
			{
				if ($conn->prop("to") == $edit_id)
				{
					$found = true;
				};
			};
		};

		if (!$found)
		{
			$errors = true;
			$xprops[] = array(
				"type" => "text",
				"caption" => t(" "),
				"value" => t("Seda seost ei saa redigeerida!"),
			);
		};

		if ($errors)
		{
			return $xprops;
		};

		if ($clid == 7)
		{
			$use_clid = "doc";
		}
		else
		{
			$use_clid = $clid;
		};
		$t = get_instance($use_clid);
		$t->init_class_base();
		$emb_group = "general";

		$filter = array(
			"group" => "general",
		);

		if (!empty($prop["use_form"]))
		{
			$filter["form"] = $prop["use_form"];
		};

		$all_props = array();

		// generate a list of all properties. Needed to display edit form
		// and to customize table display in manager mode
		//$all_props = $t->get_property_group($filter);
		$all_props = $t->load_defaults();
		$this->clid = $use_clid;
		$act_props = array();
		$use_form = $prop["use_form"];


		if (!empty($use_form))
		{
			foreach($all_props as $key => $_prop)
			{
				if (is_array($_prop["form"]) && in_array($use_form,$_prop["form"]))
				{
					$props[$key] = $key;
				};
			};
		};

		$form_type = $arr["request"][$this->elname];
		if (($arr["prop"]["always_show_add"] == 1 && !is_oid($edit_id)))
		{
			$form_type = "new";
		}
		$this->form_type = $form_type;

		#$this->all_props = $act_props;
		$pcount = sizeof($props);

		// the toolbar should be before the props, because otherwise it
		// would look freakish when adding new or changing -- ahz
		if($visual == "manager" && $arr["prop"]["no_toolbar"] != 1)
		{
			// insert the toolbar into property array
			$tbdef = $this->init_rel_toolbar($arr);
			$act_props[$tbdef["name"]] = $tbdef;
		}

		// act_props needs to contain properties, if
		// 1) visual is form and form_type is empty, if a single relation (rel_id=first) is being edited
		// 2) ....
		foreach($all_props as $key => $_prop)
		{
			//if (!empty($use_form) || (is_array($props) && in_array($key,$props)))
			//if ($all_props[$key])
			//if (is_array($props) && in_array($key,$props))
			//if ((!empty($form_type) && $all_props[$key]) || (is_array($props) && in_array($key,$props)))
			//if (!empty($form_type) && $all_props[$key] && is_array($props) && in_array($key,$props))
			if ($all_props[$key] && is_array($props) && in_array($key,$props))
			{
				// if (!empty($form_type) || $visual != "manager")
				if (!empty($form_type) || $visual != "manager")
				{
					// if (1 == $pcount)// yksiku elemendi caption releditor property captioniga sama
					if (1 == $pcount and "manager" != $visual)
					{
						$_prop["caption"] = $prop["caption"];
					};
					$act_props[$key] = $_prop;
				};
			};
			$this->all_props[$key] = $_prop;
		};

		$this->table_props = $props;

		// "someone" has already used cfgform property, but for what purpose or why, is a big f'ing mystery to me,
		// so i'll just implement something neater

		$cfgform_id = $arr["prop"]["cfgform_id"];
		if(is_oid($cfgform_id) && $this->can("view", $cfgform_id))
		{
			$cfg = get_instance(CL_CFGFORM);
			$this->cfg_act_props = $cfg->get_props_from_cfgform(array("id" => $cfgform_id));
			//$act_props = $act_props + $this->cfg_act_props;
		}

		if (!empty($prop["choose_default"]))
		{
			$this->choose_default = 1;
		};

		if ($visual == "manager")
		{
			// insert the table into property array
			$tabledef = $this->init_rel_table($arr);
			$act_props[$tabledef["name"]] = $tabledef;
		};

		// "form" does not need a caption
		if ($visual == "manager")
		{
			if ("new" == $form_type || ($arr["prop"]["always_show_add"] == 1 && !is_oid($edit_id)))
			{
				$act_props = array($this->elname . "_caption" => array(
					"name" => $this->elname . "_caption",
					"type" => "text",
					"value" => (empty($prop["no_caption"]) ? $prop["caption"] . " - " : "") . t("Uus"),
					"subtitle" => 1,
				)) + $act_props;
			}
			elseif (empty($prop["no_caption"]))
			{
				$act_props = array($this->elname . "_caption" => array(
					"name" => $this->elname . "_caption",
					"type" => "text",
					"value" => $prop["caption"],
					"subtitle" => 1,
				)) + $act_props;
			}
		}

		$obj_inst = false;

		// load the first connection.
		// It should be relatively simple to extend this so that it can load
		// a programmaticaly specified relation

		// need to check whether a existing recurrence thing is specifed, if so, add that
		if ($form_type != "new" && is_object($arr["obj_inst"]) &&  is_oid($arr["obj_inst"]->id()))
		{
			if ($edit_id)
			{
				$obj_inst = new object($edit_id);
			}
			else if (!empty($prop["rel_id"]) || $prop["rel_id"] == "first")
			{
			//else if ($prop["rel_id"] == "first")
			//{
				$o = $arr["obj_inst"];
				if (is_object($o) && is_oid($o->id()))
				{
					$conns = $o->connections_from(array(
						"type" => $prop["reltype"],
					));
					// take the first
					if ($prop["rel_id"] == "first")
					{
						$key = reset($conns);
						if ($key)
						{
							$obj_inst = $key->to();
						};
					}
					else
					if ($conns[$prop["rel_id"]])
					{
						$obj_inst = $conns[$prop["rel_id"]]->to();
					};
				};
			};
		};



		if (is_object($obj_inst) && empty($arr["view"]))
		{
			$act_props["id"] = array(
				"type" => "hidden",
				"name" => "id",
				"value" => $obj_inst->id(),
			);

		};


		if (($visual == "manager" && (is_object($obj_inst) || ($form_type == "new" || ($arr["prop"]["always_show_add"] == 1 && !is_oid($edit_id))))))
		//if ($visual == "form" || ($visual == "manager" && (is_object($obj_inst) || $form_type == "new")))
		{
			// I might not want a submit button, eh?
			// exactly my point: i don't want it, so the save button will be on toolbar -- ahz
			/*
			$act_props["sbt"] = array(
				"type" => "submit",
				"name" => "sbt",
				"value" => t("Salvesta"),
			);*/

			if ($arr["prop"]["cfgform"])
			{
				$act_props["eb_cfgform"] = array(
					"type" => "hidden",
					"name" => "cfgform",
					"value" => $arr["prop"]["cfgform"],
				);
			};
		};

		if (!$obj_inst)
		{
			$obj_inst = new object();
		};

		// so that the object can access the source object
		if (is_object($arr["obj_inst"]))
		{
			aw_global_set("from_obj",$arr["obj_inst"]->id());
		};

		// maybe I can use the property name itself
		if ($arr["cb_values"])
		{
			$t->cb_values = $arr["cb_values"];
		};


		// parse_properties fills the thing with values and stuff. And it eats my precious toolbar
		$xprops = $t->parse_properties(array(
			"properties" => $act_props,
			"name_prefix" => $this->elname,
			"obj_inst" => $obj_inst,
		));

		// add this after parse, otherwise the name will be in form propname[elname], and I do not
		// want this
		if ("manager" == $visual)
		{
			$act_name = $prop["name"] . "_action";
			$xprops[$act_name] = array(
				"type" => "hidden",
				"name" => $act_name,
				"id" => $act_name,
				"value" => "",
			);
		};

		if ($prop["parent"] != "")
		{
			$tmp = array();
			foreach($xprops as $pn => $pd)
			{
				$pd["parent"] = $prop["parent"];
				$tmp[$pn] = $pd;
			}
			$xprops = $tmp;
		}

		exit_function("init-rel-editor");
		return $xprops;
	}

	function init_rel_toolbar($arr)
	{
		if ($arr["request"]["action"] == "view")
		{
			return;
		}
		$createlinks = array();
		$return_url = get_ru();
		// You can set newly created object's parent to be current object
		if (!empty($arr['prop']['override_parent']) && $arr['prop']['override_parent'] == 'this')
		{
			$parent = $arr['obj_inst']->id();
		}
		// Or set any object for the parent
		elseif (!empty($arr['prop']['override_parent']) && is_oid($arr['prop']['override_parent']))
		{
			$parent = $arr['prop']['override_parent'];
		}
		else // Or the default, current objects parent.
		if (is_object($arr["obj_inst"]))
		{
			$parent = $arr['obj_inst']->parent();
		}
		$s_clids = array();
		foreach ($arr['prop']['clid'] as $clid)
		{
			if ($arr["prop"]["direct_links"])
			{
				$params = array(
					"return_url" => $return_url,
				);
				if ($arr["prop"]["cfgform"])
				{
					$params["cfgform"] = $arr["prop"]["cfgform"];
				}
				$params["alias_to"] = $arr["obj_inst"]->id();
				$params["reltype"] = $arr["prop"]["reltype"];
				$newurl = html::get_new_url($clid, $parent, $params);
			}
			else
			{
				$newurl = aw_url_change_var(array(
					$this->elname => "new",
				));
			}

			$createlinks[] = array('class' => $clid, 'url' => $newurl);
			$s_clids[] = $clid;
		}

		$tb = get_instance("vcl/toolbar");
		if (count($createlinks) > 1)
		{
			$tb->add_menu_button(array(
				"name" => "new",
				"tooltip" => t("Uus"),
			));
			$clss = aw_ini_get('classes');
			foreach ($createlinks as $i)
			{
				$cn = $clss[$i['class']]['name'];
				$tb->add_menu_item(array(
					'parent' => "new",
					'tooltip' => t("Uus ").$cn,
					'text' => sprintf(t('Lisa %s'),$cn),
					'link' => $i['url'],
				));
			}
		}
		else
		{
			$tb->add_button(array(
				"name" => "new",
				"img" => "new.gif",
				"tooltip" => t("Uus"),
				"url" => $createlinks[0]['url'],
			));
		}

		$confirm_test = t("Kustutada valitud objektid?");
		if (isset($arr['prop']['delete_relations']) && $arr['prop']['delete_relations'])
		{
			$confirm_test = t("Kustutada valitud seosed?");
		}

		$act_input = $this->elname . "_action";

		$tb->add_search_button(array(
			"pn" => "s_reled",
			"multiple" => 1,
			"clid" => $s_clids
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			// ma pean siia kuidagi mingi triki tegema. Fuck, I hate this :(
			"url" => "javascript:if(confirm('${confirm_test}')){el=document.getElementsByName('${act_input}');el[0].value='delete';document.changeform.submit();};",
			//"action" => "submit_list",
		));

		// because it sucks to have both toolbar and a save button, we'll put the save on toolbar -- ahz
		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
			"action" => "",
		));

		if($arr["prop"]["clone_link"] == 1)
		{
			$tb->add_button(array(
				"name" => "clone",
				"img" => "copy.gif",
				"tooltip" => t("Klooni valitud objektid"),
				"url" => "javascript:element = 'check[';len = document.changeform.elements.length;var count = 0;for (i=0; i < len; i++){if (document.changeform.elements[i].checked == true){count++;}}if(count == 1){num=prompt('Mitu objekti kloonida soovid?', '1');document.changeform.releditor_clones.value=num;document.changeform.submit();}else{alert('Sa oled kas liiga vahe voi liiga palju objekte kloonimiseks valinud, proovi uuesti')}",
			));
		}

		$rv = array(
			"name" => $this->elname . "_toolbar",
			"type" => "toolbar",
			"vcl_inst" => $tb,
			"no_caption" => 1,
		);

		return $rv;
	}


	function init_rel_table($arr)
	{
		classload("vcl/table");
		$awt = new vcl_table(array(
			"layout" => "generic",
		));
		if ($arr["prop"]["table_edit_fields"])
		{
			$ed_fields = new aw_array($arr["prop"]["table_edit_fields"]);
			$ed_fields = $ed_fields->get();
		};

		if($arr["prop"]["filt_edit_fields"] == 1)
		{
			$ed_fields = array("name" => "name");
		}
		if ($arr["prop"]["props"])
		{
			$tmp = new aw_array($arr["prop"]["props"]);
			$tb_fields = $tmp->get();
		};

		$fdata = array();
		$conns = array();
		$filt_props = array();
		if(!$arr["new"] && is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
		{
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => $arr["prop"]["reltype"],
			));
			$name = $arr["prop"]["name"];
			$return_url = get_ru();
			foreach($conns as $conn)
			{
				$c_to = $conn->prop("to");
				if ($arr["prop"]["direct_links"] == 1)
				{
					$url = $this->mk_my_orb("change",array(
						"id" => $c_to,
						"return_url" => $return_url
					),$conn->prop("to.class_id"));
				}
				else
				{
					$url = aw_url_change_var(array($this->elname => $c_to));
				};
				$target = $conn->to();
				$clinst = $target->instance();
				$rowdata = array(
					"id" => $c_to,
					"parent" => $target->parent(),
					"conn_id" => $conn->id(),
					"name" => $conn->prop("to.name"),
					"edit" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $url,
					)),
					"_sort_jrk" => $conn->prop("to.jrk"),
					"_sort_name" => $conn->prop("to.name"),
					"_active" => ($arr["request"][$this->elname] == $c_to),
				);
				$export_props = array();
				$clss = null;
				// Some autogenerated fields, for list see line 11 or so
				foreach ($this->auto_fields as $fn => $caption)
				{
					if (in_array($fn, $tb_fields))
					{
						$value = "";
						switch ($fn)
						{
							case 'class_name':
								if (!isset($clss))
								{
									$clss = aw_ini_get('classes');
								}
								$value = $clss[$target->class_id()]['name'];
							break;
							case 'class_id':
								$value = $target->class_id();
							break;
						}
						$export_props[$fn] = $value;
					}
				}

				$property_list = $target->get_property_list();

				foreach($property_list as $_pn => $_pd)
				{
					if (!in_array($_pn,$tb_fields))
					{
						continue;
					};

					/*
					if (empty($fdata[$_pn]))
					{
						continue;
					};
					*/
					$prop = $_pd;
					$prop["value"] = $target->prop($_pn);
					// now lets call get_property on that beast
					if(method_exists($clinst, "get_property"))
					{
						$test = $clinst->get_property(array(
							"prop" => &$prop,
							"obj_inst" => $target,
							"called_from" => "releditor"
						));
						if (PROP_OK != $test)
						{
							continue;
						};
					}
					// I don't want to display the value of the chooser, but the caption of the value. ;) - Kaarel
					if ($_pd["type"] == "chooser" && is_array($prop["options"]))
					{
						$prop["value"] = $prop["options"][$prop["value"]];
					}
					if ($_pd["type"] == "date_select")
					{
						$prop["value"] = date("d.m.Y", $prop["value"]);
					}
					else
					if ($_pd["type"] == "datetime_select")
					{
						$prop["value"] = date("d.m.Y", $prop["value"]);
					}
					if (($_pd["type"] == "relpicker" || $_pd["type"] == "classificator") && $this->can("view", $prop["value"]))
					{
						$_tmp = obj($prop["value"]);
						$prop["value"] = $_tmp->name();
					}
					else
					if ($_pd["type"] == "select" && is_array($prop["options"]))
					{
						$prop["value"] = $prop["options"][$prop["value"]];
					};
					if($arr["prop"]["filt_edit_fields"] == 1)
					{
						if($prop["value"] != "" && $prop["type"] == "textbox")
						{
							$ed_fields[$_pn] = $_pn;
						}
					}
					$export_props[$_pn] = $prop["value"];
				}
				


				$ed_fields["name"] = "name";
				//$export_props = $target->properties();
				if ($ed_fields && ($this->form_type != $target->id()))
				{
					foreach(array_unique($ed_fields) as $ed_field)
					{
						// fucking hackery! :(
						if ($this->all_props[$ed_field]["type"] == "textbox")
						{
							$export_props[$ed_field] = html::textbox(array(
								"name" => "$name" . '[_data][' . $conn->prop("id") . '][' . $ed_field . "]",
								"value" => $export_props[$ed_field],
								"size" => $this->all_props[$ed_field]["size"] ? $this->all_props[$ed_field]["size"] : 15,
							));
						};
					};
				};
				$rowdata = $export_props + $rowdata;
				if ($this->choose_default)
				{
					$rowdata = $rowdata + array(
						"default" => html::radiobutton(array(
							"name" => $arr["prop"]["name"] . '[_default]',
							"value" => $rowdata["id"],
							"checked" => ($arr["prop"]["value"] == $rowdata["id"]),
						)),
					);

				};
				$stuff = $this->get_sub_prop_values(array(
					"prop" => &$prop,
					"obj_inst" => $target,
					"fields" => $this->get_sub_props($tb_fields),
				));
				$rowdata = $rowdata + $stuff;
				// This one defines the display table data. Just a reminder for myself. - Kaarel
				$awt->define_data($rowdata);
			}
		}

		if ($this->choose_default)
		{
			$awt->define_field(array(
				"name" => "default",
				"caption" => t("Vali &uuml;ks"),
				"align" => "center",
				"sortable" => 1
			));
		};

		if($arr["prop"]["filt_edit_fields"] == 1)
		{
			$awt->define_field(array(
				"name" => "id",
				"caption" => t("ID"),
				"sortable" => 1
			));

			foreach($ed_fields as $field)
			{
				$caption = $this->all_props[$field]["caption"];
				if (isset($this->cfg_act_props[$field]["caption"]))
                                {
					$caption = $this->cfg_act_props[$field]["caption"];
				}
				$awt->define_field(array(
					"name" => $field,
					"caption" => $caption,
					"sortable" => 1
				));
			}
		}
		elseif (!empty($arr["prop"]["table_fields"]))
		{
			if (!is_array($arr['prop']['table_fields']))
			{
				$arr['prop']['table_fields'] = array($arr['prop']['table_fields']);
			}
			foreach($arr["prop"]["table_fields"] as $table_field)
			{
				$caption = $table_field;
				if(sizeof(explode("." , $table_field)) > 1)
				{
					$sub_fileds = explode("." , $table_field);
					$reltype = $this->all_props[$sub_fileds[0]]["reltype"];
					$o_ = new object();
					$o_->set_class_id($this->clid);
					$relinfo = $o_->get_relinfo();
					$clid = reset($relinfo[$reltype]["clid"]);
					$o_2 = new object();
					$o_2->set_class_id($clid);
					$prop2 = $o_2->get_property_list();
					$caption = $prop2[$sub_fileds[1]]["caption"];

				}
				if (isset($this->all_props[$table_field]))
				{
					$caption = $this->all_props[$table_field]['caption'];
				}
				else if (isset($this->auto_fields[$table_field]))
				{
					$caption = $this->auto_fields[$table_field];
				}
				else
				if (isset($this->cfg_act_props[$table_field]["caption"]))
				{
					$caption = $this->cfg_act_props[$table_field]["caption"];
				}
				$awt->define_field(array(
					"name" => $table_field,
					"caption" => $caption,
					"sortable" => 1
				));
				//$fdata[$table_field] = $table_field;
			};
		}
		else
		{
			$awt->define_field(array(
				"name" => "id",
				"caption" => t("ID"),
				"sortable" => 1
			));

			$awt->define_field(array(
				"name" => "name",
				"caption" => t("Nimi"),
				"sortable" => 1
			));
		};

		if ($arr["request"]["action"] != "view")
		{
			$awt->define_field(array(
				"name" => "edit",
				"caption" => t("Muuda"),
				"align" => "center",
			));

			// aliasmgr uses "check"
			$awt->define_chooser(array(
				"field" => "conn_id",
				"name" => "check",
			));
		}
		// and how do I get values for those?

		// and how do I show the selected row?

		if($arr["prop"]["clone_link"] == 1)
		{
			$awt->table_header = '<input type="hidden" name="releditor_clones" id="releditor_clones" value="0" />';
		}

		$awt->set_sortable(true);
		$awt->set_default_sortby(array("_sort_jrk"=>"_sort_jrk", "_sort_name"=>"_sort_name"));
		$awt->sort_by();
		$awt->set_sortable(false);

		$rv = array(
			"name" => $this->elname . "_table",
			"type" => "table",
			"vcl_inst" => $awt,
			"no_caption" => 1,
		);

		return $rv;
	}

	function get_sub_prop_values($arr)
	{
		$ret = array();
		foreach($arr["fields"] as $field => $data)
		{
			$ret[$field] = $arr["obj_inst"]->prop_str($field);
		}
		return $ret;
	}

	function get_sub_props($table_fields)
	{
		$ret = array();
		foreach($table_fields as $table_field)
		{
			if(sizeof(explode("." , $table_field)) > 1)
			{
				$sub_fileds = explode("." , $table_field);
				$reltype = $this->all_props[$sub_fileds[0]]["reltype"];
				$o_ = new object();
				$o_->set_class_id($this->clid);
				$relinfo = $o_->get_relinfo();
				$clid = reset($relinfo[$reltype]["clid"]);
				$o_2 = new object();
				$o_2->set_class_id($clid);
				$prop2 = $o_2->get_property_list();
				$ret[$table_field] = $prop2[$sub_fileds[1]];;
			}
		}
		return $ret;
	}

	/**
		@attrib name=delo all_args=1 public=1
	**/
	function delo($arr)
	{
		extract($arr);
		$o = obj($id);
		$o->delete();
	}

	/**
		@attrib name=process_new_releditor all_args=1
	**/
	function process_new_releditor($arr)
	{//arr($_POST);die();
		extract($arr);
		if($id) $arr["request"]["id"] = $id;
		if($reltype) $arr["prop"]["reltype"] = $reltype;
		if($clid) $arr["prop"]["clid"] = $clid;
		if($releditor_name) $arr["prop"]["name"] = $this->elname = $releditor_name;
		$clid = $arr["prop"]["clid"];
		if(is_array($clid))
		{
			$clid = reset($clid);
		}
		$parent_object = obj($arr["request"]["id"]);
		$parent = $arr["request"]["id"];
		if(!$arr["prop"]["value"])
		{
			$arr["prop"]["value"] = $_POST[$arr["prop"]["name"]];
			foreach($arr["prop"]["value"] as $key => $data)
			{
				foreach($data as $prop => $val)
				{
					if(!is_array($val))$arr["prop"]["value"][$key][$prop] = utf8_decode($val);
				}
			}
		}
		foreach($arr["prop"]["value"] as $key => $data)
		{
			$o = new object();
			$o->set_class_id($clid);
			$o->set_parent($parent);
			$o->set_name();

			foreach($data as $prop => $val)
			{
				if(is_array($val))
				{
					if(!$val["day"])
					{
						$val["day"] = 1;
					}
					$val = date_edit::get_timestamp($val);
				}
				if($o->is_property($prop))
				{
					$o->set_prop($prop , $val);
				}
			}
			$o->save();
			$parent_object->connect(array(
				"to" => $o->id(),
				"reltype" =>  $arr["prop"]["reltype"],
			));
		}
		header ("Content-Type: text/html; charset=".aw_global_get("charset"));
		die($this->init_new_rel_table($arr));
	}

	function process_releditor($arr)
	{
		if($arr["prop"]["mode"] == "manager2")
		{
			$this->process_new_releditor($arr);
			return;
		}
		$prop = &$arr["prop"];
		$obj = $arr["obj_inst"];
		$set_default_relation = false;

		$clid = $arr["prop"]["clid"][0];

		if ($arr["prop"]["reltype"])
		{
			$ps = get_instance("vcl/popup_search");
			$ps->do_create_rels($obj, $arr["request"]["s_reled"], $arr["prop"]["reltype"]);
		}

		if ($clid == 7)
		{
			$use_clid = "doc";
		}
		else
		{
			$use_clid = $clid;
		};

		if (!isset($prop['delete_relations']))
		{
			$prop['delete_relations'] = '0';
		}

		$act_prop = $prop["name"] . "_action";

		if ("delete" == $arr["request"][$act_prop])
		{
			// XXX: this will fail, if there are multiple releditors on one page
			$to_delete = new aw_array($arr["request"]["check"]);
			$delete_default = false;

			foreach($to_delete->get() as $alias_id)
			{
				$c = new connection($alias_id);

				if ("manager" == $prop["mode"] and $c->prop("to") == $obj->prop($prop["name"]))
				{
					$delete_default = true;
				}

				if (true || $prop['delete_relations'] == '1')
				{
					$c->delete();
				}
				else
				{
					$target = $c->to();
					$target->delete();
				}
			}

			if ($delete_default)
			{
				# old default deleted, set first found to be default
				$conns = $obj->connections_from(array(
					"type" => $arr["prop"]["reltype"],
				));
				$first_conn = reset($conns);

				if (is_object($first_conn))
				{
					$obj->set_prop($arr["prop"]["name"], $first_conn->prop("to"));
				}
			}

			return PROP_OK;
		}

		$clinst = get_instance($use_clid);

		$elname = $prop["name"];

		$emb = $arr["request"][$elname];
		// _data is used to edit multiple connections at once
		unset($emb["_data"]);

		if (is_oid($emb["_default"]))
		{
			$prop["value"] = $emb["_default"];
			$set_default_relation = $emb["_default"];
		};

		unset($emb["_default"]);

		$clinst->init_class_base();
		$emb_group = "general";

		$filter = array(
			"group" => "general",
		);

		if (!empty($prop["use_form"]))
		{
			$filter["form"] = $prop["use_form"];
			$use_form = $prop["use_form"];
		};

		$props = $clinst->load_defaults();

		$propname = $prop["name"];
		$proplist = is_array($prop["props"]) ? $prop["props"] : array($prop["props"]);

		$el_count = 0;

		foreach($props as $item)
		{
			// if that property is in the list of the class properties, then
			// process it
			if (!empty($use_form) || in_array($item["name"],$proplist))
			{
				if ($item["type"] == "fileupload")
				{
					if (!is_array($emb[$item["name"]]))
					{
						// ot, aga miks need 2 caset siin on?
						$name = $item["name"];
						$_fileinf = $_FILES[$elname];
						$filename = $_fileinf["name"][$name];
						$filetype = $_fileinf["type"][$name];
						$tmpname = $_fileinf["tmp_name"][$name];
						// tundub, et polnud sellist faili, eh?
						if(empty($tmpname) || !is_uploaded_file($tmpname))
						{
						}
						else
						{
							$emb[$name] = array(
								"tmp_name" => $tmpname,
								"type" => $filetype,
								"name" => $filename,
							);
							$el_count++;
						};
					}
					/*
					// ok, wtf is that code supposed to do?
					// - that code is supposed to upload the picture when it's added through another
					// - class = meaning, DO NOT TOUCH THIS OK? -- ahz
					*/
					else
					{
						$tmpname = $emb[$item["name"]]["tmp_name"];
						if (is_uploaded_file($tmpname))
						{
							$emb[$item["name"]]["contents"] = $this->get_file(array(
								"file" => $tmpname,
							));

							$el_count++;
						};
					};

				}
				else
				{
					// this shit takes care of those non-empty select boxes
					if ($emb[$item["name"]] && $item["type"] != "datetime_select" && $item["name"] != "status")
					{
						$el_count++;
					}

					if ($item["type"] == "checkbox" && !$emb[$item["name"]])
					{
						$emb[$item["name"]] = 0;
					}
				}
			}
		}


		// TODO: make it give feedback to the user, if an object can not be added
		if ($el_count > 0)
		{
			$emb["group"] = "general";
			$obj_parent = $obj->parent();
			if (is_oid($prop["obj_parent"]))
			{
				$obj_parent = $prop["obj_parent"];
			};
			if ($prop["override_parent"] == "this")
			{
				$obj_parent = $obj->id();
			}
			$emb["parent"] = $obj_parent;
			$emb["return"] = "id";
			$emb["prefix"] = $elname;

			$reltype = $arr["prop"]["reltype"];

			$emb["cb_existing_props_only"] = 1;


			$obj_id = $clinst->submit($emb);
			// fucking hackery :(
			$cb_values = aw_global_get("cb_values");
			if (is_array($cb_values) && sizeof($cb_values) > 0)
			{
				$errtxt = "";
				foreach($cb_values as $pkey => $pval)
				{
					$errtxt .= $pval["error"];
				};
				$prop["error"] = $errtxt;
				return PROP_ERROR;
				/*
				print "<pre>";
				print_r($cb_values);
				print "</pre>";
				*/
			}



			if ($prop["rel_id"] == "first" && empty($emb["id"]))
			{
				// I need to disconnect, no?
				if (is_oid($obj->id()))
				{
					$old = $obj->connections_from(array(
						"type" => $arr["prop"]["reltype"],
					));

					foreach($old as $conn)
					{
						$obj->disconnect(array(
							"from" => $conn->prop("to"),
						));
					};
				};
			};
			if (is_oid($obj_id))
			{
				if (empty($emb["id"]))
				{
					$obj->connect(array(
						"to" => $obj_id,
						"reltype" => $arr["prop"]["reltype"],
					));

					if (!$obj->prop($arr["prop"]["name"]))
					{
						$set_default_relation = $obj_id;
					}
				};
			};
		}

		if ($set_default_relation)
		{
			$obj->set_prop($arr["prop"]["name"], $set_default_relation);
		}

		// is this save() here really needed?  --dragut
		// it seems that, in some cases it saves an object which has releditor
		// although it shouldn't be saved cause some PROP_FATAL_ERROR appearance.
		// --dragut
	//	$obj->save();

		$things = $arr["request"][$elname]["_data"];
		if (sizeof($things) > 0 && is_oid($obj->id()))
		{
			$conns = $obj->connections_from(array(
				"type" => $arr["prop"]["reltype"],
			));

			foreach($conns as $conn)
			{
				$conn_id = $conn->prop("id");
				if ($things[$conn_id])
				{
					$to_obj = $conn->to();
					foreach($things[$conn_id] as $propname => $propvalue)
					{
						$to_obj->set_prop($propname,$propvalue);
					};
					$to_obj->save();
				};
			};
		};
		$num = (int) $arr["request"]["releditor_clones"];
		if($arr["prop"]["clone_link"] == 1 && $num > 0)
		{
			foreach(safe_array($arr["request"]["check"]) as $check)
			{
				$conn = new connection($check);
				$old_obj = $conn->to();
				for($i = 1; $i <= $num; $i++)
				{
					$new_obj = obj($old_obj->save_new());
					$obj->connect(array(
						"to" => $new_obj->id(),
						"reltype" => $arr["prop"]["reltype"],
					));
				}
			}
		}
	}

	function get_html()
	{
		return "here be releditor";
		//return $this->t->draw();
	}

	function callback_mod_reforb($arr)
	{
		$arr["s_reled"] = "0";
	}

};
?>
