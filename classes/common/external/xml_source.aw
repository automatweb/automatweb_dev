<?php
// xml_source.aw - XML source
/*

@classinfo syslog_type=ST_XML_SOURCE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default field=meta
@default method=serialize

@default table=objects
@default group=general

	@property url type=textbox
	@caption URL
	@comment XML faili URL

	@property tag_lang type=select
	@caption Keel
	@comment XML väli või argument, kus on imporditava sündmuse tõlke keel

	@property tag_event type=select
	@caption Sündmus
	@comment XML väli, kus on kogu imporditav sündmus

	@property tag_id type=select
	@caption ID
	@comment XML väli või argument, kus on imporditava sündmuse ID

@groupinfo parameters caption="Parameetrid"
@default group=parameters

	@property start_timestamp_unix type=textbox
	@caption Timestamp (UNIX)
	@comment UNIX tüüpi timestamp v&auml;li, mille j&auml;rgi s&uuml;ndmusi p&auml;ritakse

	@property start_timestamp type=textbox
	@caption Timestamp (YYYYMMDDHHMMSS)
	@comment Timestamp v&auml;li, mille j&auml;rgi s&uuml;ndmusi p&auml;ritakse

	@property language type=textbox
	@caption Keel
	@comment Mis keeles sündmusi p&auml;ritakse?

@groupinfo tags caption="T&auml;&auml;gid"
@default group=tags

	@property subtag_table type=table no_caption=1

@groupinfo arguements caption="T&auml;&auml;gide atribuudid"
@default group=arguements

	@property arguement_table type=table no_caption=1

@groupinfo languages caption="Keeled"
@default group=languages

	@property language_table type=table no_caption=1

*/

class xml_source extends class_base
{
	function xml_source()
	{
		// change this to the folder under the templates folder, where this classes templates will be,
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "common/external/xml_source",
			"clid" => CL_XML_SOURCE
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function _get_tag_lang($arr)
	{
		// There is just one language and that is Estonian
		$arr["prop"]["options"]["tijolatie"] = t("-- XML väljund on ainult eesti keeles --");
		$lang = $arr["obj_inst"]->prop("language");
		if(!empty($lang))
		{
			// The language is defined by URL parameters
			$arr["prop"]["options"]["tlidbup"] = t("-- Keel defineeritakse URLi parameetri abil --");
		}

		$saved_subtag_table = $arr['obj_inst']->meta("subtag_table");
		$saved_arguement_table = $arr['obj_inst']->meta("arguement_table");

		if(!isset($arr["parent_tag_name"]))
		{
			$arr["parent_tag_name"] = "root";
			$arr["parent_tag_caption"] = "root";
		}
		$subtags = $saved_subtag_table[$arr["parent_tag_name"]];
		$subtags = str_replace(" ", "", $subtags);
		$subtags = explode(",", $subtags);
		foreach($subtags as $subtag)
		{
			if(!empty($subtag))
			{
				$t_ptn = $arr["parent_tag_name"];
				$t_ptc = $arr["parent_tag_caption"];
				if($arr["parent_tag_name"] != "root")
				{
					$arr["parent_tag_name"] .= "_".$subtag;
					$arr["parent_tag_caption"] .= " -> ".$subtag;
				}
				else
				{
					$arr["parent_tag_name"] = $subtag;
					$arr["parent_tag_caption"] = $subtag;
				}

				$arr["prop"]["options"][$arr["parent_tag_name"]] = $arr["parent_tag_caption"];

				$subt_args = $saved_arguement_table[$arr["parent_tag_name"]];
				$subt_args = str_replace(" ", "", $subt_args);
				$subt_args = explode(",", $subt_args);

				foreach($subt_args as $subt_arg)
				{
					if(!empty($subt_arg))
					{
						$arr["prop"]["options"][$arr["parent_tag_name"]."_args".$subt_arg] = $arr["parent_tag_caption"]." (".$subt_arg.")";
					}
				}

				$this->_get_tag_id($arr);
				$arr["parent_tag_name"] = $t_ptn;
				$arr["parent_tag_caption"] = $t_ptc;
			}
		}
	}

	function _get_tag_id($arr)
	{
		$saved_subtag_table = $arr['obj_inst']->meta("subtag_table");
		$saved_arguement_table = $arr['obj_inst']->meta("arguement_table");

		if(!isset($arr["parent_tag_name"]))
		{
			$arr["parent_tag_name"] = "root";
			$arr["parent_tag_caption"] = "root";
		}
		$subtags = $saved_subtag_table[$arr["parent_tag_name"]];
		$subtags = str_replace(" ", "", $subtags);
		$subtags = explode(",", $subtags);
		foreach($subtags as $subtag)
		{
			if(!empty($subtag))
			{
				$t_ptn = $arr["parent_tag_name"];
				$t_ptc = $arr["parent_tag_caption"];
				if($arr["parent_tag_name"] != "root")
				{
					$arr["parent_tag_name"] .= "_".$subtag;
					$arr["parent_tag_caption"] .= " -> ".$subtag;
				}
				else
				{
					$arr["parent_tag_name"] = $subtag;
					$arr["parent_tag_caption"] = $subtag;
				}

				$arr["prop"]["options"][$arr["parent_tag_name"]] = $arr["parent_tag_caption"];

				$subt_args = $saved_arguement_table[$arr["parent_tag_name"]];
				$subt_args = str_replace(" ", "", $subt_args);
				$subt_args = explode(",", $subt_args);

				foreach($subt_args as $subt_arg)
				{
					if(!empty($subt_arg))
					{
						$arr["prop"]["options"][$arr["parent_tag_name"]."_args".$subt_arg] = $arr["parent_tag_caption"]." (".$subt_arg.")";
					}
				}

				$this->_get_tag_id($arr);
				$arr["parent_tag_name"] = $t_ptn;
				$arr["parent_tag_caption"] = $t_ptc;
			}
		}
	}

	function _get_language_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable("false");
		$t->define_field(array(
			"name" => "lang",
			"caption" => t("Keel"),
		));
		$t->define_field(array(
			"name" => "param_value",
			"caption" => t("Parameetri väärtus"),
		));

		$saved_lang_conf = $arr["obj_inst"]->meta("language_table");

		$t->define_data(array(
			"lang" => t("Inglise keel"),
			"param_value" => html::textbox(array(
				"name" => "language_table[en]",
				"value" => $saved_lang_conf["en"],
			)),
		));
		$t->define_data(array(
			"lang" => t("Eesti keel"),
			"param_value" => html::textbox(array(
				"name" => "language_table[et]",
				"value" => $saved_lang_conf["et"],
			)),
		));
	}

