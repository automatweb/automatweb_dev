<?php

define("FOR_SELECT",1);
define("ALL_PROPS",2);

// not publicly accessible, jsut to derive from
class shop_base extends aw_template
{
	function shop_base()
	{
		$this->tpl_init("shop");
		$this->db_init();
		$this->sub_merge = 1;
	}

	////
	// !returns the item type
	function get_item_type($id)
	{
		if (is_array($this->type_cache[$id]))
		{
			return $this->type_cache[$id];
		}
		$this->db_query("SELECT objects.*,shop_item_types.* FROM objects LEFT JOIN shop_item_types ON shop_item_types.id = objects.oid WHERE oid = $id");
		return $this->db_next();
	}

	////
	// !returns an array of all shop item types 
	function listall_item_types($type = FOR_SELECT)
	{
		$ret = array();
		if ($type == FOR_SELECT)
		{
			$this->db_query("SELECT objects.name, objects.oid FROM objects WHERE class_id = ".CL_SHOP_ITEM_TYPE." AND status != 0 AND SITE_ID = ".$GLOBALS["SITE_ID"]);
		}
		else
		{
			$this->db_query("SELECT objects.*,shop_item_types.* FROM objects LEFT JOIN shop_item_types ON shop_item_types.id = objects.oid WHERE class_id = ".CL_SHOP_ITEM_TYPE." AND status != 0 AND SITE_ID = ".$GLOBALS["SITE_ID"]);
		}
		while ($row = $this->db_next())
		{
			if ($type == FOR_SELECT)
			{
				$ret[$row["oid"]] = $row["name"];
			}
			else
			{
				$ret[$row["oid"]] = $row;
			}
		}
		return $ret;
	}

	////
	// !returns the shop item $id, if $check is true we detect if the object is a brother and revert to the real one
	function get_item($id,$check = false)
	{
		if ($check)
		{
			$o = $this->get_object($id);
			$id = $o["brother_of"];
		}
		$this->db_query("SELECT objects.*,shop_items.* FROM objects LEFT JOIN shop_items ON shop_items.id = objects.oid WHERE id = $id");
		return $this->db_next();
	}

	////
	// !returns the shop equasion $id
	function get_eq($id)
	{
		if (!$id)
		{
			return false;
		}
		return $this->get_object($id);
	}

	function listall_eqs()
	{
		$ret = array();
		$this->db_query("SELECT oid,name,comment FROM objects WHERE class_id = ".CL_SHOP_EQUASION." AND status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}
}
?>