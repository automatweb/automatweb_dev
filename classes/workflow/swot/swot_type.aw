<?php
// $Header: /home/cvs/automatweb_dev/classes/workflow/swot/Attic/swot_type.aw,v 1.4 2004/10/05 09:14:27 kristo Exp $

class swot_type extends class_base
{
	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		if ($prop["name"] == "clf" && !$arr["new"])
		{
			$cf = get_instance("classificator");
			$prop['options'] = $cf->get_clfs(array(
				"parent" => $arr['obj_inst']->parent(),
				"clid" => $this->clid,
				"add_empty" => true
			));
		}
		return PROP_OK;
	}
}
?>
