<?php
// aliasmgr.aw - Alias Manager
// $Header: /home/cvs/automatweb_dev/classes/Attic/aliasmgr.aw,v 2.0 2001/11/08 10:30:53 duke Exp $

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
		$this->defs["images"] = array(
				"alias" => "p",
				"title" => "Pildid",
				"table" => "images",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->id),"images"),
		);

		$this->defs["links"] = array(
				"alias" => "l",
				"title" => "Lingid",
				"table" => "extlinks",
				"addlink" => $this->mk_my_orb("new", array("docid" => $this->id, "parent" => $this->parent),"links"),
		);
		
		$this->defs["tables"] = array(
				"alias" => "t",
				"title" => "Tabelid",
				"table" => "tables",
				"addlink" => $this->mk_my_orb("add_doc", array("id" => $this->id, "parent" => $this->parent),"table"),
		);
		
		$this->defs["forms"] = array(
				"alias" => "f",
				"title" => "Vormid",
				"table" => "forms",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form"),
		);
		
		$this->defs["files"] = array(
				"alias" => "v",
				"title" => "Failid",
				"table" => "files",
				"addlink" => $this->mk_my_orb("new",array("id" => $this->id, "parent" => $this->parent),"file"),
		);
		
		$this->defs["graphs"] = array(
				"alias" => "g",
				"title" => "Graafikud",
				"table" => "graphs",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"graph"),
		);
		
		$this->defs["galleries"] = array(
				"alias" => "y",
				"title" => "Galeriid",
				"table" => "galleries",
				"addlink" => $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"gallery"),
		);
		
		$this->defs["form_chains"] = array(
				"alias" => "c",
				"title" => "Vormipärjad",
				"table" => "formchains",
				"addlink" =>  $this->mk_my_orb("new", array("parent" => $this->parent,"alias_doc" => $this->id),"form_chain"),
		);

		$this->defs["link_collections"] = array(
				"alias" => "x",
				"title" => "Lingikogu oksad",
				"table" => "link_collections",
				"addlink" => $this->mk_orb("pick_branch",array("parent" => $this->id),"link_collection"),
		);

		$this->defs["forums"] = array(
				"alias" => "o",
				"title" => "Foorumid",
				"table" => "forums",
				"addlink" => $this->mk_my_orb("new",array("parent" => $this->id),"forum"),
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
			"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "description",
			"caption" => "Kirjeldus",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "alias",
			"caption" => "Alias",
			"talign" => "center",
			"nowrap" => "1",
                ));
		$this->t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Muutja",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
                ));
		$this->t->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
                ));

		$this->_link_aliases();
		$this->_link_collection_aliases();
		$this->_forum_aliases();
		$this->_img_aliases();
		$this->_table_aliases();
		$this->_form_aliases();
		$this->_form_chain_aliases();
		$this->_file_aliases();
		$this->_graph_aliases();
		$this->_gallery_aliases();
		$this->vars(array(
			"table" => $this->contents,
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
		$this->_initialize($this->defs["images"]);
		$img->list_by_object($this->id,0);
		while($row = $img->db_next())
		{
			$this->t->define_data(array(
				"name" => $row["name"],
				"alias" => sprintf("#p%d#",$row["idx"]),
				"comment" => $row["comment"],
				"modified"    => $this->time2date($row["modified"],2),
				"modifiedby" => $row["modifiedby"],
			));
		}
		$this->_finalize($this->defs["images"]);
	}

	function _link_aliases($args = array())
	{
		$this->_initialize($this->defs["links"]);
		$links = $this->get_aliases_for($this->id,CL_EXTLINK,$_sby, $s_link_order,array("extlinks" => "extlinks.id = objects.oid"));
		$lc = 0;
		$linklist = array();
		reset($links);
		$lc = 0;
		while (list(,$v) = each($links))
		{	
			$lc++;
			$this->t->define_data(array(
				"name"                => $v["name"],
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"address"             => $v["url"],
				"alias"               => "#l".$lc."#",	
			));
		};
		$this->_finalize(array("title" => "Lingid"));
	}
	
	function _form_aliases($args = array())
	{
		$this->_initialize($this->defs["forms"]);	
		$forms = $this->get_aliases_for($this->id,CL_FORM,$s_form_sortby, $s_form_order);
		$fc = 0;
		$formlist = array();
		reset($forms);
		while (list(,$v) = each($forms))
		{
			$fc++;
			$this->t->define_data(array(
				"name"                => $v["name"],
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#f".$fc."#","comment" => $v["comment"],
				"id"                  => $v["id"],		
			));
		};
		$this->_finalize(array("title" => "Vormid"));
	}

	function _file_aliases($args = array())
	{

		$this->_initialize($this->defs["files"]);
		$files = $this->get_aliases_for($this->id,CL_FILE,$s_file_sortby, $s_file_order);
		$fic = 0;
		$filelist = array();
		reset($files);
		while (list(,$v) = each($files))
		{
			$fic++;
			$this->t->define_data(array(
				"name"                => $v["name"],
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#v".$fic."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
		}
		$this->_finalize(array("title" => "Failid"));
	}

	function _table_aliases($args = array())
	{
		$this->_initialize($this->defs["tables"]);
		$tables = $this->get_aliases_for($this->id,CL_TABLE,$x,$y);
		$tc = 0;
		while (list(,$v) = each($tables))
		{
			$tc++;
			$this->t->define_data(array(
				"name"                => $v["name"],
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#t".$tc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
		}
		$this->_finalize(array("title" => "Tabelid"));
	}

	function _form_chain_aliases($args = array())
	{
		$this->_initialize($this->defs["form_chains"]);
		$ffl = "";
		$chains = $this->get_aliases_for($this->id,CL_FORM_CHAIN,$s_chain_sortby, $s_chain_order);
		$cc = 0;
		$chainlist = array();
		reset($chains);
		while (list(,$v) = each($chains))
		{
			$cc++;
			$this->t->define_data(array(
				"name"                => $v["name"],
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#c".$cc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
		};
		$this->_finalize(array("title" => "Vormipärjad"));
	}

	function _gallery_aliases($args = array())
	{
		$this->_initialize($this->defs["galleries"]);
		$galleries = $this->get_aliases_for($this->id,CL_GALLERY,$s_gallery_sortby, $s_gallery_order);
		$galc = 0;
		$gallist = array();
		reset($galleries);
		while (list(,$v) = each($galleries))
		{
			$galc++;
			$this->t->define_data(array(
				"name"                => $v["name"],
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#y".$galc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
		}
		$this->_finalize(array("title" => "Galeriid"));
	}


	function _link_collection_aliases($args = array())
	{
		$this->_initialize($this->defs["link_collections"]);
		$link_collections = $this->get_aliases_for($id,CL_LINK_COLLECTION,0,0);
		$lcoll = "";

		if (is_array($link_collections))
		{
			$c = "";
			$lcc = 0;

			foreach($link_collections as $key => $val)
			{
				$lcc++;
				$this->t->define_data(array(
					"name" => $val["name"],
					"comment" => $val["comment"],
					"alias" => "#x$lcc#",
					"modified" => $this->time2date($val["modified"],2),
					"modifiedby" => $val["modifiedby"],
				));	
			}
		}
		$this->_finalize(array("title" => "Lingikogu oksad"));
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
				$this->t->define_data(array(
					"name" => $val["name"],
					"comment" => $val["comment"],
					"alias" => "#o$fcc#",
					"edlink" => $this->mk_my_orb("edit_properties",array("id" => $val["oid"]),"forum"),
					"modified" => $this->time2date($val["modified"],2),
					"modifiedby" => $val["modifiedby"],
				));
			};
		};
		$this->_finalize(array("title" => "Foorumid"));
	}

	function _graph_aliases($args = array())
	{
		$this->_initialize($this->defs["graphs"]);
		$graphs = $this->get_aliases_for($this->id,CL_GRAPH,$s_graph_sortby, $s_graph_order);
		$gc = 0;
		$graphlist = array();
		reset($graphs);
		while (list(,$v) = each($graphs))
		{
			$gc++;
			$this->t->define_data(array(
				"name"                => $v["name"],
				"modified"            => $this->time2date($v["modified"],2),
				"modifiedby"          => $v["modifiedby"],
				"alias"               => "#g".$gc."#","comment" => $v["comment"],
				"id"                  => $v["id"],
			));
		};
		$this->_finalize(array("title" => "Graafikud"));
	}
	
	function _initialize($args = array())
	{
		$this->t->reset_data();
		$this->t->set_attribs(array("prefix" => $args["table"]));
		$this->t->set_header_attribs(array("table" => $args["table"]));
		$this->vars(array(
			"add_link" => $args["addlink"],
		));
	}

	function _finalize($args = array())
	{
		if ($this->table == $args["table"])
		{
			$this->t->sort_by(array("field" => $this->sortby));
		}

		$this->vars(array(
			"contents" => $this->t->draw(),
			"title" => $args["title"],
		));

		$this->contents .= $this->parse("table");
	}
};
?>
