<?php

global $orb_defs;
$orb_defs["shop_stat"] = "xml";

classload("shop");
class shop_stat extends shop
{
	function shop_stat()
	{
		$this->shop();
	}

	////
	// !shows adding form
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Lisa poe statistika");
		$this->read_template("shop_stat_add.tpl");

		
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"shop_list" => $this->multiple_option_list(array(),$this->get_list())
		));
		return $this->parse();
	}

	////
	// !adds or saves the shop stat object
	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_SHOP_STATS, "name" => $name, "comment" => $comment));
		}

		$this->db_query("DELETE FROM shop2shop_stat WHERE stat_id = $id");

		if (is_array($shops))
		{
			foreach($shops as $shid)
			{
				$this->db_query("INSERT INTO shop2shop_stat(shop_id,stat_id) values($shid,$id)");
			}
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !returns an array of shops for stat object $id
	function get_shops_for_stat($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM shop2shop_stat WHERE stat_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["shop_id"]] = $row["shop_id"];
		}
		return $ret;
	}

	////
	// !return sthe stat object
	function get($id)
	{
		return $this->get_object($id);
	}

	////
	// !shows statistics
	function change($arr)
	{
		extract($arr);
		$st = $this->get($id);
		$this->mk_path($st["parent"], "Poodide statisika");
		$this->read_template("show_shop_stat.tpl");

		load_vcl("date_edit");
		$de = new date_edit(time());
		$de->configure(array(
			"year" => "",
			"month" => "",
			"day" => "",
			"hour" => "",
			"minute" => ""
		));
	
		
		$this->vars(array(
			"change" => $this->mk_my_orb("change_stat", array("id" => $id)),
			"t_from" => $de->gen_edit_form("from", 0),
			"t_to"	=> $de->gen_edit_form("to", time()),
		));
		return $this->parse();
	}

	////
	// !change form for stat object
	function change_stat($arr)
	{
		extract($arr);
		$st = $this->get($id);
		$this->mk_path($st["parent"], "Muuda poe statistikat");
		$this->read_template("shop_stat_add.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"shop_list" => $this->multiple_option_list($this->get_shops_for_stat($id),$this->get_list()),
			"name" => $st["name"],
			"comment" => $st["comment"]
		));

		return $this->parse();
	}
}
?>