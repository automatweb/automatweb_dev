<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/currency.aw,v 2.6 2002/06/10 15:50:53 kristo Exp $
// currency.aw - Currency management

define("RET_NAME",1);
define("RET_ARR",2);

class currency extends aw_template
{
	function currency()
	{
		$this->init("currency");
		$this->sub_merge = 1;
		$this->lc_load("currency","lc_currency");	
		lc_load("definition");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_currency.tpl");
		$this->mk_path($parent, LC_CURRENCY_ADD);

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $ratio));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_CURRENCY, "name" => $name, "comment" => $ratio));
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$o = $this->get_object($id);
		$this->mk_path($o["parent"], "LC_CURRENCY_CHANGE");
		$this->read_template("add_currency.tpl");

		$this->vars(array(
			"name" => $o["name"],
			"ratio" => $o["comment"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		return $this->parse();
	}

	function get_list($type = RET_NAME)
	{
		$ret = array();
		$this->db_query("SELECT oid,name,comment as rate FROM objects WHERE class_id = ".CL_CURRENCY." AND status != 0 AND site_id = ".$this->cfg["site_id"]);
		while ($row = $this->db_next())
		{
			if ($type == RET_NAME)
			{
				$ret[$row["oid"]] = $row["name"];
			}
			else
			if ($type == RET_ARR)
			{
				$ret[$row["oid"]] = $row;
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
