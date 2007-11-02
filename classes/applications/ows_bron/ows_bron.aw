<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ows_bron/ows_bron.aw,v 1.10 2007/11/02 10:03:02 kristo Exp $
// ows_bron.aw - OWS Broneeringukeskus 
/*

@classinfo syslog_type=ST_OWS_BRON relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property bank_payment type=relpicker reltype=RELTYPE_BANK_PAYMENT field=meta method=serialize
@caption Pangamakse objekt

@reltype BANK_PAYMENT value=1 clid=CL_BANK_PAYMENT
@caption Pangamakse

*/

class ows_bron extends class_base
{
	function ows_bron()
	{
		$this->init(array(
			"tpldir" => "applications/ows_bron/ows_bron",
			"clid" => CL_OWS_BRON
		));

		$this->hotel_list = array(
			"27" => "Reval Hotel Ol&uuml;mpia",
			"37" => "Reval Hotel Central",
			"39" => "Reval Park Hotel & Casino",
			"38" => "Reval Inn Tallinn",
			"40" => "Reval Hotel Latvija",
			"41" => "Reval Hotel Ridzene",
			"42" => "Reval Hotel Lietuva",
			"42" => "Reval Hotel Lietuva",
			"17969" => "Reval Inn Vilnius",
			"17971" => "Reval Inn Klaipeda",
			"18941" => "Reval Hotels Elizabete"
		);

		$this->short_cur_lut = array(
			"EUR" => "&euro;",
			"GBP" => "&pound;",
			"USD" => "$"
		);

		$this->valid_card_types = array(
			"BankCard" => "Bank Card",
			"AmericanExpress" => "American Express",
			"BarclayCard" => "Barclay Card",
			"CarteBleu" => "Carte Bleu",
			"CarteBlanche" => "Carte Blanche",
			"DinersClub" => "Diners Club",
			"DiscoverCard" => "Discover Card",
			"EnRoute" => "En Route",
			"Eurocard" => "Eurocard",
			"JapanCreditBureau" => "Japan Credit Bureau",
			"MasterCard" => "Master Card",
			"Visa" => "Visa",
			"AccessCard" => "Access Card"
		);

		$this->months = $this->make_keys(array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"));

		$this->years = array();
		for ($i = date("Y"); $i < (date("Y")+10);  $i++)
		{
			$t = date("y", mktime(1,1,1,1,1,$i));
			$this->years[$i] = $t;
		}
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

	function get_web_language_id($lc)
	{
		switch($lc)
		{
			case "en":
				$lang = 2;
			break;
			case "et":
				$lang = 1;
			break;
			case "fi":
				$lang = 6;
			break;
			case "de":
				$lang = 7;
			break;
			case "it":
				$lang = 9;
			break;
			case "lv":
				$lang = 3;
			break;
			case "lt":
				$lang = 4;
			break;
			case "ru":
				$lang = 5;
			break;
			case "es":
				$lang = 10;
			break;
			case "sv":
				$lang = 8;
			break;
		}
		return $lang;
	}

	/**
	@attrib name=show_booking_details all_args=1 nologin="1"
	**/
	function show_booking_details($arr)
	{
		$this->read_template("view3.tpl");
		lc_site_load("ows_bron", $this);
		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$arrival = mktime(0,0,0, $checkindata2[1], $checkindata2[0], $checkindata2[2]);
		$checkin = $checkindata2[2].'-'.$checkindata2[1].'-'.$checkindata2[0].'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$departure = mktime(23,59,0, $checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);
		$checkout = $checkoutdata2[2].'-'.$checkoutdata2[1].'-'.$checkoutdata2[0].'T23:59:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
		$rateid= $arr["sel_room_type"];
		$nights = ceil(($departure-$arrival)/(60*60*24))-1;
		$currency = $arr["set_currency"];

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$arr["i_adults"];
		$parameters["numberOfChildrenPerRoom"] = (int)$arr["i_children"];
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = 0;
$parameters["ow_bron"] = $arr["ow_bron"];
		if($currency)
		{
			$parameters["customCurrencyCode"] = $currency;
		}
		$return = $this->do_orb_method_call(array(
			"action" => "GetRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		$rate = $return['GetRateDetailsResult'];
		
		if($rate["ResultCode"] != 'Success')
		{
			//die(dbg::dump($parameters).dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
		}
		$rate = $rate["RateDetails"];

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["webLanguageId"] = $lang;
		$return = $this->do_orb_method_call(array(
			"action" => "GetHotelDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		$hotel = $return["GetHotelDetailsResult"]["HotelDetails"];
		$adr = get_instance(CL_CRM_ADDRESS);

		$r_url = aw_url_change_var(array(
			"smoking" => null,
			"baby_cot" => null,
			"high_floor" => null,
			"low_floor" => null,
			"bath" => null,
			"is_allergic" => null,
			"is_handicapped" => null,
			"ct_firstname" => null,
			"ct_lastname" => null,
			"ct_dob" => null,
			"ct_adr1" => null,
			"ct_adr2" => null,
			"ct_postalcode" => null,
			"ct_city" => null,
			"ct_country" => null,
			"ct_phone" => null,
			"ct_email" => null
		));

		$this->vars(array(
			"room_type" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Name"]),
			"eur_url" => aw_url_change_var("set_currency", "EUR"),
			"pound_url" => aw_url_change_var("set_currency", "GBP"),
			"usd_url" => aw_url_change_var("set_currency", "USD"),
			"eur_sel" => $currency == "EUR" ? "SELECTED" : "",
			"pound_sel" => $currency == "GBP" ? "SELECTED" : "",
			"usd_sel" => $currency == "USD" ? "SELECTED" : "",
			"totalprice" => number_format($rate["TotalPriceInCustomCurrency"], 2),
			"room" => $rooms,
			"adults" => $arr["i_adults"],
			"children" => $arr["i_children"],
			"hotelname" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"]),
			"arrival" => $arr["i_checkin"],
			"departure" => $arr["i_checkout"],
			"nights" => $nights,
			"currency" => $currency,
			"reforb" => $this->mk_reforb("show_confirm_view", array(
				"i_location" => $arr["i_location"],
				"sel_room_type" => $rateid,
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adults" => $arr["i_adults"],
				"i_children" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"set_currency" => $currency,
				"r_url" => aw_url_change_var("error", null, get_ru()),
				"ow_bron" => $arr["ow_bron"],
			)),
			"prev_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"i_child1" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"set_currency" => $currency
			)),
			"country_list" => $this->picker($arr["ct_country"], $adr->get_country_list()),
			"smoking" => checked($arr["smoking"]),
			"baby_cot" => checked($arr["baby_cot"]),
			"high_floor" => checked($arr["high_floor"]),
			"low_floor" => checked($arr["low_floor"]),
			"bath" => checked($arr["bath"]),
			"is_allergic" => checked($arr["is_allergic"]),
			"is_handicapped" => checked($arr["is_handicapped"]),
			"ct_firstname" => $arr["ct_firstname"],
			"ct_lastname" => $arr["ct_lastname"],
			"ct_dob" => $arr["ct_dob"],
			"ct_adr1" => $arr["ct_adr1"],
			"ct_adr2" => $arr["ct_adr2"],
			"ct_postalcode" => $arr["ct_postalcode"],
			"ct_city" => $arr["ct_city"],
			"ct_phone" => $arr["ct_phone"],
			"ct_email" => $arr["ct_email"],
			"step2_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"i_child1" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => $arr["section"],
				"no_reforb" => 1,
				"r_url" => obj_link($arr["section"])."&ow_bron=".$arr["ow_bron"]
			)),
			"step1_url" => obj_link($arr["section"])
		));

		if ($_GET["error"] > 0)
		{
			$this->vars(array(
				"ERR_".$_GET["error"] => $this->parse("ERR_".$_GET["error"])
 			));
		}

		return $this->parse();
	}

	/**
		@attrib name=show_confirm_view all_args="1" nologin="1"
	**/
	function show_confirm_view($arr)
	{
		$this->read_template("view4.tpl");
		lc_site_load("ows_bron", $this);
		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$arrival = mktime(0,0,0, $checkindata2[1], $checkindata2[0], $checkindata2[2]);
		$checkin = $checkindata2[2].'-'.$checkindata2[1].'-'.$checkindata2[0].'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$departure = mktime(23,59,0, $checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);
		$checkout = $checkoutdata2[2].'-'.$checkoutdata2[1].'-'.$checkoutdata2[0].'T23:59:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
		$rateid= $arr["sel_room_type"];
		$nights = ceil(($departure-$arrival)/(60*60*24))-1;
		$currency = $arr["set_currency"];

		$arr["r_url"] = aw_url_change_var(array(
			"smoking" => $arr["smoking"],
			"baby_cot" => $arr["baby_cot"],
			"high_floor" => $arr["high_floor"],
			"low_floor" => $arr["low_floor"],
			"bath" => $arr["bath"],
			"is_allergic" => $arr["is_allergic"],
			"is_handicapped" => $arr["is_handicapped"],
			"ct_firstname" => $arr["ct"]["firstname"],
			"ct_lastname" => $arr["ct"]["lastname"],
			"ct_dob" => $arr["ct"]["dob"],
			"ct_adr1" => $arr["ct"]["adr1"],
			"ct_adr2" => $arr["ct"]["adr2"],
			"ct_postalcode" => $arr["ct"]["postalcode"],
			"ct_city" => $arr["ct"]["city"],
			"ct_country" => $arr["ct"]["country"],
			"ct_phone" => $arr["ct"]["phone"],
			"ct_email" => $arr["ct"]["email"]
		), false, $arr["r_url"]);

		if (empty($arr["ct"]["firstname"]))
		{
				return aw_url_change_var("error", 1, $arr["r_url"]);
		}
		if (empty($arr["ct"]["lastname"]))
		{
				return aw_url_change_var("error", 2, $arr["r_url"]);
		}
		list($dob_d, $dob_m, $dob_y) = explode("-", $arr["ct"]["dob"]);
		if (empty($arr["ct"]["dob"]) || !$dob_y || !$dob_m || !$dob_d)
		{
				return aw_url_change_var("error", 3, $arr["r_url"]);
		}
		if (empty($arr["ct"]["adr1"]))
		{
				return aw_url_change_var("error", 4, $arr["r_url"]);
		}
		if (empty($arr["ct"]["postalcode"]))
		{
				return aw_url_change_var("error", 5, $arr["r_url"]);
		}
		if (empty($arr["ct"]["city"]))
		{
				return aw_url_change_var("error", 6, $arr["r_url"]);
		}
		if (empty($arr["ct"]["country"]))
		{
				return aw_url_change_var("error", 7, $arr["r_url"]);
		}
		if (empty($arr["ct"]["phone"]))
		{
				return aw_url_change_var("error", 8, $arr["r_url"]);
		}
		if (!empty($arr["ct"]["email"]) && !is_email($arr["ct"]["email"]))
		{
				return aw_url_change_var("error", 9, $arr["r_url"]);
		}

		$parameters = array();
		$parameters["ow_bron"] = $arr["ow_bron"];
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$arr["i_adults"];
		$parameters["numberOfChildrenPerRoom"] = (int)$arr["i_children"];
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = 0;
		if($currency)
		{
			$parameters["customCurrencyCode"] = $currency;
		}

		$return = $this->do_orb_method_call(array(
			"action" => "GetRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));

		$rate = $return['GetRateDetailsResult'];
		
		if($rate["ResultCode"] != 'Success')
		{
			//die(dbg::dump($parameters).dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
		}
		$rate = $rate["RateDetails"];

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["webLanguageId"] = $lang;
		$parameters["ow_bron"] = $arr["ow_bron"];
		$return = $this->do_orb_method_call(array(
			"action" => "GetHotelDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		if (!is_array($return["GetHotelDetailsResult"]))
		{
			//die("webservice error: ".dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
		}
		$hotel = $return["GetHotelDetailsResult"]["HotelDetails"];
		$adr = get_instance(CL_CRM_ADDRESS);
		$cl = $adr->get_country_list();

		$bp = get_instance(CL_BANK_PAYMENT);

		$o = obj();
		$o->set_parent(aw_ini_get("ows.bron_folder"));
		$o->set_class_id(CL_OWS_RESERVATION);
		$o->set_name(sprintf(t("OWS Bron %s %s @ %s"), 
			$arr["ct"]["firstname"], $arr["ct"]["lastname"], date("d.m.Y H:i")
		));
		$o->set_prop("is_confirmed", 0);
		$o->set_prop("hotel_id", $arr["i_location"]);
		$o->set_prop("rate_id", $rateid);
		$o->set_prop("arrival_date", $arrival);
		$o->set_prop("departure_date", $departure);
		$o->set_prop("num_rooms", $rooms);
		$o->set_prop("adults_per_room", (int)$arr["i_adults"]);
		$o->set_prop("child_per_room", (int)$arr["i_children"]);
		$o->set_prop("promo_code", $promo);
		$o->set_prop("currency", $currency);
		$o->set_prop("guest_title", "");
		$o->set_prop("guest_firstname", $arr["ct"]["firstname"]);
		$o->set_prop("guest_lastname", $arr["ct"]["lastname"]);
		$o->set_prop("guest_country", $arr["ct"]["country"]);
		$o->set_prop("guest_state", "");
		$o->set_prop("guest_city", $arr["ct"]["city"]);
		$o->set_prop("guest_postal_code", $arr["ct"]["postalcode"]);
		$o->set_prop("guest_adr_1", $arr["ct"]["adr1"]);
		$o->set_prop("guest_adr_2", $arr["ct"]["adr2"]);
		$o->set_prop("guest_phone", $arr["ct"]["phone"]);
		$o->set_prop("guest_email", $arr["ct"]["email"]);
		$o->set_prop("guest_comments", $arr["bron_comment"]);
		$o->set_prop("smoking", $arr["smoking"]);
		$o->set_prop("high_floor", $arr["high_floor"]);
		$o->set_prop("low_floor", $arr["low_floor"]);
		$o->set_prop("is_allergic", $arr["is_allergic"]);
		$o->set_prop("is_handicapped", $arr["is_handicapped"]);
		$o->set_meta("bron_data", $arr);
		//$o->save();

		if(is_oid($arr["ow_bron"]) && $this->can("view" , $arr["ow_bron"]))
		{
			$ow_bron = obj($arr["ow_bron"]);
			$bpo = $ow_bron->prop("bank_payment");
		}
		$this->vars(array(
			"room_type" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Name"]),
			"eur_url" => aw_url_change_var("set_currency", "EUR"),
			"pound_url" => aw_url_change_var("set_currency", "GBP"),
			"usd_url" => aw_url_change_var("set_currency", "USD"),
			"eur_sel" => $currency == "EUR" ? "SELECTED" : "",
			"pound_sel" => $currency == "GBP" ? "SELECTED" : "",
			"usd_sel" => $currency == "USD" ? "SELECTED" : "",
			"totalprice" => number_format($rate["TotalPriceInCustomCurrency"], 2),
			"room" => $rooms,
			"adults" => $arr["i_adults"],
			"children" => $arr["i_children"],
			"hotelname" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"]),
			"arrival" => $arr["i_checkin"],
			"departure" => $arr["i_checkout"],
			"nights" => $nights,
			"currency" => $currency,
			"reforb" => $this->mk_reforb("show_confirm_view", array("no_reforb" => 1)),
			"prev_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"i_child1" => $arr["i_children"],
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"set_currency" => $currency
			)),
			"ct_firstname" => $arr["ct"]["firstname"],
			"ct_lastname" => $arr["ct"]["lastname"],
			"ct_adr1" => $arr["ct"]["adr1"],
			"ct_adr2" => $arr["ct"]["adr2"],
			"ct_postalcode" => $arr["ct"]["postalcode"],
			"ct_city" => $arr["ct"]["city"],
			"ct_country" => $cl[$arr["ct"]["country"]],
			"ct_phone_ext" => $arr["ct"]["phone_ext"],
			"ct_phone" => $arr["ct"]["phone"],
			"ct_email" => $arr["ct"]["email"],
			"ct_newsletter" => checked($arr["ct"]["newsletter"]),
			"ct_create_user" => checked($arr["ct"]["create_user"]),
			"smoking" => checked($arr["smoking"]),
			"baby_cot" => checked($arr["baby_cot"]),
			"high_floor" => checked($arr["high_floor"]),
			"low_floor" => checked($arr["low_floor"]),
			"bath" => checked($arr["bath"]),
			"is_allergic" => checked($arr["is_allergic"]),
			"is_handicapped" => checked($arr["is_handicapped"]),
			"confirm_card_types" => $this->picker($arr["confirm_card_type"], $this->valid_card_types),
			"confirm_card_name" => $arr["confirm_card_name"],
			"confirm_exp_mon" => $this->picker($arr["confirm_exp_mon"],$this->months),
			"confirm_exp_year" => $this->picker($arr["confirm_exp_year"],$this->years),
			"confirm_card_number" => $arr["confirm_card_number"],
			"display_confirm" => $arr["display_confirm"] ? "display" : "none",
			"act_confirm" => $arr["display_confirm"] ? "class=\"active\"" : "",
			"display_main" => $arr["display_confirm"] ? "none" : "display",
			"act_main" => $arr["display_confirm"] ? "" : "class=\"active\"",
			"accept_terms" => checked($arr["accept_terms"]),
			
			"bank_forms" => $bp->bank_forms(array(
				"id" => $bpo,
				"reference_nr" => $o->id(),
				"amount" => $rate["TotalPriceInEur"]*16.0,
				"expl" => $o->id(),
				"lang" => $lc
			)),
			"gotoccpayment" => aw_url_change_var("aw_rvs_id", $o->id(), aw_url_change_var("action", "go_to_cc_payment", aw_url_change_var("bpo" , $bpo, aw_url_change_var("reservation" ,  $o->id())))),
			"step3_url" => $this->mk_my_orb("show_booking_details", array(
				"sel_room_type" => $arr["sel_room_type"],
				"section" => $arr["section"],
				"no_reforb" => 1,
				"set_currency" => $arr["set_currency"],
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_adults" => $arr["i_adults"],
				"i_children" => $arr["i_children"],
				"i_rooms" => $arr["i_rooms"],
				"i_promo" => $arr["i_promo"],
				"ow_bron" => $arr["ow_bron"]
			)),
			"step2_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"i_child1" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => $arr["section"],
				"no_reforb" => 1,
				"r_url" => obj_link($arr["section"])."&ow_bron=".$arr["ow_bron"]
			)),
			"step1_url" => obj_link($arr["section"])
		));
		if ($_GET["error"] > 0)
		{
			$this->vars(array(
				"ERR_".$_GET["error"] => $this->parse("ERR_".$_GET["error"])
 			));
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("handle_pay_submit", array(
				"smoking" => $arr["smoking"], "baby_cot" => $arr["baby_cot"],
				"high_floor" => $arr["high_floor"],
				"low_floor" => $arr["low_floor"],
				"bath" => $arr["bath"],
				"is_allergic" => $arr["is_allergic"],
				"is_handicapped" => $arr["is_handicapped"],
				"bron_comment" => $arr["bron_comment"],
				"ct" => $arr["ct"],
				"i_location" => $arr["i_location"],
				"sel_room_type" => $arr["sel_room_type"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adults" => $arr["i_adults"],
				"i_children" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"set_currency" => $arr["set_currency"],
				"aw_rvs_id" => $o->id(),
				"r_url" => get_ru(),
				"ow_bron" => $arr["ow_bron"],
			))
		));


		return $this->parse();
	}

	/**
		@attrib name=handle_pay_submit nologin="1"
	**/
	function handle_pay_submit($arr)
	{
		$arr = $_POST;
		$d = $arr;
		unset($d["class"]);
		unset($d["action"]);
		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		if ($arr["do_guarantee"] != "")
		{
			// validate cc number? or just do the booking
			$arr["r_url"] = aw_url_change_var(array(
					"confirm_card_type" => $arr["confirm_card_type"],
					"confirm_card_name" => $arr["confirm_card_name"],
					"confirm_exp_mon" => $arr["confirm_exp_mon"],
					"confirm_exp_year" => $arr["confirm_exp_year"],
				//	"confirm_card_number" => $arr["confirm_card_number"],	// do not send this in the url
					"display_confirm" => 1,
					"accept_terms" => $arr["accept_terms"]
			), false, $arr["r_url"]);

			if (!$arr["accept_terms"])
			{
				return aw_url_change_var("error", 1, $arr["r_url"]);
			}

		  if (!$arr["confirm_card_type"] || !isset($this->valid_card_types[$arr["confirm_card_type"]]))
			{
				return aw_url_change_var("error", 2, $arr["r_url"]);
			}

      if ($arr["confirm_card_name"] == "")
			{
				return aw_url_change_var("error", 3, $arr["r_url"]);
			}

			if ($arr["confirm_exp_year"] < date("Y") || $arr["confirm_exp_year"] > date("Y") + 10 ||
					$arr["confirm_exp_mon"] < 1 || $arr["confirm_exp_mon"] > 12)
			{
				return aw_url_change_var("error", 4, $arr["r_url"]);
			}

			if (!$this->validate_cc_num($arr["confirm_card_number"]))
			{
				return aw_url_change_var("error", 5, $arr["r_url"]);
			}
			// if everything is ok, then call MakeBooking

			$_ts = mktime(1,1,1, $arr["confirm_exp_mon"], 5, $arr["confirm_exp_year"]);
			$exp_date = $arr["confirm_exp_year"]."-".$arr["confirm_exp_mon"]."-".date("t", $_ts)."T00:00:00";

			$checkindata = $arr["i_checkin"];
			$checkindata2 = explode('.', $checkindata);
			$checkin = $checkindata2[2].'-'.$checkindata2[1].'-'.$checkindata2[0].'T00:00:00';
			$checkin_ts = mktime(0,0,0,$checkindata2[1], $checkindata2[0], $checkindata2[2]);
			$checkoutdata = $arr["i_checkout"];
			$checkoutdata2 = explode('.', $checkoutdata);
			$checkout = $checkoutdata2[2].'-'.$checkoutdata2[1].'-'.$checkoutdata2[0].'T23:59:00';
			$checkout_ts = mktime(0,0,0,$checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);

			$number = trim($arr["confirm_card_number"]); 
			$number = eregi_replace("[[:space:]]+", "", $number); 
			$number = eregi_replace("-+", "", $number); 

			$params = array(
	"ow_bron" => $arr["ow_bron"],
   			"hotelId" => $arr["i_location"],
      	"rateId" => $arr["sel_room_type"],
      	"arrivalDate" => $checkin,
      	"departureDate" => $checkout,
      	"numberOfRooms" => $arr["i_rooms"],
      	"numberOfAdultsPerRoom" => $arr["i_adults"],
      	"numberOfChildrenPerRoom" => $arr["i_children"],
      	"promotionCode" => $arr["i_promo"]." ",
      /*<partnerWebsiteGuid>string</partnerWebsiteGuid>
      <partnerWebsiteDomain>string</partnerWebsiteDomain>
      <corporateCode>string</corporateCode>
      <iataCode>string</iataCode>*/
      	"webLanguageId" => $lang,
      	"customCurrencyCode" => $arr["set_currency"],
				"guestTitle" => "",
      	"guestFirstName" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["firstname"])),
      	"guestLastName" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["lastname"])),
      	"guestCountryCode" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["country"])),
      	"guestStateOrProvince" => "",
      	"guestCity" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["city"])),
      	"guestPostalCode" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["postalcode"])),
      	"guestAddress1" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["adr1"])),
      	"guestAddress2" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["adr2"])),
      	"guestPhone" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["phone"])),
      	"guestEmail" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["email"])),
      	"roomSmokingPreferenceId" => (int)$arr["smoking"],
      	"floorPreferenceId" => (int)$arr["low_floor"],
      	"isAllergic" => (bool)$arr["is_allergic"],
      	"isHandicapped" => (bool)$arr["is_handicapped"],
      	"guestComments" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["bron_comment"])),
      	"guaranteeType" => "CreditCard",
      	"guaranteeCreditCardType" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["confirm_card_type"])),
      	"guaranteeCreditCardHolderName" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["confirm_card_name"])),
				"guaranteeCreditCardNumber" => $number,
      	"guaranteeCreditCardExpirationDate" => $exp_date,
      	"paymentType" => "NoPayment"
			);

			$return = $this->do_orb_method_call(array(
				"action" => "MakeBooking",
				"class" => "http://markus.ee/RevalServices/Booking/",
				"params" => $params,
				"method" => "soap",
				"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
			));

			if ($return["MakeBookingResult"]["ResultCode"] == "OwsError" && $return["MakeBookingResult"]["ResultMessage"] == "INVALID CREDIT CARD")
			{
				return aw_url_change_var("error", 5, $arr["r_url"]);
			}
	
			if ($return["MakeBookingResult"]["ResultCode"] != "Success")
			{
				//die("webservice error: ".dbg::dump($return));
				$this->proc_ws_error($params, $return);
			}
			//echo "HOIATUS!!! Broneeringud kirjutatakse live systeemi, niiet kindlasti tuleb need 2ra tyhistada!!!! <br><br><br>";
			//echo("makebooking with params: ".dbg::dump($params)." retval = ".dbg::dump($return));

			if (is_oid($arr["aw_rvs_id"]))
			{
				$o = obj($arr["aw_rvs_id"]);
			}
			else
			{
				$o = obj();
			}
			$o->set_parent(aw_ini_get("ows.bron_folder"));
			$o->set_class_id(CL_OWS_RESERVATION);
			$o->set_name(sprintf(t("OWS Bron %s %s @ %s"), 
				$params["guestFirstName"], $params["guestLastName"], date("d.m.Y H:i")
			));
			$o->set_prop("is_confirmed", 1);
			$o->set_prop("hotel_id", $params["hotelId"]);
			$o->set_prop("rate_id", $params["rateId"]);
			$o->set_prop("arrival_date", $this->parse_date_int($params["arrivalDate"]));
			$o->set_prop("departure_date", $this->parse_date_int($params["departureDate"]));
			$o->set_prop("num_rooms", $params["numberOfRooms"]);
			$o->set_prop("adults_per_room", $params["numberOfAdultsPerRoom"]);
			$o->set_prop("child_per_room", $params["numberOfChildrenPerRoom"]);
			$o->set_prop("promo_code", $params["promotionCode"]);
			$o->set_prop("currency", $params["customCurrencyCode"]);
			$o->set_prop("guest_title", $params["guestTitle"]);
			$o->set_prop("guest_firstname", $params["guestFirstName"]);
			$o->set_prop("guest_lastname", $params["guestLastName"]);
			$o->set_prop("guest_country", $params["guestCountryCode"]);
			$o->set_prop("guest_state", $params["guestStateOrProvince"]);
			$o->set_prop("guest_city", $params["guestCity"]);
			$o->set_prop("guest_postal_code", $params["guestPostalCode"]);
			$o->set_prop("guest_adr_1", $params["guestAddress1"]);
			$o->set_prop("guest_adr_2", $params["guestAddress2"]);
			$o->set_prop("guest_phone", $params["guestPhone"]);
			$o->set_prop("guest_email", $params["guestEmail"]);
			$o->set_prop("guest_comments", $params["guestComments"]);
			$o->set_prop("guarantee_type", $params["guaranteeType"]);
			$o->set_prop("guarantee_cc_type", $params["guaranteeCreditCardType"]);
			$o->set_prop("guarantee_cc_holder_name", $params["guaranteeCreditCardHolderName"]);
			$o->set_prop("guarantee_cc_num", "************".substr($params["guaranteeCreditCardNumber"], -4));
			$o->set_prop("guarantee_cc_exp_date", $this->parse_date_int($params["guaranteeCreditCardExpirationDate"]));
			$o->set_prop("payment_type", $params["paymentType"]);

			$o->set_prop("confirmation_code", $return["MakeBookingResult"]["ConfirmationCode"]);
			$o->set_prop("booking_id", $return["MakeBookingResult"]["BookingId"]);
			$o->set_prop("cancel_deadline", $this->parse_date_int($return["MakeBookingResult"]["CancellationDeadline"]));
			$o->set_prop("total_room_charge", $return["MakeBookingResult"]["TotalRoomAndPackageCharges"]);
			$o->set_prop("total_tax_charge", $return["MakeBookingResult"]["TotalTaxAndFeeCharges"]);
			$o->set_prop("total_charge", $return["MakeBookingResult"]["TotalCharges"]);
			$o->set_prop("charge_currency", $return["MakeBookingResult"]["ChargeCurrencyCode"]);

			$o->set_meta("query", $params);
			$o->set_meta("result", $return);
			aw_disable_acl();
			$o->save();
			aw_restore_acl();

			$this->send_mail_from_bron($o);

			return $this->mk_my_orb("display_final_page", array("rvs_id" => $o->id(), "section" => $d["section"]));
		}

		return $this->mk_my_orb("display_final_page", $d);
	}

	function send_mail_from_bron($o)
	{
		$html = $this->display_final_page(array("rvs_id" => $o->id()));

		$awm = get_instance("protocols/mail/aw_mail");
		$awm->create_message(array(
			"froma" => "info@revalhotels.com",
			"fromn" => "Reval Hotels",
			"subject" => "Your Revalhotels reservation",
			"to" => $o->prop("guest_email"),
			"body" => strip_tags($html),
		));
		$awm->htmlbodyattach(array(
			"data" => $html,
		));
		$awm->gen_mail();
	}

	function validate_cc_num($number) 
	{ 
		$number = trim($number); 
		$number = eregi_replace("[[:space:]]+", "", $number); 
		$number = eregi_replace("-+", "", $number); 
	
		# Pass 1
		$j = 0;
		for ($i = strlen($number) - 1; $i + 1; $i--) 
		{
			if ((string)(($number[$i] * 2)/2)!= "$number[$i]")
			{
				$num2 = "1";
				break;
			}
			$num2 .= is_int($j++/2)? $number[$i] : $number[$i] * 2;
		}
	
		# Pass 2
		$i = 0;
		while ($i < strlen($num2)) 
		{
			$total += $num2[$i++];
		}
	
		# Evaluate
		if ($total % 10) 
		{
			return false;
		}
		return true;
	} 

	/**
		@attrib name=display_final_page all_args=1 nologin="1"
	**/
	function display_final_page($arr)
	{
		$this->read_template("final_confirm.tpl");
		lc_site_load("ows_bron", $this);
		$o = obj($arr["rvs_id"]);

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");

		$parameters = array();
		$parameters["hotelId"] = $o->prop("hotel_id");
		$parameters["rateId"] = $o->prop("rate_id");
		$parameters["arrivalDate"] = date("Y-m-d", $o->prop("arrival_date"))."T".date("H:i:s", $o->prop("arrival_date"));
		$parameters["departureDate"] = date("Y-m-d", $o->prop("departure_date"))."T".date("H:i:s",$o->prop("departure_date"));
		$parameters["numberOfRooms"] = $o->prop("num_rooms");
		$parameters["numberOfAdultsPerRoom"] = (int)$o->prop("adults_per_room");
		$parameters["numberOfChildrenPerRoom"] = (int)$o->prop("child_per_room");
		$parameters["promotionCode"] = $o->prop("promo_code");
		$parameters["webLanguageId"] = $this->get_web_language_id($lc);
		$parameters["customerId"] = 0;
		$parameters["ow_bron"] = $arr["ow_bron"];
		if($currency)
		{
			$parameters["customCurrencyCode"] = $o->prop("currency");
		}

		$return = $this->do_orb_method_call(array(
			"action" => "GetRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));

		$rate = $return['GetRateDetailsResult'];
		if($rate["ResultCode"] != 'Success')
		{
			//die(dbg::dump($parameters).dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
		}
		$rate = $rate["RateDetails"];

		$parameters = array();
		$parameters["hotelId"] = $o->prop("hotel_id");
		$parameters["webLanguageId"] = $this->get_web_language_id($lc);
		$return = $this->do_orb_method_call(array(
			"action" => "GetHotelDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		if (!is_array($return["GetHotelDetailsResult"]))
		{
			//die("webservice error: ".dbg::dump($return));
		}
		$hotel = $return["GetHotelDetailsResult"]["HotelDetails"];

		$this->vars($o->properties());
		$this->vars(array(
			"confirmation_number" => $o->prop("confirmation_code"),
			"checkin" => date("d.m.Y", $o->prop("arrival_date")),
			"checkout" => date("d.m.Y", $o->prop("departure_date")),
			"nights" => (ceil(($o->prop("departure_date")-$o->prop("arrival_date"))/(60*60*24))-1),
			"num_rooms" => $o->prop("num_rooms"),
			"num_adults" => $o->prop("adults_per_room"),
			"room_type" => iconv("utf-8", aw_global_get("charset"), $rate["ShortNote"]),
			"room_details" => iconv("utf-8", aw_global_get("charset"), $rate["LongNote"]),
			"hotel_name" => iconv("utf-8", aw_global_get("charset"), $hotel["HotelName"]),
			"hotel_contact" => iconv("utf-8", aw_global_get("charset"), $hotel["AddressLine1"]." ".$hotel["AddressLine1"]." ".$hotel["Phone"]." ".$hotel["Fax"]." ".$hotel["Email"]),
			"tot_price" => $o->prop("total_charge"),
			"currency" => $o->prop("currency"),
			"guarantee_cc_exp_date" => date("m/Y", $o->prop("guarantee_cc_exp_date")),
			"new_booking_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $o->prop("hotel_id"),
				"i_checkin" => date("d.m.Y", $o->prop("arrival_date")),
				"i_checkout" => date("d.m.Y", $o->prop("departure_date")),
				"i_rooms" => $o->prop("num_rooms"),
				"i_adult1" => $o->prop("adults_per_room"),
				"i_child1" => $o->prop("child_per_room"),
				"i_promo" => $o->prop("promo_code"),
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"ow_bron" => $_GET["ow_bron"],
				"r_url" => obj_link(aw_global_get("section"))
			)),
			"guest_email" => urldecode($o->prop("guest_email"))
		));

		return $this->parse();
	}

	/**
	@attrib name=show_available_rooms all_args=1 nologin="1"
	**/
	function show_available_rooms($arr)
	{
		$this->read_template("view2.tpl");
		lc_site_load("ows_bron", $this);
		$arr["r_url"] = aw_url_change_var("error", null, $arr["r_url"]);

		$currency = $arr["set_currency"] ? $arr["set_currency"] : "EUR";

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$checkin = $checkindata2[2].'-'.$checkindata2[1].'-'.$checkindata2[0].'T00:00:00';
		$checkin_ts = mktime(0,0,0,$checkindata2[1], $checkindata2[0], $checkindata2[2]);
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$checkout = $checkoutdata2[2].'-'.$checkoutdata2[1].'-'.$checkoutdata2[0].'T23:59:00';
		$checkout_ts = mktime(0,0,0,$checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);

		if ($checkout_ts <= $checkin_ts)
		{
			$arr["r_url"] = aw_url_change_var("error", 2, $arr["r_url"]);
			return $arr["r_url"];
		}

		$location = $arr["i_location"];
		if (!isset($this->hotel_list[$location]))
		{
			$arr["r_url"] = aw_url_change_var("error", 3, $arr["r_url"]);
			return $arr["r_url"];
		}

		$rooms = (int)$arr["i_rooms"];
		if ($rooms < 1 || $rooms > 4)
		{
			$arr["r_url"] = aw_url_change_var("error", 4, $arr["r_url"]);
			return $arr["r_url"];
		}

		$rc_error = 0;
		$arr["r_url"] = aw_url_change_var("rooms", $rooms, $arr["r_url"]);

		for($i=1;$i<=$rooms;$i++)
		{
			$childcount[$i] = (int)$arr["i_child".$i];
			$adultcount[$i] = (int)$arr["i_adult".$i];
			$arr["r_url"] = aw_url_change_var("adults".$i, $arr["i_adult".$i], $arr["r_url"]);
			$arr["r_url"] = aw_url_change_var("children".$i, $arr["i_child".$i], $arr["r_url"]);
			if($childcount[$i] + $adultcount[$i] > 4)
			{
				$rc_error = 1;
				$arr["r_url"] = aw_url_change_var("error", 1, $arr["r_url"]);
			}
		}

		if($rc_error)
		{
			return $arr["r_url"];
		}
		$promo = $arr["i_promo"];

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["webLanguageId"] = $lang;
		$parameters["ow_bron"] = $arr["ow_bron"];
enter_function("ws:GetHotelDetails");
		$return = $this->do_orb_method_call(array(
			"action" => "GetHotelDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
exit_function("ws:GetHotelDetails");
		$hotel = $return["GetHotelDetailsResult"];
		if($hotel["ResultCode"] != 'Success')
		{
			//die("webservice error ".dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
			return $arr["r_url"];
		}

		$hotel = $hotel["HotelDetails"];
		$amenities = array("IsBusinessCenter","IsConferenceRoom","IsGym","IsInternetAccess","IsParking","IsPets","IsRestaurant","IsRoomService","IsSwimmingPool","IsWheelchair");
		foreach($amenities as $amenity)
		{
			if($hotel[$amenity] == "true")
			{
				$this->vars(array(
					$amenity => $this->parse($amenity)
				));
			}
		}
//echo dbg::dump($hotel);
		$hp = iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["PictureUrl"]);
		$hp = str_replace(".gif", "_170.gif", $hp);
		$this->vars(array(
			"HotelName" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"]),
			"HotelDesc" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["ShortNote"])),
			"HotelAddress" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["AddressLine1"].', '.$hotel["AddressLine2"]),
			"HotelPhone" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["Phone"]),
			"HotelMap" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["MapUrl"]),
			"HotelUrl" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["InfoUrl"]),
			"HotelPic" => $hp,
			"step1_url" => obj_link($arr["section"])
		));
		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = $adultcount[1];
		$parameters["numberOfChildrenPerRoom"] = $childcount[1];
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = 0;
		$parameters["ow_bron"] = $arr["ow_bron"];
		if($currency)
		{
			$parameters["customCurrencyCode"] = $currency;
		}
