<?php
// poll.aw - Generic poll handling class
// $Header: /home/cvs/automatweb_dev/classes/Attic/poll.aw,v 2.17 2002/09/19 15:11:22 kristo Exp $
session_register("poll_clicked");

// poll.aw - it sucks more than my aunt jemimas vacuuming machine 
//
// the following horribility manages polls. most of the data is in the poll object's metadata, 
// but there is also a poll_answers table so that we can count clicks easily
// but the name field in that table is not actually used
// because polls can be translated to several languages.
// so it is just there to confuse you.
// and it gets better.
//
// the answers that actually exist can be only deducted from that table - the objects metadata
// can containt answers in some of the languages that have been deleted from some others
// so we read the answers from the table and get the text from the objects metadata
// 
// why is this? well, I have said it before, and I will say it again: backwards compatibility SUCKS

class poll extends aw_template 
{
	function poll()
	{
		$this->init("poll");
		lc_site_load("poll",&$this);
	}

	////
	// !Displays the admin interface for the poll
	function poll_list($args = array())
	{
		extract($args);
		$this->lc_load("poll","lc_poll");
		$this->read_adm_template("list.tpl");


		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "poll",
			"tbgcolor" => "#C3D0DC",
		));
			
		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
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

		$ap = $this->get_cval("active_poll_id");

		$this->db_query("SELECT * FROM objects
				WHERE class_id = ".CL_POLL." AND
				status != 0 AND site_id = ".$this->cfg["site_id"]);

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
				"active" => ($row["oid"] == $ap) ? $this->parse("ACTIVE") : $this->parse("NACTIVE"),
				"change" => $this->parse("CHANGE"),
				"delete" => $this->parse("DELETE"),
			));
		}

		$t->sort_by();
		$this->vars(array(
			"table" => $t->draw(),
			"newpoll_url" => $this->mk_my_orb("new",array()),
		));
		return $this->parse();
	}

	////
	// !Deletes a poll
	function delete($args = array())
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"status" => 0,
		));
		return $this->mk_my_orb("list",array());
	}

	////
	// !Displays the form for adding a new poll
	function add($args = array())
	{
		extract($args);
		$this->lc_load("poll","lc_poll");
		if ($return_url)
		{
			$this->mk_path(0,sprintf("<a href='%s'>%s</a> / Lisa poll",$return_url,"Tagasi"));
		}
		else
		{
			$this->mk_path($parent,"Lisa poll");
		};
		$this->read_adm_template("add.tpl");

		$lg = get_instance("languages");
		$ld = $lg->fetch(aw_global_get("lang_id"));

		$this->vars(array(
			"id" => "Uus",
			"lang_id" => aw_global_get("lang_id"),
			"lang" => $ld["name"],
			"clicks" => 0,
			"sum" => 0,
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent,"return_url" => $return_url,"alias_to" => $alias_to)),
		));
		return $this->parse();
	}

	////
	// !Submits a poll
	function submit($arr)
	{
		$this->quote($arr);
		extract($arr);
		$lang_id = aw_global_get("lang_id");

		if ($id)
		{
			$obj = $this->get_object($id);

			if (is_array($answer[$lang_id]))
			{
				foreach($answer[$lang_id] as $aid => $la)
				{
					if ($aid == 0 && (strlen($la) > 0))
					{
						$this->db_query("INSERT INTO poll_answers(answer,poll_id) values('$la','$id')");
						$obj["meta"]["answers"][$lang_id][$this->db_last_insert_id()] = $la;
					}
					else
					{
						if (strlen($la) == 0)
						{
							$q = "DELETE FROM poll_answers WHERE id = '$aid'";
							$this->db_query($q);
							unset($obj["meta"]["answers"][$lang_id][$aid]);
						}
						else
						{
							$q = "UPDATE poll_answers SET answer = '$la' WHERE id = '$aid'"; 
							$this->db_query($q);
							$obj["meta"]["answers"][$lang_id][$aid] = $la;
						}
					}
				}
			}

			$this->upd_object(array(
				"oid" => $id,
				"name" => $name[$lang_id],
				"comment" => $comment[$lang_id],
				"metadata" => array(
					"answers" => $obj["meta"]["answers"],
					"name" => $name,
					"comment" => $comment
				)
			));
		}
		else
		{
			$parent = ($parent) ? $parent : 0;
			$id = $this->new_object(array(
				"name" => $name[$lang_id],
				"comment" => $comment[$lang_id],
				"class_id" => CL_POLL,
				"status" => 1,
				"parent" => $parent,
				"metadata" => array(
					"answers" => $answer,
					"name" => $name,
					"comment" => $comment
				)
			));
		}
		if ($alias_to)
		{
			$this->add_alias($alias_to,$id);
		};
		$retval = $this->mk_my_orb("change",array("id" => $id,"return_url" => $return_url,"alias_to" => $alias_to));
		return $retval;

	}

	function get_answers($id)
	{
		$ret = array();
		$poll_obj = $this->get_obj_meta($id);
		$lang_id = aw_global_get("lang_id");
		$meta_answers = $poll_obj["meta"]["answers"];

		$this->db_query("SELECT * FROM poll_answers WHERE poll_id = $id ORDER BY id");
		while ($row = $this->db_next())
		{
			if (strlen($meta_answers[$lang_id][$row["id"]]) > 0)
			{
				$row["answer"] = $meta_answers[$lang_id][$row["id"]];
			};
			$ret[$row["id"]] = $row;
		}
		return $ret;
	}

	function get_active_poll()
	{
		$apid = $this->get_cval("active_poll_id");
		return $this->get_object($apid);
	}

	////
	// !Shows the form for altering a poll
	function change($args = array())
	{
		extract($args);
		$this->lc_load("poll","lc_poll");
		$this->read_adm_template("add.tpl");
		if ($return_url)
		{
			$this->mk_path(0,sprintf("<a href='%s'>%s</a> / Muuda polli",urldecode($return_url),"Tagasi"));
		}
		else
		{
			$this->mk_path($parent,"<a href='" . $this->mk_my_orb("list",array()) . "'>Pollid</a>");
		};

		$obj = $this->get_object($id);
		$answers = $this->get_answers($id);

		// provides an empty line for adding a new variant
		$answers[0]["answer"] = "";
		reset($answers);
		$al = "";
		$sum = 0;
		// "Ford! There's an infinite number of monkeys outside who
		// want to talk to us about this line of PHP code they've worked out."
		//
		// cute. - terryf
		array_walk($answers,create_function('$val,$key,$sum','$sum = $sum + $val["clicks"];'),&$sum);
				
		$this->vars(array(
			"lang_id" => aw_global_get("lang_id")
		));

		reset($answers);
		while (list($aid,$v) = each($answers))
		{
			$percent = ($sum == 0) ? 0 : $v["clicks"]*100/$sum;
			$this->vars(array(
				"answer_id" => $aid, 
				"answer" => $v["answer"],
				"clicks" => (int)$v["clicks"],
				"percent" => sprintf("%0.02f",$percent),
			));
			$tmp .= $this->parse("QUESTION");
		};

		$l = get_instance("languages");
		$ld = $l->fetch(aw_global_get("lang_id"));

		$this->vars(array(
			"name" => $obj["meta"]["name"][aw_global_get("lang_id")],
			"comment" => $obj["meta"]["comment"][aw_global_get("lang_id")],
			"QUESTION" => $tmp,
			"lang" => $ld["name"],
			"id" => $id,
			"sum" => $sum,
			"reforb" => $this->mk_reforb("submit",array("id" => $id, "return_url" => urlencode($return_url))),
			"translate" => $this->mk_my_orb("translate", array("id" => $id, "return_url" => urlencode($return_url))),
			"clicks" => $this->mk_my_orb("clicks", array("id" => $id)),
		));
		$this->vars(array(
			"CHANGE" => $this->parse("CHANGE")
		));
		return $this->parse();
	}

	////
	// !Sets an active poll
	function set_active($args = array())
	{
		extract($args);
		$cfg = get_instance("config");
		$cfg->set_simple_config("active_poll_id", $id);
		return $this->mk_my_orb("list",array());
	}

	////
	// !Generates HTML for the user
	function gen_user_html($id = false)
	{
		if ($id)
		{
			$ap = $this->get_object($id);
			$this->read_template("poll_embed.tpl");
		}
		else
		{
			if (!($ap = $this->get_active_poll()))
			{
				return "";
			}
			$def = true;
			$this->read_template("poll.tpl");
		}

		$lid = aw_global_get("lang_id");
		$section = aw_global_get("section");

		$this->vars(array(
			"poll_id" => $ap["oid"], 
			"question" => $ap["meta"]["name"][$lid],
			"set_lang_id" => $lid
		));

		$ans = $this->get_answers($ap["oid"]);

		reset($ans);
		while (list($k,$v) = each($ans))
		{
			if ($def)
			{
				$au = $this->mk_my_orb("show", array("poll_id" => $ap["oid"], "answer_id" => $k));
			}
			else
			{
				$au = "/?section=".$section."&poll_id=".$ap["oid"]."&answer_id=".$k;
			}
			$this->vars(array(
				"answer_id" => $k, 
				"answer" => $ap["meta"]["answers"][$lid][$k],
				"click_answer" => str_replace("&", "&amp;", $au)
			));
			$as.=$this->parse("ANSWER");
		}
		if ($def)
		{
			$au = $this->mk_my_orb("show", array("poll_id" => $ap["oid"]));
		}
		else
		{
			$au = "/?section=".$section."&poll_id=".$ap["oid"];
		}
		$this->vars(array(
			"ANSWER" => $as,
			"show_url" => str_replace("&", "&amp;", $au),
			"section" => aw_global_get("section")
		));
		return $this->parse();
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
			if (!is_ip($ip))
			{
				$ip = $REMOTE_ADDR;
			}
			$this->db_query("UPDATE poll_answers SET clicks=clicks+1 WHERE id = $aid");
			$this->db_query("INSERT INTO poll_clicks(uid, ip, date, poll_id, answer_id) VALUES('".aw_global_get("uid")."','$ip',".time().",'$poll_id','$aid')");
		}

		$poa[$poll_id] = 1;
		setcookie("polls_clicked", serialize($poa),time()+24*3600*1000,"/");
	}

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
		if ($answer_id)
		{
			$this->add_click($answer_id);
		}

		$this->read_template("show.tpl");

		if (!($poll = $this->get_object($id)))
		{
			return "";
		}

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
			$this->vars(array("answer" => $v["answer"], "percent" => $percent, "width" => $width*2));
			$as.=$this->parse("ANSWER");
		}

		$this->vars(array("total_answers" => $total));

		classload("forum");
		$t = new forum;

		// pollide arhiiv
		$this->db_query("SELECT objects.* FROM objects WHERE class_id = ".CL_POLL." AND status != 0 AND site_id = ".$this->cfg["site_id"]);
		while ($row = $this->db_next())
		{
			if ($id != $row["oid"])
			{
				//$qs = aw_unserialize($row["questions"]);
				$this->vars(array(
					"question" => $row["name"], 
					"poll_id" => $row["oid"], 
					"num_comments" => $t->get_num_comments($row["oid"]),
					"link" => $this->mk_my_orb("show", array("poll_id" => $row["oid"]))
				));
				$p.=$this->parse("QUESTION");
			}
		}

		$qs = aw_unserialize($poll["questions"]);


		$this->vars(array(
			"ANSWER" => $as,
			"question" => $poll["meta"]["name"][aw_global_get("lang_id")], 
			"date" => $this->time2date($poll["modified"],2),
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
		if (!is_array($this->pollaliases) || $this->pollaliasoid != $oid)
		{
			$this->pollaliases = $this->get_aliases(array(
				"oid" => $oid,
				"type" => CL_POLL,
			));
			$this->pollaliasoid = $oid;
    };
    $f = $this->pollaliases[$matches[3] - 1];
		global $poll_id;
		if ($poll_id)
		{
			return $this->show($f["target"]);
		}
		else
		{
			return $this->gen_user_html($f["target"]);
		}
	}

	////
	// !shows the poll translation interface
	function translate($arr)
	{
		extract($arr);
		$this->read_adm_template("translate.tpl");
		$this->lc_load("poll","lc_poll");
		$this->sub_merge = 1;
		if ($return_url)
		{
			$this->mk_path(0,sprintf("<a href='%s'>%s</a> / <a href='%s'>Muuda polli</a> / T&otilde;lgi polli",urldecode($return_url),"Tagasi", $this->mk_my_orb("change", array("id" => $id, "return_url" => $return_url))));
		}
		else
		{
			$this->mk_path(0,sprintf("<a href='%s'>%s</a> / <a href='%s'>Muuda polli</a> / T&otilde;lgi polli",$this->mk_my_orb("list_polls"),"Pollid", $this->mk_my_orb("change", array("id" => $id))));
		};

		$obj = $this->get_object($id);
		$answers = $this->get_answers($id);

		$l = get_instance("languages");
		$lg = $l->get_list(array("ignore_status" => true));
		foreach($lg as $lid => $lname)
		{
			$this->vars(array(
				"lang_id" => $lid,
				"lang" => $lname,
				"name" => $obj["meta"]["name"][$lid],
				"comment" => $obj["meta"]["comment"][$lid],
			));
			$this->parse("LANG_H");
			$this->parse("LANG_Q");
			$this->parse("LANG_C");
		}

		foreach($answers as $aid => $v)
		{
			$tmp = "";
			$this->vars(array(
				"answer_id" => $aid, 
			));
			foreach($lg as $lid => $lname)
			{
				$this->vars(array(
					"lang_id" => $lid,
					"answer" => $obj["meta"]["answers"][$lid][$aid]
				));
				$tmp.=$this->parse("LANG_A");
			}
			$this->vars(array(
				"LANG_A" => $tmp
			));
			$this->parse("QUESTION");
		};

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_translate", array("id" => $id, "return_url" => urlencode($return_url)))
		));
		return $this->parse();
	}

	function submit_translate($arr)
	{
		extract($arr);

		$this->upd_object(array(
			"oid" => $id,
			"metadata" => array(
				"answers" => $answer,
				"name" => $name,
				"comment" => $comment
			)
		));
		return $this->mk_my_orb("translate", array("id" => $id, "return_url" => $return_url));
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

		$ansa = $this->get_answers($id);

		$this->db_query("SELECT * FROM poll_clicks WHERE poll_id = '$id' AND answer_id != 0");
		while ($row = $this->db_next())
		{
			$row["answer"] = $ansa[$row["answer_id"]]["answer"];
			list($row["ip"],) = aw_gethostbyaddr($row["ip"]);
			$this->t->define_data($row);
		}

		$this->t->set_default_sortby("date");
		$this->t->sort_by();
		return $this->t->draw();
	}
}
?>
