<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_booking.aw,v 1.1 2006/11/15 13:07:21 kristo Exp $
// spa_booking.aw - SPA Reserveering 
/*

@classinfo syslog_type=ST_SPA_BOOKING relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_spa_bookings index=aw_oid master_index=brother_of master_table=objects

@default table=aw_spa_bookings
@default group=general

	@property person type=relpicker reltype=RELTYPE_PERSON field=aw_person
	@caption Isik

	@property start type=date_select field=aw_start
	@caption Algus

	@property end type=date_select field=aw_end
	@caption L&otilde;pp

	@property package type=relpicker reltype=RELTYPE_PACKAGE field=aw_package automatic=1
	@caption Pakett


@reltype PERSON value=1 clid=CL_CRM_PERSON
@caption Isik

@reltype PACKAGE value=2 clid=CL_SHOP_PACKET
@caption Pakett
*/

class spa_booking extends class_base
{
	function spa_booking()
	{
		$this->init(array(
			"tpldir" => "applications/spa_bookings/spa_booking",
			"clid" => CL_SPA_BOOKING
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		if ($arr["request"]["from_b"])
		{
			$fb = obj($arr["request"]["from_b"]);
			$prop["value"] = $fb->prop($prop["name"]);
		}
		switch($prop["name"])
		{
			case "name":
				$prop["type"] = "text";
				break;

			case "person":
			case "package":
				if (!isset($prop["options"][$prop["value"]]) && $this->can("view", $prop["value"]))
				{
					$tmp = obj($prop["value"]);
					$prop["options"][$prop["value"]] = $tmp->name();
				}
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "name":
				return PROP_IGNORE;
		}
		return $retval;
	}	

	function callback_pre_save($arr)
	{
		$arr["obj_inst"]->set_name(sprintf("Broneering %s %s - %s", 
			$arr["obj_inst"]->prop("person.name"), 
			date("d.m.Y", $arr["obj_inst"]->prop("start")), 
			date("d.m.Y", $arr["obj_inst"]->prop("end"))
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_spa_bookings (aw_oid int primary key, aw_person int, aw_start int, aw_end int, aw_package int)");
			return true;
		}
	}
}
?>
