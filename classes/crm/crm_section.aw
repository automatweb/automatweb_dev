<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_section.aw,v 1.3 2004/06/17 14:33:21 duke Exp $
// crm_section.aw - &Uuml;ksus 
/*

@classinfo syslog_type=ST_CRM_SECTION relationmgr=yes

@default table=objects
@default group=general

*/

class crm_section extends class_base
{
	function crm_section()
	{
		$this->init(array(
			"clid" => CL_CRM_SECTION
		));
	}

	function get_folders_as_object_list($o, $level, $parent)
	{
		// I need all objects that target this one
		// $o - is the sector object
		$conns = $o->connections_to(array(
			"from.class_id" => CL_CRM_PERSON,
		));
		$ol = new object_list();
		foreach($conns as $conn)
		{
			$ol->add($conn->prop("from"));
		};
		return $ol;
	}

	function make_menu_link($o)
	{
		// right, now I need to implement the proper code
		// need to figure out the section!
		$sect = $o->prop("sect");
		return $this->mk_my_orb("show",array("id" => $o->id(),"section" => aw_global_get("section")),CL_CRM_PERSON);
		//return aw_ini_get("baseurl") . "/" . $o->id() . "?oid=" . $sect;
		/*
		print "swching";
		print "<pre>";
		var_dump($o->name());
		print "</pre>";
		*/


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
