<?php

classload("document");
class document_brother extends document
{
	function document_brother()
	{
		$this->document();
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent, LC_DOCUMENT_BROD_DOC);
		$this->read_template("search_doc.tpl");
		$SITE_ID = $this->cfg["site_id"];
		$period = aw_global_get("period");
		if ($s_name != "" || $s_content != "")
		{
			load_vcl("table");
			$t = new aw_table(array(
				"layout" => "generic"
			));
			$t->define_field(array(
				"name" => "name",
				"caption" => "Nimetus",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "parent",
				"caption" => "Asukoht",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "createdby",
				"caption" => "Looja",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "modified",
				"caption" => "Viimati muudetud",
				"type" => "time",
				"format" => "d.m.Y / H:i",
				"sortable" => 1
			));
			$t->define_field(array(
				"name" => "pick",
				"caption" => "Vali see",
			));
			$se = array();
			if ($s_name != "")
			{
				$se[] = " name LIKE '%".$s_name."%' ";
			}
			if ($s_content != "")
			{
				$se[] = " content LIKE '%".$s_content."%' ";
			}
			/* AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) */
			$this->db_query("
				SELECT 
					documents.title as name,
					objects.oid,
					objects.createdby,
					objects.modified
				FROM 
					objects 
					LEFT JOIN documents ON documents.docid=objects.oid 
				WHERE 
					objects.status != 0  AND 
					(
						objects.class_id = ".CL_DOCUMENT." OR 
						objects.class_id = ".CL_PERIODIC_SECTION." 
					) AND 
					".join("AND",$se));
			while ($row = $this->db_next())
			{
				$row["name"] = html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $row["oid"])),
					"caption" => $row["name"]
				));
				$row["pick"] = html::href(array(
					"url" => $this->mk_my_orb("create_bro", array("parent" => $parent, "id" => $row["oid"], "s_name" => $s_name, "s_content" => $s_content,"period" => $period)),
					"caption" => "Vali see"
				));
				$o = obj($row["oid"]);
				$row["parent"] = $o->path_str(array(
					"max_len" => 4
				));
				$t->define_data($row);
			}
			$t->set_default_sortby("name");
			$t->sort_by();
			$this->vars(array("LINE" => $t->draw()));
		}
		else
		{
			$s_name = "%";
			$s_content = "%";
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("new", array("reforb" => 0,"parent" => $parent)),
			"s_name"	=> $s_name,
			"s_content"	=> $s_content
		));
		return $this->parse();
	}

	////
	// !creates a brother of document $id under menu $parent 
	function create_bro($arr)
	{
		extract($arr);
		//check if this brother does not already exist
		$this->db_query("SELECT * FROM objects WHERE parent = $parent AND class_id = ".CL_BROTHER_DOCUMENT." AND brother_of = $id AND status != 0");
		if (!($row = $this->db_next()))
		{
			$obj = $this->get_object($id);
			if ($obj["parent"] != $parent)
			{
				$this->quote(&$obj["name"]);
				$noid = $this->new_object(array(
					"parent" => $parent,
					"class_id" => CL_BROTHER_DOCUMENT,
					"status" => 2,
					"brother_of" => $id,
					"name" => $obj["name"],
					"comment" => $obj["comment"],
					"subclass" => $subclass
				));
			}
		}
		if ($no_header)
		{
			return $noid;
		}
		else
		{
			header("Location: ".$this->mk_my_orb("new", array("parent" => $parent, "s_name" => $s_name,"s_content" => $s_content), "document_brother"));
		}
	}
}
?>
