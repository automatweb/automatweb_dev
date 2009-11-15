<?php
/*
@classinfo syslog_type=ST_K_TEST_ANSWER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_test_answer master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_test_answer
@default group=general

	@property test_entry type=relpicker reltype=RELTYPE_TEST_ENTRY
	@caption Testi sisestus

	@property test_question type=relpicker reltype=RELTYPE_TEST_QUESTION
	@caption Testi k&uuml;simus

	@property option_using type=relpicker reltype=RELTYPE_OPTION_USING
	@caption Valikvastuse kasutus
	
	@property answer_value type=textbox size=5
	@caption Vastuse v&auml;&auml;rtus

@reltype TEST_ENTRY value=1 clid=CL_K_TEST_ENTRY
@caption Testi sisestus

@reltype TEST_QUESTION value=2 clid=CL_K_TEST_QUESTION
@caption Testi k&uuml;simus

@reltype OPTION_USING value=3 clid=CL_K_OPTION_USING
@caption Valikvastuse variant

*/

class k_test_answer extends class_base
{
	function k_test_answer()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_test_answer",
			"clid" => CL_K_TEST_ANSWER
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
			$this->db_query("CREATE TABLE aw_k_test_answer(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "test_entry":
			case "test_question":
			case "option_using":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "answer_value":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;
		}
	}
}

?>