//echo dbg::dump($parameters);
enter_function("ws:GetAvailableRates");
		$return = $this->do_orb_method_call(array(
			"action" => "GetAvailableRates",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
exit_function("ws:GetAvailableRates");

/*echo dbg::dump($parameters);
echo dbg::dump($return);
echo date("d.m.Y H:i:s");*/

		$rates = $return["GetAvailableRatesResult"];
		if(!$rates["RateList"])
		{
			//die("webservice error ".dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
			return $arr["request"]["r_url"];
		}
		$rates = $rates["RateList"]["RateInfo"];
		$tmp = '';
		$i=0;
		$this->vars(array(
			"short_currency" => $this->short_cur_lut[$currency]
		));

		$fetch_ows_codes = array();
		foreach($rates as $rate)
		{
			if ($rate["IsVisible"] == "false" || $rate["IsAvailableForBooking"] == "false")
			{
				continue;
			}
			$fetch_ows_codes[] = $hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"];		
		}

		$room_desc_list = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"user4" => $fetch_ows_codes
		));
		$code2doc = array();
		foreach($room_desc_list->arr() as $doc)
		{
			$code2doc[$doc->prop("user4")] = $doc;
		}

		$rt2rates = array();
		foreach($rates as $rate)
		{
			if ($rate["IsVisible"] == "false" || $rate["IsAvailableForBooking"] == "false")
			{
				continue;
			}
			$rt2rates[$hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"]][] = $rate;
		}

		foreach($rt2rates as $rt => $ratedata)
		{
			$doc = $code2doc[$rt];
			if (!$doc)
			{
				continue;
			}

			$c1 = $c2 = null;
			if (is_oid($doc->id()))
			{
				$conns = $doc->connections_from(array("to.class_id" => CL_IMAGE));
				reset($conns);
				list(,$c1) = each($conns);
				list(,$c2) = each($conns);
			}

			$i1b_url = $i1s_url = $i2b_url = $i2s_url = "";
			if ($c1)
			{
				$i1_inst = get_instance(CL_IMAGE);
				$i1_data = $i1_inst->get_image_by_id($c1->prop("to"));
				$i1b_url = $i1_data["big_url"];
				$i1s_url = $i1_data["url"];
			}
			if ($c2)
			{
				$i2_inst = get_instance(CL_IMAGE);
				$i2_data = $i2_inst->get_image_by_id($c2->prop("to"));
				$i2b_url = $i2_data["big_url"];
				$i2s_url = $i2_data["url"];
			}

			$lead = preg_replace("/#pict(\d+?)(v|k|p|)#/i","",$doc->trans_get_val("lead"));
			$lead = preg_replace("/#p(\d+?)(v|k|p|)#/i","",$lead);
//echo "room = ".$rate["Title"]." doc = ".dbg::dump($doc)." <br>";

			list($name, $title) = explode(",", $doc->trans_get_val("title"), 2);

			$rate = reset($ratedata);
			$this->vars(array(
				"big_img_1_url" => $i1b_url,
				"big_img_2_url" => $i2b_url,
				"small_img_1_url" => $i1s_url,
				"small_img_2_url" => $i2s_url,
				"short_note" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["ShortNote"])),
				"Id" => $rate["RateId"],
				"Name" => $name.", ", //$rate["Title"],
				"Title" => $title, //$rate["Title"],
				"Note" => $lead,//$rate["LongNote"],
				"Pic" => $rate["PictureUrl"],
				"Slideshow" => $rate["SlideshowUrl"],
				"price1_avg" => number_format($rate["AverageDailyRateInCustomCurrency"], 2),
				"price1_total" => number_format($rate["TotalPriceInCustomCurrency"], 2),
				"roominfo_url" => $this->mk_my_orb("get_roominfo", array(
					"location" => $location,
					"rateid" => $rate["RateId"],
					"checkin" => $checkin,
					"checkin_date" => $arr["i_checkin"],
					"checkout" => $checkout,
					"rooms" => $rooms,
					"i_adult" => $adultcount[1],
					"i_child" => $childcount[1],
					"promo" => $promo,
					"lang" => $lang,
					"currency" => $currency,
					"ow_bron" => $arr["ow_bron"],
				), "ows_bron", false, true),
				"roomdesc_url" => $this->mk_my_orb("get_room_desc", array(
					"location" => $location,
					"rateid" => $rate["RateId"],
					"checkin" => $checkin,
					"checkin_date" => $arr["i_checkin"],
					"checkout" => $checkout,
					"rooms" => $rooms,
					"i_adult" => $adultcount[1],
					"i_child" => $childcount[1],
					"promo" => $promo,
					"lang" => $lang,
					"currency" => $currency,
					"ow_bron" => $arr["ow_bron"],
				), "ows_bron", false, true),
				"num_offers" => count($ratedata)
			));
