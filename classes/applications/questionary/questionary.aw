<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/questionary/questionary.aw,v 1.5 2006/10/20 08:35:29 tarvo Exp $
// questionary.aw - K&uuml;simustik 
/*

@classinfo syslog_type=ST_QUESTIONARY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property answer_count type=textbox
	@caption Valikvastuste arv
	
	@property thank_you_doc type=relpicker reltype=RELTYPE_DOC
	@caption T&auml;nudokument

@groupinfo groups caption=Grupid
@default group=groups
	@property gr_tb type=toolbar no_caption=1
	@property groups type=table no_caption=1

@groupinfo results caption=Vastatud
@default group=results
	@property results type=text
	@caption Vastuseid
	
	@property get_results type=text
	@caption Ekspordi vastused

@reltype GROUP value=1 clid=CL_QUESTION_GROUP
@caption K&uml;simustegrupp

@reltype DOC value=2 clid=CL_DOCUMENT
@caption T&auml;nudokument

*/

class questionary extends class_base
{
	function questionary()
	{
		$this->init(array(
			"tpldir" => "questionary",
			"clid" => CL_QUESTIONARY
		));
		$this->init_data();
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
			case "results":
				$prop["value"] = count($this->get_results($arr["obj_inst"]->id()));

				break;
			case "get_results":
				$prop["value"] = html::href(array(
					"caption" => t("Ekspordi tulemused"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $arr["obj_inst"]->id(),
						"return_url" => get_ru(),
						"group" => $arr["request"]["group"],
						"export" => 1,
					), CL_QUESTIONARY),
				));
				if($arr["request"]["export"])
				{
					$res = $this->get_results($arr["obj_inst"]->id());
					$this->gen_csv_output($res);
				}
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
		$t = $GLOBALS["_GET"];
		$questionary_id = $arr["id"];
		if($t["questionary_submitted"])
		{
			$o = obj($questionary_id);
			$docid = $o->prop("thank_you_doc");
			if(!$docid)
			{
				$this->read_template("thank_you.tpl");
				return $this->parse();
			}
			header("Location:".aw_ini_get("baseurl")."/".$docid);
		}
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");

