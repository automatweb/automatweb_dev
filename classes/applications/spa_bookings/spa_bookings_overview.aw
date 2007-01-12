<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_bookings_overview.aw,v 1.8 2007/01/12 13:39:48 kristo Exp $
// spa_bookings_overview.aw - Reserveeringute &uuml;levaade 
/*

@classinfo syslog_type=ST_SPA_BOOKINGS_OVERVIEW relationmgr=yes no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property rooms_folder type=relpicker reltype=RELTYPE_RF field=meta method=serialize
	@caption Ruumide kaust

	@property owner type=relpicker reltype=RELTYPE_OWNER field=meta method=serialize
	@caption Omanik

	@property groups type=relpicker reltype=RELTYPE_GROUP field=meta method=serialize multiple=1
	@caption Kasutajagrupid

@default group=rooms

	@property r_tb type=toolbar store=no no_caption=1

	@layout r_split type=hbox width=20%:80%

		@layout r_left type=vbox parent=r_split

			@layout r_tree type=vbox closeable=1 area_caption=Ruumid parent=r_left
	
				@property rooms_tree type=treeview store=no no_caption=1 parent=r_tree
	
			@layout r_srch type=vbox closeable=1 area_caption=Otsing parent=r_left
			
				@property rs_name type=textbox store=no captionside=top parent=r_srch size=22
				@caption Ruumi nimi

				@property rs_booker_name type=textbox store=no captionside=top parent=r_srch size=22
				@caption Broneerija nimi

				@property rs_booking_from type=date_select store=no captionside=top parent=r_srch format=day_textbox,month_textbox,year_textbox
				@caption Broneering alates
			
				@property rs_booking_to type=date_select store=no captionside=top parent=r_srch format=day_textbox,month_textbox,year_textbox
				@caption Broneering kuni

				@property rs_btn type=submit store=no parent=r_srch no_caption=1
				@caption Otsi

		@property r_list type=table store=no no_caption=1 parent=r_split

@groupinfo rooms caption=Ruumid


@reltype RF value=1 clid=CL_MENU
@caption Ruumide kaust

@reltype OWNER value=2 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Omanik

@reltype GROUP value=3 clid=CL_GROUP
@caption Kasutajagrupp

*/

