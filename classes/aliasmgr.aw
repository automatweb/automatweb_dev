<?php
// aliasmgr.aw - Alias Manager
// $Header: /home/cvs/automatweb_dev/classes/Attic/aliasmgr.aw,v 2.48 2002/09/25 22:42:35 duke Exp $

// used to specify how get_oo_aliases should return the list
define("GET_ALIASES_BY_CLASS",1);
define("GET_ALIASES_FLAT",2);

class aliasmgr extends aw_template 
{
	function aliasmgr($args = array())
	{
		extract($args);
		$this->init("aliasmgr");

		$this->contents = "";
		$this->lc_load("aliasmgr","lc_aliasmgr");
	}

	////
	// !Allows to search for objects to include in the document
	// intended to replace pickobject.aw
	function search($args = array())
	{
		extract($args);
		$search = get_instance("search");
		return $res . $search->show(array(
			"docid" => $docid,
			"name" => $name,
			"comment" => $comment,
			"obj" => "aliasmgr",
		));
	}	

	////
	// !Callbacks for object search
	function _get_s_class_id($val)
	{
		$this->make_alias_classarr();
		asort($this->classarr);
		return $this->picker($val,$this->classarr);
	}

	function _get_s_header($args)
	{
		$this->read_template("search.tpl");
		$toolbar = get_instance("toolbar");
		$buttons = "";
		$buttons .= $toolbar->gen_button(array(
			"name" => "save",
			"tooltip" => "Tee valitud objektidele aliased",
			"url" => "javascript:aw_save()",
			"imgover" => "automatweb/images/blue/awicons/save_over.gif",
			"img" => "automatweb/images/blue/awicons/save.gif",
		));

		$this->vars(array(
			"buttons" => $buttons,
			"saveurl" => $this->mk_my_orb("addalias",array("id" => $args["docid"])),
		));

		return $this->parse();
	}

	function _gen_s_chlink($args)
	{
		//return "<a href='" . $this->mk_my_orb("addalias",array("id" => $args["docid"], "alias" => $args["oid"]),"aliasmgr") . "'>Võta see</a>";
		return "<input type='checkbox' name='check' value='$args[oid]'>";
		return "<a href='" . $this->mk_my_orb("addalias",array("id" => $args["docid"], "alias" => $args["oid"]),"aliasmgr") . "'>Võta see</a>";
	}

	function _gen_s_path($args)
	{
		return array(0,"<a href='".$this->mk_my_orb("list_aliases", array("id" => $args["docid"]))."'>Tagasi</a> / Otsi objekti");
	}
	
