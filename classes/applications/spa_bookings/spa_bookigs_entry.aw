<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_bookigs_entry.aw,v 1.15 2007/01/11 11:54:12 kristo Exp $
// spa_bookigs_entry.aw - SPA Reisib&uuml;roo liides 
/*

@classinfo syslog_type=ST_SPA_BOOKIGS_ENTRY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general_sub

	@property name type=textbox field=name
	@caption Nimi

	@property packet_folder_list type=relpicker reltype=RELTYPE_PFL_FOLDER multiple=1 field=meta method=serialize
	@caption Tootepakettide kaustad

	@property user_group type=relpicker reltype=RELTYPE_GROUP field=meta method=serialize
	@caption Kasutajagrupp, millesse pannakse selle liidese kaudu sisestatud kasutajad

	@property persons_folder type=relpicker reltype=RELTYPE_PERSONS_FOLDER field=meta method=serialize
	@caption Isikute asukoht

	@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE field=meta method=serialize
	@caption Ladu

	@property print_view_ctr type=relpicker reltype=RELTYPE_PRINT_CTR field=meta method=serialize
	@caption Printvaate andmdete valideerimise kontroller

@default group=settings_mail

	@property b_send_mail_to_user type=checkbox ch_value=1 field=meta method=serialize
	@caption Saata kasutajale meil broneeringu tegemisel?

	@property b_mail_from_name type=textbox field=meta method=serialize
	@caption Meili from nimi

	@property b_mail_from_addr type=textbox field=meta method=serialize
	@caption Meili from aadress

	@property b_mail_subject type=textbox field=meta method=serialize
	@caption Meili subjekt

	@property b_mail_content type=textarea rows=10 cols=50 field=meta method=serialize
	@caption Meili sisu

	@property b_mail_legend type=text store=no 
	@caption Meili sisu legend


@default group=ppl_entry

	@property cust_entry_fb type=text store=no no_caption=1

	@property cust_entry type=table store=no no_caption=1


@default group=cust,all_bookings

	@layout ver_split type=hbox width=10%:90%

		@layout search type=vbox area_caption=Otsing parent=ver_split closeable=1

			@property s_fn type=textbox captionside=top store=no parent=search size=23
			@caption Eesnimi

			@property s_ln type=textbox captionside=top store=no parent=search size=23
			@caption Perenimi

			@property s_tb type=textbox captionside=top store=no parent=search size=23
			@caption Reisib&uuml;roo

			@property s_date_from type=date_select captionside=top store=no parent=search format=day_textbox,month_textbox,year_textbox
			@caption Alates

			@property s_date_to type=date_select captionside=top store=no parent=search format=day_textbox,month_textbox,year_textbox
			@caption Kuni

			@property s_date_not_set type=checkbox ch_value=1 captionside=top store=no parent=search no_caption=1
			@caption Ajad m&auml;&auml;ramata

			@property s_package type=select captionside=top store=no parent=search
			@caption Pakett

			@property s_btn type=submit store=no parent=search no_caption=1
			@caption Otsi

		@property s_res type=table no_caption=1 store=no parent=ver_split


@default group=my_bookings,my_bookings_agent

	@property my_bookings type=table store=no no_caption=1

@groupinfo general_sub caption="&Uuml;ldine" parent=general
@groupinfo settings_mail caption="Meiliseaded" parent=general

@groupinfo ppl_entry caption="Isikud"
@groupinfo cust caption="Kliendid" submit=no
@groupinfo my_bookings caption="Minu broneeringud" 
@groupinfo my_bookings_agent caption="Klientide broneeringud" 
@groupinfo all_bookings caption="K&otilde;ik" 

@reltype PFL_FOLDER value=1 clid=CL_MENU
@caption Tootepakettide kaust

@reltype GROUP value=2 clid=CL_GROUP
@caption Kasutajagrupp

@reltype PERSONS_FOLDER value=3 clid=CL_MENU
@caption Isikute asukoht

@reltype WAREHOUSE value=4 clid=CL_SHOP_WAREHOUSE
@caption Ladu

@reltype PRINT_CTR value=5 clid=CL_FORM_CONTROLLER
@caption Kontroller

*/

