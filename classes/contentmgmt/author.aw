<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/author.aw,v 1.5 2005/09/16 11:48:56 dragut Exp $
// author.aw - Autori artiklid 
/*

@classinfo syslog_type=ST_AUTHOR no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property limit type=textbox 
@caption Mitu viimast
@comment Mitut viimast dokumenti n&auml;idata

@property only_active_period type=checkbox ch_value=1
@caption Ainult aktiivsest perioodist
@comment N&auml;ita dokumente ainult aktiivsest perioodist

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
		//		$data["value"] = (int)$data["value"];
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
		$o = new object($arr["id"]);
		$par_obj = new object($o->parent());

		$this->lim = (int)$o->prop("limit");
		if (empty($this->lim))
		{
			$this->lim = 10;
		};
		return $this->author_docs(array(
			"obj_inst" => $o,
		));
//		return $this->author_docs($par_obj->name());
	}

	////
	// !This will list all documents created by an author
        function author_docs($arr)
        {

		$author = $arr['obj_inst']->prop("name");
		$limit = $arr['obj_inst']->prop("limit");
		$only_active_period = $arr['obj_inst']->prop("only_active_period");

		$this->read_template("show.tpl");

// ok, i need a functionality to show documents only from active periods
                $perstr = "";
                if (aw_ini_get("search_conf.only_active_periods"))
                {
                        $pei = get_instance(CL_PERIOD);
                        $plist = $pei->period_list(0,false,1);
                        $perstr = " and objects.period IN (".join(",", array_keys($plist)).")";
                }

		// composing parameters for documents object_list
		$object_list_parameters = array(
			"class_id" => CL_DOCUMENT,
			"author" => $author,
			"sort_by" => "objects.created DESC",
		);
		
		// is there set a limit, how many documents should be displayed?
		if (!empty($limit) || $limit == "0")
		{
			$object_list_parameters['limit'] = $limit;
		}

		if (!empty($only_active_period))
		{
			$object_list_parameters['period'] = aw_global_get("act_per_id");
		}

		$documents = new object_list($object_list_parameters);

		$retval = "";
		foreach ($documents->arr() as $document)
		{
			$document_id = $document->id();

			// so, document comments are not objects yet, so, the only way to get them, is via sql
			$comments_count = $this->db_fetch_field("SELECT count(*) AS cnt FROM comments WHERE board_id = '$document_id'","cnt");
			$this->vars(array(
				"link" => obj_link($document_id),
				"title" => $document->name(),
				"comments_link" => $this->mk_my_orb("show_threaded", array("board" => $document_id), "forum"),
				"comments_count" => $comments_count,
			));

			// if there are comments, then parse the HAS_COMMENTS sub
			$has_comments  = "";
                        if ($comments_count > 0)
                        {
                                $has_comments = $this->parse("HAS_COMMENTS");
                        }
                        $this->vars(array("HAS_COMMENTS" => $has_comments));

                        $retval .= $this->parse("AUTHOR_DOCUMENT");

		}
                return $retval;
        }
/*
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

		$perinst = get_instance(CL_PERIOD);

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
*/


	// This is used at least in crm_person class, so it cannot be removed
	function get_docs_by_author($arr)
	{
		$aname = $arr["author"];
		$_lim = !empty($arr["limit"]) ? $arr["limit"] : $this->lim;
		if ($_lim)
		{
			$lim = "LIMIT ".($_lim + 1);
		}

		$perstr = "";

		if (aw_ini_get("search_conf.only_active_periods"))
		{
			$pei = get_instance(CL_PERIOD);
			$plist = $pei->period_list(0,false,1);
			$perstr = " AND objects.period IN (".join(",", array_keys($plist)).")";
		}

		if ($arr["date"])
		{
			$datelim = " AND documents.modified <= " . $arr["date"];
			// now I also have to figure out whether there is a document after this one

		}

		// ookey, now I need to implement look-ahead and look-back to determine whether
		// there are any documents to be shown in the future or in the past

		// if there is no date given, then we simply show last "limit" items
		//	there are no "next" items
		// 	get limit + 1 items, leave the last one out of the search results, but if it does
		//	exist, then I know that I have to display the "previous" link

		// if there is a date given, then show the last "limit" items starting from that
		// date and going backwards. But this means that I'll have to use timestamps as dates,
		// because otherwise I cannot give exact dates, can I?

		//	get limit +1 items, leave the last one 

		// if (int)$_REQUEST["date"] == $_REQUEST["date"] - use it as a timestamp then?

		// get documents from active periods only
		$sql = "SELECT docid,title,objects.parent,documents.modified AS mod FROM documents
				LEFT JOIN objects ON objects.brother_of = documents.docid
				LEFT JOIN objects AS objects2 ON objects.parent = objects2.brother_of
				WHERE objects2.status != 0 AND author = '$aname' AND objects.status = 2 $perstr $datelim
				ORDER BY objects.created DESC $lim";
		$ids = array();
		$this->db_query($sql);
		$c = 0;
		$has_prev = false;
		$max = $this->num_rows();
		while($row = $this->db_next())
		{
			$c++;
			if ($c == $max && $max == ($_lim + 1))
			{
				$has_prev = $row["mod"];
			}
			else
			{
				$ids[$row["docid"]] = array(
					"docid" => $row["docid"],
					"mod" => $row["mod"],
				);
			};
		};


		$nav = array();

		if ($arr["date"])
		{
			// nüüd on vaja teada, et kas järgmisi dokke on olemas või mitte ja kui on,
			// sii on vaja leida viimane nendest
			$datelim =  "AND documents.modified > " . $arr["date"];
			$sql = "SELECT documents.modified AS mod FROM documents
					LEFT JOIN objects ON objects.brother_of = documents.docid
					LEFT JOIN objects AS objects2 ON objects.parent = objects2.brother_of
					WHERE objects2.status != 0 AND author = '$aname' AND objects.status = 2 $perstr $datelim
					ORDER BY objects.created LIMIT " . $arr["limit"];
			$this->db_query($sql);
			$max = $this->num_rows();
			$row = $this->db_next();
			$last_mod = 0;
			while($row = $this->db_next())
			{
				$last_mod = $row["mod"];
			};
			if ($last_mod)
			{
				$has_next = $last_mod;
			};
			/*
			$c = 0;
			if ($row)
			{
				$c++;
				if ($c == $max)
				{
					$has_next = $row["mod"];
				};
			};
			*/
		};

		if ($has_prev)
		{
			$nav["prev"] = $has_prev;
		};
		if ($has_next)
		{
			$nav["next"] = $has_next;
		};

		if (sizeof($ids) > 0)
		{
			$idarr = join(",",array_keys($ids));
			$comm_q = "SELECT count(*) AS cnt,board_id FROM comments
						WHERE board_id IN ($idarr) GROUP BY board_id";
			$this->db_query($comm_q);
			while($row = $this->db_next())
			{
				$ids[$row["board_id"]]["commcount"] = $row["cnt"];
			};
		};
		return array($nav,$ids);
	}

}
?>
