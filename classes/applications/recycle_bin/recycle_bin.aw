<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/recycle_bin/recycle_bin.aw,v 1.6 2004/11/08 12:31:30 kristo Exp $
// recycle_bin.aw - Prügikast 
/*
@default table=objects
@default group=recycle
@classinfo no_yah=1
@property toolbar type=toolbar store=no no_caption=1
@property recycle_table type=table store=no no_caption=1

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
				$this->do_recycle_table($arr);
			break;
		};
		return $retval;
	}

	function do_recycle_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
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
		));
		
		$table->define_field(array(
			"name" => "restore",
			"caption" => "Tegevus",
		));
		
		$table->define_field(array(
			"name" => "class",
			"caption" => "Objektitüüp",
			"sortable" => 1,
			"width" => 1,
		));
		
		$table->define_field(array(
			"name" => "modified_by",
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
		
		
		$query = "SELECT * FROM objects WHERE status=0";
		$this->db_query($query);
		get_instance("icons");
		while ($row = $this->db_next())
		{
			$table->define_data(array(
				"name" => $row["name"],
				"modified" => $row["modified"],
				"modified_by" => $row["modifiedby"],
				"oid" => $row["oid"],
				"id" => $row["oid"],
				"restore" => html::href(array(
					"caption" => "Taasta",
					"url" => $this->mk_my_orb("restore_object", array("oid" => $row["oid"]), "recycle_bin"),
				)),
				"class" => $classes[$row["class_id"]]["name"],
				"icon" => html::img(array(
					"url" => icons::get_icon_url($row["class_id"]),
				))
			));	
		}
		//set_default_sort_by("modified");
	 	$table->set_default_sorder("desc");
		$table->set_default_sortby("modified");
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
}
?>
