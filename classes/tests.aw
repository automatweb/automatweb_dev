<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/tests.aw,v 2.1 2001/07/17 20:51:48 duke Exp $
global $orb_defs,$astmed,$punktid;
$orb_defs["tests"] = 
array("list_questions"	=> array("function" => "list_questions", "params" =>array()),
			"add_question"		=> array("function"	=> "add_question", "params" => array()),
			"change_question"	=> array("function"	=> "change_question", "params" => array("id")),
			"submit_question"	=> array("function" => "submit_question", "params" => array("id")),
			"list_teemad"			=> array("function"	=> "list_teemad", "params" => array()),
			"change_teema"		=> array("function"	=> "change_teema", "params" => array("id")),
			"submit_teema"		=> array("function" => "submit_teema", "params" => array()),
			"list_testid"			=> array("function"	=> "list_testid", "params" => array()),
			"add_test"				=> array("function"	=> "add_test","params" => array("level")),
			"add_test_submit"	=> array("function"	=> "add_test_submit","params" => array("level")),
			"do_test_submit"	=> array("function"	=> "do_test_submit","params" => array()),
			"hinda_test"			=> array("function"	=> "hinda_test","params" => array("id")),
			"submit_hinda_test"	=> array("function" => "submit_hinda_Test", "params" => array()),
			"delete_test"			=> array("function"	=> "delete_test","params" => array("id")),
			"delete_teema"		=> array("function"	=> "delete_teema","params" => array("id")),
			"delete_question"	=> array("function"	=> "delete_question","params" => array("id"))
		 );

classload("quiz");

$astmed = array("1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6");
$punktid = array("1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6", "7"=>"7", "8"=>"8", "9"=>"9", "10"=>"10", "11"=>"11", "12"=>"12", "13"=>"13", "14"=>"14", "15"=>"15", "16"=>"16", "17"=>"17", "18"=>"18", "19"=>"19", "20"=>"20");
class tests extends quiz
{
	function tests()
	{
		$this->db_init();
		$this->tpl_init("tests"); 
		$this->sub_merge = 1;
	}

	function list_questions($arr)
	{
		$this->mk_path(0,LC_TESTS_QUESTIONS);

		$this->read_template("list_questions.tpl");

//		$tarr = $this->get_teemad();

//		$this->db_query("SELECT objects.*,test_kysimused.* FROM objects LEFT JOIN test_kysimused ON objects.oid = test_kysimused.id WHERE class_id = ".CL_TEST_QUESTION." AND status != 0");
		classload("objects");
		$o = new db_objects;
		$li = $o->get_list();

		$qar = $this->get_q_list();
		reset($qar);
		while (list(,$row) = each($qar))
		{
			if ($row[YL_YLESANNE])
			{
				$row = $row[YL_YLESANNE];
				$this->vars(array("name" => $row["name"], 
													"teema"	=> $li[$row["parent"]],
													"raskus"	=> $row["level"],
													"id"		=> $row[oid]));

//													"change"	=> $this->mk_orb("change_question", array("id" => $row[oid])),
//													"delete"	=> $this->mk_orb("delete_question", array("id" => $row[oid])),
				$this->parse("LINE");
			}
		}
		$this->vars(array("addquestion" => $this->mk_orb("add_question", array()),
											"teemad"			=> $this->mk_orb("list_teemad",array()),
											"testid"			=> $this->mk_orb("list_testid",array())));
		return $this->parse();
	}

