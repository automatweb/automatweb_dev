<?php
// $Revision: 2.7 $
// docmgr.aw - Document manager
// our first goal is it to make a decent interface to searching
// from documents and their archives.

// then all the document management functions (and I mean editing,
// and other stuff like that should be moved over here to reduce
// the memory requirements of the main document class.

classload("document");
class docmgr extends document 
{
	function docmgr($args = array())
	{
		// call document constructor to initialize settings
		$this->document();
	}

	// displays the search form
	function _search($args = array())
	{
		extract($args);
		$GLOBALS["site_title"] = "Dokumendihaldur";
		$this->read_template("doc_search.tpl");
		$this->_prepare_search_form($args);
		return $this->parse();
	}

	// performs the actual serach
	function search($args = array())
	{
		$this->read_template("doc_search.tpl");
		$this->_prepare_search_form($args);
		$GLOBALS["site_title"] = "Dokumendihaldur";
		$form = $this->parse();
		// I'm a template molester
		$this->read_template("doc_search_results.tpl");
		load_vcl("table");
		if (defined("ARCHIVE"))
		{
			classload("archive");
			$archiver = new archive();
		};
		// create the query string for aw_table
		$query_string = array(); 
		$qstring = array(); // sellega loeme sisse koik vastavad dokumendid
		if (is_array($args["fields"]))
		{
			foreach($args["fields"] as $key => $value)	
			{
				$query_string[] = rawurlencode("fields[$key]") . "=$value";
				$qstring[] = " $key LIKE '%$value%' ";
			};
		};

		// otsime ainult yhest saidist
		$qstring[] = " objects.site_id = " . $this->cfg["site_id"] . " ";

		$t = new aw_table(array(
			"prefix" => "docmgr",
			"tbgcolor" => "#C3D0DC",
		));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

		$t->define_field(array(
			"name" => "oid",
			"caption" => "ID",
			"talign" => "center",
			"nowrap" => 1,
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "title",
			"caption" => "Pealkiri",
			"talign" => "center",
			"nowrap" => 1,
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "archived",
			"caption" => "Arhiivis",
			"talign" => "center",
			"align" => "center",
			"nowrap" => 1,
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "archive_name",
			"caption" => "Nimi arhiivis",
			"talign" => "center",
			"nowrap" => 1,
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"nowrap" => 1,
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"talign" => "center",
			"nowrap" => 1,
			"sortable" => 1,
		));
		
		$t->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"talign" => "center",
			"nowrap" => 1,
			"align" => "center",
			"sortable" => 1,
		));
		

		// kõigepealt tuleks sooritada arhiiviotsing.
		$arc = array();
		$in_archive = array("-1");
		if ($args["fields"] && defined("ARCHIVE"))
		{
			foreach($args["fields"] as $key => $val)
			{
				if (strlen($val) > 0)
				{
					$q = "SELECT * FROM archive WHERE class_id = " . CL_DOCUMENT . " AND name = '$key' AND contents LIKE '%$val%' ORDER BY oid";
					$this->db_query($q);
					while($row = $this->db_next())
					{
						$arc[$row["oid"]][$row["version"]] = $row;
						$in_archive[$row["oid"]] = $row["oid"];
					};
				};
			};
		};

		$_ss = join(" AND ",$qstring);
		if ($_ss != "")
		{
			$q = sprintf("SELECT documents.*,objects.modified AS modified,objects.modifiedby AS modifiedby,objects.status AS status,objects.parent AS parent,objects.meta AS meta FROM documents LEFT JOIN objects ON (documents.docid = objects.oid) WHERE (%s) OR docid IN (%s) ORDER BY oid",$_ss,join(",",$in_archive));
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$link = $this->mk_orb("change",array("id" => $row["docid"],"parent" => $row["parent"]),"document");
				$titlelink = sprintf("<a href='%s'>%s</a>",$link,strip_tags($row["title"]));
				if ($in_archive[$row["docid"]])
				{
					$meta = $archiver->serializer->php_unserialize($row["meta"]);
					foreach($arc[$row["docid"]] as $key => $value)
					{
						$t->define_data(array(
							"oid" => $row["docid"],
							"title" => $titlelink,
							"modified" => $this->time2date($meta["archive"][$key]["timestamp"],4),
							"modifiedby" => $meta["archive"][$key]["uid"],
							"archive_name" => $meta["archive"][$key]["name"],
							"status" => "Deaktiivne",
							"archived" => "<b>jah</b>",
						));
					};
				}
				else
				{
					$t->define_data(array(
						"oid" => $row["docid"],
						"title" => $titlelink,
						"modified" => $this->time2date($row["modified"],4),
						"modifiedby" => $row["modifiedby"],
						"status" => ($row["status"] == 2) ? "Aktiivne" : "Deaktiivne",
						"archived" => "ei",
					));
				};
			};
		}

		$t->sort_by();
	
		$this->vars(array(
			"results" => $t->draw(),
		));

		$retval = "";
		if ($args["fields"])
		{
			$retval = $this->parse();
		};
		$retval .= $form;
		return $retval;
	}

	////
	// !Creates the search form and populates it with data
	// should be called after search.tpl is read
	function _prepare_search_form($args = array())
	{
		$fieldnames = array_flip($this->knownfields);
		foreach($this->archive_fields as $afield)
		{
			$this->vars(array(
				"caption" => $fieldnames[$afield],
				"name" => $afield,
				"value" => $args["fields"][$afield],
			));

			$c .= $this->parse("field");
		};

		$this->vars(array(
			"field" => $c,
			"reforb" => $this->mk_reforb("search",array("no_redir" => 1)),
			"search_archive" => checked($args["search_archive"]),
		));
	}
		
};
?>
