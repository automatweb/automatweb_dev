<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/project.aw,v 1.1 2003/11/07 14:04:06 duke Exp $
// project.aw - Projekt 
/*

@classinfo syslog_type=ST_PROJECT relationmgr=yes

@default table=objects
@default group=general

*/

define("RELTYPE_SUBPROJECT",1);
define("RELTYPE_PARTICIPANT",2);

class project extends class_base
{
	function project()
	{
		$this->init(array(
			"clid" => CL_PROJECT
		));
	}

	function callback_get_rel_types()
	{
                return array(
                        RELTYPE_SUBPROJECT => "alamprojekt",
                        RELTYPE_PARTICIPANT => "osaleja",
                );
        }

	function callback_get_classes_for_relation($arr)
	{
                $retval = false;
                switch($arr["reltype"])
                {
                        case RELTYPE_SUBPROJECT:
                                $retval = array(CL_PROJECT);
                                break;
                        
			case RELTYPE_PARTICIPANT:
                                $retval = array(CL_USER);
                                break;
                };
                return $retval;
        }


	/*
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

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
