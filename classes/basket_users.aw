<?php
classload("basket");
class basket_users extends basket
{
	function basket_users()
	{
		$this->basket();
	}

	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Lisa kasutaja tellimuste nimekiri");
		$this->read_template("add_uo_list.tpl");

		$this->vars(array(
			"baskets" => $this->picker(0,$this->list_objects(array("class" => CL_SHOP_BASKET, "addempty" => true))),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_doc" => $alias_doc))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_SHOP_BASKET_USER_ORDERS
			));
		}

		if ($alias_doc)
		{
			$this->add_alias($alias_doc, $id);
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"basket" => $basket,
			)
		));
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$ob = $this->get_basket($id);
		$this->mk_path($ob["parent"], "Muuda kasutaja tellimuste nimekirja");
		$this->read_template("add_uo_list.tpl");

		$this->vars(array(
			"name" => $ob["name"],
			"baskets" => $this->picker($ob["meta"]["basket"],$this->list_objects(array("class" => CL_SHOP_BASKET, "addempty" => true))),
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));

		return $this->parse();
	}

	////
	// !gets called if the order list is embedded somewhere
	function parse_alias($args = array())
	{
		extract($args);
		return $this->user_orders(array("id" => $alias["target"]));
	}

	////
	// !displays the orders that the currently logged in user has created
	// and lets you view them and create new ones based on them
	// parameters:
	//    id - order list id
	function user_orders($arr)
	{
		extract($arr);
		$this->read_template("user_orders.tpl");
		$ob = $this->get_object($id);

		$this->_init_table();

		if ($ob["meta"]["basket"])
		{
			$ss = "AND subclass = '".$ob["meta"]["basket"]."'";
		}
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_SHOP_BASKET_ORDER." AND status = 2 AND createdby = '".aw_global_get("uid")."' $ss");
		while ($row = $this->db_next())
		{
			$row["meta"] = aw_unserialize($row["metadata"]);
			$row["t_price"] = $row["meta"]["t_price"];
			$row["view"] = sprintf("<a href='%s'><b>%s</b></a>",$this->mk_my_orb("change",array("id" => $row["oid"]),"basket_order"),"Vaata");
			$this->t->define_data($row);
		}
		$this->t->sort_by();

		$this->vars(array(
			"table" => $this->t->draw(),
		));
		return $this->parse();
	}

	function _init_table()
	{
		$this->t = new aw_table(array(
			"prefix" => "basket_users",
			"tbgcolor" => "#C3D0DC",
		));
		$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

		$this->t->define_field(array(
			"name" => "oid",
			"caption" => "Tellimuse nr",
			"talign" => "center",
			"align" => "center",
			"nowrap" => 1,
			"sortable" => 1,
			"numeric" => 1
		));

		$this->t->define_field(array(
			"name" => "created",
			"caption" => "Millal",
			"talign" => "center",
			"nowrap" => 1,
			"sortable" => 1,
			"type" => "time",
			"format" => "d-M-y/H:i",
			"numeric" => 1
		));
		
		
		$this->t->define_field(array(
			"name" => "t_price",
			"caption" => "Hind",
			"talign" => "center",
			"nowrap" => 1,
			"sortable" => 1,
			"numberic" => 1
		));
		
		$this->t->define_field(array(
			"name" => "view",
			"caption" => "Vaata",
			"talign" => "center",
			"nowrap" => 1,
			"align" => "center",
		));
		$this->t->set_default_sortby("order_id");
	}
}
?>