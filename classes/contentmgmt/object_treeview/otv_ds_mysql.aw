<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/Attic/otv_ds_mysql.aw,v 1.1 2004/10/21 16:39:02 dragut Exp $
// otv_ds_mysql.aw - Objektinimekirja MySQL andmeallikas 
/*

@classinfo syslog_type=ST_OTV_DS_MYSQL relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property db_table type=relpicker reltype=RELTYPE_DB_TABLE_CONTENTS
@caption Andmebaasitabelis sisu objekt

@reltype DB_TABLE_CONTENTS value=1 clid=CL_DB_TABLE_CONTENTS
@caption Andmebaasitabeli sisu objekt
*/

class otv_ds_mysql extends class_base
{
	function otv_ds_mysql()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "contentmgmt/object_treeview/otv_ds_mysql",
			"clid" => CL_OTV_DS_MYSQL
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

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

/////////////////////////////////////////////////////////////////////////
// this is used by object_treeview_v2 to display the selectable columns
// on object_treeview_v2 "tulbad" tab
/////////////////////////////////////////////////////////////////////////
	function get_fields($o)
	{

        $db_table_contents_inst = get_instance(CL_DB_TABLE_CONTENTS);
		$db_table_contents_obj = obj($o->prop("db_table"));
		return $db_table_contents_inst->get_fields($db_table_contents_obj);
//		return array();
	}
/////////////////////////////////////////////////////////////////////////
// this is used by object_treeview_v2 to show the table.
// here i put in the table the trafficlimits
/////////////////////////////////////////////////////////////////////////
	function get_objects($o, $fld = NULL, $parent = NULL)
	{

        $db_table_contents_inst = get_instance(CL_DB_TABLE_CONTENTS);
		$db_table_contents_obj = obj($o->prop("db_table"));

		return $db_table_contents_inst->get_objects($db_table_contents_obj);
	}

/////////////////////////////////////////////////////////////////////////
// this is used by object_treeview_v2 to show the folders part.
// here i dont have any folders to show, or something that looks
// nice displayd as folders, so i return empty array, othervise it
// gives an error
//
// and for the record, as i remember, i didnt get it to work anyway
// but as i said, i didn't need it so i didn't spend much time to
// fool around with it.
/////////////////////////////////////////////////////////////////////////
	function get_folders($o)
	{
		return array();
	}

/////////////////////////////////////////////////////////////////////////
// this is also used by object_treeview_v2, obviously it makes some
// acl checks, but here it isn't used, and i don't know how to use it.
/////////////////////////////////////////////////////////////////////////
	function check_acl()
	{
		return true;
	}
	
}
?>
