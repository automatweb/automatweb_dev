<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/Attic/cfgobject.aw,v 1.2 2002/11/07 10:52:31 kristo Exp $
// cfgobject.aw - configuration objects
// adds, changes and in general handles configuration objects

class cfgobject extends aw_template
{
	function cfgobject($args = array())
	{
		$this->init("cfgobject");
	}

	////
	// !Adds a new configuration object
	function add($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		$clid = array();
		$l = "";
		$this->get_objects_by_class(array(
			"class" => CL_CFGFORM,
		));
		while($row = $this->db_next())
		{
			$cfgforms[$row["oid"]] = $row["name"];
		};

		$this->mk_path($parent,"Lisa uus konfiobjekt");
		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "add",
                        "tooltip" => "Lisa",
                        "url" => "javascript:document.clform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));

		$this->vars(array(
			"toolbar" => $toolbar->get_toolbar(),
			"toolbar2" => $toolbar->get_toolbar(array("id" => "booyaka")),
			"cfgforms" => $this->picker(-1,array("0" => " -- vali üks -- ") + $cfgforms),
			"line" => $l,
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// !Allows to change the configuration object
	function change($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$this->mk_path($obj["parent"],"Muuda konfiobjekti");

		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Salvesta",
                        "url" => "javascript:document.clform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));
		$toolbar->add_button(array(
                        "name" => "search",
                        "tooltip" => "Vali objektid",
                        "url" => "javascript:show_search()",
                        "imgover" => "search_over.gif",
                        "img" => "search.gif",
                ));
		
		$this->read_template("change.tpl");
		
		// generate lists of objects
		$o = array();
		$cx = $this->get_class_picker(array("field" => "file"));
		if (is_array($obj["meta"]["objects"]))
		{
			$oids = join(",",$obj["meta"]["objects"]);
			$q = "SELECT class_id,oid,name,modified,modifiedby FROM objects WHERE oid IN ($oids)";
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$churl = $this->mk_my_orb("change",array("id" => $row["oid"]),$cx[$row["class_id"]]);
				$this->vars(array(
					"name" => "<a href='$churl'>$row[name]</a>",
					"oid" => $row["oid"],
					"modified" => $this->time2date($row["modified"],2),
					"modifiedby" => $row["modifiedby"],
				));
				
				$o[$cx[$row["class_id"]]] .= $this->parse("objline");
			};
		};

		$cfgformid = (int)$obj["meta"]["cfgform"];
		$c = "";
		if ($cfgformid)
		{
			$html = get_instance("html");
			$cp = $this->get_class_picker(array("index" => "file"));
			$cfgform = $this->get_object($cfgformid);
			$cfgproperties = new aw_array($cfgform["meta"]["properties"]);
			// cycle over all the properties this configuration form has
			foreach($cfgproperties->get() as $clid => $cl_properties)
			{
				$l = "";
				// get_instance is cheap
				$t = get_instance($clid);

				// get the defined properties for each class in the configuraton form
				// to get the caption
				$props = $t->get_properties();

				// create the lines with checkboxes. or smth.
				foreach($cl_properties as $pkey => $val)
				{
					// what to do if the class does not provide that key anymore?
					// should we ignore it?
					$type = $props[$pkey]["type"];
					$name = sprintf("properties[%s][%s]",$clid,$pkey);
					$checked = $obj["meta"]["properties"][$clid][$pkey];
					if ($type == "checkbox")
					{
						$el = $html->checkbox(array(
									"name"  => $name, 
									"value" => 1,
									"checked" => $checked,
						));
					}
					elseif ($type == "select")
					{
						$el = $html->select(array(
								"name" => $name,
								"selected" => $obj["meta"]["properties"][$clid][$pkey],
								"options" => $props[$pkey]["options"],
						));

					}
					elseif ($type == "time_select")
					{
						$el = $html->time_select(array(
								"name" => $name,
								"value" => $obj["meta"]["properties"][$clid][$pkey],
						));

					}
					else
					{
						$el = $html->text(array(
									"name"  => $name,
									"size" => $props[$pkey]["size"],
									"value" => $obj["meta"]["properties"][$clid][$pkey],
						));
					};
					$this->vars(array(
						"pname" => $props[$pkey]["caption"],
						"el" => $el,
					));
					$l .= $this->parse("line");
				};

				$this->vars(array(
					"line" => $l,
					"clname" => $cp[$clid],
					"objline" => $o[$clid],
				));

				$c .= $this->parse("class_container");
			};
		};


		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"toolbar" => $toolbar->get_toolbar(),
			"toolbar2" => $toolbar->get_toolbar(array("id" => "booyaka")),
			"class_container" => $c,
			"oline" => $o,
			"search_url" => $this->mk_my_orb("search",array("id" => $id)),
			"priority" => $obj["meta"]["priority"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Submits the configuration object
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		$clidlist = $this->_remap_classes();
		if ($id)
		{
			$obj = $this->get_object($id);
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"properties" => $properties,
					"priority" => $priority,
				),
			));
			// this is where I need to write the code to update the properties
			// of all the objects that this configuration object affects

			// I have to cycle over all the properties that this configuration
			// object has and calculate the values
			$cfgformid = $obj["meta"]["cfgform"];
			if ($cfgformid)
			{
				$cfgform = $this->get_object($cfgformid);
				$cfgproperties = new aw_array($cfgform["meta"]["properties"]);
			};

			$props_to_set = array();

			foreach($cfgproperties->get() as $clid => $elements)
			{
				$t = get_instance($clid);
				$cl_props = $t->get_properties();
				foreach($elements as $key  => $val)
				{
					if ($properties[$clid][$key])
					{
						$value = $properties[$clid][$key];
					}
					else
					{
						if ($cl_props[$key]["type"] == "checkbox")
						{
							$value = 0;
						}
						else
						{
							$value = "";
						};
					};
					$realclid =  $clidlist[$clid];
					if ($cl_props[$key]["store"] == "table")
					{
						$fields_to_set[$realclid][$key] = $value;
						$tables[$realclid] = $cl_props[$key]["table"];
						$idfields[$realclid] = $cl_props[$key]["idfield"];
					}
					else
					{
						$props_to_set[$realclid][$key] = $value;
					};
				};
			};

			// and now I need to update all the relevant objects
			$objects = $obj["meta"]["objects"];

			if (is_array($objects))
			{
				$oidlist = join(",",$objects);
				$q = "SELECT oid,name,class_id FROM objects WHERE oid IN ($oidlist) ORDER BY class_id";
				$oclist = array();
				$this->db_query($q);
				// first I'll create a list of objects which is sorted by class
				while($row = $this->db_next())
				{
					// table will be written into later on
					$oclist[$row["class_id"]][] = $row["oid"];

					$this->save_handle();

					if ($props_to_set[$row["class_id"]])
                              {
                                    $this->upd_object(array(
                                          "oid" => $row["oid"],
                                          "metadata" => $props_to_set[$row["class_id"]],
                                    ));
                              };

					$this->restore_handle();

				};

				foreach($oclist as $okey => $oitems)
				{
					// update the tables with one query
					if ($fields_to_set[$okey])
					{
						$ref = $fields_to_set[$okey];
						$this->db_update_record(array(
							"table" => $tables[$okey],
							"key" => array($idfields[$okey]  => $oitems),
							"values" => $ref,
						));
					};

				};
			};

		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_CFGOBJECT,
				"metadata" => array(
					"cfgform" => $cfgform,
				),
			));
		};

		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Shows the form for assigning multiple configuration objects to selected objects
	function assign($args = array())
	{
		$this->quote($args);
		extract($args);
		
		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Rakenda",
                        "url" => "javascript:document.assignform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));

		$this->mk_path(-1,"Vali konfiguratsiooniobjektid");
	
		$this->read_template("assign.tpl");


		$this->vars(array(
			"reforb" => $this->mk_reforb("assign",array("no_reforb" => 1,"parent" => $parent)),
			"toolbar" => $toolbar->get_toolbar(),
		));

		return $this->parse();
	}

	function search($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);

		$search = get_instance("search");
		$args["clid"] = "cfgobject";
		$form = $search->show($args);

		$this->read_template("search.tpl");
		$this->mk_path($obj["parent"],"Muuda konfiobjekti");
		
		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Salvesta",
                        "url" => "javascript:document.searchform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));

		$toolbar->add_button(array(
                        "name" => "search",
                        "tooltip" => "Otsi",
                        "url" => "javascript:document.queryform.submit()",
                        "imgover" => "search_over.gif",
                        "img" => "search.gif",
                ));

		$toolbar->add_button(array(
                        "name" => "edit",
                        "tooltip" => "Muuda",
                        "url" => $this->mk_my_orb("change",array("id" => $id)),
                        "imgover" => "edit_over.gif",
                        "img" => "edit.gif",
                ));

		$this->vars(array(
			"toolbar" => $toolbar->get_toolbar(),
			"toolbar2" => $toolbar->get_toolbar(array("id" => "booyaka")),
			"form" => $form,
			"results" => $search->get_results(),
			"reforb" => $this->mk_reforb("search",array("id" => $id,"no_reforb" => 1,"search" => 1)),
		));

		return $this->parse();
	}

	function search_callback_table_header($args = array())
	{
		return "<form name='searchform' method='post' action='reforb.aw'>";
	}
	
	function search_callback_table_footer($args = array())
	{
		$ref = $this->mk_reforb("save_objects",array("id" => $args["id"]));
		$ref .= "</form>";
		return $ref;
	}

	function search_callback_modify_data($row,$args)
	{
		$row["change"] = "<input type='checkbox' name='sel[]' value='$row[oid]'>";
	}

	function save_objects($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$old_objs = $obj["meta"]["objects"];
		$this->upd_object(array(
			"oid" => $id,
			"metadata" => array(
				"objects" => array_merge($old_objs,$sel),
			),
		));
		return $this->mk_my_orb("change",array("id" => $id));
	}

	function _remap_classes($args = array())
	{
		$res = array();
		foreach($this->cfg["classes"] as $id => $data)
		{
			$res[$data["file"]] = $id;
		};
		return $res;
	}

};
?>
