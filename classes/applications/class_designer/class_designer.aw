<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/class_designer.aw,v 1.2 2005/03/03 13:39:22 kristo Exp $
// class_designer.aw - Vormidisainer 
/*

@classinfo syslog_type=ST_CLASS_DESIGNER relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property visualize type=text store=no
@caption 

@default group=settings
@property relationmgr type=checkbox ch_value=1 default=1
@caption Seostehaldur

@property no_comment type=checkbox ch_value=1
@caption Kommentaari muuta ei saa

@property no_status type=checkbox ch_value=1
@caption Aktiivsust muuta ei saa

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

@groupinfo settings caption="Seaded"
@groupinfo designer caption="Disainer" submit=no
@groupinfo classdef caption="Klassi definitsioon" submit=no

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
				CL_PROPERTY_TREE
				);
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
					"url" => $this->mk_my_orb("view",array("id" => $arr["obj_inst"]->id()),"class_visualizer"),
					"caption" => "Visualiseeri",
				));
				break;

		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	/** Creates a hierarchy of groups and grids
	**/
	function create_layout_tree(&$arr)
	{
		$o = $arr["obj_inst"];
		$tree = &$arr["prop"]["vcl_inst"];
		$tree->add_item(0,array(
			"name" => $o->name(),
			"id" => $o->id(),
			"url" => $this->mk_my_orb("change",array(
				"id" => $o->id(),
				"group" => "designer",
			)),
		));

		$el_tree = new object_tree(array(
			"parent" => $o->id(),
			"class_id" => array(CL_PROPERTY_GROUP,CL_PROPERTY_GRID),
			"lang_id" => array(),
			"site_id" => array(),

		));
		$el_list = $el_tree->to_list();
		foreach($el_list->arr() as $el)
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
					"id" => $o->id(),
					"group" => "designer",
					"group_parent" => $el_id,
				)),
				"iconurl" => $iconurl,
			));
			//arr($el);
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
			"text" => "Uus tab",
			"link" => "javascript:create_group();",
			"disabled" => !$this->can_add["group"],
		));

		$tb->add_menu_item(array(
			"parent" => "new",
			"text" => "Uus grid",
			"link" => "javascript:create_grid();",
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
				"link" => "javascript:create_element($element);",
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

		/*
		$tb->add_button(array(
			"name" => "cut",
			"action" => "cut",
			"tooltip" => t("Cut"),
			"action" => "cut",
			"img" => "cut.gif",
		));
		$tb->add_button(array(
			"name" => "paste",
			"action" => "paste",
			"tooltip" => t("Paste"),
			"action" => "paste",
			"img" => "paste.gif",
		));
		*/

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
		));

		$clinf = aw_ini_get("classes");

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
				"ord" => $element->prop("ord"),
				"ordbox" => html::textbox(array(
					"name" => "ord[${el_id}]",
					"size" => 2,
					"value" => $element->prop("ord"),
				)),
				"edit" => html::href(array(
					"caption" => "Muuda",
					"url" => $this->mk_my_orb("change",array("id" => $el_id, "return_url" => urlencode(aw_global_get("REQUEST_URI"))),$el_clid),
				)),
			));
		};

		$t->set_numeric_field("ord");

		$t->sort_by(array(
			"field" => array("ord", "created"),
			"sorder" => array("ord" => "desc", "created" => "asc")
		));
	}

	/** 
		@attrib name=create_group
	**/
	function create_group($arr)
	{
		//print "inside create_group<br>";
		// group_parent on see mille alla teha
		// tmp_name on uue grupi nimi ... so what could be easier
		if (!empty($arr["tmp_name"]))
		{
			$g = new object();
			$g->set_class_id(CL_PROPERTY_GROUP);
			// XX: check whether we are allowed to add groups here
			$g->set_parent($arr["group_parent"]);
			$g->set_status(STAT_ACTIVE);
			$g->set_name($arr["tmp_name"]);
			$g->save();
		};
		$arr["action"] = "change";
		return $this->finish_action($arr);
	}

	/**
		@attrib name=create_grid
	**/
	function create_grid($arr)
	{
		$g = new object();
		$g->set_class_id(CL_PROPERTY_GRID);
		$g->set_parent($arr["group_parent"]);
		// XX: check whether we are allowed to add grids here
		$g->set_status(STAT_ACTIVE);
		$g->set_prop("grid_type",0);
		$g->set_name("vbox");
		$g->save();
		$arr["action"] = "change";
		return $this->finish_action($arr);
	}

	/**
		@attrib name=create_element
	**/
	function create_element($arr)
	{
		// XX: check whether we are allowed to add elements here
		// parent on group_parent
		// class_id on el_id
		// name on tmp_name

		// XX: check whether this is allowed class_id
		if (!empty($arr["tmp_name"]))
		{
			$e = new object();
			$e->set_class_id($arr["element_type"]);
			$e->set_parent($arr["group_parent"]);
			$e->set_status(STAT_ACTIVE);
			$e->set_name($arr["tmp_name"]);
			$e->save();
		};
		$arr["action"] = "change";
		return $this->finish_action($arr);
	}

	/**
		@attrib name=save_elements
	**/
	function save_elements($arr)
	{
		if (is_array($arr["ord"]))
		{
			foreach($arr["ord"] as $oid => $ord)
			{
				$el_o = new object($oid);
				$el_o->set_prop("ord",$ord);
				$el_o->set_name($arr["name"][$oid]);
				$el_o->save();
			};
		};
		$arr["action"] = "change";
		return $this->finish_action($arr);

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

		$clsrc = str_replace("__classname",$clname,$clsrc);
		$clsrc = str_replace("__name",$c->name(),$clsrc);
		$clsrc = str_replace("__classdef",$clid,$clsrc);

		$gpblock = "";
		$methods = "";

		foreach($cl_list->arr() as $el)
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
			};
			if ($el_clid == CL_PROPERTY_GROUP)
			{
				$grpid = $this->_valid_id($name);
				$grps .= "@groupinfo $grpid caption=\"$name\"\n";
			};
		};
		$clsrc = str_replace("/* get_property */",$gpblock,$clsrc);
		$clsrc = str_replace("/* methods */",$methods,$clsrc);
		$clsrc = str_replace("@default group=general",$rv . $grps,$clsrc);
		return $clsrc;
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
		return $this->finish_action($arr);
	}
}
?>
