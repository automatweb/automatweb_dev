<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_v2.aw,v 1.6 2003/07/03 16:02:20 duke Exp $
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

	@property topics_on_page type=select
	@caption Teemasid lehel

	@property comments_on_page type=select
	@caption Postitusi lehel

	@property container type=text store=no group=container no_caption=1
	@caption Konteiner

	@property show type=callback callback=callback_gen_contents store=no no_caption=1 group=contents
	@caption Foorumi sisu

	@property add_topic type=callback callback=callback_gen_add_topic store=no no_caption=1 group=add_topic
	@caption Lisa teema
	
	@property add_comment type=callback callback=callback_gen_add_comment store=no no_caption=1 group=add_comment
	@caption Lisa kommentaar

	@groupinfo container caption=Foorum submit=no
	@groupinfo contents caption=Sisu submit=no parent=container
	@groupinfo add_topic caption="Lisa teema" parent=container
	@groupinfo add_comment caption="Lisa kommentaar" parent=container

*/

define('RELTYPE_TOPIC_FOLDER',1);
define('RELTYPE_ADDRESS_FOLDER',2);

class forum_v2 extends class_base
{
	function forum_v2()
	{
		$this->init(array(
			"tpldir" => "forum",
			"clid" => CL_FORUM_V2
		));
	}

	function callback_get_rel_types()
	{
                return array(
                        RELTYPE_TOPIC_FOLDER => "teemade kataloog",
			RELTYPE_ADDRESS_FOLDER => "listiliikmete kataloog",
		);
	}

	function callback_get_classes_for_relation($args)
	{
		$retval = false;
	
		switch($args["reltype"])
		{
                        case RELTYPE_TOPIC_FOLDER:
			case RELTYPE_ADDRESS_FOLDER:
                                $retval = array(CL_PSEUDO);
				break;
		};
		return $retval;
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "topics_on_page":
			case "comments_on_page":
				$data["options"] = array(5 => 5,10 => 10,15 => 15,20 => 20,25 => 25,30 => 30);
				break;

		};	
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "add_topic":
				$this->create_forum_topic($args);
				break;

