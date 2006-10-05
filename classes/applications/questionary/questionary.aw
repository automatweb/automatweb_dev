<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/questionary/questionary.aw,v 1.2 2006/10/05 16:10:36 tarvo Exp $
// questionary.aw - K&uuml;simustik 
/*

@classinfo syslog_type=ST_QUESTIONARY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property answer_count type=textbox
	@caption Valikvastuste arv

@groupinfo groups caption=Grupid
@default group=groups
	@property gr_tb type=toolbar no_caption=1
	@property groups type=table no_caption=1

@groupinfo results caption=Vastatud
@default group=results
	@property results_tbl type=text no_caption=1

@reltype GROUP value=1 clid=CL_QUESTION_GROUP
@caption K&uml;simustegrupp

*/

class questionary extends class_base
{
	function questionary()
	{
		$this->init(array(
			"tpldir" => "questionary",
			"clid" => CL_QUESTIONARY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "gr_tb":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "action",
					"img" => "new.gif",
					"tooltip" => t("Uus grupp"),
					"url" => $this->mk_my_orb("new", array(
						"alias_to" => $arr["obj_inst"]->id(),
						"reltype" => 1,
						"parent" => $arr["obj_inst"]->id(),
						"return_url" => get_ru(),
					), CL_QUESTION_GROUP), 
				));
			break;
			case "groups":
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "name",
					"caption" => t("Nimi"),
				));
				foreach($this->get_groups($arr["obj_inst"]->id()) as $oid => $obj)
				{
					$url = $this->mk_my_orb("change", array(
						"id" => $oid,
						"return_url" => get_ru(),
					), CL_QUESTION_GROUP);
					$t->define_data(array(
						"name" => html::href(array(
							"caption" => $obj->name(),
							"url" => $url,
						)),
					));
				}
			break;
			case "results_tbl":
				$results = $this->get_results($arr["obj_inst"]->id());
				classload("vcl/table");
				foreach($results as $result => $answers)
				{
					$group_no = 0;
					$group_id = null;
					$topic_no = 0;
					$topic_id = null;
					$question_no = 0;
					$question_id = null;
					foreach($answers as $ans_id => $data)
					{
						if($data["group"] != $group_id)
						{
							$group_id = $data["group"];
							$group_no++;
							$topic_no = 0;
							$question_no = 0;
						}
						if($data["topic"] != $topic_id)
						{
							$topic_id = $data["topic"];
							$topic_no++;
							$question_no = 0;
						}
						if($data["question"] != $question_id)
						{
							$question_id = $data["question"];
							$question_no++;
						}
						$tmp = $group_no."-".$topic_no."-".$question_no;
						$res[$result][$tmp] = $data["answer"];
						$struct[] = $tmp;
					}
				}
				$t = new vcl_table();
				foreach($struct as $name)
				{
					$t->define_field(array(
						"name" => $name,
						"caption" => $name,
					));
				}
				foreach($res as $data)
				{
					$t->define_data($data);
				}
				$prop["value"] = $t->draw();
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}

	function callback_mod_reforb($arr, $request)
	{
		$arr["post_ru"] = post_ru();
	}

	function parse_alias($arr)
	{
		$arr["id"] = $arr["alias"]["to"];
		return $this->show($arr);
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");

		$gr = $this->get_groups($arr["id"]);
		$gr_inst = get_instance(CL_QUESTION_GROUP);
		$size = 10;
		foreach($gr as $oid => $obj)
		{
			$no_answer = $obj->prop("no_answer");
			unset($header, $rows);
			// table header
			$questions = $gr_inst->get_questions($oid);
			foreach($questions as $o)
			{
				$this->vars(array(
					"question_name" => $o->name(),
				));
				$header .= $this->parse("QUESTION");
			}
			if(!$no_answer)
			{
				$this->vars(array(
					"question_name" => t("Ei vasta"),
				));
				$header .= $this->parse("QUESTION");
			}
			$this->vars(array(
				"corner_caption" => t("topic\question"),
				"QUESTION" => $header,
			));
			$header = $this->parse("HEADER");

			$topics = $gr_inst->get_topics($oid);
			foreach($topics as $o)
			{
				unset($answer);
				$arr["topic"] = $o->id();
				foreach($questions as $q_o)
				{
					$arr["question"] = $q_o->id();
					$arr["group"] = $oid;
					$this->vars(array(
						"answer_element" => $this->_get_answer_element($arr),
					));
					$answer .= $this->parse("ANSWER");
				}
				if(!$no_answer)
				{
					$this->vars(array(
						"answer_element" => html::checkbox(array(
							"name" => "no_answer[".$oid."][".$arr["topic"]."]",
							"value" => false,
						)),
					));
					$answer .= $this->parse("ANSWER");
				}

				$this->vars(array(
					"topic_name" => $o->name(),
					"ANSWER" => $answer,
				));
				$rows .= $this->parse("TOPIC");
			}
			$this->vars(array(
				"span" => ($no_answer)?(count($questions) + 1):(count($questions) + 2),
				"name" => $obj->name(),
				"HEADER" => $header,
				"TOPIC" => $rows,
			));
			$groups .= $this->parse("GROUP");
		}
		
		$this->vars(array(
			"GROUP" => $groups,
			"name" => $ob->prop("name"),
			"reforb" => $this->mk_reforb("add_result", array(
				"questionary" => $arr["id"],
				"return_url" => post_ru(),
			)),
			"submit_caption" => t("Vasta"),
		));
		return $this->parse();
	}
	
