<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_table_column.aw,v 1.1 2005/02/28 10:45:37 duke Exp $
// property_table_column.aw - Tabeli veerg 
/*

@classinfo syslog_type=ST_PROPERTY_TABLE_COLUMN relationmgr=yes

@default table=objects
@default group=general

@property ord field=jrk size=2
@caption Jrk

@default field=meta
@default method=serialize

@property sortable type=checkbox ch_value=1
@caption Sorteeritav

@property width type=textbox datatype=int size=2
@caption Laius

@property nowrap type=checkbox ch_value=1
@caption Poolitamine keelatud

*/

class property_table_column extends class_base
{
	function property_table_column()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_TABLE_COLUMN
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

}
?>
