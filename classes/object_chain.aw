<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/object_chain.aw,v 2.21 2005/03/20 16:46:11 kristo Exp $
// object_chain.aw - Objektipärjad

/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_OBJECT_CHAIN, on_add_alias)

*/

class object_chain extends aw_template
{
	function object_chain()
	{
		$this->init("object_chain");
	}

	/**
		@attrib name=new

		@param parent required
		@param return_url optional

	**/
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

	/**
		@attrib name=submit
	**/
	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$o = obj($id);
			$o->set_name($name);

			$par_obj = obj($o->parent());
		}
		else
		{
			$o = obj();
			$o->set_parent($parent);
			$o->set_name($name);
			$o->set_class_id(CL_OBJECT_CHAIN);
			$id = $o->save();

			$par_obj = obj($parent);
			if ( ($par_obj->class_id() == CL_DOCUMENT) || ($par_obj->class_id() == CL_TABLE))
			{
				$par_obj->connect(array(
					"to" => $id
				));
			};
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

		// kui tegemist on aliaste ja kui see siin on objektipärg, siis
		// loeme koigepealt sisse olemasolevad aliased ning _kustutame_ need
		if ( ($par_obj->class_id() == CL_DOCUMENT) || ($par_obj->class_id() == CL_TABLE))
		{
			$old_contents = $this->get_objects_in_chain($id);
			if (is_array($old_contents))
			{
				foreach($old_contents as $value)
				{
					$par_obj->disconnect(array(
						"from" => $value
					));
				}
			};
			$this->expl_chain(array("id" => $id,"parent" => $par_obj->id(),"objects" => $arr));
		};

		$o->set_meta("objs", $arr);
		$o->save();

		return $this->mk_my_orb("change", array(
			"id" => $id,
			"search" => $search,
			"s_name" => urlencode($s_name),
			"s_comment" => urlencode($s_comment),
			"s_type" => $s_type,
			"s_id_from" => $s_id_from, 
			"s_id_to" => $s_id_to, 
			"s_parent" => $s_parent, 
			"return_url" => urlencode($return_url)
		));
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
				$o = obj($parent);
				$o->connect(array(
					"to" => $value
				));
			};
		};

	}

	/**
		@attrib name=change

		@param id required
		@param return_url optional

	**/
		function change($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$o = obj($id);

		if ($return_url)
		{
			$return_url = urldecode($return_url);
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa objektipärg");
		}
		else
		{
			$this->mk_path($o->parent(), "Muuda objektip&auml;rga");
		};

		$meta = $o->meta();

		$tar = array(0 => "K&otilde;ik");
		$class_defs = aw_ini_get("classes");
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
			"s_parent" => $this->picker($s_parent, $this->get_menu_list(false, true)),
			"types" => $this->multiple_option_list($this->make_keys($s_type),$tar)
		));

		$ol = $this->get_menu_list(false,false,1);

		$toar = array_values($meta["objs"]);
		if ($search && ($s_name != "%" || $s_comment != "%" || $s_type || $s_id_from != "" || $s_id_to != "" || $s_parent != ""))
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
			if ($s_parent)
			{
				$ml = $this->get_menu_list(false, false, $s_parent) + array($s_parent => "");
				$sidp = " AND parent IN (".join(",",array_keys($ml)).") ";
			}
			
			$s_names = explode("&&", $s_name);
			$sst = join(" OR ", map("name LIKE '%%%s%%'", $s_names));
	
			if (!($s_comment == "%" || $s_comment == ""))
			{
				$sic = " (comment LIKE '%".$s_comment."%') ";
			}

			if ($sst != "")
			{
				$sst = "(".$sst.") ".($sic != "" ? " AND " : "");
			}

			$q = "SELECT oid FROM objects WHERE $sst $sic $st $sidf $sidt $sidp AND status != 0 AND lang_id = ".aw_global_get("lang_id")." AND site_id = ".aw_ini_get("site_id");
			$this->db_query($q);
//			echo "q = $q <br />";
			while ($row = $this->db_next())
			{
				if (!in_array($row["oid"], $toar) && $this->can("view", $row["oid"]))
				{
					$toar[] = $row["oid"];
				}
			}
		}

		$qar = array();
		$str = join(",",map("%s",$toar));
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
			"name" => $o->name(),
			"SEARCH" => $this->parse("SEARCH"),
			"OBJECT" => $os
		));

		return $this->parse();
	}

	function get_objects_in_chain($id)
	{
		$o = obj($id);
		return $o->meta("objs");
	}

	////
	// !adding alias to document support
	function on_add_alias($arr)
	{
		$this->expl_chain(array(
			"id" => $arr["connection"]->prop("to"),
			"parent" => $arr["connection"]->prop("from")
		));
	}

	////
	// !Adds a new object group
	function add_group($args = array())
	{
//		print "<pre>";
//		print_r($args);
//		print "</pre>";


	}
}
?>