class spa_bookings_overview extends class_base
{
	function spa_bookings_overview()
	{
		$this->init(array(
			"tpldir" => "applications/spa_bookings/spa_bookings_overview",
			"clid" => CL_SPA_BOOKINGS_OVERVIEW
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
		$arr["args"]["rs_name"] = $arr["request"]["rs_name"];
		$arr["args"]["rs_booker_name"] = $arr["request"]["rs_booker_name"];
		$arr["args"]["rs_booking_from"] = $arr["request"]["rs_booking_from"];
		$arr["args"]["rs_booking_to"] = $arr["request"]["rs_booking_to"];
	}	

	function _get_rooms_tree($arr)
	{
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "rooms_ovtr",
				"persist_state" => true,
			),
			"root_item" => obj($arr["obj_inst"]->prop("rooms_folder")),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $arr["obj_inst"]->prop("rooms_folder")
			)),
			"var" => "tf"
		));
	}

	function _init_r_list(&$t)
	{
		$t->define_field(array(
			"name" => "cal",
			"caption" => t("&nbsp;"),
			"align" => "center",
			"sortable" => 1,
			"width" => 1
		));
		$t->define_field(array(
			"name" => "room",
			"caption" => t("Ruum"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "bron",
			"caption" => t("Broneering"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_r_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_r_list($t);

		$ol = new object_list();

		$from = date_edit::get_timestamp($arr["request"]["rs_booking_from"]);
		$to = date_edit::get_timestamp($arr["request"]["rs_booking_to"]);

		$srch = !empty($arr["request"]["rs_name"]) || !empty($arr["request"]["rs_booker_name"]) || $from > 1 || $to > 1 ;
		$room2booking = array();
		if ($srch)
		{
			$room2booking = array();
			$f = array(
				"class_id" => CL_ROOM,
				"lang_id" => array(),
				"site_id" => array()
			);
			if (!empty($arr["request"]["rs_name"]))
			{
				$f["name"] = "%".$arr["request"]["rs_name"]."%";
			}

			if (!empty($arr["request"]["rs_booker_name"]) || $from > 1 || $to > 1)
			{
				// get all bookings for that person
				$bf = array(
					"class_id" => CL_RESERVATION,
					"lang_id" => array(),
					"site_id" => array(),
				);
				if (!empty($arr["request"]["rs_booker_name"]))
				{
					$bf["CL_RESERVATION.customer.name"] = "%".$arr["request"]["rs_booker_name"]."%";
				}
				if ($from > 1)
				{
					$bf["start1"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $from);
				}
				else
				{
					$bf["start1"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, 1);
				}

				if ($to > 1)
				{
					$bf["end"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $to);
				}

				$bookings = new object_list($bf);
				$rooms = array();
				foreach($bookings->arr() as $booking)
				{
					$rooms[$booking->prop("resource")] = $booking->prop("resource");
					if (!empty($arr["request"]["rs_booker_name"]))
					{
						$room2booking[$booking->prop("resource")][] = $booking;
					}
				}
				if (count($rooms))
				{
					$f["oid"] = $rooms;
				}
				else
				{
					$f["oid"] = -1;
				}
			}
			$rf = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_RF");
			$flds = new object_tree(array("parent" => $rf->id(), "class_id" => CL_MENU, "lang_id" => array(), "site_id" => array()));
			$f["parent"] = $this->make_keys($flds->ids());
			$f["parent"][$rf->id()] = $rf->id();
			
		
			$f["sort_by"] = "objects.jrk";
			$ol = new object_list($f);
		}
		else
		{
			$rf = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_RF");
			$pt = $arr["request"]["tf"] ? $arr["request"]["tf"] : $rf->id();
			$ol = new object_list(array(
				"class_id" => CL_ROOM,
				"parent" => $pt,
				"lang_id" => array(),
				"site_id" => array(),
				"sort_by" => "objects.jrk"
			));
		}

		foreach($ol->arr() as $o)
		{
			$brons = array();
			foreach($room2booking[$o->id()] as $booking)
			{
				$brons[] = html::get_change_url($booking->id(), array("return_url" => get_ru()), $booking->prop_str("customer")." ".date("d.m.Y H:i", $booking->prop("start1"))." - ".date("d.m.Y H:i", $booking->prop("end")));
			}
			$t->define_data(array(
				"cal" => html::get_change_url($o->id(), array("return_url" => get_ru(), "group" => "calendar"), icons::get_icon($o)),
				"room" => html::get_change_url($o->id(), array("return_url" => get_ru(), "group" => "calendar"), $o->name()),
				"bron" => join("<br>", $brons),
				"oid" => $o->id()
			));
		}
		$t->set_sortable(false);
	}

	function _get_rs_name($arr)
	{
		$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
	}

	function _get_rs_booker_name($arr)
	{
		$arr["prop"]["value"] = $arr["request"][$arr["prop"]["name"]];
	}

	function _get_rs_booking_from($arr)
	{
		$v = date_edit::get_timestamp($arr["request"][$arr["prop"]["name"]]);
		if ($v < 1)
		{
			$v = mktime(0,0,0, date("m"), date("d")-1, date("Y"));
		}
		$arr["prop"]["value"] = $v;
	}

	function _get_rs_booking_to($arr)
	{
		$v = date_edit::get_timestamp($arr["request"][$arr["prop"]["name"]]);
		if ($v < 1)
		{
			$v = mktime(0,0,0, date("m"), date("d")+7, date("Y"));
		}
		$arr["prop"]["value"] = $v;
	}

	function _get_r_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_ROOM), $arr["request"]["tf"] ? $arr["request"]["tf"] : $arr["obj_inst"]->prop("rooms_folder"));
		$tb->add_delete_button();
		$tb->add_separator();
		$tb->add_button(array(
			"name" => "cut",
			"tooltip" => t("L&otilde;ika"),
			"img" => "cut.gif",
			"action" => "cut_b",
		));
		$tb->add_button(array(
			"name" => "copy",
			"tooltip" => t("Kopeeri"),
			"img" => "copy.gif",
			"action" => "copy_b",
		));
		if (is_array($_SESSION["spa"]["cut"]) && count($_SESSION["spa"]["cut"]))
		{
			$tb->add_button(array(
				"name" => "paste",
				"tooltip" => t("Kleebi"),
				"img	" => "paste.gif",
				"action" => "paste_b",
			));
		}
		$tb->add_separator();
		$url = $this->mk_my_orb("show_cals_pop", array("id" => $arr["obj_inst"]->id(), "rooms" => "0"));
		$tb->add_button(array(
			"name" => "cal",
			"tooltip" => t("Kalender"),
			"img" => "icon_cal_today.gif",
			"onClick" => "vals='';f=document.changeform.elements;l=f.length;num=0;for(i=0;i<l;i++){ if(f[i].name.indexOf('sel') != -1 && f[i].checked) {vals += ','+f[i].value;}};aw_popup_scroll('$url'+vals,'mulcal',700,500);return false;",
		));

		$tb->add_menu_button(array(
			"name" => "print",
			"img" => "print.gif",
		));
				
		$tb->add_sub_menu(array(
			"parent" => "print",
			"name" => "p_all_g",
			"text" => t("K&otilde;ik"),
		));

		// add yesterday/today/tomorrow subs
		$this->_add_day_subs($tb, "p_all_g", null);

		foreach(safe_array($arr["obj_inst"]->prop("groups")) as $grp_id)
		{
			$go = obj($grp_id);
			$tb->add_sub_menu(array(
				"parent" => "print",
				"text" => $go->name(),
				"name" => "g_".$grp_id
			));
			$this->_add_day_subs($tb, "g_".$grp_id , $grp_id);
		}

	}

	/**
		@attrib name=show_cals_pop
		@param id required
		@param rooms optional
	**/
	function show_cals_pop($arr)
	{
		classload("vcl/table");
		$html = "";
		$this->read_template("room_cals.tpl");
		$cals = "";
		$first = true;
		$roids = array();
		foreach(explode(",", $arr["rooms"]) as $room_id)
		{
			if ($this->can("view", $room_id))
			{
				$roids[] = $room_id;
			}
		}

		$r_ol = new object_list(array(
			"oid" => $roids,
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "objects.jrk"
		));
		foreach($r_ol->ids() as $room_id)
		{
			if ($first)
			{
				$ri = get_instance(CL_ROOM);
				$toolbar = get_instance("vcl/toolbar");
				$p = array(
					"vcl_inst" => &$toolbar,
				);
				$ri->_calendar_tb(array(
					"prop" => $p,
					"obj_inst" => obj($room_id)
				));

				$toolbar->add_menu_button(array(
					"name" => "print",
					"img" => "print.gif",
				));
				
				$toolbar->add_menu_item(array(
					"parent" => "print",
					"text" => t("K&otilde;ik"),
					"link" => $this->mk_my_orb("room_booking_printer", array(
						"rooms" => $r_ol->ids(),
						"from" => $_GET["start"],
						"to" => $_GET["end"]
					))
				));
				$oo = obj($arr["id"]);
				foreach(safe_array($oo->prop("groups")) as $grp_id)
				{
					$go = obj($grp_id);
					$toolbar->add_menu_item(array(
						"parent" => "print",
						"text" => $go->name(),
						"link" => $this->mk_my_orb("room_booking_printer", array(
							"rooms" => $r_ol->ids(),
							"from" => $_GET["start"],
							"to" => $_GET["end"],
							"group" => $grp_id
						))
					));
				}
				

				$this->vars(array(
					"toolbar" => $toolbar->get_toolbar(),
					"picker" => $ri->_get_calendar_select(array(
						"prop" => array(),
						"request" => array(
							"start" => $_GET["start"] ? $_GET["start"] : time()
						),
						"obj_inst" => obj($room_id)
					)),
					"reforb" => $this->mk_reforb("do_add_reservation", array(
						"id" => $room_id, 
						"set_view_dates" => "0",
						"post_ru" => get_ru()
					))
				));
			}

			$first = false;
			// show room cal
			$ro = obj($room_id);
			$ri = $ro->instance();
			$t = new aw_table;
			$prop = array(
				"vcl_inst" => &$t
			);
		
			$ri->_get_calendar_tbl(array(
				"room" => $room_id,
				"prop" => $prop
			));
			$this->vars(array(
				"cal" => html::get_change_url($ro->id(), array(
					"return_url" => get_ru(), 
					"group" => "calendar",
					"start" => $_GET["start"],
					"end" => $_GET["end"]
				), parse_obj_name($ro->name()))."<br>".$t->draw()
			));
			$cals .= $this->parse("CAL");
		}
		$this->vars(array(
			"CAL" => $cals,
			//"reforb" => $this->mk_reforb("do_add_reservation", array("id" => $arr["id"], "post_ru" => get_ru()), "room")
		));
		return $this->parse();
	}

	/**
		@attrib name=do_add_reservation all_args=1
	**/
	function do_add_reservation($arr)
	{
		if ($arr["set_view_dates"])
		{
			$start = date_edit::get_timestamp($arr["set_d_from"]);
                        if ($arr["set_view_dates"] == 1)
                        {
                                $end = date_edit::get_timestamp($arr["set_d_to"])+24*3600;
                        }
                        else
                        if ($arr["set_view_dates"] == 2)
                        {
                                 $end = $start + 24*3600;
                        }
                        else
                        if ($arr["set_view_dates"] == 3)
                        {
                                $end = $start + 24*3600*7;
                        }
                        else
                        if ($arr["set_view_dates"] == 4)
                        {
                                $end = $start + 24*3600*31;
                        }
			return aw_url_change_var("start",$start,aw_url_change_var("end",$end,$arr["post_ru"]));
		}
		$ri = get_instance(CL_ROOM);
		return $ri->do_add_reservation($arr);
	}

	/**
		@attrib name=room_booking_printer
		@param rooms required
		@param from optional
		@param to optional
		@param group optional
	**/
	function room_booking_printer($arr)
	{
		$this->read_any_template("booking_printer.tpl");

		// get all the bookings for the given rooms in the given timespan and group if set

		$days = ($arr["to"] - $arr["from"]) / (24 * 3600);

		if (!is_array($arr["rooms"]))
		{
			$arr["rooms"] = explode(",", $arr["rooms"]);
		}

		$r_inst = get_instance(CL_RESERVATION);
		$rs = "";
		foreach(safe_array($arr["rooms"]) as $room_id)
		{
			if (!$this->can("view", $room_id))
			{
				continue;
			}
			$ro = obj($room_id);
			$day_str = "";
			for($day = 0; $day < $days; $day++)
			{
				$from = $arr["from"] + ($day * 24*3600);
				$to = $from+(24*3600);
				$ft = array(
					"class_id" => CL_RESERVATION,
					"lang_id" => array(),
					"site_id" => array(),
					"resource" => $room_id,
					new obj_predicate_compare(OBJ_COMP_IN_TIMESPAN, array("start1", "end"), array($from, $to))
				);			
				if ($arr["group"])
				{
					// get group members
					$g = get_instance(CL_GROUP);
					$users = $g->get_group_members(obj($arr["group"]));
					$ft["createdby"] = array();
					foreach($users as $user)
					{
						$ft["createdby"][] = $user->prop("uid");
					}
				}
				$books = "";
				$reservation_ol = new object_list($ft);
				foreach($reservation_ol->arr() as $r)
				{
					$this->vars(array(
						"time_from" => date("H:i", $r->prop("start1")),
						"time_to" => date("H:i", $r->prop("end")),
						"customer" => $r->prop("customer.name"),
						"products" => $r_inst->get_products_text($r, " ")
					));
					$books .= $this->parse("BOOKING");
				}
				
				if ($books != "")
				{
					$this->vars(array(
						"BOOKING" => $books,
						"date" => date("d.m.Y", $from)
					));
					$day_str .= $this->parse("DAY");
				}
			}

			$this->vars(array(
				"DAY" => $day_str,
				"room_name" => $ro->trans_get_val("name"),
				"date_from" => date("d.m.Y", $arr["from"]),
				"date_to" => date("d.m.Y", $arr["to"]),
			));
			$rs .= $this->parse("ROOM");
		}

		$this->vars(array(
			"ROOM" => $rs
		));

		return $this->parse();
	}

	function _add_day_subs(&$tb, $pt, $grp)
	{
		$link = $this->mk_my_orb("room_booking_printer", array(
			"from" => get_day_start()-24*3600,
			"to" => get_day_start(),
			"group" => $grp
		));
		$tb->add_menu_item(array(
			"parent" => $pt,
			"text" => t("Eile"),
			"onClick" => "vals='&rooms=';f=document.changeform.elements;l=f.length;num=0;for(i=0;i<l;i++){ if(f[i].name.indexOf('sel') != -1 && f[i].checked) {vals += ','+f[i].value;}};aw_popup_scroll('$link'+vals,'mulcal',700,500);return false;",
		));

		$link = $this->mk_my_orb("room_booking_printer", array(
			"from" => get_day_start(),
			"to" => get_day_start()+24*3600,
			"group" => $grp
		));
		$tb->add_menu_item(array(
			"parent" => $pt,
			"text" => t("T&auml;na"),
			"onClick" => "vals='&rooms=';f=document.changeform.elements;l=f.length;num=0;for(i=0;i<l;i++){ if(f[i].name.indexOf('sel') != -1 && f[i].checked) {vals += ','+f[i].value;}};aw_popup_scroll('$link'+vals,'mulcal',700,500);return false;",
		));

		$link = $this->mk_my_orb("room_booking_printer", array(
			"from" => get_day_start()+24*3600,
			"to" => get_day_start()+24*3600+24*3600,
			"group" => $grp
		));
		$tb->add_menu_item(array(
			"parent" => $pt,
			"text" => t("Homme"),
			"onClick" => "vals='&rooms=';f=document.changeform.elements;l=f.length;num=0;for(i=0;i<l;i++){ if(f[i].name.indexOf('sel') != -1 && f[i].checked) {vals += ','+f[i].value;}};aw_popup_scroll('$link'+vals,'mulcal',700,500);return false;",
		));
	}
}
?>
