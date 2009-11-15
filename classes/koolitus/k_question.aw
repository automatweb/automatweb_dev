<?php
/*
@classinfo syslog_type=ST_K_QUESTION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=koolitus15
@tableinfo aw_k_question master_index=brother_of master_table=objects index=aw_oid

@default table=aw_k_question
@default group=general

	@property q_image type=relpicker reltype=RELTYPE_IMAGE rel_id=first use_form=emb field=meta method=serialize table=objects
	@caption Pilt

@groupinfo options caption="Valikvastused"
@default group=options

	@property options_toolbar type=toolbar store=no no_caption=1
	
	@property options_table type=table store=no no_caption=1

@reltype OPTION value=1 clid=CL_K_OPTION
@caption Valikvastus

@reltype IMAGE value=2 clid=CL_IMAGE
@caption Pilt

*/

class k_question extends class_base
{
	function k_question()
	{
		$this->init(array(
			"tpldir" => "koolitus/k_question",
			"clid" => CL_K_QUESTION
		));
	}

	public function _get_options_toolbar($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_new_button(array(CL_K_OPTION), $arr["obj_inst"]->id, 1);
		$t->add_delete_button();
	}

	public function _get_options_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$ol = new object_list(array(
			"class_id" => CL_K_OPTION,
			"CL_K_OPTION.RELTYPE_OPTION(CL_K_QUESTION)" => $arr["obj_inst"]->id(),
		));
		$t->table_from_ol(
			$ol,
			array("name", "created", "createdby"),
			CL_K_OPTION
		);
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
			$this->db_query("CREATE TABLE aw_k_question(aw_oid int primary key)");
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
