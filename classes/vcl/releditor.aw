<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/releditor.aw,v 1.51 2005/04/14 13:53:49 duke Exp $
/*
	Displays a form for editing one connection
	or alternatively provides an interface to edit
	multiple connections
*/

class releditor extends core
{
	function releditor()
	{
		$this->init();
	}

	function init_rel_editor($arr)
	{
		enter_function("init-rel-editor");
		$prop = $arr["prop"];
		$this->elname = $prop["name"];
		$obj = $arr["obj_inst"];
		$obj_inst = $obj;

		$clid = $arr["prop"]["clid"][0];

		$props = $arr["prop"]["props"];
		if (!is_array($props) && !empty($props))
		{
			$props = array($props);
		};

		$xprops = array();

		$errors = false;


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
				"error" => sprintf(t("Viga %s definitsioonis (seose tüüp defineerimata!)"), $prop["name"])
			);
		};

		// now check whether a relation was requested from url
		$edit_id = $arr["request"][$this->elname];

		$found = true;
		
		$cache_inst = get_instance("cache");
		$cache_inst->file_invalidate_regex('alias_cache-source-.*');
		// XXX: there is a weird bug with connection caching .. this NEEDS to be fixed in the future
		$cache_inst->file_invalidate_regex('connection-search.*');

		if (!empty($edit_id) && is_oid($edit_id))
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
		$this->form_type = $form_type;

		#$this->all_props = $act_props;
		$pcount = sizeof($props);

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
				if (!empty($form_type) || $visual != "manager")
				{
					if (1 == $pcount)
					{
						$_prop["caption"] = $prop["caption"];
					};
					$act_props[$key] = $_prop;
				};
			};
			$this->all_props[$key] = $_prop;
		};
		
		// "someone" has already used cfgform property, but for what purpose or why, is a big f'ing mystery to me,
		// so i'll just implement something neater 
		
		$cfgform_id = $arr["prop"]["cfgform_id"];
		if(is_oid($cfgform_id) && $this->can("view", $cfgform_id))
		{
			$cfg = get_instance(CL_CFGFORM);
			$act_props = $cfg->get_props_from_cfgform(array("id" => $cfgform_id));
		}

		if ($visual == "manager")
		{
			// insert the toolbar into property array
			$tbdef = $this->init_rel_toolbar($arr);
			$act_props[$tbdef["name"]] = $tbdef;

			// insert the table into property array
			$tabledef = $this->init_rel_table($arr);
			$act_props[$tabledef["name"]] = $tabledef;
		};

		// "form" does not need a caption
		if ($visual == "manager" && $form_type == "new")
		{
			$act_props = array($this->elname . "_caption" => array(
				"name" => $this->elname . "_caption",
				"type" => "text",
				"value" => "Uus",
				"subtitle" => 1,
			)) + $act_props;
		};

		$obj_inst = false;


		// load the first connection.
		// It should be relatively simple to extend this so that it can load
		// a programmaticaly specified relation

		// need to check whether a existing recurrence thing is specifed, if so, add that
		if ($form_type != "new")
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


		if (($visual == "manager" && (is_object($obj_inst) || $form_type == "new")))
		//if ($visual == "form" || ($visual == "manager" && (is_object($obj_inst) || $form_type == "new")))
		{
			// I might not want a submit button, eh?
			$act_props["sbt"] = array(
				"type" => "submit",
				"name" => "sbt",
				"value" => "Salvesta",
			);

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
		
		exit_function("init-rel-editor");
		
		return $xprops;
	}

	function init_rel_toolbar($arr)
	{
		if ($arr["prop"]["direct_links"])
		{
			$params = array(
				"return_url" => urlencode(aw_global_get("REQUEST_URI"))
			);
			if ($arr["prop"]["cfgform"])
			{
				$params["cfgform"] = $arr["prop"]["cfgform"];
			}
			$params["alias_to"] = $arr["obj_inst"]->id();
			$params["reltype"] = $arr["prop"]["reltype"];
			$params["return_url"] = urlencode(aw_global_get("REQUEST_URI"));
			$newurl = html::get_new_url($arr["prop"]["clid"][0], $arr["obj_inst"]->parent(), $params);
		}
		else
		{
			$newurl = aw_url_change_var(array(
				$this->elname => "new",
			));
		}

		$tb = get_instance("vcl/toolbar");
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus"),
			"url" => $newurl,
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"confirm" => t("Kustutada valitud objektid?"),
			"action" => "submit_list",
		));
		if($arr["prop"]["clone_link"] == 1)
		{
			$tb->add_button(array(
				"name" => "clone",
				"img" => "save.gif",
				"tooltip" => t("Klooni valitud objektid"),
				"url" => "javascript:element = 'check[';len = document.changeform.elements.length;var count = 0;for (i=0; i < len; i++){if (document.changeform.elements[i].checked == true){count++;}}if(count == 1){num=prompt('Mitu objekti kloonida soovid?', '1');document.changeform.releditor_clones.value=num;document.changeform.submit();}else{alert('Sa oled kas liiga vähe või liiga palju objekte kloonimiseks valinud, proovi uuesti')}",
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
		$fdata = array();
		$conns = array();
		$filt_props = array();
		if(!$arr["new"])
		{
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => $arr["prop"]["reltype"],
			));
			$name = $arr["prop"]["name"];
			foreach($conns as $conn)
			{
				if ($arr["prop"]["direct_links"] == 1)
				{
					$url = $this->mk_my_orb("change",array("id" => $conn->prop("to"),"return_url" => urlencode(aw_global_get("REQUEST_URI"))),$conn->prop("to.class_id"));
				}
				else
				{
					$url = aw_url_change_var(array($this->elname => $conn->prop("to")));
				};
				$target = $conn->to();
				$clinst = $target->instance();
				$rowdata = array(
					"id" => $conn->prop("to"),
					"conn_id" => $conn->id(),
					"name" => $conn->prop("to.name"),
					"edit" => html::href(array(
						"caption" => t("Muuda"),
						"url" => $url,
					)),
					"_active" => ($arr["request"][$this->elname] == $conn->prop("to")),
				);
				$property_list = $target->get_property_list();
				$export_props = array();
				foreach($property_list as $_pn => $_pd)
				{
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
						));
						if (PROP_OK != $test)
						{
							continue;
						};
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
				//$export_props = $target->properties();
				if ($ed_fields && ($this->form_type != $target->id()))
				{
					foreach($ed_fields as $ed_field)
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
				$awt->define_data($rowdata);
			}
		}

		if($arr["prop"]["filt_edit_fields"] == 1)
		{
			$awt->define_field(array(
				"name" => "id",
				"caption" => t("ID"),
			));
			foreach($ed_fields as $field)
			{
				$awt->define_field(array(
					"name" => $field,
					"caption" => $this->all_props[$field]["caption"],
				));
			}
		}
		elseif (is_array($arr["prop"]["table_fields"]))
		{
			foreach($arr["prop"]["table_fields"] as $table_field)
			{
				$awt->define_field(array(
					"name" => $table_field,
					"caption" => $this->all_props[$table_field]["caption"],
				));
				//$fdata[$table_field] = $table_field;
			};
		}
		else
		{
			$awt->define_field(array(
				"name" => "id",
				"caption" => t("ID"),
			));

			$awt->define_field(array(
				"name" => "name",
				"caption" => t("Nimi"),
			));
		};

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

		// and how do I get values for those?

		// and how do I show the selected row?

		if($arr["prop"]["clone_link"] == 1)
		{
			$awt->table_header = '<input type="hidden" name="releditor_clones" id="releditor_clones" value="0" />';
		}

		$rv = array(
			"name" => $this->elname . "_table",
			"type" => "table",
			"vcl_inst" => $awt,
			"no_caption" => 1,
		);

		return $rv;
	}

	function process_releditor($arr)
	{
		$prop = $arr["prop"];
		$obj = $arr["obj_inst"];

		$clid = $arr["prop"]["clid"][0];
		if ($clid == 7)
		{
			$use_clid = "doc";
		}
		else
		{
			$use_clid = $clid;
		};
			
	
		$clinst = get_instance($use_clid);

		$elname = $prop["name"];

		$emb = $arr["request"][$elname];
		// _data is used to edit multiple connections at once
		unset($emb["_data"]);

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
							$contents = $this->get_file(array(
								"file" => $tmpname,
							));
							$emb[$name] = array(
								"tmp_name" => $tmpname,
								"type" => $filetype,
								"name" => $filename,
								"contents" => $contents,
							);
							$el_count++;
						};
					}
					else
					{
						$emb[$item["name"]]["contents"] = $this->get_file(array(
							"file" => $emb[$item["name"]]["tmp_name"],
						));

						$el_count++;
					};
				}
				else
				{
					// this shit takes care of those non-empty select boxes
					if ($emb[$item["name"]] && $item["type"] != "datetime_select" && $item["name"] != "status")
					{
						$el_count++;
					};
				};
			};
		};

		// TODO: make it give feedback to the user, if an object can not be added
		if ($el_count > 0)
		{

			$emb["group"] = "general";
			$emb["parent"] = $obj->parent();
			$emb["return"] = "id";
			$emb["prefix"] = $elname;

			$reltype = $arr["prop"]["reltype"];

			$emb["cb_existing_props_only"] = 1;


			$obj_id = $clinst->submit($emb);

			if ($prop["rel_id"] == "first" && empty($emb["id"]))
			{
				// I need to disconnect, no?
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

			if (is_oid($obj_id))
			{
				if (empty($emb["id"]))
				{
					$obj->connect(array(
						"to" => $obj_id,
						"reltype" => $arr["prop"]["reltype"],
					));
				};
			};
		};

		$obj->save();

		$things = $arr["request"][$elname]["_data"];
		if (sizeof($things) > 0)
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

		$cache_inst = get_instance("cache");
		$cache_inst->file_invalidate_regex('alias_cache-source-.*');



	}

	function get_html()
	{
		//return "here be releditor";
		//return $this->t->draw();
	}


};
?>