//-- methods --//

	function _get_answer_element($arr)
	{
		$o = obj($arr["id"]);
		$a_count = $o->prop("answer_count");
		for($i=1; $i <= $a_count; $i++)
		{
			$this->vars(array(
				"nr" => $i,
				"html_element" => html::radiobutton(array(
					"name" => "answer[".$arr["group"]."][".$arr["topic"]."][".$arr["question"]."]",
					"value" => $i,
				)),
			));
			$elements .= $this->parse("INPUT");
		}
		$this->vars(array(
			"INPUT" => $elements,
		));
		return $this->parse("A_ELEMENT");
	}

	/**
	**/
	function get_groups($oid)
	{
		$c = new connection();
		$conns = $c->find(array(
			"from" => $oid,
			"from.class_id" => CL_QUESTIONARY,
			"to.class_id" => CL_QUESTION_GROUP,
			"type" => "RELTYPE_GROUP",
		));
		foreach($conns as $cdata)
		{
			$ret[$cdata["to"]] = obj($cdata["to"]);
		}
		return $ret;
	}

	/**
		@attrib params=name name=add_result all_args=1
	**/
	function add_result($arr)
	{
		$ans_inst = get_instance(CL_QUESTIONARY_RESULT);
		$uniq_id = gen_uniq_id();
		foreach($arr["answer"] as $group_id => $topics)
		{
			foreach($topics as $topic_id => $questions)
			{
				if(!$arr["no_answer"][$group_id][$topic_id])
				{
					foreach($questions as $question_id => $answer)
					{
						$ans_inst->add_answer(array(
							"questionary" => $arr["questionary"],
							"question" => $question_id,
							"group" => $group_id,
							"topic" => $topic_id,
							"answer" => $answer,
							"relation_id" => $uniq_id,
						));
					}
				}
			}
		}

		return $arr["return_url"];
	}

	/**
		@attrib params=pos api=1
		@param id required type=oid
	**/
	function get_results($id)
	{
		if(!$id)
		{
			return false;
		}
		$ol = new object_list(array(
			"class_id" => CL_QUESTIONARY_RESULT,
			"questionary" => $id,
		));
		foreach($ol->arr() as $ans_id => $obj)
		{
			$ret[$obj->prop("relation_id")][$obj->id()] = array(
				"question" => $obj->prop("question"),
				"topic" => $obj->prop("question_topic"),
				"group" => $obj->prop("question_group"),
				"answer" => $obj->prop("answer"),
			);
		}
		return $ret;
	}
}
?>
