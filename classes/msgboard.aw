<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/msgboard.aw,v 2.21 2001/10/02 10:16:58 cvs Exp $
define(PER_PAGE,10);
define(PER_FLAT_PAGE,20);
define(TOPICS_PER_PAGE,7);

class msgboard extends aw_template
{
	function msgboard()
	{
		$this->db_init();
		$this->tpl_init("msgboard");
	}

	function submit_votes($args = array())
	{
		if ($GLOBALS["uid"] == "fubar")
		{
			$this->raise_error("sa pole sisse logitud ja ei saa h\xe4\xe4letada",true);
		};
		
		classload("users");
		$u = new users();
		extract($args);
		global $HTTP_SESSION_VARS;
		global $commentvotes;
		$oldvotes = array();
		$oldvotes = $HTTP_SESSION_VARS["commentvotes"];
		if (is_array($vote))
		{
			foreach($vote as $key => $val)
			{
				$topicvotes = $this->get_object_metadata(array(
							"oid" => $key,
							"key" => "votes",
				));
	
				$oldvotes[$key] = $val;

				$topicvotes["votes"] = $topicvotes["votes"] + 1;
				$topicvotes["total"] = $topicvotes["total"] + $val;

				$this->set_object_metadata(array(
							"oid" => $key,
							"key" => "votes",
							"value" => $topicvotes,
				));
			};
		};
		$commentvotes = $oldvotes;
		session_register("commentvotes");
		global $baseurl;
		// this sucks, really
//		header("Location: $baseurl/comments.aw?action=topics");
//		exit;
		return;
	}	

	function show($id,$page,$forum_id = 0)
	{
		global $msgboard_type,$aw_mb_last;
		//if ($msgboard_type == "threaded" && $forum_id)	// the lotsa-pageviews version
		// forum_id on defineerimata, kui klikkida artikli juures "Kommentaare" linki,
		// hetkel on Eesti Naises aga tehtud ainult threaded template
		if ($msgboard_type == "threaded")	// the lotsa-pageviews version
			return $this->show_threaded($id, $page,$forum_id);

		$aw_mb_last = unserialize(stripslashes($aw_mb_last));
		$aw_mb_last[$id] = time();
		// miks mitte sessiooni kasutada?
		setcookie("aw_mb_last",serialize($aw_mb_last),time()+24*3600*1000,"/");

		if ($msgboard_type == "flat")	// the wussy version
			return $this->show_flat($id, $page,$forum_id);


		$this->quote(&$id);

		$this->db_query("SELECT * FROM comments WHERE board_id = '$id' ORDER BY time");
		while ($row = $this->db_next())
		{
			$this->comments[$row["parent"]][] = $row;
		}

		$this->read_template("messages.tpl");
		
		$this->vars(array("forum_id" => $forum_id,"topic_id" => $id));

		if ($this->is_template("TOPIC"))
		{
			$this->db_query("SELECT * FROM objects where class_id = ".CL_MSGBOARD_TOPIC." AND oid = '$id'");
			if (($row = $this->db_next()))
			{
				$this->vars(array(
					"topic" => $row["name"], 
					"created" => $this->time2date($row["created"], 2),
					"text" => str_replace("\n","<Br>",$row["comment"]),
					"from" => $row["last"],
					"topic_id" => $id,
					"forum_id" => $forum_id
				));
				$top = $this->parse("TOPIC");
			}
			$this->vars(array("TOPIC" => $top));
		}
		
		$this->vars(array("section" => $id,"page" => $page));

		$this->level = -1;
		$this->msg_num = 0;
		$this->msg_begin = $page * PER_PAGE;
		$this->msg_end = ($page+1) * PER_PAGE;

		$this->req_msgs(0,&$str);
		$this->vars(array("message" => $str));

		// calc & show pages selector
		$total = $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM comments WHERE parent = 0 AND board_id = '$id'", "cnt");
		$num_pages = ($total / PER_PAGE);
		for ($i=0; $i <= $num_pages; $i++)
		{
			$this->vars(array("pagenum" => $i, "ltext" => $i,"from" => $i*PER_PAGE, "to" => min($total,($i+1)*PER_PAGE)));
			if ($i == $page)
				$p.=$this->parse("SEL_PAGE");
			else
				$p.=$this->parse("PAGE");
		}
		if ($num_pages > 1)
		{
			$this->vars(array("PAGE" => $p,"SEL_PAGE" => ""));
			$ps = $this->parse("PAGES");
		}

		$this->vars(array("PAGES" => $ps, "date" => $this->time2date(time(), 2)));

		$ret = $this->parse();
		return $ret.$this->add(0,$id,$page,$forum_id);
	}

