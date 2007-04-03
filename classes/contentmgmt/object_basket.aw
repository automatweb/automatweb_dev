<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_basket.aw,v 1.2 2007/04/03 12:36:14 kristo Exp $
// object_basket.aw - Objektide korv 
/*

@classinfo syslog_type=ST_OBJECT_BASKET relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property basket_type type=chooser field=meta method=serialize multiple=1
	@caption Korvi t&uuml;&uuml;p
	

*/

define("OBJ_BASKET_SESSION", 1);
define("OBJ_BASKET_USER", 2);
class object_basket extends class_base
{
	function object_basket()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/object_basket",
			"clid" => CL_OBJECT_BASKET
		));

		$this->basket_types = array(
			OBJ_BASKET_SESSION => t("Sessioonip&otilde;hine"),
			OBJ_BASKET_USER => t("Kasutajap&otilde;hine")
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}

	function _get_basket_type($arr)
	{
		$arr["prop"]["options"] = $this->basket_types;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$basket = obj($arr["id"]);
		$objs = $this->get_basket_content($basket);
		$this->read_template("show_basket.tpl");

		// parse vars from sub
		$sub_ct = $this->get_template_string("LINE");
		preg_match_all("/\{VAR\:(.*)\}/imsU", $sub_ct, $mt, PREG_PATTERN_ORDER);

		$ls = "";
		foreach($objs as $dat)
		{
			$o = obj($dat["oid"]);
			foreach($mt[1] as $var_name)
			{
				$v = array();
				list($clid, $prop) = explode(".", $var_name, 2);
				if (constant($clid) == $o->class_id())
				{
					$v[$var_name] = $o->prop_str($prop);
				}
			}
			$this->vars($v);
			$ls .= $this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $ls
		));
		return $this->parse();
	}

	/** Returns the basket content
		@attrib api=1

		@param o required type=object
			The basket object with the configuration

	**/
	function get_basket_content($o)
	{
		$bt = $this->make_keys($o->prop("basket_type"));
		if ($bt[OBJ_BASKET_USER] && aw_global_get("uid") != "")
		{
			return safe_array(aw_unserialize($this->get_cval("object_basket_".$o->id()."_".aw_global_get("uid"))));
		}
		if ($bt[OBJ_BASKET_SESSION])
		{
			return safe_array($_SESSION["object_basket"][$o->id()]["content"]);
		}
	}

	/**
		@attrib name=add_object nologin="1"
		@param oid required type=int acl=view
		@param basket required type=int acl=view
		@param ru required 
	**/
	function add_object($arr)
	{
		$o = obj($arr["basket"]);
		$ct = $this->get_basket_content($o);
		$ct[$arr["oid"]]["oid"] = $arr["oid"];
		$bt = $this->make_keys($o->prop("basket_type"));
		if ($bt[OBJ_BASKET_USER] && aw_global_get("uid") != "")
		{
			$this->set_cval(
				"object_basket_".$o->id()."_".aw_global_get("uid"),
				aw_serialize($ct)
			);
		}
		else
		{
			$_SESSION["object_basket"][$o->id()]["content"] = $ct;
		}
		return $arr["ru"];
	}
}
?>
