<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/messenger.aw,v 2.60 2001/06/13 14:43:10 duke Exp $
// messenger.aw - teadete saatmine
// klassid - CL_MESSAGE. Teate objekt

classload("defs","menuedit_light","xml","msg_sql");
global $orb_defs;
$orb_defs["messenger"] = "xml";

// sisemine, aw sees saadetud teade
define('MSG_INTERNAL',1);
// väline, ntx pop3 serverist võetud teade
define('MSG_EXTERNAL',2);

// teadete staatused
define('MSG_STATUS_UNREAD',0);
define('MSG_STATUS_READ',1);

// kontaktide vormid
define('CONTACT_FORM',2007);

// siit algab messengeri põhiklass
class messenger extends menuedit_light 
{
	////
	// !Konstruktor
	var $drivername = "sql";
	

	function messenger($args = array())
	{
		$this->db_init();
		$this->tpl_init("messenger");
		$driverclass = "msg_" . $this->drivername . "_driver";

		// $this->driveri kaudu pöördutakse andmebaasidraiveri poole
		$this->driver = new $driverclass;
		
		// juhuks, kui kusagil on vaja kasutada messengeri alamhulka, siis konstruktorile
		// fast argumendi etteandmisega saab vältida rohkem aega nõudvaid operatsioone
		// kuigi tegelikult peaks selleks messengeri klassi hoopis kaheks lööma
		// messenger_user ja messenger. Hiljem jõuab. Ehk.
		if (!$args["fast"])
		{
			$this->user = $this->get_user(array(
				"uid" => UID,
			));
			classload("users");
			$users = new users;
			$this->msgconf = $users->get_user_config(array(
						"uid" => UID,
						"key" => "messenger",
			));
			$this->xml = new xml();
			// igal klassi loomisel toome ka 
			$this->conf = $this->_get_msg_conf(array("conf" => $this->user["messenger"]));
		};
	}
	
	////
	// !Initsialiseerib messengeri folderid kasutaja jaoks
	function init_messenger($args = array())
	{
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
		classload("xml");
		$xml = new xml();
		// Moodustame konfi pohjal uue xml-i
		// users tabeli messenger vali on tegelikult Deprecated.
		$newconf = $xml->xml_serialize($conf);
		$this->quote($newconf);
		$q = "UPDATE users SET msg_inbox = '$msg_inbox',messenger = '$newconf' WHERE uid = '" . UID . "'";
		$this->db_query($q);
	}

