<?php
// questionnaire_question.aw - D&uuml;naamilise k&uuml;simustiku k&uuml;simus
/*

@classinfo syslog_type=ST_QUESTIONNAIRE_QUESTION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@groupinfo conf parent=general caption=Seaded
	@default group=conf

		@property name type=textbox field=name
		@caption K&uuml;simus

		@property jrk type=textbox size=4 field=jrk
		@caption J&auml;rjekord

		@property ans_type type=chooser field=meta method=serialize
		@caption Vastuse t&uuml;&uuml;p

		@property comm type=textarea field=comment
		@caption Kommentaar

		@property dsply_acomment type=chooser field=meta method=serialize
		@caption Kuva vastuse kommentaari
		@comment Kuvatakse ka siis, kui k&uuml;simustiku seadetes pole seda lubatud.

	@groupinfo pics parent=general caption=Pildid
	@default group=pics

		@property pics type=relpicker multiple=1 reltype=RELTYPE_IMAGE field=meta method=serialize
		@caption K&uuml;simuse pildid

		@property p_correct type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
		@caption &Otilde;ige vastuse pilt

		@property p_false type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
		@caption Vale vastuse pilt

@groupinfo answers submit=no caption=Vastused
@default group=answers

	@property atlbr type=toolbar no_caption=1 submit=no

	@property atbl type=table no_caption=1 submit=no

@reltype ANSWER value=1 clid=CL_QUESTIONNAIRE_ANSWER
@caption Vastus

@reltype IMAGE value=2 clid=CL_IMAGE
@caption Pilt

*/

class questionnaire_question extends class_base
{
	function questionnaire_question()
	{
		$this->init(array(
			"tpldir" => "applications/questionary/questionnaire_question",
			"clid" => CL_QUESTIONNAIRE_QUESTION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "ans_type":
				$prop["options"] = array(
					"0" => t("Valikvastused"),
					"1" => t("Tekstikast"),
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

			case "atlbr":
				$t = &$prop["vcl_inst"];
				$t->add_new_button(array(CL_QUESTIONNAIRE_ANSWER), $arr["obj_inst"]->id(), 1);
				$t->add_delete_button();
				$t->add_save_button();
				break;

			case "atbl":
				$this->_get_qtbl($arr);
				break;
		}

		return $retval;
	}

	function _get_qtbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(true);
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("J&auml;rjekord"),
			"sortable" => false,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "answer",
			"caption" => t("Vastus"),
			"align" => "center",
		));
		foreach($arr["obj_inst"]->connections_from(array("type" => 1)) as $conn)
		{
			$t->define_data(array(
				"oid" => $conn->conn["to"],
				"answer" => html::get_change_url($conn->conn["to"], array("return_url" => get_ru()), $conn->conn["to.name"]),
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
			case "atbl":
				foreach($arr["request"]["jrk"] as $i => $v)
				{
					if($arr["request"]["jrk_old"][$i] == $v)
						continue;

					$o = obj($i);
					$o->set_prop("jrk", $v);
					$o->save();
				}
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}

?>
