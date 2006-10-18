<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/watercraft_management/watercraft_add.aw,v 1.1 2006/10/18 14:37:46 dragut Exp $
// watercraft_add.aw - Vees&otilde;iduki lisamine 
/*

@classinfo syslog_type=ST_WATERCRAFT_ADD relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo watercraft_add index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property watercraft_management type=relpicker reltype=RELTYPE_WATERCRAFT_MANAGEMENT table=watercraft_add
	@caption Vees&otilde;idukite haldus

@reltype WATERCRAFT_MANAGEMENT value=1 clid=CL_WATERCRAFT_MANAGEMENT
@caption Vees&otilde;idukite haldus
*/

class watercraft_add extends class_base
{
	function watercraft_add()
	{
		$this->init(array(
			"tpldir" => "applications/watercraft_management/watercraft_add",
			"clid" => CL_WATERCRAFT_ADD
		));
	}

	function get_property($arr)
	{
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

	function do_db_upgrade($table, $field, $query, $error)
	{

		if (empty($field))
		{
			// db table doesn't exist, so lets create it:
			$this->db_query('CREATE TABLE '.$table.' (
				oid INT PRIMARY KEY NOT NULL,
				
				watercraft_management int
			)');
			return true;
		}

		switch ($field)
		{
			case 'watercraft_management':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
                }

		return false;
	}
}
?>
