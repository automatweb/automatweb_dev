<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room.aw,v 1.26 2006/10/26 14:37:36 markop Exp $
// room.aw - Ruum 
/*

@classinfo syslog_type=ST_ROOM relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default field=meta
@default method=serialize

# TAB GENERAL

@groupinfo general caption="&Uuml;ldine"
@default group=general

	@layout general_split type=hbox width=50%:50%

	@layout general_up type=vbox closeable=1 area_caption=&Uuml;ldinfo parent=general_split
	@default parent=general_up

		@property name type=textbox field=name method=none
		@caption Nimi

		@property location type=relpicker reltype=RELTYPE_LOCATION
		@caption Asukoht

		@property owner type=relpicker reltype=RELTYPE_OWNER
		@caption Omanik

		@property warehouse type=relpicker reltype=RELTYPE_SHOP_WAREHOUSE
		@caption Ladu
		
		@property resources_fld type=hidden reltype=RELTYPE_INVENTORY_FOLDER no_caption=1
		@caption Ressursside kataloog

		@property area type=relpicker reltype=RELTYPE_AREA
		@caption Valdkond

		@property reservation_template type=select
		@caption Broneeringu template

	@layout general_down type=vbox closeable=1 area_caption=Mahutavus&#44;&nbsp;kasutustingimused parent=general_split
	@default parent=general_down
		
		@property conditions type=relpicker reltype=RELTYPE_CONDITIONS
		@caption Kasutustingimused
		
		@property square_meters type=textbox size=5
		@caption Suurus(ruutmeetrites)

		@property normal_capacity type=textbox size=5
		@caption Normaalne mahutavus

		@property max_capacity type=textbox size=5
		@caption Maksimaalne mahutavus

		@property buffer_before type=textbox size=5
		@caption Puhveraeg enne
		
		@property buffer_after type=textbox size=5
		@caption Puhveraeg p&auml;rast
		
		
valdkonnanimi (link, mis avab popupi, kuhu saab lisada vastava valdkonnaga seonduva täiendava info selle valdkonna objektitüübi kaudu, nt konverentsid).
- puhveraeg enne (mitu tundi enne reserveeringu algust lisaks bronnitakse ruumide ettevalmistamiseks)
- puhveraeg pärast (mitu tundi peale reserveeringu lõppu broneeritakse ruumide korrastamiseks

# TAB CALENDAR

@groupinfo calendar caption="Kalender" submit=no
@default group=calendar
	@property calendar_tb type=toolbar no_caption=1 submit=no
	@property calendar type=calendar no_caption=1 viewtype=relative store=no
	@property calendar_tbl type=table no_caption=1

#TAB RESOURCES
@groupinfo resources caption="Ressursid"
@default group=resources

	@property resources_tb type=toolbar no_caption=1
	@property resources_tbl type=table no_caption=1

# TAB IMAGES

@groupinfo images caption="Pildid"
@default group=images,parent=
	@property images_tb type=toolbar no_caption=1
	@property images_tbl type=table no_caption=1
	@property images_search type=hidden no_caption=1 store=no

# TAB PRODUCTS
@groupinfo products caption="Tooted"
@default group=products
	@property products_tb type=toolbar no_caption=1 store=no	

	@layout products_l type=hbox width=30%:70%
		
		@layout products_left type=vbox parent=products_l
		
		@layout products_tree type=vbox parent=products_left closeable=1 area_caption=Toodete&nbsp;puu
			@property products_tr type=treeview no_caption=1 store=no parent=products_tree
	
		@layout products_find_params type=vbox parent=products_left closeable=1 area_caption=Toodete&nbsp;otsing
			@property products_find_product_name type=textbox store=no parent=products_find_params captionside=top size=10
			@caption Toote nimetus
			@property do_find_products type=submit store=no parent=products_find_params no_caption=1
			@caption Otsi
	@property products_tbl type=table no_caption=1 store=no parent=products_l

# TAB PRICES

@groupinfo prices caption="Hinnad"
@default group=prices,parent=
	
	@groupinfo prices_general caption="&Uuml;ldine" parent=prices
	@default group=prices_general

		@property currency type=relpicker multiple=1 reltype=RELTYPE_CURRENCY
		@caption Valuuta

		@property price type=chooser multiple=1 ch_value=1
		@caption Hind

		@property price_per_face_if_too_many type=textbox size=5
		@caption Lisanduv hind &uuml;le normaalse mahutavuse oleva inimese kohta
		

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

@reltype SHOP_WAREHOUSE value=10 clid=CL_SHOP_WAREHOUSE
@caption Ruumi hind

reltype TEMPLATE value=11 clid=CL_SHOP_WAREHOUSE
caption Ruumi hind

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
		$this->step_lengths = array(
			"" => 3600, //default
			1 => 60,
			2 => 3600,
			3 => 86400,
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
				return PROP_IGNORE;
				$prop["value"] = $this->create_room_calendar ($arr);
				break;
			case "calendar_tbl":
				$this->_get_calendar_tbl($arr);
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
*/				case "products_tr":
					if(!$arr["obj_inst"]->prop("resources_fld"))
					{
						$prop["error"] = t("Pole valitud lao toodete kataloogi");
						return PROP_ERROR;
					}
					$this->_products_tr($arr);
					break;	
				case "reservation_template":
					$tm = get_instance("templatemgr");
					$prop["options"] = $tm->template_picker(array(
						"folder" => "common/room"
					));
					if(!sizeof($prop["options"]))
					{//arr($prop);
						$prop["caption"] .= t("\n".$this->site_template_dir."");
		//				$prop["type"] = "text";
		//				$prop["value"] = t("Template fail peab asuma kataloogis :".$this->site_template_dir."");
					}
					break;
				
				case "products_tbl":
					$this->_products_tbl($arr);
					break;
				case "products_tb":
					$this->_products_tb($arr);
					break;		
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$doc = obj(9314);
		$doc->connect(array(
			"to" => $arr["obj_inst"]->id(),
			"reltype" => "RELTYPE_ALIAS",
		));
	
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "products_find_product_name":
				
				if($arr["request"]["sel_imp"]);
				if($arr["request"]["products_find_product_name"])
				{
					$arr["obj_inst"]->set_meta("search_data" , $arr["request"]);
				}
				break;
			case "resources_fld":
				if(is_oid($arr["request"]["warehouse"]) && $this->can("view" ,$arr["request"]["warehouse"]))
				{
					$warehouse = obj($arr["request"]["warehouse"]);
					if(is_oid($warehouse->prop("conf")) && $this->can("view" ,$warehouse->prop("conf")))
					{
						$warehouse->config = obj($warehouse->prop("conf"));
						$prop["value"] = $warehouse->config->prop("prod_fld");
					}
				}
				break;
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
		/*
		$o = obj(1331);
		$o->set_prop("started", time()-7200);
		$o->set_prop("finished", time()+7200);
		$o->save();
		*/
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
			return $cal->id();
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
			"action" => "do_add_reservation",
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
/*	
	function create_room_calendar ($arr)
	{
		$this_object =& $arr["obj_inst"];

		$calendar = &$arr["prop"]["vcl_inst"];
		classload("vcl/calendar");
//		$calendar = new vcalendar (array ("tpldir" => "mrp_calendar"));
//		$calendar->init_calendar (array ());
		$calendar->configure (array (
			"overview_func" => array (&$this, "get_overview"),
			"full_weeks" => true,
		));
		$range = $calendar->get_range (array (
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));

		$list = new object_list(array(
			"class_id" => array(CL_RESERVATION),
//			"parent" => $this_object->id(),
			"resource" => $this_object->id(),
		));
		
		$calendar->init_output(array("event_template" => "my_new_templ.tpl"));
		foreach($list->arr() as $task)
		{
			$calendar->add_item (array (
				"item_start" => $task->prop("start1"),
				"item_end" => $task->prop("end"),
				"data" => array(
					"name" => $task->name(),
					"link" => html::get_change_url($task->id(), array("return_url" => get_ru())),
					"comment" => "asd",
					"utextarea1" => "asdd",
				),
			));
			$this->cal_items[$task->prop("start1")] = html::get_change_url($task->id(), array("return_url" => get_ru()));
		}
		return $calendar->get_html ();
	}
	*/

	function _get_calendar_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_calendar_t($t);
		
		$reservations = new object_list(array(
			"class_id" => array(CL_RESERVATION),
			"lang_id" => array(),
			"resource" => $arr["obj_inst"]->id(),
			1 => new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"start1" => new obj_predicate_compare(OBJ_COMP_BETWEEN, (time()-86400), (time()+86400* 7)),
					"end" => new obj_predicate_compare(OBJ_COMP_BETWEEN, (time()-86400), (time()+86400* 7))
				)
			)),
		));
		$booked = array();
		//et siis broneeringud ühte massiivi... ei suut paremat moment välja mõelda kui et võrdleb pärast kõik elemendid läbi
		foreach($reservations->arr() as $res)
		{
			//date("l d/m/Y", time())
			$booked[] = array("start" => $res->prop("start1"), "end" => $res->prop("end"));
		}
	//	arr($booked);
		$today_start = mktime(0, 0, 0, date("m", time()), date("d", time()), date("y", time()));
	
		$step = 0;
		
		$step_length = $this->step_lengths[$arr["obj_inst"]->prop("time_unit")];
		
		while($step < 86400/($step_length * $arr["obj_inst"]->prop("time_step")))
		{
//			$today_start = mktime(0, 0, 0, date("m", time()), date("d", time()), date("y", time()));
			$d = $col = array();
			$x = 0;
			$start_step = $today_start + $step * $step_length * $arr["obj_inst"]->prop("time_step");
			$end_step = $start_step + $step_length * $arr["obj_inst"]->prop("time_step");
			while($x<7)
			{
				$d[$x] = html::checkbox(array("name"=>'bron['.$start_step.']' , "value" =>'1')).t("Vaba");
				$col[$x] = "";
				foreach($booked as $b)
				{
					if((($b["start"] <= $start_step) &&  ($start_step < $b["end"])) || (($b["start"] < $end_step) && ($b["end"] > $end_step)) || (($b["start"] < $end_step) && ($b["start"] >= $start_step)))
					{
						$d[$x] = t("BRON");
						$col[$x] = "red";
						break;
					}
				}
				$x++;
				$start_step += 86400;
				$end_step += 86400;
//				$today_start += 86400;
			}
			$t->define_data(array(
				"time" => date("G:i" , $today_start+ $step*$step_length*$arr["obj_inst"]->prop("time_step"))." - ".date("G:i" , $today_start+ ($step+1)*$step_length*$arr["obj_inst"]->prop("time_step")), //$step.":00-".($step + $arr["obj_inst"]->prop("time_step")).":00".html::hidden(array("name" => "step" , "value" => $step)),
				"d0" => $d[0],
				"d1" => $d[1],
				"d2" => $d[2],
				"d3" => $d[3],
				"d4" => $d[4],
				"d5" => $d[5],
				"d6" => $d[6],
				"col0" => $col[0],
				"col1" => $col[1],
				"col2" => $col[2],
				"col3" => $col[3],
				"col4" => $col[4],
				"col5" => $col[5],
				"col6" => $col[6],
			));
		//	$step = $step + $arr["obj_inst"]->prop("time_step");
			$step = $step + 1;
		}
	}

	function _init_calendar_t(&$t)
	{
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"width" => "20px",
		));
		$t->define_field(array(
			"name" => "d0",
			"caption" => date("l d/m/Y", time()),
			"width" => "20px",
			"chgbgcolor" => "col0",
		));
		$t->define_field(array(
			"name" => "d1",
			"caption" => date("l d/m/Y", time() + 86400),
			"width" => "20px",
			"chgbgcolor" => "col1",
		));
		$t->define_field(array(
			"name" => "d2",
			"caption" => date("l d/m/Y", time() + 86400*2),
			"width" => "20px",
			"chgbgcolor" => "col2",
		));
		$t->define_field(array(
			"name" => "d3",
			"caption" => date("l d/m/Y", time() + 86400*3),
			"chgbgcolor" => "col3",
			"width" => "20px",
		));
		$t->define_field(array(
			"name" => "d4",
			"caption" => date("l d/m/Y", time() + 86400*4),
			"width" => "20px",
			"chgbgcolor" => "col4",
		));
		$t->define_field(array(
			"name" => "d5",
			"caption" => date("l d/m/Y", time() + 86400*5),
			"width" => "20px",
			"chgbgcolor" => "col5",
		));
		$t->define_field(array(
			"name" => "d6",
			"caption" => date("l d/m/Y", time() + 86400*6),
			"chgbgcolor" => "col6",
			"width" => "20px",
		));
		$t->table_caption = t("Broneerimine");
		$t->set_sortable(false);
	}

	/**
		@attrib name=do_add_reservation params=name all_args=1
		@param id required oid
			room id
		@param bron optional array
			keys are start timestamps
	**/
	function do_add_reservation($arr)
	{
		if(is_oid($arr["id"]))
		{
			$room = obj($arr["id"]);
			$length = $this->step_lengths[$room->prop("time_unit")] * $room->prop("time_step") ;
					
			if(is_object($room->get_first_obj_by_reltype("RELTYPE_CALENDAR")))
			{
				$cal_obj = $room->get_first_obj_by_reltype("RELTYPE_CALENDAR");
				$cal = $cal_obj->id();
				$parent = $cal_obj->prop("event_folder");
				$step = $room->prop("time_step");
			}

			$end = $arr["bron"][0];
			foreach($arr["bron"] as $bron => $val)
			{
				if(!$start)
				{
					$start = $bron;
					$end = $start + $length-1;
				}
				if(($end+1) == $bron)
				{
					$end = $bron + $length-1;
				}
			}
		}
		//arr($arr); arr($start); arr($end);
		//die();
		return $this->mk_my_orb(
			"new",
			array(
				"parent" => $parent,
				"return_url" => get_ru(),
				"calendar" => $cal,
				"start1" => $start,
				"end" => $end,
				"resource" => $arr["id"],
				"return_url" => $arr["post_ru"],
			),
			CL_RESERVATION
		);
	}

	function _get_resources_tb($arr)
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

	function _get_resources_tbl($arr)
	{
		if(!$arr["obj_inst"]->prop("resources_fld"))
		{
			$arr["prop"]["value"] = t("Ressursside kataloog m&auml;&auml;ramata");
			return PROP_OK;
		}
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));

		foreach($this->get_room_resources($arr["obj_inst"]->id()) as $oid => $obj)
		{
			$t->define_data(array(
				"name" => $obj->name(),
			));
		}
	}

	function get_room_resources($oid)
	{
		if(!is_oid($oid))
		{
			return array();
		}
		$obj = obj($oid);

		$ol = new object_list(array(
			"class_id" => CL_MRP_RESOURCE,
			"parent" => $obj->prop("resources_fld"),
		));
		return $ol->arr();
	}
	
	function search_products($this_obj)
	{
		$ol = new object_list();
		$filter = array("class_id" => array(CL_SHOP_PRODUCT), "lang_id" => array());
		$data = $this_obj->meta("search_data");
		if($data["products_find_product_name"]) $filter["name"] = "%".$data["products_find_product_name"]."%";
		$ol = new object_list($filter);
		return $ol;
	}	
	
	function _products_tbl(&$arr)
	{
		classload("core/icons");
		$tb =& $arr["prop"]["vcl_inst"];		
		$this->_init_prod_list_list_tbl($tb);

		// get items 
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

		classload("core/icons");

		//$ol = $ot->to_list();
		$ol = $ot->arr();
	
		//otsingust
		if(sizeof($arr["obj_inst"]->meta("search_data")) > 1)
		{
			$ol = $this->search_products($arr["obj_inst"]);
			$arr["obj_inst"]->set_meta("search_data", null);
			$arr["obj_inst"]->save();
			$ol = $ol->arr(); 
		}
		
		$prod_data = $arr["obj_inst"]->meta("prod_data");
		foreach($ol as $o)
		{

			if ($o->class_id() == CL_MENU)
			{
				$tp = t("Kaust");
			}
			else
			if (is_oid($o->prop("item_type")))
			{
				$tp = obj($o->prop("item_type"));
				$tp = $tp->name();
			}
			else
			{
				$tp = "";
			}

			$get = "";
			if ($o->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("V&otilde;ta laost")
				));
			}

			$name = $o->name();
			if ($o->class_id() == CL_MENU)
			{
				$name =  html::href(array(
					"url" => html::get_change_url($o->id()),
					"caption" => $name
				));
			}
			
			//järjekorda kui pole, siis võtab objektist selle järjekorra mis on laos jne
			$ord = $o->ord();
			if($prod_data[$o->id()]["ord"]) 
			{
				$ord = $prod_data[$o->id()]["ord"];
			}
			
			$tb->define_data(array(
				"active" =>  html::checkbox(array(
					"name" => "sel_imp[".$o->id()."]",
					"value" => $o->id(),
					"checked" => $prod_data[$o->id()]["active"],
				)),
				"oid" => $o->id(),
				"icon" => html::img(array("url" => icons::get_icon_url($o->class_id(), $o->name()))),
				"name" => $name,
				"cnt" => $o->prop("item_count"),
				"item_type" => $tp,
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"return_url" => get_ru()
					), $o->class_id()),
					"caption" => t("Muuda")
				)),
				"get" => $get,
				"put" => $put,
				"del" => html::checkbox(array(
					"name" => "sel[]",
					"value" => $o->id()
				)),
				"is_menu" => ($o->class_id() == CL_MENU ? 0 : 1),
				"ord" => html::textbox(array(
					"name" => "set_ord[".$o->id()."]",
					"value" => $ord,
					"size" => 5
				)).html::hidden(array(
					"name" => "old_ord[".$o->id()."]",
					"value" => $o->ord()
				)),
				"hidden_ord" => $ord
			));
		}

		$tb->set_numeric_field("hidden_ord");				
		$tb->set_default_sortby(array("is_menu", "hidden_ord"));
		$tb->sort_by();

		return $tb->draw(array(
			"pageselector" => "text",
			"records_per_page" => 50,
			"has_pages" => 1
		));
	}

	function _init_prod_list_list_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "active",
			"caption" => t("Aktiivne"),
		));
		
		$t->define_field(array(
			"name" => "icon",
			"caption" => t("&nbsp;"),
			"sortable" => 0,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "item_type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "cnt",
			"caption" => t("Kogus laos"),
			"align" => "center",
			"type" => "int"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => t("V&otilde;ta laost"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "del",
			"caption" => t("Vali"),
			"align" => "center",
		));
	}	

	function _products_tr($arr)
	{
		$arr["prop"]["vcl_inst"] = new object_tree(array(
			"parent" => $arr["obj_inst"]->prop("resources_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"sort_by" => "objects.jrk"
		));
		
		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "prods",
				"persist_state" => true,
			),
			"root_item" => obj($arr["obj_inst"]->prop("resources_fld")),
			"ot" => $arr["prop"]["vcl_inst"],
			"var" => "tree_filter"
		));
		$arr["prop"]["value"] = $tv->finalize_tree();
	}
	
	function _init_products_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t("&nbsp;"),
			"sortable" => 0,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "item_type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "cnt",
			"caption" => t("Kogus laos"),
			"align" => "center",
			"type" => "int"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => t("V&otilde;ta laost"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => t("Vii lattu"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "del",
			"caption" => "<a href='javascript:aw_sel_chb(document.changeform,\"sel\")'>".t("Vali")."</a>",
			"align" => "center",
		));
	}
	
	function _products_tb(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$this->prod_type_fld = $data["obj_inst"]->prop("resources_fld");
		$this->prod_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $data["obj_inst"]->prop("resources_fld");
			
		$tb->add_menu_button(array(
			"name" => "crt_".$this->prod_type_fld,
			"tooltip" => t("Uus")
		));

		$this->_req_add_itypes($tb, $this->prod_type_fld, $data);

		$tb->add_menu_item(array(
			"parent" => "crt_".$this->prod_type_fld,
			"text" => t("Lisa kaust"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->prod_tree_root,
				"return_url" => get_ru(),
			), CL_MENU)
		));
		
		$tb->add_menu_item(array(
			"parent" => "crt_".$this->prod_type_fld,
			"text" => t("Lisa tootekategooria"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->prod_tree_root,
				"return_url" => get_ru(),
			), CL_SHOP_PRODUCT_TYPE)
		));

		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Aktiivseks"),
			'action' => 'save_products',
		));

		$tb->add_button(array(
			"name" => "del",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud"),
			'action' => 'delete_cos',
		));

	}
	
	/**
		@attrib name=save_products params=name all_args=1
	**/
	function save_products($arr)
	{
		$this_obj = obj($arr["id"]);
		$prod_data = $this_obj->meta("prod_data");
		foreach($arr["set_ord"] as $id => $ord)
		{
			$prod_data[$id]["ord"] = $ord;
			$prod_data[$id]["active"] = $arr["sel_imp"][$id];
		}
		$this_obj->set_meta("prod_data" , $prod_data);
		$this_obj->save();
		return $arr["post_ru"];
	}
	
	function _req_add_itypes(&$tb, $parent, &$data)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_SHOP_PRODUCT_TYPE),
			"lang_id" => array(),
			"site_id" => array()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() != CL_MENU)
			{
				$tb->add_menu_item(array(
					"parent" => "crt_".$parent,
					"text" => $o->name(),
					"link" => $this->mk_my_orb("new", array(
						"item_type" => $o->id(),
						"parent" => $this->prod_tree_root,
						//"alias_to" => $this->warehouse->id(),
						"reltype" => 2, //RELTYPE_PRODUCT,
						"return_url" => get_ru(),
						"cfgform" => $o->prop("sp_cfgform"),
						"object_type" => $o->prop("sp_object_type")
					), CL_SHOP_PRODUCT)
				));
			}
			else
			{
				$tb->add_sub_menu(array(
					"parent" => "crt_".$parent,
					"name" => "crt_".$o->id(),
					"text" => $o->name()
				));
				$this->_req_add_itypes($tb, $o->id(), $data);
			}
		}
	}
	
	function get_prod_tree($o)
	{
		if(is_oid($o))
		{
			$o = obj($o);
		}
		if(is_oid($o->prop("resources_fld")) && $this->can("view" , $o->prop("resources_fld")))
		{
			$tree = new object_tree(array(
				"parent" => $o->prop("resources_fld"),
				"class_id" => CL_MENU,
				"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
				"sort_by" => "objects.jrk"
			));
			return $tree;
		}
		else
		{
			return "";
		}
	}
	 
	function get_prod_list($o)
	{
		
		$ol = new object_list();
		
		if(is_oid($o))
		{
			$o = obj($o);
		}
		
		if(!is_object($o))
		{
			return new object_list();
		}
		
		if($o->class_id() == CL_MENU)
		{
			$parent = $o->id();
		}
		else
		{
			$menu_tree = $this->get_prod_tree($o);
			$menu_list = $menu_tree->to_list(array(
				"add_root" => true,
			));
			$parent = $menu_list->ids();
		}
		
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"parent" => $parent,
		));
	
		return $ol;
	}
	
	/**
		@attrib params=pos
		@param prod_list required type=Array
			array of product id's and amount of them
			array(
				prod_oid => amount,
			)

		@param reservation required type=oid
			rervations oid with what these products where ordered
		@param time optional type=int
			the time, when this order needs to be in order :) .. basically this is needed to cover cross-day reservations and different orders for each day..
			If not set, reservation start time is set instead.

	**/
	function order_products($prod_list, $reservation, $time)
	{
		if(!is_array($prod_list) || !is_oid($reservation))
		{
			return false;
		}
		$reservation = obj($reservation);
		$room = $reservation->prop("resource");
		if(is_oid($room))
		{
			$room = obj($room);
		}
		else
		{
			return false;
		}
		$warehouse = $room->prop("warehouse");
		if(is_oid($warehouse))
		{
			$warehouse = obj($warehouse);
		}
		else
		{
			return false;
		}
		$so = get_instance(CL_SHOP_ORDER);
		$so->start_order($warehouse);
		foreach($prod_list as $prod_id => $amount)
		{
			if(!is_oid($prod_id) || $amount < 1)
			{
				continue;
			}
			$so->add_item(array(
				"iid" => $prod_id,
				"item_data" => array(
					"items" => $amount,
				),
			));

		}
		$order_id = $so->finish_order();
		$reserv = get_instance(CL_RESERVATION);
		if(!$reserv->add_order($reservation->id(), $order_id, $time))
		{
			return false;
		}
		return true;
	}

	/** Change the realestate object info.
		
		@attrib name=parse_alias is_public="1" caption="Change"
	
	**/
	function parse_alias($arr)
	{
		enter_function("room::parse_alias");
		$tpl = "kolm.tpl";
		$this->read_template($tpl);
		lc_site_load("room", &$this);
		
		$data = array("joga" => "jogajoga");
		$this->vars($data);
		//property väärtuse saatmine kujul "property_nimi"_value
		exit_function("room::parse_alias");
		return $this->parse();
	}
	
	/**
		@attrib params=name
		@param room required type=oid
			room id
		@param people optional type=int
			number of people
		@param start required type=int
			whenever that stuff , you need room for, starts
		@param end required type=int
			when the event, you need room for, ends
		@param products optional type=array
			products you want to order with room
	**/
	function cal_room_price($arr)
	{
		extract($arr);		
		if(!is_oid($room))
		{
			return 0;
		}
		$room = obj($room);
		
		$this->step_length = $this->step_lengths[$room->prop("time_unit")];
		$sum = array();
		
		$price_inst = get_instance(CL_ROOM_PRICE);
		$this_price = "";
		$this_prices = array();
		$prices = $room->connections_from(array(
			"class_id" => CL_ROOM_PRICE,
			"type" => "RELTYPE_ROOM_PRICE",
		));
		foreach($prices as $conn)
		{
			$price = $conn->to();
			if(($price->prop("date_from") < $start) && $price->prop("date_to") > $end && $price->prop("type") == 1)
			{
				if(in_array((date("w", $start) + 1) , $price->prop("weekdays")))
				{
					$this_price = $price;
					$this_prices[$price->prop("nr")][] = $price;
//					break;
				}
			}
		}
		
		$step = 1;
		$time = $end-$start;//+60 seepärast et oleks nagu täisminutid ja täistunnid jne
		while($time > 60)//alla minuti ei ole oluline aeg eriti... et toimiks nii 00, kui ka minut enne.. siis peaks igaks juhuks 60sek otsas olema
		{//arr($end);
			$price = "";
			if(is_array($this_prices[$step]))
			{
				$price = $this->get_best_time_in_prices(array(
					"time" => $time,
					"prices" => $this_prices[$step],
					"end" => $end,
				));
			}
			if(!is_object($price))//kõvemal tasemel enam ei ole hindu.... laseb vanaga edasi
			{
				$price = $this->get_best_time_in_prices(array(
					"time" => $time,
					"prices" => $this_prices[$step-1],
					"end" => $end,
				));
			}
			else
			{
				$step++;
			}
			
			if(!is_object($price))//juhul kui miski uus aeg vms... hakkab otsast peale
			{
				$price = $this->get_best_time_in_prices(array(
					"time" => $time,
					"prices" => $this_prices[1],
					"end" => $end,
				));
			}

			if(!is_object($price) || !($price->prop("time") > 0) || !$this->step_length)//igaks juhuks... ei taha et asi tsüklisse jääks
			{
				break;
			}
			//otsib, kas mõni soodushind kattub 
			$bargain = $this->get_bargain(array(
				"price" => $price,
				"room" => $room,
				"time" => $price->prop("time") * $this->step_length,
				"start" => $end-$time,
			));
			foreach($price->meta("prices") as $currency => $hr_price)
			{
				$sum[$currency] += ($hr_price - $bargain*$hr_price);//+1 seepärast, et lõppemise täistunniks võetakse esialgu ümardatud allapoole tunnid... et siis ajale tuleb üks juurde liita, sest poolik tund läheb täis tunnina arvesse
			}
			$time = $time - ($price->prop("time") * $this->step_length);
		}
		if(is_object($this_price))
		{
			
			//arvan , et õige oleks 1 maha võtta.... et saaks panna lõpptähtajaks järgmise broneeringu algustähtaeg... et siis 10:00 -11:00 10:00 - 10:59 asemel
		//	$end = $end-1;
			
			//siin teeb asjad täistundideks....
		//	$hr_start = mktime(date("G", $start), 0, 0, date("m", $start), date("d", $start), date("y", $start));
		//	$hr_end = mktime(date("G", $end), 0, 0, date("m", $end), date("d", $end), date("y", $end));
			foreach($price_inst->get_prices($this_price->id()) as $currency => $hr_price)
			{
			//	$sum[$currency] = $hr_price*(int)(($hr_end-$hr_start)/3600 + 1);//+1 seepärast, et lõppemise täistunniks võetakse esialgu ümardatud allapoole tunnid... et siis ajale tuleb üks juurde liita, sest poolik tund läheb täis tunnina arvesse
				if($people > $room->prop("normal_capacity"))
				{
					$sum[$currency] += ($people-$room->prop("normal_capacity")) * $room->prop("price_per_face_if_too_many"); 
				}
				if(is_array($products) && sizeof($products))
				{
					$sum[$currency] += $this->cal_products_price(array(
						"products" => $products,
						"currency" => $currency,
						"warehouse" => $room->prop("warehouse"),
					));
				}
			}
		}
		return $sum;
	}
	
	//annab soodustuse juhul kui see täpselt kattub hinna ajaga või kui üks soodustus lõppeb kas enne aja lõppu , või algab alles poole pealt
	//inimliku lolluse vastu kahjuks see funktsioon ei aita, kui kellelgi on tahtmist mitmeid poolikult kattuvaid soodustusi ühele ajale paigutada... palun väga, kuid resultaati ei oska ette ennustada
	function get_bargain($arr)
	{
		extract($arr);
		if(is_object($price) && is_object($room))
		{
			$bargains = array();
			$bargain_conns = $room->connections_from(array(
				"class_id" => CL_ROOM_PRICE,
				"type" => "RELTYPE_ROOM_PRICE",
			));
			$end = $start+$time;
			foreach($bargain_conns as $conn)
			{
				$bargain = $conn->to();//kui järgnevas iffis midagi ei tööta.... siis edu... mulle vist 
				if(
					($bargain->prop("active") == 1) &&
					($bargain->prop("type") == 2) &&
					(in_array((date("w", $start) + 1) , $bargain->prop("weekdays"))) && 
					(
						(
							$bargain->prop("date_from") <= ($start+60) &&
							($bargain->prop("date_to") + 60) >= ($start+$time)
						)||
						(
							$bargain->prop("recur")	&&
							(
								(
									(100*date("n",$bargain->prop("date_from")) + date("j",$bargain->prop("date_from"))) <= (100*date("n",$start) + date("j",$start)) && 
									(100*date("n",$bargain->prop("date_to")) + date("j",$bargain->prop("date_to"))) >= (100*date("n",($start+$time)) + date("j",($start+$time)))
								) || 
								(
									(100*date("n",$bargain->prop("date_from")) + date("j",$bargain->prop("date_from")) >= 100*date("n",$bargain->prop("date_to")) + date("j",$bargain->prop("date_to"))) &&
										(
											((100*date("n",$bargain->prop("date_from")) + date("j",$bargain->prop("date_from"))) <= (100*date("n",$start) + date("j",$start)))||
											((100*date("n",$bargain->prop("date_to")) + date("j",$bargain->prop("date_to"))) >= (100*date("n",($start+$time)) + date("j",($start+$time)))
										)
									)
								)
							)
						)
					)
				)
				{
					$from = $bargain->prop("time_from");
					$to = $bargain->prop("time_to");//arr(mktime($from["hour"], $from["minute"], 0, date("m",$start), date("d",$start), date("y",$start))); arr(mktime($to["hour"], $to["minute"], 0, date("m",$end), date("d",$end), date("y",$end))); arr($start);arr($end);
					//juhul kui aeg mahub täpselt soodushinna sisse
					if(mktime($from["hour"], $from["minute"], 0, date("m",$start), date("d",$start), date("y",$start)) <=  $start && mktime($to["hour"], $to["minute"], 0, date("m",$end), date("d",$end), date("y",$end)) >=  $end)
					{
						return 0.01*$bargain->prop("bargain_percent");
					}
					//juhul kui mõni kattub poolikult... esimene siis , et kui allahindlus algul on,... teine, et allahindlus tuleb poolepealt
					if((mktime($from["hour"], $from["minute"], 0, date("m",$start), date("d",$start), date("y",$start)) <=  $start) && (mktime($to["hour"], $to["minute"], 0, date("m",$end), date("d",$end), date("y",$end)) > $start))
					{
						return 0.01*$bargain->prop("bargain_percent")*(mktime($to["hour"], $to["minute"], 0, date("m",$end), date("d",$end), date("y",$end)) - $start)/($end-$start);
					}
					if((mktime($to["hour"], $to["minute"], 0, date("m",$end), date("d",$end), date("y",$end)) >=  $end) && (mktime($from["hour"], $from["minute"], 0, date("m",$start), date("d",$start), date("y",$start)) < $end))
					{
						return 0.01*$bargain->prop("bargain_percent")*($end - mktime($from["hour"], $from["minute"], 0, date("m",$from), date("d",$from), date("y",$from)))/($end-$start);
					}
				}
			}
		}
		return 0;
	}
	
	/**
		@attrib params=name
		@param prices required type=array
			price objects.... keys are price->prop(time)
		@param time required type=int
			time left ... still without tax
		@param end required type=int
			event ending time
		@return object
			price object... largest of smaller times... or smallest of larger times
	**/
	function get_best_time_in_prices($arr)
	{
		extract($arr);
		$largest = "";
		$smaller = "";
		$prices_to_use_when_situation_is_hopeless = array();
		$start = $arr["end"] - $time;//arr($start); arr($end);arr()
		//arr($start);arr(date("G:i",$start));
		foreach($prices as $key => $price)
		{//arr($time);
			//jube porno.... testib kas hinna ajastus kattub järgneva ajaga
			$time_from = $price->prop("time_from");
			$time_to = $price->prop("time_to");
			$end = $start + $price->prop("time") * $this->step_length;//arr("/");arr($end);arr("\\");
			if(!((mktime($time_from["hour"], $time_from["minute"], 0, date("m",$start), date("d",$start), date("y",$start)) <= $start) && 
			     (mktime($time_to["hour"], $time_to["minute"], 0, date("m",$end), date("d",$end), date("y",$end))>= ($start + $price->prop("time") * $this->step_length))
			))
			{
				if((mktime($time_from["hour"], $time_from["minute"], 0, date("m",$start), date("d",$start), date("y",$start)) <= $start) || 
					(mktime($time_to["hour"], $time_to["minute"], 0, date("m",$end), date("d",$end), date("y",$end))>= ($start + $price->prop("time") * $this->step_length))
				)//kui miskeid täis aegu ei ole, siis lähevad poolikud hiljem kasutusse
				{
					$prices_to_use_when_situation_is_hopeless[] = $price;
				}
				continue; //siia tuleb mingi eriti sünge kood, mis peaks hindu ajaliselt tükeldama hakkama ....
			}
			//	arr(date("G:i",mktime($time_from["hour"], $time_from["minute"], 0, date("m",$start), date("d",$start), date("y",$start))));arr(date("G:i",$start)); arr(date("G:i",$start + $price->prop("time") * $this->step_length));  arr(date("G:i",mktime($time_to["hour"], $time_to["minute"], 0, date("m",$end), date("d",$end), date("y",$end))));
			//arr($price->prop("time") * $this->step_length); arr($time);
			if($time + 60 >= ($price->prop("time") * $this->step_length) && (!$smaller || ($smaller->prop("time") < $price->prop("time"))))
			{
				$smaller = $price;
			}
			if(($time <= ($price->prop("time") * $this->step_length)+ 60) && (!$larger || ($larger->prop("time") > $price->prop("time"))))
			{
				$larger = $price;
			}
		}
		if(is_object($smaller))
		{
			return $smaller;
		}
		elseif(is_object($larger))
		{
			return $larger;
		}
		else
		{
			$arr["prices"] = $prices_to_use_when_situation_is_hopeless;
			return $this->get_half_prices($arr);
		}
	}
	
	//parem ära ürita aru saada mis see pooletoobine funktsioon teeb.... loodame lihtsalt, et kunagi seda vaja ei lähe
	function get_half_prices($arr)
	{	
		extract($arr);
		$sum = 0;
		$start = $arr["end"] - $time;
		$half_obj = "";
		foreach($arr["prices"] as $price)
		{
			$time_from = $price->prop("time_from");
			$time_to = $price->prop("time_to");
			$end = $start + $price->prop("time") * $this->step_length;
			if(mktime($time_from["hour"], $time_from["minute"], 0, date("m",$start), date("d",$start), date("y",$start)) <= $start)
			{
				//p näitab kui suur osa summast ja ajast kasutusse läheb
				$p = (mktime($time_to["hour"], $time_to["minute"], 0, date("m",$start), date("d",$start), date("y",$start))-$start)/($price->prop("time") * $this->step_length);
				$half_obj = new object();
				$half_obj->set_parent($price->id());
				$half_obj->set_class_id(CL_ROOM_PRICE);
				$meta_prices = ($price->meta("prices"));
				$half_obj->set_prop("time", (mktime($time_to["hour"], $time_to["minute"], 0, date("m",$end), date("d",$end), date("y",$end))-$start)/$this->step_length);
				foreach($meta_prices as $curr => $sum)
				{
					$meta_prices[$curr] = $sum * $p;
					$half_obj->set_meta("prices", $meta_prices);
 				}
			}
			if(mktime($time_to["hour"], $time_to["minute"], 0, date("m",$end), date("d",$end), date("y",$end))>= ($start + $price->prop("time") * $this->step_length))
			{
				;//kui loomingulisem hoog peale tuleb, saab siia miskit toredat lisada
			}
		}
		return $half_obj;
	}
	
	/**
		@attrib params=name
		@param products required type=array
			products and their amounts
		@param currency optional type=oid
			if you want result in not the same currency the company uses.
		@return int
			price of all products
	**/
	function cal_products_price($arr)
	{
		extract($arr);
		$sum = 0;
		foreach($products as $id => $amt)
		{
			if($amt && is_oid($id))
			{
				$product = obj($id);
				if(is_oid($currency))
				{
					$cur_pr = $product->meta("cur_prices");
					if($cur_pr[$currency])
					{
						$sum += $cur_pr[$currency] *  $amt;
					}
					else $sum += $product->prop("price") * $amt;
				}
			}
		}
		return $sum;
	}
}
?>
