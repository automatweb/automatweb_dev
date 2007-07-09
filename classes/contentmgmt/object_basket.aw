<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_basket.aw,v 1.6 2007/07/09 12:25:23 kristo Exp $
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
		aw_global_set("no_cache", 1);
		$basket = obj($arr["id"]);
		$objs = $this->get_basket_content($basket);
		$this->read_template("show_basket.tpl");

		// parse vars from sub
		$sub_ct = $this->get_template_string("LINE");
		preg_match_all("/\{VAR\:(.*)\}/imsU", $sub_ct, $mt, PREG_PATTERN_ORDER);

		$per_page = 4;
		$cur_page_from = $_GET["bm_page"] * $per_page;
		$cur_page_to = ($_GET["bm_page"]+1) * $per_page;

		$ls = "";
		$counter = 0;
		foreach($objs as $dat)
		{
			if ($counter > $cur_page_from && $counter <= $cur_page_to)
			{
				$v = array(
					"remove_single_url" => $this->mk_my_orb("remove_single", array("basket" => $basket->id(), "item" => $dat["oid"], "ru" => get_ru()))
				);
				$o = obj($dat["oid"]);
				foreach($mt[1] as $var_name)
				{
					list($clid, $prop) = explode(".", $var_name, 2);
					if (constant($clid) == $o->class_id())
					{
						if ($prop == "id")
						{
							$v[$var_name] = $o->id();
						}
						else
						{
							$v[$var_name] = is_array($o->prop($prop)) ? reset($o->prop($prop)) : $o->prop($prop);
						}
					}
				}
				$this->vars_safe($v);
				$ls .= $this->parse("LINE");
			}
			$counter++;
		}
		$pgs = "";
		$num_pages = count($objs) / $per_page;
		for($i = 0; $i < $num_pages; $i++)
		{
			$this->vars_safe(array(
				"page_from" => ($i*$per_page)+1,
				"page_to" => min(($i+1)*$per_page, count($objs)),
				"page_link" => aw_url_change_var("bm_page", $i)
			));
			if ($_GET["bm_page"] == $i)
			{
				$pgs .= $this->parse("SEL_PAGE");
			}
			else
			{
				$pgs .= $this->parse("PAGE");
			}
		}

		$this->vars_safe(array(
			"PAGE" => $pgs,
			"SEL_PAGE" => "",
			"LINE" => $ls,
			"total_count" => count($objs),
			"remove_all_url" => $this->mk_my_orb("remove_all", array("basket" => $basket->id(),"ru" => get_ru())),
			"print_url" => aw_url_change_var("print", 1)
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
			$tz = aw_serialize($ct);
			$this->quote(&$tz);
			$this->set_cval(
				"object_basket_".$o->id()."_".aw_global_get("uid"),
				$tz
			);
		}
		else
		{
			$_SESSION["object_basket"][$o->id()]["content"] = $ct;
		}
		return $arr["ru"];
	}

	/**
		@attrib name=remove_all nologin="1"
		@param basket required type=int acl=view
		@param ru required 
	**/
	function remove_all($arr)
	{
		$o = obj($arr["basket"]);
		$ct = $this->get_basket_content($o);
		$ct = array();
		$bt = $this->make_keys($o->prop("basket_type"));
		if ($bt[OBJ_BASKET_USER] && aw_global_get("uid") != "")
		{
			$tz = aw_serialize($ct);
			$this->quote(&$tz);
			$this->set_cval(
				"object_basket_".$o->id()."_".aw_global_get("uid"),
				$tz
			);
		}
		else
		{
			$_SESSION["object_basket"][$o->id()]["content"] = $ct;
		}
		return $arr["ru"];
	}

	/**
		@attrib name=remove_single nologin="1"
		@param basket required type=int acl=view
		@param item required type=int acl=view
		@param ru required 
	**/
	function remove_single($arr)
	{
		$o = obj($arr["basket"]);
		$ct = $this->get_basket_content($o);
		unset($ct[$arr["item"]]);
		$bt = $this->make_keys($o->prop("basket_type"));
		if ($bt[OBJ_BASKET_USER] && aw_global_get("uid") != "")
		{
			$tz = aw_serialize($ct);
			$this->quote(&$tz);
			$this->set_cval(
				"object_basket_".$o->id()."_".aw_global_get("uid"),
				$tz
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
