<?php

classload("objects");

class keyword_db extends aw_template
{
	function keyword_db()
	{
		$this->init("automatweb/keywords");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_db.tpl");
	
		$this->mk_path($parent, "Lisa keywordide baas");

		$ob = new objects;
		$ol = $ob->get_list();
		$this->vars(array(
			"keyw_cats" => $this->multiple_option_list(array(),$ol),
			"bro_cats" => $this->multiple_option_list(array(),$ol),
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"name" => $name, 
				"oid" => $id
			));
		}
		else
		{
			$id = $this->new_object(array(
				"name" => $name, 
				"parent" => $parent,
				"class_id" => CL_KEYWORD_DB,
				"status" => 2,
			));
		}

		$this->db_query("DELETE FROM keyword_db2keyword_menus WHERE db_id = '$id'");
		if (is_array($keyw_cats))
		{
			foreach($keyw_cats as $mid)
			{
				$this->db_query("INSERT INTO keyword_db2keyword_menus(menu_id,db_id) VALUES('$mid','$id')");
			}
		}

		$this->db_query("DELETE FROM keyword_db2menu WHERE db_id = '$id'");
		if (is_array($bro_cats))
		{
			foreach($bro_cats as $mid)
			{
				$this->db_query("INSERT INTO keyword_db2menu(menu_id,db_id) VALUES('$mid','$id')");
			}
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add_db.tpl");
		
		$obj = $this->get_object($id);
		$this->mk_path($obj["parent"], "Muuda keywordide baasi");

		$ob = new objects;
		$ol = $ob->get_list();
		$this->vars(array(
			"name" => $obj["name"],
			"keyw_cats" => $this->multiple_option_list($this->get_keyw_cats($id),$ol),
			"bro_cats" => $this->multiple_option_list($this->get_bro_cats($id),$ol),
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		return $this->parse();
	}

	function get_keyw_cats($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM keyword_db2keyword_menus WHERE db_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["menu_id"]] = $row["menu_id"];
		}
		return $ret;
	}

	function get_bro_cats($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM keyword_db2menu WHERE db_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["menu_id"]] = $row["menu_id"];
		}
		return $ret;
	}
}

?>