<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_bookigs_entry.aw,v 1.1 2006/11/15 13:07:21 kristo Exp $
// spa_bookigs_entry.aw - SPA Reisib&uuml;roo liides 
/*

@classinfo syslog_type=ST_SPA_BOOKIGS_ENTRY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property packet_folder_list type=relpicker reltype=RELTYPE_PFL_FOLDER multiple=1 field=meta method=serialize
	@caption Tootepakettide kaustad

	@property user_group type=relpicker reltype=RELTYPE_GROUP field=meta method=serialize
	@caption Kasutajagrupp, millesse pannakse selle liidese kaudu sisestatud kasutajad

	@property persons_folder type=relpicker reltype=RELTYPE_PERSONS_FOLDER field=meta method=serialize
	@caption Isikute asukoht


@default group=ppl_entry

	@property cust_entry_fb type=text store=no no_caption=1

	@property cust_entry type=table store=no no_caption=1


@default group=cust

	@layout ver_split type=hbox width=10%:90%

		@layout search type=vbox area_caption=Otsing parent=ver_split closeable=1

			@property s_fn type=textbox captionside=top store=no parent=search size=23
			@caption Eesnimi

			@property s_ln type=textbox captionside=top store=no parent=search size=23
			@caption Perenimi

			@property s_date_from type=date_select captionside=top store=no parent=search format=day_textbox,month_textbox,year_textbox
			@caption Alates

			@property s_date_to type=date_select captionside=top store=no parent=search format=day_textbox,month_textbox,year_textbox
			@caption Kuni

			@property s_package type=select captionside=top store=no parent=search
			@caption Pakett

			@property s_btn type=submit store=no parent=search no_caption=1
			@caption Otsi

		@property s_res type=table no_caption=1 store=no parent=ver_split


@default group=my_bookings,my_bookings_agent

	@property my_bookings type=table store=no no_caption=1

@groupinfo ppl_entry caption="Isikud"
@groupinfo cust caption="Kliendid" submit=no
@groupinfo my_bookings caption="Minu broneeringud" 
@groupinfo my_bookings_agent caption="Klientide broneeringud" 

@reltype PFL_FOLDER value=1 clid=CL_MENU
@caption Tootepakettide kaust

@reltype GROUP value=2 clid=CL_GROUP
@caption Kasutajagrupp

@reltype PERSONS_FOLDER value=3 clid=CL_MENU
@caption Isikute asukoht

*/

