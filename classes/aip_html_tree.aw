<?php

class aip_html_tree extends class_base
{
	function aip_html_tree()
	{
		$this->init("aip_html_tree");
	}

	function show($arr)
	{
		extract($arr);
		$this->read_template("tree.tpl");

		if (!$parent)
		{
			$parent = get_root();
		}

		$cache_dir = aw_ini_get("cache.page_cache");
		$cache_file = $cache_dir."/aip_html_tree::cache::lang_id::".aw_global_get("lang_id")."::parent::$parent";

		if (!cache_is_expired($cache_file,$this))
		{
			return $this->get_file(array("file" => $cache_file));
		}

		$this->oc = $this->get_object_chain($parent);

		// ok, start from the first level and go down all levels, for each checking if this level contains the
		// active menu and if so, recurse to that
		$ml = array();
		$this->draw_req_menus(get_root(), &$ml);


		// make yah link.
		$root = get_root();

		$y = array();
		foreach($this->oc as $ocid => $ocdat)
		{
			$this->vars(array(
				"text" => $ocdat["name"],
				"link" => $this->mk_my_orb("show", array("parent" => $ocid))
			));
			$y[] = $this->parse("YAH_ENTRY");
			if ($ocid == $root)
			{
				break;
			}
		}

		$this->vars(array(
			"MENU" => "<table border='0' cellpadding='0' cellspacing='0'>".join("\n", $ml)."</table>",
			"YAH_ENTRY" => join(" / ",array_reverse($y))
		));
		$ret = $this->parse();
		$this->put_file(array(
			"file" => $cache_file,
			"content" => $ret
		));
		return $ret;
	}

	function draw_req_menus($parent, &$ml)
	{
		$this->level++;
		$data = array();
		$ids = new aw_array();
		$counts = array();
		$fnames = array();
		$af_cache = get_instance("aip_pdf_cache");

		// get all menus for this level
		$this->db_query("SELECT oid,name,metadata FROM objects WHERE parent = $parent AND class_id = ".CL_PSEUDO." AND status = 2 AND lang_id = ".aw_global_get("lang_id")." ORDER BY objects.jrk");
		while ($row = $this->db_next())
		{
			$row['meta'] = aw_unserialize($row['metadata']);
			$data[] = $row;
			$ids->set($row['oid']);
			$fnames[] = $row['meta']['aip_filename'];
		}

		$this->db_query("SELECT parent FROM objects WHERE parent IN (".$ids->to_sql().") AND class_id = ".CL_PSEUDO." AND status = 2 AND lang_id = ".aw_global_get("lang_id"));
		while ($row = $this->db_next())
		{
			$counts[$row['parent']]++;
		}

		$num = 0;
		$cnt = count($data);
		foreach($data as $row)
		{
			if ($cnt-1 == $num && $this->level == 1)
			{
				$this->first_level_menu_is_last = true;
			}
			else
			if ($this->level == 1)
			{
				$this->first_level_menu_is_last = false;
			}

			$this->vars(array(
				"link" => $this->mk_my_orb("show", array("parent" => $row['oid'])),
				"name" => $row["name"],
				"section" => $row['oid']
			));

			if ($counts[$row['oid']])
			{
				$ms = $this->parse("MENU");
			}
			else
			{
				$ms = $this->parse("MENU_NOSUBS");
			}

			if ($this->level > 1 && $af_cache->get_pdf_count_for_menu($row['meta']['aip_filename']) > 0)
			{
				$ms .= $this->parse("GET_PDF");
			}

			// if the first level menu on this line is the last in it's level, then the first image must be empty
			if ($this->level == 1)
			{
				$str = "";
			}
			else
			if ($this->first_level_menu_is_last)
			{
				$str = "<td><img HSPACE='0' VSPACE='0' src='".$this->cfg["baseurl"]."/images/ftv2blank.gif'></td>";
			}
			else
			{
				$str = "<td><img HSPACE='0' VSPACE='0' src='".$this->cfg["baseurl"]."/images/ftv2vertline.gif'></td>";
			}

			if ($counts[$row['oid']])
			{
				$str .= str_repeat("<td><img HSPACE='0' VSPACE='0' src='".$this->cfg["baseurl"]."/images/ftv2vertline.gif'></td>", max(0,$this->level-2));
				if ($cnt-1 == $num)
				{
					$str.= "<td><a href='".$this->mk_my_orb("show", array("parent" => $row['oid']))."'><img HSPACE='0' VSPACE='0' border='0' src='".$this->cfg["baseurl"]."/images/ftv2plastnode.gif'></a></td>";
				}
				else
				{
					if (isset($this->oc[$row['oid']]))
					{
						$str.= "<td><img HSPACE='0' VSPACE='0' src='".$this->cfg["baseurl"]."/images/ftv2mnode.gif'></td>";
					}
					else
					{
						$str.= "<td><a href='".$this->mk_my_orb("show", array("parent" => $row['oid']))."'><img HSPACE='0' VSPACE='0' border='0' src='".$this->cfg["baseurl"]."/images/ftv2pnode.gif'></a></td>";
					}
				}
			}
			else
			{
				$str .= str_repeat("<td><img HSPACE='0' VSPACE='0' src='".$this->cfg["baseurl"]."/images/ftv2vertline.gif'></td>", max(0,$this->level-2));
				if ($cnt-1 == $num)
				{
					$str.= "<td><img HSPACE='0' VSPACE='0' src='".$this->cfg["baseurl"]."/images/ftv2lastnode.gif'></td>";
				}
				else
				{
					$str.= "<td><img HSPACE='0' VSPACE='0' src='".$this->cfg["baseurl"]."/images/ftv2node.gif'></td>";
				}
			}

			$ml[] = "<tr>".$str."<td colspan='".(10-$this->level)."'>".$ms."</td></tr>";

			// now check if this menu is in the oc for the active menu 
			// and if so, then recurse to the next level
			if (isset($this->oc[$row['oid']]))
			{
				$this->draw_req_menus($row['oid'], &$ml);
			}
			$num++;
		}
		$this->level--;
	}
}
?>