//echo "<hr>enter sandman = ".dbg::dump($rate)." <br>";
			$f = true;
			$inl = "";
			foreach($ratedata as $rate)
			{
				if ($f)
				{
					$f = false;
					continue;
				}
//echo "also, skipper: ".dbg::dump($rate)."<br>";
				$this->vars(array(
					"big_img_1_url" => $i1b_url,
					"big_img_2_url" => $i2b_url,
					"small_img_1_url" => $i1s_url,
					"small_img_2_url" => $i2s_url,
					"short_note" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["ShortNote"])),
					"Id" => $rate["RateId"],
					"Name" => $name.", ", //$rate["Title"],
					"Title" => $title, //$rate["Title"],
					"Note" => $lead,//$rate["LongNote"],
					"Pic" => $rate["PictureUrl"],
					"Slideshow" => $rate["SlideshowUrl"],
					"price1_avg" => number_format($rate["AverageDailyRateInCustomCurrency"], 2),
					"price1_total" => number_format($rate["TotalPriceInCustomCurrency"], 2),
					"roominfo_url" => $this->mk_my_orb("get_roominfo", array(
						"location" => $location,
						"rateid" => $rate["RateId"],
						"checkin" => $checkin,
						"checkin_date" => $arr["i_checkin"],
						"checkout" => $checkout,
						"rooms" => $rooms,
						"i_adult" => $adultcount[1],
						"i_child" => $childcount[1],
						"promo" => $promo,
						"lang" => $lang,
						"currency" => $currency,
						"ow_bron" => $arr["ow_bron"],
					), "ows_bron", false, true),
					"roomdesc_url" => $this->mk_my_orb("get_room_desc", array(
						"location" => $location,
						"rateid" => $rate["RateId"],
						"checkin" => $checkin,
						"checkin_date" => $arr["i_checkin"],
						"checkout" => $checkout,
						"rooms" => $rooms,
						"i_adult" => $adultcount[1],
						"i_child" => $childcount[1],
						"promo" => $promo,
						"lang" => $lang,
						"currency" => $currency,
						"ow_bron" => $arr["ow_bron"],
					), "ows_bron", false, true)
				));
				$inl .= $this->parse("type_in_rate");
			}
			$rate = reset($ratedata);
			$this->vars(array(
				"big_img_1_url" => $i1b_url,
				"big_img_2_url" => $i2b_url,
				"small_img_1_url" => $i1s_url,
				"small_img_2_url" => $i2s_url,
				"short_note" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["ShortNote"])),
				"Id" => $rate["RateId"],
				"Name" => $name.", ", //$rate["Title"],
				"Title" => $title, //$rate["Title"],
				"Note" => $lead,//$rate["LongNote"],
				"Pic" => $rate["PictureUrl"],
				"Slideshow" => $rate["SlideshowUrl"],
				"price1_avg" => number_format($rate["AverageDailyRateInCustomCurrency"], 2),
				"price1_total" => number_format($rate["TotalPriceInCustomCurrency"], 2),
				"roominfo_url" => $this->mk_my_orb("get_roominfo", array(
					"location" => $location,
					"rateid" => $rate["RateId"],
					"checkin" => $checkin,
					"checkin_date" => $arr["i_checkin"],
					"checkout" => $checkout,
					"rooms" => $rooms,
					"i_adult" => $adultcount[1],
					"i_child" => $childcount[1],
					"promo" => $promo,
					"lang" => $lang,
					"currency" => $currency,
					"ow_bron" => $arr["ow_bron"],
				), "ows_bron", false, true),
				"roomdesc_url" => $this->mk_my_orb("get_room_desc", array(
					"location" => $location,
					"rateid" => $rate["RateId"],
					"checkin" => $checkin,
					"checkin_date" => $arr["i_checkin"],
					"checkout" => $checkout,
					"rooms" => $rooms,
					"i_adult" => $adultcount[1],
					"i_child" => $childcount[1],
					"promo" => $promo,
					"lang" => $lang,
					"currency" => $currency,
					"ow_bron" => $arr["ow_bron"],
				), "ows_bron", false, true),
				"num_offers" => count($ratedata)
			));
			$this->vars(array(
				"type_in_rate" => $inl
			));
			$tmp .= $this->parse("RateList");
		}

		$this->vars(array(
			"i_rooms_".$arr["i_rooms"] => "SELECTED",
			"i_adult1_".$arr["i_adult1"] => "SELECTED",
			"i_child1_".$arr["i_child1"] => "SELECTED",
			"i_checkin" => htmlspecialchars($arr["i_checkin"]),
			"i_checkout" => htmlspecialchars($arr["i_checkout"]),
			"sel_hotel_".$arr["i_location"] => "SELECTED",
			"eur_url" => aw_url_change_var("set_currency", "EUR"),
			"pound_url" => aw_url_change_var("set_currency", "GBP"),
			"usd_url" => aw_url_change_var("set_currency", "USD"),
			"eur_sel" => $currency == "EUR" ? "SELECTED" : "",
			"pound_sel" => $currency == "GBP" ? "SELECTED" : "",
			"usd_sel" => $currency == "USD" ? "SELECTED" : "",
			"RateList" => $tmp,
			"currentdate" => date('d.m.Y'),
			"tomorrow" => date("d.m.Y", time() + 24*3600),
			"reforb1" => $this->mk_reforb(
				"show_available_rooms",
				array(
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"r_url" => $arr["r_url"],
					"ow_bron" => $arr["ow_bron"],
				)
			),
			"reforb2" => $this->mk_reforb(
				"show_booking_details",
				array(
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"set_currency" => $currency,
					"i_location" => $arr["i_location"],
					"i_checkin" => $arr["i_checkin"],
					"i_checkout" => $arr["i_checkout"],
					"i_adults" => $adultcount[1],
					"i_children" => $childcount[1],
					"i_rooms" => $arr["i_rooms"],
					"i_promo" => $arr["i_promo"],
					"ow_bron" => $arr["ow_bron"],
				)
			)
		));
		return $this->parse();
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);

		$this->read_template("bron_box.tpl");
		lc_site_load("ows_bron", $this);
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		$error1 = ' class="error"';
		$error2 = '<p class="error">Maximum number of persons per room is 4. Please review</p>';
		$rooms = $this->picker($_GET['rooms']?$_GET['rooms']:1, array(1=>1,2=>2,3=>3,4=>4));
		for($i=1;$i<=4;$i++)
		{
			${'adults'.$i} = $this->picker($_GET['adults'.$i]?$_GET['adults'.$i]:1, array(1=>1,2=>2,3=>3,4=>4));
			${'children'.$i} = $this->picker($_GET['children'.$i]?$_GET['children'.$i]:0, array(0=>0,1=>1,2=>2,3=>3,4=>4));
			$this->vars(array(
				"adults".$i => ${"adults".$i},
				"children".$i => ${"children".$i},
			));
			if($_GET["error".$i])
			{
				$this->vars(array(
					"error".$i."1" => $error1,
					"error".$i."2" => $error2,
				));
			}
		}
		$this->vars(array(
			"currentdate" => date('d.m.Y'),
			"tomorrow" => date("d.m.Y", time() + 24*3600),
			"rooms" => $rooms,
			"reforb" => $this->mk_reforb(
				"show_available_rooms",
				array(
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"r_url" => get_ru(),
					"ow_bron" => $arr["id"],
				)
			)
		));
		if ($_GET["error"] > 0)
		{
			$this->vars(array(
				"ERR_".$_GET["error"] => $this->parse("ERR_".$_GET["error"])
 			));
		}
		return $this->parse();
	}

	/**
		@attrib name=get_roominfo all_args="1" nologin="1"
	**/
	function get_roominfo($arr)
	{
		$this->read_template("roominfo.tpl");
		lc_site_load("ows_bron", $this);
		extract($arr);
		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = (int)$rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$i_adult;
		$parameters["numberOfChildrenPerRoom"] = (int)$i_child;
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = 0;
		if($currency)
		{
			$parameters["customCurrencyCode"] = $currency;
		}

		$return = $this->do_orb_method_call(array(
			"action" => "GetRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));

		$rate = $return['GetRateDetailsResult']["RateDetails"];
		$this->vars(array(
			"room_rate" => number_format(nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["AverageDailyRate"])), 2),
			"total_rate" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["TotalPrice"])),
			"currency" => $currency,
			"description" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["LongNote"])),
			"cancel_by" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["CancellationDeadline"])),
			"start" => $checkin_date,
		));

		die($this->parse());
	}

