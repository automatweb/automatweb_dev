<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/object_chain.aw,v 2.2 2002/01/03 18:29:13 duke Exp $
// object_chain.aw - Objektip�rjad

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
		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa objektip�rg");
		}
		else
		{
			$this->mk_path($parent,"Lisa objektip&auml;rg");
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent,"return_url" => $return_url))
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
		
			$_tmp = $this->get_object($id);
			$par_obj = $this->get_object($_tmp["parent"]);
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_OBJECT_CHAIN
			));
			$par_obj = $this->get_object($parent);
			if ( ($par_obj["class_id"] == CL_DOCUMENT) || ($par_obj["class_id"] == CL_TABLE))
			{
				$this->add_alias($parent,$id);
			};
		}

			#$old_contents = $this->get_objects_in_chain($id);
			#print "<pre>";
			#print $par_obj["oid"];
			#print_r($old_contents);
			#print "</pre>";

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
		
		// kui tegemist on aliaste ja kui see siin on objektip�rg, siis
		// loeme koigepealt sisse olemasolevad aliased ning _kustutame_ need
		if ( ($par_obj["class_id"] == CL_DOCUMENT) || ($par_obj["class_id"] == CL_TABLE))                        {
			$old_contents = $this->get_objects_in_chain($id);
			if (is_array($old_contents))
			{
				foreach($old_contents as $value)
				{
					$this->delete_alias($par_obj["oid"],$value);
				}
			};
			$this->expl_chain(array("id" => $id,"parent" => $par_obj["oid"],"objects" => $arr));
		};

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "objs",
			"value" => $arr
		));

		return $this->mk_my_orb("change", array("id" => $id,"search" => $search,"s_name" => $s_name,"s_comment" => $s_comment,"s_type" => $s_type,"return_url" => urlencode($return_url)));
	}

	//// explodes the added object into single aliases
	function expl_chain($args = array())
	{
		extract($args);
		if (not($objects))
		{
			$objects = $this->get_objects_in_chain($id);
		};
		
		if (is_array($objects))
		{
			foreach($objects as $value)
			{
				print "$parent - $value<br>";
				$this->add_alias($parent,$value);
			};
		};

	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$o = $this->get_object($id);
	
		if ($return_url)
		{
			$return_url = urldecode($return_url);
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa objektip�rg");
		}
		else
		{
			$this->mk_path($o["parent"], "Muuda objektip&auml;rga");
		};

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
			"reforb" => $this->mk_reforb("submit", array("id" => $id,"return_url" => urlencode($return_url))),
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
