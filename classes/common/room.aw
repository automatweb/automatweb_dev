<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room.aw,v 1.114 2007/01/25 09:06:17 kristo Exp $
// room.aw - Ruum 
/*

@classinfo syslog_type=ST_ROOM relationmgr=yes no_comment=1 no_status=1

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

		@property short_name type=textbox
		@caption Nime l&uuml;hend

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

		@property professions type=relpicker reltype=RELTYPE_PROFESSION multiple=1
		@caption Ametinimetused
	
		property reservation_template type=select
		caption Broneeringu template

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

		@layout buffer_before_l type=hbox width=30%:70% parent=general_down
		@default parent=buffer_before_l

		@property buffer_before type=textbox size=5
		@caption Puhveraeg enne
		
		@property buffer_before_unit type=select no_caption=1
		
		@layout buffer_after_l type=hbox  width=30%:70% parent=general_down
		@default parent=buffer_after_l
		
		@property buffer_after type=textbox size=5
		@caption Puhveraeg p&auml;rast
		
		@property buffer_after_unit type=select no_caption=1
		
		@property use_product_times type=checkbox parent=general_down no_caption=1
		@caption Kasuta toodetele määratud aegu
		
		@property settings type=relpicker parent=general_down multiple=1 reltype=RELTYPE_SETTINGS
		@caption Seaded
		
valdkonnanimi (link, mis avab popupi, kuhu saab lisada vastava valdkonnaga seonduva täiendava info selle valdkonna objektitüübi kaudu, nt konverentsid).
- puhveraeg enne (mitu tundi enne reserveeringu algust lisaks bronnitakse ruumide ettevalmistamiseks)
- puhveraeg pärast (mitu tundi peale reserveeringu lõppu broneeritakse ruumide korrastamiseks

# TAB CALENDAR

@groupinfo calendar caption="Kalender" submit=no
@default group=calendar
	@property calendar_tb type=toolbar no_caption=1 submit=no
	@property calendar type=calendar no_caption=1 viewtype=relative store=no
	
	@property calendar_select type=text no_caption=1
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

		@property prod_discount_loc type=chooser
		@caption Toodete soodushind võetakse: 

		@property prod_web_discount type=textbox size=2
		@caption Toodete soodushind

		@layout time_step type=hbox width=5%:5%:20%:60%
		@caption Aja samm

			@property time_from type=textbox size=5 parent=time_step
			@caption Alates

			@property time_to type=textbox size=5 parent=time_step
			@caption kuni

			@property time_step type=textbox size=5 parent=time_step
			@caption ,sammuga
			
			@property selectbox_time_step type=textbox size=5 parent=time_step
			@caption Valiku aja samm (kui on erinev, mõjub ka hinnale)

			
		property selectbox_length type=textbox size=4
		caption Ajavaliku pikkus
		
		@property web_min_prod_price type=callback callback=cb_gen_web_min_prices 
		@caption Veebi miinumum toodete hind 

		@property childtitle110 type=text store=no subtitle=1
		@caption Kogu ruumi broneeringu miinimumhind
		
			@property min_prices_props type=callback callback=gen_min_prices_props
			@caption Miinimum hinnad


	@groupinfo prices_price caption="Hinnad" parent=prices
	@default group=prices_price,prices_bargain_price
		@property prices_search type=hidden no_caption=1 store=no
		@property prices_tb type=toolbar no_caption=1
		@property prices_tbl type=table no_caption=1

	@groupinfo prices_bargain_price caption="Soodushinnad" parent=prices
	@default group=prices_bargain_price

@groupinfo open_hrs caption="Avamisajad"
@default group=open_hrs

	@property openhours type=releditor reltype=RELTYPE_OPENHOURS rel_id=first use_form=emb store=no
	@caption Avamisajad

@groupinfo transl caption=T&otilde;lgi
@default group=transl
	
	@property transl type=callback callback=callback_get_transl store=no
	@caption T&otilde;lgi


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
@caption Ladu

reltype TEMPLATE value=11 clid=CL_SHOP_WAREHOUSE
caption Templeit

@reltype OPENHOURS value=44 clid=CL_OPENHOURS
@caption Avamisajad

@reltype PROFESSION value=12 clid=CL_CRM_PROFESSION
@caption Ametinimetus

@reltype SETTINGS value=13 clid=CL_ROOM_SETTINGS
@caption Seaded

*/

