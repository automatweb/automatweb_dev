<?php

class obj_table_conf extends aw_template
{
	var $data = array(
		"oid" => array("name" => "ID", "type" => "int", "sortable" => true),
		"parent" => array("name" => "parent", "type" => "int", "sortable" => true),
		"name" => array("name" => "Nimi", "type" => "text", "sortable" => true),
		"createdby" => array("name" => "Loodud", "type" => "text", "sortable" => true),
		"created" => array("name" => "Millal loodud", "type" => "time", "sortable" => true),
		"modifiedby" => array("name" => "Muudetud", "type" => "text", "sortable" => true),
		"modified" => array("name" => "Millal muudetud", "type" => "time", "sortable" => true),
		"class_id" => array("name" => "T&uuml;&uuml;p", "type" => "int", "sortable" => true),
		"status" => array("name" => "Aktiivsus", "type" => "int"),
		"lang_id" => array("name" => "Keel", "type" => "int", "sortable" => true),
		"comment" => array("name" => "Kommentaar", "type" => "text", "sortable" => true),
		"jrk" => array("name" => "J&auml;rjekord", "type" => "int"),
		"period" => array("name" => "Periood", "type" => "int", "sortable" => true),
		"alias" => array("name" => "Alias", "type" => "text", "sortable" => true),
		"perioodiline" => array("name" => "Periodic", "type" => "int"),
		"site_id" => array("name" => "Saidi ID", "type" => "int", "sortable" => true),
		"activate_at" => array("name" => "Aktiveeri millal", "type" => "time", "sortable" => true),
		"deactivate_at" => array("name" => "Deaktiveeri millal", "type" => "time", "sortable" => true),
		"autoactivate" => array("name" => "Aktiveeri automaatselt", "type" => "int"),
		"autodeactivate" => array("name" => "Deaktiveeri automaatselt", "type" => "int"),
		"---- actions" => array("name" => "---- actions"),
		"icon" => array("name" => "Ikoon"),
		"link" => array("name" => "Link"),
		"select" => array("name" => "Vali"),
		"change" => array("name" => "Muuda"),
		"delete" => array("name" => "Kustuta"),
		"acl" => array("name" => "Muuda ACLi"),
		"java" => array("name" => "Java menu"),
		"---- use next ones with caution!" => array("name" => "---- use next ones with caution!"),
		"hits" => array("name" => "hits", "type" => "int", "sortable" => true),
		"last" => array("name" => "last", "type" => "text", "sortable" => true),
		"visible" => array("name" => "visible", "type" => "int", "sortable" => true),
		"doc_template" => array("name" => "doc_template", "type" => "int", "sortable" => true),
		"brother_of" => array("name" => "Mille vend", "type" => "int", "sortable" => true),
		"cachedirty" => array("name" => "Cache dirty", "type" => "int", "sortable" => true),
		"metadata" => array("name" => "metadata", "type" => "text", "sortable" => true),
		"meta" => array("name" => "meta", "type" => "text", "sortable" => true),
		"subclass" => array("name" => "Subclass", "type" => "int", "sortable" => true),
		"cachedata" => array("name" => "cachedata", "type" => "text", "sortable" => true),
		"flags" => array("name" => "Flags", "type" => "int", "sortable" => true),
	);

