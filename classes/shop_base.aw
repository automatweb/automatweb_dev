<?php
lc_load("shop");
define("FOR_SELECT",1);
define("ALL_PROPS",2);

define("PRICE_PER_WEEK",1);
define("PRICE_PER_DAY",2);

// not publicly accessible, jsut to derive from
class shop_base extends aw_template
{
	function shop_base()
	{
		$this->tpl_init("shop");
		$this->db_init();
		$this->sub_merge = 1;
		lc_load("definition");
		lc_load("shop");

		global $lc_shop;
		if (is_array($lc_shop))
		{
			$this->vars($lc_shop);
	}

	}

	////
	// !returns the item type
	function get_item_type($id)
	{
		if (isset($this->type_cache[$id]))
		{
			return $this->type_cache[$id];
		}
		$this->db_query("SELECT objects.*,shop_item_types.* FROM objects LEFT JOIN shop_item_types ON shop_item_types.id = objects.oid WHERE oid = $id");
		$ret =  $this->db_next();
		$this->type_cache[$id] = $ret;
		return $ret;
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

	function listall_eqs($addempty = false)
	{
		if ($addempty)
		{
			$ret = array(0 => "");
		}
		else
		{
			$ret = array();
		}
		$this->db_query("SELECT oid,name,comment FROM objects WHERE class_id = ".CL_SHOP_EQUASION." AND status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	function find_shop_id($section)
	{
		// now here's the possibility that $shop was omitted. therefore we must figure it out ourself
		// we do that by loading all the root folders for all the shops
		// and then traversing the object tree from the current point upwards until we hit a shop root folder.
		// what if we don't ? hm. well. error message sounds l33t :p
		$shfolders = array();
		$this->db_query("SELECT id,root_menu FROM objects,shop WHERE objects.oid = shop.id AND objects.status != 0 AND objects.class_id = ".CL_SHOP);
		while ($row = $this->db_next())
		{
			$shfolders[$row["root_menu"]] = $row["id"];
		}

		$oc = $this->get_object_chain($section);
		foreach($oc as $oid => $orow)
		{
			if ($shfolders[$oid])
			{
				// and we found a matching root folder!
				$shop = $shfolders[$oid];
				break;
			}
		}

		if (!$shop)
		{
			$this->raise_error("can't find the matching shop for the item!", true);
		}
		return $shop;
	}

	function listall_items($type = FOR_SELECT)
	{
		$ret = array();
		$this->db_query("SELECT objects.* FROM objects WHERE class_id = ".CL_SHOP_ITEM." AND status = 2");
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
}
?>