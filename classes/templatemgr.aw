<?php

class templatemgr extends aw_template
{
	function templatemgr()
	{
		$this->init("templatemgr");
	}

	////
	// !Retrieves a list of templates
	// type(int) - the type of templtes to list
	// caption - the string for the default entry, defaults to "default"
	// menu - if set, default template is read from that menu
	// def - default template filename
	function get_template_list($args = array())
	{
		if (!isset($args["caption"]))
		{
			$args["caption"] = "default";
		}

		// kysime infot adminnitemplatede kohta
		$type = (int)$args["type"];
		if ($args["menu"])
		{
			// find the template for that type for the menu
			if ($type == 0)
			{
				$d = get_instance("document");
				$def = $d->get_edit_template($args["menu"]);
			}
			else
			if ($type == 1)
			{
				$def = $this->get_lead_template($args["menu"]);
			}
			else
			if ($type == 2)
			{
				$def = $this->get_long_template($args["menu"]);
			}
		}
		$q = "SELECT * FROM template WHERE type = $type ORDER BY id";
		$this->db_query($q);
		$result = array("0" => $args["caption"]);
		while($tpl = $this->db_fetch_row())
		{
			$result[$tpl["id"]] = $tpl["name"];
			if ($tpl["filename"] == $def)
			{
				$def_n = $tpl["name"];
			}
		};
		if ($def_n != "")
		{
			$result["0"] = "Vaikimisi: ".$def_n;
		}
		return $result;
	}

	function get_template_file_by_id($args = array())
	{
		$id = (int)$args["id"];
		if (!($ret = aw_cache_get("templatemgr::get_template_file_by_id", $id)))
		{
			$ret = $this->db_fetch_field("SELECT filename FROM template WHERE id = '$id'", "filename");
			aw_cache_set("templatemgr::get_template_file_by_id", $id, $ret);
		}
		return $ret;
	}

	////
	// !returns a list of all template folders that are for this site
	// return value is array, key is complete template folder path and value is the path, starting from the site basefolder
	function get_template_folder_list($arr)
	{
		extract($arr);
		$this->tplfolder_list = array(
			$this->cfg["tpldir"] => $this->cfg["tpldir"]//str_replace($this->cfg["site_basedir"],$this->cfg["stitle"], $this->cfg["tpldir"])
		);
		$this->_req_tplfolders($this->cfg["tpldir"]);
		return $this->tplfolder_list;
	}

	function _req_tplfolders($fld)
	{
		$cnt = 0;
		if ($dir = @opendir($fld)) 
		{
			while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					$cf = $fld."/".$file;
					if (is_dir($cf))
					{
						$cnt++;
						$this->_req_tplfolders($cf);
						$this->tplfolder_list[$cf] = $cf;//str_replace($this->cfg["site_basedir"],$this->cfg["stitle"], $cf);
					}
				}
			}  
			closedir($dir);
		}
		return $cnt;
	}
}
?>
