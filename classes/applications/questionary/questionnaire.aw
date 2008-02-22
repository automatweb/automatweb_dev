<?php
// questionnaire.aw - Dünaamiline küsimustik
/*

@classinfo syslog_type=ST_QUESTIONNAIRE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@groupinfo conf parent=general caption=Seaded
	@default group=conf

		@property name type=textbox field=name
		@caption Nimi

		#@property qlimit type=textbox size=3 field=meta method=serialize
		#@caption K&uuml;simusi korraga
		#@comment 0 = unlimited

		@property dsply_qcomment type=chooser field=meta method=serialize
		@caption Kuva k&uuml;simuse kommentaari
		@comment Kuvatakse kommentaare, mille pikkus on > 0.

		@property dsply_acomment type=chooser field=meta method=serialize
		@caption Kuva vastuse kommentaari
		@comment Kuvatakse kommentaare, mille pikkus on > 0.

		@property dsply_correct2wrong type=checkbox ch_value=1 field=meta method=serialize
		@caption Vale vastuse korral kuva &otilde;iged

		@property dsply_correct2wrong_caption_single type=textbox field=meta method=serialize
		@caption &Otilde;igete vastuste caption (ainsus)
		@comment Kuvatakse vale vastuse korral

		@property dsply_correct2wrong_caption_multiple type=textbox field=meta method=serialize
		@caption &Otilde;igete vastuste caption (mitmus)
		@comment Kuvatakse vale vastuse korral

		@property dsply_correct2correct type=checkbox ch_value=1 field=meta method=serialize
		@caption &Otilde;ige vastuse korral kuva k&otilde;ik &otilde;iged

		@property dsply_correct2correct_caption_single type=textbox field=meta method=serialize
		@caption &Otilde;igete vastuste caption (ainsus)
		@comment Kuvatakse &otilde;ige vastuse korral

		@property dsply_correct2correct_caption_multiple type=textbox field=meta method=serialize
		@caption &Otilde;igete vastuste caption (mitmus)
		@comment Kuvatakse &otilde;ige vastuse korral

		@property comment2nothing type=textbox field=meta method=serialize
		@caption Kommentaar, kui vastus on t&uuml;hi
		@comment Kuvatakse vastuse kommentaari v&auml;ljas

		@property str_rslts type=checkbox ch_value=1 field=meta method=serialize
		@caption Salvesta vastamised

	@groupinfo pics parent=general caption=Pildid
	@default group=pics

		@property p_correct type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
		@caption &Otilde;ige vastuse pilt

		@property p_false type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
		@caption Vale vastuse pilt

	@groupinfo dsply_rslts parent=general caption=Tulemuste&nbsp;kuvamine
	@default group=dsply_rslts

		@property rd_percent type=checkbox field=meta method=serialize
		@caption &Otilde;igete vastuste protsent

		@property rd_text type=textarea field=meta method=serialize
		@caption Tekst

		@property rd_percent_text type=table store=no
		@caption Tekst &otilde;igete vastuste protsendi j&auml;rgi

@groupinfo questions caption=K&uuml;simused submit=no
@default group=questions

	@property qtlbr type=toolbar no_caption=1 store=no

	@property qtbl type=table no_caption=1 store=no

@groupinfo answerers submit=no caption=Vastajad
@default group=answerers

	@property acnt type=text store=no
	@caption Vastajate arv

@reltype QUESTION value=1 clid=CL_QUESTIONNAIRE_QUESTION
@caption K&uuml;simus

@reltype IMAGE value=2 clid=CL_IMAGE
@caption Pilt

@reltype ANSWERER value=3 clid=CL_QUESTIONNAIRE_ANSWERER
@caption Vastaja

*/

