<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_offer_row.aw,v 1.3 2006/10/04 15:02:18 markop Exp $
// procurement_offer_row.aw - Pakkumise rida 
/*

@classinfo syslog_type=ST_PROCUREMENT_OFFER_ROW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta

	@property product type=textbox size=10 
	@caption Toode

	@property amount type=textbox 
	@caption Kogus

	@property b_amount type=textbox 
	@caption Ostetav kogus
	
	@property b_price type=textbox 
	@caption Ostu hind

	@property unit type=select
	@caption &Uuml;hik

	@property price type=textbox 
	@caption Hind

	@property currency type=select
	@caption Valuuta

	@property shipment type=textbox size=10 
	@caption Tarneaeg

	@property accept type=checkbox ch_value=1
	@caption Aktsepteeritud


@reltype OFFER value=1 clid=CL_PROCUREMENT_OFFER
@caption Pakkumine

*/

class procurement_offer_row extends class_base
{
	function procurement_offer_row()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_offer_row",
			"clid" => CL_PROCUREMENT_OFFER_ROW
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "unit":
				$unit_list = new object_list(array(
					"class_id" => CL_UNIT
				));
				foreach($unit_list->arr() as $unit)
				{
					$prop["options"][$unit->id()] = $unit->prop("unit_code");
				}
				break;
			
			case "currency":
			
				$unit_opts = array();
				$curr_list = new object_list(array(
					"class_id" => CL_CURRENCY
				));
				$prop["options"] = $curr_list->names();
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
}
?>
