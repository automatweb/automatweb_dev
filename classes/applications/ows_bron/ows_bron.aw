<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ows_bron/ows_bron.aw,v 1.1 2007/09/17 12:20:31 robert Exp $
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

		return $this->parse();
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
		$checkin = $checkindata2[2].'-'-$checkindata2[1].'-'.$checkindata2[0].'T00:00';
		$checkoutdata = $arr["i_checkout"];
		$checkoutdata2 = explode('.', $checkoutdata);
		$checkin = $checkoutdata2[2].'-'-$checkoutdata2[1].'-'.$checkoutdata2[0].'T00:00';
		$location = $arr["i_location"];
		$rooms = (int)$arr["i_rooms"];
		$childcount = (int)$arr["i_child"];
		$adultcount = (int)$arr["i_adult"];
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
			"server" => "http://domino.forumcinemas.ee/RevalServices/BookingService.asmx"
		));
		$hotel = $return["GetHotelDetailsResult"];
		die(arr($hotel));
		if($hotel["ResultCode"] != 'Success')
			return $arr["request"]["r_url"];

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
		$parameters["numberOfAdultsPerRoom"] = $adultcount;
		$parameters["numberOfChildrenPerRoom"] = $childcount;
		$parameters["promotionCode"] = $promo;
		$parameters["webLanguageId"] = $lang;
		if($currency)
			$parameters["customCurrencyCode"] = $currency;
		$return = $this->do_orb_method_call(array(
			"action" => "GetAvailableRates",
			"class" => "http://markus.ee/RevalServices/Booking/",
			"params" => $parameters,
			"method" => "soap",
			"server" => "http://domino.forumcinemas.ee/RevalServices/BookingService.asmx"
		));
		$rates = $return["GetAvailableRatesResult"];
		if($rates["ResultCode"] != 'Success')
			return $arr["request"]["r_url"];

		$rates = $rates["RateList"];
		$tmp = '';
		die(arr($rates));
		foreach($rates as $rate)
		{
			$tmp2 = '';
			/*foreach($rate['hotels'] as $id => $hotel)
			{
				$this->vars(array(
					"Hotel" => $hotel["HotelInfo"]["HotelName"],
					"HotelId" => $id
				));
				$tmp2 .= $this->parse("HotelList");
			}*/
			$this->vars(array(
				"Categories" => $tmp2,
				"Name" => $rate["Name"],
				"Title" => $rate["Title"],
				"Note" => $rate["ShortNote"],
				"Pic" => $rate["PictureUrl"],
				"Slideshow" => $rate["SlideshowUrl"],
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
					"r_url" => get_ru()
				)
			)
		));
		return $this->parse();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);

		$this->read_template("bron_box.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		$this->vars(array(
			"currentdate" => date('d.m.Y'),
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

//-- methods --//
}
?>
