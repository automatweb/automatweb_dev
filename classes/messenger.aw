<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/messenger.aw,v 2.111 2002/12/02 18:54:09 kristo Exp $
// messenger.aw - teadete saatmine
// klassid - CL_MESSAGE. Teate objekt
lc_load("definition");

// sisemine, aw sees saadetud teade
define('MSG_INTERNAL',1);
// väline, ntx pop3 serverist võetud teade
define('MSG_EXTERNAL',2);

define('MSG_MASKOUT',63999);// !(MSG_LIST+MSG_HTML)
define('MSG_MASKNOTHTML',64511);// !MSG_HTML
// meililisti saatmiseks mõeldud teade
define('MSG_LIST',512);
// näitab et tegemist on html teatega
define('MSG_HTML',1024);

// teadete staatused
define('MSG_STATUS_UNREAD',0);
define('MSG_STATUS_READ',1);

// siit algab messengeri põhiklass

classload("menuedit_light");
class messenger extends menuedit_light
{
	////
	// !Konstruktor
	var $drivername = "sql";
	

	function messenger($args = array())
	{
		$this->init("messenger");
		$this->lc_load("messenger","lc_messenger");
		$driverclass = "msg_" . $this->drivername;

		// $this->driveri kaudu pöördutakse andmebaasidraiveri poole
		$this->driver = get_instance($driverclass);
		
		// juhuks, kui kusagil on vaja kasutada messengeri alamhulka, siis konstruktorile
		// fast argumendi etteandmisega saab vältida rohkem aega nõudvaid operatsioone
		// kuigi tegelikult peaks selleks messengeri klassi hoopis kaheks lööma
		// messenger_user ja messenger. Hiljem jõuab. Ehk.
		$this->user = $this->get_user(array(
			"uid" => aw_global_get("uid"),
		));
		if (!$args["fast"])
		{
			$this->xml = get_instance("xml");

			// do some crappy-ass detection if we are running as an added object
			global $messenger_id;
			if ($messenger_id)
			{
				$this->msg_obj = $this->get_object($messenger_id);
				$this->msgconf = $this->msg_obj["meta"]["msgconf"];
				$this->conf = $this->msg_obj["meta"]["conf"];
				$this->object_id = $messenger_id;
				$this->msg_inbox = $this->msg_obj["meta"]["msg_inbox"] ? $this->msg_obj["meta"]["msg_inbox"] : $this->msg_obj["parent"];
				$this->rules = $this->msg_obj["meta"]["rules"];
			}
			else
			{
				$users = get_instance("users");
				$this->msgconf = $users->get_user_config(array(
					"uid" => aw_global_get("uid"),
					"key" => "messenger",
				));
				// igal klassi loomisel toome ka 
				$this->conf = $this->_get_msg_conf(array("conf" => $this->user["messenger"]));
				$this->object_id = false;
				$this->msg_inbox = ($this->user["msg_inbox"]) ? $this->user["msg_inbox"] : $this->user["home_folder"];
				$this->rules = $users->get_user_config(array(
					"uid" => aw_global_get("uid"),
					"key" => "rules",
				));
			}
			if ($this->msgconf["msg_window"])
			{
				aw_global_set("no_menus",1);
			};
		};

		$this->pages = array(
			"0" => "Inbox",
			"1" => "Compose",
			"2" => "Preferences",
		);

		$this->priorities = array(
			"0" => "default",
			"5" => "5 - Low",
			"3" => "3 - Normal",
			"1" => "1 - High",
		);

		$this->default_format = array(
			"0" => "Autodetect (brauseri järgi)",
			"1" => "plain text",
			"2" => "html (richedit)",
		);
	}
	
	////
	// !Initsialiseerib messengeri folderid kasutaja jaoks
	function init_messenger($args = array())
	{
		if ($this->object_id)
		{
			return $this->init_obj_messenger($args);
		}
		// need funktsioonid peaks viima kasutaja loomise juurde
		// Inbox
		$msg_inbox = $this->_create_folder(array(
			"name" => "Inbox",
			"parent" => $this->user["home_folder"],
		));

		// Outbox
		$msg_outbox = $this->_create_folder(array(
			"name" => "Outbox",
			"parent" => $this->user["home_folder"],
		));

		// Drafts
		$msg_draft = $this->_create_folder(array(
			"name" => "Drafts",
			"parent" => $this->user["home_folder"],
		));

		// Trash
		$msg_trash = $this->_create_folder(array(
			"name" => "Trash",
			"parent" => $this->user["home_folder"],
		));
		$conf = $this->conf;
		$conf["msg_outbox"] = $msg_outbox;
		$conf["msg_draft"] = $msg_draft;
		$conf["msg_trash"] = $msg_trash;

		// Moodustame konfi pohjal uue xml-i
		// users tabeli messenger vali on tegelikult Deprecated.
		$newconf = aw_serialize($conf,SERIALIZE_XML);
		$this->quote($newconf);
		$q = "UPDATE users SET msg_inbox = '$msg_inbox',messenger = '$newconf' WHERE uid = '" . aw_global_get("uid"). "'";
		$this->db_query($q);
	}

	function init_obj_messenger($args)
	{
		// Inbox
		$msg_inbox = $this->_create_folder(array(
			"name" => "Inbox",
			"parent" => $this->msg_obj["parent"],
		));

		// Outbox
		$msg_outbox = $this->_create_folder(array(
			"name" => "Outbox",
			"parent" => $this->msg_obj["parent"],
		));

		// Drafts
		$msg_draft = $this->_create_folder(array(
			"name" => "Drafts",
			"parent" => $this->msg_obj["parent"],
		));

		// Trash
		$msg_trash = $this->_create_folder(array(
			"name" => "Trash",
			"parent" => $this->msg_obj["parent"],
		));
		$conf = $this->conf;
		$conf["msg_outbox"] = $msg_outbox;
		$conf["msg_draft"] = $msg_draft;
		$conf["msg_trash"] = $msg_trash;

		$this->upd_object(array(
			'oid' => $this->object_id,
			'metadata' => array(
				'conf' => $conf,
				'msgconf' => $this->msgconf,
				'msg_inbox' => $msg_inbox,
				'rules' => $this->rules
			)
		));
	}

	////
	// !Votab salvestatud attachi vastu
	function store_attach($args = array())
	{
		extract($args);
		print "<pre>";
		print_r($args);
		print "</pre>";
		$awf = get_instance("file");
		$awf->cp(array("id" => $attach,"parent" => $folder));
		print "<script language='Javascript'> window.close(); </script>";
		exit;
	}

	////
	// !Joonistab menüü
	// argumendid:
	// activelist(array), levelite kaupa info selle kohta, millised elemendid aktiivsed on
	// vars(array) - muutujad mida xml-i sisse pannakse
	function gen_msg_menu($args = array())
	{
		extract($args);

		if ($this->msgconf["msg_hide_menubar"])
		{
			return false;
		};

		$basedir = $this->cfg["basedir"];
		$this->read_template("logo.tpl");
		$this->vars(array("title" => $title));
		$logo = $this->parse();
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		// yeah. so you can customize the menu for different sites but others also work.
		if (file_exists($this->template_dir . "/menucode.xml"))
		{
			$xml = $this->template_dir . "/menucode.xml";
		}
		else
		{
			$xml = $basedir . "/xml/messenger/menucode.xml";
		}

		if ($this->object_id)
		{
			$xm->vars($vars);

			// add &messenger_id to the end of links if it is set
			$xml_c = $this->get_file(array(
				"file" => $xml
			));
			$xml_c = preg_replace('/<link>(.*)<\/link>/isU','<link>\\1&amp;messenger_id='.$this->object_id.'</link>', $xml_c);
			$xm->load_from_memory(array(
				"template" => $this->get_file(array("file" => $this->template_dir . "/menus.tpl")),
				"xmldef" => $xml_c
			));
			$retval = $xm->create(array(
				"activelist" => $activelist
			));
		}
		else
		{
			$retval = $xm->build_menu(array(
				"vars"	=> $vars,
				"xml"	=> $xml,
				"tpl"	=> $this->template_dir . "/menus.tpl",
				"activelist" => $activelist,
			));
		}
		return $logo . $retval;
	}
	
	////
	// !Show headers
	function show_headers($args = array())
	{
		extract($args);
		$q = "SELECT headers FROM messages WHERE id = '$id'";
		$this->db_query($q);
		$this->read_template("headers.tpl");
		$row = $this->db_next();
		$this->vars(array(
			"headers" => htmlspecialchars($row["headers"]),
		));
		print $this->parse();
		exit;
	}

	////
	// !Pritsib attachi kasutajale välja
	// argumendid:
	// msgid(int) - kirja id
	// attnum(int) - attachi number selle kirja juures
	function get_attach($args = array())
	{
		extract($args);
		$q = "SELECT * FROM objects WHERE parent = '$msgid'";
		$this->db_query($q);
		$c = 0;
		while($row = $this->db_next())
		{
			$c++;
			if ($c == $attnum)
			{
				$awf = get_instance("file");
				$fdata = $awf->get_file_by_id($row["oid"]);
				header("Content-Type: $fdata[type]");
				header("Content-Disposition: filename=$row[name]");
				print $fdata["content"];
				exit;
			};
		};
	}
	

	////
	// !Attachib objekti mingi kirja külge
	// msg_id - teade mille külge attachide
	// data - serialiseeritud objekt
	function attach_serialized_object($args = array())
	{
		$this->quote($args);
		extract($args);
		$q = "INSERT INTO msg_objects (message_id,content)
			VALUES('$msg_id','$data')";
		$this->db_query($q);
	}
		

	////
	// !Counts unread messages in a folder
	function count_unread($args = array())
	{
		return $this->driver->count_unread(array("folder" => $this->msg_inbox));
	}

	////
	// !Koostab folderite nimekirja
	function _folder_list($args = array())
	{
		if ($this->object_id)
		{
			$root = $this->msg_obj["parent"];
		}
		else
		{
			$root = $this->user["home_folder"];
		}
		$mnl = get_instance("menuedit_light");
		$folder_list = $mnl->gen_rec_list(array(
			"start_from" => $root,
			"add_start_from" => true,
		));
		$folder_list[$this->msg_inbox] .= " (In)";
		$folder_list[$this->conf["msg_outbox"]] .= " (Out)";
		$folder_list[$this->conf["msg_draft"]] .= " (Drft)";
		$folder_list[$this->conf["msg_trash"]] .= " (Trash)";
		return $folder_list;
	}