class spa_bookigs_entry extends class_base
{
	function spa_bookigs_entry()
	{
		$this->init(array(
			"tpldir" => "applications/spa_bookings/spa_bookigs_entry",
			"clid" => CL_SPA_BOOKIGS_ENTRY
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

	function callback_mod_retval($arr)
	{
		$arr["args"]["s_fn"] = $arr["request"]["s_fn"];
		$arr["args"]["s_ln"] = $arr["request"]["s_ln"];
		$arr["args"]["s_date_from"] = $arr["request"]["s_date_from"];
		$arr["args"]["s_date_to"] = $arr["request"]["s_date_to"];
		$arr["args"]["s_package"] = $arr["request"]["s_package"];
	}	

	function _init_cust_entry_t(&$t)
	{
		$t->define_field(array(
			"name" => "firstname",
			"caption" => t("Eesnimi"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "lastname",
			"caption" => t("Perenimi"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Algus"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("L&otilde;pp"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "package",
			"caption" => t("Pakett"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "password",
			"caption" => t("Parool"),
			"align" => "center",
		));
	}

	function _get_cust_entry($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cust_entry_t($t);

		for($i = 0; $i < 5; $i++)
		{
			$t->define_data(array(
				"firstname" => html::textbox(array(
					"name" => "d[$i][fn]",
					"size" => 10
				)),
				"lastname" => html::textbox(array(
					"name" => "d[$i][ln]",
					"size" => 10
				)),
				"email" => html::textbox(array(
					"name" => "d[$i][email]",
					"size" => 10
				)),
				"start" => html::date_select(array(
					"name" => "d[$i][start]",
					"format" => array("day_textbox", "month_textbox", "year_textbox")
/*					"day" => "text",
					"month" => "text",
					"year" => "text"*/
				)),
				"end" => html::date_select(array(
					"name" => "d[$i][end]",
					"format" => array("day_textbox", "month_textbox", "year_textbox")
/*					"day" => "text",
					"month" => "text",
					"year" => "text"*/
				)),
				"package" => html::select(array(
					"name" => "d[$i][package]",
					"options" => $this->_get_pk_list($arr["obj_inst"])
				)),
				"password" => html::textbox(array(
					"name" => "d[$i][pass]",
					"size" => 10
				)),
			));
		}
		$t->set_sortable(false);
	}

	function _set_cust_entry($arr)
	{
		$feedback = "";
		for($i = 0; $i < 5; $i++)
		{
			$d = $arr["request"]["d"][$i];
			if ($d["fn"] != "" && $d["ln"] != "" && $d["pass"] != "")
			{
				$start = date_edit::get_timestamp($d["start"]);
				$end = date_edit::get_timestamp($d["end"]);
				// create person, user, booking

				$eml = obj();
				$eml->set_class_id(CL_ML_MEMBER);
				$eml->set_name($d["fn"]." ".$d["ln"]." <".$d["email"].">");
				$eml->set_prop("name", $d["fn"]." ".$d["ln"]);
				$eml->set_prop("mail", $d["email"]);
				$eml->set_parent($arr["obj_inst"]->prop("persons_folder"));
				$eml->save();

				// check if person exists
				$ol = new object_list(array(
					"class_id" => CL_CRM_PERSON,
					"lang_id" => array(),
					"site_id" => array(),
					"parent" => $arr["obj_inst"]->prop("persons_folder"),
					"firstname" => $d["fn"],
					"lastname" => $d["ln"]
				));
				if ($ol->count())
				{
					$p = $ol->begin();
					$p_i = $p->instance();
					$user = $p_i->has_user($p);
				}
				else
				{
					$p = obj();
					$p->set_class_id(CL_CRM_PERSON);
					$p->set_parent($arr["obj_inst"]->prop("persons_folder"));
					$p->set_name($d["fn"]." ".$d["ln"]);
					$p->set_prop("firstname", $d["fn"]);
					$p->set_prop("lastname", $d["ln"]);
					$p->set_prop("email", $eml->id());
					$p->save();

					$cu = get_instance("crm/crm_user_creator");
					$uid = $cu->get_uid_for_person($p);

					$u = get_instance(CL_USER);
					$user = $u->add_user(array(
						"uid" => $uid,
						"email" => $d["email"],
						"password" => $d["pass"],
						"real_name" => $d["fn"]." ".$d["ln"]
					));


					$user->connect(array(
						"to" => $p->id(),
						"type" => "RELTYPE_PERSON"
					));
					$user->set_prop("email", $d["email"]);
					$user->set_prop("after_login_redir", $this->mk_my_orb("change", array(
							"id" => $arr["obj_inst"]->id(), "group" => "my_bookings"
						), CL_SPA_BOOKIGS_ENTRY));  
					$user->save();

					if ($arr["obj_inst"]->prop("user_group"))
					{
						$gr = get_instance(CL_GROUP);
						$gr->add_user_to_group($user, obj($arr["obj_inst"]->prop("user_group")));
					}
				}


				$booking = obj();
				$booking->set_parent($arr["obj_inst"]->prop("persons_folder"));
				$booking->set_name(sprintf("Broneering %s %s - %s", $d["fn"]." ".$d["ln"], date("d.m.Y", $start), date("d.m.Y", $end)));
				$booking->set_class_id(CL_SPA_BOOKING);
				$booking->set_prop("person", $p->id());
				$booking->set_prop("start", $start);
				$booking->set_prop("end", $end);
				$booking->set_prop("package", $d["package"]);
				$booking->save();

				$po = obj($d["packet"]);
				$feedback .= sprintf("Lisasin kasutaja %s, isiku %s ja <a href='%s'>broneeringu</a> paketile %s algusega %s ja l&otilde;puga %s<br>", 
					html::obj_change_url($user->id()),
					html::obj_change_url($p->id()),
					html::get_change_url($booking->id(), array("return_url" => $arr["request"]["post_ru"])),
					html::obj_change_url($po->id()),
					date("d.m.Y H:i", $start), 
					date("d.m.Y H:i", $end)
				);

				mail($d["email"], t("Teile on broneeritud aeg"), sprintf("Tere!\n\nOma broneeringu vaatamiseks klikkige siia: %s\nja sisestage kasutajanimi %s ja parool %s.\n\nHead kasutamist!", aw_ini_get("baseurl")."/login.aw", $user->prop("uid"), $d["pass"])); 
			}
		}
		$_SESSION["spa_bookings_entry_fb"] = $feedback;
	}

	function _get_cust_entry_fb($arr)
	{
		if ($_SESSION["spa_bookings_entry_fb"] == "")
		{
			return PROP_IGNORE;
		}
		$arr["prop"]["value"] = $_SESSION["spa_bookings_entry_fb"];
		unset($_SESSION["spa_bookings_entry_fb"]);
	}

	function _init_s_res(&$t)
	{
		$t->define_field(array(
			"name" => "person",
			"caption" => t("Isik"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "email",
			"caption" => t("E-post"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "start",
			"caption" => t("Alates"),
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("Kuni"),
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "package",
			"caption" => t("Pakett"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "add_p",
			"caption" => t("Lisa"),
			"align" => "center",
		));
	}

	function _get_s_res($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_s_res($t);

		foreach($this->get_search_results($arr["request"], $arr["obj_inst"]) as $item)
		{
			$t->define_data(array(
				"person" => html::obj_change_url($item->prop("person")),
				"email" => html::obj_change_url($item->prop("person.email")),
				"start" => $item->prop("start"),
				"end" => $item->prop("end"),
				"package" => html::obj_change_url($item->prop("package")) /*html::select(array(
					"name" => "p[".$item->id()."]",
					"options" => $this->_get_pk_list($arr["obj_inst"]),
					"value" => $item->prop("package")
				))*/,
				"change" => html::href(array(
					"url" => html::get_change_url($item->id(), array("return_url" => get_ru())),
					"caption" => t("Muuda")
				)),
				"add_p" => html::href(array(
					"url" => html::get_new_url(CL_SPA_BOOKING, $arr["obj_inst"]->prop("persons_folder"), array("return_url" => get_ru(), "from_b" => $item->id())),
					"caption" => t("Lisa samale isikule uus")
				))
			));
		}
	}

	function _get_pk_list($o)
	{
		static $pk_list;
		if (!is_array($pk_list))
		{
			$ot = new object_tree(array(
				"class_id" => CL_MENU,
				"parent" => reset($o->prop("packet_folder_list")),
				"lang_id" => array(),
				"site_id" => array()
			));
			$ol = new object_list(array(
				"class_id" => CL_SHOP_PACKET,
				"parent" => $ot->ids(),
				"lang_id" => array(),
				"site_id" => array()
			));
			$pk_list = $ol->names();
		}
		return $pk_list;
	}

	function get_search_results($r, $o)
	{
		$d = array(
			"class_id" => CL_SPA_BOOKING,
			"lang_id" => array(),
			"site_id" => array(),
			"createdby" => aw_global_get("uid")
		);
//echo dbg::dump($r);

		if ($r["s_fn"] != "")
		{
			$d["CL_SPA_BOOKING.person.firstname"] = "%".$r["s_fn"]."%";
		}
		if ($r["s_ln"] != "")
		{
			$d["CL_SPA_BOOKING.person.lastname"] = "%".$r["s_ln"]."%";
		}
		$from = date_edit::get_timestamp($r["s_date_from"]);
		$to = date_edit::get_timestamp($r["s_date_to"]);
		/*if ($from > 100 && $to > 100)
		{
			$d[] = new object_list_filter(array(
				"logic" => "AND",
				"conditions" => array(
					"start" => new obj_predicate_compare(OBJ_COMP_
				)
			));
		}
		else*/
		if ($from > 100)
		{
			$d["start"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $from);
		}
		else
		if ($to > 100)
		{
			$d["end"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $to);
		}

		if ($r["s_package"])
		{
			$d["s_package"] = $r["s_package"];
		}

//die(dbg::dump($d));
		if (count($d) > 3)
		{
			$ol = new object_list($d);
			return $ol->arr();
		}
		return array();
	}

	function _get_s_fn($arr)
	{
		$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
	}

	function _get_s_ln($arr)
	{
		$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
	}

	function _get_s_date_from($arr)
	{
		$arr["prop"]["value"] = date_edit::get_timestamp($arr["request"][$arr["prop"]["name"]]);
	}

	function _get_s_package($arr)
	{
		$arr["prop"]["value"] = date_edit::get_timestamp($arr["request"][$arr["prop"]["name"]]);
	}

	function _get_s_date_to($arr)
	{
		$arr["prop"]["value"] = date_edit::get_timestamp($arr["request"][$arr["prop"]["name"]]);
	}

	function _init_my_bookings(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Toode"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "when",
			"caption" => t("Millal"),
			"align" => "center"
		));
	}

	function _get_my_bookings($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_my_bookings($t);

		// get bookings for my person
		$p = get_current_person();
		if ($arr["request"]["group"] == "my_bookings")
		{
			$ol = new object_list(array(
				"class_id" => CL_SPA_BOOKING,
				"lang_id" => array(),
				"site_id" => array(),
				"person" => $p->id()
			));
		}
		else
		{
			$ol = new object_list(array(
				"class_id" => CL_SPA_BOOKING,
				"lang_id" => array(),
				"site_id" => array(),
				"createdby" => aw_global_get("uid"),
				"start" => new obj_predicate_compare(OBJ_COMP_GREATER, time())
			));
			
		}
		foreach($ol->arr() as $o)
		{
			// bookingul has package
			// package has products
			// rooms have products
			// so, list all the products in the package and for each product let the user select from all the rooms that have that package
			if (!$this->can("view", $o->prop("package")))
			{
				continue;
			}
			$package = obj($o->prop("package"));
			$pk = $package->instance();
			$dates = $o->meta("booking_dates");
			foreach($pk->get_products_for_package($package) as $prod)
			{
				$date = "";
				if ($dates[$prod->id()])
				{
					$sets = $dates[$prod->id()];
					$room = obj($sets["room"]);
					$date .= sprintf("Ruum %s, ajal %s - %s", $room->name(), date("d.m.Y H:i", $sets["from"]), date("d.m.Y H:i", $sets["to"]));
				}
				$date .= " ".html::popup(array(
					"url" => $this->mk_my_orb("select_room_booking", array("booking" => $o->id(), "prod" => $prod->id())),
					"caption" => t("M&auml;&auml;ra aeg"),
					"height" => 400,
					"width" => 550,
					"scrollbars" => 1,
					"resizable" => 1
				));
				$t->define_data(array(
					"booking" => $o->name(),
					"name" => $prod->name(),
					"when" => $date
				));
			}
		}

		$t->set_rgroupby(array("booking" => "booking"));
	}

	/**
		@attrib name=select_room_booking
		@param booking required type=int
		@param prod required type=int
	**/
	function select_room_booking($arr)
	{
		classload("core/date/date_calc");
		$html = "";

		// get date range from booking
		$b = obj($arr["booking"]);
		$from = $b->prop("start");
		$to = $b->prop("end");
		// split into weeks, and if more than 1, let the user select range
		$rs = get_week_start($from) + 24*7*3600;
 		if (($to - $rs) > (7*24*3600))
		{
			$ranges = array();
			$re = $to;
			$ranges[] = array("from" => $from, "to" => $rs);
			while ($rs < $re)
			{
				$ranges[] = array("from" => $rs, "to" => min($rs + 24*3600*7, $to));
				$rs += 24*3600*7;
			}
			$opts = array();
			foreach($ranges as $range)
			{
				$url = aw_url_change_var("range_from", $range["from"], aw_url_change_var("range_to", $range["to"]));
				$opts[$url] = date("d.m.Y", $range["from"])." - ".date("d.m.Y", $range["to"]);
			}
			$html .= html::select(array(
				"name" => "range_select",
				"options" => $opts,
			));
			if (!$_GET["range_from"])
			{
				$range_from = get_week_start($ranges[0]["from"]);
				$range_to = $ranges[0]["to"];
			}
		}
		else
		{
			if (!$_GET["range_from"])
			{
				$range_from = get_week_start($from);
				$range_to = $range_from+(24*3600*7);
			}
		}

		if ($_GET["range_from"])
		{
			$range_from = $_GET["range_from"];
			$range_to = $_GET["range_to"];
		}
		// now, draw table for the active range
		classload("vcl/table");
		$t = new aw_table();
		for ($i = 0; $i < 7; $i++)
		{
			$s = $range_from + ($i * 24 * 3600);
			if ($s < $from || $s > $to)
			{
				continue;
			}
			$t->define_field(array(
				"name" => "aa".$i,
				"caption" => date("d.m.Y", $range_from+($i*24*3600)),
				"align" => "center"
			));
		}

		// list all rooms and find the ones for this product
		$p_rooms = array();
		$rooms = new object_list(array(
			"class_id" => CL_ROOM,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($rooms->arr() as $room)
		{
			$pd = $room->meta("prod_data");
			if ($pd[$arr["prod"]]["active"])
			{
				$p_rooms[$room->id()] = $room;
			}
		}
		if (count($p_rooms) == 0)
		{
			die(t("Seda toodet ei ole v&otilde;imalik broneerida &uuml;htegi ruumi!"));
		}

		// get reservation length from product
		$prod_obj = obj($arr["prod"]);
		$prod_inst = $prod_obj->instance();
		$time_step = $prod_inst->get_reservation_length($prod_obj);
		$num_steps = (24*3600) / $time_step;

		$p = get_current_person();
		for ($h = 0; $h < $num_steps; $h++)
		{
			$d = array();
			for ($i = 0; $i < 7; $i++)
			{
				$d_from = 24*3600;
				$d_to = 0;

				$tmd = $h*$time_step;
				$tmd2 = ($h+1)*$time_step;

				$avail = false;
				foreach($p_rooms as $room)
				{
					$oh = $room->prop("openhours");
					if ($this->can("view", $oh))
					{
						$oh = obj($oh);
						$oh_i = $oh->instance();
						list($d_start, $d_end) = $oh_i->get_times_for_date($oh, $range_from+($i*24*3600)+$h*3600);
						$d_from = min($d_from, $d_start);
						$d_to = max($d_to, $d_end);
					}
					$room_inst = $room->instance();
					if ($room_inst->check_if_available(array("room" => $room->id(), "start" => $range_from+($i*24*3600)+$h*$time_step, "end" => $range_from+($i*24*3600)+($h+1)*$time_step)))
					{
						$avail = true;
					}
				}
				if ($h*$time_step < $d_from || $h*$time_step > $d_to)
				{
					continue;
				}
				$url = $this->mk_my_orb("make_reservation",array(
					"start" => $range_from+($i*24*3600)+$h*$time_step,
					"end" => $range_from+($i*24*3600)+($h+1)*$time_step,
					"prod" => $arr["prod"],
					"booking" => $arr["booking"],
				));
				if (!$avail)
				{
					$d["aa".$i] = t("Broneeritud");
				}
				else
				{
					$d["aa".$i] = html::href(array(
						"url" => $url,
						"caption" => sprintf("%02d:%02d-%02d:%02d", floor($tmd / 3600), floor(($tmd - floor($tmd / 3600)*3600) / 60), floor($tmd2 / 3600), floor(($tmd2 - floor($tmd2 / 3600)*3600) / 60))
					));
				}
			}
			if (count($d))
			{
				$t->define_data($d);
			}
		}
		$html .= $t->draw();
		return $html;
	}

	/**
		@attrib name=make_reservation
		@param start required type=int
		@param end required type=int
		@param prod required type=int
		@param booking required type=int
	**/
	function make_reservation($arr)
	{
		// list all rooms and find the ones for this product
		$p_rooms = array();
		$rooms = new object_list(array(
			"class_id" => CL_ROOM,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($rooms->arr() as $room)
		{
			$pd = $room->meta("prod_data");
			if ($pd[$arr["prod"]]["active"])
			{
				$p_rooms[$room->id()] = $room;
			}
		}
		if (count($p_rooms) == 0)
		{
			die(t("Seda toodet ei ole v&otilde;imalik broneerida &uuml;htegi ruumi!"));
		}

		// go over all rooms and the first one that is available, we book
		foreach($p_rooms as $room)
		{
			$room_inst = $room->instance();
			if ($room_inst->check_if_available(array("room" => $room->id(), "start" => $arr["start"], "end" => $arr["end"])))
			{
				$bron = obj($arr["booking"]);
				$dates = $bron->meta("booking_dates");
				if (!is_array($dates))
				{
					$dates = array();
				}
				$dates[$arr["prod"]] = array(
					"room" => $room->id(),
					"from" => $arr["start"],
					"to" => $arr["end"]
				);
				$bron->set_meta("booking_dates", $dates);
				$bron->save();
				$room_inst->make_reservation(array(
					"id" => $room->id(),
					"data" => array(
						"start" => $arr["start"],
						"end" => $arr["end"],
					)
				));
				return aw_ini_get("baseurl")."/automatweb/closewin.html";

			}
		}
		die(t("Vahepeal on valitud aeg broneeritud!"));
	}
}
?>
