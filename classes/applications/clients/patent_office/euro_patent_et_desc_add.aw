<?php
/*
@classinfo syslog_type=ST_EURO_PATENT_ET_DESC_ADD relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=ma
@tableinfo aw_euro_patent_et_desc_add master_index=brother_of master_table=objects index=aw_oid

@default table=aw_euro_patent_et_desc_add
@default group=general

*/

class euro_patent_et_desc_add extends class_base
{
	function euro_patent_et_desc_add()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/euro_patent_et_desc_add",
			"clid" => CL_EURO_PATENT_ET_DESC_ADD
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
			$this->db_query("CREATE TABLE aw_euro_patent_et_desc_add(aw_oid int primary_key)");
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