	//// 
	// !Näitab folderite nimekirja
	function show_folders($args = array())
	{
		// Kui siia tullakse esimest korda, ja messenger on veel konfigureerimata,
		// siis just siitkaudu saab seda teha
		// hm. tegelikult peaks seda kontrolli tegema ka mujal, kus
		// messengeri poole pöördutakse
		$baseurl = $this->cfg["baseurl"];
		if (!isset($this->conf["msg_outbox"]))
		{
			$retval = MSG_INIT;
			$this->init_messenger();
			$retval .= '<a href=\''.$this->mk_my_orb("folder").'\'>'.MSG_INIT2.'</a>';
			return $retval;
		};

		$menu = $this->gen_msg_menu(array(
			"title" => MSG_TITLE_FOLDERS,
			"activelist" => array("configure","folders2","flist"),
		));
		$this->read_template("folders.tpl");

		$flist = new aw_array($this->_folder_list());
		$idlist = new aw_array(array_keys($flist->get()));

		$q = "SELECT parent,count(*) AS cnt FROM objects WHERE class_id = '".CL_MESSAGE."' AND parent IN ( ".$idlist->to_sql().")  AND status = 2 GROUP BY parent"; 
		$this->db_query($q);
		$totals = array();
		while($row = $this->db_next())
		{
			$totals[$row["parent"]] = $row["cnt"];
		};

		$q = "SELECT parent,count(*) AS cnt FROM objects LEFT join messages ON (objects.oid = messages.id)
			WHERE class_id = '".CL_MESSAGE."' AND messages.status = 0 AND parent IN ( ".$idlist->to_sql().") GROUP BY parent";
		$this->db_query($q);
		$unread = array();
		while($row = $this->db_next())
		{
			$unread[$row["parent"]] = $row["cnt"];
		};

		foreach($flist->get() as $key => $val)
		{
			$this->vars(array(
				"id" => $key,
				"name" => $val,
				"total" => ($totals[$key]) ? $totals[$key] : 0,
				"unread" => ($unread[$key]) ? $unread[$key] : 0,
				"checked" => checked($this->msgconf["msg_defaultfolder"] == $key),
				"go" => $this->mk_my_orb("folder", array("id" => $key))
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
			"menu" => $menu,
			"reforb" => $this->mk_reforb("submit_configure",array("aft" => "folders")),
		));
		return $this->parse();
	}
	
	////
	// !Näitab folderi sisu
	function show_mailbox($args = array())
	{
		extract($args);
		// see on ka default action
		// messengeril saab määrata default lehte, mida avatakse
		// http://site/?class=messenger peale
		// FIXME: teha miski eraldi funktsioon selleks entry pointiks, mis siis
		// suunaks vajalikku kohta ümber
		$default_page = $this->msgconf["msg_default_page"];
		if ($default_page > 0)
		{
			switch($default_page)
			{
				case "1":
					return $this->mk_my_orb("create",array());

				case "2":
					return $this->mk_my_orb("configure",array());
			};
		};
		
		$folder = $id;
		$baseurl = $this->cfg["baseurl"];

		$inbox = $this->msg_inbox;

		$mactive = array();
	
		if (!$id)
		{
			$mactive = array("inbox");
			$id = $this->msgconf["msg_defaultfolder"];
			if (!$id)
			{
				$id = $inbox;
			};
			$folder = $id;
		};

		$fld_info = $this->get_object($folder);
		$folder_name = $fld_info["name"];

		if ($id == $inbox)
		{
			$folder_name = "Inbox ($folder_name)";
		};
		
		$menu = $this->gen_msg_menu(array(
			"title" => $folder_name,
			"activelist" => $mactive,
		));
		
		$this->read_template("mailbox.tpl");

		$folder_list = $this->_folder_list();

		$onpage = $this->msgconf["msg_on_page"];
		if (!$page)
		{
			$page = 1;
		};

		if (!$onpage)
		{
			$onpage = 20;
		};

		$c = "";
		$cnt = 0;
		$x_from = ($page - 1) * $onpage;
		$x_to = $x_from + $onpage;

		// Kirjade nimekiri selles folderis
		$msglist = $this->driver->msg_list(array(
			"folder" => $id,
			"from" => $x_from,
			"to" => $x_to
		));

		$num_msgs = $this->driver->get_num_messages(array(
			"folder" => $id
		));

		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "mailbox",
			"tbgcolor" => "#C3D0DC",
		));

		$t->parse_xml_def($this->cfg["basedir"]."/xml/messenger/table.xml");
		$t->define_field(array(
			"name" => "check",
			"caption" => "<a href='#' onClick='toggle_all()'>X</a>",
			"talign" => "center",
			"nowrap" => "1",
		));

		$t->define_field(array(
			"name" => "from",
			"caption" => "Kellelt",
			"talign" => "left",
			"nowrap" => 1,
			"sortable" => 1,
		));
	
		$t->define_field(array(
			"name" => "subject",
			"caption" => "Teema",
			"strformat" => "<a href='".$this->mk_my_orb("show", array("id" => "{VAR:id}"))."'>%s</a>",
			"talign" => "left",
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "attach",
			"caption" => "A",
			"sortable" => 1,
			"talign" => "center",
			"align" => "center",
		));
		
		$t->define_field(array(
			"name" => "when",
			"caption" => "Aeg",
			"talign" => "left",
			"type" => "time",
			"format" => "H:i d-M-Y",
			"nowrap" => 1,
			"numeric" => 1,
			"sortable" => 1,
		));

		
		if (is_array($msglist))
		{
			$pages = (int)($num_msgs / $onpage) + 1;
			if ($pages <= 0)
			{
				$pages = 1;
			};
			if ($page < 1)
			{
				$page = 1;
			}
			
			if ($page > $pages)
			{
				$page = $pages;
			};

			$prev = $next = "";
			if ($page > 1)
			{
				$prevpage = $page - 1;
				$this->vars(array(
					"pg" => $this->mk_my_orb("folder", array("id" => $folder, "page" => $prevpage))
				));
				$prev = $this->parse("prev");
			};

			if ($page < $pages)
			{
				$nextpage = $page + 1;
				$this->vars(array(
					"pg" => $this->mk_my_orb("folder", array("id" => $folder, "page" => $nextpage))
				));
				$next = $this->parse("next");
			};
				
			$c = "";
			$cnt = $x_from;

			foreach($msglist as $key => $msg)
			{
				if (!$msg["subject"])
				{
					$msg["subject"] = "(no subject)";
				};

				// kui status == true, siis on teade loetud
				$msg["attach"] = ($msg["num_att"] > 0) ? $this->parse("attach") : "";
				$msg["id"] = $msg["oid"];
				$msg["color"] = ($cnt % 2) ? "#EEEEEE" : "#FFFFFF";
				$msg["style"] = ($msg["status"]) ? "textsmall" : "textsmallbold";
				if (($msg["type"] & MSG_MASKOUT) == MSG_EXTERNAL)
				{
					$from = $this->MIME_decode($msg["mfrom"]);
					$ofrom = $mfrom;
					if ($this->msgconf["msg_filter_address"])
					{
						$from = preg_replace("/[<|\(|\[].*[>|\)|\]]/","",$from);
						if (strlen($from) == 0)
						{
							$from = $ofrom;
						};
					};
					$from = htmlspecialchars($from);
					$msg["from"] = $from;
				}
				else
				{
					$msg["from"] = $msg["modifiedby"];
				};
				$subject = $this->MIME_decode($msg["subject"]);
				if ($args["id"] == $this->conf["msg_draft"])
				{
					$msg["subject"] = "<a href='".$this->mk_my_orb("edit", array("id" => $msg["id"]))."'>" . $subject . "</a>";
				}
				else
				{
					$msg["subject"] = "<a href='".$this->mk_my_orb("show", array("id" => $msg["id"]))."'>" . $subject . "</a>";
				};
				$msg["pri"] = ($msg["pri"]) ? $msg["pri"] : 0;
				$msg["cnt"] = $cnt;
				$tm2 = $msg["tm"];
				$msg["tm"] = $this->time2date($msg["tm"],1);
				$this->vars($msg);
				$c .= $this->parse("line");
				$t->define_data(array(
					"check" => sprintf("<input type='checkbox' name='check[%d]' value='1'>",$msg["id"]),
					"from" => $msg["from"],
					"id" => $msg["oid"],
					"attach" => $msg["attach"],
					"subject" => $subject,
					"when" => $tm2,
					"style" => ($msg["status"]) ? "textsmall" : "textsmallbold",
				));
			};
			$cnt++;
		};

		$dummy = array("header" => "  - Mine - ");	
		$dummy2 = array("header" => " - Liiguta - ");
		// active_folder - selle folderi ID
		// $id - aktiivne folder
		$pagelist = array();

		$t->sort_by();

		for ($i = 1; $i <= $pages; $i++)
		{
			$pagelist[$i] = " - $i - ";
		};

