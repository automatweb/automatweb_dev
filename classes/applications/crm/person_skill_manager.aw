<?php
/*
@classinfo syslog_type=ST_PERSON_SKILL_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@tableinfo aw_person_skill_manager master_index=brother_of master_table=objects index=aw_oid

@default table=aw_person_skill_manager
@default group=general

@property company type=relpicker reltype=RELTYPE_COMPANY field=meta method=serialize
@caption Default tasemed


@groupinfo skills caption="Oskused"
@default group=skills
	@property skills_tb type=toolbar no_caption=1 store=no 

	@property skills_tbl type=table store=no no_caption=1


@groupinfo workers caption="T&ouml;&ouml;tajad"
@default group=workers
	@property workers_tb type=toolbar no_caption=1 store=no 

	@layout workers_layout type=hbox width=20%:80%

		@property workers_tree type=treeview store=no parent=workers_layout no_caption=1
		
		@property workers_tbl type=table store=no parent=workers_layout no_caption=1

@reltype COMPANY value=2 clid=CL_CRM_COMPANY
@caption Tasemed

*/

class person_skill_manager extends class_base
{
	function person_skill_manager()
	{
		$this->init(array(
			"tpldir" => "applications/crm/person_skill_manager",
			"clid" => CL_PERSON_SKILL_MANAGER
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
			$this->db_query("CREATE TABLE aw_person_skill_manager(aw_oid int primary_key)");
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
