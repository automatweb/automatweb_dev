<?php
/*
@classinfo syslog_type=ST_K_QUESTION_CATEGORY relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_question_category master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_question_category
@default group=general

*/

class k_question_category extends class_base
{
	function k_question_category()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_question_category",
			"clid" => CL_K_QUESTION_CATEGORY
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
			$this->db_query("CREATE TABLE aw_k_question_category(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}

?>