	function req_msgs($parent,&$str)
	{
		if (!is_array($this->comments[$parent]))
			return;

		$this->level++;

		global $uid;

		reset($this->comments[$parent]);
		while (list(,$v) = each($this->comments[$parent]))
		{
			$show = true;

			if ($this->level == 0)	//count messages only on 1st level
			{
				if ($this->msg_num < $this->msg_begin || $this->msg_num >= $this->msg_end)
					$show = false;
				$this->msg_num++;
			}

			if ($show)
			{
				$this->dequote(&$v);
				$this->vars(array(
					"id" => $v["id"],
					"from" => $v["name"], 
					"email" => $v["email"], 
					"comment" => nl2br($v["comment"]), 
					"level" => $this->level*20,
					"subj" => $v["subj"],
					"time" => $this->time2date($v["time"],2),
					"KUSTUTA" => ""
				));

				if ($this->prog_acl("view",PRG_MENUEDIT))
				{
					$this->vars(array("KUSTUTA" => $this->parse("KUSTUTA")));
				}

				$str.=$this->parse("message");

				$this->req_msgs($v["id"], &$str);
			}
		}

		$this->level--;
	}

	////
	// !Kuvab kommentaari lisamise vormi
	function add($parent,$section,$page,$forum_id = 0,$msg="",$email="",$subj="",$comment="")
	{
		$parent = (int)$parent;
		$this->quote(&$section);

		if ($parent > 0)
		{
			$this->db_query("SELECT * FROM comments WHERE id = $parent");
			if (!($row = $this->db_next()))
				$this->raise_error("msgboard->add($parent, $section): no comment with id $parent!", true);

			if ($subj == "")
			{
				// pane ennast põlema
				$subj = strpos($row["subj"],"Re:")===false ? "Re: ".$row["subj"] : $row["subj"];
			}

			if ($comment == "")
			{
				$comment = join("\r\n",$this->map("> %s",explode("\r\n",wordwrap($row[comment],33,"\r\n",1))));
			}
		}

		$this->tpl_reset();
		$this->read_template("add.tpl");
		$this->vars(array(
			"email" => $email,
			"message" => $msg,
			"parent" => $parent, 
			"section" => $section,
			"ext" => $GLOBALS["ext"],
			"subj" => $subj, 
			"comment" => $comment,
			"page" => $page,
			"forum_id" => $forum_id
		));
		$this->flush_cache();
		return $this->parse();
	}

	function submit_add($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if (is_number($parent) && $comment != "" && $from != "")
		{
			// hm, we must preserve > in the beginning of lines, cause they are cool.
			// so ew split the message into lines and then remplate the > with &gt; and 
			// then merge the message back together
			$mar = explode("\n",$comment);
			reset($mar);
			$comment = "";
			while (list(,$line) = each($mar))
			{
				$pos = 0;
				while (($line[$pos] == " " || $line[$pos] == ">") && $pos < strlen($line))
				{
					if ($line[$pos] == ">")
					{
						$line = substr($line,0,$pos)."&gt;".substr($line,$pos+1);
						$pos+=3;
					}
					$pos++;
				}
				$comment.=$line."\n";
			}

			// also preserve to-be links <http://www.ee>
			$comment = preg_replace("/<http(.*)>/","&lt;http\\1&gt;",$comment);
			$comment = preg_replace("/<ftp(.*)>/","&lt;ftp\\1&gt;",$comment);
			$comment = strip_tags($comment, "<b>,<u>,<i>,<ul>,<ol>,<li>");

			// figure out the senders ip.
			// FIXME: comments.aw?HTTP_X_FORWARDED_FOR=h4x0r3d
			$ip = $GLOBALS["HTTP_X_FORWARDED_FOR"];
			if ($ip == "")
			{
				$ip = $GLOBALS["REMOTE_ADDR"];
			}
			$ip = gethostbyaddr($ip);
			$pp = strpos($ip,".");
			// eh?
			$ip = "---".substr($ip,$pp);

			$this->db_query("INSERT INTO comments(parent, board_id,name, email,comment,subj,time,site_id,sendmail,ip) values($parent,'$section','$from','$email','$comment','$subj',".time().",".$GLOBALS["SITE_ID"].",'$sendmail','$ip')");
			$id = $this->db_fetch_field("SELECT MAX(id) as id FROM comments","id");

			// if it is under a topic object, update the topic objects modified date if the configuration says so.
			if ($GLOBALS["msgboard_topic_order_by_last_message"] && is_number($section))
			{
				$t_ob = $this->get_object($section);
				if ($t_ob["class_id"] == CL_MSGBOARD_TOPIC)
				{
					$this->upd_object(array("oid" => $section));
				}
			}

			// now check if we must send this comment to somebody
			// make list of all the messages above this one
			$p = $parent;
			$mails = array();
			while ($p)
			{
				$this->db_query("SELECT * FROM comments WHERE id = $p");
				$row = $this->db_next();
				if ($row["sendmail"] == 1)
				{
					$mails[$row["email"]] = $row;
				}
				$p = $row["parent"];
			}

			global $baseurl, $ext,$section;
			reset($mails);
			while (list(,$mail) = each($mails))
			{
				$msg = sprintf(FORUM_MAIL_MESSAGE, $baseurl."/comments.$ext?section=$section&msg=".$mail[id], $mail[comment], $baseurl."/comments.$ext?section=$section&msg=".$id,$comment);
				$msg = str_replace("&gt;",">",$msg);
				$msg = str_replace("&lt;","<",$msg);
				$msg = str_replace("\n","\n\r", $msg);
				mail($mail["email"], FORUM_MAIL_SUBJECT, $msg, "From: ".FORUM_MAIL_FROM."\n\n");
			}
		}
		else
		{
			return false;
		}
		$this->flush_cache();
		return $id;
	}