			case "add_comment":
				$this->create_forum_comment($args);
				break;
		}
		return $retval;
	}

	function callback_gen_contents($args = array())
	{
		$fld = $args["obj"]["meta"]["topic_folder"];
		if (!is_numeric($fld))
		{
			return false;
		};			

		$args["fld"] = $fld;

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
		
		$prop = $args["prop"];
		$prop["type"] = "text";
		$prop["value"] = $retval;
		return array($prop);
	}	

	function draw_all_folders($args = array())
	{
		extract($args);

		// first level folders
		$flds = $this->get_objects_below(array(
			"parent" => $fld,
			"class" => CL_PSEUDO,
		));

		$this->read_template("forum.tpl");

		$c = "";
			
		foreach($flds as $fdata)
		{
			$this->vars(array(
				"name" => $fdata["name"],
				"comment" => $fdata["comment"],
				"open_l1_url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"c" => $fdata["oid"],"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
			));

			$c .= $this->parse("L1_FOLDER");

			if (empty($args["request"]["c"]) || ($args["request"]["c"] == $fdata["oid"]))
			{
				// for each first level folder, figure out all the second level
				// folders.
				$subflds = $this->get_objects_below(array(
					"parent" => $fdata["oid"],
					"class" => CL_PSEUDO,
				));

				// for each second level folder, figure out the amount of topics
				// and posts 
				list($topic_counts,$topic_list) = $this->get_topic_list(array(
					"parents" => array_keys($subflds),
				));

				// ja iga alamtopicu jaoks on mul vaja teada, mitu
				// teemat seal on.
				foreach($subflds as $soid => $sdata)
				{
					list(,$comment_count) = $this->get_comment_counts(array(
						"parents" => $topic_list[$soid],
					));
					
					$last = $this->get_last_comments(array(
						"parents" => $topic_list[$soid],
					));

					$this->vars(array(
						"name" => $sdata["name"],
						"topic_count" => (int)$topic_counts[$soid],
						"comment_count" => (int)$comment_count,
						"last_createdby" => $last["createdby"],
						"last_date" => $this->time2date($last["created"],2),
						"open_topic_url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"folder" => $sdata["oid"],"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
					));
					$c .= $this->parse("L2_FOLDER");
				};
			};
		}

		$this->vars(array(
			"L1_FOLDER" => $c,
		));
		return $this->parse();
	}

	////
	// !Draws the contents of a single folder
	function draw_folder($args = array())
	{
		extract($args);
		$topics_on_page = !empty($args["obj"]["meta"]["topics_on_page"]) ? $args["obj"]["meta"]["topics_on_page"] : 5;
		$topic_obj = $this->get_object($args["request"]["folder"]);

		$this->read_template("folder.tpl");

		$obj_chain = $this->get_obj_chain(array(
			"oid" => $topic_obj["oid"],
			"stop" => $args["fld"],
		));

		$path = array();
		foreach($obj_chain as $key => $name)
		{
			if ($key == $fld)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
					"caption" => $name,
				));
			}
			else
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"c" => $key,"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
					"caption" => $name,
				));


			}
			$path[] = $name;
		};

		$subtopics = $this->get_objects_below(array(
			"parent" => $topic_obj["oid"],
			"class" => CL_MSGBOARD_TOPIC,
		));

		$c = $pager = "";

		list($comm_counts,) = $this->get_comment_counts(array(
			"parents" => array_keys($subtopics),
		));
		
		$tcount = sizeof($subtopics);
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

		foreach($subtopics as $key => $val)
		{
			$cnt++;
			if (!between($cnt,$from,$to))
			{
				continue;
			};
			$last = $this->get_last_comments(array(
				"parents" => array($key),
			));

			if (!$last)
			{
				$last = array(
					"created" => $val["created"],
					"createdby" => $val["createdby"],
				);
			};

			$this->vars(array(
				"name" => $val["name"],
				"comment_count" => (int)$comm_counts[$key],
				"last_date" => $this->time2date($last["created"],2),
				"last_createdby" => $last["createdby"],
				"author" => $val["createdby"],
				"open_topic_url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"group" => $args["request"]["group"],"topic" => $val["oid"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
			));

			$c .= $this->parse("SUBTOPIC");

		};

		// draw pager
		for ($i = 1; $i <= $num_pages; $i++)
		{
			$this->vars(array(
				"num" => $i,
				"url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"folder" => $topic_obj["oid"],"page" => $i,"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
			));
			$pager .= $this->parse($selpage == $i ? "active_page" : "page");
		};

		$this->vars(array(
			"SUBTOPIC" => $c,
			"name" => $topic_obj["name"],
			"path" => join(" &gt; ",array_reverse($path)),
			"active_page" => $pager,

		));
		return $this->parse();
	}

	function draw_topic($args = array())
	{
		$fld = $args["fld"];
		$this->read_template("topic.tpl");
		$obj_chain = $this->get_obj_chain(array(
			"oid" => $args["request"]["topic"],
			"stop" => $args["obj"]["meta"]["topic_folder"],
		));

		$topic_obj = $this->get_object(array(
			"oid" => $args["request"]["topic"],
			"clid" => CL_MSGBOARD_TOPIC,
		));
		
		$comments_on_page = !empty($args["obj"]["meta"]["topics_on_page"]) ? $args["obj"]["meta"]["topics_on_page"] : 5;
		$path = array();
		foreach($obj_chain as $key => $name)
		{
			if ($key == $fld)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
					"caption" => $name,
				));
			}
			else
			{
				$obj = $this->get_object($key);
				if ($obj["class_id"] == CL_PSEUDO)
				{
					if ($obj["parent"] != $fld)
					{
						$name = html::href(array(
							"url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"group" => $args["request"]["group"],"folder" => $obj["oid"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
							"caption" => $name,
						));
					}
					else
					{
						$name = html::href(array(
							"url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"group" => $args["request"]["group"],"c" => $obj["oid"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
							"caption" => $name,
						));
					};
				};
						
			};
			$path[] = $name;
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
				"url" => $this->mk_my_orb("change",array("id" => $args["obj"]["oid"],"topic" => $topic_obj["oid"],"page" => $i,"group" => $args["request"]["group"],"section" => aw_global_get("section"),"_alias" => get_class($this))),
			));
			$pager .= $this->parse($selpage == $i ? "active_page" : "page");
		};

		$this->vars(array(
			"active_page" => $pager,
			"name" => $topic_obj["name"],
			"comment" => $topic_obj["comment"],
			"COMMENT" => $c,
			"path" => join(" &gt; ",array_reverse($path)),
		));

		return $this->parse();

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

	function create_forum_topic($args)
	{
		$t = get_instance("contentmgmt/forum/forum_topic");
                $emb = $args["form_data"]["emb"];
                $t->id_only = true;
                if (isset($emb["group"]))
                {
                        $this->emb_group = $emb["group"];
                };
		$emb["forum_id"] = $args["form_data"]["id"];
                $this->topic_id = $t->submit($emb);
		return PROP_OK;
	}
	
	function create_forum_comment($args)
	{
		$t = get_instance("contentmgmt/forum/forum_comment");
                $emb = $args["form_data"]["emb"];
                $t->id_only = true;
                if (isset($emb["group"]))
                {
                        $this->emb_group = $emb["group"];
                };
		// now .. this is where I will have to let the comment class now whether to send
		// out messages or not
                $this->comm_id = $t->submit($emb);
		$this->topic_id = $emb["parent"];
		$topic_obj = $this->get_object($this->topic_id);
		$topic_inst = get_instance("contentmgmt/forum/forum_topic");
		$topic_inst->mail_subscribers(array(
			"id" => $this->topic_id,
			"subject" => "Uus postitus teemas $topic_obj[name]: " . $emb["name"],
			"message" => $emb["uname"] . " @ " . $this->time2date(time(),2) . "\n\n" . $emb["commtext"],
		));
		return PROP_OK;
	}
	
	function callback_mod_retval($args = array())
	{
		if ($this->topic_id)
		{
			$form_data = &$args["form_data"];
                	$emb = $args["form_data"]["emb"];
			$args = &$args["args"];
			$args["folder"] = $emb["parent"];
			$args["topic"] = $this->topic_id;
			$args["group"] = "contents";
		}
	}

	function get_topic_list($args = array())
	{	$topic_count = $topic_list = array();
		if (sizeof($args["parents"]) != 0)
		{
			$q = sprintf("SELECT oid,parent FROM objects WHERE parent IN (%s) AND class_id = '%d' AND status != 0",
					join(",",$args["parents"]),CL_MSGBOARD_TOPIC);
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$topic_count[$row["parent"]]++;
				$topic_list[$row["parent"]][] = $row["oid"];
			};
		};
		return array($topic_count,$topic_list);
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

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

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

		return $this->change(array(
			"id" => $alias["target"],
			"action" => "view",
			"folder" => $_GET["folder"],
			"topic" => $_GET["topic"],
			"c" => $_GET["c"],
			"group" => isset($_GET["group"]) ? $_GET["group"] : "container",
		));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}
};
?>
