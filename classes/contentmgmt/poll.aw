<?php
// poll.aw - Generic poll handling class
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/poll.aw,v 1.4 2002/12/24 15:19:08 kristo Exp $
session_register("poll_clicked");

// poll.aw - it sucks more than my aunt jemimas vacuuming machine 
//
// latest version - all answer data is in metadata "answers"[lang_id][answer_id] array, poll_answers is just to count clicks. 


/*

@classinfo trans=1
@classinfo relationmgr=yes
@classinfo syslog_type=ST_POLL

@groupinfo clicks caption=Klikke
@groupinfo translate caption=T&otilde;lgi
@groupinfo activity caption=Aktiivsus

@property clicks type=text store=no group=clicks
@caption Klikid

@default group=general

@property in_archive type=checkbox ch_value=1 field=meta method=serialize table=objects
@caption Arhiivis

@property in_archive type=checkbox ch_value=1 field=meta method=serialize table=objects
@caption Arhiivis

@property question type=textbox store=no
@caption K&uuml;simus

@property answers type=callback callback=callback_get_answers store=no
@caption Vastused

@property activity type=table group=activity no_caption=1
@caption Aktiivsus

@property translate type=callback group=translate callback=callback_get_translate store=no
@caption T&otilde;lgi


*/

class poll extends class_base
{
	function poll()
	{
		$this->init(array(
			"tpldir" => "poll",
			"clid" => CL_POLL
		));
		lc_site_load("poll",&$this);
	}

	function get_answers($id)
	{
		$o = obj($id);
		$ans = $o->meta("answers");

		$ret = $ans[$o->lang_id()];

		$data = array();
		$this->db_query("SELECT * FROM poll_answers WHERE poll_id = '$id' ORDER BY id");
		while ($row = $this->db_next())
		{
			$data[$row["id"]] = $row;
		}

		$awa = new aw_array($ans[aw_global_get("lang_id")]);
		foreach($awa->get() as $aid => $aval)
		{
			$data[$aid]["answer"] = $aval;
			$ret[$aid] = $data[$aid];
		}

		if (!is_array($ret))
		{
			return array();
		}
		return $ret;
	}

	function get_active_poll()
	{
		$apid = $this->get_cval("active_poll_id_".aw_ini_get("site_id"));
		if (!$apid)
		{
			// try the old way
			$apid = $this->db_fetch_field("SELECT oid FROM objects WHERE class_id = ".CL_POLL." AND status = 2 AND parent =".aw_ini_get("site_id"),"oid");
			if (!$apid)
			{
				// try the oldest way
				$apid = $this->db_fetch_field("SELECT oid FROM objects WHERE class_id = ".CL_POLL." AND status = 2 ","oid");
			}
		}
		return obj($apid);
	}

	////
	// !Generates HTML for the user
	function gen_user_html($id = false)
	{
		if ($id)
		{
			$ap = obj($id);
			$this->read_any_template("poll_embed.tpl");
		}
		else
		{
			if (!($ap = $this->get_active_poll()))
			{
				return "";
			}
			$def = true;
			$this->read_any_template("poll.tpl");
		}

		if ($GLOBALS["answer_id"] && !$GLOBALS["class"] && $GLOBALS["poll_id"] == $id)
		{
			return $this->show($GLOBALS["poll_id"]);
		}

		$lid = aw_global_get("lang_id");
		$section = aw_global_get("section");


		if (is_array($ap->meta("name")))
		{
			$namear = $ap->meta("name");
		}
		else
		{
			$namear = $ap->meta("names");
		}

		$this->vars(array(
			"poll_id" => $ap->id(), 
			"question" => ($namear[$lid] == "" ? $ap->name() : $namear[$lid]),
			"set_lang_id" => $lid
		));

		$ans = $this->get_answers($ap->id());

		reset($ans);
		while (list($k,$v) = each($ans))
		{
			if ($def)	 
			{	 
				$au = $this->mk_my_orb("show", array("poll_id" => $ap->id(), "answer_id" => $k, "section" => aw_global_get("section")));
			}	 
			else	 
			{	 
				$au = "/?section=".$section."&poll_id=".$ap->id()."&answer_id=".$k."&section=".aw_global_get("section");	 
			}
			$this->dequote(&$v["answer"]);

			$this->vars(array(
				"answer_id" => $k, 
				"answer" => $v["answer"], 
				"click_answer" => str_replace("&", "&amp;", $au),
				"clicks" => $v["clicks"],
			));
			$as.=$this->parse("ANSWER");
		}
		if ($def)
		{
			$au = $this->mk_my_orb("show", array("poll_id" => $ap->id()));
		}
		else
		{
			$au = "/?section=".$section."&poll_id=".$ap->id();
		}
		$this->vars(array(
			"ANSWER" => $as,
			"show_url" => str_replace("&", "&amp;", $au),
			"section" => aw_global_get("section")
		));
		$str =  $this->parse();
		return $str;
	}

