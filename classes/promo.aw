<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/promo.aw,v 2.36 2003/06/19 09:55:41 kristo Exp $
// promo.aw - promokastid.

/*
	@default group=general
	
	@property caption type=textbox table=objects field=meta method=serialize
	@caption Pealkiri

	@property tpl_lead type=select table=menu group=show
	@caption Template n�itamiseks
	
	@property type type=select table=objects field=meta method=serialize
	@caption Kasti t��p

	@property link type=textbox table=menu
	@caption Link
	
	@property link_caption type=textbox table=objects field=meta method=serialize
	@caption Lingi kirjeldus
	
	@default table=objects
	@default field=meta

	@property no_title type=checkbox ch_value=1 value=1 group=show method=serialize
	@caption Ilma pealkirjata

	@property groups type=select multiple=1 size=15 group=show method=serialize
	@caption Grupid, kellele kasti n�idata

	@property use_fld_tpl type=checkbox ch_value=1 group=show method=serialize
	@caption Kasuta dokumendi asukoha templatet
	
	@property all_menus type=checkbox ch_value=1 value=1 group=menus method=serialize
	@caption N�ita igal pool

	@property section type=text callback=callback_get_menus group=menus method=serialize
	@caption Vali men��d, mille all kasti n�idata
	
	@property last_menus type=text callback=callback_get_doc_sources group=menus method=serialize
	@caption Vali men��d, mille alt viimaseid dokumente v�etakse

	@property ndocs type=textbox size=4 group=menus table=menu field=ndocs 
	@caption Mitu viimast dokumenti

	@classinfo corefields=name,comment,status
	@classinfo relationmgr=yes
	@classinfo syslog_type=ST_PROMO

	@tableinfo menu index=id master_table=objects master_index=oid

	@groupinfo general caption=�ldine
	@groupinfo menus caption=Kaustad
	@groupinfo show caption=N�itamine
			
*/
define("RELTYPE_ASSIGNED_MENU",1);
define("RELTYPE_DOC_SOURCE",2);
class promo extends class_base
{
	function promo()
	{
		$this->init(array(
			"clid" => CL_PROMO,
			"tpldir" => "promo",
		));
		lc_load("definition");
		$this->lc_load("promo","lc_promo");
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_ASSIGNED_MENU || $args["reltype"] == RELTYPE_DOC_SOURCE)
		{
			return array(CL_PSEUDO);
		}
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_ASSIGNED_MENU => "n�ita men�� juures",
			RELTYPE_DOC_SOURCE => "v�ta dokumente selle men�� alt",
		);
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK; 
		switch($data["name"])
		{
			case "tpl_lead":
				// kysime infot lyhikeste templatede kohta
				$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
				$this->db_query($q);
				$short_templates = array("0" => "Vali template");
				$def_name = "";
				while($tpl = $this->db_fetch_row()) 
				{
					if ($tpl["filename"] == aw_ini_get("promo.default_tpl"))
					{
						$def_name = $tpl["id"];
						$tpl["name"] .= " (V) ";
					}
					$short_templates[$tpl["id"]] = $tpl["name"];
				};
				if ($def_name != "" && !$data["value"])
				{
					$data["value"] = $def_name;
				}
				$data["options"] = $short_templates;
				break;
	
			case "type":
				$data["options"] = array(
					"0" => "Vasakul",
					"1" => "Paremal",
					"2" => "�leval",
					"3" => "All",
					"scroll" => "Skrolliv",
				);
				break;

			case "groups":
				$u = get_instance("users");
				$data["options"] = $u->get_group_picker(array(
					"type" => array(GRP_REGULAR,GRP_DYNAMIC),
				));
				break;

		}
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$meta = &$args["metadata"];
                $retval = PROP_OK;
		if ($data["name"] == "section")
		{
			$meta["section_include_submenus"] = $args["form_data"]["include_submenus"];
		};

		if ($data["name"] == "ndocs")
		{
			$args["metadata"]["as_name"] = $args["form_data"]["as_name"];
		}
		return $retval;

	}

	function callback_get_menus($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();
		$section_include_submenus = $args["obj"]["meta"]["section_include_submenus"];
		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
                $this->t = new aw_table(array(
                        "prefix" => "promo_menus",
                ));
                $this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
                $this->t->define_field(array(
                        "name" => "oid",
                        "caption" => "ID",
                        "talign" => "center",
                        "align" => "center",
                        "nowrap" => "1",
                        "width" => "30",
                ));
                $this->t->define_field(array(
                        "name" => "name",
                        "caption" => "Nimi",
                        "talign" => "center",
                ));
                $this->t->define_field(array(
                        "name" => "check",
                        "caption" => "k.a. alammen��d",
                        "talign" => "center",
			"width" => 80,
			"align" => "center",
                ));

		$alias_reltype = $args["obj"]["meta"]["alias_reltype"];
		// and from this array, I need to get the list of objects that
		// have the reltype RELTYPE_ASSIGNED_MENU
		if (is_array($alias_reltype))
		{
			$menu_ids = array_filter($alias_reltype,create_function('$val','if ($val==RELTYPE_ASSIGNED_MENU) return true;'));
		};

		if (sizeof($menu_ids) > 0)
		{
			// now get those objects and put them into table
			$q = sprintf("SELECT oid,name,status FROM objects
					LEFT JOIN menu ON (objects.oid = menu.id) WHERE oid IN (%s)",join(",",array_keys($menu_ids)));

			$this->db_query($q);
			while($row = $this->db_next())
			{
				// it shouldn't be too bad, cause get_object is cached.
				// still, it sucks.
				$this->save_handle();
				$chain = array_reverse($this->get_obj_chain(array(
					"oid" => $row["oid"],
				)), true);
				$path = join("/",array_slice($chain,-3));
				$this->restore_handle();
				$this->t->define_data(array(
					"oid" => $row["oid"],
					"name" => $path,
					"check" => html::checkbox(array(
						"name" => "include_submenus[$row[oid]]",
						"value" => $row["oid"],
						"checked" => $section_include_submenus[$row["oid"]],
					)),
				));
			};
		};
		
		$nodes[$prop["name"]] = array(
			"type" => "text",
			"caption" => $prop["caption"],
			"value" => $this->t->draw(),
		);
		return $nodes;
	}

	function callback_get_doc_sources($args = array())
	{
		$prop = $args["prop"];
		$nodes = array();
		// now I have to go through the process of setting up a generic table once again
		load_vcl("table");
                $this->t = new aw_table(array(
                        "prefix" => "promo_menus",
                ));
                $this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
                $this->t->define_field(array(
                        "name" => "oid",
                        "caption" => "ID",
                        "talign" => "center",
                        "align" => "center",
                        "nowrap" => "1",
                        "width" => "30",
                ));
                $this->t->define_field(array(
                        "name" => "name",
                        "caption" => "Nimi",
                        "talign" => "center",
                        //"nowrap" => "1",
                        "sortable" => 1,
                ));
                $this->t->define_field(array(
                        "name" => "as_name",
                        "caption" => "Pane pealkirjaks",
                        "talign" => "center",
                        "align" => "center",
                ));

		$alias_reltype = $args["obj"]["meta"]["alias_reltype"];
		// and from this array, I need to get the list of objects that
		// have the reltype RELTYPE_DOC_SOURCE
		if (is_array($alias_reltype))
		{
			$menu_ids = array_filter($alias_reltype,create_function('$val','if ($val==RELTYPE_DOC_SOURCE) return true;'));
		};

		if (sizeof($menu_ids) > 0)
		{

			// now get those objects and put them into table
			$q = sprintf("SELECT oid,name,status FROM objects
					LEFT JOIN menu ON (objects.oid = menu.id) WHERE oid IN (%s)",join(",",array_keys($menu_ids)));

			$this->db_query($q);
			while($row = $this->db_next())
			{
				// it shouldn't be too bad, cause get_object is cached.
				// still, it sucks.
				$this->save_handle();
				$chain = array_reverse($this->get_obj_chain(array(
					"oid" => $row["oid"],
				)),true);
				$this->restore_handle();
				$path = join("/",array_slice($chain,-3));
				$this->t->define_data(array(
					"oid" => $row["oid"],
					"name" => $path,
					"as_name" => html::radiobutton(array(
						"name" => "as_name",
						"value" => $row["oid"],
						"checked" => ($args["obj"]["meta"]["as_name"] == $row["oid"])
					))
				));
			};
			$this->t->define_data(array(
				"oid" => 0,
				"name" => "",
				"as_name" => html::radiobutton(array(
					"name" => "as_name",
					"value" => 0,
					"checked" => (!$args["obj"]["meta"]["as_name"])
				))
			));
		};
		
		$nodes[$prop["name"]] = array(
			"type" => "text",
			"caption" => $prop["caption"],
			"value" => $this->t->draw(),
		);

		return $nodes;
	}

	function callback_pre_edit($args = array())
	{
		$id = $args["object"]["oid"];
		$menu = $this->get_menu($id);
		// first check, whether the promo box was in the very old format (contained serialized data
		// in the comment field
		$check1 = aw_unserialize($menu["comment"]);
		$check2 = aw_unserialize($menu["sss"]);
		if (is_array($check1) || is_array($check2))
		{
			$convert_url = $this->mk_my_orb("promo_convert",array(),"converters");
			print "See objekt on vanas formaadis. Enne kui seda muuta saab, tuleb k�ik s�steemis olevad promokastis uude formaati konvertida. <a href='$convert_url'>Kliki siia</a> konversiooni alustamiseks";
			exit;
		};
		
		// now, check, whether we have to convert the current contents of comment and sss to relation objects
		// we use a flag in object metainfo for that

		// and still, it would be nice if we could convert all the promo boxes at once.
		// then I wouldn't have to check for this shit each fucking time, for each
		// fucking promo box. But maybe it's not as bad as I imagine it
		if ($args["object"]["meta"]["uses_relationmgr"])
		{
			return true;
		};
		
		$id = $args["object"]["oid"];
		$oldaliases = $this->get_aliases_for($id,CL_PSEUDO);
		$flatlist = array();
		$alias_reltype = $args["object"]["meta"]["alias_reltype"];
		foreach($oldaliases as $alias)
		{
			$flatlist[$alias["target"]] = $alias_reltype[$alias["target"]];
		};

		// basically, I have to get a list of menus in $args["object"]["meta"]["section"]
		// and create a relation of type RELTYPE_ASSIGNED_MENU for each of those

		$sections = $args["object"]["meta"]["section"];
		if ( is_array($sections) && (sizeof($sections) > 0) )
		{
			foreach($sections as $key => $val)
			{
				if (!$flatlist[$val])
				{
					$alias_reltype[$val] = RELTYPE_ASSIGNED_MENU;
					$this->add_alias($id,$val,CL_PSEUDO);
				};
			};
		}

		// then I have to get a list of menus in $args["object"]["meta"]["last_menus"] and
		// create a relation of type RELTYPE_DOC_SOURCE for each of those.

		// I also want to keep the old representation around, so that old code keeps working
		$last_menus = $args["object"]["meta"]["last_menus"];
		if ( is_array($last_menus) && (sizeof($last_menus) > 0) )
		{
			foreach($last_menus as $key => $val)
			{
				if (!$flatlist[$val])
				{
					$alias_reltype[$val] = RELTYPE_DOC_SOURCE;
					$this->add_alias($id,$val,CL_PSEUDO);
				};
			};
		}

		// update reltype information, that is only if there is anything to update
		if (sizeof($alias_reltype) > 0)
		{
			$this->upd_object(array(
				"oid" => $args["object"]["oid"],
				"metadata" => array(
					"alias_reltype" => $alias_reltype,
					"uses_relationmgr" => 1,
				),
			));
		};
	}

	function callback_pre_save($args = array())
	{
		$objdata = &$args["objdata"];
		if (!$objdata["type"])
		{
			$objdata["type"] = MN_PROMO_BOX;
		};
	}

	function callback_on_submit_relation_list($args = array())
	{
		// this is where we put data back into object metainfo, for backwards compatibility
		$id = $args["id"];
		$obj = $this->get_object($id);
		
		$oldaliases = $this->get_aliases_for($id,CL_PSEUDO);
		
		$section = array();
		$last_menus = array();

		$alias_reltype = $obj["meta"]["alias_reltype"];
		$new_alias_reltype = array();
		foreach($oldaliases as $alias)
		{
			if ($alias_reltype[$alias["target"]] == RELTYPE_ASSIGNED_MENU)
			{
				$section[$alias["target"]] = $alias["target"];
				$new_alias_reltype[$alias["target"]] = RELTYPE_ASSIGNED_MENU;
			};
			
			if ($alias_reltype[$alias["target"]] == RELTYPE_DOC_SOURCE)
			{
				$last_menus[$alias["target"]] = $alias["target"];
				$new_alias_reltype[$alias["target"]] = RELTYPE_DOC_SOURCE;
			};
		};

		// serializes makes empty array into array("0" => "0") and this is bad in this
		// case, so we work around it
		if (sizeof($last_menus) == 0)
		{
			$last_menus = "";
		};

		$this->upd_object(array(
			"oid" => $id,
			"metadata" => array(
				"section" => $section,
				"last_menus" => $last_menus,
				"alias_reltype" => $new_alias_reltype,
			),
		));
	}

	function callback_on_addalias($args = array())
	{
		$obj_list = explode(",",$args["alias"]);
		$obj = $this->get_object($args["id"]);

		if ($args["reltype"] == RELTYPE_ASSIGNED_MENU)
		{
			$var = "section";
		}
		else
		if ($args["reltype"] == RELTYPE_DOC_SOURCE)
		{
			$var = "last_menus";
		};

		$data = $obj["meta"][$var];

		foreach($obj_list as $val)
		{
			$data[$val] = $val;
		};

		$this->upd_object(array(
			"oid" => $args["id"],
			"metadata" => array(
				$var => $data,
			),
		));
	}


	function parse_alias($args = array())
	{
		$alias = $args["alias"];
		$ob = $this->get_object($alias["target"]);

		$this->read_template("default.tpl");

		$me = get_instance("contentmgmt/site_content");
		if (is_array($ob['meta']['last_menus']))
		{
			$def = array();
			foreach($ob['meta']['last_menus'] as $menu)
			{
				$_t = new aw_array($me->get_default_document($menu,true));
				$def += $_t->get();
			}
			$def = new aw_array($def);
		}
		else
		{
			$def = new aw_array($me->get_default_document($alias["target"],true));
		}

		$_ob = aw_ini_get("menuedit.document_list_order_by");
		if ($_ob != "")
		{
			$_ids = $def->to_sql();
			$_q = "SELECT objects.oid as oid FROM objects  LEFT JOIN documents ON documents.docid=objects.oid WHERE objects.oid IN ($_ids) ORDER BY $_ob";
			$this->db_query($_q);
			$def = array();
			while ($row = $this->db_next())
			{
				$def[] = $row["oid"];
			}
			$def = new aw_array($def);
		}

		$ndocs = $this->db_fetch_field("SELECT ndocs FROM menu WHERE id = '$alias[target]'", "ndocs");
		if ($ndocs)
		{
			$def = new aw_array(array_slice($def->get(), 0, $ndocs));
		}

		$content = "";
		$doc = get_instance("document");

		$mn = $this->get_menu($alias["target"]);

		$q = "SELECT filename FROM template WHERE id = '$mn[tpl_lead]'";
		$row = $this->db_fetch_row($q);

		$parms = array(
			"leadonly" => 1,
			"showlead" => 1,
			"boldlead" => 1,
			"no_strip_lead" => 1,
		);
		if (!$ob['meta']['use_fld_tpl'])
		{
			$parms["tpl"] = $row["filename"];
		}
		else
		{
			$parms["tpl_auto"] = 1;
		}
	
		foreach($def->get() as $key => $val)
		{
			$_parms = $parms;
			$_parms["docid"] = $val;
			$content .= $doc->gen_preview($_parms);
		}

		if ($ob['meta']['as_name'] && $ob['meta']["caption"] == "")
		{
			$ob["meta"]['caption'] = $this->db_fetch_field("SELECT name FROM objects WHERE oid = '".$ob['meta']['as_name']."'","name");
		}

		$this->vars(array(
			"title" => $ob['meta']["caption"],
			"content" => $content,
		));

		if (!$ob['meta']['no_title'])
		{
			$this->vars(array(
				"SHOW_TITLE" => $this->parse("SHOW_TITLE")
			));
		}
		return $this->parse();
	}
}
?>
