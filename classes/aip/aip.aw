<?php

/*

@classinfo syslog_type=ST_AIP

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

*/

class aip extends class_base
{
	function aip()
	{
		$this->init(array(
			'tpldir' => 'aip/aip',
			'clid' => CL_AIP
		));
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	function get_root()
	{
		if (aw_global_get("lang_id") == 3)
		{
			return 6551;
		}
		else
		{
			return 298;
		}
	}

	function mk_yah_link($section, $at = false)
	{
		if (!is_object($at))
		{
			global $at;
			if (!is_object($at))
			{
				$at = new class_base;
				$at->init();
			}
		}
		$od = $at->get_object_chain($section);
		$show = true;
		$od = array_reverse($od);
		$show = false;
		foreach($od as $_oid => $row)
		{
			if ($show)
			{
				$meta = $at->get_object_metadata(array(
					"metadata" => $row["metadata"]
				));
				$at->vars(array(
					"pre" => $meta["aip_menu_upload_id"],
					"parent" => $row["oid"],
					"name" => $row["name"]
				));
				$t .= $at->parse("YAH_LINK");
			}
			if ($row["oid"] == aip::get_root())
			{
				$show = true;
			}
		}
		return $t;
	}
}
?>
