<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/object_chain.aw,v 2.5 2002/06/13 23:03:49 kristo Exp $
// object_chain.aw - Objektipärjad

classload("objects");

class object_chain extends aw_template
{
	function object_chain()
	{
		$this->init("object_chain");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa objektipärg");
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
		
		// kui tegemist on aliaste ja kui see siin on objektipärg, siis
		// loeme koigepealt sisse olemasolevad aliased ning _kustutame_ need
		if ( ($par_obj["class_id"] == CL_DOCUMENT) || ($par_obj["class_id"] == CL_TABLE))                        
		{
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

		return $this->mk_my_orb("change", array("id" => $id,"search" => $search,"s_name" => $s_name,"s_comment" => $s_comment,"s_type" => $s_type,"s_id_from" => $s_id_from, "s_id_to" => $s_id_to,"return_url" => urlencode($return_url)));
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
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa objektipärg");
		}
		else
		{
			$this->mk_path($o["parent"], "Muuda objektip&auml;rga");
		};

		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$tar = array(0 => "K&otilde;ik");
		$class_defs = $this->cfg["classes"];
		foreach($class_defs as $clid => $cldata)
		{
			$tar[$clid] = $cldata["name"];
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id,"return_url" => urlencode($return_url))),
			"s_name" => $s_name,
			"s_comment" => $s_comment,
			"s_id_from" => $s_id_from,
			"s_id_to" => $s_id_to,
			"types" => $this->multiple_option_list($this->make_keys($s_type),$tar)
		));

		$ob = new objects;
		$ol = $ob->get_list();

		$toar = array_values($meta["objs"]);
		if ($search && ($s_name != "%" || $s_comment != "%" || $s_type || $s_id_from != "" || $s_id_to != ""))
		{
			if (is_array($s_type))
			{
				$st = " AND class_id IN (".join(",",$s_type).")";
			}
			if ($s_id_from != "")
			{
				$sidf = " AND oid >= '$s_id_from' ";
			}
			if ($s_id_to != "")
			{
				$sidt = " AND oid <= '$s_id_to' ";
			}
			$q = "SELECT oid FROM objects WHERE name LIKE '%".$s_name."%' AND (comment LIKE '%".$s_comment."%') $st $sidf $sidt AND status != 0 AND lang_id = ".aw_global_get("lang_id")." AND site_id = ".aw_ini_get("site_id");
			$this->db_query($q);
//			echo "q = $q <br>";
			while ($row = $this->db_next())
			{
				if (!in_array($row["oid"], $toar))
				{
					$toar[] = $row["oid"];
				}
			}
		}

		$qar = array();
		$str = join(",",$this->map("%s",$toar));
		if ($str != "")
		{
			$q = "SELECT oid,name,parent,class_id FROM objects WHERE oid IN($str)";
			$this->db_query($q);

			while ($row = $this->db_next())
			{
				$qar[$row["oid"]] = $row;
			}
		}

		foreach($toar as $oid)
		{
			$row = $qar[$oid];
			$this->vars(array(
				"name" => $row["name"],
				"oid" => $row["oid"],
				"place" => $ol[$row["parent"]],
				"type" => $tar[$row["class_id"]],
				"sel" => checked($meta["objs"][$row["oid"]])
			));
			$fo.=$this->parse("S_RESULT");
		}
		$this->vars(array(
			"S_RESULT" => $fo,
		));

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
