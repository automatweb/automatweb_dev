<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/classificator.aw,v 1.1 2004/11/07 11:47:38 kristo Exp $

/*

@classinfo syslog_type=ST_CLASSIFICATOR relationmgr=yes


@default table=objects
@default group=general

@property comment type=textarea cols=50 rows=5 field=comment
@caption Kommentaar

@default field=meta
@default method=serialize

@property folders type=relpicker reltype=RELTYPE_FOLDER multiple=1
@caption Kus kehtib

@property clids type=select multiple=1 
@caption Klassid millele kehtib

@reltype FOLDER value=1 clid=CL_MENU
@caption hallatav kataloog

*/

class classificator extends class_base
{
	function classificator()
	{
		$this->init(array(
			'clid' => CL_CLASSIFICATOR
		));
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		classload("aliasmgr");
		if ($prop['name'] == "clids")
		{
			$prop['options'] = aliasmgr::get_clid_picker();
		}

		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = new object($id);
		$this->db_query("DELETE FROM classificator2menu WHERE clf_id = '".$id."'");
		$arr = new aw_array($ob->prop("folders"));
		foreach($arr->get() as $_fid => $_tt)
		{
			$_arr = new aw_array($ob->prop('clids'));
			foreach($_arr->get() as $clid)
			{
				// so how do I use storage for queries like this? -- duke
				$this->db_query("INSERT INTO classificator2menu(menu_id, class_id, clf_id) VALUES('".$_tt."','".$clid."','".$id."')");
			}
		}
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		if ($arr["view"])
		{
			$this->view = 1;
		};
		if($prop["recursive"] == 1)
		{
			$this->recursive = 1;
		}

		$ch_args = array(
			"clid" => $arr["clid"],
			"name" => $prop["name"],
			"obj_inst" => $arr["obj_inst"],
		);

		if (is_oid($prop["object_type_id"]))
		{
			$ch_args["object_type_id"] = $prop["object_type_id"];
		};
		
		list($choices,$name,$use_type) = $this->get_choices($ch_args);

		$selected = false;
		$connections = array();

		if ($prop["store"] == "connect")
		{
			if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
			{
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => $prop["reltype"],
				));

				foreach($conns as $conn)
				{
					$selected = $conn->prop("to");
					$connections[$selected] = $selected;
				};
			}
			else
			{
				// try to figure out values from some place else
				$connections = $prop["value"];
			};


