<?php
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/personalihaldus_arvutioskus.aw,v 1.4 2004/06/17 13:53:09 kristo Exp $
// personalihaldus_arvutioskus.aw - Arvutioskus 
/*

@classinfo syslog_type=ST_PERSONALIHALDUS_ARVUTIOSKUS relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property oskus type=classificator  orient=vertical
@caption Oskus

@property tase type=classificator  orient=vertical
@caption Tase
*/

class personalihaldus_arvutioskus extends class_base
{
	function personalihaldus_arvutioskus()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONALIHALDUS_ARVUTIOSKUS
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/
}
?>
