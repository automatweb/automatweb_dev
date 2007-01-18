<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/reservation.aw,v 1.31 2007/01/18 12:43:44 kristo Exp $
// reservation.aw - Broneering 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_RESERVATION, on_delete_reservation)

@tableinfo planner index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_RESERVATION relationmgr=yes no_status=1 prop_cb=1

@default table=objects
@default group=general
#TAB GENERAL

@groupinfo general caption=&Uuml;ldine default=1 icon=edit focus=cp_fn

@layout general_split type=hbox

@layout general_up type=vbox closeable=1 area_caption=&Uuml;ldinfo parent=general_split

	@layout general_up_up parent=general_up type=vbox
	
@default parent=general_up_up

	@property b_tb type=toolbar store=no no_caption=1

	@property name type=textbox field=name method=none size=20
	@caption Nimi
	
	@property deadline type=datetime_select table=planner field=deadline
	@caption Maksmistähtaeg
			
	@property verified type=checkbox ch_value=1 field=meta method=serialize no_caption=1 default=1
	@caption Kinnitatud

	@property unverify_reason type=text store=no no_caption=1

	@property resource type=relpicker reltype=RELTYPE_RESOURCE field=meta method=serialize
	@caption Ressurss
	
	@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER
	@caption Klient

	@property cp_fn type=textbox store=no size=20
	@caption Eesnimi

	@property cp_ln type=textbox store=no size=20
	@caption Perenimi
	
	@property cp_phone type=textbox store=no size=12
	@caption Telefon
	
	@property cp_email type=textbox store=no size=20
	@caption E-mail
	
	@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT
	@caption Projekt
	
	@property send_bill type=checkbox ch_value=1 table=planner field=send_bill no_caption=1
	@caption Saata arve
	
	@property bill_no type=hidden table=planner 
	@caption Arve number
	
	@property comment type=textarea cols=40 rows=1
	@caption Kommentaar
	
	@property content type=textarea cols=40 rows=5 field=description table=planner
	@caption Sisu
	
	@property time_closed type=checkbox ch_value=1 table=objects field=meta method=serialize
	@caption Suletud

	@property closed_info type=textbox table=objects field=meta method=serialize size=30
	@caption Sulgemise p&otilde;hjus

	@layout sbt_layout type=hbox parent=general_up
	
	@property sbt type=submit no_caption=1 parent=sbt_layout
	@caption Salvesta

	@property sbt_close type=submit no_caption=1 parent=sbt_layout
	@caption Salvesta ja sulge
	
@layout general_down type=vbox closeable=1 area_caption=Aeg&#44;&nbsp;ja&nbsp;hind parent=general_split
@default parent=general_down
	
	@property people_count type=textbox size=3 field=meta method=serialize
	@caption Inimesi
		
	@property start1 type=datetime_select field=start table=planner
	@caption Algus

	@property length type=select store=no 
	@caption Pikkus

	@property end type=datetime_select table=planner
	@caption L&otilde;peb

	@property special_discount type=textbox size=5 field=meta method=serialize
	@caption Spetsiaal allahindlus

	property code type=hidden size=5 table=planner field=code
	caption Kood

	@property client_arrived type=chooser field=meta method=serialize
	@caption Klient saabus

	@property people type=select field=meta method=serialize
	@caption Org. esindajad

	@property products_text type=text submit=no
	@caption Toode

	@property sum type=text field=meta method=serialize
	@caption Summa

	@property modder type=text store=no no_caption=1
	
property summary type=textarea cols=80 rows=30 table=planner field=description no_caption=1
caption Kokkuvõte

@groupinfo reserved_resources caption="Ressursid"
@default group=reserved_resources
	
	@property resources_tbl type=table no_caption=1

@tableinfo planner index=id master_table=objects master_index=brother_of

@groupinfo products caption="Tooted"
@default group=products
	
	@property products_tbl type=table no_caption=1

@tableinfo planner index=id master_table=objects master_index=brother_of

#RELTYPES

@reltype CUSTOMER value=1 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype PROJECT value=2 clid=CL_PROJECT
@caption Projekt

@reltype RESOURCE value=3 clid=CL_ROOM
@caption Ressurss

*/

