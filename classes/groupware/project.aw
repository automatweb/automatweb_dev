<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/project.aw,v 1.2 2003/11/10 23:16:04 duke Exp $
// project.aw - Projekt 
/*

@classinfo syslog_type=ST_PROJECT relationmgr=yes

@default table=objects
@default group=general

@reltype SUBPROJECT clid=CL_PROJECT value=1
@caption alamprojekt

@reltype PARTICIPANT clid=CL_USER value=2
@caption kasutaja
*/

// god, this has shrunken so much.
class project extends class_base
{
	function project()
	{
		$this->init(array(
			"clid" => CL_PROJECT
		));
	}
};
?>
