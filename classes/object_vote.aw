<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/object_vote.aw,v 2.9 2004/06/26 10:03:19 kristo Exp $

class object_vote extends aw_template
{
	function object_vote($args = array())
	{
		$this->init("documents");
	}

	////
	// !Lisab uue objektiklastri, kroonika/seltskonna puhul siis lubab valida perioodi
	function add_cluster($args = array())
	{
		extract($args);
		$this->read_template("add_cluster.tpl");
		$per_oid = $this->cfg["per_oid"];
		$dbp = get_instance("period",$per_oid);
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
		$dbp = get_instance("period",$per_oid);
		$rec = $dbp->get($period);
		$name = $rec["description"];

		$o = obj();
		$o->set_parent($per_oid);
		$o->set_name($name);
		$o->set_period($period);
		$o->set_class_id(CL_OBJECT_VOTE);
		$oid = $o->save();
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
		$tmp = obj($id);
		$meta = $tmp->meta("object_vote");
		$check = $meta["check"];
		$jrk = $meta["jrk"];
		$cluster = obj($id);
		$q = "SELECT *,objects.* FROM documents LEFT JOIN objects ON (documents.docid = objects.oid) WHERE site_id = '$SITE_ID' AND objects.period = '".$cluster->period()."' AND status = 2";
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
		
		$o = obj($id);
		$o->set_meta("object_vote", $block);
		$o->save();

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
		$tmp = obj($id);
		$meta = $tmp->meta();

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

		$tmp = obj($id);
		$xmeta = $tmp->meta("votes");

		$votes = $xmeta;
		$votes[$args["vote"]] += 1;
		$votes["total"] += 1;

		$o = obj($id);
		$o->set_meta("votes", $votes);
		$o->save();

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