class reservation extends class_base
{
	function reservation()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/reservation",
			"clid" => CL_RESERVATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "bill_no":
				if(!is_oid($prop["value"]))
				{
					return PROP_IGNORE;
				}
				break;
			case "start1":
			case "end":
			case "resource":
				if($arr["new"] && $arr["request"][$prop["name"]])
				{
					$prop["value"] = $arr["request"][$prop["name"]];
				}
				if ($this->can("view", $prop["value"]))
				{
					$ri = get_instance(CL_ROOM);
					$sets = $ri->get_settings_for_room(obj($prop["value"]));
					if ($this->can("view", $sets->prop("related_room_folder")))
					{
						$rrs = new object_list(array(
							"class_id" => CL_ROOM,
							"parent" => $sets->prop("related_room_folder"),
							"lang_id" => array(),
							"site_id" => array()
						));
						$prop["options"] += $rrs->names();
					}
				}
				break;
			case "products_tbl":
				$this->get_products_tbl;
				break;

			case "verified":
				if ($prop["value"] == 1)
				{
					$prop["onclick"] = "document.changeform.reason.value=prompt(\"Sisestage t&uuml;histuse p&otilde;hjus\");if (document.changeform.reason.value == \"\") {document.changeform.verified.checked=true; } else {submit_changeform(\"unverify\");}";
				}
				break;
				
			case "sum":
				$room_instance = get_instance(CL_ROOM);
				$sum = $room_instance->cal_room_price(array(
					"room" => $arr["obj_inst"]->prop("resource"),
					"start" => $arr["obj_inst"]->prop("start1"),
					"end" => $arr["obj_inst"]->prop("end"),
					"people" => $arr["obj_inst"]->prop("people_count"),
					"products" => $arr["obj_inst"]->meta("amount"),
					"bron" => $arr["obj_inst"]
				));
				foreach($sum as $cur=>$price)
				{
					$cur = obj($cur);
					$prop["value"].= $price." ".$cur->name()."<br>";
				}
				break;
			case "deadline":
				if($arr["obj_inst"]->prop("verified"))
				{
					return PROP_IGNORE; 
				}
				if(!$prop["value"])
				{
					$prop["value"] = time() + 15*60;
				}
				break;
			case "client_arrived":
				$prop["options"] = array("Ei" , "Jah");
//				if(!$prop["value"])
//				{
//					$prop["value"] = 0;
//				}
				break;
				
			case "products_text":
				$prop["value"] = $this->get_products_text($arr["obj_inst"]);
				break;	

			case "people":
				if(is_oid($arr["obj_inst"]->meta("resource")))
				{
					$room = obj($arr["obj_inst"]->meta("resource"));
				}
				else
				{
					if(is_oid($arr["request"]["resource"]))
					{
						$room = obj($arr["request"]["resource"]);
					}
				}
				if(is_object($room))
				{
					$professions = $room->prop("professions");
					if(is_array($professions) && sizeof($professions))
					{
						$ol = new object_list(array(
							"class_id" => CL_CRM_PERSON,
							"lang_id" => array(),
							"CL_CRM_PERSON.RELTYPE_RANK" => $professions,
						));
						$prop["options"] = array("") + $ol->names();
					}
				}
				
//			case "sum":
//				break;

			case "name":
				if (!is_oid($arr["obj_inst"]->id()))
				{
					return PROP_IGNORE;
				}
				$prop["value"] = sprintf(t("%s: %s / %s-%s %s"), 
					$arr["obj_inst"]->prop("customer.name"),
					date("d.m.Y", $arr["obj_inst"]->prop("start1")),
					date("H:i", $arr["obj_inst"]->prop("start1")),
					date("H:i", $arr["obj_inst"]->prop("end")),
					$arr["obj_inst"]->prop("resource.name")
				);
				$prop["type"] = "text";
				break;
		};
		return $retval;
	}

	function callback_generate_scripts($arr)
	{
		return $arr["request"]["saved"] ? "if (window.opener) { window.opener.location.reload(); }" : "";
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		// get resource, then get settings from that and verify req fields
		if ($this->can("view", $arr["request"]["resource"]) && !$arr["request"]["time_closed"])
		{
			$reso = obj($arr["request"]["resource"]);
			$resi = $reso->instance();
			$sett = $resi->get_settings_for_room($reso);
			$reqf = $sett->meta("bron_req_fields");
			if (is_array($reqf) && count($reqf))
			{
				if ($reqf[$prop["name"]]["req"] == 1 && $prop["value"] == "")
				{
					$prop["error"] = sprintf(t("V&auml;li %s peab olema t&auml;idetud!"), $prop["caption"]);
					return PROP_FATAL_ERROR;
				}
			}
		}
	
		switch($prop["name"])
		{
			case "products_tbl":
				$arr["obj_inst"]->set_meta("amount", $arr["request"]["amount"]);
				$arr["obj_inst"]->set_meta("prod_discount", $arr["request"]["discount"]);
				break;

			case "time_closed":
				if ($prop["value"]  && $arr["request"]["closed_info"] == "")
				{
					$prop["error"] = t("Sulgemise p&otilde;hjus peab olema t&auml;idetud!");
					return PROP_FATAL_ERROR;
				}
				break;
		}
		return $retval;
	}	

	function callback_pre_save($arr)
	{
		$arr["obj_inst"]->set_name(sprintf(t("%s: %s / %s-%s %s"),
	                $arr["obj_inst"]->prop("customer.name"),
                        date("d.m.Y", $arr["obj_inst"]->prop("start1")),
                        date("H:i", $arr["obj_inst"]->prop("start1")),
                        date("H:i", $arr["obj_inst"]->prop("end")),
                        $arr["obj_inst"]->prop("resource.name")
		));
		if ($arr["request"]["length"] > 0)
		{
			$mul = 3600;
			if ($this->can("view", $arr["obj_inst"]->prop("resource")))
			{
				$room = obj($arr["obj_inst"]->prop("resource"));
				if ($room->prop("time_unit") == 1)
				{
					$mul = 60;
				}
			}

			$arr["obj_inst"]->set_prop("end", $arr["obj_inst"]->prop("start1")+$arr["request"]["length"]*$mul);
		}
	}