		$this->vars(array(
			"line" => $c,
			"folders_dropdown" => $this->picker("dummy",$dummy + $folder_list),
			"folders_dropdown2" => $this->picker("dummy",$dummy2 + $folder_list),
			"active_folder" => $folder,
			"message_count" => verbalize_number($num_msgs),
			"pagelist" => $this->picker($page,$pagelist),
			"table" => $t->draw(),
			"folder_name" => $folder_name,
			"prev" => $prev,
			"next" => $next,
			"reforb" => $this->mk_reforb("mailbox_op",array("active_folder" => $folder)),
			"id" => $folder,
			"menu" => $menu,
			"url_no_id" => $this->mk_my_orb("folder"),
			"gopage_reforb" => $this->mk_reforb("folder", array("id" => $folder, "no_reforb" => true))
		));
		return $this->parse();
	}
	
	////
	// !Teeb mailboxi peal mingi eelnevalt operatsiooni (ntx liigutab teateid, kustutab, vmt.)
	// argumendid
	// check(array) - märgistatud teadete ID-d
	// op(string) - hetkel on defineeritud 3 (delete, mark_as_read, move_to)
	function mailbox_op($args = array())
	{
		extract($args);
		if ( ($move_to1) && ($folder1 == "header") )
		{	
			return $this->mk_my_orb("folder",array("id" => $active_folder));
		};
		
		if ( ($move_to2) && ($folder2 == "header") )
		{	
			return $this->mk_my_orb("folder",array("id" => $active_folder));
		};

		if ($delete)
		{
			$op = "delete";
		};
		if ($mark_as_new)
		{
			$op = "mark_as_new";
		};
		if ($mark_as_read)
		{
			$op = "mark_as_read";
		};
		if ($move_to1)
		{
			$op = "move_to";
			$folder = $folder1;
		};
		if ($move_to2)
		{
			$op = "move_to";
			$folder = $folder2;
		};
		if (!is_array($check))
		{
			$status_msg = MSG_MAILBOX_OP_WARNING;
		}
		else
		{
			$cnt = sizeof($check);
			// now, since all driver function expect message id-s as _values_ of id argument,
			// and since we get from the form has the id-s as keys, we need to "reverse" the array
			$rcheck = array_keys($check);
			switch($op)
			{
				// kustutamine on lihtsalt Trash folderisse liigutamine
				case "delete":
					$moveto = $this->conf["msg_trash"];
					$this->driver->msg_move(array(
						"id" => $rcheck,
						"folder" => $moveto,
					));
					$status_msg = sprintf(MSG_MAILBOX_OP_DELETED,$cnt);
					break;

				case "move_to":
					$this->driver->msg_move(array(	
						"id" => $rcheck,
						"folder" => $folder,
					));
					$status_msg = sprintf(MSG_MAILBOX_OP_MOVED,$cnt);
					break;

				case "mark_as_read":
					$moveto = ($this->msgconf["msg_move_read"]) ? $this->msgconf["msg_move_read_folder"] : "";
					$this->driver->msg_mark(array(
						"id" => $rcheck,
						"folder" => $moveto,
					));
					$status_msg = sprintf(MSG_MAILBOX_OP_MARKREAD,$cnt);
					break;

				case "mark_as_new":
					$this->driver->msg_mark(array(
						"id" => $rcheck,
						"status" => MSG_STATUS_UNREAD,
					));
					$status_msg = sprintf(MSG_MAILBOX_OP_MARKNEW,$cnt);
					break;

				default:
					$status_msg = sprintf(MSG_MAILBOX_OP_UNKOWN,$op);
			}
		};
		aw_session_set("status_msg",$status_msg);

		// Ja avame jälle selle folderi, mida me enne vaatasime
		return $this->mk_my_orb("folder",array("id" => $active_folder));
	}

	
	////
	// !Teeb kasutaja drafts folderisse uue tyhja teate, ning suunab siis ymber
	// edimisvormi
	function create_draft($args = array())
	{
		$oid = $this->init_message();
		return $this->mk_my_orb("edit",array("id" => $oid));
	}

	function init_message($args = array())
	{
		$oid = $this->new_object(array(
			"parent" => $this->conf["msg_draft"],
			"class_id" => CL_MESSAGE,false
		));
		$q = "INSERT INTO messages (id,draft) VALUES ('$oid',1)";
		$this->db_query($q);
		return $oid;
	}


	function reply_message($args = array())
	{	
		extract($args);
		$newid = $this->new_object(array(
			"parent" => $this->conf["msg_draft"],
			"class_id" => CL_MESSAGE,false
		));
		$this->driver->msg_copy(array(
			"id" => $reply,
			"newid" => $newid,
			"reply" => true,
			"reply_all" => $all,
			"qchar" => $this->msgconf["msg_quotechar"],
		));
		return $this->mk_my_orb("edit",array("id" => $newid));
	}

	function forward_message($args = array())
	{
		extract($args);
		$newid = $this->new_object(array(
			"parent" => $this->conf["msg_draft"],
			"class_id" => CL_MESSAGE,false
		));
		$this->driver->msg_copy(array(
			"id" => $forward,
			"newid" => $newid,
			"forward" => true,
			"qchar" => $this->msgconf["msg_quotechar"],
		));
		
		// add all the attaches to the new message as well
		$awf = get_instance("file");
		$this->get_objects_by_class(array(
			"class" => CL_FILE,
			"parent" => $forward,
		));
		while($row = $this->db_next())
		{
			$awf->cp(array("id" => $row["oid"],"parent" => $newid));
		}

		return $this->mk_my_orb("edit",array("id" => $newid));
	}
			
	////
	// !Kuvab uue teate sisestamise/muutmise vormi
	function gen_edit_form($args = array())
	{
		classload('icons');
		extract($args);
		$menu = $this->gen_msg_menu(array(
			"activelist" => array("write"),
		));
	
		$qchar = $this->msgconf["msg_quotechar"];
		$quote = false;
		$sprefix = "";

		if ($args["reply"])
		{
			$msg_id = $reply;
			$msg = $this->driver->msg_get(array("id" => $newid));
			if (strpos($msg["subject"],"Re: ") === false)
			{
				$sprefix = "Re: ";
			};
			$quote = true;
			
			if ($msg["type"] & MSG_MASKOUT== MSG_EXTERNAL)
			{
				$msg["mtargets1"] = str_replace("\"","",$msg["mfrom"]);
			}
			else
			{
				$msg["mtargets1"] = $msg["createdby"];
			};
		}
		elseif ($args["forward"])
		{
			$msg_id = $forward;
			$msg = $this->driver->msg_get(array("id" => $msg_id));
			if (strpos($msg["subject"],"Fwd: ") === false)
			{
				$sprefix = "Fwd: ";
			};
			$quote = true;
		}
		else
		{
			$msg_id = $args["id"];
			$msg = $this->driver->msg_get(array("id" => $msg_id));
			$msg["mtargets1"] = str_replace("\"","",$msg["mtargets1"]);
			$sprefix = "";
		};

		$msg["subject"] = $sprefix . $this->MIME_decode($msg["subject"]);
		$msg["message"] = $this->MIME_decode($msg["message"]);

		if ($quote)
		{
			$msg["message"] = str_replace("\n","\n$qchar",$msg["message"]);
			$msg["message"] = "\n$qchar" . $msg["message"];
		};


		// Loeme sisse ka objekti (teate) metainfo
		$metadata = $this->get_object_metadata(array(
			"oid" => $msg_id,
			"key" => "msg",
		));

		if (is_array($metadata))
		{
			$defsig = $metadata["signature"];
			$defident = $metadata["identity"];
		}
		else
		{
			$defsig = $this->conf["defsig"];
			$defident = $this->msgconf["msg_default_account"];
		};

		// loome nimekirja signatuuridest

		$siglist = array();
		$siglist["none"] = "(puudub)";
		if (is_array($this->msgconf["msg_signatures"]))
		{	
			// ilge vägistamine käib
			foreach($this->msgconf["msg_signatures"] as $sigkey => $sigdata)
			{
				$siglist[$sigkey] = $sigdata["name"];
			};
			$siglist = $this->picker($defsig,$siglist);
		};

		// picker funktsioonidel on 0 väärtusega elementidega kalad

		$idlist = array();
		$idlist["default"] = "Default (liitumisvormist)";

		if (is_array($this->msgconf["msg_pop3servers"]))
		{
			foreach($this->msgconf["msg_pop3servers"] as $idkey => $idata)
			{
				$idlist[$idkey] = $idata["name"];
			};
			$idlist = $this->picker($defident,$idlist);
		};


		$this->read_template("write.tpl");
		
		// koostame attachide nimekirja
		$this->get_objects_by_class(array(
			"class" => CL_FILE,
			"parent" => $msg_id,
		));
		$c = 0;
		$attaches = "";
		while($row = $this->db_next())
		{
			$c++;
			$this->vars(array(
				"cnt" => $c,
				"msgid" => $msg_id,
				"icon" => icons::get_icon_url($row["class_id"],""),
				"name" => $row["name"],
				"get_attach" => $this->mk_my_orb("get_attach", array("msgid" => $msg_id, "attnum" => $c))
			));
			$attaches .= $this->parse("attaches");
		};

		// siin tekitame nii mitu file input valja, kui konfis määratud oli
		$attach = "";	
		for ($i = 1; $i <= $this->msgconf["msg_cnt_att"]; $i++)
		{
			$this->vars(array("anum" => $i));
			$attach .= $this->parse("attach");
		};


		// topime kogutud info template sisse
		$this->vars($msg);

		// lauri muudetud -->
		$this->vars(array(
			"msg_field_width" => ($this->msgconf["msg_field_width"]) ? $this->msgconf["msg_field_width"] : 50,
			"msg_box_width" => ($this->msgconf["msg_box_width"]) ? $this->msgconf["msg_box_width"] : 60,
			"msg_box_height" => ($this->msgconf["msg_box_height"]) ? $this->msgconf["msg_box_height"] : 20,
			"pick_contact" => $this->mk_my_orb("pick", array("type" => "popup", "listmsg" => ($msg["type"] & MSG_LIST ? 1:0)), "contacts"),
			"attach_aw_o" => $this->mk_my_orb("search", array("target" => $args["id"], "stype" => 1),"objects")
		));

		if ($msg["type"] & MSG_LIST)
		{
			$mlist = get_instance("mailinglist/ml_list");
			$muutujad= $mlist->get_all_varnames($mlist->get_list_id_by_name($msg["mtargets1"]));
			foreach($muutujad as $k => $v)
			{
				$muutujalistvahe.="&nbsp;<b>#$v#</b>&nbsp;";
				if (strlen($muutujalistvahe)>100)
				{
					$muutujalist.=($muutujalist?"<br>":"")."$muutujalistvahe";
					$muutujalistvahe="";
				};
			};
			$muutujalist.=($muutujalist?"<br>":"")."$muutujalistvahe";
			$stamps="";
			// võta need stambid, millele saatjal on "send" õigus
			$this->get_objects_by_class(array("class" => CL_ML_STAMP));
			while ($stamp = $this->db_next())
			{
				if ($this->can("send",$stamp["oid"]))
				{
					$stampsvahe.="&nbsp;<b>#".$stamp["name"]."#</b>&nbsp;";
					if (strlen($stampsvahe)>100)
					{
						$stamps.=($stamps?"<br>":"")."$stampsvahe";
						$stampsvahe="";
					};
				};
			};
			$stamps.=($stamps?"<br>":"")."$stampsvahe";
			$this->vars(array(
				"muutujalist" => $muutujalist,
				"stambilist" => $stamps));
			$muutujad = $this->parse("muutujad");
		};
		// <--

		if (not($msg["type"]) && $this->msgconf["msg_default_format"] == 2)
		{
			// force html form
			$msg["type"] = MSG_HTML;
		};
		
		if (not($msg["type"]))
		{
			// $msg["type"] = 65535;
		};
	
		
		
		if ($msg["type"] & MSG_HTML)
		{
			$textedit=$this->parse("htmledit");
			$switch="text";
			$switchval="3";
		} 
		else
		{
			$textedit=$this->parse("textedit");
			$switch="html";
			$switchval="2";
		};


		if ($this->msgconf["msg_confirm_send"])
		{
			$send = $this->parse("confirmsend");
		}
		else
		{
			$send = $this->parse("send");
		};

		// if the menu bar is turned off, provide a link to configuration dialog from
		// the write message screen (may not make sense, but that's what a client requested)
		$confbutton = "";
		if ($this->msgconf["msg_hide_menubar"])
		{
			$confbutton = $this->parse("confbutton");
		};

		$this->vars(array(
			"msg_id" => $msg_id,
			"send" => $send,
			// lauri muudetud -->

			"muutujad" => $muutujad,
			"textedit" => $textedit,
			"htmledit" => "",
			"switch" => $switch,
			"switchval" => $switchval,
			// <--
			"siglist" => $siglist,
			"idlist" => $idlist,
			"prilist" => $this->picker($this->msgconf["msg_default_pri"],$this->priorities),
			"attach" => $attach,
			"attaches" => $attaches,
			"menu" => $menu,
			"msg_box_width" => ($this->msgconf["msg_box_width"]) ? $this->msgconf["msg_box_width"] : 60,
			"msg_box_height" => ($this->msgconf["msg_box_height"]) ? $this->msgconf["msg_box_height"] : 20,
			"msg_field_width" => ($this->msgconf["msg_field_width"]) ? $this->msgconf["msg_field_width"] : 50,
			"reforb" => $this->mk_reforb("handle",array("type" => $msg["type"])),
			"confbutton" => $confbutton,
		));
		
		$toolbar = $this->parse("toolbar");
		$this->vars(array(
			"toolbar" => $toolbar,
		));

		return $this->parse();
	}

	////
	// !Handleb teadet, s.t. kas postitab, voi hoopis salvestab selle
	// id - /opt/ teate id, 
	// argumendid:
	// msg_id(int) - teade mida saadetakse
	// message(string) - teate sisu
	// subject(string) - subject
	// mtargets1(string) - kasutajad kellele teade saata
	// identity(int) - identiteedi ID, mida kasutada saatmiseks
	// signature(int) - signatuuri ID, mida kasutada saatmisel
	// pri(int) - prioriteet

	// ja nuh. attachid ka. aga need antakse edasi globaalsete muutujate kaudu :(
	function handle_message($args = array())
	{
		// Shucks. Aga teist voimalust ei ole nende kättesaamiseks
		extract($args);

		if ($post)
		{
			// hm. ma ei teagi, kas ja kus seda kasutatakse?
			$args = $this->driver->msg_get(array(
				"id" => $id,
			));
		}
		elseif ($configure)
		{
			return $this->mk_my_orb("configure",array());
		}
		else
		{
			// First we need to fetch and store the attached files. If any.
			$this->receive_attaches(array(
				"msg_id" => $msg_id,
			));

			// now, we figure out how many attached objects that message has
			$num_att = $this->count_objects(array(
				"class" => CL_FILE,
				"parent" => $msg_id,
			));
		
			// and finally we will save the message
			$args["num_att"] = $num_att;
			
			if ($_makelist)
			{
				$save=1;
			};
			if ($_sethtml == 3)
			{
				$args["settype"] = 65535;
			};
			$this->save_message($args);
		};

		if ($save)
		{
			// bounce back to edit form
			return $this->mk_my_orb("edit",array("id" => $msg_id));
		};

		if ($preview)
		{
			return $this->mk_my_orb("preview",array("id" => $msg_id));
		};

		// Kuna me siia joudsime, siis jarelikult on meil vaja meil laiali saata
		// koigepealt splitime mtargetsi komade pealt ära ja eemaldame whitespace

		$args=array_merge($args,$rmsg);
		$this->post_message($args);	

		// at this moment we just return from this function call
		return $this->mk_my_orb("folder");
	}


	function post_message($args = array())
	{
		extract($args);

		$targets = explode(",",$mtargets1);

		$cc = $to = array();

		foreach($targets as $key => $val)
		{
			$targets[$key] = trim($targets[$key]);
		};

		$cctargets = explode(",",$mtargets2);
		foreach($cctargets as $key => $val)
		{
			if (strpos($val,"@"))
			{
				$cc[] = $val;
			};
		};

		if (strlen($targets[0]) == 0)
		{
			aw_session_set("status_msg",MSG_ADDR_CHECK_FAILED);
			// bounce back to edit form
			return $this->mk_my_orb("edit",array("id" => $msg_id));
		};

		// now we should be ok, let's separate internal and external addresses
		$externals = $internals = $lists= array();
	
		// kysime info teate kohta.
		$mdata = $this->get_object($msg_id);

		foreach($targets as $key => $val)
		{
			if (strpos($val,"@"))
			{
				$externals[] = $val;
				$to[] = $val;
			}
			elseif ($val[0]!= ":")  //selle järgi tunneb listi ära
			{
				$internals[] = $val;
			}
			else
			{
				$lists[]=$val;
			};
		};


		$message = $args["message"];
		$this->dequote($message);
		$this->dequote($subject);
		$this->dequote($subject);
	
		// Signatuuri voiks siiski juba edismivormi lisada,
		// siis saab kasutaja seda muuta soovi kohaselt
		if ($args["signature"] != "none")
		{
			// signatuur loppu
			$message .= "\r\n" . $this->msgconf["msg_signatures"][$args["signature"]]["signature"];
		};

		$this->awm = get_instance("aw_mail");

		#$message = str_replace("\r","",$message);
		#$message = str_replace("\n","\r\n",$message);
		$message = str_replace("\r\n", "\n", $message);

		//$message = str_replace("\n","\r\n",$message);
		// kui meil on tarvis saata ka valiseid faile, siis teeme seda siin
		if (sizeof($externals) > 0)
		{
			$dto = join(",",$to);
			$dcc = join(",",$cc);

			$this->deliver(array(
				"identity" => $identity,
				"subject" => $subject,
				"to" => $dto,
				"cc" => $dcc,
				"message" => /*stripslashes(*/$message/*)*/,
				"msg_id" => $msg_id,
				"type" => $args["type"],
				"pri" => $args["pri"],
			));
					
		};

		$udata = $this->get_user();

		if (sizeof($internals) > 0)
		{
			$sentto=array();

			$awe = get_instance("email");
			foreach($internals as $internal)
			{
				$this->get_object_by_name(array(
					"name" => $internal,
					"class_id" => CL_MAILINGLIST,
				));
				$row = $this->db_next();
				if ($row)
				{
					$this->save_handle();
					$members = $awe->get_members(array(
						"list_id" => $row["oid"],
					));
					$this->list_id = $row["oid"];
					$this->restore_handle();
					if (is_array($members))
					foreach($members as $key => $membermail)
					{
						if (isset($sentto[$membermail]))
						{
							unset($members[$key]);
							/*if ($GLOBALS["automatweb"]=="kalatehas")
								echo("$internal denied mail $membermail [$key]<br>");*/
						} 
						else
						{
							$sentto[$membermail]=1;
							/*if ($GLOBALS["automatweb"]=="kalatehas")
								echo("$internal allowed mail $membermail [$key]<br>");*/
						};
					};
					$to = join(",",$members);
					$headers = "From: $udata[email]";
					$subject = str_replace("\\","",$subject);
					$this->deliver(array(
						"identity" => $identity,
						"subject" => $subject,
						"to" => "",
						"message" => $message,
						"msg_id" => $msg_id,
						"alist" => $members,
						"type" => $args["type"],
						"pri" => $pri,
					));
				};
			};
		};



		// ja lopuks liigutame ta draftist ära outboxi
		// aga seda peaks tegema ainult siis, kui ta ka toesti oli draftis.
		if ($mdata["parent"] == $this->conf["msg_draft"])
		{
			$outbox = $this->conf["msg_outbox"];
			$q = "UPDATE objects SET parent = '$outbox' WHERE oid = '$msg_id'";
			$this->db_query($q);
		}

		// siin kutsume välja meililistidesse saatmise (kui vaja)
		if (sizeof($lists))
		{
			$mllist=get_instance("mailinglist/ml_list");
			$route_back=$this->mk_my_orb("edit",array("id" => $msg_id));
			aw_session_set("route_back",$route_back);
			$url=$mllist->route_post_message(array("id" => $msg_id, "targets" => $lists));
			Header("Location: $url");
			die();
		};
	}

	function deliver($args = array())
	{
		$this->awm->clean();
		extract($args);
		// leiame kasutatud identiteedi
		if ($identity != "default")
		{
			$froma = $this->msgconf["msg_pop3servers"][$args["identity"]]["address"];
			$fromn = $this->msgconf["msg_pop3servers"][$args["identity"]]["name1"] . " " . $this->msgconf["msg_pop3servers"][$args["identity"]]["surname"];
			if (!$froma)
			{
				print MSG_FROM_CHECK_FAILED;
				exit;
			}
		}
		else
		{
			$froma = $udata["email"];
			$fromn = "";
		};

		$mfrom = sprintf("%s <%s>",$fromn,$froma);
		$this->quote($mfrom);
		$tm = time();
		$q = "UPDATE messages SET mfrom = '$mfrom',tm=$tm WHERE id = '$msg_id'";
		$this->db_query($q);

		$message = stripslashes($message);

		//echo("in deliver type=$type");//dbg
		// tavaline meil
		if (($type & MSG_HTML) == 0)
		{
			//see oli enne handle_message()s. mix seda vaja yldse on?? kes siis plaintexti tage topib
			// sellepärast ongi vaja, et ei topitaks -- duke
			$body=strip_tags($message);
		} 
		else
		{
			// HTML mail
			//$body=strip_tags(strtr($message,array("<br>"=>"\r\n","<BR>"=>"\r\n","</p>"=>"\r\n","</P>"=>"\r\n")));
			$body=strtr($message,array("<br>"=>"\r\n","<BR>"=>"\r\n","</p>"=>"\r\n","</P>"=>"\r\n"));
		};
		$this->awm->create_message(array(
			"froma" => $froma,
			"fromn" => $fromn,
			"subject" => $subject,
			"to" => $to,
			"cc" => $cc,
			"body" => $body,
		));

		// tsekka, kas on html teade
		if ($type & MSG_HTML)
		{
			$this->awm->htmlbodyattach(array("data"=>$message));
		};
		// Nyyd otsime valja koik attachitavad failid ja lisame need ka kirjale

		$this->get_objects_by_class(array(
			"class" => CL_FILE,
			"parent" => $msg_id,
		));
		while($row = $this->db_next())
		{
			// dammit, I don't like this shit a single bit,
			// but as ahto is pushing me to complete this as fast as possible
			// I can't spend anymore time here right now
			// this should probably be replaced with calls to the file class
			// to retrieve the content and attach it to the message
			$this->save_handle();
			$q = "SELECT * FROM files WHERE id = '$row[oid]'";
			$this->db_query($q);
			$row2 = $this->db_next();
			$this->restore_handle();
			$prefix = substr($row2["file"],0,1);
			if (substr($row2["file"],0,1) == "/")
			{
				$fname = $row2["file"];
			}
			else
			{
				$fname = $this->cfg["site_basedir"] . "/files/$prefix/$row2[file]";
			};
			if (file_exists($fname))
			{
				$this->awm->fattach(array(	
					"path" => $fname,
					"name" => $row["name"],
					"disp" => "attachment; filename=\"" . $row["name"] . "\"",
					"contenttype" => $row2["type"],
				));
			};

		};

		// noja lopuks siis, saadame meili minema ka
		$baseurl = $this->cfg["baseurl"];
		
		// set the priority
		if ($args["pri"])
		{
			$this->awm->set_header("X-Priority",$args["pri"]);
		};

		if (is_array($alist) && sizeof($alist) > 0)
		{
			$awe = get_instance("email");
			foreach($alist as $addr)
			{
				$member = $awe->get_member(array("list_id" => $this->list_id,"mail" => $addr));
				$this->awm->set_header("To",$addr);
				$this->awm->body_replace(array(
							"#nimi#" => $member["name"],
							"#email#" => $member["mail"],
							"#kuupaev#" => $this->time2date(time(),2),
				));
				$this->awm->gen_mail();
				print "Saadan aadressile $member[mail]<br>";
				//print ".";
				flush();
			}
			print "<br>";
			print sizeof($alist) . " kirja saadetud<br>";
			$baseurl = aw_global_get("baseurl");
			print "<a href='".$this->mk_my_orb("folder")."'>tagasi messengeri</a>";
			exit;
			//mis se on siis ?? lauri
			// feedback kirjade saatmise kohta. Mõnikord tahetakse miskisse paarisaja
			// liikmega listi kirja saata ja siis on hea, kui saatja näeb, et midagi
			// toimub - selle asemel, et liivakella põrnitseda. -- duke
		}
		else
		{
			$this->awm->gen_mail();
		};
	}
	
	////
	// !Receives the attached files and put them away for further use
	// This handles only new files, e.g. <input type="file"-s
	// argumendid:
	// msg_id(int) - the message we are storing the newly created objects under
	// returns:
	// the number of files actually "attached"
	function receive_attaches($args = array())
	{
		// we have to use global variables here, since there simply is
		// no other way to do it.
		global $attach;
		global $attach_name;
		global $attach_type;
		
		$awf = get_instance("file");
		$count = 0;
		
		if (!is_array($attach))
		{
			return 0;
		};

		foreach($attach as $idx => $tmpname)
		{
			// opera paneb siia tyhja stringi, mitte none
			if (($tmpname != "none") && ($tmpname))
			{
				$count++;
				 // fail sisse
				$fc = $this->get_file(array(
					"file" => $tmpname,
				));

				// now we have the file and must store the attached
				// files. And we will store in the filesystem. Where else?
				// Where else?

				// And... shouldn't we check the return code or something?
				$awf->put(array(
					"store" => "fs",
					"parent" => $args["msg_id"],
					"filename" => basename($attach_name[$idx]),
					"type" => $attach_type[$idx],
					"content" => $fc,
				));
			}
		};

		return $count;
	}

	////
	// !Saves the message
	// msg_id(int) - teade mida saadetakse
	// message(string) - teate sisu
	// subject(string) - subject
	// mtargets1(string) - kasutajad kellele teade saata
	// identity(int) - identiteedi ID, mida kasutada saatmiseks
	// signature(int) - signatuuri ID, mida kasutada saatmisel
	// pri(int) - prioriteet
	function save_message($args = array())
	{
		extract($args);
		// lauri muudetud: lisasin mfrom välja
		unset($sethtml);
		if ($_sethtml=="3")// html => text
		{
			//echo("<textarea cols=80 rows=15>$message</textarea>");//dbg
			$message=strip_tags(strtr($message,array("<br>"=>"\r\n","<BR>"=>"\r\n","</p>"=>"\r\n","</P>"=>"\r\n")));
			//echo("<textarea cols=80 rows=15>$message</textarea>");//dbg
			$sethtml=", type = type & ".MSG_MASKNOTHTML;
			if ($_makelist)
			{
				$sethtml.=" | ".MSG_LIST;
			};
		};
		
		if ($_sethtml=="2")// text => html
		{
			$message=strtr($message,array("\r"=>"","\n"=>"<br>"));
			$sethtml=", type = type | ".MSG_HTML;
			if ($_makelist)
			{
				$sethtml.=" | ".MSG_LIST;
			};
		};

		if (!$sethtml && $_makelist)
		{
			$sethtml=", type = type | ".MSG_LIST;
		};
		// siin tuleb vaadata, et messages.type != NULL kuna siis ei saa & ega | operaatoreid kasutada
		if (not($settype))
		{
			$settype = 0;
		};
		$this->db_query("UPDATE messages SET type='$settype' WHERE id='$msg_id' AND type IS NULL");
		
		if ($subject[0] == ":")
		{
			$sethtml .= ", type | ".MSG_LIST;
		}

		$q = "UPDATE messages
			SET 
				message = '$message',
				subject = '$subject',
				mtargets1 = '$mtargets1',
				mtargets2 = '$mtargets2',
				mfrom = '$mfrom',
				pri = '$pri'
				$sethtml
			WHERE id = '$msg_id'";
		$this->db_query($q);

		$msg_meta = array(
			"identity" => $identity,
			"signature" => $signature,
		);

		$this->set_object_metadata(array(
			"oid" => $msg_id,
			"key" => "msg",
			"value" => $msg_meta,
		));
	}

	////
	// !Initsialiseerib otsingu
	function _init_search($args = array())
	{
		$this->fields = array(
			"mfrom" => MSG_FIELD_FROM,
			"mto" => MSG_FIELD_TO,
			"subject"=> MSG_FIELD_SUBJECT,
			"message" => MSG_FIELD_CONTENT,
		);

		$this->connectors = array(
			"and" => MSG_CONNECTOR_AND,
			"or" => MSG_CONNECTOR_OR,
		);

		// since we need to save the contents of the search, we will 
		// do it right with user metadata
		$this->awuser = get_instance("users");
	}


	function search($args = array())
	{
		$a2 = ($args["refine"]) ? "refine" : "newsearch";
		$menu = $this->gen_msg_menu(array(
			"title" => MSG_TITLE_SEARCH,
			"activelist" => array("search",$a2),
		));

		$flist = $this->_folder_list();

		$this->read_template("search.tpl");
		$c = "";

		$this->_init_search();

		if ($this->object_id)
		{
			$_sconf = $this->msg_obj["meta"]["msg_searches"];
		}
		else
		{
			$_sconf = $this->awuser->get_user_config(array(
				"uid" => aw_global_get("uid"),
				"key" => "msg_searches",
			));
		}

		if (is_array($_sconf) && ($args["refine"]))
		{
			list(,$sconf) = each($_sconf);
			$checkall = false;
		}
		else
		{
			// kui kuvame uut otsimisvormi, siis by default on koik
			// folderid checkitud.
			$checkall = true;
			$sconf = array();
		};

		// koostame checkboxitud nimekirja vormidest, kust on vaja otsida
		foreach($flist as $key => $val)
		{
			if ($checkall)
			{
				$checked = "checked";
			}
			else
			{
				$checked = ($sconf["folders"][$key]) ? "checked" : "";
			};

			$this->vars(array(
				"checked" => $checked,
				"id" => $key,
				"name" => $val,
			));
			$c .= $this->parse("line");
		};

		$rf =  $this->mk_reforb("register_search",array());

		$sline = "";

		for ($i = 0; $i <= 4; $i++)
		{
			$chosen = ($checkall) ? -1 : $sconf["field"][$i];

			if (isset($args["mfrom"]))
			{
				if ( $i == 0 )
				{
					$value = htmlspecialchars(rawurldecode($args["mfrom"]));
					$value = stripslashes($value);
				}
				else
				{
					$value = "";
				};
			};

			$this->vars(array(
				"idx" => $i,
				"num" => $i + 1,
				"fieldlist" => $this->picker($chosen,$this->fields),
				"value" => (isset($value)) ? $value : $sconf["search"][$i],
			));
			$sline .= $this->parse("sline");
			if ($i < 4)
			{
				$chosen = ($checkall) ? -1 : $sconf["connector"][$i];
				$this->vars(array(
					"connlist" => $this->picker($chosen,$this->connectors),
				));

				$sline .= $this->parse("connline");
			};
		};

		$this->vars(array(
			"msg_search_remark" => MSG_SEARCH_REMARK,
			"sline" => $sline,
			"line" => $c,
			"menu" => $menu,
			"reforb" => $rf,
		));

		return $this->parse();
	}

	////
	// !Registreerib otsingu
	// argumendid:
	// connector (array) - sisaldab koiki connectoreid
	// search (array) - sisaldab otsistringe
	// field (array) - sisaldab fielde, millest otsida.
	function register_search($args = array())
	{
		extract($args);

		$id = "s" . gen_uniq_id();

		$this->_init_search();

		if ($this->object_id)
		{
			$this->set_object_metadata(array(
				"oid" => $this->object_id,
				"key" => "msg_searches",
				"value" => array($id => $args)
			));
		}
		else
		{
			// me salvestame ta kasutajatabelisse, sest mingil hetkel tulevikus lisame 
			// otsingute salvestamise featuuri
			$this->awuser->set_user_config(array(
				"uid" => aw_global_get("uid"),
				"key" => "msg_searches",
				"value" => array($id => $args),
			));
		}

		return $this->mk_my_orb("do_search");	
	}

	////
	// !Performs the actual search
	function do_search($args = array())
	{
		$menu = $this->gen_msg_menu(array(
			"title" => MSG_TITLE_SEARCH,
			"activelist" => array("search"),
		));

		$this->_init_search();
		
		if ($this->object_id)
		{
			$_sconf = $this->msg_obj["meta"]["msg_searches"];
		}
		else
		{
			$_sconf = $this->awuser->get_user_config(array(
				"uid" => aw_global_get("uid"),
				"key" => "msg_searches",
			));
		}

		list(,$sconf) = each($_sconf);
		
		extract($sconf);

		$folder_list = $this->_folder_list();
		
		// koigepealt tuleb siis koostada query string
		$qs = "";
		$quser = "";
		for ($i = 1; $i < sizeof($search); $i++)
		{
			// kui selles valjas asub string, siis lisame selle query stringile
			if ($search[$i - 1])
			{
				$qs .= sprintf("(%s LIKE '%%%s%%')",$field[$i - 1], $search[$i - 1]);
				$quser .= sprintf(" ..väljal <b>%s</b> sisaldub string '%s'",$this->fields[$field[$i - 1]],stripslashes(htmlspecialchars($search[$i - 1])));
			};

			if ($search[$i])
			{
				$qs .= sprintf(" %s ",$connector[$i-1]);
				$quser .= ($connector[$i-1] == "and") ? MSG_CONNECTOR_AND : MSG_CONNECTOR_OR;
				$quser .= "<br>";
			};
		};

		if (strlen($qs) == 0)
		{
			return $this->mk_my_orb("search");
		};

		$baseurl = $this->cfg["baseurl"];

		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "mailbox_search",
			"tbgcolor" => "#C3D0DC",
		));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/messenger/table.xml");
		$t->define_field(array(
			"name" => "folder",
			"caption" => "Folder",
			"strformat" => "<a href='".$this->mk_my_orb("folder", array("id" => "{VAR:fid}"))."'>%s</a>",
			"talign" => "left",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "from",
			"caption" => "Kellelt",
			"talign" => "left",
			"nowrap" => 1,
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "subject",
			"caption" => "Teema",
			"strformat" => "<a href='".$this->mk_my_orb("show", array("id" => "{VAR:id}"))."'>%s</a>",
			"talign" => "left",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "when",
			"caption" => "Aeg",
			"talign" => "left",
			"type" => "time",
			"format" => "H:i d-M-Y",
			"nowrap" => 1,
			"sortable" => 1,
		));

		$results = $this->driver->msg_search(array(
			"value" => $value,
			"connector" => $connector,
			"qs" => $qs,
			"folders" => array_keys($folders),
		));
		$this->read_template("searchresults.tpl");
		$c = "";
		foreach ($results as $id => $contents)
		{
			$subject = (isset($contents["subject"])) ? $this->MIME_decode($contents["subject"]) : "(no subject)";
			$this->dequote($subject);
			$this->dequote($subject);
			//$contents["tm"] = $this->time2date($contents["tm"],2);
			$contents["from"] = $this->MIME_decode($contents["mfrom"]);
			$contents["folder"] = $folder_list[$contents["parent"]];
			$contents["fid"] = $contents["parent"];
			$contents["mid"] = $contents["oid"];
			$contents["subject"] = $subject;
			$t->define_data(array(
				"folder" => $folder_list[$contents["parent"]],
				"from" => $this->MIME_decode($contents["mfrom"]),
				"subject" => $subject,
				"id" => $contents["oid"],
				"fid" => $contents["parent"],
				"when" => $contents["tm"],
			));
			$this->vars($contents);
			$c .= $this->parse("line");
		};
		
		$t->sort_by();
		$this->vars(array(
			"line" => $c,
			"quser" => $quser,
			"table" => $t->draw(),
			"value" => $value,
			"menu" => $menu,
		));
		$ret = $this->parse();
		return $ret;
	}

	function filter($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array());
	}

	////
	// !Kuvab teate
	function show_message($args = array())
	{
		classload('icons');
		$menu = $this->gen_msg_menu(array(
			"title" => "read",
		));
		extract($args);
	
		// id-d kasutame sellepärast, et integeri jargi otsimine on kiirem, kui 
		// stringi (ntx UIDL)
		$msg = $this->driver->msg_get(array(
			"id" => $id,
		));
		
		$folder_list = $this->_folder_list();

		$this->read_template("message.tpl");
		// koostame attachide nimekirja
		$this->get_objects_by_class(array(
			"class" => CL_FILE,
			"parent" => $id,
		));
		$c = 0;
		$attaches = "";
		$awf = get_instance("file");
		$xml = get_instance("xml");
		while($row = $this->db_next())
		{
			$c++;
			$this->save_handle();
			$fdat = $awf->get_file_by_id($row["oid"]);
			$this->restore_handle();
			if ($fdat["type"] == "text/aw-event")
			{
				$subtpl = "event_attach";
				$edata = $xml->xml_unserialize(array("source" => $fdat["content"]));
				$evars = array("start" => date("H:i d-m-Y",$edata["data"]["start"]),
					"end" => date("H:i d-m-Y",$edata["data"]["end"]),
					"name" => $edata["data"]["title"],
					"icon" => icons::get_icon_url(CL_CAL_EVENT,""),
					"id" => $row["oid"],
					"cnt" => $c,
				);
				$this->vars($evars);
			}
			else
			{
				$subtpl = "attach";
				$this->vars(array(
					"aid" => $row["id"],
					"msg_id" => $id,
					"cnt" => $c,
					"msgid" => $args["id"],
					"icon" => icons::get_icon_url(CL_FILE,$row["name"]),
					"name" => $this->MIME_decode($row["name"]),
					"get_attach" => $this->mk_my_orb("get_attach", array("msgid" => $args["id"], "attnum" => $c)),
					"pick_folder" => $this->mk_my_orb("pick_folder", array("type" => "popup", "attach" => $row["id"], "msg_id" => $id))
				));
			};
			$attaches .= $this->parse($subtpl);
		};
		
		$vars = array();

		// Sõltuvalt message tüübist on vaja erinevad template väljad täita erinevate väärtustega
		switch($msg["type"] & MSG_MASKOUT)
		{
			case MSG_EXTERNAL:
				// replace < and > in the fields with correspondening HTML entitites
				$from = $msg["mfrom"];
				$cc = $msg["mtargets2"];
				$from = $this->MIME_decode($from);
				$subject = $this->MIME_decode($msg["subject"]);
				$to = $msg["mto"];
				$to_parts = explode(",",$to);

				$mto = "";
				foreach($to_parts as $id => $addr)
				{
					$this->vars(array(
						"addr" => htmlspecialchars(trim($addr)),
						"imp_contact" => $this->mk_my_orb("import", array("addr" => rawurlencode($addr)), "contacts")
					));
					$mto .= $this->parse("import_contact");
					$mto .= " ";
				};

				$cc_parts = explode(",",$cc);
				$mcc = "";
				foreach($cc_parts as $id => $addr)
				{
					$this->vars(array(
						"addr" => htmlspecialchars(trim($addr)),
						"imp_contact" => $this->mk_my_orb("import", array("addr" => rawurlencode($addr)), "contacts")
					));
					$mcc .= $this->parse("import_contact");
					$mcc .= " ";
				};
					
				$vars = array(
					"mfrom" => htmlspecialchars($from),
					"imp_c_2" => $this->mk_my_orb("import", array("addr" => rawurlencode($from)), "contacts"),
					"mtargets1" => $mto,
					"subject" => htmlspecialchars($subject),
					"mtargets2" => $mcc,
					"search" => $this->mk_my_orb("search", array("mfrom" => rawurlencode($from)))
				);
				break;

			case MSG_INTERNAL:
				$subject = $this->MIME_decode($msg["subject"]);
				$vars = array(
					"mfrom" => $msg["createdby"],
					"mto" => $msg["mto"],
					"mtargets" => $msg["mtargets1"],
					"subject" => $subject,
				);
				break;

			default:
				// ehk siis, meil on kasil uue kirja koostamine
				$vars = array(
					"mtargets1" => $msg["mtargets1"],
					"subject" => $msg["subject"],
				);
				break;
		};

		$this->vars($vars);	
		$cc = ($cc) ? $this->parse("cc") : "";

		$message = $msg["message"];
		if ($msg["type"] & MSG_HTML)
		{
		}
		else
		{
			$message = htmlspecialchars($message);
			$message = $this->MIME_decode($message);
			$message = preg_replace("/(\r)/","",$message);
			$message = preg_replace("/(\n)/","<br>",$message);
			$message = quoted_printable_decode($message);
			$message = preg_replace("/(http|https|ftp)(:\/\/\S+?)\s/si","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $message);
		};

		
		$this->vars(array(
			"msg_id" => $args["id"],
			"reply" => $this->mk_my_orb("reply", array("reply" => $args["id"])),
			"reply_all" => $this->mk_my_orb("reply_all", array("reply" => $args["id"])),
			"forward" => $this->mk_my_orb("forward", array("forward" => $args["id"])),
			"delete" => $this->mk_my_orb("delete", array("id" => $args["id"])),
			"headers" => $this->mk_my_orb("headers", array("id" => $args["id"])),
			"edit" => $this->mk_my_orb("edit", array("id" => $args["id"])),
			"post" => $this->mk_my_orb("post", array("id" => $args["id"])),
			"showatt" => $this->mk_my_orb("show_attach", array("type" => "popup")),
			"gotourl" => $this->mk_my_orb("folder"),
			"save_cal" => $this->mk_my_orb("importfile", array("id" => $msg["id"]), "planner")
		));
		
		// soltuvalt "op"-ist naitame kas show voi preview sectionit muutmistemplatest
		$s = ($op == "show") ? $this->parse("show") : $this->parse("preview");

		$this->vars(array(
			"tm" => $this->time2date($msg["tm"]),
			"mtargets2" => $msg["mtargets2"],
			"id" => $msg["id"],
			"msg_id" => $args["id"],
			"msgid" => $args["id"],
			"cc" => $cc,
			"show" => $s,
			"status" => $msg["status"],
			"message" => $message,
			"msg_font" => ($this->msgconf["msg_font"]) ? $this->msgconf["msg_font"] : "Courier",
			"msg_font_size" => ($this->msgconf["msg_font_size"]) ? $this->msgconf["msg_font_size"] : "0",
			"del_reforb" => $this->mk_reforb("delete",array("id" => $msg["id"])),
			"attach" => $attaches,
			"reply_reforb" => $this->mk_reforb("reply",array("id" => $msg["id"])),
			"mailbox" => $this->picker($mailbox,$mboxes),
			"mbox_name" => $mboxes[$mailbox],
			"folders_dropdown" => $this->picker($this->msg_inbox,$folder_list),
		));
		
		$this->vars(array(
			"att" => $att,
			"menu" => $menu,
		));

		// kui kasutaja soovib teateid liigutada, siis teeme seda nüüd
		$moveto = ($this->msgconf["msg_move_read"]) ? $this->msgconf["msg_move_read_folder"] : "";
		$this->driver->msg_mark(array(
			"id" => $id,
			"parent" => $moveto,
		));
		return $this->parse();
	}

	////
	// !Näitab attachi
	function show_attach($args = array())
	{
		extract($args);	
		$actions = array(
			CL_CAL_EVENT => array(
				"class" => "planner",
				"action" => "show_event",
				),
			CL_FILE => array(
				"class" => "file",
				"action" => "show2",
				),
		);

		$q = "SELECT * FROM msg_objects WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$data = unserialize($row["content"]);
		$class = $actions[$data["class_id"]]["class"];
		$action = $actions[$data["class_id"]]["action"];
		$t = get_instance($class);
		$data["att_id"] = $id;
		print $t->$action($data);
	}

	////
	// !Moodustab kasutaja messengeri konfiguratsiooni, asendades puuduvad väärtused vajadusel defaultidega
	// conf - users tabelist loetud "messenger" välja sisu
	function _get_msg_conf($args = array())
	{
		extract($args);
		$raw = aw_unserialize($args["conf"]);

		// mitu teadet lehel kuvatakse
		if (!isset($raw["msg_on_page"]))
		{
			$raw["msg_on_page"] = 30;
		};		

		// mida teha "kustutatud" kirjadega
		if (!isset($raw["msg_ondelete"]))
		{
			$raw["msg_ondelete"] = "delete";
		};

		// küsida kirja saatmisel kinnitust
		if (!isset($raw["msg_confirm_send"]))
		{
			$raw["msg_confirm_send"] = 1;
		};

		// draft folderi asukoht
		if (!isset($raw["msg_draft"]))
		{
			// vaikimisi salvestame draftid inboxi
			$raw["msg_draft"] = $this->msg_inbox;
		};

		// default signa
		if (!isset($raw["msg_defsig"]))
		{
			$raw["msg_defsig"] = 0;
		};

		// default prioriteet uue kirja kirjutamisel
		if (!isset($raw["msg_default_pri"]))
		{
			$raw["msg_default_pri"] = 0;
		};

		// millist märki vaikimisi kvootimiseks kasutatakse
		if (!isset($raw["msg_quotechar"]))
		{
			$raw["msg_quotechar"] = ">";
		};

		// mitu attachi lisamise textboxi by default kuvatakse
		if (!isset($raw["msg_cnt_att"]))
		{
			$raw["msg_cnt_att"] = 3;
		};

		if (!isset($raw["msg_move_read"]))
		{
			$raw["msg_move_read"] = $this->user["msg_move_read"];
		};
		
		return $raw;
	}
			
		
	////
	// !Kuvab konfigureerimisvormi (oigemini vastava lehe sellelt)
	function configure($args = array())
	{
		extract($args);
		// Menüü koostamine
		$page = ($page) ? $page : "general"; // see viimane kehtib by default
		$menu = $this->gen_msg_menu(array(
			"title" => MSG_TITLE_CONFIG,
			"activelist" => array("configure",$page),
		));

		$vars = array();
		
		// messengeri nimelises väljas kasutaja tabelis hoiakse kasutaja mailboxi konfiguratsiooni
		// xml-is, if you want to know.

		$folder_list = $this->_folder_list();
		switch($page)
		{
			case "folders":
				$tpl = "conf_folders.tpl";
				$vars = array(
					"inbox_select" => $this->picker($this->msg_inbox,$folder_list),
					"outbox_select" => $this->picker($this->conf["msg_outbox"],$folder_list),
					"trash_select" => $this->picker($this->conf["msg_trash"],$folder_list),
					"draft_select" => $this->picker($this->conf["msg_draft"],$folder_list),
				);
				break;

			case "signature":
				// Kõigepealt koostame olemasolevate signatuuride nimekirja
				$siglist = "";
				$cnt = 0;
				if (is_array($this->msgconf["msg_signatures"]))
				{
					$this->read_template("signatures.tpl");
					foreach($this->msgconf["msg_signatures"] as $signum => $sigdat)
					{
						$this->vars(array(
							"signum" => $signum,
							"signame" => $sigdat["name"],
							"signature" => nl2br($sigdat["signature"]),
							"default" => checked($this->conf["defsig"] == $signum),
							"edit" => $this->mk_my_orb("edit_signature", array("id" => $signum))
						));
						$siglist .= $this->parse("sig");
					};
					$this->vars(array("sig" => $siglist));
					$siglist = $this->parse();
				};
				$vars = array(
					"siglist" => $siglist,
					"sigcount" => verbalize_number($cnt)
				);
				$tpl = "conf_signatures.tpl";
				break;

			case "accounts":
				$tpl = "accounts.tpl";
				$this->read_template($tpl);
				$c = "";
				$cnt = 0;
				$pop3conf = $this->msgconf["msg_pop3servers"];
				if (is_array($pop3conf))
				{
					foreach($pop3conf as $accid => $cvalues)
					{
						$this->vars(array(
							"id" => $accid,
							"name" => $cvalues["name"],
							"defcheck" => checked($this->msgconf["msg_default_account"] == $accid),
							"checked" => checked($cvalues["default"]),
							"type" => "POP3", // *pun intended*
							"change" => $this->mk_my_orb("configure_pop3", array("id" => $accid)),
							"getmail" => $this->mk_my_orb("get_mail", array("account" => $accid))
						));
						$cnt++;
						$c .= $this->parse("line");
					};
				};
				$acclist = $c;
				$vars = array(
					"line" => $acclist,
					"aftpage" => "accounts"
				);
				break;


			default:
				$tpl = "conf_general.tpl";
				$conf = $this->msgconf;
				$vars = array(
					"msg_on_page" => $this->picker($conf["msg_on_page"],array("10" => "10", "20"=>"20","30"=>"30","40"=>"40","50" => "50","75" => "75","100" => "100","200" => "200","500" => "500")),
					"msg_store_sent" => checked($conf["msg_store_sent"]),
					"msg_ondelete" => $this->picker($conf["msg_ondelete"],array("delete" => "Kustutakse", "move" => "Viiakse Trash folderisse")),
					"msg_confirm_send" => checked($conf["msg_confirm_send"]),
					"msg_filter_address" => checked($conf["msg_filter_address"]),
					"msg_window" => checked($conf["msg_window"]),
					"msg_quote_list" => $this->picker($conf["msg_quotechar"],array(">" => ">",":" => ":","}" => "}")),
					"msg_default_pri" => $this->picker($conf["msg_default_pri"],$this->priorities),
					"msg_cnt_att" => $this->picker($conf["msg_cnt_att"],array("1" => "1","2" => "2","3" => "3","4" => "4","5" => "5")),
					"msg_move_read_folder" => $this->picker($conf["msg_move_read_folder"],$folder_list),
					"msg_move_read" => checked($conf["msg_move_read"]),
					"msg_hide_menubar" => checked($conf["msg_hide_menubar"]),
					"msg_font" => $this->picker($conf["msg_font"],array("courier" => "Courier","arial" => "Arial","Tahoma" => "Tahoma")),
					"msg_font_size" => $this->picker($conf["msg_font_size"],array("1" => "1","2" => "2", "3" => "3","+2" => "+2", "+3" => "+3")),
					"msg_box_width" => ($conf["msg_box_width"]) ? $conf["msg_box_width"] : 60,
					"msg_box_height" => ($conf["msg_box_height"]) ? $conf["msg_box_height"] : 20,
					"msg_field_width" => ($conf["msg_field_width"]) ? $conf["msg_field_width"] : 50,
					"msg_default_page" => $this->picker($conf["msg_default_page"],$this->pages),
					"msg_default_format" => $this->picker($conf["msg_default_format"],$this->default_format),
					"aftpage" => "general",
				);
				break;
		};
		
		// sisemine vorm
		$this->read_template($tpl);
		$this->vars($vars);
		$content = $this->parse();

		// raam
		$this->read_template("configure.tpl");
		// page abil suuname kasutaja pärast tagasi õigele lehele
		$this->vars(array(
			"content" => $content,
			"menu" => $menu,
			"reforb" => $this->mk_reforb("submit_configure",array("page" => $page)),
		));
		return $this->parse();
	}

	////
	// !Submitib eelmisest vormist tulnud data
	function submit_configure($args = array())
	{
		extract($args);
		if ($page == "folders")
		{
			$this->conf["msg_outbox"] = $msg_outbox;
			$this->conf["msg_draft"] = $msg_draft;
			$this->conf["msg_trash"] = $msg_trash;
			$this->msg_inbox = $msg_inbox;
			$this->save_conf();
			return $this->mk_my_orb("configure",array("page" => "folders"));
		}
		else
		if ($page == "signature")
		{
			$ds = new aw_array($delsig);
			foreach($ds->get() as $sigid => $one)
			{
				if ($one == 1)
				{
					unset($this->msgconf["msg_signatures"][$sigid]);
				}
			}
			$this->conf["defsig"] = $defsig;
			$this->save_conf();
			return $this->mk_my_orb("configure",array("page" => "signature"));
		}
		else
		if ($page == "accounts")
		{
			// default_acc peaks sisaldama koigi nende accountide id-sid, millelt
			// get mail id-sid peaks kysima
			$serverlist = $this->msgconf["msg_pop3servers"];
			foreach($serverlist as $key => $val)
			{
				$serverlist[$key]["default"] = $default_acc[$key];
			};
			$this->msgconf["msg_pop3servers"] = $serverlist;
			$this->msgconf["msg_default_account"] = $msg_default_account;
			$this->save_conf();
			return $this->mk_my_orb("configure",array("page" => "accounts"));
		}
			
		// nüüd tsükkel üle kõigi $args-i elementide, mille nimi algab msg_-ga
		foreach($args as $key => $val)
		{
			if (strpos($key,"msg_") === 0)
			{
				$this->msgconf[$key] = $val;
			}
		};
		if ($checkbool)
		{
			$this->msgconf["msg_store_sent"] = ($msg_store_sent) ? 1 : 0;
			$this->msgconf["msg_confirm_send"] = ($msg_confirm_send) ? 1 : 0;
			$this->msgconf["msg_move_read"] = ($msg_move_read) ? 1 : 0;
			$this->msgconf["msg_filter_address"] = ($msg_filter_address) ? 1 : 0;
			$this->msgconf["msg_window"] = ($msg_window) ? 1 : 0;
			$this->msgconf["msg_hide_menubar"] = ($msg_hide_menubar) ? 1 : 0;
		};

		$this->save_conf();
		aw_session_set("status_msg",MSG_STATUS_CONFIG_SAVED);
		if ($aft)
		{
			return $this->mk_my_orb($aft);
		}
		return $this->mk_my_orb("configure",array("page" => "general"));
	}


	////
	// !Kuvab olemasoleva voi uue signatuuri muutmis/lisamisvormi
	function edit_signature($args = array())
	{
		$menu = $this->gen_msg_menu(array(
			"activelist" => array("configure","signatures"),
		));
		$this->read_template("edit_signature.tpl");
		extract($args);
		if (isset($id))
		{
			$vars = $this->msgconf["msg_signatures"][$id];
			$title = MSG_EDIT_SIGNATURE;
			$this->vars($vars);
		}
		else
		{
			$title = MSG_NEW_SIGNATURE;
		};
		$this->vars(array(
			"menu" => $menu,
			"title" => $title,
			"reforb" => $this->mk_reforb("submit_signature",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Submitib uue voi olemasoleva signatuuri
	function submit_signature($args = array())
	{
		$siglist = new aw_array($this->msgconf["msg_signatures"]);
		$datablock = array(
			"name" => $args["name"],
			"signature" => $args["signature"],
		);

		$sig_id = $args["id"];
		if (!$sig_id)
		{
			$sig_id = 1;
			while ($siglist->get_at($sig_id))
			{
				$sig_id++;
			}
		}

		$siglist->set_at($sig_id,$datablock);
		$this->msgconf["msg_signatures"] = $siglist->get();
		$this->save_conf();

		$status_msg = $args["id"] ? MSG_STATUS_SIGNATURE_SAVED : MSG_STATUS_SIGNATURE_ADDED;
		aw_session_set("status_msg",$status_msg);

		return $this->mk_my_orb("configure", array("page" => "signature"));
	}
		
	
	////
	// !Kuvab uue accoundi tüübi valimise vormi
	function account_type($args = array())
	{
		return $this->mk_my_orb("configure_pop3",array("id" => "new"));
	}

	////
	//
	function submit_account_type($args = array())
	{
		extract($args);
		return $this->mk_my_orb("configure_pop3",array("id" => "new"));
	}

	////
	// !Kuvab pop3 accoundi haldamise vormi
	function configure_pop3($args = array())
	{
		$menu = $this->gen_msg_menu(array(
			"activelist" => array("configure","accounts"),
		));
		extract($args);
		$pop3conf = $this->msgconf["msg_pop3servers"];
		$this->read_template("pop3conf.tpl");
		$this->vars(array(
			"name" => $pop3conf[$id]["name"],
			"name1" => $pop3conf[$id]["name1"],
			"surname" => $pop3conf[$id]["surname"],
			"address" => $pop3conf[$id]["address"],
			"server" => $pop3conf[$id]["server"],
			"uid" => $pop3conf[$id]["uid"],
			"password" => $pop3conf[$id]["password"],
			"reforb" => $this->mk_reforb("submit_pop3_conf",array("id" => $id)),
			"menu" => $menu,
		));
		return $this->parse();
	}

	////
	// !Handled pop3 konfigureerimisvormist tulnud datat
	function submit_pop3_conf($args = array())
	{
		extract($args);
		$pop3conf = $this->msgconf["msg_pop3servers"];
		$confblock = array(
			"name1" => $name1,
			"surname" => $surname,
			"address" => $address,
			"name" => $name,
			"server" => $server,
			"uid" => $uid,
			"password" => $password,
		);

		if ($id == "new")
		{
			$id = 1;
			while(isset($pop3conf[$id]))
			{
				$id++;
			}
			$status_msg = MSG_STATUS_ACCOUNT_ADDED;
		}

		// kui mailbox oli default, siis hoiame selle info alles
		if ($pop3conf[$id]["default"])
		{
			$confblock["default"] = 1;
		};
		$pop3conf[$id] = $confblock;
		$status_msg = MSG_STATUS_ACCOUNT_SAVED;

		$this->msgconf["msg_pop3servers"] = $pop3conf;
		$this->save_conf();

		aw_session_set("status_msg", $status_msg);
		return $this->mk_my_orb("configure_pop3",array("id" => $id));
	}

	////
	// !Fetchib maili default pop3 serverist
	// argumetns
	// account(int)(optional) - accounti id. millest meili tirida
	function get_mail($args = array())
	{
		extract($args);
		$pop3conf = $this->msgconf["msg_pop3servers"];
		$c = "";
		$accdata = array();
		if (is_array($pop3conf))
		{
			foreach($pop3conf as $accid => $cvalues)
			{
				if ($account == $accid)
				{
					$accdata[] = $cvalues;
				}
				elseif ($cvalues["default"])
				{
					$accdata[] = $cvalues;
				};
			};
		};

		// teeme kasutaja inboxi asukoha kindlaks
		// kuid siia tuleb ka filterdamine vahele panna
		if (!$this->msg_inbox)
		{
			$retval = MSG_INIT;
			$this->init_messenger();
			$retval .= "<a href='$baseurl/?class=messenger'>" . MSG_INIT2 . "</a>";
			return $retval;
		};

		$parent = $this->msg_inbox;

		// tekitame uidl-ide nimekirja
		$uidls = array();
		// bug. What if I move the message into another folder?
		$q = "SELECT uidl FROM messages WHERE folder = '$parent'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$uidls[] = $row["uidl"];
		};
		$acc_count = 0;
		$msg_count = 0;
		foreach($accdata as $acc)
		{
			$acc_count++;
			$msg_count += $this->_get_pop3_messages(array(
				"server" => $acc["server"],
				"uid" => $acc["uid"],
				"password" => $acc["password"],
				"rules" => $this->rules,
				"uidls" => $uidls,
				"parent" => $parent,
			));
		};
		aw_session_set("status_msg",sprintf(MSG_STATUS_MAIL_RECEIVED,$msg_count,$acc_count));
		return $this->mk_my_orb("folder",array());
	}

	////
	// !Fetchib mingist serverist kirjad ja salvestab need aw objektidena
	// argumendid
	// server(string) - serveri nimi
	// uid(string) - kasutaja
	// password(string) - parool
	// parent(int) - objekt, mille parentina uued kirjad registreeritakse
	// uilds(array) - uidl-ide nimekiri.
	function _get_pop3_messages($args = array())
	{
		extract($args);
		$pop3 = get_instance("pop3");
		$awm = get_instance("aw_mail");
		$awf = get_instance("file");
		$msgs = $pop3->get_messages($server,$uid,$password,false,$uidls,$rules);

		$c = 0;
		if (is_array($msgs))
		{
			foreach($msgs as $data)
			{
				$c++;
				// prepare the class for new message
				$awm->clean();
				// we'll parse the message
				$res = $awm->parse_message(array(
					"data" => $data["msg"],
				));

				$body = $awm->get_part(array("part" => "body"));
				// siin checkime ruule:
				$subject = $body["headers"]["Subject"];
				$from = $body["headers"]["From"];
				$cc = $body["headers"]["Cc"];
				$to = $body["headers"]["To"];
				$content = $body["body"];
				$mfrom = $from;
				$processing = true;
				$deliver_to = $parent;
				$priority = 0;
				if (is_array($rules))
				{
					foreach($rules as $rkey => $rval)
					{
						$field = $rval["field"];
						if (!(strpos($$field,$rval["rule"]) === false) && $processing )
						{
							if ($rval["set_priority"])
							{
								$priority = $rval["set_priority_to"];
							}
							$deliver_to = $rval["folder"];
							$processing = false;
						};
					};
				};
				$this->quote($subject);
				$oid = $this->new_object(array(
					"parent" => $deliver_to,
					"name" => $subject,
					"class_id" => CL_MESSAGE),false
				);
				
				// kui kirjal oli attache, siis salvestame need file objektideks
				$dec = 0;
				if ($res > 0)
				{
					for ($i = 1; $i <= $res; $i++)
					{
						$part = $awm->get_part(array("part" => $i));
						if ($part["headers"]["Content-Name"])
						{
							$awf->put(array(
								"store" => "fs",
								"parent" => $oid,
								"filename" => $part["headers"]["Content-Name"],
								"type" => $part["headers"]["Content-Type"],
								"content" => $part["body"],
							));
						}
						else
						{
							// kui me attachi ei salvestanud, siis votame pärast $res-i vaiksemaks,
							// vastasel korral satub messenger segadusse sellest
							$dec++;
						};
					};
				};
				$res = $res - $dec;	
				$uidl = trim($data["uidl"]);
				// registreerime vastse teate
				//$subject = $body["headers"]["Subject"];
				$to = $this->MIME_decode($body["headers"]["To"]);
				$tm = strtotime($body["headers"]["Date"]);
				$header = join("\n",map2("%s: %s",$body["headers"]));
				$this->quote($subject);
				$this->quote($cc);
				$this->quote($from);
				$this->quote($to);
				$this->quote($uidl);
				$this->quote($uidl);
				$this->quote($content);
				$this->quote($header);
				
				// tyypi 2 on välised kirjad. as simpel as that.
				$q = "INSERT INTO messages (id,pri,mfrom,mto,mtargets2,folder,subject,tm,type,uidl,message,headers,num_att)
					VALUES('$oid',$priority,'$from','$to','$cc','$parent','$subject','$tm','2','$uidl','$content','$header','$res')";

				$this->db_query($q);
			};
		};
		return $c;
	}

	////
	// !Kuvab ruulide konfigureerimisvormi
	function rules($args = array())
	{
		$menu = $this->gen_msg_menu(array(
			"title" => MSG_TITLE_RULES,
			"activelist" => array("configure","rules","list"),
		));
		$fields = array(
			"mfrom" => LC_MESSENGER_WHOM,
			"to" => LC_MENUEDIT_TO_WHO,
			"subject" => LC_MENUEDIT_SUBJECT,
			"message" => LC_MENUEDIT_MATTER,
		);

		$folders = $this->_folder_list();
		$this->read_template("rules.tpl");
		$rl = new aw_array($this->rules);
		foreach($rl->get() as $key => $val)
		{
			$this->vars(array(
				"field" => $fields[$val["field"]],
				"endpoint" => $folders[$val["folder"]],
				"rule" => $val["rule"],
				"id" => $key,
				"edit" => $this->mk_my_orb("editrule", array("id" => $key))
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
			"menu" => $menu,
			"reforb" => $this->mk_reforb("submitrules",array()),
		));
		return $this->parse();
	}

	function submitrules($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			$cnt = 0;
			foreach($check as $key => $val)
			{
				$cnt++;
				unset($this->rules[$key]);
			};
			$this->save_conf();
			aw_session_set("status_msg",sprintf(LC_MENUEDIT_ERASED,$cnt));
		};
		return $this->mk_my_orb("rules");
	}

	////
	// !Kuvab ruuli lisamis/muutmisvormi
	function editrule($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
			"title" => MSG_TITLE_RULES,
			"activelist" => array("configure","rules","addrule"),
		));

		$fields = array("mfrom" => LC_MESSENGER_WHOM,
			"message" =>LC_MENUEDIT_MATTER,
			"to" => LC_MENUEDIT_TO_WHO,
			"subject" => LC_MENUEDIT_SUBJECT,
		);

		$folders = $this->_folder_list();

		$this->read_template("editrule.tpl");
		$delivery = "fldr";

		if ($id)
		{
			$title = LC_MESSENGER_CHANGE_RULE;
			$btn_cap = LC_MESSENGER_SAVE;
			$row = $this->rules[$id];
			if (!$row)
			{
				print "no such rule";
				exit;
			};
			$field_index = $row["field"];
			$folder_index = $row["folder"];
			// juhuks, kui tabelis see väli tühjaks on jäänud. Mida muidugi ei tohiks juhtuda
			$delivery = ($row["delivery"]) ? $row["delivery"] : "fldr";
			$rule = $row["rule"];
		}
		else
		{
			$title = LC_MESSENGER_NEW_RULE;
			$bnt_cap = LC_MESSENGER_ADD;
			$field_index = 0;
			$folder_index = $this->msg_inbox;
		};

		$this->vars(array(
			"field_list" => $this->picker($field_index,$fields),
			"folder_list" => $this->picker($folder_index,$folders),
			"folder_checked" => checked($delivery == "fldr"),
			"addr_checked" => checked($delivery == "mail"),
			"rule" => $row["rule"],
			"set_pri_checked" => checked($row["set_priority"]),
			"pri_list" => $this->picker($row["set_priority_to"],array("0","1","2","3","4","5","6","7","8","9")),
			"addr" => $row["addr"],
			"title" => $title,
			"btn_cap" => $btn_cap,
			"menu" => $menu,
			"reforb" => $this->mk_reforb("submitrule",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !submitib uue ruuli
	function submit_rule($args = array())
	{
		extract($args);
		$keyblock = array(
			"field" => $field,
			"rule" => $rule,
			"set_priority" => $set_priority,
			"set_priority_to" => (isset($set_priority)) ? $set_priority_to : "",
			"folder" => $folder,
		);

		$status_msg = LC_MESSENGER_BULE_SAVED;
		if (!$id)
		{
			$id = 1;
			while(isset($this->rules[$id]))
			{
				$id++;
			}
			$status_msg = LC_MESSENGER_RULE_ADDED;
		}

		$this->rules[$id] = $keyblock;
		$this->save_conf();
		aw_session_set("status_msg",$status_msg);
		return $this->mk_my_orb("rules");
	}
		

	// Kuvab uue folderi lisamise vormi
	function new_folder($args = array())
	{
		$menu = $this->gen_msg_menu(array(
			"title" => LC_MESSENGER_NEW_FOLDER,
			"activelist" => array("configure","folders2","newfolder"),
		));
		$this->read_template("addfolder.tpl");
		$this->vars(array(
			"menu" => $menu,
			"reforb" => $this->mk_reforb("submit_new_folder", array(
				"parent" => $this->object_id ? $this->msg_obj["parent"] : $this->user["home_folder"]
			))
		));
		return $this->parse();
	}

	// Loob uue folderi, kui see mingil pohjusel ei eksisteerinud
	function _create_folder($args = array())
	{
		$m = get_instance("menuedit");
		$new = $m->add_new_menu(array(
			"name" => $args["name"],
			"parent" => $args["parent"],
			"type" => ($this->object_id ? MN_CONTENT : MN_HOME_FOLDER_SUB)
		));
		return $new;
	}
	
	function submit_new_folder($args = array())
	{
		extract($args);
		$m = get_instance("menuedit");
		$m->add_new_menu(array(
			"name" => $folder,
			"parent" => $parent,
			"type" => ($this->object_id ? MN_CONTENT : MN_HOME_FOLDER_SUB)
		));
		aw_session_set("status_msg",LC_MESSENGER_FOLDER_ADDED);
		return $this->mk_my_orb("folders");
	}

	// Kuvab folderi valimise vormi
	function set_folder($args = array())
	{
		$mnl = get_instance("menuedit_light");
		$chooser = $mnl->gen_rec_list(array(
			"start_from" => 220,
			"tpl" => "objects/chooser.tpl",
			"start_tpl" => "object",
			"single_tpl" => true,
		));
		$this->read_template("pick_folder.tpl");
		$this->vars(array(
			"variants" => $chooser,
			"reforb" => $this->mk_reforb("submit_folder",array("user" => 1,"type" => $args["type"])),
		));
		return $this->parse();
	}

	function submit_folder($args = array())
	{
		extract($args);
		if ($this->object_id)
		{
			if ($type == "inbox")
			{
				$this->set_object_metadata(array(
					"oid" => $this->object_id,
					"key" => "msg_inbox",
					"value" => $folder
				));
			}
			else
			{
				$this->conf["msg_outbox"] = $folder;
				$this->set_object_metadata(array(
					"oid" => $this->object_id,
					"key" => "conf",
					"value" => $this->conf
				));
			};
		}
		else
		{
			if ($type == "inbox")
			{
				$field = "msg_inbox";
			}
			else
			{
				$field = "msg_outbox";
			};
			$q = "UPDATE users SET $field = '$folder' WHERE uid = '". aw_global_get("uid") . "'";
			$this->db_query($q);
		}
		return $this->mk_my_orb("configure");
	}
			

	////
	// !Kustutab teate
	function delete_message($args = array())
	{
		extract($args);
		$q = "SELECT folder FROM messages WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$folder = $row["folder"];
		aw_session_set("status_msg",LC_MESSENGER_NOTE_ERASED);
		$this->driver->msg_delete($args);
		return $this->mk_my_orb("folder",array("id" => $folder));
	}
	

	//// 
	// !Kustutab teated
	function delete_msgs($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			while(list($k,) = each($check))
			{
				$this->driver->msg_delete(array("id" => $k));
			};
		};
		return $this->mk_my_orb("folders");
	}

	////
	// !Dekodeerib MIME encodingus teate
	function MIME_decode($string)
	{
		$pos = strpos($string,'=?');
		if ($pos === false)
		{
			return $string;
		}
		else
		{
			#quoted_printable_decode($string);
		};

		// take out any spaces between multiple encoded words
		$string = preg_replace('|\?=\s=\?|', '?==?', $string);

		$preceding = substr($string, 0, $pos); // save any preceding text

		$search = substr($string, $pos + 2, 75); // the mime header spec says this is the longest a single encoded word can be
		$d1 = strpos($search, '?');
		if (!is_int($d1)) 
		{
			return $string;
		}


		$charset = substr($string, $pos + 2, $d1);
		$search = substr($search, $d1 + 1);

		$d2 = strpos($search, '?');
		if (!is_int($d2)) 
		{
			return $string;
		}

		$encoding = substr($search, 0, $d2);
		$search = substr($search, $d2+1);

		$end = strpos($search, '?=');
		if (!is_int($end)) 
		{
			return $string;
		}

		$encoded_text = substr($search, 0, $end);
		$rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text) + 6));

		switch ($encoding) 
		{
			case 'Q':
			case 'q':
				$encoded_text = str_replace('_', '%20', $encoded_text);
				$encoded_text = str_replace('=', '%', $encoded_text);
				$decoded = urldecode($encoded_text);

				if (strtolower($charset) == 'windows-1251') 
				{
					$decoded = convert_cyr_string($decoded, 'w', 'k');
				}
				break;

			case 'B':
			case 'b':
				$decoded = urldecode(base64_decode($encoded_text));

				if (strtolower($charset) == 'windows-1251') 
				{
					$decoded = convert_cyr_string($decoded, 'w', 'k');
				}
				break;

			default:
				$decoded = '=?' . $charset . '?' . $encoding . '?' . $encoded_text . '?=';
				break;
			}
		$retval = $preceding . $decoded . $this->MIME_decode($rest);
		return quoted_printable_decode($retval);
	}

	///////////////////////////////////////////////////////////
	// orb add/change functions that enable messenger object creation
	function orb_new($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent, "Lisa messenger");
		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	function orb_submit($arr)
	{
		extract($arr);
		if ($parent)
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_MESSENGER,
				"name" => $name
			));
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function orb_change($arr)
	{
		extract($arr);
		header("Location: ".$this->mk_my_orb("folder", array("messenger_id" => $id)));
		die();
	}

	function mk_my_orb($fun,$arr=array(),$cl_name="",$force_admin = false,$use_orb = array(),$separator = "&")
	{
		if (!isset($arr["messenger_id"]) && $this->object_id)
		{
			$arr["messenger_id"] = $this->object_id;
		}
		return parent::mk_my_orb($fun, $arr, $cl_name, $force_admin, $use_orb, $separator);
	}

	function mk_reforb($fun,$arr = array(),$cl_name = "")
	{
		if (!isset($arr["messenger_id"]) && $this->object_id)
		{
			$arr["messenger_id"] = $this->object_id;
		}
		return parent::mk_reforb($fun, $arr, $cl_name);
	}

	function save_conf()
	{
		if ($this->object_id)
		{
			$this->upd_object(array(
				'oid' => $this->object_id,
				'metadata' => array(
					'conf' => $this->conf,
					'msgconf' => $this->msgconf,
					'msg_inbox' => $this->msg_inbox,
					'rules' => $this->rules
				)
			));
		}
		else
		{
			$uid = aw_global_get('uid');
			// Moodustame konfi pohjal uue xml-i
			// users tabeli messenger vali on tegelikult Deprecated.
			$newconf = aw_serialize($this->conf,SERIALIZE_XML);
			$this->quote($newconf);
			$q = "UPDATE users SET msg_inbox = '$this->msg_inbox',messenger = '$newconf' WHERE uid = '$uid'";
			$this->db_query($q);

			$users = get_instance("users");
			$users->set_user_config(array(
				"uid" => $uid,
				"key" => "messenger",
				"value" => $this->msgconf,
			));
		}
	}

	function get_default_froma($ident)
	{
		return $this->msgconf["msg_pop3servers"][$ident]["address"];
	}

	function get_default_fromn($ident)
	{
		return $this->msgconf["msg_pop3servers"][$ident]["name1"]." ".$this->msgconf["msg_pop3servers"][$ident]["surname"];
	}
};
?>