/**
		@attrib name=get_room_desc all_args="1" nologin="1"
	**/
	function get_room_desc($arr)
	{
		$this->read_template("room_desc.tpl");
		lc_site_load("ows_bron", $this);
		extract($arr);
		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = (int)$rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$i_adult;
		$parameters["numberOfChildrenPerRoom"] = (int)$i_child;
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = 0;
		if($currency)
		{
			$parameters["customCurrencyCode"] = $currency;
		}

		$return = $this->do_orb_method_call(array(
			"action" => "GetRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));

		$rate = $return['GetRateDetailsResult']["RateDetails"];

		$room_desc_list = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"user4" => $rate["OwsRoomTypeCode"]
		));
		$doc = $room_desc_list->begin();
		if (!$doc)
		{
			$doc = obj();

		}
		$this->vars(array(
			"room_rate" => number_format($rate["AverageDailyRate"], 2),
			"total_rate" => $rate["Total_price"],
			"currency" => $currency,
			"short_currency" => $this->short_cur_lut[$currency],
			"description" => $doc->trans_get_val("content"),//$rate["LongNote"],
			"cancel_by" => $rate["CancellationDeadline"],
			"start" => $checkin_date,
			"room_name" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Title"])),
			"doc_title" => $doc->trans_get_val("title"),
			"long_note" => $doc->trans_get_val("content")//$rate["LongNote"],
		));

		die($this->parse());
	}

	/**
		@attrib name=go_to_cc_payment all_args="1" nologin="1"
	**/
	function go_to_cc_payment($arr)
	{
		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$arrival = mktime(0,0,0, $checkindata2[1], $checkindata2[0], $checkindata2[2]);
		$checkin = $checkindata2[2].'-'.$checkindata2[1].'-'.$checkindata2[0].'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$departure = mktime(23,59,0, $checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);
		$checkout = $checkoutdata2[2].'-'.$checkoutdata2[1].'-'.$checkoutdata2[0].'T23:59:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
		$rateid= $arr["sel_room_type"];
		$nights = ceil(($departure-$arrival)/(60*60*24));
		$currency = $arr["set_currency"];

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$arr["i_adults"];
		$parameters["numberOfChildrenPerRoom"] = (int)$arr["i_children"];
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = 0;
		if($currency)
		{
			$parameters["customCurrencyCode"] = $currency;
		}

		$return = $this->do_orb_method_call(array(
			"action" => "GetRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));

		$rate = $return['GetRateDetailsResult'];
		
		if(false && $rate["ResultCode"] != 'Success')
		{
			die(dbg::dump($parameters).dbg::dump($return));
		}
		$rate = $rate["RateDetails"];

			$bp = get_instance(CL_BANK_PAYMENT);
			die($bp->do_payment(array(
				"payment_id" => $arr["bpo"],
				"bank_id" => "credit_card",
				"amount" => $rate["TotalPriceInEur"]*16.0,
				"reference_nr" => $arr["reservation"],
				"expl" => $arr["reservation"],
			)));
	}

	/**
		@attrib name=cancel_booking all_args="1"
	**/
	function cancel_booking($arr)
	{
		$return = $this->do_orb_method_call(array(
			"action" => "CancelBooking",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => array(
				"confirmationCode" => $arr["confirmation_number"],
				"cancellationReasonCode" => 1,
				"cancellationReasonText" => "web booking system test booking"
			),
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
die(dbg::dump($return));
	}

	function parse_date_int($ds)
	{
		list($date_part, $time_part) = explode("T", $ds);
		list($y, $m, $d) = explode("-", $date_part);
		list($h, $min, $sec) = explode(":", $time_part);

		return mktime($h, $min, $sec, $m, $d, $y);
	}

	function proc_ws_error($parameters, $return)
	{
		//mail("vead@struktu);
		error::raise(array(
			"id" => "ERR_OWS",
			"msg" => "rv = ".dbg::dump($return)." params = ".dbg::dump($parameters)
		));
		die("ws error ".dbg::dump($return));
	}
}
?>
