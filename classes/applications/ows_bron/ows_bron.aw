<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ows_bron/ows_bron.aw,v 1.41 2008/07/16 13:20:15 markop Exp $
// ows_bron.aw - OWS Broneeringukeskus 
/*

@classinfo syslog_type=ST_OWS_BRON relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo

@default table=objects
@default group=general

@property bank_payment type=relpicker reltype=RELTYPE_BANK_PAYMENT field=meta method=serialize
@caption Pangamakse objekt

@property confirmed_rvs_folder type=relpicker reltype=RELTYPE_CONFIRMED_FOLDER field=meta method=serialize
@caption Kinnitatud tellimuste kataloog

@property template type=textbox field=meta method=serialize
@caption Template (Vaikimis bron_box.tpl)

@default group=mail_settings_confirm,mail_settings_cancel

	@property mail_templates type=table store=no no_caption=1

@default group=mail_bcc

	@property mail_bcc type=table store=no no_caption=1

@default group=bank_settings

	@property bank_settings_table type=table store=no no_caption=1

@default group=mail_bank_bcc

	@property mail_bank_bcc type=table store=no no_caption=1

@groupinfo mail_settings caption="Meiliseaded"
	@groupinfo mail_settings_confirm caption="Kinnitusmeil" parent=mail_settings
	@groupinfo mail_settings_cancel caption="T&uuml;histusmeil" parent=mail_settings
	@groupinfo mail_bcc caption="BCC" parent=mail_settings
	@groupinfo mail_bank_bcc caption="Pangalingi BCC" parent=mail_settings

@groupinfo bank_settings caption="Panga seaded"
	
@reltype BANK_PAYMENT value=1 clid=CL_BANK_PAYMENT
@caption Pangamakse

@reltype CONFIRMED_FOLDER value=2 clid=CL_MENU
@caption Kinnitatud tellimuste kaust
*/

class ows_bron extends class_base
{
	function ows_bron()
	{
		$this->init(array(
			"tpldir" => "applications/ows_bron/ows_bron",
			"clid" => CL_OWS_BRON
		));

		$this->country_lut = array();

		aw_ini_set("menuedit.protect_emails", 0);


		$this->country_lut["en"] = array(
			"EE" => "Estonia",
			"LT" => "Lithuania",
			"LV" => "Latvia",
			"LT" => "Lithuania"
		);
		$this->country_lut["et"] = array(
			"EE" => "Eesti",
			"LT" => "Leedu",
			"LV" => "L&auml;ti",
			"LT" => "Lithuania"
		);
		$this->country_lut["fi"] = $this->country_lut["de"] = $this->country_lut["it"] = $this->country_lut["lv"] = $this->country_lut["lt"] = $this->country_lut["ru"] = $this->country_lut["es"] = $this->country_lut["sv"] = array(
			"EE" => "Estonia",
			"LT" => "Lithuania",
			"LV" => "Latvia",
			"LT" => "Lithuania"
		);

		$this->city_lut = array();

		$this->city_lut["en"] = array(
			"Tallinn" => "Tallinn",
			"Riga" => "Riga",
			"Vilnius" => "Vilnius",
			"Klaipeda" => "Klaipeda",
			"Kaunas" => "Kaunas",
			"KAUN" => "Kaunas"
		);
		$this->city_lut["et"] = array(
			"Tallinn" => "Tallinn",
			"Riga" => "Riia",
			"Vilnius" => "Vilnius",
			"Klaipeda" => "Klaipeda",
			"Kaunas" => "Kaunas",
			"KAUN" => "Kaunas"
		);
		$this->city_lut["fi"] = $this->city_lut["de"] = $this->city_lut["it"] = $this->city_lut["lv"] = $this->city_lut["lt"] = $this->city_lut["ru"] = $this->city_lut["es"] = $this->city_lut["sv"] = array(
			"Tallinn" => "Tallinn",
			"Riga" => "Riga",
			"Vilnius" => "Vilnius",
			"Klaipeda" => "Klaipeda",
			"Kaunas" => "Kaunas",
			"KAUN" => "Kaunas"
		);


		$this->hotel_list = array(
			"27" => "Reval Hotel Ol&uuml;mpia",
			"37" => "Reval Hotel Central",
			"39" => "Reval Park Hotel & Casino",
			"38" => "Reval Inn Tallinn",
			"40" => "Reval Hotel Latvija",
			"41" => "Reval Hotel Ridzene",
			"42" => "Reval Hotel Lietuva",
			"17969" => "Reval Inn Vilnius",
			"17971" => "Reval Inn Klaipeda",
			"18941" => "Reval Hotel Elizabete",
			"17380" => "Reval Hotel Neris"
		);

		$this->short_cur_lut = array(
			"EUR" => "&euro;",
			"GBP" => "&pound;",
			"USD" => "$",
			"EEK" => "EEK",
			"LVL" => "LVL",
			"LIT" => "LIT"
		);

		$this->currency_picker = array(
			"EUR" => "EUR",
			"EEK" => "EEK",
			"LVL" => "LVL",
			"LIT" => "LIT"
		);

		$this->valid_card_types = array(
/*			"BankCard" => "Bank Card",
			"BarclayCard" => "Barclay Card",
			"CarteBleu" => "Carte Bleu",
			"CarteBlanche" => "Carte Blanche",
			"DiscoverCard" => "Discover Card",
			"EnRoute" => "En Route",
			"Eurocard" => "Eurocard",
			"JapanCreditBureau" => "Japan Credit Bureau",
			"AccessCard" => "Access Card"*/


			"DinersClub" => "Diners Club",
			"AmericanExpress" => "American Express",
			"MasterCard" => "Master Card",
			"Visa" => "Visa"
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
		$arr["partnerWebsiteGuid"] = $arr["partnerWebsiteGuid"];
		$arr["partnEerWebsiteDomain"] = $arr["partnEerWebsiteDomain"];
	}

	/**
		@attrib name=show_cancel_page nologin="1"
	**/
	function show_cancel_page($arr)
	{
		$this->read_template("cancel_page.tpl");
		lc_site_load("ows_bron", $this);
		$this->vars(array(
			"section" => aw_global_get("section"),
			"last_name" => $arr["last_name"],
			"confirmation_number" => $arr["confirmation_number"],
			"change_dates_checked" => checked($arr["reason"] == "change_dates"),
			"plans_changed_checked" => checked($arr["reason"] == "plans_changed"),
			"wrong_price_checked" => checked($arr["reason"] == "wrong_price"),
			"other_checked" => checked($arr["reason"] == "other"),
			"reason_comment" => htmlspecialchars($arr["reason_comment"])
		));
		if ($_GET["err"] > 0)
		{
			$this->vars(array(
				"ERR_".$_GET["err"] => $this->parse("ERR_".$_GET["err"])
 			));
		}
		return $this->parse();
	}
	
	function get_web_language_id($lc)
	{
		return 2;
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
		$arrival = mktime(23,59,0, $checkindata2[1], $checkindata2[0], $checkindata2[2]);
		$checkin = sprintf("%04d", $checkindata2[2]).'-'.sprintf("%02d", $checkindata2[1]).'-'.sprintf("%02d", $checkindata2[0]).'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$departure = mktime(23,59,0, $checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);
		$checkout = sprintf("%04d", $checkoutdata2[2]).'-'.sprintf("%02d", $checkoutdata2[1]).'-'.sprintf("%02d", $checkoutdata2[0]).'T23:59:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
		$rateid= $arr["sel_room_type"];
		$nights = ceil(($departure-$arrival)/(60*60*24));
		$currency = $arr["set_currency"];

		if (!$rateid)
		{
				$ru =  $this->mk_my_orb("show_available_rooms", array(
					"i_location" => $arr["i_location"],
					"i_checkin" => $arr["i_checkin"],
					"i_checkout" => $arr["i_checkout"],
					"i_rooms" => $arr["i_rooms"],
					"i_adult1" => $arr["i_adults"],
					"i_child1" => $arr["i_children"],
					"i_promo" => $arr["i_promo"],
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"set_currency" => $currency,
					"r_url" => $arr["r_url"]
				));
				return aw_url_change_var("error", 1, $ru);
		}

		if ($arrival < time())
		{
				$ru =  $this->mk_my_orb("show_available_rooms", array(
					"i_location" => $arr["i_location"],
					"i_checkin" => $arr["i_checkin"],
					"i_checkout" => $arr["i_checkout"],
					"i_rooms" => $arr["i_rooms"],
					"i_adult1" => $arr["i_adults"],
					"i_child1" => $arr["i_children"],
					"i_promo" => $arr["i_promo"],
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"set_currency" => $currency,
					"r_url" => $arr["r_url"]
				));
				return aw_url_change_var("error", 2, $ru);
		}

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$arr["i_adults"];
		$parameters["numberOfChildrenPerRoom"] = (int)$arr["i_children"];
		$parameters["promotionCode"] = iconv(aw_global_get("charset"), "utf-8", $promo);
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = reval_customer::get_cust_id();
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
			$ru =  $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"i_child1" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"set_currency" => $currency,
				"r_url" => $arr["r_url"]
			));
			return aw_url_change_var("error", 1, $ru);

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
			"ct_phone_ext" => null,
			"ct_email" => null
		));
