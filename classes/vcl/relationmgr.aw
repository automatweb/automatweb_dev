<?php 
 // this is the new relationmanager, unfortunately for now, still template based, let's hope
 // that class_base will one day be powerful enough to replace these with properties without
 // a headache -- ahz
class relationmgr extends aw_template
{
	function relationmgr()
	{
		$this->init("relationmgr");
	}
	
	function init_vcl_property($arr)
	{
		if(in_array($arr["obj_inst"]->class_id() , array(CL_MENU, CL_GROUP, CL_PROMO)))
		{
			$this->parent = $arr["obj_inst"]->id();
		}
		else
		{
			$this->parent = $arr["obj_inst"]->parent();
		}
		$this->_init_relations($arr);
		if($arr["request"]["srch"] == 1)
		{
			return $this->_show_search($arr);
		}
		else
		{
			return $this->_show_relations($arr);
		}
	}
	
	function _init_relations($arr)
	{
		$this->clids = array();
		$classes = aw_ini_get("classes");
		
		// maybe it would be nice to get this kind of relinfo from core
		// or storage, so i wouldn't have to make it here? -- ahz
		$this->clids[CL_MENU] = basename($classes[CL_MENU]["file"]);
		$this->clids[CL_SHOP_PRODUCT] = basename($classes[CL_SHOP_PRODUCT]["file"]);
		$this->clids[CL_SHOP_PACKET] = basename($classes[CL_SHOP_PACKET]["file"]);
		$this->clids[CL_SHOP_PRODUCT_PACKAGING] = basename($classes[CL_SHOP_PRODUCT_PACKAGING]["file"]);
		$this->clids[CL_GROUP] = basename($class[CL_GROUP]["file"]);
		$this->reltypes[0] = "Alias";
		$this->reltypes[RELTYPE_BROTHER] = "Too vend";
		$this->reltypes[RELTYPE_ACL] = "&Otilde;igus";
		$tmp = array();
		foreach($classes as $key => $class)
		{
			if($class["alias"])
			{
				$tmp[$key] = $class["name"];
				$this->clids[$key] = basename($class["file"]);
			}
		}
		$this->rel_classes[0] = array("capt_new_object" => t("Objekti tüüp")) + $tmp;
		$this->rel_classes[RELTYPE_ACL] = array(
			"capt_new_object" => t("Objekti tüüp"),
			CL_GROUP => $classes[CL_GROUP]["name"]
		);
		$this->rel_classes[RELTYPE_BROTHER] = array(
			"capt_new_object" => t("Objekti tüüp"),
			CL_MENU => $classes[CL_MENU]["name"],
			CL_SHOP_PRODUCT => $classes[CL_SHOP_PRODUCT]['name'],
			CL_SHOP_PACKET => $classes[CL_SHOP_PACKET]['name'],
			CL_SHOP_PRODUCT_PACKAGING => $classes[CL_SHOP_PRODUCT_PACKAGING]['name'],
		);

		foreach($arr["relinfo"] as $key => $rel)
		{
			if(!$this->reltypes[$rel["value"]])
			{
				$this->reltypes[$rel["value"]] = $rel["caption"];
				$tmp = array();
				foreach($rel["clid"] as $val)
				{
					$tmp[$val] = $classes[$val]["name"];
					$this->clids[$val] = basename($classes[$val]["file"]);
				}
				$this->rel_classes[$rel["value"]] = $tmp;
			}
		}
		$atc = get_instance(CL_ADD_TREE_CONF);
		$filt = false;
		if (($adc_id = $atc->get_current_conf()))
		{
			$adc = obj($adc_id);
			$filt = $atc->get_alias_filter($adc_id);
		}
		if($filt)
		{
			$tmp = array();
			foreach($tmp as $key => $val)
			{
				foreach($val as $key2 => $val2)
				{
					if($adc->can_access_class($adc, $val2))
					{
						$tmp[$key][$key2] = $val2;
					}
				}
			}
			$this->true_rel_classes = $tmp;
			$tmp = array();
			foreach($this->clids as $key => $val)
			{
				if (array_key_exists($key, $filt))
				{
					$tmp[$key] = $val;
				}
			}
		}
		else
		{
			$this->true_rel_classes = $this->rel_classes;
		}
		foreach($this->clids as $key => $val)
		{
			$this->clid_list .= 'clids['.$key.'] = "'.$val.'";'."\n";
		}
	}
	
