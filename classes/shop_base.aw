<?php
define("FOR_SELECT",1);
define("ALL_PROPS",2);

define("PRICE_PER_WEEK",1);
define("PRICE_PER_DAY",2);

// not publicly accessible, jsut to derive from
class shop_base extends aw_template
{
	function shop_base()
	{
		$this->init("shop");
		$this->sub_merge = 1;
		lc_load("definition");

		$this->lc_load("shop","lc_shop");
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
			$this->db_query("SELECT objects.name, objects.oid FROM objects WHERE class_id = ".CL_SHOP_ITEM_TYPE." AND status != 0 AND SITE_ID = ".$this->cfg["site_id"]);
		}
		else
		{
			$this->db_query("SELECT objects.*,shop_item_types.* FROM objects LEFT JOIN shop_item_types ON shop_item_types.id = objects.oid WHERE class_id = ".CL_SHOP_ITEM_TYPE." AND status != 0 AND SITE_ID = ".$this->cfg["site_id"]);
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
			$this->raise_error(ERR_SHOP_NMITEM,"can't find the matching shop for the item!", true);
		}
		return $shop;
	}

	function listall_items($type = FOR_SELECT,$constraint = "")
	{
		$ret = array();
		$this->db_query("SELECT objects.*,shop_items.* FROM objects LEFT JOIN shop_items ON shop_items.id = objects.oid WHERE class_id = ".CL_SHOP_ITEM." AND status = 2 $constraint ORDER BY jrk");
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

	function get($id)
	{
		$this->db_query("SELECT shop.*,objects.* FROM objects LEFT JOIN shop ON shop.id = objects.oid WHERE objects.oid = $id");
		return $this->db_next();
	}

	function do_core_change_tables($id)
	{
		$itypes = $this->listall_item_types();
		$tables = $this->list_objects(array("class" => CL_FORM_TABLE));

		$this->db_query("SELECT * FROM shop2table WHERE shop_id = $id");
		while ($row = $this->db_next())
		{
			$sh2t[$row["type_id"]] = $row["table_id"];
		}

		foreach($itypes as $typeid => $typename)
		{
			$this->vars(array(
				"typename" => $typename,
				"type_id" => $typeid,
				"tables" => $this->picker($sh2t[$typeid], $tables)
			));
			$this->parse("TYPE");
		}
	}

	function get_article_menus($shop_id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM shop2article_menu WHERE shop_id = '$shop_id'");
		while ($row = $this->db_next())
		{
			$ret[$row["menu_id"]] = $row["menu_id"];
		}
		return $ret;
	}

	function save_article_menus($shop_id, $sel)
	{
		$this->db_query("DELETE FROM shop2article_menu WHERE shop_id = '$shop_id'");
		foreach($sel as $mid)
		{
			$this->db_query("INSERT INTO shop2article_menu (shop_id, menu_id) VALUES('$shop_id','$mid')");
		}
	}

	function do_core_admin_ofs($id)
	{
		$ofs = $this->get_ofs_for_shop($id);
		foreach($ofs as $ofid => $chk)
		{
			$ofa[$ofid] = $ofid;
		}
		$fb = new form_base;
		$fl = $fb->get_list(FTYPE_ENTRY);
		$op_list = $fb->get_op_list();
		foreach($ofs as $of_id => $row)
		{
			$this->vars(array(
				"of_id" => $of_id,
				"of_name" => $this->db_fetch_field("SELECT name FROM objects WHERE oid = ".$of_id,"name"),
				"of_checked" => checked($row["repeat"]),
				"of_ops" => $this->picker($row["op_id"],$op_list[$of_id]),
				"of_ops_long" => $this->picker($row["op_id_long"],$op_list[$of_id]),
				"of_ops_search" => $this->picker($row["op_id_search"],$op_list[$of_id])
			));
			$of.=$this->parse("OF");
		}
		$this->vars(array(
			"OF" => $of,
			"of" => $this->multiple_option_list($ofa,$fl),
		));
	}

	function get_ofs_for_shop($id)
	{
		$ret = array();
		$this->db_query("SELECT of_id,repeat,op_id,op_id_long,op_id_search FROM shop2order_form WHERE shop_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["of_id"]] = $row;
		}
		return $ret;
	}

	function do_core_save_ofs($id,$order_form,$of_rep,$of_op,$of_op_long,$of_op_search)
	{
		$this->db_query("DELETE FROM shop2order_form WHERE shop_id = '$id'");
		if (is_array($order_form))
		{
			foreach($order_form as $of_id)
			{
				$this->db_query("INSERT INTO shop2order_form(shop_id,of_id,repeat,op_id,op_id_long,op_id_search) values($id,$of_id,'".$of_rep[$of_id]."','".$of_op[$of_id]."','".$of_op_long[$of_id]."','".$of_op_search[$of_id]."')");
			}
		}
	}

	function listall_shop_tables()
	{
		$ret = array();
		$this->db_query("SELECT oid,name FROM objects WHERE class_id = ".CL_SHOP_TABLE." AND status != 0 ");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	function get_tables_for_types()
	{
		$ret = array();
		$this->db_query("SELECT * FROM shop_table2item_type");
		while ($row = $this->db_next())
		{
			$ret[$row["type_id"]] = $row["table_id"];
		}
		return $ret;
	}

	function get_user_tables_for_types()
	{
		$uid = aw_global_get("uid");
		$all_users_grp = aw_ini_get("groups.all_users_grp");
		$gid = $this->db_fetch_field("SELECT groups.gid as gid FROM groupmembers LEFT JOIN groups ON groups.gid = groupmembers.gid WHERE groupmembers.uid = '$uid' AND groupmembers.gid != $all_users_grp AND (groups.type = ".GRP_REGULAR." OR groups.type = ".GRP_DYNAMIC.")  ORDER BY groups.priority DESC","gid");

		$ret = array();
		$this->db_query("SELECT * FROM shop_table2item_type WHERE gid = $gid");
		while ($row = $this->db_next())
		{
			$ret[$row["type_id"]] = $row["table_id"];
		}
		return $ret;
	}

	////
	// !returns the items that should be shown to the currently logged in user
	function get_user_item_picker($arr = array())
	{
		classload("config");
		$con = new config;
		$_its = $con->get_simple_config("show_items");
		$its = aw_unserialize($_its);

		// now find the group with the biggest priority that the user belongs to.
		$uid = aw_global_get("uid");
		$all_users_grp = aw_ini_get("groups.all_users_grp");

		$gid = $this->db_fetch_field("SELECT groups.gid as gid FROM groupmembers LEFT JOIN groups ON groups.gid = groupmembers.gid WHERE groupmembers.uid = '$uid' AND groupmembers.gid != $all_users_grp AND (groups.type = ".GRP_REGULAR." OR groups.type = ".GRP_DYNAMIC.") ORDER BY groups.priority DESC","gid");

		$arr["short_name"] = 1;
		if ($its["groups"][$gid]["all_items"] == 1)
		{
			return $this->get_item_picker($arr);
		}
		else
		{
			return $this->get_item_picker(array("item_ids" => $its["groups"][$gid]["items"],"type" => $arr["type"],"short_name" => 1));
		}
	}

	////
	// !returns all items that are not deleted and not under deleted menus either
	// parameters: constraint = string that is placed in WHERE clause of item select query
	// type == FOR_SELECT - returns array of item_id => item_name
	// type == ALL_PROPS - returns array of item_id => item_props_array
	// item_ids = array, if set, specifies the ids if items to include
	function get_item_picker($arr = array())
	{
		extract($arr);
		classload("objects");
		$ob = new objects;
		if ($short_name == 1)
		{
			$this->db_query("SELECT oid,name FROM objects WHERE class_id = ".CL_PSEUDO." AND status != 0");
			while ($row = $this->db_next())
			{
				$menus[$row["oid"]] = $row["name"];
			}
		}
		else
		{
			$menus = $ob->get_list(false,false,($root_menu ? $root_menu : -1));
		}

		if (is_array($item_ids))
		{
			$cons = " AND oid IN (".join(",",$this->map("%d",$item_ids)).")";
			$items = $this->listall_items(ALL_PROPS,$cons);
		}
		else
		{
			$items = $this->listall_items(ALL_PROPS,$arr["constraint"]);
		}
		$ret = array();
		foreach($items as $iid => $irow)
		{
			if (isset($menus[$irow["parent"]]))
			{
				if ($type == ALL_PROPS)
				{
					$ret[$iid] = $irow;
				}
				else
				{
					$ret[$iid] = $menus[$irow["parent"]]."/".$irow["name"];
				}
			}
		}
		return $ret;
	}
}
?>