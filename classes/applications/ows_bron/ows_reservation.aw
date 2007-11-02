<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ows_bron/ows_reservation.aw,v 1.4 2007/11/02 10:03:02 kristo Exp $
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

@property smoking type=checkbox ch_value=1 field=aw_smoking
@caption Smoking

@property high_floor type=checkbox ch_value=1 field=aw_high_floor
@caption High floor

@property low_floor type=checkbox ch_value=1 field=aw_low_floor
@caption Low floor

@property is_allergic type=checkbox ch_value=1 field=aw_is_allergic
@caption Allergic

@property is_handicapped type=checkbox ch_value=1 field=aw_is_handicapped
@caption Handicapped

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

	function do_db_upgrade($t, $f)
	{
		switch($f)
		{
			case "aw_smoking":
			case "aw_high_floor":
			case "aw_low_floor":
			case "aw_is_allergic":
			case "aw_is_handicapped":
				$this->db_add_col($t, array("name" => $f, "type" => "int"));
				return true;
		}
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

	function bank_return($arr)
	{
if (!is_oid($arr["id"]))
{
	die("you bafoon!");
}
			$o = obj($arr["id"]);
			if ($o->prop("is_confirmed") == 1)
			{
					return;
			}

			$checkin = date("Y", $o->prop("arrival_date")).'-'.date("m", $o->prop("arrival_date")).'-'.date("d", $o->prop("arrival_date")).'T00:00:00';

			$checkout = date("Y", $o->prop("departure_date")).'-'.date("m", $o->prop("departure_date")).'-'.date("d", $o->prop("departure_date")).'T23:59:00';

			$l = get_instance("languages");
			$owb = get_instance(CL_OWS_BRON);
			$lang = $owb->get_web_language_id($l->get_langid($o->lang_id()));

			$params = array(
   			"hotelId" => $o->prop("hotel_id"),
      	"rateId" => $o->prop("rate_id"),
      	"arrivalDate" => $checkin,
      	"departureDate" => $checkout,
      	"numberOfRooms" => (int)$o->prop("num_rooms"),
      	"numberOfAdultsPerRoom" => (int)$o->prop("adults_per_room"),
      	"numberOfChildrenPerRoom" => (int)$o->prop("child_per_room"),
      	"promotionCode" => $o->prop("promo_code")." ",
      /*<partnerWebsiteGuid>string</partnerWebsiteGuid>
      <partnerWebsiteDomain>string</partnerWebsiteDomain>
      <corporateCode>string</corporateCode>
      <iataCode>string</iataCode>*/
      	"webLanguageId" => $lang,
      	"customCurrencyCode" => $o->prop("currency"),
				"guestTitle" => $o->prop("guest_title"),
      	"guestFirstName" => $o->prop("guest_firstname"),
      	"guestLastName" => $o->prop("guest_lastname"),
      	"guestCountryCode" => $o->prop("guest_country"),
      	"guestStateOrProvince" => $o->prop("guest_state"),
      	"guestCity" => $o->prop("guest_city"),
      	"guestPostalCode" => $o->prop("guest_postal_code"),
      	"guestAddress1" => $o->prop("guest_adr_1"),
      	"guestAddress2" => $o->prop("guest_adr_2"),
      	"guestPhone" => $o->prop("guest_phone"),
      	"guestEmail" => $o->prop("guest_email"),
      	"guestComments" => urlencode($o->prop("guest_comments"))." ",
      	"roomSmokingPreferenceId" => (int)$o->prop("smoking"),
      	"floorPreferenceId" => (int)$o->prop("low_floor"),
      	"isAllergic" => (bool)$o->prop("is_allergic"),
      	"isHandicapped" => (bool)$o->prop("is_handicapped"),
				"guaranteeType" => "NonGuaranteed",
      	"paymentType" => "BankAccount"
			);
//die(dbg::dump($params));
			$return = $this->do_orb_method_call(array(
				"action" => "MakeBooking",
				"class" => "http://markus.ee/RevalServices/Booking/",
				"params" => $params,
				"method" => "soap",
				"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
			));
	
			if ($return["MakeBookingResult"]["ResultCode"] != "Success")
			{
				die("webservice error: ".dbg::dump($return));
			}
			//echo "HOIATUS!!! Broneeringud kirjutatakse live systeemi, niiet kindlasti tuleb need 2ra tyhistada!!!! <br><br><br>";
			//echo("makebooking with params: ".dbg::dump($params)." retval = ".dbg::dump($return));

			//$o->set_parent(aw_ini_get("ows.bron_folder"));
			//$o->set_class_id(CL_OWS_RESERVATION);
			$o->set_prop("is_confirmed", 1);
			$o->set_prop("payment_type", $params["paymentType"]);

			$o->set_prop("confirmation_code", $return["MakeBookingResult"]["ConfirmationCode"]);
			$o->set_prop("booking_id", $return["MakeBookingResult"]["BookingId"]);
			$o->set_prop("cancel_deadline", $owb->parse_date_int($return["MakeBookingResult"]["CancellationDeadline"]));
			$o->set_prop("total_room_charge", $return["MakeBookingResult"]["TotalRoomAndPackageCharges"]);
			$o->set_prop("total_tax_charge", $return["MakeBookingResult"]["TotalTaxAndFeeCharges"]);
			$o->set_prop("total_charge", $return["MakeBookingResult"]["TotalCharges"]);
			$o->set_prop("charge_currency", $return["MakeBookingResult"]["ChargeCurrencyCode"]);

			$o->set_meta("query", $params);
			$o->set_meta("result", $return);
			aw_disable_acl();
			$o->save();
			aw_restore_acl();
	}
}
?>
