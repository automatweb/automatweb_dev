<?php

class templatemgr extends aw_template
{
	var $types = array("0" => "Muutmine", "1" => "Lead", "2" => "Vaatamine");

	function templatemgr()
	{
		$this->init("templatemgr");
	}

	function orb_list($arr)
	{
		$this->read_template("list.tpl");

		$this->db_query("SELECT * FROM template");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"type" => $this->types[$row["type"]],
				"name" => $row["name"],
				"filename" => $row["filename"],
				"change" => $this->mk_my_orb("change", array("id" => $row["id"])),
				"delete" => $this->mk_my_orb("delete", array("id" => $row["id"]))
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"add" => $this->mk_my_orb("new")
		));
		return $this->parse();
	}

	function add($arr)
	{
		$this->read_template("add.tpl");
		$this->mk_path(0,"<a href='".$this->mk_my_orb("list")."'>Templated</a> / Lisa");

		$this->vars(array(
			"type" => $this->picker(0, $this->types),
			"reforb" => $this->mk_reforb("submit")
		));

		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->db_query("UPDATE template SET name='$name', type='$type', filename='$template' WHERE id = '$id'");
		}
		else
		{
			$this->db_query("INSERT INTO template(name,type,filename,site_id) VALUES('$name','$type','$template','".aw_ini_get("site_id")."')");
			$id = $this->db_fetch_field("SELECT MAX(id) AS id FROM template","id");
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->db_query("SELECT * FROM template WHERE id = '$id'");
		$row = $this->db_next();

		$this->read_template("add.tpl");
		$this->mk_path(0,"<a href='".$this->mk_my_orb("list")."'>Templated</a> / Muuda");

		$this->vars(array(
			"name" => $row["name"],
			"type" => $this->picker($row["type"], $this->types),
			"template" => $row["filename"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));

		return $this->parse();
	}

	////
	// !Retrieves a list of templates
	// type(int) 
	function get_template_list($args = array())
	{
		// kysime infot adminnitemplatede kohta
		$type = (int)$args["type"];
		$q = "SELECT * FROM template WHERE type = $type ORDER BY id";
		$this->db_query($q);
		$result = array("0" => "default");
		while($tpl = $this->db_fetch_row())
		{
			$result[$tpl["id"]] = $tpl["name"];
		};
		return $result;
	}

	function get_template_file_by_id($args = array())
	{
		$id = (int)$args["id"];
		$row = $this->get_record("template","id",$id,array("filename"));
		return $row["filename"];
	}

	////
	// !returns a list of all template folders that are for this site
	// return value is array, key is complete template folder path and value is the path, starting from the site basefolder
	function get_template_folder_list($arr)
	{
		extract($arr);
		$this->tplfolder_list = array(
			$this->cfg["tpldir"] => str_replace($this->cfg["site_basedir"],$this->cfg["stitle"], $this->cfg["tpldir"])
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
						$this->tplfolder_list[$cf] = str_replace($this->cfg["site_basedir"],$this->cfg["stitle"], $cf);
					}
				}
			}  
			closedir($dir);
		}
		return $cnt;
	}
}
?>