	////
	// !And this should be somewhere else as well
	function pick_folder($args = array())
	{
		global $udata;
		$this->read_template("pf.tpl");
		extract($args);
		$att = $this->get_object($attach);
		$hf = $udata["home_folder"];
		$id = ($args["id"]) ? $args["id"] : $hf;
		$object =  $this->get_object($id);
		$q = "SELECT * FROM objects WHERE parent = '$id' AND status = 2 AND class_id = 1 ORDER BY name";
		$this->db_query($q);
		$c = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
					"folder" => "<a href='?class=messenger&action=pick_folder&id=$row[oid]&type=popup&attach=$attach&msg_id=$msg_id'>$row[name]</a>",
			));
			$c .= $this->parse("line");
		};
		$u = "";
		if ($id != $hf)
		{
			$parent = $this->get_object($object["parent"]);
			$this->vars(array("id" => $parent[oid],"aid" => $attach));
			$u = $this->parse("up");
		};
		$this->vars(array(
				"line" => $c,
				"up" => $u,
				"name" => $object["name"],
				"oname" => $att["name"],
				"reforb" => $this->mk_reforb("store_attach",array("msg_id" => $msg_id,"attach" => $attach,"folder" => $id))));
		print $this->parse();
	}

	////
	// !Votab salvestatud attachi vastu
	function store_attach($args = array())
	{
		extract($args);
		classload("file");
		$awf = new file();
		$awf->cp(array("id" => $attach,"parent" => $folder));
		print "<script language='Javascript'> window.close(); </script>";
		exit;
	}

	////
	// !Well, actually, this SHOULD be someplace else. Um. Maybe.
	function pick_users($args = array())
	{
		classload("users");
		$users = new users();
		$users->tpl_init("messenger");
		print $users->gen_plain_list(array("tpl" => "pick_users.tpl"));
	}

	////
	// !And this too should be some place else
	function pick_groups($args = array())
	{
		classload("menuedit_light");
		$ml = new menuedit_light();
		$glist = $ml->gen_rec_list(array(
				"start_from" => 0,
				"type" => "groups",
				"field" => "gid",
				"class_id" => CL_GROUP,
			));
		reset($glist);
		$glist2 = array();
		$this->read_template("pick_groups.tpl");
		$c = "";
		$c2 = "";
		foreach($glist as $key => $val)
		{
			$this->vars(array(
				"gid" => $key,
				"name" => $val,
				"name2" => str_replace("&nbsp;","",$val),
				"members" => "n/a",
			));
			$c .= $this->parse("line");
			$c2 .= $this->parse("names");
		};
		$this->vars(array(
				"line" => $c,
				"names" => $c2
			));
		return $this->parse();
			
	}

	////
	// !Joonistab menüü
	// argumendid:
	// activelist(array), levelite kaupa info selle kohta, millised elemendid aktiivsed on
	// vars(array) - muutujad mida xml-i sisse pannakse
	function gen_msg_menu($args = array())
	{
		extract($args);
		global $basedir;
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$xm->vars($vars);
		$xm->load_from_files(array(
					"xml" => $basedir . "/xml/messenger/menucode.xml",
					"tpl" => $this->template_dir . "/menus.tpl",
				));

		return $xm->create(array(
				"activelist" => $activelist,
			));
	}


	////
	// !Contact manager
	function contacts($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("contacts","list"),
				"vars" => array("folder" => ($folder) ? "folder=$folder" : ""),
				));
		
		$this->read_template("contacts.tpl");
		global $udata;
		$folder = ($args["folder"]) ? $args["folder"] : $udata["home_folder"];
		$this->get_objects_by_class(array(
					"parent" => $folder,
					"class" => CL_CONTACT_GROUP,
				));
		$glist = "";
		$cnt = 0;
		while($row = $this->db_next())
		{
			$cnt++;
			$this->vars(array(
					"jrk" => $cnt,
					"name" => $row["name"],
					"id" => $row["oid"],
					"members" => "n/a",
			));
			$glist .= $this->parse("gline");
		};
		classload("form");
		$f = new form(CONTACT_FORM);
		$f->load(CONTACT_FORM);
		$ids = $f->get_ids_by_name(array("names" => array("name","surname","email","phone")));
		$f->get_entries(array("parent" => $folder));
		$c = "";
		$cnt = 0;
		while($row = $f->db_next())
		{
			$this->vars(array(
					"name" => $row[$ids["name"]] . " " . $row[$ids["surname"]],
					"email" => $row[$ids["email"]],
					"phone" => $row[$ids["phone"]],
					"id" => $row["id"],
					"color" => ($cnt % 2) ? "#EEEEEE" : "#FFFFFF",
			));
			$cnt++;
			$c .= $this->parse("line");
		};
		$this->vars(array(
				"menu" => $menu,
				"line" => $c,
				"gline" => $glist,
		));
		return $this->parse();
	}

	////
	// !Displays a form for editing/adding a contact
	function edit_contact($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"activelist" => ($args["id"]) ? array("contacts") : array("contacts","newcontact"),
			));
		classload("form");
		$f = new form(CONTACT_FORM);
		global $ext,$udata;
		$folder = ($folder) ? $folder : $udata["home_folder"];
		$form = $f->gen_preview(array(
						"id" => CONTACT_FORM,
						"entry_id" => ($args["id"]) ? $args["id"] : "",
						"reforb" => $this->mk_reforb("submit_contact",array("folder" => $folder)),
						"form_action" => "/index.$ext",
					));
		$this->read_template("edit_contact.tpl");
		$this->vars(array(
				"menu" => $menu,
				"form" => $form,
		));
		return $this->parse();
	}

	////
	// !Submits a contact
	function submit_contact($args = array())
	{
		extract($args);
		classload("form");
		$f = new form(CONTACT_FORM);
		// save the form entry, and now .. should we show it?
		$args["id"] = CONTACT_FORM;
		$args["parent"] = $folder;
		$f->process_entry($args);
		global $status_msg;
		$status_msg = ($entry_id) ? "Kontakt on salvestatud" : "Kontakt on lisatud";
		session_register("status_msg");
		if (!$entry_id)
		{
			$entry_id = $f->entry_id;
		};
		$ref = $this->mk_site_orb(array(
					"action" => "edit_contact",
					"id" => $entry_id,
			));
		return $ref;
	}

	////
	// !Kuvab kontaktigrupi muutmis/lisamisvormi
	function edit_contact_group($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"activelist" => ($args["id"]) ? array("contacts") : array("contacts","newgroup"),
			));
		$this->read_template("contact_group.tpl");
		$name = "";
		if ($args["id"])
		{
			$obj = $this->get_object($args["id"]);
			$name = $obj["name"];
		};
		$this->vars(array(
				"name" => $name,
				"menu" => $menu,
				"reforb" => $this->mk_reforb("submit_contact_group",array("id" => $id,"folder" => $folder)),
		));
		return $this->parse();
	}

	////
	// !Submitib kontaktigrupi
	function submit_contact_group($args = array())
	{
		extract($args);
		// kui folder on defineeritud, siis lisame grupi selle alla
		// kui mitte, siis otse kodukataloogi alla
		global $udata;
		$folder = ($folder) ? $folder: $udata["home_folder"];
		if ($args["id"])
		{
			$this->upd_object(array(
						"oid" => $id,
						"name" => $name,
			));
		}
		else
		{
			$id = $this->new_object(array(
						"class_id" => CL_CONTACT_GROUP,
						"name" => $name,
						"parent" => $folder,
			));
		};
		global $status_msg;
		$status_msg = ($args["id"]) ? "Kontaktigrupp on salvestatud" : "Kontaktigrupp on lisatud";
		session_register("status_msg");
		return $this->mk_site_orb(array(
					"action" => "edit_contact_group",
					"id" => $id,
		));
	}

	////
	// !Kuvab kontakti otsimise vormi
	function search_contact($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("contacts","search"),
			));
		$this->read_template("search_contact.tpl");
		classload("form");
		$f = new form(2024);
		global $ext;
		$form = $f->gen_preview(array(
						"id" => 2024,
						"reforb" => $this->mk_reforb("submit_search_contact",array()),
						"form_action" => "/index.$ext",
					));
		$this->vars(array(
				"menu" => $menu,
				"form" => $form,
		));
		return $this->parse();
	}

	////
	// !Performs the actual search
	function submit_search_contact($args = array())
	{
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("contacts","search"),
			));
		$this->read_template("search_contact_res.tpl");
		// FIXME:
		classload("form");
		$f = new form(2024);
		// vaja kuvada otsitulemused. kuidas?
		$this->vars(array(
				"menu" => $menu,
				"form" => "Tulemused",
		));
		return $this->parse();
	}

	function _get_groups_by_level($parent)
	{
		$groups = array();
		$this->get_objects_by_class(array(
					"parent" => $parent,
					"class" => CL_CONTACT_GROUP
		));

		while($row = $this->db_next())
		{
			$groups[$row["oid"]] = array("name" => $row["name"],"parent" => $row["parent"]);
		};
		return $groups;
	}

	function _indent_array($arr,$level)
	{
		while(list($key,$val) = each($arr[$level]))
		{
			$this->flatlist[$key] = str_repeat("&nbsp;",$this->indentlevel*3) . $val;
			if (is_array($arr[$key]))
			{
				$this->indentlevel++;
				$this->_indent_array($arr,$key);
				$this->identlevel--;
			};
		};
	}

	function pick_contacts($args = array())
	{
		$this->read_template("pick_contacts.tpl");

		// siia paneme koikide gruppide flat listi
		$grps = array();

		// ja siia parenti jargi grupeerituna
		$grps_by_parent = array();
		
		// Koostame nimekirja koigist selle kasutaja kontaktigruppidest
		global $udata;
		$fldr = $udata["home_folder"];

		do
		{
			// kysime koik sellel levelil asuvad objektid
			$groups = $this->_get_groups_by_level($fldr);

			// sorteerime nad parentite jargi ära
			// ja paigutame ka flat massiivi
			foreach($groups as $key => $val)
			{
				$grps_by_parent[$val["parent"]][$key] = $val["name"];
				$grps[$key] = $val["name"];
			};
		
			// koostame parentite nimekirja jargmise tsykli jaoks
			$fldr = array_keys($groups);
	
		// kordame nii kaua, kuni yhtegi objekti enam ei leitud
		} while(sizeof($groups) > 0);
		
		// nyyd on dropdowni jaoks vaja koostada idenditud nimekiri koigist objektidest
		$this->flatlist = array($udata["home_folder"] => "sorteerimata");
		$this->indentlevel = 0;
		$this->_indent_array($grps_by_parent,$udata["home_folder"]);
		
		// koostame nimekirja koigist selle formi entritest
		classload("form");
		$f = new form(CONTACT_FORM);
		$f->load(CONTACT_FORM);
		$ids = $f->get_ids_by_name(array("names" => array("name","surname","email","phone")));
		
		// see on selleks, et get_entries arvestaks ka neid kontakte, mis kodukataloogi
		// on salvestatud
		$grps[$udata["home_folder"]] = 1;
	
		$f->get_entries(array("parent" => array_keys($grps)));

		// siia salvestame koik entryd parentite kaupa grupeerituna
		$entries_by_parent = array();

		while($row = $f->db_next())
		{
			$name = sprintf("%s %s <%s>",$row[$ids["name"]],$row[$ids["surname"]],$row[$ids["email"]]);
			$entries[$row["oid"]] = $name;
			$entries_by_parent[$row["parent"]][] = $name;
		};
		
		$cnt = 1;
		$g = "";
		$gl = "";
	
		foreach($grps as $oid => $name)
		{
			$this->vars(array(
					"oid" => $oid,
				));
			if (is_array($entries_by_parent[$oid]))
			{
				foreach($entries_by_parent[$oid] as $key => $gname)
				{
					$this->vars(array(
							"id" => $key,
							"name" => $gname,
						));
					$gl .= $this->parse("gline");
				};
			};
			$this->vars(array("gline" => $gl));
			$gl = "";
			$g .= $this->parse("group");
		};
		/*
		while($row = $this->db_next())
		{
			$groups[$row["oid"]] = $row["name"];
			$this->vars(array(
					"oid" => $row["oid"],
					"name" => $row["name"],
			 	));
			$g .= $this->parse("group");
		};
		*/
		$this->vars(array(
				"groups" => $this->picker(-1,$this->flatlist),
				"group" => $g,
				"hf" => $udata["home_folder"],
			));
		print $this->parse();
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
				classload("file");
				$awf = new file();
				$fdata = $awf->get(array("id" => $row["oid"]));
				header("Content-Type: $fdata[type]");
				header("Content-Disposition: filename=$row[name]");
				print $fdata["file"];
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
	// !This should thread all the messages we have here, overrides the function in menuedit_light
	// but it is not used right now. As far as I can see.
	function _gen_rec_list2($parents = array())
	{
		$this->save_handle();
		$plist = join(",",$parents);
		$q = sprintf("SELECT objects.*,messages.* FROM objects WHERE class_id = '%d' AND parent = %d",
				CL_MESSAGE,
			        $this->folder);

	}

	////
	// !Counts unread messages in a folder
	function count_unread($args = array())
	{
		$inbox = ($this->user["msg_inbox"]) ? $this->user["msg_inbox"] : $this->user["home_folder"];
		$count = $this->driver->count_unread(array("folder" => $inbox));
		return $count;
	}


	////
	// !Koostab folderite nimekirja
	function _folder_list($args = array())
	{
		classload("menuedit_light");
		$mnl = new menuedit_light();
		$folder_list = $mnl->gen_rec_list(array(
			"start_from" => $this->user["home_folder"],
			"add_start_from" => true,
		));
		$folder_list[$this->user["msg_inbox"]] .= " (In)";
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
		if (!isset($this->conf["msg_outbox"]))
		{
			$retval = "Initsialiseerin Messengeri<br>";
			$this->init_messenger();
			$retval .= "<a href='?class=messenger'>Kliki siia</a>";
			return $retval;
		};

		$menu = $this->gen_msg_menu(array(
				"activelist" => array("configure","folders2","flist"),
				));
		$flist = $this->_folder_list();
		$this->read_template("folders.tpl");
		reset($flist);
		$c = "";
		$clsid = CL_MESSAGE;
		$idlist = array();
		while(list($k,) = each($flist))
		{
			$idlist[] = $k;
		};

		if (sizeof($idlist) > 0)
		{
			$idstring = " AND parent IN ( " . join(",",$idlist) . ")";
		}
		else
		{
			$idstring = "";
		};
		$q = "SELECT parent,count(*) AS cnt FROM objects WHERE class_id = '$clsid' $idstring  AND status = 2 GROUP BY parent"; 
		$this->db_query($q);
		$totals = array();
		while($row = $this->db_next())
		{
			$totals[$row["parent"]] = $row["cnt"];
		};
		reset($flist);
		$q = "SELECT parent,count(*) AS cnt FROM objects LEFT join messages ON (objects.oid = messages.id)
			WHERE class_id = '$clsid' AND messages.status = 0 $idstring GROUP BY parent";
		$this->db_query($q);
		$unread = array();
		while($row = $this->db_next())
		{
			$unread[$row["parent"]] = $row["cnt"];
		};
		foreach($flist as $key => $val)
		{
			$this->vars(array(
				"id" => $key,
				"name" => $val,
				"total" => ($totals[$key]) ? $totals[$key] : 0,
				"unread" => ($unread[$key]) ? $unread[$key] : 0,
				"checked" => checked($this->msgconf["msg_defaultfolder"] == $key),
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
		$folder = $id;

		$inbox = $this->user["msg_inbox"];

		$mactive = array();
	
		if (!$id)
		{
			$mactive = array("inbox");
			$id = $this->msgconf["msg_defaultfolder"];
			$folder = $id;
			//$id = $inbox;
		};
		$menu = $this->gen_msg_menu(array(
						"activelist" => $mactive,
					));

		$this->read_template("mailbox.tpl");
		$fld_info = $this->get_object($folder);
		$folder_name = $fld_info["name"];

		if ($id == $inbox)
		{
			$folder_name = "Inbox ($folder_name)";
		};

		$folder_list = $this->_folder_list();

		// Kirjade nimekiri selles folderis
		$msglist = $this->driver->msg_list(array(
			"folder" => $id));

		$c = "";
		$cnt = 0;

		$onpage = $this->msgconf["msg_on_page"];
		if (!$page)
		{
			$page = 1;
		};

		if (!$onpage)
		{
			$onpage = 20;
		};

		$x_from = ($page - 1) * $onpage;
		$x_to = $x_from + $onpage;
		
		
		if (is_array($msglist))
		{
			$pages = (int)(sizeof($msglist) / $onpage);
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
				$pg = "?class=messenger&action=folder&id=$folder&page=$prevpage";
				$this->vars(array("pg" => $pg));
				$prev = $this->parse("prev");
			};

			if ($page < $pages)
			{
				$nextpage = $page + 1;
				$pg = "?class=messenger&action=folder&id=$folder&page=$nextpage";
				$this->vars(array("pg" => $pg));
				$next = $this->parse("next");
			};
				
			
			$cp = "";
			for ($i = 1; $i <= $pages; $i++)
			{
				if ($page == $i)
				{
					$pg = $i;
				}
				else
				{
					$pg = "<a href='?class=messenger&action=folder&id=$folder&page=$i'>$i</a>";
				};
				$this->vars(array("pg" => $pg));
				$cp .= $this->parse("page");
			};
			
			$c = "";
			$cnt = 0;

			foreach($msglist as $key => $msg)
			{
				if (($cnt >= $x_from) && ($cnt <= $x_to))
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
					if ($msg["type"] == MSG_EXTERNAL)
					{
						$from = $this->MIME_decode($msg["mfrom"]);
						if ($this->msgconf["msg_filter_address"])
						{
							$from = preg_replace("/[<|\(|\[].*[>|\)|\]]/","",$from);
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
						$msg["subject"] = "<a href='?class=messenger&action=edit&id=$msg[id]'>" . $subject . "</a>";
					}
					else
					{
						$msg["subject"] = "<a href='?class=messenger&action=show&id=$msg[id]'>" . $subject . "</a>";
					};
					$msg["pri"] = ($msg["pri"]) ? $msg["pri"] : 0;
					$msg["cnt"] = $cnt;
					$msg["tm"] = $this->time2date($msg["tm"],1);
					$this->vars($msg);
					$c .= $this->parse("line");
				};
				$cnt++;
			};
		};

		// active_folder - selle folderi ID
		$this->vars(array(
			"line" => $c,
			"folders_dropdown" => $this->picker($id,$folder_list),
			"active_folder" => $folder,
			"message_count" => verbalize_number($cnt),
			"folder_name" => $folder_name,
			"page" => $cp,
			"prev" => $prev,
			"next" => $next,
			"reforb" => $this->mk_reforb("mailbox_op",array("active_folder" => $args["id"])),
			"menu" => $menu,
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
		global $status_msg;
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
			$status_msg = "Ühtegi teadet polnud märgistatud, seega ei tehtud midagi";
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
					$status_msg = "$cnt teadet kustutati";
					break;

				case "move_to":
					$this->driver->msg_move(array(	
								"id" => $rcheck,
								"folder" => $folder,
								));
					$status_msg = "$cnt teadet viidi teise folderisse";
					break;

				case "mark_as_read":
					$moveto = ($this->msgconf["msg_move_read"]) ? $this->msgconf["msg_move_read_folder"] : "";
					$this->driver->msg_mark(array(
								"id" => $rcheck,
								"folder" => $moveto,
								));
					$status_msg = "$cnt teadet märgiti loetuks";
					break;

				case "mark_as_new":
					$this->driver->msg_mark(array(
								"id" => $rcheck,
								"status" => MSG_STATUS_UNREAD,
								));
					$status_msg = "$cnt teadet märgiti uueks";
					break;

				default:
					$status_msg = "Tundmatu operatsioonikood - $op";
			}
		};
		session_register("status_msg");

		// Ja avame jälle selle folderi, mida me enne vaatasime
		return $this->mk_site_orb(array(
			"action" => "folder",
			"id" => $active_folder,
		));
	}

	
	////
	// !Teeb kasutaja drafts folderisse uue tyhja teate, ning suunab siis ymber
	// edimisvormi
	function create_draft($args = array())
	{
		$oid = $this->new_object(array(
					"parent" => $this->conf["msg_draft"],
					"class_id" => CL_MESSAGE,false)); // hetkel ACL-i teate jaoks ei looda
		$q = "INSERT INTO messages (id,draft) VALUES ('$oid',1)";
		$this->db_query($q);
		return $this->mk_site_orb(array(
						"action" => "edit",
						"id" => $oid,
					));
	}
		
	////
	// !Kuvab uue teate sisestamise/muutmise vormi
	function gen_edit_form($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("write"),
				));
	
		$qchar = $this->msgconf["msg_quotechar"];
		$quote = false;

		if ($args["reply"])
		{
			$msg_id = $reply;
			$msg = $this->driver->msg_get(array("id" => $msg_id));
			$sprefix = "Re: ";
			$quote = true;
			
			if ($msg["type"] == MSG_EXTERNAL)
			{
				$msg["mtargets1"] = $msg["mfrom"];
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
			$sprefix = "Fwd: ";
			$quote = true;
		}
		else
		{
			$msg_id = $args["id"];
			$msg = $this->driver->msg_get(array("id" => $msg_id));
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
			$defident = 0;
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
					"icon" => get_icon_url($row["class_id"],""),
					"name" => $row["name"],
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

		if ($this->msgconf["msg_confirm_send"])
		{
			$send = $this->parse("confirmsend");
		}
		else
		{
			$send = $this->parse("send");
		};

		$this->vars(array(
			"msg_id" => $msg_id,
			"send" => $send,
			"siglist" => $siglist,
			"idlist" => $idlist,
			"prilist" => $this->picker($this->msgconf["msg_default_pri"],array("0","1","2","3","4","5","6","7","8","9")),
			"attach" => $attach,
			"attaches" => $attaches,
			"menu" => $menu,
			"msg_box_width" => ($this->msgconf["msg_box_width"]) ? $this->msgconf["msg_box_width"] : 60,
			"msg_box_height" => ($this->msgconf["msg_box_height"]) ? $this->msgconf["msg_box_height"] : 20,
			"reforb" => $this->mk_reforb("handle",array()),
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
		global $status_msg;
		global $udata;
		extract($args);
	
		if ($post)
		{
			$args = $this->driver->msg_get(array(
				"id" => $id,
			));
		}
		else
		{
			$this->quote($args);
			extract($args);


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
			$this->save_message($args);
		};

		if ($save)
		{
			// bounce back to edit form
			return $this->mk_site_orb(array(
							"action" => "edit",
							"id" => $msg_id,
						));
		};

		if ($preview)
		{
			return $this->mk_site_orb(array(
							"action" => "preview",
							"id" => $msg_id,
						));
		};
		// Kuna me siia joudsime, siis jarelikult on meil vaja meil laiali saata
		// koigepealt splitime mtargetsi komade pealt ära ja eemaldame whitespace
		
		$this->post_message($args);	

		// at this moment we just return from this function call
		return $this->mk_site_orb(array());
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
			$status_msg = "Ühtegi korrektset aadressi ei leitud. Kontrollige üle";
			session_register("status_msg");
			// bounce back to edit form
			return $this->mk_site_orb(array(
							"action" => "edit",
							"id" => $msg_id,
						));
		};

		// now we should be ok, let's separate internal and external addresses
		$externals = $internals = array();

		foreach($targets as $key => $val)
		{
			if (strpos($val,"@"))
			{
				$externals[] = $val;
				$to[] = $val;
			}
			else
			{
				$internals[] = $val;
			};
		};


		$message = $args["message"];
		$this->dequote($message);
		
		if ($args["signature"] != "none")
		{
			// signatuur loppu
			$message .= "\n--\n" . $this->msgconf["msg_signatures"][$args["signature"]]["signature"];
		};

		// kui meil on tarvis saata ka valiseid faile, siis teeme seda siin
		if (sizeof($externals) > 0)
		{
			classload("aw_mail");
			$awm = new aw_mail();

			// leiame kasutatud identiteedi
			if ($identity != "default")
			{
				$froma = $this->msgconf["msg_pop3servers"][$args["identity"]]["address"];
				$fromn = $this->msgconf["msg_pop3servers"][$args["identity"]]["name1"] . " " . $this->msgconf["msg_pop3servers"][$args["identity"]]["surname"];
				if (!$froma)
				{
					print "E-mail address for this account has not been set. Cannot send any messages before you do that";
					exit;
				}
			}
			else
			{
				$froma = $udata["email"];
				$fromn = "";
			};
			$awm->create_message(array(
					"froma" => $froma,
					"fromn" => $fromn,
					"subject" => $subject,
					"to" => join(",",$to),
					"cc" => join(",",$cc),
					"body" => $message,
				));

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
				$this->save_handle();
				$q = "SELECT * FROM files WHERE id = '$row[oid]'";
				$this->db_query($q);
				$row2 = $this->db_next();
				$this->restore_handle();
				$prefix = substr($row2["file"],0,1);
				$fname = SITE_DIR . "/files/$prefix/$row2[file]";
				if (file_exists($fname))
				{
					$awm->fattach(array(	
							"path" => $fname,
							"name" => $row["name"],
							"contenttype" => $row["type"],
					));
				};

			};
	
			// noja lopuks siis, saadame meili minema ka
			$awm->gen_mail();
			$status_msg = "Meil on saadetud";
			session_register("status_msg");
		};

		if (sizeof($internals) < 0)
		{
			// saadame teate sisemistele kasutajatele laiali
			if (1);
			{
                               	$ser = serialize($row);
				$this->quote($ser);
				$this->quote($ser);
				$q = "INSERT INTO msg_objects (message_id,content)
						VALUES('$msg_id','$ser')";
				$this->db_query($q);
			};
		};

		// ja lopuks liigutame ta draftist ära outboxi
		$outbox = $this->conf["msg_outbox"];

		$q = "UPDATE objects SET parent = '$outbox' WHERE oid = '$msg_id'";
		$this->db_query($q);
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
		
		classload("file");
		$awf = new file();
		$count = 0;
	
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
		$q = "UPDATE messages
			SET 
				message = '$message',
				subject = '$subject',
				mtargets1 = '$mtargets1',
				mtargets2 = '$mtargets2',
				pri = '$pri'
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

	function search($args = array())
	{
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("search","newsearch"),
				));
		$flist = $this->_folder_list();
		$this->read_template("search.tpl");
		$c = "";
		foreach($flist as $key => $val)
		{
			$this->vars(array(
					"id" => $key,
					"name" => $val,
				));
			$c .= $this->parse("line");
		};
		$rf =  $this->mk_reforb("do_search",array());
		$this->vars(array(
			"line" => $c,
			"menu" => $menu,
			"reforb" => $rf,
		));
		return $this->parse();
	}

	function do_search($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("search"),
				));
		$folder_list = $this->_folder_list();
		if (!is_array($folders))
		{
			$folders = array($folders => $folders);
		};
		$results = $this->driver->msg_search(array(
					"fields" => array_keys($fields),
					"value" => $value,
					"connector" => $connector,
					"folders" => array_keys($folders),
		));
		$this->read_template("searchresults.tpl");
		$c = "";
		foreach ($results as $id => $contents)
		{
			$contents["tm"] = $this->time2date($contents["tm"],3);
			$contents["from"] = $this->MIME_decode($contents["mfrom"]);
			$contents["folder"] = $folder_list[$contents["parent"]];
			$contents["fid"] = $contents["parent"];
			$contents["mid"] = $contents["oid"];
			$contents["subject"] = $this->MIME_decode($contents["subject"]);
			$this->vars($contents);
			$c .= $this->parse("line");
		};
		$this->vars(array(
				"line" => $c,
				"fields" => join(",",array_keys($fields)),
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
		$menu = $this->gen_msg_menu(array());
		extract($args);
	
		// hm. kas seda GLOBALS["udata"] seest ei saaks?
		$user = $this->get_user(array(
			"uid" => UID,
		));

		// id-d kasutame sellepärast, et integeri jargi otsimine on kiirem, kui 
		// stringi (ntx UIDL)
		$msg = $this->driver->msg_get(array(
			"id" => $id,
		));
		
		$folder_list = $this->_folder_list();

		$this->read_template("message.tpl");
		// koostame attachide nimekirja
		$q = "SELECT * FROM objects WHERE parent = '$id'";
		$this->db_query($q);
		$c = 0;
		$attaches = "";
		while($row = $this->db_next())
		{
			$c++;
			$this->vars(array(
					"aid" => $row["oid"],
					"msg_id" => $id,
					"cnt" => $c,
					"msgid" => $args["id"],
					"icon" => get_icon_url($row["class_id"],""),
					"name" => $row["name"],
				));
			$attaches .= $this->parse("attach");
		};
		
		$vars = array();

		// Sõltuvalt message tüübist on vaja erinevad template väljad täita erinevate väärtustega
		switch($msg["type"])
		{
			case MSG_EXTERNAL:
				// replace < and > in the fields with correspondening HTML entitites
				$from = $msg["mfrom"];
				$cc = $msg["mtargets2"];
				$from = $this->MIME_decode($from);
				$subject = $this->MIME_decode($msg["subject"]);
				$vars = array(
					"mfrom" => htmlspecialchars($from),
					"mtargets1" => htmlspecialchars($msg["mto"]),
					"subject" => htmlspecialchars($subject),
					"mtargets2" => htmlspecialchars($cc),
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
		$message = ereg_replace("((ftp://)|(http://))(([[:alnum:]]|[[:punct:]])*)", "<a href=\"\\0\" target='_new'>\\0</a>",$message);

		$message = nl2br($this->MIME_decode($message));
		$this->vars(array("msg_id" => $args["id"]));
		
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
			"folders_dropdown" => $this->picker($user["msg_inbox"],$folder_list),
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
		classload($class);
		$t = new $class;
		$data["att_id"] = $id;
		print $t->$action($data);
	}

	////
	// !Moodustab kasutaja messengeri konfiguratsiooni, asendades puuduvad väärtused vajadusel defaultidega
	// conf - users tabelist loetud "messenger" välja sisu
	function _get_msg_conf($args = array())
	{
		extract($args);
		classload("xml");
		$xml = new xml();
		$raw = $xml->xml_unserialize(array(
						"source" => $args["conf"],
					));

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
			$raw["msg_draft"] = $this->user["msg_inbox"];
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
				"activelist" => array("configure",$page),
				));

		$vars = array();
		

		// messengeri nimelises väljas kasutaja tabelis hoiakse kasutaja mailboxi konfiguratsiooni
		// xml-is, if you want to know.

		// kui kasutajal mailboxi konff puudub, siis xml_unserialize tagastab tühja array
		$conf = $this->_get_msg_conf(array(
							"conf" => $this->user["messenger"],
						));

		$folder_list = $this->_folder_list();
		switch($page)
		{
			case "folders":
				$tpl = "conf_folders.tpl";
				$vars = array(
						"inbox_select" => $this->picker($this->user["msg_inbox"],$folder_list),
						"outbox_select" => $this->picker($conf["msg_outbox"],$folder_list),
						"trash_select" => $this->picker($conf["msg_trash"],$folder_list),
						"draft_select" => $this->picker($conf["msg_draft"],$folder_list),
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
						$cnt++;
						$this->vars(array(
								"signum" => $signum,
								"cnt" => $cnt, 
								"signame" => $sigdat["name"],
								"signature" => nl2br($sigdat["signature"]),
								"default" => checked($this->conf["defsig"] == $signum),
							));
						$siglist .= $this->parse("sig");
					};
					$this->vars(array("sig" => $siglist));
					$siglist = $this->parse();
				};
				$vars = array("siglist" => $siglist,
						"sigcount" => verbalize_number($cnt));
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
								"defcheck" => checked($this->msgconf["msg_default_account"] == $cnt),
								"checked" => checked($cvalues["default"]),
								"type" => "POP3", // *pun intended*
						));
						$cnt++;
						$c .= $this->parse("line");
					};
				};
				$acclist = $c;
				$vars = array("line" => $acclist,
						"aftpage" => "accounts");
				break;


			default:
				$tpl = "conf_general.tpl";
				$conf = $this->msgconf;
				$vars = array(
						"msg_on_page" => $this->picker($conf["msg_on_page"],array("10" => "10", "20"=>"20","30"=>"30","40"=>"40")),
						"msg_store_sent" => checked($conf["msg_store_sent"]),
						"msg_ondelete" => $this->picker($conf["msg_ondelete"],array("delete" => "Kustutakse", "move" => "Viiakse Trash folderisse")),
						"msg_confirm_send" => checked($conf["msg_confirm_send"]),
						"msg_filter_address" => checked($conf["msg_filter_address"]),
						"msg_quote_list" => $this->picker($conf["msg_quotechar"],array(">" => ">",":" => ":")),
						"msg_default_pri" => $this->picker($conf["msg_default_pri"],array(0,1,2,3,4,5,6,7,8,9)),
						"msg_cnt_att" => $this->picker($conf["msg_cnt_att"],array("1" => "1","2" => "2","3" => "3","4" => "4","5" => "5")),
						"msg_move_read_folder" => $this->picker($conf["msg_move_read_folder"],$folder_list),
						"msg_move_read" => checked($conf["msg_move_read"]),
						"msg_font" => $this->picker($conf["msg_font"],array("courier" => "Courier","arial" => "Arial","Tahoma" => "Tahoma")),
						"msg_font_size" => $this->picker($conf["msg_font_size"],array("1" => "1","2" => "2", "3" => "3","+2" => "+2", "+3" => "+3")),
						"msg_box_width" => ($conf["msg_box_width"]) ? $conf["msg_box_width"] : 60,
						"msg_box_height" => ($conf["msg_box_height"]) ? $conf["msg_box_height"] : 20,
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
		// loeme vana konffi sisse
		extract($args);
		classload("users");
		$users = new users();
		// default_acc peaks sisaldama koigi nende accountide id-sid, millelt
		// get mail id-sid peaks kysima
		if (is_array($default_acc))
		{
			$serverlist = $this->msgconf["msg_pop3servers"];
			foreach($serverlist as $key => $val)
			{
				$serverlist[$key]["default"] = $default_acc[$key];
			};
			$this->msgconf["msg_pop3servers"] = $serverlist;
		};
		if ($aftpage)
		{
			$ret = $this->mk_my_orb("configure",array("page" => $aftpage));

		}
		else
		{
			$ret = $this->mk_my_orb("folders",array());	
		};
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
		};
		$users->set_user_config(array(
						"uid" => UID,
						"key" => "messenger",
						"value" => $this->msgconf,
		));
		global $status_msg;
		$status_msg = "Konfiguratsioonimuudatused on salvestatud";
		session_register("status_msg");
		return $ret;
		
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
			$title = "Muuda signatuuri";
			$this->vars($vars);
		}
		else
		{
			$title = "Uus signatuur";
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
		$siglist = $this->msgconf["msg_signatures"];
		if (!is_array($siglist))
		{
			$siglist = array();
		};
		$datablock = array(
				"name" => $args["name"],
				"signature" => $args["signature"],
			);
		if (isset($args["id"]))
		{
			$siglist[$args["id"]] = $datablock;
		}
		else
		{
			$siglist[] = $datablock;
		};
		$this->msgconf["msg_signatures"] = $siglist;
		classload("users");
		$users = new users();
		$users->set_user_config(array(
						"uid" => UID,
						"key" => "messenger",
						"value" => $this->msgconf,
					));
		global $status_msg;
		$status_msg = (isset($args["id"])) ? "Signatuur on salvestatud" : "Signatuur on lisatud";
		session_register("status_msg");
		$ref = $this->mk_site_orb(array(
				"action" => "configure",
				"page" => "signature",
		));
		return $ref;
	}
		
	
	// see on old style kood ja kuulub varsti korvaldamisele
	function _submit_configure($args = array())
	{
		// blargh. bad thing on see, et selle funktsiooni peab taiesti ymber
		// kirjutama. ja seda selleks, et ta kasutaks users klassi
		// set_user_config ja get_user_config funktsioone
		extract($args);
		$knownfields = array("msg_on_page","store_sent","ondelete","confirm_send",
					"msg_outbox","msg_trash","msg_draft","signatures","defsig",
					"quotechar","default_pri","sigsep","cnt_att","default_folder");

		$bool = array("foo","confirm_send","store_sent");
		$bool = array_flip($bool);
		reset($bool);

		if ($page == "accounts")
		{
			classload("users");
			$users = new users();
			$pop3conf = $users->get_user_config(array(
								"uid" => UID,
								"key" => "pop3servers",
						));
			foreach($pop3conf as $key => $val)
			{
				if ($key == $default)
				{
					$pop3conf[$key]["default"] = 1;
				}
				else
				{
					unset($pop3conf[$key]["default"]);
				};
			};

			$users->set_user_config(array(
						"uid" => UID,
						"key" => "pop3servers",
						"value" => $pop3conf,
						));
		}
		else
		{
	
			// Salvestamisel küsime kõigepealt messengeri esialgse konfiguratsiooni	
			$conf = $this->_get_msg_conf(array(
								"conf" => $this->user["messenger"],
							));
			$_aq = "";
			if ($msg_inbox)
			{
				// see on ainuke muutuja, mida kasutajatabelis hoitakse
				$_aq = "msg_inbox = '$msg_inbox',";
			};

			reset($knownfields);
	
			// overraidime vanas konfis olnud väärtused vormist tulnutega
			foreach($knownfields as $field)
			{
				if ((isset($args[$field])) || ($bool[$field]))
				{
					$conf[$field] = ($args[$field]) ? $args[$field] : 0;
				};
			};

			// kustutame märgitud signatuurid
			if (is_array($delsig))
			{
				foreach($delsig as $key => $val)
				{
					// Kontrollime just in case
					if (isset($conf["signatures"][$key]))
					{
						unset($conf["signatures"][$key]);
					};
				};
			};
	
			// kas lisati uus signa?
			// lisame ainult siis, kui nii nimi, kui ka sisu on määratud
			if ($new_signature && $new_signame)
			{
				$conf["signatures"][] = array("name" => $new_signame,"content" => $new_signature);
			};
			classload("xml");
			$xml = new xml();
			// Moodustame konfi pohjal uue xml-i
			$newconf = $xml->xml_serialize($conf);
			$this->quote($newconf);
			$q = "UPDATE users SET $_aq messenger = '$newconf' WHERE uid = '" . UID . "'";
			$this->db_query($q);
		};
		global $status_msg;
		$status_msg = "Konfiguratsiooni muudatused on salvestatud";
		session_register("status_msg");
		return $this->mk_site_orb(array(
			"action" => "configure",
			"page" => $page,
		));
	}

	////
	// !Kuvab uue accoundi tüübi valimise vormi
	function account_type($args = array())
	{
		extract($args);
		//$this->read_template("account1.tpl");
		//$this->vars(array(
		//	"reforb" => $this->mk_reforb("submit_account_type",array()),
		//));
		//return $this->parse();
		return $this->mk_site_orb(array(
				"action" => "configure_pop3",
				"id" => "new",
			));
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
		$conf = $this->msgconf;
		$pop3conf = $conf["msg_pop3servers"];
		$confblock = array(
					"name1" => $name1,
					"surname" => $surname,
					"address" => $address,
					"name" => $name,
					"server" => $server,
					"uid" => $uid,
					"password" => $password,
				);
					
		global $status_msg;
		if ($id == "new")
		{
			$pop3conf[] = $confblock;
			$id = sizeof($pop3conf) - 1;
			$status_msg = "Konto on lisatud";
		}
		else
		{
			// kui mailbox oli default, siis hoiame selle info alles
			if ($pop3conf[$id]["default"])
			{
				$confblock["default"] = 1;
			};
			$pop3conf[$id] = $confblock;
			$status_msg = "Konto muudatused on salvestatud";
		};
		classload("users");
		$users = new users();
		$conf["msg_pop3servers"] = $pop3conf;
		$users->set_user_config(array(
					"uid" => UID,
					"key" => "messenger",
					"value" => $conf,
		));
		session_register("status_msg");
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
		classload("users");
		$users = new users();
		$rules = $users->get_user_config(array(
						"uid" => UID,
						"key" => "rules",
		));
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
		$q = "SELECT msg_inbox FROM users WHERE uid = '" . UID . "'";
		$this->db_query($q);
		$row = $this->db_next();

		if (!$row["msg_inbox"])
		{
			print "inbox does not exist. aborting";
			exit;
		};

		$parent = $row["msg_inbox"];

		// tekitame uidl-ide nimekirja
		$uidls = array();
		$q = "SELECT uidl FROM messages WHERE folder = '$parent'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$uidls[] = $row["uidl"];
		};
		#print "getting mail from " . $accdata["server"];
		global $status_msg;
		$acc_count = 0;
		$msg_count = 0;
		foreach($accdata as $acc)
		{
			$acc_count++;
			$msg_count += $this->_get_pop3_messages(array(
						"server" => $acc["server"],
						"uid" => $acc["uid"],
						"password" => $acc["password"],
						"rules" => $rules,
						"uidls" => $uidls,
						"parent" => $parent,
					));
		};
		$status_msg = "Received $msg_count messages from $acc_count accounts";
		session_register("status_msg");
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
		classload("pop3","aw_mail","file");
		$pop3 = new pop3();
		$awm = new aw_mail();
		$awf = new file();
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
				$subject = $this->MIME_decode($body["headers"]["Subject"]);
				$from = $this->MIME_decode($body["headers"]["From"]);
				$cc = $this->MIME_decode($body["headers"]["Cc"]);
				$mfrom = $from;
				$content = $this->MIME_decode($body["body"]);
				$processing = true;
				$deliver_to = $parent;
				if (is_array($rules))
				{
					foreach($rules as $rkey => $rval)
					{
						$field = $rval["field"];
						if (!(strpos($$field,$rval["rule"]) === false) && $processing )
						{
							$deliver_to = $rval["folder"];
							$processing = false;
						};
					};
				};
				$this->quote($subject);
				$oid = $this->new_object(array(
						"parent" => $deliver_to,
						"name" => $subject,
						"class_id" => CL_MESSAGE),false);
				

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
						};
					};
				};
		
				$uidl = trim($data["uidl"]);
				// registreerime vastse teate
				//$subject = $body["headers"]["Subject"];
				$to = $this->MIME_decode($body["headers"]["To"]);
				$tm = strtotime($body["headers"]["Date"]);
				$header = join("\n",map2("%s: %s",$body["headers"]));
				$this->quote($subject);
				$this->quote($from);
				$this->quote($to);
				$this->quote($uidl);
				$this->quote($uidl);
				$this->quote($content);
				$this->quote($header);
				
				// tyypi 2 on välised kirjad. as simpel as that.
				$q = "INSERT INTO messages (id,pri,mfrom,mto,mtargets2,folder,subject,tm,type,uidl,message,headers,num_att)
					VALUES('$oid','0','$from','$to','$cc','$parent','$subject','$tm','2','$uidl','$content','$header','$res')";

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
				"activelist" => array("configure","rules","list"),
				));
		$fields = array("mfrom" => "Kellelt",
				"subject" => "Teema",
				"message" => "Sisu",
		);
		classload("users");
		$users = new users();
		$rules = $users->get_user_config(array(
						"uid" => UID,
						"key" => "rules",
		));
		$folders = $this->_folder_list();
		$this->read_template("rules.tpl");
		if (is_array($rules))
		{
			foreach($rules as $key => $val)
			{
				$this->vars(array(
					"field" => $fields[$val["field"]],
					"endpoint" => $folders[$val["folder"]],
					"rule" => $val["rule"],
					"id" => $key,
				));
				$c .= $this->parse("line");
			};
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
		global $status_msg;
		extract($args);
		if (is_array($check))
		{
			$cnt = 0;
			$todelete = array();
			foreach($check as $key => $val)
			{
				$cnt++;
				$todelete[] = $key;
			};
			$status_msg = "Kustutati $cnt reeglit";
			session_register("status_msg");
		};
		$q = "DELETE FROM msg_rules WHERE id IN (" . join(",",$todelete) . ")";
		$this->db_query($q);
		return $this->mk_site_orb(array(
			"action" => "rules",
		));
	}

	////
	// !Kuvab ruuli lisamis/muutmisvormi
	function addrule($args = array())
	{
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("configure","rules","addrule"),
				));
		$fields = array("mfrom" => "Kellelt",
				"message" => "Sisu",
				"subject" => "Teema",
		);
		$folders = $this->_folder_list();
		extract($args);
		$user = $this->get_user(array(
			"uid" => UID,
		));
		$this->read_template("newrule.tpl");
		$delivery = "fldr";
		classload("users");
		$users = new users();
		$oldrules = $users->get_user_config(array(
						"uid" => UID,
						"key" => "rules",
		));
		if (isset($id))
		{
			$title = "Muuda reeglit";
			$btn_cap = "Salvesta";
			$row = $oldrules[$id];
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
			$title = "Uus reegel";
			$bnt_cap = "Lisa";
			$field_index = 0;
			$folder_index = $user["msg_inbox"];
		};
		$this->vars(array(
			"field_list" => $this->picker($field_index,$fields),
			"folder_list" => $this->picker($folder_index,$folders),
			"folder_checked" => checked($delivery == "fldr"),
			"addr_checked" => checked($delivery == "mail"),
			"rule" => $row["rule"],
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
		global $status_msg;
		$this->quote($args);
		extract($args);
		classload("users");
		$users = new users();
		$oldrules = $users->get_user_config(array(
						"uid" => UID,
						"key" => "rules",
		));
		if (isset($id))
		{
			$keyblock = array(
					"field" => $field,
					"rule" => $rule,
					"folder" => $folder,
			);
			$oldrules[$id] = $keyblock;
			$status_msg = "Reegel salvestatud";
		}
		else
		{
			$keyblock = array(
					"field" => $field,
					"rule" => $rule,
					"folder" => $folder,
			);
			$oldrules[] = $keyblock;
			$status_msg = "Reegel lisatud";
		};
		$users->set_user_config(array(
						"uid" => UID,
						"key" => "rules",
						"value" => $oldrules,
		));
		session_register("status_msg");
		return $this->mk_site_orb(array(
			"action" => "rules",
		));
	}
		

	// Kuvab uue folderi lisamise vormi
	function new_folder($args = array())
	{
		$menu = $this->gen_msg_menu(array(
				"activelist" => array("configure","folders2","newfolder"),
				));
		$this->read_template("addfolder.tpl");
		$parent = $this->user["home_folder"];
		$this->vars(array("parent" => $parent,
				"menu" => $menu));
		return $this->parse();
	}

	// Loob uue folderi, kui see mingil pohjusel ei eksisteerinud
	function _create_folder($args = array())
	{
		classload("menuedit");
		$m = new menuedit();
		$new = $m->add_new_menu(array(
			"name" => $args["name"],
			"parent" => $args["parent"],
		));
		return $new;
	}
	
	function submit_new_folder($args = array())
	{
		extract($args);
		classload("menuedit");
		$m = new menuedit;
		$m->add_new_menu(array(
			"name" => $folder,
			"parent" => $parent,
		));
		global $status_msg;	
		$status_msg = "Folder on lisatud";
		session_register("status_msg");
		return $this->mk_site_orb(array(
			"action" => "folders",
		));
	}

	// Kuvab folderi valimise vormi
	function set_folder($args = array())
	{
		classload("menuedit_light");
		$mnl = new menuedit_light();
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
		if ($type == "inbox")
		{
			$field = "msg_inbox";
		}
		else
		{
			$field = "msg_outbox";
		};
		$q = "UPDATE users SET $field = '$folder' WHERE uid = '". UID . "'";
		$this->db_query($q);
		return $this->mk_site_orb(array(
			"action" => "configure",
		));
	}
			

	////
	// !Kustutab teate
	function delete_message($args = array())
	{
		extract($args);
		global $status_msg;
		$q = "SELECT folder FROM messages WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$folder = $row["folder"];
		$status_msg = "Teade kustutati";
		session_register("status_msg");
		$this->driver->msg_delete($args);
		return $this->mk_site_orb(array(
			"action" => "folder",
			"id" => $folder,
		));
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
		return $this->mk_site_orb(array(
			"action" => "folders",
		));
	}

	////
	// !Dekodeerib MIME encodingus teate
	function MIME_decode($string)
	{
		$pos = strpos($string,'=?');
		if ($pos === false)
		{
			return quoted_printable_decode($string);
		};

		// take out any spaces between multiple encoded words
		$string = preg_replace('|\?=\s=\?|', '?==?', $string);

		$preceding = substr($string, 0, $pos); // save any preceding text

		$search = substr($string, $pos + 2, 75); // the mime header spec says this is the longest a single encoded word can be
	        $d1 = strpos($search, '?');
		if (!is_int($d1)) {
			return $string;
		}

		$charset = substr($string, $pos + 2, $d1);
		$search = substr($search, $d1 + 1);

		$d2 = strpos($search, '?');
		if (!is_int($d2)) {
			return $string;
		}

		$encoding = substr($search, 0, $d2);
		$search = substr($search, $d2+1);

		$end = strpos($search, '?=');
		if (!is_int($end)) {
			return $string;
		}

		$encoded_text = substr($search, 0, $end);
		$rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text) + 6));

		switch ($encoding) {
			case 'Q':
			case 'q':
				$encoded_text = str_replace('_', '%20', $encoded_text);
				$encoded_text = str_replace('=', '%', $encoded_text);
				$decoded = urldecode($encoded_text);

				if (strtolower($charset) == 'windows-1251') {
					$decoded = convert_cyr_string($decoded, 'w', 'k');
				}
				break;

			case 'B':
			case 'b':
				$decoded = urldecode(base64_decode($encoded_text));

				if (strtolower($charset) == 'windows-1251') {
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

};
?>