class room extends class_base
{
	function room()
	{
		$this->init(array(
			"tpldir" => "common/room",
			"clid" => CL_ROOM
		));
		classload("core/icons");

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
		
		$this->time_units = array(
			1 => t("Sekundit"), //default
			60 => t("Minutit"),
			3600 => t("Tundi"),
			86400 => t("P&auml;eva"),
		);
		$this->weekdays = array(
			t("Sunday") , t("Monday") , t("Tuesday"), t("Wednesday") , t("Thursday") , t("Friday"), t("Saturday")
		);
		$this->weekdays_short = array(
			t("Su") , t("Mo") , t("Tu"), t("We") , t("Th") , t("Fr"), t("Sa")
		);
		
		classload("core/date/date_calc");

		$this->trans_props = array(
			"name"
		);
		$this->ui = get_instance(CL_USER);
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
			case "prices_tb":
				$this->_get_prices_tb($arr);
				break;
			case "prices_tbl":
				$this->_get_prices_tbl($arr);
				break;
			case "buffer_before_unit":
			case "buffer_after_unit":
				$prop["options"] = $this->time_units;
				break;
			case "prod_discount_loc":
				$prop["options"] = array(t("Tellimiskeskkonnast") , t("Ruumi juurest"));
				break;
			case "prod_web_discount":
				if(!$arr["obj_inst"]->prop("prod_discount_loc"))
				{
					return PROP_IGNORE;
				}
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
			case "calendar_select":
				$prop["value"] = $this->_get_calendar_select($arr);
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
				case "openhours":
					if(!sizeof($arr["obj_inst"]->connections_from(array(
						"type" => "RELTYPE_OPENHOURS",
					))))
					{
						$oh = new object();
						$oh->set_parent($arr["obj_inst"]->id());
						$oh->set_class_id(CL_OPENHOURS);
						$oh->set_name($arr["obj_inst"]->name()." ".openhours);
						$oh->save();
						$arr["obj_inst"]->set_prop("openhours" , $oh->id());
						$arr["obj_inst"]->connect(array("to" => $oh->id(), "reltype" => "RELTYPE_OPENHOURS"));
					}
					break;
		};
		return $retval;
	}

	function last_reservation_arrived_not_set($room)
	{
		if(!(is_object($room)))
		{
			return false;
		}
		
		$reservations = new object_list(array(
			"class_id" => array(CL_RESERVATION),
			"lang_id" => array(),
			"resource" => $room->id(),
			"end" => new obj_predicate_compare(OBJ_COMP_BETWEEN, time() - 84600, time()),
		));
 		$result = array();
  		foreach($reservations->arr() as $res)
  		{
			if(($res->prop("client_arrived") == null)  && ($res->prop("verified") || $res->prop("deadline") > time()))
			{
				$result[$res->prop("end")] = $res->id();
			}
  		}
		usort($result);
		if(sizeof($result))
		{
			return end($result);
		}
		else
		{
			return false;
		}
	}

	function set_property($arr = array())
	{
		/*
		$doc = obj(9314);
		$doc->connect(array(
			"to" => $arr["obj_inst"]->id(),
			"reltype" => "RELTYPE_ALIAS",
		));
		*/
	
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
			case "deadline":
				$prop["value"] = time() + 15*60;
				break;
			case "openhours":
				$prop["parent"] = $arr["obj_inst"]->id();
				break;

			case "web_min_prod_price":
				$arr["obj_inst"]->set_meta("web_min_prod_prices", $arr["request"]["wpm_currency"]);
				break;
				
			case "min_prices_props":
				$arr["obj_inst"]->set_meta("web_room_min_price", $arr["request"]["web_room_min_price"]);
				break;
				
				//$prop[""]
			//-- set_property --//
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;
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
		if ($arr["request"]["set_view_dates"])
		{
			$arr["args"]["start"] = date_edit::get_timestamp($arr["request"]["set_d_from"]);
			if ($arr["request"]["set_view_dates"] == 1)
			{
				$arr["args"]["end"] = date_edit::get_timestamp($arr["request"]["set_d_to"]);
			}
			else
			if ($arr["request"]["set_view_dates"] == 2)
			{
				 $arr["args"]["end"] = $arr["args"]["start"] + 24*3600;
			}
			else
			if ($arr["request"]["set_view_dates"] == 3)
			{
				$arr["args"]["end"] = $arr["args"]["start"] + 24*3600*7;
			}
			else
			if ($arr["request"]["set_view_dates"] == 4)
			{
				$arr["args"]["end"] = $arr["args"]["start"] + 24*3600*31;
			}
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
		$arr["set_view_dates"] = " ";
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
	
	/**
		@attrib name=admin_add_bron_popup params=name all_args=1
		@param start1 required type=int
			start
		@param parent required type=oid
			parent
		@param end required type=int
			end
		@param resource required type=int
			room
		@param product optional
			chosen product
		@param return_url optional type=string
			url for opener window
	**/
	function admin_add_bron_popup($arr)
	{
		extract($arr);
//		arr($arr);
		extract($_POST["bron"]);
		
		
		$room = obj($resource);
		$professions = $room->prop("professions");
		if(is_array($professions) && sizeof($professions))
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"lang_id" => array(),
				"CL_CRM_PERSON.RELTYPE_RANK" => $professions,
			));
			$people_opts = array("") + $ol->names();
		}
		
		if(is_array($_POST["bron"]))
		{
			$room_inst = get_instance(CL_ROOM);
			$start1 = mktime($start1["hour"], $start1["minute"], 0, $start1["month"], $start1["day"], $start1["year"]);
			$end = mktime($end["hour"], $end["minute"], 0, $end["month"], $end["day"], $end["year"]);
			
			if(is_oid($product))
			{
				$product_obj = obj($product);
				$end = $start1 + $product_obj->prop("reservation_time")*$product_obj->prop("reservation_time_unit");
			}
			
			if(!$room_inst->check_if_available(array(
				"room" => $resource,
				"start" => $start1,
				"end" => $end,
			)) && $room_inst->last_bron_id !=$id)
			{
				$err = t("Sellisele ajale pole broneerida v&otilde;imalik");
				die($err);
			}
			else
			{
				if(is_oid($id) && $this->can("view" , $id))
				{
					$bron = obj($id);
				}
				else
				{
					$bron = new object();
					$bron->set_class_id(CL_RESERVATION);
					$bron->set_parent($parent);
					$bron->set_prop("resource", $resource);
					$bron->save();
					if($product)
					{
						$bron->set_meta("amount" ,array($product => 1));
					}
				}
				$bron->set_prop("start1", $start1);
				$bron->set_prop("end" ,$end);
				$bron->set_prop("content" , $comment);
				$bron->set_prop("verified" , 1);
				$bron->set_prop("deadline" , time()+15*60);
				
				if(is_oid($people) && $this->can("view" , $people))
				{
					$bron->set_prop("people" , $people);
				}
				if(strlen($phone))
				{
					$phones = new object_list(array(
						"lang_id" => array(),
						"class_id" => CL_CRM_PHONE,
						"name" => $phone,
					));
					if(!sizeof($phones->arr()))
					{
						$phone_obj = new object();
						$phone_obj->set_class_id(CL_CRM_PHONE);
						$phone_obj->set_name($phone);
						$phone_obj->set_prop("type" , "mobile");
						$phone_obj->set_parent($parent);
						$phone_obj->save();
					}
					else
					{
						$phone_obj = reset($phones->arr());
					}
				}
				
				if(strlen($firstname) || strlen($lastname))
				{
					$persons = new object_list(array(
						"lang_id" => array(),
						"class_id" => CL_CRM_PERSON,
						"firstname" => $firstname,
						"lastname" => $lastname,
					));
					if(!sizeof($persons->arr()))
					{
						$person = new object();
						$person->set_class_id(CL_CRM_PERSON);
						$person->set_parent($parent);
						$person->set_name($firstname." ".$lastname);
						$person->set_prop("firstname" , $firstname);
						$person->set_prop("lastname" , $lastname);
						$person->save();
					}
					else
					{
						$person = reset($persons->arr());
					}
					$bron->set_prop("customer",$person->id());
					$bron->connect(array("to" => $person->id(), "reltype" => 1));
					if(strlen($phone))
					{
						$person->connect(array("to"=> $phone_obj->id(), "type" => "RELTYPE_PHONE"));
					}
				}
				
				if(strlen($company))
				{
					$companys = new object_list(array(
						"lang_id" => array(),
						"class_id" => CL_CRM_Company,
						"name" => $company,
					));
					if(!sizeof($companys->arr()))
					{
						$co = new object();
						$co->set_class_id(CL_CRM_COMPANY);
						$co->set_parent($parent);
						$co->set_name($company);
						$co->save();
					}
					else
					{
						$co = reset($companys->arr());
					}
					$bron->set_prop("customer",$co->id());
					$bron->connect(array("to" => $co->id(), "reltype" => 1));
					if(strlen($phone))
					{
						$co->connect(array("to"=> $phone_obj->id(), "type" => "RELTYPE_PHONE"));
					}
				}
				$bron->save();
				$id = $bron->id();
				die("<script type='text/javascript'>
					if (window.opener)
					window.opener.location.href='".$arr['return_url']."';
					window.close();
					</script>
				");
			}
		}
		
		if(is_oid($product) && $start1>1)
		{
			$product_obj = obj($product);
			$end = $start1 + $product_obj->prop("reservation_time")*$product_obj->prop("reservation_time_unit");
		}

		
		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
		));
		
		$t->define_field(array(
			"name" => "caption",
		));
		$t->define_field(array(
			"name" => "value",
		));

		$t->define_data(array(
			"caption" => t("Algusaeg"),
			"value" => html::datetime_select(array(
				"name" => "bron[start1]",
				"value" => $start1,
			)),
		));
		$t->define_data(array(
			"caption" => t("L&otilde;ppaeg"),
			"value" => html::datetime_select(array(
				"name" => "bron[end]",
				"value" => $end,
			)),
		));
			
		$t->define_data(array(
			"caption" => t("Eesnimi"),
			"value" => html::textbox(array(
				"name" => "bron[firstname]",
				"size" => 40,
				"value" => $firstname,
			)),
		));
			
		$t->define_data(array(
			"caption" => t("Perenimi"),
			"value" => html::textbox(array(
				"name" => "bron[lastname]",
				"size" => 40,
				"value" => $lastname,
			)),
		));
			
		$t->define_data(array(
			"caption" => t("Organisatsioon"),
			"value" => html::textbox(array(
				"name" => "bron[company]",
				"size" => 40,
				"value" => $company,
			)),
		));
		
		$t->define_data(array(
			"caption" => t("Telefon"),
			"value" => html::textbox(array(
				"name" => "bron[phone]",
				"size" => 40,
				"value" => $phone,
			)),
		));
			
		$t->define_data(array(
			"caption" => t("Märkused"),
			"value" => html::textarea(array(
				"name" => "bron[comment]",
				"size" => 40,
				"value" => $comment,
			)),
		));	
			
		$t->define_data(array(
			"caption" => t("Meie esindaja"),
			"value" => html::select(array(
				"name" => "bron[people]",
				"value" => $people,
				"options" => $people_opts,
			)),
		));	
		
		$t->define_data(array(
			"value" => html::submit(array(
				"value" => t("Salvesta"),
			)),
		));	
		
		$t->define_data(array(
			"value" => html::hidden(array(
				"name" => "bron[id]",
				"value" => $id,
			)),
		));
		die($err.html::form(array("method" => "POST", "content" => $t->draw())));
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

	function _get_time_select($arr)
	{
		$x=0;
		$options = array();
		$week = date("W" , time());
		$weekstart = mktime(0,0,0,1,1,date("Y" , time())) + (date("z" , time()) - date("w" , time()) + 1)*86400;
		while($x<20)
		{
			$url = aw_url_change_var("start",$weekstart,get_ru());
			$options[$url] = date("W" , $weekstart) . ". " .date("d.m.Y", $weekstart) . " - " . date("d.m.Y", ($weekstart+604800));
			if($arr["request"]["start"] == $weekstart) $selected = $url;
			$weekstart = $weekstart + 604800;
			$x++;
		};
		
		$ret.= html::select(array(
			"name" => "room_reservation_select",
			"options" => $options,
			"onchange" => " window.location = this.value;",
			"selected" => $selected,
		));
		return $ret;
	}

	function _get_length_select($arr)
	{
		$ret = "";
		if(is_object($arr["obj_inst"]) && !$arr["obj_inst"]->prop("use_product_times"))
		{

			$options = array();
			$end = $arr["obj_inst"]->prop("time_to")/$arr["obj_inst"]->prop("time_step");
			$x = $arr["obj_inst"]->prop("time_from")/$arr["obj_inst"]->prop("time_step");
			if($arr["obj_inst"]->prop("selectbox_time_step") > 0)
			{
				$add = $arr["obj_inst"]->prop("selectbox_time_step")/$arr["obj_inst"]->prop("time_step");
			}
			else
			{
				$add = 1;
			}
			//$x = ($arr["obj_inst"]->prop("selectbox_time_step")/$arr["obj_inst"]->prop("time_step"));
				while($x<=$end)
				{
					//$options[$x] = ($x * $arr["obj_inst"]->prop("time_step"))%10;
					$options["".$x] = ($x * $arr["obj_inst"]->prop("time_step"))%10;
					if(($x * $arr["obj_inst"]->prop("time_step") - ($x * $arr["obj_inst"]->prop("time_step"))%10))
					{
						$small_units = round(($x * $arr["obj_inst"]->prop("time_step") - ($x * $arr["obj_inst"]->prop("time_step"))%10)*60);
						if($small_units%60 == 0)
						{
							$options["".$x] = $options["".$x] + $small_units/60;
						}
						else
						{
							if($small_units < 10)
							{
								$small_units = "0".$small_units;
							}
							$options["".$x] = $options["".$x] . ":" . $small_units;
						}
					}
					$x = $x + $add;
				}
			//}
/*			else
			{
				while($start <= $end)
				{
					//$options[$x] = ($x * $arr["obj_inst"]->prop("time_step"))%10;
					$options[$x] = ($x * $arr["obj_inst"]->prop("time_step"))%10;
					if(($x * $arr["obj_inst"]->prop("time_step") - ($x * $arr["obj_inst"]->prop("time_step"))%10))
					{
						$options[$x] = $options[$x] . ":" . ($x * $arr["obj_inst"]->prop("time_step") - ($x * $arr["obj_inst"]->prop("time_step"))%10)*60; 
					}
					$x++;
				};
			}*/
			$ret.= html::select(array(
				"name" => "room_reservation_length",
				"options" => $options,
				"onchange" => "changeRoomReservationLength(this);",
			));
			$ret.= $this->unit_step[$arr["obj_inst"]->prop("time_unit")];
		//	$ret.= $this->unit_step[$arr["obj_inst"]->prop("time_unit")];
		}
		$ret.= html::hidden(array("name" => "product", "id"=>"product_id" ,"value"=>""));
		return $ret;

	}

	function _get_hidden_fields($arr)
	{
		$ret = html::hidden(array("name" => "product", "id"=>"product_id" ,"value"=>""));
		$ret.=html::hidden(array("name" => "free_field_value", "id"=>"free_field_value" ,"value"=>"VABA"));
		$ret.=html::hidden(array("name" => "res_field_value", "id"=>"res_field_value" ,"value"=>"BRON"));
		$ret.=html::hidden(array("name" => "do_field_value", "id"=>"do_field_value" ,"value"=>"Broneeri"));
		return $ret;
	}

	function _get_calendar_select($arr)
	{
		$settings = $this->get_settings_for_room($arr["obj_inst"]);
		
		$ret = "";
		if($arr["user"] != 1 &&  $this->can("view",$bron_id = $this->last_reservation_arrived_not_set($arr["obj_inst"])) && !$settings->prop("no_cust_arrived_pop"))
		{
			$reservaton_inst = get_instance(CL_RESERVATION);
			$ret.="<script name= javascript>window.open('".$reservaton_inst->mk_my_orb("mark_arrived_popup", array("bron" => $bron_id,))."','', 'toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=150, width=300')
			</script>";
		}
                $x=0;
                $options = array();
                $week = date("W" , time());
                $weekstart = mktime(0,0,0,1,1,date("Y" , time())) + (date("z" , time()) - date("w" , time()) + 1)*86400;
                while($x<20)
                {
                        $url = aw_url_change_var("end", null, aw_url_change_var("start",$weekstart,get_ru()));
                        $options[$url] = date("W" , $weekstart) . ". " .date("d.m.Y", $weekstart) . " - " . date("d.m.Y", ($weekstart+604800));
                        if($arr["request"]["start"] == $weekstart) $selected = $url;
                        $weekstart = $weekstart + 604800;
                        $x++;
                };
		$ret.= $this->_get_hidden_fields($arr);

                $ws = html::select(array(
                        "name" => "room_reservation_select",
                        "options" => $options,
                        "onchange" => " window.location = this.value;",
                        "selected" => $selected,
                ));

		$this->read_template("cal_header.tpl");
		$this->vars(array(
			"pop" => $ret,
			"week_select" => $ws,
			"date_from" => html::date_select(array(
                  	      "name" => "set_d_from",
                        	"value" => $_GET["start"]
                	)),
			"ts_buttons" => html::button(array(
	                        "onclick" => "document.changeform.set_view_dates.value=2;submit_changeform();",
        	                "value" => t("P&auml;ev")
                	))." ".html::button(array(
	                        "onclick" => "document.changeform.set_view_dates.value=3;submit_changeform();",
        	                "value" => t("N&auml;dal")
                	))." ".html::button(array(
                        	"onclick" => "document.changeform.set_view_dates.value=4;submit_changeform();",
	                        "value" => t("Kuu")
        	        )),
			"date_to" => html::date_select(array(
	                        "name" => "set_d_to",
        	                "value" => $_GET["end"]
                	)),
			"to_button" => html::button(array(
	                        "onclick" => "document.changeform.set_view_dates.value=1;submit_changeform();",
        	                "value" => t("N&auml;ita vahemikku")
                	)),
		));
		
		if(is_object($arr["obj_inst"]) && !$arr["obj_inst"]->prop("use_product_times"))
		{
			$this->vars(array(
				"length_sel" => t("Vali broneeringu pikkus: ").$this->_get_length_select(array("obj_inst" => $arr["obj_inst"]))
			));
		}

		return $arr["prop"]["value"] = $this->parse();
	}

	function _get_calendar_tbl($arr)
	{
		enter_function("get_calendar_tbl");
		//kkui asi tuleb veebist
		if(is_oid($arr["room"]) && $this->can("view" , $arr["room"]))
		{	
			$arr["obj_inst"] = obj($arr["room"]);
			if($_GET["start"])
			{
				$arr["request"]["start"] = $_GET["start"];
			}
		}
		$t = &$arr["prop"]["vcl_inst"];
//		arr($arr["obj_inst"]->prop("openhours"));
		$open_inst = $this->open_inst = get_instance(CL_OPENHOURS);
		if(is_oid($arr["obj_inst"]->prop("openhours")) && $this->can("view" , $arr["obj_inst"]->prop("openhours")))
		{
			$this->openhours = obj($arr["obj_inst"]->prop("openhours"));
		}
		if(!is_object($this->openhours))
		{
			$this->openhours = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OPENHOURS");
		}
		if(is_object($this->openhours))
		{
 			$open = $this->open = $open_inst->get_times_for_date($this->openhours, $time);
		}
		if(!is_object($this->openhours))
		{
			$this->openhours = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_OPENHOURS");
		}
		
		$this->start = $arr["request"]["start"];
		// do this later, so we can feed it the start/end date
		//$this->generate_res_table($arr["obj_inst"]);

		exit_function("get_calendar_tbl");
		//see siis näitab miskeid valitud muid nädalaid
		enter_function("get_calendar_tbl::2");
		$start_hour = 0;
		$start_minute = 0;
		if(is_object($this->openhours))
		{
			$gwo = $this->get_when_opens();
			extract($gwo);
		}
		
		if($arr["request"]["start"])
		{
			$today_start = $arr["request"]["start"];
			//seda avamise alguse aega peab ka ikka arvestama, muidu võtab esimese tsükli miskist x kohast
 			if($gwo["start_hour"])
 			{
	 			$this->start = $this->start+3600*$gwo["start_hour"];
 				$today_start = $today_start+3600*$gwo["start_hour"];
 			}
 			if($gwo["start_minute"])
	 		{	
 				$this->start = $this->start+60*$gwo["start_minute"];
 				$today_start = $today_start+60*$gwo["start_minute"];
	 		}
		}
		else
		{
			if($start_hour == 24)
			{
				$start_hour = 0;
			}
			$this->start = $today_start = mktime($start_hour, $start_minute, 0, date("n", time()), date("j", time()), date("Y", time()));
		}

		$step = 0;
		$step_length = $this->step_lengths[$arr["obj_inst"]->prop("time_unit")];
		exit_function("get_calendar_tbl::2");

		$settings = $this->get_settings_for_room($arr["obj_inst"]);

		classload("core/date/date_calc");
		if (is_oid($settings->id()) && !$arr["request"]["start"])
		{
			if ($settings->prop("cal_from_today"))
			{
				$this->start = $today_start = get_day_start();
			}
			else
			{
				$this->start = $today_start = get_week_start();
			}

			//seda avamise alguse aega peab ka ikka arvestama, muidu võtab esimese tsükli miskist x kohast
			if($gwo["start_hour"])
			{
				$this->start = $this->start+3600*$gwo["start_hour"];
				$today_start = $today_start+3600*$gwo["start_hour"];
			}
			if($gwo["start_minute"])
			{
				$this->start = $this->start+60*$gwo["start_minute"];
				$today_start = $today_start+60*$gwo["start_minute"];
			}
		}
		enter_function("get_calendar_tbl::3");
		$len = 7;
		if ($_GET["start"] && $_GET["end"])
		{
			$len = floor(($_GET["end"] - $_GET["start"]) / 86400);
		}
		$this->generate_res_table($arr["obj_inst"], $this->start, $this->start + 24*3600*$len);
		$this->_init_calendar_t($t,$this->start, $len);
//arr($this->res_table);
		$arr["step_length"] = $step_length * $arr["obj_inst"]->prop("time_step");
		
		$steps = (int)(86400 - (3600*$gwo["start_hour"] + 60*$gwo["start_minute"]))/($step_length * $arr["obj_inst"]->prop("time_step"));
		// this seems to fuck up in reval room calendar view and only display time to 15:00
		//while($step < floor($steps))
		//arr(86400/($step_length * $arr["obj_inst"]->prop("time_step")));
		while($step < 86400/($step_length * $arr["obj_inst"]->prop("time_step")))
		{
			$d = $col = $ids = $rowspan = $onclick = array();
			$x = 0;
			$start_step = $today_start + $step * $step_length * $arr["obj_inst"]->prop("time_step");
			$end_step = $start_step + $step_length * $arr["obj_inst"]->prop("time_step");
			$visible = 0;
			while($x<$len)
			{
				if(!is_object($this->openhours) || $this->is_open($start_step,$end_step))
				{
					$visible=1;
					$rowspan[$x] = 1;
					if($this->check_if_available(array(
						"room" => $arr["obj_inst"]->id(),
						"start" => $start_step,
						"end" => $end_step,
						
					)))
					{
						$arr["timestamp"] = $start_step;
						$prod_menu="";
						if($arr["obj_inst"]->prop("use_product_times"))
						{
							$arr["menu_id"] = "menu_".$start_step."_".$arr["obj_inst"]->id();
							$img_id = 'm_'.$arr["obj_inst"]->id().'_'.$start_step;
							$prod_menu = '<a class="menuButton" href="javascript:void(0)" onclick="bron_disp_popup(\'bron_menu_'.$arr["obj_inst"]->id().'\', '.$start_step.',\''.$img_id.'\');" alt="" title="" id=""><img alt="" title="" border="0" src="'.aw_ini_get("icons.server").'/class_.gif" id="'.$img_id.'" ></a>';
						}
						else
						if ($settings->prop("bron_popup_immediate") && is_admin())
						{
							$onclick[$x] = "doBronExec('".$arr["obj_inst"]->id()."_".$start_step."', ".($step_length * $arr["obj_inst"]->prop("time_step")).")";
						}
						else
						{
							$onclick[$x] = "doBron('".$arr["obj_inst"]->id()."_".$start_step."' , ".($step_length * $arr["obj_inst"]->prop("time_step")).")";
							//$string = t("VABA");
						}

						if (!$this->group_can_do_bron($settings, $start_step))
						{
							$onclick[$x] = "";
							$prod_menu = "";
						}
						$val = 0;
						$string = t("VABA");
						$col[$x] = "#E1E1E1";
						if($_SESSION["room_reservation"][$arr["obj_inst"]->id()]["start"]<=$start_step && $_SESSION["room_reservation"][$arr["obj_inst"]->id()]["end"]>=$end_step)
						{
							//teeb selle kontrolli ka , et äkki tüübid ültse teist ruumi tahavad juba... et siis läheks sassi
							if(!$_SESSION["room_reservation"]["room_id"] || $_SESSION["room_reservation"]["room_id"] == $arr["obj_inst"]->id() || in_array($arr["obj_inst"]->id(), $_SESSION["room_reservation"]["room_id"]))
							{
								$val = 1;
								$col[$x] = "red";
								$string = t("Broneeri");
							}
						}
						$d[$x] = "<span>".$string."</span>".html::hidden(array("name"=>'bron['.$arr["obj_inst"]->id().']['.$start_step.']' , "value" =>$val)). " " . $prod_menu;
					}
					else
					{
						if(is_oid($this->last_bron_id) && !$arr["web"])
						{
							$last_bron = obj($this->last_bron_id);
							$cus = t("BRON");
							$title = "";
							$codes = array();
							if(is_oid($last_bron->prop("customer")) && $this->can("view", $last_bron->prop("customer")))
							{
								$customer = obj($last_bron->prop("customer"));
								$cus = $customer->name();
								$products = $last_bron->meta("amount");
								$title = $last_bron->prop("content");
								if(trim($last_bron->prop("comment")) != "")
								{
									$title.=", ".$last_bron->prop("comment");
								}
								foreach($products as $prod => $val)
								{
									if($val)
									{
										if($this->can("view" , $prod))
										{
											$product = obj($prod);
											if ($product->prop("code") != "")
											{
												$codes[] = $product->prop("code");
											}
											$title .= " ".$product->name();
										}
									}
								}
							}
							$dx_p = array(
								"url" => html::get_change_url($this->last_bron_id,array("return_url" => get_ru(),)),
								"caption" => "<span><font color=#26466D>".$cus . " " . join($codes , ",")."</FONT></span>",
								"title" => $title,
							);
							
							if ($settings->prop("cal_show_prods"))
							{
								$dx_p["caption"] .= " ".$title;
							}
							
							if ($settings->prop("bron_no_popups"))
							{
								$d[$x] = html::href($dx_p);
							}
							else
							{
								$dx_p["width"] = 800;
								$dx_p["height"] = 600;
								$dx_p["scrollbars"] = 1;
								$dx_p["href"] = "#";
								$d[$x] = html::popup($dx_p);
							}
							$b_len = $last_bron->prop("end") - $last_bron->prop("start1");
							if ($settings->prop("col_buffer") != "" && !$settings->prop("disp_bron_len"))
							{
								$buf_tm = sprintf("%02d:%02d", floor($b_len / 3600), ($b_len % 3600) / 60);
								$d[$x] .= " ".$buf_tm;
							}
							if ($last_bron->prop("time_closed") == 1)
							{
								$col[$x] = "#".$settings->prop("col_closed");
								$d[$x] .= " ".$last_bron->prop("closed_info");
							}
							else
							if($last_bron->prop("verified"))
							{
								$col[$x] = $this->get_colour_for_bron($last_bron, $settings);
							}
							else
							{
								$col[$x] = $settings->prop("col_web_halfling") != "" ? $settings->prop("col_web_halfling") : "#FFE4B5";
							}

							if ($last_bron->prop("content") != "" || $last_bron->comment() != "")
							{
								$d[$x] .= html::href(array(
									"url" => "#",
									"caption" => "*",
									"title" => $last_bron->prop("content")." ".$last_bron->comment()
								));
							}
							if(($last_bron->prop("end") - $start_step) / ($step_length * $arr["obj_inst"]->prop("time_step")) >= 1)
							{
								$rowspan[$x] = (int)((
									$last_bron->prop("end")
									+ $this->get_after_buffer(array("room" => $arr["obj_inst"], "bron" => $last_bron))
									 - $start_step)
									 / ($step_length * $arr["obj_inst"]->prop("time_step"))) ;
								if((($last_bron->prop("end")+$this->get_after_buffer(array("room" => $arr["obj_inst"], "bron" => $last_bron)) - $start_step) % ($step_length * $arr["obj_inst"]->prop("time_step"))))
								{
									$rowspan[$x]++;
								}
							}

							if ($settings->prop("col_buffer") != "")
							{
								$buf = $this->get_before_buffer(array(
									"room" => $arr["obj_inst"],
									"bron" => $last_bron,
								));
								if ($buf > 0)
								{
									$buf_tm = "";
									if (!$settings->prop("disp_bron_len"))
									{
										$buf_tm = sprintf("%02d:%02d", floor($buf / 3600), ($buf % 3600) / 60);
									}
									$d[$x] = "<div style='position: relative; left: -7px; background: #".$settings->prop("col_buffer")."'>".$settings->prop("buffer_time_string")." ".$buf_tm."</div><div style='padding-left: 5px; height: 90%'>".$d[$x]."</div>";
								}

								$buf = $this->get_after_buffer(array(
									"room" => $arr["obj_inst"],
									"bron" => $last_bron,
								));
								if ($buf > 0)
								{
									$buf_tm = "";
									if (!$settings->prop("disp_bron_len"))
									{
										$buf_tm = sprintf("%02d:%02d", floor($buf / 3600), ($buf % 3600) / 60);
									}
									$d[$x] .= " <div style='position: relative; left: -7px; background: #".$settings->prop("col_buffer")."'>".$settings->prop("buffer_time_string")." ".$buf_tm."</div>";
								}
							}
							//$d[$x] = "<table border='1' style='width: 100%; height: 100%'><tr><td>".$d[$x]."</td></tr></table>";
						}
						else
						{
							$col[$x] = "#EE6363";
					 		$d[$x] ="<span><font color=#26466D>".t("BRON")."</FONT></span>";
						}
						$onclick[$x] = "";
					}
				}
				else
				{
					$d[$x] = "<span>".t("Suletud")."</span>";
				}
				//$ids[$x] = $arr["room"]."_".$start_step;
				$ids[$x] = $arr["obj_inst"]->id()."_".$start_step;
				$x++;
				$start_step += 86400;
				$end_step += 86400;
//				$today_start += 86400;
			}
			if($visible)
			{
				$tmp_row_data = array(
					"time" => date("G:i" , $today_start+ $step*$step_length*$arr["obj_inst"]->prop("time_step"))
				);
				for($i = 0; $i < $len; $i++)
				{
					$tmp_row_data["d".$i] = $d[$i];
					$tmp_row_data["id".$i] = $ids[$i];
					$tmp_row_data["onclick".$i] = $onclick[$i];
					$tmp_row_data["col".$i] = $col[$i];
					$tmp_row_data["rowspan".$i] = $rowspan[$i];
				}
				$t->define_data($tmp_row_data);
			}
			$step = $step + 1;
		}
		exit_function("get_calendar_tbl::3");
		//$t->set_rgroupby(array("group" => "d2"));
		$popup_menu = $this->get_room_prod_menu($arr, ($settings->prop("bron_popup_immediate") && is_admin()));
		$t->set_caption(t("Broneerimine").$popup_menu);
	}
	
	function get_room_prod_menu($arr, $immediate = false)
	{
		$res = '<div class="menu" id="bron_menu_'.$arr["obj_inst"]->id().'" style="display: none;">';

		$m_oid = $arr["obj_inst"]->id();
		$this->prod_data = $arr["obj_inst"]->meta("prod_data");
		$item_list = $this->get_active_items($arr["obj_inst"]->id());
		$prod_list = $item_list->names();
		$times = array();
		foreach($prod_list as $oid => $pname)
		{
			$times[$oid] = $this->cal_product_reserved_time(array("id" => $m_oid, "oid" => $oid));
		}
		foreach($prod_list as $oid => $name)
		{
			$res .='<a class="menuItem" href="#"  onClick="'.($immediate? "doBronExec" : "doBron").'(
					\''.$m_oid.'_\'+current_timestamp ,
					'.$arr["step_length"].' ,
					'.$times[$oid].' ,
					'.$oid.');">'.$name.'</a>';
		}


		$res .= '</div>';
		return $res;
	}
	
	function get_colour_for_bron($bron, $settings)
	{
		$gc = $settings->meta("grp_cols");
		if (!is_array($gc) || count($gc) == "")
		{
			return "#EE6363"; //#FFE4B5";
		}

		$u = get_instance(CL_USER);
		$grp = $u->get_highest_pri_grp_for_user($bron->createdby(), true);
		if ($grp && !empty($gc[$grp->id()]))
		{
			return "#".$gc[$grp->id()];
		}
		return "#EE6363";
	}
	
	function get_after_buffer($arr)
	{
		extract($arr);
		if(!is_object($room))
		{
			return 0;
		}
		
		if(is_object($room) && $room->prop("use_product_times") && is_object($bron))
		{
			return $this->get_products_buffer(array("bron" => $bron, "time" => "after"));
		}
		elseif(is_object($room))
		{
			return $room->prop("buffer_after")*$room->prop("buffer_after_unit");
		}
	}
	
	function get_before_buffer($arr)
	{
		extract($arr);
		if(!is_object($room))
		{
			return 0;
		}
		
		if(is_object($room) && $room->prop("use_product_times") && is_object($bron))
		{
			return $this->get_products_buffer(array("bron" => $bron, "time" => "before"));
		}
		elseif(is_object($room))
		{
			return $room->prop("buffer_before")*$room->prop("buffer_before_unit");
		}
	}
		
	function get_when_opens()
	{
		if(!$this->open_inst)
		{
			$this->open_inst = get_instance(CL_OPENHOURS);
		}
		$start_hour = 0;
		$start_minute = 0;
		$opens = $this->open_inst->get_opening_time($this->openhours);
		return array("start_hour" => $opens["hour"], "start_minute" => $opens["minute"]);
	}
	
	function is_open($start,$end)
	{
		if(!$this->open_inst)
		{
			$this->open_inst = get_instance(CL_OPENHOURS);
		}
		$end_this = (date("H" , $end-1)*3600 + date("i" , $end-1)*60);
		$start_this = (date("H" , $start)*3600 + date("i" , $start)*60);
		
		//kontrollib et tsükli lõpp äkki läheb järgmisesse päeva juba... siis oleks lõpp kuidagi varajane ja avatud oleku kontroll läheks puusse
		if($start_this > $end_this)
		{
			$end_this+=24*3600;
		}
		$open = $this->open_inst->get_times_for_date($this->openhours, $start);
		if(
			is_array($open) && 
			($open[0] || $open[1]) && 
			($open[1]-1 >= $end_this) && 
			($open[0] <= $start_this)
		)
		{
			return true;
		}
		else return false;
	}

	function get_prod_menu($arr, $immediate = false)
	{
		enter_function("get_calendar_tbl::get_prod_menu");
		$m_oid = $arr["obj_inst"]->id();
		static $prod_list;
		static $times;
		if ($prod_list == null)
		{
			$this->prod_data = $arr["obj_inst"]->meta("prod_data");
			$item_list = $this->get_active_items($arr["obj_inst"]->id());
			$prod_list = $item_list->names();
			$times = array();
			foreach($prod_list as $oid => $pname)
			{
				$times[$oid] = $this->cal_product_reserved_time(array("id" => $m_oid, "oid" => $oid));
			}
		}
		$ret = "";
		$pm = get_instance("vcl/popup_menu");
		$pm->begin_menu($arr["menu_id"]);
		foreach($prod_list as $oid => $name)
		{
			$pm->add_item(array(
				"text" => $name,
				"link" => "javascript:dontExecutedoBron=1;void(0)",
				"onClick" => ($immediate? "doBronExec" : "doBron")."(
					'".$m_oid."_".$arr["timestamp"]."' ,
					".$arr["step_length"]." ,
					".$times[$oid]." ,
					".$oid.");",
			),"CL_ROOM");
		}

		$ret.= $pm->get_menu(array(
			"icon" => icons::get_icon_url($package),
			//"icon" =>aw_ini_get("baseurl")."/automatweb/images/vaba.gif",
		));
		exit_function("get_calendar_tbl::get_prod_menu");
		return $ret;
	}

	function is_open_day($time)
	{
		if(!is_object($this->openhours))
		{
			return true;
		}
		if(!$this->open_inst)
		{
			$this->open_inst = get_instance(CL_OPENHOURS);
		}
		$open = $this->open_inst->get_times_for_date($this->openhours, $time);
		if($open[0] || $open[1])
		{
			return true;
		}
		else return false;
		
	}

	function _init_calendar_t(&$t,$time=0, $len = 7)
	{
		if(!$time)
		{
			$time = time();
		}
		
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"width" => "20px",
		));

		for($i = 0; $i < $len; $i++)
		{
			$tm = $time+$i*24*3600;
			if($this->is_open_day($tm))
			{
				$t->define_field(array(
					"name" => "d".$i,
					"caption" => substr(date("l" , $tm) , 0 , 2).date(" d/m/y" , $tm),// d/m/Y", $tm)//date("l d/m/Y", $tm),
					"width" => "20px",
					"chgbgcolor" => "col".$i,
					"id" => "id".$i,
					"onclick" => "onclick".$i,
					"rowspan" => "rowspan".$i,
				));
			}
		}
		
		$t->table_caption = t("Broneerimine");
		$t->set_sortable(false);
	}

	//see ruumi sees tehes, eeldusel, et pärast liigub edasi reserveerimise objekti vaatesse, kus valib asju... tregelt nüüd juba popup kõigepealt
	/**
		@attrib name=do_add_reservation params=name all_args=1
		@param id optional oid
			room id
		@param bron optional array
			keys are start timestamps
	**/
	function do_add_reservation($arr)
	{
		extract($arr);
		if(is_oid($arr["id"]))
		{
			foreach($bron as $room => $val)
			{
				$times = $this->_get_bron_time(array(
					"bron" => $val,
					"id" => $room,
					"room_reservation_length" => $room_reservation_length,
				));
				if ($times["start"])
				{
					$arr["id"] = $room;
					break;
				}
				if(!$arr["id"])
				{
					$arr["id"] = $room;
				}
			}

			extract($times);

			$room = obj($arr["id"]);
			if(is_object($room->get_first_obj_by_reltype("RELTYPE_CALENDAR")))
			{
					$cal_obj = $room->get_first_obj_by_reltype("RELTYPE_CALENDAR");
					$cal = $cal_obj->id();
					$parent = $cal_obj->prop("event_folder");
					$step = $room->prop("time_step");
					if (!$parent)
					{
						$parent = $cal_obj->id();
					}
					
					
			}
		}
		$product = $arr["product"];
		//arr($arr); arr($start); arr($end);
		//die();
		if (!$parent)
		{
			$parent = $arr["id"];
		}

		$settings = $this->get_settings_for_room($room);
		if ($settings->prop("bron_popup_detailed"))
		{
			$url = html::get_new_url(CL_RESERVATION, $parent, array(
				"return_url" => $arr["post_ru"],
				"start1" => $start,
				"calendar" => $cal,
				"end" => $end,
				"resource" => $arr["id"],
				"product" => $product,
			));
			$w = 1000;
			$h = 600;
		}
		else
		{
			$url = $this->mk_my_orb("admin_add_bron_popup", array(
                                "parent" => $parent,
                                "calendar" => $cal,
                                "start1" => $start,
                                "end" => $end,
                                "resource" => $arr["id"],
                                "return_url" => $arr["post_ru"],
                                "product" => $product,
                        ));
			$w = 500;
			$h = 400;
		}

		if ($settings->prop("bron_no_popups"))
		{
			header("Location: ".$url);
			die();
		}
		else
		{
			die("<script type='text/javascript'>
			window.open('$url','', 'toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=$h, width=$w');
			 
				window.location.href='".$arr["post_ru"]."';
			</script>");
		}
		
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

	function get_settings_for_room($room)
	{
		$si = get_instance(CL_ROOM_SETTINGS);
		$rv = $si->get_current_settings($room);
		if (!is_object($rv))
		{
			return obj();
		}
		return $rv;

		$settings = obj();
                if (is_array($room->prop("settings")))
                {
                        $set_id = reset($room->prop("settings"));
                }
                else
                {
                        $set_id = $room->prop("settings");
                }
                if ($this->can("view", $set_id))
                {
                        $settings = obj($set_id);
                }
		return $settings;
	}
	
	/**
		@attrib name=make_reservation params=name all_args=1
		@param id required oid
			room id
		@param res_id optional oid
			reservationid
		@param data required array
			propertys and stuff 
			products - array(product_id=> amount)
			start - reservation starts
			end - reservation ends
			comment
			people - number of people
			name - contact persons name
			email - contact persons email
			phone - contact persons phone
			customer - If the person object already exists, then connect the booking to this person, if given 
			verified - if true, reservation is marked as verified

		@param meta optional type=array
			Any key=>value paris given here, will be written to the objects metadata
	**/
	function make_reservation($arr)
	{
		extract($arr);
		if(is_oid($id) && $this->can("view", $id))
		{
			$room = obj($id);
			if(is_object($room->get_first_obj_by_reltype("RELTYPE_CALENDAR")))
			{
					$cal_obj = $room->get_first_obj_by_reltype("RELTYPE_CALENDAR");
					$cal = $cal_obj->id();
					$parent = $cal_obj->prop("event_folder");
					$step = $room->prop("time_step");
				if (!$parent)
				{
			       		$parent = $cal_obj->id();
			      	}
			}
			else return "";
		}
		else return "";

		if($this->can("view", $res_id))
		{
			$reservation = obj($res_id);
			$reservation->set_name($room->name()." bron ".date("d:m:Y" ,$data["start"]));
			$reservation->set_parent($parent);
			$reservation->set_prop("deadline", (time() + 15*60));
			$reservation->set_prop("resource" , $room->id());
		}
		else
		{
			$reservation = new object();
			$reservation->set_class_id(CL_RESERVATION);
			$reservation->set_name($room->name()." bron ".date("d:m:Y" ,$data["start"]));
			$reservation->set_parent($parent);
			$reservation->set_prop("deadline", (time() + 15*60));
			$reservation->set_prop("resource" , $room->id());
			$reservation->save();
		}

		if (is_array($arr["meta"]))
		{
			foreach($arr["meta"] as $meta_k => $meta_v)
			{
				$reservation->set_meta($meta_k, $meta_v);
			}
		}

		foreach($data as $prop => $val)
		{
			switch($prop)
			{
				case "products":
					$reservation->set_meta("amount" , $val);
					break;
				case "start":
					$reservation->set_prop("start1" , $val);
					break;
				case "end":
					$reservation->set_prop($prop , $val);
					break;
				case "comment":
					$reservation->set_prop("content" , $val);
					break;
				case "people":
					$reservation->set_prop("people_count" , $val);
					break;
				case "customer":
					$reservation->set_prop("customer", $val);
					break;
				case "verified":
					$reservation->set_prop("verified", $val);
					break;
			}
		}
		if($data["name"])
		{
			$customer = new object();
			$customer->set_class_id(CL_CRM_PERSON);
			$customer->set_name($data["name"]);
			list($fn , $ln) = explode(" ", $data["name"]);
			$customer->set_prop("firstname", $fn);
			$customer->set_prop("lastname", $ln);
			$customer->set_parent($parent);
			$customer->save();
			if($data["phone"])
			{
				$phone = new object();
				$phone->set_class_id(CL_CRM_PHONE);
				$phone->set_name($data["phone"]);
				$phone->set_prop("type" , "mobile");
				$phone->set_parent($parent);
				$phone->save();
				$customer->connect(array("to"=> $phone->id(), "type" => "RELTYPE_PHONE"));
				$customer->set_prop("phone", $phone->id());
			}
			if($data["email"])
			{
				$email = new object();
				$email->set_class_id(CL_ML_MEMBER);
				$email->set_name($data["email"]);
				$email->set_prop("mail" , $data["email"]);
				$email->set_parent($parent);
				$email->save();
				$customer->connect(array("to"=> $email->id(), "type" => "RELTYPE_EMAIL"));
				$customer->set_prop("email", $email->id());
			}
			$customer->save();
			$reservation->set_prop("customer" , $customer->id());
		}
		$reservation->set_name(sprintf(t("%s: %s / %s-%s %s"),
			$reservation->prop("customer.name"),
			date("d.m.Y", $reservation->prop("start1")),
			date("H:i", $reservation->prop("start1")),
                        date("H:i", $reservation->prop("end")),
                        $reservation->prop("resource.name")
		));
		$reservation->save();
		return $reservation->id();
	}

	/**
		@attrib name=get_bron_time params=name all_args=1 nologin=1
		@param id required oid
			room id
		@param bron optional array
			keys are start timestamps
		@param room_reservation_length optional double
			length/step
	**/
	function _get_bron_time($arr)
	{
		foreach($arr["bron"] as $key => $val)
		{
			if(!$val) unset($arr["bron"][$key]);
		}
		extract($arr);
		if(is_oid($arr["id"]))
		{
			$room = obj($arr["id"]);
			$length = $this->step_lengths[$room->prop("time_unit")] * $room->prop("time_step") ;
			$end = $arr["bron"][0];
			foreach($arr["bron"] as $bron => $val)
			{
				if(!$start)
				{
					$start = $bron;
					$end = $start + $length;
				}
				if(($end) == $bron)
				{
					$end = $bron + $length;
				}
			}
			if($room_reservation_length > 0)
			{
				$end = $start + $length * $room_reservation_length;
			}
		}
		return array("start" => $start, "end" => $end);
		
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
		if($data["products_find_product_name"])
		{
			$filter["name"] = "%".$data["products_find_product_name"]."%";
		}
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
				"class_id" => array(CL_MENU,CL_SHOP_PRODUCT,CL_SHOP_PRODUCT_PACKAGING),
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
				"active" =>  $prod_data[$o->id()]["active"],//html::checkbox(array(
	//				"name" => "sel_imp[".$o->id()."]",
	//				"value" => $o->id(),
	//				"checked" => $prod_data[$o->id()]["active"],
	//			)),
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
		$t->define_chooser(array(
			"name" => "active",
			"caption" => t("Aktiivne"),
			"field" => "oid",
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

/*		$t->define_field(array(
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
*/
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

		// list all shop product types and add them to the menu
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT_TYPE,
			"lang_id" => array(),
			"site_id" => array()
		));
		$tb->add_menu_separator(array("parent" => "crt_".$this->prod_type_fld));
		foreach($ol->arr() as $prod_type)
		{
			$tb->add_menu_item(array(
				"parent" => "crt_".$this->prod_type_fld,
				"text" => $prod_type->name(),
				"link" => $this->mk_my_orb("new", array(
                                                "item_type" => $prod_type->id(),
                                                "parent" => $this->prod_tree_root,
                                                //"alias_to" => $this->warehouse->id(),
                                                "reltype" => 2, //RELTYPE_PRODUCT,
                                                "return_url" => get_ru(),
                                                "cfgform" => $prod_type->prop("sp_cfgform"),
                                                "object_type" => $prod_type->prop("sp_object_type"),
						"pseh" => aw_register_ps_event_handler(
							CL_ROOM,
							"handle_product_add",
							array("id" => $data["obj_inst"]->id()),
							CL_SHOP_PRODUCT
						)
                                        ), CL_SHOP_PRODUCT) 
			));
		}
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

	function handle_product_add($product, $arr)
	{
		$room = obj($arr["id"]);
		$prod_data = $room->meta("prod_data");
		$prod_data[$product->id()]["active"] = 1;
		$room->set_meta("prod_data", $prod_data);
		$room->save();
	}

	/**
		@attrib name=save_products params=name all_args=1
	**/
	function save_products($arr)
	{
		$this_obj = obj($arr["id"]);
		$prod_data = $this_obj->meta("prod_data");
		foreach($arr["set_ord"]  as $id => $ord)
		{
			$prod_data[$id]["active"] = $arr["active"][$id];
			$prod_data[$id]["ord"] = $ord;
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
	
	function get_prod_tree_ids($o)
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
			
			$menu_list = $tree->to_list(array(
				"add_root" => true,
			));
			$parents = $menu_list->ids();
			foreach($parents as $key => $parent)
			{
				if(!$this->prod_data[$parent]["active"] && !($o->prop("resources_fld") == $parent))
				{
					unset($parents[$key]);
				}
			}
			return $parents;
		}
		else
		{
			return "";
		}
	}
	
	function get_folder_items($o,$menu)
	{
		if(!$this->active_items)
		{
			$ol = $this->get_active_items($o);
			$this->active_items = $ol->ids();
		}
		return new object_list(array(
			"lang_id" => array(),
			"parent" => $menu,
			"oid" => $this->active_items,
		));
	
	}
	
	//returns active packages
	function get_package_list($o)
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
		
		if($o->class_id() == CL_SHOP_PRODUCT)
		{
			$packages = $o->connections_from(array(
				"type" => "RELTYPE_PACKAGING",
			));
			foreach($packages as $conn)
			{
				$package = $conn->prop("to");
				if($this->prod_data[$package]["active"])
				{
					$ol->add($package);
				}
			}
		}
		if($o->class_id() == CL_ROOM)
		{
			$this->prod_data = $o->meta("prod_data");
			$parents = $this->get_prod_tree_ids($o);
			$ol = new object_list(array(
				"class_id" => CL_SHOP_PRODUCT_PACKAGING,
				"lang_id" => array(),
				"parent" => $parents,
			));
			foreach($ol->ids() as $package)
			{
				if(!$this->prod_data[$package]["active"])
				{
					$ol->remove($package);
				}
			}
		
		}
		return $ol;
	}

	//annab vastavalt ruumile siis kas pakendite või toodete object listi, mis on aktiivsed
	function get_active_items($o)
	{
		$ol = new object_list();
		if(is_oid($o))
		{
			$o = obj($o);
		}
		if(!is_object($o) || !$o->class_id() == CL_ROOM)
		{
			return $ol;
		}
		$warehouse = obj($o->prop("warehouse"));
		if(!is_oid($warehouse->prop("conf")))
		{
			return $ol;
		}
		$conf = obj($warehouse->prop("conf"));
		if($conf->prop("sell_prods"))
		{
			return $this->get_prod_list($o);
		}
		else
		{
			$prods = $this->get_prod_list($o);
			foreach($prods->arr() as $product)
			{
				$ol->add($this->get_package_list($product));
			}
		}
		return $ol;
	}
	
	//returns active products
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
			$parents = $o->id();
		}
		else
		{
			$this->prod_data = $o->meta("prod_data");
			$parents = $this->get_prod_tree_ids($o);
		}
		
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"parent" => $parents,
		));
		foreach($ol->ids() as $prod)
		{
			if(!$this->prod_data[$prod]["active"])
			{
				$ol->remove($prod);
			}
		}
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
		@param products optional type=array, -1
			products you want to order with room , -1 if room price without products
		@param detailed_info type=bool default=false
			if set to true, data is returned in detail, separate entries for products and everything
	**/
	function cal_room_price($arr)
	{
		extract($arr);		
		if(!is_oid($room))
		{
			return 0;
		}
		$room = obj($room);
		$this->bargain_value = array();
		$this->step_length = $this->step_lengths[$room->prop("time_unit")];
		$sum = array();
		$rv = array();	
		
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
		while($time >= 60)//alla minuti ei ole oluline aeg eriti..
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
			$rv["room_bargain"] = $bargain;
			foreach($price->meta("prices") as $currency => $hr_price)
			{//arr($hr_price); arr($hr_price - $bargain*$hr_price);arr("");
				$sum[$currency] += ($hr_price - $bargain*$hr_price);//+1 seepärast, et lõppemise täistunniks võetakse esialgu ümardatud allapoole tunnid... et siis ajale tuleb üks juurde liita, sest poolik tund läheb täis tunnina arvesse
				$this->bargain_value[$currency] = $this->bargain_value[$currency] + $bargain*$hr_price;
			}
			$time = $time - ($price->prop("time") * $this->step_length);
		}
		
		$rv["room_price"] = $sum;
		$rv["room_bargain_value"] = $this->bargain_value;
		
		$warehouse = $room->prop("warehouse");
		if(is_oid($warehouse) && $this->can("view" , $warehouse))
		{
			$w_obj = obj($warehouse);
			$w_cnf = obj($w_obj->prop("conf"));
			if(is_oid($w_obj->prop("order_center")) && $this->can("view" , $w_obj->prop("order_center")))
			{
				$soc = obj($w_obj->prop("order_center"));
				if($room->prop("prod_discount_loc"))
				{
					$prod_discount = $room->prop("prod_web_discount");
				}
				else
				{
					$prod_discount = $soc->prop("web_discount");
				}
			}
		}
		
		$rv["prod_discount"] = $prod_discount;
		foreach($room->prop("currency") as $currency)
		{
			if(!$sum[$currency])
			{
				$sum[$currency] = 0;
			}
			if($people > $room->prop("normal_capacity"))
			{
				$sum[$currency] += ($people-$room->prop("normal_capacity")) * $room->prop("price_per_face_if_too_many"); 
				$rv["room_price"][$currency] += ($people-$room->prop("normal_capacity")) * $room->prop("price_per_face_if_too_many");
			}
//			if(is_array($products) && sizeof($products))
			if(!($products == -1))
			{
				$tmp = $this->cal_products_price(array(
					"products" => $products,
					"currency" => $currency,
					"prod_discount" => $prod_discount,
					"room" => $room,
				));
				$sum[$currency] += $tmp;
				$rv["prod_price"][$currency] += $tmp;

				// calculate the amount of money saved by the discount back from the discounted price
				$adv = 100 - $prod_discount;
				$rv["prod_discount_value"][$currency] = ((100.0 * $tmp) / $adv) - $tmp;
			}
		}
		if ($arr["detailed_info"])
		{
			return $rv;
		}
		else
		{
			return $sum;
		}
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
						return 0.01*$bargain->prop("bargain_percent")*($end - mktime($from["hour"], $from["minute"], 0, date("m",$start), date("d",$start), date("y",$start)))/($end-$start);
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
		@attrib name=cal_product_reserved_time params=name all_args=1 nologin=1
		@param oid required type=oid
			products and their amounts
		@return int
			min time to reserve
	**/
	function cal_product_reserved_time($arr)
	{
		extract($arr);
		if(is_oid($arr["oid"]) && $this->can("view", $arr["oid"]))
		{
			$product=obj($arr["oid"]);
			return ($product->prop("reservation_time")*$product->prop("reservation_time_unit")+$product->prop("buffer_time_after")*$product->prop("buffer_time_unit"));
		}
		return 0;
	}
	
	/**
		@attrib params=name
		@param products required type=array
			products and their amounts
		@param currency optional type=oid
			if you want result in not the same currency the company uses.
		@param prod_discount optional type=int
		@param room optional type=object
			room object
		@return int
			price of all products
	**/
	function cal_products_price($arr)
	{
		extract($arr);
			
		if(is_array($products) && sizeof($products))//kui tooteid pole, võiks selle osa vahele jätta... võibolla võidab paar millisekundit
		{
			if(is_object($room))
			{
				$prod_discount = $this->get_prod_discount(array("room" => $room->id()));
			}
			$sum = 0;
			foreach($products as $id => $amt)
			{
				if($amt && $this->can("view", $id))
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
				//võtab toote hinnalt toodete allahindluse maha
			if($prod_discount)
			{
				$this->last_discount = $sum*0.01*$prod_discount;
				$sum = $sum-$this->last_discount;
			}
		}
		
		//ja juhul kui jääb alla miinimumi, siis jääb miinimum
		if(is_object($room) && is_oid($currency))
		{
			$min = $room->meta("web_min_prod_prices");
			if($sum < $min[$currency])
			{
				$sum = $min[$currency];
			}
		}
		return $sum;
	}

	function get_prod_discount($arr)
	{
		extract($arr);
		if(is_oid($room) && $this->can("view" , $room))
		{
			$o = obj($room);
		}
		if(!is_object($o))
		{
			return 0;
		}
		$prod_discount = 0;
		$warehouse = $o->prop("warehouse");
		if($o->prop("prod_discount_loc"))
		{
			$prod_discount = $o->prop("prod_web_discount");
		}
		else
		{
			if(is_oid($warehouse) && $this->can("view" , $warehouse))
			{
				$w_obj = obj($warehouse);
				$w_cnf = obj($w_obj->prop("conf"));
				if(is_oid($w_obj->prop("order_center")) && $this->can("view" , $w_obj->prop("order_center")))
				{
					$soc = obj($w_obj->prop("order_center"));
					$prod_discount = $soc->prop("web_discount");
				}
			}
		}
		return $prod_discount;
	}
	
	function check_from_table($arr)
	{
		foreach($this->res_table as $key => $val)
		{
			if($key > $arr["end"])
			{
				return true;
			}
			if($val["end"] > $arr["start"])
			{//if($key == 1169301600){arr(date("h:i" , arr($key))); arr(date("h:i", $arr["end"]));}
				if($key < $arr["end"])
				{
					$this->last_bron_id = $val["id"];
					return false;
				}
			}
		}
		return true;
	}

	function generate_res_table($room, $start = 0, $end = 0)
	{
		if(!$this->start)
		{
			classload("core/date/date_calc");
			$this->start =get_week_start();
		}

		if ($start == 0)
		{
			$start = $this->start;
		}
		if ($end == 0)
		{
			$end = $this->start + (7*24*3600);
		}
		$step_length = $this->step_lengths[$room->prop("time_unit")];
		$filt = array(
			"class_id" => array(CL_RESERVATION),
			"lang_id" => array(),
			"resource" => $room->id(),
			"start1" => new obj_predicate_compare(OBJ_COMP_LESS, $end),
			"end" => new obj_predicate_compare(OBJ_COMP_GREATER, $start),
		);
		$use_prod_times = $room->prop("use_product_times");
		
		$reservations = new object_list($filt);
		$this->res_table = array();
		foreach($reservations->arr() as $res)
		{
			if($res->prop("verified") || ($res->prop("deadline") > time()))
			{
				if($use_prod_times)
				{
					$start = $res->prop("start1")-$this->get_products_buffer(array("bron" => $res, "time" => "before"));
					$this->res_table[$start]["end"] = $res->prop("end") + $this->get_products_buffer(array("bron" => $res, "time" => "after"));
				}
				else
				{
					$start = $res->prop("start1")-$room->prop("buffer_before")*$room->prop("buffer_before_unit");
					$this->res_table[$start]["end"] = $res->prop("end") + $room->prop("buffer_after")*$room->prop("buffer_after_unit");
				}
				if($res->prop("verified"))
				{
					$this->res_table[$start]["verified"] = 1;
				}
				$this->res_table[$start]["real_end"] = $res->prop("end");
				$this->res_table[$start]["real_start"] = $res->prop("start1");
				$this->res_table[$start]["id"] = $res->id();
			}
		}
		ksort($this->res_table);
	}

	/**
		@attrib params=name
		@param room required type=oid
			room id
		@param start required type=int
		@param end required type=int
		@param ignore_booking optional type=int
			If given, the booking with this id will be ignored in the checking - this can be used for changing booking times for instance
		@return boolean
	**/
	function check_if_available($arr)
	{
		if(is_array($this->res_table))
		{
			return $this->check_from_table($arr);
		}
		
		extract($arr);
		if(!(is_oid($room) && $this->can("view" , $room)))
		{
			return false;
		}
		$room = obj($room);
		$buff_before = $room->prop("buffer_before")*$room->prop("buffer_before_unit");
		$buff_after = $room->prop("buffer_after")*$room->prop("buffer_after_unit");
	
		//tootepõhisel ruumi broneerimisel
		if($room->prop("use_product_times"))
		{
			$last_bron = $this->get_last_bron(array("room" => $room , "start" => $start));
			$next_bron = $this->get_next_bron(array("room" => $room , "end" => $end));
			$buffer_start = $this->get_products_buffer(array("bron" => $last_bron, "time" => "after"));
			$buffer_end = $this->get_products_buffer(array("bron" => $next_bron, "time" => "before"));
		}
		else
		{
		//	$buffer = $buffer_end = $buffer_start = $buff_before + $buff_after;
			$buffer_end = $buff_before;
			$buffer_start =$buff_after;
		}

		$buffer = $buff_before+$buff_after;
		$filt = array(
			"class_id" => array(CL_RESERVATION),
			"lang_id" => array(),
			"resource" => $room->id(),
			"start1" => new obj_predicate_compare(OBJ_COMP_LESS, ($end+$buffer_end)),
			"end" => new obj_predicate_compare(OBJ_COMP_GREATER, ($start-$buffer_start)),
		);

		if (!empty($arr["ignore_booking"]))
		{
			$filt["oid"] = new obj_predicate_not($arr["ignore_booking"]);
		}
		$reservations = new object_list($filt);
		
		//ueh... filter ei tööta, niiet .... oehjah
		$verified_reservations = new object_list();
		foreach($reservations->arr() as $res)
		{
			if($res->prop("verified"))
			{
				$verified_reservations->add($res->id());
				//$reservations->remove($res->id());
			}
			elseif(!($res->prop("deadline") > time()))
			{
				$reservations->remove($res->id());
			}
	//		$booked[] = array("start" => $res->prop("start1"), "end" => $res->prop("end"));
		}
		
		if(!sizeof($reservations->arr()))
		{
			return true;
		}
		else
		{
			if(sizeof($verified_reservations->arr()))
			{
				$bron = reset($verified_reservations->arr());
			}
			else
			{
				$bron = reset($reservations->arr());
			}	
			$this->last_bron_id = $bron->id();
			return false;
		}
	}
	
	/** returns int (reservation products buffer time)
		@attrib api=1 params=name
		@param $bron required type=object
			The reservation object
		@param $time optional type=string
			if "before" , calculates before buffer times, if "after", calculates after buffer times, if not set, calculates both
	**/
	function get_products_buffer($arr)
	{
		extract($arr);
		if(!is_object($bron))
		{
			return 0;
		}
		$products = $bron->meta("amount");
		$ret = 0;
		if(is_array($products))
		{
			foreach($products as $product=> $amount)
			{
				if($amount && $this->can("view" , $product))
				{
					$prod = obj($product);
					if(!$time)
					{
						$ret = $ret + $prod->prop("buffer_time_before") + $prod->prop("buffer_time_after")*$prod->prop("buffer_time_unit");
					}
					else
					{
						$ret = $ret + $prod->prop("buffer_time_".$time)*$prod->prop("buffer_time_unit");
					}
				}
			}
		}
		return $ret;
	}
	
	/** returns object (last bron object before start time)
		@attrib api=1 params=name
		@param $room required type=object
			The room object
		@param $start required type=int
			last reservation before that timestamp
	**/
	function get_last_bron($arr)
	{
	
		extract($arr);
		$ret = ""; $max = $start - 24*3600;
		$reservations = new object_list(array(
			"class_id" => array(CL_RESERVATION),
			"lang_id" => array(),
			"resource" => $room->id(),
			"end" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, ($start - 24*3600) , $start),
			"verified" => 1,
		));
		
		foreach($reservations->arr() as $res)
		{
			if($res->prop("end") > $max)
			{
				$ret = $res; $max = $res->prop("end");
			}
		}
		return $ret;
	}
	
	
	/** returns object (first reservation object after end time)
		@attrib api=1 params=name
		@param $room required type=object
			The room object
		@param $end required type=int
			first reservation object after that timestamp
	**/
	function get_next_bron($arr)
	{
		extract($arr);
		$ret = ""; $min = $end + 24*3600;
		$reservations = new object_list(array(
			"class_id" => array(CL_RESERVATION),
			"lang_id" => array(),
			"resource" => $room->id(),
			"start1" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $end, ($end + 24*3600)),
		//	"start1" => new obj_predicate_compare(OBJ_COMP_LESS, ()),//24h hiljem pole ka vaja enam
			"verified" => 1,
		));
		foreach($reservations->ids() as $id)
		{
			$res = obj($id);
			if($res->prop("start1") < $min && $res->prop("start1")>100)
			{
				$ret = $res; $min = $res->prop("start1");
			}
		}
		return $ret;
		
	}
	
	function callback_generate_scripts($arr)
	{
		return 'doLoad(600000);
			var sURL = unescape(window.location.href);
			function doLoad()
			{
			setTimeout( "refresh()", 600000 );
			}
			function refresh()
			{
				window.location.href = sURL;
			}
		';
	}

	function cb_gen_web_min_prices($arr)
	{
		$cur = $arr["obj_inst"]->prop("currency");
		$prices = $arr["obj_inst"]->meta("web_min_prod_prices");
		foreach(safe_array($cur) as $cur)
		{
			if (!is_oid($cur))
			{
				continue;
			}
			$c = obj($cur);
			$retval["wpm_currency[".$cur."]"] = array(
                               "name" => "wpm_currency[".$cur."]",
                               "type" => "textbox",
                               "caption" => sprintf(t("Min hind toodetele veebis (%s)"), $c->prop("unit_name")),
                               "value" => $prices[$cur],
                               "editonly" => 1,
			       "size" => 5
                        );
		}
		return $retval;
	}

        /**
                @attrib name=delete_cos
        **/
        function delete_cos($arr)
        {
                object_list::iterate_list($arr["sel"], "delete");
                return $arr["post_ru"];
        }

	function gen_min_prices_props($arr)
	{
		$curs = $arr["obj_inst"]->prop("currency");
		$prices = $arr["obj_inst"]->meta("web_room_min_price");
		$retval = array();
		foreach($curs as $cur)
		{
			if(!is_oid($cur))
			{
				continue;
			}
			$c = obj($cur);
			$retval["web_room_min_price[".$cur."]"] = array(
				"name" => "web_room_min_price[".$cur."]",
				"type" => "textbox",
				"size" => 4,
				"caption" => $c->prop("unit_name"),
				"value" => $prices[$cur],
				"editonly" => 1,
			);
		}
		return $retval;
	}

	/** checks if the group bron time settings allow the bron to be changed/created in that time
		@attrib api=1
	**/
	function group_can_do_bron($s, $tm)
	{
		$gpt = $s->meta("grp_bron_tm");
		$grp = $this->ui->get_highest_pri_grp_for_user(aw_global_get("uid"), true);
		if (isset($gpt[$grp->id()]))
		{
			$t = $gpt[$grp->id()];
			if ($t["from"] > 0 || true)
			{
				$from_sec = 0;
				$cur_tm = time();
				switch($t["from_ts"])
				{
					case "min":
						$from_sec = $t["from"] * 60;
						break;

					case "hr":
						$from_sec = $t["from"] * 3600;
						break;

					default:
					case "day":
						$cur_tm = get_day_start();
						$from_sec = $t["from"] * 3600 * 24;
						break;
				}
				$can_bron_to = $cur_tm + $from_sec;
				if ($tm > $can_bron_to)
				{
					return false;
				}
			}

			if ($t["to"] > 0 || true)
			{
				$to_sec = 0;
				$cur_tm = time();
				switch($t["to_ts"])
				{
					case "min":
						$to_sec = $t["to"] * 60;
						break;

					case "hr":
						$to_sec = $t["to"] * 3600;
						break;

					default:
					case "day":
						$cur_tm = get_day_start();
						$to_sec = $t["to"] * 3600 * 24;
						break;
				}
				$can_bron_from = $cur_tm + $to_sec;
				if ($tm < $can_bron_from)
				{
					return false;
				}
			}
		}
		return true;
	}
	
	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}
}
?>
