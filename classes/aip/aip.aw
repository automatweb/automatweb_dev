<?php
/*

@classinfo syslog_type=ST_AIP

@default table=objects
@default group=general

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
