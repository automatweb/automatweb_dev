<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/forum.aw,v 2.53 2002/10/15 20:33:51 duke Exp $
// foorumi hindamine tuleb teha 100% konfigureeritavaks, s.t. 
// hindamisatribuute peab saama sisestama läbi veebivormi.

class forum extends aw_template
{
	function forum($args = array())
	{
		extract($args);
		$this->embedded = false;

		$this->init("msgboard");
		// $this->sub_merge = 1;
		// to keep track of how many topics we have already drawn
		$this->topic_count = 0; 

		if ($this->embedded)
		{	
			global $section;
			// remember the section id to keep the layout
			if ($section)
			{
				$this->section = $section;
			}
		};

		if ($section)
		{
			$this->section = $section;
		};

		// yikes
		classload("users_user");
		$u = new users_user();
		$this->$members = $u->getgroupmembers("Kasutajatugi");
		
		$this->lc_load("msgboard","lc_msgboard");
	}

	function change_rates($args = array())
	{
		extract($args);
		$this->read_template("edit_ratings.tpl");
		$title = "Muuda foorumit";
		$_tmp = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"oid" => $id,
		));
		$parent = $tmp["parent"];
		$this->mk_path($parent, $title);
		$c = "";
		if (is_array($meta["rates"]))
		{
			$ords = array();
			foreach($meta["rates"] as $key => $rate)
			{
				$ords[$key] = $rate["ord"];
			};
			asort($ords);

			foreach($ords as $key => $val)
			{
				$this->vars(array(
					"id" => $key,
					"ord" => $meta["rates"][$key]["ord"],
					"name" => $meta["rates"][$key]["name"],
					"rate" => $meta["rates"][$key]["rate"],
				));

				$c .= $this->parse("rateline");
			};
		};
		$this->vars(array(
			"topics_link" => $this->mk_my_orb("topics",array("id" => $id)),
			"new_rate_link" => $this->mk_my_orb("add_rate",array("id" => $id)),
			"rateline" => $c,
			"change_link" => $this->mk_my_orb("configure",array("id" => $id)),
			"rates_link" => $this->mk_my_orb("change_rates",array("id" => $id)),
			"reforb" => $this->mk_reforb("submit_rates",array("id" => $id)),
		));
		return $this->parse();
	}

	function submit_rates($args = array())
	{
		extract($args);
		$rates = array();
		if (is_array($rate_name))
		{
			foreach($rate_name as $key => $val)
			{
				if ($delete && $rate_check[$key])
				{
				}
				else
				{
					$rates[$key] = array("ord" => $rate_order[$key],"name" => $val,"rate" => $rate_value[$key]);
				};
			};

			$this->set_object_metadata(array(
				"oid" => $id,
				"key" => "rates",
				"value" => $rates,
			));
		};
		return $this->mk_my_orb("change_rates",array("id" => $id));
	}

	function add_rate($args = array())
	{
		extract($args);
		$this->mk_path(0,"Lisa hinne");
		$this->read_template("add_rate.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_rate",array("id" => $id)),
		));
		return $this->parse();
	}

	function submit_rate($args = array())
	{
		extract($args);
		$old_rates = $this->get_object_metadata(array(
			"oid" => $id,
			"key" => "rates",
		));
		$old_rates[] = array("name" => $name,"rate" => $rate);
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "rates",
			"value" => $old_rates,
		));
		return $this->mk_my_orb("change_rates",array("id" => $id));
	}

	function notify_list($args = array())
	{
		extract($args);
		$this->read_template("notify_list.tpl");
		$this->mk_path(0,"Muuda foorumit");
	
		load_vcl("table");	
		$t = new aw_table(array(
			"prefix" => "nforum",
			"tbgcolor" => "#C3D0DC",
		));

		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "address",
			"caption" => "Aadress",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "check",
			"caption" => "Vali",
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			//"sortable" => 1,
		));

		$nflist = $this->get_object_metadata(array(
			"oid" => $id,
			"key" => "notifylist",
		));

		if (is_array($nflist))
		{
			foreach($nflist as $key => $val)
			{
				$t->define_data(array(
					"name" => $val["name"],
					"address" => $val["address"],
					"check" => "<input type='checkbox' name='chk[$key]' value='1'>",
				));
			}
		};

		$t->sort_by();

		$this->vars(array(
			"table" => $t->draw(),
			"change_link" => $this->mk_my_orb("configure",array("id" => $id)),
			"rates_link" => $this->mk_my_orb("change_rates",array("id" => $id)),
			"reforb" => $this->mk_reforb("submit_notify_list",array("id" => $id)),
		));
		return $this->parse();
	}

	function submit_notify_list($args = array())
	{
		extract($args);
		$nflist = $this->get_object_metadata(array(
			"oid" => $id,
			"key" => "notifylist",
		));
		if (is_array($chk))
		{
			foreach($chk as $key => $val)
			{
				unset($nflist[$key]);
			};
		};

		if ($newaddress && $newname)
		{
			$nflist[] = array(
				"name" => $newname,
				"address" => $newaddress,
			);
		}
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "notifylist",
			"overwrite" => 1,
			"value" => $nflist,
		));
		return $this->mk_my_orb("notify_list",array("id" => $id));
	}

	////
	// !Displays the form for configuring the form
	function configure($arr)
	{
		extract($arr);
		// if parent is defined, then we are about to add a new forum,
		if ($parent)
		{
			$pobj = $this->get_object($parent);
			// FIXME: if we entered this function from the objects list,
			// we redirect the user to the topic list, becase the Big Pointy
			// Haired boss wants it that way. And besides, this is temporary
			// (yeah, right) anyway, until we figure out something better.
			//if (not($new) && ($pobj["class_id"] == CL_PSEUDO))
			//{
			//	header("Location: " . $this->mk_my_orb("topics",array("id" => $id)));
			//	exit;
			//};
			
			$title = "Lisa foorum";
		
			$meta = array();
		}
		// otherwise we are modifying an existing forum
		else
		{
			$obj = $this->get_object($id);
			$this->id = $id;
			$pobj = $this->get_object($obj["parent"]);
			$title = "Muuda foorumit";
			$this->mk_path($parent, $title);
			$meta = $this->get_object_metadata(array("metadata" => $obj["metadata"]));
		};

		$toolbar = get_instance("toolbar");

		$content_url = $this->mk_my_orb("topics",array("id" => $id));
		$rates_url = $this->mk_my_orb("change_rates",array("id" => $id));
		$notify_url = $this->mk_my_orb("notify_list",array("id" => $id));

		$content_link = "<a href='$content_url' class='fgtitle'>Foorumi sisu</a>";
		$rates_link = "<a href='$rates_url' class='fgtitle'>Hinded</a>";
		$notify_link = "<a href='$notify_url' class='fgtitle'>E-posti aadressid</a>";

		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.changeform.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
                ));

		if ($this->id)
		{
			$toolbar->add_separator();
			$toolbar->add_cdata($content_link);
			$toolbar->add_separator();
		
			$toolbar->add_cdata($rates_link);
			$toolbar->add_separator();
		
			$toolbar->add_cdata($notify_link);
		};

		if ($pobj["class_id"] == CL_DOCUMENT)
		{
			$this->mk_path($pobj["parent"],sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("list_aliases",array("id" => $parent),"aliasmgr"),$pobj["name"]) . " / $title");
	
		}
		else
		{
			$this->mk_path($pobj["oid"], $title);
		};

		$cfgform = get_instance("cfgform");
		$reforb = $this->mk_reforb("submit_properties",array("id" => $id,"parent" => $parent));
		$xf = $cfgform->ch_form(array(
				"clid" => &$this,
				"obj" => $obj,
				"reforb" => $reforb,
		));

		return $toolbar->get_toolbar() . $xf;
	}
	
	////
	// !Creates a new forum or updates configuration for an existing one
	function submit_properties($arr)
	{
		$this->quote($arr);
		extract($arr);
		// we need to know the type of the parent object to figure
		// out what to do after the forum has been added.
		$pobj = $this->get_object($parent);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_FORUM,
				"name" => $name,
				"comment" => $comment,
			));
		}
	
		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"comments" => $comments,
				"rated" => $rated,
				"template" => $template,
				"onpage" => $onpage,
				"topicsonpage" => $topicsonpage,
			),
		));

		if ($pobj["class_id"] == CL_DOCUMENT)
		{
			// yea, it was a document allright. So we create an alias
			// and return to the alias list. Or shouldn't we?
			$this->add_alias($parent,$id);
			$retval = $this->mk_my_orb("list_aliases",array("id" => $parent),"aliasmgr");
		}
		else
		{
			$retval = $this->mk_my_orb("configure", array("id" => $id,"parent" => $parent));
		};
		return $retval;
	}

	////
	// !Displays tabs 
	function tabs($args = array(),$active = "")
	{
		// et mitte koiki seniseid saite katki teha
		if (not($this->cfg["tabs"]))
		{
			return "";
		};

		$id = $this->forum_id;
		$board = $this->board;
		$from = $this->from;
		// oh god, I hate this
		if (strpos(aw_global_get("REQUEST_URI"),"automatweb"))
		{
			array_push($args,"configure");
		};

		$tabs = array(
			"newtopic" => $this->mk_my_orb("add_topic",array("id" => $id,"_alias" => "forum","section" => $this->section)),
			"configure" => $this->mk_my_orb("configure",array("id" => $id,"_alias" => "forum","section" => $this->section)),
			"addcomment" => $this->mk_my_orb("addcomment",array("board" => $board,"_alias" => "forum","section" => $this->section)),
			"forum_link" => $this->mk_my_orb("topics",array("id" => $id,"_alias" => "forum", "section" => $this->section)),
			"archive" => $this->mk_my_orb("topics",array("id" => $id,"_alias" => "forum", "section" => $this->section,"archive" => 1)),
			"props_link" => $this->mk_my_orb("configure",array("id" => $id)),
			"mark_all_read" => $this->mk_my_orb("mark_all_read",array("id" => $id,"_alias" => "forum", "section" => $this->section)),
			"search" => $this->mk_my_orb("search",array("id" => $id,"_alias" => "forum","section" => $this->section,)),
			"search_link" => $this->mk_my_orb("search",array("id" => $id,"_alias" => "forum","section" => $this->section,)),
			"flatcomments" => $this->mk_my_orb("show",array("board" => $board,"_alias" => "forum","section" => $this->section)),
			"threadedcomments" => $this->mk_my_orb("show_threaded",array("board" => $board,"_alias" => "forum","section" => $this->section)),
			"threadedsubjects" => $this->mk_my_orb("show_threaded",array("board" => $board,"_alias" => "forum","section" => $this->section,"no_comments" => 1)),
			"no_response" => $this->mk_my_orb("no_response",array("board" => $board,"_alias" => "forum","section" => $this->section)),
			"details" => $this->mk_my_orb("topics_detail",array("id" => $id, "_alias" => "forum","section" => $this->section, "from" => $from)),
			"flat" => $this->mk_my_orb("topics",array("id" => $id, "_alias" => "forum","section" => $this->section, "from" => $from)),
		);

		$captions = array(
			"newtopic" => "Uus teema",
			"addcomment" => "Uus küsimus",
			"flat" => "Teemad",
			"configure" => "Konfigureeri",
			"archive" => "Arhiiv",
			"threadedsubjects" => "Pealkirjad",
			"mark_all_read" => "Kõik loetuks",
			"search" => "Otsi",
			//"no_response" => "Vastamata küsimused",
			"details" => "Foorum",
			"flatcomments" => "Aja järgi",
			"threadedcomments" => "Kommentaarid",
		);

		$retval .= "";
		$this->read_template("tabs.tpl");
		foreach($args as $key => $val)
		{
			if ( ($val == "newtopic") && $this->cfg["newtopic_logged_only"] == 1 && aw_global_get("uid") == "" )
			{
				// suck
			}
			else
			{
				if ($captions[$val])
				{
					$this->vars(array(
						"link" => $tabs[$val],
						"caption" => $captions[$val],
					));
					$tpl = ($active == $val) ? "active_tab" : "tab";
					$retval .= $this->parse($tpl);
				};
			};
		}
		$this->vars(array(
			"tab" => $retval,
		));
		return $this->parse();

	}

	////
	// !Generates links for forum templates. This has to be in one central place
	// to make it easier to alter the way links are shown
	// TODO: we conver those links into tabs .. which would then use the TAB subtemplate
	// in the template to display links. That would really be much more dynamic
	function mk_links($args = array())
	{
		extract($args);
		$alias = ($this->embedded) ? "forum" : "";

		if ($id)
		{
			$this->vars(array(
				"newtopic_link" => $this->mk_my_orb("add_topic",array("id" => $id,"_alias" => $alias,"section" => $this->section)),
				"forum_link" => $this->mk_my_orb("topics",array("id" => $id,"_alias" => $alias, "section" => $this->section)),
				"props_link" => $this->mk_my_orb("configure",array("id" => $id)),
				"mark_all_read" => $this->mk_my_orb("mark_all_read",array("id" => $id,"_alias" => $alias, "section" => $this->section)),
				"search_forum_link" => $this->mk_my_orb("search",array("id" => $id,"_alias" => $alias,"section" => $this->section,)),
				"search_link" => $this->mk_my_orb("search",array("id" => $id,"_alias" => $alias,"section" => $this->section,)),
				"topic_detail_link" => $this->mk_my_orb("topics_detail",array("id" => $id, "_alias" => $alias,"section" => $this->section, "from" => $from)),
				"topic_flat_link" => $this->mk_my_orb("topics",array("id" => $id, "_alias" => $alias,"section" => $this->section, "from" => $from)),
				"flat_link" => $this->mk_my_orb("topics",array("id" => $id, "_alias" => $alias,"section" => $this->section, "from" => $from)),
			));
		}
		
		if ($board)
		{
			$b_obj = $this->get_object($board);
			if ($b_obj["class_id"] == CL_PERIODIC_SECTION)
			{
				$topic_link = document::get_link($board);
			};
			$this->vars(array(
				"topic_link" => $topic_link,
				"threaded_link" => $this->mk_my_orb("show_threaded", array("board" => $board,"_alias" => $alias,"section" => $this->section)),
				"threaded_topic_link" => $this->mk_my_orb("show_threaded", array("board" => $board,"_alias" => $alias,"section" => $this->section)),
				"change_topic" => $this->mk_my_orb("change_topic", array("board" => $board,"_alias" => $alias,"section" => $this->section)),
				"flat_link" => $this->mk_my_orb("show",array("board" => $board,"_alias" => $alias,"section" => $this->section)),
				"search_link" => $this->mk_my_orb("search",array("board" => $board,"_alias" => $alias,"section" => $this->section)),
				"topic_detail_link" => $this->mk_my_orb("topics_detail",array("id" => $id, "from" => $from,"_alias" => $alias,"section" => $this->section)),
				"forum_link" => $this->mk_my_orb("topics",array("id" => $parent,"_alias" => $alias, "section" => $this->section)),
			));
		};
	}

	////
	// !Kuvab uue topicu lisamise vormi
	function add_topic($args = array())
	{
		// this first setting should really be configurable on per-forum basis
		if ( $this->cfg["newtopic_logged_only"] && aw_global_get("uid") == "" )
		{
			classload("config");
			$c = new db_config;
			$doc = $c->get_simple_config("orb_err_mustlogin");
			if ($doc != "")
			{
				header("Location: $doc");
				die();
			}
			else
			{
				$this->raise_error(ERR_FORUM_LOGIN,E_ORB_LOGIN_REQUIRED,$fatal,$silent);
			}
		};
		extract($args);
		if ($section)
		{
			$this->section = $section;
		};
		$object = $this->get_object($id);
		#$parent = $this->get_object($object["parent"]);
		// kui kaasa antakse section argument, siis peaks kontrollima
		// kas see ikka kuulub selle foorumi juurde
		$text = $this->mk_orb("configure",array("id" => $id));
		$this->mk_path($object["parent"],"<a href='$text'>$object[name]</a> / Lisa teema");
		$this->forum_id = $id;
		$tabs = $this->tabs(array("flat","details","newtopic","mark_all_read","archive","search"),"new
topic");
		$this->read_template("add_topic.tpl");
		$this->mk_links(array(
			"id" => $id,
		));
		$this->vars(array(
			"TABS" => $tabs,
			"reforb" => $this->mk_reforb("submit_topic",array("id" => $id,"section" => $section)),
		));
		return $this->parse();
	}

	////
	// !Lisab uue topicu
	function submit_topic($args = array())
	{
		extract($args);
		$tid = $this->new_object(array(
			"parent" => $id,
			"name" => $topic,
			"comment" => ($text) ? $text : $comment,
			"last" => $from,
			"class_id" => CL_MSGBOARD_TOPIC,
			"status" => 2,
		));
		
		$this->set_object_metadata(array(
			"oid" => $tid,
			"key" => "author_email",
			"value" => $email,
		));

		if ($section)
		{
			$retval = $this->cfg["baseurl"] . "/?section=$section";
		}
		else
		{
			$retval = $this->mk_my_orb("topics",array("id" => $id,"section" => $section));
		}
		return $retval;
	}

	////
	// !Shows a flat list of messages
	function show($args = array())
	{
		extract($args);
		$board_obj = $this->get_obj_meta($board);
		$forum_obj = $this->get_obj_meta($board_obj["parent"]);
		global $HTTP_COOKIE_VARS;
		$aw_mb_last = unserialize($HTTP_COOKIE_VARS["aw_mb_last"]);
		$aw_mb_last[$board_obj["parent"]] = time();
		$meta = $this->get_object_metadata(array("oid" => $forum_obj["oid"]));
		$board_meta = $this->get_object_metadata(array("oid" => $board));
		setcookie("aw_mb_last",serialize($aw_mb_last),time()+24*3600*1000);
		$flink = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("configure",array("id" => $forum_obj["oid"])),$forum_obj["name"]);
		$this->mk_path($forum_obj["parent"],$flink . " / $board_obj[name]");
		$this->_query_comments(array("board" => $board));
		$this->comm_count = 0;
		$this->section = $section;
		$this->board = $board;
		if (not($id))
		{
			$id = $forum_obj["oid"];
		};
		$this->forum_id = $id;

		$content = "";

	
		$tabs = $this->tabs(array("flat","addcomment","flatcomments","threadedcomments","threadedsubjects","no_response","search","details"),"flatcomments");	
		if ($addcomment)
		{
			$tabs = $this->tabs(array("flat","addcomment","flatcomments","threadedcomments","threadedsubjects","no_response","search","details"),"addcomment");

		}
		elseif ($no_response)
		{
			$tabs = $this->tabs(array("flat","addcomment","flatcomments","threadedcomments","threadedsubjects","no_response","search","details"),"no_response");
		};
		$rated = "";
		if ($meta["rated"])
		{
			$rated = $this->_draw_ratings($meta["rates"],$board_meta);
		};
		$this->read_template("messages.tpl");


		if ($no_response)
		{
			while($row = $this->db_next())
			{
				$this->_comments[$row["parent"]][] = $row;
				$this->comm_count++;
			};
			$this->level = 1;
			$this->count_replies(0);
			if (is_array($this->_comments))
			{
			foreach($this->_comments[0] as $key => $val)
			{
				if ($this->reply_counts[$val["id"]] == 0)
				{
					$content .= $this->display_comment($val);
				};
			};
			};
		}
		elseif (not($addcomment))
		{
			while($row = $this->db_next())
			{
				$this->comm_count++;
				$content .= $this->display_comment($row);
			};
		};


		// miskit splitter tyypi funktsiooni on vaja, mis soltuvalt sellest kas tegu on adminni
		// voi dokumendi sees oleva asjaga valjastaks sobiva lingi
		if (not($id))
		{
			$id = $forum_obj["oid"];
		};

		$comment = stripslashes($board_obj["comment"]);
		$comment = str_replace("'","",$comment);
		$this->vars(array(
			"topic" => $board_obj["name"],
			"from" => ($board_obj["last"]) ? $board_obj["last"] : $board_obj["createdby"],
			"email" => $board_obj["meta"]["author_email"],
			"created" => $this->time2date($board_obj["created"],2),
			"rated" => $rated,
			"rate" => sprintf("%0.2f",$board_obj["rate"]),
			"text" => nl2br(create_links($comment)),
			"reforb" => $this->mk_reforb("submit_messages",array("board" => $board,"section" => $this->section,"act" => "show")),
		));

		$this->mk_links(array(
			"board" => $board,
			"id" => $id
		));

		$voteblock = "";
	
		if ($forum_obj["meta"]["rated"])
		{	
			// arvutame häälte arvu sellele foorumile
			if ($board_obj["meta"]["voters"] == 0)
			{
				$rate = 0;
			}
			else
			{
				$rate = $board_obj["meta"]["votesum"] / $board_obj["meta"]["voters"];
			};

			$this->vars(array(
				"vote_reforb" => $this->mk_reforb("submit_vote",array("board" => $board)),
				"rate" => sprintf("%0.2f",$rate),
			));
		
			global $forum_votes;
			if ($forum_votes[$board])
			{
				$voteblock = $this->parse("ALREADY_VOTED");
			}
			else
			{
				$voteblock = $this->parse("VOTE_FOR_TOPIC");
			};
		};

		$this->vars(array(
			"CHANGE_TOPIC" => ($this->prog_acl("view", PRG_MENUEDIT) ? $this->parse("CHANGE_TOPIC") : "")
		));


		$this->vars(array(
			"TABS" => $tabs,
			"message" => $content,
			"VOTE_FOR_TOPIC" => $voteblock,
			"TOPIC" => $this->parse("TOPIC"),
		));
		if ($this->prog_acl("view", PRG_MENUEDIT))
		{
			$actions = $this->parse("actions");
		}
	
		if ($this->comm_count > 0)
		{
			$this->vars(array(
				"actions" => $actions,
			));
		};

		$this->vars(array(
			"forum_link" => $this->mk_my_orb("topics",array("id" => $board_obj["parent"])),
		));
		$retval = $this->parse();
		$retval .= $this->add_comment(array("board" => $board,"parent" => $parent,"section" => $section,"act" => "show"));

		return $retval;

	}

	////
	// !Submits a vote to a topic
	function submit_vote($args = array())
	{
		extract($args);
		//global $forum_votes;
		if (not($forum_votes[$args["board"]]))
		{
			$forum_votes[$args["board"]] = rand();
			session_register("forum_votes");
			$board_obj = $this->get_obj_meta($board);
			$voters = ++$board_obj["meta"]["voters"];
			$votesum = $board_obj["meta"]["votesum"] + $vote;
			$this->set_object_metadata(array(
				"oid" => $board,
				"key" => "voters",
				"value" => $voters,
			));
			$this->set_object_metadata(array(
				"oid" => $board,
				"key" => "votesum",
				"value" => $votesum,
			));
		}

		return $this->mk_my_orb("show",array("board" => $board));
	}

	////
	// !Shows a threaded list of messages
	function show_threaded($args = array())
	{
		$this->level = 0;
		$this->comments = array();
		$this->content = "";
		extract($args);
		$board_obj = $this->get_obj_meta($board);
		$forum_obj = $this->get_object($board_obj["parent"]);
		$meta = $this->get_object_metadata(
			array("oid" => $forum_obj["oid"],
		));
		$board_meta = $this->get_object_metadata(array("oid" => $board));
		$flink = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("configure",array("id" => $forum_obj["oid"])),$forum_obj["name"]);
		$this->mk_path($forum_obj["parent"],$flink . " / $board_obj[name]");
		$this->forum_id = $forum_obj["oid"];
		$this->board = $board;
		$this->section = $section;
		
		$this->_query_comments(array("board" => $board));
	
		$rated = "";
		if ($meta["rated"])
		{
			$rated = $this->_draw_ratings($meta["rates"],$board_meta);
		};
	
		if ($no_comments)
		{
			$tabs = $this->tabs(array("flat","addcomment","flatcomments","threadedcomments","threadedsubjects","no_response","search","details"),"threadedsubjects");
			$this->read_template("subjects_threaded.tpl");
		}
		else
		{
			$tabs = $this->tabs(array("flat","addcomment","flatcomments","threadedcomments","threadedsubjects","no_response","search"),"threadedcomments");
			$this->read_template("messages_threaded.tpl");
		};

		$content = "";
		$this->comm_count = 0;
		$this->reply_counts = array();
		while($row = $this->db_next())
		{
			$this->comm_count++;
			$this->_comments[$row["parent"]][] = $row;
		};
		if ($no_response)
		{
			$this->count_replies(0);
		}
		else
		{
			$start_from = ($cid) ? $cid : 0;
			global $HTTP_COOKIE_VARS;
			$this->aw_mb_read = unserialize($HTTP_COOKIE_VARS["aw_mb_read"]);
			if ($cid)
			{

				$q = "SELECT * FROM comments WHERE id = '$cid'";
				$this->db_query($q);
				$crow = $this->db_next();
				#$this->_comments[$start_from][] = $crow;
				$this->mark_comments = 1;
				$this->content .= $this->display_comment($crow);
			};
			if (!$no_comments)
			{
				// if we are showing the threaded version with comments, then we oughta mark them read as well, souldn't we
				$this->mark_comments = true;
			}
			$this->rec_comments($start_from);
		};
		$this->mk_links(array(
			"board" => $board,
			"id" => $forum_obj["oid"],
		));

		$this->vars(array(
			"TABS" => $tabs,
			"message" => $this->content,
			"reforb" => $this->mk_reforb("submit_messages",array("board" => $board,"section" => $this->section,"act" => "show_threaded")),
			"topic" => $board_obj["name"],
			"from" => ($board_obj["last"]) ? $board_obj["last"] : $board_obj["createdby"],
			"email" => $board_obj["meta"]["author_email"],
			"created" => $this->time2date($board_obj["created"],2),
			"rated" => $rated,
			"rate" => sprintf("%0.2f",$board_obj["rate"]),
			"text" => nl2br(create_links($board_obj["comment"])),
		));

		if ($this->prog_acl("view", PRG_MENUEDIT))
		{
			$actions = $this->parse("actions");
		}

		if ($this->comm_count > 0)
		{
			$this->vars(array(
				"actions" => $actions,
			));
		};
		$this->vars(array(
			"TOPIC" => $this->parse("TOPIC"),
			"forum_link" => $this->mk_my_orb("topics",array("id" => $board_obj["parent"])),
		));
		if ($cid)
		{
			$add_params = array("parent" => $cid,"subj" => $crow["subj"]);
		};
		if (!$no_comments)
		{
			setcookie("aw_mb_read",serialize($this->aw_mb_read),time()+24*3600*1000,"/");
		}

		$aw_mb_last = unserialize(stripslashes($HTTP_COOKIE_VARS["aw_mb_last"]));
		$aw_mb_last[$board] = time();
		setcookie("aw_mb_last",serialize($aw_mb_last),time()+24*3600*1000,"/");

		return $this->parse() . $this->add_comment(array_merge(array("board" => $board,"parent" => $parent,"section" => $this->section,"act" => "show_threaded"),$add_params));
	}
	////
	// !Submits a message list
	function submit_messages($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			// unfortunately, it's not so simple. 
			// we gotta delete the comments below the to-be-deleted ones as well, so that 
			// we can do the comment count quickly later
			foreach($check as $delid)
			{
				$this->req_del_comments($delid);
			}
//			$to_delete = join(",",$check);
//			$this->db_query("DELETE FROM comments WHERE id IN ($to_delete)");
		};
		return $this->mk_my_orb($act,array("board" => $board,"_alias" => "forum", "section" => $this->section));
	}

	function req_del_comments($parent)
	{
		if (!$parent)
		{
			return;
		}

		$this->save_handle();
		$this->db_query("SELECT id FROM comments WHERE parent = '$parent'");
		while ($row = $this->db_next())
		{
			$this->req_del_comments($row["id"]);
		}

		$this->db_query("DELETE FROM comments WHERE id = $parent");
		$this->restore_handle();
	}

	function _draw_ratings($args = array(),$board_meta = array())
	{
		$this->read_template("rate.tpl");
		$c = "";
		if (is_array($args))
		{
			foreach($args as $key => $val)
			{
				$this->vars(array(
					"value" => $key,
					"name" => $val["name"],
				));
				$c .= $this->parse("rate");
			}
		};
	
		if ($board_meta["voters"] > 0)
		{
			$ratings = sprintf("%0.02f",$board_meta["votesum"] / $board_meta["voters"]);
		}
		else
		{
			$ratings = "0.00";
		};

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_vote",array("board" => $this->board)),
			"rating" => $ratings,
			"rate" => $c,
		));

		return $this->parse();
	}



	////
	// !creates an indented list of comments
	function rec_comments($level)
	{
		if (not(is_array($this->_comments[$level])))
		{
			return;
		}
		$icons = "";

		$commcount = sizeof($this->_comments[$level]);
		$icon_prefix = "";
	
		if ($this->level > 0)
		{
			for ($i = 0; $i < ($this->level - 1); $i++)
			{
				$icons .= "<img src='".$this->cfg["baseurl"]."/img/forum/vert.gif'>";
			};

			$icon_prefix = "<img src='".$this->cfg["baseurl"]."/img/forum/vert.gif'>";
		}

		$cc = 0;
		foreach($this->_comments[$level] as $key => $val)
		{
			$cc++;
			$val["spacer"] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",$this->level);
			$val["level"] = 20 * $this->level;
			$replies = sizeof($this->_comments[$val["id"]]);
			#$icon_prefix = ($cc == $commcount) ? "<img src='/img/forum/blank.gif'>" : "<img src='/img/forum/vert.gif'>";
			if ($cc == $commcount)
			{
				$icon_sufix = ($replies == 0) ? "last" : "minus-last";
			}
			else
			{
				$icon_sufix = ($replies == 0) ? "node" : "minus";
			};
			$val["icons"] = $icons . $icon_prefix . "<img src='".$this->cfg["baseurl"]."/img/forum/$icon_sufix.gif'>";
			$this->content .= $this->display_comment($val);
			$this->level++;
			$this->rec_comments($val["id"]);
			$this->level--;
		}
	}

	////
	// !counts replies under each message
	function count_replies($level)
	{
		static $use_level = 0;
		if (not(is_array($this->_comments[$level])))
		{
			return;
		}

		foreach($this->_comments[$level] as $key => $val)
		{
			if ($this->level == 1)
			{
				$use_level = $val["id"];
			};
			if ($val["response"])
			{
				$this->reply_counts[$use_level]++;
			};
			$this->level++;
			$this->count_replies($val["id"]);
			$this->level--;
		};
	}
			

	////
	// !displays a reply form
	function reply($args = array())
	{
		extract($args);
		$q = "SELECT * FROM comments WHERE id = '$parent'";
		$this->db_query($q);
		$row = $this->db_next();
		$board_obj = $this->get_object($row["board_id"]);
		$forum_obj = $this->get_object($board_obj["parent"]);
		$flink = sprintf("<a href='%s'>%s</a>",$this->mk_my_orb("configure",array("id" => $forum_obj["oid"])),$forum_obj["name"]);
		$this->mk_links(array("board" => $board_obj["oid"],"id" => $board_obj["parent"]));
		$this->board = $board_obj["oid"];
		$this->mk_path($forum_obj["parent"],$flink . " / $board_obj[name]");
		$this->section = $section;
		$tabs = $this->tabs(array("addcomment","threadedcomments","threadedsubjects","no_response","search","details"),"addcomment");
		 if ($row)
		{
			$this->read_template("messages.tpl");
			$this->vars(array(
				"topic" => $board_obj["name"],
				"from" => ($board_obj["last"]) ? $board_obj["last"] : $board_obj["createdby"],
				"created" => $this->time2date($board_obj["created"],2),
				"rate" => sprintf("%0.2f",$board_obj["rate"]),
				"text" => nl2br(create_links($board_obj["comment"])),
			));
			$content = $this->display_comment($row);
		}
		$this->vars(array(
			"message" => $content,
			"TABS" => $tabs,
			"TOPIC" => $this->parse("TOPIC"),
		));
		$act = $this->cfg["reply_return"] != "" ? $this->cfg["reply_return"] : "show";
		return $this->parse() . $this->add_comment(array("parent" => $parent,"section" => $section,"act" => $act,"subj" => $row["subj"]));
	}

	////
	// !Displays a single comment
	// requires a loaded template with a subtemplate "message"
	function display_comment($args = array())
	{
		if ($args["response"])
		{
			$color = "#D4D4D4";
		}
		else
		{
			$color = "#ececec";
		};

		$new = "";
		if ($this->cfg["track_users"])
		{
			$uid = aw_global_get("uid");
			$board_id = $args["board_id"];
			$id = $args["id"];

			$this->save_handle();
			$uid = aw_global_get("uid");
			$q = "SELECT count(*) AS cnt FROM forum_track WHERE comm_id = '$id' AND uid = '$uid'";
			$this->db_query($q);
			$row = $this->db_next();


			if ($row["cnt"] == 0)
			{
				$new = $this->parse("NEW_MSGS");
			}
			else
			{
				$new = $this->parse("READ_MSGS");
			};
			$this->restore_handle();
		} else if (not($this->aw_mb_read[$args["id"]]))
		{
			$new = $this->parse("NEW_MSG");
		};

		$alias = ($this->embedded) ? "forum" : "";
		$this->vars(array(
			"SHOW_COMMENT" => "",
			"spacer" => $args["spacer"],
			"level" => $args["level"],
			"from" => $args["name"],
			"email" => $args["email"],
			"icons" => $args["icons"],
			"parent" => $args["parent"],
			"subj" => ($args["subj"]) ? $args["subj"] : "(nimetu)",
			"id" => $args["id"],
			"new" => $new,
			"time" => $this->time2date($args["time"],2),
			"color" => $color,
			"comment" => nl2br(create_links($args["comment"])),
			"del_msg" => $this->mk_my_orb("del_msg", array("board" => $args["board_id"], "comment" => $args["id"],"section" => $this->section)),
			"reply_link" => $this->mk_my_orb("reply",array("parent" => $args["id"],"section" => $this->section,"_alias" => $alias,"section" => $this->section)),
			"open_link" => $this->mk_my_orb("topics_detail",array("id" => $this->forum_id,"cid" => $args["id"],"from" => $this->from,"section" => $this->section)),
			"open_link2" => $this->mk_my_orb("show_threaded",array("board" => $args["board_id"],"cid" => $args["id"],"from" => $this->from,"section" => $this->section)),
			"topic_link" => $this->mk_my_orb("show",array("board" => $args["board_id"],"section" => $this->section,"_alias" => $alias)),
		));

		if ($this->mark_comments)
		{
			$this->aw_mb_read[$args["id"]] = 1;
		};

		if ($this->cfg["track_users"])
		{
			$this->save_handle();
			// remember that this comment has already been shown
			$q = "REPLACE INTO forum_track (uid,thread_id,comm_id)
				VALUES ('$uid','$board_id','$id')";
			$this->db_query($q);
			$this->restore_handle();
		};

		if ($this->is_template("SHOW_COMMENT") && ($this->cid == $args["id"]))
		{
			$this->vars(array("SHOW_COMMENT" => $this->parse("SHOW_COMMENT")));
		};

		if ( ($this->prog_acl("view", PRG_MENUEDIT)) || ($this->members[aw_global_get("uid")]))
		{
			$del = $this->parse("KUSTUTA");
			$repl = $this->parse("REPLY");
		}
		$this->vars(array(
			"KUSTUTA" => $del,
			"REPLY" => $repl,
		));

		$retval = $this->parse("message");
		return $retval;
	}

	////
	// !Displays the form to add comments
	function add_comment($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		global $HTTP_COOKIE_VARS;
		$aw_mb_name = $HTTP_COOKIE_VARS["aw_mb_name"];
		$aw_mb_mail = $HTTP_COOKIE_VARS["aw_mb_mail"];
		if ($subj)
		{
			$reply = $this->parse("reply");
		}
		else
		{
			$reply = "";
		};
		$this->section = $section;
		$cnt = $this->db_fetch_field("SELECT count(*) AS cnt 
			FROM comments WHERE board_id = '$board'","cnt");
		$this->mk_links(array("board" => $board));
		
		if ($subj && not(preg_match("/Re:/i",$args["subj"])))
		{
			$subj = "Re: " . $subj;
		};

		$this->vars(array(
			"cnt" => $cnt,
			"num_comments" => $cnt,
			"name" => $aw_mb_name,
			"mail" => $aw_mb_mail,
			"comment" => $args["comment"],
			"subj" => $subj,
			"reply" => $reply,
			"reforb" => $this->mk_reforb("submit_comment",array("board" => $board,"parent" => $parent,"section" => $section,"act" => $act)),
		));
		return $this->parse();
	}

	function get_num_comments($board)
	{
		$cnt = $this->db_fetch_field("SELECT count(*) AS cnt 
		FROM comments WHERE board_id = '$board'","cnt");
		return $cnt;
	}

	////
	// !Submits comment to a topic
	function submit_comment($args = array())
	{
		$this->quote($args);
		extract($args);
		if (!$name)
		{
			$name = $from;
		};

		$forum_obj = $this->get_object($board);
		$mx = $this->get_object_metadata(array(
			"oid" => $forum_obj["parent"],
			"key" => "notifylist",
		));

		if ($parent)
		{
			$q = "SELECT * FROM comments WHERE id = '$parent'";
			$this->db_query($q);
			$row = $this->db_next();
			$board = $row["board_id"];
		};

		if ( (strlen($name) > 2) && (strlen($comment) > 1) )
		{
			if (is_array($mx))
			{
				foreach($mx as $key => $val)
				{
					mail($val["name"] . "<" . $val["address"] . ">",
						"Uus sissekanne teemal: $forum_obj[name]",
						"Nimi: $name\nE-post: $email\nTeema: $subj\nKommentaar:\n$comment\n\nVastamiseks kliki siia: http://sylvester.struktuur.ee/?class=forum&action=show_threaded&board=$board",
						"From: $name <$email>");
				}
			};
			$name = strip_tags($name);
			$email = strip_tags($email);
			$comment = strip_tags($comment);
			$subj = strip_tags($subj);
			$parent = (int)$parent;
			$site_id = $this->cfg["site_id"];
			$ip = aw_global_get("REMOTE_ADDR");
			$t = time();
			if ($remember_me)
			{
				setcookie("aw_mb_name",$name,time()+24*3600*1000);
				setcookie("aw_mb_mail",$email,time()+24*3600*1000);
			}
			// yeah, legacy code sucks, but we support it anyway
			if (not($name))
			{
				$name = $from;
			};
			if ($response)
			{
				$q = "INSERT INTO comments (parent, board_id, name, email, comment, subj,
						time, site_id, ip, response)
					VALUES ('$parent','$board','$name','$email','$comment','$subj',

						$t,'$site_id', '$ip', '$response')";
			}
			else
			{
				$q = "INSERT INTO comments (parent, board_id, name, email, comment, subj,
						time, site_id, ip)
				VALUES ('$parent','$board','$name','$email','$comment','$subj',
						$t,'$site_id', '$ip')";
			};
			$this->upd_object(array(
				"oid" => $board,
			));

			$this->db_query($q);

		}
		if (not($act))
		{
			$act = "show_threaded";
		};

		if ($section)
		{
			$retval =$this->mk_my_orb($act,array("board" => $board,"section" => $section,"_alias" => "forum"));
		}
		else
		{
			$retval =$this->mk_my_orb($act,array("board" => $board,"section" => $section));
		};
		return $retval;

	}

	////
	// !Shows a list of topics for a forum
	// id(int) - forum id
	function topics($args = array())
	{
		extract($args);
		$o = $this->get_obj_meta($id);
		$this->section = $section;

		$this->forum_id = $id;
		$this->from = $from;
		$this->archive = $archive;
		if ($archive)
		{
			$act_tab = "archive";
		}
		else
		{
			$act_tab = "flat";
		};

		$tabs = $this->tabs(array("flat","details","newtopic","mark_all_read","archive","search"),$act_tab);

		$this->topicsonpage = ($o["meta"]["topicsonpage"]) ? $o["meta"]["topicsonpage"] : 5;

		$this->mk_path($o["parent"], "Foorum");
		$this->read_template("list_topics.tpl");

		global $HTTP_COOKIE_VARS;
		$aw_mb_last = unserialize(stripslashes($HTTP_COOKIE_VARS["aw_mb_last"]));
		$this->last_read = $aw_mb_last;
		$this->now = time();

		$this->use_orb_for_links = 1;
		$content = $this->_draw_all_topics(array(
			"id" => $id,
		));

		// õkk, this is overkill
		// $this->db_query("SELECT COUNT(id) as cnt ,board_id, MAX(time) as mtime FROM comments GROUP BY board_id");
		// pealkirjad, vastuseid, postitas, alustatud, hiliseim vastus

		$this->mk_links(array(
			"id" => $id,
			"board" => $board,
			"from" => $from,
		));

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_topics",array("id" => $id,"section" => $this->section)),
			"TO_ARCHIVE" => ($archive == 1 ? $this->parse("FROM_ARCHIVE") : $this->parse("TO_ARCHIVE")),
			"FROM_ARCHIVE" => ""
		));


		$this->vars(array(
			"actions" => ($this->prog_acl("view",PRG_MENUEDIT) ? $this->parse("actions") : ""),
			"TABS" => $tabs,
			"TOPIC" => $content,
			"TOPIC_EVEN" => $content,
		));
		return $this->parse();
	}

	////
	// !Displays a detailed list of topics
	function topics_detail($args = array())
	{
		extract($args);
		$o = $this->get_obj_meta($id);

		$this->forum_id = $id;
		$this->from = $from;
		$this->board = $id;
		$this->section = $section;
		$this->topicsonpage = ($o["meta"]["topicsonpage"]) ? $o["meta"]["topicsonpage"] : 5;
		$tabs = $this->tabs(array("flat","details","newtopic","mark_all_read","archive","search"),"details");
		$this->read_template("list_topics_detail.tpl");
		
		global $HTTP_COOKIE_VARS;
		$aw_mb_last = unserialize(stripslashes($HTTP_COOKIE_VARS["aw_mb_last"]));
		$this->last_read = $aw_mb_last;

		$this->cid = $args["cid"];
		$content = $this->_draw_all_topics(array(
			"id" => $id,
			"details" => 1,
		));
		
		$this->mk_links(array(
			"id" => $id,
			"board" => $id,
			"from" => $from,
		));
		
		$this->vars(array(
			"TOPIC" => $content,
			"TOPIC_EVEN" => $content,
			"TABS" => $tabs,
		));

		return $this->parse();
	}

	function submit_topics($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			$stat = 1;
			if ($act == "delete")
			{
				$stat = 0;
			}
			if ($act == "activate")
			{
				$stat = 2;
			}
			$to_delete = join(",",$check);
			$q = "UPDATE objects SET status = $stat WHERE oid IN ($to_delete)";
			$this->db_query($q);
		};
		return $this->mk_my_orb("topics",array("id" => $id,"section" => $section,"_alias" => "forum","section" => $section));
	}


	////
	// !Shows the search form
	function search($args = array())
	{
		extract($args);
		if (not($id) && not($board))
		{
		  // neither is defined .. what the hell do you want anyway?
			return;
		};
		$this->section = $section;
		$this->forum_id = $id;
		$tabs = $this->tabs(array("flat","details","newtopic","mark_all_read","archive","search"),"search");
		$this->read_template("search.tpl");
		$o = $this->get_object($board);
		$board_obj = $this->get_obj_meta($board);
		$forum_obj = $this->get_obj_meta($board_obj["parent"]);
		$flink = $this->mk_my_orb("configure",array("id" => $board));
		$this->mk_path($o["parent"], "<a href='$flink'>$o[name]</a> / Otsi");
		$this->mk_links(array(
			"id" => $id,
			"board" => $board,
		));
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_search",array("id" => $id, "board" => $board,"no_reforb" => 1,"section" => $this->section)),
			"TABS" => $tabs,
		));
		return $this->parse();
	}

	////
	// !Performs the actual search
	function submit_search($args = array())
	{
		extract($args);
		$this->section = $section;
		$this->forum_id = $id;
		$tabs = $this->tabs(array("flat","details","newtopic","mark_all_read","archive","search"),"search");
		$this->read_template("search_results.tpl");
		if (not($board))
		{
			$board = $id;
		};
		$board_obj = $this->get_obj_meta($board);
		$forum_obj = $this->get_obj_meta($board_obj["parent"]);
		$c = "";

		// koigepealt tuleb koostada topicute nimekiri mingi foorumi all
		$blist[] = 0;
		if ($board_obj["class_id"] == CL_MSGBOARD_TOPIC)
		{
			$blist[] = $board_obj["oid"];
			$this->mk_links(array(
				"board" => $board,
				"parent" => $board_obj["parent"],
			));
			$this->forum_id = $forum_obj["oid"];
		}
		else
		{	
			$status = ($in_archive) ? "" : " AND status = 2";
			$q = "SELECT * FROM objects WHERE parent = '$board' $status AND class_id = " . CL_MSGBOARD_TOPIC;
			$this->db_query($q);
			$this->forum_id = $board_obj["parent"];
			$this->mk_links(array(
				"id" => $board,
			));
			while ($row = $this->db_next())
			{
				$blist[] = $row["oid"];
			};

			// also search topics
			if ($this->is_template("TOPIC_EVEN") && $this->is_template("TOPIC_ODD"))
			{
				$matlist = array();
				$matches = array();
				$q = "SELECT * FROM objects WHERE parent = '$board' $status AND class_id = ".CL_MSGBOARD_TOPIC." AND
							createdby LIKE '%$from%' AND name LIKE '%$email%' AND comment LIKE '%$comment%'";
				$this->db_query($q);
				while ($row = $this->db_next())
				{
					$matlist[] = $row["oid"];
					$matches[] = $row;
				}
				$this->comments = $this->_get_comment_counts($matlist);

				foreach($matches as $row)
				{
					$row["meta"] = aw_unserialize($row["metadata"]);
					$c .= $this->_draw_topic(array_merge($row,array("section" => $this->section)));
				}
			}
		};
		$bjlist = join(",",$blist);
		// valjad: from,email,subj,comment
		// baasis: name,email,subj,comment
		$q = "SELECT * FROM comments WHERE
			name LIKE '%$from%' AND
			email LIKE '%$email%' AND
			subj LIKE '%$subj%' AND	
			comment LIKE '%$comment%' AND
			board_id IN ($bjlist)
			ORDER BY time DESC";
		$this->db_query($q);
		$cnt = 0;
		while($row = $this->db_next())
		{
			$cnt++;
			#$this->vars(array(
			#	"from" => $row["name"],
			#	"subj" => $row["subj"],
			#	"email" => $row["email"],
			#	"time" => $this->time2date($row["time"]),
			#	"comment" => $row["comment"],
			#));
			#$c .= $this->parse("message");
			$c .= $this->display_comment($row);
		};
		$this->vars(array(
			"count" => $cnt,
			"message" => $c,
			"TABS" => $tabs,
			"TOPIC_EVEN" => "",
			"TOPIC_ODD" => ""
		));
		return $this->parse();

	}

	////
	// !Marks all the boards in the current forum as read
	function mark_all_read($args = array())
	{
		extract($args);
		global $HTTP_COOKIE_VARS;
		$aw_mb_last = unserialize(stripslashes($HTTP_COOKIE_VARS["aw_mb_last"]));
		$aw_mb_last[$id] = time();
		setcookie("aw_mb_last",serialize($aw_mb_last),time()+24*3600*1000);
		return $this->mk_my_orb("topics",array("id" => $id,"section" => $section,"_alias" => "forum"));
	}

	////
	// !Handles the forum alias inside the document
	function parse_alias($args = array())
	{
		extract($args);
		// we are inside the document, so we switch to embedded mode
		$this->embedded = true;
		$l = $alias;
    $target = $l["target"];
	  $tobj = $this->get_object($target);
    $parent = $tobj["last"];
		$id = $target;
		$section = $oid;

		$vars = $GLOBALS["HTTP_GET_VARS"];
		if (is_array($vars))
		{
			if ($vars["alias"])
			{
				classload("orb");
				$orb = new orb(array(
					"class" => $vars["alias"],
					"action"=> $vars["action"],
					"vars" => array_merge($vars,array("section" => aw_global_get("section"))),
				));
				$content = $orb->get_data();
				if (substr($content,0,5) == "http:")
				{
					header("Location: $content");
					exit;
				};
			}
			else
			{
				$this->section = $oid;
				// kui ühtegi argumenti pole antud, siis näitame foorumit topicuvaates
				$content = $this->topics(array("id" => $id,"section" => $oid));
			};
		};
		return $content;
	}

	////
	// !Calculates amounts of comments on all given message boards
	function _get_comment_counts($boards = array())
	{
		if (not(is_array($boards)))
		{
			return false;
		};
		
		// check the length too, otherwise we get a nasty mysql error
		if (sizeof($boards) == 0)
		{
			return false;
		};
		$comments = array();
		$q = sprintf("SELECT board_id,count(*) AS cnt FROM comments
				WHERE board_id IN (%s) 
				GROUP BY board_id",join(",",$boards));
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$comments[$row["board_id"]] = $row["cnt"];
		};
		return $comments;
	}
			
	////
	// !Draws a list of all topics under a forum
	function _draw_all_topics($args = array())
	{
		extract($args);
		$act = ($this->archive) ? 1 : 2;
		$obj = $this->get_objects_below(array(
			"parent" => $id,
			"class" => CL_MSGBOARD_TOPIC,
			"orderby" => "created desc",
			"status" => $this->archive ? 1 : 2,
		));
		$content = "";
		if (is_array($obj))
		{
			$blist = array();
			foreach($obj as $key => $item)
			{
				$blist[] = $item["oid"];
			}

			$this->comments = $this->_get_comment_counts($blist);
			list($from,$to) = $this->_draw_pager(array(
						"total" => sizeof($obj),
						"onpage" => $this->topicsonpage,
						"active" => $this->from,
						"details" => $args["details"],
			));
			$cnt = 0;
			foreach($obj as $key => $item)
			{
				$item["meta"] = aw_unserialize($item["metadata"]);
				if ( ($cnt >= $from) && ($cnt <= $to) )
				{
					if ($args["details"])
					{
						// this is a tad ineffective, because we query 
						// for each topic, instead of getting the comments
						// as a batch
						$this->_query_comments(array("board" => $item["oid"]));
						// put the comments into tree
						while($row = $this->db_next())
						{
							$this->_comments[$row["parent"]][] = $row;
						};
						$this->rec_comments(0);
						$this->vars(array("message" => $this->content));
						$this->content = "";
						$this->_comments = array();
					}
					$content .= $this->_draw_topic(array_merge($item,array("section" => $oid)));
				}
				$cnt++;
			}
				
		};
		return $content;
	}

	////
	// !Draws a single topic
	// requires a loaded template with either TOPIC_EVEN AND TOPIC_ODD subtemplates
	// or just a TOPIC template
	function _draw_topic($args = array())
	{
		$this->topic_count++;

		$alias = ($this->embedded) ? "forum" : "";
		
		$this->use_orb_for_links = 1;
		#if ($this->use_orb_for_links)
		#{
			$topic_link = $this->mk_my_orb("show",array("board" => $args["oid"],"section" => $this->section,"_alias" => $alias));
			$threaded_topic_link = $this->mk_my_orb("show_threaded",array("board" => $args["oid"],"section" => $this->section,"_alias" => $alias));
			$threaded_topic_link2 = $this->mk_my_orb("show_threaded",array("board" => $args["oid"],"section" => $this->section,"_alias" => $alias,"no_comments" => 1));
		#}
		#else
		#{
		#	$topic_link = $this->mk_url(array("board" => $args["oid"],"section" => $args["section"]));
		#};

		// mille vastu võrrelda=
		$check_against = ($args["modified"] > $args["created"]) ? $args["modified"] : $args["created"];
		global $DBUG;
		if ($DBUG)
		{
			print "<pre>";
			print "ca = " . $this->last_read[$args["oid"]] . "<br>";
			print "</pre>";
		}
		$mark = ($check_against > $this->last_read[$args["oid"]]) ? $this->parse("NEW_MSGS") : "";

		if ($this->cfg["track_users"])
		{
			$this->save_handle();
			$uid = aw_global_get("uid");
			$q = "SELECT count(*) AS cnt FROM forum_track WHERE thread_id = '$args[oid]' AND uid = '$uid'";
			$this->db_query($q);
			$row = $this->db_next();

			$read_msgs = $row["cnt"];
			
			
			$total_msgs = (int)$this->comments[$args["oid"]];

			global $DBUG;
			if ($DBUG)
			{
				print "read = $read_msgs, total = $total_msgs<br>";
			}

			if ($read_msgs < $total_msgs)
			{
				$mark = $this->parse("NEW_MSGS");
			}
			else
			{
				$mark = "";
			}
		};

		$this->vars(array(
			"del_topic" => $this->mk_my_orb("delete_topic", array("board" => $args["oid"],"forum_id" => $args["parent"])),
			"change_topic" => $this->mk_my_orb("change_topic", array("board" => $args["oid"],"forum_id" => $args["parent"])),
			"id" => $args["oid"],
		));

		$meta = $this->get_object_metadata(array(
			"metadata" => $args["metadata"]
		));
		if ($meta["voters"] == 0)
		{
			$rate = 0;
		}
		else
		{
			$rate = $meta["votesum"] / $meta["voters"];
		};

		$this->vars(array(
			"topic" => ($args["name"]) ? $args["name"] : "(nimetu)",
			"created" => $this->time2date($args["created"],2),
			"created_date" => $this->time2date($args["created"],8),
			"from" => $args["createdby"],
			"email" => $args["meta"]["author_email"],
			"text" => $args["comment"],
			"createdby" => ($args["last"]) ? $args["last"] : $args["createdby"],
			"last" => $this->time2date($args["modified"],11),
			"lastmessage" => $this->time2date($args["modified"],11),
			"comments" => (int)$this->comments[$args["oid"]],
			"cnt" => (int)$this->comments[$args["oid"]],
			"topic_link" => $topic_link,
			"threaded_topic_link" => $threaded_topic_link,
			"threaded_topic_link2" => $threaded_topic_link2,
			"NEW_MSGS" => $mark,
			"rate" => (floor(($rate*10)+0.5)/10),
			"DELETE" => ($this->prog_acl("view",PRG_MENUEDIT) ? $this->parse("DELETE") : ""),
			"DEL_TOPIC" => ($this->prog_acl("view",PRG_MENUEDIT) ? $this->parse("DELETE") : "")
		));
		$even = ($this->topic_count % 2);
		if ($this->is_template("TOPIC_EVEN"))
		{
			// if TOPIC_EVEN template exitsts then we assume that TOPIC_ODD also exists
			// actually we should check for it 
			$tpl_to_parse = ($even) ? "TOPIC_EVEN" : "TOPIC_ODD";
		}
		else
		{
			$tpl_to_parse = "TOPIC";
		};
		$retval = $this->parse($tpl_to_parse);
		$this->vars(array(
			"DELETE" => "",
			"NEW_MSGS" => "",
		));
		return $retval;
	}
	
	////
	// !Performs a query to get comments matching a certain criteria
	function _query_comments($args = array())
	{
		extract($args);
		if ($args["board"])
		{
			$q = "SELECT * FROM comments WHERE board_id = '$board' ORDER BY time";
			$this->db_query($q);
		}
	}

	////
	// !Draws a page
	// requires a loaded template and PAGES, PAGE and SEL_PAGE subtemplates to be defined
	// total(int) - how many items do we have?
	// onpage(int) - how many items on a page?
	// active(int) - what item are we showing at the moment? 
	function _draw_pager($args = array())
	{
		extract($args);
		$content = "";
		if (!$onpage)
		{
			$onpage = 5;
		};
		$num_pages = (int)(($total / $onpage) + 1);

		// no pager, if we have less entries than will fit on one page
		if ($total < ($onpage - 1))
		{
			return array(0,$total);
		};

		for ($i = 1; $i <= $num_pages; $i++)
		{
			$page_start = ($i - 1)  * $onpage;
			$page_end = $page_start + $onpage - 1;
			if ( ($active >= $page_start) and ($active < $page_end) )
			{
				$act_start = $page_start;
				$act_end = $page_end;
				$tpl = "SEL_PAGE";
			}
			else
			{
				$tpl = "PAGE";
			};
			$pg_action = ($args["details"]) ? "topics_detail" : "topics";
			$this->vars(array(
				"pagelink" => $this->mk_my_orb($pg_action,array("id" => $this->forum_id,"from" => $page_start,"section" => $this->section,  "archive" => $this->archive)),
				"linktext" => $i,
			));
			$content .= $this->parse($tpl);
		};
		$this->vars(array(
			"PAGE" => $content,
		));
		$this->vars(array(
			"PAGES" => $this->parse("PAGES"),
		));

		return(array($act_start,$act_end));
	}

	///
	// !deletes a topic from the board. What about the comments though?
	function del_topic($arr)
	{
		extract($arr);
		$this->delete_object($board);
		$pobj = $this->get_object($forum_id);
		if ($pobj["class_id"] == CL_DOCUMENT)
		{
			$retval = $this->mk_link(array("section" => $pobj["oid"]));
		}
		else
		{
			$retval = $this->mk_my_orb("topics", array("id" => $forum_id));
		};
		return $retval;

	}

	////
	// !Allows to change the topic
	function change_topic($arr)
	{
		extract($arr);
		$this->read_template("add_topic.tpl");

		$top = $this->get_obj_meta($board);

		$this->vars(array(
			"name" => $top["name"],
			"comment" => $top["comment"],
			"from" => $top["last"],
			"email" => $top["meta"]["author_email"],
			"reforb" => $this->mk_reforb("save_topic", array("board" => $board))
		));
		return $this->parse();
	}


	////
	// !Submits the changed topic
	function save_topic($arr)
	{
		$this->quote($arr);
		extract($arr);
		$this->upd_object(array(
			"oid" => $board,
			"name" => $topic,
			"last" => $from,
			"comment" => $comment
		));
		$this->set_object_metadata(array(
			"oid" => $board,
			"key" => "author_email",
			"value" => $email,
		));

		$pobj = $this->get_object($forum_id);
		if ($pobj["class_id"] == CL_DOCUMENT)
		{
			$retval = $this->mk_link(array("section" => $pobj["oid"]));
		}
		else
		{
			$retval = $this->mk_my_orb("show", array("board" => $board));
		};
		return $retval;
	}

	////
	// !Deletes a message from a board
	function del_msg($arr)
	{
		extract($arr);

		$this->db_query("DELETE FROM comments WHERE id = $comment");
		$_tmp = $this->get_object($board);
		$pobj = $this->get_object($_tmp["parent"]);
		if ($section)
		{
			$retval = $this->mk_my_orb("show",array("_alias" => "forum","board" => $board,"section" => $section));
		}
		else
		{
			$retval = $this->mk_my_orb("show", array("board" => $board));
		};

		return $retval;
	}

	function get_properties($args = array())
	{
		$fields = array();
		if ($this->id)
		{
			$url = $this->mk_my_orb("topics",array("id" => $this->id));
			$fields["content_link"] = array(
				"type" => "text",
				"caption" => "URL",
				"value" => "<a href='$url' target='_blank'>$url</a>",
			);
		};

		$fields["comments"] = array(
                        "type" => "checkbox",
                        "caption" => "Kommenteeritav",
			"checked" => $args["comments"],
			"value" => 1,
                        "store" => "meta",
                );
		
		$fields["rated"] = array(
                        "type" => "checkbox",
                        "caption" => "Hinnatav",
                        "checked" => $args["rated"],
			"value" => 1,
                        "store" => "meta",
                );

		$fields["template"] = array(
                        "type" => "select",
                        "options" => aw_ini_get("menuedit.template_sets"),
                        "caption" => "Template",
                        "selected" => $args["template"],
                        "store" => "meta",
                );

		$fields["onpage"] = array(
                        "type" => "select",
                        "options" => array(5 => 5,10 => 10,15 => 15,20 => 20,25 => 25,30 => 30),
                        "caption" => "Kommentaare lehel",
                        "selected" => $args["onpage"],
                        "store" => "meta",
                );
		
		$fields["topicsonpage"] = array(
                        "type" => "select",
                        "options" => array(5 => 5,10 => 10,15 => 15,20 => 20,25 => 25,30 => 30),
                        "caption" => "Teemasid lehel",
                        "selected" => $args["topicsonpage"],
                        "store" => "meta",
                );

		return $fields;
	}
}
?>
