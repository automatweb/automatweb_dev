<?php

/* Storage helper functions */

/** checks if the parameter is an aw object id
	@attrib api=1

	@param oid required type=var
		The value to check if it is a valid oid

	@comment
		This does NOT check if the object actually exists, it just checks if the parameter could be an object id.
		Valid object id's are integers that are greater than 0

	@returns boolean
		true if the given value is a valid oid, false if not
**/
function is_oid($oid)
{
	return is_numeric($oid) && $oid > 0;
}

/** checks if the parameter is a valid class_id
	@attrib api=1

	@param clid required type=int
		The value to check for a valid class_id

	@returns
		true if the parameter is a valid class id
		false if not
**/
function is_class_id($clid)
{
	if (!is_numeric($clid))
	{
		return false;
	}
	return true;
}

/** disables all acl checks
	@attrib api=1

	@comment
		This should only be used just before savin something, not before reading something. The reason for this is, that if you keep this on for a bit loner, then aw will start throwing errors about nonexisting objects. This is because object deletion checks are also done via acl and since acl is turned off, things start to go wrong if anything is deleted.
		This behaviour is intentional, to make it really hard to use this function, because you really shouldn't. Acls should be set correctly.

	@examples
		$o = obj($oid);
		aw_disable_acl();
		$o->save();			// this is how this is meant to be used.
		aw_restore_acl();
**/
function aw_disable_acl()
{
	if (!isset($GLOBALS["__aw_disable_acl"]) or !is_array($GLOBALS["__aw_disable_acl"]))
	{
		$GLOBALS["__aw_disable_acl"] = array();
	}
	$GLOBALS["__aw_disable_acl"][] = aw_ini_get("acl.no_check");
	aw_ini_set("acl.no_check", "1");
}

/** restores acl checks that were turned off by aw_disable_acl()
	@attrib api=1

	@examples
		${aw_disable_acl}
**/
function aw_restore_acl()
{
	aw_ini_set("acl.no_check", array_pop($GLOBALS["__aw_disable_acl"]));
}

/** Finds the class id, given the class name
	@attrib api=1 params=pos

	@param class_name required type=string
		The name of the class to look up

	@returns
		The class_id of the given class or null if no id is assigned to that class

	@examples
		$o = obj();
		$o->set_class_id(clid_for_name("bug"));
**/
function clid_for_name($class_name)
{
	try
	{
		$clid = aw_ini_get("class_lut.".$class_name);
	}
	catch (Exception $e)
	{
		$clid = null;
	}

	return $clid;
}



/** Loads and returns object instance by $param or creates empty new aw object
@attrib api=1
@param param optional type=var
	Object id, alias, ...
@returns object
	Automatweb object
**/
function obj($param = NULL)
{
	return new object($param);
}

/** sets an object system property

@comment
currently possible options:
no_cache - 1/0 - if 1, ds_cache is not used even if it is loaded
**/
function obj_set_opt($opt, $val)
{
//echo "set opt $opt => $val from ".dbg::short_backtrace()." <br>";
	$tmp = null;
	if (!isset($GLOBALS['__obj_sys_opts']))
	{
		$GLOBALS['__obj_sys_opts'] = array();
	}
	if (isset($GLOBALS['__obj_sys_opts'][$opt]))
	{
		$tmp = $GLOBALS['__obj_sys_opts'][$opt];
	}
	$GLOBALS["__obj_sys_opts"][$opt] = $val;
	return $tmp;
}

function obj_get_opt($opt)
{
	return $GLOBALS["__obj_sys_opts"][$opt];
}

function dump_obj_table($pre = "")
{
	echo "---------------------------------------- object table dump: <br />$pre <br />\n";
	foreach($GLOBALS["objects"] as $oid => $obj)
	{
		echo "oid in list $oid , data: {oid => ".$obj->id().", name = ".$obj->name()." parent = ".$obj->parent()." } <br />\n";
	}
	echo "++++++++++<br />\n";
	flush();
}

?>