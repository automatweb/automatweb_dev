<?php
// $Header: /home/cvs/automatweb_dev/classes/workflow/swot/Attic/swot_type.aw,v 1.2 2003/06/11 17:18:37 duke Exp $

class swot_type extends class_base
{
	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		if ($prop["name"] == "clf")
		{
			$cf = get_instance("classificator");
			$prop['options'] = $cf->get_clfs(array(
				"parent" => $arr['obj']['parent'],
				"clid" => $this->clid,
				"add_empty" => true
			));
		}
		return PROP_OK;
	}
}
?>