	function add_question($arr)
	{
		$this->mk_path(0,"<a href='".$this->mk_orb("list_questions",array()).LC_TESTS_QUEST_ADD);
		global $astmed,$punktid;
		extract($arr);
		$this->read_template("add_question.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_question", array("id" => 0)),
		"raskus" => $this->picker(0,$astmed),
		"teema" => $this->picker(0,$this->get_teemad()),
		"max_punkte" => $this->picker(0,$punktid)));
		return $this->parse();
	}

	function submit_question($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id,"name" => $name));
			$this->db_query("UPDATE test_kysimused SET teema = '$teema' , raskusaste = '$raskus' , max_punkte='$max_punkte', examix='$examix', content='$text', answer='$lahendus' WHERE id = $id");
		}
		else
		{
			$id = $this->new_object(array("parent" => 1, "class_id" => CL_TEST_QUESTION, "name" => $name));
			$this->db_query("INSERT INTO test_kysimused(id, teema, raskusaste, max_punkte, examix, content, answer) values($id, '$teema','$raskus','$max_punkte','$examix','$text','$lahendus')");
		}

		return $this->mk_orb("change_question", array("id" => $id));
	}

	function change_question($arr)
	{
		$this->mk_path(0,"<a href='".$this->mk_orb("list_questions",array()).LC_TESTS_QUEST_CHANGE);

		global $astmed,$punktid;
		extract($arr);

		$q = $this->get_question($id);

		$this->read_template("add_question.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_question", array("id" => $id)),
		"raskus" => $this->picker($q[raskusaste],$astmed),
		"examix" => ($q[examix] == 1 ? "CHECKED" : ""),
		"name" => $q[name],
		"text" => $q[content],
		"lahendus" => $q[answer],
		"max_punkte" => $this->picker($q[max_punkte],$punktid),
		"teema"			=> $this->picker($q[teema],$this->get_teemad())));
		return $this->parse();
	}

	function get_question($id)
	{
		$this->db_query("SELECT objects.*,test_kysimused.* FROM objects LEFT JOIN test_kysimused ON test_kysimused.id = objects.oid WHERE oid = $id");
		return $this->db_next();
	}

	function list_teemad()
	{
		$this->read_template("list_teemad.tpl");
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_TEST_TEEMA." AND status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array("id" => $row[oid], "name" => $row[name],
												"change"	=> $this->mk_orb("change_teema",array("id" => $row[oid])),
												"delete"	=> $this->mk_orb("delete_teema",array("id" => $row[oid]))));
			$this->parse("LINE");
		}
		$this->vars(array("addteema"	=> $this->mk_orb("change_teema", array("id" => 0)),
											"questions"	=> $this->mk_orb("list_questions",array()),
											"testid"		=> $this->mk_orb("list_testid",array())));
		return $this->parse();
	}

	function submit_teema($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name));
		}
		else
		{
			$id = $this->new_object(array("parent" => 1, "class_id" => CL_TEST_TEEMA, "status" => 2, "name" => $name));
		}
		return $this->mk_orb("list_teemad",array());
	}

	function change_teema($arr)
	{
		extract($arr);
		$this->read_template("change_teema.tpl");
		$o = $this->get_object($id);
		$this->vars(array("name" => $o[name], 
											"reforb"	=> $this->mk_reforb("submit_teema",array("id" => $id))));
		return $this->parse();
	}

	function get_teemad()
	{
		$ret = array();
		$this->db_query("SELECT * FROM objects WHERE status != 0 AND class_id = ".CL_TEST_TEEMA);
		while ($row = $this->db_next())
			$ret[$row[oid]] = $row[name];
		return $ret;
	}

	function list_testid($arr)
	{
		$this->read_template("list_testid.tpl");
		$this->db_query("SELECT objects.*,test_testid.* FROM objects LEFT JOIN test_testid ON test_testid.id = objects.oid WHERE status != 0 AND class_id=".CL_TEST);
		while ($row = $this->db_next())
		{
			$this->vars(array("id" => $row[oid],
												"whodunit"	=> $row[createdby],
												"when"			=> $this->time2date($row[created],2),
												"raskus"		=> $row[raskus],
												"status"		=> $row[status] == 1 ? LC_TESTS_FILLED : LC_TESTS_APPRAISE,
												"hinne"		=> $row[punkte],
												"num_questions"		=> $row[ylesandeid],
												"hinda"			=> $this->mk_orb("hinda_test",array("id" => $row[oid])),
												"delete"		=> $this->mk_orb("delete_test",array("id" => $row[oid]))));
			$this->parse("LINE");
		}
		$this->vars(array("addtest"	=> $this->mk_orb("add_test",array("level" => 1)),
											"teemad"	=> $this->mk_orb("list_teemad",array()),
											"questions"	=> $this->mk_orb("list_questions",array())));
		return $this->parse();
	}

	function add_test($arr)
	{
		$this->mk_path(0,"<a href='".$this->mk_orb("list_testid",array())."'>Testid</a> / Lisa");
		global $astmed;
		if ($arr[level] == 1)
		{
			// valik, kuidas test koostataxe
			$this->read_template("add_test.tpl");
			$this->vars(array("raskus"	=> $this->picker(0,$astmed),
												"teemad"	=> $this->multiple_option_list(0,$this->get_teemad()),
												"reforb"	=> $this->mk_reforb("add_test_submit",array())));
			return $this->parse();
		}
		else
		if ($arr[level] == 2)
		{
			// tyyp valib ylesannete kaupa mis teemadest nad on
			global $kysimusi,$raskus,$teemad;
			$this->dequote($teemad);
			$teemad = unserialize($teemad);
			$tarr = $this->get_teemad();

			$tem = array();
			reset($teemad);
			while (list(,$v) = each($teemad))
				$tem[$v] = $tarr[$v];

			$this->read_template("add_test_l2.tpl");
			for ($i=0; $i < $kysimusi; $i++)
			{
				$this->vars(array("num" => $i, "teema" => $this->picker(0,$tem)));
				$this->parse("QUESTION");
			}
			$this->vars(array("reforb" => $this->mk_reforb("add_test_submit",array("level" => 5,"kysimusi" => $kysimusi,"raskus" => $raskus))));
			return $this->parse();
		}
		else
		if ($arr[level] == 3)
		{
			// tyyp valib teemade kaupa, mitu kysimust igast teemast
			global $kysimusi,$raskus,$teemad;
			$this->dequote($teemad);
			$teemad = unserialize($teemad);
			$tarr = $this->get_teemad();

			$this->read_template("add_test_l3.tpl");
			reset($teemad);
			while (list(,$v) = each($teemad))
			{
				$this->vars(array("teema" => $tarr[$v], "tid" => $v));
				$this->parse("QUESTION");
			}
			$this->vars(array("reforb" => $this->mk_reforb("add_test_submit",array("level" => 6,"kysimusi" => $kysimusi,"raskus" => $raskus))));
			return $this->parse();
		}
		else
		if ($arr[level] == 4)
		{
			// suvaliselt koostatud
			global $kysimusi,$raskus,$teemad;
			$this->dequote($teemad);
			$teemad = unserialize($teemad);

			$questions = array(); $qc=0;
			$tstr = join(",",$teemad);
			$this->db_query("SELECT test_kysimused.*,objects.* FROM test_kysimused LEFT JOIN objects ON test_kysimused.id=objects.oid WHERE raskusaste = $raskus AND  teema IN ($tstr) AND status!=0");
			while (($row = $this->db_next()) && $qc < $kysimusi)
			{
				$questions[$qc] = $row;
				$qc++;
			}
			return $this->show_test($questions,$raskus);
		}
		else
		if ($arr[level] == 5)
		{
			// n2itame testi level 2 parameetrite j2rgi
			global $level, $kysimusi, $raskus,$teemad;
			$this->dequote($teemad);
			$valik = unserialize($teemad);
			$t_used = array();
			// leiame k6ik teemad, mis on kasutusel.
			$q = array(); $qc = 0;
			reset($valik["teemad"]);
			while (list(,$v) = each($valik["teemad"]))
			{
				if ($q[$v] < 1)
					$q[$v] = 0;
				$t_used[$v] = $v;
				$q_t[$v][$q[$v]] = $qc;
				$q[$v]++;
				$qc++;
			}

			$q=0;
			$questions = array();
			reset($t_used);
			while (list(,$v) = each($t_used))
			{
				$tc = 0;
				$this->db_query("SELECT test_kysimused.*,objects.* FROM test_kysimused LEFT JOIN objects ON test_kysimused.id=objects.oid WHERE raskusaste = $raskus AND  teema = $v AND status!=0");
				while ($row = $this->db_next())
				{
					$questions[$q_t[$v][$tc]] = $row;
					$tc++;
				}
			}

			return $this->show_test($questions,$raskus);
		}
		else
		if ($arr[level] == 6)
		{
			// mitu igast teemast on paika pandud
			global $level, $kysimusi, $raskus,$teemad;
			$this->dequote($teemad);
			$valik = unserialize($teemad);

			$questions = array(); $qc = 0;
			reset($valik["teema"]);
			while (list($tid,$v) = each($valik["teema"]))
			{
				$t_c = 0;
				$this->db_query("SELECT test_kysimused.*,objects.* FROM test_kysimused LEFT JOIN objects ON objects.oid = test_kysimused.id WHERE teema = $tid AND status != 0 AND raskusaste = $raskus");
				while (($row = $this->db_next()) && $tc < $v)
				{
					$questions[$qc] = $row;
					$qc++;
				}
			}

			return $this->show_test($questions,$raskus);
		}
	}

	function show_test($qarr,$raskus)
	{
		$this->read_template("do_test.tpl");

		$tarr = $this->get_teemad();

		$q = 0;
		reset($qarr);
		while (list(,$row) = each($qarr))
		{
			$this->vars(array("num" => $q, "max_punkte" => $row[max_punkte], "text" => $this->proc_text($row[content]), "qid" => $row[oid], "teema" => $tarr[$row[teema]]));
			$this->parse("QUESTION");
			$q++;
		}

		$tid = $this->new_object(array("parent" => 1, "class_id" => CL_TEST,"status" => 1));
		$this->db_query("INSERT INTO test_testid(id,ylesandeid, raskus) values($tid,$q,$raskus)");

		$this->vars(array("reforb" => $this->mk_reforb("do_test_submit",array("id" => $tid))));
		return $this->parse();
	}

	function add_test_submit($arr)
	{
		extract($arr);
		if ($arr[level] < 5)
		{
			return $this->mk_orb("add_test",array("level" => $level,"kysimusi" => $kysimusi,"raskus" => $raskus,"teemad" => serialize($teemad)));
		}
		else
		{
			$a = serialize($arr);
			return $this->mk_orb("add_test",array("level" => $level, "kysimusi" => $kysimusi,"raskus" => $raskus, "teemad" => $a));
		}
	}

	function proc_text($text)
	{
		$text = str_replace("\n", "<br>", $text);
//		$text = preg_replace("/<vec>(.*)<\/vec>/isU", "<img src='/images/nool.gif'>\\1", $text);
		return $text;
		//<table align=left border=0 cellpadding=0 cellspacing=0><tr><Td>
		//</td></tr><td></td></tr></table>
	}

	function do_test_submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		reset($answers);
		while (list($kid,$v) = each($answers))
		{
			$this->db_query("INSERT INTO test_kysimus_vastused(test_id,kysimus_id,vastus) values($id,$kid,'$v')");
		}

		return $this->mk_orb("list_testid", array());
	}

	function hinda_test($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_orb("list_testid",array())."'>Testid</a> / Hinda");
		$this->read_template("hinda_test.tpl");
		$this->db_query("SELECT objects.*,test_testid.* FROM objects LEFT JOIN test_testid ON test_testid.id = objects.oid WHERE oid = $id");
		$test = $this->db_next();

		$tarr = $this->get_teemad();

		$q = 0;
		$this->db_query("SELECT test_kysimus_vastused.*,test_kysimused.*,test_kysimus_vastused.id as vid,test_kysimus_vastused.punkte as hinne FROM test_kysimus_vastused LEFT JOIN test_kysimused ON test_kysimused.id = test_kysimus_vastused.kysimus_id WHERE test_id = $id");
		while ($row = $this->db_next())
		{
			$parr = array();
			for ($i=1; $i <= $row[max_punkte]; $i++)
				$parr[$i] = $i;
			$this->vars(array("num" => $q, "teema" => $tarr[$row[teema]], "text" => $this->proc_text($row[content]),"answer" => $this->proc_text($row[vastus]),"correct_answer" => $this->proc_text($row[answer]),"vid" => $row[vid],
			"punkte" => $this->picker($row[hinne],$parr)));
			$this->parse("QUESTION");
			$q++;
		}
		$this->vars(array("whodunit" => $test[createdby],"when" => $this->time2date($test[created],2),"raskus" => $test[raskus],"questions" => $test[ylesandeid],"reforb" => $this->mk_reforb("submit_hinda_test",array("id" => $id))));
		return $this->parse();
	}

	function submit_hinda_test($arr)
	{
		extract($arr);

		reset($punkte);
		while (list($kid,$v) = each($punkte))
		{
			$this->db_query("UPDATE test_kysimus_vastused SET punkte = $v WHERE id = $kid");
			$pk+=$v;
		}
		$this->upd_object(array("oid" => $id, "status" => 2));
		$this->db_query("UPDATE test_testid SET punkte = $pk WHERE id = $id");
		return $this->mk_orb("hinda_test", array("id" => $id));
	}

	function delete_test($arr)
	{
		extract($arr);
		$this->delete_object($id);
		header("Location: ".$this->mk_orb("list_testid",array()));
	}

	function delete_teema($arr)
	{
		extract($arr);
		$this->delete_object($id);
		header("Location: ".$this->mk_orb("list_teemad",array()));
	}

	function delete_question($arr)
	{
		extract($arr);
		$this->delete_object($id);
		header("Location: ".$this->mk_orb("list_questions",array()));
	}
}
?>