	////
	// !Submits the alias list
	function submit_list($args = array())
	{
		extract($args);
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "aliaslinks",
			"value" => $link,
			"overwrite" => 1,
		));
		$this->cache_oo_aliases($id);
		return $this->mk_my_orb("list_aliases",array("id" => $id));
	}
		
	////
	// !Gets all aliases for an object
	// params:
	//   oid - the object whose aliases we must return
	//   ret_type - if GET_ALIASES_BY_CLASS, return array is indexed by class and index, otherwise just index, default is GET_ALIASES_BY_CLASS
	//   filter - array(classref,funcref) - if defined, this is called for each returned record
	//   modifier(string) - allows to modify the sql clause to return only the data you need
	function get_oo_aliases($args = array())
	{
		extract($args);
		$oid = (int)$oid;
		$ret_type = ($ret_type) ? $ret_type : GET_ALIASES_BY_CLASS;

		$obj = $this->get_object($oid);
		// with this you can alter the sql clause to fetch only the data you are
		// actually going to use
		$modifier = ($modifier) ? $modifier : "aliases.*";
		$q = "SELECT $modifier, objects.class_id AS class_id, objects.name AS name
			FROM aliases
			LEFT JOIN objects ON (aliases.target = objects.oid)
			WHERE source = '$oid' ORDER BY aliases.id";
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			$row["aliaslink"] = $obj["meta"]["aliaslinks"][$row["target"]];
			if ($filter)
			{
				$row = &$filter[0]->$filter[1]($row);
				if (is_array($row))
				{
					$retval = $retval + $row;
				};
			}
			elseif ($ret_type == GET_ALIASES_BY_CLASS)
			{
				$retval[$row["class_id"]][] = $row;
			}
			else
			{
				$retval[] = $row;
			};
		};
		return $retval;
	}

	////
	// !Parses all embedded objects inside another document
	// arguments:
	// oid(int) - document id
	// source - document content
	// args[meta][aliases] - optional, if set, result of get_oo_aliases for object $oid
	function parse_oo_aliases($oid,&$source,$args = array())
	{
		extract($args);
		if (is_array($meta["aliases"]))
		{
			$aliases = $meta["aliases"];
		}
		else
		{
			$aliases = $this->get_oo_aliases(array("oid" => $oid));
			// write the aliases into metainfo for faster access later on
			if (is_array($aliases))
			{
				$this->set_object_metadata(array(
					"oid" => $oid,
					"key" => "aliases",
					"value" => $aliases,
				));
			};
		};

		$by_alias = array();
		foreach($this->cfg["classes"] as $clid => $cldat)
		{
			$li = explode(",", $cldat["alias"]);
			foreach($li as $lv)
			{
				$by_alias[$lv]["file"] = $cldat["alias_class"] != "" ? $cldat["alias_class"] : $cldat["file"];
				$by_alias[$lv]["class_id"] = $clid;
			}
		}

		preg_match_all("/(#)(\w+?)(\d+?)(v|k|p|)(#)/i",$source,$matches,PREG_SET_ORDER);

		if (is_array($matches))
		{
			// we gather all aliases in here, grouped by class so we gan give them to parse_alias_list()
			$toreplace = array();
			foreach ($matches as $key => $val)
			{
				$clid = $by_alias[$val[2]]["class_id"];
				$idx = $val[3] - 1;
				$target = $aliases[$clid][$idx]["target"];

				$toreplace[$clid][$val[0]] = $aliases[$clid][$idx];
				$toreplace[$clid][$val[0]]["val"] = $val;
			}

			// here we do the actual parse/replace bit
			foreach($toreplace as $clid => $claliases)
			{
				$emb_obj_name = "emb" . $clid;
				$cldat = $this->cfg["classes"][$clid];
				$class_name = $cldat["alias_class"] != "" ? $cldat["alias_class"] : $cldat["file"];

				if ($class_name)
				{
					// load and create the class needed for that alias type
					$$emb_obj_name = get_instance($class_name);
				}

				if (method_exists($$emb_obj_name, "parse_alias_list"))
				{
					// if the class supports alias list parsing, do it
					$repl = $$emb_obj_name->parse_alias_list(array(
						"oid" => $oid,
						"aliases" => $claliases,
						"tpls" => &$args["templates"],
					));
					if (is_array($repl))
					{
						foreach($repl as $aname => $avalue)
						{
							$inplace = false;
							if (is_array($avalue))
							{
								$replacement = $avalue["replacement"];
								$inplace = $avalue["inplace"];
							}
							else
							{
								$replacement = $avalue;
							}

							if ($inplace)
							{
								$this->tmp_vars = array($inplace => $replacement);
								$replacement = "";
							};
								
							$source = str_replace($aname,$replacement,$source);
						}
					}
				}
				else
				{
					// if not, then parse all the aliases one by one
					foreach($claliases as $avalue => $adata)
					{
						// if nothing comes up, we just replace it with a empty string
						$replacement = "";

						if (method_exists($$emb_obj_name,"parse_alias"))
						{
							$repl = $$emb_obj_name->parse_alias(array(
								"oid" => $oid,
								"matches" => $adata["val"],
								"alias" => $adata,
								"tpls" => &$args["templates"],
							));
					
							$inplace = false;
							if (is_array($repl))
							{
								$replacement = $repl["replacement"];
								$inplace = $repl["inplace"];
							}
							else
							{
								$replacement = $repl;
							}

							if ($inplace)
							{
								$this->tmp_vars = array($inplace => $replacement);
								$replacement = "";
							};
								
							$source = str_replace($avalue,$replacement,$source);
						}
					}
				}
			}
		};
	}

	////
	// Returns the variables createad by parse_oo_alias
	function get_vars()
	{
		return (is_array($this->tmp_vars)) ? $this->tmp_vars : array();
	}

	function _init_la_tbl()
	{
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "images",
		));
		$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
		$this->t->define_field(array(
			"name" => "icon",
			"caption" => "",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
			//"sortable" => 1,
    ));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			//"nowrap" => "1",
			"sortable" => 1,
    ));
		$this->t->define_field(array(
			"name" => "comment",
			"caption" => "Muu info",
			"talign" => "center",
			//"nowrap" => "1",
			"sortable" => 1,
    ));
		$this->t->define_field(array(
			"name" => "alias",
			"caption" => "Alias",
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
			//"nowrap" => "1",
    ));
		$this->t->define_field(array(
			"name" => "link",
			"caption" => "Link",
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
			"nowrap" => "1",
    ));
		$this->t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"align" => "center",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
    ));
		$this->t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.y / H:i"
    ));
		$this->t->define_field(array(
			"name" => "title",
			"caption" => "Tüüp",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
    ));
		$this->t->define_field(array(
			"caption" => "<a href='javascript:void(0)' onClick='selall()'>Vali</a>",
			"name" => "check",
			"width" => 20,
			"align" => "center",
		));
	}

	////
	// !the new alias lister
	// params:
	//   id - the object whose aliases we will observe
	function new_list_aliases($args)
	{
		extract($args);
		$this->read_template("lists_new.tpl");
		$obj = $this->get_object($id);

		// init vcl table to $this->t and define columns
		$this->_init_la_tbl();

		// creates $this->typearr
		$this->make_alias_typearr();
		// creates $this->aliasarr
		$this->make_alias_classarr();

		// this will be an array of class => name pairs for all object types that can be embedded
		$aliases = array();
		$types = array();
		$classes = $this->cfg["classes"];
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				if ($cldat["alias_class"])
				{
					$cldat["file"] = $cldat["alias_class"];
					$classes[$clid]["file"] = $cldat["alias_class"];
				}
				$aliases[$cldat["file"]] = $cldat["name"];
				$types[] = $clid;
			}
		}

		$return_url = urlencode($this->mk_my_orb("list_aliases", array("id" => $id)));

		// fetch a list of all the aliases for this object
		$alist = $this->get_aliases(array(
			"oid" => $id,
			"type" => $types
		));
		
		$chlinks = array();
		foreach($alist as $alias)
		{
			$aclid = $alias["class_id"];
			list($astr) = explode(",",$classes[$aclid]["alias"]);
			$astr = sprintf("#%s%d#",$astr,++$this->acounter[$aclid]);
			$ch = $this->mk_my_orb("change", array("id" => $alias["target"], "return_url" => $return_url),$classes[$aclid]["file"]);
			$chlinks[$alias["target"]] = $ch;

			$alias["icon"] = sprintf("<img src='%s'>",get_icon_url($aclid,""));
			$alias["alias"] = sprintf("<input type='text' size='5' value='%s' onClick='this.select()' onBlur='this.value=\"%s\"'>",$astr,$astr);
			$alias["link"] = sprintf("<input type='checkbox' name='link[%d]' value='1' %s>",$alias["target"],checked($obj["meta"]["aliaslinks"][$alias["target"]]));
			$alias["title"] = $classes[$aclid]["name"];
			$alias["check"] = sprintf("<input type='checkbox' name='check' value='%d'>",$alias["target"]);
			$alias["name"] = sprintf("<a href='%s'>%s</a>",$ch,($alias["name"] == "" ? "(no name)" : $alias["name"]));

			$this->t->define_data($alias);
		}

		$this->t->set_default_sortby("title");
		$this->t->sort_by();
		$this->vars(array(
			"table" => $this->t->draw(),
			"id" => $id,
			"parent" => $obj["parent"],
			"return_url" => $return_url,
			"aliases" => $this->picker("",$aliases),
			"reforb" => $this->mk_reforb("submit_list",array("id" => $id)),
			"chlinks" => join("\n",map2("chlinks[%s] = \"%s\";",$chlinks)),
		));
			
		return $this->parse();
	}

	////
	// !deletes aliases (separated by ;'s) 
	// params:
	//   id - list of aliases
	//   oid - the object where to delete aliases from
	function del_alias($arr)
	{
		extract($arr);
		$ids = explode(";",$id);
		foreach($ids as $real_id)
		{
			$this->delete_alias($oid,$real_id);
		};
		header("Location: ".$this->mk_my_orb("list_aliases", array("id" => $oid),"aliasmgr"));
	}

	////
	// !adds the specified alias to the object
	// parameters
	//   id - the object to which the alias is added
	//   alias - id of the object to add as alias
	function addalias($arr)
	{
		extract($arr);
		$aliases = explode(",",$alias);
		foreach($aliases as $onealias)
		{
			$_al = (int)$onealias;
			if ($_al > 0)
			{
				$al = $this->get_object($_al);
			}
			// let the correct class override the alias adding if it wants to
			// if the class does not handle it, it falls back on core::addalias
			$cl = $this->cfg["classes"][$al["class_id"]]["file"];
			if ($cl != "")
			{
				$inst = get_instance($cl);
				$inst->addalias($arr);
			}
			else
			{
				$this->add_alias($id,$alias);
			}
		};
		header("Location: ".$this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"));
	}

	////
	// !puts all alias classes into $this->typearr
	function make_alias_typearr()
	{
		$this->typearr = array();

		$classes = $this->cfg["classes"];
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				$this->typearr[] = $clid;
			}
		}
	}

	function make_alias_classarr()
	{
		$this->classarr = array();

		$classes = $this->cfg["classes"];
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				$this->classarr[$clid] = $cldat["name"];
			}
		}
	}

	////
	// !returns an array of alias id => alias name (#blah666#) for object $oid
	function get_alias_list_for_obj_as_aliasnames($oid)
	{
		$cnts = array();
		$ret = array();

		$aliases = $this->get_aliases_for($oid);
		foreach($aliases as $ad)
		{
			list($astr) = explode(",",$this->cfg["classes"][$ad["class_id"]]["alias"]);
			$ret[$ad["id"]] = "#".$astr.(++$cnts[$ad["class_id"]])."#";
		}
		return $ret;
	}

	////
	// !updates the alias list cache for object $oid
	function cache_oo_aliases($oid)
	{
		$_aliases = $this->get_oo_aliases(array("oid" => $oid));

		// paneme aliases kirja
    if (is_array($_aliases))
		{
			$this->set_object_metadata(array(
				"oid" => $oid,
				"key" => "aliases",
				"value" => $_aliases,
			));
    };
	}
}
?>