	function add_click($aid)
	{
		global $polls_clicked;
		$poa = unserialize($polls_clicked);

		$poll_id = $this->db_fetch_field("SELECT poll_id FROM poll_answers WHERE id = $aid", "poll_id");

		if ($poa[$poll_id] != 1)
		{
			$REMOTE_ADDR = aw_global_get("REMOTE_ADDR");
			$ip = aw_global_get("HTTP_X_FORWARDED_FOR");
			if (!inet::is_ip($ip))
			{
				$ip = $REMOTE_ADDR;
			}
			$this->db_query("UPDATE poll_answers SET clicks=clicks+1 WHERE id = $aid");
			$this->db_query("INSERT INTO poll_clicks(uid, ip, date, poll_id, answer_id) VALUES('".aw_global_get("uid")."','$ip',".time().",'$poll_id','$aid')");
		}

		$poa[$poll_id] = 1;
		setcookie("polls_clicked", serialize($poa),time()+24*3600*1000,"/");
	}

	/**  
		
		@attrib name=show params=name nologin="1" default="0"
		
		@param poll_id required type=int
		@param answer_id optional type=int
		
		@returns
		
		
		@comment

	**/
	function show($id)
	{
		if (is_array($id))
		{
			// orb call
			extract($id);
			$id = $poll_id;
			$def = true;
		}
		if (!is_number($id))
		{
			return "";
		}

		global $answer_id;
		if ($answer_id && $GLOBALS["poll_id"] == $id)
		{
			$this->add_click($answer_id);
		}

		$this->read_template("show.tpl");

		if (!$this->object_exists($id) || !$this->can("view", $id))
		{
			return "";
		}

		$poll = obj($id);

		$lang_id = aw_global_get("lang_id");
		$this->vars(array(
			"set_lang_id" => $lang_id
		));

		$answers = $this->get_answers($id);

		$total = 0;
		reset($answers);
		while(list($k,$v) = each($answers))
		{
			$total += $v["clicks"];
		}
	
		reset($answers);
		while(list($k,$v) = each($answers))
		{
			$percent = $total ? (($v["clicks"] / $total) * 100) : 0;
			$width = sprintf("%2.0f", $percent);
			$percent = sprintf("%2.1f", $percent);
			if ($lang_id == 1)
			{
				$percent = str_replace(".",",",$percent);
			}
			$mp = $this->cfg["result_width_mp"];
			$this->vars(array(
				"answer" => $v["answer"],
				"percent" => $percent,
				"width" => (int)$width*$mp,
				"clicks" => $v["clicks"],
			));
			$as.=$this->parse("ANSWER");
		}

		$this->vars(array("total_answers" => $total));

		$t = get_instance("forum");

		// pollide arhiiv
		$ol = new object_list(array(
			"class_id" => CL_POLL
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($id != $o->id())
			{
				if ($o->meta('in_archive') == 1)
				{
					$this->vars(array(
						"question" => $o->name(), 
						"poll_id" => $o->id(), 
						"num_comments" => $t->get_num_comments($o->id()),
						"link" => $this->mk_my_orb("show", array("poll_id" => $o->id()))
					));
					$p.=$this->parse("QUESTION");
				}
			}
		}

		if (is_array($poll->meta("name")))
		{
			$namear = $poll->meta("name");
		}
		else
		{
			$namear = $poll->meta("names");
		}

		$this->dequote(&$namear);

		$na = $namear[aw_global_get("lang_id")];

		$this->vars(array(
			"ANSWER" => $as,
			"question" => ($na == "" ? $poll->name() : $na),
			"date" => $this->time2date($poll->modified(),2),
			"addcomment" => $t->add_comment(array("board" => $id)), 
			"num_comments" => $t->get_num_comments($id), 
			"poll_id" => $id, 
			"QUESTION" => $p
		));

		if ($def)
		{
			$this->vars(array("HAS_ARCHIVE" => $this->parse("HAS_ARCHIVE")));
		}
		return $this->parse();
	}

	function parse_alias($args = array())
	{
		extract($args);
		if ($alias["target"] == $f["target"])
		{
			return $this->show($f["target"]);
		}
		else
		{
			return $this->gen_user_html($alias["target"]);
		}
	}

	function clicks($arr)
	{
		extract($arr);
		load_vcl("table");
		$this->t = new aw_table(array("prefix" => "images"));
		$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
		$this->t->define_field(array(
			"name" => "uid",
			"caption" => "UID",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "ip",
			"caption" => "IP",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "date",
			"caption" => "Kuup&auml;ev",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.y / H:i"
		));
		$this->t->define_field(array(
			"name" => "answer",
			"caption" => "Vastus",
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));

		$id = $arr["obj_inst"]->id();
		$ansa = $this->get_answers($id);

		$this->db_query("SELECT * FROM poll_clicks WHERE poll_id = '$id' AND answer_id != 0");
		while ($row = $this->db_next())
		{
			$row["answer"] = $ansa[$row["answer_id"]]["answer"];
			list($row["ip"],) = inet::gethostbyaddr($row["ip"]);
			$this->t->define_data($row);
		}

		$this->t->set_default_sortby("date");
		$this->t->sort_by();
		return $this->t->draw();
	}

	function on_get_subtemplate_content($arr)
	{
		$arr["inst"]->vars(array(
			"POLL" => $this->gen_user_html()
		));
	}

	function callback_get_answers($arr)
	{
		$ansa = $arr["obj_inst"]->meta("answers");

		$ret = array();

		$last_id = 0;
		$idx = 0;

		foreach($ansa[aw_global_get("lang_id")] as $a_id => $a)
		{
			$idx++;
			$ret["answers[".$a_id."]"] = array(
				"type" => "textbox",
				"name" => "answers[".$a_id."]",
				"caption" => "Vastus nr $idx",
				"value" => $a
			);

			$last_id = max($a_id, $last_id);
		}
		$last_id ++;
		$idx++;

		$ret["answers[".$last_id."]"] = array(
			"type" => "textbox",
			"name" => "answers[".$last_id."]",
			"caption" => "Vastus nr $idx",
			"value" => ""
		);

		return $ret;
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];

		if ($prop["name"] == "question")
		{
			$qs = $arr["obj_inst"]->meta("name");
			$prop["value"] = $qs[aw_global_get("lang_id")];
		}
		else
		if ($prop["name"] == "clicks")
		{
			$prop["value"] = $this->clicks($arr);
		}
		else
		if ($prop["name"] == "activity")
		{
			$this->mk_activity_table($arr);
		}

		return PROP_OK;
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];

