<?php
// aliasmgr.aw - Alias Manager
// $Header: /home/cvs/automatweb_dev/classes/Attic/aliasmgr.aw,v 2.15 2002/01/04 15:49:32 duke Exp $

global $orb_defs;
$orb_defs["aliasmgr"] = "xml";

class aliasmgr extends aw_template {
	function aliasmgr($args = array())
	{
		extract($args);
		$this->db_init();
		$this->tpl_init("aliasmgr");
		$this->contents = "";
		lc_load("aliasmgr");
		global $lc_aliasmgr;
		if (is_array($lc_aliasmgr))
		{
			$this->vars($lc_aliasmgr);
		};

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
		
		$return_url = urlencode($this->mk_my_orb("list_aliases", array("id" => $this->id) ) );
		$this->return_url = $return_url;

		$this->defs["links"] = array(
				"alias" => "l",
				"title" => "link",
				"generator" => "_link_aliases",
				"class_id" => CL_EXTLINK,
				"addlink" => $this->mk_my_orb("new", array("docid" => $this->id, "parent" => $this->parent),"links"),
				"chlink" => $this->mk_my_orb("change",array("parent" => $this->id),"links"),
				"dellink" => $this->mk_my_orb("delete",array("parent" => $this->id),"links"),
				"field" => "id",
		);
		
		$this->defs["image"] = array(
				"alias" => "p",
				"title" => "pilt",
				"generator" => "_image_aliases",
				"class_id" => CL_IMAGE,
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->parent, "return_url" => $return_url,"alias_to" => $this->id),"image"),
				"chlink" => "#",
		);
		
		$this->defs["tables"] = array(
				"alias" => "t",
				"title" => "tabel",
				"generator" => "_table_aliases",
				"class_id" => CL_TABLE,
				"addlink" => $this->mk_my_orb("add_doc", array("id" => $this->id, "parent" => $this->parent),"table"),
				"chlink" => $this->mk_my_orb("change",array(),"table"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["forms"] = array(
				"alias" => "f",
				"title" => "vorm",
				"class_id" => CL_FORM,
				"generator" => "_form_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form"),
				"chlink" => $this->mk_my_orb("change",array(),"form"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["files"] = array(
				"alias" => "v",
				"title" => "fail",
				"class_id" => CL_FILE,
				"generator" => "_file_aliases",
				"addlink" => $this->mk_my_orb("new",array("id" => $this->id, "parent" => $this->parent),"file"),
				"chlink" => $this->mk_my_orb("change",array("doc" => $this->id),"file"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["graphs"] = array(
				"alias" => "g",
				"title" => "graafik",
				"class_id" => CL_GRAPH,
				"generator" => "_graph_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"graph"),
				"chlink" => $this->mk_my_orb("change",array("doc" => $this->id),"graph"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["galleries"] = array(
				"alias" => "y",
				"title" => "galerii",
				"generator" => "_gallery_aliases",
				"class_id" => CL_GALLERY,
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"gallery"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"chlink" => "#",
		);
		
		$this->defs["form_chains"] = array(
				"alias" => "c",
				"title" => "vormipärg",
				"generator" => "_form_chain_aliases",
				"class_id" => CL_FORM_CHAIN,
				"addlink" =>  $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form_chain"),
				"chlink" => $this->mk_my_orb("change",array(),"form_chain"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);

		$this->defs["link_collections"] = array(
				"alias" => "x",
				"title" => "lingikogu",
				"class_id" => CL_LINK_COLLECTION,
				"generator" => "_link_collection_aliases",
				"addlink" => $this->mk_orb("pick_collection",array("parent" => $this->id),"link_collection"),
				"chlink" => $this->mk_orb("pick_branch",array("parent" => $this->id),"link_collection"),
				"field" => "id",
		);

		$this->defs["forums"] = array(
				"alias" => "o",
				"title" => "foorum",
				"class_id" => CL_FORUM,
				"generator" => "_forum_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id),"forum"),
				"chlink" => "#",
		);

		

		$this->defs["form_entry"] = array(
				"alias" => "r",
				"title" => "vormi sisetus",
				"generator" => "_form_entry_aliases",
				"class_id" => CL_FORM_ENTRY,
				"addlink" => $this->mk_my_orb("new_entry_alias",array("parent" => $this->parent, "return_url" => $return_url,"alias_to" => $this->id),"form_alias"),
				"chlink" => "#",
		);
		
		$this->defs["menu_chains"] = array(
				"alias" => "mc",
				"title" => "menüüpärg",
				"generator" => "_menu_chain_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"menu_chain"),
				"chlink" => $this->mk_my_orb("change",array(),"menu_chain"),
				"field" => "id"
		);
		
		$this->defs["object_chain"] = array(
				"alias" => "oc",
				"title" => "objektipärg",
				"generator" => "_object_chain_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"object_chain"),
				"chlink" => $this->mk_my_orb("change",array(),"object_chain"),
				"field" => "id"
		);
		
		$this->defs["menus"] = array(
				"alias" => "m",
				"title" => "menüü link",
				"generator" => "_menu_aliases",
				"addlink" => $this->mk_my_orb("add_alias",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"menu"),
				"chlink" => $this->mk_my_orb("change",array(),"menu"),
				"field" => "id"
		);
		
		$this->defs["pullouts"] = array(
				"alias" => "q",
				"title" => "pullout",
				"class_id" => CL_PULLOUT,
				"generator" => "_pullout_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"pullout"),
				"chlink" => $this->mk_my_orb("change",array(),"pullout"),
				"field" => "id"
		);
		
		$this->defs["calendars"] = array(
				"alias" => "k",
				"title" => "calendar",
				"class_id" => CL_CALENDAR,
				"generator" => "_calendar_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"planner"),
				"chlink" => $this->mk_my_orb("change",array(),"planner"),
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
			"imgurl"    => $GLOBALS["baseurl"]."/automatweb/images",
			"tbgcolor" => "#C3D0DC",
		));

		$this->t->parse_xml_def($GLOBALS["basedir"]."/xml/generic_table.xml");
		$this->t->set_header_attribs(array(
			"id" => $this->id,
			"class" => "aliasmgr",
			"action" => "list_aliases",
		));
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
			"nowrap" => "1",
			"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "description",
			"caption" => "Muu info",
			"talign" => "center",
			"nowrap" => "1",
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
			"caption" => "Vali",
			"name" => "check",
			"width" => 20,
			"align" => "center",
		));

		$aliases = array();
		$cnt = 0;
		$targets = "";
		$counter = 0;

		$this->dellinks = array();
		$this->chlinks = array();

		foreach($this->defs as $key => $val)
		{
			$cnt++;
			$this->vars(array(
				"cnt" => $cnt,
				"target" => $val["addlink"],
				"chlink" => $val["chlink"],
			));
			$targets .= $this->parse("target_def");
			$aliases[$cnt] = $val["title"];

			$this->def_id = $key;

			$this->_initialize($key);
			$this->$val["generator"]();
			$this->_finalize($key);
		};

		if (not($args["sortby"]))
		{
			$sortby = "title";
		}
		else
		{
			$sortby = $args["sortby"];
		};
		$this->t->sort_by(array("field" => $sortby));

		$this->vars(array(
			"table" => $this->t->draw(),
			"id" => $id,
			"aliases" => $this->picker(0,$aliases),
			"chlinks" => join("\n",map2("chlinks[%s] = \"%s\";",$this->chlinks)),
			"dellinks" => join("\n",map2("dellinks[%s] = %s;",$this->dellinks)),
			"delorb" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
			"target_def" => $targets,
		));
			
		return $this->parse();
	}
		
	// 
	// Every alias class has its own subroutine to draw the according table
	//

	function _link_aliases($args = array())
	{
		$links = $this->get_aliases_for($this->id,CL_EXTLINK,$_sby, $s_link_order,array("extlinks" => "extlinks.id = objects.oid"));
		reset($links);
		while (list(,$v) = each($links))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"docid" => $id),"links");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description"             => $v["url"],
			));
			$v["url"] = $url;
			$this->_common_parts($v);
		};
	}
	
	function _object_chain_aliases($args = array())
	{
		$menu_chains = $this->get_aliases_for($this->id,CL_OBJECT_CHAIN,$_sby, $s_link_order);
		reset($menu_chains);
		while (list(,$v) = each($menu_chains))
		{	
			$meta = aw_unserialize($v["metadata"]);
			$this->dequote($v);
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"return_url" => $this->return_url),"object_chain");
			$mchain = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $mchain,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description"             => $v["comment"],
			));
			$this->_common_parts($v);
		};
	}
	
	function _menu_chain_aliases($args = array())
	{
		$menu_chains = $this->get_aliases_for($this->id,CL_MENU_CHAIN,$_sby, $s_link_order);
		reset($menu_chains);
		while (list(,$v) = each($menu_chains))
		{	
			$meta = aw_unserialize($v["metadata"]);
			$this->dequote($v);
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"return_url" => $this->return_url),"menu_chain");
			$mchain = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $mchain,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description"             => $v["comment"],
			));
			$this->_common_parts($v);
		};
	}
	
	function _menu_aliases($args = array())
	{
		$menu_chains = $this->get_aliases_for($this->id,CL_PSEUDO,$_sby, $s_link_order);
		reset($menu_chains);
		while (list(,$v) = each($menu_chains))
		{	
			$url = $this->mk_my_orb("change_alias", array("id" => $v["id"],"return_url" => $this->return_url),"menu");
			$mchain = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $mchain,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description"             => $v["comment"],
			));
			$this->_common_parts($v);
		};
	}
	
	function _pullout_aliases($args = array())
	{
		$menu_chains = $this->get_aliases_for($this->id,CL_PULLOUT,$_sby, $s_link_order);
		reset($menu_chains);
		while (list(,$v) = each($menu_chains))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"pullout");
			$mchain = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $mchain,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description"             => $v["comment"],
			));
			$this->_common_parts($v);
		};
	}
	
	function _calendar_aliases($args = array())
	{
		$aliases = $this->get_aliases_for($this->id,CL_PULLOUT,$_sby, $s_link_order);
		reset($aliases);
		while (list(,$v) = each($aliases))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"planner");
			$mchain = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $mchain,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description"             => $v["comment"],
			));
			$this->_common_parts($v);
		};
	}
	
	function _form_aliases($args = array())
	{
		$forms = $this->get_aliases_for($this->id,CL_FORM,$s_form_sortby, $s_form_order);
		reset($forms);
		while (list(,$v) = each($forms))
		{
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"form");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;

			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" 		=> $v["comment"],
				"id"                  => $v["id"],		
			));
			$this->_common_parts($v);
		};
	}

	function _file_aliases($args = array())
	{

		$files = $this->get_aliases_for($this->id,CL_FILE,$s_file_sortby, $s_file_order);
		reset($files);
		while (list(,$v) = each($files))
		{
			$url = $this->mk_my_orb("change", array("id" => $v["id"], "doc" => $id),"file");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
	}

	function _image_aliases($args = array())
	{
		$files = $this->get_aliases_for($this->id,CL_IMAGE,$s_image_sortby, $s_image_order);
		$fic = 0;
		reset($files);
		while (list(,$v) = each($files))
		{
			$fic++;
			$return_url = $this->mk_my_orb("list_aliases", array("id" => $this->id));
			$url = $this->mk_my_orb("change", array("id" => $v["id"], "return_url" => urlencode($return_url)),"image");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" 		=> $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
	}

	function _table_aliases($args = array())
	{
		$tables = $this->get_aliases_for($this->id,CL_TABLE,$x,$y);
		$tc = 0;
		while (list(,$v) = each($tables))
		{
			$tc++;
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"table");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
	}

	function _form_chain_aliases($args = array())
	{
		$ffl = "";
		$chains = $this->get_aliases_for($this->id,CL_FORM_CHAIN,$s_chain_sortby, $s_chain_order);
		$cc = 0;
		reset($chains);
		while (list(,$v) = each($chains))
		{
			$cc++;
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"form_chain");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		};
	}

	function _gallery_aliases($args = array())
	{
		$galleries = $this->get_aliases_for($this->id,CL_GALLERY,$s_gallery_sortby, $s_gallery_order);
		$galc = 0;
		reset($galleries);
		while (list(,$v) = each($galleries))
		{
			$galc++;
			$url = $this->mk_orb("admin", array("id" => $v["id"]),"gallery");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
	}

	function _form_entry_aliases($args = array())
	{
		$fes = $this->get_aliases_for($this->id,CL_FORM_ENTRY,$s_fe_sortby, $s_fe_order);
		$fec = 0;
		reset($fes);
		while (list(,$v) = each($fes))
		{
			$fec++;
			$url = $this->mk_orb("change_entry_alias", array("id" => $v["id"]),"form_alias");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["link"] = $link;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		}
	}


	function _link_collection_aliases($args = array())
	{
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
				$url = $this->defs["link_collections"]["chlink"] . "&id=?" . $val["oid"];
				$link = sprintf("<a href='%s&id=%d'>%s</a>",$url,$name);
				$val["url"] = $url;
				$this->t->define_data(array(
					"name" => $link,
					"description" => $val["comment"],
					"modified" => $this->time2date($val["modified"],2),
					"modifiedby" => $val["modifiedby"],
				));	
				$this->_common_parts($val);
			}
		}
	}
	
	function _forum_aliases($args = array())
	{
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
				$url = $this->mk_my_orb("change",array("id" => $val["oid"]),"forum");
				$link = sprintf("<a href='%s'>%s</a>",$url,$val["name"]);
				$val["url"] = $url;
				$this->t->define_data(array(
					"name" => $link,
					"comment" => $val["comment"],
					"edlink" => $this->mk_my_orb("change",array("id" => $val["oid"]),"forum"),
					"modified" => $this->time2date($val["modified"],2),
					"modifiedby" => $val["modifiedby"],
				));
				$this->_common_parts($val);
			};
		};
	}

	function _graph_aliases($args = array())
	{
		$graphs = $this->get_aliases_for($this->id,CL_GRAPH,$s_graph_sortby, $s_graph_order);
		$gc = 0;
		reset($graphs);
		while (list(,$v) = each($graphs))
		{
			$gc++;
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"graph");
			$link = sprintf("<a href='%s'>%s</a>",$url,$v["name"]);
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"comment" => $v["comment"],
				"id"                  => $v["id"],
			));
			$this->_common_parts($v);
		};
	}
	
	function _initialize($id)
	{
		#$this->t->reset_data();
		$this->acounter = 0;
		#$this->t->set_attribs(array("prefix" => $this->defs[$id]["table"]));
		#$this->t->set_header_attribs(array("table" => $this->defs[$id]["table"]));
		#$this->vars(array(
		#	"add_link" => $this->defs[$id]["addlink"],
		#));
	}

	function _finalize($id)
	{
		if ($this->table == $this->attribs["table"])
		{
			#$this->t->sort_by(array("field" => $this->sortby));
		}

		$this->vars(array(
			#"contents" => $this->t->draw(),
			"title" => $this->defs[$id]["title"],
			"type" => $this->defs[$id]["table"],
			"chlink" => $this->defs[$d]["chlink"],
			"field" => $this->defs[$id]["field"],
			"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
		));

		#if ($this->t->rows() > 0)
		#{
		#	$this->contents .= $this->parse("table");
		#};
	}

	function _common_parts($args = array())
	{
		$id = ($args["id"]) ? $args["id"] : $args["oid"];
		$this->acounter++;
		$alias = sprintf("#%s%d#",$this->defs[$this->def_id]["alias"],$this->acounter);
		$this->counter++;
		$this->chlinks[$this->counter] = $args["url"];
		$this->dellinks[$this->counter] = $id;
		$this->t->merge_data(array(
			"title" => $this->defs[$this->def_id]["title"],
			"check" => sprintf("<input type='checkbox' name='check' value='%d'>",$this->counter),
			"icon"	=> sprintf("<img src='%s'>",get_icon_url($this->defs[$this->def_id]["class_id"],"")),
			"alias" => sprintf("<input type='text' size='5' value='%s' onClick='this.select()' onBlur='this.value=\"%s\"'>",$alias,$alias),
		));
	}
};
?>
