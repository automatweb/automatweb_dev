<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/keyword_db.aw,v 2.10 2008/01/31 13:49:48 kristo Exp $
// keyword_db.aw - V&otilde;tmes&otilde;nade baas
/*
@classinfo syslog_type=ST_KEYWORD_DB no_status=1 no_comment=1 maintainer=kristo

@default table=objects
@default group=general

@property keyw_cats type=select multiple=1 rows=20 store=no
@caption Kataloogid, mille alt v&otilde;tmes&otilde;nad kuuluvad sellesse baasi

@property bro_cats type=select multiple=1 rows=20 store=no
@caption Kataloogid, mille alla saab selle baasi v&otilde;tmes&otilde;nu vennastada


*/
class keyword_db extends class_base
{
	function keyword_db()
	{
		$this->init(array(
			"clid" => CL_KEYWORD_DB,
			"tpldir" => "automatweb/keywords",
		));
	}

	function callback_on_load($arr)
	{
		$this->ol = $this->get_menu_list();
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "keyw_cats":
				$prop["options"] = $this->ol;
				if(!$arr["new"])
				{
					$id = $arr["obj_inst"]->id();
					$prop["value"] = $this->get_keyw_cats($id);
				}
				break;

			case "bro_cats":
				$prop["options"] = $this->ol;
				if(!$arr["new"])
				{
					$id = $arr["obj_inst"]->id();
					$prop["value"] = $this->get_bro_cats($id);
				}
				break;
		}
	}

	function callback_post_save($arr)
	{
		$id = $arr["obj_inst"]->id();
		$keyw_cats = $arr["request"]["keyw_cats"];
		$this->db_query("DELETE FROM keyword_db2keyword_menus WHERE db_id = '$id'");
		if (is_array($keyw_cats))
		{
			foreach($keyw_cats as $mid)
			{
				$this->db_query("INSERT INTO keyword_db2keyword_menus(menu_id,db_id) VALUES('$mid','$id')");
			}
		}
		$bro_cats = $arr["request"]["bro_cats"];
		$this->db_query("DELETE FROM keyword_db2menu WHERE db_id = '$id'");
		if (is_array($bro_cats))
		{
			foreach($bro_cats as $mid)
			{
				$this->db_query("INSERT INTO keyword_db2menu(menu_id,db_id) VALUES('$mid','$id')");
			}
		}
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
