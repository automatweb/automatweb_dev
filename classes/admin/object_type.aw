<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/object_type.aw,v 1.1 2003/11/08 09:29:01 duke Exp $
// object_type.aw - objekti klass (lisamise puu jaoks)
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property type type=select
	@caption Objektitüüp

*/

class object_type extends class_base
{
	function object_type()
	{
		$this->init(array(
			"clid" => CL_OBJECT_TYPE,
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "type":
				$data["options"] = $this->get_type_picker();
				break;
		}
		return PROP_OK;
	}

	function get_type_picker()
	{
		$ret = array();
		foreach($this->cfg["classes"] as $clid => $cldat)
		{
			if ($cldat["can_add"] == 1)
			{
				$ret[$clid] = $cldat["name"];
			}
		}
		asort($ret);
		$ret = array("__all_objs" => "K&otilde;ik") + $ret;
		return $ret;
	}
}
?>
