<?php
// $Header: /home/cvs/automatweb_dev/classes/export/menu_export.aw,v 1.2 2004/09/03 09:58:11 kristo Exp $
// menu_export.aw - helper class for exporting menus
class menu_export
{
	function menu_export()
	{


	}
	////
	// !exports menu $id and all below it
	// if $ret_data is true, then the export arr is returned, not output
	function export_menus($arr)
	{
		extract($arr);
		if (!is_array($ex_menus))
		{
			return;
		}

		$i = get_instance("icons");
		$this->m = get_instance("menuedit");
		
		$menus = array("0" => $id);

		// ok. now we gotta figure out which menus the user wants to export. 
		// he can select just the lower menus and assume that the upper onec come along with them.
		// biyaatch 

		// kay. so we cache the menus
		$this->m->db_listall();
		while ($row = $this->m->db_next())
		{
			$this->mar[$row["oid"]] = $row;
		}

		// this keeps all the menus that will be selected
		$sels = array();	
		// now we start going through the selected menus
		reset($ex_menus);
		while (list(,$eid) = each($ex_menus))
		{
			// and for each we run to the top of the hierarchy and also select all menus 
			// so we will gather a list of all the menus we need. groovy.
			
			$sels[$eid] = $eid;
			while ($eid != $id && $eid > 0)
			{
				$sels[$eid] = $eid;
				$eid = $this->mar[$eid]["parent"];
			}
		}

		// so now we have a complete list of menus to fetch.
		// so fetchemall
		reset($sels);
		while (list(,$eid) = each($sels))
		{
			$row = $this->mar[$eid];
			if ($allactive)
			{
				$row["status"] = 2;
			}
			$this->append_exp_arr($row,&$menus,$ex_icons,$i);
		}

		if ($ret_data)
		{
			return $menus;
		}

		/// now all menus are in the array with all the other stuff, 
		// so now export it.
		header("Content-type: x-automatweb/menu-export");
		header("Content-Disposition: filename=awmenus.txt");
		echo serialize($menus);
		die();
	}

	function append_exp_arr($db, $menus,$ex_icons,&$i)
	{
		$ret = array();
		$ret["db"] = $db;
		if ($ex_icons)
		{
			$icon = -1;
			// admin_feature icon takes precedence over menu's icon. so include just that.
			if ($db["admin_feature"] > 0)
			{
				$icon = $this->m->pr_icons[$db["admin_feature"]]["id"];
				if ($icon)
				{
					$icon = $i->get($icon);
				}
			}
			else
			if ($db["icon_id"] > 0)
			{
				$icon = $i->get($db["icon_id"]);
			}
			$ret["icon"] = $icon;
		}
		$menus[$db["parent"]][] = $ret;
	}

};
?>
