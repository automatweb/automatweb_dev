<?php
// $Header: /home/cvs/automatweb_dev/classes/formgen/currency.aw,v 1.6 2006/07/25 11:33:34 markop Exp $
// currency.aw - Currency management

/*

@classinfo syslog_type=ST_CURRENCY no_status=1 

@default group=general

@property ord type=textbox table=objects field=jrk size=5
@caption J&auml;rjekord

@property comment type=textbox table=objects field=comment 
@caption Kurss euro suhtes

@property unit_name type=textbox table=objects field=meta method=serialize
@caption Raha&uuml;hiku nimetus

@property small_unit_name type=textbox table=objects field=meta method=serialize
@caption Peenraha&uuml;hiku nimetus

@groupinfo rates caption=Kursid
@default group=rates

@property rates type=callback callback=callback_get_rates
@caption Kaustad kust otsida

*/

define("RET_NAME",1);
define("RET_ARR",2);

class currency extends class_base
{
	function currency()
	{
		$this->init(array(
			"tpldir" => "currency",
			"clid" => CL_CURRENCY
		));
		$this->sub_merge = 1;
		$this->lc_load("currency","lc_currency");	
		lc_load("definition");
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
			case "rates":
				$this->submit_meta($arr);
				break;
		}
		return $retval;
	}

	function submit_meta($arr = array())
	{
		$arr["obj_inst"]->set_meta("rates", $arr["request"]["rates"]);
 	}

	function callback_get_rates($arr)
	{
		$rates = $arr["obj_inst"]->meta("rates");
		$count = sizeof($rates);
		if($count > 0 && !$rates[$count-1]["rate"])$count--;
		if($count > 0 && !$rates[$count-1]["rate"])$count--;
		
		$curr_object_list = new object_list(array(
			"class_id" => CL_CURRENCY,
		));
		
		$curr_opt = array();
		foreach($curr_object_list->arr() as $curr)
		{
			if($arr["obj_inst"]->id() != $curr->id()) $curr_opt[$curr->id()] = $curr->name();
		}
		
		load_vcl("table");
		$t = new aw_table(array(
			"layout" => "generic"
		));
		$t->define_field(array(
			"name" => "start_date",
			"caption" => t("Alguskuupäev"),
		));
		$t->define_field(array(
			"name" => "end_date",
			"caption" => t("Lõppkuupäev"),
		));
		$t->define_field(array(
			"name" => "currency",
			"caption" => t("Valuuta"),
		));
		$t->define_field(array(
			"name" => "rate",
			"caption" => t("Kurss"),
		));
		$t->define_field(array(
			"name" => "buy_rate",
			"caption" => t("Ostukurss"),
		));
		$t->define_field(array(
			"name" => "sell_rate",
			"caption" => t("Müügikurss"),
		));
		for($i = 0; $i < $count+1; $i++)
		{
			$t->define_data(array(
				"start_date" => html::date_select(array(
					"name" => "rates[".$i."][start_date]",
					"value" => $rates[$i]["start_date"])),
				"end_date" => html::date_select(array(
					"name" => "rates[".$i."][end_date]",
					"value" => $rates[$i]["end_date"])),
				"currency" => html::select(array(
					"name" => "rates[".$i."][currency]",
					"options" => $curr_opt,
					"value" => $rates[$i]["currency"])),
				"rate"	=> html::textbox(array(
					"name" => "rates[".$i."][rate]",
					"value" => $rates[$i]["rate"],
					"size" => 5,
				)),
				"buy_rate" => html::textbox(array(
					"name" => "rates[".$i."][buy_rate]",
					"value" => $rates[$i]["buy_rate"],
					"size" => 5,
				)),
				"sell_rate" =>html::textbox(array(
					"name" => "rates[".$i."][sell_rate]",
					"value" => $rates[$i]["sell_rate"],
					"size" => 5,
				)),
			));
		}
		$ret["rates"] = array(
			"name" => "rates",
			"caption" => t("Kursid"),
			"type" => "text",
			"value" => $t->draw(),
		);
		return $ret;
	}

	function get_list($type = RET_NAME)
	{
		$ret = array();
		$ol = new object_list(array(
			"class_id" => CL_CURRENCY,
			"lang_id" => array()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($type == RET_NAME)
			{
				$ret[$o->id()] = $o->name();
			}
			else
			if ($type == RET_ARR)
			{
				$ret[$o->id()] = array(
					"oid" => $o->id(),
					"name" => $o->name(),
					"rate" => $o->comment()
				);
			}
		}
		return $ret;
	}

	function get($id)
	{
		if (!is_array(aw_global_get("currency_cache")))
		{
			aw_global_set("currency_cache",$this->get_list(RET_ARR));
		}

		$_t = aw_global_get("currency_cache");
		return $_t[$id];
	}
}
?>
