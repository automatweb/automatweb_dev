<?php
// poll.aw - Generic poll handling class
// $Header: /home/cvs/automatweb_dev/classes/Attic/poll.aw,v 2.3 2002/01/11 21:17:43 duke Exp $
session_register("poll_clicked");
global $class_defs;
$class_defs["poll"] = "xml";
class poll extends aw_template 
{
	function poll()
	{
		$this->db_init();
		$this->tpl_init("poll");
		lc_site_load("poll",&$this);
		global $lc_poll;
		if (is_array($lc_poll))
		{
			$this->vars($lc_poll);
		}
		//$this->sub_merge = 1;
	}

	////
	// !Displays the admin interface for the poll
	function poll_list($args = array())
	{
		extract($args);
		global $lc_poll;
		lc_load("poll");
		$this->vars($lc_poll);
		$this->read_adm_template("list.tpl");


		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "poll",
			"imgurl"    => $GLOBALS["baseurl"]."/automatweb/images",
			"tbgcolor" => "#C3D0DC",
		));
			
		$t->parse_xml_def($GLOBALS["basedir"]."/xml/generic_table.xml");

		$t->set_header_attribs(array(
			"class" => "poll",
			"action" => "list",
                ));

		$t->define_field(array(
			"name" => "name",
			"caption" => $lc_poll["LC_POLL_QUESTION"],
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
                ));
		
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => $lc_poll["LC_POLL_MUUTJA"],
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
                ));
		
		$t->define_field(array(
			"name" => "modified",
			"caption" => $lc_poll["LC_POLL_CHANGED"],
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
                ));
		
		$t->define_field(array(
			"name" => "active",
			"caption" => $lc_poll["LC_POLL_ACTIVITY"],
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
                ));
		
		$t->define_field(array(
			"name" => "change",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
                ));
		
		$t->define_field(array(
			"name" => "delete",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
                ));

		$this->db_query("SELECT * FROM objects
				WHERE class_id = ".CL_POLL." AND
				status != 0 AND parent = ".$GLOBALS["SITE_ID"]);

		while ($row = $this->db_next())
		{
			$this->vars(array(
				"change_url" => $this->mk_my_orb("change",array("id" => $row["oid"])),
				"delete_url" => $this->mk_my_orb("delete",array("id" => $row["oid"])),
				"activate_url" => $this->mk_my_orb("set_active",array("id" => $row["oid"])),
			));
				
			$t->define_data(array(
				"name" => $row["name"],
				"modified" => $this->time2date($row["modified"],2),
				"modifiedby" => $row["modifiedby"],
				"active" => ($row["status"] == 2) ? $this->parse("ACTIVE") : $this->parse("NACTIVE"),
				"change" => $this->parse("CHANGE"),
				"delete" => $this->parse("DELETE"),
			));
				
		}

		$t->sort_by(array("field" => $args["sortby"]));
		$this->vars(array(
			"table" => $t->draw(),
			"newpoll_url" => $this->mk_my_orb("new",array()),
		));
		return $this->parse();
	}

	////
	// !Displays the form for adding a new poll
	function add($args = array())
	{
		extract($args);
		global $lc_poll;
		lc_load("poll");
		$this->vars($lc_poll);
		$this->read_adm_template("add.tpl");
		$this->vars(array(
			"id" => 0,
		));
		return $this->parse();
	}

	////
	// !Submits a poll
	function submit($arr)
	{
		$this->quote($arr);
		extract($arr);


		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
			));
			
			if (is_array($answer))
			{
				foreach($answer as $aid => $la)
				{
					if ($aid == 0)
					{
						$this->db_query("INSERT INTO poll_answers(answer,poll_id) values('$la','$id')");
					}
					else
					{
						if ($la == "")
						{
							$this->db_query("DELETE FROM poll_answers WHERE id = '$aid'");
						}
						else
						{
							$this->db_query("UPDATE poll_answers SET answer = '$la' WHERE id = '$aid'");
						}
					}
				}
			}
		}
		else
		{
			$id = $this->new_object(array(
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_POLL,
				"status" => 1,
				"parent" => $GLOBALS["SITE_ID"],
			));
		}
		return $this->mk_my_orb("change",array("id" => $id));

	}

	function get_answers($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM poll_answers WHERE poll_id = $id ORDER BY id");
		while ($row = $this->db_next())
		{
			//$row["answer"] = aw_unserialize($row["answer"]);
			$ret[$row["id"]] = $row;
		}
		return $ret;
	}

	function get_active_poll()
	{
		$this->db_query("SELECT objects.* FROM objects WHERE class_id = ".CL_POLL." AND status = 2 AND parent =".$GLOBALS["SITE_ID"]);
		return $this->db_next();
	}

	////
	// !Shows the form for altering a poll
	function change($args = array())
	{
		extract($args);
		global $lc_poll;
		lc_load("poll");
		$this->vars($lc_poll);
		$this->read_adm_template("add.tpl");
		$this->mk_path(0,"<a href='" . $this->mk_my_orb("list",array()) . "'>Pollid</a>");

		$poll = $this->get_object($id);
		
		//$questions = aw_unserialize($poll["questions"]);

		//classload("languages");
		//$l = new languages;
		//$langs = $l->listall();
		//foreach($langs as $lang)
		//{
		//	$this->vars(array(
		//		"lang_name" => $lang["name"],
		//		"lang_id" => $lang["id"],
		//		"question" => $questions[$lang["id"]]
		//	));
		//	$this->parse("LANG");
		//	$this->parse("Q_LANG");
		//}

		$answers = $this->get_answers($id);

		// provides an empty line for adding a new variant
		$answers[0]["answer"] = "";
		reset($answers);
		$al = "";
		while (list($aid,$v) = each($answers))
		{
			//foreach($langs as $lang)
			//{
				$this->vars(array(
					"lang_name" => $lang["name"],
					"lang_id" => $lang["id"],
					"answer_id" => $aid, 
					"answer" => $v["answer"],
				));
				$al.=$this->parse("QUESTION");
			//}
			$this->vars(array("A_LANG" => $al));
			//$this->parse("ANSWER");
		}

		$this->vars(array(
			"name" => $poll["name"],
			"comment" => $poll["comment"],
			"QUESTION" => $al,
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Sets an active poll
	function set_active($args = array())
	{
		extract($args);
		$this->db_query("UPDATE objects SET status = 1 WHERE class_id = ".CL_POLL." AND parent = ".$GLOBALS["SITE_ID"]." AND status = 2 ");
		$this->db_query("UPDATE objects SET status = 2 WHERE oid = $id");
		return $this->mk_my_orb("list",array());
	}

	////
	// !Generates HTML for the user
	function gen_user_html()
	{
		$this->read_template("poll.tpl");

		if (!($ap = $this->get_active_poll()))
			return "";

		//global $lang_id;
		$this->vars(array("poll_id" => $ap[oid], "question" => $ap["name"]));

		$ans = $this->get_answers($ap["oid"]);
		reset($ans);
		while (list($k,$v) = each($ans))
		{
			$answer = ($v["answer"]) ? $v["answer"]: $v["answer"];
			$this->vars(array("answer_id" => $k, "answer" => $answer));
			$as.=$this->parse("ANSWER");
		}
		$this->vars(array("ANSWER" => $as));
		return $this->parse();
	}

	function add_click($aid)
	{
		global $polls_clicked;
		$poa = unserialize($polls_clicked);

		$poll_id = $this->db_fetch_field("SELECT poll_id FROM poll_answers WHERE id = $aid", "poll_id");

		if ($poa[$poll_id] != 1)
		{
			$this->db_query("UPDATE poll_answers SET clicks=clicks+1 WHERE id = $aid");
		}

		$poa[$poll_id] = 1;
		setcookie("polls_clicked", serialize($poa),time()+24*3600*1000,"/");
	}

	function show($id)
	{
		if (!is_number($id))
			return "";

		$this->read_template("show.tpl");

		if (!($poll = $this->get_object($id)))
			return "";
		global $lang_id;

		$answers = $this->get_answers($id);
		$total = 0;
		reset($answers);
		while(list($k,$v) = each($answers))
			$total += $v[clicks];
	
		reset($answers);
		while(list($k,$v) = each($answers))
		{
			$percent = $total ? (($v[clicks] / $total) * 100) : 0;
			$width = sprintf("%2.0f", $percent);
			$percent = sprintf("%2.1f", $percent);
			if ($GLOBALS["lang_id"] == 1)
			{
				$percent = str_replace(".",",",$percent);
			}
			$this->vars(array("answer" => $v[answer], "percent" => $percent, "width" => $width*2));
			$as.=$this->parse("ANSWER");
		}

		$this->vars(array("total_answers" => $total));

		classload("msgboard");
		$t = new msgboard;

		// pollide arhiiv
		$this->db_query("SELECT objects.* FROM objects WHERE class_id = ".CL_POLL." AND status != 0 AND parent = ".$GLOBALS["SITE_ID"]);
		while ($row = $this->db_next())
		{
			if ($id != $row[oid])
			{
				//$qs = aw_unserialize($row["questions"]);
				$this->vars(array("question" => $row["name"], "poll_id" => $row[oid], "num_comments" => $t->get_num_comments($row[oid])));
				$p.=$this->parse("QUESTION");
			}
		}

		$qs = aw_unserialize($poll["questions"]);


		$this->vars(array("ANSWER" => $as,"question" => $qs[$lang_id], "date" => $this->time2date($poll[modified],2),"addcomment" => $t->add(0,$id,0),"num_comments" => $t->get_num_comments($id), "poll_id" => $id, "QUESTION" => $p));

		return $this->parse();
	}
}
?>