	function _get_tag_event($arr)
	{
		$saved_subtag_table = $arr['obj_inst']->meta("subtag_table");

		if(!isset($arr["parent_tag_name"]))
		{
			$arr["parent_tag_name"] = "root";
			$arr["parent_tag_caption"] = "root";
		}
		$subtags = $saved_subtag_table[$arr["parent_tag_name"]];
		$subtags = str_replace(" ", "", $subtags);
		$subtags = explode(",", $subtags);
		foreach($subtags as $subtag)
		{
			if(!empty($subtag))
			{
				$t_ptn = $arr["parent_tag_name"];
				$t_ptc = $arr["parent_tag_caption"];
				if($arr["parent_tag_name"] != "root")
				{
					$arr["parent_tag_name"] .= "_".$subtag;
					$arr["parent_tag_caption"] .= " -> ".$subtag;
				}
				else
				{
					$arr["parent_tag_name"] = $subtag;
					$arr["parent_tag_caption"] = $subtag;
				}

				$arr["prop"]["options"][$arr["parent_tag_name"]] = $arr["parent_tag_caption"];

				$this->_get_tag_event($arr);
				$arr["parent_tag_name"] = $t_ptn;
				$arr["parent_tag_caption"] = $t_ptc;
			}
		}
	}

	function subt_subt($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$saved_table["subtag"] = $arr["obj_inst"]->meta("subtag_table");
		$saved_table["arguement"] = $arr["obj_inst"]->meta("arguement_table");

		$subtags = $saved_table["subtag"][$arr["parent_tag_name"]];
		$subtags = str_replace(" ", "", $subtags);
		$subtags = explode(",", $subtags);
		foreach($subtags as $subtag)
		{
			if(!empty($subtag))
			{
				$t_ptn = $arr["parent_tag_name"];
				$t_ptc = $arr["parent_tag_caption"];
				if($arr["parent_tag_name"] != "root")
				{
					$arr["parent_tag_name"] .= "_".$subtag;
					$arr["parent_tag_caption"] .= " -> ".$subtag;
				}
				else
				{
					$arr["parent_tag_name"] = $subtag;
					$arr["parent_tag_caption"] = $subtag;
				}
				$t->define_data(array(
					"tag" => $arr["parent_tag_caption"],
					$arr["table_type"]."s" => html::textbox(array(
						"name" => $arr["table_type"]."_table[".$arr["parent_tag_name"]."]",
						"value" => $saved_table[$arr["table_type"]][$arr["parent_tag_name"]],
					)),
				));

				$this->subt_subt($arr);
				$arr["parent_tag_name"] = $t_ptn;
				$arr["parent_tag_caption"] = $t_ptc;
			}
		}
	}

	function _get_subtag_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "tag",
			"caption" => t("T&auml;&auml;g"),
		));
		$t->define_field(array(
			"name" => "subtags",
			"caption" => t("Subt&auml;&auml;gid"),
		));
		$t->define_field(array(
			"name" => "multiple",
			"caption" => t("Mitu"),
		));

		$saved_subtag_table = $arr['obj_inst']->meta("subtag_table");

		$o = obj($arr["request"]["id"]);

		$t->define_data(array(
			"tag" => "root",
			"subtags" => html::textbox(array(
				"name" => "subtag_table[root]",
				"value" => $saved_subtag_table["root"],
			)),
		));

		$arr["parent_tag_name"] = "root";
		$arr["parent_tag_caption"] = "root";
		$arr["table_type"] = "subtag";
		$this->subt_subt($arr);
	}

	function _get_arguement_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "tag",
			"caption" => t("T&auml;&auml;g"),
		));
		$t->define_field(array(
			"name" => "arguements",
			"caption" => t("Argumendid"),
		));

		$arr["parent_tag_name"] = "root";
		$arr["parent_tag_caption"] = "root";
		$arr["table_type"] = "arguement";
		$this->subt_subt($arr);
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "subtag_table":
				if (!empty($arr['request']["subtag_table"]))
				{
					$arr['obj_inst']->set_meta("subtag_table", $arr['request']['subtag_table']);
				}
				break;

			case "arguement_table":
				if (!empty($arr['request']["arguement_table"]))
				{
					$arr['obj_inst']->set_meta("arguement_table", $arr['request']['arguement_table']);
				}
				break;

			case "language_table":
				if (!empty($arr['request']["language_table"]))
				{
					$arr['obj_inst']->set_meta("language_table", $arr['request']['language_table']);
				}
				break;
		}
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}

?>
