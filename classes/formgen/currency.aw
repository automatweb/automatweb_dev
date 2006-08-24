<?php
// $Header: /home/cvs/automatweb_dev/classes/formgen/currency.aw,v 1.7 2006/08/24 13:08:36 markop Exp $
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

@groupinfo translate caption=T&otilde;lge
@default group=translate

@property translate type=table no_caption=1 


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

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "translate":
				$this->do_table($arr);
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
			case "rates":
				$this->submit_meta($arr);
				break;
			case "translate":
				$this->submit_trans($arr);
				break;
		}
		return $retval;
	}

	function submit_trans($arr = array())
	{
		$arr["obj_inst"]->set_meta("unit", $arr["request"]["unit"]);
		$arr["obj_inst"]->set_meta("small_unit", $arr["request"]["small_unit"]);
	}

	function do_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "lang",
			"caption" => t("Keel"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "unit",
			"caption" => t("&Uuml;hik"),
		));
		$t->define_field(array(
			"name" => "small_unit",
			"caption" => t("Peenraha"),
		));
		
		$langdata = array();
		aw_global_set("output_charset", "utf-8");
		$lg = get_instance("languages");
		$langdata = $lg->get_list(array("all_data" => 1,));

		$unit_meta = $arr["obj_inst"]->meta("unit");
		$small_unit_meta = $arr["obj_inst"]->meta("small_unit");
		
		foreach($langdata as $id => $lang)
		{
			if($arr["obj_inst"]->lang_id() != $id)
			{
				$t->define_data(array(
					"unit" => html::textbox(array(
							"name" => "unit[".$lang["acceptlang"]."]",
							"value" => $unit_meta[$lang["acceptlang"]],
							"size" => 10,
					)),
					"lang" => $lang["name"],
					"small_unit" =>html::textbox(array(
							"name" => "small_unit[".$lang["acceptlang"]."]",
							"value" => $small_unit_meta[$lang["acceptlang"]],
							"size" => 10,
					)),
				));
			}
		}

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
