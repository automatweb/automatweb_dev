<?php
// $Header: /home/cvs/automatweb_dev/classes/common/price.aw,v 1.2 2007/11/15 16:57:03 markop Exp $
// price.aw - Hind 
/*

@tableinfo aw_prices index=aw_oid master_table=objects master_index=brother_of

@classinfo syslog_type=ST_PRICE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

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
	function get_prices($arr)
	{
		extract($arr);
		$filter = array(
			"class_id" => array(CL_PRICE),
			"site_id" => array(),
			"lang_id" => array(),
			"CL_PRICE.RELTYPE_OBJECT.id" => $object,
		);
		$ol = new object_list($filter);
		return $ol;
	}

	//object
	//name , parent
	function add($arr)
	{
		$o = obj($arr["object"]);
		$price = new object();
		$price->set_class_id(CL_PRICE);
		$price->set_name($arr["name"] ? $arr["name"] : $o->name()." ".t("hind"));
		$price->set_parent($arr["parent"] ? $arr["parent"] : $arr["object"]);

		if($arr["class_id"])
		{
			$price->set_prop("type" , $arr["class_id"]);
		}

		$price->save();
		$price->connect(array(
			"to" => $arr["object"],
			"type" => RELTYPE_OBJECT,
		));
		
		return $price->id();
	}

	//
	//id , data(name , date_from , date_to, prices)
	function change($arr)
	{
		extract($arr);
		$o = obj($id);
		$props = array("date_from" , "name" , "date_to");
		foreach($props as $prop)
		{
			if($data[$prop])
			{
				if(is_array($data[$prop]))
				{
					$data[$prop] = date_edit::get_timestamp($data[$prop]);
				}
				$o->set_prop($prop , $data[$prop]);
			}
		}
		if($data["prices"])
		{
			$prices = $o->meta("prices");
			foreach($data["prices"] as $key => $val)
			{
				$prices[$key] = $val;
			}
			$o->set_meta("prices" , $prices);
		}
		$o->save();
		return $o->id();
	}

}
?>