/*	function set_sum($arr)
	{
		extract($arr);		
		$this_obj = obj($id);
		if(!is_oid($resource))
		{
			return 0;
		}
		$room = obj($resource);

		$prices = $room->connections_from(array(
			"class_id" => CL_ROOM_PRICE,
			"type" => "RELTYPE_ROOM_PRICE",
		));
		foreach($prices as $conn)
		{
			$price = $conn->to();
			if(($price->prop("date_from") < $this_obj->prop("start1")) && $price->prop("date_to") > $this_obj->prop("end"))
			{
//				if()
//				{
					arr($price->prop("weekdays"));
//				}
			}
		
		}
			
//		if($people_count <= $room->prop("normal_capacity"))
//		{
//			$sum = $people_count * 
//		}
		$sum = 0;
		$this_obj->set_prop("sum" , $sum);
		$this_obj->save();
		return $sum;
	}*/

	function callback_mod_retval($arr)
	{
		$arr["args"]["saved"] = 1;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["reason"] = " ";
		if($_GET["calendar"]) 
		{
			$arr["calendar"] = $_GET["calendar"];
		}
		if(!$arr["id"])
		{
			$arr["resource"] = $_GET["resource"];
		}
	}

	function callback_post_save($arr)
	{
		if($arr["new"]==1 && is_oid($arr["request"]["calendar"]) && $this->can("view" , $arr["request"]["calendar"]))
		{
			$cal = obj($arr["request"]["calendar"]);
			$cal->connect(array(
				"to" => $arr["obj_inst"]->id(),
				"reltype" => "RELTYPE_EVENT"
			));
		}
		if($arr["new"] && is_oid($arr["request"]["resource"]) && $this->can("view" , $arr["request"]["resource"]))
		{
			$arr["obj_inst"]->set_prop("resource" ,$arr["request"]["resource"]);
			$arr["obj_inst"]->save();
		}

		if ($arr["request"]["sbt_close"] != "")
		{
			die("<script language='javascript'>if (window.opener) window.opener.refresh(); window.close();</script>");
		}
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
			"verified" => ($ob->prop("verified") ? t("Kinnitatud") : t("Kinnitamata")),
			"time_str" => $this->get_time_str(array(
				"start" => $ob->prop("start1"),
				"end" => $ob->prop("end"),
			)),
		));
		return $this->parse();
	}
	
	function get_time_str($arr)
	{
		$room_inst = get_instance(CL_ROOM);
		extract($arr);
		$res = "";
		$res.= $room_inst->weekdays[(int)date("w" , $arr["start"])];
		$res.= ", ";
		$res.= date("d.m.Y" , $arr["start"]);
		$res.= ", ";
		$res.= date("H:i" , $arr["start"]);
		$res.= " - ";
		$res.= date("H:i" , $arr["end"]);
		return $res;
	}
	
	function request_execute ($this_object)
	{
		return $this->show (array (
			"this" => $this_object,
		));
	}
	

