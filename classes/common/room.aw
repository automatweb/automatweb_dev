<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room.aw,v 1.7 2006/10/17 13:26:12 tarvo Exp $
// room.aw - Ruum 
/*

@classinfo syslog_type=ST_ROOM relationmgr=yes no_comment=1 no_status=1 prop_cb=1


@default table=objects
@default field=meta
@default method=serialize

# TAB GENERAL

@groupinfo general caption="&Uuml;ldine"
@default group=general

	@property general_tb type=toolbar no_caption=1

	@layout general_split type=hbox width=50%:50%

	@layout general_up type=vbox closeable=1 area_caption=&Uuml;ldinfo parent=general_split
	@default parent=general_up

		@property name type=textbox
		@caption Nimi

		@property location type=relpicker reltype=RELTYPE_LOCATION
		@caption Asukoht

		@property owner type=relpicker reltype=RELTYPE_OWNER
		@caption Omanik

		@property resources_fld type=relpicker reltype=RELTYPE_INVENTORY_FOLDER
		@caption Ressursside kataloog

		@property area type=relpicker reltype=RELTYPE_AREA
		@caption Valdkond

	@layout general_down type=vbox closeable=1 area_caption=Mahutavus&#44;&nbsp;kasutustingimused parent=general_split
	@default parent=general_down

		@property square_meters type=textbox
		@caption Suurus(ruutmeetrites)

		@property normal_capacity type=textbox
		@caption Normaalne mahutavus

		@property max_capacity type=textbox
		@caption Maksimaalne mahutavus

		@property conditions type=relpicker reltype=RELTYPE_CONDITIONS
		@caption Kasutustingimused

# TAB CALENDAR

@groupinfo calendar caption="Kalender" submit=no
@default group=calendar
	@property calendar_tb type=toolbar no_caption=1 submit=no
	@property calendar type=calendar no_caption=1 viewtype=relative store=no
# TAB IMAGES

@groupinfo images caption="Pildid"
@default group=images,parent=
	@property images_tb type=toolbar no_caption=1
	@property images_tbl type=table no_caption=1
	@property images_search type=hidden no_caption=1 store=no

# TAB PRICES

@groupinfo prices caption="Hinnad"
@default group=prices,parent=
	
	@groupinfo prices_general caption="&Uuml;ldine" parent=prices
	@default group=prices_general

		@property currency type=relpicker multiple=1 reltype=RELTYPE_CURRENCY
		@caption Valuuta

		@property price type=chooser multiple=1 ch_value=1
		@caption Hind

		@property time_unit type=chooser
		@caption Aja&uuml;hik

		@layout time_step type=hbox width=5%:5%:80%
		@caption Aja samm

			@property time_from type=textbox size=5 parent=time_step
			@caption Alates

			@property time_to type=textbox size=5 parent=time_step
			@caption kuni

			@property time_step type=textbox size=5 parent=time_step
			@caption , sammuga
	
	@groupinfo prices_price caption="Hinnad" parent=prices
	@default group=prices_price,prices_bargain_price
		@property prices_search type=hidden no_caption=1 store=no
		@property prices_tb type=toolbar no_caption=1
		@property prices_tbl type=table no_caption=1

	@groupinfo prices_bargain_price caption="Soodushinnad" parent=prices
	@default group=prices_bargain_price

# RELTYPES

@reltype LOCATION value=1 clid=CL_LOCATION
@caption Asukoht

@reltype OWNER value=2 clid=CL_CRM_COMPANY
@caption Omanik

@reltype INVENTORY_FOLDER value=3 clid=CL_MENU
@caption Ressursside kataloog

@reltype AREA value=4 clid=CL_CRM_FIELD_CONFERENCE_ROOM
@caption Valdkond

@reltype CONDITIONS value=5 clid=CL_DOCUMENT
@caption Kasutustingimused

@reltype IMAGE value=6 clid=CL_IMAGE
@caption Pilt

@reltype CURRENCY value=7 clid=CL_CURRENCY
@caption Valuuta

@reltype CALENDAR value=8 clid=CL_PLANNER
@caption Kalender

@reltype ROOM_PRICE value=9 clid=CL_ROOM_PRICE
@caption Ruumi hind
*/

