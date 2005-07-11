<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfg_view_controller.aw,v 1.5 2005/07/11 12:54:39 kristo Exp $
// cfg_view_controller.aw - NÃ&auml;itamise kontroller 
/*

@classinfo syslog_type=ST_CFG_VIEW_CONTROLLER relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property help type=text store=no no_caption=1

@property formula type=textarea rows=20 cols=80
@caption Valem

*/

class cfg_view_controller extends class_base
{
	function cfg_view_controller()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "cfg/cfg_view_controller",
			"clid" => CL_CFG_VIEW_CONTROLLER
		));
	}
	
		
	//function check_property($arr)
	function check_property($prop, $controller_oid, $arr, &$prop)
	{
		// $prop, $controller_oid, $arr
		//extract($arr);
		$retval = PROP_OK;
		$controller_inst = &obj($controller_oid);
		eval($controller_inst->prop("formula"));
		return $retval;
	}
}
?>