//-- methods --//

	/**
		@param resource
		@param start
		@param end
		@comment
			basically what this does, is checks if this reservation can use given resource object in given time perion, and if can how many isntances of it
		@returns
			returns number instances that this resource can be used in this time period
	**/
	function resource_availability($arr)
	{
		$res = $arr["resource"];
		if(!is_oid($res))
		{
			arr("ehh");
			return 0;
		}
		$list = new object_list(array(
			"class_id" => CL_RESERVATION,
			"start1" => new obj_predicate_compare(OBJ_COMP_LESS, $arr["end"]),
			"end" => new obj_predicate_compare(OBJ_COMP_GREATER, $arr["start"]),
		));
		$total_usage = 0;
		foreach($list->arr() as $oid => $obj)
		{
			$inf = $this->resource_info($oid);
			foreach($inf as $resource => $count)
			{
				$total_usage = ($resource == $res)?($total_usage+$count):$total_usage;
			}
		}
		$res = obj($res);
		$total_count = count($res->prop("thread_data"));
		return ($total_count-$total_usage);
	}

	function resource_info($reservation)
	{
		if(!is_oid($reservation))
		{
			return false;
		}
		$reservation = obj($reservation);
		return $reservation->meta("resource_info");
	}

	/**
		@param reservation
			reservation object oid
		@param info
			array(
				resource object oid => number of resource instances used
			)
	**/
	function set_resource_info($reservation, $info)
	{
		if(!is_oid($reservation))
		{
			false;
		}
		$reservation = obj($reservation);
		$reservation->set_meta("resource_info", $info);
		$reservation->save();
		return true;
	}

	function _get_resources_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Kogus"),
		));
		$res = $this->resource_info($arr["obj_inst"]->id());
		foreach($res as $res => $count)
		{
			$o = obj($res);
			$t->define_data(array(
				"name" => $o->name(),
				"amount" => $count,
			));
		}
	}
	
	function get_room_products($room)
	{
		$ol = new object_list();
		if(is_oid($room))
		{
			$room = obj($room);
		}
		if(is_object($room))
		{
			$room_instance = get_instance(CL_ROOM);
			$ol = $room_instance->get_prod_list($room);
//			$prod_data = $room->meta("prod_data");
//			foreach($ol->arr() as $id => $o)
//			{
//				if(!$prod_data[$id]["active"])
//				{
//					$ol->remove($id);
//				}
//			}
		}
		return $ol;
	}
	
	function _get_products_order_view($arr)
	{
		extract($arr);
		$shop_order_center = get_instance(CL_SHOP_ORDER_CENTER);
		$wh = get_instance(CL_SHOP_WAREHOUSE);
		$room_instance = get_instance(CL_ROOM);
		if(is_oid($room) && $this->can("view" , $room))
		{
			$room_obj = obj($room);
			$warehouse = $room_obj->prop("warehouse");
			if(is_oid($warehouse) && $this->can("view" , $warehouse))
			{
				$w_obj = obj($warehouse);
				$w_cnf = obj($w_obj->prop("conf"));
				if(is_oid($w_obj->prop("order_center")) && $this->can("view" , $w_obj->prop("order_center")))
				{
					$soc = obj($w_obj->prop("order_center"));
					$pl_ol =  $room_instance->get_active_items($room);
					$pl = $pl_ol->arr();
//					$pl = $wh->get_packet_list(array(
//						"id" => $wh_id,
//						"parent" => $room_obj->prop("resources_fld"),
//						"only_active" => $soc->prop("only_active_items")
//					));
					
					//peksab need välja mis ruumi juures aktiivseks pole läinud
// 					$prod_data = $room_obj->meta("prod_data");
// 					foreach($pl as $key=> $val)
// 					{
// 						if(!$prod_data[$val->id()]["active"])
// 						{
// 							unset($pl[$key]);
// 						}
// 					}
// 					
					$shop_order_center->do_sort_packet_list($pl, $soc->meta("itemsorts"), $soc->prop("grouping"));
				
					// get the template for products for this folder
					$layout = $shop_order_center->get_prod_layout_for_folder($soc, $room_obj->prop("resources_fld"));
		
					// get the table layout for this folder
					$t_layout = $shop_order_center->get_prod_table_layout_for_folder($soc, $room_obj->prop("resources_fld"));
					$html .= $shop_order_center->do_draw_prods_with_layout(array(
						"t_layout" => $t_layout, 
						"layout" => $layout, 
						"pl" =>  $pl,
						"soc" => $soc,
					));
					return $html;	
				}
			}
		}
		$this->_get_products_tbl(array(
			"prop" => array("vcl_inst" => &$arr["prop"]["vcl_inst"]),
			"web" => $arr["web"],
			"room" => $arr["room"],
		));
		return 0;
	}
	
	function _get_products_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "picture",
			"caption" => t(""),
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind")
		));
		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Kogus"),
		));
		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa")
		));
		//kui veebipoolne
		if($arr["web"])
		{
			$prod_list = $this->get_room_products($arr["room"]);
			$amount = $arr["obj_inst"]->$_SESSION["room_reservation"]["products"];
		}
		else
		{
			$prod_list = $this->get_room_products($arr["obj_inst"]->prop("resource"));
			$amount = $arr["obj_inst"]->meta("amount");
		}
		$room = obj($arr["obj_inst"]->prop("resource"));
		
		$warehouse = obj($room->prop("warehouse"));
		if(is_oid($warehouse->prop("conf")))
		{
			$conf = obj($warehouse->prop("conf"));
			if($conf->prop("sell_prods"))
			{
				$sell_products = 1;
			}
		}
		
		if(is_object($room))
		{
			$prod_data = $room->meta("prod_data");
		}
		$image_inst = get_instance(CL_IMAGE);
		foreach($prod_list->arr() as $prod)
		{
			$image = "";
			if(is_object($prod->get_first_obj_by_reltype(array("type" => "RELTYPE_IMAGE"))))
			{
				$pic = $prod->get_first_obj_by_reltype(array("type" => "RELTYPE_IMAGE"));
				if(is_object($pic))
				{
					$image = $image_inst->make_img_tag_wl($pic->id());
				}
			}
			$po = obj($prod->parent());
			if($sell_products)
			{			
				$t->define_data(array(
					"picture" => $image,
					"name" => "<b>".$prod->name()."<b> <i>".$prod->comment()."</i>",
					"amount" =>  html::textbox(array(
						"name"=>'amount['.$prod->id().']',
						"value" => $amount[$prod->id()],
						"size" => 5,
						"onChange" => "el=document.getElementById('pr".$prod->id()."');el.innerHTML=this.value*".$prod->prop("price").";els=document.getElementsByTagName('span');tots = 0;for(i=0; i < els.length; i++) { el=els[i]; if (el.id.indexOf('pr') == 0) { tots += parseInt(el.innerHTML);}} te=document.getElementById('total');te.innerHTML=tots;disc=parseInt(document.changeform.discount.value);disc_el=document.getElementById('disc_val');if(disc>0){disc_el.innerHTML=(tots*(disc/100));} sum_val = document.getElementById('sum_val');if (disc > 0) {sum_val.innerHTML=(tots-(tots*(disc/100)));} else { sum_val.innerHTML=tots; } "
					)),
					"price" => number_format($prod->prop("price"), 2),
					"sum" => "<span id='pr".$prod->id()."'>".number_format($prod->prop("price") * $amount[$prod->id()], 2)."</span>",
					"parent" => $po->name()
				));
				$sum += $prod->prop("price") * $amount[$prod->id()];
			}
			else
			{
				$t->define_data(array(
					"picture" => $image,
					"name" => "<b>".$prod->name()."<b>",
	//				"amount" =>  html::textbox(array(
	//					"name"=>'amount['.$prod->id().']',
	//					"value" => $amount[$prod->id()],
	//				)),
					"parent" => $po->name()
				));
				$packages = $prod->connections_from(array(
					"type" => "RELTYPE_PACKAGING",
				));
				foreach($packages as $conn)
				{
					$package = $conn->to();
					if(!$prod_data[$package->id()]["active"])
					{
						continue;
					}
					$image = "";
					if(is_object($package->get_first_obj_by_reltype(array("type" => "RELTYPE_IMAGE"))))
					{
						$pic = $package->get_first_obj_by_reltype(array("type" => "RELTYPE_IMAGE"));
						if(is_object($pic))
						{
							$image = $image_inst->make_img_tag_wl($pic->id());
						}
					}
					$t->define_data(array(
						"picture" => $image,
						"name" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$package->name(),
						"amount" =>  html::textbox(array(
							"name"=>'amount['.$package->id().']',
							"value" => $amount[$package->id()],
						)),
					));
				}
			}
		}
		$t->set_default_sortby("name");
		$t->sort_by(array("rgroupby" => array("parent" => "parent")));
		$t->set_sortable(false);
		
		$t->define_data(array(
			"name" => t("Kogusumma"),
			"sum" => "<span id=total>".number_format($sum, 2)."</span>"
		));

		$disc = $sum * ($arr["obj_inst"]->meta("prod_discount") / 100.0);
		$t->define_data(array(
			"name" => t("Allahindlus (%)"),
			"amount" => html::textbox(array(
				"name" => "discount",
				"value" => $arr["obj_inst"]->meta("prod_discount"),
				"size" => 4,
				"onChange" => "els=document.getElementsByTagName('span');tots = 0;for(i=0; i < els.length; i++) { el=els[i]; if (el.id.indexOf('pr') == 0) { tots += parseInt(el.innerHTML);}} te=document.getElementById('total');te.innerHTML=tots;disc=parseInt(document.changeform.discount.value);disc_el=document.getElementById('disc_val');if(disc>0){disc_el.innerHTML=(tots*(disc/100));} sum_val = document.getElementById('sum_val');if (disc > 0) {sum_val.innerHTML=(tots-(tots*(disc/100)));} else { sum_val.innerHTML=tots; }"
			)),
			"sum" => "<span id='disc_val'>".number_format($disc, 2)."</span>"
		));

		$t->define_data(array(
			"name" => t("<b>Summa</b>"),
			"sum" => "<span id='sum_val'>".number_format($sum-$disc, 2)."</span>"
		));
		return $t;
	}

	function add_order($reservation, $order, $time = false)
	{
		if(!is_oid($reservation) || !is_oid($order))
		{
			return false;
		}
		$reservation = obj($reservation);

		$orders = $this->get_orders($reservation->id());
		if(!$time || ($time < $reservation->prop("start1") && $time > $reservation->prop("end")))
		{
			$time = $reservation->prop("start1");
		}
		$orders[$order] = $time;
		$reservation->set_meta("order_times", $orders);
		$reservation->save();
	}

	function get_orders($reservation)
	{
		if(!is_oid($reservation))
		{
			return false;
		}
		$reservation = obj($reservation);
		return $reservation->meta("order_times");
	}
	
	/**
		@attrib name=mark_arrived_popup params=name all_args=1
		@param bron required type=oid
			products and their amounts
	**/
	function mark_arrived_popup($arr)
	{
		extract($arr);
		if(is_oid($bron) && $this->can("view" , $bron))
		{
			$bron_obj = obj($bron);
			if(isset($_POST[$bron]))
			{
				$bron_obj->set_prop("client_arrived" , $_POST[$bron]);
				$bron_obj->save();
				die("<script type='text/javascript'>window.close();</script>");
			}
			$ret = "<form method=POST action=".get_ru().">";
			$ret.= t("Broneering : ");
			$ret.= date("G:i" , $bron_obj->prop("start1"));
			$ret.= "-";
			$ret.= date("G:i" , $bron_obj->prop("end"));
			
			if(is_oid($bron_obj->prop("customer")))
			{
				$customer = obj($bron_obj->prop("customer"));
				$ret.= "\n<br>".$customer->name();
			}
			$ret.= "\n<br>".html::radiobutton(array("name" => $bron , "value" => 0 , "caption" => t("Klient ei ilmunud kohale")));
			$ret.= "\n<br>".html::radiobutton(array("name" => $bron , "value" => 1 , "caption" => t("Klient ilmus kohale")));
			$ret.= "\n<br>".html::submit(array("name" => "submit", "value" => t("M&auml;rgi")));
			$ret.="</form>";
		}
		$ret.="<!-- $arr[bron] -->";
		die($ret);
	}

	function _get_b_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		if ($this->can("delete", $arr["obj_inst"]->id()))
		{
			$tb->add_button(array(
				"name" => "delete_bron",
				"tooltip" => t("Kustuta broneering"),
				"confirm" => t("Kas oled kindel et soovid broneeringut kustutada?"),
				"img" => "delete.gif",
				"action" => "del_bron"
			));
			$has = true;
		}
		if ($arr["obj_inst"]->prop("verified"))
		{
			$tb->add_button(array(
				"name" => "unverify",
				"tooltip" => t("T&uuml;hista kinnitus"),
				"onClick" => "document.changeform.reason.value=prompt('Sisestage t&uuml;histuse p&otilde;hjus');submit_changeform('unverify')",
				"action" => ""
			));
			$has = true;
		}

		$has = true;
		$tb->add_button(array(
			"name" => "rclose",
			"img" => "refresh.gif",
			"onClick" => "window.opener.refresh();window.close();"
		));
		if (!$has)
		{
			return PROP_IGNORE;
		}
	}

	/**
		@attrib name=unverify
	**/
	function unverify($arr)
	{
		$o = obj($arr["id"]);
		$o->set_prop("verified", 0);
		$o->set_meta("unverify_reason", $arr["reason"]);
		$o->save();
		return $arr["post_ru"];
	}

	/**
		@attrib name=del_bron
	**/
	function del_bron($arr)
	{
		$o = obj($arr["id"]);
		$room = obj($o->prop("resource"));
		$o->delete();
		$room_i = get_instance(CL_ROOM);
		$settings = $room_i->get_settings_for_room($room);
		if ($settings->prop("bron_no_popups"))
		{
			return $arr["return_url"];
		}
		// close if in popup
		die("<script language='javascript'>
			if (window.opener)
			{
				window.opener.refresh();
			}
			window.close();
		</script>");
	}

	function _get_length($arr)
	{
		if ($this->can("view", $arr["obj_inst"]->prop("resource")))
		{
			$room = obj($arr["obj_inst"]->prop("resource"));
			if ($room->prop("time_unit") == 1)
			{
				$arr["prop"]["options"] = $this->make_keys(range(0, 60));
				$arr["prop"]["post_append_text"] = t("Minutit");
				return;
			}
		}
		$arr["prop"]["options"] = $this->make_keys(range(0, 20));
		$arr["prop"]["post_append_text"] = t("Tundi");
	}

	function _get_cp_fn($arr)
	{
		if (!$this->can("view", $arr["obj_inst"]->prop("customer")))
		{
			return PROP_OK;
		}
		$cust = obj($arr["obj_inst"]->prop("customer"));
		if ($cust->class_id() == CL_CRM_PERSON)
		{
			$arr["prop"]["value"] = $cust->prop("firstname");
		}
		else
		{
			return PROP_IGNORE;
		}
	}

	function _get_cp_ln($arr)
        {
                if (!$this->can("view", $arr["obj_inst"]->prop("customer")))
                {
                        return PROP_OK;
                }

                $cust = obj($arr["obj_inst"]->prop("customer"));
                if ($cust->class_id() == CL_CRM_PERSON)
                {
                        $arr["prop"]["value"] = $arr["obj_inst"]->prop("customer.lastname");
                }
                else
                {
                        return PROP_IGNORE;
                }
        }
	
	function _get_cp_phone($arr)
        {
                if (!$this->can("view", $arr["obj_inst"]->prop("customer")))
                {
                        return PROP_OK;
                }

                $cust = obj($arr["obj_inst"]->prop("customer"));
                if ($cust->class_id() == CL_CRM_PERSON)
                {
                        $arr["prop"]["value"] = $arr["obj_inst"]->prop("customer.phone.name");
                }
                else
                {
                        return PROP_IGNORE;
                }
        }

        function _get_cp_email($arr)
        {
                if (!$this->can("view", $arr["obj_inst"]->prop("customer")))
                {
                        return PROP_OK;
                }

                $cust = obj($arr["obj_inst"]->prop("customer"));
                if ($cust->class_id() == CL_CRM_PERSON)
                {
                        $arr["prop"]["value"] = $arr["obj_inst"]->prop("customer.email.mail");
                }
                else
                {
                        return PROP_IGNORE;
                }
        }
	
	function _set_cp_fn($arr)
	{
                if (!$this->can("view", $arr["obj_inst"]->prop("customer")) && $arr["prop"]["value"] != "")
                {
			$cust = obj();
			$cust->set_parent($arr["obj_inst"]->id() ? $arr["obj_inst"]->id() : $_POST["parent"]);
			$cust->set_class_id(CL_CRM_PERSON);
			$cust->save();
			$arr["obj_inst"]->set_prop("customer", $cust->id());
                }

		if (!$this->can("view", $arr["obj_inst"]->prop("customer")) || $arr["prop"]["value"] == "")
		{
			return PROP_IGNORE;
		}
                $cust = obj($arr["obj_inst"]->prop("customer"));
                if ($cust->class_id() == CL_CRM_PERSON)
                {
			$cust->set_prop("firstname", $arr["prop"]["value"]);
			$cust->set_name($cust->prop("firstname")." ".$cust->prop("lastname"));
			$cust->save();
		}	
		return PROP_IGNORE;
	}

        function _set_cp_ln($arr)
        {
                if (!$this->can("view", $arr["obj_inst"]->prop("customer")) || $arr["prop"]["value"] == "")
                {
                        return PROP_IGNORE;
                }

                $cust = obj($arr["obj_inst"]->prop("customer"));
                if ($cust->class_id() == CL_CRM_PERSON)
                {
                        $cust->set_prop("lastname", $arr["prop"]["value"]);
                        $cust->set_name($cust->prop("firstname")." ".$cust->prop("lastname"));
                        $cust->save();
                }
                return PROP_IGNORE;
        }

        function _set_cp_phone($arr)
        {
                if (!$this->can("view", $arr["obj_inst"]->prop("customer")) || $arr["prop"]["value"] == "")
                {
                        return PROP_IGNORE;
                }

                $cust = obj($arr["obj_inst"]->prop("customer"));
                if ($cust->class_id() == CL_CRM_PERSON)
                {
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
			$ph->set_name($arr["prop"]["value"]);
			$ph->save();
			if (!$this->can("view", $cust->prop("phone")))
			{
				$cust->connect(array(
					"to" => $ph->id(),
					"type" => "RELTYPE_PHONE"
				));
				$cust->set_prop("phone", $ph->id());
				$cust->save();
			}
                }
                return PROP_IGNORE;
        }

        function _set_cp_email($arr)
        {
                if (!$this->can("view", $arr["obj_inst"]->prop("customer")) || $arr["prop"]["value"] == "")
                {
                        return PROP_IGNORE;
                }

                $cust = obj($arr["obj_inst"]->prop("customer"));
                if ($cust->class_id() == CL_CRM_PERSON)
                {
                        if ($this->can("view", $cust->prop("email")))
                        {
                                $ph = obj($cust->prop("email"));
                        }
                        else
                        {
                                $ph = obj();
                                $ph->set_parent($cust->id());
                                $ph->set_class_id(CL_ML_MEMBER);
                        }
                        $ph->set_name($arr["prop"]["value"]);
			$ph->set_prop("mail", $arr["prop"]["value"]);
                        $ph->save();
                        if (!$this->can("view", $cust->prop("email")))
                        {
                                $cust->connect(array(
                                        "to" => $ph->id(),
                                        "type" => "RELTYPE_EMAIL"
                                ));
                                $cust->set_prop("email", $ph->id());
                                $cust->save();
                        }
                }
                return PROP_IGNORE;
        }

	function _get_modder($arr)
	{
		$u = get_instance(CL_USER);
		$p = $u->get_person_for_uid($arr["obj_inst"]->createdby());
		$mp = $u->get_person_for_uid($arr["obj_inst"]->modifiedby());
		$arr["prop"]["value"] = sprintf(
			t("Loomine: %s / %s.<br>Muutmine: %s / %s"),
			html::obj_change_url($p),
			date("d.m.Y H:i", $arr["obj_inst"]->created()),
			html::obj_change_url($mp),
			date("d.m.Y H:i", $arr["obj_inst"]->modified())
		);
	}

	function _get_unverify_reason($arr)
	{
		if ($arr["obj_inst"]->meta("unverify_reason") == "")
		{
			return PROP_IGNORE;
		}
		$arr["prop"]["value"] = sprintf(t("Kinnituse eemaldamise p&otilde;hjus: %s"), $arr["obj_inst"]->meta("unverify_reason"));
	}

	function on_delete_reservation($arr)
	{
		$o = obj($arr["oid"]);
		if ($this->can("view", $o->prop("resource")))
		{
			$res = obj($o->prop("resource"));
			$sets = $res->prop("settings");
			if (is_array($sets))
			{
				$sets = reset($sets);
			}
			if ($this->can("view", $sets))
			{
				$set = obj($sets);
				if ($set->prop("send_del_mail"))
				{
					$this->do_send_on_delete_mail($o, $set);
				}
			}
		}
	}

	function do_send_on_delete_mail($bron, $settings)
	{
		send_mail(
			$settings->prop("del_mail_to"),
			$settings->prop("del_mail_subj"),
			str_replace("#ord#", $this->_get_mail_ord_ct($bron), $settings->prop("del_mail_ct")),
			"From: ".$this->_get_del_mail_from($settings)
		);
	}

	function _get_mail_ord_ct($bron)
	{
		$res = "";
		//kliendi nimi, kontaktid, aeg, broneeritud ruum, toidud
		$res .= sprintf(t("Klient: %s / %s / %s\n"), 
			$bron->prop("customer.name"),
			$bron->prop("customer.email.mail"),
			$bron->prop("customer.phone.name")
		);
		$res .= sprintf(t("Aeg: %s: %s-%s\n"), 
			date("d.m.Y", $bron->prop("start1")),
			date("H:i", $bron->prop("start1")),
			date("H:i", $bron->prop("end"))
		);
		$res .= sprintf(t("Koht: %s\n"), $bron->prop("resource.name"));

                $amount = $bron->meta("amount");
                $val = array();
                foreach($amount as $product => $amt)
 	        {
        	        if($amt && $this->can("view", $product))
                	{
		                $prod=obj($product);
                		$val[] = $prod->name();
	                }
                }
                $res .= sprintf(t("Toidud: %s\n"), join($val , ","));

		return $res;
	}

	function _get_del_mail_from($s)
	{
		if ($s->prop("del_mail_from_name") != "")
		{
			return $s->prop("del_mail_from_name")." <".$s->prop("del_mail_from").">";
		}
		return $s->prop("del_mail_from");
	}

	function get_products_text($o, $sep = "<BR>")
	{
		$amount = $o->meta("amount");
		$val = array();
		foreach($amount as $product => $amt)
		{
			if($amt && $this->can("view", $product))
			{
				$prod=obj($product);
				$val[] = sprintf(t("%s: %s tk / %s"),
					$prod->name(),
					$amt,
					number_format($prod->prop("price")*$amt,2)
				);
			}
		}
		return join($val , $sep);
	}
}
?>
