<?php
class menuedit_light extends aw_template {
	function menuedit_light()
	{
		$this->db_init();
		$this->tpl_init();
	}

	////
	// !Genereerib mingit klassi objektide nimekirja, rekursiivselt alates $start_from-ist
	// Eeliseks järgneva funktsiooni ees on see, et ei loeta koiki menüüsid sisse

	// ja esimese taseme templateks votame $start_tpl-i
	function gen_rec_list($args = array())
	{
		extract($args);
		// vaatame ainult seda tüüpi objekte
		$this->class_id = ($args["class_id"]) ? $args["class_id"] : CL_PSEUDO;
		$this->type = $args["type"];
		//
		$this->field = ($args["field"]) ? $args["field"] : "oid";
		$this->sq = (isset($args["sq"])) ? $args["sq"] : 3;
		$this->tpl = ($args["tpl"]) ? $args["tpl"] : false;
		$this->spacer = ($args["spacer"]) ? $args["spacer"] : "&nbsp;";
		$this->threadby = ($args["threadby"]) ? $args["threadby"] : "parent";
		$this->func_gain_data = ($args["func_gain_data"]) ? $args["func_gain_data"] : "_gen_rec_list";
		$this->add_start_from = ($args["add_start_from"]) ? $args["add_start_from"] : false;
		// kui see on true, siis kasutatakse ainult ühte ja esimest templatet
		$this->single_tpl = ($args["single_tpl"]) ? $args["single_tpl"] : false;
		// moodustame 2mootmelise array koigist objektidest
		// parent -> child1,(child2,...childn)
		$this->object_list = array(); // siia satuvad koik need objektid
		// koigepealt genereerime menyyde nimekirja
		$this->_gen_rec_list(array("$start_from"));
		if ( (sizeof($this->object_list) == 0) && not($this->add_start_from) )
		{
			$retval = false;
		}
		else
		{
			if ($this->tpl)
			{
				$this->read_template($args["tpl"]);
				$this->res = "";
			}
			else
			{
				$this->res = array();
			};
			if ($this->add_start_from)
			{
				$_root = $this->get_object($start_from);
				// Override the default name
				#$_root["name"] = "Kodukataloog";
				$this->object_list[$_root[$this->threadby]][$start_from] = $_root;
			}
			reset($this->object_list);
			$this->level = 0;
			$this->_recurse_object_list(array(
				"parent" => $_root[$this->threadby],
			));
			if ($this->tpl)
			{
				$this->read_template($args["tpl"]);
				$this->vars(array(
					"content" => $this->res,
				));
				$retval = $this->parse();
			} 
			else
			{
				$retval = $this->res;
			};
		};
		return $retval;
	}

	////
	// !Rekursiivne funktsioon, kutsutakse välja gen_rec_list seest
	function _gen_rec_list($parents = array())
	{
		$this->save_handle();
		$plist = join(",",$parents);
		if ($this->type == "groups")
		{
			$q = sprintf("SELECT * FROM groups WHERE parent IN (%s)",
				$plist);
		}
		else
		{
			$q = sprintf("SELECT * FROM objects WHERE class_id = '%d' AND parent IN (%s) AND status != 0",
					$this->class_id,
					$plist);
		};
		$this->db_query($q);
		$_parents = array();
		while($row = $this->db_next())
		{
			$_parents[] = $row[$this->field];
			$this->object_list[$row[$this->threadby]][$row[$this->field]] = $row;
		};
		if (sizeof($_parents) > 0)
		{
			$this->_gen_rec_list($_parents);
		};
		$this->restore_handle();
	}
	
	/////
	// !Recurse and print object array
	function _recurse_object_list($args = array())
	{
		if ($args["parent"])
		{
			$parent = $args["parent"];
		}
		else
		{
			$parent = 0;
		};
		$slice = $this->object_list[$parent];
		if (is_array($slice) && (sizeof($slice) > 0))
		{
			while(list($k,$v) = each($slice))
			{
				$id = $v[$this->field];
				$spacer = str_repeat($this->spacer,$this->level * $this->sq);
				$name = $spacer . $v["name"];
				if ($this->tpl)
				{
					if ($this->single_tpl)
					{
						$tpl = $this->tlist[1][0];
					}
					else
					{
						$tpl = $this->tlist[$this->level + 1][0];
					};
					$this->vars(array(
						"oid" => $id,
						"name" => $v["name"],
						"spacer" => $spacer,
					));
					$this->res .= $this->parse($tpl);
				}
				else
				{
					$this->res[$id] = $name;
				};
				$this->level++;
				$this->_recurse_object_list(array(
					"parent" => $v[$this->field],
				));
				$this->level--;
			};
		}
		else
		{
			return;
		};
	}
};
?>
