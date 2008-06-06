<?php
// $Header: /home/cvs/automatweb_dev/classes/terminator.aw,v 1.3 2008/06/06 09:11:30 instrumental Exp $
// terminator.aw - The Terminator 
/*

@classinfo syslog_type=ST_TERMINATOR relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general2

	@property skills_releditor1 type=releditor mode=manager2 reltype=RELTYPE_SKILL_LEVEL props=skill,level table_fields=skill,level store=no
	@caption Oskused releditor1

	@property skills_releditor2 type=releditor mode=manager2 reltype=RELTYPE_SKILL_LEVEL2 props=skill,level table_fields=skill,level store=no
	@caption Oskused releditor2

	@property skills_releditor3 type=releditor mode=manager2 reltype=RELTYPE_SKILL_LEVEL3 props=skill,level table_fields=skill,level store=no
	@caption Oskused releditor3

	@property skills_releditor4 type=releditor mode=manager2 reltype=RELTYPE_SKILL_LEVEL4 props=skill,level table_fields=skill,level store=no
	@caption Oskused releditor4

	@property skills_releditor5 type=releditor mode=manager2 reltype=RELTYPE_SKILL_LEVEL5 props=skill,level table_fields=skill,level store=no
	@caption Oskused releditor5

@reltype PERSON value=1 clid=CL_CRM_PERSON
@caption Isik

@reltype SKILL_LEVEL value=2 clid=CL_CRM_SKILL_LEVEL
@caption Isik

@reltype SKILL_LEVEL2 value=3 clid=CL_CRM_SKILL_LEVEL
@caption Isik

@reltype SKILL_LEVEL3 value=4 clid=CL_CRM_SKILL_LEVEL
@caption Isik

@reltype SKILL_LEVEL4 value=5 clid=CL_CRM_SKILL_LEVEL
@caption Isik

@reltype SKILL_LEVEL5 value=6 clid=CL_CRM_SKILL_LEVEL
@caption Isik

*/

class terminator extends class_base
{
	function terminator()
	{
		$this->init(array(
			"tpldir" => "terminator",
			"clid" => CL_TERMINATOR
		));
	}

	function get_property($arr)
	{
		$o = obj(177);
		arr($o->phones());
		foreach($o->phones()->arr() as $ph)
		{
			arr($ph->conn_id);
		}
		exit;

		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
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
