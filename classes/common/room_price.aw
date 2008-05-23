<?php
/*
@classinfo syslog_type=ST_ROOM_PRICE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop

@default table=objects
@default group=general
@default field=meta
@default method=serialize

	@property type type=chooser default=1
	@caption Hinna t&uuml;&uuml;p

	@property recur type=checkbox ch_value=1
	@caption Kordub

	@property active type=checkbox ch_value=1
	@caption Kehtib

	@property date_from type=date_select
	@caption Alates

	@property date_to type=date_select
	@caption Kuni

	@property weekdays type=chooser multiple=1 captionside=top
	@caption N&auml;dalap&auml;evad

	@property apply_groups type=relpicker reltype=RELTYPE_GROUP multiple=1
	@caption Kehtib gruppidele

	@property nr type=select
	@caption Mitmes

	@property time_from type=time_select
	@caption Alates

	@property time_to type=time_select
	@caption Kuni

	@property time type=select editonly=1
	@caption Aeg

	@property bron_made_from type=datetime_select default=-1
	@caption Broneering tehtud alates

	@property bron_made_to type=datetime_select default=-1
	@caption Broneering tehtud kuni

	@property bargain_percent type=textbox
	@caption Soodustuse protsent

	@property prices_props type=callback callback=gen_prices_props
	@caption Hinnad
	
	@property priority type=select
	@caption Prioriteet

@reltype GROUP value=1 clid=CL_GROUP
@caption Kehtib grupile
*/

class room_price extends class_base
{
	function room_price()
	{
		$this->init(array(
			"tpldir" => "common/room_price",
			"clid" => CL_ROOM_PRICE
		));
		
		$this->weekdays = array(
			1 => t("E"),
			2 => t("T"),
			3 => t("K"),
			4 => t("N"),
			5 => t("R"),
			6 => t("L"),
			7 => t("P"),
		); 
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "weekdays":
				$prop["options"] = $this->weekdays;
				if(!$prop["value"])
				{
				    $prop["value"] = Array(1,1,1,1,1,1,1,1);
				}
				break;

			case "nr":
				if($arr["obj_inst"]->prop("type") == 2)
				{
					return PROP_IGNORE;
				}
				for($i=1;$i<11;$i++)
				{
					$opts[$i] = $i;
				}
				$prop["options"] = $opts;
				break;

			case "time":
				if($arr["obj_inst"]->prop("type") == 2)
				{
					return PROP_IGNORE;
				}
				$prop["options"] = $this->get_time_selections($arr["obj_inst"]->id());
				break;

			case "type":
				$prop["options"] = array(
					1 => t("Hind"),
					2 => t("Soodushind"),
				);
				if(!$arr["obj_inst"]->prop("type"))
				{
					$prop["value"] = ($arr["request"]["ba"]==1)?2:1;
				}
				break;

			// ignore's for normal price
			case "priority":
				$prop["options"] = array("1" , "2" , "3");
			case "recur":
			case "active":
			case "bargain_percent":
				if($arr["obj_inst"]->prop("type") == 1)
				{
					return PROP_IGNORE;
				}
				break;

			// ignore's for bargain price
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "type":
				$prop["value"] = $prop["value"]?$prop["value"]:$prop["default"];
				break;
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

	private function get_room($oid)
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
		return $c->from();
	}

	private function get_currencys($oid)
	{
		return ($room = $this->get_room($oid))?$room->prop("currency"):$room;
	}

	function gen_prices_props($arr)
	{
		if($arr["obj_inst"]->prop("type") == 2)
		{
			return PROP_IGNORE;
		}

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
				"editonly" => 1,
			);
		}
		if(!count($retval))
		{
			$retval["currencys"] = array(
				"name" => "currencys",
				"type" => "text",
				"caption" => t("Valuutad"),
				"value" => t("Hind ei ole seotud &uuml;hegi ruumiga v&otilde;i on valuutad m&auml;&auml;ramata"),
				"editonly" => 1,
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
	private function save_prices($oid, $prices)
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

	/**
		@comment
			Gets time caption from room object, if room is connected
	**/
	private function get_time_caption($oid)
	{
		$room = $this->get_room($oid);
		if(!$room)
		{
			return $room;
		}
		$room_inst = get_instance(CL_ROOM);
		return $room_inst->unit_step[$room->prop("time_unit")];
	}
	
	/**
		@comment
			Generates array of available options for booking the room
			array(
				nr_of_units => nr_of_units unit_caption,
			)
	**/
	private function get_time_selections($oid)
	{
		$data = $this->get_time_step($oid);
		if(!$data)
		{
			return false;
		}
		$caption = $this->get_time_caption($oid);
		for($i = $data["from"]; $i <= $data["to"]; $i += $data["step"])
		{
			$ret["$i"] = $i." ".$caption;
		}
		return $ret;
	}

	/**
		@comment
			gets time_step information from the room that price is connected to

		@returns
			array(
				from => ,
				to => ,
				step => ,
			)
			.. or false if this price isn't connected to any room
	**/
	private function get_time_step($oid)
	{
		if(!($room = $this->get_room($oid)))
		{
			return $room;
		}
		$ret["from"] = $room->prop("time_from");
		$ret["to"] = $room->prop("time_to");
		if($room->prop("selectbox_time_step") > 0)
		{
			$ret["step"] = $room->prop("selectbox_time_step");
		}
		else
		{
			$ret["step"] = $room->prop("time_step");
		}
		return $ret;
	}

	private function get_prices($oid)
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
