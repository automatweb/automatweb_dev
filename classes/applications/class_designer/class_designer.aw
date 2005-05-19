<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/class_designer.aw,v 1.16 2005/05/19 14:36:02 kristo Exp $
// class_designer.aw - Vormidisainer 
/*

@classinfo syslog_type=ST_CLASS_DESIGNER relationmgr=yes

@default table=objects
@default group=general

@property is_registered type=checkbox ch_value=1 field=meta method=serialize
@caption Klass on registreeritud

@property infomsg type=text store=no
@caption Info

@property preview type=text store=no
@caption 

@default group=settings
@property reg_class_id type=text table=aw_class field=class_id
@caption CLID
@comment Kui klass on registreeritud, siis näitab klassile määratud ID-d

@property object_name type=select field=meta method=serialize
@caption Objekti nime omadus
@comment Siit valitud vormi väljast võetakse objektile nimi

@property can_add type=checkbox ch_value=1 table=aw_class
@caption Saab lisada
@comment Kui see märgistada, siis saab klassi lisada rohelise nupu menüü kaudu

@property class_folder type=select table=aw_class
@caption Asukoht lisamismenüüs
@comment Läheb arvesse ainult siis, kui klassi 'saab lisada'

@default field=meta
@default method=serialize

@property relationmgr type=checkbox ch_value=1 default=1
@caption Seostehaldur
@comment Kas klassil on seostehaldur?

@property no_comment type=checkbox ch_value=1
@caption Kommentaari muuta ei saa

@property no_status type=checkbox ch_value=1
@caption Aktiivsust muuta ei saa
@comment Kui see märgistada, siis ei saa klassi aktiivsust muuta, ta on alati aktiivne

@default group=classdef
@property classdef type=text store=no no_caption=1
@caption Klassi definitsioon

@default group=designer

@layout hbox1 type=hbox group=designer

@property designer_toolbar type=toolbar no_caption=1 store=no parent=hbox1
@caption Designer toolbar

@layout vbox1 type=vbox group=designer
@layout hbox2 type=hbox group=designer parent=vbox1 width=30%:70%

@property layout_tree type=treeview parent=hbox2 no_caption=1
@caption Grupid

@property element_list type=table no_caption=1 parent=hbox2 no_caption=1
@caption Elemendid

@layout hbox3 type=hbox group=designer

@property helper type=text no_caption=1 parent=hbox3
@property group_parent type=hidden 
@property tmp_name type=hidden
@property element_type type=hidden

@default group=relations

	@property relations_mgr type=releditor reltype=RELTYPE_RELATION mode=manager no_caption=1 props=name,r_class_id,value table_fields=name,r_class_id,value 
	@caption Seosed

@groupinfo settings caption="Seaded"
@groupinfo designer caption="Disainer" submit=no
@groupinfo relations caption="Seosed" submit=no
@groupinfo classdef caption="Klassi definitsioon" submit=no


@reltype RELATION value=1 clid=CL_CLASS_DESIGNER_RELATION
@caption seos

@tableinfo aw_class index=aw_id master_table=objects master_index=brother_of

*/