	function get_num_comments($id)
	{
		$this->quote(&$id);
		return $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM comments WHERE board_id = '$id'", "cnt");
	}

	function show_flat($id,$page,$forum_id)
	{
		$this->quote(&$id);
		global $HTTP_SESSION_VARS;
		$votes = $HTTP_SESSION_VARS["commentvotes"];
		
		$forumdat = $this->get_object($forum_id);
		$meta = $this->get_object_metadata(array("metadata" => $forumdat["metadata"]));

		$votedata = $this->get_object_metadata(array(
					"oid" => $id,
					"key" => "votes",
		));

		$votecount = ($votedata["votes"]) ? $votedata["votes"] : 1;

		$this->read_template("messages.tpl");
		$this->vars(array(
			"forum_id" => $forum_id,
			"rate" => sprintf("%0.2f",$votedata["total"] / $votecount),
			"topic_id" => $id
		));

		if ($this->is_template("TOPIC"))
		{
			$this->db_query("SELECT * FROM objects where class_id = ".CL_MSGBOARD_TOPIC." AND oid = '$id'");
			if (($row = $this->db_next()))
			{
				$this->vars(array(
					"topic" => $row["name"],
					"created" => $this->time2date($row["created"], 2),
					"text" => str_replace("\n","<br>",$row["comment"]),
					"from" => $row["last"],
				));
				$top = $this->parse("TOPIC");
			}
			$this->vars(array("TOPIC" => $top));
		}


		$this->vars(array("section" => $id,"page" => $page));

		$msg_num = 0;
		$msg_begin = $page * PER_FLAT_PAGE;
		$msg_end = ($page+1) * PER_FLAT_PAGE;

		global $uid,$msgboard_order;

		if ($msgboard_order = "reverse")
			$ss = "DESC";

		$this->db_query("SELECT * FROM comments WHERE board_id = '$id' ORDER BY time $ss");
		while ($row = $this->db_next())
		{
			$show = true;

			if ($msg_num >= $msg_end)
				break;

			if ($msg_num < $msg_begin)
				$show = false;
			$msg_num++;

			if ($show)
			{
				$this->vars(array(
					"id" => $row["id"],
					"from" => $row["name"], 
					"email" => $row["email"], 
					"comment" => nl2br($row["comment"]), 
					"level" => 0,
					"subj" => $row["subj"],
					"time" => $this->time2date($row["time"],2),
					"KUSTUTA" => "",
				));

				if ($this->prog_acl("view",PRG_MENUEDIT))
				{
					$this->vars(array("KUSTUTA" => $this->parse("KUSTUTA")));
				}

				$str.=$this->parse("message");
			}
		}

		$this->vars(array("message" => $str));

		// calc & show pages selector
		$total = $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM comments WHERE board_id = '$id'", "cnt");
		$num_pages = ($total / PER_FLAT_PAGE);
		for ($i=0; $i <= $num_pages; $i++)
		{
			$this->vars(array("pagenum" => $i, "ltext" => $i,"from" => $i*PER_PAGE, "to" => min($total,($i+1)*PER_PAGE)));
			if ($page == $i)
				$p.=$this->parse("SEL_PAGE");
			else
				$p.=$this->parse("PAGE");
		}
		if ($num_pages > 1)
		{
			$this->vars(array("PAGE" => $p,"SEL_PAGE" => ""));
			$ps = $this->parse("PAGES");
		}

		$this->vars(array("topic_id" => $id,"forum_id" => $forum_id));
		$l = (isset($votes[$id])) ? $this->parse("ALREADY_VOTED") : $this->parse("VOTE_FOR_TOPIC");

		if (not($meta["rated"]))
		{
			$l = "";
		};
				

		$this->vars(array(
			"PAGES" => $ps,
			"ALREADY_VOTED" => $l,
			"date" => $this->time2date(time(), 2),

		));

		$ret = $this->parse();
		$addform = ($meta["comments"]) ? $this->add(0,$id,$page,$forum_id) : "";
		return $ret.$addform;
	}

