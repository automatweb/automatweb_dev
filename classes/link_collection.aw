<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/link_collection.aw,v 2.3 2001/11/29 21:58:58 duke Exp $
// link_collection.aw - Lingikogude haldus
global $orb_defs;
lc_load("linkcollection");
$orb_defs["link_collection"] = "xml";

class link_collection extends aw_template {
	function link_collection($args = array())
	{
		$this->db_init();
		$this->tpl_init("link_collection");
		global $lc_linkcollection;
		if (is_array($lc_linkcollection))
		{
			$this->vars($lc_linkcollection);
		}
	}

	////
	// !Is 
	function pick_branch($args = array())
	{
		extract($args);
		$par_obj = $this->get_object($parent);
		$this->mk_path($par_obj["parent"],"Lisa lingikogu oks");
		$this->read_template("pick_branch.tpl");
		// Yes, this is really scary but I need to know where all the link
		// collections are
		classload("menuedit");
		$awm = new menuedit();

		$awm->make_menu_caches();

		$collections = array();
		// and it gets "better", we access the data from the other class directly
		foreach($awm->mar as $key => $val)
		{
			if ($val["links"] > 0)
			{
				$collections[$key] = $val["name"];
			};
		}
		
		$this->vars(array(
			"branches" => $this->picker(-1,$collections),
			"reforb" => $this->mk_reforb("submit_branch",array("parent" => $parent)),
		));
		return $this->parse();
	}

	function submit_branch($args = array())
	{
		extract($args);
		// name, comment, branch
		$par_obj = $this->get_object($parent);

		$id = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"comment" => $comment,
			"status" => 2,
			"last" => $branch,
			"class_id" => CL_LINK_COLLECTION,
		));

		$this->add_alias($parent,$id);

		// salvestan lingikogu aliase
		return $this->mk_orb("list_aliases",array("id" => $parent),"aliasmgr");
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
		$target = $l["target"];
		$tobj = $this->get_object($target);
		$parent = $tobj["last"];

		$q = "SELECT * FROM objects WHERE parent = '$parent' AND class_id = '" . CL_EXTLINK . "' AND status = 2 ";
		$this->db_query($q);
		$html = "";
		global $baseurl,$ext;
		while($row = $this->db_next())
		{
			$linksrc = sprintf("%s/indexx.%s?id=%d",$baseurl,$ext,$row["oid"]);
			$html .= "<a href='$linksrc' target='_blank'>$row[name]</a></a> - $row[comment]<br>";
		};
		return $html;
		// koigepealt siis kysime koigi extrnal linkide aliased. 
	}

};
?>
