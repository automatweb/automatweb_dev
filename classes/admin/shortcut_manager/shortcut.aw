<?php
/*
@classinfo syslog_type=ST_SHORTCUT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=hannes

@default table=objects
@default group=general

@property name type=textbox field=name
@caption Nimi

@property keycombo type=textbox field=meta method=serialize
@caption Klahvikombinatsioon

@property type type=select field=meta method=serialize
@caption T&uml;&uml;p

@property url type=textbox field=meta method=serialize
@caption URL

*/

class shortcut extends class_base
{
	function shortcut()
	{
		$this->init(array(
			"tpldir" => "admin/shortcut_manager/shortcut",
			"clid" => CL_SHORTCUT
		));
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "type":
					$prop["options"] = array(
						"" => t("Vali t&uml;&uml;"),
						"go_to_url" => t("Mine URL'ile")
					);
				break;
			case "url":
				$o = $arr["obj_inst"];
				if ($o->prop("type") != "go_to_url")
				{
					$retval = PROP_IGNORE;
				}
			break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}
	
	function callback_generate_scripts($arr)
	{
		$s_out = '
		$.getScript("'.aw_ini_get("baseurl").'/automatweb/js/jquery/plugins/jquery_catch_keycombo.js", function(){
			$("#keycombo").catch_keycombo();
		});
		';
		return $s_out;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}

?>