	function search($id,$forum_id)
	{
		$this->read_template("search.tpl");
		$this->vars(array(
			"section" => $id,
			"date" => $this->time2date(time(), 2),
			"forum_id" => $forum_id
		));
		return $this->parse();
	}

	function do_search($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		$s_params = array("site_id = ".$GLOBALS["SITE_ID"]);

		if ($s_from == 1)			$s_params[] = " name LIKE '%$from%' ";
		if ($s_email == 1)		$s_params[] = " email LIKE '%$email%' ";
		if ($s_subj == 1)			$s_params[] = " subj LIKE '%$subj%' ";
		if ($s_subj == 1)			$s_params[] = " subj LIKE '%$subj%' ";
		if ($s_comment == 1)	$s_params[] = " comment LIKE '%$comment%' ";
		if ($s_all != 1)			$s_params[] = " board_id = '$section' ";

		$s_str = join(" AND ", $s_params);
		if ($s_str == "")
			return "";

		$this->read_template("search_results.tpl");
		$this->vars(array("forum_id" => $forum_id));

		$msg_begin = $page * PER_FLAT_PAGE;
		$msg_end = ($page+1) * PER_FLAT_PAGE;

		$cnt = 0;
		$this->db_query("SELECT * FROM comments WHERE $s_str ORDER BY board_id, time");
		while ($row = $this->db_next())
		{
			$show = true;

			if ($cnt >= $msg_end)
				break;

			if ($cnt < $msg_begin)
				$show = false;

			if ($show)
			{
				$this->vars(array(
					"email" => $row["email"],
					"from" => $row["name"], 
					"time" => $this->time2date($row["time"], 2),
					"subj" => $row["subj"],
					"comment_id" => $row["id"],
					"s_section" => $row["board_id"],
				));
				$c.=$this->parse("message");
			}
			$cnt++;
		}
		$this->vars(array("message" => $c,"date" => $this->time2date(time(),2)));
	
		// calc & show pages selector
		$cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM comments WHERE $s_str ORDER BY board_id, time","cnt");
		$num_pages = ($cnt / PER_FLAT_PAGE);
		for ($i=0; $i <= $num_pages; $i++)
		{
			$this->vars(array("url" => $this->make_url(array("page" => $i)), "ltext" => $i,"from" => $i*PER_PAGE, "to" => min($cnt,($i+1)*PER_PAGE)));
			if ($page == $i)
				$p.=$this->parse("SEL_PAGE");
			else
				$p.=$this->parse("PAGE");
		}
		if ($num_pages > 1)
		{
			$this->vars(array("PAGE" => $p,"SEL_PAGE" => ""));
			$ps = $this->parse("PAGES");
		}

		$this->vars(array("PAGES" => $ps, "count" => $cnt, "section" => $section));

		return $this->parse();
	}

	function get_page_for_comment($section, $cid)
	{
		$this->quote(&$section);
		$this->quote(&$cid);

		global $msgboard_type;
		if ($msgboard_type == "flat")	// the wussy version
		{
			$cnt=0;
			$this->db_query("SELECT * FROM comments WHERE board_id = '$section' ORDER BY time");
			while ($row = $this->db_next())
			{
				if ($row["id"] == $cid)
					break;
				$cnt++;
			}
			return sprintf("%4.0f",$cnt / PER_FLAT_PAGE);
		}
		else	// the cool version
		{
			// 1st find the top level comment for $cid
			$parent = $cid;
			while ($parent > 1)
				$parent = $this->db_fetch_field("SELECT parent FROM comments WHERE id = $parent", "parent");

			// now find the page that top level comment is on
			$cnt=0;
			$this->db_query("SELECT * FROM comments WHERE board_id = '$section' AND parent = 0");
			while ($row = $this->db_next())
			{
				if ($row["id"] == $parent)
					break;
				$cnt++;
			}
			return sprintf("%4.0f",$cnt / PER_PAGE);
		}
	}

	function delete_comment($id)
	{
		$id = (int)$id;
		$this->db_query("DELETE FROM comments WHERE id = $id");
	}

	function rename_topic($name,$id)
	{
		print "renaming forum $id to $name<br>";
	}