	function _show_search($arr)
	{
		$pr = array();
		$this->reltype = $arr["request"]["s"]["reltype"] ? $arr["request"]["s"]["reltype"] : $arr["request"]["reltype"];
		$search = get_instance("search");
		$arr = array_merge($arr, $arr["request"]);
		$arr["clid"] = get_instance("aliasmgr");
		$this->search = &$search;
		$form = $search->show($arr);
		$tb = &$this->_make_toolbar($arr);
		$this->read_template("rel_search.tpl");
		$req = $arr["request"];
		unset($req["action"]);
		unset($req["s"]);
		$reforb = $this->mk_reforb("change", array("no_reforb" => 1, "search" => 1) + $req, $req["class"]);
		$this->vars(array(
			"form" => $form,
			"table" => $search->get_results(),
			"toolbar" => $tb->get_toolbar(),
			"parent" => $this->parent,
			"reforb" => $reforb,
			"clids" => $this->clid_list,
			"period" => $arr["request"]["period"],
			"id" => $arr["obj_inst"]->id(),
			"saveurl" => $this->mk_my_orb("submit", array("reltype" => $this->reltype, "group" => $req["group"], "return_url" => urlencode($req["return_url"]), "reforb" => 1, "id" => $req["id"]), $req["class"]),
		));
		$pr = array(
			"relations" => array(
				"name" => "relations",
				"type" => "text",
				"value" => $this->parse(),
				"no_caption" => 1,
		));
		return $pr;
	}
	
