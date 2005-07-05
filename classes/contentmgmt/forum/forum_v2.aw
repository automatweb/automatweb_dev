<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_v2.aw,v 1.77 2005/07/05 09:06:57 duke Exp $
// forum_v2.aw.aw - Foorum 2.0 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_FORUM_V2, on_connect_menu)
*/

/*

	@classinfo syslog_type=ST_FORUM
	@classinfo relationmgr=yes

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property show_logged type=checkbox ch_value=1
	@caption Kuva sisseloginud kasutaja e-posti ja nime atuomaatselt
	
	@property topic_folder type=relpicker reltype=RELTYPE_TOPIC_FOLDER
	@caption Teemade kaust
	@comment Sellest kaustast võetakse foorumi teemad
	
	@property address_folder type=relpicker reltype=RELTYPE_ADDRESS_FOLDER
	@caption Listiliikmete kaust
	@comment Sellesse kausta paigutatakse "listi liikmete" objektid

	@property faq_folder type=relpicker reltype=RELTYPE_FAQ_FOLDER
	@caption KKK kaust
	@comment Sellesse kausta paigutatakse KKK dokumendid

	@property topics_on_page type=select
	@caption Teemasid lehel

	@property comments_on_page type=select
	@caption Kommentaare lehel

	@property topic_depth type=select group=topic_selector default=0 
	@caption Teemade sügavus

	@property topic_selector type=table group=topic_selector no_caption=1
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
	@caption Teema kausta stiil

	@property style_folder_caption type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema kausta pealkirja stiil
	
	@property style_folder_topic_count type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema kausta arvu stiil
	
	@property style_folder_comment_count type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema kausta vastuste arvu stiil
	
	@property style_folder_last_post type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema kausta viimase vastuse stiil
	
	@property style_forum_yah type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Foorumi asukohariba stiil
	
	@property style_topic_caption type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema pealkirja stiil
	
	@property style_topic_replies type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema vastuste arvu stiil
	
	@property style_topic_author type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema autori stiil
	
	@property style_topic_last_post type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema viimase vastuse stiil

	@property style_comment_count type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Vastuste arvu stiil
	
	@property style_comment_creator type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Teema autori stiil

	@property style_comment_user type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Vastuse autori stiil

	@property style_comment_time type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Vastuse aja stiil

	@property style_comment_text type=relpicker group=styles reltype=RELTYPE_STYLE
	@caption Vastuse teksti stiil

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
	@caption Teemade kaust

	@reltype ADDRESS_FOLDER value=2 clid=CL_MENU
	@caption Listiliikmete kaust

	@reltype STYLE value=3 clid=CL_CSS
	@caption Stiil

	@reltype STYLE_DONOR value=4 clid=CL_FORUM_V2
	@caption Stiilidoonor

	@reltype FORUM_ADMIN value=5 clid=CL_USER,CL_GROUP
	@caption Administraator

	@reltype FAQ_FOLDER value=6 clid=CL_MENU
	@caption KKK kaust

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
				$depth = $arr["obj_inst"]->prop("topic_depth");
				// hide topic_selector if it doesn't make any sense
				if (0 == $depth)
				{
					$retval = PROP_IGNORE;
				}
				else if (!is_oid($topic_folder))
				{
					$retval = PROP_ERROR;
					$data["error"] = t("Teemade kaust on valimata");
				}
				else
				{
					$this->get_topic_selector(&$arr);
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

			case "show":
				$this->process_contents($arr);
				break;
		}
		return $retval;
	}

	function process_contents($arr)
	{
		if (isset($arr["request"]["delete_selected_topics"]))
		{
			$topics_to_delete = new aw_array($arr["request"]["sel_topic"]);
			foreach($topics_to_delete->get() as $topic_id => $foo)
			{
				if ($this->can("delete",$topic_id))
				{
					$topic_obj = new object($topic_id);
					$topic_obj->delete();
				};
			};
		};
		if (isset($arr["request"]["locktoggle_selected_topics"]))
		{
			$topic_list = new aw_array($arr["request"]["sel_topic"]);
			foreach($topic_list->get() as $topic_id => $foo)
			{
				if ($this->can("edit",$topic_id))
				{
					$topic_obj = new object($topic_id);
					$topic_obj->set_prop("locked",!$topic_obj->prop("locked"));
					$topic_obj->save();
				};
			};
		};
		return PROP_OK;
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
				"reltype" => "RELTYPE_TOPIC_FOLDER",
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

		foreach ($topic_list->arr() as $to)
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

				foreach ($comm_list->arr() as $co)
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
		if (is_oid($style_donor))
		{
			$this->style_donor_obj = new object($style_donor);
		};
		$this->_add_style("style_caption");

		$rv = array();

		if (is_oid($arr["request"]["topic"]))
		{
			$rv = $this->draw_topic($arr);
		}
		elseif (is_oid($arr["request"]["folder"]))
		{
			$rv["contents"] = array(
				"type" => "text",
				"name" => "contents",
				"value" => $this->draw_folder($arr),
				"no_caption" => 1,
			);
		}
		else
		{
			// default view, used when the user first views the forum
			// shows all folders
			$rv["contents"] = array(
				"type" => "text",
				"name" => "contents",
				"value" => $this->draw_all_folders($arr),
				"no_caption" => 1,
			);
		};

		//$prop = $arr["prop"];
		//$prop["value"] = $retval;
		//return array($prop);
		return $rv;
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
			"type" => "RELTYPE_TOPIC_FOLDER",
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
			foreach ($folder_list->arr() as $folder_obj)
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
		foreach ($sub_folder_list->arr() as $sub_folder_obj)
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
		foreach ($folder_list->arr() as $folder_obj)
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

		$oid = $args["obj_inst"]->id();

		$topic_obj = new object($args["request"]["folder"]);

		$this->read_template("folder.tpl");

		$obj_chain = $topic_obj->path();
		$obj_chain = array_reverse($obj_chain);

		$path = array();
		$path[] = $this->_get_fp_link(array(
			"id" => $oid,
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
						"id" => $oid,
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
						"id" => $oid,
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
			"sort_by" => "objects.created desc"
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
				if(!empty($u_date[$oid]))
				{
					$c_date = strtotime("-".$u_date[$oid]." days");
					$age_check = true;
				}
			}
		}

		// each topic can have its own ACL (I highly doubt that this is ever going
		// to happen though) and DELETE_ACTION subtemplate is parsed only if any of
		// the topics can actually be deleted
		$delete_action = false;
		$section = aw_global_get("section");
		$can_admin = $this->_can_admin(array("forum_id" => $args["obj_inst"]->id()));

		foreach($subtopic_list->arr() as $subtopic_obj)
		{
			$cnt++;
			if(!between($cnt, $from, $to))
			{
				continue;
			};

			$st_oid = $subtopic_obj->id();

			// retrieve the date of the latest comment
			$last = $this->get_last_comments(array(
				"parents" => array($st_oid),
			));
			
			if($age_check === true && $last["created"] < $c_date)
			{
				$cnt--;
				continue;
			}
			
			if ($last)
			{
				$last["created"] = $this->time2date($last["created"],2);
			};

			$topic_name = $subtopic_obj->name();
			if (1 == $subtopic_obj->prop("locked"))
			{
				$topic_name = "[L] " . $topic_name;
			};

			$this->vars(array(
				"name" => $topic_name,
				"comment_count" => (int)$comm_counts[$st_oid],
				"topic_id" => $st_oid,
				"last_date" => $last["created"],
				"last_createdby" => $last["uname"],
				"author" => $subtopic_obj->prop("author_name"),
				"open_topic_url" => $this->mk_my_orb("change",array(
						"id" => $oid,
						"group" => $args["request"]["group"],
						"topic" => $st_oid,
						"section" => $section,
						"_alias" => get_class($this),
				)),
			));

			$del = "";
			if ($can_admin && $this->can("delete",$st_oid))
			{
				$delete_action = true;

				// add_faq_url - it is the matter of template to actually show the link or not
				$this->vars(array(
					"add_faq_url" => $this->mk_my_orb("add_faq", array(
						"topic" => $st_oid,
						"id" => $oid,
						"section" => $section,
					)),
				));
				$del = $this->parse("ADMIN_BLOCK");
			};

			$this->vars(array(
				"ADMIN_BLOCK" => $del,
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
					"id" => $oid,
						"folder" => $topic_obj->id(),
						"page" => $i,
						"group" => $args["request"]["group"],
						"section" => $section,
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
				"id" => $oid,
				"section" => aw_global_get("section"),
				"folder" => $args["request"]["folder"],
				"_alias" => get_class($this),
			)),
		));
		if ($can_admin)
		{
			if ($delete_action)
			{
				$this->vars(array(
					"DELETE_ACTION" => $this->parse("DELETE_ACTION"),
				));
			};

			$this->vars(array(
				"LOCK_ACTION" => $this->parse("LOCK_ACTION"),
			));
		};
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
		
		$oid = $args["obj_inst"]->id();

		$can_delete = $this->_can_admin(array("forum_id" => $oid));
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
					"commtext" => $this->_filter_output($comment["commtext"]),
					"date" => $this->time2date($comment["created"],2),
					"createdby" => $comment["createdby"],
					"uname" => $comment["uname"],
					"ip" => $comment["ip"],
					"ADMIN_POST" => "",
				));
				// have to check if the comment creator is admin or not
				if ($this->_can_admin(array(
					"forum_id" => $oid,
					"uid" => $comment['createdby'],
				)))
				{
					$this->vars(array(
						"ADMIN_POST" => $this->parse("ADMIN_POST"),
					));
				}

				if ($can_delete)
				{
					$this->vars(array(
						"ADMIN_BLOCK" => $this->parse("ADMIN_BLOCK"),
					));
				};

				$c .= $this->parse("COMMENT");
			};
		};		

		$section = aw_global_get("section");
		
		// draw pager
		for ($i = 1; $i <= $num_pages; $i++)
		{
			$this->vars(array(
				"num" => $i,
				"url" => $this->mk_my_orb("change",array(
					"id" => $oid,
					"topic" => $topic_obj->id(),
					"page" => $i,
					"group" => $args["request"]["group"],
					"section" => $section,
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

			if ($_to->id() == $section)
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
			$clid = $obj->class_id();
			if ($clid == CL_MENU)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $oid,
						"group" => $args["request"]["group"],
						"folder" => $obj->id(),
						"section" => $section,
						"_alias" => get_class($this),
					)),
					"caption" => $obj->name(),
				));
			}
			elseif ($clid == CL_MSGBOARD_TOPIC)
			{
				$name = html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $oid,
						"group" => $args["request"]["group"],
						"topic" => $_to->id(),
						"section" => $section,
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
			"id" => $oid,
			"group" => $args["request"]["group"],
			"name" => $args["obj_inst"]->name(),
		));

		array_unshift($path,$fp);

		// path drawing ends .. sucks
		$this->vars(array(
			"ADMIN_TOPIC" => "",
		));
		if ($this->_can_admin(array(
			"forum_id" => $oid,
			"uid" => $topic_obj->createdby(),
		)))
		{
			$this->vars(array(
				"ADMIN_TOPIC" => $this->parse("ADMIN_TOPIC"),
			));
		}

		$this->vars(array(
			"active_page" => $pager,
			"name" => $topic_obj->name(),
			"createdby" => $topic_obj->prop("author_name"),
			"date" => $this->time2date($topic_obj->created(),2),
			"comment" => $this->_filter_output($topic_obj->comment()),
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

		if (0 == $topic_obj->prop("locked"))
		{

			$this->read_template("add_comment.tpl");
			$this->reforb_action = "submit_comment";
			$this->_add_style("style_form_caption");
			$this->_add_style("style_form_text");
			$this->_add_style("style_form_element");
			$this->vars($this->style_data);
			//return $rv . $this->parse();

			$retval = array();

			
			if (false === strpos(aw_global_get("REQUEST_URI"),"class="))
			{
				$embedded = true;		
			}

			if ($embedded)
			{
				$retval["_alias"] = array(
					"type" => "hidden",
					"name" => "_alias",
					"value" => 1,
				);
			};
			
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

			$rv .= $this->parse();
		};
			
		$retval["contents"] = array(
			"type" => "text",
			"name" => "contents",
			"value" => $rv,
			"no_caption" => 1,
		);

		return $retval;

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

	function callback_mod_retval($args = array())
	{
		$req = $args["request"];
		if ($this->topic_id)
		{
                	$emb = $args["request"]["emb"];
			$rv_args = &$args["args"];
			$rv_args["folder"] = $emb["parent"];
			$rv_args["topic"] = $this->topic_id;
			$rv_args["group"] = "contents";
			$rv_args["page"] = $args["request"]["page"];
		}
		else
		{
			$rv_args = &$args["args"];
			if ($req["folder"])
			{
				$rv_args["folder"] = $req["folder"];
			};
			if ($req["section"])
			{
				$rv_args["_alias"] = get_class($this);
			};
		};	
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
		$topic_folder = $arr["obj_inst"]->prop("topic_folder");
		//$depth = $arr["obj_inst"]->prop("topic_folder");
		$depth = $topic_folder;
		$this->rv = "";

		$ot = new object_tree(array(
			   "parent" => $topic_folder,
			   "class_id" => CL_MENU,
		));

		$this->ot = $ot;

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "spacer",
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Teema"),
		));

		$t->define_field(array(
			"name" => "exclude",
			"caption" => t("Jäta välja"),
			"align" => "center",
			"width" => 100,
		));

		$t->define_field(array(
			"name" => "exclude_subs",
			"caption" => t("k.a. alamkaustad"),
			"align" => "center",
			"width" => 100,
		));

		$this->t = &$t;

		$this->exclude = $arr["obj_inst"]->meta("exclude");
		$this->exclude_subs = $arr["obj_inst"]->meta("exclude_subs");

		$this->_do_rec_topic(array(
			"parent" => $topic_folder,
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
			$this->t->define_data(array(
				"name" => $item->name(),
				"spacer" => str_repeat("&nbsp;",$level*3),
				"exclude" => html::checkbox(array(
					"name" => "exclude[${id}]",
					"checked" => $this->exclude[$id],
				)),
				"exclude_subs" => html::checkbox(array(
					"name" => "exclude_subs[${id}]",
					"checked" => $this->exclude_subs[$id],
				)),
			));			
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
		if (is_oid($request["folder"]))
		{
			$arr["folder"] = $request["folder"];
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
		$this->inst->embedded = true;
		$this->embedded = true;

		// XXX: temporary workaround to make embedded forum work correctly
		parse_str(aw_global_get("REQUEST_URI"),$req_args);
		$act = isset($req_args["action"]) ? $req_args["action"] : "change";
		$group = isset($req_args["group"]) ? $req_args["group"] : "contents";


		if (method_exists($this, $act))
		{
			$args = array(
				"id" => $alias["target"],
				"action" => $act,
				"rel_id" => $args["alias"]["relobj_id"],
				"folder" => $req_args["folder"],
				"topic" => $req_args["topic"],
				"page" => $req_args["page"],
				"c" => $req_args["c"],
				"cb_part" => 1,
				"form_embedded" => 1,
				"fxt" => 1,
				"group" => $group,
			);
			return $this->$act($args);
		}
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->classconfig = array(
			"hide_tabs" => 1,
			"relationmgr" => false,
		);

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
		//$this->read_template("add_topic.tpl");
		$this->obj_inst = new object($arr["id"]);
		//$this->_add_style("style_form_caption");
		//$this->_add_style("style_form_text");
		//$this->_add_style("style_form_element");
		// see raip ntx tuleks viia class_base peale
		//$this->vars($this->style_data);
		//$this->vars(array(
		//	"reforb" => $this->mk_reforb("submit_topic",array(
		//		"id" => $arr["id"],
		//		"section" => aw_global_get("section"),
		//		"folder" => $arr["folder"],
		//	)),
		//));
		//return $this->parse();
		$htmlc = get_instance("cfg/htmlclient",array("template" => "webform.tpl"));
		$htmlc->start_output();

		$htmlc->add_property(array(
			"name" => "caption",
			"caption" => t("Uus teema"),
			"type" => "text",
			"subtitle" => 1,
		));

		$cfgu = get_instance("cfg/cfgutils");
                $props = $cfgu->load_class_properties(array(
                        "clid" => CL_MSGBOARD_TOPIC,
                ));
	
		$use_props = array("author_name","name","author_email","answers_to_mail","comment");

		$cb_values = aw_global_get("cb_values");
		aw_session_del("cb_values");
		foreach($use_props as $key)
		{
			$propdata = $props[$key];
			if (isset($cb_values[$key]["error"]))
			{
				$propdata["error"] = $cb_values[$key]["error"];
			};
			if (isset($cb_values[$key]["value"]))
			{
				$propdata["value"] = $cb_values[$key]["value"];
			};
			$htmlc->add_property($propdata);
		};

		/*
		$htmlc->add_property($props["author_name"]);
		$htmlc->add_property($props["name"]);
		$htmlc->add_property($props["author_email"]);
		$htmlc->add_property($props["comment"]);
		*/

                $htmlc->add_property(array(
                        "name" => "sbt",
                        "caption" => t("Lisa"),
                        "type" => "submit",
                ));

		$class = aw_global_get("class");
		// XXX: are we embedded? I know, this sucks :(
		$form_handler = "";
		if (empty($_GET["class"]))
		{
			$form_handler = aw_ini_get("baseurl") . "/" . aw_global_get("section");
		};

		$htmlc->finish_output(array("data" => array(
				"class" => get_class($this),
				"section" => aw_global_get("section"),
				"action" => "submit_topic",
				"folder" => $arr["folder"],
				"id" => $arr["id"],
			),
			"form_handler" => $form_handler,
                ));

                $html = $htmlc->get_result(array(
                        "form_only" => 1
                ));

                return $html;

		/*
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
		*/
	}

	/**  
		
		@attrib name=submit_topic params=name all_args="1" nologin="1"
		
		@returns
		
		
		@comment

	**/
	function submit_topic($arr)
	{
		$t = get_instance(CL_MSGBOARD_TOPIC);
		if(is_oid($arr["id"]) && $this->can("view", $arr["id"]))
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
                $emb = $arr;
		$emb["parent"] = $arr["folder"];
		$emb["forum_id"] = $arr["id"];
		$arr["group"] = "contents";
		$emb["status"] = STAT_ACTIVE;
		$emb["return"] = "id";
		unset($emb["id"]);
                $this->topic_id = $t->submit($emb);
		$cb_values = $t->cb_values;
		// ma pean tagasi suunama siin
		if (is_array($cb_values) && sizeof($cb_values) > 0)
		{
			return $this->abort_action($arr);
		};
		$arr["topic"] = $this->topic_id;
		// see bloody finish_action kalab :(

		aw_session_set("no_cache", 1);
		return $this->finish_action($arr);
	}
	
	/** Creates a new comment object for a topic 
		
		@attrib name=submit_comment params=name all_args="1" nologin="1"

	**/
	function submit_comment($arr)
	{
		if(is_oid($arr["id"]) && $this->can("view", $arr["id"]))
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
				
		aw_session_set("no_cache", 1);
		return $this->finish_action($arr);
		/*
                $this->comm_id = $t->submit($emb);
		unset($arr["class"]);
		$arr["alias"] = get_class($this);

		$topic->mail_subscribers(array(
			"id" => $arr["topic"],
			"message" => $arr["commtext"],
			"forum_id" => $arr["id"],
		));
		
		$rv = $this->finish_action($arr);
		$rv = aw_url_change_var("class","",$rv);
		return $rv;
		*/
	}

	/**
		@attrib name=delete_comments

	**/
	function delete_comments($arr)
	{
		// _can_admin requires reltypes defined in class header, creating an instance
		// of the object loads them
		$forum_obj = new object($arr["id"]);
		if ($this->_can_admin(array("forum_id" => $arr["id"])) && sizeof($arr["del"]) > 0)
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

	/**  
		@attrib name=change params=name all_args="1" nologin="1"
		
		@param id optional type=int 
		@param group optional
		@param period optional
		@param alias_to optional
		@param return_url optional
		
                @returns

                @comment

	**/
	function change($arr)
	{
		if (!is_admin())
		{
			$arr["fxt"] = 1;
			$arr["group"] = "contents";
		}
		return parent::change($arr);
	}


	/**
		@comment 
			checks whether the remote user can admin the forum
	**/
//	function _can_admin($forum_id)
	function _can_admin($arr)
	{
		// admin can be either CL_USER or CL_GROUP, check for both
		// checking if uid comes through function params ($arr)
		// if it doesn't, then use logged in user
		if (!isset($arr['uid']))
		{
			$uid_oid = aw_global_get("uid_oid");
			$gids = aw_global_get("gidlist_oid");

		}
		else
		if (!empty($arr['uid']))
		{
			$uid_oid = users::get_oid_for_uid($arr['uid']);
			$user_inst = get_instance(CL_USER);
			$user_groups = $user_inst->get_groups_for_user($arr['uid']);
			$gids = $this->make_keys($user_groups->ids());

		}

		if (empty($uid_oid) || empty($arr['forum_id']))
		{
			return false;
		}

		if ($_GET["XX5"])
		{
			$nlg = $this->get_cval("non_logged_in_users_group");
			$g_oid = users::get_oid_for_gid($nlg);
			print "nlg = $nlg, g_oid = $g_oid<br>";
		};


		$check_ids = array($uid_oid) + $gids;
		$c = new connection();
		$conns = $c->find(array(
			"from" => $arr['forum_id'],
			"to" => $check_ids,
			"type" => 5 //RELTYPE_FORUM_ADMIN,
		));

		return sizeof($conns) > 0;

	}
	

	function on_connect_menu($arr)
	{
		$conn = &$arr["connection"];
		if ($conn->prop("reltype") == 1) //RELTYPE_TOPIC_FOLDER
		{
			// now I need to grant certian privileges
			$nlg = $this->get_cval("non_logged_in_users_group");
                        $g_oid = users::get_oid_for_gid($nlg);
			$group = new object($g_oid);

			$target_object = new object($conn->prop("to"));
			$target_object->acl_set($group, array("can_add" => 1, "can_view" => 1));
			$target_object->save();

		};

	}

	function _filter_output($text)
	{
		if (false !== strpos($text,"#php#"))
		{
			$text = preg_replace("/(#php#)(.+?)(#\/php#)/esm","highlight_string(stripslashes('<'.'?php'.'\$2'.'?'.'>'),true)",$text);
		};
		$text = preg_replace("/\r([^<])/m","<br />\n\$1",$text);
		//$text = nl2br($text);
		return $text;
	}

	function callback_post_save($arr)
	{
		if ($arr["request"]["new"])
		{
			// create folders and set props
			$topic_folder = obj();
			$topic_folder->set_parent($arr["obj_inst"]->parent());
			$topic_folder->set_name($arr["obj_inst"]->name().t(" teemade kaust"));
			$topic_folder->set_class_id(CL_MENU);
			$topic_folder->save();
			$arr["obj_inst"]->set_prop("topic_folder", $topic_folder->id());

			$address_folder = obj();
			$address_folder->set_parent($arr["obj_inst"]->parent());
			$address_folder->set_name($arr["obj_inst"]->name().t(" aadresside kaust"));
			$address_folder->set_class_id(CL_MENU);
			$address_folder->save();
			$arr["obj_inst"]->set_prop("address_folder", $address_folder->id());

			$arr["obj_inst"]->save();
		}
	}

	/**
		@attrib name=add_faq no_login=1
		@param id required type="int" acl="edit"
		@param topic required type="int" acl="edit"
		@param section optional 
	**/	
	function add_faq($arr)
	{
		
		$forum_obj = new object($arr['id']);
		$faq_folder_id = $forum_obj->prop("faq_folder");
		if (!empty($faq_folder_id))
		{

			$topic_obj = new object($arr['topic']);	

			$comment_inst = get_instance(CL_COMMENT);
			$comments = $comment_inst->get_comment_list(array("parent" => $topic_obj->id()));
			$comments_str = "";
			foreach ($comments as $comment)
			{
				$comments_str .= $comment['name']."<br />\n";
				$comments_str .= "-------------------------------------------------------<br />\n";
				$comments_str .= $comment['commtext']."<br /><br />\n\n";
			}
	
			$faq_document = new object();
			$faq_document->set_class_id(CL_DOCUMENT);
			$faq_document->set_parent($faq_folder_id);
			$topic_obj_name = $topic_obj->name();
			$faq_document->set_name($topic_obj_name);
			$faq_document->set_status(STAT_ACTIVE);
			$faq_document->set_prop("title", $topic_obj_name); 
			$faq_document->set_prop("lead", $this->_filter_output($topic_obj->comment()));
			$faq_document->set_prop("content", $this->_filter_output($comments_str));
			$faq_document->save();

		}
		return $this->mk_my_orb("change", array(
				"id" => $forum_obj->id(),
				"section" => $arr['section'],
				"group" => "contents",
				"_alias" => get_class($this),
			) 
		);
	}
};
?>
