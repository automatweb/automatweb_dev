<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/search.aw,v 2.7 2002/10/16 14:35:10 duke Exp $
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
			"location" => 0,
			"createdby" => "",
			"modifiedby" => "",
			"status" => 3,
			"special" => "",
			"alias" => "",
			"redir_target" => "",
		);

		$_obj = $args["obj"];
		$real_fields = array_merge($defaults,$args["s"]);
		$this->quote($real_fields);
		$this->sub_merge = 1;
		extract($args);
		extract($real_fields);
		$c = "";
		$table = "";

		// create an instance of a object for callbacks 
		if ($args["clid"])
		{
			if (gettype($args["clid"]) == "object")
			{
				$this->obj_ref = $args["clid"];
			}
			else
			{
				$_obj = get_instance($args["clid"]);
				if (!$_obj)
				{
					$this->raise_error(ERR_CORE_NO_FILE,"Cannot create an instance of $clid",true);
				};
				$this->obj_ref = $_obj;
				if ($args["clid"] == "document")
				{
					$mn = get_instance("menuedit");
					$parent = (int)$parent;
					$toolbar = $mn->rf_toolbar(array(
						"parent" => $parent,
					));
				};
			};
			$this->read_template("objects.tpl");
		}
		else
		{
			$this->read_template("full.tpl");
			$parent = (int)$parent;
			

			$url = $this->mk_my_orb("search",array("parent" => $parent));
			$this->parent = $parent;
			$this->mk_path($parent,"<a href='$url'>Objektiotsing</a>");
		};

		$this->rescounter = 0;

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

					case "location":
						if ($val == 0)
						{
							$_part = sprintf(" parent IN (%s) ",join(",",array_keys($obj_list)));
						}
						else
						{
							$_part = " parent = '$val' ";
							$partcount++;
						};
						$parts["location"] = $_part;
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
						if (is_array($val))
						{
							$xval = join(",",$val);
							$parts["class_id"] = " class_id IN ($xval) ";
							$partcount++;
						}
						elseif ($val != 0)
						{
							$parts["class_id"] = " class_id = '$val' ";
							$partcount++;
						};
						break;

					case "status":
						if ($val == 3)
						{
							$parts["status"] = " status != 0 ";
						}
						elseif ($val == 2)
						{
							$parts["status"] = " status = 2 ";
						}
						elseif ($val == 1)
						{
							$parts["status"] = " status = 1 ";
						};
						break;

					default:
				};
					
			};

			// first check, whether the caller has a do_search callback,
			// if so, we assume that it knows what it's doing and simply
			// call it.
			$caller_search = $this->search_callback(array("name" => "do_search","args" => $args));

			// if not, use our own (old?) search method
			if ($caller_search === false)
			{
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
			};

			//while($row = $this->db_next())
			while($row = $this->get_next())
			{
				$this->rescounter++;
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
				if (!$args["clid"] || ($args["clid"] == "aliasmgr"))
				{
					$row["name"] = "<a href='" . $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $this->cfg["classes"][$row["class_id"]]["file"]) . "'>$row[name]</a>";
				};

				// trim the location to show only up to 3 levels
				$_loc = explode("/",$row["location"]);
				while(sizeof($_loc) > 3)
				{
					array_shift($_loc);
				};
				$row["location"] = join("/",$_loc);

				if (!$args["clid"])
				{
					$row["change"] = "<input type='checkbox' name='sel[$row[oid]]' value='$row[oid]'>";
				};


				$this->t->define_data($row);
				$results++;
			};

			if ($partcount > 0)
			{
				if (!$sortby)
				{
					$sortby = "name";
				}
				$this->t->sort_by(array("field" => $sortby));

				$table .= "<span style='font-family: Arial; font-size: 12px;'>$results tulemust</span>";
				$table .= $this->t->draw();
				$table .= "<span style='font-family: Arial; font-size: 12px;'>$results tulemust</span>";
			};

			if ($results > 0)
			{
				// I use that in modify_toolbar to determine whether
				// to show the "create object group" and "assign configuration"
				// buttons
				$this->has_results = 1;
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
						$element = "<select name='s[$key]' onChange='$fieldref[onChange]'>$items</select>";
						$caption = $fieldref["caption"];
						break;

					case "radiogroup":

						$element = "";
						foreach($fieldref["options"] as $okey => $oval)
						{
							$checked = checked($okey == $fieldref["selected"]);
							$element .= sprintf("<input type='radio' name='s[%s]' value='%s' %s> %s &nbsp;&nbsp;&nbsp;",$key,$okey,$checked,$oval);
						};

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
						$size = ($fieldref["size"]) ? $fieldref["size"] : 5;
						$element = sprintf("<select multiple size='$size' name='s[%s][]' onChange='%s'>%s</select>",$key,$fieldref["onChange"],$items);
						$caption = $fieldref["caption"];
						break;

					case "textbox":
						$element = "<input type='text' name='s[$key]' size='40' value='$fieldref[value]'>";
						$caption = $fieldref["caption"];
						break;

					case "checkbox":
						$element = "<input type='checkbox' name='s[$key]' $fieldref[checked]>";
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
			$header = $this->search_callback(array("name" => "table_header","args" => $args));
			if (!$header)
			{
				$header = "<form name='searchform'>";
			};
			
			$footer = $this->search_callback(array("name" => "table_footer","args" => $args));
			if (!$footer)
			{
				$footer = "</form>";
			};

			$table = $header . $table . $footer;
		}
		else
		{
			$table = "<form name='resulttable' method='post' action='reforb.aw'>" . $table;
			$args["subaction"] = "kala";
			$args["grpname"] = "noname";
			unset($args["class"]);
			unset($args["action"]);
			unset($args["no_reforb"]);
			if (is_array($args["class_id"]))
			{
				$class_ids = join("\n",map("<input type='hidden' name='class_id[]' value='%d'>",$args["class_id"]));
				unset($args["class_id"]);
			};
			if (is_array($args["s"]))
			{
				$ss = join("\n",map2("<input type='hidden' name='s[%s]' value='%d'>",$args["s"]));
				unset($args["s"]);
			};
			$this->vars(array(
				"redir_target" => $this->_get_s_redir_target(),
				"treforb" => $this->mk_reforb("submit_table",$args) . $class_ids . $ss,
				"ef" => "</form>",
			));
		};

		if (!$args["clid"])
		{
			$mn = get_instance("menuedit");
			
			$toolbar = $mn->rf_toolbar(array(
				"parent" => $parent,
				"callback" => array($this,"modify_toolbar"),
			));

			// override the search button with our own
			$toolbar->add_button(array(
				"name" => "search",
				"tooltip" => "Otsi",
				"url" => "javascript:document.searchform.submit()",
				"imgover" => "search_over.gif",
				"img" => "search.gif",
			));
		};

		$this->table = $table;

		$this->vars(array(
			"table" => $table,
			"toolbar" => (is_object($toolbar)) ? $toolbar->get_toolbar() : "",
			"reforb" => $this->mk_reforb("search",array("no_reforb" => 1,"search" => 1,"obj" => $args["obj"],"docid" => $docid, "parent" => $parent)),
		));

		return $this->parse();
	}

	function modify_toolbar($args)
	{
		if ($this->has_results && is_object($args["toolbar"]))
		{
			$args["toolbar"]->add_separator();

                        $args["toolbar"]->add_button(array(
                                "name" => "mkgroup",
                                "tooltip" => "Moodusta objektigrupp",
                                "url" => "javascript:mk_group('Objektigrupi nimi:')",
                                "imgover" => "save_over.gif",
                                "img" => "save.gif",
                        ));
			
		};
	}

	function get_results()
	{
		return $this->table;
	}

	function modify_fields($args = array(),&$fields)
	{
		// two ways to solve the problem
		// 1 - put the fields into array, so that I can fetch
		// the names of the search fields and remember the values OR
		// 2 - put the names of the search fields into an array
		// s[name] .. and so on, so that I can access them by 
		// accessing the s array. I like the latter a lot more.

		if (!$fields["name"])
		{
			$fields["name"] = array(
				"type" => "textbox",
				"caption" => "Nimi",
				"value" => $args["s"]["name"],
			);
		};

		if (!$fields["comment"])
		{
			$fields["comment"] = array(
				"type" => "textbox",
				"caption" => "Kommentaar",
				"value" => $args["s"]["comment"],
			);
		};
		
		if (!$fields["special"])
		{
			$fields["special"] = array(
				"type" => "checkbox",
				"caption" => "Spetsiaalotsing",
				"checked" => checked($args["s"]["special"]),
			);
		};

		if (!$fields["class_id"])
		{
			$fields["class_id"] = array(
				"type" => "multiple",
				"caption" => "Tüüp",
				"size" => 10,
				"options" => $this->_get_s_class_id($args["s"]["class_id"]),
				"selected" => $args["s"]["class_id"],
				"onChange" => "refresh_page(this)",
			);
		};

		if (!$fields["location"])
		{
			$fields["location"] = array(
				"type" => "select",
				"caption" => "Asukoht",
				"options" => $this->_get_s_parent($args["s"]["location"]),
				"selected" => $args["location"],
			);
		};

		if (!$fields["createdby"])
		{
			$fields["createdby"] = array(
				"type" => "textbox",
				"caption" => "Looja",
				"value" => $args["s"]["createdby"],
			);
		};

		if (!$fields["modifiedby"])
		{
			$fields["modifiedby"] = array(
				"type" => "textbox",
				"caption" => "Muutja",
				"value" => $args["s"]["modifiedby"],
			);
		};

		if (!$fields["status"])
		{
			$fields["status"] = array(
				"type" => "radiogroup",
				"caption" => "Aktiivsus",
				"options" => array("3" => "Kõik","2" => "Aktiivsed","1" => "Deaktiivsed"),
				"selected" => ($args["s"]["status"]) ? $args["s"]["status"] : 3,
			);
		};

		if (!$fields["alias"])
		{
			$fields["alias"] = array(
				"type" => "textbox",
				"caption" => "Alias",
				"value" => $args["s"]["alias"],
			);
		};
		
	}

	// generates contents for the class picker drop-down menu
	function _get_s_class_id($val)
	{
		$tar = array(0 => LC_OBJECTS_ALL) + $this->get_class_picker();
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
			"url" => $this->mk_my_orb("docsearch",array("parent" => $this->parent),"document"),
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
			"nowrap" => "1",
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
			"nowrap" => "1",
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
			"nowrap" => "1",
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

	function get_next()
	{
		if (method_exists($this->obj_ref,"search_callback_get_next"))
		{
			$result = $this->search_callback(array("name" => "get_next"));
		}
		else
		{
			$result = $this->db_next();
		};
		return $result;
	}

	////
	// !Callback funktsioonid
	function search_callback($args = array())
	{
		$prefix = "search_callback_";
		$allowed = array("get_fields","get_query","get_table_defs","modify_data","table_header","table_footer","do_query","get_next");
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
			elseif ($name == "get_next")
			{
				$retval = $this->obj_ref->$name();
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

	function submit_table($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($subaction == "cut")
		{
			$cut_objects = array();
			if (is_array($sel))
			{
				foreach($sel as $oid => $one)
				{
					$cut_objects[$oid] = $oid;
				}
			}
                	aw_session_set("cut_objects",$cut_objects);
		}
		elseif($subaction == "copy")
		{
			$copied_objects = array();
			if (is_array($sel))
			{
				foreach($sel as $oid => $one)
				{
					$r = $this->serialize(array("oid" => $oid));
					if ($r != false)
					{
						$copied_objects[$oid] = $r;
					}
				}
			}
			aw_session_set("copied_objects", $copied_objects);
		}
		elseif($subaction == "delete")
		{
			if (is_array($sel))		
			{
				while (list($ooid,) = each($sel))
				{
					$this->delete_object($ooid);
					$this->delete_aliases_of($ooid);
				}
			};
		}
		elseif($subaction == "mkgroup")
		{
			// aga kuidas otsing kirja panna?
			$id = $this->new_object(array(
                                "parent" => $parent,
                                "name" => $grpname,
                                "class_id" => CL_OBJECT_CHAIN,
				"metadata" => array("objs" => $sel,"s" => $s),
                        ));
		}
		elseif ($subaction == "assign_config")
		{
			$ac = get_instance("cfgobject");
			die($ac->assign($args));
		};

		unset($args["class"]);
		unset($args["action"]);	
		unset($args["subaction"]);
		unset($args["sel"]);
		unset($args["reforb"]);
		$retval = $this->mk_my_orb("search",$args);
		return $retval;
	}

	function assign_config($args = array())
	{
		print "inside cfgobject->assign_config<br>";
		print "<pre>";
		print_r($args);
		print "</pre>";

	}
}
?>