//$_SESSION["reval_fc"]["data"] = reval_customer::do_call("GetCustomerProfile", array("customerId" => 24419, "webLanguageId" => 1), "Customers");
		if ($_SESSION["reval_fc"]["data"])
		{
				$rc = get_instance(CL_REVAL_CUSTOMER);
				$cust_data = $rc->do_call("GetCustomerProfile", array(
					"customerId" => $rc->get_cust_id(),
					"webLanguageId" => 0//$rc->_get_web_language_id()
				), "Customers");
			if (empty($arr["is_allergic"]))
			{
				$arr["is_allergic"] = $cust_data["IsAllergic"] == "true";
			}
			if (empty($arr["is_handicapped"]))
			{
				$arr["is_handicapped"] = $cust_data["IsHandicapped"] == "true";
			}
			if (empty($arr["ct_firstname"]))
			{
				$arr["ct_firstname"] = iconv("utf-8", aw_global_get("charset"), $cust_data["FirstName"]);
			}
			if (empty($arr["ct_lastname"]))
			{
				$arr["ct_lastname"] = iconv("utf-8", aw_global_get("charset"), $cust_data["LastName"]);
			}
			if (empty($arr["ct_dob"]))
			{
				$arr["ct_dob"] = date("d.m.Y", reval_customer::_parse_date($cust_data["Birthday"]));
			}
			if (empty($arr["ct_adr1"]))
			{
				$arr["ct_adr1"] = iconv("utf-8", aw_global_get("charset"), $cust_data["AddressLine1"]);
			}
			if (empty($arr["ct_adr2"]))
			{
				$arr["ct_adr2"] = iconv("utf-8", aw_global_get("charset"), $cust_data["AddressLine2"]);
			}
			if (empty($arr["ct_postalcode"]))
			{
				$arr["ct_postalcode"] = iconv("utf-8", aw_global_get("charset"), $cust_data["PostalCode"]);
			}
			if (empty($arr["ct_city"]))
			{
				$arr["ct_city"] = iconv("utf-8", aw_global_get("charset"), $cust_data["CityName"]);
			}
			if (empty($arr["ct_phone"]))
			{
				$arr["ct_phone"] = iconv("utf-8", aw_global_get("charset"), $cust_data["MobilePhone"]);
				if (empty($arr["ct_phone"]))
				{
					$arr["ct_phone"] = iconv("utf-8", aw_global_get("charset"), $cust_data["HomePhone"]);
				}
			}
			if (empty($arr["ct_email"]))
			{
				$arr["ct_email"] = iconv("utf-8", aw_global_get("charset"), $cust_data["Email"]);
			}
			if (empty($arr["ct_country"]))
			{
				$arr["ct_country"] = trim($cust_data["CountryCode"]);

				if ($arr["ct_country"] != "")
				{
					// get telephone code for couintry and remove it from in front of the phone number
					$ca = get_instance(CL_CRM_ADDRESS);
					$phe = $ca->get_phone_ext_list();
					$ext = $phe[$arr["ct_country"]];
					if (substr($arr["ct_phone"], 0, strlen($ext)) == $ext)
					{
						$arr["ct_phone"] = trim(substr($arr["ct_phone"], strlen($ext)));
					}
				}
			}

		}
		aw_ini_set("menuedit.protect_emails", 0);

		if (!$arr["ct_country"])
		{
			$arr["ct_country"] = $this->detect_country();
		}

		$code =  $hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"];
		$ol = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"user4" => $code
		));
		$doc = $ol->begin();
		if (!$doc)
		{
			$doc = obj();
		}
		$this->vars(array(
			"cur_select" => $this->picker($currency, $this->currency_picker),
			"room_type" => $doc->name(),  //iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Name"]),
			"rate_title" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Title"]),
			"eur_url" => aw_url_change_var("set_currency", "EUR"),
			"pound_url" => aw_url_change_var("set_currency", "GBP"),
			"usd_url" => aw_url_change_var("set_currency", "USD"),
			"eek_url" => aw_url_change_var("set_currency", "EEK"),
			"eur_sel" => $currency == "EUR" ? "SELECTED" : "",
			"pound_sel" => $currency == "GBP" ? "SELECTED" : "",
			"usd_sel" => $currency == "USD" ? "SELECTED" : "",
			"eek_sel" => $currency == "EEK" ? "SELECTED" : "",
			"totalprice" => number_format($rate["TotalPriceInCustomCurrency"], 2),
			"room" => $rooms,
			"adults" => $arr["i_adults"],
			"children" => $arr["i_children"],
			"hotelname" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"]),
			"arrival" => $arr["i_checkin"],
			"departure" => $arr["i_checkout"],
			"promo" => $arr["i_promo"],
			"nights" => max(1,$nights),
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
				//"set_currency" => $currency,
				"api_departure_days" => $arr["api_departure_days"],
				"r_url" => aw_url_change_var("error", null, get_ru()),
				"ow_bron" => $arr["ow_bron"],
				"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
				"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
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
				"api_departure_days" => $arr["api_departure_days"],
				"no_reforb" => 1,
				"set_currency" => $currency,
				"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
				"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
			)),
			"phone_ext_list_as_js_array" => $adr->get_phone_ext_list_as_js_array(),
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
			"ct_phone_ext" => $arr["ct_phone_ext"],
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
				"api_departure_days" => $arr["api_departure_days"],
				"no_reforb" => 1,
				"r_url" => obj_link($arr["section"])."&ow_bron=".$arr["ow_bron"],
				"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
				"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
			)),
			"step1_url" => obj_link(aw_ini_get("frontpage"))
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
		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$arrival = mktime(23,59,0, $checkindata2[1], $checkindata2[0], $checkindata2[2]);
		$checkin = sprintf("%04d", $checkindata2[2]).'-'.sprintf("%02d", $checkindata2[1]).'-'.sprintf("%02d", $checkindata2[0]).'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$departure = mktime(23,59,0, $checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);
		$checkout = sprintf("%04d", $checkoutdata2[2]).'-'.sprintf("%02d", $checkoutdata2[1]).'-'.sprintf("%02d", $checkoutdata2[0]).'T23:59:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
		$rateid= $arr["sel_room_type"];
		$nights = ceil(($departure-$arrival)/(60*60*24));
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
			"ct_phone_ext" => $arr["ct"]["phone_ext"],
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
		if (!$dob_y)
		{
			list($dob_d, $dob_m, $dob_y) = explode("/", $arr["ct"]["dob"]);
			if (!$dob_y)
			{
				list($dob_d, $dob_m, $dob_y) = explode(".", $arr["ct"]["dob"]);
			}
		}
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
		$nct = preg_replace("/[+ 0-9]*/", "", $arr["ct"]["phone"]);
		if (empty($arr["ct"]["phone"]) || !empty($nct))
		{
				return aw_url_change_var("error", 8, $arr["r_url"]);
		}
		if (!empty($arr["ct"]["email"]) && !is_email($arr["ct"]["email"]))
		{
				return aw_url_change_var("error", 9, $arr["r_url"]);
		}

		$currency = "EUR";
		$parameters = array();
		$parameters["ow_bron"] = $arr["ow_bron"];
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$arr["i_adults"];
		$parameters["numberOfChildrenPerRoom"] = (int)$arr["i_children"];
		$parameters["promotionCode"] = iconv(aw_global_get("charset"), "utf-8", $promo);
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = reval_customer::get_cust_id();
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

		if($rate["ResultCode"] != 'Success' && $rate["ResultMessage"] == "ROOM RESTRICTED")
		{
				return aw_url_change_var("error", 0, $arr["r_url"]);
		}

		if($rate["ResultCode"] != 'Success')
		{
			//die(dbg::dump($parameters).dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
		}
		$rate = $rate["RateDetails"];
		if (false /*aw_global_get("uid") == "struktuur" || aw_global_get("uid") == "erik" || aw_global_get("uid") == "martorav"*/)
		{
			$this->read_template("view4_bank.tpl");
		}
		else
		if ($rate["IsMandatoryDeposit"] != "false")
		{
			$this->read_template("view4_only_cc.tpl");
		}
		else
		{
			$this->read_template("view4.tpl");
		}
		lc_site_load("ows_bron", $this);

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

		
		//genereerib uue bookingu id et saaks seda kasutada makse logimisel
		$parameters = array(
			"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
			"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
		);
		$return = $this->do_orb_method_call(array(
			"action" => "GenerateNewBookingID",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServicesTest/BookingService.asmx"
		));

		$o = obj();
		$o->set_parent(aw_ini_get("ows.bron_folder"));
		$o->set_class_id(CL_OWS_RESERVATION);
		$o->set_name(sprintf(t("OWS Bron %s %s @ %s"), 
			$arr["ct"]["firstname"], $arr["ct"]["lastname"], date("d.m.Y H:i")
		));
		$o->set_prop("ows_bron", $arr["ow_bron"]);
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
		$o->set_prop("guest_phone", $arr["ct"]["phone_ext"]." ".$arr["ct"]["phone"]);
		$o->set_prop("guest_email", $arr["ct"]["email"]);
		$o->set_prop("guest_comments", $arr["bron_comment"]);
		$o->set_prop("guest_bd", mktime(1,1,1,$dob_m, $dob_d, $dob_y));
		$o->set_prop("smoking", $arr["smoking"]);
		$o->set_prop("high_floor", $arr["high_floor"]);
		$o->set_prop("low_floor", $arr["low_floor"]);
		$o->set_prop("is_allergic", $arr["is_allergic"]);
		$o->set_prop("is_handicapped", $arr["is_handicapped"]);
		$o->set_prop("rate_title", $rate["Title"]);
		$o->set_prop("rate_long_note", $rate["LongNote"]);
		$o->set_prop("rate_room_type_code", $rate["OwsRoomTypeCode"]);
		$o->set_meta("customer_id", reval_customer::get_cust_id());
		$o->set_meta("booking_id", $return["GenerateNewBookingIDResult"]);
		$o->set_meta("partnerWebsiteGuid", $arr["partnerWebsiteGuid"]);
		$o->set_meta("partnEerWebsiteDomain", $arr["partnEerWebsiteDomain"]);
		$o->set_meta("bron_data", $arr);
		$o->set_meta("join_fc", $arr["join_fc"]);
		aw_disable_acl();
		$o->save();
		aw_restore_acl();

		if (!$arr["ow_bron"])
		{
			$arr["ow_bron"] = 107222;
		}

		if(is_oid($arr["ow_bron"]) && $this->can("view" , $arr["ow_bron"]))
		{
			$ow_bron = obj($arr["ow_bron"]);
			$bpo = $ow_bron->prop("bank_payment");
			$bs = $ow_bron->meta("bank_settings");
			if ($this->can("view", $bs[$arr["i_location"]]))
			{
				$bpo = $bs[$arr["i_location"]];
			}
		//echo "<!-- bpo = $bpo , loc = ".$arr["i_location"]." bs = ".dbg::dump($bs)." --> ";
		}

		$code =  $hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"];
		$ol = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"user4" => $code
		));
		$doc = $ol->begin();
		if (!$doc)
		{
			$doc = obj();
		}

		$this->vars(array(
			"guest_comments" => nl2br($arr["bron_comment"]),
			"room_type" => $doc->name(), //iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Name"]),
			"rate_title" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Title"]),
			"eur_url" => aw_url_change_var("set_currency", "EUR"),
			"pound_url" => aw_url_change_var("set_currency", "GBP"),
			"usd_url" => aw_url_change_var("set_currency", "USD"),
			"eek_url" => aw_url_change_var("set_currency", "EEK"),
			"eur_sel" => $currency == "EUR" ? "SELECTED" : "",
			"pound_sel" => $currency == "GBP" ? "SELECTED" : "",
			"usd_sel" => $currency == "USD" ? "SELECTED" : "",
			"eek_sel" => $currency == "EEK" ? "SELECTED" : "",
			"totalprice" => number_format($rate["TotalPriceInCustomCurrency"], 2),
			"room" => $rooms,
			"adults" => $arr["i_adults"],
			"children" => $arr["i_children"],
			"hotelname" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"]),
			"arrival" => $arr["i_checkin"],
			"departure" => $arr["i_checkout"],
			"nights" => max(1,$nights),
			"currency" => $currency,
			"reforb" => $this->mk_reforb("show_confirm_view", array("no_reforb" => 1)),
			"prev_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"api_departure_days" => $arr["api_departure_days"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"i_child1" => $arr["i_children"],
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"set_currency" => $currency,
				"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
				"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
			)),
			"ct_firstname" => $arr["ct"]["firstname"],
			"ct_lastname" => $arr["ct"]["lastname"],
			"ct_adr1" => $arr["ct"]["adr1"],
			"ct_adr2" => $arr["ct"]["adr2"],
			"ct_postalcode" => $arr["ct"]["postalcode"],
			"ct_city" => $arr["ct"]["city"],
			"ct_dob" => $arr["ct"]["dob"],
			"ct_country" => $cl[$arr["ct"]["country"]],
			"ct_phone_ext" => $arr["ct"]["phone_ext"],
			"ct_phone" => $arr["ct"]["phone_ext"]." ".$arr["ct"]["phone"],
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
				"expl" => "webID:".$o->id()." ".$arr["i_checkin"]."-".$arr["i_checkout"]." ".iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"])." ".$arr["ct"]["firstname"]." ".$arr["ct"]["lastname"],
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
				"api_departure_days" => $arr["api_departure_days"],
				"i_children" => $arr["i_children"],
				"i_rooms" => $arr["i_rooms"],
				"i_promo" => $arr["i_promo"],
				"ow_bron" => $arr["ow_bron"],
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
				"ct_phone_ext" => $arr["ct"]["phone_ext"],
				"ct_email" => $arr["ct"]["email"],
				"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
				"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
			)),
			"step2_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"api_departure_days" => $arr["api_departure_days"],
				"i_child1" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => $arr["section"],
				"no_reforb" => 1,
				"r_url" => obj_link($arr["section"])."&ow_bron=".$arr["ow_bron"],
				"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
				"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
			)),
			"step1_url" => obj_link(aw_ini_get("frontpage"))
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
				"set_currency" => "EUR", //$arr["set_currency"],
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

			$number = trim($arr["confirm_card_number"]); 
			$number = eregi_replace("[[:space:]]+", "", $number); 
			$number = eregi_replace("-+", "", $number); 
			if (!$this->validate_cc_num($number))
			{
				//return aw_url_change_var("error", 5, $arr["r_url"]);
			}
			// if everything is ok, then call MakeBooking

			$_ts = mktime(1,1,1, $arr["confirm_exp_mon"], 5, $arr["confirm_exp_year"]);
			$exp_date = sprintf("%04d", $arr["confirm_exp_year"])."-".sprintf("%02d", $arr["confirm_exp_mon"])."-".date("t", $_ts)."T00:00:00";

			$checkindata = $arr["i_checkin"];
			$checkindata2 = explode('.', $checkindata);
			$checkin = sprintf("%04d", $checkindata2[2]).'-'.sprintf("%02d", $checkindata2[1]).'-'.sprintf("%02d", $checkindata2[0]).'T23:59:00';
			$checkin_ts = mktime(23,59,0,$checkindata2[1], $checkindata2[0], $checkindata2[2]);
			$checkoutdata = $arr["i_checkout"];
			$checkoutdata2 = explode('.', $checkoutdata);
			$checkout = sprintf("%04d", $checkoutdata2[2]).'-'.sprintf("%02d", $checkoutdata2[1]).'-'.sprintf("%02d", $checkoutdata2[0]).'T23:59:00';
			$checkout_ts = mktime(23,59,0,$checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);

			if (is_oid($arr["aw_rvs_id"]))
			{
				$o = obj($arr["aw_rvs_id"]);
			}
			else
			{
				$o = obj();
			}

			$bd = date("Y-m-d", $o->prop("guest_bd"))."T00:00:00";

			$params = array(
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
      	"guestPhone" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["phone_ext"])." ".urldecode($arr["ct"]["phone"])),
      	"guestEmail" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["ct"]["email"])),
	"guestBirthday" => $bd,
      	"roomSmokingPreferenceId" => (int)$arr["smoking"] ? 3 : 2,
      	"floorPreferenceId" => $arr["high_floor"] ? 2 : ($arr["low_floor"] ? 3 : 1),
      	"isAllergic" => (bool)$arr["is_allergic"],
      	"isHandicapped" => (bool)$arr["is_handicapped"],
      	"guestComments" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["bron_comment"])),
      	"guaranteeType" => "CreditCard",
      	"guaranteeCreditCardType" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["confirm_card_type"])),
      	"guaranteeCreditCardHolderName" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["confirm_card_name"])),
	"guaranteeCreditCardNumber" => $number,
      	"guaranteeCreditCardExpirationDate" => $exp_date,
	"guaranteeReferenceInfo" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["bron_comment"])),
      	"paymentType" => "NoPayment",
	"partnerWebsiteGuid" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["partnerWebsiteGuid"])),
	"partnEerWebsiteDomain" => iconv(aw_global_get("charset"), "UTF-8", urldecode($arr["partnEerWebsiteDomain"])),
			);
			$params["customerId"] = reval_customer::get_cust_id();
			$return = $this->do_orb_method_call(array(
				"action" => "MakeBookingExWithBirthday",
				"class" => "http://markus.ee/RevalServices/Booking/",
				"params" => $params,
				"method" => "soap",
				"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
			));

			if ($return["MakeBookingExWithBirthdayResult"]["ResultCode"] == "OwsError" && ($return["MakeBookingExWithBirthdayResult"]["ResultMessage"] == "INVALID CREDIT CARD" || $return["MakeBookingExWithBirthdayResult"]["ResultMessage"] == "CREDIT CARD IS REQUIRED" ||
			$return["MakeBookingExWithBirthdayResult"]["ResultMessage"] == "INVALID CREDIT CARD EXPIRATION"))
			{
				return aw_url_change_var("error", 5, $arr["r_url"]);
			}
	
			if ($return["MakeBookingExWithBirthdayResult"]["ResultCode"] != "Success")
			{
				//die("webservice error: ".dbg::dump($return));
				$this->proc_ws_error($params, $return);
			}
			//echo "HOIATUS!!! Broneeringud kirjutatakse live systeemi, niiet kindlasti tuleb need 2ra tyhistada!!!! <br><br><br>";
			//echo("makebooking with params: ".dbg::dump($params)." retval = ".dbg::dump($return));

			if ($this->can("view", $o->prop("ows_bron.confirmed_rvs_folder")))
			{
				$o->set_parent($o->prop("ows_bron.confirmed_rvs_folder"));
			}
			else
			{
				$o->set_parent(aw_ini_get("ows.bron_folder"));
			}
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
			$o->set_prop("guest_title", "");
			$o->set_prop("guest_firstname", urldecode($arr["ct"]["firstname"]));
			$o->set_prop("guest_lastname", urldecode($arr["ct"]["lastname"]));
			$o->set_prop("guest_country", urldecode($arr["ct"]["country"]));
			$o->set_prop("guest_state", "");
			$o->set_prop("guest_city", urldecode($arr["ct"]["city"]));
			$o->set_prop("guest_postal_code", urldecode($arr["ct"]["postalcode"]));
			$o->set_prop("guest_adr_1", urldecode($arr["ct"]["adr1"]));
			$o->set_prop("guest_adr_2", urldecode($arr["ct"]["adr2"]));
			$o->set_prop("guest_phone", $arr["ct"]["phone_ext"]." ".urldecode($arr["ct"]["phone"]));
			$o->set_prop("guest_email", urldecode($arr["ct"]["email"]));
			$o->set_prop("guest_comments", urldecode($arr["bron_comment"]));
			$o->set_prop("guarantee_type", $params["guaranteeType"]);
			$o->set_prop("guarantee_cc_type", $params["guaranteeCreditCardType"]);
			$o->set_prop("guarantee_cc_holder_name", urldecode($arr["confirm_card_name"]));
			$o->set_prop("guarantee_cc_num", "************".substr($params["guaranteeCreditCardNumber"], -4));
			$o->set_prop("guarantee_cc_exp_date", $this->parse_date_int($params["guaranteeCreditCardExpirationDate"]));
			$o->set_prop("payment_type", $params["paymentType"]);

			$o->set_prop("confirmation_code", $return["MakeBookingExWithBirthdayResult"]["ConfirmationCode"]);
			$o->set_prop("booking_id", $return["MakeBookingExWithBirthdayResult"]["BookingId"]);
			$o->set_prop("cancel_deadline", $this->parse_date_int($return["MakeBookingExWithBirthdayResult"]["CancellationDeadline"]));
			$o->set_prop("total_room_charge", $return["MakeBookingExWithBirthdayResult"]["TotalRoomAndPackageCharges"]);
			$o->set_prop("total_tax_charge", $return["MakeBookingExWithBirthdayResult"]["TotalTaxAndFeeCharges"]);
			$o->set_prop("total_charge", $return["MakeBookingExWithBirthdayResult"]["TotalCharges"]);
			$o->set_prop("charge_currency", $return["MakeBookingExWithBirthdayResult"]["ChargeCurrencyCode"]);

			$o->set_meta("query", $params);
			$o->set_meta("result", $return);
			aw_disable_acl();
			$o->save();
			aw_restore_acl();

			$this->send_mail_from_bron($o);
			return $this->mk_my_orb("display_final_page", array("ows_rvs_id" => $o->prop("confirmation_code"), "section" => $d["section"]));
		}

		return $this->mk_my_orb("display_final_page", $d);
	}

	function send_mail_from_bron($o, $do_bcc = false)
	{
		$this->is_mail = 1;
		$html = $this->display_final_page(array("ows_rvs_id" => $o->prop("confirmation_code")));
	
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

		$owb = $o->prop("ows_bron");
		if (!$this->can("view", $owb))
		{
			$owb = 107222;
		}
		if ($owb && $do_bcc)
		{
			$bron = obj($owb);
			if ($o->prop("payment_type") == "CreditCard")
			{
				$h_bcc = $bron->meta("hotel_bcc");
				$h_bcc_t = $bron->meta("hotel_bcc_titles");
			}
			else
			{
				$h_bcc = $bron->meta("hotel_bank_bcc");
				$h_bcc_t = $bron->meta("hotel_bank_bcc_titles");
			}
			if (!empty($h_bcc[$o->prop("hotel_id")]))
			{
				$subj = "Revalhotels reservation";
				if (!empty($h_bcc_t[$o->prop("hotel_id")]))
				{
					$subj = $h_bcc_t[$o->prop("hotel_id")];
				}
				$awm = get_instance("protocols/mail/aw_mail");
				$awm->create_message(array(
					"froma" => "info@revalhotels.com",
					"fromn" => "Reval Hotels",
					"subject" => $subj,
					"to" => $h_bcc[$o->prop("hotel_id")],
					"body" => strip_tags($html),
				));
				$awm->htmlbodyattach(array(
					"data" => $html,
				));
				$awm->gen_mail();
			}
		}

		if ($o->prop("hotel_id") == 38)
		{
			$awm = get_instance("protocols/mail/aw_mail");
			$awm->create_message(array(
				"froma" => "info@revalhotels.com",
				"fromn" => "Reval Hotels",
				"subject" => "Revalhotels reservation",
				"to" => "express.onlbook@revalhotels.com",
				"body" => strip_tags($html),
			));
			$awm->htmlbodyattach(array(
				"data" => $html,
			));
			$awm->gen_mail();
		}

		if ($o->meta("join_fc"))
		{
			$this->read_any_template("go_to_join_mail_content.tpl");
			lc_site_load("ows_bron", $this);
			$content = $this->parse();

			$subj = $this->parse("SUBJECT");
			$from = $this->parse("FROM");

			send_mail($o->prop("guest_email"), $subj, $content, "From: $from");
/*			$awm = get_instance("protocols/mail/aw_mail");
			$awm->create_message(array(
				"froma" => $from,
				"subject" => $subj,
				"to" => $o->prop("guest_email"),
				"body" => $content
			));
			$awm->gen_mail();*/
		}
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

	private function final_page_from_ows($arr)
	{
		$this->read_site_template("final_confirm.tpl");
		lc_site_load("ows_bron", $this);

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");

		$rvs_data = $this->do_orb_method_call(array(
			"action" => "GetBookingDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => array(
				"webLanguageId" => $this->get_web_language_id($lc),
				"confirmationCode" => $arr["ows_rvs_id"]
			),
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServicesTest/BookingService.asmx"
		));

		$rvs_data = $rvs_data["GetBookingDetailsResult"]["Booking"];
		if (!$rvs_data["ConfirmationCode"])
		{
			return t("No such booking found!");
		}

		$parameters = array();
		$parameters["hotelId"] = $rvs_data["HotelId"];
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

		$code =  $hotel["OwsHotelCode"]."-".$rvs_data["OwsRoomTypeCode"];
		$ol = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"user4" => $code
		));
		$doc = $ol->begin();
		if (!$doc)
		{
			$doc = obj();
		}

		$this->vars(array(
			"doc_room_type" => $doc->name(),
			"guest_phone" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestPhone"]),
			"confirmation_number" => htmlentities($arr["ows_rvs_id"]),
			"cancel_url" => str_replace("/orb.aw?", "/?", $this->mk_my_orb("show_cancel_page", array(
					"confirmation_number" => $arr["ows_rvs_id"], 
					"last_name" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestLastName"])
				))),
			"checkin" => date("d.m.Y", $this->parse_date_int($rvs_data["ArrivalDate"])),
			"checkout" => date("d.m.Y", $this->parse_date_int($rvs_data["DepartureDate"])),
			"nights" => $rvs_data["LengthOfStay"],
			"num_rooms" => $rvs_data["NumberOfRooms"],
			"num_adults" => $rvs_data["NumberOfAdultsPerRoom"],
			"room_type" => iconv("utf-8", aw_global_get("charset"), $rvs_data["RateTitle"]),
			"room_details" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["RateLongNote"])),
			"hotel_name" => iconv("utf-8", aw_global_get("charset"), $hotel["HotelName"]),
			"hotel_contact" => iconv("utf-8", aw_global_get("charset"), $hotel["AddressLine1"]." ".$hotel["AddressLine1"]." ".$hotel["Phone"]." ".$hotel["Fax"]." ".$hotel["Email"]),
			"tot_price" => $rvs_data["TotalCharges"],
			"currency" => $rvs_data["ChargeCurrencyCode"],
			"guarantee_cc_exp_date" => date("m/Y", $this->parse_date_int($rvs_data["GuaranteeCreditCardExpires"])),
			"new_booking_url" => $this->mk_my_orb("show_available_rooms", array(
				"i_location" => $rvs_data["HotelId"],
				"i_checkin" => date("d.m.Y", $this->parse_date_int($rvs_data["ArrivalDate"])),
				"i_checkout" => date("d.m.Y", $this->parse_date_int($rvs_data["DepartureDate"])),
				"i_rooms" => $rvs_data["NumberOfRooms"],
				"i_adult1" => $rvs_data["NumberOfAdultsPerRoom"],
				"i_child1" => $rvs_data["NumberOfChildrenPerRoom"],
				"section" => aw_global_get("section"),
				"no_reforb" => 1,
				"ow_bron" => $_GET["ow_bron"],
				"r_url" => obj_link(aw_global_get("section"))
			)),
			"guest_email" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestEmail"]),
			"guest_firstname" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestFirstName"]),
			"guest_lastname" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestLastName"]),
			"guest_adr_1" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestAddressLine1"]),
			"guest_adr_2" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestAddressLine2"]),
			"guest_postal_code" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestPostalCode"]),
			"guest_city" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestCity"]),
			"guest_country" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestCountryCode"]),
			"guest_phone" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuestPhone"]),
			"guarantee_cc_type" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuaranteeCreditCardType"]),
			"guarantee_cc_num" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $rvs_data["GuaranteeCreditCardNumber"]),
		));

		// if cc data exists, then let the user see it.
		if ($this->parse_date_int($rvs_data["GuaranteeCreditCardExpires"]) > 100)
		{
			$this->vars(array("HAS_CC_DATA" => $this->parse("HAS_CC_DATA")));
		}
		// if payment was by cc, then write that
		if ($rvs_data["PaymentMethod"] == "CreditCard")
		{
			$this->vars(array("PAID_BY_CC" => $this->parse("PAID_BY_CC")));
		}

		return $this->parse();
	}

	/**
		@attrib name=ask_confirm nologin="1"
		@param id required
	**/
	function ask_confirm($arr)
	{
		$this->read_template("ask_confirm.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("display_final_page", array("no_reforb" => 1, "rvs_id" => $arr["id"], "section" => $arr["section"]))
		));
		return $this->parse();
	}

	/**
		@attrib name=display_final_page all_args=1 nologin="1"
	**/
	function display_final_page($arr)
	{
		if (!$arr["ows_rvs_id"] && $arr["rvs_id"] && !$this->is_mail)
		{
			return $this->mk_my_orb("ask_confirm", array("id" => $arr["rvs_id"], "section" => aw_global_get("section")));
		}

		if ($arr["ows_rvs_id"])
		{
			// find the aw bron oid
			$ol = new object_list(array(
				"class_id" => CL_OWS_RESERVATION,
				"lang_id" => array(),
				"site_id" => array(),
				"confirmation_code" => $arr["ows_rvs_id"]
			));
			if ($ol->count())
			{
				$o = $ol->begin();
				$arr["rvs_id"] = $o->id();
			}
			else
			{
			return $this->final_page_from_ows($arr);
				return t("No such reservation found!");
			}

		}

		$o = obj($arr["rvs_id"]);
		if (!$this->is_mail)
		{
			$this->read_site_template("final_confirm.tpl");
		}
		else
		{
			$tpl = "mail_content.tpl";
			if ($this->can("view", $o->prop("ows_bron")))
			{
				$ob = obj($o->prop("ows_bron"));
				$h = $ob->meta("mail_settings_confirm");
				if ($h[$o->prop("hotel_id")] != "")
				{
					$tpl = $h[$o->prop("hotel_id")];
				}
			}
			$this->read_site_template($tpl);
		}
		lc_site_load("ows_bron", $this);

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");

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

		$code =  $hotel["OwsHotelCode"]."-".$o->prop("rate_room_type_code");
		$ol = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"user4" => $code
		));
		$doc = $ol->begin();
		if (!$doc)
		{
			$doc = obj();
		}

		$cancel_url = $this->mk_my_orb("show_cancel_page", array("confirmation_number" => $o->prop("confirmation_code")));
		$cancel_url = str_replace("/orb.aw?", "/?", str_replace("automatweb/", "", $cancel_url));
	
		$nb_url = $this->mk_my_orb("show_available_rooms", array(
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
		));
		$nb_url = str_replace("automatweb/", "", $nb_url);

		$this->vars(array(
			"doc_room_type" => $doc->name(),
			"guest_phone" => urldecode($o->prop("guest_phone")),
			"confirmation_number" => $o->prop("confirmation_code"),
			"cancel_url" => $cancel_url, //$this->mk_my_orb("cancel_booking", array("confirmation_number" => $o->prop("confirmation_code"))),
			"checkin" => date("d.m.Y", $o->prop("arrival_date")),
			"checkout" => date("d.m.Y", $o->prop("departure_date")),
			"nights" => max(1,(ceil(($o->prop("departure_date")-$o->prop("arrival_date"))/(60*60*24)))),
			"num_rooms" => $o->prop("num_rooms"),
			"num_adults" => $o->prop("adults_per_room"),
			"room_type" => iconv("utf-8", aw_global_get("charset"), $o->prop("rate_title")),
			"room_details" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $o->prop("rate_long_note"))),
			"hotel_name" => iconv("utf-8", aw_global_get("charset"), $hotel["HotelName"]),
			"hotel_contact" => iconv("utf-8", aw_global_get("charset"), $hotel["AddressLine1"]." ".$hotel["AddressLine1"]." ".$hotel["Phone"]." ".$hotel["Fax"]." ".$hotel["Email"]),
			"tot_price" => $o->prop("total_charge"),
			"currency" => $o->prop("currency"),
			"guarantee_cc_exp_date" => date("m/Y", $o->prop("guarantee_cc_exp_date")),
			"new_booking_url" => $nb_url,
			"guest_email" => urldecode($o->prop("guest_email"))
		));

		// if cc data exists, then let the user see it.
		if ($o->prop("guarantee_cc_exp_date") > 100)
		{ 
			$this->vars(array("HAS_CC_DATA" => $this->parse("HAS_CC_DATA")));
		}
		// if payment was by cc, then write that
		if ($o->prop("payment_type") == "CreditCard")
		{
			$this->vars(array("PAID_BY_CC" => $this->parse("PAID_BY_CC")));
		}

		return $this->parse();
	}

	/**
	@attrib name=show_available_rooms all_args=1 nologin="1"
	**/
	function show_available_rooms($arr)
	{
		if (!$arr["r_url"])
		{
			$arr["r_url"] = obj_link($arr["section"]);
		}
		if ($arr["i_location"] == "")
		{
			$arr["i_location"] = "27,37,38,39,40,41,42";
		}
		if ($arr["api_start_days"] > 0)
		{
			$arr["i_checkin"] = date("d.m.Y", time() + $arr["api_start_days"]*24*3600);
		}
		if ($arr["i_checkin"] == "")
		{
				$arr["i_checkin"] = date("d.m.Y");
		}
		if ($arr["i_checkout"] == "" && $arr["api_departure_days"])
		{
			list($d,$m, $y) = explode(".", $arr["i_checkin"]);
			$arr["i_checkout"] = date("d.m.Y", mktime(1,1,1,$m, $d+$arr["api_departure_days"], $y));
		}
		else
		if ($arr["i_checkout"] == "")
		{
				$arr["i_checkout"] = date("d.m.Y", time() + 24*3600);
		}

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$checkin = 
			sprintf("%04d",$checkindata2[2]).'-'.
			sprintf("%02d",$checkindata2[1]).'-'.
			sprintf("%02d",$checkindata2[0]).'T00:00:00';

		$checkin_ts = mktime(0,0,0,$checkindata2[1], $checkindata2[0], $checkindata2[2]);
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$checkout = sprintf("%04d", $checkoutdata2[2]).'-'.sprintf("%02d",$checkoutdata2[1]).'-'.sprintf("%02d",$checkoutdata2[0]).'T23:59:00';
		$checkout_ts = mktime(0,0,0,$checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);

		if (strpos($arr["i_location"], ",") !== false)
		{
				$this->read_template("multi_hotel.tpl");
				lc_site_load("ows_bron", $this);
				$html = "";
				$tmp = $arr;
				foreach(explode(",", $arr["i_location"]) as $hid)
				{
					$tmp["i_location"] = $hid;
					$i = get_instance(CL_OWS_BRON);
					$i->set_tpl = "one_in_many_hotel.tpl";
					$html .= $i->show_available_rooms($tmp);
				}
				$this->vars(array(
					"html" => $html
				));

				$this->_insert_hotel_list($arr["i_location"]);
		
				$this->vars(array(
					"i_rooms_".$arr["i_rooms"] => "SELECTED",
					"i_adult1_".$arr["i_adult1"] => "SELECTED",
					"i_child1_".$arr["i_child1"] => "SELECTED",
					"i_checkin" => htmlspecialchars($arr["i_checkin"]),
					"i_checkout" => htmlspecialchars($arr["i_checkout"]),
					"api_departure_days" => $arr["api_departure_days"],
					"sel_hotel_".$arr["i_location"] => "SELECTED",
					"eur_url" => aw_url_change_var("set_currency", "EUR"),
					"pound_url" => aw_url_change_var("set_currency", "GBP"),
					"usd_url" => aw_url_change_var("set_currency", "USD"),
					"eek_url" => aw_url_change_var("set_currency", "EEK"),
					"eur_sel" => $currency == "EUR" ? "SELECTED" : "",
					"pound_sel" => $currency == "GBP" ? "SELECTED" : "",
					"usd_sel" => $currency == "USD" ? "SELECTED" : "",
					"eek_sel" => $currency == "EEK" ? "SELECTED" : "",
					"RateList" => $tmp,
					"currentdate" => date('d.m.Y', $checkin_ts),
					"i_promo" => $arr["i_promo"],
					"tomorrow" => date("d.m.Y", $checkout_ts),
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
							"api_departure_days" => $arr["api_departure_days"],
							"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
							"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
						)
					),
					"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
					"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
				));
				return $this->parse();
		}

		if ($this->set_tpl)
		{
			$this->read_template($this->set_tpl);
		}
		else
		{
			$this->read_template("view2.tpl");
		}
		lc_site_load("ows_bron", $this);
		$arr["r_url"] = aw_url_change_var("error", null, $arr["r_url"]);
		$arr["r_url"] = aw_url_change_var("i_location", $arr["i_location"], $arr["r_url"]);

		$currency = $arr["set_currency"] ? $arr["set_currency"] : "EUR";

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		if ($arr["i_location"] == 17971)
		{
			//header("Location: http://www.revalinn.com/en/reval-inn-klaipeda");
			die("<script language=javascript>window.open('http://www.revalinn.com/en/reval-inn-klaipeda'); window.location.href = '".$arr["r_url"]."';</script>");
		}

		if ($arr["i_location"] == 17969)
		{
			die("<script language=javascript>window.open('http://www.revalinn.com/en/reval-inn-vilnius'); window.location.href = '".$arr["r_url"]."';</script>");
			header("Location: http://www.revalinn.com/en/reval-inn-vilnius");
			die();
		}

		if (($checkout_ts - $checkin_ts) > (24*3600*99))
		{
			$arr["r_url"] = aw_url_change_var("error", 7, $arr["r_url"]);
			return $arr["r_url"];
		}

		classload("core/date/date_calc");
		if ($checkoutdata2[2] < date("Y") || $checkindata2[2] < date("Y") || $checkindata2[2] > (date("Y")+5) || $checkoutdata2[2] > (date("Y")+5) || ($checkin_ts < get_day_start()))
		{
			$arr["r_url"] = aw_url_change_var("error", 5, $arr["r_url"]);
			return $arr["r_url"];
		}

		if (($checkin_ts > (time() + 24*3600*370)) || ($checkout_ts > (time() + 24*3600*370)))
		{
			$arr["r_url"] = aw_url_change_var("error", 10, $arr["r_url"]);
			return $arr["r_url"];
		}

		if ($arr["i_location"] == 17380 && $checkin_ts < mktime(0,0,0,3,17,2008))
		{
			$arr["r_url"] = aw_url_change_var("error", 101, $arr["r_url"]);
			return $arr["r_url"];
		}

		if ($arr["i_location"] == 18941 && $checkin_ts < mktime(0,0,0,4,28,2008))
		{
			$arr["r_url"] = aw_url_change_var("error", 102, $arr["r_url"]);
			return $arr["r_url"];
		}

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

		$rooms = max((int)$arr["i_rooms"], 1);
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
			if($childcount[$i] + $adultcount[$i] > 4 || $adultcount[$i] < 1)
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
if ($_GET["DH"] == 1)
{
	die(dbg::dump($hotel));
}
		/*$amenities = array("IsBusinessCenter","IsConferenceRoom","IsGym","IsInternetAccess","IsParking","IsPets","IsRestaurant","IsRoomService","IsSwimmingPool","IsWheelchair");
		foreach($amenities as $amenity)
		{
			if($hotel[$amenity] == "true")
			{
				$this->vars(array(
					$amenity => $this->parse($amenity)
				));
			}
		}*/
