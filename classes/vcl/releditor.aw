<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/releditor.aw,v 1.20 2004/06/17 14:33:35 duke Exp $
/*
	Displays a form for editing an connection
*/

class releditor extends aw_template 
{
	function releditor()
	{
		$this->tpl_init();
	}

	function init_rel_editor($arr)
	{
		global $awt;
		$awt->start("init-rel-editor");
		$prop = $arr["prop"];
		$this->elname = $prop["name"];
		$obj = $arr["obj_inst"];

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
				"caption" => " ",
				"error" => "Viga $prop[name] definitsioonis (omadused defineerimata!)",
			);
		};

		if (empty($clid))
		{
			$errors = true;
			$xprops[] = array(
				"type" => "text",
				"caption" => " ",
				"error" => "Viga $prop[name] definitsioonis (seose tüüp defineerimata!)",
			);
		};

		// now check whether a relation was requested from url
		$edit_id = $arr["request"][$this->elname];

		$found = true;
		
		$cache_inst = get_instance("cache");
		$cache_inst->file_invalidate_regex('alias_cache-source-.*');

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
				"caption" => " ",
				"value" => "Seda seost ei saa redigeerida!",
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

		// generate a list of all properties. Needed if I'm going to display a form
		// but not needed, if I'm going to add a button only
		if ($visual == "manager")
		{
			if (!empty($arr["request"][$this->elname]))
			{
				$all_props = $t->get_property_group($filter);
			};
		}
		else
		{
			$all_props = $t->get_property_group($filter);
		};
		
		$act_props = array();

		if ($visual == "manager")
		{
			// insert the toolbar into property array
			$tbdef = $this->init_rel_toolbar($arr);
			$act_props[$tbdef["name"]] = $tbdef;

			// insert the table into property array
			$tabledef = $this->init_rel_table($arr);
			$act_props[$tabledef["name"]] = $tabledef;
		};

		$form_type = $arr["request"][$this->elname];
		// "form" does not need a caption
		if ($visual == "manager" && $form_type == "new")
		{
			$act_props[$this->elname . "_caption"] = array(
				"name" => $this->elname . "_caption",
				"type" => "text",
				"value" => "Uus",
				"subtitle" => 1,
			);
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
			else if (!empty($prop["rel_id"]))
			{
			//else if ($prop["rel_id"] == "first")
			//{
				$o = $arr["obj_inst"];
				if (is_oid($o->id()))
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

		$use_form = $prop["use_form"];

		foreach($all_props as $key => $prop)
		{
			if (!empty($use_form) || (is_array($props) && in_array($key,$props)))
			{
				$act_props[$key] = $prop;
			};
		};

		if (is_object($obj_inst))
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
		aw_global_set("from_obj",$arr["obj_inst"]->id());

		// maybe I can use the property name itself
		if ($arr["cb_values"])
		{
			$t->cb_values = $arr["cb_values"];
		};

		$xprops = $t->parse_properties(array(
			"properties" => $act_props,
			"name_prefix" => $this->elname,
			"obj_inst" => $obj_inst,
		));
		
		$awt->stop("init-rel-editor");
		
		return $xprops;
	}

	function parse_releditor($arr)
	{


	}

	function init_rel_toolbar($arr)
	{
		$newurl = aw_url_change_var(array(
			$this->elname => "new",
		));

		classload("toolbar");
		$tb = new toolbar();
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => "Uus",
			"url" => $newurl,
		));

		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"confirm" => "Kustutada valitud objektid?",
			"action" => "submit_list",
		));
		
		$rv = array(
			"name" => $this->elname . "_toolbar",
			"type" => "toolbar",
			"toolbar" => $tb,
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

		$awt->define_field(array(
			"name" => "id",
			"caption" => "ID",
		));

		$awt->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));

		$awt->define_field(array(
			"name" => "edit",
			"caption" => "Muuda",
			"align" => "center",
		));

		// aliasmgr uses "check"
		$awt->define_chooser(array(
			"field" => "conn_id",
			"name" => "check",
		));

		// and how do I get values for those?

		// and how do I show the selected row?

		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => $arr["prop"]["reltype"],
		));

		foreach($conns as $conn)
		{
			if ($arr["prop"]["direct_links"] == 1)
			{
				$url = $this->mk_my_orb("change",array("id" => $conn->prop("to")),$conn->prop("to.class_id"));
			}
			else
			{
				$url = aw_url_change_var(array($this->elname => $conn->prop("to")));
			};
			$awt->define_data(array(
				"id" => $conn->prop("to"),
				"conn_id" => $conn->id(),
				"name" => $conn->prop("to.name"),
				"edit" => html::href(array(
					"caption" => "Muuda",
					"url" => $url,
				)),
				"_active" => ($arr["request"][$this->elname] == $conn->prop("to")),
			));
		};

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

		$props = $clinst->get_property_group($filter);

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
					$name = $item["name"];
					$_fileinf = $_FILES[$elname];
					$filename = $_fileinf["name"][$name];
					$filetype = $_fileinf["type"][$name];
					$tmpname = $_fileinf["tmp_name"][$name];
					// tundub, et polnud sellist faili, eh?
					if (empty($tmpname) || !is_uploaded_file($tmpname))
					{
						return false;
					};
					$contents = $this->get_file(array(
						"file" => $tmpname,
					));
					$emb[$name] = array(
						"tmp_name" => $tmpname,
						"type" => $filetype,
						"name" => $filename,
						"contents" => base64_encode($contents),
					);
					$el_count++;
				}
				else
				{
					// this shit takes care of those non-empty select boxes
					if ($emb[$item["name"]] && $item["type"] != "datetime_select")
					{
						$el_count++;
					};
				};

                        };
                };

		// TODO: make it give feedback to the user, if an object can not be added
		if ($el_count == 0)
		{
			return false;
		};

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
			
		$obj->save();
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
