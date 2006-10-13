<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room.aw,v 1.2 2006/10/13 13:02:52 tarvo Exp $
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

		@property name type=textbox
		@caption Nimi

		@property location type=relpicker reltype=RELTYPE_LOCATION
		@caption Asukoht

		@property owner type=relpicker reltype=RELTYPE_OWNER
		@caption Omanik

		@property inventory type=relpicker reltype=RELTYPE_INVENTORY
		@caption Varustuse kataloog

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

@groupinfo calendar caption="Kalender"
@default group=calendar,parent=
	@property calendar type=calendar no_caption=1 viewtype=relative

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
	@default group=prices_price
		
		/@property prices_manager type=releditor mode=manager reltype=RELTYPE_ROOM_PRICE props=name,date_from,date_to,weekdays,nr table_fields=name,date_from,date_to,weekdays,nr no_caption=1
		@property prices_tbl type=table no_caption=1

	@groupinfo prices_bargain_price caption="Soodushinnad" parent=prices
	@default group=prices_bargain_price

		@property bargain_prices_tbl type=table no_caption=1

# RELTYPES

@reltype LOCATION value=1 clid=CL_LOCATION
@caption Asukoht

@reltype OWNER value=2 clid=CL_CRM_COMPANY
@caption Omanik

@reltype INVENTORY value=3 clid=CL_SHOP_WAREHOUSE
@caption Varustuse kataloog

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
			case "time_unit":
				$prop["options"] = array(
					1 => t("minutitites"),
					2 => t("tundides"),
					3 => t("p&auml;evades"),
				);
				break;
			# TAB CALENDAR
			case "calendar":
				//$cal = $this->get_calendar($arr["obj_inst"]->id());
				$c = &$prop["vcl_inst"];
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
				break;
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
		$o->disconnect(array(
			"from" => $arr["sel"]
		));
		return $arr["post_ru"];
	}

	function _handle_img_search_result($arr)
	{
		$p = get_instance("vcl/popup_search");
		$p->do_create_rels(obj($arr["args"]["id"]), $arr["request"]["images_search"], 6);
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

	function _get_prices_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
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
			$cur_fields["currency_".$cobj->id()] = html::textbox(array(
				"name" => "",
				"size" => 5,
			)). $cobj->prop("unit_name");
		}
		$ds = get_instance("vcl/date_edit");
		$ds->configure(array(
			"year" => "year",
			"month" => "month",
			"day" => "day",
		));

		# getting data
		$price_objs = $this->get_prices($arr["obj_inst"]->id());
		foreach($price_objs as $oid => $obj)
		{
			$t->define_data(array(
				"dates" => $obj->prop("date_from")." kuni ".$obj->prop("date_to"),
				"weekdays" => $obj->prop("weekdays"),
				"nr" => $obj->prop("nr"),
			));
		}
	
		/*
		$add_new_row = array(
			"dates" => $ds->gen_edit_form("date_from", "", 2005, 2010, true).t(" kuni ").$ds->gen_edit_form("date_to", "", 2005, 2010, true),
			"weekdays" => "wk",
			"nr" => "nr",
			"time" => "time",
			"time_step" => "time_step",
		);
		$add_new_row = array_merge($add_new_row, $cur_fields);
		*/

		$t->define_data($add_new_row);
	}

	function get_calendar($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		return $o->get_first_obj_by_reltype("RELTYPE_CALENDAR");
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
	function get_prices($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		$cs = $o->connections_from(array(
			"class_id" => CL_ROOM_PRICE,
			"reltype" => "RELTYPE_ROOM_PRICE",
		));
		foreach($cs as $c)
		{
			$to = $c->to();
			$ret[$to->id()] = $to;
		}
		return $ret;
	}
}
?>
