<?php

classload("objects");

class object_chain extends aw_template
{
	function object_chain()
	{
		$this->tpl_init("object_chain");
		$this->db_init();
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent,"Lisa objektip&auml;rg");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_OBJECT_CHAIN
			));
		}

		$arr = array();
		if (is_array($objs))
		{
			foreach($objs as $oid => $one)
			{
				if ($one == 1)
				{
					$arr[$oid] = $oid;
				}
			}
		}

		if (is_array($sel))
		{
			foreach($sel as $oid => $val)
			{
				if ($val == 1)
				{
					$arr[$oid] = $oid;
				}
			}
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "objs",
			"value" => $arr
		));

		return $this->mk_my_orb("change", array("id" => $id,"search" => $search,"s_name" => $s_name,"s_comment" => $s_comment,"s_type" => $s_type));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$o = $this->get_object($id);
	
		$this->mk_path($o["parent"], "Muuda objektip&auml;rga");

		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$tar = array(0 => "K&otilde;ik");
		global $class_defs;
		foreach($class_defs as $clid => $cldata)
		{
			$tar[$clid] = $cldata["name"];
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"s_name" => $s_name,
			"s_comment" => $s_comment,
			"types" => $this->multiple_option_list($this->make_keys($s_type),$tar)
		));

		if ($search && ($s_name != "%" || $s_comment != "%" || $s_type))
		{
			$ob = new objects;
			$ol = $ob->get_list();
			if (is_array($s_type))
			{
				$st = " AND class_id IN (".join(",",$s_type).")";
			}
			$q = "SELECT oid,parent,class_id,name FROM objects WHERE name LIKE '%".$s_name."%' AND (comment LIKE '%".$s_comment."%' OR comment IS NULL) $st";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"],
					"oid" => $row["oid"],
					"place" => $ol[$row["parent"]],
					"type" => $tar[$row["class_id"]],
					"sel" => checked($meta["objs"][$oid])
				));
				$fo.=$this->parse("S_RESULT");
			}
			$this->vars(array(
				"S_RESULT" => $fo,
			));
		}

		$names = array();
		$str = join(",",$this->map("%s",$meta["objs"]));
		if ($str != "")
		{
			$this->db_query("SELECT oid,name,class_id FROM objects WHERE oid IN (".$str.")");
			while ($row = $this->db_next())
			{
				$names[$row["oid"]] = $row["name"];
				$this->vars(array(
					"change" => $this->mk_my_orb("change", array("id" => $row["oid"]),$class_defs[$row["class_id"]]["file"]),
					"name" => $row["name"],
					"oid" => $row["oid"]
				));
				$os.=$this->parse("OBJECT");
			}
		}

		$this->vars(array(
			"name" => $o["name"],
			"SEARCH" => $this->parse("SEARCH"),
			"OBJECT" => $os
		));

		return $this->parse();
	}

	function get_objects_in_chain($id)
	{
		$o = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));
		return $meta["objs"];
	}
}
?>