	function list_topics($forum_id)
	{
		global $page;
		global $HTTP_SESSION_VARS;
		$votes = $HTTP_SESSION_VARS["commentvotes"];

		$obj = $this->get_obj_meta($forum_id);
		if ($obj["meta"]["template"])
		{
			$this->tpl_init("../" . $obj["meta"]["template"] . "/msgboard");
		};
		$this->read_template("list_topics.tpl");
		global $section;
		$this->vars(array("forum_id" => $forum_id,"section" => $section));

		$this->db_query("SELECT COUNT(id) as cnt ,board_id, MAX(time) as mtime FROM comments GROUP BY board_id");
		while ($row = $this->db_next())
		{
			$numcomments[$row["board_id"]] = array("cnt" => $row["cnt"], "mtime" => $row["mtime"]);
		}

		global $aw_mb_last;
		$aw_mb_last = unserialize($aw_mb_last);

		$count = $this->db_fetch_field("SELECT count(*) as cnt FROM objects WHERE class_id = ".CL_MSGBOARD_TOPIC." AND status = 2 AND parent = $forum_id","cnt");
		if ($count > TOPICS_PER_PAGE)
		{
			for ($i=0; $i < ($count / TOPICS_PER_PAGE); $i++)
			{
				$this->vars(array("pagenum" => $i, "ltext" => $i));
				if ($i == $page)
				{
					$pages.=$this->parse("SEL_PAGE");
				}
				else
				{
					$pages.=$this->parse("PAGE");
				}
			}
			$this->vars(array("PAGE" => $pages, "SEL_PAGE" => ""));
			$pages = $this->parse("PAGES");
		}
		$this->vars(array("PAGES" => $pages));

		$can_delete = false;
		if ($this->prog_acl("view", PRG_MENUEDIT))
		{
			$can_delete = true;
		}
		$this->line = 0;
		$this->db_query("SELECT objects.* FROM objects WHERE class_id = ".CL_MSGBOARD_TOPIC." AND status = 2  AND parent = $forum_id ORDER BY objects.modified DESC");

		global $forum_parents;
		global $section;
	
		while ($row = $this->db_next())
		{
		
			$forum_parents[$row[oid]] = $section;
			if ($cnt >= ($page * TOPICS_PER_PAGE) && $cnt <= (($page+1) * TOPICS_PER_PAGE))
			{
				$nc = $numcomments[$row[oid]]["cnt"];
				$lc = $numcomments[$row[oid]]["mtime"];
				// FIXME: We already have the data from the previous query
				$this->save_handle();

				$votedata = $this->get_object_metadata(array(
								"oid" => $row["oid"],
								"key" => "votes",
				));
				
				$this->restore_handle();
				$votecount = ($votedata["votes"]) ? $votedata["votes"] : 1;
				$this->vars(array(
					"topic" => $row[name], 
					"from" => $row[last],
					"created" => $this->time2date($row[created],2), 
					"text" => str_replace("\n","<br>",$row[comment]),
					"topic_id" => $row[oid],
					"cnt" => ( $nc < 1 ? "0" : $nc),
					"rate" => sprintf("%0.2f",$votedata["total"] / $votecount),
					"lastmessage" => $this->time2date($lc,2)
				));
				 // priviligeerimata kasutajad ei nae kustuta linki. praegu kasutan menuediti oigusi selleks 
				$dt = $this->prog_acl("view",PRG_MENUEDIT) ? $this->parse("DELETE") : "";

				$this->save_handle();
				if ($aw_mb_last[$row[oid]] < 1)
				{
					$nc = $this->parse("NEW_MSGS");
				}
				else
				{
					$tm = $aw_mb_last[$row[oid]];
					$nnew = $this->db_fetch_field("SELECT count(*) as cnt FROM comments WHERE comments.time >= $tm AND board_id = '".$row[oid]."'","cnt");
					$nc = $nnew > 0 ? $this->parse("NEW_MSGS") : "";
				}
				$this->restore_handle();

				$this->vars(array("DELETE" => $dt,"NEW_MSGS" => $nc));
				$l.=$this->parse($this->line & 1 ? "TOPIC_EVEN" : "TOPIC_ODD");
				$l .= (isset($votes[$row["oid"]])) ? $this->parse("ALREADY_VOTED") : $this->parse("VOTE_FOR_TOPIC");
				$this->line++;
			}
			$cnt++;
		}
		$this->vars(array("TOPIC_EVEN" => $l, "TOPIC_ODD" => "", "DELETE" => ""));
		session_register("forum_parents");
		return $this->parse();
	}

