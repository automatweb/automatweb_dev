<?php
// aliasmgr.aw - Alias Manager
// $Header: /home/cvs/automatweb_dev/classes/Attic/aliasmgr.aw,v 2.115 2003/07/10 12:03:45 duke Exp $

// used to specify how get_oo_aliases should return the list
define("GET_ALIASES_BY_CLASS",1);
define("GET_ALIASES_FLAT",2);

class aliasmgr extends aw_template
{
	function aliasmgr($args = array())
	{
//		arr($this->cfg,1);
		extract($args);
		$this->use_class = isset($args["use_class"]) ? $args["use_class"] : get_class($this);
		$this->init("aliasmgr");
		$this->contents = "";
		$this->lc_load("aliasmgr","lc_aliasmgr");

		// we need a better way to do a version upgrade or smth.
		$this->do_check_tables();
	}

	////
	// !Allows to search for objects to include in the document
	// intended to replace pickobject.aw
	function search($args = array())
	{
		extract($args);

		$this->reltype = isset($args['s']['reltype']) ? $args['s']['reltype']: $reltype;
//		$this->reltype = isset($args['objtype']) ? $args['objtype']: NULL;

		$GLOBALS['site_title'] = "Seostehaldur";
		$this->read_template("search.tpl");
		$search = get_instance("search");

		$reltypes[0] = "alias";
		$reltypes = new aw_array($reltypes);
		$this->reltypes = $reltypes->get();

		$this->make_alias_classarr();

		if (is_array($rel_type_classes))
		{
			foreach ($rel_type_classes as $key => $val)
			{
				$this->rel_type_classes[$key] = $this->make_alias_classarr2($val);
			}
		}

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
				$clids .= 'clids['.$clid.'] = "'.basename($cldat["file"]).'";'."\n";
			}
		}

		$args["clid"] = &$this;
		$form = $search->show($args);
		$this->search = &$search;

		$id = ($args["id"]) ? $args["id"] : $args["docid"];
		$this->id = $id;
		$obj = $this->get_object($id);


		if (is_object($this->ref))
		{
			$this->ref = $ref;
		};

		$return_url = $this->mk_my_orb("list_aliases", array("id" => $id),$this->use_class);
		$return_url = urlencode($return_url);

		$this->vars(array(
			'class_ids' => $clids,
			"parent" => $obj["parent"],
			"period" => $period,
			"id" => $id,
			"return_url" => $return_url,
			"reforb" => $this->mk_reforb("search_aliases",array(
				"no_reforb" => 1,
				"search" => 1,
				"id" => $id,
				"reltype" => $reltype,
				"return_url" => $return_url,
			),$this->use_class),
			"saveurl" => $this->mk_my_orb("addalias",array("id" => $id,"reltype" => $reltype),$this->use_class),
			"toolbar" => $this->mk_toolbar($args['s']['class_id'], $args['objtype']),
			"form" => $form,
			"table" => $search->get_results(),
		));
		//$results = $search->get_results();
		return $this->parse();
	}

	function search_callback_get_fields(&$fields,$args)
	{
		if (isset($args['complex']))
		{
			aw_session_set('complex',"1");
		}
		if (isset($args['simple']))
		{
			aw_session_del('complex');
		}

		$fields = array();
		$this->make_alias_classarr($this->clid_list);
		asort($this->classarr);
		$options = (sizeof($this->classarr) == 1) ? $this->classarr : array(""=>"") + $this->classarr;
		$fields["special"] = "n/a";

		$request = str_replace('&complex=1','',aw_global_get("REQUEST_URI"));
		$request = str_replace('&simple=1','',$request);

		if (aw_global_get('complex') == '1')
		{
			$fields["complexity"] = array(
				"type" => "text",
				"caption" => "",
				'value' => html::href(array(
					'caption' =>'lihtsam otsing',
					'url' => $request.'&simple=1',
				)),
			);

			$fields["class_id"] = array(
				"type" => "class_id_multiple",
				"caption" => "Klass",
				"size" => "8",
				"options" => (isset($this->rel_type_classes[$this->reltype]) && is_array($this->rel_type_classes[$this->reltype])) ? $this->rel_type_classes[$this->reltype] : $options,
				"selected" => $args["s"]["class_id"],
				"filter" => "1",
			);
			//$fields["class_id"] = 'n/a';
			$fields["reltype"] = array(
				'type' => 'hidden',
				'value' => $args['reltype'],
			);
		}
		else
		{
			$fields["reltype"] = array(
				'type' => 'hidden',
				'value' => $args['reltype'],
			);
			$fields["server"] = "n/a";
			$fields["location"] = "n/a";
			$fields["alias"] = "n/a";
			$fields["period"] = "n/a";
			$fields["site_id"] = "n/a";
			
			$fields["complexity"] = array(
				"type" => "text",
				"caption" => "",
				'value' => html::href(array(
					'caption' =>'täpsem otsing',
					'url' => $request.'&complex=1',
				)),
			);
			$fields["class_id"] = array(
				"type" => "class_id_hidden",
				'value' => '0',
			);
		}



	}

	function search_callback_modify_data($row,$args)
	{
		if (method_exists($this,'search_callback_popup_get'))
		{
			$this->search_callback_popup_get(&$row,$args);
		}
		else
		{
			$row["change"] = "<input type='checkbox' name='check' value='$row[oid]'>";
		}
	}

	////
	// !Submits the alias list
	function submit_list($args = array())
	{
		extract($args);
		if ($subaction == "delete")
		{
			$to_delete = new aw_array($check);
			foreach($to_delete->get() as $alias_id)
			{
				$this->delete_alias($id,$alias_id);
				unset($link[$alias_id]);
			};
		};
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "aliaslinks",
			"value" => $link,
			"overwrite" => 1,
		));


		$cache_inst = get_instance("cache");
		$alist = $this->get_aliases_for($id);

		/*
			$aclid = $alias["class_id"];
			list($astr) = explode(",",$classes[$aclid]["alias"]);
			// oh no. this is BAD. if I have a document with a lot of images
			// for example and delete one of those - ALL other images
			// will shift. I mean, fuck
			$astr = sprintf("#%s%d#",$astr,++$this->acounter[$aclid]);


		*/

		foreach($alist as $ad)
		{
			if ($ad['cached'] != $cache[$ad['target']])
			{
				if (!$cache[$ad['target']])
				{
					$cache_inst->file_invalidate_regex('alias_cache::source::'.$id.'::target::'.$ad['target'].'.*');
				}
				$q = "UPDATE aliases SET cached = '".$cache[$ad['target']]."' WHERE target = '".$ad['target']."' AND source = '$id'";
				$this->db_query($q);
			}
		}
		$this->cache_oo_aliases($id);
		return $this->mk_my_orb("list_aliases",array("id" => $id),$this->use_class);
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
		$this->recover_idx_enumeration($oid);
		$ret_type = isset($ret_type) ? $ret_type : GET_ALIASES_BY_CLASS;
		$this->alias_rel_type = array();

		$obj = $this->get_object($oid);
		// with this you can alter the sql clause to fetch only the data you are
		// actually going to use
		$modifier = isset($modifier) ? $modifier : "aliases.*";
		$q = "SELECT $modifier, objects.class_id AS class_id, objects.name AS name
			FROM aliases
			LEFT JOIN objects ON (aliases.target = objects.oid)
			WHERE source = '$oid' ORDER BY aliases.id";
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			$row["aliaslink"] = $obj["meta"]["aliaslinks"][$row["target"]];
			if (isset($filter))
			{
				$row = &$filter[0]->$filter[1]($row);
				if (is_array($row))
				{
					$retval = $retval + $row;
				};
			}
			elseif ($ret_type == GET_ALIASES_BY_CLASS)
			{
				$retval[$row["class_id"]][$row["idx"]] = $row;
				$this->alias_rel_type[$row["target"]] = $row["reltype"];
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

		$aliases = $this->get_oo_aliases(array("oid" => $oid));
		$by_idx = $by_alias = array();

		foreach($this->cfg["classes"] as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				$li = explode(",", $cldat["alias"]);
				foreach($li as $lv)
				{
					if (isset($cldat["alias_class"]))
					{
						$by_alias[$lv]["file"] = $cldat["alias_class"];
					}
					else
					{
						$by_alias[$lv]["file"] = $cldat["file"];
					}
					$by_alias[$lv]["class_id"] = $clid;
				}
			}
		}

		// try to find aliases until we no longer find any. 
		// why is this? well, to enable the user to add aliases bloody anywhere. like in files that are to be shown right away
		while (1)
		{

		$_cnt++;
		if ($_cnt > 20)
		{
			// make sure we don't end up in an endless loop
			break;
		}

		$_res = preg_match_all("/(#)(\w+?)(\d+?)(v|k|p|)(#)/i",$source,$matches,PREG_SET_ORDER);
		if (!$_res)
		{
			// if no more aliases are found, then break out of the loop.
			break;
		}

		$cache_inst = get_instance("cache");

		if (is_array($matches))
		{
			// we gather all aliases in here, grouped by class so we gan give them to parse_alias_list()
			$toreplace = array();
			foreach ($matches as $key => $val)
			{
				$clid = $by_alias[$val[2]]["class_id"];
				// dammit, this sucks. I need some way to figure out
				// whether there is a correct idx set in the aliases, and if so
				// use that, instead of the one in the list.
				//$idx = $val[3] - 1;
				$idx = $val[3];
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
					$$emb_obj_name->embedded = true;
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
						// check if the alias is cached
						// if nothing comes up, we just replace it with a empty string
						$replacement = $this->get_alias_cache($adata, $$emb_obj_name, &$cache_inst);
						$from_cache = true;

						if (method_exists($$emb_obj_name,"parse_alias") && ($replacement === false))
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

							$from_cache = false;
						}

						$source = str_replace($avalue,$replacement,$source);
						$this->write_alias_cache($adata, $$emb_obj_name, &$cache_inst, $replacement, $from_cache);
					}
				}
			}
		}
		}	// while (1)
	}

	////
	// Returns the variables created by parse_oo_alias
	function get_vars()
	{
		return (is_array($this->tmp_vars)) ? $this->tmp_vars : array();
	}

	function _init_la_tbl()
	{
		load_vcl("table");
		$this->t = new aw_table(array(
			"layout" => "generic"
		));
		$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
		$this->t->define_field(array(
			"name" => "icon",
			"caption" => "",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "comment",
			"caption" => "Muu info",
			"talign" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "alias",
			"caption" => "Alias",
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
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
			"name" => "cache",
			"caption" => "Cache",
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
			"name" => "reltype",
			"caption" => "Seose tüüp",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
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
	//   reltypes(array) - array of relation types
	function list_aliases($args)
	{
		extract($args);
		$GLOBALS['site_title'] = "Seostehaldur";
		classload('icons');
		$this->read_template("lists_new.tpl");

		$obj = $this->get_object($id);
		$this->id = $id;

		$reltypes[0] = "alias";
		$reltypes = new aw_array($reltypes);
		$this->reltypes = $reltypes->get();

		// init vcl table to $this->t and define columns
		$this->_init_la_tbl();

		// creates $this->typearr
		$this->make_alias_typearr();
		// creates $this->aliasarr
		$this->make_alias_classarr();

		if (is_array($rel_type_classes))
		{
			foreach ($rel_type_classes as $key => $val)
			{
				$this->rel_type_classes[$key] = $this->make_alias_classarr2($val);
			}
		}
		//arr($this->rel_type_classes);

		$this->search_url = $search_url;

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

				preg_match("/(\w*)$/",$cldat["file"],$m);
				//$aliases[$m[1]] = $cldat["name"];
				$types[] = $clid;
				$clids .= 'clids['.$clid.'] = "'.basename($cldat["file"]).'";'."\n";
			}
		}

		if (!$return_url)
		{
			$return_url = $this->mk_my_orb("list_aliases", array("id" => $id));
		};

		$return_url = urlencode($return_url);

		$this->recover_idx_enumeration($id);

		// fetch a list of all the aliases for this object
		$alist = $this->get_aliases(array(
			"oid" => $id,
			//"type" => $types
		));

		$chlinks = array();
		foreach($alist as $alias)
		{
			$aclid = $alias["class_id"];
			list($astr) = explode(",",$classes[$aclid]["alias"]);
			$astr = sprintf("#%s%d#",$astr,$alias["idx"]);
			$ch = $this->mk_my_orb("change", array("id" => $alias["target"], "return_url" => $return_url),$classes[$aclid]["file"]);
			$chlinks[$alias["target"]] = $ch;
			$reltype_id = (int)$obj["meta"]["alias_reltype"][$alias["target"]];

			$alias["icon"] = html::img(array(
				"url" => icons::get_icon_url($aclid,""),
			));

			// it has a meaning only for embedded aliases
			if ($reltype_id == 0)
			{
				$alias["alias"] = sprintf("<input type='text' size='10' value='%s' onClick='this.select()' onBlur='this.value=\"%s\"'>",$astr,$astr);
			};

			$alias["link"] = html::checkbox(array(
				"name" => "link[" . $alias["target"] . "]",
				"value" => 1,
				"checked" => $obj["meta"]["aliaslinks"][$alias["target"]],
			));

			$alias["title"] = $classes[$aclid]["name"];

			$alias["check"] = html::checkbox(array(
				"name" => "check[" . $alias["target"] . "]",
				"value" => $alias["target"],
			));

			$alias["name"] = html::href(array(
				"url" => $ch,
				"caption" => ($alias["name"] == "") ? "(no name)" : $alias["name"],
			));

			if ($alias["relobj_id"])
			{
				$alias["reltype"] = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $alias["relobj_id"],"return_url" => $return_url),$classes[$aclid]["file"]),
					"caption" => $this->reltypes[$reltype_id],
				));
			}
			else
			{
				$alias["reltype"] = $this->reltypes[$reltype_id];
			};



			$alias["cache"] = html::checkbox(array(
				'name' => 'cache['.$alias['target'].']',
				'value' => 1,
				'checked' => ($alias['cached'] == 1)
			));

			$this->t->define_data($alias);
		}

		$this->t->set_default_sortby("title");
		$this->t->sort_by();
		$toolbar = $this->mk_toolbar($args['s']['class_id']);

		if (isset($this->reforb))
		{
			$reforb = $this->reforb;
		}
		else
		{
			$reforb = $this->mk_reforb("submit_list",array(
				"id" => $id,
				"subaction" => "none",
				"return_url" => $return_url
				),$this->use_class);
		};

		$this->vars(array(
			'class_ids' => $clids,
			"table" => $this->t->draw(),
			"id" => $id,
			"parent" => $obj["parent"],
			"reforb" => $reforb,
			"chlinks" => join("\n",map2("chlinks[%s] = \"%s\";",$chlinks)),
			"toolbar" => $toolbar,
			"return_url" => $return_url,
			"period" => $period,
			"search_url" => $this->mk_my_orb("search_aliases",array("id" => $this->id),$this->use_class),
		));

		return $this->parse();
	}

	////
	// !adds the specified alias to the object
	// parameters
	//   id - the object to which the alias is added (source)
	//   alias - id of the object to add as alias (target)
	function create_alias($args = array())
	{
		extract($args);
		$aliases = explode(",",$alias);
		$obj = $this->get_object($id);
		$alias_reltype = $obj["meta"]["alias_reltype"];

		foreach($aliases as $onealias)
		{
			$_al = (int)$onealias;
			if ($_al > 0)
			{
				$al = $this->get_object($_al);
			}

			// parent will be the parent of the object from which the relation
			// goes out.
			$relobj_id = $this->new_object(array(
				"parent" => $obj["parent"],
				"class_id" => CL_RELATION,
				"status" => STAT_ACTIVE,
				"subclass" => $al["class_id"],
				"no_flush" => 1,
			));

			// let the correct class override the alias adding if it wants to
			// if the class does not handle it, it falls back on core::addalias
			$cl = $this->cfg["classes"][$al["class_id"]]["alias_class"];
			if ($cl != "")
			{
				$inst = get_instance($cl);
				$inst->addalias(array(
					"id" => $id,
					"alias" => $onealias,
					"reltype" => $reltype,
					"relobj_id" => $relobj_id,
					"extra" => $args["data"],
				));
				$alias_reltype[$onealias] = $reltype;
			}
			else
			{
				$this->addalias(array(
					"id" => $id,
					"alias" => $onealias,
					"reltype" => $reltype,
					"relobj_id" => $relobj_id,
					"extra" => $args["data"],
				));
				$alias_reltype[$onealias] = $reltype;
			}

		};

		// this really is obsoleted by the reltype field in the aliases table ..
		// but before it can be removed, we should check whether any code
		// relies on reading the type from metainfo. -- duke
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "alias_reltype",
			"value" => $alias_reltype,
		));
	}

	function orb_addalias($args = array())
	{
		$this->create_alias($args);
		return $this->mk_my_orb("list_aliases",array("id" => $args["id"]),$this->use_class);
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

	function make_alias_classarr($clid_list = false)
	{
		$this->classarr = array();

		$classes = $this->cfg["classes"];
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				if (is_array($clid_list) && (sizeof($clid_list) > 0) )
				{
					if (in_array($clid,$clid_list))
					{
						$this->classarr[$clid] = $cldat["name"];
					}
				}
				else
				{
					$this->classarr[$clid] = $cldat["name"];
				};

			}
		}
	}

	function make_alias_classarr2($rel_arr)
	{
		if (!is_array($rel_arr) || ($rel_arr == array()))
			return NULL;
		$classes = $this->cfg["classes"];
		$arr = array();
		foreach($rel_arr as $val)
		{
			if (isset($classes[$val]))// && isset($classes[$val]["alias"]))
			{
				$fil = ($classes[$val]["alias_class"] != "") ? $classes[$val]["alias_class"] : $classes[$val]["file"];
				//preg_match("/(\w*)$/",$fil,$m);
				//$lib = $m[1];
				//$arr[$val] = $classes[$val]['name'];
				//$arr[basename($fil)] = $classes[$val]['name'];
				$arr[$val] = $classes[$val]['name'];
			}
		}
		return $arr;
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
		// paneme aliases kirja
		if (!empty($oid))
		{
			$this->upd_object(array(
				"oid" => $oid,
				"metadata" => array(
					"aliases" => "",
					"alias_reltype" => $this->alias_rel_type,
				),
			));
		};
	}

	function recover_idx_enumeration($id)
	{
		// fetch a list of all the aliases for this object
		$alist = $this->get_aliases(array(
			"oid" => $id,
		));
		$oid = $id;

		// we need to check whether there are any conflicts in the idx list
		// and if so, correct them
		$idx_check = array();
		$need_to_enumerate = array();
		$idx_by_class = array();
		$idx_by_id = array();
		foreach($alist as $alias)
		{
			// if any of the idx-s is 0, then we need to re-enumerate for sure.
			if ($alias["idx"] == 0)
			{
				$need_to_enumerate[$alias["class_id"]] = 1;
			};

			$idx_by_class[$alias["class_id"]]++;

			$idx_by_id[$alias["id"]] = $idx_by_class[$alias["class_id"]];

			if ($idx_check[$alias["class_id"]][$alias["idx"]])
			{
				$need_to_enumerate[$alias["class_id"]] = 1;
			}
			else
			{
				$idx_check[$alias["class_id"]][$alias["idx"]] = 1;
			};
		}

		if ( (sizeof($need_to_enumerate) > 0) && (sizeof($idx_by_id) > 0) )
		{
			foreach($idx_by_id as $id => $idx)
			{
				$q = "UPDATE aliases SET idx = '$idx' WHERE id = '$id'";
				$this->db_query($q);
			};
			$this->cache_oo_aliases($oid);
		};
	}

	////
	// !Search and list share the same toolbar
	function mk_toolbar($objtype = '', $selectedot = '')
	{
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/icons"));

		if (is_array($objtype) && (count($objtype) == 1))
		{
			$objtype = array_pop($objtype);
		}
		else
		if (is_numeric($objtype = ltrim($objtype,',')))
		{

		}
		else
		{
			$objtype = NULL;
		}

		$choices = array();
		$choices2 = array();
		$classes = $this->cfg["classes"];
		// generate a list of class => name pairs
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				//$fil = ($cldat["alias_class"] != "") ? $cldat["alias_class"] : $cldat["file"];
				//preg_match("/(\w*)$/",$fil,$m);
				//$lib = $m[1];
				//indent the names
				if (empty($cldat["disable_alias"]))
					$choices[$clid] = $cldat["name"];

				$choices2[$clid] = $cldat["name"];
			}
		}
		asort($choices);
		asort($choices2);

		$boxesscript = $this->get_file(array('file' => $this->cfg['tpldir'].'/aliasmgr/selectboxes.tpl'));

		$hist = aw_global_get('aliasmgr_obj_history');

		$hist = !is_array($hist) ? array() : $this->make_alias_classarr2($hist);

		foreach($this->reltypes as $k => $v)
		{
			$dval = true;
			$single_select = "capt_new_object";
			$sele = NULL;
			if ($k == 0)
			{
				$choice =  &$choices;
			}
			else
			{
				$choice = &$choices2;
			}

			if (!isset($this->rel_type_classes[$k]))
			{
				$vals = $this->mk_kstring($choice);
				$defaults1 .= 'listB.setDefaultOption("'.$k.'","capt_new_object");'."\n";
				if (isset($choice[$objtype]))
					$sele = $objtype;
			}
			else
			{
				if (count($this->rel_type_classes[$k])<=1)
				{
					$dval = false;
					$single_select = $this->rel_type_classes[$k][0];
				}
				else
				{
				}
				$vals = $this->mk_kstring($this->rel_type_classes[$k]);
				if (isset($this->rel_type_classes[$k][$objtype]))
					$sele = $objtype;
			}

			$history = array();
			$dvals = '';

			if ($dval)
			{
				$dvals = ',"Objekti tüüp","capt_new_object"';
				$comp = (isset($this->rel_type_classes[$k]) && is_array($this->rel_type_classes[$k])) ? $this->rel_type_classes[$k] : $choice;
				$hh = array_intersect($hist,$comp);
				$history = $this->mk_kstring($hh);
				if ($history)
				{
					$dvals .= ',"----------------","capt_new_object",'.$history.',"----------------","capt_new_object"';
				}
			}

			$rels1 .= 'listB.addOptions("'.$k.'"'.$dvals.','.$vals.");\n";
			$defaults1 .= 'listB.setDefaultOption("'.$k.'","'.($sele ? $sele : $single_select).'");'."\n";
			if ($selectedot && ($this->reltype == $k))
			{
				$defaults1 .= 'listB.setDefaultOption("'.$k.'","'.$selectedot.'");'."\n";
			}

		}

		$rels1 .= 'listB.addOptions("_"'.',"Objekti tüüp","capt_new_object"'.");\n";
		$defaults1 .= 'listB.setDefaultOption("_","capt_new_object");'."\n";

		$boxesscript = localparse($boxesscript, array('rels1' => $rels1, 'defaults1' =>  $defaults1));
		$boxesscript .= $this->get_file(array('file'=> $this->cfg['tpldir'].'/aliasmgr/selectbox_selector.tpl'));
		$toolbar->add_cdata($boxesscript);

		$toolbar->add_cdata(
			html::select(array(
				"options" => (count($this->reltypes) <= 1) ? $this->reltypes :(array('_' => 'Seose tüüp') + $this->reltypes),
				"name" => "reltype",
				"selected" => $this->reltype,
				'onchange' => "listB.populate();",
			))
		);

		$ht = <<<HTM
			<select NAME="aselect" style="width:200px" onChange="GetOptions(document.foo.aselect,document.searchform.elements['s[class_id]']);">
				<script LANGUAGE="JavaScript">listB.printOptions()</SCRIPT>
			</select>
