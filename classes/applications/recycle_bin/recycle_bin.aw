<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/recycle_bin/recycle_bin.aw,v 1.9 2005/01/18 11:22:59 kristo Exp $
// recycle_bin.aw - Prügikast 
/*
@default table=objects
@default group=recycle
@classinfo no_yah=1
@property toolbar type=toolbar store=no no_caption=1
@property recycle_table type=text store=no no_caption=1

@groupinfo recycle submit=no caption="Prügikast"
*/
class recycle_bin extends class_base
{
	function recycle_bin()
	{
		$this->init(array(
			"clid" => CL_RECYCLE_BIN,
		));
	}
	
	function callback_mod_tab($arr)
	{
		if($arr["id"] == "general")
		{
			return false;
		}
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "toolbar":
				$this->do_toolbar(&$arr);
			break;
			case "recycle_table":
				$prop["value"] = $this->do_recycle_table($arr);
			break;
		};
		return $retval;
	}

	function do_recycle_table($arr)
	{
		//$table = &$arr["prop"]["vcl_inst"];
		classload("vcl/table");
		$table = new aw_table();
		
		$table->define_field(array(
			"name" => "icon",
			"caption" => "",
			"width" => 15,
		));
		
		$table->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
		));
		
		$table->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"sortable" => 1,
			"width" => 50,
			"numeric" => 1
		));
		
		$table->define_field(array(
			"name" => "restore",
			"caption" => "Tegevus",
		));
		
		$table->define_field(array(
			"name" => "class_id",
			"caption" => "Objektitüüp",
			"sortable" => 1,
			"width" => 1,
		));
		
		$table->define_field(array(
			"name" => "modifiedby",
			"caption" => "Kustutaja",
			"sortable" => "1",
			"width" => 80,
			"align" => "center",
		));
		
		$table->define_field(array(
			"name" => "modified",
			"caption" => "Kustutatud",
			"sortable" => "1",
			"width" => 100,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.y - H:m:s",
			"align" => "center",
		));
		
		$table->define_chooser(array(
    		"name" => "mark",
    		"field" => "id",
    		"caption" => "Vali",
		));
	
		$classes = aw_ini_get("classes");
		
		get_instance("icons");

		$cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM objects WHERE status=0 ", "cnt");
		
		if ($arr["request"]["sortby"] == "")
		{
			$arr["request"]["sortby"] = "modified";
		}

		if ($arr["request"]["sort_order"] == "")
		{
			$arr["request"]["sort_order"] = "desc";
		}

		$ob = " ORDER BY ".$arr["request"]["sortby"]." ".$arr["request"]["sort_order"];

		$lim = "LIMIT ".($arr["request"]["ft_page"] * 100).",".(100);

		$query = "SELECT * FROM objects WHERE status=0 AND site_id = ".aw_ini_get("site_id")." $ob ".$lim;
		$this->db_query($query);
		$rows = array();
		while ($row = $this->db_next())
		{
			$rows[$row["oid"]] = $row;
		}

		$paths = $this->_get_paths($rows);
		
		foreach($rows as $row)
		{
			$table->define_data(array(
				"name" => $row["name"],
				"modified" => $row["modified"],
				"modifiedby" => $row["modifiedby"],
				"oid" => $row["oid"],
				"id" => $row["oid"],
				"restore" => html::href(array(
					"caption" => "Taasta",
					"url" => $this->mk_my_orb("restore_object", array("oid" => $row["oid"]), "recycle_bin"),
				)),
				"class_id" => $classes[$row["class_id"]]["name"],
				"icon" => html::img(array(
					"url" => icons::get_icon_url($row["class_id"]),
					"alt" => $paths[$row["oid"]],
					"title" => $paths[$row["oid"]]
				))
			));	
		}
		//set_default_sort_by("modified");
	 	$table->set_default_sorder("desc");
		$table->set_default_sortby("modified");

		$table->sort_by();		

		return $table->draw_text_pageselector(array(
			"d_row_cnt" => $cnt,
			"records_per_page" => 100
		)).$table->draw();
	}
	
	function do_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
    		"name" => "save",
    		"img" => "restore.gif",
    		"tooltip" => "Taasta valitud objektid",
    		"action" => "restore_objects",
    	));
    	$tb->add_button(array(
    		"name" => "refresh",
    		"img" => "refresh.gif",
    		"tooltip" => "Uuenda",
    		"url" => aw_url_change_var(array()),
    	));
    	$tb->add_button(array(
    		"name" => "delete",
    		"img" => "delete.gif",
    		"tooltip" => "Kustuta",
    		"action" => "final_delete",
			"confirm" => "Kas olete 100% kindel et soovite valitud objekte l&otilde;plikult kustutada?"
    	));
	}

	/**
		@attrib name=restore_object all_args=1
	**/
	function restore_object($arr)
	{
		$query = "UPDATE objects SET status=1 WHERE oid =".$arr['oid'];
		$this->db_query($query);
		// clear cache
		$c = get_instance("cache");
		$c->file_invalidate_regex(".*");
		
		return $this->mk_my_orb("change", array("group" => "recycle"), "recycle_bin");
	}
	
	/**
		@attrib name=restore_objects
	**/
	function restore_objects($arr)
	{
		if($arr["mark"])
		{
			foreach($arr["mark"] as $oid)
			{
				$query = "UPDATE objects SET status=1 WHERE oid=$oid";
				$this->db_query($query);
			}
		}

		// clear cache
		$c = get_instance("cache");
		$c->file_invalidate_regex(".*");

		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]), $arr["class"]);	
	}

	function _get_paths($rows)
	{
		$o2n = array();
		foreach($rows as $row)
		{
			$o2n[$row["oid"]] = array($row["name"], $row["parent"]);
			$o2n[$row["parent"]] = array(NULL, NULL);
		}

		while ($this->_fetch($o2n))
		{
			;
		}

		$ret = array();
		foreach($rows as $row)
		{
			$pt = array();
			$id = $o2n[$row["oid"]][1];
			while ($o2n[$id][1] && $id != $this->cfg["admin_rootmenu2"])
			{
				$pt[] = $o2n[$id][0];
				$id = $o2n[$id][1];
			}
			$ret[$row["oid"]] = join(" / ", array_reverse($pt));
		}
		return $ret;
	}

	function _fetch(&$o2n)
	{
		$ids = array();
		foreach($o2n as $id => $n)
		{
			if ($n[0] === NULL && $id)
			{
				$ids[] = $id;
			}
		}

		if (!count($ids))
		{
			return false;
		}

		$sql = "SELECT oid,name,parent FROM objects WHERE oid in (".join(",", $ids).")";
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			$o2n[$row["oid"]] = array($row["name"], $row["parent"]);
			if ($row["parent"] && !isset($o2n[$row["parent"]]))
			{
				$o2n[$row["parent"]] = array(NULL,NULL);
			}
		}
		return true;
	}

	/**

		@attrib name=final_delete

	**/
	function final_delete($arr)
	{
		$cl = aw_ini_get("classes");

		foreach(safe_array($arr["mark"]) as $id)
		{
			// get class
			$clid = $this->db_fetch_field("SELECT class_id FROM objects WHERE oid = '$id'", "class_id");
			
			// load props by clid
			$file = $cl[$clid]["file"];
			if ($clid == 29)
			{
				$file = "doc";
			}

			list($properties, $tableinfo, $relinfo) = $GLOBALS["object_loader"]->load_properties(array(
				"file" => $file,
				"clid" => $clid
			));

			$tableinfo = safe_array($tableinfo);
			$tableinfo["objects"] = array(
				"index" => "oid"
			);
			foreach($tableinfo as $tbl => $inf)
			{
				$sql = "DELETE FROM $tbl WHERE $inf[index] = '$id' LIMIT 1";
				$this->db_query($sql);
			}
		}

		return $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"]
		));	
	}
}
?>
