<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/search.aw,v 2.1 2002/09/25 16:36:40 duke Exp $
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
                );
		$_obj = $args["obj"];
		$real_fields = array_merge($defaults,$args);
		$this->quote($real_fields);
		$this->sub_merge = 1;
		extract($real_fields);
		$this->read_template("objects.tpl");
		$c = "";
		$table = "";
	
		// create an instance of a object for callbacks 
		if ($args["obj"])
		{
			$_obj = get_instance($args["obj"]);
		};

		if (is_object($_obj) && method_exists($_obj,"_gen_s_path"))
		{
			list($path_parent,$path_text) = $_obj->_gen_s_path($args);
		}
		else
		{
			$path_parent = 0;
			$path_text = "Otsing";
		};

		$this->mk_path($path_parent,$path_text);

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

			if ($partcount == 0)
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
				if (is_object($_obj) && method_exists($_obj,"_gen_s_chlink"))
				{
					$row["change"] = $_obj->_gen_s_chlink($args + $row);
				}
				else
				{
					$row["change"] = "<a href='" . $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $this->cfg["classes"][$row["class_id"]]["file"]) . "'>Muuda</a>";
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


		foreach($real_fields as $key => $val)
		{
			$tpl = "f_" . $key;
			$mkey = "_get_s_" . $key;
			if (is_object($_obj) && (method_exists($_obj,$mkey)))
			{
				$value = $_obj->$mkey($val);
			}
			else
			if (method_exists($this,$mkey))
			{
				$value = $this->$mkey($val);
			}
			else
			{
				$value = $val;
			};

			$this->vars(array(
				$key => $value,
			));

			$c .= $this->parse($tpl);
		};

		$this->vars(array(
			"table" => $table,
			"reforb" => $this->mk_reforb("search",array("no_reforb" => 1,"search" => 1,"obj" => $args["obj"],"docid" => $docid)),
		));

		return $this->parse();
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
		return $this->picker($val,$tar);
	}

	function _get_s_parent($val)
	{
		$li = array("0" => "igalt poolt") + $this->get_menu_list(false,true);
		return $this->picker($val,$li);
	}

	function _get_s_active($val)
	{
		return checked($val);
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
			"caption" => "Muuda",
			"talign" => "center",
		));
	}

}
?>