	function list_topics_detail()
	{
		global $page;
		$this->read_template("list_topics_detail.tpl");

		global $aw_mb_last;
		// lauri muudetud --> stripslashes()
		$this->aw_mb_last = unserialize(stripslashes($aw_mb_last));

		$count = $this->db_fetch_field("SELECT count(*) as cnt FROM objects WHERE class_id = ".CL_MSGBOARD_TOPIC." AND status = 2 ","cnt");
		if ($count > TOPICS_PER_PAGE)
		{
			for ($i=0; $i < ($count / TOPICS_PER_PAGE); $i++)
			{
				$this->vars(array("pagenum" => $i, "ltext" => $i));
				if ($i == $page)
					$pages.=$this->parse("SEL_PAGE");
				else
					$pages.=$this->parse("PAGE");
			}
			$this->vars(array("PAGE" => $pages, "SEL_PAGE" => ""));
			$pages = $this->parse("PAGES");
		}
		$this->vars(array("PAGES" => $pages));

		$msgcache = array();
		$topics = array();
		$topicarr = array();
		$this->db_query("SELECT objects.* FROM objects WHERE class_id = ".CL_MSGBOARD_TOPIC." AND status = 2  ORDER BY objects.created DESC");
		while ($row = $this->db_next())
		{
			if ($cnt >= ($page * TOPICS_PER_PAGE) && $cnt <= (($page+1) * TOPICS_PER_PAGE))
			{
				$topics[] = $row[oid];
				$topicarr[] = $row;
			}
		}

		$topicss = join(",",$this->map("'%s'",$topics));
		if ($topicss != "")
		{
			// teeme kommentaaridest suure array, mida kasutame puu n2itamisel
			$this->db_query("SELECT * FROM comments WHERE board_id IN ($topicss)");
			while ($row = $this->db_next())
			{
				if (!$row[parent])
				{
					$row[parent] = $row[board_id];
				}
				$msgcache[$row[parent]][] = $row;
			}
		}

		global $baseurl;
		
		$this->line = 0;
		reset($topicarr);
		while (list(,$row) = each($topicarr))
		{
			$image = "<img src='$baseurl/images/foorumimgs/miinus.gif' width='9' height='100%'>";
			if (!is_array($msgcache[$row[oid]]))
			{
				$image = "<img src='$baseurl/images/foorumimgs/pluss.gif' width='9' height='100%'>";
			}
			$this->vars(array("topic" => $row[name], "from" => $row[last], "created" => $this->time2date($row[created],2),
												"image" => $image,"topic_id" => $row[oid]));

			$n = $this->aw_mb_last[$row[oid]] < $row[created] ? $this->parse("NEW") : "";
			$this->vars(array("NEW" => $n));
			$l.=$this->parse($this->line & 1 ? "TOPIC_EVEN" : "TOPIC_ODD");
			$this->line++;

			$this->topic = $row[oid];
			$l.=$this->req_msgs_short($row[oid],&$msgcache,"<img src='/images/transa.gif' width='9' height='100%'>","",false,true);
		}
		$this->vars(array("TOPIC_EVEN" => $l, "TOPIC_ODD" => ""));
		return $this->parse();
	}

