<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_v2.aw,v 1.17 2004/02/03 12:32:52 duke Exp $
// forum_v2.aw.aw - Foorum 2.0 
/*

	@classinfo syslog_type=ST_FORUM
	@classinfo relationmgr=yes

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property topic_folder type=relpicker reltype=RELTYPE_TOPIC_FOLDER
	@caption Teemade kataloog
	@comment Sellest kataloogist võetakse foorumi teemasid
	
	@property address_folder type=relpicker reltype=RELTYPE_ADDRESS_FOLDER
	@caption Listiliikmete kataloog
	@comment Sellesse kataloogi paigutatakse "listi liikmete" objektid

	@property topics_on_page type=chooser 
	@caption Teemasid lehel

	@property comments_on_page type=chooser 
	@caption Postitusi lehel

	@property topic_depth type=chooser default=1 
	@caption Teemade sügavus

	@property topic_selector type=text group=topic_selector no_caption=1
	@caption Teemade tasemed

	@property container type=text store=no group=container no_caption=1
	@caption Konteiner

	@property topic type=hidden store=no group=container
	@caption Topic ID (sys)

	@property subaction type=hidden store=no group=container
	@caption Subact (sys)

	@property show type=callback callback=callback_gen_contents store=no no_caption=1 group=contents
	@caption Foorumi sisu

	@property add_topic type=callback callback=callback_gen_add_topic store=no no_caption=1 group=add_topic
	@caption Lisa teema
	
	@property add_comment type=callback callback=callback_gen_add_comment store=no no_caption=1 group=add_comment
	@caption Lisa kommentaar

	@property style_donor type=relpicker group=styles reltype=RELTYPE_STYLE_DONOR
	@caption Stiilidoonor
	
	@property style_caption type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Tabeli pealkirja stiil
	
	@property style_l1_folder type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Esimese taseme folderi stiil

	@property style_folder_caption type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Folderi pealkirja stiil
	
	@property style_folder_topic_count type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Folderi teemade arvu stiil
	
	@property style_folder_comment_count type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Folderi postituste arvu stiil
	
	@property style_folder_last_post type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Folderi viimase postituse stiil
	
	@property style_topic_caption type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema pealkirja stiil
	
	@property style_topic_replies type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema vastuste arvu stiil
	
	@property style_topic_author type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema autori stiil
	
	@property style_topic_last_post type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema viimase postituste stiil

	@property style_comment_user type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Kommentaari kasutajainfo stiil

	@property style_comment_time type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Kommentaari aja stiil

	@property style_comment_text type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Kommentaari teksti stiil

	@property style_form_caption type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Sisestusvormi pealkirja stiil

	@property style_form_text type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Sisestusvormi teksti stiil

	@property style_form_element type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Sisestusvormi elemendi stiil

	@groupinfo container caption=Foorum submit=no
	@groupinfo topic_selector caption="Teemad" 
	@groupinfo contents caption=Sisu submit=no parent=container
	@groupinfo styles caption=Stiilid
	@groupinfo add_topic caption="Lisa teema" parent=container
	@groupinfo add_comment caption="Lisa kommentaar" parent=container

	@reltype TOPIC_FOLDER value=1 clid=CL_MENU
	@caption teemade kataloog

	@reltype ADDRESS_FOLDER value=2 clid=CL_MENU
	@caption listiliikmete kataloog

	@reltype STYLE value=3 clid=CL_CSS
	@caption Stiil

	@reltype STYLE_DONOR value=4 clid=CL_FORUM_V2
	@caption võta stiilid

*/

