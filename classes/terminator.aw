<?php
// $Header: /home/cvs/automatweb_dev/classes/terminator.aw,v 1.1 2008/02/29 11:10:32 instrumental Exp $
// terminator.aw - The Terminator 
/*

@classinfo syslog_type=ST_TERMINATOR relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property pers type=relpicker reltype=RELTYPE_PERSON field=meta method=serialize automatic=1
@caption Tere

@reltype PERSON value=1 clid=CL_CRM_PERSON

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
		$odl = new object_data_list(
			array(
				"class_id" => CL_FILE
			),
			array(
				CL_FILE => array("oid" => "id", "name"),
			)
		);
		arr($odl);
		$files_data = $odl->arr();
		arr($files_data);
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

//-- methods --//
}
?>