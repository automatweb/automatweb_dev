<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/classificator.aw,v 1.11 2004/03/24 12:44:41 duke Exp $

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
		$arr = $ob->prop("folders");
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
		list($choices,$name,$use_type) = $this->get_choices(array(
			"clid" => $arr["clid"],
			"name" => $prop["name"],
		));

		$selected = false;
		$connections = array();

		if ($prop["store"] == "connect")
		{
			if (is_object($arr["obj_inst"]) && is_oid($arr["obj_inst"]->id()))
			{
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => constant($prop["reltype"]),
				));

				foreach($conns as $conn)
				{
					$selected = $conn->prop("to");
					$connections[$selected] = $selected;
				};
			};

			if ($use_type == "checkboxes" || ($use_type == "select" && $prop["multiple"] == 1))
			{
				$prop["value"] = $connections;
			}
			else
			{
				$prop["value"] = $selected;
			};
		};

		$prop["caption"] = $name;
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

			default:
				$prop["type"] = "select";
				$prop["options"] = array("" => "") + $choices->names();
		};

		return array($prop["name"] => $prop);
		// well, that was pretty easy. Now I need a way to tell the bloody classificator, that
		// it should use connections instead of field. And what could be easier than doing
		// it where the classificator is defined. ajee!
	}

	function get_choices($arr)
	{
		// needs clid
		// needs $property name

		$ot = get_instance(CL_OBJECT_TYPE);
		$ff = $ot->get_obj_for_class(array(
			"clid" => $arr["clid"],
		));

		$oft = new object($ff);
		$clf = $oft->meta("classificator");
		$clf_type = $oft->meta("clf_type");
		$use_type = $clf_type[$arr["name"]];

		$ofto = new object($clf[$arr["name"]]);
		$olx = new object_list(array(
			"parent" => $ofto->id(),
			"class_id" => CL_META,
			"lang_id" => array(),
		));

		return array($olx,$ofto->name(),$use_type);

	}

	function process_vcl_property($arr)
	{
		$property = $arr["prop"];

		$items = new aw_array($property["value"]);

		$connections = array();
		if (is_oid($arr["obj_inst"]->id()))
		{
			// first I need a list of old connections.
			$oldconns = $arr["obj_inst"]->connections_from(array(
				"type" => constant($property["reltype"]),
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
					$arr["obj_inst"]->connect(array(
						"to" => $item,
						"reltype" => constant($property["reltype"]),
					));
				};
				unset($connections[$item]);

			};
		};

		if (sizeof($connections) > 0)
		{
			foreach($connections as $to_remove)
			{
				$arr["obj_inst"]->disconnect(array(
					"from" => $to_remove,
				));
			};
		};


			// XXX: would be nice if connect would recognize symbolic reltypes
			// and this belongs to some place else, don't you think so?
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

		$ch = $this->get_object_chain($parent);
		foreach($ch as $id => $name)
		{
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
