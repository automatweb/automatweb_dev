<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/search.aw,v 2.3 2001/08/27 14:01:55 duke Exp $
// search.aw - Search Manager
class search extends aw_template
{
	function search($args = array())
	{
		$this->init("search");
	}

	////
	// !Displays the form for adding a new search
	function add($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));


	}

	////
	// !Displays the form for modifying an existing search
	function change($args = array())
	{
		extract($args);
		$this->read_template("change.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));

	}

	////
	// !Submits a new or existing search
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);


		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Shows a search form
	// this is the core for interactive searching. It allows to enter data
	// for search fields and displays the form and/or the results table
	// args:
	// fields(array) - list of fields you want in the search form
	// 	fields = array("name" => "%","comment" => "%");
	// 	obj - reference to caller
	function show($args = array())
	{
		// all the required fields and their default values
		$defaults = array(
			"name" => "",
			"comment" => "",
			"class_id" => 0,
			"parent" => 0,
			"createdby" => "",
			"modifiedby" => "",
			"active" => "",
			"alias" => "",
			"redir_target" => "",
                );
		$_obj = $args["obj"];
		$real_fields = array_merge($defaults,$args);
		$this->quote($real_fields);
		$this->sub_merge = 1;
		extract($real_fields);
		$c = "";
		$table = "";
		$this->read_template("full.tpl");

		// create an instance of a object for callbacks 
		if ($args["clid"])
		{
			$_obj = get_instance($args["clid"]);
			if (!$_obj)
			{
				$this->raise_error(ERR_CORE_NO_FILE,"Cannot create an instance of $clid",true);
			};
			$this->obj_ref = $_obj;
			$this->read_template("objects.tpl");
		}
		else
		{
			$url = $this->mk_my_orb("search",array());
			$this->mk_path(-1,"<a href='$url'>Objektiotsing</a>");
		};

		// perform the actual search
		if ($search)
		{
			$obj_list = $this->get_menu_list(false,true);
			load_vcl("table");
			$this->t = new aw_table(array(
				"prefix" => "search",
			));

			$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

			$this->_init_os_tbl();

			$parts = array();
			$partcount = 0;
			foreach($real_fields as $key => $val)
			{
				switch ($key)
				{
					case "name":
						if ($val)
						{
							$parts["name"] = " name LIKE '%$val%' ";
							$partcount++;
						};
						break;

					case "comment":
						if ($val)
						{
							$parts["comment"] = " comment LIKE '%$val%' ";
							$partcount++;
						};
						break;

					case "parent":
						if ($val == 0)
						{
							$_part = sprintf(" parent IN (%s) ",join(",",array_keys($obj_list)));
						}
						else
						{
							$_part = " parent = '$val' ";
							$partcount++;
						};
						$parts["parent"] = $_part;
						break;

					case "createdby":
						if ($val)
						{
							$val = preg_replace("/\s/","",$val);
							$_val = explode(",",$val);
							$val = join(",",map("'%s'",$_val));
							$parts["createdby"] = " createdby IN ($val) ";
							$partcount++;
						};
						break;

					case "modifiedby":
						if ($val)
						{
							$val = preg_replace("/\s/","",$val);
							$_val = explode(",",$val);
							$val = join(",",map("'%s'",$_val));
							$parts["modifiedby"] = " modifiedby IN ($val) ";
						};
						break;

					case "alias":
						if ($val)
						{
							$parts["alias"] = " alias LIKE '%$val%'";
							$partcount++;
						};
						break;

					case "class_id":
						if ($val)
						{
							$parts["class_id"] = " class_id = '$val' ";
							$partcount++;
						};

					case "active":
						if ($val)
						{
							$parts["active"] = " status = 2 ";
						}
						else
						{
							$parts["active"] = " status != 0 ";
						};
						break;

					default:
				};
					
			};

			$query = $this->search_callback(array("name" => "get_query","args" => $args,"parts" => $parts));

			if ($query)
			{
				$this->db_query($query);
				$partcount = 1;
			}
			elseif ($partcount == 0)
			{
				$table = "<span style='font-family: Arial; font-size: 12px; color: red;'>Defineerige otsingutingimused</span>";
			}
			else
			{
				$where = join(" AND ",$parts);
				$q = "SELECT * FROM objects WHERE $where";
				$this->db_query($q);
			};

			$results = 0;

			while($row = $this->db_next())
			{
				$type = $this->cfg["classes"][$row["class_id"]]["name"];
				$row["icon"] = sprintf("<img src='%s' alt='$type' title='$type'>",get_icon_url($row["class_id"],""));
				if (!$row["name"])
				{
					$row["name"] = "(nimetu)";
				};
				$row["type"] = $type;
				$row["location"] = $obj_list[$row["parent"]];
				$this->search_callback(array(
					"name" => "modify_data",
					"data" => &$row,
					"args" => $args,
				));
				if (!$args["clid"])
				{
					$row["name"] = "<a href='" . $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $this->cfg["classes"][$row["class_id"]]["file"]) . "'>$row[name]</a>";
				};
				$this->t->define_data($row);
				$results++;
			};

			if ($partcount > 0)
			{
				$this->t->sort_by(array("sortby" => $sortby));
				$table = $this->t->draw();
				$table .= "<span style='font-family: Arial; font-size: 12px;'>$results tulemust</span>";
			};

		};

		$fields = array();

		$this->search_callback(array("name" => "get_fields","fields" => &$fields,"args" => $args));

		$this->modify_fields($args,&$fields);
		
		//foreach($real_fields as $key => $val)
		foreach($fields as $key => $val)
		{
			if (is_array($fields[$key]))
			{
				$fieldref = $fields[$key];
				switch($fieldref["type"])
				{
					case "select":
						$items = $this->picker($fieldref["selected"],$fieldref["options"]);
						$element = "<select name='$key' onChange='$fieldref[onChange]'>$items</select>";
						$caption = $fieldref["caption"];
						break;
					
					case "multiple":
						if (is_array($fieldref["selected"]))
						{
							$sel = array_flip($fieldref["selected"]);
						}
						else
						{
							$sel = array();
						};
						//$items = $this->mpicker($fieldref["selected"],$fieldref["options"]);
						$items = $this->mpicker($sel,$fieldref["options"]);
						$element = sprintf("<select multiple size='5' name='%s[]' onChange='%s'>%s</select>",$key,$fieldref["onChange"],$items);
						$caption = $fieldref["caption"];
						break;

					case "textbox":
						$element = "<input type='text' name='$key' size='40' value='$fieldref[value]'>";
						$caption = $fieldref["caption"];
						break;

					case "checkbox":
						$element = "<input type='checkbox' name='$key' $fieldref[checked]>";
						$caption = $fieldref["caption"];
						break;

					default:
						$element = "n/a";
						$caption = "n/a";
						break;
				};

				$this->vars(array(
						"caption" => $caption,
						"element" => $element,
				));

				$c .= $this->parse("field");
			};
		};

		if ($args["clid"])
		{
			$table = "<form name='searchform' method='get'>" . $table . "</form>";
		}
		else
		{
			$this->vars(array(
				"redir_target" => $this->_get_s_redir_target(),
			));
		};

		$this->table = $table;

		$this->vars(array(
			"table" => $table,
			"reforb" => $this->mk_reforb("search",array("no_reforb" => 1,"search" => 1,"obj" => $args["obj"],"docid" => $docid)),
		));

		return $header . $this->parse();
	}


	function get_results()
	{
		return $this->table;
	}

	function modify_fields($args = array(),&$fields)
	{

		if (!$fields["name"])
		{
			$fields["name"] = array(
				"type" => "textbox",
				"caption" => "Nimi",
				"value" => $args["name"],
			);
		};

		if (!$fields["comment"])
		{
			$fields["comment"] = array(
				"type" => "textbox",
				"caption" => "Kommentaar",
				"value" => $args["comment"],
			);
		};

		if (!$fields["class_id"])
		{
			$fields["class_id"] = array(
				"type" => "select",
				"caption" => "Tüüp",
				"options" => $this->_get_s_class_id($args["class_id"]),
				"selected" => $args["class_id"],
				"onChange" => "refresh_page(this)",
			);
		};

		if (!$fields["parent"])
		{
			$fields["parent"] = array(
				"type" => "select",
				"caption" => "Asukoht",
				"options" => $this->_get_s_parent($args["parent"]),
				"selected" => $args["parent"],
			);
		};

		if (!$fields["createdby"])
		{
			$fields["createdby"] = array(
				"type" => "textbox",
				"caption" => "Looja",
				"value" => $args["createdby"],
			);
		};

		if (!$fields["modifiedby"])
		{
			$fields["modifiedby"] = array(
				"type" => "textbox",
				"caption" => "Muutja",
				"value" => $args["modifiedby"],
			);
		};

		if (!$fields["active"])
		{
			$fields["active"] = array(
				"type" => "checkbox",
				"caption" => "Aktiivne",
				"checked" => checked($args["active"]),
			);
		};

		if (!$fields["alias"])
		{
			$fields["alias"] = array(
				"type" => "textbox",
				"caption" => "Alias",
				"value" => $args["alias"],
			);
		};
	}

	// generates contents for the class picker drop-down menu
	function _get_s_class_id($val)
	{
		$tar = array(0 => " " . LC_OBJECTS_ALL);
		reset($this->cfg["classes"]);
		while (list($v,) = each($this->cfg["classes"]))
		{
			$name = $this->cfg["classes"][$v]["name"];
			// skip the objects with no name
			if (strlen($name) > 0)
			{
				$tar[$v] = $this->cfg["classes"][$v]["name"];
			};
		}
		asort($tar);
		return $tar;
	}

	function _get_s_parent($val)
	{
		$li = array("0" => "igalt poolt") + $this->get_menu_list(false,true);
		return $li;
	}

	function _get_s_redir_target()
	{
		$this->vars(array(
			"clid" => 7,
			"url" => $this->mk_my_orb("docsearch",array(),"document"),
		));
		$retval = $this->parse("redir_target");
		$this->vars(array(
			"redir_target" => "",
		));
		return $retval;
	}

	function _init_os_tbl()
	{
		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));

		$this->t->define_field(array(
			"name" => "icon",
			"caption" => "",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
			

		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"sortable" => 1,
		));
			
		$this->t->define_field(array(
			"name" => "type",
			"caption" => "Tüüp",
			"talign" => "center",
			"sortable" => 1,
		));
			
		$this->t->define_field(array(
			"name" => "location",
			"caption" => "Asukoht",
			"talign" => "center",
			"sortable" => 1,
		));
			
		$this->t->define_field(array(
			"name" => "created",
			"caption" => "Loodud",
			"talign" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.y / H:i"
		));

		$this->t->define_field(array(
			"name" => "createdby",
			"caption" => "Looja",
			"talign" => "center",
			"sortable" => 1,
		));
			
		$this->t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.y / H:i"
		));
			
		$this->t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"talign" => "center",
			"sortable" => 1,
		));
		
		$this->t->define_field(array(
			"name" => "change",
			"caption" => "",
			"align" => "center",
			"talign" => "center",
		));
	}

	////
	// !Callback funktsioonid
	function search_callback($args = array())
	{
		$prefix = "search_callback_";
		$allowed = array("get_fields","get_query","get_table_defs","modify_data");
		if (!is_object($this->obj_ref))
		{
			return false;
		};

		// paranoia? maybe. but still, do not let the user use random functions
		// from the caller.
		if (!in_array($args["name"],$allowed))
		{
			$retval = false;
		};
			
		$name = $prefix . $args["name"];

		if (method_exists($this->obj_ref,$name))
		{
			if ($args["data"])
			{
				// data is defined for modify_data and works directly
				// on the data fetched from the database and not on a copy

				// still, if it's done otherwise in the future, return
				// the possible value as well
				$retval = $this->obj_ref->$name(&$args["data"],$args["args"]);
			}
			elseif ($name == "search_callback_get_fields")
			{
				$retval = $this->obj_ref->$name(&$args["fields"],$args["args"]);
			}
			elseif ($name == "search_callback_get_query")
			{
				$retval = $this->obj_ref->$name($args["args"],$args["parts"]);
			}
			else
			{
				$retval = $this->obj_ref->$name($args["args"]);
			};

		}
		else
		{
			$retval = false;
		};

		return $retval;

	}
}
?>