class forum_v2 extends class_base
{
	function forum_v2()
	{
		$this->init(array(
			"tpldir" => "forum",
			"clid" => CL_FORUM_V2,
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "topics_on_page":
			case "comments_on_page":
				$data["options"] = array(5 => 5,10 => 10,15 => 15,20 => 20,25 => 25,30 => 30);
				break;

			case "topic_depth":
				$data["options"] = array("1" => "1","2" => "2","3" => "3","4" => "4","5" => "5");
				break;

			case "topic_selector":
				$data["value"] = $this->get_topic_selector($arr);
				break;

			case "topic":
				if (!empty($arr["request"]["topic"]))
				{
					$data["value"] = $arr["request"]["topic"];
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "subaction":
				if (!empty($arr["request"]["topic"]))
				{
					$data["value"] = "submit_comment";
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

		};	
		return $retval;
	} 

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "add_topic":
				$this->create_forum_topic($arr);
				break;

			case "add_comment":
				$this->create_forum_comment($arr);
				break;

			case "topic_selector":
				$this->update_topic_selector($arr);
				break;

			case "container":
				// now, I have to 
				$this->update_contents($arr);
				break;
		}
		return $retval;
	}

	function callback_pre_edit($arr)
	{
		$this->rel_id = $arr["request"]["rel_id"];
		$this->rel_id = aw_global_get("section");
	}

	function callback_gen_contents($args = array())
	{
		classload("layout/active_page_data");
		$this->style_data = array();
		/*
		$fld = $args["obj_inst"]->prop("topic_folder");
		if (!is_numeric($fld))
		{
			return false;
		};			
		*/

		$this->obj_inst = $args["obj_inst"];
		$style_donor = $this->obj_inst->prop("style_donor");
		if (!empty($style_donor))
		{
			$this->style_donor_obj = new object($style_donor);
		};
		$this->_add_style("style_caption");
		
		//$args["fld"] = $fld;

		if (is_numeric($args["request"]["topic"]))
		{
			$retval = $this->draw_topic($args);
		}
		elseif (is_numeric($args["request"]["folder"]))
		{
			$retval = $this->draw_folder($args);
		}
		else
		{
			$retval = $this->draw_all_folders($args);
		};

		// bummer ... I need to be able to draw multiple topics

		$prop = $args["prop"];
		//$prop["type"] = "text";
		$prop["value"] = $retval;
		return array($prop);
	}	

	function draw_all_folders($args = array())
	{
		extract($args);

		$this->read_template("forum.tpl");

		$c = "";
		
		$this->_add_style("style_l1_folder");
		$this->_add_style("style_folder_caption");
		$this->_add_style("style_folder_topic_count");
		$this->_add_style("style_folder_comment_count");
		$this->_add_style("style_folder_last_post");
		$this->vars($this->style_data);

		// so now I need a function that gives me all folders .. hm .... can I use object_tree
		// for that then? no, obviously not.

		// it is important to know that comments may only be at the lowest level

		$depth = $args["obj_inst"]->prop("topic_depth");
		if (empty($depth))
		{
			$depth = 1;
		};

		$this->depth = $depth;
		$this->exclude = $args["obj_inst"]->meta("exclude");
		$this->exclude_subs = $args["obj_inst"]->meta("exclude_subs");

		$this->level = 1;
		$this->group = $args["request"]["group"];

		$conns = $args["obj_inst"]->connections_from(array(
			"type" => RELTYPE_TOPIC_FOLDER,
		));

		$c = "";
		// I need to consolidate this code in some way with the code that draws 
		// the topic selector .. oh .. herr god, how the fuck am I going to do that?
		foreach($conns as $conn)
		{
			$c .= $this->_draw_one_level(array(
				//"parent" => $args["fld"],
				"parent" => $conn->prop("to"),
				"id" => $args["obj_inst"]->id(),
			));
		};



		$this->vars(array(
			"forum_contents" => $c,
		));

		$rv = $this->parse();
		return $rv;
	}

	function _draw_one_level($arr)
	{
		if ($this->level == $this->depth)
		{
			$c .= $this->_draw_last_level(array(
				"parent" => $arr["parent"],
				"id" => $arr["id"],
			));
		}
		else
		{
			$folder_list = new object_list(array(
				"parent" => $arr["parent"],
				"class_id" => CL_MENU,
			));
			for ($folder_obj = $folder_list->begin(); !$folder_list->end(); $folder_obj = $folder_list->next())
			{
				$this->vars(array(
					"name" => $folder_obj->name(),
					"comment" => $folder_obj->comment(),
					"open_l1_url" => $this->mk_my_orb("change",array(
						"id" => $arr["id"],
						"c" => $folder_obj->id(),
						"group" => $this->group,
						"section" => $this->rel_id,
						"_alias" => get_class($this),
					)),
				));

				$tplname = "L" . $this->level . "_FOLDER";

				$tplname = "FOLDER";
				$this->vars(array(
					"spacer" => str_repeat("&nbsp;",6*($this->level-1)),
				));

				if (empty($this->exclude[$folder_obj->id()]))
				{
					$c .= $this->parse($tplname);
				};

				$this->level++;
				$c .= $this->_draw_one_level(array(
					"parent" => $folder_obj->id(),
					"id" => $arr["id"],
				));
				$this->level--;

				// so if I want to make this work, I need to figure out how
				// many levels do I have to draw. I have to make this function recursive.
				/*
				if (empty($args["request"]["c"]) || ($args["request"]["c"] == $folder_obj->id()))
				{
					$c .= $this->_draw_last_level(array(
						"parent" => $folder_obj->id(),
						"forum_id" => $args["obj_inst"]->id(),
					));
				}
				else
				*/



			}
		};
		return $c;
	}

	// needs at least one argument .. the parent
	function _draw_last_level($arr)
	{
		// for each first level folder, figure out all the second level
		// folders.
		$sub_folder_list = new object_list(array(
			"parent" => $arr["parent"],
			"class_id" => CL_MENU,
		));

		// for each second level folder, figure out the amount of topics
		// and posts 
		list($topic_counts,$topic_list) = $this->get_topic_list(array(
			"parents" => $sub_folder_list->ids(),
		));

		// ja iga alamtopicu jaoks on mul vaja teada, mitu
		// teemat seal on.
		for ($sub_folder_obj = $sub_folder_list->begin(); !$sub_folder_list->end(); $sub_folder_obj = $sub_folder_list->next())
		{
			list(,$comment_count) = $this->get_comment_counts(array(
				"parents" => $topic_list[$sub_folder_obj->id()],
			));
			
			$last = $this->get_last_comments(array(
				"parents" => $topic_list[$sub_folder_obj->id()],
			));

			$mdate = $last["created"];
			$datestr = empty($date) ? "" : $this->time2date($mdate,2);

			$lv = $this->level - 2;
			if ($lv < 0)
			{
				$lv = 0;
			};

			$this->vars(array(
				"name" => $sub_folder_obj->name(),
				"topic_count" => (int)$topic_counts[$sub_folder_obj->id()],
				"comment_count" => (int)$comment_count,
				"last_createdby" => $last["createdby"],
				"last_date" => $datestr,
				"spacer" => str_repeat("&nbsp;",6*($lv)),
				"open_topic_url" => $this->mk_my_orb("change",array(
					"id" => $arr["id"],
					"folder" => $sub_folder_obj->id(),
					"group" => $this->group,
					"section" => $this->rel_id,
					"_alias" => get_class($this),
				)),
			));
			$c .= $this->parse("LAST_LEVEL");
		};
		return $c;
	}

	function update_contents($arr)
	{
		if ($arr["request"]["subaction"] == "submit_comment")
		{
			$this->submit_comment($arr["request"]);
			$this->topic_id = $arr["request"]["topic"];
		};
	}

	function get_folder_tree($arr)
	{
		$this->tree = array();


	}

	////
	// !antakse ette parent ja sügavus ja siis tehakse lotsa tööd
	function _rec_folder_tree($arr)
	{
		$folder_list = new object_list(array(
			"parent" => $arr["parent"],
			"class_id" => CL_MENU,
		));
		for ($folder_obj = $folder_list->begin(); !$folder_list->end(); $folder_obj = $folder_list->next())
		{
			$this->tree[$folder_obj->parent()][$folder_obj->id()] = $folder_obj->name();
		};
	}

	////
	// !Draws the contents of a single folder
	function draw_folder($args = array())
	{
		extract($args);
		$topics_on_page = $args["obj_inst"]->prop("topics_on_page");
		if (empty($topics_on_page))
		{
			$topics_on_page = 5;
		};

		$topic_obj = new object($args["request"]["folder"]);

		$this->read_template("folder.tpl");

		$obj_chain = $this->get_obj_chain(array(
			"oid" => $topic_obj->id(),
			"stop" => $args["request"]["folder"],
		));

		$path = array();
		foreach($obj_chain as $key => $name)
		{
			if ($key == $fld)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $args["obj_inst"]->id(),"group" => $args["request"]["group"],"section" => $this->rel_id,"_alias" => get_class($this))),
					"caption" => $name,
				));
			}
			else
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $args["obj_inst"]->id(),"c" => $key,"group" => $args["request"]["group"],"section" => $this->rel_id,"_alias" => get_class($this))),
					"caption" => $name,
				));


			}
			$path[] = $name;
		};
		
		$this->_add_style("style_topic_caption");
		$this->_add_style("style_topic_replies");
		$this->_add_style("style_topic_author");
		$this->_add_style("style_topic_last_post");
		$this->vars($this->style_data);

		$subtopic_list = new object_list(array(
			"parent" => $topic_obj->id(),
			"class_id" => CL_MSGBOARD_TOPIC,
		));

		$c = $pager = "";

		list($comm_counts,) = $this->get_comment_counts(array(
			"parents" => $subtopic_list->ids(),
		));
		
		$tcount = sizeof($subtopic_list->ids());
		$num_pages = (int)(($tcount / $topics_on_page) + 1);
		$selpage = (int)$args["request"]["page"];
		if ($selpage == 0)
		{
			$selpage = 1;
		};
		if ($selpage > $num_pages)
		{
			$selpage = $num_pages;
		};

		$from = ($selpage - 1) * $topics_on_page + 1;
		$to = $from + $topics_on_page - 1;
		$cnt = 0;
				
		for ($subtopic_obj = $subtopic_list->begin(); !$subtopic_list->end(); $subtopic_obj = $subtopic_list->next())
		{
			$cnt++;
			if (!between($cnt,$from,$to))
			{
				continue;
			};
			$last = $this->get_last_comments(array(
				"parents" => array($subtopic_obj->id()),
			));
			
			$creator = $subtopic_obj->createdby();

			if (!$last)
			{
				$last = array(
					"created" => $subtopic_obj->created(),
					"createdby" => $creator->createdby(),
				);
			};

			$this->vars(array(
				"name" => $subtopic_obj->name(),
				"comment_count" => (int)$comm_counts[$subtopic_obj->id()],
				"last_date" => $this->time2date($last["created"],2),
				"last_createdby" => $last["createdby"],
				"author" => $creator->name(),
				"open_topic_url" => $this->mk_my_orb("change",array(
						"id" => $args["obj_inst"]->id(),
						"group" => $args["request"]["group"],
						"topic" => $subtopic_obj->id(),
						"section" => aw_global_get("section"),
						"_alias" => get_class($this),
				)),
			));

			$c .= $this->parse("SUBTOPIC");

		};

		// draw pager
		for ($i = 1; $i <= $num_pages; $i++)
		{
			$this->vars(array(
				"num" => $i,
				"url" => $this->mk_my_orb("change",array(
					"id" => $args["obj_inst"]->id(),
						"folder" => $topic_obj->id(),
						"page" => $i,
						"group" => $args["request"]["group"],
						"section" => aw_global_get("section"),
						"_alias" => get_class($this),
				)),
			));
			$pager .= $this->parse($selpage == $i ? "active_page" : "page");
		};

		$this->vars(array(
			"SUBTOPIC" => $c,
			"name" => $topic_obj->name(),
			"path" => join(" &gt; ",array_reverse($path)),
			"active_page" => $pager,
			"add_topic_url" => $this->mk_my_orb("add_topic",array(
				"id" => $args["obj_inst"]->id(),
				"section" => aw_global_get("section"),
				"folder" => $args["request"]["folder"],
				"_alias" => get_class($this),
			)),
		));
		return $this->parse();
	}

	function draw_topic($args = array())
	{
		$fld = $args["fld"];
		$this->read_template("topic.tpl");

		$topic_obj = new object($args["request"]["topic"]);

		$this->_add_style("style_comment_user");
		$this->_add_style("style_comment_time");
		$this->_add_style("style_comment_text");
		$this->vars($this->style_data);

		$comments_on_page = $args["obj_inst"]->prop("topics_on_page");
		if (empty($comments_on_page))
		{
			$comments_on_page = 5;
		};
		
		$t = get_instance("contentmgmt/forum/forum_comment");
		$comments = $t->get_comment_list(array("parent" => $args["request"]["topic"]));

		$c = $pager = "";
		
		$tcount = sizeof($comments);
		$num_pages = (int)(($tcount / $comments_on_page) + 1);
		$selpage = (int)$args["request"]["page"];
		if ($selpage == 0)
		{
			$selpage = 1;
		};
		if ($selpage > $num_pages)
		{
			$selpage = $num_pages;
		};

		$from = ($selpage - 1) * $comments_on_page + 1;
		$to = $from + $comments_on_page - 1;
		$cnt = 0;

		if (is_array($comments))
		{
			foreach($comments as $comment)
			{
				$cnt++;
				if (!between($cnt,$from,$to))
				{
					continue;
				};
				$this->vars(array(
					"name" => $comment["name"],
					"commtext" => nl2br($comment["commtext"]),
					"date" => $this->time2date($comment["created"],2),
					"createdby" => $comment["createdby"],
				));
				$c .= $this->parse("COMMENT");
			};
		};		
		
		// draw pager
		for ($i = 1; $i <= $num_pages; $i++)
		{
			$this->vars(array(
				"num" => $i,
				"url" => $this->mk_my_orb("change",array("id" => $args["obj_inst"]->id(),"topic" => $topic_obj->id(),"page" => $i,"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
			));
			$pager .= $this->parse($selpage == $i ? "active_page" : "page");
		};
	
		// path drawing starts
		$path = array();
		$obj_chain = $this->get_obj_chain(array(
			"oid" => $args["request"]["topic"],
			"stop" => $args["obj_inst"]->prop("topic_folder"),
		));
		foreach($obj_chain as $key => $name)
		{
			if ($key == $fld)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $args["obj_inst"]->id(),"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
					"caption" => $name,
				));
			}
			else
			{
				$obj = new object($key);
				if ($obj->class_id() == CL_MENU)
				{
					if ($obj["parent"] != $fld)
					{
						$name = html::href(array(
							"url" => $this->mk_my_orb("change",array("id" => $args["obj_inst"]->id(),"group" => $args["request"]["group"],"folder" => $obj->id(),"section" => aw_global_get("section"),"_alias" => get_class($this))),
							"caption" => $name,
						));
					}
					else
					{
						$name = html::href(array(
							"url" => $this->mk_my_orb("change",array("id" => $args["obj_inst"]->id(),"group" => $args["request"]["group"],"c" => $obj->id(),"section" => aw_global_get("section"),"_alias" => get_class($this))),
							"caption" => $name,
						));
					};
				};
						
			};
			$path[] = $name;
		};

		// path drawing ends .. sucks

		$this->vars(array(
			"active_page" => $pager,
			"name" => $topic_obj->name(),
			"comment" => $topic_obj->comment(),
			"COMMENT" => $c,
			"path" => join(" &gt; ",array_reverse($path)),
		));

		$rv = $this->parse();

		// XXX: implement topic lock
		$this->read_template("add_comment.tpl");
		$this->_add_style("style_form_caption");
		$this->_add_style("style_form_text");
		$this->_add_style("style_form_element");
		$this->vars($this->style_data);
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_comment",array(
				"id" => $args["obj_inst"]->id(),
				"section" => aw_global_get("section"),
				"topic" => $topic_obj->id(),
			)),
		));
		return $rv . $this->parse();

	}

	function callback_mod_tab($args = array())
	{
		$retval = true;
		if ($args["id"] == "add_topic")
		{
			// we can only add new topics under new folders
			if (empty($args["request"]["folder"]))
			{
				$retval = false;
			};
			$args["link"] .= "&folder=" . $args["request"]["folder"];
		};
		if ($args["id"] == "add_comment")
		{
			// we can only add comments under topics
			if (empty($args["request"]["topic"]))
			{
				$retval = false;
			};
			$args["link"] .= "&topic=" . $args["request"]["topic"];
		};
		return $retval;
	}

	function callback_gen_add_topic($args = array())
	{
		$t = get_instance("contentmgmt/forum/forum_topic");
		$t->init_class_base();
		$emb_group = "general";
		if ($this->event_id && $args["request"]["cb_group"])
		{
			$emb_group = $args["request"]["cb_group"];
		};
		$all_props = $t->get_active_properties(array(
			"group" => $emb_group,
		));

		$t->request = $args["request"];

		$all_props[] = array("type" => "hidden","name" => "class","value" => "forum_topic");
		$all_props[] = array("type" => "hidden","name" => "action","value" => "submit");
		$all_props[] = array("type" => "hidden","name" => "group","value" => $emb_group);
		$all_props[] = array("type" => "hidden","name" => "parent","value" => $args["request"]["folder"]);

		return $t->parse_properties(array(
			"properties" => $all_props,
			"name_prefix" => "emb",
		));
	}
	
	function callback_gen_add_comment($args = array())
	{
		$t = get_instance("contentmgmt/forum/forum_comment");
		$t->init_class_base();
		$emb_group = "general";
		if ($this->event_id && $args["request"]["cb_group"])
		{
			$emb_group = $args["request"]["cb_group"];
		};

		$all_props = $t->get_active_properties(array(
			"group" => $emb_group,
		));
		
		$all_props[] = array("type" => "hidden","name" => "class","value" => "forum_comment");
		$all_props[] = array("type" => "hidden","name" => "action","value" => "submit");
		$all_props[] = array("type" => "hidden","name" => "group","value" => $emb_group);
		$all_props[] = array("type" => "hidden","name" => "parent","value" => $args["request"]["topic"]);

		return $t->parse_properties(array(
			"properties" => $all_props,
			"name_prefix" => "emb",
		));
	}

	function update_container($arr)
	{
		print "updating container<bR>";
		print "<pre>";
		print_R($arr);
		print "</pre>";

	}

	function create_forum_topic($args)
	{
		$t = get_instance("contentmgmt/forum/forum_topic");
                $emb = $args["request"]["emb"];
                $t->id_only = true;
                if (isset($emb["group"]))
                {
                        $this->emb_group = $emb["group"];
                };
		$emb["forum_id"] = $args["request"]["id"];
                $this->topic_id = $t->submit($emb);
		return PROP_OK;
	}
	
	function create_forum_comment($args)
	{
		$t = get_instance("contentmgmt/forum/forum_comment");
                $emb = $args["request"]["emb"];
                $t->id_only = true;
                if (isset($emb["group"]))
                {
                        $this->emb_group = $emb["group"];
                };
		// now .. this is where I will have to let the comment class now whether to send
		// out messages or not
                $this->comm_id = $t->submit($emb);
		$this->topic_id = $emb["parent"];
		$topic_obj = new object($this->topic_id);
		$topic_inst = get_instance("contentmgmt/forum/forum_topic");
		$topic_inst->mail_subscribers(array(
			"id" => $this->topic_id,
			"subject" => "Uus postitus teemas " . $topic_obj->name() . ": " . $emb["name"],
			"message" => $emb["uname"] . " @ " . $this->time2date(time(),2) . "\n\n" . $emb["commtext"],
		));
		return PROP_OK;
	}
	
	function callback_mod_retval($args = array())
	{
		if ($this->topic_id)
		{
			$form_data = &$args["request"];
                	$emb = $args["request"]["emb"];
			$args = &$args["args"];
			$args["folder"] = $emb["parent"];
			$args["topic"] = $this->topic_id;
			$args["group"] = "contents";
			$args["page"] = $args["request"]["page"];
		}
	}

	function get_topic_list($args = array())
	{	$topic_count = $tlist = array();
		if (sizeof($args["parents"]) != 0)
		{
			$topic_list = new object_list(array(
				"parent" => $args["parents"],
				"class_id" => CL_MSGBOARD_TOPIC,
			));	
			for ($topic = $topic_list->begin(); !$topic_list->end(); $topic = $topic_list->next())
			{
				$topic_count[$topic->parent()]++;
				$tlist[$topic->parent()][] = $topic->id();
			};
		};
		return array($topic_count,$tlist);
	}
	
	function get_comment_counts($args = array())
	{
		$comment_count = array();
		$grand_total = 0;
		if (sizeof($args["parents"]) != 0)
		{
			$q = sprintf("SELECT count(*) AS cnt,parent FROM objects WHERE parent IN (%s) AND class_id = '%d'
					AND status != 0 GROUP BY parent",join(",",$args["parents"]),CL_COMMENT);
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$comment_count[$row["parent"]] = $row["cnt"];
				$grand_total += $row["cnt"];
			};
		};
		return array($comment_count,$grand_total);
	}

	function get_last_comments($args = array())
	{
		$retval = array();
		if (sizeof($args["parents"]) != 0)
		{
			$q = sprintf("SELECT parent,created,createdby FROM objects WHERE parent IN (%s) AND class_id = '%d'
				AND status != 0 ORDER BY created DESC LIMIT 1",join(",",$args["parents"]),CL_COMMENT);
			$this->db_query($q);
			$retval = $this->db_next();
		};
		return $retval;
	}

	function _add_style($name)
	{
		// this right now takes data from the currently loaded object
		if (is_object($this->style_donor_obj))
		{
			$st_data = $this->style_donor_obj->prop($name);
		}
		else
		{
			$st_data = $this->obj_inst->prop($name);
		};
		if ($st_data)
		{
			active_page_data::add_site_css_style($st_data);
			$this->style_data[$name] = "st" . $st_data;
		};
	}

	function get_topic_selector($arr)
	{
		// I need to create a list of topics and add a checkbox for each one
			// I need to create a list of topics and add a checkbox for each one
		$depth = $arr["obj_inst"]->prop("topic_folder");
		$this->rv = "";

		$ot = new object_tree(array(
			   "parent" => $arr["obj_inst"]->prop("topic_folder"),
			   "class_id" => CL_MENU,
		));

		$this->ot = $ot;

		$this->read_template("topic_selector.tpl");
		$this->exclude = $arr["obj_inst"]->meta("exclude");
		$this->exclude_subs = $arr["obj_inst"]->meta("exclude_subs");

		$this->_do_rec_topic(array(
			"parent" => $arr["obj_inst"]->prop("topic_folder"),
		));

		$this->vars(array(
			"ITEM" => $this->rv,
		));
		return $this->parse();
	}


	// so now, how do I do the consolidation?
	function _do_rec_topic($arr)
	{
		static $level = 0;
		$litems = $this->ot->level($arr["parent"]);
		foreach($litems as $item)
		{
			$this->vars(array(
				"caption" => $item->name(),
				"id" => $item->id(),
				"spacer" => str_repeat("&nbsp;",$level*3),
				"exclude" => checked($this->exclude[$item->id()]),
				"exclude_subs" => checked($this->exclude_subs[$item->id()]),
			));
			$this->rv .= $this->parse("ITEM");
			$level++;
			$this->_do_rec_topic(array("parent" => $item->id()));
			$level--;
		};
	}

	function update_topic_selector($arr)
	{
		$arr["obj_inst"]->set_meta("exclude",$arr["request"]["exclude"]);
		$arr["obj_inst"]->set_meta("exclude_subs",$arr["request"]["exclude_subs"]);
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		$this->classconfig = array(
			"hide_tabs" => 1,
			"relationmgr" => false,
		);

		// nii. see paneb selle paika. Ja nüt, mk_my_orb peaks suutma detectida kas 
		// relobj_id on püsti ja kui on, siis tegema kõik lingid selle baasil.
		// ah? mis?
		$act = isset($_GET["action"]) ? $_GET["action"] : "change";
		return $this->$act(array(
			"id" => $alias["target"],
			"action" => isset($_GET["action"]) ? $_GET["action"] : "view",
			"rel_id" => $args["alias"]["relobj_id"],
			"folder" => $_GET["folder"],
			"topic" => $_GET["topic"],
			"c" => $_GET["c"],
			"cb_part" => 1,
			"fxt" => 1,
			"group" => isset($_GET["group"]) ? $_GET["group"] : "container",
		));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob->name(),
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=add_topic params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function add_topic($arr)
	{
		$this->read_template("add_topic.tpl");
		$this->obj_inst = new object($arr["id"]);
		$this->_add_style("style_form_caption");
		$this->_add_style("style_form_text");
		$this->_add_style("style_form_element");
		$this->vars($this->style_data);
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_topic",array(
				"id" => $arr["id"],
				"section" => aw_global_get("section"),
				"folder" => $arr["folder"],
			)),
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_topic params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_topic($arr)
	{
		$t = get_instance("contentmgmt/forum/forum_topic");
                $emb = $arr;
		$emb["parent"] = $arr["folder"];
                $t->id_only = true;
		$emb["forum_id"] = $arr["id"];
		unset($emb["id"]);
                $this->topic_id = $t->submit($emb);
		return $this->mk_my_orb("change",array(
			"group" => "container",
			"section" => $arr["section"],
			"folder" => $arr["folder"],
			"_alias" => get_class($this),
		));
	}
	
	/**  
		
		@attrib name=submit_comment params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_comment($arr)
	{
		$t = get_instance("contentmgmt/forum/forum_comment");
                $emb = $arr;
                $t->id_only = true;
		unset($emb["id"]);
		$emb["parent"] = $arr["topic"];
                $this->comm_id = $t->submit($emb);

		return $this->mk_my_orb("change",array(
			"id" => $arr["id"],
			"group" => "container",
			"section" => $arr["section"],
			"topic" => $arr["topic"],
			"_alias" => get_class($this),
		));
	}

};
?>
