<?php
// aliasmgr.aw - Alias Manager
// $Header: /home/cvs/automatweb_dev/classes/Attic/aliasmgr.aw,v 2.33 2002/06/13 23:05:45 kristo Exp $

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

	function get_defs()
	{
		return $this->defs;
	}
		
	function _init_aliases($args = array())
	{
		$this->defs = array();
		$obj = $this->get_obj_meta($this->id);
		$this->obj_data = $obj;
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
				"class" => "extlinks",
				"generator" => "_link_aliases",
				"class_id" => CL_EXTLINK,
				"templates" => array("link"),
				"addlink" => $this->mk_my_orb("new", array("docid" => $this->id, "parent" => $this->parent),"links"),
				"chlink" => $this->mk_my_orb("change",array("parent" => $this->id),"links"),
				"dellink" => $this->mk_my_orb("delete",array("parent" => $this->id),"links"),
				"field" => "id",
		);
		
		$this->defs["image"] = array(
				"alias" => "p",
				"title" => "pilt",
				"class" => "image",
				"generator" => "_image_aliases",
				"templates" => array("image","image_linked","image_inplace","image_flash"),
				"class_id" => CL_IMAGE,
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->parent, "return_url" => $return_url,"alias_to" => $this->id),"image"),
				"chlink" => "#",
		);
		
		$this->defs["tables"] = array(
				"alias" => "t",
				"title" => "tabel",
				"class" => "table",
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
				"class" => "form",
				"class_id" => CL_FORM,
				"generator" => "_form_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form"),
				"chlink" => $this->mk_my_orb("change",array(),"form"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["form_output"] = array(
				"alias" => "u",
				"title" => "Formi v&auml;ljund",
				"class" => "form_output",
				"class_id" => CL_FORM_OUTPUT,
				"generator" => "_op_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form_output"),
				"chlink" => $this->mk_my_orb("change",array(),"form_output"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["form_table"] = array(
				"alias" => "w",
				"title" => "Formi tabel",
				"class" => "form_table",
				"class_id" => CL_FORM_TABLE,
				"generator" => "_ft_aliases",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form_table"),
				"chlink" => $this->mk_my_orb("change",array(),"form_table"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["files"] = array(
				"alias" => "v",
				"title" => "fail",
				"class_id" => CL_FILE,
				"generator" => "_file_aliases",
				"class" => "file",
				"addlink" => $this->mk_my_orb("new",array("id" => $this->id, "parent" => $this->parent),"file"),
				"chlink" => $this->mk_my_orb("change",array("return_url" => $return_url),"file"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"field" => "id",
		);
		
		$this->defs["graphs"] = array(
				"alias" => "g",
				"title" => "graafik",
				"class" => "graph",
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
				"class" => "gallery",
				"generator" => "_gallery_aliases",
				"class_id" => CL_GALLERY,
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"gallery"),
				"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
				"chlink" => "#",
		);
		
		$this->defs["documents"] = array(
				"alias" => "d",
				"title" => "document",
				"class" => "document",
				"class_id" => CL_DOCUMENT,
				"generator" => "_document_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"poll"),
				"chlink" => $this->mk_my_orb("change",array(),"document"),
				"field" => "id"
		);
		
		$this->defs["form_chains"] = array(
				"alias" => "c",
				"title" => "vormipärg",
				"class" => "form_chain",
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
				"class" => "link_collection",
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
				"class" => "forum",
				"generator" => "_forum_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id),"forum"),
				"chlink" => "#",
		);

		

		$this->defs["form_entry"] = array(
				"alias" => "r",
				"title" => "vormi sisestus",
				"class" => "form_alias",
				"generator" => "_form_entry_aliases",
				"class_id" => CL_FORM_ENTRY,
				"addlink" => $this->mk_my_orb("new_entry_alias",array("parent" => $this->parent, "return_url" => $return_url,"alias_to" => $this->id),"form_alias"),
				
				"chlink" => $this->mk_my_orb("change_entry_alias",array("return_url" => $return_url),"form_alias"),
				"field" => "id",
		);
		
		$this->defs["menu_chains"] = array(
				"alias" => "mc",
				"title" => "menüüpärg",
				"class_id" => CL_MENU_CHAIN,
				"generator" => "_menu_chain_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"menu_chain"),
				"chlink" => $this->mk_my_orb("change",array(),"menu_chain"),
				"field" => "id"
		);
		
		$this->defs["object_chain"] = array(
				"alias" => "oc",
				"title" => "objektipärg",
				"class_id" => CL_OBJECT_CHAIN,
				"generator" => "_object_chain_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"object_chain"),
				"chlink" => $this->mk_my_orb("change",array(),"object_chain"),
				"field" => "id"
		);
		
		$this->defs["menus"] = array(
				"alias" => "m",
				"title" => "menüü link",
				"class" => "menu",
				"generator" => "_menu_aliases",
				"class_id" => CL_PSEUDO,
				"addlink" => $this->mk_my_orb("add_alias",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"menu"),
				"chlink" => $this->mk_my_orb("change",array(),"menu"),
				"field" => "id"
		);
		
		$this->defs["pullouts"] = array(
				"alias" => "q",
				"title" => "pullout",
				"class" => "pullout",
				"class_id" => CL_PULLOUT,
				"generator" => "_pullout_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"pullout"),
				"chlink" => $this->mk_my_orb("change",array(),"pullout"),
				"field" => "id"
		);
		
		//$this->defs["poll"] = array(
		//		"alias" => "k",
		//		"title" => "poll",
		//		"class" => "poll",
		//		"class_id" => CL_POLL,
		//		"generator" => "_poll_aliases",
		//		"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"poll"),
		//		"chlink" => $this->mk_my_orb("change",array(),"poll"),
		//		"field" => "id"
		//);
		
		$this->defs["calendars"] = array(
				"alias" => "k",
				"title" => "calendar",
				"class" => "planner",
				"class_id" => CL_CALENDAR,
				"generator" => "_calendar_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"planner"),
				"chlink" => $this->mk_my_orb("change",array(),"planner"),
				"field" => "id"
		);
		
		$this->defs["menu_tree"] = array(
				"alias" => "e",
				"title" => "Menüüpuu",
				"class" => "menu_tree",
				"class_id" => CL_MENU_TREE,
				"generator" => "_menu_tree_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"menu_tree"),
				"chlink" => $this->mk_my_orb("change",array("return_url" => $return_url),"menu_tree"),
				"field" => "id"
		);
		
		$this->defs["iframe"] = array(
				"alias" => "i",
				"title" => "inline frame",
				"class" => "iframe",
				"class_id" => CL_HTML_IFRAME,
				"generator" => "_iframe_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url,"alias_to" => $this->id),"iframe"),
				"chlink" => $this->mk_my_orb("change",array("return_url" => $return_url),"iframe"),
				"field" => "id"
		);

		$this->defs["mingi"] = array(
				"alias" => "z",
				"title" => "mingi objekt",
				"class" => "mingi",
				"class_id" => CL_MINGI,
				"generator" => "_mingi_aliases",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id, "return_url" => $return_url, "alias_to" => $this->id),"mingi"),
				"chlink" => $this->mk_my_orb("change",array("return_url" => $return_url),"mingi"),
				"field" => "id"
		);

	}

	////
	// !Allows to search for objects to include in the document
	// intended to replace pickobject.aw
	function search($args = array())
	{
		extract($args);
		$this->_init_aliases();
		$this->read_template("search_doc.tpl");
		global $s_name, $s_comment,$s_type;
		foreach($this->defs as $key => $val)
		{
			$clid = $val["class_id"];
			$this->typearr[] = $clid;
		}

    if ($s_name != "" || $s_comment != "" || $s_type > 0)
    {
			$se = array();
			if ($s_name != "")
			{
				$se[] = " objects.name LIKE '%".$s_name."%' ";
			}
			if ($s_comment != "")
			{
				$se[] = " objects.comment LIKE '%".$s_comment."%' ";
			}
			if ($s_type > 0)
			{
				$se[] = " objects.class_id = '".$s_type."' ";
			}
			else
			{
				$se[] = " objects.class_id IN (".join(",",$this->typearr).") ";
			}

			$q = "SELECT objects.name as name,objects.oid as oid,objects.class_id as class_id,objects.created as created,objects.createdby as createdby,objects.modified as modified,objects.modifiedby
as modifiedby,pobjs.name as parent_name FROM objects, objects AS pobjs WHERE pobjs.oid = objects.parent AND objects.status != 0 AND (objects.site_id = ".$this->cfg["site_id"]." OR objects.site_id IS NULL) AND ".join("AND",$se);
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"],
					"id" => $row["oid"],
					"type"  => $this->cfg["classes"][$row["class_id"]]["name"],
					"created" => $this->time2date($row["created"],2),
					"modified" => $this->time2date($row["modified"], 2),
					"createdby" => $row["createdby"],
					"modifiedby" => $row["modifiedby"],
					"parent_name" => $row["parent_name"],
					"pick_url" => $this->mk_orb("addalias",array("id" => $docid, "alias" => $row["oid"]),"document"),
				));
				$l.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $l));
		}
		else
		{
			$s_name = "%";
			$s_comment = "%";
			$s_type = 0;
		}

		$tar = array(0 => LC_OBJECTS_ALL);
		foreach($this->defs as $key => $val)
		{
			$clid = $val["class_id"];
			$tar[$clid] = $this->cfg["classes"][$clid]["name"];
		}
		$this->vars(array(
			"docid" => $docid,
			"s_name"  => $s_name,
			"s_type"  => $s_type,
			"s_comment" => $s_comment,
			"pick_link" => $this->mk_my_orb("pick",array("id" => $docid)),
			"types" => $this->picker($s_type, $tar)
		));
		return $this->parse();
	}	
	
	////
	// !kasutatakse dokude juurde aliaste lisamiseks
	//function gen_pick_list($parent,$docid,&$mstring) 
	function gen_pick_list($args = array()) 
	{
		extract($args);
		$docid = $id;
		$this->_init_aliases();
		foreach($this->defs as $key => $val)
		{
			$clid = $val["class_id"];
			$typearr[] = $clid;
		}
		$this->tpl_init("automatweb/objects");
		$parent = 1;
		if ($parent > 0) 
		{
			$parentlist = $this->get_object_chain($parent,true);
			while(list($p_oid,$p_cap) = each($parentlist)) 
			{
				if ($p_oid == $parent) 
				{
					$mmap[] = $p_cap["name"];
				} 
				else 
				{
					$mmap[] = sprintf("<a href='%s?docid=%d&parent=%d'>%s</a>",$PHP_SELF,$docid,$p_oid,$p_cap["name"]);
				};
			};
			$mstring = join(" &gt; ",array_reverse($mmap));
		};

		$this->read_template("pick.tpl");
		$this->vars(array(
			"search" => $this->mk_my_orb("search",array("docid" => $docid))
		));
		//$this->listall_types($parent,$this->typearr2);
		$lines = "";
		$count = 0;
		while($row = $this->db_next()) 
		{
			$count++;
			$this->vars(array("oid" => $row["oid"]));
			extract($row);
			// saveme handle, sest count_by_parent vajab handlerit
      $this->save_handle();
			$subs = $this->count_by_parent($row["oid"],$typearr);
			print "<pre>";
			print_r($subs);
			print "</pre>";
      $this->restore_handle();
			// kui selle objekti all on veel elemente, siis saab expandida
			if ($subs > 0) 
			{
				$expandurl = "<a href='$PHP_SELF?type=search&parent=$oid&docid=$docid'><b>+</b></a> ($subs)";
			} 
			else 
			{
				$expandurl = "($subs)";
			};
			$this->vars(array("oid" => $oid,
						"parent"				=> $parent,
						"name"					=> $name,
						"rec"						=> $count,
						"modifier"			=> $modifiers[$class_id],
						"modifiedby"		=> $row["modifiedby"],
						"created"				=> $this->time2date($created),
						"modified"			=> $this->time2date($modified),
						"class"					=> $this->cfg["classes"][$class_id]["name"],
						"docid"					=> $docid,
						"expandurl"     => $expandurl,
						"pickurl"				=> (in_array($class_id,$this->typearr) ? "<a href='".$this->mk_orb("addalias",array("id" => $docid, "alias" => $oid),"document")."'>Võta see</a>" : "")));
			$lines .= $this->parse("line");
		};
		$this->vars(array(
			"line"    => $lines,
		  "total"   => verbalize_number($count),
      "parent"  => $parent,
		  "message" => $message));
    return $this->parse();
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
		$meta = $this->get_object_metadata(array(
			"oid" => $id,
			"key" => "aliaslinks",
		));
		$this->aliaslinks = $meta;
		load_vcl("table");

		$this->t = new aw_table(array(
			"prefix" => "images",
			"imgurl"    => $this->cfg["baseurl"]."/automatweb/images",
			"tbgcolor" => "#C3D0DC",
		));

		$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
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
			$this->def_cnt = $cnt;

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
			"reforb" => $this->mk_reforb("submit_list",array("id" => $this->id)),
			"target_def" => $targets,
		));
			
		return $this->parse();
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
		$_aliases = $this->get_oo_aliases(array("oid" => $id));


		// paneme aliases kirja
                if (is_array($_aliases))
		{
			$this->set_object_metadata(array(
				"oid" => $id,
				"key" => "aliases",
				"value" => $_aliases,
			));
                };
		return $this->mk_my_orb("list_aliases",array("id" => $id));
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
	
	function _document_aliases($args = array())
	{
		$links = $this->get_aliases_for($this->id,CL_DOCUMENT,$_sby, $s_link_order);
		reset($links);
		while (list(,$v) = each($links))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"docid" => $id),"document");
			$link = sprintf("<a href='%s'>%s</a>",$url,strip_tags($v["name"]));
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
	
	function _poll_aliases($args = array())
	{
		$menu_chains = $this->get_aliases_for($this->id,CL_POLL,$_sby, $s_link_order);
		reset($menu_chains);
		while (list(,$v) = each($menu_chains))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"return_url" => urlencode($this->return_url)),"poll");
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
		$aliases = $this->get_aliases_for($this->id,CL_CALENDAR,$_sby, $s_link_order);
		reset($aliases);
		while (list(,$v) = each($aliases))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"return_url" => urlencode($this->return_url)),"planner");
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
	
	function _menu_tree_aliases($args = array())
	{
		$aliases = $this->get_aliases_for($this->id,CL_MENU_TREE,$_sby, $s_link_order);
		reset($aliases);
		while (list(,$v) = each($aliases))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"return_url" => urlencode($this->return_url)),"menu_tree");
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
	
	function _iframe_aliases($args = array())
	{
		$aliases = $this->get_aliases_for($this->id,CL_HTML_IFRAME,$_sby, $s_link_order);
		reset($aliases);
		while (list(,$v) = each($aliases))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"return_url" => urlencode($this->return_url)),"iframe");
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
	
	function _mingi_aliases($args = array())
	{
		$aliases = $this->get_aliases_for($this->id,CL_MINGI,$_sby, $s_link_order);
		reset($aliases);
		while (list(,$v) = each($aliases))
		{	
			$url = $this->mk_my_orb("change", array("id" => $v["id"],"return_url" => urlencode($this->return_url)),"mingi");
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

	function _op_aliases($args = array())
	{
		$ops = $this->get_aliases_for($this->id,CL_FORM_OUTPUT,$s_form_sortby, $s_form_order);
		reset($ops);
		while (list(,$v) = each($ops))
		{
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"form_output");
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

	function _ft_aliases($args = array())
	{
		$ops = $this->get_aliases_for($this->id,CL_FORM_TABLE,$s_ft_sortby, $s_ft_order);
		reset($ops);
		while (list(,$v) = each($ops))
		{
			$url = $this->mk_my_orb("change", array("id" => $v["id"]),"form_table");
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
			$url = $this->mk_my_orb("change", array("id" => $v["id"], "return_url" => $this->return_url),"file");
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
			$url = $this->mk_orb("change_entry_alias", array("id" => $v["id"],"return_url" => $this->return_url),"form_alias");
			$name = ($v["name"]) ? $v["name"] : "(nimetu)";
			$link = sprintf("<a href='%s'>%s</a>",$url,$name);
			$v["link"] = $link;
			$v["url"] = $url;
			$this->t->define_data(array(
				"name"                => $link,
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"description" 	      => $v["comment"],
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
				$url = $this->defs["link_collections"]["chlink"] . "&id=" . $val["oid"];
				$link = sprintf("<a href='%s'>%s</a>",$url,$name);
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
		$this->acounter = 0;
	}

	function _finalize($id)
	{
		$this->vars(array(
			"title" => $this->defs[$id]["title"],
			"type" => $this->defs[$id]["table"],
			"chlink" => $this->defs[$d]["chlink"],
			"field" => $this->defs[$id]["field"],
			"dellink" => $this->mk_my_orb("delete_alias",array("docid" => $this->id),"document"),
		));
	}

	function _common_parts($args = array())
	{
		$id = ($args["id"]) ? $args["id"] : $args["oid"];
		$this->acounter++;
		$alias = sprintf("#%s%d#",$this->defs[$this->def_id]["alias"],$this->acounter);
		$this->counter++;
		$this->chlinks[$this->counter] = $args["url"];
		$this->dellinks[$id] = $id;
		$this->t->merge_data(array(
			"title" => $this->defs[$this->def_id]["title"],
			"check" => sprintf("<input type='checkbox' name='check' value='%d'>",$id),
			"link" => sprintf("<input type='checkbox' name='link[%d]' value='1' %s>",$id,$this->aliaslinks[$id] ? "checked" : ""),
			"icon"	=> sprintf("<img src='%s'>",get_icon_url($this->defs[$this->def_id]["class_id"],"")),
			"alias" => sprintf("<input type='text' size='5' value='%s' onClick='this.select()' onBlur='this.value=\"%s\"'>",$alias,$alias),
		));
	}

	////
	// !Gets all aliases for an object
	function get_oo_aliases($args = array())
	{
		extract($args);
		$oid = sprintf("%d",$oid);
		$ret_type = ($ret_type) ? $ret_type : GET_ALIASES_BY_CLASS;
		$this->id = $oid;
		$this->_init_aliases();
		$q = "SELECT aliases.*, objects.class_id AS class_id, objects.name AS name
			FROM aliases
			LEFT JOIN objects ON (aliases.target = objects.oid)
			WHERE source = '$oid' ORDER BY aliases.id";
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			$row["aliaslink"] = $this->obj_data["meta"]["aliaslinks"][$row["target"]];
			if ($ret_type == GET_ALIASES_BY_CLASS)
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
	// oid(int) - object oid
	function parse_oo_aliases($oid,&$source,$args = array())
	{
		extract($args);
		if (is_array($meta["aliases"]))
		{
//			print "from cache ";
			$aliases = $meta["aliases"];
		}
		else
		{
//			print "from db ";
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

		$construct = get_instance("construct");
		$this->aliases = $aliases;

		$by_alias = array();
		$this->id = $oid;
		$this->_init_aliases();
		foreach($this->defs as $key => $val)
		{
			$by_alias[$val["alias"]] = $key;
		}  

		preg_match_all("/(#)(\w+?)(\d+?)(v|k|p|)(#)/i",$source,$matches,PREG_SET_ORDER);

		if (is_array($matches))
		{
			foreach ($matches as $key => $val)
			{
				// if nothing comes up, we just replace it with a empty string
				// or perhaps we shouldn't? 
				$replacement = "";

				// reference to $this->defs
				$defref = $by_alias[$val[2]];

				// store the class id of the alias into a temprary variable for later use
				$clid = $this->defs[$defref]["class_id"];
				$emb_obj_name = "emb" . $clid;

				$idx = $val[3] - 1;

				if (not(is_object($$emb_obj_name)))
				{
					$class_name = $this->defs[$defref]["class"];
					if ($class_name)
					{
						// load and create the class needed for that alias type
						classload($class_name);
						$$emb_obj_name = new $class_name;
					};

				};

				if ( is_object($$emb_obj_name) )
				{

					$params = array(
						"oid" => $oid,
						"matches" => $val,
						"alias" => $aliases[$clid][$idx],
						"tpls" => &$args["templates"],
					);

					global $DEBUG;
					if ($DEBUG)
					{
						print "<pre>";
						print_r($aliases);
						print_r($val);
						print "clid = $clid<br>";
						print "idx = $idx<br>";
					};
					$construct->load($clid,$aliases[$clid][$idx]["target"],$params);

					$repl = $$emb_obj_name->parse_alias($params);
				
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
					

				}

				if ($inplace)
				{
					$this->tmp_vars = array($inplace => $replacement);
					$replacement = "";
				};
					
				$source = str_replace($val[0],$replacement,$source);

			}
		};
		upd_instance("construct",$construct);
		//return $source;
	}

	function get_flat_list()
	{
		$this->flat_list = array();
		return $this->flat_list;
	}

	////
	// Returns the variables createad by parse_oo_alias
	function get_vars()
	{
		return (is_array($this->tmp_vars)) ? $this->tmp_vars : array();
	}
};
?>