class spa_bookigs_entry extends class_base
{
	function spa_bookigs_entry()
	{
		$this->init(array(
			"tpldir" => "applications/spa_bookings/spa_bookings_entry",
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
		$arr["args"]["s_date_not_set"] = $arr["request"]["s_date_not_set"];
		$arr["args"]["s_tb"] = $arr["request"]["s_tb"];
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
				)),
				"end" => html::date_select(array(
					"name" => "d[$i][end]",
					"format" => array("day_textbox", "month_textbox", "year_textbox")
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
				//$bd = date_edit::get_timestamp($d["birthday"]);
				// create person, user, booking

				// check if person exists
				$ol = new object_list(array(
					"class_id" => CL_CRM_PERSON,
					"lang_id" => array(),
					"site_id" => array(),
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
					$eml = obj();
					$eml->set_class_id(CL_ML_MEMBER);
					$eml->set_name($d["fn"]." ".$d["ln"]." <".$d["email"].">");
					$eml->set_prop("name", $d["fn"]." ".$d["ln"]);
					$eml->set_prop("mail", $d["email"]);
					$eml->set_parent($arr["obj_inst"]->prop("persons_folder"));
					$eml->save();
	
					$p = obj();
					$p->set_class_id(CL_CRM_PERSON);
					$p->set_parent($arr["obj_inst"]->prop("persons_folder"));
					$p->set_name($d["fn"]." ".$d["ln"]);
					$p->set_prop("firstname", $d["fn"]);
					$p->set_prop("lastname", $d["ln"]);
					$p->set_prop("email", $eml->id());
					$p->set_prop("birthday", sprintf("%04d-%02d-%02d", $d["birthday"]["year"], $d["birthday"]["month"], $d["birthday"]["day"]));
					$p->set_prop("gender", $d["gender"]);
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
							"id" => $arr["obj_inst"]->id(), "group" => "my_bookings", "section" => "3169"
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

				$this->created_booking = $booking->id();

				// for this booking, create empty reservations for all products so we can search by them
				$booking_inst = $booking->instance();
				$booking_inst->check_reservation_conns($booking);

				$po = obj($d["packet"]);
				$feedback .= sprintf(t("Lisasin kasutaja %s, isiku %s ja <a href='%s'>broneeringu</a> paketile %s algusega %s ja l&otilde;puga %s<br>"), 
					is_admin() ? html::obj_change_url($user->id()) : $user->name(),
					is_admin() ? html::obj_change_url($p->id()) : $p->name(),
					is_admin() ? html::get_change_url($booking->id(), array("return_url" => $arr["request"]["post_ru"])) : "#",
					is_admin() ? html::obj_change_url($po->id()) : $po->name(),
					date("d.m.Y H:i", $start), 
					date("d.m.Y H:i", $end)
				);

				if ($arr["obj_inst"]->prop("b_send_mail_to_user"))
				{
					send_mail(
						$d["email"], 
						$arr["obj_inst"]->prop("b_mail_subject"), 
						str_replace(array("#uid#", "#pwd#", "#login_url#"), array($user->prop("uid"), $d["pass"], aw_ini_get("baseurl")."/login.aw"), $arr["obj_inst"]->prop("b_mail_content")),
						"From: ".$this->_get_from_addr($arr["obj_inst"])
					);
				}
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

	function _get_s_res($arr)
	{
		return $this->_get_my_bookings($arr);
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
			foreach($ol->arr() as $o)
			{
				$pk_list[$o->id()] = $o->trans_get_val("name");
			}
		}
		return $pk_list;
	}

	function get_search_results($r, $o)
	{
		$d = array(
			"class_id" => CL_SPA_BOOKING,
			"lang_id" => array(),
			"site_id" => array(),
		);

		$cnt = 3;
		if ($r["group"] == "cust")
		{
			$d["createdby"] = aw_global_get("uid");
			$cnt = 4;
		}

		if ($r["s_tb"] && !isset($d["createdby"]))
		{
			$d["createdby"] = "%".$r["s_tb"]."%";
		}

		if ($r["s_date_not_set"])
		{
			// we need to list all bookings that the person has not set times for
			$d[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_SPA_BOOKING.RELTYPE_ROOM_BRON.start1" => new obj_predicate_compare(OBJ_COMP_LESS, 1),
					"CL_SPA_BOOKING.RELTYPE_ROOM_BRON.end" => new obj_predicate_compare(OBJ_COMP_LESS, 1),
				)
			));
		}

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
		if ($from > 100 && $to > 100)
		{
			$d[] = new obj_predicate_compare(OBJ_COMP_IN_TIMESPAN, array("start", "end"), array($from, $to));
		}
		else
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
			$d["package"] = $r["s_package"];
		}

		if (count($d) > $cnt)
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

	function _get_s_tb($arr)
	{
		if ($arr["request"]["group"] == "cust")
		{
			return PROP_IGNORE;
		}
		$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
	}

	function _get_s_date_not_set($arr)
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
		$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
		$arr["prop"]["options"] = array("" => t("--vali--")) +  $this->_get_pk_list($arr["obj_inst"]);
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
			"align" => "right"
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
		if ($arr["request"]["group"] == "cust" || $arr["request"]["group"] == "all_bookings")
		{
			$ol = new object_list();
			$sr = $this->get_search_results($arr["request"], $arr["obj_inst"]);
			$ol->add($sr);

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
			$dates = $this->get_booking_data_from_booking($o);

			$booking_str = sprintf(t("Broneering %s, %s - %s, pakett %s"), html::href(array(
					"url" => "mailto:".$o->prop("person.email.mail"),
					"caption" => $o->prop("person.name")
				)),
				date("d.m.Y", $o->prop("start")),
				date("d.m.Y", $o->prop("end")),
				$o->prop("package.name")
			);
			$booking_str .= " ".html::popup(array(
				"url" => $this->mk_my_orb("add_prod_to_bron", array("bron" => $o->id(), "wb" => $arr["obj_inst"]->id())),
				"caption" => t("Lisa teenus"),
				"width" => 600,
				"height" => 400,
				"scrollbars" => 1,
				"resizable" => 1
			));

			$booking_str .= " / ".html::href(array(
				"url" => $this->mk_my_orb("print_booking", array("id" => $o->id(), "wb" => $arr["obj_inst"]->id())),
				"caption" => t("Prindi"),
				"target" => "_blank"
			));
			if ($arr["request"]["group"] == "cust")
			{
				$booking_str .= " ".html::get_change_url($o->id(), array("return_url" => get_ru()), t("Muuda"));
			}

			$fd = array();
			$has_unc = false;
			$prod_list = $pk->get_products_for_package($package);
			$grp_list = $pk->get_group_list($package);
			$grp_list[] = "__extra_items";
			foreach($grp_list as $prod_group)
			{
				// repeat group by the count of the first product in the group
				$prods_in_group = $pk->get_products_in_group($package, $prod_group);
				if ($prod_group == "__extra_items")
				{
					$prods_in_group = array();
					$extra_items = safe_array($o->meta("extra_prods"));
					foreach($extra_items as $extra_item_entry)
					{
						$prods_in_group[] = $extra_item_entry["prod"];
					}
				}
				$first_item_count = max(1,$prod_list[reset($prods_in_group)]);
				for ($i = 0; $i < $first_item_count; $i++)
				{
					$prod_str = array();
					$date = "";
					$date_booking_id = null;
					$prod2room = array();
					$prod2tm = array();
					foreach($prods_in_group as $prod_id)
					{
						$prod = obj($prod_id);
						foreach($dates as $_prod_id => $nums)
						{
							if ($_prod_id == $prod_id && isset($nums[$i]) && $nums[$i]["from"] > 1)
							{
								$sets = $nums[$i];
								$room = obj($sets["room"]);
								$prod2room[$_prod_id] = $room->id();
								$prod2tm[$_prod_id] = $sets["from"];
								$date .= sprintf("Ruum %s, ajal %s - %s", $room->name(), date("d.m.Y H:i", $sets["from"]), date("d.m.Y H:i", $sets["to"]));
								$date_booking_id = $sets["reservation_id"];
							}
						}
					}


					foreach($prods_in_group as $prod_id)
					{
						$prod = obj($prod_id);
						if ($date == "")
						{
							$prod_str[] = html::popup(array(
								"url" => $this->mk_my_orb("select_room_booking", array("booking" => $o->id(), "prod" => $prod_id, "prod_num" => "".$i, "section" => "3169", "pkt" => $package->id())),
								"caption" => $prod->name(),
								"height" => 500,
								"width" => 750,
								"scrollbars" => 1,
								"resizable" => 1
							));
						}
						else
						{
							$prod_str[] = $prod->name();
						}
					}

					if ($date != "")
					{
						$ri = get_instance(CL_ROOM);
						$settings = $ri->get_settings_for_room(obj($prod2room[$prod_id]));
						if ($ri->group_can_do_bron($settings, $prod2tm[$prod_id]))
						{
							$date .= " ".html::href(array(
								"url" => $this->mk_my_orb("clear_booking", array("return_url" => get_ru(), "booking" => $date_booking_id)),
								"caption" => t("T&uuml;hista")
							));
						}
					}
					else
					{
						$has_unc = true;
					}

					$fd[] = (array(
						"booking" => $booking_str,
						"name" => join("<br>", $prod_str),
						"when" => $date
					));
				}
			}

			if ($arr["request"]["s_date_not_set"] && !$has_unc)
			{
				continue;
			}
			foreach($fd as $row)
			{
				$t->define_data($row);
			}
		}

		$t->set_rgroupby(array("booking" => "booking"));
		$t->set_default_sortby("name");
	}

	/**
		@attrib name=select_room_booking
		@param booking required type=int
		@param prod required type=int
		@param prod_num required type=int
		@param pkt optional type=int
	**/
	function select_room_booking($arr)
	{
		classload("core/date/date_calc");
		$html = "";

		// get date range from booking
		$b = obj($arr["booking"]);
		$from = $b->prop("start");
		$to = $b->prop("end");
		$range_from = $from;
		$range_to = $to;
		// split into weeks, and if more than 1, let the user select range
		$rs = get_week_start($from) + 24*7*3600;
		// now, draw table for the active range
		classload("vcl/table");
		$t = new aw_table();
		$num_days = floor(($range_to - $range_from) / (24*3600)+1);
		for ($i = 0; $i < $num_days; $i++)
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

		$p_rooms = $this->get_rooms_for_product($arr["prod"]);
		if (count($p_rooms) == 0)
		{
			die(t("Seda toodet ei ole v&otilde;imalik broneerida &uuml;htegi ruumi!"));
		}

		get_instance(CL_ROOM);
		$room2inst = array();
		foreach($p_rooms as $room_id => $room_obj)
		{
			$room2inst[$room_id] = new room;
			$room2inst[$room_id]->generate_res_table($room_obj, $range_from, $range_to);
		}

		$reserved_days = $this->get_reserved_days_for_pkt($arr["pkt"], $range_from, $range_to, $arr["booking"], $current_booking);

		// get the current booking for this prod so we can ignore it in the taken checks
		$book_dates = $this->get_booking_data_from_booking($b);
		$current_booking = null;
		if (isset($book_dates[$arr["prod"]][$arr["prod_num"]]))
		{
			$current_booking = $book_dates[$arr["prod"]][$arr["prod_num"]]["reservation_id"];
		}

		// get reservation length from product
		$prod_obj = obj($arr["prod"]);
		$prod_inst = $prod_obj->instance();
		$time_step = $prod_inst->get_reservation_length($prod_obj);
		if ($time_step == 0)
		{
			die(sprintf(t("Tootele %s pole m&auml;&auml;ratud broneeringu pikkust!"), html::obj_change_url($prod_obj)));
		}
		$num_steps = (24*3600) / $time_step;

		$p = get_current_person();
		$settings_inst = get_instance(CL_ROOM_SETTINGS);
		$data = array();

		for ($h = 0; $h < $num_steps; $h++)
		{
			$d = array();
			for ($i = 0; $i < $num_days; $i++)
			{
				$s = $range_from + ($i * 24 * 3600);
				if ($s < $from || $s > $to)
				{
					continue;
				}
				
				$d_from = 24*3600;
				$d_to = 0;

				$tmd = $h*$time_step;
				$tmd2 = min(3600*24, ($h+1)*$time_step);
				$cur_step_start = $range_from+($i*24*3600)+$h*$time_step;
				$cur_step_end = $range_from+($i*24*3600)+($h+1)*$time_step;
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
						//arr(date("G:i" , $room_midday));
					
						if(!$room_midday)
						{
							$room_midday = $oh_i->get_midday($oh,$range_from+($i*24*3600)+$h*3600);
						}
						if(!$settings)
						{
							$settings = $settings_inst->get_current_settings($room->id());
						}
						
					}
					$room_inst = $room->instance();
					if ($room2inst[$room->id()]->check_if_available(array("room" => $room->id(), "start" => $cur_step_start, "end" => $cur_step_end)))
					{
						$avail = true;
					}
				}

				foreach($book_dates as $_book_prod => $_prod_nums)
				{
					foreach($_prod_nums as $_book_time)
					{
						if ($_book_time["from"] > 1 && timespans_overlap($cur_step_start, $cur_step_end, $_book_time["from"]-30*60, $_book_time["to"]+30*60))
						{
							$avail = false;
						}
					}
				}

				$date_str = date("d.m.Y", $range_from+($i*24*3600));
				if ($reserved_days[$date_str])
				{
					$avail = false;
				}
				if ($h*$time_step < $d_from || $h*$time_step > $d_to)
				{
					continue;
				}
				$url = $this->mk_my_orb("make_reservation",array(
					"start" => $cur_step_start,
					"end" => $cur_step_end,
					"prod" => $arr["prod"],
					"prod_num" => $arr["prod_num"],
					"booking" => $arr["booking"],
				));
				if (!$avail)
				{
					$d["aa".$i] = t("Broneeritud");
				}
				else
				{
					$tmd_h = floor($tmd / 3600);
					$tmd2_h = floor($tmd2 / 3600);
					$d["aa".$i] = html::href(array(
						"url" => $url,
						"caption" => sprintf("%02d:%02d-%02d:%02d", $tmd_h, floor(($tmd - $tmd_h*3600) / 60), $tmd2_h, floor(($tmd2 - $tmd2_h*3600) / 60))
					));
				}
			}
			if (count($d))
			{
				$data["".($tmd/3600).""] = $d;
				//$t->define_data($d);
			}
		}