	function _make_toolbar($arr)
	{
		$tb = get_instance("vcl/toolbar");
		$objtype = $arr["s"]["class_id"] ? $arr["s"]["class_id"] : $arr["request"]["objtype"];
		if (is_array($objtype) && (count($objtype) == 1))
		{
			$objtype = array_pop($objtype);
		}
		elseif (is_numeric($objtype = ltrim($objtype,',')))
		{
		}
		else
		{
			$objtype = NULL;
		}
		$this->read_template("selectboxes.tpl");
		foreach($this->reltypes as $k => $v)
		{
			$dval = true;
			$single_select = "capt_new_object";
			$sele = NULL;
			$vals = $this->true_rel_classes[$k];
			$vals = str_replace("&auml;", "ä", $vals);
			$vals = str_replace("&Auml;", "Ä", $vals);
			$vals = str_replace("&ouml;", "ö", $vals);
			$vals = str_replace("&Ouml;", "Ö", $vals);
			$vals = str_replace("&uuml;", "ü", $vals);
			$vals = str_replace("&Uuml;", "Ü", $vals);
			$vals = str_replace("&otilde;", "õ", $vals);
			$vals = str_replace("&Otilde;", "Õ", $vals);
			$vals = $this->mk_kstring($vals);
			if (isset($this->true_rel_classes[$k][$objtype]))
			{
				$sele = $objtype;
			}
			else
			{
				$sele = key($this->true_rel_classes[$k]);
			}
			$rels1 .= 'listB.addOptions("'.$k.'"'.$dvals.','.$vals.");\n";
			if ($objtype && $this->reltype == $k)
			{
				$defaults1 .= 'listB.setDefaultOption("'.$k.'","'.$objtype.'");'."\n";
			}
			else
			{
				$defaults1 .= 'listB.setDefaultOption("'.$k.'","'.($sele ? $sele : $single_select).'");'."\n";
			}
		}

		$rels1 .= 'listB.addOptions("_","Objekti tüüp","capt_new_object"'.");\n";
		$defaults1 .= 'listB.setDefaultOption("_","capt_new_object");'."\n";

		$this->vars(array(
			"parent" => $this->parent,
			"rels1" => $rels1,
			"defaults1" => $defaults1,
		));

		$tb->add_cdata($this->parse());

		$tb->add_cdata(
			html::select(array(
				"options" => (count($this->reltypes) <= 1) ? $this->reltypes :(array('_' => 'Seose tüüp') + $this->reltypes),
				"name" => "reltype",
				"selected" => $this->reltype,
				'onchange' => "listB.populate();",
			))
		);
		$tb->add_cdata('<select NAME="aselect" style="width:200px"><script LANGUAGE="JavaScript">listB.printOptions()</SCRIPT></select>');
		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => "javascript:create_new_object()",
			"tooltip" => t("Lisa uus objekt"),
		));
		if($arr["request"]["srch"] == 1)
		{
			$tb->add_button(array(
				"name" => "search",
				"img" => "search.gif",
				"tooltip" => t("Otsi"),
				"url" => "javascript:if (document.foo.reltype.value!='_') {document.searchform.submit();} else alert('Vali seosetüüp!')",
			));
		}
		else
		{
			$tb->add_button(array(
				"name" => "search",
				"img" => "search.gif",
				"tooltip" => t("Otsi"),
				"url" => "javascript:search_for_object()",
			));
		}
		
		$tb->add_separator();
		
		if (aw_ini_get("config.object_translation") == 1)
		{
			$tb->add_button(array(
				"name" => "translate",
				"tooltip" => t("Tõlgi"),
				"url" => $this->mk_my_orb("create", array("id" => $arr["obj_inst"]->id(), "return_url" => urlencode($arr["request"]["return_url"])), "object_translation"),
				"target" => "_blank",
				"img" => "edit.gif",
			));
			$tb->add_separator();
		}
		
		$tb->add_button(array(
			"name" => "refresh",
			"img" => "refresh.gif",
			"tooltip" => t("Uuenda"),
			"url" => "javascript:window.location.reload()",
		));
		
		
		if($arr["request"]["srch"] == 1)
		{
			if ($this->search->get_opt("rescounter") > 0)
			{
				$tb->add_button(array(
					"name" => "save",
					"tooltip" => "Loo seos(ed)",
					"url" => "javascript:aw_save()",
					"img" => "save.gif",
				));
			}
		}
		else
		{
			$tb->add_button(array(
				"name" => "save",
				"img" => "save.gif",
				"tooltip" => t("Salvesta"),
				"url" => "javascript:document.changeform.submit();",
			));
			
			$tb->add_button(array(
				"name" => "delete",
				"img" => "delete.gif",
				"tooltip" => t("Kustuta seos(ed)"),
				"url" => "javascript:awdelete()",
			));
		}
		return $tb;
	}
	
	function mk_kstring($arr)
	{
		$alls = array();
		foreach($arr as $key => $val)
		{
			$alls[] ='"'.$val.'"';
			//$alls[] ='"'.htmlspecialchars($val).'"';
			$alls[] ='"'.$key.'"';
		}
		return implode(',', $alls);
	}
	
	function _show_relations($arr)
	{
		classload("core/icons");
		$pr = array();
		
		$tb = &$this->_make_toolbar($arr);
		$this->read_template("list_aliases.tpl");
		// table part
		classload("vcl/table");
		$tbl = new vcl_table();
		
		$tbl->parse_xml_def(aw_ini_get("basedir")."/xml/generic_table.xml");
		
		$tbl->define_field(array(
			"name" => "icon",
			"caption" => "",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "25",
		));
		$tbl->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"talign" => "center",
			"sortable" => 1,
		));
		$tbl->define_field(array(
			"name" => "lang",
			"caption" => t("Keel"),
			"talign" => "center",
			"align" => "center",
			"sortable" => 1,
		));
		$tbl->define_field(array(
			"name" => "comment",
			"caption" => t("Muu info"),
			"talign" => "center",
			"sortable" => 1,
		));
		$tbl->define_field(array(
			"name" => "alias",
			"caption" => t("Alias"),
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
		));
		$tbl->define_field(array(
			"name" => "link",
			"caption" => t("Link"),
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
			"nowrap" => "1",
		));
		$tbl->define_field(array(
			"name" => "cache",
			"caption" => t("Cache"),
			"talign" => "center",
			"width" => 50,
			"align" => "center",
			"class" => "celltext",
			"nowrap" => "1",
		));
		$tbl->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
		));
		$tbl->define_field(array(
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
		$tbl->define_field(array(
			"name" => "title",
			"caption" => t("Tüüp"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
		));
		$tbl->define_field(array(
			"name" => "reltype",
			"caption" => t("Seose tüüp"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));
		
		$tbl->define_chooser(array(
			"name" => "check",
			"field" => "id",
		));
		
		$alinks = $arr["obj_inst"]->meta("aliaslinks");
		
		$classes = aw_ini_get("classes");
		foreach($arr["obj_inst"]->connections_from() as $alias)
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
			
			$edfile = $classes[$aclid]["file"];
			if ($aclid == CL_DOCUMENT)
			{
				$edfile = "doc";
			}
			
			$ch = $this->mk_my_orb("change", array("id" => $alias->prop("to"), "return_url" => urlencode($arr["request"]["return_url"])), $edfile);
			$reltype_id = $alias->prop("reltype");
			
			$adat["icon"] = html::img(array(
			"url" => icons::get_icon_url($target_obj),
			));
			
			if ($reltype_id == 0)
			{
				list($astr) = explode(",", $classes[$aclid]["alias"]);
				if ($astr == "")
				{
					list($astr) = explode(",", $classes[$aclid]["old_alias"]);
				}
				$astr = sprintf("#%s%d#", $astr, $alias->prop("idx"));
				$adat["alias"] = sprintf("<input type='text' size='10' value='%s' onClick='this.select()' onBlur='this.value=\"%s\"'>", $astr, $astr);
			}
			
			$adat["link"] = html::checkbox(array(
				"name" => "link[".$alias->prop("to")."]",
				"value" => 1,
				"checked" => $alinks[$alias->prop("to")],
			));
			
			$adat["title"] = $classes[$aclid]["name"];
			
			// for the chooser
			$adat["id"] = $alias->prop("id");
			
			$adat["name"] = html::href(array(
				"url" => $ch,
				"caption" => parse_obj_name($alias->prop("to.name")),
			));
			
			$adat["cache"] = html::checkbox(array(
				"name" => "cache[".$alias->prop("to")."]",
				"value" => 1,
				"checked" => ($alias->prop("cached") == 1)
			));
			
			$type_str = $this->reltypes[$reltype_id];
			if ((aw_ini_get("config.object_translation") == 1) && ($reltype_id == RELTYPE_TRANSLATION))
			{
				$type_str = "tõlge (".$langinfo[$alias->prop("to.lang_id")].")";
			}
			if ((aw_ini_get("config.object_translation") == 1) && ($reltype_id == RELTYPE_ORIGINAL))
			{
				$type_str = "originaal (".$langinfo[$alias->prop("to.lang_id")].")";
			}

			if ($alias->prop("relobj_id"))
			{
				$adat["reltype"] = html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $alias->prop("relobj_id"),"return_url" => $return_url), $classes[$aclid]["file"]),
					"caption" => $type_str,
				));
			}
			else
			{
				$adat["reltype"] = $type_str;
			}
			$tbl->define_data($adat);
		}
		$tbl->set_default_sortby("title");
		$tbl->sort_by();
		$req = $arr["request"];
		unset($req["action"]);
		$reforb = $this->mk_reforb("submit", $req + array("reforb" => 1), $req["class"]);
		$this->vars(array(
			"class_ids" => $this->clid_list,
			"table" => $tbl->draw(),
			"id" => $arr["obj_inst"]->id(),
			"reforb" => $reforb,
			"toolbar" => $tb->get_toolbar(),
			"return_url" => urlencode($arr["request"]["return_url"]),
			"period" => $period,
			"search_url" => aw_ini_get("baseurl").aw_url_change_var(array(
				"return_url" => urlencode($arr["request"]["return_url"]),
				"srch" => 1,
			)),
		));
		$pr["form"] = array(
			"name" => "show_aliases",
			"type" => "text",
			"no_caption" => 1,
			"value" => $this->parse(),
		);
		return $pr;
	}
	
	function process_vcl_property($arr)
	{
		if ($arr["request"]["subaction"] == "delete")
		{
			$to_delete = new aw_array($arr["request"]["check"]);
			foreach($to_delete->get() as $alias_id)
			{
				$c = new connection($alias_id);
				$c->delete();
			}
		}
		elseif($arr["request"]["alias"])
		{
			$alias = $arr["request"]["alias"];
			$reltype = $arr["request"]["reltype"];
			$aliases = explode(",", $alias);
			if ($reltype == "_")
			{
				$reltype = "";
			}
			foreach($aliases as $oalias)
			{
				$arr["obj_inst"]->connect(array(
					"to" => $oalias,
					"reltype" => $reltype,
					"data" => $arr["request"]["data"]
				));
			}
		}
		if($arr["request"]["link"] || $arr["request"]["cache"])
		{
			$arr["obj_inst"]->set_meta("aliaslinks", $arr["request"]["link"]);
			$arr["obj_inst"]->save();
	
			$cache = $arr["request"]["cache"];
			$cache_inst = get_instance("cache");
			foreach($arr["obj_inst"]->connections_from() as $ad)
			{
				if ($ad->prop("cached") != $cache[$ad->prop("to")])
				{
					if (!$cache[$ad->prop("to")])
					{
						$cache_inst->file_invalidate_regex('alias_cache-source-'.$id.'-target-'.$ad->prop("to").'.*');
					}
					$ad->change(array(
						"cached" => $cache[$ad->prop("to")],
					));
				}
			}
		}
	}
}
?>