	////
	// !generates list of messafes that only includes message subject and sender, assumes messages are cached
	function req_msgs_short($parent, &$msgcache,$img_prefix,$add, $center,$firstlevel = false)
	{
		if (!is_array($msgcache[$parent]))
		{
			return "";
		}

		$num = count($msgcache[$parent]);

		global $baseurl;
		$cnt = 1;
		reset($msgcache[$parent]);
		while (list(,$v) = each($msgcache[$parent]))
		{
			$image = "<img src='".$baseurl."/images/transa.gif' width='9' height='100%'>";
			$nadd = "<img src='".$baseurl."/images/transa.gif' width='9' height='100%'>";

			$center = true;

			if ($cnt >= $num)
			{
				if (!is_array($msgcache[$v[id]]))
				{
					$image = "<img src='".$baseurl."/images/foorumimgs/lopp.gif' width='9' height='100%'>";
				}
				else
				{
					$image = "<img src='".$baseurl."/images/foorumimgs/vahe.gif' width='9' height='100%'>";
				}
				$center= false;
			}

			if ($num == 1)
			{
				if (!is_array($msgcache[$v[id]]))
				{
					$image = "<img src='".$baseurl."/images/foorumimgs/pk.gif' width='9' height='100%'>";
				}
				else
				{
					$image = "<img src='".$baseurl."/images/foorumimgs/miinus.gif' width='9' height='100%'>";
					$nadd = "<img src='".$baseurl."/images/foorumimgs/lopp.gif' width='9' height='100%'>";
				}
				$center = false;
			}

			if ($center)
			{
				$image = "<img src='".$baseurl."/images/foorumimgs/vahe.gif' width='9' height='100%'>";
			}

			if ($cnt == 1 && $num != 1)
			{
				$image = "<img src='".$baseurl."/images/foorumimgs/miinus.gif' width='9' height='100%'>";
			}

			$tip = $img_prefix;
			if ($firstlevel && $cnt == 1)
			{
				$img_prefix = "<img src='".$baseurl."/images/foorumimgs/l2opp.gif' width='9' height='100%'>";
			}
			$img_prefix = str_replace("vahe.gif","kriips.gif",$img_prefix);
			$img_prefix = str_replace("foorumimgs/lopp.gif","transa.gif",$img_prefix);

			if (!(($cnt == 1 && $num != 1) || $num == 1))
			{
				$add = str_replace("vahe.gif","kriips.gif",$add);
			}
		
			if ($num != 1 && $cnt > 1)
			{
				$add = str_replace("foorumimgs/lopp.gif","transa.gif",$add);
			}

			$this->vars(array("topic" => $v[subj], "from" => $v[name], "created" => $this->time2date($v[time],2),
												"image" => $img_prefix.$add.$image,"msg_id" => $v[id]));
		
			$n = $this->aw_mb_last[$this->topic] < $v[time] ? $this->parse("NEW") : "";
			$this->vars(array("NEW" => $n));

			$l.=$this->parse($this->line & 1 ? "TOPIC_EVEN" : "TOPIC_ODD");
			$this->line++;

			$img_prefix = $tip;

			if ($center)
			{
				$nadd = "<img src='".$baseurl."/images/foorumimgs/vahe.gif' width='9' height='100%'>";
			}

			if ($cnt >= $num)
			{
				if (is_array($msgcache[$v[id]]))
				{
					$nadd = "<img src='".$baseurl."/images/foorumimgs/lopp.gif' width='9' height='100%'>";
				}
			}

			$l.=$this->req_msgs_short($v[id],&$msgcache,$img_prefix.$add,$nadd,$center);
			$cnt++;
		}

		return $l;
	}

	function add_topic($forum_id)
	{
		$this->read_template("add_topic.tpl");
		$this->vars(array(
			"forum_id" => $forum_id,
			"section" => $GLOBALS["section"],
		));
		return $this->parse();
	}

	function submit_topic($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		global $ext, $baseurl;

		$tid = $this->new_object(array("parent" => $forum_id, "name" => $topic, "last" => $from, "comment" => $text,"class_id" => CL_MSGBOARD_TOPIC,"status" => 2));
		// siin tuleb meili saata ka
		if (MSGBOARD_MAIL_TOPIC_TO != "")
		{
			mail(
				MSGBOARD_MAIL_TOPIC_TO,
				MSGBORARD_NOTIFY_SUBJ,
				sprintf(MSGBOARD_NOTIFY_BODY,$from,$topic,($baseurl."/comments.$ext?section=".$tid."&type=flat&forum_id=$forum_id"))
			);
		}
	}

	function delete_topic($id)
	{
		if ($GLOBALS["uid"] != "")
		{
			$id = (int)$id;
			$this->delete_object($id);
			$this->db_query("DELETE FROM comments WHERE board_id = '$id'");
		}
	}