//echo dbg::dump($hotel);
		$ol = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"user4" => $hotel["OwsHotelCode"],
			"site_id" => array(),
			"lang_id" => array()
		));
		$doc = $ol->begin();
		if ($doc)
		{
	// TRANS!
			$this->vars(array("amenities" => $doc->prop("lead") != "" ? $doc->prop("lead") : $doc->prop("content")));
		}
		$hpo = iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["PictureUrl"]);
		$hpo = str_replace("_170", "", $hpo);
		$hp = str_replace(".jpg", "_170.jpg", str_replace(".gif", "_170.gif", $hpo));
		$hp_big = str_replace(".jpg", "_500.jpg", str_replace(".gif", "_500.gif", $hpo));

		$this->vars(array(
			"i_adults" => $arr["i_adult1"],
			"i_children" => $arr["i_child1"],
			"i_promo" => $arr["i_promo"],
			"HotelName" => iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"]),
			"HotelDesc" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["ShortNote"])),
			"HotelAddress" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["AddressLine1"].', '.$hotel["AddressLine2"]),
			"HotelPhone" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["Phone"]),
			"HotelMap" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["MapUrl"]),
			"HotelUrl" => iconv("utf-8", aw_global_get("charset")."//IGNORE",$hotel["InfoUrl"]),
			"HotelPic" => $hp,
			"HotelPicBig" => $hp_big,
