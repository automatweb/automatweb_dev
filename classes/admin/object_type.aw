<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/object_type.aw,v 1.2 2003/11/18 14:42:06 duke Exp $
// object_type.aw - objekti klass (lisamise puu jaoks)
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property type type=select 
	@caption Objektitüüp

	@property use_cfgform type=relpicker reltype=RELTYPE_OBJECT_CFGFORM
	@caption Kasuta seadete vormi

	@reltype OBJECT_CFGFORM value=1 clid=CL_CFGFORM
	@caption Seadete vorm

	@classinfo relationmgr=yes

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
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "type":
				$data["options"] = $this->get_type_picker();
				break;
		}
		return $retval;
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