		if(is_object($settings))
		{
			$available_for_user = $settings->prop("max_times_per_day");
		}
		if($available_for_user)
		{
			//sellesse lisab ainult vajaliku arvu vabu aegu
			$data2 = array();
			$available_before = (int)($available_for_user/2);
			$available_after = $available_for_user-$available_before;
			$midday_h = date("G" ,$room_midday);
			$booked_in_day = array(0,0,0,0,0,0,0);
			//otsib pooled vabad ajad peale keskpäeva
			foreach($data as $key => $dat)
			{
				foreach($dat as $day => $val)
				{
					if($key >= $midday_h && $booked_in_day[(int)$day[2]] < $available_after)
					{
						if(substr_count($val,"href"))
						{
							$booked_in_day[(int)$day[2]]++;
						}
						$data2[$key][$day] = $val;
					}
				}
			}
			//otsib ülejäänud vabad ajad enne keskpäeva
			krsort($data);
			foreach($data as $key => $dat)
			{
				foreach($dat as $day => $val)
				{
					if($key < $midday_h && $booked_in_day[(int)$day[2]] < $available_for_user)
					{
						if(substr_count($val,"href"))
						{
							$booked_in_day[(int)$day[2]]++;
						}
						$data2[$key][$day] = $val;
					}
				}
			}
			//juhul kui vabu aegu ei saand enne keskpäeva täis, siis vaatab igaks juhuks , äkki on peale lõunat veel vabu aegu
			ksort($data);
			foreach($data as $key => $dat)
			{
				foreach($dat as $day => $val)
				{
					if($key > $midday_h && $booked_in_day[(int)$day[2]] < $available_for_user)
					{
						if(substr_count($val,"href"))
						{
							$booked_in_day[(int)$day[2]]++;
						}
						$data2[$key][$day] = $val;
					}
				}
			}
			ksort($data2);
			$data = $data2;
			//arr($data2);
		}
		foreach($data as $d)
		{
			// skip all rows that either have all days empty or all days booked
			$has_free = false;
			foreach($d as $v)
			{
				if (strpos($v, "href") !== false)
				{
					$has_free = true;
				}
			}

			if ($has_free)
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
		@param prod_num required type=int
		@param booking required type=int
	**/
	function make_reservation($arr)
	{
		$arr["prod_num"] = (int)$arr["prod_num"];
		$p_rooms = $this->get_rooms_for_product($arr["prod"]);
		if (count($p_rooms) == 0)
		{
			die(t("Seda toodet ei ole v&otilde;imalik broneerida &uuml;htegi ruumi!"));
		}

		$bron = obj($arr["booking"]);

		// if there is a previous booking for the same package for the same product, then we need to remove that one first
		$cur_bookings = $this->get_booking_data_from_booking($bron);
		$current_booking = null;
		if (isset($cur_bookings[$arr["prod"]][$arr["prod_num"]]) && $this->can("view", $cur_bookings[$arr["prod"]][$arr["prod_num"]]["reservation_id"]))
		{
			$current_booking = $cur_bookings[$arr["prod"]][$arr["prod_num"]]["reservation_id"];
		}

		// go over all rooms and the first one that is available, we book
		foreach($p_rooms as $room)
		{
			$room_inst = $room->instance();
			if ($room_inst->check_if_available(array("room" => $room->id(), "start" => $arr["start"], "end" => $arr["end"])))
			{
				$rv_id = $room_inst->make_reservation(array(
					"id" => $room->id(),
					"res_id" => $current_booking,
					"data" => array(
						"start" => $arr["start"],
						"end" => $arr["end"],
						"customer" => $bron->prop("person"),
						"verified" => 1,
						"products" => array($arr["prod"] => 1)
					),
					"meta" => array(
						"product_for_bron" => $arr["prod"],
						"product_count_for_bron" => $arr["prod_num"]
					)
				));
				$rvo = obj($rv_id);
				$bron->connect(array(
					"to" => $rv_id,
					"type" => "RELTYPE_ROOM_BRON"
				));

				return aw_ini_get("baseurl")."/automatweb/closewin.html";

			}
		}
		die(t("Vahepeal on valitud aeg broneeritud!"));
	}

	/**
		@attrib name=print_booking
		@param id required
		@param wb required
	**/
	function print_booking($arr)
	{
		$b = obj($arr["id"]);
		$wb = obj($arr["wb"]);
		$this->read_site_template("booking.tpl");
		lc_site_load("spa_bookigs_entry", &$this);

		list($y, $m, $d) = explode("-", $b->prop("person.birthday"));
		$this->vars(array(
			"bureau" => $b->createdby(),
			"person" => $b->prop_str("person"),
			"package" => $b->prop_str("package"),
			"from" => date("d.m.Y", $b->prop("start")),
			"to" => date("d.m.Y", $b->prop("end")),
			"person_comment" => $b->prop("person.comment"),
			"person_name" => $b->prop("person.name"),
			"person_birthday" => sprintf("%02d.%02d.%04d", $d, $m, $y),
			"person_ext_id" => $b->prop("person.ext_id_alphanumeric"),
			"person_gender" => $b->prop("person.gender") == 1 ? t("Mees") : t("Naine")
		));

		// now, list all bookings for rooms 
		$dates = $this->get_booking_data_from_booking($b);
		$books = "";
		$items = array();
		foreach($dates as $prod => $entries)
		{
			foreach($entries as $entry)
			{
				$items[] = $entry;
			}
		}

		usort($items, create_function('$a,$b', 'return $a["from"] - $b["from"];'));
		foreach($items as $entry)
		{
			if ($entry["from"] < 1)
			{
				continue;
			}
			$ro = obj($entry["room"]);
			$rvs = obj($entry["reservation_id"]);
			$prod_obj = obj($rvs->meta("product_for_bron"));
			$this->vars(array(
				"r_from" => date("d.m.Y H:i", $entry["from"]),
				"r_to" =>  date("d.m.Y H:i", $entry["to"]),
				"r_room" => $ro->name(),
				"r_prod" => $prod_obj->name(),
				"start_time" => $entry["from"],
				"end_time" => $entry["to"],
			));
			$books .= $this->parse("BOOKING");
		}
		$this->vars(array(
			"BOOKING" => $books
		));

		if ($this->can("view", $wb->prop("print_view_ctr")))
		{
			$fc = get_instance(CL_FORM_CONTROLLER);
			$fc->eval_controller($wb->prop("print_view_ctr"), $arr);
		}
		die($this->parse());
	}

	function _get_b_mail_legend($arr)
	{
		$arr["prop"]["value"] = t("Meili sisus kasutatavad muutujad:<br>#uid# - kasutajanimi<br>#pwd# - parool<br>#login_url# - sisse logimise aadress<br>");
	}

	function _get_from_addr($o)
	{
		if ($o->prop("b_mail_from_name") != "")
		{
			return $o->prop("b_mail_from_name")." <".$o->prop("b_mail_from_addr").">";
		}
		return $o->prop("b_mail_from_addr");
	}

	function get_booking_data_from_booking($o)
	{
		$dates = array();
		foreach($o->connections_from(array("type" => "RELTYPE_ROOM_BRON")) as $conn)
		{
			$room_bron = $conn->to();
			if ($room_bron->meta("product_for_bron"))
			{
				$dates[$room_bron->meta("product_for_bron")][$room_bron->meta("product_count_for_bron")] = array(
					"room" => $room_bron->prop("resource"),
					"from" => $room_bron->prop("start1"),
					"to" => $room_bron->prop("end"),
					"reservation_id" => $room_bron->id()
				);
			}
		}
		return $dates;
	}

	function get_rooms_for_product($prod)
	{
		// list all rooms and find the ones for this product
		$p_rooms = array();
		$rooms = new object_list(array(
			"class_id" => CL_ROOM,
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "objects.jrk"
		));
		foreach($rooms->arr() as $room)
		{
			$pd = $room->meta("prod_data");
			if ($pd[$prod]["active"])
			{
				$p_rooms[$room->id()] = $room;
			}
		}
		return $p_rooms;
	}

	function get_reserved_days_for_pkt($pkt, $range_from, $range_to, $booking, $current_booking = null)
	{
		$bo = obj($booking);
		$conn_ol = new object_list($bo->connections_from(array("type" => "RELTYPE_ROOM_BRON")));
		$rv_ids = $this->make_keys($conn_ol->ids());
		if (!count($rv_ids))
		{
			return array();
		}
		
		$pkt = obj($pkt);
		$reserved_days = array();
		if ($pkt->prop("max_usage_in_time") > 0)
		{
			// get reservations in the selected timespan. 
			// if on some days the count is over the edge
			// block that day
			$filt = array(
				"class_id" => CL_RESERVATION,
				"oid" => $rv_ids,
				"lang_id" => array(),
				"site_id" => array(),
				"start1" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ,$range_from),
				"end" => new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ,$range_to),
			);
			if ($current_booking)
			{
				unset($filt["oid"][$current_booking]);
			}
			$rvs = new object_list($filt);
			$count_by_day = array();
			foreach($rvs->arr() as $rv)
			{
				$count_by_day[date("d.m.Y", $rv->prop("start1"))]++;
			}

			foreach($count_by_day as $date => $count)
			{
				if ($count >= $pkt->prop("max_usage_in_time"))
				{
					$reserved_days[$date] = 1;
				}
			}
		}
		return $reserved_days;
	}

	/**
		@attrib name=clear_booking
		@param return_url required
		@param booking required type=int 
	**/
	function clear_booking($arr)
	{
		$b = obj($arr["booking"]);
		$b->set_prop("start1", -1);
		$b->set_prop("end", -1);
		$b->save();
		return $arr["return_url"];
	}

	/**
		@attrib name=add_prod_to_bron
		@param bron required type=int acl=edit
		@param wb required type=int acl=edit
	**/
	function add_prod_to_bron($arr)
	{
		$this->read_template("treetable.tpl");

		$this->vars(array(
			"tree" => $this->_get_prod_fld_tree($arr),
			"list" => $this->_get_prod_list_tbl($arr)
		));
		return $this->parse();
	}

	function _get_prod_fld_tree($arr)
	{
		$o = obj($arr["wb"]);
		$wh = obj($o->prop("warehouse"));
		$wh_i = $wh->instance();
		$p = array(
			"obj_inst" => $wh
		);
		$wh_i->_init_view($p);
		return $wh_i->_prod_list_tree($p);
	}

	function _get_prod_list_tbl($arr)
	{
		classload("vcl/table");
		$t = new aw_table();

		if (!$_GET["tree_filter"])
		{
			$ot = new object_list();
		}
		else
		{
			$ot = new object_list(array(
				"parent" => $_GET["tree_filter"],
				"class_id" => array(CL_MENU,CL_SHOP_PRODUCT),
				"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
			));
		}

		$t->define_field(array(
			"name" => "prod",
			"caption" => t("Vali toode"),
			"align" => "center"
		));
		foreach($ot->arr() as $o)
		{
			$arr["prod"] = $o->id();
			$t->define_data(array(
				"prod" => html::href(array(
					"caption" => parse_obj_name($o->name()),
					"url" => $this->mk_my_orb("fin_add_prod_to_bron", $arr)
				)),
				"ord" => $o->ord()
			));
		}
		$t->set_default_sortby("ord");
		$t->sort_by();
		return $t->draw();
	}

	/**
		@attrib name=fin_add_prod_to_bron
		@param bron required type=int acl=edit
		@param wb required type=int acl=edit
		@param prod required type=int acl=view
	**/
	function fin_add_prod_to_bron($arr)
	{
		$bron = obj($arr["bron"]);
		
		$bron->connect(array(
			"to" => $arr["prod"],
			"type" => "RELTYPE_EXTRA_PROD"
		));

		// akso make a new room reservation object for the extra thingie
		$rooms = $this->get_rooms_for_product($arr["prod"]);
		if (count($rooms))
		{
			$room_inst = get_instance(CL_ROOM);
			$rv_id = $room_inst->make_reservation(array(
				"id" => reset(array_keys($rooms)),
				"data" => array(
					"customer" => $bron->prop("person")
				),
				"meta" => array(
					"product_for_bron" => $arr["prod"],
					"product_count_for_bron" => 0
				)
			));
			$bron->connect(array(
				"to" => $rv_id,
				"type" => "RELTYPE_ROOM_BRON"
			));

			$extra_prods = safe_array($bron->meta("extra_prods"));
			$extra_prods[] = array(
				"prod" => $arr["prod"],
				"reservation" => $rv_id
			);
			$bron->set_meta("extra_prods", $extra_prods);
			$bron->save();
		}

		return aw_ini_get("baseurl")."/automatweb/closewin.html";
	}

	/**
		@attrib name=enter_cust_data_pop
		@param bron required type=int acl=edit
		@param props optional
	**/
	function enter_cust_data_pop($arr)
	{
		classload("cfg/htmlclient");
		$htmlc = new htmlclient(array(
			'template' => "default",
		));
		$htmlc->start_output();
		$htmlc->add_property(array(
			"caption" => t("Sisesta kasutaja puuduvad andmed"),
		));

		$tmp = obj();
		$tmp->set_class_id(CL_CRM_PERSON);
		$propl = $tmp->get_property_list();
	
		$bron = obj($arr["bron"]);
		foreach(safe_array($arr["props"]) as $propertyn)
		{
			$capt = $propl[$propertyn]["caption"];
			switch($propertyn)
			{
				case "phone":
					$capt = t("Telefon");
					break;
			}
			$val = $bron->prop("person.".$propertyn.".name");
			if ($val == "")
			{
				$val = $bron->prop("person.".$propertyn);
			}
			$type = "textbox";
			switch($propl[$propertyn]["type"])
			{
				case "date_select":
					$type="date_select";
					break;

				case "chooser":
					$type="chooser";
					$i = get_instance(CL_CRM_PERSON);
					$p = array(
						"obj_inst" => obj($bron->prop("person")),
						"prop" => &$propl[$propertyn]
					);
					$i->get_property($p);
					$opts = $p["prop"]["options"];
					break;
			}

			$htmlc->add_property(array(
				"name" => "ud[$propertyn]",
				"type" => $type,
				"caption" => $capt,
				"value" => $val,
				"options" => $opts,
				"year_from" => 1900,
				"year_to" => date("Y")
			));
		}

		$htmlc->add_property(array(
			"name" => "s[submit]",
			"type" => "submit",
			"value" => "Salvesta",
			"class" => "sbtbutton"
		));

		$htmlc->finish_output(array(
			"action" => "save_cust_data_pop",
			"method" => "POST",
			"data" => array(
				"id" => $arr["id"],
				"orb_class" => "spa_bookigs_entry",
				"reforb" => 0,
				"props" => $arr["props"],
				"bron" => $arr["bron"]
			)
		));

		return $htmlc->get_result();
	}

	/**
		@attrib name=save_cust_data_pop all_args=1
	**/
	function save_cust_data_pop($arr)
	{
		$arr = $_POST;
		$bron = obj($arr["bron"]);
		if (!$this->can("view", $bron->prop("person")))
		{
			$cust = obj();
			$cust->set_class_id(CL_CRM_PERSON);
			$cust->set_parent($bron->id());
			$cust->save();
			$bron->set_prop("person", $cust->id());
			$bron->save();
		}
		else
		{
			$cust = obj($bron->prop("person"));
		}

		$tmp = obj();
		$tmp->set_class_id(CL_CRM_PERSON);
		$propl = $tmp->get_property_list();

		foreach(safe_array($arr["props"]) as $pn)
		{
			if ($propl[$pn]["type"] == "date_select")
			{
				if ($arr["ud"][$pn]["year"] < 100)
				{
					$arr["ud"][$pn] = "";
				}
				else
				{
					$arr["ud"][$pn] = sprintf("%04d-%02d-%02d", $arr["ud"][$pn]["year"], $arr["ud"][$pn]["month"], $arr["ud"][$pn]["day"]);
				}
			}
			switch($pn)
			{
				case "name":
					$cust->set_name($arr["ud"][$pn]);
					break;

				case "phone":
					if ($this->can("view", $cust->prop("phone")))
					{
						$ph = obj($cust->prop("phone"));
					}
					else
					{
						$ph = obj();
						$ph->set_parent($cust->id());
						$ph->set_class_id(CL_CRM_PHONE);
					}
					$ph->set_name($arr["ud"][$pn]);
					$ph->save();
					if (!$this->can("view", $cust->prop("phone")))
					{
						$cust->connect(array(
							"to" => $ph->id(),
							"type" => "RELTYPE_PHONE"
						));
						$cust->set_prop("phone", $ph->id());
					}
					break;

				default:
					$cust->set_prop($pn, $arr["ud"][$pn]);
					break;
			}
		}
		$cust->save();

		return aw_ini_get("baseurl")."/automatweb/closewin.html";
	}
}
?>
