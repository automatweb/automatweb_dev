<?php

// simple error class to replace core::raise_error. why? well, because this will have a *static* method
// that can throw errors, so you can throw errors from objects that do not derive from core

class error
{
	////
	// !throws an error, parameters:
	// id - error id, defined in errors.ini
	// msg - error message to show
	// fatal - if set, aborts execution
	// show - if true, error is shown to user, error is always logged and sent to the mailinglist
	function raise($arr)
	{
		if (!isset($arr["id"]) || !$arr["id"])
		{
			$arr["id"] = ERR_GENERIC;
		}
		if (!isset($arr["msg"]))
		{
			$arr["msg"] = "";
		}
		if (!isset($arr["fatal"]))
		{
			$arr["fatal"] = true;
		}
		if (!isset($arr["show"]))
		{
			$arr["show"] = true;
		}
		$inst = new aw_template;
		$inst->init();
		$inst->raise_error($arr["id"], $arr["msg"], $arr["fatal"], !$arr["show"]);
	}

	////
	// !trows an acl error, parameters:
	// access - type of access that was denied
	// oid - the object to what the access was denied
	// func - the function where access was denied
	function throw_acl($arr)
	{
		error::raise(array(
			"id" => ERR_ACL,
			"msg" => "Acl error, access ".(isset($arr["access"]) ? "can_".$arr["access"] : "not specified")." was denied for object ".(isset($arr["oid"]) ? $arr["oid"] : "not specified")." in function ".(isset($arr["func"]) ? $arr["func"] : "not specified"),
			"fatal" => true,
			"show" => true
		));
	}

	////
	// !throws error if $cond is true
	// params:
	//	cond - if true, error is raised
	//	arr - array of parameters that get passed to throw()
	function raise_if($cond, $arr)
	{
		if ($cond)
		{
			error::raise($arr);
		}
	}

	/** checks if the current user has view access to the given oid and if not, redirects the user to the error page or gives a 404 error. does NOT send an error e-mail to the list

		@attrib api=1

		@param $oid 

		@comment
			oid - id of object to check
	**/
	function view_check($oid)
	{
		$t = new acl_base;
		$t->init();
		if (!is_oid($oid) || !$t->can("view", $oid))
		{
			$i = get_instance("menuedit");
			$i->_do_error_redir($oid);
		}
	}
}
?>
