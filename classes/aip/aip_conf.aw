<?php
// $Header: /home/cvs/automatweb_dev/classes/aip/Attic/aip_conf.aw,v 1.4 2004/02/11 21:51:14 duke Exp $

/*

@classinfo syslog_type=ST_AIP_CONF relationmgr=yes

@default table=objects
@default group=general

@property change_folder type=relpicker reltype=RELATION_CHANGE_FOLDER field=meta method=serialize
@caption Muudatuste kataloog

@reltype CHANGE_FOLDER value=1 clid=CL_MENU
@caption Muudatuste kataloog

*/

class aip_conf extends class_base
{
	function aip_conf()
	{
		$this->init(array(
			'clid' => CL_AIP_CONF
		));
	}
}
?>
