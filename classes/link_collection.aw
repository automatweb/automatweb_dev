<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/link_collection.aw,v 2.8 2002/06/13 23:06:36 kristo Exp $
// link_collection.aw - Lingikogude haldus

class link_collection extends aw_template 
{
	function link_collection($args = array())
	{
		$this->init("link_collection");
		$this->lc_load("linkcollection","lc_linkcollection");
	}

	////
	// !First step is to pick a link collection
	function pick_collection($args = array())
	{
		extract($args);
		$par_obj = $this->get_object($parent);
		$this->read_template("pick_collection.tpl");
		$this->mk_path($par_obj["parent"],sprintf("<a href='%s'>%s</a> / Lisa lingikogu oks",$this->mk_my_orb("list_aliases",array("id" => $parent),"aliasmgr"),$par_obj["name"]));
		// Yes, this is really scary but I need to know where all the link
		// collections are 
		classload("menuedit");
		$awm = new menuedit();

		$awm->make_menu_caches();

		$collections = array();
		// and it gets "better", we access the data from the other class directly
		$firstkey = 0;
		foreach($awm->mar as $key => $val)
		{
			if ($val["links"] > 0)
			{
				$collections[$key] = $val["name"];
				if ($firstkey == 0)
				{
					$firstkey = $key;
				};
			};
		}
		
		$this->vars(array(
			"collections" => $this->picker($firstkey,$collections),
			"reforb" => $this->mk_reforb("pick_branch",array("parent" => $parent,"no_reforb" => 1)),
		));
		return $this->parse();
	}

	////
	// !And next we let the user pick a branch
	function pick_branch($args = array())
	{
		extract($args);
		$this->read_template("pick_branch.tpl");
		classload("menuedit_light");
		$mnl = new menuedit_light();
		$in_collection = true;
		$par_obj = $this->get_object($parent);
		$this->mk_path($par_obj["parent"],sprintf("<a href='%s'>%s</a> / Muuda lingikogu oksa",$this->mk_my_orb("list_aliases",array("id" => $parent),"aliasmgr"),$par_obj["name"]));
		if (not($collection))
		{
			$_tmp = $this->get_object($id);
			$chain = $this->get_object_chain($_tmp["last"]);
			if (is_array($chain))
			{
				while($in_collection && (list($key,$val) = each($chain)))
				{
					// stop processing when we find the actual link collection
					// AND use the "YAH_BEGIN" subtemplate for the FIRST element
					// remember, path elements come in in reverse order
					$in_collection = ($val["links"] > 0) ? false : true;
					if (not($in_collection))
					{
						$collection = $val["oid"];
					};
				};
			};
		};

		$branch_list = $mnl->gen_rec_list(array(
			"start_from" => $collection,
			"add_start_from" => true,
		));
		$c = "";

		if (is_array($branch_list))
		{
			foreach($branch_list as $key => $val)
			{
				if ($id)
				{
					$checked = ($_tmp["last"] == $key) ? "checked" : "";
				}
				else
				{
					$checked = (strlen($c) == 0) ? "checked" : "";
				};

				$this->vars(array(
					"key" => $key,
					"value" => $val,
					// select the first branch by default
					"checked" => $checked,
				));

				$c .= $this->parse("line");
			};
		};
		$this->vars(array(
			"name" => $_tmp["name"],
			"comment" => $_tmp["comment"],
			"line" => $c,
			"reforb" => $this->mk_reforb("submit_branch",array("collection" => $collection,"parent" => $parent,"id" => $id)),
		));
		return $this->parse();
	}

	function submit_branch($args = array())
	{
		extract($args);
		// name, comment, branch

		$par_obj = $this->get_object($parent);

		// CL_LINK_COLLECTION - nimi on täbar... actually it means
		// a branch of a link collection

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"last" => $branch,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"status" => 2,
				"last" => $branch,
				"class_id" => CL_LINK_COLLECTION,
			));
			$this->add_alias($parent,$id);
		};

		// salvestan lingikogu aliase
		return $this->mk_orb("pick_branch",array("id" => $id,"parent" => $parent),"link_collection");
	}


	////
	// !Hoolitseb ntx doku sees olevate extlinkide aliaste parsimise eest (#l2#)
	function parse_alias($args = array())
	{
		extract($args);
		$this->lc_aliases = $this->get_aliases(array(
			"oid" => $oid,
			"type" => CL_LINK_COLLECTION,
		));
		$l = $this->lc_aliases[$matches[3] - 1];
		// see imeb. kivimune.
		global $lcb;
		if ($lcb)
		{
			$l = $this->get_object($lcb);
			$parent = $l["oid"];
		}
		else
		{
			$parent = $l["last"];
		};

		if (not($parent))
		{
			return "";
		};
		$target = $l["target"];
		
		$pobj = $this->get_object($parent);

		$in_collection = true;
		$yah = "";
		$this->read_template("link_collection.tpl");
		// we have to find the oid of the link collection, we are in
		$chain = $this->get_object_chain($pobj["oid"]);
		if (is_array($chain))
		{
			while($in_collection && (list($key,$val) = each($chain)))
			{
				$this->vars(array(
					"url" => $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/".$this->mk_link(array("section" => $oid,"lcb" => $val["oid"])),
					"name" => $val["name"],
				));
				// stop processing when we find the actual link collection
				// AND use the "YAH_BEGIN" subtemplate for the FIRST element
				// remember, path elements come in in reverse order
				$in_collection = ($val["links"] > 0) ? false : true;
				$yah = (($in_collection) ? $this->parse("YAH") : $this->parse("YAH_BEGIN")). $yah;
			};
		};

		// now we create links to categories
		$q = "SELECT oid,name FROM objects WHERE parent = '$parent' AND class_id = '" . CL_PSEUDO . "' AND status = 2 ORDER BY name";
		$this->db_query($q);
		define("SECTION_COLUMNS",2);
		$cnt = 0;
		$cols = array();
		while($row = $this->db_next())
		{
			$cnt++;
			$this->vars(array(
				"name" => $row["name"],
				"url" => $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/".$this->mk_link(array("section" => $oid,"lcb" => $row["oid"])),
			));
			$cols[$cnt % SECTION_COLUMNS] .= $this->parse("SECTIONS_COL");
		};

		$columns = "";

		foreach($cols as $column)
		{
			$this->vars(array(
				"SECTIONS_COL" => $column,
			));

			$columns .= $this->parse("SECTIONS_LINE");
		};

		$q = "SELECT * FROM objects WHERE parent = '$parent' AND class_id = '" . CL_EXTLINK . "' AND status = 2 ";
		$this->db_query($q);
		define("LINK_COLS",1);
		$on_this_line = 0;
		$c = "";
		$_tmp = "";
		classload("extlinks");
		$ec = new extlinks();
		while($row = $this->db_next())
		{
			list($url,$target,$caption) = $ec->draw_link($row["oid"]);
			$this->vars(array(
				"url" => $url,
				"target" => $target,
				"name" => $row["name"],
				"text" => $row["comment"],
			));

			$_tmp .= $this->parse("LINK_COL");
			$on_this_line++;
			if ($on_this_line == LINK_COLS)
			{
				$this->vars(array(
					"LINK_COL" => $_tmp,
				));
				$c .= $this->parse("LINK_LINE");
				$_tmp = "";
				$on_this_line = 0;
			};
		};

		$this->vars(array(
			"SECTIONS_LINE" => $columns,
			"YAH" => $yah,
			"LINK_LINE" => $c,
		));
		return $this->parse();
		// koigepealt siis kysime koigi extrnal linkide aliased. 
	}

};
?>