class class_designer extends class_base
{
	function class_designer()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer",
			"clid" => CL_CLASS_DESIGNER
		));
		
		$this->elements = array(
			CL_PROPERTY_TEXTBOX,CL_PROPERTY_CHOOSER,
			CL_PROPERTY_CHECKBOX,CL_PROPERTY_TABLE,
			CL_PROPERTY_TEXTAREA,CL_PROPERTY_SELECT,
			CL_PROPERTY_TREE,CL_PROPERTY_TOOLBAR
		);

		$this->all_els = $this->elements;
		$this->all_els[] = CL_PROPERTY_GROUP;
		$this->all_els[] = CL_PROPERTY_GRID;
		
		$this->gen_folder = $this->cfg["site_basedir"]."/files/classes";
	}

	function callback_mod_reforb(&$arr)
	{
		$arr["return_url"] = aw_ini_get("baseurl").aw_global_get("REQUEST_URI");
		$arr["group_parent"] = $_GET["group_parent"] ? $_GET["group_parent"] : $arr["id"];
		$arr["register_under"] = $_REQUEST["register_under"];
	}

	function callback_pre_edit($arr)
	{
		if ($arr["request"]["group"] == "designer")
		{
			$can_add = array(
				"group" => false,
				"grid" => false,
				"element" => false,
			);

			if (empty($arr["request"]["group_parent"]))
			{
				$group_parent = $arr["obj_inst"]->id();
				// cannot add those to top level
				$can_add["group"] = true;
			}
			else
			{
				$group_parent = $arr["request"]["group_parent"];
				$grp_p = new object($group_parent);
				$grp_clid = $grp_p->class_id();
				if ($grp_clid == CL_PROPERTY_GROUP)
				{
					$can_add["grid"] = true;
					$can_add["group"] = true;
				}
				elseif ($grp_clid == CL_PROPERTY_GRID)
				{
					$can_add["element"] = true;
				};
			};

			$this->can_add = $can_add;
			$this->group_parent = $group_parent;
		};
	}


	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "designer_toolbar":
				$this->create_designer_toolbar(&$arr);
				break;

			case "layout_tree":
				$this->create_layout_tree(&$arr);
				break;

			case "element_list":
				$this->create_element_list($arr);
				break;

			case "helper":
				$this->read_template("helper_functions.tpl");
				$prop["value"] = $this->parse();
				break;

			case "group_parent":
				$prop["value"] = $this->group_parent;
				break;

			case "classdef":
				$prop["value"] = "<pre>" . htmlspecialchars($this->gen_classdef($arr)) . "</pre>";
				break;

			case "visualize":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("view",array("class" => $arr["obj_inst"]->id()),"class_visualizer"),
					"caption" => t("Visualiseeri"),
				));
				break;

			case "is_registered":
				if (1 == $prop["value"])
				{
					$prop["type"] = "text";
					$prop["value"] = "<b>Klass on registreeritud!</b>";
				};
				break;

			case "object_name":
				$otree = new object_tree(array(
					"parent" => $arr["obj_inst"],
				));
				$olist = $otree->to_list();
				// not much point in using a checkbox as name is there?
				foreach($olist->arr() as $o)
				{
					if (CL_PROPERTY_TEXTBOX == $o->class_id())
					{
						$prop["options"][$o->id()] = $o->name();
					};

				};
				break;

			case "infomsg":
				$clx = aw_ini_get("classes");
				if (!is_writable($this->gen_folder))
				{
					$prop["value"] = t("Väljundkataloog ei ole kirjutatav!");
				};
				if ($prop["value"] == "")
				{
					return PROP_IGNORE;
				}
				break;

			case "class_folder":
				$prop["options"] = $this->gen_folder_tree();
				if (!$prop["value"])
				{
					$prop["value"] = $arr["obj_inst"]->meta("register_under");
				}
				break;

			case "preview":
				if (!$arr["obj_inst"]->prop("reg_class_id"))
				{
					return PROP_IGNORE;
				}
				// find some objects
				
				$ol = new object_list(array("class_id" => $arr["obj_inst"]->prop("reg_class_id"),"limit" => 2));
				if ($ol->count())
				{
					$o = $ol->begin();
					$prop["value"] = html::get_change_url($o->id(), array("return_url" => get_ru()), t("Eelvaade"));
				}
				else
				{
					$prop["value"] = html::get_new_url($arr["obj_inst"]->prop("reg_class_id"), $arr["obj_inst"]->id(), array("return_url" => get_ru()), t("Eelvaade"));
				}
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "is_registered":
				if (1 == $prop["value"])
				{
					$this->do_register_class($arr);
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

		}
		return $retval;
	}	

	/** Creates a hierarchy of groups and grids
	**/
	function create_layout_tree(&$arr)
	{
		$o = $arr["obj_inst"];
		$tree = &$arr["prop"]["vcl_inst"];
		$oid = $o->id();
		$tree->add_item(0,array(
			"name" => $o->name(),
			"id" => $oid,
			"is_open" => 1,
			"url" => $this->mk_my_orb("change",array(
				"id" => $oid,
				"group" => "designer",
			)),
		));

		$el_tree = new object_tree(array(
			"parent" => $oid,
			"class_id" => array(CL_PROPERTY_GROUP,CL_PROPERTY_GRID),
			"lang_id" => array(),
			"site_id" => array(),

		));
		$el_list = $el_tree->to_list();
		$ellist = $el_list->arr();
		$this->__elord = $o->meta("element_ords");
		usort($ellist, array(&$this, "__ellist_comp"));

		foreach($ellist as $el)
		{
			$clid = $el->class_id();
			$iconurl = "";
			// XXX: use class icons
			if ($clid == CL_PROPERTY_GRID)
			{
				$iconurl = "/automatweb/images/icons/merge_down.png";
			};
			$el_id = $el->id();
			$tree->add_item($el->parent(),array(
				"name" => $el->name(),
				"id" => $el_id,
				"url" => $this->mk_my_orb("change",array(
					"id" => $oid,
					"group" => "designer",
					"group_parent" => $el_id,
				)),
				"iconurl" => $iconurl,
				"is_open" => 1,
			));
		};

		$tree->set_selected_item($this->group_parent);

	}

	/** Helper toolbar to deal with elements
	**/
	function create_designer_toolbar(&$arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "new",
			"img" => "new.gif",
		));


		// XXX: siin on vaja pidada tracki selle üle milliseid elemente parajasti lisada saab
		// gridi saab lisada ainult siis kui parentiks on grupp
		// elementi saab lisada ainult siis kui parentiks on grid
		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Uus tab"),
			"link" => $this->mk_my_orb("create_group", array(
				"group_parent" => $this->group_parent, 
				"return_url" => get_ru(),
				"id" => $arr["obj_inst"]->id()
			)),
			"disabled" => !$this->can_add["group"],
		));

		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => t("Uus grid"),
			"link" => $this->mk_my_orb("create_grid", array(
				"group_parent" => $this->group_parent, 
				"return_url" => get_ru(),
				"id" => $arr["obj_inst"]->id()
			)),
			"disabled" => !$this->can_add["grid"],
		));

		$tb->add_menu_separator(array(
			"parent" => "new"
		));


		$clinf = aw_ini_get("classes");
		foreach($this->elements as $element)
		{
			$tb->add_menu_item(array(
				"parent" => "new",
				"text" => $clinf[$element]["name"],
				"link" => $this->mk_my_orb("create_element", array(
					"element_type" => $element, 
					"group_parent" => $this->group_parent, 
					"return_url" => get_ru(),
					"id" => $arr["obj_inst"]->id()
				)),
				"disabled" => !$this->can_add["element"],
			));
		};


		$tb->add_separator();

		$tb->add_button(array(
			"name" => "save",
			"action" => "save",
			"tooltip" => t("Save"),
			"action" => "save_elements",
			"img" => "save.gif",
		));

		$tb->add_separator();

		$tb->add_button(array(
			"name" => "cut",
			"action" => "cut",
			"tooltip" => t("Cut"),
			"action" => "cut",
			"img" => "cut.gif",
		));

		if (count(safe_array($_SESSION["cd_cut"])) > 0 && $this->can_add["element"])
		{
			$tb->add_button(array(
				"name" => "paste",
				"action" => "paste",
				"tooltip" => t("Paste"),
				"action" => "paste",
				"img" => "paste.gif",
			));
		}

		$tb->add_button(array(
			"name" => "delete",
			"action" => "delete",
			"tooltip" => t("Delete"),
			"action" => "delete",
			"img" => "delete.gif",
			"confirm" => t("Kustutada valitud objektid?"),
		));

	}

	function create_element_list(&$arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "ordbox",
			"caption" => t("Jrk"),
			"align" => "center",
			"width" => 50,
		));
		$t->define_field(array(
			"name" => "namebox",
			"caption" => t("Nimi"),
			"width" => 200,
		));
		$t->define_field(array(
			"name" => "class_id",
			"caption" => t("Tüüp"),
			"width" => "100",
		));
		$t->define_field(array(
			"name" => "edit",
			"caption" => t("Muuda"),
			"align" => "center",
			"width" => 100,
		));
		
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		
		//$t->set_sortable(false);
	
		$o = $arr["obj_inst"];

		$elist = new object_list(array(
			"parent" => $this->group_parent,
			"class_id" => $this->all_els
		));

		$clinf = aw_ini_get("classes");

		$elords = $arr["obj_inst"]->meta("element_ords");

		foreach($elist->arr() as $element)
		{
			$el_id = $element->id();
			$el_clid = $element->class_id();
			$elname = $element->name();

			$t->define_data(array(
				"namebox" => html::textbox(array(
					"name" => "name[${el_id}]",
					"size" => 30,
					"value" => $elname,
				)),
				"id" => $el_id,
				"class_id" => $clinf[$el_clid]["name"],
				"ord" => $elords[$element->id()],
				"ordbox" => html::textbox(array(
					"name" => "ord[${el_id}]",
					"size" => 2,
					"value" => $elords[$element->id()],
				)),
				"edit" => html::href(array(
					"caption" => t("Muuda"),
					"url" => $this->mk_my_orb("change",array("id" => $el_id, "return_url" => urlencode(aw_global_get("REQUEST_URI"))),$el_clid),
				)),
			));
		};

		$t->set_numeric_field("ord");
		$t->set_default_sortby("ord");
		$t->sort_by();
	}

	/** 
		@attrib name=create_group

		@param id required type=int acl=view
		@param group_parent required
		@param return_url required

	**/
	function create_group($arr)
	{
		//print "inside create_group<br>";
		// group_parent on see mille alla teha
		// tmp_name on uue grupi nimi ... so what could be easier
		$g = new object();
		$g->set_class_id(CL_PROPERTY_GROUP);
		// XX: check whether we are allowed to add groups here
		$g->set_parent($arr["group_parent"]);
		$g->set_status(STAT_ACTIVE);

		$ol = new object_list(array("class_id" => CL_PROPERTY_GROUP, "parent" => $arr["group_parent"]));

		$g->set_name(t("Grupp ").($ol->count()+1));
		$g->save();

		$o = obj($arr["id"]);
		$elo = safe_array($o->meta("element_ords"));
		$elo[$g->id()] = count($elo) ? max(array_values($elo)) + 1 : 1;
		$o->set_meta("element_ords", $elo);
		$o->save();

		return html::get_change_url($g->id(), array("return_url" => urlencode($arr["return_url"])));
	}

	/**
		@attrib name=create_grid

		@param id required type=int acl=view
		@param group_parent required
		@param return_url required

	**/
	function create_grid($arr)
	{
		$g = new object();
		$g->set_class_id(CL_PROPERTY_GRID);
		$g->set_parent($arr["group_parent"]);
		// XX: check whether we are allowed to add grids here
		$g->set_status(STAT_ACTIVE);
		$g->set_prop("grid_type",0);

		$ol = new object_list(array("class_id" => CL_PROPERTY_GRID, "parent" => $arr["group_parent"]));

		$g->set_name(t("vbox ").($ol->count() + 1));
		$g->save();

		$o = obj($arr["id"]);
		$elo = safe_array($o->meta("element_ords"));
		$elo[$g->id()] = count($elo) ? max(array_values($elo)) + 1 : 1;
		$o->set_meta("element_ords", $elo);
		$o->save();

		return html::get_change_url($g->id(), array("return_url" => urlencode($arr["return_url"])));
	}

	/**
		@attrib name=create_element
		
		@param id required type=int acl=view
		@param element_type required
		@param group_parent required
		@param return_url required
	**/
	function create_element($arr)
	{
		// XX: check whether we are allowed to add elements here
		// parent on group_parent
		// class_id on el_id

		// XX: check whether this is allowed class_id
		$e = new object();
		$e->set_class_id($arr["element_type"]);
		$e->set_parent($arr["group_parent"]);
		$e->set_status(STAT_ACTIVE);

		$ol = new object_list(array("class_id" => $arr["element_type"], "parent" => $arr["group_parent"]));
		$cl = aw_ini_get("classes");

		$e->set_name($cl[$arr["element_type"]]["name"]." ".($ol->count() + 1));
		$e->save();

		$o = obj($arr["id"]);
		$elo = safe_array($o->meta("element_ords"));
		$elo[$e->id()] = count($elo) ? max(array_values($elo)) + 1 : 1;
		$o->set_meta("element_ords", $elo);
		$o->save();

		return html::get_change_url($e->id(), array("return_url" => urlencode($arr["return_url"])));
	}

	/**
		@attrib name=save_elements
	**/
	function save_elements($arr)
	{
		$o = obj($arr["id"]);
		$ords = safe_array($o->meta("element_ords"));
		foreach(safe_array($arr["ord"]) as $elid => $elord)
		{
			$ords[$elid] = $elord;
		}
		$o->set_meta("element_ords", $ords);
		$o->save();

		return $arr["return_url"];

	}

	function __ellist_comp($a, $b)
	{
		$a_o = $this->__elord[$a->id()];
		$b_o = $this->__elord[$b->id()];

		if ($a_o == $b_o)
		{
			return 0;
		}
		return ($a_o > $b_o ? 1 : -1);
	}

	function gen_classdef($arr)
	{
		$c = $arr["obj_inst"];
		$cltree = new object_tree(array(
			"parent" => $c,
		));
		$cl_list = $cltree->to_list();
		$rv = "";
		$grps = "";
		$clinf = aw_ini_get("classes");
		$clname = $this->_valid_id($c->name());

		$path = aw_ini_get("basedir") . "/install/class_template/classes/base.aw";
		$clsrc = file_get_contents($path);
		$clid = "CL_" . strtoupper($this->_valid_id($c->name()));

		$clid = $c->prop("reg_class_id");



		$clsrc = str_replace("__classname",$clname,$clsrc);
		$clsrc = str_replace("__name",$c->name(),$clsrc);
		$clsrc = str_replace("__classdef",$clid,$clsrc);

		$gpblock = $spblock = "";
		$methods = "";

		// sort elements according to order in metadata
		$this->__elord = $c->meta("element_ords");
		$ellist = $cl_list->arr();
		usort($ellist, array(&$this, "__ellist_comp"));

		$saver = "";

		$saveable = array(CL_PROPERTY_TEXTBOX,CL_PROPERTY_TEXTAREA,CL_PROPERTY_CHOOSER,CL_PROPERTY_CHECKBOX);
		$name_prop = $c->prop("object_name");

		foreach($ellist as $el)
		{
			$el_clid = $el->class_id();
			$name = $el->name();
			if (in_array($el_clid,$this->elements))
			{
				$parent = new object($el->parent());
				$grandparent = new object($parent->parent());
				$sys_name = $this->_valid_id($name);
				$group_name = $this->_valid_id($grandparent->name());
				$eltype = strtolower(str_replace("CL_PROPERTY_","",$clinf[$el_clid]["def"]));
				$rv .= "@property ${sys_name} type=${eltype} group=${group_name}";
				$inst = $el->instance();
				$generate_methods = array();
					
				if (method_exists($inst,"generate_get_property"))
				{
					$gpdata = $inst->generate_get_property(array(
						"id" => $el->id(),
						"name" => $sys_name,
					));
					if (strlen($gpdata["get_property"]) > 0)
					{
						$gpblock .= $gpdata["get_property"];
					};
					if (is_array($gpdata["generate_methods"]))
					{
						$generate_methods = array_merge($generate_methods,$gpdata["generate_methods"]);
					};


				};

				if ($el_clid == CL_PROPERTY_CHOOSER)
				{
					if ($el->prop("orient") == 1)
					{
						$rv .= " orient=vertical";
					};
					if ($el->prop("multiple") == 1)
					{
						$rv .= " multiple=1";
					};
				};

				if ($el_clid == CL_PROPERTY_TEXTBOX)
				{
					if ($el->prop("size"))
					{
						$rv .= " size=" . $el->prop("size");
					};
				};
				$rv .= "\n";
				$rv .= "@caption $name\n\n";


				if (sizeof($generate_methods) > 0 && method_exists($inst,"generate_method"))
				{
					foreach($generate_methods as $method_name)
					{
						$methods .= $inst->generate_method(array(
							"id" => $el->id(),
							"name" => $method_name,
						));
					};
					//print "additionally generate methods";
					//arr($generate_methods);
				};
				$spblock .= "\n\t\t\tcase '${sys_name}':\n";
				$spblock .= "\t\t\t\t\$retval = PROP_IGNORE;\n";
				$spblock .= "\t\t\t\tbreak;\n";

				if (in_array($el_clid,$saveable))
				{
					$saver .= "\t\t\$o->set_meta('${sys_name}',\$arr[\"request\"][\"${sys_name}\"]);\n";
					if ($el->id() == $name_prop)
					{
						$saver .= "\t\t\$o->set_name(\$arr[\"request\"][\"${sys_name}\"]);\n";
					};
				};

			};
			if ($el_clid == CL_PROPERTY_GROUP)
			{
				$grpid = $this->_valid_id($name);
				$grps .= "@groupinfo $grpid caption=\"".($el->prop("caption") != "" ? $el->prop("caption") : $name)."\"\n";
			};
		};
		
		if ($c->prop("relationmgr") == 1)
		{
			$gpblock .= "\n\t\t\tcase 'relationmgr':\n";
			$gpblock .= "\t\t\t\t\$this->configure_relationmgr(\$arr);\n";
			$gpblock .= "\t\t\t\t\$retval = PROP_OK;\n";
			$gpblock .= "\t\t\t\tbreak;\n";
		
			$methods .= $this->generate_relationmgr_config($arr["obj_inst"]->id());

			$rv .= $this->generate_reltypes($arr["obj_inst"]->id());

		};

		$methods .= "\tfunction callback_pre_save(\$arr)\n";
		$methods .= "\t{\n";
		$methods .= "\t\t\$o = \$arr[\"obj_inst\"];\n";
		$methods .= $saver;

		$methods .= "\t}\n";
		$clsrc = str_replace("//-- get_property --//",$gpblock,$clsrc);
		$clsrc = str_replace("//-- set_property --//",$spblock,$clsrc);
		$clsrc = str_replace("/*/* --remove--","",$clsrc);
		$clsrc = str_replace("--remove-- */","",$clsrc);
		$clsrc = str_replace("//-- methods --//",$methods,$clsrc);
		$clsrc = str_replace("@default group=general",$rv . $grps,$clsrc);
		return $clsrc;
	}

	/** generates code for configuring relation manager
	**/
	function generate_relationmgr_config($id)
	{
		$o = new object($id);
		//$clsid = aw_global_get("class");
		$clsid = $o->id();
		$clso = new object($clsid);
		// iga seostatud objekt annab ühe välja vasakusse selecti
		$conns = $clso->connections_from(array(
			"type" => "RELTYPE_RELATION",
		));
		$classes = aw_ini_get("classes");
		$export_rels = array();
		$export_rel_names = array();
		$rv = "\tfunction configure_relationmgr(\$arr)\n";
		$rv .= "\t{\n";
		foreach($conns as $conn)
		{
			$relobj = $conn->to();
			$rel_id = $relobj->id();
			// iga iga relobj käest saame selle juurde kuuluva parempoolse selecti sisu
			$rclasses = $relobj->prop("r_class_id");
			//$export_rels[$relobj->id()]["capt_new_object"] = "Objekti tüüp";
			foreach($rclasses as $rclass)
			{
				$export_rels[$rel_id][$rclass] = $classes[$rclass]["name"];
				$rv .= "\t\t\$arr['prop']['configured_rels'][$rel_id][$rclass] = '" . $classes[$rclass]["name"] . "';\n";
				
			};
			$rv .= "\t\t\$arr['prop']['configured_rel_names'][" . $rel_id ."] = '" . $relobj->name() . "';\n";
			//$export_rel_names[$rel_id] = $relobj->name();
		};
			
		$rv .= "\t}\n\n";
		return $rv;

	}

	function generate_reltypes($id)
	{
		$o = new object($id);
		$clsid = $o->id();
		$clso = new object($clsid);
		// iga seostatud objekt annab ühe välja vasakusse selecti
		$conns = $clso->connections_from(array(
			"type" => "RELTYPE_RELATION",
		));
		$classes = aw_ini_get("classes");
		$export_rels = array();
		$export_rel_names = array();
		$clinf = aw_ini_get("classes");
		foreach($conns as $conn)
		{
			$rv .= "@reltype " . strtoupper($this->_valid_id($conn->prop("to.name"))) . " value=" . $conn->prop("id") . " clid=";
			$to = $conn->to();
			$r_class_ids = $to->prop("r_class_id");
			$cldefs = array();
			foreach($r_class_ids as $r_class_id)
			{
				$cldefs[] = $clinf[$r_class_id]["def"];

			};
			$rv .= join(",",$cldefs);
			$rv .= "\n";
			$rv .= "@caption " . $conn->prop("to.name") . "\n\n";
		}
		return $rv;


	}

	function _valid_id($src)
	{
		return strtolower(preg_replace("/\s/","_",$src));


	}

	/**
		@attrib name=delete
	**/
	function delete($arr)
	{
		$sel = $arr["sel"];
		if (is_array($sel))
		{
			foreach($sel as $oid)
			{
				$o = new object($oid);
				$o->delete();
			};
		};
		return $arr["return_url"];
	}

	/** cuts properties

		@attrib name=cut

	**/
	function do_cut($arr)
	{
		$_SESSION["cd_cut"] = safe_array($arr["sel"]);
		return $arr["return_url"];
	}

	/** pastes properties

		@attrib name=paste

	**/
	function do_paste($arr)
	{
		foreach(safe_array($_SESSION["cd_cut"]) as $oid)
		{
			if (is_oid($oid) && $this->can("edit", $oid))
			{
				$o = obj($oid);
				$o->set_parent($arr["group_parent"]);
				$o->save();
			}
		}
		$_SESSION["cd_cut"] = array();
		return $arr["return_url"];
	}

	function gen_folder_tree()
	{
		$folders = aw_ini_get("classfolders");
		$this->by_parent = array();
		foreach($folders as $id => $folderdat)
		{
			$this->by_parent[$folderdat["parent"]][$id] = $folderdat["name"];
		};
		$this->level = -1;
		return $this->_rec_folder_tree(0);
	}

	function _rec_folder_tree($parent)
	{
		$this->level++;
		$rv = array();
		foreach ($this->by_parent[$parent] as $key => $val)
		{
			$spacer = str_repeat("&nbsp;",$this->level*4);
			$rv[$key] = $spacer .= $val;
			if ($this->by_parent[$key])
			{
				$rv = $rv + $this->_rec_folder_tree($key);
			};
		};
		$this->level--;
		return $rv;


	}

	// register class in central register
	function do_register_class($arr)
	{
		$c = $arr["obj_inst"];
		$class_name = $this->_valid_id($c->name());
		$clid = "CL_" . strtoupper($class_name);
		$st = "ST_" . strtoupper($class_name);
		$class = array(
			"def" => $clid,
			"file" => $class_name,
			"syslog_type" => $st,
			"name" => $c->name(),
		);
		$classlist = get_instance("core/class_list");
		$new_clid = $classlist->register_new_class_id(array(
			"data" => $class
		));

		$c->set_prop("reg_class_id",$new_clid);

	}

	function callback_post_save($arr)
	{
		if ($arr["request"]["register_under"])
		{
			$arr["obj_inst"]->set_meta("register_under", $arr["request"]["register_under"]);
			$arr["obj_inst"]->save();
		}

		// I need to put class information into an ini file as well
		//print "generating class file";
		$cldef = $this->gen_classdef(array(
			"obj_inst" => $arr["obj_inst"],
		));

		$fld = $this->gen_folder;
		if (is_writable($fld))
		{
			$this->put_file(array(
				"file" => $fld . "/" . $arr["obj_inst"]->id() . ".aw",
				"content" => $cldef,
			));

			// generate class information for generated classes as well
	                $clist = new object_list(array(
				"class_id" => CL_CLASS_DESIGNER,
				"can_add"  => 1,
                	));

			$ini_file = "";

			// def, name, file, can_add, alias, parents
			foreach($clist->arr() as $class_obj)
			{
				$clid = $class_obj->prop("reg_class_id");
				$prefix = "classes[$clid]";

				$ini_file .= $prefix . "[def] = " . "CL_" . $clid . "\n";
				$ini_file .= $prefix . "[name] = " . $class_obj->name() . "\n";
				$ini_file .= $prefix . "[file] = " . $class_obj->id() . "\n";
				$ini_file .= $prefix . "[parents] = " . $class_obj->prop("class_folder") . "\n";
				// maybe I do not need this .. don't know right now
				$ini_file .= $prefix . "[generated] = 1\n";
				$ini_file .= $prefix . "[can_add] = " . $class_obj->prop("can_add") . "\n\n";
			};

			$this->put_file(array(
				"file" => $fld . "/" . "generated_classes.ini",
				"content" => $ini_file,
			));

		};

		//print "done<br>";

	}
}
?>
