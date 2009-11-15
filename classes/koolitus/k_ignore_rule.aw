<?php
/*
@classinfo syslog_type=ST_K_IGNORE_RULE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_ignore_rule master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_ignore_rule
@default group=general

	@property question type=relpicker reltype=RELTYPE_QUESTION
	@caption K&uuml;simus

	@property ignore_question type=relpicker reltype=RELTYPE_IGNORE_QUESTION
	@caption Seotud k&uuml;simus

	@property ignore_option type=relpicker reltype=RELTYPE_OPTION
	@caption Seotud valikvastus

@reltype QUESTION value=1 clid=CL_K_TEST_QUESTION
@caption K&uuml;simus

@reltype IGNORE_QUESTION value=2 clid=CL_K_TEST_QUESTION
@caption Seotud k&uuml;simus

@reltype OPTION value=3 clid=CL_K_OPTION
@caption Seotud valikvastus

*/

class k_ignore_rule extends class_base
{
	function k_ignore_rule()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_ignore_rule",
			"clid" => CL_K_IGNORE_RULE
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
			$this->db_query("CREATE TABLE aw_k_ignore_rule(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "ignore_question":
			case "ignore_option":
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
