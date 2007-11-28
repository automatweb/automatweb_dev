<?php
// $Header: /home/cvs/automatweb_dev/classes/fun/deathcounter.aw,v 1.2 2007/11/28 23:51:35 hannes Exp $
// deathcounter.aw - Surmakaunter 
/*

@classinfo syslog_type=ST_DEATHCOUNTER no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general



@property days_to_live type=text store=yes field=meta method=serialize
@caption Päevi elada

@property death_year type=select store=yes field=meta method=serialize
@caption Suremise aasta

@property death_month type=select store=yes field=meta method=serialize
@caption Suremise kuu

@property death_day type=select store=yes field=meta method=serialize
@caption Suremise p&auml;ev

property time_of_death type=date_select year_to=2100 field=meta method=serialize
caption Suremise aeg

*/

class deathcounter extends class_base
{
	function deathcounter()
	{
		$this->init(array(
			"tpldir" => "fun",
			"clid" => CL_DEATHCOUNTER
		));
		include_once(aw_ini_get("basedir")."/addons/ADOdb_Date_Time_Library/adodb-time.inc.php");
	}

	function callback_post_save($arr)
	{
		$o =& $arr["obj_inst"];
		$o-> set_prop("days_to_live", $this->days_to_live($arr) );
		$o->save();
	}

	function days_to_live($arr)
	{
		$obj = $arr["obj_inst"];
		$i_death_stamp = adodb_mktime (0, 0, 0, $obj->prop("death_month"), $obj->prop("death_day"), $obj->prop("death_year"));
		$i_time_to_live_stamp = $i_death_stamp  - time(); 
		return round ($i_time_to_live_stamp/(60*60*24));
	}

	function _get_death_month($arr)
	{
		$property =& $arr["prop"];

		$property["options"] = array(
			1 => t("Jaanuar"),
			2 => t("Veebruar"),
			3 => t("M&auml;rts"),
			4 => t("Aprill"),
			5 => t("Mai"),
			6 => t("Juuni"),
			7 => t("Juuli"),
			8 => t("August"),
			9 => t("September"),
			10 => t("Oktoober"),
			11 => t("November"),
			12 => t("Detsember"),
		);
	}

	function _get_death_day($arr)
	{
		$property =& $arr["prop"];

		for ($i=1;$i<32;$i++)
		{
			$property["options"][] = $i;
		}
	}


	function _get_death_year($arr)
	{
		$property =& $arr["prop"];

		$i_current_year = date("Y", time());

		for ($i=0;$i<100;$i++)
		{
			$a_years[$i_current_year+$i] = $i_current_year+$i;
		}

		$property["options"]  = $a_years;
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
		$this->read_template("deathcounter.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
			"days_to_live" => $ob->prop("days_to_live"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
