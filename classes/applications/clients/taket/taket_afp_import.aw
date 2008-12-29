<?php
/*
@classinfo syslog_type=ST_TAKET_AFP_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert
@tableinfo aw_taket_afp_import master_index=brother_of master_table=objects index=aw_oid

@default group=general
@default table=objects

@property main_tb type=toolbar no_caption=1

@property name type=textbox
@caption Nimi

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE field=meta method=serialize
@caption Ladu

@property org_fld type=relpicker reltype=RELTYPE_ORG_FLD field=meta method=serialize
@caption Organisatsioonide kaust

@property amount type=textbox field=meta method=serialize default=5000
@caption Mitu rida korraga importida

@property code_ctrl type=relpicker reltype=RELTYPE_CODE_CONTROLLER field=meta method=serialize
@caption L&uuml;hikese koodi kontroller

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption Ladu

@reltype CODE_CONTROLLER value=2 clid=CL_CFGCONTROLLER
@caption L&uuml;hikese koodi kontroller

@reltype ORG_FLD value=3 clid=CL_MENU
@caption Organisatsioonide kaust
*/

class taket_afp_import extends class_base
{
	function taket_afp_import()
	{
		$this->init(array(
			"tpldir" => "applications/clients/taket/taket_afp_import",
			"clid" => CL_TAKET_AFP_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
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

	function _get_main_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		
		$tb->add_button(array(
			"name" => "import_button",
			"action" => "import_data",
			"img" => "import.gif",
			"tooltip" => t("Impordi tooteandmed"),
		));
	}

	/**
	@attrib name=import_data
	**/
	function import_data($arr)
	{
		if($this->can("view", $arr["id"]))
		{
			obj($arr["id"])->get_data($arr);
		}
		return $arr["post_ru"];
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}

?>
