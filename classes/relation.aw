<?php

/*

@classinfo syslog_type=ST_RELATION maintainer=kristo

@default table=objects
@default group=general

*/

class relation extends class_base
{
	function relation()
	{
		$this->init(array(
			"clid" => CL_RELATION
		));
	}

	// now I have an interesting dilemma, I have to show a different configuration form
	// based on the subclass of the relation object. This means I need to be able to 
	// go between the load object data of class_base

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "comment":
			case "status":
				$retval = PROP_IGNORE;
				break;
		};
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "status":
				$data["value"] = STAT_ACTIVE;
				break;

		}
		return $retval;
	}

}
?>
