<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/object_vote.aw,v 2.4 2002/11/07 10:52:24 kristo Exp $

class object_vote extends aw_template
{
	function object_vote($args = array())
	{
		$this->init("documents");
	}

	function list_objects($args = array())
	{
		$this->read_template("list_clusters.tpl");
		$per_oid = $this->cfg["per_oid"];
		$q = "SELECT * FROM objects WHERE parent = '$per_oid' AND class_id = " . CL_OBJECT_VOTE . " AND status != 0 ORDER BY period DESC";
		$this->db_query($q);
		$c = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
				"id" => $row["oid"],
				"title" => $row["name"],
				"checked" => ($row["status"] == 2) ? "checked" : "",
				"class" => ($row["status"] == 2) ? "selected" : "plain",
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"add" => "orb.".$this->cfg["ext"]."?class=object_vote&action=add_cluster",
			"line" => $c,
			"reforb" => $this->mk_reforb("submit_cluster_list",array()),
		));
		return $this->parse();
	}

	function submit_cluster_list($args = array())
	{
		extract($args);
		$per_oid = $this->cfg["per_oid"];
		$q = "UPDATE objects SET status = 1 WHERE parent = '$per_oid' AND class_id = " . CL_OBJECT_VOTE;
		$this->db_query($q);
		$q = "UPDATE objects set status = 2 WHERE oid = $check";
		$this->db_query($q);
		header("Location: " . $this->cfg["baseurl"] . "/automatweb/orb.".$this->cfg["ext"]."?class=object_vote&action=list");
		exit;
	}
		

	////
	// !Lisab uue objektiklastri, kroonika/seltskonna puhul siis lubab valida perioodi
	function add_cluster($args = array())
	{
		extract($args);
		$this->read_template("add_cluster.tpl");
		$per_oid = $this->cfg["per_oid"];
		$dbp = get_instance("periods",$per_oid);
		$active = $dbp->get_active_period();
		$dbp->clist();
		$periods = array();
		while($row = $dbp->db_next())
		{
		 $periods[$row["id"]] = $row["description"];
		};
		$this->vars(array(
			"periods" => $this->picker($active,$periods),
			"reforb" => $this->mk_reforb("submit_add_cluster",array()),
		));
		return $this->parse();
	}

	////
	// !Submitib uue clustri, ning kuvad dokude lisamise vorm
	function submit_add_cluster($args = array())
	{
		extract($args);
		$baseurl = $this->cfg["baseurl"];
		$per_oid = $this->cfg["per_oid"];
		$dbp = get_instance("periods",$per_oid);
		$rec = $dbp->get($period);
		$name = $rec["description"];
		$oid = $this->new_object(array(
			"name" => $name,
			"parent" => $per_oid,
			"period" => $period,
			"class_id" => CL_OBJECT_VOTE,
		));
		$link = "$baseurl/automatweb/orb.".$this->cfg["ext"]."?class=object_vote&action=edit_cluster&id=$oid";
		header("Location: $link");
		print " ";
		exit;
	}

	function edit_cluster($args = array())
	{
		extract($args);
		$SITE_ID = $this->cfg["site_id"];
		$this->read_template("list_contents.tpl");
		$meta = $this->get_object_metadata(array(
			"oid" => $id,
			"key" => "object_vote",
		));
		$check = $meta["check"];
		$jrk = $meta["jrk"];
		$cluster = $this->get_object($id);
		$q = "SELECT *,objects.* FROM documents LEFT JOIN objects ON (documents.docid = objects.oid) WHERE site_id = '$SITE_ID' AND objects.period = '$cluster[period]' AND status = 2";
		$this->db_query($q);
		$c  = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
				"id" => $row["oid"],
				"name" => $row["name"],
				"value" => ($jrk[$row["oid"]]) ? $jrk[$row["oid"]] : 0,
				"checked" => ($check[$row["oid"]]) ? "checked" : "",
				"author" => $row["author"],
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
			"reforb" => $this->mk_reforb("submit_edit_cluster",array("id" => $id)),
		));
		return $this->parse();
	}

	function submit_edit_cluster($args = array())
	{
		extract($args);
		$block = array(
			"check" => $check,
			"jrk" => $jrk,
		);
		
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "object_vote",
			"value" => $block,
		));


		return $this->cfg["baseurl"] . "/automatweb/orb.".$this->cfg["ext"]."?class=object_vote&action=edit_cluster&id=$id";
	}

	function gen_user_html()
	{
		$per_oid = $this->cfg["per_oid"];
		$SITE_ID = $this->cfg["site_id"];
		$mboard = get_instance("msgboard");
		$this->read_template("object_vote.tpl");
		$q = "SELECT * FROM objects WHERE parent = '$per_oid' AND site_id = '$SITE_ID' and status = 2 AND class_id =" . CL_OBJECT_VOTE;
		$this->db_query($q);
		$row = $this->db_next();
		$id = $row["oid"];
		$meta = $this->get_object_metadata(array(
			"oid" => $id,
		));

		$votes = $meta["votes"];
		$check = $meta["object_vote"]["check"];
		$jrk = $meta["jrk"];
		$lx = array();

		foreach($check as $key => $val)
		{
			$lx[$key] = $jrk[$key];
		}

		asort($lx);
		$docs = join(",",array_keys($lx));
		$q = "SELECT * FROM documents WHERE docid IN ($docs)";
		$this->db_query($q);
		$retval = "";
		$c = "";
		$d = get_instance("document");
		while($row = $this->db_next())
		{
			if ($votes["total"] > 0)
			{
				$percent = sprintf("%0.2f",$votes[$row["docid"]] * 100 /  $votes["total"]);
			}
			else
			{
				$percent = "0.00%";
			};

			$lead = preg_replace("/(#)(p)(\d+?)(v|k|p|)(#)/i","",$row["lead"]);
				
			$this->vars(array(
				"id" => $row["docid"],
				"author" => $row["author"],
				"section" => $row["docid"],
				"title" => strip_tags($row["title"]),
				"num_comments" => $mboard->get_num_comments("nl" . $row["docid"]),
				"percent" => $percent,
				"lead" => strip_tags($lead),
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
		));
		return $this->parse();
	}

	function process_vote($args = array())
	{
		extract($args);
		$args["section"] = "nl" . $args["vote"];
		$args["parent"] = 0;
		$mboard = get_instance("msgboard");
		$mboard->submit_add($args);
		$per_oid = $this->cfg["per_oid"];
		$SITE_ID = $this->cfg["site_id"];
		$q = "SELECT * FROM objects WHERE parent = '$per_oid' AND site_id = '$SITE_ID' and status = 2 AND class_id =" . CL_OBJECT_VOTE;
		$this->db_query($q);
		$row = $this->db_next();
		$id = $row["oid"];
		$xmeta = $this->get_object_metadata(array(
			"oid" => $id,
			"metadata" => $row["metadata"],
			"key" => "votes",
		));
		$votes = $xmeta;
		$votes[$args["vote"]] += 1;
		$votes["total"] += 1;
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "votes",
			"value" => $votes,
		));
		if ($args["comment"])
		{
			return true;
		}
		else
		{
			return false;
		};
	}
		
};
?>
