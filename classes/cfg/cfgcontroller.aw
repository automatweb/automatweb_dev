<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgcontroller.aw,v 1.9 2006/03/10 14:49:11 kristo Exp $
// cfgcontroller.aw - Kontroller(Classbase) 
/*

@classinfo syslog_type=ST_CFGCONTROLLER relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property formula type=textarea rows=20 cols=80
@caption Valem

@property errmsg type=textbox
@caption Veateade
@comment Kuvatakse, kui kontroller blokeerib sisestuse

property show_error type=checkbox ch_value=1
caption Kas näitamise kontroller näitab elemendi asemel veateadet? 

property only_warn type=checkbox ch_value=1
caption Ainult hoiatus

property error_in_popup type=checkbox ch_value=1
caption Veateade popupis 
*/

class cfgcontroller extends class_base
{
	function cfgcontroller()
	{
		$this->init(array(
			"tpldir" => "cfg/cfgcontroller",
			"clid" => CL_CFGCONTROLLER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			
		};
		return $retval;
	}
	
	
	/** runs the controller given
		@attrib api=1

		@param controller_oid required type=int
			OID of the controller to run

		@param obj_id required type=int
			OID of the object to run the controller for

		@param prop required type=array
			Data for the property to check

		@param request required type=array
			Array of name=>value pairs that come from the http request currently in progress

		@param entry required type=array
			Data to pass to the controller

		@param obj_inst required type=object
			The object the controller should be run on

		@errors
			error is thrown if the controller object given does not exist
	
		@returns
			the value that the controller sets to the variable $retval

		@examples
			$ctr = obj(59);	
			$object_to_run_on = obj(100);
			$ctr_instance = $crt->instance();
			$prop = array("name" => "whatever");
			echo "the controller said ".$ctr_instance->check_property($ctr->id(), $object_to_run_on->id(), $prop, $_GET, array("a" => "b"), $object_to_run_on);

			// prints whatever the controller assigned to $retval
	**/
	function check_property($controller_oid, $obj_id, &$prop, $request, $entry, $obj_inst)
	{
		// $controller_oid, $obj_id, &$prop, $request, $entry, $obj_inst
		//extract($arr);
		$retval = PROP_OK;
		if (!is_oid($controller_oid))
		{
			return;
		}
		$controller_inst = &obj($controller_oid);
		eval($controller_inst->prop("formula"));
		return $retval;
	}
}
?>
