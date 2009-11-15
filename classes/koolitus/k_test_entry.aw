<?php
/*
@classinfo syslog_type=ST_K_TEST_ENTRY relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_test_entry master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_test_entry
@default group=general

	@property county type=textbox
	@caption Maakond

	@property city type=textbox
	@caption Linn/Vald

	@property village type=textbox
	@caption K&uuml;la

	@property answers type=table store=no 
	@caption Vastused

@groupinfo data caption="Andmed"
@default group=data

	@property test type=relpicker reltype=RELTYPE_TEST
	@caption Test

	@property total_points type=textbox
	@caption Punktisumma

	@property result type=checkbox
	@caption Vajab anal&uuml;&uuml;si

@reltype TEST value=1 clid=CL_K_TEST
@caption Test

*/

class k_test_entry extends class_base
{
	function k_test_entry()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_test_entry",
			"clid" => CL_K_TEST_ENTRY
		));
	}

	function _get_answers($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "question",
			"caption" => t("K&uuml;simus"),
			"align" => "left"
		));
		$t->define_field(array(
			"name" => "answer",
			"caption" => t("Kuidas vastati"),
			"align" => "left"
		));
		$t->define_field(array(
			"name" => "points",
			"caption" => t("Punktid vastuse eest"),
			"align" => "right"
		));
		$t->define_field(array(
			"name" => "yesno",
			"caption" => t("JahEi"),
			"align" => "left"
		));
		$total_points = 0;
		$answers_yes = 0;
		$answers_no = 0;
		foreach($arr["obj_inst"]->get_all_answers() as $answer)
		{
			$t->define_data(array(
				"question" => $answer->prop("test_question.question.name"),
				"answer" => $answer->prop("option_using.option.name"),
				"points" => $answer->prop("answer_value"),
				"yesno" => $answer->prop("option_using.is_yes_no") ? t("Jah") : t("Ei"),
				"block" => $answer->prop("test_question.block.name"),
				"created" => $answer->created()
			));
			$total_points += $answer->prop("answer_value");
			if ($answer->prop("option_using.is_yes_no"))
			{
				$answers_yes++;
			}
			else
			{
				$answers_no++;
			}
		}
		$t->set_rgroupby(array(
			"block" => "block"
		));
		$t->set_default_sortby("created");
		$t->sort_by();

		$t->set_sortable(false);

		$t->define_data(array(
			"question" => html::strong(t("Summa")),
			"points" => html::strong($total_points),
			"yesno" => html::strong(sprintf(t("Jah vastuseid %s Ei vastuseid %s"), 
							$answers_yes,
							$answers_no
			))
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
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

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_k_test_entry(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "result":
			case "test":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "total_points":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;

			case "county":
			case "city":
			case "village":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				return true;
		}
	}
}

?>
