<?php
session_register("poll_clicked");

class poll extends aw_template 
{
	function poll()
	{
		$this->db_init();
		$this->tpl_init("poll");
	}

	function admin()
	{
		$this->read_template("list.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_POLL." AND status != 0 AND parent = ".$GLOBALS["SITE_ID"]);
		while ($row = $this->db_next())
		{
			$this->vars(array("name" => $row[name], "id" => $row[oid], "comment" => $row[comment],"modified" => $this->time2date($row[modified],2), "modifiedby" => $row[modifiedby]));

			if ($row[status] == 2)
				$ac = $this->parse("ACTIVE");
			else
				$ac = $this->parse("NACTIVE");
			$this->vars(array("ACTIVE" => $ac, "NACTIVE" => ""));
			$p.=$this->parse("LINE");
		}

		$this->vars(array("LINE" => $p));
		return $this->parse();
	}

	function add()
	{
		$this->read_template("add.tpl");
		$this->vars(array("id" => 0, "name" => "", "comment" => "","QUESTION" => ""));
		return $this->parse();
	}

	function submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if ($id)
		{
			$answers = $this->get_answers($id);

			reset($arr);
			while (list($k, $v) = each($arr))
			{
				if (substr($k,0,3) == "an_")
				{
					$anid = substr($k,3);
					if (!is_array($answers[$anid]) && $v != "")
					{
						// add new answer
						$this->db_query("INSERT INTO poll_answers(answer,poll_id) VALUES('$v','$id')");
					}

					if ($answers[$anid][answer] != $v)
					{
						if ($v != "")
						{
							// change answer
							$this->db_query("UPDATE poll_answers SET answer = '$v' WHERE id = $anid");
							unset($answers[$anid]);
						}
					}

					if ($answers[$anid][answer] == $v && $v != "")
						unset($answers[$anid]);
				}
			}
			// now $answers contains the answers to remove
			reset($answers);
			while (list($aid,) = each($answers))
				$this->db_query("DELETE FROM poll_answers WHERE id = $aid");

			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("name" => $name, "comment" => $comment, "class_id" => CL_POLL,"status" => 1,"parent" => $GLOBALS["SITE_ID"]));
		}

		return $id;
	}

	function get_answers($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM poll_answers WHERE poll_id = $id ORDER BY id");
		while ($row = $this->db_next())
			$ret[$row[id]] = $row;
		return $ret;
	}

	function get_active_poll()
	{
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_POLL." AND status = 2 AND parent =".$GLOBALS["SITE_ID"]);
		return $this->db_next();
	}

	function change($id)
	{
		$this->read_template("add.tpl");

		$poll = $this->get_object($id);
		$this->dequote(&$poll);
		$answers = $this->get_answers($id);
		reset($answers);
		while (list($aid,$v) = each($answers))
		{
			$this->vars(array("answer_id" => $aid, "answer" => $v[answer]));
			$a.=$this->parse("QUESTION");	// tee hee. confusion is the key ;)
		}
		$this->vars(array("answer_id" => 0, "answer" => ""));
		$a.=$this->parse("QUESTION");	// tee hee. confusion is the key ;)


		$this->vars(array("QUESTION" => $a, "id" => $id, "name" => $poll[name], "comment" => $poll[comment]));
		return $this->parse();
	}

	function set_active($id)
	{
		$this->db_query("UPDATE objects SET status = 1 WHERE class_id = ".CL_POLL." AND parent = ".$GLOBALS["SITE_ID"]." AND status = 2");
		$this->db_query("UPDATE objects SET status = 2 WHERE oid = $id");
	}

	function gen_user_html()
	{
		$this->read_template("poll.tpl");

		if (!($ap = $this->get_active_poll()))
			return "";

		$this->vars(array("poll_id" => $ap[oid], "question" => $ap[name]));

		$ans = $this->get_answers($ap[oid]);
		reset($ans);
		while (list($k,$v) = each($ans))
		{
			$this->vars(array("answer_id" => $k, "answer" => $v[answer]));
			$as.=$this->parse("ANSWER");
		}
		$this->vars(array("ANSWER" => $as));
		return $this->parse();
	}

	function add_click($aid)
	{
		if ($GLOBALS["poll_clicked"] != 1)
			$this->db_query("UPDATE poll_answers SET clicks=clicks+1 WHERE id = $aid");

		$GLOBALS["poll_clicked"] = 1;
	}

	function show($id)
	{
		if (!is_number($id))
			return "";

		$this->read_template("show.tpl");

		if (!($poll = $this->get_object($id)))
			return "";

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
			$this->vars(array("answer" => $v[answer], "percent" => $percent, "width" => $width*2));
			$as.=$this->parse("ANSWER");
		}

		classload("msgboard");
		$t = new msgboard;

		// pollide arhiiv
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_POLL." AND status != 0 AND parent = ".$GLOBALS["SITE_ID"]);
		while ($row = $this->db_next())
		{
			if ($id != $row[oid])
			{
				$this->vars(array("question" => $row[name], "poll_id" => $row[oid], "num_comments" => $t->get_num_comments($row[oid])));
				$p.=$this->parse("QUESTION");
			}
		}

		$this->vars(array("ANSWER" => $as,"question" => $poll[name], "date" => $this->time2date($poll[modified],2),"addcomment" => $t->add(0,$id,0),"num_comments" => $t->get_num_comments($id), "poll_id" => $id, "QUESTION" => $p));

		return $this->parse();
	}
}
?>