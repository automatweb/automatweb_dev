<?php

class galleries extends aw_template
{
	function galleries()
	{
		$this->tpl_init("gallery");
		$this->db_init();
	}

	function gen_list()
	{
		$this->read_template("list.tpl");
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_GALLERY." AND status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array("id" => $row[oid], "name" => $row[name], "comment" => $row[comment]));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l));
		return $this->parse();
	}

	function add($parent,$alias_doc)
	{
		$this->read_template("add.tpl");
		$this->vars(array("id" => 0,"name" => "", "comment" => "","parent" => $parent,"alias_doc" => $alias_doc));
		return $this->parse();
	}

	function change($id)
	{
		$this->db_query("SELECT * FROM objects WHERE oid = $id");
		if (!($row = $this->db_next()))
			$this->raise_error("no such gallery($id)!", true);

		$this->read_template("add.tpl");
		$this->vars(array("id" => $id, "name" => $row[name], "comment" => $row[comment]));
		return $this->parse();
	}

	function submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$parent = $parent ? $parent : $GLOBALS["rootmenu"];
			$id = $this->new_object(array("parent" => $parent,"name" => $name, "comment" => $comment, "class_id" => CL_GALLERY));
			$this->db_query("INSERT INTO galleries VALUES($id,'')");
			if ($alias_doc)
			{
				$this->add_alias($alias_doc, $id);
			}
		}
		return $id;
	}

	function delete($id)
	{
		$this->delete_object($id);
	}
}
?>