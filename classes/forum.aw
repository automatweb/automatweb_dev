<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/forum.aw,v 2.9 2001/11/02 11:22:38 duke Exp $
global $orb_defs;
$orb_defs["forum"] = "xml";
lc_load("msgboard");

// Foorumite manageerimine, siia tuleb yle tuua ka koik msgboardi halduse funktsioonid,
// et kogu see vark muutuks ORB kompatiibliks

class forum extends aw_template
{
	function forum()
	{
		$this->db_init();
		$this->tpl_init("msgboard");
		// $this->sub_merge = 1;
		// to keep track of how many topics we have already drawn
		$this->topic_count = 0; 
		global $lc_msgboard;
		if (is_array($lc_msgboard))
		{
			$this->vars($lc_msgboard);
		}
	}

	////
	// !Kuvab uue foorumi lisamise vormi
	function add($arr)
	{
		extract($arr);
		$this->read_template("add_forum.tpl");
		$this->mk_path($parent,"Lisa foorum");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_properties", array("parent" => $parent)),
			"comments" => checked(1),
		));
		return $this->parse();
	}


	////
	// !Kuvab uue topicu lisamise vormi
	function add_topic($args = array())
	{
		extract($args);
		$object = $this->get_object($id);
		#$parent = $this->get_object($object["parent"]);
		// kui kaasa antakse section argument, siis peaks kontrollima
		// kas see ikka kuulub selle foorumi juurde
		$text = $this->mk_orb("change",array("id" => $id));
		$this->mk_path($object["parent"],"<a href='$text'>$object[name]</a> / Lisa teema");
		$this->read_template("add_topic.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_topic",array("id" => $id,"section" => $section)),
		));
		return $this->parse();
	}

	////
	// !Lisab uue topicu
	function submit_topic($args = array())
	{
		extract($args);
		$tid = $this->new_object(array(
			"parent" => $id,
			"name" => $topic,
			"comment" => $comment,
			"class_id" => CL_MSGBOARD_TOPIC,
			"status" => 2,
		));

		if ($section)
		{
			global $baseurl;
			$retval = $baseurl . "/?section=$section";
		}
		else
		{
			$retval = $this->mk_my_orb("topics",array("id" => $id));
		}
		return $retval;
	}

	////
	// !Shows a flat list of messages
	function show($args = array())
	{
		extract($args);
		$board_obj = $this->get_obj_meta($board);
		$forum_obj = $this->get_object($board_obj["parent"]);
		$flink = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change",array("id" => $forum_obj["oid"])),$forum_obj["name"]);
		$this->mk_path($forum_obj["parent"],$flink . " / $board_obj[name]");
		$this->_query_comments(array("board" => $board));
		$this->read_template("messages.tpl");

		$content = "";

		while($row = $this->db_next())
		{
			$content .= $this->display_comment($row);

		};

		// miskit splitter tyypi funktsiooni on vaja, mis soltuvalt sellest kas tegu on adminni
		// voi dokumendi sees oleva asjaga valjastaks sobiva lingi
		if (not($id))
		{
			$id = $forum_obj["oid"];
		};
		
		// arvutame häälte arvu sellele foorumile
		if ($board_obj["meta"]["voters"] == 0)
		{
			$rate = 0;
		}
		else
		{
			$rate = $board_obj["meta"]["votesum"] / $board_obj["meta"]["voters"];
		};

		$this->vars(array(
			"vote_reforb" => $this->mk_reforb("submit_vote",array("board" => $board)),
			"topic" => $board_obj["name"],
			"rate" => sprintf("%0.2f",$rate),
		));
		global $forum_votes;
		if ($forum_votes[$board])
		{
			$voteblock = $this->parse("ALREADY_VOTED");
		}
		else
		{
			$voteblock = $this->parse("VOTE_FOR_TOPIC");
		};


		$this->vars(array(
			"message" => $content,
			"threaded_link" => $this->mk_my_orb("show_threaded",array("board" => $board)),
			"newtopic_link" => $this->mk_my_orb("add_topic",array("id" => $id,"section" => $oid)),
			"flat_link" => $this->mk_my_orb("show",array("board" => $board)),
			"forum_link" => $this->mk_my_orb("topics",array("id" => $id)),
			"VOTE_FOR_TOPIC" => $voteblock,
			"TOPIC" => $this->parse("TOPIC"),
		));
		
		return $this->parse() . $this->add_comment(array("board" => $board,"parent" => $parent,"section" => $section));

	}

	////
	// !Submits a vote to a topic
	function submit_vote($args = array())
	{
		extract($args);
		global $forum_votes;
		if (not($forum_votes[$args["board"]]))
		{
			$forum_votes[$args["board"]] = rand();
			session_register("forum_votes");
			$board_obj = $this->get_obj_meta($board);
			$voters = ++$board_obj["meta"]["voters"];
			$votesum = $board_obj["meta"]["votesum"] + $vote;
			$this->set_object_metadata(array(
				"oid" => $board,
				"key" => "voters",
				"value" => $voters,
			));
			$this->set_object_metadata(array(
				"oid" => $board,
				"key" => "votesum",
				"value" => $votesum,
			));
		}

		return $this->mk_my_orb("show",array("board" => $board));
	}

	////
	// !Shows a threaded list of messages
	function show_threaded($args = array())
	{
		$this->level = 0;
		$this->comments = array();
		$this->content = "";
		extract($args);
		$board_obj = $this->get_object($board);
		$forum_obj = $this->get_object($board_obj["parent"]);
		$flink = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change",array("id" => $forum_obj["oid"])),$forum_obj["name"]);
		$this->mk_path($forum_obj["parent"],$flink . " / $board_obj[name]");
		
		$this->_query_comments(array("board" => $board));

		$this->read_template("messages_threaded.tpl");
		$content = "";
		while($row = $this->db_next())
		{
			$this->comments[$row["parent"]][] = $row;
		};
		$this->rec_comments(0);
		$this->vars(array(
			"message" => $this->content,
			"flat_link" => $this->mk_my_orb("show",array("board" => $board)),
			"threaded_link" => $this->mk_my_orb("show_threaded",array("board" => $board)),
			"forum_link" => $this->mk_my_orb("topics",array("id" => $forum_obj["oid"])),
		));
		return $this->parse() . $this->add_comment(array("board" => $board,"parent" => $parent));
	}

	function rec_comments($level)
	{
		if (not(is_array($this->comments[$level])))
		{
			return;
		}

		foreach($this->comments[$level] as $key => $val)
		{
			$val["spacer"] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",$this->level);
			$val["level"] = 20 * $this->level;
			$this->content .= $this->display_comment($val);
			$this->level++;
			$this->rec_comments($val["id"]);
			$this->level--;
		}
	}

	function reply($args = array())
	{
		extract($args);
		$q = "SELECT * FROM comments WHERE id = '$parent'";
		$this->db_query($q);
		$row = $this->db_next();
		$board_obj = $this->get_object($row["board_id"]);
		$forum_obj = $this->get_object($board_obj["parent"]);
		$flink = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change",array("id" => $forum_obj["oid"])),$forum_obj["name"]);
		$this->mk_path($forum_obj["parent"],$flink . " / $board_obj[name]");
		if ($row)
		{
			$this->read_template("messages.tpl");
			$content = $this->display_comment($row);
		}
		return $this->parse() . $this->add_comment(array("parent" => $parent));
	}

	////
	// !Displays a single comment
	// requires a loaded template with a subtemplate "message"
	function display_comment($args = array())
	{
		$this->vars(array(
			"SHOW_COMMENT" => "",
			"spacer" => $args["spacer"],
			"level" => $args["level"],
			"from" => $args["name"],
			"email" => $args["email"],
			"parent" => $args["parent"],
			"subj" => $args["subj"],
			"time" => $this->time2date($args["time"],2),
			"comment" => $args["comment"],
			"reply_link" => $this->mk_my_orb("reply",array("parent" => $args["id"])),
			"open_link" => $this->mk_my_orb("topics_detail",array("id" => $this->forum_id,"cid" => $args["id"],"from" => $this->from)),
		));

		if ($this->is_template("SHOW_COMMENT") && ($this->cid == $args["id"]))
		{
			$this->vars(array("SHOW_COMMENT" => $this->parse("SHOW_COMMENT")));
		};

		return $this->parse("message");
	}

	////
	// !Displays the form to add comments
	function add_comment($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		$this->vars(array(
			"comment" => $args["comment"],
			"subj" => $args["subj"],
			"reforb" => $this->mk_reforb("submit_comment",array("board" => $board,"parent" => $parent,"section" => $section)),
		));
		return $this->parse();
	}

	////
	// !Submits comment to a topic
	function submit_comment($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($parent)
		{
			$q = "SELECT * FROM comments WHERE id = '$parent'";
			$this->db_query($q);
			$row = $this->db_next();
			$board = $row["board_id"];
		};
		$parent = (int)$parent;
		$site_id = $GLOBALS["SITE_ID"];
		$ip = $GLOBALS["REMOTE_ADDR"];
		$t = time();
		$q = "INSERT INTO comments (parent, board_id, name, email, comment, subj,
					time, site_id, ip)
			VALUES ('$parent','$board','$name','$email','$comment','$subj',
					$t,'$site_id', '$ip')";
		$this->upd_object(array(
			"oid" => $board,
		));

		$this->db_query($q);
		if ($section)
		{
			$retval = $this->mk_url(array("section" => $section,"board" => $board));
		}
		else
		{
			$retval =$this->mk_my_orb("show",array("board" => $board));
		};
		return $retval;

	}

	function change($args = array())
	{
		extract($args);
		$o = $this->get_obj_meta($id);

		$this->forum_id = $id;
		$this->from = $from;
		$this->topicsonpage = ($o["meta"]["topicsonpage"]) ? $o["meta"]["topicsonpage"] : 5;

		$this->mk_path($o["parent"], "Foorum");
		$this->read_template("list_topics.tpl");

		global $HTTP_COOKIE_VARS;
		$aw_mb_last = unserialize($HTTP_COOKIE_VARS["aw_mb_last"]);
		$this->last_read = $aw_mb_last[$id];
		$this->now = time();

		$this->use_orb_for_links = 1;
		$content = $this->_draw_all_topics(array(
						"id" => $id,
		));

		// õkk, this is overkill
		$this->db_query("SELECT COUNT(id) as cnt ,board_id, MAX(time) as mtime FROM comments GROUP BY board_id");
		// pealkirjad, vastuseid, postitas, alustatud, hiliseim vastus

		$this->vars(array(
			"newtopic_link" => $this->mk_my_orb("add_topic",array("id" => $id)),
			"props_link" => $this->mk_my_orb("edit_properties",array("id" => $id)),
			"search_link" => $this->mk_my_orb("search",array("board" => $id)),
			"mark_all_read" => $this->mk_my_orb("mark_all_read",array("id" => $id)),
			"topic_detail_link" => $this->mk_my_orb("topics_detail",array("id" => $id,"from" => $from)),
			"TOPIC" => $content,
			"TOPIC_EVEN" => $content,
		));
		return $this->parse();
	}

	function topics_detail($args = array())
	{
		extract($args);
		$o = $this->get_obj_meta($id);

		$this->forum_id = $id;
		$this->from = $from;
		$this->topicsonpage = ($o["meta"]["topicsonpage"]) ? $o["meta"]["topicsonpage"] : 5;
		$this->read_template("list_topics_detail.tpl");

		$this->cid = $args["cid"];
		$content = $this->_draw_all_topics(array(
						"id" => $id,
						"details" => 1,
		));
		
		$this->vars(array(
			"newtopic_link" => $this->mk_my_orb("add_topic",array("id" => $id)),
			"topics_link" => $this->mk_my_orb("topics",array("id" => $id,"from" => $from)),
			"props_link" => $this->mk_my_orb("edit_properties",array("id" => $id)),
			"search_link" => $this->mk_my_orb("search",array("board" => $id)),
			"mark_all_read" => $this->mk_my_orb("mark_all_read",array("id" => $id)),
			"topic_detail_link" => $this->mk_my_orb("topics_detail",array("id" => $id)),
			"TOPIC" => $content,
			"TOPIC_EVEN" => $content,
		));

		return $this->parse();
	}

		

	////
	// !Kuvab foorumi muutmise vormi
	function edit_properties($arr)
	{
		extract($arr);
		$this->read_template("add_forum.tpl");
		$o = $this->get_object($id);
		$meta = $this->get_object_metadata(array("metadata" => $o["metadata"]));
		$this->mk_path($o["parent"], "Muuda foorumit");
		global $template_sets;
		$this->vars(array(
			"content_link" => $this->mk_my_orb("change",array("id" => $id)),
		));
		$this->vars(array(
			"name" => $o["name"],
			"comment" => $o["comment"],
			"comments" => checked($meta["comments"]),
			"template" => $this->picker($meta["template"],$template_sets),
			"onpage" => $this->picker($meta["onpage"],array(10 => 10,15 => 15,20 => 20,25 => 25,30 => 30)),
			"topicsonpage" => $this->picker($meta["topicsonpage"],array(10 => 10,15 => 15,20 => 20,25 => 25,30 => 30)),
			"rated" => checked($meta["rated"]),
			"reforb" => $this->mk_reforb("submit_properties",array("id" => $id)),
			"url" => $GLOBALS["baseurl"]."/comments.".$GLOBALS["ext"]."?action=topics&forum_id=".$id,
			"EDIT" => $this->parse("EDIT"),
		));
		$this->parse("CHANGE");
		return $this->parse();
	}
	
	////
	// !Salvestab foorumi
	function submit_properties($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_FORUM, "name" => $name,"comment" => $comment));
		}
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "comments",
			"value" => $comments,
		));
		
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "rated",
			"value" => $rated,
		));
		
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "template",
			"value" => $template,
		));

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "onpage",
			"value" => $onpage,
		));
		
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "topicsonpage",
			"value" => $topicsonpage,
		));

		if ($parent)
		{
			$parobj = $this->get_object($parent);
			if ($parobj["class_id"] == CL_DOCUMENT)
			{
				$this->add_alias($parent,$id);
			}
			$retval = $this->mk_my_orb("change",array("id" => $parent),"document");
		}
		else
		{
			$thisobj = $this->get_object($id);
			$parobj = $this->get_object($thisobj["parent"]);
			if ($parobj["class_id"] == CL_DOCUMENT)
			{
				$retval = $this->mk_my_orb("change",array("id" => $parobj["oid"]),"document");
			}
			else
			{
				$retval = $this->mk_my_orb("edit_properties", array("id" => $id));
			}
		};
		return $retval;
	}

	////
	// !Shows the search form
	function search($args = array())
	{
		extract($args);
		$this->read_template("search.tpl");
		$o = $this->get_object($board);
		$flink = $this->mk_my_orb("change",array("id" => $board));
		$this->mk_path($o["parent"], "<a href='$flink'>$o[name]</a> / Otsi");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_search",array("board" => $board)),
		));
		return $this->parse();
	}

	////
	// !Performs the actual search
	function submit_search($args = array())
	{
		extract($args);

	}

	function mark_all_read($args = array())
	{
		extract($args);
		global $HTTP_COOKIE_VARS;
		$aw_mb_last = unserialize($HTTP_COOKIE_VARS["aw_mb_last"]);
		$aw_mb_last[$id] = time();
		setcookie("aw_mb_last",serialize($aw_mb_last),time()+24*3600*1000);
		return $this->mk_my_orb("topics",array("id" => $id));
	}

	function parse_alias($args = array())
	{
		extract($args);
		$this->f_aliases = $this->get_aliases(array(
                                                "oid" => $oid,
                                                "type" => CL_FORUM,
                ));
                $l = $this->f_aliases[$matches[3] - 1];
                $target = $l["target"];
                $tobj = $this->get_object($target);
                $parent = $tobj["last"];
		$id = $target;

		$this->read_template("list_topics.tpl");

		$board = false;

		if ($GLOBALS["board"])
		{
			$retval = $this->show(array("board" => $GLOBALS["board"],"section" => $oid));
			$this->vars(array(
				"newtopic_link" => $this->mk_my_orb("add_topic",array("id" => $id,"section" => $oid)),
				"search_link" => $this->mk_my_orb("search",array("board" => $id,"section" => $oid)),
			));
		}
		else
		{
			$content .= $this->_draw_all_topics(array(
							"id" => $id,
							"oid" => $oid,
			));



			$this->vars(array(
				"newtopic_link" => $this->mk_my_orb("add_topic",array("id" => $id,"section" => $oid)),
				"search_link" => $this->mk_my_orb("search",array("board" => $id,"section" => $oid)),
				"TOPIC_EVEN" => $content,
			));

			$retval = $this->parse();
		}
		$this->vars = array();
		return $retval;
	}

	////
	// !Calculates amounts of comments on all given message boards
	function _get_comment_counts($boards = array())
	{
		if (not(is_array($boards)))
		{
			return false;
		};
		
		// check the length too, otherwise we get a nasty mysql error
		if (sizeof($boards) == 0)
		{
			return false;
		};
		$comments = array();
		$q = sprintf("SELECT board_id,count(*) AS cnt FROM comments
				WHERE board_id IN (%s) 
				GROUP BY board_id",join(",",$boards));
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$comments[$row["board_id"]] = $row["cnt"];
		};
		return $comments;
	}
			
	////
	// !Draws a list of all topics under a forum
	function _draw_all_topics($args = array())
	{
		extract($args);
		$obj = $this->get_objects_below(array(
			"parent" => $id,
			"class" => CL_MSGBOARD_TOPIC,
			"orderby" => "created desc",
		));
		$content = "";
		if (is_array($obj))
		{
			$blist = array();
			foreach($obj as $key => $item)
			{
				$blist[] = $item["oid"];
			}

			$this->comments = $this->_get_comment_counts($blist);
				
			list($from,$to) = $this->_draw_pager(array(
						"total" => sizeof($obj),
						"onpage" => $this->topicsonpage,
						"active" => $this->from,
						"details" => $args["details"],
			));
			$cnt = 0;
			foreach($obj as $key => $item)
			{
				if ( ($cnt >= $from) && ($cnt <= $to) )
				{
					if ($args["details"])
					{
						// this is a tad ineffective, because we query 
						// for each topic, instead of getting the comments
						// as a batch
						$this->_query_comments(array("board" => $item["oid"]));
						// put the comments into tree
						while($row = $this->db_next())
						{
							$this->comments[$row["parent"]][] = $row;
						};
						$this->rec_comments(0);
						$this->vars(array("message" => $this->content));
						$this->content = "";
						$this->comments = array();
					}
					$content .= $this->_draw_topic(array_merge($item,array("section" => $oid)));
				}
				$cnt++;
			}
				
		};
		return $content;
	}

	////
	// !Draws a single topic
	// requires a loaded template with either TOPIC_EVEN AND TOPIC_ODD subtemplates
	// or just a TOPIC template
	function _draw_topic($args = array())
	{
		$this->topic_count++;

		if ($this->use_orb_for_links)
		{
			$topic_link = $this->mk_my_orb("show",array("board" => $args["oid"]));
		}
		else
		{
			$topic_link = $this->mk_url(array("board" => $args["oid"],"section" => $args["section"]));
		};

		// mille vastu võrrelda=
		$check_against = ($args["modified"] > $args["created"]) ? $args["modified"] : $args["created"];
		$mark = ($check_against > $this->last_read) ? $this->parse("NEW_MSGS") : "";

		$this->vars(array(
			"topic" => ($args["name"]) ? $args["name"] : "nimetu",
			"created" => $this->time2date($args["created"],2),
			"createdby" => $args["createdby"],
			"last" => $this->time2date($args["modified"],2),
			"lastmessage" => $this->time2date($args["modified"],2),
			"comments" => (int)$this->comments[$args["oid"]],
			"cnt" => (int)$this->comments[$args["oid"]],
			"topic_link" => $topic_link,
			"NEW_MSGS" => $mark,
		));
		$even = ($this->topic_count % 2);
		if ($this->is_template("TOPIC_EVEN"))
		{
			// if TOPIC_EVEN template exitsts then we assume that TOPIC_ODD also exists
			// actually we should check for it 
			$tpl_to_parse = ($even) ? "TOPIC_EVEN" : "TOPIC_ODD";
		}
		else
		{
			$tpl_to_parse = "TOPIC";
		};
		$retval = $this->parse($tpl_to_parse);
		return $retval;
	}
	
	////
	// !Performs a query to get comments matching a certain criteria
	function _query_comments($args = array())
	{
		extract($args);
		if ($args["board"])
		{
			$q = "SELECT * FROM comments WHERE board_id = '$board' ORDER BY time DESC";
			$this->db_query($q);
		}
	}

	////
	// !Draws a page
	// requires a loaded template and PAGES, PAGE and SEL_PAGE subtemplates to be defined
	// total(int) - how many items do we have?
	// onpage(int) - how many items on a page?
	// active(int) - what item are we showing at the moment? 
	function _draw_pager($args = array())
	{
		extract($args);
		$content = "";
		$num_pages = (int)(($total / $onpage) + 1);

		// no pager, if we have less entries than will fit on one page
		if ($total > ($num_pages * $onpage))
		{
			return false;
		};

		for ($i = 1; $i <= $num_pages; $i++)
		{
			$page_start = ($i - 1)  * $onpage;
			$page_end = $page_start + $onpage - 1;
			if ( ($active >= $page_start) and ($active < $page_end) )
			{
				$act_start = $page_start;
				$act_end = $page_end;
				$tpl = "SEL_PAGE";
			}
			else
			{
				$tpl = "PAGE";
			};
			$pg_action = ($args["details"]) ? "topics_detail" : "topics";
			$this->vars(array(
				"pagelink" => $this->mk_my_orb($pg_action,array("id" => $this->forum_id,"from" => $page_start)),
				"linktext" => $i,
			));
			$content .= $this->parse($tpl);
		};
		$this->vars(array(
			"PAGE" => $content,
		));
		$this->vars(array(
			"PAGES" => $this->parse("PAGES"),
		));

		return(array($act_start,$act_end));
	}
}
?>
