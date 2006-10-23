<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/reservation.aw,v 1.8 2006/10/23 13:04:10 tarvo Exp $
// reservation.aw - Broneering 
/*

@tableinfo planner index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_RESERVATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
#GENERAL
	
@property start1 type=datetime_select field=start table=planner
@caption Algus

@property end type=datetime_select table=planner
@caption L&otilde;peb

@property deadline type=datetime_select table=planner field=deadline
@caption T&auml;htaeg
		
@property resource type=relpicker reltype=RELTYPE_RESOURCE field=meta method=serialize
@caption Ressurss

@property customer type=relpicker table=planner field=customer reltype=RELTYPE_CUSTOMER
@caption Klient
			
@property project type=relpicker table=planner field=project reltype=RELTYPE_PROJECT
@caption Projekt

@property send_bill type=checkbox ch_value=1 table=planner field=send_bill 
@caption Saata arve

@property bill_no type=hidden table=planner 
@caption Arve number
	
@property content type=textarea no_caption=1 cols=50 rows=5 field=description table=planner
@caption Sisu

@property code type=hidden size=5 table=planner field=code
@caption Kood

property summary type=textarea cols=80 rows=30 table=planner field=description no_caption=1
caption Kokkuvõte

@groupinfo reserved_resources caption="Ressursid"
@default group=reserved_resources
	
	@property resources_tbl type=table no_caption=1

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
		if($_GET["calendar"]) 
		{
			$arr["calendar"] = $_GET["calendar"];
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
