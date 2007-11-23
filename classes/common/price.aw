<?php
// $Header: /home/cvs/automatweb_dev/classes/common/price.aw,v 1.4 2007/11/23 11:11:59 markop Exp $
// price.aw - Hind 
/*

@tableinfo aw_prices index=aw_oid master_table=objects master_index=brother_of

@classinfo syslog_type=ST_PRICE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop

@default table=objects
@default group=general

	@property type type=select default=1 table=aw_prices field=aw_type
	@caption Hinna t&uuml;&uuml;p

	@property sum type=textbox default=0 table=aw_prices field=aw_sum
	@caption Summa

	@property currency type=select table=aw_prices field=aw_currency
	@caption Valuuta

	@property date_from type=date_select table=aw_prices field=aw_date_from
	@caption Alates

	@property date_to type=date_select table=aw_prices field=aw_date_to
	@caption Kuni

//idee oleks selles, et kui uuele klassile tahaks hinda külge panna, siis siia lisada lihtsalt klassi id
@reltype OBJECT value=1 clid=CL_TRANSPORT_TYPE
@caption Objekt millele hind m&otilde;jub

*/

class price extends class_base
{
	function price()
	{
		$this->init(array(
			"tpldir" => "common/price",
			"clid" => CL_PRICE
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

	function do_db_upgrade($t, $f)
	{
		if ($f == "" && $t == "aw_prices")
		{
			$this->db_query("CREATE TABLE aw_prices(aw_oid int primary key,
				aw_verified int,
				aw_type int,
				aw_sum double,
				aw_currency int,
				aw_date_from int,
				aw_date_to int
			)");
			return true;
		}
		return false;
	}


//siia tuleks api funktsioonid
	/**
		@attrib name=get_price_objects api=1 all_args=1
	@param object required type=oid
		object
	@returns object list

	@comment
		Returns main price objects connected to object
	**/
	function get_price_objects($arr)
	{
		extract($arr);
		$filter = array(
			"class_id" => array(CL_PRICE),
			"site_id" => array(),
			"lang_id" => array(),
			"CL_PRICE.RELTYPE_OBJECT.id" => $object,
//			"parent.class_id" => new obj_predicate_not(CL_PRICE),
		);
		$ol = new object_list($filter);
		foreach($ol->arr() as $o)
		{
			$parent = obj($o->parent());
			if($parent->class_id() == CL_PRICE)
			{
				$ol->remove($o->id());
			}
		}
		return $ol;
	}
	/**
		@attrib name=get_prices api=1 all_args=1
	@param o required type=object
		main price object
	@returns array("currency" => "price")

	@comment
		returns the price in different curr
		uses child objects
	**/
	function get_prices($o)
	{
		$ret = array();
		$ret[$o->prop("currency")] = $o->prop("sum");
		$filter = array(
			"class_id" => array(CL_PRICE),
			"site_id" => array(),
			"lang_id" => array(),
			"parent" => $o->id(),
		);
		$ol = new object_list($filter);

		foreach($ol->arr() as $obj)
		{
			$ret[$obj->prop("currency")] = $obj->prop("sum");
		}
		return $ret;
	}

	//object
	//name , parent
	/**
		@attrib name=add api=1 all_args=1
	@param object required type=oid
		object to connect to
	@param name optional type=String
		price object name
	@param parent optional type=oid default=object
		price object parent
	@param currency optional type=oid
		Price object currency oid
	@param sum optional type=double
	@returns oid - Price object oid.

	@comment
		adds new price object.
	**/
	function add($arr)
	{
		$o = obj($arr["object"]);
		$price = new object();
		$price->set_class_id(CL_PRICE);
		$price->set_name($arr["name"] ? $arr["name"] : $o->name()." ".t("hind"));
		$price->set_parent($arr["parent"] ? $arr["parent"] : $arr["object"]);
		$price->set_prop("type" , $o->class_id());
		if($arr["currency"])
		{
			$price->set_prop("currency" , $arr["currency"]);
		}
		if($arr["sum"])
		{
			$price->set_prop("sum" , $arr["sum"]);
		}
		$price->save();
		$price->connect(array(
			"to" => $arr["object"],
			"type" => RELTYPE_OBJECT,
		));
		
		return $price->id();
	}

	//object
	//name , parent
	function add_other($o,$curr,$sum)
	{
		$objs = $o->connections_from(array(
			"type" => "RELTYPE_OBJECT",
		));
		foreach($objs as $obj)
		{
			$object = $obj->prop("to");
		}

		$co = $this->add(array(
			"name" => $o->name(),
			"parent" => $o->id(),
			"class_id" => $o->prop("type"),
			"date_from" => $o->prop("date_from"),
			"date_to" => $o->prop("date_to"),
			"object" => $object,
			"currency" => $curr,
			"sum" => $sum,
		));

//		$coobj = obj($co);
//		$coobj->connect(array(
//			"to" => $o->id(),
//			"type" => "RELTYPE_GET_PROPERTIES_FROM",
//		));
//		return $coobj->id();
		return $co;
	}

	/**
		@attrib name=change_price api=1 all_args=1
	@param id required type=oid
		Price object oid
	@param data optional type=array
		data to be changed , array("date_from" => value , "name" => value, "date_to" => value , prices => array(currency1 => value1 , currency2 => value2 ...))

	@comment
		changes price data.
	**/
	function change_price($arr)
	{
		extract($arr);
		$o = obj($id);
		$parent_obj_props = array("sum" , "val");
		$props = array("date_from" , "name" , "date_to");

		$ol = new object_list();
		$ol->add($o);
		foreach($parent_obj_props as $prop)
		{
			if($data[$prop])
			{
				$o->set_prop($prop , $data[$prop]);
			}
		}
		$o->save();

		if($data["prices"])
		{
			if ($data["prices"][$o->prop("currency")])
			{
				$o->set_prop("sum" , $data["prices"][$o->prop("currency")]);
				unset($data["prices"][$o->prop("currency")]);
			}
			foreach($data["prices"] as $key => $val)
			{
				$col = new object_list(array(
					"lang_id" => array(),
					"site_id" => array(),
					"class_id" => array(CL_PRICE),
					"currency" => $key,
					"parent" => $o->id(),
				));
				$co = reset($col->ids());
				if(!is_oid($co))
				{
					$co = $this->add_other($o,$key,$val);
				}
				$coob = obj($co);
				$coob->set_prop("sum" , $val);
				$coob->save();
				$ol->add($co);
			}
		}
		
		foreach($ol->arr() as $price)
		{
			foreach($props as $prop)
			{
				if($data[$prop])
				{
					if(is_array($data[$prop]))
					{
						$data[$prop] = date_edit::get_timestamp($data[$prop]);
					}
					$price->set_prop($prop , $data[$prop]);
				}
			}
			$price->save();
		}
		return $o->id();
	}
}
?>
