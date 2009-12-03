<?php
/*
@classinfo syslog_type=ST_K_OPTION_USING relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_option_using master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_option_using
@default group=general

	@property value type=textbox size=4
	@caption V&auml;&auml;rtus

	@property option type=relpicker reltype=RELTYPE_OPTION
	@caption Valikvastus

	@property test_question type=relpicker reltype=RELTYPE_QUESTION
	@caption Testi k&uuml;simus

	@property is_yes_no type=select 
	@caption JahEi

### RELTYPES

@reltype TEST_QUESTION value=1 clid=CL_K_TEST_QUESTION
@caption Testi k&uuml;simus

@reltype OPTION value=2 clid=CL_K_OPTION
@caption Valikvastus

*/

class k_option_using extends class_base
{
	function k_option_using()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_option_using",
			"clid" => CL_K_OPTION_USING
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

	public function _get_is_yes_no($arr)
	{
		$arr["prop"]["options"] = array(
			0 => t("No"),
			1 => t("Yes")
		);
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
			$this->db_query("CREATE TABLE aw_k_option_using(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "value":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "float(6,2)"
				));
				return true;

			case "is_yes_no":
			case "option":
			case "test_question":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
