<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room_price.aw,v 1.1 2006/10/13 13:02:52 tarvo Exp $
// room_price.aw - Ruumi hind 
/*

@classinfo syslog_type=ST_ROOM_PRICE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property date_from type=date_select
	@caption Alates

	@property date_to type=date_select
	@caption Kuni

	@property weekdays type=chooser multiple=1
	@caption N&auml;dalap&auml;evad

	@property nr type=select
	@caption Mitmes

	@property prices type=callback callback=gen_prices_props

*/

class room_price extends class_base
{
	function room_price()
	{
		$this->init(array(
			"tpldir" => "common/room_price",
			"clid" => CL_ROOM_PRICE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "weekdays":
				$prop["options"] = array(
					1 => "E",
					2 => "T",
					3 => "K",
				);
				break;
			case "nr":
				$prop["options"] = array(
					1 => 1,
					2 => 2,
					3 => 3,
				);
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

	function callback_mod_retval($arr)
	{
		if(count($arr["request"]["currency"]))
		{
			
			$this->save_prices($arr["request"]["id"], $arr["request"]["currency"]);
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

	function get_currencys($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		$cs = $o->connections_to(array(
			"class_id" => CL_ROOM,
			"reltype" => "RELTYPE_ROOM_PRICE",
		));
		$c = reset($cs);
		$room = $c->from();
		return $room->prop("currency");
	}

	function gen_prices_props($arr)
	{
		$curs = $this->get_currencys($arr["obj_inst"]->id());
		$prices = $this->get_prices($arr["obj_inst"]->id());
		$retval = array();
		foreach($curs as $cur)
		{
			if(!is_oid($cur))
			{
				continue;
			}
			$c = obj($cur);
			$retval["currency[".$cur."]"] = array(
				"name" => "currency[".$cur."]",
				"type" => "textbox",
				"caption" => $c->prop("unit_name"),
				"value" => $prices[$cur],
			);
		}
		if(!count($retval))
		{
			$retval["currencys"] = array(
				"name" => "currencys",
				"type" => "text",
				"caption" => t("Valuutad"),
				"value" => t("Hind ei ole seotud &uuml;hegi ruumiga v&otilde;i on valuutad m&auml;&auml;ramata"),
			);
		}
		return $retval;
	}

	/**
		@param oid type=oid
			room_price objects oid
		@param prices type=array
			array of prices:
			array(
				CL_CURRENCY object oid => price
			)
	**/
	function save_prices($oid, $prices)
	{
		if(!is_oid($oid) || !is_array($prices))
		{
			return false;
		}
		$o = obj($oid);
		$o->set_meta("prices", $prices);
		$o->save();
		return true;
	}

	function get_prices($oid)
	{
		if(!is_oid($oid))
		{
			return false;
		}
		$o = obj($oid);
		return $o->meta("prices");
	}
}
?>
