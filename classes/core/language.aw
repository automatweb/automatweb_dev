<?php
// $Header: /home/cvs/automatweb_dev/classes/core/language.aw,v 1.4 2004/02/12 15:01:54 kristo Exp $
// language.aw - Keel 
/*

@classinfo syslog_type=ST_LANGUAGE relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@groupinfo langs caption="K&otilde;ik keeled"

@tableinfo languages index=oid master_table=objects master_index=oid

@property lang_status table=languages type=status field=status
@caption Aktiivne

@property lang_name table=languages type=textbox field=name
@caption Nimi

@property lang_sel_lang type=select store=no
@caption Keel

@property lang_charset table=languages type=select field=charset
@caption Charset

@property lang_acceptlang table=languages type=select field=acceptlang
@caption Keele kood

@property lang_site_id table=languages type=select multiple=1 field=site_id
@caption Saidid kus keel on valitav

@property lang_id table=languages type=hidden field=id
@caption Keele ID

@property lang_trans_msg table=languages type=textarea rows=5 cols=30 field=meta method=serialize
@caption T&otilde;lkimata sisu teade

@property lang_img table=languages type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
@caption Keele pilt

@property lang_img_act table=languages type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
@caption Aktiivse keele pilt

@property langs type=table group=langs field=meta method=serialize store=no
@caption Keeled

@reltype IMAGE value=1 clid=CL_IMAGE
@caption pilt

*/

class language extends class_base
{
	function language()
	{
		$this->init(array(
			"tpldir" => "languages",
			"clid" => CL_LANGUAGE
		));

		$this->adm = get_instance("admin/admin_languages");

		// check if we need to upgrade
		$tbl = $this->db_get_table("languages");
		if (!isset($tbl["fields"]["oid"]))
		{
			die("Keeled tuleb konvertida uuele s&uuml;steemile, seda saab teha ".html::href(array(
				"url" => $this->mk_my_orb("lang_new_convert", array(), "converters"),
				"caption" => "siit"
			)));
		}
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "lang_site_id":
				$prop["options"] = $this->adm->_get_sl();
				$prop["value"] = $this->make_keys(explode(",",$prop["value"]));
				break;

			case "langs":
				$this->_get_langs_tbl($arr);
				break;

			case "lang_sel_lang":
				$ol = new object_list(array(
					"class_id" => CL_LANGUAGE,
					"site_id" => array(),
					"lang_id" => array()
				));
				$ll = array();
				for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
				{
					$ll[$o->prop("lang_acceptlang")] = true;
				}

				$tmp = aw_ini_get("languages.list");
				$lang_codes = array();
				foreach($tmp as $_lid => $langdata)
				{
					if ($langdata["acceptlang"] == $arr["obj_inst"]->prop("lang_acceptlang"))
					{
						$prop["selected"] = $_lid;
						$lang_codes[$_lid] = $langdata["name"];
					}
					else
					if (!isset($ll[$langdata["acceptlang"]]))
					{
						$lang_codes[$_lid] = $langdata["name"];
					}
				};
				$prop["options"] = $lang_codes;
				break;

			case "lang_acceptlang":
				return PROP_IGNORE;

			case "lang_charset":
				return PROP_IGNORE;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "langs":
				$ol = new object_list(array(
					"class_id" => CL_LANGUAGE,
					"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
					"lang_id" => array(),
					"site_id" => array()
				));
				for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
				{
					if ($arr["request"]["act"][$o->id()] != ($o->prop("lang_status") - 1))
					{
						$o->set_status($arr["request"]["act"][$o->id()] == 1 ? STAT_ACTIVE : STAT_NOTACTIVE);
						$o->set_prop("lang_status", $arr["request"]["act"][$o->id()] == 1 ? STAT_ACTIVE : STAT_NOTACTIVE);
						$o->save();
						$al = get_instance("admin/admin_languages");
						$al->set_status($o->prop("lang_id"), $arr["request"]["act"][$o->id()] == 1 ? STAT_ACTIVE : STAT_NOTACTIVE);
					}
				}
				if ($arr["request"]["set_sel_lang"] != aw_global_get("lang_id"))
				{
					$l = get_instance("languages");
					$l->set_active($arr["request"]["set_sel_lang"], true);
				}
				break;

			case "lang_sel_lang":
				// set the acceptlang and charset properties based on the selection
				$tmp = aw_ini_get("languages.list");
				$arr["obj_inst"]->set_prop("lang_acceptlang", $tmp[$prop["value"]]["acceptlang"]);
				$arr["obj_inst"]->set_prop("lang_charset", $tmp[$prop["value"]]["charset"]);
				$l = get_instance("admin/admin_languages");	
				$l->init_cache(true);
				break;

			case "lang_acceptlang":
				return PROP_IGNORE;

			case "lang_charset":
				return PROP_IGNORE;

			case "lang_site_id":
				$prop["value"] = join(",", array_keys(is_array($prop["value"]) ? $prop["value"] : array()));
				break;

		}
		return $retval;
	}	

	function callback_pre_save($arr)
	{
		$arr["obj_inst"]->set_name($arr["obj_inst"]->prop("lang_name"));
		$arr["obj_inst"]->set_prop("lang_site_id", join(",", array_keys(is_array($prop["value"]) ? $arr["obj_inst"]->prop("lang_site_id") : array())));
		$l = get_instance("admin/admin_languages");	
		$l->init_cache(true);
	}

	function _get_langs_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "lang",
			"caption" => "Keel",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "act",
			"caption" => "Staatus",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "sel",
			"caption" => "Valitud",
			"align" => "center"
		));

		$ol = new object_list(array(
			"class_id" => CL_LANGUAGE,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"lang_id" => array(),
			"site_id" => array()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$t->define_data(array(
				"lang" => $o->prop("name"),
				"act" => html::checkbox(array(
					"name" => "act[".$o->id()."]",
					"value" => 1,
					"checked" => ($o->prop("lang_status") == 2)
				)),
				"sel" => html::radiobutton(array(
					"name" => "set_sel_lang",
					"value" => $o->prop("lang_id"),
					"checked" => (aw_global_get("lang_id") == $o->prop("lang_id"))
				))
			));
		}
	}

	function on_site_init(&$dbi, &$site, &$ini_opts, &$log, &$osi_vars)
	{
		if ($site['site_obj']['use_existing_templates'])
		{
			return;
		}
		$conv = get_instance("admin/converters");
		$conv->dc = $dbi->dc;
		$conv->lang_new_convert(array(
			"parent" => $osi_vars["langs"]
		));
	}
}
?>