//			"step1_url" => obj_link($arr["section"])
			"step1_url" => obj_link(aw_ini_get("frontpage")), /*aw_url_change_var(array(
				"i_location" => $arr["i_location"],
				"i_checkin" => $arr["i_checkin"],
				"i_checkout" => $arr["i_checkout"],
				"i_rooms" => $arr["i_rooms"],
				"i_adult1" => $arr["i_adults"],
				"i_child1" => $arr["i_children"],
				"i_promo" => $arr["i_promo"],
				"section" => aw_ini_get("frontpage"), //$arr["section"],
				"api_departure_days" => $arr["api_departure_days"],
				"no_reforb" => 1,
				"r_url" => obj_link($arr["section"])."&ow_bron=".$arr["ow_bron"]
			), false, obj_link(aw_global_get("section")))*/
		));
		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = $adultcount[1];
		$parameters["numberOfChildrenPerRoom"] = $childcount[1];
		$parameters["promotionCode"] = iconv(aw_global_get("charset"), "utf-8", $promo);
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = reval_customer::get_cust_id();
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

		if($rates["ResultCode"] != 'Success' && $rates["ResultMessage"] == "PROPERTY NOT AVAILABLE")
		{
			$arr["r_url"] = aw_url_change_var("error", 8, $arr["r_url"]);
			return $arr["r_url"];
		}

		if($rates["ResultCode"] != 'Success' && $rates["ResultMessage"] == "PROPERTY RESTRICTED")
		{
			$arr["r_url"] = aw_url_change_var("error", 8, $arr["r_url"]);
			return $arr["r_url"];
		}

		if($rates["ResultCode"] != 'Success' && substr($rates["ResultMessage"], 0, strlen("Number of Children")) == "Number of Children")
		{
			$arr["r_url"] = aw_url_change_var("error", 9, $arr["r_url"]);
			return $arr["r_url"];
		}

		if($rates["ResultCode"] == 'OwsError' && $rates["ResultMessage"] == "NUMBER NIGHTS EXCEEDS LIMIT")
		{
			$arr["r_url"] = aw_url_change_var("error", 10, $arr["r_url"]);
			return $arr["r_url"];
		}

		if($rates["ResultCode"] != 'Success')
		{
			//die("webservice error ".dbg::dump($return));
			$this->proc_ws_error($parameters, $return);
			return $arr["request"]["r_url"];
		}
		else
		if (!$rates["RateList"])
		{
			$arr["r_url"] = aw_url_change_var("error", 6, $arr["r_url"]);
			return $arr["r_url"];
		}
		if ($rates["RateList"]["RateInfo"]["RateId"] > 0)
		{
			$rates = array( $rates["RateList"]["RateInfo"]);
		}
		else
		{
			$rates = $rates["RateList"]["RateInfo"];
		}
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
		//echo "rates = ".dbg::dump($rates)." <br>";
