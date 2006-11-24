<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_booking.aw,v 1.2 2006/11/24 11:06:26 kristo Exp $
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

@reltype ROOM_BRON value=3 clid=CL_RESERVATION
@caption Ruumi broneering
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

	/** checks if all necessary reservation objects are connected to this booking object and creates empty ones if needed
		@attrib api=1
	**/
	function check_reservation_conns($booking)
	{
		if (!$this->can("view", $booking->prop("package")))
		{
			return;
		}

		$rv2prod = array();
		foreach($booking->connections_from(array("type" => "RELTYPE_ROOM_BRON")) as $c)
		{
			$room_bron = $c->to();
			$rv2prod[$room_bron->meta("product_for_bron")] = $room_bron;
		}

		$package = obj($booking->prop("package"));
		$pk = $package->instance();
		$entry_inst = get_instance(CL_SPA_BOOKIGS_ENTRY);
		$dates = $entry_inst->get_booking_data_from_booking($booking);
		foreach($pk->get_products_for_package($package) as $prod)
		{
			if (!isset($rv2prod[$prod->id()]))
			{
				$rooms = $entry_inst->get_rooms_for_product($prod->id());
				if (count($rooms))
				{
					$room_inst = get_instance(CL_ROOM);
					$rv_id = $room_inst->make_reservation(array(
						"id" => reset(array_keys($rooms)),
						"data" => array(
							"customer" => $booking->prop("person")
						),
						"meta" => array(
							"product_for_bron" => $prod->id()
						)
					));
					$booking->connect(array(
						"to" => $rv_id,
						"type" => "RELTYPE_ROOM_BRON"
					));
				}
			}
		}
	}
}
?>
