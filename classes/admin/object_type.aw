<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/object_type.aw,v 1.3 2003/12/03 12:47:00 kristo Exp $
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

	////
	// !builds the url for adding a new object
	function get_add_url($arr)
	{
		$o = new object($arr["id"]);

		$clss = $this->cfg["classes"][$o->prop("type")]["file"];
		if ($clss == "document")
		{
			$clss = "doc";
		}
		$rv = $this->mk_my_orb("new", array(
				"parent" => $arr["parent"],
				"period" => aw_global_get("period"),
				"section" => $arr["section"],
				"cfgform" => $o->prop("use_cfgform"),
			 ),$clss);
		return $rv;
	

	}
}
?>
