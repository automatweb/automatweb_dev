<?php

//maintainer=markop  
class price_object extends _int_object
{
	function _init_override_object()
	{
		;
	}

	function price_object()
	{
		parent::_int_object();
	}

	function name()
	{
		return parent::name();
	}

	function set_prop($name,$value)
	{
		$children = $this->_get_price_children();
		$toallprops = array("date_from" , "name" , "date_to");

		if(is_array($value) && array_key_exists("year" ,$value))
		{
			$value = date_edit::get_timestamp($value);
		}
		if(in_array($name , $toallprops))
		{
			foreach($children->arr() as $c)
			{
				$c->set_prop($name , $value);
			}
		}
		parent::set_prop($name,$value);
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
	function get_price_objects($object)
	{
		$filter = array(
			"class_id" => array(CL_PRICE),
			"site_id" => array(),
			"lang_id" => array(),
			"CL_PRICE.RELTYPE_OBJECT.id" => $object,
//			"CL_PRICE.RELTYPE_.id" => $object,
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

	function set_prices($prices)
	{
		$curr_array = $this->_get_curr_objects();
		foreach($prices as $key => $val)
		{
			if($val != "")
			{
				if($curr_array[$key])
				{
					$co = $curr_array[$key];
				}
				else
				{
					$co = $this->add_other($key,$val);
				}
				$coob = obj($co);
				$coob->set_prop("sum" , $val);
				$coob->save();
			}
			else
			{
				if($curr_array[$key])
				{
					$co = obj($curr_array[$key]);
					$co->delete();
				}
			}
		}
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
		foreach($arr as $key => $val)
		{
			if($price->is_property($key) && $val)
			{
				$price->set_prop($key , $val);
			}

		}
/*
		if($arr["currency"])
		{
			$price->set_prop("currency" , $arr["currency"]);
		}
		if($arr["sum"])
		{
			$price->set_prop("sum" , $arr["sum"]);
		}*/

		$price->save();
		if(is_oid($arr["object"]))
		{
			$price->connect(array(
				"to" => $arr["object"],
				"type" => "RELTYPE_OBJECT",
			));
		}
		return $price->id();
	}


	/**
	@returns array("currency" => "price")

	@comment
		returns the price in different curr
		uses child objects
	**/
	function get_prices()
	{
		$ret = array();
		$ret[$this->prop("currency")] = $this->prop("sum");

		$ol = $this->_get_price_children();

		foreach($ol->arr() as $obj)
		{
			$ret[$obj->prop("currency")] = $obj->prop("sum");
		}
		return $ret;
	}

	/**
		@attrib name=set_data api=1 all_args=1
		@param data optional type=array
		data to be changed , array("date_from" => value , "name" => value, "date_to" => value , prices => array(currency1 => value1 , currency2 => value2 ...))

	@comment
		changes price data.
	**/
	function set_data($arr)
	{
		extract($arr);
		foreach($arr as $prop => $val)
		{
			if($prop == "prices")
			{
				$this->set_prices($val);
			}
			else
			{
				$this->set_prop($prop , $val);
			}
		}
		$this->save();
		return $this->id();
/*		$o = obj($id);
		$parent_obj_props = array("sum" , "val");
		$props = array("date_from" , "name" , "date_to");

		$ol = new object_list();
		$ol->add($this);
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
		return $o->id();*/
	}

	//object
	//name , parent
	function add_other($curr,$sum)
	{
		$objs = $this->connections_from(array(
			"type" => "RELTYPE_OBJECT",
		));
		foreach($objs as $obj)
		{
			$object = $obj->prop("to");
		}

		$co = $this->add(array(
			"name" => $this->name(),
			"parent" => $this->id(),
			"date_from" => $this->prop("date_from"),
			"date_to" => $this->prop("date_to"),
			"object" => $object,
			"currency" => $curr,
			"sum" => $sum,
		));

		return $co;
	}

	function _get_price_children()
	{
		if(!$this->price_children)
		{
			$this->price_children = new object_list(array(
				"lang_id" => array(),
				"site_id" => array(),
				"class_id" => array(CL_PRICE),
				"parent" => $this->id(),
			));
		}
		return $this->price_children;
	}

	function _get_curr_objects()
	{
		$c = $this->_get_price_children();
		$ret = array();
		foreach($c->arr() as $o)
		{
			$ret[$o->prop("currency")] = $o->id();
		}
		return $ret;
	}



}
?>