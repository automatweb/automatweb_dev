<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfg_view_controller.aw,v 1.7 2006/03/10 14:43:38 kristo Exp $
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
	
		
	/** runs the controller given
		@attrib api=1

		@param prop required type=array
			Data for the property to check

		@param controller_oid required type=int
			OID of the controller to run

		@param arr required type=array
			Misc data that you can pass to the controller

		@errors
			error is thrown if the controller object given does not exist
	
		@returns
			the value that the controller sets to the variable $retval

		@examples
			$ctr = find_controller_object();	
			$ctr_instance = $crt->instance();
			$prop = array("name" => "whatever");
			echo "the controller said ".$ctr_instance->check_property($prop, $ctr->id(), array("a" => "b"));

			// prints whatever the controller assigned to $retval
	**/
	function check_property(&$prop, $controller_oid, $arr)
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
