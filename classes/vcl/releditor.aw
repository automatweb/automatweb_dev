<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/releditor.aw,v 1.10 2004/03/25 16:16:38 duke Exp $
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
		$visual = isset($prop["visual"]) && $prop["visual"] == "manager" ? "manager" : "form";

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
			else if ($prop["rel_id"] == "first")
			{
				// take the first
				$o = $arr["obj_inst"];
				if (is_oid($o->id()))
				{
					$conns = $o->connections_from(array(
						"type" => $prop["reltype"],
					));
					$key = reset($conns);
					if ($key)
					{
						$obj_inst = $key->to();
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
		};

		if (!$obj_inst)
		{
			$obj_inst = new object();
		};

		$xprops = $t->parse_properties(array(
			"properties" => $act_props,
			"name_prefix" => "cba_emb",
			"obj_inst" => $obj_inst,
		));
		$awt->stop("init-rel-editor");
		
		return $xprops;
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
		$awt = new aw_table(array(
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

		// hookei. And pray god, please tell me, which fields to I put into that table?
		// and how do I get values for those?

		// and how do I show the selected row?

		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => $arr["prop"]["reltype"],
		));

		foreach($conns as $conn)
		{
			$awt->define_data(array(
				"id" => $conn->prop("to"),
				"conn_id" => $conn->id(),
				"name" => $conn->prop("to.name"),
				"edit" => html::href(array(
					"caption" => "Muuda",
					"url" => aw_url_change_var(array($this->elname => $conn->prop("to"))),
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
		$clinst = get_instance($clid);

		$emb = $arr["request"]["cba_emb"];
		
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
					$_fileinf = $_FILES["cba_emb"];
					$filename = $_fileinf["name"][$name];
					$filetype = $_fileinf["type"][$name];
					$tmpname = $_fileinf["tmp_name"][$name];
					$contents = $this->get_file(array(
						"file" => $tmpname,
					));
					// tundub, et polnud sellist faili, eh?
					if (empty($tmpname))
					{
						return false;
					};
					$emb[$name] = array(
						"tmp_name" => $tmpname,
						"type" => $filetype,
						"name" => $filename,
						"contents" => $contents,
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

		$reltype = $arr["prop"]["reltype"];

		$obj_id = $clinst->submit($emb);

		$obj->connect(array(
			"to" => $obj_id,
			"reltype" => $arr["prop"]["reltype"],
		));
	}

	function get_html()
	{
		return "here be releditor";
		//return $this->t->draw();
	}


};
?>
