<?php

class keyword_db extends aw_template
{
	function keyword_db()
	{
		$this->init("automatweb/keywords");
	}

	/**  
		
		@attrib name=new params=name default="0"
		
		@param parent required acl="add"
		
		@returns
		
		
		@comment

	**/
	function add($arr)
	{
		extract($arr);
		$this->read_template("add_db.tpl");
	
		$this->mk_path($parent, "Lisa keywordide baas");

		$ol = $this->get_menu_list();
		$this->vars(array(
			"keyw_cats" => $this->multiple_option_list(array(),$ol),
			"bro_cats" => $this->multiple_option_list(array(),$ol),
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$o = obj($id);
			$o->set_name($name);
			$o->save();
		}
		else
		{
			$o = obj();
			$o->set_name($name);
			$o->set_parent($parent);
			$o->set_class_id(CL_KEYWORD_DB);
			$o->set_status(STAT_ACTIVE);
			$id = $o->save();
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

	/**  
		
		@attrib name=change params=name default="0"
		
		@param id required acl="edit;view"
		
		@returns
		
		
		@comment

	**/
	function change($arr)
	{
		extract($arr);
		$this->read_template("add_db.tpl");
	
		$obj = new object($id);
		$this->mk_path($obj->parent(), "Muuda keywordide baasi");

		$ol = $this->get_menu_list();
		$this->vars(array(
			"name" => $obj->name(),
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
