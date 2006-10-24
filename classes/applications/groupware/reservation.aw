<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/reservation.aw,v 1.10 2006/10/24 14:18:19 markop Exp $
// reservation.aw - Broneering 
/*

@tableinfo planner index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_RESERVATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
#TAB GENERAL

@layout general_split type=hbox

@layout general_up type=vbox closeable=1 area_caption=&Uuml;ldinfo parent=general_split
@default parent=general_up

	@property name type=textbox field=name method=none size=20
	@caption Nimi
	
	property deadline type=datetime_select table=planner field=deadline
	caption T&auml;htaeg
			
	@property resource type=relpicker reltype=RELTYPE_RESOURCE field=meta method=serialize
	@caption Ressurss
	
	@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER
	@caption Klient
				
	@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT
	@caption Projekt
	
	@property send_bill type=checkbox ch_value=1 table=planner field=send_bill no_caption=1
	@caption Saata arve
	
	@property bill_no type=hidden table=planner 
	@caption Arve number
	
	@property content type=textarea cols=40 rows=5 field=description table=planner
	@caption Sisu	

@layout general_down type=vbox closeable=1 area_caption=Aeg&#44;&nbsp;ja&nbsp;hind parent=general_split
@default parent=general_down
	
	@property people_count type=textbox size=3 field=meta method=serialize
	@caption Inimesi
		
	@property start1 type=datetime_select field=start table=planner
	@caption Algus

	@property end type=datetime_select table=planner
	@caption L&otilde;peb
	
	property code type=hidden size=5 table=planner field=code
	caption Kood

	@property sum type=text field=meta method=serialize
	@caption Summa

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
				break;
			case "products_tbl":
				$this->get_products_tbl;
				break;
			case "sum":
				$room_instance = get_instance(CL_ROOM);
				$sum = $room_instance->cal_room_price(array(
					"room" => $arr["obj_inst"]->prop("resource"),
					"start" => $arr["obj_inst"]->prop("start1"),
					"end" => $arr["obj_inst"]->prop("end"),
					"people" => $arr["obj_inst"]->prop("people_count"),
					"products" => $arr["obj_inst"]->meta("amount"),
				));
				foreach($sum as $cur=>$price)
				{
					$cur = obj($cur);
					$prop["value"].= $price." ".$cur->name()."<br>";
				}
				break;

//			case "sum":
//				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "products_tbl":
				$arr["obj_inst"]->set_meta("amount", $arr["request"]["amount"]);
				break;
			//-- set_property --//
		}
		return $retval;
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
			$prod_data = $room->meta("prod_data");
			foreach($ol->arr() as $id => $o)
			{
				if(!$prod_data[$id]["active"])
				{
					$ol->remove($id);
				}
			}
		}
		return $ol;
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
			"name" => "amount",
			"caption" => t("Kogus"),
		));
		$prod_list = $this->get_room_products($arr["obj_inst"]->prop("resource"));
		$amount = $arr["obj_inst"]->meta("amount");
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
			$t->define_data(array(
				"picture" => $image,
				"name" => "<b>".$prod->name()."<b>",
//				"amount" =>  html::textbox(array(
//					"name"=>'amount['.$prod->id().']',
//					"value" => $amount[$prod->id()],
//				)),
			));
			$packages = $prod->connections_from(array(
				"type" => "RELTYPE_PACKAGING",
			));
			foreach($packages as $conn)
			{
				$package = $conn->to();
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
		$t->set_sortable(false);
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
}
?>
