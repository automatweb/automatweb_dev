<?php
// aliasmgr.aw - Alias Manager
// $Header: /home/cvs/automatweb_dev/classes/Attic/aliasmgr.aw,v 2.9 2001/12/12 21:58:31 duke Exp $

// yup, this class is really braindead at the moment and mostly a copy of
// the current alias manager inside the document class, but I will optimize
// this a later.
global $orb_defs;
$orb_defs["aliasmgr"] = "xml";

class aliasmgr extends aw_template {
	function aliasmgr($args = array())
	{
		extract($args);
		$this->db_init();
		$this->tpl_init("aliasmgr");
		$this->contents = "";

	}
		
	function _init_aliases($args = array())
	{
		$this->defs = array();
		$obj = $this->get_object($this->id);
		$this->parent = $obj["parent"];

		// yes, we define all aliases separately, so later on
		// we can create policys about what type of aliases to show
		// depending on different conditions (site policy, menu 
		// properties).
/*		$this->defs["images"] = array(
				"alias" => "p",
				"title" => "Pildid",
				"table" => "image",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->id),"images"),
				"chlink" => $this->mk_my_orb("change",array(),"images"),
				"dellink" => $this->mk_my_orb("delete",array("docid" => $this->id),"images"),
				"field" => "id",

		);*/
		$return_url = urlencode($this->mk_my_orb("list_aliases", array("id" => $this->id) ) );

		$this->defs["links"] = array(
				"alias" => "l",
				"title" => "Lingid",
				"table" => "extlink",
				"generator" => "_link_aliases",
				"addlink" => $this->mk_my_orb("new", array("docid" => $this->id, "parent" => $this->parent),"links"),
				"chlink" => $this->mk_my_orb("change",array("parent" => $this->id),"links"),
				"dellink" => $this->mk_my_orb("delete",array("parent" => $this->id),"links"),
				"field" => "id",
		);
		
		$this->defs["image"] = array(
				"alias" => "p",
				"title" => "Pildid",
				"table" => "image",
				"generator" => "_image_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->parent, "return_url" => $return_url,"alias_to" => $this->id),"image"),
				"chlink" => "#",
		);
		
		$this->defs["tables"] = array(
				"alias" => "t",
				"title" => "Tabelid",
				"table" => "table",
				"generator" => "_table_aliases",
				"addlink" => $this->mk_my_orb("add_doc", array("id" => $this->id, "parent" => $this->parent),"table"),
				"chlink" => $this->mk_my_orb("change",array(),"table"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["forms"] = array(
				"alias" => "f",
				"title" => "Vormid",
				"table" => "form",
				"generator" => "_form_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form"),
				"chlink" => $this->mk_my_orb("change",array(),"form"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["files"] = array(
				"alias" => "v",
				"title" => "Failid",
				"table" => "file",
				"generator" => "_file_aliases",
				"addlink" => $this->mk_my_orb("new",array("id" => $this->id, "parent" => $this->parent),"file"),
				"chlink" => $this->mk_my_orb("change",array("doc" => $this->id),"file"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["graphs"] = array(
				"alias" => "g",
				"title" => "Graafikud",
				"table" => "graph",
				"generator" => "_graph_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"graph"),
				"chlink" => $this->mk_my_orb("change",array("doc" => $this->id),"graph"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["galleries"] = array(
				"alias" => "y",
				"title" => "Galeriid",
				"table" => "gallery",
				"generator" => "_gallery_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"gallery"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"chlink" => "#",
		);
		
		$this->defs["form_chains"] = array(
				"alias" => "c",
				"title" => "Vormipärjad",
				"table" => "formchain",
				"generator" => "_form_chain_aliases",
				"addlink" =>  $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form_chain"),
				"chlink" => $this->mk_my_orb("change",array(),"form_chain"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);

		$this->defs["link_collections"] = array(
				"alias" => "x",
				"title" => "Lingikogu oksad",
				"table" => "link_collection",
				"generator" => "_link_collection_aliases",
				"addlink" => $this->mk_orb("pick_collection",array("parent" => $this->id),"link_collection"),
				"chlink" => $this->mk_orb("pick_branch",array("parent" => $this->id),"link_collection"),
				"field" => "id",
		);

		$this->defs["forums"] = array(
				"alias" => "o",
				"title" => "Foorumid",
				"table" => "forum",
				"generator" => "_forum_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id),"forum"),
				"chlink" => "#",
		);

		

		$this->defs["form_entry"] = array(
				"alias" => "r",
				"title" => "Formi sisetus",
				"table" => "form_entries",
				"generator" => "_form_entry_aliases",
				"addlink" => $this->mk_my_orb("new_entry_alias",array("parent" => $this->parent, "return_url" => $return_url,"alias_to" => $this->id),"form_alias"),
				"chlink" => "#",
		);
		
		$this->defs["menu_chains"] = array(
				"alias" => "m",
				"title" => "Menüüpärjad",
				"table" => "menu_chains",
				"generator" => "_menu_chain_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"menu_chain"),
				"chlink" => $this->mk_my_orb("change",array(),"menu_chain"),
				"field" => "id"
		);

		$this->defs["pullouts"] = array(
				"alias" => "q",
				"title" => "Pulloudid",
				"table" => "pullout",
				"generator" => "_pullout_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"pullout"),
				"chlink" => $this->mk_my_orb("change",array(),"pullout"),
				"field" => "id"
		);

	}

	////
	// !This is the main function which lists all the aliases
	// id(int) - the object (right now only document is supported)
	// that we will use to display aliases for
	function list_aliases($args = array())
	{
		extract($args);
		$this->id = $id;
		$this->_init_aliases();
		$this->table = $table;
		$this->sortby = $sortby;
		$this->sort_order = $sort_order;
		$this->read_template("lists.tpl");
		load_vcl("table");

		$this->t = new aw_table(array(
			"prefix" => "images",
			"imgurl"    => $GLOBALS["baseurl"]."/img",
			"tbgcolor" => "#C3D0DC",
		));

		$this->t->parse_xml_def($GLOBALS["basedir"]."/xml/generic_table.xml");
		$this->t->set_header_attribs(array(
			"id" => $this->id,
			"class" => "aliasmgr",
			"action" => "list_aliases",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"nowrap" => "1",
			//"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "description",
			"caption" => "Kirjeldus",
			"talign" => "center",
			"nowrap" => "1",
			//"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "alias",
			"caption" => "Alias",
			"talign" => "center",
			//"nowrap" => "1",
                ));
		$this->t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"talign" => "center",
			"nowrap" => "1",
			//"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"nowrap" => "1",
			//"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "check",
			"align" => "center",
		));

		$aliases = array();
		$cnt = 0;
		$targets = "";
		foreach($this->defs as $key => $val)
		{
			$cnt++;
			$this->vars(array(
				"cnt" => $cnt,
				"target" => $val["addlink"],
			));
			$targets .= $this->parse("target_def");
			$aliases[$cnt] = $val["title"];

			$this->$val["generator"]();
		};

		$this->vars(array(
			"table" => $this->contents,
			"id" => $id,
			"aliases" => $this->picker(0,$aliases),
			"target_def" => $targets,
		));
			
		return $this->parse();
	}
		
	// 
	// Every alias class has its own subroutine to draw the according table
	//
	/////
	// !This one handles all the images
	function _img_aliases($args = array())
	{
		classload("images");
		$img = new db_images;
		$this->_initialize($this->defs["image"]);
		$img->list_by_object($this->id,0);
		while($row = $img->db_next())
		{
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $row["oid"]),"images"),$row["name"]);

			$this->t->define_data(array(
				"name" => $link,
				"alias" => sprintf("#p%d#",$row["idx"]),
				"comment" => $row["comment"],
				"modified"    => $this->time2date($row["modified"],2),
				"modifiedby" => $row["modifiedby"],
			));
			$this->_common_parts($row);
		}
		$this->_finalize($this->defs["image"]);
	}

	function _link_aliases($args = array())
	{
		$this->_initialize($this->defs["links"]);
		$links = $this->get_aliases_for($this->id,CL_EXTLINK,$_sby, $s_link_order,array("extlinks" => "extlinks.id = objects.oid"));
		reset($links);
		$lc = 0;
		while (list(,$v) = each($links))
		{	
			$lc++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"],"docid" => $id),"links"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"address"             => $v["url"],
				"alias"               => "#l".$lc."#",	
			));
			$this->_common_parts($v);
		};
		$this->_finalize($this->defs["links"]);
	}
	
	function _menu_chain_aliases($args = array())
	{
		$this->_initialize($this->defs["menu_chains"]);
		$menu_chains = $this->get_aliases_for($this->id,CL_MENU_CHAIN,$_sby, $s_link_order);
		reset($menu_chains);
		$mc = 0;
		while (list(,$v) = each($menu_chains))
		{	
			$mc++;
			$mchain = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"]),"menu_chain"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $mchain,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"address"             => $v["url"],
				"alias"               => "#m".$mc."#",	
			));
			$this->_common_parts($v);
		};
		$this->_finalize($this->defs["menu_chains"]);
	}
	
	function _pullout_aliases($args = array())
	{
		$this->_initialize($this->defs["pullouts"]);
		$menu_chains = $this->get_aliases_for($this->id,CL_PULLOUT,$_sby, $s_link_order);
		reset($menu_chains);
		$mc = 0;
		while (list(,$v) = each($menu_chains))
		{	
			$mc++;
			$mchain = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"]),"pullout"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $mchain,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"address"             => $v["url"],
				"alias"               => "#q".$mc."#",	
			));
			$this->_common_parts($v);
		};
		$this->_finalize($this->defs["pullouts"]);
	}
	
	function _form_aliases($args = array())
	{
		$this->_initialize($this->defs["forms"]);	
		$forms = $this->get_aliases_for($this->id,CL_FORM,$s_form_sortby, $s_form_order);
		$fc = 0;
		reset($forms);
		while (list(,$v) = each($forms))
		{
			$fc++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"]),"form"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#f".$fc."#","comment" => $v["comment"],
				"id"                  => $v["id"],		
			));
			$this->_common_parts($v);
		};
		$this->_finalize($this->defs["forms"]);
	}

	function _file_aliases($args = array())
	{

		$this->_initialize($this->defs["files"]);
		$files = $this->get_aliases_for($this->id,CL_FILE,$s_file_sortby, $s_file_order);
		$fic = 0;
		reset($files);
		while (list(,$v) = each($files))
		{
			$fic++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"], "doc" => $id),"file"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#v".$fic."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
		$this->_finalize($this->defs["files"]);
	}

	function _image_aliases($args = array())
	{
		$this->_initialize($this->defs["image"]);
		$files = $this->get_aliases_for($this->id,CL_IMAGE,$s_image_sortby, $s_image_order);
		$fic = 0;
		reset($files);
		while (list(,$v) = each($files))
		{
			$fic++;
			$return_url = $this->mk_my_orb("list_aliases", array("id" => $this->id));
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"], "return_url" => urlencode($return_url)),"image"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#p".$fic."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
		$this->_finalize($this->defs["image"]);
	}

	function _table_aliases($args = array())
	{
		$this->_initialize($this->defs["tables"]);
		$tables = $this->get_aliases_for($this->id,CL_TABLE,$x,$y);
		$tc = 0;
		while (list(,$v) = each($tables))
		{
			$tc++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"]),"table"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#t".$tc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
		$this->_finalize($this->defs["tables"]);
	}

	function _form_chain_aliases($args = array())
	{
		$this->_initialize($this->defs["form_chains"]);
		$ffl = "";
		$chains = $this->get_aliases_for($this->id,CL_FORM_CHAIN,$s_chain_sortby, $s_chain_order);
		$cc = 0;
		reset($chains);
		while (list(,$v) = each($chains))
		{
			$cc++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"]),"form_chain"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#c".$cc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		};
		$this->_finalize($this->defs["form_chains"]);
	}

	function _gallery_aliases($args = array())
	{
		$this->_initialize($this->defs["galleries"]);
		$galleries = $this->get_aliases_for($this->id,CL_GALLERY,$s_gallery_sortby, $s_gallery_order);
		$galc = 0;
		reset($galleries);
		while (list(,$v) = each($galleries))
		{
			$galc++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_orb("admin", array("id" => $v["id"]),"gallery"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#y".$galc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
		$this->_finalize($this->defs["galleries"]);
	}

	function _form_entry_aliases($args = array())
	{
		$this->_initialize($this->defs["form_entry"]);
		$fes = $this->get_aliases_for($this->id,CL_FORM_ENTRY,$s_fe_sortby, $s_fe_order);
		$fec = 0;
		reset($fes);
		while (list(,$v) = each($fes))
		{
			$fec++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_orb("change_entry_alias", array("id" => $v["id"]),"form_alias"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#r".$fec."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
		$this->_finalize($this->defs["form_entry"]);
	}


	function _link_collection_aliases($args = array())
	{
		$this->_initialize($this->defs["link_collections"]);
		$link_collections = $this->get_aliases_for($this->id,CL_LINK_COLLECTION,0,0);
		$lcoll = "";

		if (is_array($link_collections))
		{
			$c = "";
			$lcc = 0;

			foreach($link_collections as $key => $val)
			{
				$lcc++;
				$name = ($val["name"]) ? $val["name"] : "(nimetu)";
				$link = sprintf("<a href='%s&id=%d'>%s</a>",$this->defs["link_collections"]["chlink"],$val["oid"],$name);
				$this->t->define_data(array(
					"name" => $link,
					"description" => $val["comment"],
					"alias" => "#x$lcc#",
					"modified" => $this->time2date($val["modified"],2),
					"modifiedby" => $val["modifiedby"],
				));	
				$this->_common_parts($val);
			}
		}
		$this->_finalize($this->defs["link_collections"]);
	}
	
	function _forum_aliases($args = array())
	{
		$this->_initialize($this->defs["forums"]);
		$forums = $this->get_aliases_for($this->id,CL_FORUM,0,0);
		$lcoll = "";

		$this->vars(array(
			"addlink" => $this->mk_my_orb("new",array("parent" => $id),"forum"),
		));


		if (is_array($forums) && (sizeof($forums) > 0))
		{
			$c = "";
			$fcc = 0;

			foreach($forums as $key => $val)
			{
				$fcc++;
				$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change",array("id" => $val["oid"]),"forum"),$val["name"]);
				$this->t->define_data(array(
					"name" => $link,
					"comment" => $val["comment"],
					"alias" => "#o$fcc#",
					"edlink" => $this->mk_my_orb("change",array("id" => $val["oid"]),"forum"),
					"modified" => $this->time2date($val["modified"],2),
					"modifiedby" => $val["modifiedby"],
				));
				$this->_common_parts($val);
			};
		};
		$this->_finalize($this->defs["forums"]);
	}

	function _graph_aliases($args = array())
	{
		$this->_initialize($this->defs["graphs"]);
		$graphs = $this->get_aliases_for($this->id,CL_GRAPH,$s_graph_sortby, $s_graph_order);
		$gc = 0;
		reset($graphs);
		while (list(,$v) = each($graphs))
		{
			$gc++;
			$link = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("change", array("id" => $v["id"]),"graph"),$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#g".$gc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		};
		$this->_finalize($this->defs["graphs"]);
	}
	
	function _initialize($args = array())
	{
		$this->t->reset_data();
		$this->t->set_attribs(array("prefix" => $args["table"]));
		$this->t->set_header_attribs(array("table" => $args["table"]));
		$this->vars(array(
			"add_link" => $args["addlink"],
		));
		$this->attribs = $args;
	}

	function _finalize($args = array())
	{
		if ($this->table == $this->attribs["table"])
		{
			$this->t->sort_by(array("field" => $this->sortby));
		}


		$this->vars(array(
			"contents" => $this->t->draw(),
			"title" => $this->attribs["title"],
			"type" => $this->attribs["table"],
			"chlink" => $this->attribs["chlink"],
			"field" => $this->attribs["field"],
			"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
		));

		if ($this->t->rows() > 0)
		{
			$this->contents .= $this->parse("table");
		};
	}

	function _common_parts($args = array())
	{
		$id = ($args["id"]) ? $args["id"] : $args["oid"];
		$this->t->merge_data(array(
			"check" => sprintf("<input type='checkbox' name='c_%s' value='%d'>",$this->attribs["table"],$id),
		));
	}
};
?>