		if ($prop["name"] == "question")
		{
			$qs = $arr["obj_inst"]->meta("name");
			$qs[aw_global_get("lang_id")] = $prop["value"];
			$arr["obj_inst"]->set_meta("name", $qs);
		}
		else
		if ($prop["name"] == "answers")
		{
			$ans = new aw_array($arr["request"]["answers"]);

			$answers = $arr["obj_inst"]->meta("answers");

			$tawa = new aw_array($answers[aw_global_get("lang_id")]);
			$rans = $ans->get();
			foreach($tawa->get() as $id => $val)
			{
				if (!isset($rans[$id]))
				{
					$this->db_query("DELETE FROM poll_answers WHERE id = '$id'");
				}
			}

			$tmpa = array();
			foreach($ans->get() as $id => $val)
			{
				if ($val != "")
				{
					if (!isset($answers[aw_global_get("lang_id")][$id]))
					{
						$tval = $val;
						$this->quote($tval);
						$this->db_query("INSERT INTO poll_answers(answer,poll_id) values('".$tval."','".$arr["obj_inst"]->id()."')");
						$id = $this->db_last_insert_id();
					}
					$tmpa[$id] = $val;
				}
			}

			$answers[aw_global_get("lang_id")] = $tmpa;

			$arr["obj_inst"]->set_meta("answers", $answers);
		}
		else
		if ($prop["name"] == "activity")
		{
			$cfg = get_instance("config");
			$cfg->set_simple_config("active_poll_id_".aw_ini_get("site_id"), $arr["request"]["activeperiod"]);
		}
		else
		if ($prop["name"] == "translate")
		{
			$answers = array();
			$ans = new aw_array($arr["request"]["answers"]);
			foreach($ans->get() as $lid => $ldat)
			{
				$lans = new aw_array($ldat);
				foreach($lans->get() as $aid => $aval)
				{
					if ($aval != "")
					{
						$answers[$lid][$aid] = $aval;
					}
				}
			}

			$arr["obj_inst"]->set_meta("answers", $answers);
			$arr["obj_inst"]->set_meta("name", $arr["request"]["question"]);
		}


