<?php
/*
@classinfo syslog_type=ST_K_TEST_QUESTION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_test_question master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_test_question
@default group=general

	@property block type=relpicker reltype=RELTYPE_BLOCK
	@caption Plokk

	@property test type=relpicker reltype=RELTYPE_TEST
	@caption Test

	@property question type=relpicker reltype=RELTYPE_QUESTION
	@caption K&uuml;simus

@reltype BLOCK value=1 clid=CL_K_TEST_BLOCK
@caption Plokk

@reltype TEST value=2 clid=CL_K_TEST
@caption Test

@reltype QUESTION value=3 clid=CL_K_QUESTION
@caption K&uuml;simus

*/

class k_test_question extends class_base
{
	function k_test_question()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_test_question",
			"clid" => CL_K_TEST_QUESTION
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
			$this->db_query("CREATE TABLE aw_k_test_question(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "block":
			case "test":
			case "question":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
