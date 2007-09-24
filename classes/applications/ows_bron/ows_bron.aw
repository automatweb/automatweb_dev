<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ows_bron/ows_bron.aw,v 1.2 2007/09/24 13:01:38 kristo Exp $
// ows_bron.aw - OWS Broneeringukeskus 
/*

@classinfo syslog_type=ST_OWS_BRON relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class ows_bron extends class_base
{
	function ows_bron()
	{
		$this->init(array(
			"tpldir" => "applications/ows_bron/ows_bron",
			"clid" => CL_OWS_BRON
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
	@attrib name=show_booking_details all_args=1
	**/
	function show_booking_details($arr)
	{
		$this->read_template("view3.tpl");
		

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lanc_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$arrival = mktime(0,0,0, $checkindata[1], $checkindata[0], $checkindata[2]);
		$checkin = $checkindata2[2].'-'.$checkindata2[1].'-'.$checkindata2[0].'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$departure = mktime(23,59,0, $checkoutdata[1], $checkoutdata[0], $checkoutdata[2]);
		$checkoutdata2 = explode('.', $checkoutdata);
		$checkout = $checkoutdata2[2].'-'.$checkoutdata2[1].'-'.$checkoutdata2[0].'T23:59:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
		$currency = $arr["i_currency"]?$arr["request"]["i_currency"]:0;
		$rateid= $arr["radio1"];
		$nights = ceil(($departure-$arrival)/(60*60*24));

		$parameters = array();
		$parameters["hotelId"] = $location;
		$parameters["rateId"] = $rateid;
		$parameters["arrivalDate"] = $checkin;
		$parameters["departureDate"] = $checkout;
		$parameters["numberOfRooms"] = $rooms;
		$parameters["numberOfAdultsPerRoom"] = (int)$arr["i_adult"];
		$parameters["numberOfChildrenPerRoom"] = (int)$arr["i_child"];
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		$parameters["customerId"] = 0;
		if($currency)
			$parameters["customCurrencyCode"] = $currency;
		$return = $this->do_orb_method_call(array(
			"action" => "GetRateDetails",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));

		$rate = $return['GetRateDetailsResult'];
		
		if(!$rate["ResultCode"] != 'Success')
			return $arr["r_url"];
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

		$this->vars(array(
			"totalprice" => number_format($rate["TotalPriceInCustomCurrency"], 2),
			"room" => $rooms,
			"adults" => $arr["i_adult"],
			"hotelname" => $hotel["HotelName"],
			"arrival" => $arr["i_checkin"],
			"departure" => $arr["i_checkout"],
			"nights" => $nights,
			"reforb" => $this->mk_reforb("show_confirm_view", array("no_reforb" => 1))
		));

		return $this->parse();
	}

	/**
		@attrib name=show_confirm_view all_args="1"
	**/
	function show_confirm_view($arr)
	{
		$this->read_template("view4.tpl");
		$a =  $this->parse();
		return $a;
	}

	/**
	@attrib name=show_available_rooms all_args=1
	**/
	function show_available_rooms($arr)
	{
		$this->read_template("view2.tpl");

		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lanc_lc") : aw_global_get("LC");
		$lang = $this->get_web_language_id($lc);

		$checkindata = $arr["i_checkin"];
		$checkindata2 = explode('.', $checkindata);
		$checkin = $checkindata2[2].'-'.$checkindata2[1].'-'.$checkindata2[0].'T00:00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$checkout = $checkoutdata2[2].'-'.$checkoutdata2[1].'-'.$checkoutdata2[0].'T23:59:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
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
				$arr["r_url"] = aw_url_change_var("error".$i, 1, $arr["r_url"]);
			}
		}
		if($rc_error)
		{
			return $arr["r_url"];
		}
		$promo = $arr["i_promo"];
		$currency = $arr["i_currency"]?$arr["request"]["i_currency"]:0;

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

		$hotel = $return["GetHotelDetailsResult"];
		if($hotel["ResultCode"] != 'Success')
			return $arr["r_url"];

		$hotel = $hotel["HotelDetails"];
		$amenities = array();
		if($hotel["IsBusinessCenter"])
			$amenities[] = t('Ärikeskus');
		if($hotel["IsConferenceRoom"])
			$amenities[] = t('Konverentsisaal');
		if($hotel["IsGym"])
			$amenities[] = t('Jõusaal');
		if($hotel["IsInternetAccess"])
			$amenities[] = t('Tasuta Internet');
		if($hotel["IsParking"])
			$amenities[] = t('Parkla');
		if($hotel["IsPets"])
			$amenities[] = t('Lemmikloomad lubatud');
		if($hotel["IsRestaurant"])
			$amenities[] = t('Restoran');
		if($hotel["IsRoomService"])
			$amenities[] = t('Toateenindus');
		if($hotel["IsSwimmingPool"])
			$amenities[] = t('Bassein');
		if($hotel["IsWheelchair"])
			$amenities[] = t('Ratastooliga ligipääsetav');
		$tmp = '';
		foreach($amenities as $amenity)
		{
			$this->vars(array(
				"Item" => $amenity
			));
			$tmp .= $this->parse("HotelAmenities");
		}
		$this->vars(array(
			"HotelAmenities" => $tmp
		));
		$this->vars(array(
			"HotelName" => $hotel["HotelName"],
			"HotelDesc" => $hotel["ShortNote"],
			"HotelAddress" => $hotel["AddressLine1"].', '.$hotel["AddressLine2"],
			"HotelPhone" => $hotel["Phone"],
			"HotelMap" => $hotel["MapUrl"],
			"HotelUrl" => $hotel["InfoUrl"],
			"HotelPic" => $hotel["PictureUrl"],
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
		if($currency)
		{
			$parameters["customCurrencyCode"] = $currency;
		}

		$return = $this->do_orb_method_call(array(
			"action" => "GetAvailableRates",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServices/BookingService.asmx"
		));
		$rates = $return["GetAvailableRatesResult"];

		if(!$rates["RateList"])
		{
			return $arr["request"]["r_url"];
		}
		$rates = $rates["RateList"]["RateInfo"];
		$tmp = '';
		$i=0;
		foreach($rates as $rate)
		{
			$i++;
			if($i>5)continue;
			$this->vars(array(
				"Id" => $rate["RateId"],
				"Name" => $rate["Name"],
				"Title" => $rate["Title"],
				"Note" => $rate["LongNote"],
				"Pic" => $rate["PictureUrl"],
				"Slideshow" => $rate["SlideshowUrl"],
				"price1_avg" => $rate["AverageDailyRate"],
				"price1_total" => $rate["TotalPrice"]
			));
			$tmp .= $this->parse("RateList");
		}
		$this->vars(array(
			"RateList" => $tmp,
			"currentdate" => date('d.m.Y'),
			"reforb1" => $this->mk_reforb(
				"show_available_rooms",
				array(
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"r_url" => get_ru()
				)
			),
			"reforb2" => $this->mk_reforb(
				"show_booking_details",
				array(
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"i_checkin" => $arr["i_checkin"],
					"i_checkout" => $arr["i_checkout"],
					"i_adult" => 1,
					"i_child" => 0,
					"i_rooms" => 1,
					"i_promo" => $arr["i_promo"],
					"i_location" => $arr["i_location"],
					"r_url" => get_ru()
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
			"rooms" => $rooms,
			"reforb" => $this->mk_reforb(
				"show_available_rooms",
				array(
					"section" => aw_global_get("section"),
					"no_reforb" => 1,
					"r_url" => get_ru()
				)
			)
		));
		return $this->parse();
	}
}
?>