	function mk_last5()
	{
		$this->read_template("last5.tpl");
		$this->db_query("SELECT objects.*,count(comments.id) as msgcnt FROM objects LEFT JOIN comments ON comments.board_id = objects.oid WHERE class_id = ".CL_MSGBOARD_TOPIC." AND objects.status = 2  GROUP BY objects.oid ORDER BY objects.modified DESC LIMIT 5");
		while ($row = $this->db_next())
		{
			$this->vars(array("topic_id" => $row[oid], "name" => $row[name],"msgs" => $row["msgcnt"]));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l));
		return $this->parse();
	}

	function markallread($forum_id)
	{
		global $aw_mb_last;
		// lauri muudetud --> stripslashes()
		$aw_mb_last = unserialize(stripslashes($aw_mb_last));

		$this->db_query("SELECT objects.* FROM objects WHERE class_id = ".CL_MSGBOARD_TOPIC." AND status = 2  AND parent = $forum_id ORDER BY objects.created DESC");
		while ($row = $this->db_next())
		{
			$aw_mb_last[$row[oid]] = time();
		}
		setcookie("aw_mb_last",serialize($aw_mb_last),time()+24*3600*1000,"/");
	}

	function show_threaded($id,$page,$forum_id)
	{
		$id = $id+0;
		global $msg;

		$this->read_template("messages_threaded.tpl");
		$this->vars(array("forum_id" => $forum_id));
		global $baseurl;
		global $aw_mb_last;
		$message= array();
		$this->aw_mb_last = unserialize($aw_mb_last);

		$this->db_query("SELECT * FROM comments WHERE board_id = '$id'");
		while ($row = $this->db_next())
		{
			if (!$row[parent])
			{
				$row[parent] = $id;
			}
			$msgcache[$row[parent]][] = $row;
			if ($row[id] == $msg)
			{
				$message = $row;
			}
		}

		$this->db_query("SELECT * FROM objects WHERE oid = '$id'");
		$row = $this->db_next();
		if ($row["class_id"] != CL_MSGBOARD_TOPIC)
		{
			$row["last"] = "";
		}
		global $baseurl;
		$image = "<img src='".$baseurl."/images/foorumimgs/miinus.gif' width='9' height='100%'>";
		if (!is_array($msgcache[$id]))
		{
			$image = "<img src='".$baseurl."/images/foorumimgs/pluss.gif' width='9' height='100%'>";
		}
		$this->vars(array("topic" => $row[name], "from" => $row[last], "created" => $this->time2date($row[created],2),
											"image" => $image,"topic_id" => $row[oid]));

		$n = $this->aw_mb_last[$row[oid]] < $row[created] ? $this->parse("NEW") : "";
		$this->vars(array("NEW" => $n));
		$l.=$this->parse($this->line & 1 ? "TOPIC_EVEN" : "TOPIC_ODD");
		$this->line++;

		$this->topic = $row[oid];
		$l.=$this->req_msgs_short($row[oid],&$msgcache,"<img src='".$baseurl."/images/transa.gif' width='9' height='100%'>","",false,true);
		$this->vars(array("TOPIC_EVEN" => $l, "TOPIC_ODD" => ""));
		
		// if no comment was selected, this means the topic is selecteed, so show it. 
		if (!$msg)
		{
			$this->vars(array("subj" => $row[name], "author" => $row[last], "date" => $this->time2date($row[created],2),
												"message" => $this->proc_show_msg($row[comment])));
			$time = $row[created];
			$subj = $row[name];
			$comment = $row[comment];
		}
		else
		{
			$author = $message[name];
			if ($message[email] != "")
			{
				$author = '<a href="mailto:'.$message[email].'">'.$author.'</a>';
			}
			$this->vars(array("subj" => $message[subj], "author" => $author, "date" => $this->time2date($message[time],2),
												"message" => $this->proc_show_msg($message[comment]),"ip" => $message[ip]));
			$time = $message[time];
			$subj = $message[subj];
			$comment = $message[comment];
		}

		$this->aw_mb_last[$row[oid]] = time();
		setcookie("aw_mb_last",serialize($this->aw_mb_last),time()+24*3600*1000,"/");

		$subj = strpos($subj,"Re:")===false ? "Re: ".$subj : $subj;
		$comment = join("\r\n",$this->map("> %s",explode("\r\n",wordwrap($comment,33,"\r\n",1))));

		
		$l = (isset($votes[$row["oid"]])) ? $this->parse("ALREADY_VOTED") : $this->parse("VOTE_FOR_TOPIC");
		$this->vars(array(
			"ALREADY_VOTED" => $l,
		));

		$this->vars(array("a_subj" => $subj, "a_comment" => $comment,
											"topic_id" => $id, "msg_id" => $msg ? $msg : 0));

		return $this->parse();
	}

	function proc_show_msg($msg)
	{
		$ret = nl2br($msg);
		$ret = preg_replace("/&lt;http(.*)&gt;/","<a href='http\\1'>http\\1</a>",$ret);
		return preg_replace("/&lt;ftp(.*)&gt;/","<a href='ftp\\1'>ftp\\1</a>",$ret);
	}

	function get_count_all($topic)
	{
		$this->db_query("SELECT COUNT(board_id) AS cnt,board_id FROM comments WHERE board_id LIKE '$topic' GROUP BY board_id");
		while ($row=$this->db_next())
		{
			$arr[$row["board_id"]]=$row["cnt"];
		};

		return $arr;
	}

	function get_count_new($topic)
	{
		global $aw_mb_last;
		$a=unserialize(stripslashes($aw_mb_last));
		if (!is_array($a))
		{
			return array();
		};

		$board=array();
		$this->db_query("SELECT board_id FROM comments WHERE board_id LIKE '$topic'");
		while ($row=$this->db_next())
		{
			$board[$row["board_id"]]=$row["board_id"];
		};

		foreach ($board as $k => $v)
		{
			if ($a[$v])
			{
				$tm= $a[$v];
				$arr[$v]=$this->db_fetch_field("SELECT COUNT(*) as cnt FROM comments WHERE board_id= '$v' AND comments.time>= '$tm'","cnt");
			};
		};

		return $arr;
	}
};
?>
