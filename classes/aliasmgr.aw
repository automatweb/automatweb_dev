<?php
// aliasmgr.aw - Alias Manager
// $Header: /home/cvs/automatweb_dev/classes/Attic/aliasmgr.aw,v 2.193 2006/03/08 15:15:01 kristo Exp $

class aliasmgr extends aw_template
{
	function aliasmgr($args = array())
	{
		extract($args);
		$this->use_class = isset($args["use_class"]) ? $args["use_class"] : get_class($this);
		$this->init("aliasmgr");
		$this->lc_load("aliasmgr","lc_aliasmgr");

		$this->alp = get_instance("alias_parser");
	}

	/**  
		
		@attrib name=search_aliases params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function search($args = array())
	{
		extract($args);

		$this->reltype = isset($args['s']['reltype']) ? $args['s']['reltype']: $reltype;

		$GLOBALS['site_title'] = "Seostehaldur";
		$search = get_instance(CL_SEARCH);

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

		$this->_filtr_rtc();

		$classes = aw_ini_get("classes");
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				if (false && $cldat["alias_class"])
				{
					$cldat["file"] = $cldat["alias_class"];
					$classes[$clid]["file"] = $cldat["alias_class"];
				}
			}
			$clids .= 'clids['.$clid.'] = "'.basename($cldat["file"]).'";'."\n";
		}
		

		$args["clid"] = &$this;
		$form = $search->show($args);
		$this->search = &$search;

		$id = ($args["id"]) ? $args["id"] : $args["docid"];
		$this->id = $id;

		$obj = obj($id);

		if (is_object($this->ref))
		{
			$this->ref = $ref;
		};

		if (empty($return_url))
		{
			$return_url = urlencode($this->mk_my_orb("list_aliases", array("id" => $id),$this->use_class));
		}
		$tb = $this->mk_toolbar($args['s']['class_id'], $args['objtype']);

		$this->read_template("search.tpl");
		$this->vars(array(
			"create_relation_url" => $this->mk_my_orb("search_aliases",array("id" => $this->id),$this->use_class),
		));
		
		$this->vars(array(
			'class_ids' => $clids,
			"parent" => $obj->parent(),
			"period" => $period,
			"id" => $id,
			"return_url" => urlencode($return_url),
			"reforb" => $this->mk_reforb("search_aliases",array(
				"no_reforb" => 1,
				"search" => 1,
				"id" => $id,
				"reltype" => $reltype,
				"return_url" => $return_url,
			),$this->use_class),
			"saveurl" => $this->mk_my_orb("addalias",array("id" => $id,"reltype" => $reltype,"return_url" => ($return_url)),$this->use_class),
			"toolbar" => $tb,
			"form" => $form,
			"table" => $search->get_results(),
		));
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
				"caption" => t(""),
				'value' => html::href(array(
					'caption' =>'lihtsam otsing',
					'url' => $request.'&simple=1',
				)),
			);

			$fields["class_id"] = array(
				"type" => "class_id_multiple",
				"caption" => t("Klass"),
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
				"caption" => t(""),
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

	/** Submits the alias list 
		
		@attrib name=submit_list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_list($args = array())
	{
		extract($args);
		$o = obj($id);

		if ($subaction == "delete")
		{
			$to_delete = new aw_array($check);
			foreach($to_delete->get() as $alias_id)
			{
				$c = new connection($alias_id);
				$c->delete();
			};
		};

		$o->set_meta("aliaslinks",$link);
		$o->save();

		if (!empty($group))
		{
			$act = "change";
		}
		else
		{
			$act = "list_aliases";
		};
		$xargs = array("reforb", "check", "subaction", "emb", "alias_to", "alias_to_prop", "cfgform", "ret_to_orb", "artists", "MAX_FILE_SIZE");
		foreach($xargs as $arg)
		{
			unset($args[$arg]);
		}
		// so how should this thing work? if there is a special argument, then put the current
		// url into the new url as a return_url

		// except that if there already is one, then add do it. eh?
		return $this->mk_my_orb($act, $args + array("id" => $id,"group" => $group,"return_url" => $orig_return_url, "no_op" => $no_op), $this->use_class);
	}
		
	////
	// !Gets all aliases for an object
	// params:
	//   oid - the object whose aliases we must return
	function get_oo_aliases($args = array())
	{
		return $this->alp->get_oo_aliases($args);
	}

	////
	// !Parses all embedded objects inside another document
	// arguments:
	// oid(int) - document id
	// source - document content
	// args[meta][aliases] - optional, if set, result of get_oo_aliases for object $oid
	function parse_oo_aliases($oid,&$source,$args = array())
	{
		return $this->alp->parse_oo_aliases($oid, $source, $args);
	}

	////
	// Returns the variables created by parse_oo_alias
	function get_vars()
	{
		return $this->alp->get_vars();
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
			"caption" => t(""),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "25",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"talign" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "lang",
			"caption" => t("Keel"),
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "comment",
			"caption" => t("Asukoht"),
			"talign" => "center",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "alias",
			"caption" => t("Alias"),
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
		));
		$this->t->define_field(array(
			"name" => "link",
			"caption" => t("Link"),
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
			"nowrap" => "1",
		));
		$this->t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
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
			"caption" => t("Tüüp"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
		));
		$this->t->define_field(array(
			"name" => "reltype",
			"caption" => t("Seose tüüp"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));

		$this->t->define_chooser(array(
                        "name" => "check",
                        "field" => "id",
                ));

	}

	/** the new alias lister 
		
		@attrib name=list_aliases params=name default="0"
		
		@param id required type=int
		@param table optional
		@param sortby optional
		@param sort_order optional
		
		@returns
		
		
		@comment
		params:
		id - the object whose aliases we will observe
		reltypes(array) - array of relation types

	**/
	function list_aliases($args)
	{
		extract($args);
		$GLOBALS['site_title'] = "Seostehaldur | ".html::get_change_url($id, array(),"Objekti muutmine");
		classload('core/icons');

		$obj = obj($id);
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

		$this->_filtr_rtc();

		$this->search_url = $search_url;

		// this will be an array of class => name pairs for all object types that can be embedded
		$aliases = array();
		$types = array();
		$classes = aw_ini_get("classes");
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				if (false && $cldat["alias_class"])
				{
					$cldat["file"] = $cldat["alias_class"];
					$classes[$clid]["file"] = $cldat["alias_class"];
				}

				preg_match("/(\w*)$/",$cldat["file"],$m);
				$types[] = $clid;
			}
			$clids .= 'clids['.$clid.'] = "'.basename($cldat["file"]).'";'."\n";
		}

		if (!$return_url)
		{
			$return_url = $this->mk_my_orb("list_aliases", array("id" => $id));
		};
		
		$toolbar = $this->mk_toolbar($args['s']['class_id']);
		$this->read_template("lists_new.tpl");

		$return_url = urlencode($return_url);

		//$this->recover_idx_enumeration($id);

		if (aw_ini_get("config.object_translation") == 1)
		{
			$l = get_instance("languages");
			$langinfo = $l->get_list();
		};

		// fetch a list of all the aliases for this object

		$alinks = $obj->meta("aliaslinks");

		foreach($obj->connections_from() as $alias)
		{
			$adat = array(
				"createdby" => $alias->prop("to.createdby"),
				"created" => $alias->prop("to.created"),
				"modifiedby" => $alias->prop("to.modifiedby"),
				"modified" => $alias->prop("to.modified"),
				"comment" => $alias->prop("to.comment")
			);

			$target_obj = $alias->to();

			$adat["lang"] = $target_obj->lang();

			$aclid = $alias->prop("to.class_id");

			// yuck. I wish the static document editing forms were gone already
			$edfile = $classes[$aclid]["file"];
			if ($aclid == CL_DOCUMENT)
			{
				$edfile = "doc";
			};

			$reltype_id = $alias->prop("reltype");

			$adat["icon"] = html::img(array(
				"url" => icons::get_icon_url($target_obj),
			));

			// it has a meaning only for embedded aliases
			if ($reltype_id == 0)
			{
				list($astr) = explode(",",$classes[$aclid]["alias"]);
				if ($astr == "")
				{
					list($astr) = explode(",",$classes[$aclid]["old_alias"]);
				}
				$astr = sprintf("#%s%d#",$astr,$alias->prop("idx"));
				$adat["alias"] = sprintf("<input type='text' size='10' value='%s' onClick='this.select()' onBlur='this.value=\"%s\"'>",$astr,$astr);
			};

			$adat["link"] = html::checkbox(array(
				"name" => "link[" . $alias->prop("to") . "]",
				"value" => 1,
				"checked" => $alinks[$alias->prop("to")],
			));

			$adat["title"] = $classes[$aclid]["name"];

			// for the chooser
			$adat["id"] = $alias->prop("id");

			$__to = $alias->to();
			$str = "";
			if ($__to->parent())
			{
				$__pt1 = obj($__to->parent());
				$str .= $__pt1->name();
				if ($__pt1->parent())
				{
					$__pt2 = obj($__pt1->parent());
					$str = $__pt2->name()." / ".$str;
				}
			}
			$adat["comment"] = $str;
			$adat["name"] = html::href(array(
				"url" => $this->mk_my_orb("change", array("id" => $alias->prop("to"), "return_url" => $return_url), $edfile),
				"caption" => parse_obj_name($__to->name()),
			));

			$type_str = $this->reltypes[$reltype_id];
			// shoot me. 
			// you fool, "shoot me" is just a good string to locate this place quickly later on.
			//  -- duke
			if ((aw_ini_get("config.object_translation") == 1) && ($reltype_id == RELTYPE_TRANSLATION))
			{
				$type_str = "tõlge (" . $langinfo[$alias->prop("to.lang_id")] . ")";
			};
			if ((aw_ini_get("config.object_translation") == 1) && ($reltype_id == RELTYPE_ORIGINAL))
			{
				$type_str = "originaal (" . $langinfo[$alias->prop("to.lang_id")] . ")";
			};

			if ($alias->prop("relobj_id"))
			{
				$adat["reltype"] = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $alias->prop("relobj_id"),"return_url" => $return_url),$classes[$aclid]["file"]),
					"caption" => $type_str,
				));
			}
			else
			{
				$adat["reltype"] = $type_str;
			};

			$this->t->define_data($adat);
		}

		$this->t->set_default_sortby("title");
		$this->t->sort_by();

		if (isset($this->reforb))
		{
			$reforb = $this->reforb;
		}
		else
		{
			$reforb = $this->mk_reforb("submit_list",array(
				"id" => $id,
				"subaction" => "none",
				"return_url" => $return_url,
				"orig_return_url" => urlencode($_GET["return_url"]),
				"no_op" => $_GET["no_op"]
				),$this->use_class
			);
		};

		if (in_array($obj->class_id() , get_container_classes()))
		{
			$_a_parent = $obj->id();
		}
		else
		{
			$_a_parent = $obj->parent();
		}

		$defcs = array(CL_IMAGE => "image.default_folder", CL_FILE => "file.default_folder", CL_EXTLINK => "links.default_folder");
		$def_str = "";
		foreach($defcs as $def_clid => $def_ini)
		{
			$def_val = aw_ini_get($def_ini);
			if (is_oid($def_val) && $this->can("view", $def_val) && $this->can("add", $def_val))
			{
				$this->vars(array(
					"parent" => $def_val,
					"period" => $period,
					"id" => $id,
					"return_url" => $return_url,
					"def_fld_clid" => $def_clid
				));
				$def_str .= $this->parse("HAS_DEF_FOLDER");
			}
		}


		$this->vars(array(
			"HAS_DEF_FOLDER" => $def_str,
			"class_ids" => $clids,
			"table" => $this->t->draw(),
			"id" => $id,
			"parent" => $_a_parent,
			"reforb" => $reforb,
			"toolbar" => $toolbar,
			"return_url" => $return_url,
			"period" => $period,
			"search_url" => $this->mk_my_orb("search_aliases",array(
				"id" => $this->id,
				"return_url" => (isset($search_return_url) ? $search_return_url : $return_url),
			),$this->use_class),
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

		if ($reltype == "_")
		{
			$reltype = "";
		}

		foreach($aliases as $onealias)
		{
			$o = obj($id);
			$o->connect(array(
				"to" => $onealias,
				"reltype" => $reltype,
				"data" => $args["data"]
			));

		};
	}

	/**  
		
		@attrib name=addalias params=name default="0"
		
		@param id required
		@param alias required
		@param reltype optional
		
		@returns
		
		
		@comment

	**/
	function orb_addalias($args = array())
	{
		$this->create_alias($args);
		return $this->mk_my_orb("list_aliases",array("id" => $args["id"],"return_url" => ($args["return_url"])),$this->use_class);
	}

	////
	// !puts all alias classes into $this->typearr
	function make_alias_typearr()
	{
		$adc = get_instance(CL_ADD_TREE_CONF);
		$filt = false;
		if (($adc_id = $adc->get_current_conf()))
		{
			$filt = $adc->get_alias_filter($adc_id);
		}

		$this->typearr = array();

		$classes = aw_ini_get("classes");
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				if (!is_array($filt) || $filt[$clid] == $clid)
				{
					$this->typearr[] = $clid;
				}
			}
		}
	}

	function make_alias_classarr($clid_list = false)
	{
		// check if there is an add tree conf for the current user
		$adc = get_instance(CL_ADD_TREE_CONF);
		$filt = false;
		if (($adc_id = $adc->get_current_conf()))
		{
			$filt = $adc->get_alias_filter($adc_id);
		}

		$this->classarr = array();

		$classes = aw_ini_get("classes");
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				if (is_array($clid_list) && (sizeof($clid_list) > 0) )
				{
					if (in_array($clid,$clid_list))
					{
						if (!is_array($filt) || $filt[$clid] == $clid)
						{
							$this->classarr[$clid] = $cldat["name"];
						}
					}
				}
				else
				{
					if (!is_array($filt) || $filt[$clid] == $clid)
					{
						$this->classarr[$clid] = $cldat["name"];
					}
				};

			}
		}
	}

	function make_alias_classarr2($rel_arr)
	{
		if (!is_array($rel_arr) || ($rel_arr == array()))
		{
			return NULL;
		}

		// check if there is an add tree conf for the current user
		$adc = get_instance(CL_ADD_TREE_CONF);
		$filt = false;
		if (($adc_id = $adc->get_current_conf()))
		{
			$filt = $adc->get_alias_filter($adc_id);
		}

		$classes = aw_ini_get("classes");
		$arr = array();
		foreach($rel_arr as $val)
		{
			if (isset($classes[$val]))
			{
				if (!is_array($filt) || $filt[$clid] == $clid)
				{
					$fil = ($classes[$val]["alias_class"] != "") ? $classes[$val]["alias_class"] : $classes[$val]["file"];
					$arr[$val] = $classes[$val]['name'];
				}
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

		$o = obj($oid);
		$tmp = aw_ini_get("classes");
		foreach($o->connections_from() as $c)
		{
			list($astr) = explode(",",$tmp[$c->prop("to.class_id")]["alias"]);
			$ret[$c->prop("to")] = "#".$astr.($c->prop("idx"))."#";
		}
		return $ret;
	}

	////
	// !Search and list share the same toolbar
	function mk_toolbar($objtype = '', $selectedot = '')
	{
		$toolbar = get_instance("vcl/toolbar");

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

		$adc = get_instance(CL_ADD_TREE_CONF);
		$filt = false;
		if (($adc_id = $adc->get_current_conf()))
		{
			$filt = $adc->get_alias_filter($adc_id);
		}

		$choices = array();
		$choices2 = array();
		$classes = aw_ini_get("classes");
		// generate a list of class => name pairs
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				if (!is_array($filt) || $filt[$clid] == $clid)
				{
					//indent the names
					if (empty($cldat["disable_alias"]))
					{
						$choices[$clid] = $cldat["name"];
					}

					$choices2[$clid] = $cldat["name"];
				}
			}
		}
		asort($choices);
		asort($choices2);

		$this->read_template("selectboxes.tpl");
		// wuh, this is bad and stuff
		//$boxesscript = $this->get_file(array('file' => $this->cfg['tpldir'].'/aliasmgr/selectboxes.tpl'));

		$tmp = aw_ini_get("classes");
		$this->reltypes[RELTYPE_BROTHER] = "too vend";
		$this->rel_type_classes[RELTYPE_BROTHER] = array(
			CL_MENU => $tmp[CL_MENU]["name"],
			CL_SHOP_PRODUCT => $tmp[CL_SHOP_PRODUCT]['name'],
			CL_SHOP_PACKET => $tmp[CL_SHOP_PACKET]['name'],
			CL_SHOP_PRODUCT_PACKAGING => $tmp[CL_SHOP_PRODUCT_PACKAGING]['name'],
		);

		$this->reltypes[RELTYPE_ACL] = "&otilde;igus";
		$this->rel_type_classes[RELTYPE_ACL] = array(
			CL_GROUP => $tmp[CL_GROUP]["name"]
		);


		foreach($this->reltypes as $k => $v)
		{
			$v = html_entity_decode($v);
			$this->reltypes[$k] = $v;
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
				{
					$sele = $objtype;
				}
			}
			else
			{
				if (count($this->rel_type_classes[$k])<=1)
				{
					$dval = false;
					$single_select = $this->rel_type_classes[$k][0];
				}

				$vals = $this->mk_kstring($this->rel_type_classes[$k]);
				if (isset($this->rel_type_classes[$k][$objtype]))
				{
					$sele = $objtype;
				}
			}

			$history = array();
			$dvals = '';
			$v = str_replace("&auml;","ä",$vals);
			$v = str_replace("&Auml;","Ä",$v);
			$v = str_replace("&uuml;","ü",$v);
			$v = str_replace("&Üuml;","Ü",$v);
			$v = str_replace("&otilde;","õ",$v);
			$v = str_replace("&Otilde;","Õ",$v);
			$v = str_replace("&ouml;","ö",$v);
			$vals = str_replace("&Ouml;","Ö",$v);
			if ($dval)
			{
				$dvals = ',"Objekti tüüp","capt_new_object"';
				$comp = (isset($this->rel_type_classes[$k]) && is_array($this->rel_type_classes[$k])) ? $this->rel_type_classes[$k] : $choice;
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

		$this->vars(array(
			"rels1" => $rels1,
			"defaults1" => $defaults1,
		));

		$boxesscript = $this->parse();

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
			<select NAME="aselect" style="width:200px" >
				<script LANGUAGE="JavaScript">listB.printOptions()</SCRIPT>
			</select>
HTM;

		$toolbar->add_cdata($ht);

		$toolbar->add_button(array(
			"name" => "new",
			"tooltip" => t("Lisa uus objekt"),
			"url" => "javascript:create_new_object()",
			"img" => "new.gif",
		));


		if (is_object($this->search))
		{
			$toolbar->add_button(array(
				"name" => "search",
				"tooltip" => t("Otsi"),
				"url" => "javascript:if (document.foo.reltype.value!='_') {document.searchform.submit();} else alert('Vali seosetüüp!')",
				"img" => "search.gif",
			));
		}
		else
		{
			$toolbar->add_button(array(
				"name" => "search",
				"tooltip" => t("Otsi"),
				"url" => "javascript:search_for_object()",
				"img" => "search.gif",
			));
		};

		$toolbar->add_separator();

		$return_url = $this->mk_my_orb("list_aliases", array("id" => $this->id),$this->use_class);
		//$return_url = aw_global_get('REQUEST_URI');
				
		$return_url = urlencode($return_url);

		if (aw_ini_get("config.object_translation") == 1)
		{
			$toolbar->add_button(array(
				"name" => "translate",
				"tooltip" => t("Tõlgi"),
				"url" => $this->mk_my_orb("create",array("id" => $this->id,"return_url" => $return_url),"object_translation"),
				"target" => "_blank",
				"img" => "edit.gif",
			));
			
			$toolbar->add_separator();
		};

		$toolbar->add_button(array(
			"name" => "refresh",
			"tooltip" => t("Reload"),
			"url" => "javascript:window.location.reload()",
			"img" => "refresh.gif",
		));

		if (is_object($this->search))
		{
			if ($this->search->get_opt("rescounter") > 0)
			{
				$toolbar->add_button(array(
					"name" => "save",
					"tooltip" => t("Loo seos(ed)"),
					"url" => "javascript:aw_save()",
					"img" => "save.gif",
				));
			};
		}
		else
		{
			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => t("Salvesta"),
				"url" => "javascript:saveform()",
				"img" => "save.gif",
			));

			$toolbar->add_button(array(
				"name" => "delete",
				"tooltip" => t("Kustuta valitud seos(ed)"),
				"url" => "javascript:awdelete()",
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

	function search_callback_modify_parts(&$args,&$parts)
	{
		// if there is no class_id part, limit the search to those object types
		// which have alias_class set
		if (!$parts["class_id"])
		{
			$this->make_alias_classarr();
			$parts["class_id"] = sprintf("class_id IN (%s)",join(",",array_keys($this->classarr)));
		};

		if ($args["s"]["search_bros"] != 1)
		{
			$parts["brother_id"] = "(brother_of = 0 OR brother_of = oid)";
		}
		unset($parts["search_bros"]);
	}

	////
	// !returns an array of class_id => class name's that you can feed to picker()
	function get_clid_picker()
	{
		$ret = array();
		$tmp = aw_ini_get("classes");
		foreach($tmp as $clid => $cldat)
		{
			if ($cldat["name"] != "")
			{
				// add folders
				$nm = $cldat["name"];
				$ret[$clid] = $nm;
			}
		}
		asort($ret);
		return $ret;
	}

	function _filtr_rtc()
	{
		// filtr
		// check if there is an add tree conf for the current user
		$adc = get_instance(CL_ADD_TREE_CONF);
		if (($adc_id = $adc->get_current_conf()) && is_array($this->rel_type_classes))
		{
			$tmp = array();

			foreach($this->rel_type_classes as $key => $dat)
			{
				foreach(safe_array($dat) as $clid => $_nm)
				{
					if ($adc->can_access_class(obj($adc_id), $clid))
					{
						$tmp[$key][$clid] = $_nm;
					}
				}
			}
			$this->rel_type_classes = $tmp;
		}
	}

}
?>