	function obj_table_conf()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("obj_table_conf");
	}

	////
	// !called, when adding a new object 
	// parameters:
	//    parent - the folder under which to add the object
	//    return_url - optional, if set, the "back" link should point to it
	//    alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa objektitabeli konf");
		}
		else
		{
			$this->mk_path($parent,"Lisa objektitabeli konf");
		}
		$this->read_template("change.tpl");

		$this->vars(array(
			"col_id" => 1,
			"cols" => $this->picker(" ",$this->mk_col_picker()),
			"ord" => 0,
			"idx" => 0
		));

		$this->vars(array(
			"EL" => $this->parse("EL")
		));

		$this->vars(array(
			"COLUMN_HEADER" => $this->parse("COLUMN_HEADER"),
			"COLUMN" => $this->parse("COLUMN"),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
		));
		return $this->parse();
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_OBJ_TABLE_CONF
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		$cl = array();
		if (is_array($cols))
		{
			foreach($cols as $clid => $cldat)
			{
				$rd = array();
				if (is_array($cldat["col"]))
				{
					foreach($cldat["col"] as $idx => $colname)
					{
						if (trim($colname) != "")
						{
							$rd[$idx] = $colname;
						}
					}
				}
				if (count($rd) > 0)
				{
					$cldat["col"] = $rd;
					$cl[$clid] = $cldat;
				}
			}
		}

		uasort($cl, create_function('$a,$b','if ($a["ord"] > $b["ord"]) { return 1; } if ($a["ord"] < $b["ord"]) { return -1; } return 0;'));

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "cols",
			"value" => $cl
		));

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "sep",
			"value" => $sep
		));
		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda objektitabeli konfi");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda objektitabeli konfi");
		}
		$this->read_template("change.tpl");

		$cl = "";
		$maxclid = $mxord = 0;
		if (is_array($ob["meta"]["cols"]))
		{
			foreach($ob["meta"]["cols"] as $colid => $coldata)
			{
				$els = "";
				$mxidx = 0;
				$this->vars(array(
					"col_id" => $colid,
					"title" => $coldata["title"],
					"ord" => $coldata["ord"],
					"sortable" => checked($coldata["sortable"])
				));
				if (is_array($coldata["col"]))
				{
					foreach($coldata["col"] as $idx => $el)
					{
						$this->vars(array(
							"idx" => $idx,
							"cols" => $this->picker($el, $this->mk_col_picker()),
						));
						$els.=$this->parse("EL");
						$mxidx = max($idx, $mxidx);
					}
				}
				$this->vars(array(
					"idx" => $mxidx+1,
					"cols" => $this->picker(" ",$this->mk_col_picker())
				));
				$els.=$this->parse("EL");
				$this->vars(array(
					"EL" => $els
				));

				$cl.=$this->parse("COLUMN");
				$maxclid = max($maxclid, $colid);
				$mxord = max($mxord, $coldata["ord"]);
			}
		}

		$this->vars(array(
			"col_id" => $maxclid+1,
			"cols" => $this->picker(" ", $this->mk_col_picker()),
			"ord" => $mxord+1,
			"title" => "",
			"idx" => 1,
		));
		$this->vars(array(
			"EL" => $this->parse("EL")
		));
		$cl.=$this->parse("COLUMN");

		$this->vars(array(
			"COLUMN" => $cl,
			"name" => $ob["name"],
			"sep" => $ob["meta"]["sep"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		return aw_serialize($row);
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = unserialize($str);
		$row["parent"] = $parent;
		$id = $this->new_object($row);
		return true;
	}

	////
	// !returns an array of column name => description for objects table
	function mk_col_picker()
	{
		$ret = array(" " => " ");
		foreach($this->data as $tblc => $tbld)
		{
			$ret[$tblc] = $tbld["name"];
		}
		return $ret;
	}

	////
	// !initializes vcl table $tbl_ref to be the table defined in table conf $id
	function init_table($id, &$tbl_ref)
	{
		$this->ob = $this->get_object($id);
		if (is_array($this->ob["meta"]["cols"]))
		{
			foreach($this->ob["meta"]["cols"] as $clid => $cldat)
			{
				// pick the first element's type as the type for the column
				reset($cldat["col"]);
				list(,$clname) = each($cldat["col"]);

				$row = array(
					"name" => "col_".$clid,
					"caption" => ($cldat["title"] == "" ? $clname : $cldat["title"]),
					"talign" => "center",
					"align" => "center",
					"sortable" => $cldat["sortable"],
					"numeric" => ($this->data[$clname]["type"] == "int" || $this->data[$clname]["type"] == "time"),
				);
				if ($this->data[$clname]["type"] == "time")
				{
					$row["type"] = "time";
					$row["format"] = "d.m.y / H:i";
				}
				$tbl_ref->define_field($row);
			}
		}
	}

	function table_row($row, &$tbl_ref)
	{
		$dat = array();
		foreach($this->ob["meta"]["cols"] as $clid => $cldat)
		{
			$str = array();
			foreach($cldat["col"] as $idx => $colname)
			{
				$str[] =$row[$colname];
			}
			$dat["col_".$clid] = join($this->ob["meta"]["sep"], $str);
		}
		$tbl_ref->define_data($dat);
	}
}
?>