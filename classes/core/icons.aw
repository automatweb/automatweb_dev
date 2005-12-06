<?php
// $Header: /home/cvs/automatweb_dev/classes/core/icons.aw,v 1.2 2005/12/06 18:20:36 kristo Exp $

class icons extends aw_template
{
	function icons()
	{
		$this->init("automatweb/config");
	}

	
	/** returns the url for the icons for the given class / object name (for file objects)

		@attrib api=1

		@comment
			arg1 - class id / object class instance
			name - object name
	**/
	function get_icon_url($arg1,$name = "")
	{
		if (is_object($arg1))
		{
			$clid = $arg1->class_id();
			$done = false;
			$done = $arg1->flags() & OBJ_IS_DONE;
		}
		else
		{
			$clid = $arg1;
		};

		if ($clid == CL_FILE)
		{
			$pi = pathinfo($name);
			return aw_ini_get("icons.server")."/ftype_".$pi["extension"].".gif";
		}
		else
		if (in_array($clid,array("promo_box","brother","conf_icon_other","conf_icon_programs","conf_icon_classes","conf_icon_ftypes","conf_icons","conf_jf","conf_users","conf_icon_import","conf_icon_db","homefolder","hf_groups")))
		{
			return aw_ini_get("icons.server")."/iother_".$clid.".gif";
		}
		else
		{
			$sufix = $done ? "_done" : "";
			return aw_ini_get("icons.server")."/class_".$clid.$sufix.".gif";
		}

		return aw_ini_get("baseurl")."/automatweb/images/icon_aw.gif";
	}
	
	function get_feature_icon_url($fid)
	{
		return aw_ini_get("icons.server")."/prog_".$fid.".gif";
	}

	function get_icon($o)
	{
		return html::img(array(
			"url" => icons::get_icon_url($o),
		));
	}
}
?>
