<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ows_bron/ows_reservation.aw,v 1.2 2007/10/12 10:42:03 kristo Exp $
// ows_reservation.aw - OWS Broneering 
/*

@classinfo syslog_type=ST_OWS_RESERVATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_ows_reservations index=aw_oid master_table=objects master_index=brother_of

@default table=aw_ows_reservations
@default group=general

@property is_confirmed type=checkbox ch_value=1 field=aw_is_confirmed
@caption Kinnitatud

@default group=cust_data

@property hotel_id type=textbox field=aw_hotel_id
@caption Hotell

@property rate_id type=textbox field=aw_rate_id
@caption Rate

@property arrival_date type=date_select field=aw_arrival
@caption Saabumine

@property departure_date type=date_select field=aw_departure
@caption Lahkumine

@property num_rooms type=textbox field=aw_num_rooms
@caption Tube

@property adults_per_room type=textbox field=aw_adults_per_room
@caption Adults per room

@property child_per_room type=textbox field=aw_child_per_room
@caption Children per room

@property promo_code type=textbox field=aw_promo_code
@caption Promo kood

@property currency type=textbox field=aw_currency
@caption Valuuta

@property guest_title type=textbox field=aw_guest_title
@caption Guest title

@property guest_firstname type=textbox field=aw_first_name
@caption Guest first name

@property guest_lastname type=textbox field=aw_last_name
@caption Guest last name

@property guest_country type=textbox field=aw_country
@caption Guest country

@property guest_state type=textbox field=aw_state
@caption Guest state

@property guest_city type=textbox field=aw_city
@caption Guest city

@property guest_postal_code type=textbox field=aw_postal_code
@caption Guest postal code

@property guest_adr_1 type=textbox field=aw_adr_1
@caption Guest address line 1

@property guest_adr_2 type=textbox field=aw_adr_2
@caption Guest address line 2

@property guest_phone type=textbox field=aw_phone
@caption Guest phone

@property guest_email type=textbox field=aw_email
@caption Guest email

@property guest_comments type=textbox field=aw_comments
@caption Guest comments

@property guarantee_type type=textbox field=aw_guarantee_type
@caption Cuarantee type

@property guarantee_cc_type type=textbox field=aw_guarantee_cc_type
@caption Cuarantee CC type

@property guarantee_cc_holder_name type=textbox field=aw_guarantee_cc_holder_name
@caption Cuarantee CC holder name

@property guarantee_cc_num type=textbox field=aw_guarantee_cc_num
@caption Cuarantee CC number

@property guarantee_cc_exp_date type=date_select field=aw_guarantee_cc_exp_date
@caption Cuarantee CC exp date

@property payment_type type=textbox field=aw_payment_type
@caption Payment type

@default group=bron_data

	@property confirmation_code type=textbox field=aw_confirmation_code
	@caption Confirmation code

	@property booking_id type=textbox field=aw_booking_id
	@caption booking id

	@property cancel_deadline type=datetime_select field=aw_cancel_deadline
	@caption Cancel deadline

	@property total_room_charge type=textbox field=aw_total_room_charge
	@caption Total room charge

	@property total_tax_charge type=textbox field=aw_total_tax_charge
	@caption Total tax charge

	@property total_charge type=textbox field=aw_total_charge
	@caption Total charge

	@property charge_currency type=textbox field=aw_charge_currency
	@caption Charge currency

@groupinfo cust_data caption="Sisestatud andmed"
@groupinfo bron_data caption="Reserveeringu andmed"

*/

class ows_reservation extends class_base
{
	function ows_reservation()
	{
		$this->init(array(
			"tpldir" => "applications/ows_bron/ows_reservation",
			"clid" => CL_OWS_RESERVATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrage($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_ows_reservations (aw_oid int primary key, 
				aw_is_confirmed int, aw_hotel_id int, aw_rate_id int, 
				aw_arrival int, aw_departure int, aw_num_rooms int,
				aw_adults_per_room int,aw_child_per_room int, aw_promo_code varchar(255),
				aw_currency char(3), aw_guest_title varchar(255), aw_first_name varchar(255),
				aw_last_name varchar(255), aw_country varchar(255), aw_state varchar(255),
				aw_city varchar(255), aw_postal_code varchar(255), aw_adr_1 varchar(255), 
				aw_adr_2 varchar(255), aw_phone varchar(255), aw_email varchar(255),
				aw_comments varchar(255), aw_guarantee_type varchar(255), 
				aw_guarantee_cc_type varchar(255), aw_guarantee_cc_holder_name varchar(255), aw_guarantee_cc_num varchar(255),
				aw_guarantee_cc_exp_date int, aw_payment_type varchar(255),
				aw_confirmation_code varchar(255), aw_booking_id int, aw_cancel_deadline int, 
				aw_total_room_charge double, aw_total_tax_charge double, aw_total_charge double,
				aw_charge_currency varchar(255)
			)");
			return true;
		}
	}
}
?>