			if (empty($prop["value"]))
			{
				if ($use_type == "checkboxes" || ($use_type == "select" && $prop["multiple"] == 1) || $use_type == "mselect")
				{
					$prop["value"] = $connections;
				}
				else
				{
					$prop["value"] = $selected;
				};
			};
		};

		if (!empty($name))
		{
			$prop["caption"] = $name;
			// so I know that these are object in that array
		};
		if (empty($use_type))
		{
			$use_type = $prop["mode"];
		};
		if ($this->view)
		{
			$use_type = "view";
		}
		switch($use_type)
		{
			case "checkboxes":
				$prop["type"] = "chooser";
				$prop["multiple"] = 1;
				$prop["options"] = $choices->names();
				break;

			case "radiobuttons":
				$prop["type"] = "chooser";
				$prop["options"] = $choices->names();
				break;

			case "mselect":
				$prop["type"] = "select";
				$prop["multiple"] = 1;
				$prop["options"] = $choices->names();
				break;

			case "view":
				$prop["options"] = $choices->names();
				break;

			default:
				$prop["type"] = "select";
				$prop["options"] = array("" => "") + $choices->names();
		};

		global $XX5;
		if ($XX5)
		{
			arr($prop);
		};

		return array($prop["name"] => $prop);
		// well, that was pretty easy. Now I need a way to tell the bloody classificator, that
		// it should use connections instead of field. And what could be easier than doing
		// it where the classificator is defined. ajee!
	}

	// this will eventually replace delayed vcl property thingie
	function _get_vcl_property($arr)
	{
		print "siin ei ole kala";


	}

	function get_choices($arr)
	{
		// needs clid
		// needs $property name

		$ot = get_instance(CL_OBJECT_TYPE);
		if (isset($arr["object_type_id"]))
		{
			$ff = $arr["object_type_id"];
		}
		else
		{
			$ff = $ot->get_obj_for_class(array(
				"clid" => $arr["clid"],
			));
		};
		//if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
		if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->meta("object_type"))) 
		{
			$custom_ff = $arr["obj_inst"]->meta("object_type");
			if (is_oid($custom_ff))
			{
				$ff = $custom_ff;
			};
		};

		$oft = new object($ff);
		$clf = $oft->meta("classificator");
		
		$name = $arr["name"];
		// if name is formatted like userdata[uservar1], convert it to just uservar1
		if (false !== strpos($name,"["))
		{
			$name = substr($name,strpos($name,"[")+1,-1);
		};

		$clf_type = $oft->meta("clf_type");
		$use_type = $clf_type[$name];

		// XXX: implement some error checking


		$ofto = new object($clf[$name]);

		$parent = is_oid($ofto->id()) ? $ofto->id() : -1;
		if($this->recursive == 1)
		{
			$asd = new object_tree(array(
				"parent" => $parent,
				"class_id" => CL_META,
				"lang_id" => array(),
			));
			$olx = $asd->to_list();
		}
		else
		{
			$olx = new object_list(array(
				"parent" => $parent,
				"class_id" => CL_META,
				"lang_id" => array(),
			));
		}

		return array($olx,$ofto->name(),$use_type);

	}

	function process_vcl_property($arr)
	{
		$property = $arr["prop"];

		if ($property["store"] != "connect")
		{
			return false;
		};
		$items = new aw_array($property["value"]);
		$connections = array();

		if (is_oid($arr["obj_inst"]->id()))
		{
			// first I need a list of old connections.
			$oldconns = $arr["obj_inst"]->connections_from(array(
				"type" => $property["reltype"],
			));
			foreach($oldconns as $conn)
			{
				$connections[$conn->prop("to")] = $conn->prop("to");
			};
		};

		list($choices,,) = $this->get_choices(array(
			"clid" => $arr["clid"],
			"name" => $property["name"],
		));

		$ids = $this->make_keys($choices->ids());

		// I need to list the choices
		foreach($items->get() as $item)
		{
			// skip invalid items
			if (empty($ids[$item]))
			{
				continue;
			};
			if (is_oid($item))
			{
				// create the connection if it didn't exist
				if (empty($connections[$item]))
				{
					//print "connecting to $item with type " . constant($property["reltype"]) . "<br>";
					$arr["obj_inst"]->connect(array(
						"to" => $item,
						"reltype" => constant($property["reltype"]),
					));
				};
				unset($connections[$item]);

			};
		};

		//print "1 = <br>";
		//arr($connections);

		if (sizeof($connections) > 0)
		{
			foreach($connections as $to_remove)
			{
				//print "disconnecting from $to_remove<br>";
				$arr["obj_inst"]->disconnect(array(
					"from" => $to_remove,
				));
			};
		};


			// XXX: would be nice if connect would recognize symbolic reltypes
			// and this belongs to some place else, don't you think so?
	}

	////
	// !needs name and clid as arguments
	function get_options_for($arr)
	{
		if (empty($arr["name"]))
		{
			return false;
		};

		if (empty($arr["clid"]))
		{
			return false;
		};

		$cfgu = get_instance("cfg/cfgutils");

		$props = $cfgu->load_properties(array(
			"clid" => $arr["clid"],
			"filter" => array("name" => $arr["name"]),
		));

		if (is_oid($arr["object_type"]))
		{
			$active_object_id = $arr["object_type"];
		}
		else
		{
			$ot = get_instance(CL_OBJECT_TYPE);
			$active_object_id = $ot->get_obj_for_class(array(
				"clid" => $arr["clid"],
			));
		
			if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
			{
				$custom_ff = $arr["obj_inst"]->meta("object_type");
				if (is_oid($custom_ff))
				{
					$active_object_id = $custom_ff;
				};
			};
		}

		$c_obj = new object($active_object_id);
		$clinf = $c_obj->meta("classificator");

		$items = new object_list(array(
			"parent" => $clinf[$arr["name"]],
			"class_id" => CL_META,
			"lang_id" => array(),
			"sort_by" => "objects.jrk"
		));

		return $items->names();
	}

	////
	// !returns a list of id => name of classificators for specified folder/clid combo
	// parameters:
	//	clid - class id 
	//	parent - folder
	function get_clfs($arr)
	{
		extract($arr);
		if ($add_empty)
		{
			$ret = array("0" => "");
		}
		else
		{
			$ret = array();
		}

		$pt = obj($parent);
		$ch = $pt->path();
		foreach($ch as $o)
		{
			$id = $o->id();
			$name = $o->name();
			$found = false;
			$this->db_query("
				SELECT 
					o.name as name,o.oid as oid 
				FROM 
					classificator2menu c
					LEFT JOIN objects o ON o.oid = c.clf_id
				WHERE 
					c.class_id = '$clid' AND 
					c.menu_id = '$id'
			");
			while($row = $this->db_next())
			{
				$found = true;
	 			$ret[$row["oid"]] = $row["name"];
			}

			if ($found)
			{
				return $ret;
			}
		}

		return $ret;
	}
}
?>
