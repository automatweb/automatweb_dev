<?php

classload("shop/shop_base");
class shop_eq extends shop_base
{
	function shop_eq()
	{
		$this->shop_base();
		lc_load("definition");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_eq.tpl");
		$this->mk_path($parent, LC_SHOP_EQ_ADD_FORMULA);

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		if ($name == "")
		{
			$name = $eq;
		}

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $eq));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP_EQUASION, "name" => $name, "comment" => $eq));
		}

		return $this->mk_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add_eq.tpl");
		$eq = $this->get_eq($id);
		$this->mk_path($eq["parent"], LC_SHOP_EQ_CHANGE_FORMULA);

		$this->vars(array(
			"name" => $eq["name"],
			"eq" => $eq["comment"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		return $this->parse();
	}

	function check_environment(&$sys, $fix = false)
	{
		$ret = $sys->check_admin_templates("shop", array("add_eq.tpl"));
		$ret.= $sys->check_orb_defs(array("shop_eq"));
		return $ret;
	}
}
?>