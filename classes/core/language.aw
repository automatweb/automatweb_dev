<?php
// $Header: /home/cvs/automatweb_dev/classes/core/language.aw,v 1.1 2004/02/11 11:57:28 kristo Exp $
// language.aw - Keel 
/*

@classinfo syslog_type=ST_LANGUAGE relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@tableinfo languages index=oid master_table=objects master_index=oid

@property lang_name table=languages type=textbox field=name
@caption Nimi

@property lang_charset table=languages type=select field=charset
@caption Charset

@property lang_status table=languages type=status field=status
@caption Aktiivne

@property lang_acceptlang table=languages type=select field=acceptlang
@caption Keele kood

@property lang_site_id table=languages type=select multiple=1 field=site_id
@caption Saidid

@property lang_trans_msg table=languages type=textarea rows=5 cols=30 field=meta method=serialize
@caption T&otilde;lkimata sisu teade

@property lang_img table=languages type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
@caption Keele pilt

@property lang_img_act table=languages type=relpicker reltype=RELTYPE_IMAGE field=meta method=serialize
@caption Aktiivse keele pilt

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
			case "lang_acceptlang":
				$tmp = aw_ini_get("languages.list");
				$lang_codes = array();
				foreach($tmp as $langdata)
				{
					$lang_codes[$langdata["acceptlang"]] = $langdata["acceptlang"] . " (" . $langdata["name"] . ")";
				};
				$prop["options"] = $lang_codes;
				break;

			case "lang_charset":
				$tmp = aw_ini_get("languages.list");
				$lang_codes = array();
				foreach($tmp as $langdata)
				{
					$lang_codes[$langdata["charset"]] = $langdata["charset"] . " (" . $langdata["name"] . ")";
				};
				$prop["options"] = $lang_codes;
				break;

			case "lang_site_id":
				$prop["options"] = $this->adm->_get_sl();
				break;
		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/
}
?>
