<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/author.aw,v 1.1 2004/04/12 13:34:38 duke Exp $
// author.aw - Autori artiklid 
/*

@classinfo syslog_type=ST_AUTHOR no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property limit type=textbox 
@caption Mitu viimast

*/

// esimene asi - näitamisviis

// võiks saada määrata mitmest viimasest perioodist lugusid võetakse?

class author extends class_base
{
	function author()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/author",
			"clid" => CL_AUTHOR
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {
			case "limit":
				$data["value"] = (int)$data["value"];
				break;

		}
		return $retval;
	}	

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$par_obj = new object($ob->parent());
		$this->lim = (int)$ob->prop("limit");
		if (empty($this->lim))
		{
			$this->lim = 10;
		};
		return $this->author_docs($par_obj->name());
	}

	function get_author_doc_ids($arr)
	{
		$aname = $arr["author"];
		$_lim = $this->lim;
		if ($_lim)
		{
			$lim = "LIMIT ".$_lim;
		}

		$perstr = "";

		if (aw_ini_get("search_conf.only_active_periods"))
		{
			$pei = get_instance("period");
			$plist = $pei->period_list(0,false,1);
			$perstr = " and objects.period IN (".join(",", array_keys($plist)).")";
		}

		// get documents from active periods only
		$sql = "SELECT docid,title,objects.parent FROM documents
				LEFT JOIN objects ON objects.oid = documents.docid
				LEFT JOIN objects AS objects2 ON objects.parent = objects2.oid
				WHERE objects2.status != 0 AND author = '$aname' AND objects.status = 2 $perstr
				ORDER BY objects.created DESC $lim";
		$ids = array();
		$this->db_query($sql);
		while($row = $this->db_next())
		{
			$ids[] = $row["docid"];
		};
		return $ids;
	}

	////
	// !This will list all documents created by an author
	function author_docs($author)
	{
		$lsu = aw_ini_get("menuedit.long_section_url");
		//$_lim = aw_ini_get("document.max_author_docs");

		$ids = $this->get_author_doc_ids(array(
			"author" => $author,
		));

		$this->read_template("show.tpl");
		$idarr = join(",",$ids);

		$comm_q = "SELECT count(*) AS cnt,board_id FROM comments
					WHERE board_id IN ($idarr) GROUP BY board_id";
		$comm_counts = array();
		$this->db_query($comm_q);
		while($row = $this->db_next())
		{
			$comm_counts[$row["board_id"]] = $row["cnt"];
		};

		$perinst = get_instance("period");

		foreach($ids as $docid)
		{
			$num_comments = !empty($comm_counts[$docid]) ? $comm_counts[$docid] : 0;

			$docobj = new object($docid);
			if ($this->can("view",$docobj->parent()))
			{
				$par = new object($docobj->parent());
			};

			$per_oid = $perinst->get_oid_for_id($docobj->period());
			$per_obj = new object($per_oid);

			if ($lsu)
			{
				$link = $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."/section=".$docid;
			}
			else
			{
				$link = $this->cfg["baseurl"]."/".$docid;
			}

			$this->vars(array(
				"link" => $link,
				"comments" => $num_comments,
				"title" => strip_tags($docobj->name()),
				"topic_name" => $par->name(),
				"period_name" => $per_obj->name(),
				"comm_link" => $this->mk_my_orb("show_threaded",array("board" => $docid),"forum"),
			));
			$hc = "";
			if ($num_comments > 0)
			{
				$hc = $this->parse("HAS_COMM");
			}

			$this->vars(array("HAS_COMM" => $hc));

			$c.=$this->parse("AUTHOR_DOC");
		}
		$this->vars(array(
			"AUTHOR_DOC" => $c,
		));
		return $this->parse();
	}

}
?>