class questionnaire extends class_base
{
	function questionnaire()
	{
		$this->init(array(
			"tpldir" => "applications/questionary/questionnaire",
			"clid" => CL_QUESTIONNAIRE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "dsply_qcomment":
				$prop["options"] = array(
					"0" => t("Mitte kunagi"),
					"1" => t("Alati"),
					"2" => t("Ainult &otilde;ige vastuse korral"),
					"3" => t("Ainult vale vastuse korral"),
					"4" => t("Suvalise vastuse korral")
				);
				if(!$prop["value"])
					$prop["value"] = 0;
				break;
			case "dsply_acomment":
				$prop["options"] = array(
					"0" => t("Mitte kunagi"),
					"1" => t("Alati"),
					"2" => t("Ainult &otilde;ige vastuse korral"),
					"3" => t("Ainult vale vastuse korral"),
				);
				if(!$prop["value"])
					$prop["value"] = 0;
				break;

			case "qtlbr":
				$t = &$prop["vcl_inst"];
				$t->add_new_button(array(CL_QUESTIONNAIRE_QUESTION), $arr["obj_inst"]->id(), 1);
				$t->add_delete_button();
				$t->add_save_button();
				break;

			case "rd_percent_text":
				$this->_get_rd_percent_text($arr);
				break;
			
			case "qtbl":
				$this->_get_atbl($arr);
				break;

			case "acnt":
				if(!$arr["obj_inst"]->prop("str_rslts"))
					$prop["value"] = t("Vastajaid ei salvestata!");
				else
					$prop["value"] = count($arr["obj_inst"]->connections_from(array("type" => 3)));
				break;
		}

		return $retval;
	}

	function _get_rd_percent_text($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "from",
			"caption" => t("Protsent alates"),
			"align" => "center",
			"width" => 100,
		));
		$t->define_field(array(
			"name" => "to",
			"caption" => t("Protsent kuni"),
			"align" => "center",
			"width" => 100,
		));
		$t->define_field(array(
			"name" => "text",
			"caption" => t("Tekst"),
			"align" => "center",
		));
		$ms = $arr["obj_inst"]->meta("rd_percent_text");
		foreach($ms as $i => $m)
		{
			$t->define_data(array(
				"from" => html::textbox(array(
					"name" => "pc_txt[".$i."][from]",
					"size" => 3,
					"value" => $m["from"],
				)),
				"to" => html::textbox(array(
					"name" => "pc_txt[".$i."][to]",
					"size" => 3,
					"value" => $m["to"],
				)),
				"text" => html::textarea(array(
					"name" => "pc_txt[".$i."][text]",
					"cols" => 80,
					"rows" => 5,
					"value" => $m["text"],
				)),
				"from_hidden" => $m["from"],
			));
		}
		$t->define_data(array(
			"from" => html::textbox(array(
				"name" => "pc_txt[new][from]",
				"size" => 3,
			)),
			"to" => html::textbox(array(
				"name" => "pc_txt[new][to]",
				"size" => 3,
			)),
			"text" => html::textarea(array(
				"name" => "pc_txt[new][text]",
				"cols" => 80,
				"rows" => 5,
			)),
			"from_hidden" => 99999,
		));
		$t->sort_by(array(
			"field" => "from_hidden",
			"sorder" => "ASC",
		));
	}

	function _get_qtbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("J&auml;rjekord"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "question",
			"caption" => t("K&uuml;simus"),
			"align" => "center",
		));
		foreach($arr["obj_inst"]->connections_from(array("type" => 1)) as $conn)
		{
			$t->define_data(array(
				"oid" => $conn->conn["to"],
				"question" => html::get_change_url($conn->conn["to"], array("return_url" => get_ru()), $conn->conn["to.name"]),
				"jrk" => html::hidden(array(
					"name" => "jrk_old[".$conn->conn["to"]."]",
					"value" => $conn->conn["to.jrk"],
				)).html::textbox(array(
					"name" => "jrk[".$conn->conn["to"]."]",
					"value" => $conn->conn["to.jrk"],
					"size" => 4
				)),
				"jrk_hidden" => $conn->conn["to.jrk"],
			));
		}
		$t->sort_by(array(
			"field" => "jrk_hidden",
			"sorder" => "ASC",
		));
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "qtbl":
				foreach($arr["request"]["jrk"] as $i => $v)
				{
					if($arr["request"]["jrk_old"][$i] == $v)
						continue;

					$o = obj($i);
					$o->set_prop("jrk", $v);
					$o->save();
				}
				break;

			case "rd_percent_text":
				$m = array();
				$i = 0;
				foreach($arr["request"]["pc_txt"] as $v)
				{
					if(!$v["from"] && !$v["to"] && !$v["text"])
						continue;

					$m[$i] = $v;
					$i++;
				}
				$arr["obj_inst"]->set_meta("rd_percent_text", $m);
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function array_search_by_column($n, $a, $s)
	{
		foreach($a as $i => $v)
		{
			if($v[$s] == $n)
				return $i;
		}
		return 0;
	}

	function correct_percent($a)
	{
		$ccnt = 0;
		foreach($a as $v)
		{
			if($v == 2)
				$ccnt++;
		}
		return round($ccnt*100/count($a), 0);
	}

	function show($arr)
	{
		$_qs = aw_unserialize(aw_global_get("questions_".$arr["id"]));
		$set_qs = !is_array($_qs);
		/* $_qs values:
		0 - undone
		1 - done, wrong
		2 - done, correct
		*/

		$o = new object($arr["id"]);
		$i = get_instance(CL_IMAGE);
		$this->read_template("show.tpl");

		if($_GET["qid"] == "end")
		{
			if($o->prop("rd_percent"))
			{
				$this->vars(array(
					"results_percent" => t("&Otilde;igeid vastuseid")." ".$this->correct_percent($_qs).t("%."),
				));
			}
			if($o->prop("rd_text"))
			{
				$this->vars(array(
					"results_text" => $o->prop("rd_text"),
				));
			}
			foreach($o->meta("rd_percent_text") as $m)
			{
				if(($m["from"] <= $this->correct_percent($_qs) || strlen($m["from"]) == 0) && ($m["to"] >= $this->correct_percent($_qs) || strlen($m["to"]) == 0))
				{
					$this->vars(array(
						"results_text_by_percent" => $m["text"],
					));
					$RESULTS_TEXT_BY_PERCENT .= $this->parse("RESULTS_TEXT_BY_PERCENT");
				}
			}
			$this->vars(array(
				"RESULTS_TEXT_BY_PERCENT" => $RESULTS_TEXT_BY_PERCENT,
			));

			$RESULTS = $this->parse("RESULTS");
			$this->vars(array(
				"RESULTS" => $RESULTS,
			));
			return $this->parse();
		}

		$conns = $o->connections_from(array("type" => 1));
		foreach($conns as $conn)
		{
			if($set_qs)
				$_qs[$conn->conn["to"]] = 0;

			$qs[$conn->conn["to"]]["oid"] = $conn->conn["to"];
			$qs[$conn->conn["to"]]["caption"] = $conn->conn["to.name"];
			$qs[$conn->conn["to"]]["jrk"] = $conn->conn["to.jrk"];
		}
		foreach ($qs as $k => $r)
		{
			$jrk[$k]  = $r["jrk"];
		}
		array_multisort($jrk, SORT_ASC, $qs);
		if(count($qs) == 0)
			return false;

		$qs_id = $this->array_search_by_column($_GET["qid"], $qs, "oid");
		$q = $qs[$qs_id];
		$q_obj = obj($q["oid"]);
		foreach($q_obj->connections_from(array("type" => 1)) as $conn)
		{
			$as[$conn->conn["to"]]["oid"] = $conn->conn["to"];
			$as[$conn->conn["to"]]["caption"] = $conn->conn["to.name"];
			$as[$conn->conn["to"]]["jrk"] = $conn->conn["to.jrk"];
		}
		unset($jrk);
		foreach ($as as $k => $r)
		{
			$jrk[$k]  = $r["jrk"];
		}
		array_multisort($jrk, SORT_ASC, $as);

		if($q_obj->prop("ans_type"))
		{
			$this->vars(array(
				"answer_value" => $_POST["answer"],
			));
			$ANSWER_TEXTBOX = $this->parse("ANSWER_TEXTBOX");
			$this->vars(array("ANSWER_TEXTBOX" => $ANSWER_TEXTBOX));
		}
		else
		{
			foreach($as as $a)
			{
				$answer_checked = ($a["oid"] == $_POST["answer"]) ? "checked" : "";
				$this->vars(array(
					"answer_oid" => $a["oid"],
					"answer_caption" => $a["caption"],
					"answer_checked" => $answer_checked,
				));
				$ANSWER_RADIO .= $this->parse("ANSWER_RADIO");
			}
			$this->vars(array("ANSWER_RADIO" => $ANSWER_RADIO));
		}

		if(array_key_exists(($qs_id + 1), $qs))
		{
			$next_caption = t("J&auml;rgmine");
			$next_url = aw_url_change_var("qid", $qs[$qs_id + 1]["oid"]);
		}
		else
		{
			$next_caption = t("L&otilde;peta");
			$next_url = aw_url_change_var("qid", "end");
		}

		foreach($q_obj->prop("pics") as $pic_id)
		{
			if(!is_oid($pic_id))
				continue;

			$this->vars(array(
				"picture" => $i->make_img_tag_wl($pic_id),
			));
			$PICTURE .= $this->parse("PICTURE");
		}
		$this->vars(array(
			"PICTURE" => $PICTURE,
		));

		$dsply_acomment = $o->prop("dsply_acomment");
		$dsply_qcomment = $o->prop("dsply_qcomment");

		// If this is set for the question, we override the settings set in the questionnaire conf
		if($q_obj->prop("dsply_acomment"))
			$dsply_acomment = $q_obj->prop("dsply_acomment");

		if($_POST["qid"] && $_POST["answer"])
		{
			if($q_obj->prop("ans_type"))
			{
				$correct = false;
				foreach($as as $a)
				{
					if($a["caption"] == $_POST["answer"])
					{
						$a_obj = obj($a["oid"]);
						if($a_obj->prop("correct"))
						{
							$correct = true;
							break;
						}
					}
				}
				$acomment = $correct ? t("Õige!<br>") : t("Vale!<br>");
				if($correct)
					switch($dsply_acomment)
					{
						case 1:
							$acomment .= $a_obj->prop("comm");
							break;
						case 2:
							if($correct)
								$acomment .= $a_obj->prop("comm");
							break;
						case 3:
							if(!$correct)
								$acomment .= $a_obj->prop("comm");
							break;
					}
			}
			else
			{
				$a_obj = obj($_POST["answer"]);
				$correct = $a_obj->prop("correct");
				$acomment = $correct ? t("Õige!<br>") : t("Vale!<br>");
				switch($dsply_acomment)
				{
					case 1:
						$acomment .= $a_obj->prop("comm");
						break;
					case 2:
						if($correct)
							$acomment .= $a_obj->prop("comm");
						break;
					case 3:
						if(!$correct)
							$acomment .= $a_obj->prop("comm");
						break;
				}
			}

			switch($dsply_qcomment)
			{
				case 1:
				case 4:
					$qcomment .= $q_obj->prop("comm");
					break;
				case 2:
					if($correct)
						$qcomment .= $q_obj->prop("comm");
					break;
				case 3:
					if(!$correct)
						$qcomment .= $q_obj->prop("comm");
					break;
			}
			$_qs[$_POST["qid"]] = $correct ? 2 : 1;

			// If picture for correct answer is set in the question object, we'll override whatever is in the questionnaire object.
			if($q_obj->prop("p_correct"))
				$o->set_prop("p_correct", $q_obj->prop("p_correct"));

			// If picture for wrong answer is set in the question object, we'll override whatever is in the questionnaire object.
			if($q_obj->prop("p_false"))
				$o->set_prop("p_false", $q_obj->prop("p_false"));

			if($o->prop("p_correct") && $correct)
			{
				$this->vars(array(
					"picture" => $i->view(array("id" => $o->prop("p_correct"))),
				));
				$ANSWER_PICTURE = $this->parse("ANSWER_PICTURE");
				$this->vars(array(
					"ANSWER_PICTURE" => $ANSWER_PICTURE,
				));
			}
			if($o->prop("p_false") && !$correct)
			{
				$this->vars(array(
					"picture" => $i->view(array("id" => $o->prop("p_false"))),
				));
				$ANSWER_PICTURE = $this->parse("ANSWER_PICTURE");
				$this->vars(array(
					"ANSWER_PICTURE" => $ANSWER_PICTURE,
				));
			}
			if((!$correct && $o->prop("dsply_correct2wrong")) || ($correct && $o->prop("dsply_correct2correct")))
			{
				foreach($as as $a)
				{
					$a_obj = obj($a["oid"]);
					$correct_answer_count = 0;
					if($a_obj->prop("correct"))
					{
						$answer = $a_obj->prop("name");
						$this->vars(array(
							"answer" => $answer,
						));
						$CORRECT_ANSWER .= $this->parse("CORRECT_ANSWER");
						$correct_answer_count++;
					}
				}
				if(!$correct && $o->prop("dsply_correct2wrong"))
				{
					$correct_answer_caption = ($correct_answer_count == 1) ? $o->prop("dsply_correct2false_caption_single") : $o->prop("dsply_correct2false_caption_multiple");
				}
				else
				{
					$correct_answer_caption = ($correct_answer_count == 1) ? $o->prop("dsply_correct2correct_caption_single") : $o->prop("dsply_correct2correct_caption_multiple");
				}
				$this->vars(array(
					"CORRECT_ANSWER" => $CORRECT_ANSWER,
				));
				$CORRECT_ANSWERS = $this->parse("CORRECT_ANSWERS");
				$this->vars(array(
					"CORRECT_ANSWERS" => $CORRECT_ANSWERS,
				));
			}
		}
		elseif($_POST["qid"])
		{
			$acomment = $o->prop("comment2nothing");
			$this->vars(array(
				"acomment" => $acomment,
			));
		}
		else
		{
			$submit = html::submit(array(
				"value" => "Vasta",
			));
			$this->vars(array(
				"submit" => $submit,
			));

			if($dsply_qcomment == 1)
				$qcomment = $q_obj->prop("comm");
		}

		$this->vars(array(
			"question" => $q["caption"],
			"next_url" => $next_url,
			"next_caption" => $next_caption,
			"question_id" => $q["oid"],
			"acomment" => $acomment,
			"qcomment" => $qcomment,
		));

		$QUESTIONNAIRE = $this->parse("QUESTIONNAIRE");
		$this->vars(array(
			"QUESTIONNAIRE" => $QUESTIONNAIRE,
		));

		aw_session_set("questions_".$arr["id"], aw_serialize($_qs));
		return $this->parse();
	}
}

?>