class room extends class_base
{
	function room()
	{
		$this->init(array(
			"tpldir" => "common/room",
			"clid" => CL_ROOM
		));

		$this->unit_step = array(
			1 => t("minutit"),
			2 => t("tundi"),
			3 => t("p&auml;eva"),
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			
			# TAB PRICE
			case "price":
				$prop["options"] = array(
					1 => t("Inimese kohta"),
					2 => t("Kasutusaja kohta"),
				);
				break;
			
			case "calendar_tb":
				$this->_calendar_tb($arr);
				break;
			
			case "time_unit":
				$prop["options"] = array(
					1 => t("minutitites"),
					2 => t("tundides"),
					3 => t("p&auml;evades"),
				);
				break;
			# TAB CALENDAR
			case "calendar":
				### update schedule
				$prop["value"] = $this->create_room_calendar ($arr);
				break;
				
				
				//$cal = $this->get_calendar($arr["obj_inst"]->id());
/*				$c = &$prop["vcl_inst"];
				$c->add_item(array(
					"timestamp" => time(),
					"item_start" => time()-3000,
					"item_end" => time()+3000,
					"data" => array(
						"name" => "syndmus",
						"comment" => "haahaaa... comment",
						"icon" => "new.gif",
					),
				));
				$c->add_item(array(
					"timestamp" => time(),
					"item_start" => time()+86000,
					"item_end" => time()+90000,
					"data" => array(
						"name" => "syndmus",
						"comment" => "haahaaa... comment",
						"icon" => "new.gif",
					),
				));
				$c->configure(array(
					"overview_range" => 1,
				));
				$prop["value"] = "s"; //$c->draw_month();
*/				break;
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

	function callback_mod_retval($arr)
	{
		if($arr["request"]["images_search"])
		{
			$this->_handle_img_search_result(&$arr);
		}
		if($arr["request"]["prices_search"])
		{
			$this->_handle_prc_search_result(&$arr);
		}
		if($arr["request"]["img"])
		{
			$this->_save_img_ord(&$arr);
		}
	}

	function callback_pre_edit($arr)
	{
		if(!$this->get_calendar($arr["obj_inst"]->id()))
		{
			$this->_create_calendar($arr["obj_inst"]);
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//

	/**
		@attrib name=remove_images params=name all_args=1
	**/
	function remove_images($arr)
	{
		$o = obj($arr["id"]);
		if(count($arr["sel"]))
		{
			$o->disconnect(array(
				"from" => $arr["sel"]
			));
		}
		return $arr["post_ru"];
	}

	function _handle_img_search_result($arr)
	{
		$p = get_instance("vcl/popup_search");
		$p->do_create_rels(obj($arr["args"]["id"]), $arr["request"]["images_search"], 6);
	}
	function _handle_prc_search_result($arr)
	{
		$p = get_instance("vcl/popup_search");
		$p->do_create_rels(obj($arr["args"]["id"]), $arr["request"]["prices_search"], 9);
	}
	
	function _save_img_ord($arr)
	{
		$o = obj($arr["args"]["id"]);
		$o->set_meta("img_ord", $arr["request"]["img"]);
		$o->save();
	}

	function _get_images_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "add_image",
			"tooltip" => t("Lisa pilt"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("new", array(
				"parent" => $arr["obj_inst"]->id(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 6,
				"return_url" => get_ru(),
			), CL_IMAGE),
		));

		$popup_search = get_instance("vcl/popup_search");
		$search_butt = $popup_search->get_popup_search_link(array(
			"pn" => "images_search",
			"clid" => CL_IMAGE,
		));
		$tb->add_cdata($search_butt);

		$tb->add_button(array(
			"name" => "remove_image",
			"tooltip" => t("Eemalda pildid"),
			"img" => "delete.gif",
			"action" => "remove_images",
		));
		return PROP_OK;
		
	}

	function _get_images_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "sele",
			"width" => "20px",
		));
		$t->define_field(array(
			"name" => "image_ord",
			
			"caption" => t("jrk"),
			"width" => "20px",
		));
		$t->define_field(array(
			"name" => "image_name",
			"caption" => t("Pilt"),
		));
		$t->table_caption = t("Pildid");

		$imgs = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_IMAGE",
		));
		$ord = $arr["obj_inst"]->meta("img_ord");
		foreach($imgs as $c)
		{
			$img = $c->to();
			$t->define_data(array(
				"sele" => $img->id(),
				"image_ord" => html::textbox(array(
					"name" => "img[".$img->id()."]",
					"size" => "3",
					"value" => $ord[$img->id()],
				)),
				"image_name" => $img->name(),
			));
		}
	}

	function _get_prices_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$ba = ($arr["request"]["group"] == "prices_price")?false:true;
		$tb->add_button(array(
			"name" => "new",
			"tooltip" => t("Uus hind"),
			"img" => "new.gif",
			"url" => $this->mk_my_orb("new", array(
				"parent" => $arr["obj_inst"]->id(),
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 9, 
				"ba" => $ba,
				"return_url" => get_ru(),
			), CL_ROOM_PRICE),
		));

		$popup_search = get_instance("vcl/popup_search");
		$search_butt = $popup_search->get_popup_search_link(array(
			"pn" => "prices_search",
			"clid" => CL_ROOM_PRICE,
		));
		$tb->add_cdata($search_butt);

		$tb->add_button(array(
			"name" => "remove_image",
			"tooltip" => t("Eemalda hinnad"),
			"img" => "delete.gif",
			"action" => "remove_images",
		));
		return PROP_OK;
	}

	function _get_prices_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$ba = ($arr["request"]["group"] == "prices_price")?false:true;
		if($ba)
		{
			$t->define_field(array(
				"name" => "active",
				"caption" => t("Kehtib"),
				"align" => "center",
			));
			$t->define_field(array(
				"name" => "recur",
				"caption" => t("Kordub"),
				"align" => "center",
			));
		}
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "selected",
			"width" => "30px",
		));
		$t->define_field(array(
			"name" => "dates",
			"caption" => t("Kuup&auml;evad"),
		));
		$t->define_field(array(
			"name" => "weekdays",
			"caption" => t("N&auml;dalap&auml;evad"),
		));
		$t->define_field(array(
			"name" => "nr",
			"caption" => t("Mitmes"),
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Kellaaeg"),
		));
		if(!$ba)
		{
			$t->define_field(array(
				"name" => "time_step",
				"caption" => t("Aeg"),
			));
	
			$cur = $arr["obj_inst"]->prop("currency");
			foreach($cur as $c)
			{
				if(!is_oid($c))
				{
					continue;
				}
				$cobj = obj($c);
				$t->define_field(array(
					"name" => "currency_".$cobj->id(),
					"caption" => $cobj->prop("unit_name"),
				));
			}
		}
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"width" => "30px",
			"align" => "center",
		));


		$ds = get_instance("vcl/date_edit");
		$ds->configure(array(
			"year" => "year",
			"month" => "month",
			"day" => "day",
		));

		# getting data
		$price_objs = $this->get_prices($arr["obj_inst"]->id(), $ba);
		$price_inst = get_instance(CL_ROOM_PRICE);
		$caption = $this->unit_step[$arr["obj_inst"]->prop("time_unit")];
		foreach($price_objs as $oid => $obj)
		{
			$wd = $obj->prop("weekdays");
			$wds = array();
			foreach($wd as $nr)
			{
				$wds[$nr] = $price_inst->weekdays[$nr];
			}
			$prices = $price_inst->get_prices($oid);
			foreach($prices as $cur_oid => $price)
			{
				$prc["currency_".$cur_oid] = $price;
			}
			$t_from = $obj->prop("time_from");
			$t_to = $obj->prop("time_to");
			$data = array(
				"dates" => date("d/m/Y", $obj->prop("date_from"))." kuni ".date("d/m/Y", $obj->prop("date_to")),
				"time" => str_pad($t_from["hour"], 2, "0", STR_PAD_LEFT).":".str_pad($t_from["minute"], 2, "0", STR_PAD_LEFT)." - ".str_pad($t_to["hour"], 2, "0", STR_PAD_LEFT).":".str_pad($t_to["minute"], 2, "0", STR_PAD_LEFT),
				"weekdays" => join(",", $wds),
				"nr" => $obj->prop("nr"),
				"selected" => $oid,
				"time_step" => $obj->prop("time")." ".$caption,
				"active" => $obj->prop("active")?t("Jah"):t("Ei"),
				"recur" => $obj->prop("recur")?t("Jah"):t("Ei"),
				"change" => html::href(array(
					"caption" => html::img(array(
						"url" => aw_ini_get("baseurl")."/automatweb/images/icons/edit.gif",
						"border" => 0,
						"alt" => t("Muuda"),
					)),
					"url" => html::get_change_url($oid, array(
						"return_url" => get_ru(),
					)),
				)),
			);
			$data = array_merge($data, $prc);
			$t->define_data($data);
		}
	}

	function get_calendar($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		$cal = $o->get_first_obj_by_reltype("RELTYPE_CALENDAR");
		if(is_object($cal))
		{
			return $cal->id;
		}
		else 
		{
			return false;
		}
	}
	
	function get_overview($arr = array())
	{
		return $this->overview;
	}

	function _create_calendar($room)
	{
		$o = obj();
		$o->set_class_id(CL_PLANNER);
		$o->set_parent($room->id());
		$o->set_name("Ruumi '".$room->name()."' kalender");
		$o->save();
		$room->connect(array(
			"to" => $o->id(),
			"reltype" => "RELTYPE_CALENDAR",
		));
	}

	/**
		@comment
			returns array of CL_ROOM_PRICE objects
	**/
	function get_prices($oid, $bargain_prices = false)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		$cs = $o->connections_from(array(
			"class_id" => CL_ROOM_PRICE,
			"type" => "RELTYPE_ROOM_PRICE",
		));
		foreach($cs as $c)
		{
			$to = $c->to();
			if(($to->prop("type") == 2 && $bargain_prices) || ($to->prop("type") == 1 && !$bargain_prices))
			{
				$ret[$to->id()] = $to;
			}
		}
		return $ret;
	}
	
	function _calendar_tb($arr)
	{
		$arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR");
		if(is_object($arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR")))
		{
			$cal_obj = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_CALENDAR");
			$cal = $cal_obj->id();
			$parent = $cal_obj->prop("event_folder");
		}
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "new_reservation",
			"img" => "new.gif",
			"tooltip" => t("Broneering"),
			"url" => $this->mk_my_orb(
				"new", 
				array(
					"parent" => $parent,
					"return_url" => get_ru(),
					"calendar" => $cal,
		//			"alias_to" => $arr["obj_inst"]->id(),
	//				"reltype" => 9,
//					"alias_to_org" => $arr["obj_inst"]->prop("customer"),
//					"set_proj" => $arr["obj_inst"]->prop("project")
				),
				CL_RESERVATION
			)
		));
	}
	
	function create_room_calendar ($arr)
	{
		$this_object =& $arr["obj_inst"];

		$calendar = &$arr["prop"]["vcl_inst"];
		classload("vcl/calendar");
//		$calendar = new vcalendar (array ("tpldir" => "mrp_calendar"));
//		$calendar->init_calendar (array ());
//		$calendar->configure (array (
//			"overview_func" => array (&$this, "get_overview"),
//			"full_weeks" => true,
//		));
		$range = $calendar->get_range (array (
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));

		$list = new object_list(array(
			"class_id" => array(CL_RESERVATION),
//			"parent" => $this_object->id(),
			"resource" => $this_object->id(),
		));
		foreach($list->arr() as $task)
		{
			$calendar->add_item (array (
				"item_start" => $task->prop("start1"),
				"item_end" => $task->prop("end"),
				"data" => array(
					"name" => $task->name(),
					"link" => html::get_change_url($task->id(), array("return_url" => get_ru())),
				),
			));
			$this->cal_items[$task->prop("start1")] = html::get_change_url($task->id(), array("return_url" => get_ru()));
		}
		return $calendar->get_html ();
	}

	function _get_general_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		if($arr["obj_inst"]->prop("resources_fld"))
		{
			$tb->add_button(array(
				"name" => "add_resource",
				"tooltip" => t("Lisa ressurss"),
				"url" => $this->mk_my_orb("new", array(
					"mrp_workspace" => $arr["obj_inst"]->id(),
					"mrp_parent" => $arr["obj_inst"]->prop("resources_fld"),
					"return_url" => get_ru(),
					"parent" => $arr["obj_inst"]->prop("resources_fld"),
				), CL_MRP_RESOURCE),
				"img" => "new.gif",
			));
		}
	}
}
?>