		$gr = $this->get_groups($arr["id"]);
		$gr_inst = get_instance(CL_QUESTION_GROUP);
		$size = 10;
		foreach($gr as $jrk_ => $obj)
		{
			$oid = $obj->id();
			$no_answer = !$obj->prop("no_answer");
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

		// SICK FUCK PART
		// area

		foreach($this->pers["area"] as $k => $el)
		{
			$this->vars(array(
				"caption" => $el,
				"value" => $k,
			));
			$areas .= $this->parse("PERS_AREA");
		}
		// schools
		foreach($this->pers["school"] as $k => $el)
		{
			$this->vars(array(
				"caption" => $el,
				"value" => $k,
			));
			$schs .= $this->parse("PERS_SCHOOL");
		}

		// intrests
		foreach($this->pers["intrests"] as $k => $el)
		{
			$this->vars(array(
				"caption" => $el,
				"value" => $k,
			));
			$area2 .= $this->parse("S_AREA");
		}

		// visites to library
		foreach($this->pers["visits"] as $k => $el)
		{
			$this->vars(array(
				"caption" => $el,
				"value" => $k,
			));
			$visits .= $this->parse("VISITS");
		}

		// usage

		foreach($this->pers["usage"] as $k => $el)
		{
			$this->vars(array(
				"caption" => $el,
				"value" => $k,
			));
			$usage .= $this->parse("USAGE");
		}
		
		$this->vars(array(
			"PERS_AREA" => $areas,
			"PERS_SCHOOL" => $schs,
			"S_AREA" => $area2,
			"VISITS" => $visits,
			"USAGE" => $usage,
		));
		$formdata = $this->parse("PERS_DATA");
	

		// END OF SICK FUCK PART
		$this->vars(array(
			"GROUP" => $groups,
			"name" => $ob->prop("name"),
			"PERS_DATA" => $formdata,
			"reforb" => $this->mk_reforb("add_result", array(
				"questionary" => $questionary_id,
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
			$o = obj($cdata["to"]);
			$ret[$o->prop("jrk")] = obj($cdata["to"]);
		}
		ksort($ret);
		return $ret;
	}

	/**
		@attrib params=name name=add_result all_args=1
	**/
	function add_result($arr)
	{
		$o = obj();
		$o->set_class_id(CL_ANSWERER);
		$o->set_parent($arr["questionary"]);
		$o->set_name("Küsimustikule vastaja");
		$o->save();
		$o->set_prop("questionary", $arr["questionary"]);
		$o->set_prop("gender", $this->pers["gender"][$arr["pers"]["gender"]]);
		$o->set_prop("age", $this->pers["age"][$arr["pers"]["age"]]);
		if(!($a = $arr["pers"]["area_radio"]))
		{
			foreach($arr["pers"]["area_text"] as $k => $v)
			{
				if(strlen($v))
				{
					$area = $this->pers["area"][$k].", ".$v;
					break;
				}
			}
		}
		elseif($a == count($this->pers["area"]))
		{
			$area = "muu,´".$arr["pers"]["area_text"][$a];
		}
		else
		{
			$area = $this->pers["area"][$a].", Tallinnast";
		}
		$o->set_prop("area", $area);
		# SHCOOL
		if((!$a = $arr["pers"]["school_radio"]))
		{
			foreach($arr["pers"]["school_text"] as $k => $v)
			{
				if(strlen($v))
				{
					$school = $this->pers["school"][$k].", ".$v;
					break;
				}
			}
		}
		elseif($a == count($this->pers["area"]))
		{
			$school = "muu, ".$arr["pers"]["school_text"][$a];
		}
		else
		{
			$school = $this->pers["school"][$a].", ".$arr["pers"]["school_text"][$a];
		}
		$o->set_prop("school", $school);

		# INTRESTS
		arr($arr);
		foreach($arr["pers"]["intrest_check"] as $nr => $pointless)
		{
			$intrests[] = $this->pers["intrests"][$nr].(strlen(($tmp = $arr["pers"]["intrest_text"][$nr]))?"(".$tmp.")":"");
		}

		$o->set_prop("intrests", join(", ", $intrests));

		# VISITS etc...
		$o->set_prop("visit_recur", $this->pers["visits"][$arr["pers"]["visits"]]);
		$o->set_prop("usage", $this->pers["usage"][$arr["pers"]["usage"]]);
		$o->save();

		$ans_inst = get_instance(CL_QUESTIONARY_RESULT);
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
							"answerer" => $o->id(),
						));
					}
				}
			}
		}

		return $arr["return_url"]."?questionary_submitted=1";
	}

	/**
		@attrib params=pos api=1
		@param id required type=oid
	**/
	function get_results($id)
	{
		if(!is_oid($id))
		{
			return false;
		}
		$ol = new object_list(array(
			"class_id" => CL_ANSWERER,
			"questionary" => $id,
		));
		foreach($ol->arr() as $oid => $obj)
		{
			$conns = $obj->connections_from(array(
				"type" => "RELTYPE_ANSWER",
			));
			foreach($conns as $data)
			{
				$result = $data->to();
				$ret[$oid][$result->id()] = array(
					"question" => $result->prop("question"),
					"topic" => $result->prop("question_topic"),
					"group" => $result->prop("question_group"),
					"answer" => $result->prop("answer"),
				);
			}

		}
		return $ret;
	}

	/**
		@attrib name=gen_csv_output params=name all_args=1
	**/
	function gen_csv_output($results)
	{
		$first = true;
		foreach($results as $result => $answers)
		{
			$group_no = 0;
			$group_id = null;
			$topic_no = 0;
			$topic_id = null;
			$question_no = 0;
			$question_id = null;
			$answerer = obj($result);

			if($first)
			{
				$struct[] = "Sugu";
				$struct[] = "Vanus";
				$struct[] = "Tegevusala";
				$struct[] = "Õppimine/töötamine kõrgkoolis";
				$struct[] = "Huvivaldkond";
				$struct[] = "Rahvusraamatukogu külastan";
				$struct[] = "Raamatukogu teenuseid kasutan";
			}

			$res[$result][] = $answerer->prop("gender");
			$res[$result][] = $answerer->prop("age");
			$res[$result][] = html_entity_decode($answerer->prop("area"));
			$res[$result][] = html_entity_decode($answerer->prop("school"));
			$res[$result][] = html_entity_decode($answerer->prop("intrests"));
			$res[$result][] = html_entity_decode($answerer->prop("visits"));
			$res[$result][] = html_entity_decode($answerer->prop("usage"));

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
				$tmp = $group_no."_".$topic_no."_".$question_no;
				$res[$result][$tmp] = $data["answer"];
				$struct[$tmp] = $tmp;
			}
			$first = false;
		}

		
		// sick fuck

		
		$file[] = $struct;
		foreach($res as $key => $row)
		{
			unset($newrow);
			foreach($struct as $skey => $srow)
			{
				$newrow[$skey] = $row[$skey];
			}
			$file[] = $newrow;
		}

		foreach($file as $row_nr => $row)
		{
			$row_str = join(";",$row);
			$tot_str .= $row_str."\n";
		}
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="vastused.csv"');
		die($tot_str);
	}

	function init_data()
	{
		$this->pers["gender"] = array(
			1 => "Mees",
			2 => "Naine",
		);
		$this->pers["age"] = array(
			1 => "18 või noorem",
			2 => "19-29",
			3 => "30-39",
			4 => "40-49",
			5 => "50-59",
			6 => "60 või vanem",
		);
		$this->pers["area"] = array(
			1 => "riigiteenistuja",
			2 => "teadlane, &otilde;ppej&otilde;ud",
			3 => "loomeinimene",
			4 => "spetsialist, juhtiv t&ouml;&ouml;taja",
			5 => "doktorant",
			6 => "magistrant",
			7 => "bakalaurus&otilde;ppe &uuml;li&otilde;pilane",
			8 => "&otilde;pilane",
			9 => "muu (t&auml;psustage)",
		);
		$this->pers["school"] = array(
			1 => "Tallinna &Uuml;likool",
			2 => "Tallinna Tehnika&uuml;likool",
			3 => "Eesti Muusikaakadeemia",
			4 => "Eesti Kunstiakadeemia",
			5 => "Tartu &Uuml;likool",
			6 => "Eesti Maa&uuml;likool",
			7 => "Muu (milline)",
		);
		$this->pers["intrests"] = array(
			1 => "Humanitaarteadused",
			2 => "Sotsiaalteadused",
			3 => "Loodus ja t&auml;ppisteadused",
			4 => "Tehnikateadused",
			5 => "Meditsiiin",
			6 => "P&otilde;llumajandus, aiandus, metsandus",
			7 => "Muu"
		);
		$this->pers["visits"] = array(
			1 => "Iga p&auml;ev",
			2 => "M&otilde;ne korra n&auml;dalas",
			3 => "M&otilde;ne korra kuus",
			4 => "M&otilde;ne korra aastas",
		);
		$this->pers["usage"] = array(
			1 => "Ainult E-raamatukogu RR-i kodulehel",
			2 => "Peamiselt E-raamatukogu RR-i kodulehel",
			3 => "Ainult raamatukoguhoones",
			4 => "Peamiselt raamatukoguhoones",
			5 => "Kasutan k&otilde;iki v&otilde;imalusi",
		);
	}
}
?>
