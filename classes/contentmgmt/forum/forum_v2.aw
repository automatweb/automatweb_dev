<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_v2.aw,v 1.49 2004/11/24 15:37:36 ahti Exp $
// forum_v2.aw.aw - Foorum 2.0 
/*

	@classinfo syslog_type=ST_FORUM
	@classinfo relationmgr=yes

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property show_logged type=checkbox ch_value=1
	@caption Kui kasutaja on sisse loginud, siis täidab e-posti ja nime välja automaatselt
	
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

	@property topic_depth type=select default=0 
	@caption Teemade sügavus

	@property topic_selector type=text group=topic_selector no_caption=1
	@caption Teemade tasemed

	@property topic type=hidden store=no group=contents
	@caption Topic ID (sys)

	@property show type=callback callback=callback_gen_contents store=no no_caption=1 group=contents
	@caption Foorumi sisu

	@property style_donor type=relpicker group=styles reltype=RELTYPE_STYLE_DONOR
	@caption Stiilidoonor
	
	@property style_new_topic_row type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema lisamise stiil
	
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
	
	@property style_forum_yah type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Foorumi YAH
	
	@property style_topic_caption type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema pealkirja stiil
	
	@property style_topic_replies type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema vastuste arvu stiil
	
	@property style_topic_author type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema autori stiil
	
	@property style_topic_last_post type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema viimase postituste stiil

	@property style_comment_count type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Kommentaaride arvu stiil
	
	@property style_comment_creator type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema püstitaja stiil

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

	@property import_xml_file type=fileupload store=no group=import
	@caption Vali XML fail

	--------------MEILISEADED-------------
	@default group=mail_settings
	
	@property answers_to_mail type=checkbox field=meta method=serialize ch_value=1
	@caption Soovi korral vastused meiliga
	
	@property mail_from type=textbox field=meta method=serialize
	@caption Kellelt
	comment Default on kommenteerija nimi
	
	@property mail_address type=textbox field=meta method=serialize
	@caption E-maili aadress kellelt
	comment Default - Kommenteerija e-mail
	
	@property mail_subject type=textbox field=meta method=serialize
	@caption Maili subject
	@comment Kui määramata, siis foorumi topic
	-----------------------------------------
	
	@property mail_from type=textbox field=meta method=serialize
	@caption E-mail kellelt
	
	@groupinfo contents caption=Sisu submit=no
	@groupinfo styles caption=Stiilid
	@groupinfo settings caption=Seadistused
	@groupinfo topic_selector caption=Teemad parent=settings
	@groupinfo import caption=Import parent=settings
	@groupinfo mail_settings caption="Meiliseaded" parent=settings

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

		lc_site_load("forum",&$this);
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
				$data["options"] = array("0" => "0","1" => "1","2" => "2","3" => "3","4" => "4","5" => "5");
				break;

			case "topic_selector":
				$topic_folder = $arr["obj_inst"]->prop("topic_folder");
				if (!is_oid($topic_folder))
				{
					$retval = PROP_ERROR;
					$data["error"] = "Teemade kataloog on valimata";
				}
				else
				{
					$data["value"] = $this->get_topic_selector($arr);
				};
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


		};	
		return $retval;
	} 

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "topic_selector":
				$this->update_topic_selector($arr);
				break;

			case "container":
				$this->update_contents($arr);
				break;
			
			case "import_xml_file":
				$tmpname = $_FILES["import_xml_file"]["tmp_name"];
				if (is_uploaded_file($tmpname))
				{
					$contents = aw_unserialize(file_get_contents($tmpname));
					$forumdata = $contents["forum"];
					$topicdata = $contents["topics"];
					$commentdata = $contents["comments"];
					if (is_array($forumdata) && is_array($topicdata) && is_array($commentdata))
					{
						$this->obj_inst = $arr["obj_inst"];
						$this->create_forum_from_comments($forumdata,$topicdata,$commentdata);
					};
				};
				break;
		}
		return $retval;
	}

	function create_forum_from_comments($forumdata,$topicdata,$commentdata)
	{
		$ol = new object_list(array(
			"class_id" => CL_FORUM_V2,
			"name" => $forumdata["name"],
		));
		$id = $this->obj_inst->id();
		$o = $this->obj_inst;
		
		print "creating forum object with name " . $forumdata["name"] . "<br>";
		print "creating folder for topics " . $forumdata["name"] . " teemad" . "<br>";
		print "creating topics<br>";

		$o->set_name($forumdata["name"]);
		$o->set_comment($forumdata["comment"]);
		$o->set_prop("topics_on_page",$forumdata["topics_on_page"]);
		$o->set_prop("comments_on_page",$forumdata["comments_on_page"]);

		// does this forum have a topic folder?
		$folder_conns = $o->connections_from(array(
			"type" => "RELTYPE_TOPIC_FOLDER",
		));

		if (sizeof($folder_conns) == 0)
		{
			// create the folder then!
			print "creating folder for topics<br>";
			$mn = new object();
			$mn->set_class_id(CL_MENU);
			$mn->set_parent($o->parent());
			$mn->set_status(STAT_ACTIVE);
			$mn->set_name($forum_data["name"] . " teemad");
			$mn->save();
			$topic_folder = $mn->id();

			$o->connect(array(
				"to" => $topic_folder,
				"reltype" => RELTYPE_TOPIC_FOLDER,
			));

			$o->set_prop("topic_folder",$topic_folder);
			print "connecting<br>";
		}
		else
		{
			$topic_folder = $o->prop("topic_folder");
		};
			
		$o->save();

		// first, create a list of all topics in this folder
		$topic_list = new object_list(array(
			"parent" => $topic_folder,
			"class_id" => CL_MSGBOARD_TOPIC,
		));

		$existing_topics = array();

		for ($to = $topic_list->begin(); !$topic_list->end(); $to = $topic_list->next())
		{
			// each imported topic has its unique id in metadata
			$import_id = $to->meta("import_id");
			if (!empty($import_id))
			{
				$existing_topics[$import_id] = 1;
			};
		};

		// there is a shitload of topics with no name, I need to take those into account
		foreach($topicdata as $topic_id => $topic_data)
		{
			if ($existing_topics[$topic_id])
			{
				print "topic exists, not creating object<br>";
				$comment_parent = $topic_id;
			}
			else
			{
				print "creating topic $topic_id / " . $topic_data["subject"] . "<br>";
				//arr($topic_data);
				$topic_obj = new object();
				$topic_obj->set_class_id(CL_MSGBOARD_TOPIC);
				$topic_obj->set_parent($topic_folder);
				$topic_obj->set_name($topic_data["subject"]);
				$topic_obj->set_comment($topic_data["comment"]);
				// XXX: HACK: can't modify created, but need it. this is the workaround
				$topic_obj->set_subclass($topic_data["time"]);
				// XXX: HACK: can't modify created, but need it. this is the workaround
				$topic_obj->set_prop("author_name",$topic_data["author"]);
				$topic_obj->set_prop("author_email",$topic_data["email"]);
				$topic_obj->set_status(STAT_ACTIVE);
				$topic_obj->set_meta("import_id",$topic_id);
				$topic_obj->save();
				$comment_parent = $topic_obj->id();
			};

			if (is_array($commentdata[$topic_id]))
			{
				$existing_comments = array();
				// first, create a list of all topics in this folder
				$comm_list = new object_list(array(
					"parent" => $comment_parent,
					"class_id" => CL_COMMENT,
				));

				for ($co = $comm_list->begin(); !$comm_list->end(); $co = $comm_list->next())
				{
					// each imported topic has its unique id in metadata
					$import_id = $co->meta("import_id");
					if (!empty($import_id))
					{
						$existing_comments[$import_id] = 1;
					};
				};

				foreach($commentdata[$topic_id] as $comm_id => $comments)
				{
					if ($existing_comments[$comm_id])
					{
						print "not creating existing comment<br>";
					}
					else
					{
						print "creating comment ";
						arr($comments);
						print "<br>";
						$comm = new object();
						print "cp is $comment_parent<br>";
						$comm->set_parent($comment_parent);
						$comm->set_class_id(CL_COMMENT);
						$comm->set_name($comments["subject"]);
						$comm->set_prop("ip",$comments["ip"]);
						$comm->set_status(STAT_ACTIVE);
						$comm->set_prop("uname",$comments["name"]);
						$comm->set_prop("uemail",$comments["email"]);
						$comm->set_prop("commtext",$comments["comment"]);
						$comm->set_meta("import_id",$comm_id);
						// XXX: HACK: can't modify created, but need it. this is the workaround
						$comm->set_subclass($comments["time"]);
						// XXX: HACK: can't modify created, but need it. this is the workaround
						$comm->save();
					};
				};
			};

			print "topic loading finished<br>";
		}
		print "forum import finished<br>";
	}

	function callback_pre_edit($arr)
	{
		$this->rel_id = $arr["request"]["rel_id"];
		$this->rel_id = aw_global_get("section");
	}

	function callback_gen_contents($arr)
	{
		classload("layout/active_page_data");
		$this->style_data = array();
		$this->obj_inst = $arr["obj_inst"];
		$style_donor = $this->obj_inst->prop("style_donor");
		if (!empty($style_donor))
		{
			$this->style_donor_obj = new object($style_donor);
		};
		$this->_add_style("style_caption");

		if (is_oid($arr["request"]["topic"]))
		{
			$retval = $this->draw_topic($arr);
		}
		elseif (is_oid($arr["request"]["folder"]))
		{
			$retval = $this->draw_folder($arr);
		}
		else
		{
			// default view, used when the user first views the forum
			// shows all folders
			$retval = $this->draw_all_folders($arr);
		};

		$prop = $args["prop"];
		$prop["value"] = $retval;
		return array($prop);
	}	

	function draw_all_folders($args = array())
	{
		extract($args);

		$this->read_template("forum.tpl");

		$c = "";
		
		$this->_add_style("style_new_topic_row");
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
		if (empty($depth) && $depth != 0)
		{
			$depth = 1;
		};

		$this->depth = $depth;

		// forum allows turning off of certain folders, this deals with it.
		$this->exclude = $args["obj_inst"]->meta("exclude");
		$this->exclude_subs = $args["obj_inst"]->meta("exclude_subs");

		$this->level = 1;
		$this->group = $args["request"]["group"];

		$conns = $args["obj_inst"]->connections_from(array(
			"type" => RELTYPE_TOPIC_FOLDER,
		));

		$c = "";
		$first = 0;
		foreach($conns as $conn)
		{
			// ideaalis võiks saada teemasid teha ka otse foorumi sisse.
			// see tähendab siis seda, et kui kataloogi pole määratud, siis
			// tulevad teemad by default kohe foorum sisse. Ja nii ongi...
			if ($this->depth == 0)
			{
				$args["request"]["folder"] = $conn->prop("to");
				$c .= $this->draw_folder($args);
			}
			else
			{
				$c .= $this->_draw_one_level(array(
					"parent" => $conn->prop("to"),
					"id" => $args["obj_inst"]->id(),
				));
			};
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
			// this shit doesn't even draw the first level, even if explicitly specify that one should exist
			// why???
			$folder_list = new object_list(array(
				"parent" => $arr["parent"],
				"class_id" => CL_MENU,
				"status" => STAT_ACTIVE,
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
			}
		};
		return $c;
	}

	// needs at least one argument .. the parent
	function _draw_last_level($arr)
	{
		$sub_folder_list = new object_list(array(
			"parent" => $arr["parent"],
			"class_id" => CL_MENU,
			"status" => STAT_ACTIVE,
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

	function _get_fp_link($arr)
	{
		return html::href(array(
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["id"],
				"group" => $arr["group"],
				"section" => aw_global_get("section"),
				"_alias" => get_class($this),
			)),
			"caption" => $arr["name"],
		));
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

		$obj_chain = $topic_obj->path();
		$obj_chain = array_reverse($obj_chain);

		$path = array();
		$path[] = $this->_get_fp_link(array(
			"id" => $args["obj_inst"]->id(),
			"group" => $args["request"]["group"],
			"name" => $args["obj_inst"]->name(),
		));

		$stop = false;
		foreach($obj_chain as $o)
		{
			if ($stop)
			{
				continue;
			};
			if ($o->id() == $topic_obj->id())
			{
				// this creates the link back to the front page 
				// of the topic and stops processing
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $args["obj_inst"]->id(),
						"group" => $args["request"]["group"],
						"section" => $this->rel_id,
						"folder" => $o->id(),
						"_alias" => get_class($this),
					)),
					"caption" => $o->name(),
				));
				$stop = true;
			}
			else
			{
				// this is used for all other levels
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $args["obj_inst"]->id(),
						"c" => $key,
						"group" => $args["request"]["group"],
						"section" => $this->rel_id,
						"_alias" => get_class($this),
					)),
					"caption" => $o->name(),
				));


			}
			$path[] = $name;
		};

		
		$this->_add_style("style_topic_caption");
		$this->_add_style("style_topic_replies");
		$this->_add_style("style_topic_author");
		$this->_add_style("style_topic_last_post");
		$this->_add_style("style_forum_yah");
		$this->vars($this->style_data);

		$subtopic_list = new object_list(array(
			"parent" => $topic_obj->id(),
			"class_id" => CL_MSGBOARD_TOPIC,
			"status" => STAT_ACTIVE,
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
		
		$age_check = false;
		$c_date = 0;
		$user_id = aw_global_get("uid_oid");
		if(!empty($user_id))
		{
			$user_obj = obj($user_id);
			$u_date = $user_obj->meta("topic_age");
			if(is_array($u_date))
			{
				if(!empty($u_date[$args["obj_inst"]->id()]))
				{
					$c_date = strtotime("-".$u_date[$args["obj_inst"]->id()]." days");
					$age_check = true;
				}
			}
		}
		
		foreach($subtopic_list->arr() as $subtopic_obj)
		{
			$cnt++;
			if(!between($cnt, $from, $to))
			{
				continue;
			};

			// retrieve the date of the latest comment
			$last = $this->get_last_comments(array(
				"parents" => array($subtopic_obj->id()),
			));
			
			if($age_check === true && $last["created"] < $c_date)
			{
				$cnt--;
				continue;
			}
			
			$creator = $subtopic_obj->createdby();

			if ($last)
			{
				$last["created"] = $this->time2date($last["created"],2);
			};
			
			
			$this->vars(array(
				"name" => $subtopic_obj->name(),
				"comment_count" => (int)$comm_counts[$subtopic_obj->id()],
				"last_date" => $last["created"],
				"last_createdby" => $last["uname"],
				"author" => $subtopic_obj->prop("author_name"),
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

		$page_count = 0;

		// draw pager
		for ($i = 1; $i <= $num_pages; $i++)
		{
			$page_count++;
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

		if ($this->is_template("PAGER") && $page_count > 1)
		{
			$this->vars(array(
				"active_page" => $pager,
			));
			$pager = $this->parse("PAGER");
			$this->vars(array(
				"PAGER" => $pager,
			));
			$pager = "";
		};	
	

		$this->vars(array(
			"SUBTOPIC" => $c,
			"name" => $topic_obj->name(),
			"path" => join(" &gt; ",$path),
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
		$this->_add_style("style_comment_creator");
		$this->_add_style("style_forum_yah");
		$this->_add_style("style_comment_count");
		$this->_add_style("style_comment_time");
		$this->_add_style("style_comment_text");
		$this->vars($this->style_data);

		$comments_on_page = $args["obj_inst"]->prop("comments_on_page");
		if (empty($comments_on_page))
		{
			$comments_on_page = 5;
		};
		
		$t = get_instance(CL_COMMENT);
		$comments = $t->get_comment_list(array("parent" => $topic_obj->id()));

		$c = $pager = "";
		
		$tcount = sizeof($comments);
		$num_pages = (int)(($tcount / $comments_on_page));
		if ($tcount % $comments_on_page)
		{
			$num_pages++;
		};
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

		// XXX: is there a better way to do this?
		$can_delete = $this->can_admin_forum();


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
					"id" => $comment["oid"],
					"name" => $comment["name"],
					"commtext" => nl2br($comment["commtext"]),
					"date" => $this->time2date($comment["created"],2),
					"createdby" => $comment["createdby"],
					"uname" => $comment["uname"],
					"ip" => $comment["ip"],
				));
				if ($can_delete)
				{
					$this->vars(array(
						"ADMIN_BLOCK" => $this->parse("ADMIN_BLOCK"),
					));
				};

				$c .= $this->parse("COMMENT");
			};
		};		
		
		// draw pager
		for ($i = 1; $i <= $num_pages; $i++)
		{
			$this->vars(array(
				"num" => $i,
				"url" => $this->mk_my_orb("change",array(
					"id" => $args["obj_inst"]->id(),
					"topic" => $topic_obj->id(),
					"page" => $i,
					"group" => $args["request"]["group"],
					"section" => aw_global_get("section"),
					"_alias" => get_class($this),
				)),
			));
			$pager .= $this->parse($selpage == $i ? "active_page" : "page");
		};
	
		// path drawing starts
		$path = array();
		$fld = $topic_obj->parent(); 
		$obj_chain = array_reverse($topic_obj->path());

		$show = true;
		foreach($obj_chain as $_to)
		{

			if ($_to->id() == aw_global_get("section"))
			{
				$show = false;
			}

			if ($_to->id() == $args["obj_inst"]->prop("topic_folder"))
			{
				$show = false;
			};
			
			if (!$show)
			{
				continue;
			}

			$obj = $_to;
			if ($obj->class_id() == CL_MENU)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $args["obj_inst"]->id(),
						"group" => $args["request"]["group"],
						"folder" => $obj->id(),
						"section" => aw_global_get("section"),
						"_alias" => get_class($this),
					)),
					"caption" => $obj->name(),
				));
			}
			elseif ($obj->class_id() == CL_MSGBOARD_TOPIC)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $args["obj_inst"]->id(),
						"group" => $args["request"]["group"],
						"topic" => $_to->id(),
						"section" => aw_global_get("section"),
						"_alias" => get_class($this),
					)),
					"caption" => $obj->name(),
				));
			};

						
			//};
			array_unshift($path,$name);
			//$path[] = $name;
		};
		
		$fp = $this->_get_fp_link(array(
			"id" => $args["obj_inst"]->id(),
			"group" => $args["request"]["group"],
			"name" => $args["obj_inst"]->name(),
		));

		array_unshift($path,$fp);

		// path drawing ends .. sucks

		$this->vars(array(
			"active_page" => $pager,
			"name" => $topic_obj->name(),
			"createdby" => $topic_obj->prop("author_name"),
			"date" => $this->time2date($topic_obj->created(),2),
			"comment" => $topic_obj->comment(),
			"COMMENT" => $c,
			"path" => join(" &gt; ",$path),
		));

		if ($num_pages > 1)
		{
			$this->vars(array(
				"PAGER" => $this->parse("PAGER"),
			));
		};

		if ($can_delete)
		{
			$this->vars(array(
				"DELETE_ACTION" => $this->parse("DELETE_ACTION"),
			));
		};

		$rv = $this->parse();

		$this->read_template("add_comment.tpl");
		$this->reforb_action = "submit_comment";
		$this->_add_style("style_form_caption");
		$this->_add_style("style_form_text");
		$this->_add_style("style_form_element");
		$this->vars($this->style_data);
		$uid = aw_global_get("uid");
		$add = "";
		if($this->obj_inst->prop("show_logged") == 1 && !empty($uid))
		{
			$this->vars(array(
				"author" => $uid,
			));
			$add = "_logged";
		}
		$this->vars(array(
			"a_name" => $this->parse("a_name".$add),
		));
		return $rv . $this->parse();

	}

	function callback_gen_add_topic($args = array())
	{
		$t = get_instance(CL_MSGBOARD_TOPIC);
		$t->init_class_base();
		$emb_group = "general";
		if ($this->event_id && $args["request"]["cb_group"])
		{
			$emb_group = $args["request"]["cb_group"];
		};
		$all_props = $t->get_property_group(array(
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
		$t = get_instance(CL_COMMENT);
		$t->init_class_base();
		$emb_group = "general";
		if ($this->event_id && $args["request"]["cb_group"])
		{
			$emb_group = $args["request"]["cb_group"];
		};

		$all_props = $t->get_property_group(array(
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

	function callback_mod_retval($args = array())
	{
		if ($this->topic_id)
		{
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
				"status" => STAT_ACTIVE,
			));	
			foreach ($topic_list->arr() as $topic)
			{
				$parent = $topic->parent();
				$topic_count[$parent]++;
				$tlist[$parent][] = $topic->id();
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
			// hm, but this does not work at all with multiple parents
			$q = sprintf("SELECT parent,created,createdby,forum_comments.uname FROM objects LEFT JOIN forum_comments ON (objects.oid = forum_comments.id) WHERE parent IN (%s) AND class_id = '%d'
				AND status != 0 ORDER BY created DESC",join(",",$args["parents"]),CL_COMMENT);
			$this->db_query($q);
			$retval = $this->db_next();
		};
		return $retval;
	}

	function _add_style($name)
	{
		classload("layout/active_page_data");
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
			$id = $item->id();
			$this->vars(array(
				"caption" => $item->name(),
				"id" => $id,
				"spacer" => str_repeat("&nbsp;",$level*3),
				"exclude" => checked($this->exclude[$id]),
				"exclude_subs" => checked($this->exclude_subs[$id]),
			));
			$this->rv .= $this->parse("ITEM");
			$level++;
			$this->_do_rec_topic(array("parent" => $id));
			$level--;
		};
	}

	function callback_mod_reforb($arr,$request)
	{
		if (!empty($this->reforb_action))
		{
			$arr["action"] = $this->reforb_action;
		};
		if (is_numeric($request["page"]))
		{
			$arr["page"] = $request["page"];
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
		if (method_exists($this, $act))
		{
			return $this->$act(array(
				"id" => $alias["target"],
				"action" => isset($_GET["action"]) ? $_GET["action"] : "view",
				"rel_id" => $args["alias"]["relobj_id"],
				"folder" => $_GET["folder"],
				"topic" => $_GET["topic"],
				"page" => $_GET["page"],
				"c" => $_GET["c"],
				"cb_part" => 1,
				"fxt" => 1,
				"group" => "contents",
				//"group" => isset($_GET["group"]) ? $_GET["group"] : "contents",
			));
		}
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
		
		@attrib name=add_topic params=name all_args="1" nologin="1"
		
		
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
		$uid = aw_global_get("uid");
		$add = "";
		if($this->obj_inst->prop("show_logged") == 1 && !empty($uid))
		{
			$user = obj(aw_global_get("uid_oid"));
			$this->vars(array(
				"author" => $uid,
				"email" => $user->prop("email"),
			));
			$add = "_logged";
		}
		$this->vars(array(
			"a_name" => $this->parse("a_name".$add),
			"a_email" => $this->parse("a_email".$add),
		));
		
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_topic params=name all_args="1" nologin="1"
		
		@returns
		
		
		@comment

	**/
	function submit_topic($arr)
	{
		if($this->can("view", $arr["id"]) && is_oid($arr["id"]))
		{
			$obj_inst = obj($arr["id"]);
			$uid = aw_global_get("uid");
			if($obj_inst->prop("show_logged") == 1 && !empty($uid))
			{
				$user = obj(aw_global_get("uid_oid"));
				$arr["author_name"] = $uid;
				$arr["author_email"] = $user->prop("email");
			} 
		}
		$t = get_instance("contentmgmt/forum/forum_topic");
                $emb = $arr;
		$emb["parent"] = $arr["folder"];
                $t->id_only = true;
		$emb["forum_id"] = $arr["id"];
		$arr["group"] = "contents";
		$emb["status"] = STAT_ACTIVE;
		unset($emb["id"]);
                $this->topic_id = $t->submit($emb);
		$arr["topic"] = $this->topic_id;
		return $this->finish_action($arr);
	}
	
	/** Creates a new comment object for a topic 
		
		@attrib name=submit_comment params=name all_args="1" nologin="1"

	**/
	function submit_comment($arr)
	{
		if($this->can("view", $arr["id"]) && is_oid($arr["id"]))
		{
			$obj_inst = obj($arr["id"]);
			$uid = aw_global_get("uid");
			if($obj_inst->prop("show_logged") == 1 && !empty($uid))
			{
				$arr["uname"] = $uid;
			}
		}
		//arr($arr);
		$t = get_instance(CL_COMMENT);
		$topic = get_instance(CL_MSGBOARD_TOPIC);
		
		$emb = $arr;
		$t->id_only = true;
		unset($emb["id"]);
		$emb["parent"] = $arr["topic"];
		$emb["status"] = STAT_ACTIVE;
        $this->comm_id = $t->submit($emb);
		
        $topic->mail_subscribers(array(
        	"id" => $arr["topic"],
        	"message" => $arr["commtext"],
        	"forum_id" => $arr["id"],
        ));
		return $this->finish_action($arr);
	}

	/**
		@attrib name=delete_comments

	**/
	function delete_comments($arr)
	{
		if ($this->can_admin_forum() && sizeof($arr["del"]) > 0)
		{
			$to_delete = new object_list(array(
				"oid" => $arr["del"],
				"parent" => $arr["topic"],
				"class_id" => CL_COMMENT,
			));

			$to_delete->delete();
		};
		return $this->finish_action($arr);
	}

	function can_admin_forum()
	{
		// XXX: implement a better check perhaps?
		return $this->prog_acl("view",PRG_MENUEDIT);
	}

	/**  
		
		@attrib name=change params=name all_args="1" nologin="1"
		
		@param id optional type=int acl="edit"
		@param group optional
		@param period optional
		@param alias_to optional
		@param return_url optional
		
		@returns
		
		
		@comment

	**/
	function change($arr)
	{
		return parent::change($arr);
	}
};
?>
