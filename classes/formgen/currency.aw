<?php
// $Header: /home/cvs/automatweb_dev/classes/formgen/currency.aw,v 1.1 2004/11/07 19:40:42 kristo Exp $
// currency.aw - Currency management

/*

@classinfo syslog_type=ST_CURRENCY no_status=1 

@default group=general

@property comment type=textbox table=objects field=comment 
@caption Kurss saksa marga suhtes

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
