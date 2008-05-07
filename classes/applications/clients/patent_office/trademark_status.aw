<?php
// trademark_status.aw - Trademark status
/*

@classinfo syslog_type=ST_TRADEMARK_STATUS relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@tableinfo aw_trademark_status index=aw_oid master_table=objects master_index=brother_of

@default group=general

	@property verified type=checkbox table=aw_trademark_status field=aw_verified ch_value=1
	@caption Kinnitatud

	@property exported type=checkbox caption=no table=aw_trademark_status field=aw_exported ch_value=1
	@caption Eksporditud

	@property export_date type=date_select table=aw_trademark_status field=aw_exported_date
	@caption Ekspordi kuup&auml;ev

	@property nr type=textbox table=aw_trademark_status field=aw_nr
	@caption Taotluse number

	@property sent_date type=date_select table=aw_trademark_status field=aw_sent_date
	@caption Saatmise kuup&auml;ev

	@property modified type=hidden table=objects field=modified

*/

class trademark_status extends class_base
{
	function trademark_status()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/trademark_status",
			"clid" => CL_TRADEMARK_STATUS
		));
	}

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

	function set_property($arr = array())
	{

		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f, $q, $err)
	{
		if ($f == "" && $t == "aw_trademark_status")
		{
			$this->db_query("CREATE TABLE aw_trademark_status(
				aw_oid int primary key,
				aw_verified int,
				aw_exported int,
				aw_exported_date int,
				aw_nr int,
				aw_sent_date int,
			)");
		}
		switch($f)
		{
			case "aw_sent_date":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}


//-- methods --//
}
?>
