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
			$se = array();
			if ($s_name != "")
			{
				$se[] = " name LIKE '%".$s_name."%' ";
			}
			if ($s_content != "")
			{
				$se[] = " content LIKE '%".$s_content."%' ";
			}
			$this->db_query("SELECT documents.title as name,objects.oid FROM objects LEFT JOIN documents ON documents.docid=objects.oid WHERE objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) AND (objects.class_id = ".CL_DOCUMENT." OR objects.class_id = ".CL_PERIODIC_SECTION." ) AND ".join("AND",$se));
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"], 
					"id" => $row["oid"],
					"brother" => $this->mk_my_orb("create_bro", array("parent" => $parent, "id" => $row["oid"], "s_name" => $s_name, "s_content" => $s_content,"period" => $period)),
					"change" => $this->mk_my_orb("change", array("parent" => $parent, "id" => $row["oid"]), "document")
				));
				$l.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $l));
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