$rate_ids = array();
		foreach($rates as $rate)
		{
		//echo "rate = ".dbg::dump($rate);
			if ($rate["IsVisible"] == "false" || $rate["IsAvailableForBooking"] == "false")
			{
				continue;
			}

			if (!empty($arr["rate_id"]) && $rate["RateId"] != $arr["rate_id"])
			{
				continue;
			}

			if (!is_array($rt2rates[$hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"]]))
			{
				$rt2rates[$hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"]] = array();
			}
			$rt2rates[$hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"]][] = $rate;
			$rate_ids[] = $rate["RateId"];
		}
//echo dbg::dump($rt2rates);
//die();
		$this->code2doc = $code2doc;
		uksort($rt2rates, array(&$this, "__sort_rt"));

		foreach($rt2rates as $rt => $ratedata)
		{
			$doc = $code2doc[$rt];
			if (!$doc)
			{
			if ($_GET["ROOM_DOC_DBG"] == 1)
			{
				echo "no doc for $rt <Br>";
			}
			/*	continue;*/
				$doc = obj();
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

			// TRANS!
			$lead = preg_replace("/#pict(\d+?)(v|k|p|)#/i","",$doc->prop("lead"));
			$lead = preg_replace("/#p(\d+?)(v|k|p|)#/i","",$lead);
//echo "room = ".$rate["Title"]." doc = ".dbg::dump($doc)." <br>";
			usort($ratedata, array(&$this, "__sort_rates"));
			$rate = reset($ratedata);

			if (is_oid($doc->id()))
			{
			// TRANS!
				list($name, $title) = explode(",", $doc->prop("title"), 2);
			}
			else
			{
				$name = $rate["Title"];
			}
			if ($_GET["dbg"] == 1)
			{
//				die(dbg::dump($rate));
				}
			$this->vars(array(
				"big_img_1_url" => $i1b_url,
				"big_img_2_url" => $i2b_url,
				"small_img_1_url" => $i1s_url,
				"small_img_2_url" => $i2s_url,
				"short_note" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Title"])),
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
					"doc_code" => $doc->id()
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
					"short_note" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Title"])),
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
					"doc_code" => $doc->id()

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
				"short_note" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Title"])),
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
					"doc_code" => $doc->id()
				), "ows_bron", false, true),
				"num_offers" => count($ratedata)
			));
			$this->vars(array(
				"type_in_rate" => $inl
			));
			$tmp .= $this->parse("RateList");
		}
		$this->_insert_hotel_list($arr["i_location"]);

		$arr["i_rooms"] = max(1, $arr["i_rooms"]);
		$this->vars(array(
			"i_rooms_".$arr["i_rooms"] => "SELECTED",
			"i_adult1_".$arr["i_adult1"] => "SELECTED",
			"i_child1_".$arr["i_child1"] => "SELECTED",
			"i_checkin" => htmlspecialchars($arr["i_checkin"]),
			"i_checkout" => htmlspecialchars($arr["i_checkout"]),
			"api_departure_days" => $arr["api_departure_days"],
			"sel_hotel_".$arr["i_location"] => "SELECTED",
			"eur_url" => aw_url_change_var("set_currency", "EUR"),
			"pound_url" => aw_url_change_var("set_currency", "GBP"),
			"usd_url" => aw_url_change_var("set_currency", "USD"),
			"eek_url" => aw_url_change_var("set_currency", "EEK"),
			"eur_sel" => $currency == "EUR" ? "SELECTED" : "",
			"pound_sel" => $currency == "GBP" ? "SELECTED" : "",
			"usd_sel" => $currency == "USD" ? "SELECTED" : "",
			"eek_sel" => $currency == "EEK" ? "SELECTED" : "",
			"cur_select" => $this->picker($currency, $this->currency_picker),
			"RateList" => $tmp,
			"currentdate" => date('d.m.Y', $checkin_ts),
			"i_promo" => $arr["i_promo"],
			"tomorrow" => date("d.m.Y", $checkout_ts),
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
					"api_departure_days" => $arr["api_departure_days"],
					"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
					"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
				)
			),
			"partnerWebsiteGuid" => $arr["partnerWebsiteGuid"],
			"partnEerWebsiteDomain" => $arr["partnEerWebsiteDomain"],
		));

		if ($_GET["error"] > 0)
		{
			$this->vars(array(
				"ERR_".$_GET["error"] => $this->parse("ERR_".$_GET["error"])
 			));
		}
		
		$tmp = "";
		for ($i=0;$i<10;$i++)
		{
			$this->vars(array(
				"i" => $i,
			));
			$tmp .= $this->parse("CURRENCIES");
		}
		$this->vars(array(
			"CURRENCIES" => $tmp,
		));


		return $this->parse();
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
if ($_GET["finder"])
{
ob_end_clean();
aw_global_set("soap_debug", 1);
			$return = $this->do_orb_method_call(array(
				"action" => "GetHotelDescriptions",
				"class" => "http://markus.ee/RevalServices/Booking/",
				"params" => array(),
				"method" => "soap",
				"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
			));
die(dbg::dump($return));
		$return = $this->do_orb_method_call(array(
			"action" => "GetBookingDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => array("confirmationCode" => $_GET["finder"]),
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
die(dbg::dump($return));
	
	$ol = new object_list(array(
		"class_id" => CL_OWS_RESERVATION,
		"confirmation_code" => $_GET["finder"]
	));
	die(dbg::dump($ol->arr()));
}
		$ob = new object($arr["id"]);

		$tpl = "bron_box.tpl";
		if ($ob->prop("template") != "")
		{
			$tpl = $ob->prop("template");
		}
		$this->read_template($tpl);

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		lc_site_load("ows_bron", $this);
		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		$this->_insert_hotel_list($_GET["i_location"] ? $_GET["i_location"] : $this->detect_hotel());

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
		$o = obj(aw_global_get("section"));
		$has = false;
		foreach($o->path() as $item)
		{
			if ($item->id() == 173700)
			{
				$has = true;
			}
		}
		$this->vars(array(
			"currentdate" => $_GET["i_checkin"] ? $_GET["i_checkin"] : date('d.m.Y'),
			"tomorrow" => $_GET["i_checkout"] ? $_GET["i_checkout"] : date("d.m.Y", time() + 24*3600),
			"rooms" => $rooms,
			"api_departure_days" => max($_GET["api_departure_days"], 1),
			"i_promo" => $_GET["i_promo"],
			"reforb" => $this->mk_reforb(
				"show_available_rooms",
				array(
					"section" => $has ? 177281 : 107220, //aw_global_get("section"),
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
		$parameters["promotionCode"] = iconv(aw_global_get("charset"), "utf-8", $promo);
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = reval_customer::get_cust_id();
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

		/*$parameters = array();
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

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = (int)$rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$i_adult;
		$parameters["numberOfChildrenPerRoom"] = (int)$i_child;
		$parameters["promotionCode"] = iconv(aw_global_get("charset"), "utf-8", $promo);
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
*/

/*		$room_desc_list = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"lang_id" => array(),
			"site_id" => array(),
			"user4" => $arr["doc_code"]//$hotel["OwsHotelCode"]."-".$rate["OwsRoomTypeCode"]
		));*/

		$doc = obj($arr["doc_code"]);
		$this->vars(array(
//			"room_rate" => number_format($rate["AverageDailyRate"], 2),
	//		"total_rate" => $rate["Total_price"],
		//	"currency" => $currency,
//			"short_currency" => $this->short_cur_lut[$currency],
// TRANS!
			"description" => $doc->prop("content"),//$rate["LongNote"],
//			"cancel_by" => $rate["CancellationDeadline"],
//			"start" => $checkin_date,
//			"room_name" => nl2br(iconv("utf-8", aw_global_get("charset")."//IGNORE", $rate["Title"])),
			"doc_title" => $doc->prop("title"),
			"long_note" => $doc->prop("content")//$rate["LongNote"],
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
		$checkin = sprintf("%04d",$checkindata2[2]).'-'.sprintf("%02d",$checkindata2[1]).'-'.sprintf("%02d",$checkindata2[0]).'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$departure = mktime(23,59,0, $checkoutdata2[1], $checkoutdata2[0], $checkoutdata2[2]);
		$checkout = sprintf("%04d",$checkoutdata2[2]).'-'.sprintf("%02d",$checkoutdata2[1]).'-'.sprintf("%02d",$checkoutdata2[0]).'T23:59:00';
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
		$parameters["promotionCode"] = iconv(aw_global_get("charset"), "utf-8", $promo);
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = reval_customer::get_cust_id();
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
			$bp = get_instance(CL_BANK_PAYMENT);
//die(dbg::dump($arr["bpo"]));
			die($bp->do_payment(array(
				"payment_id" => $arr["bpo"],
				"bank_id" => "credit_card",
				"amount" => $rate["TotalPriceInEur"]*15.65,
				"reference_nr" => $arr["aw_rvs_id"],
				"expl" => "webID:".$arr["aw_rvs_id"]." ".$arr["i_checkin"]."-".$arr["i_checkout"]." ".iconv("utf-8", aw_global_get("charset")."//IGNORE", $hotel["HotelName"])." ".$arr["ct"]["firstname"]." ".$arr["ct"]["lastname"],
				"lang" => $lc
			)));
	}

	/**
		@attrib name=cancel_booking all_args="1" nologin="1"
	**/
	function cancel_booking($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_OWS_RESERVATION,
			"lang_id" => array(),
			"site_id" => array(),
			"confirmation_code" => $arr["confirmation_number"],
			"limit" => 1
		));
		$obj = $ol->begin();
		if (empty($arr["confirmation_number"]) || empty($arr["last_name"]) || empty($arr["reason"]))
		{
			return $this->mk_my_orb("show_cancel_page", array(
				"err" => 1, 
				"confirmation_number" => $arr["confirmation_number"],
				"last_name" => $arr["last_name"],
				"section" => $arr["section"],
				"reason" => $arr["reason"],
				"reason_comment" => $arr["reason_comment"]
			));
		}

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");

		// check booking from them as well
		$parameters = array(
			"confirmationCode" => $arr["confirmation_number"],
			"webLanguageId" => $this->get_web_language_id($lc)
		);
		$return = $this->do_orb_method_call(array(
			"action" => "GetBookingDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		$lastname = iconv("utf-8", aw_global_get("charset"), $return["GetBookingDetailsResult"]["Booking"]["GuestLastName"]);

		if (strcasecmp(trim($lastname),trim($arr["last_name"])) !== 0)
		{
			return $this->mk_my_orb("show_cancel_page", array(
				"err" => 1, 
				"confirmation_number" => $arr["confirmation_number"],
				"last_name" => $arr["last_name"],
				"section" => $arr["section"],
				"reason" => $arr["reason"],
				"reason_comment" => $arr["reason_comment"]
			));
		}

		$reason_lut = array(
			"change_dates" => t("Dates changed"),
			"plans_changed" => t("Plans changed"),
			"wrong_price" => t("Wrong price"),
			"other" => t("Other")
		);

		$parameters = array(
			"confirmationCode" => $arr["confirmation_number"],
			"cancellationReasonCode" => 1,
			"cancellationReasonText" => sprintf("Reason: %s / Comment: %s", iconv("utf-8", aw_global_get("charset"), $reason_lut[$arr["reason"]]), iconv("utf-8", aw_global_get("charset"), $arr["reason_comment"]))
		);
		$return = $this->do_orb_method_call(array(
			"action" => "CancelBooking",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		
		$rate = $return['CancelBookingResult'];
		if ($rate["ResultCode"] == "ObjectNotFound" || !$ol->count())
		{
			return t("The booking you requested does not exist!");
		}

		if ($rate["ResultCode"] == "OwsError" && $rate["ResultMessage"] == "BOOKING PREVIOUSLY CANCELLED")
		{
			return t("The booking you requested has already been cancelled!");
		}

		if ($rate["ResultCode"] == "OwsError" && $rate["ResultMessage"] == "TOO LATE TO CANCEL")
		{
			return t("Sorry, but it is already too late to cancel this booking!");
		}

		if($rate["ResultCode"] != 'Success')
		{
			$this->proc_ws_error($parameters, $return);
		}
	
		$obj->set_prop("cancel_type", $arr["reason"]);
		$obj->set_prop("cancel_other", $arr["cancel_other"]);
		aw_disable_acl();
		$obj->save();
		aw_restore_acl();

		// send mail about cancellation
		$html = $this->send_cancel_mail($obj);

		die($html);
	}

	function send_cancel_mail($o)
	{
		$tpl = "cancel_mail.tpl";
		if ($this->can("view", $o->prop("ows_bron")))
		{
			$ob = obj($o->prop("ows_bron"));
			$h = $ob->meta("mail_settings_cancel");
			if ($h[$o->prop("hotel_id")] != "")
			{
				$tpl = $h[$o->prop("hotel_id")];
			}
		}
		$this->read_template($tpl);

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");

		/*$parameters = array();
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
			//$this->proc_ws_error($parameters, $return);
		}
		$rate = $rate["RateDetails"];*/

		$this->vars($o->properties());
		
		$this->vars(array(
			"guest_email" => iconv("utf-8", aw_global_get("charset"),urldecode($o->prop("guest_email"))),
			"confirmation_number" => $o->prop("confirmation_code"),
			"checkin" => date("d.m.Y", $o->prop("arrival_date")),
			"checkout" => date("d.m.Y", $o->prop("departure_date")),
			"nights" => max(1,(ceil(($o->prop("departure_date")-$o->prop("arrival_date"))/(60*60*24)))),
			"num_rooms" => $o->prop("num_rooms"),
			"num_adults" => $o->prop("adults_per_room"),
			"room_type" => iconv("utf-8", aw_global_get("charset"), $o->prop("rate_title")),
			"room_details" => iconv("utf-8", aw_global_get("charset"), $o->prop("rate_long_note")),
			"hotel_name" => iconv("utf-8", aw_global_get("charset"), $hotel["HotelName"]),
			"hotel_contact" => iconv("utf-8", aw_global_get("charset"), $hotel["AddressLine1"]." ".$hotel["AddressLine1"]." ".$hotel["Phone"]." ".$hotel["Fax"]." ".$hotel["Email"]),
			"tot_price" => $o->prop("total_charge"),
			"currency" => $o->prop("currency"),
			"guarantee_cc_exp_date" => date("m/Y", $o->prop("guarantee_cc_exp_date")),
			"guest_email" => urldecode($o->prop("guest_email"))
		));

		$html = $this->parse();
	
		$awm = get_instance("protocols/mail/aw_mail");
		$awm->create_message(array(
			"froma" => "info@revalhotels.com",
			"fromn" => "Reval Hotels",
			"subject" => "Your Revalhotels reservation has been canceled!",
			"to" => $o->prop("guest_email"),
			"body" => strip_tags($html),
		));
		$awm->htmlbodyattach(array(
			"data" => $html,
		));
		$awm->gen_mail();
		return $html;
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
		//die("ws error ".dbg::dump($return));
header("Location: http://www.revalhotels.com");
die();
	}

	function _insert_hotel_list($sel_id = null)
	{
			$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);
		// list hotels
		$parameters = array();
		$parameters["webLanguageId"] = $lang;
		$parameters["arrivalDate"] = 
			sprintf("%04d",date("Y")).'-'.
			sprintf("%02d",date("m")).'-'.
			sprintf("%02d",date("d")).'T00:00:00';
		$tm = time() + 5*24*3600;
		$parameters["departureDate"] = 
			sprintf("%04d",date("Y", $tm)).'-'.
			sprintf("%02d",date("m", $tm)).'-'.
			sprintf("%02d",date("d", $tm)).'T00:00:00';
		$parameters["numberOfRooms"] = 1;
		$parameters["numberOfAdultsPerRoom"] = 1;

enter_function("ws:GetAvailableHotels");
		$c = get_instance("cache");
		if (($ct = $c->file_get("ows_hotel_list")))
		{
			$return = unserialize($ct);
		}
		else
		{
			$return = $this->do_orb_method_call(array(
				"action" => "GetHotelDescriptions",
				"class" => "http://markus.ee/RevalServices/Booking/",
				"params" => array(),
				"method" => "soap",
				"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
			));
			$c->file_set("ows_hotel_list", serialize($return));
		}
if ($_GET["kk"] == 1)
{
echo dbg::dump($return);
}
		$hotels = $return["GetHotelDescriptionsResult"]["HotelMinimalInfo"];
		$hdata = array();

		foreach($hotels as $hotel)
		{
				if ($hotel["IsEnabled"] == "false")
				{
					continue;
				}
				$narr = $hotel["TranslatedHotelNames"]["TranslatedName"];
				$nstr = "";
				if (isset($narr["WebLanguageId"]))
				{
					$narr = array($narr);
				}
				foreach($narr as $d)
				{
					if ($d["WebLanguageId"] == $lang)
					{
						$nstr = $d["Name"];
					}
				}
				$hdata[$this->country_lut[$lc][trim($hotel["HotelCountryCode"])].", ".$this->city_lut[$lc][$hotel["HotelCityCode"]]][$hotel["HotelId"]] = iconv("utf-8", aw_global_get("charset")."//IGNORE", $nstr);
		}
//die(dbg::dump($hdata));

		$hs = "";
		foreach($hdata as $loc => $hotels)
		{
				$hotel_list = "";
				foreach($hotels as $hid => $hn)
				{
					if ($hid == 38 && aw_ini_get("site_id") == 354)
					{
						continue;
					}
					$this->vars(array(
						"hotel_id" => $hid,
						"hotel_name" => $hn,
						"hotel_selected" => selected($hid == $sel_id)
					));
					$hotel_list .= $this->parse("HOTEL_ENTRY");
				}
				$this->vars(array(
					"loc" => $loc,
					"HOTEL_ENTRY" => $hotel_list
				));
				$hs .= $this->parse("HOTEL_LOCATION");
		}
		$this->vars(array(
			"HOTEL_LOCATION" => $hs
		));
		exit_function("ws:GetAvailableHotels");
	}

	function detect_country()
	{
		$ipl = get_instance("core/util/ip_locator/ip_locator");
		$v = $ipl->search(get_ip());
		if ($v == false)
		{
			$adr = inet::gethostbyaddr(get_ip());
			$domain = strtoupper(substr($adr, strrpos($adr, ".")));
			return $domain;
		}
		return $v["country_code3"];
	}

	function __sort_rt($code1, $code2)
	{
		$jrk1 = $this->code2doc[$code1];
		$jrk2 = $this->code2doc[$code2];
		if (!$jrk1)
		{
			$jrk1 = obj();
		}
		if (!$jrk2)
		{
			$jrk2 = obj();
		}
		return $jrk1->ord()-$jrk2->ord();
	}

	function __sort_rates($rate1, $rate2)
	{
		return $rate1["TotalPriceInCustomCurrency"] - $rate2["TotalPriceInCustomCurrency"];
	}

	/**
		@attrib name=fetch_currency_prices all_args="1" nologin="1"
	**/
	function fetch_currency_prices($arr)
	{
		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		list($d,$m,$y) = explode(".", $arr["i_checkin"]);
		$checkin = sprintf("%04d", $y).'-'.sprintf("%02d", $m).'-'.sprintf("%02d", $d).'T00:00:00';

		list($d,$m,$y) = explode(".", $arr["i_checkout"]);
		$checkout = sprintf("%04d", $y).'-'.sprintf("%02d", $m).'-'.sprintf("%02d", $d).'T00:00:00';

		$parameters = array();
		$parameters["hotelId"] = $arr["i_location"];
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $arr["i_rooms"];
		$parameters["numberOfAdultsPerRoom"] = $arr["i_adult1"];
		$parameters["numberOfChildrenPerRoom"] = $arr["i_child1"];
		$parameters["promotionCode"] = iconv(aw_global_get("charset"), "utf-8", $arr["i_promo"]);
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = reval_customer::get_cust_id();
		$parameters["rateIDs"] = explode(",", $arr["rate_ids"]);
		$parameters["customCurrencyCode"] = $arr["set_currency"];
		$return = $this->do_orb_method_call(array(
			"action" => "GetMultipleRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		$i=0;
		$s_out = "var a_prices = new Array();\n";
		if (count($parameters["rateIDs"]) > 1)
		{
			foreach ($return["GetMultipleRateDetailsResult"]["RateList"]["RateInfo"] as $rateinfo)
			{
				$s_out .= 'tmp = {"id" : "'.$rateinfo["RateId"].'", "AverageDailyRate" : "'.$rateinfo["AverageDailyRateInCustomCurrency"].'", "TotalPrice" : "'.$rateinfo["TotalPriceInCustomCurrency"].'" };'."\n";
				$s_out .= "a_prices[a_prices.length] = tmp;\n";
			}
		}
		else if (count($parameters["rateIDs"]) != 0)
		{
			$rateinfo = $return["GetMultipleRateDetailsResult"]["RateList"]["RateInfo"];
			$s_out .= 'tmp = {"id" : "'.$rateinfo["RateId"].'", "AverageDailyRate" : "'.$rateinfo["AverageDailyRate"].'", "TotalPrice" : "'.$rateinfo["TotalPrice"].'" };'."\n";
			$s_out .= "a_prices[a_prices.length] = tmp;\n";
		}
		die($s_out);
	}

	function _init_mail_templates_t(&$t)
	{
		$t->define_field(array(
			"name" => "hotel",
			"caption" => t("Hotell"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "template",
			"caption" => t("Template"),
			"align" => "center"
		));
	}

	function _get_mail_templates($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mail_templates_t($t);

		$h = $arr["obj_inst"]->meta($arr["request"]["group"]);
		foreach($this->hotel_list as $hotel_id => $hotel_name)
		{
			$t->define_data(array(
				"hotel" => $hotel_name,
				"template" => html::textbox(array(
					"name" => "mail_templates[$hotel_id]",
					"value" => $h[$hotel_id]
				))
			));
		}
	}

	function _set_mail_templates($arr)
	{
		$arr["obj_inst"]->set_meta($arr["request"]["group"], $arr["request"]["mail_templates"]);
	}

	function _init_bank_settings_t(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "bank_object",
			"caption" => t("Pangamakse objekt"),
			"align" => "center"
		));
	}

	function _get_bank_settings_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bank_settings_t($t);

		$bank_ol = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_BANK_PAYMENT")));
		$bank_picker = array("" => t("--vali--")) + $bank_ol->names();
		
		$sets = $arr["obj_inst"]->meta("bank_settings");
		foreach($this->hotel_list as $hotel_id => $hotel_name)
		{
			$t->define_data(array(
				"name" => $hotel_name,
				"bank_object" => html::select(array(
					"name" => "bank[$hotel_id]",
					"value" => $sets[$hotel_id],
					"options" => $bank_picker
				))
			));
		}
	}

	function _set_bank_settings_table($arr)
	{
		$arr["obj_inst"]->set_meta("bank_settings", $arr["request"]["bank"]);
	}

	function _init_mail_bcc_t(&$t)
	{
		$t->define_field(array(
			"name" => "hotel",
			"caption" => t("Hotell"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "bcc",
			"caption" => t("BCC"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "subject",
			"caption" => t("Kirja teema"),
			"align" => "center"
		));
	}

	function _get_mail_bcc($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mail_bcc_t($t);

		$h = $arr["obj_inst"]->meta("hotel_bcc");
		$ht = $arr["obj_inst"]->meta("hotel_bcc_titles");
		foreach($this->hotel_list as $hotel_id => $hotel_name)
		{
			$t->define_data(array(
				"hotel" => $hotel_name,
				"bcc" => html::textbox(array(
					"name" => "bcc[$hotel_id]",
					"value" => $h[$hotel_id]
				)),
				"subject" => html::textbox(array(
					"name" => "subj[$hotel_id]",
					"value" => $ht[$hotel_id]
				))
			));
		}
	}

	function _set_mail_bcc($arr)
	{
		$arr["obj_inst"]->set_meta("hotel_bcc", $arr["request"]["bcc"]);
		$arr["obj_inst"]->set_meta("hotel_bcc_titles", $arr["request"]["subj"]);
	}

	function _init_mail_bank_bcc_t(&$t)
	{
		$t->define_field(array(
			"name" => "hotel",
			"caption" => t("Hotell"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "bcc",
			"caption" => t("BCC"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "subject",
			"caption" => t("Kirja teema"),
			"align" => "center"
		));
	}

	function _get_mail_bank_bcc($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mail_bank_bcc_t($t);

		$h = $arr["obj_inst"]->meta("hotel_bank_bcc");
		$ht = $arr["obj_inst"]->meta("hotel_bank_bcc_titles");
		foreach($this->hotel_list as $hotel_id => $hotel_name)
		{
			$t->define_data(array(
				"hotel" => $hotel_name,
				"bcc" => html::textbox(array(
					"name" => "bcc[$hotel_id]",
					"value" => $h[$hotel_id]
				)),
				"subject" => html::textbox(array(
					"name" => "subj[$hotel_id]",
					"value" => $ht[$hotel_id]
				))
			));
		}
	}

	function _set_mail_bank_bcc($arr)
	{
		$arr["obj_inst"]->set_meta("hotel_bank_bcc", $arr["request"]["bcc"]);
		$arr["obj_inst"]->set_meta("hotel_bank_bcc_titles", $arr["request"]["subj"]);
	}
	
	function detect_hotel()
	{
		$o = obj(aw_global_get("section")); 
		foreach($o->path() as $path_item)
		{
				if ($path_item->prop("color"))
				{
					return $path_item->prop("color");
				}
		}
		return null;
	}
}
?>
