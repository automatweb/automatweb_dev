<?php
// json_source.aw - JSON Source
/*

@classinfo syslog_type=ST_JSON_SOURCE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=voldemar

@default table=objects
@default group=general

*/

class json_source extends class_base
{
	function json_source()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "common/external/json_source",
			"clid" => CL_JSON_SOURCE
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//

		}
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}

?>