		return PROP_OK;
	}

	function mk_activity_table($arr)
	{
		// this is supposed to return a list of all active polls
		// to let the user choose the active one
		$table = &$arr["prop"]["vcl_inst"];
		$table->parse_xml_def("poll/list");

		$active = $this->get_active_poll();
		$active = $active->id();

		$pl = new object_list(array(
			"class_id" => CL_POLL
		));	
		for($o = $pl->begin(); !$pl->end(); $o = $pl->next())
		{
			$actcheck = checked($o->id() == $active);
			$act_html = "<input type='radio' name='activeperiod' $actcheck value='".$o->id()."'>";
			$row = $o->arr();
			$row["active"] = $act_html;
			$table->define_data($row);
		};
	}

	function callback_get_translate($arr)
	{
		$ansa = $arr["obj_inst"]->meta("answers");
		$names = $arr["obj_inst"]->meta("name");

		$ret = array();

		$l = get_instance("languages");
		$lgs = $l->get_list();
		foreach($lgs as $lid => $lname)
		{
			$idx = 0;
			$adat = new aw_array($ansa[aw_global_get("lang_id")]);

			$ret["splitter_".$lid] = array(
				"type" => "text",
				"name" => "splitter_".$lid,
				"caption" => "",
				"no_caption" => 1,
				"value" => "<b>".$lname."</b>"
			);

			$ret["question[$lid]"] = array(
				"type" => "textbox",
				"name" => "question[$lid]",
				"caption" => "K&uuml;simus",
				"value" => $names[$lid]
			);

			foreach($adat->get() as $a_id => $a)
			{
				$idx++;
				$ret["answers[$lid][".$a_id."]"] = array(
					"type" => "textbox",
					"name" => "answers[$lid][".$a_id."]",
					"caption" => "Vastus nr $idx ",
					"value" => $ansa[$lid][$a_id]
				);
			}
		}

		return $ret;
	}
}
?>