HTM;

		$toolbar->add_cdata($ht);

		$toolbar->add_button(array(
			"name" => "new",
			"tooltip" => "Lisa uus objekt",
			"url" => "javascript:create_new_object()",
			"imgover" => "new_over.gif",
			"img" => "new.gif",
		));


		if (is_object($this->search))
		{
			$toolbar->add_button(array(
				"name" => "search",
				"tooltip" => "Otsi",
				"url" => "javascript:if (document.foo.reltype.value!='_') {document.searchform.submit();} else alert('Vali seosetüüp!')",
				"imgover" => "search_over.gif",
				"img" => "search.gif",
			));
		}
		else
		{
			$toolbar->add_button(array(
				"name" => "search",
				"tooltip" => "Otsi",
				"url" => "javascript:search_for_object()",
				"imgover" => "search_over.gif",
				"img" => "search.gif",
			));
		};

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "refresh",
			"tooltip" => "Reload",
			"url" => "javascript:window.location.reload()",
			"imgover" => "refresh_over.gif",
			"img" => "refresh.gif",
		));

		if (is_object($this->search))
		{
			if ($this->search->get_opt("rescounter") > 0)
			{
				$toolbar->add_button(array(
					"name" => "save",
					"tooltip" => "Loo seos(ed)",
					"url" => "javascript:aw_save()",
					"imgover" => "save_over.gif",
					"img" => "save.gif",
				));
			};
		}
		else
		{
			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "javascript:saveform()",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));

			$toolbar->add_button(array(
				"name" => "delete",
				"tooltip" => "Kustuta valitud seos(ed)",
				"url" => "javascript:awdelete()",
				"imgover" => "delete_over.gif",
				"img" => "delete.gif",
			));
		};

		$this->vars(array(
			"create_relation_url" => $this->mk_my_orb("search_aliases",array("id" => $this->id),$this->use_class),
		));

		return $toolbar->get_toolbar();
	}

	function mk_kstring($arr)
	{
		$alls = array();
		foreach($arr as $key => $val)
		{
			$alls[] ='"'.$val.'"';
			$alls[] ='"'.$key.'"';
		}
		return implode(',', $alls);
	}

	function get_alias_cache($adata, &$emb_inst, &$cache_inst)
	{
		if ($adata['cached'] == 1)
		{
			if (method_exists($emb_inst, "callback_alias_cache_get_url_hash"))
			{
				$this->url_hash = $emb_inst->callback_alias_cache_get_url_hash();
			}
			else
			{
				$this->url_hash = gen_uniq_id($this->REQUEST_URI);
			}

			$key = 'alias_cache::source::'.$adata['source'].'::target::'.$adata['target'].'::urlhash::'.$this->url_hash;
			if (($replacement = $cache_inst->file_get($key)) !== false)
			{
				return $replacement;
			}
		}
		return false;
	}

	function write_alias_cache($adata, &$emb_inst, &$cache_inst, $replacement, $from_cache)
	{
		if ($from_cache)
		{
			// just let the object handle the cache show if needed
			if (method_exists($emb_inst, "callback_alias_cache_show_alias"))
			{
				$emb_inst->callback_alias_cache_show_alias(array(
					"alias" => $adata,
					"content" => $replacement
				));
			}
		}

		if (!$from_cache)
		{
			if ($adata['cached'] == 1)
			{
				$groups = array();
				if (method_exists($emb_inst, "callback_alias_cache_get_groups"))
				{
					$groups = $emb_inst->callback_alias_cache_get_groups(array(
						'id' => $adata['source']
					));
				}

				$key = 'alias_cache::source::'.$adata['source'].'::target::'.$adata['target'].'::urlhash::'.$this->url_hash;
				$cache_inst->file_set($key,$replacement);
			}
		}
	}

	function search_callback_modify_parts($args,$parts)
	{
		// if there is no class_id part, limit the search to those object types
		// which have alias_class set
		if (!$parts["class_id"])
		{
			$this->make_alias_classarr();
			$parts["class_id"] = sprintf("class_id IN (%s)",join(",",array_keys($this->classarr)));
		};
		$parts["brother_id"] = "(brother_of = 0 OR brother_of = oid)";
	}

	////
	// !Create new fields in the aliases table. The concept sucks, but I think
	// that separate fields are the best approach to this.
	function do_check_tables()
	{
		$table = $this->db_get_table("aliases");
		if (!$table["fields"]["relobj_id"])
		{
			$q = "ALTER TABLE aliases ADD relobj_id bigint unsigned not null";
			$this->db_query($q);
			$q = "ALTER TABLE aliases ADD reltype bigint unsigned not null";
			$this->db_query($q);
		}; 
		if (!$table["fields"]["pri"])
		{
			$this->db_query("ALTER TABLE aliases ADD pri int unsigned not null default 0");
		};
	}
	
	////
	// !returns an array of class_id => class name's that you can feed to picker()
	function get_clid_picker()
	{
		$ret = array();
		foreach($this->cfg["classes"] as $clid => $cldat)
		{
			if ($cldat["name"] != "")
			{
				// add folders
				$nm = $cldat["name"];
/*				if ($cldat["parents"] != "")
				{
					list($pt) = explode(",", $cldat["parents"]);
					while (isset($this->cfg["classfolders"][$pt]))
					{
						$nm = $this->cfg["classfolders"][$pt]["name"]."/".$nm;
						$pt = $this->cfg["classfolders"][$pt]["parent"];
					}
				}*/
				$ret[$clid] = $nm;
			}
		}
		asort($ret);
		return $ret;
	}

}
?>
