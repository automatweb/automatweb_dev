<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/messenger.aw,v 2.25 2001/05/24 20:02:28 cvs Exp $
// messenger.aw - teadete saatmine
// klassid - CL_MESSAGE. Teate objekt

classload("defs");
global $orb_defs;
$orb_defs["messenger"] = "xml";

// sql draiver messengeri jaoks
class msg_sql_driver extends db_connector
{
	function msg_sql_driver()
	{
		$this->db_init();
	}

	// fetchib mingi teate ID järgi
	function msg_get($args = array())
	{
		extract($args);
		$q = "SELECT *,objects.*
			FROM messages 
			LEFT JOIN objects ON (messages.id = objects.oid) WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();

		if ($mark_as_read)
		{
			$q = "UPDATE messages SET status = 1 WHERE id = '$id'";
			$this->db_query($q);
		}
		return $row;
	}

	// otsib teateid
	function msg_search($args = array())
	{
		extract($args);
		$s = join(",",$folders);
		$q = "SELECT *,objects.*
			FROM messages
			LEFT JOIN objects ON (messages.id = objects.oid) 
			WHERE parent IN ($s) AND $field LIKE '%$value%'
			ORDER BY parent";
		$this->db_query($q);
		$rows = array();
		while($row = $this->db_next())
		{
			$rows[$row["oid"]] = $row;
		};
		return $rows;
	}
			

	// Koostab attachide nimekirja
	function msg_list_attaches($args = array())
	{
		extract($args);
		$this->attaches = array();
		$reslist = array();
		$q = "SELECT * FROM msg_objects WHERE message_id = '$id'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->attaches[$row["id"]] = $row;
			$reslist[] = $row["id"];
		};
		// ja tagastab arrays nende nimekirja
		return $reslist;
	}

	function msg_get_attach_by_id($args = array())
	{
		extract($args);
		return $this->attaches[$id];
	}

	////
	// !liigutab mingi messi teise folderisse
	// argumendid:
	// folder(int) - kuhu teade liigutada
	// id(int) - milline teade liigutada
	function msg_move($args = array())
	{
		extract($args);
		$q = "UPDATE objects SET parent = '$folder' WHERE oid = '$id'";
		$this->db_query($q);
	}

	// listib teated. inefficient? maybe
	function msg_list($args = array())
	{
		extract($args);
		$q = sprintf("SELECT objects.*,messages.* FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE class_id = %d AND parent = '$folder'
			ORDER BY created DESC",CL_MESSAGE);
		$this->db_query($q);
		$res = array();
		while($row = $this->db_next())
		{
			$res[] = $row;
		};
		return $res;
	}

	function msg_send($args = array())
	{
		extract($args);
		$t = time();
		$q = "INSERT INTO messages (id,pri,mfrom,reply,mtargets1,mtargets2,folder,subject,tm,type,message)
			VALUES('$oid','$pri','$mfrom','$reply','$mtargets1','$mtargets2','$folder','$subject',$t,1,'$message')";
		$this->db_query($q);
		$retval = true;
		return $retval;
	}

	function msg_delete($args = array())
	{
		extract($args);
		// kustutame teated
		$q = "DELETE FROM messages WHERE id = '$id'";
		$this->db_query($q);
		// kustutame objektitabeli kirje
		$q = "DELETE FROM objects WHERE oid = '$id'";
		$this->db_query($q);
		// kustutame voimalikud attachid
		$q = "DELETE FROM msg_objects WHERE message_id = '$id'";
		$this->db_query($q);
	}

	function count_unread($args = array())
	{
		extract($args);
		$q = "SELECT count(*) AS cnt FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE class_id = " . CL_MESSAGE . " and parent = $folder and messages.status = 0";
		$this->db_query($q);
		$row = $this->db_next();
		return $row["cnt"];
	}

}

classload("menuedit_light","xml");
// siit algab messengeri põhiklass
// kirju tüüpi draft peab ka kuidagi märkima
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
		$newconf = $xml->xml_serialize($conf);
		$this->quote($newconf);
		$q = "UPDATE users SET msg_inbox = '$msg_inbox',messenger = '$newconf' WHERE uid = '" . UID . "'";
		$this->db_query($q);
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
		$this->vars(array("line" => $c,
				"names" => $c2));
		return $this->parse();
			
	}

	////
	// !Joonistab XML-is defineeritud menüü
	function gen_msg_menu($args = array())
	{
		global $basedir;
		$fname = $basedir . "/xml/messenger/menucode.xml";
		$menudef = $this->get_file(array(
					"file" => $basedir . "/xml/messenger/menucode.xml",
				));
		classload("xml");
		$xml = new xml();
		$menudefs = $xml->xml_unserialize(array(
					"source" => $menudef,
		));
		$l1 = $l2 = "";
		$this->read_template("menus.tpl");
		foreach($menudefs as $key => $val)
		{
			$this->vars(array(
					"link" => $val["link"],
					"caption" => $val["caption"],
				));
			$tpl = ($key == $args["l1"]) ? "level1_act" : "level1";
			$l1 .= $this->parse($tpl);
			if (is_array($val["sublinks"]) && ($key == $args["l1"]))
			{
				foreach($val["sublinks"] as $k1 => $v1)
				{
					$this->vars(array(
						"link" => $v1["link"],
						"caption" => $v1["caption"],
					));
					$tpl = ($k1 == $args["l2"]) ? "level2_act" : "level2";
					$l2 .= $this->parse($tpl);
				};
			};
		};
		$this->vars(array(
				"level1" => $l1,
				"level2" => $l2,
		));
		$retval = $this->parse();
		return $retval;
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
				"l1" => "folders",
				"l2" => "flist",
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
			$checked = ($this->msgconf["msg_defaultfolder"] == $key) ? "checked" : "";
			$this->vars(array(
				"id" => $key,
				"name" => $val,
				"total" => ($totals[$key]) ? $totals[$key] : 0,
				"unread" => ($unread[$key]) ? $unread[$key] : 0,
				"checked" => $checked,
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
		$menu = $this->gen_msg_menu(array());
		$this->read_template("mailbox.tpl");

		$inbox = $this->user["msg_inbox"];
		if (!$id)
		{
			$id = $this->msgconf["msg_default_folder"];
		};
		if (!$id)
		{
			$id = $inbox;
		};

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
					$msg["id"] = $msg["oid"];
					$msg["color"] = ($msg["status"]) ? "#FFFFFF" : "#EEEEEE";
					if ($msg["type"] == 2)
					{
						$msg["from"] = htmlspecialchars($msg["mfrom"]);
					}
					else
					{
						$msg["from"] = $msg["modifiedby"];
					};
					$subject = $this->MIME_decode($msg["subject"]);
					$msg["subject"] = "<a href='?class=messenger&action=show&id=$msg[id]'>" . $subject . "</a>";
					$msg["pri"] = ($msg["pri"]) ? $msg["pri"] : 0;
					$msg["cnt"] = $cnt;
					$msg["tm"] = $this->time2date($row["tm"]);
					$this->vars($msg);
					$c .= $this->parse("line");
				};
				$cnt++;
			};
		};

		$this->vars(array(
			"line" => $c,
			"folders_dropdown" => $this->picker($id,$folder_list),
			"active_folder" => $folder,
			"message_count" => verbalize_number($cnt),
			"folder_name" => $folder_name,
			"page" => $cp,
			"reforb" => $this->mk_reforb("move_msgs",array("aft" => $args["id"])),
			"menu" => $menu,
		));
		return $this->parse();
	}
	
	//// 
	// !Loob tühja teate
	// Sel hetkel, kui kasutaja vajutab linki "Uus teade" tehakse koigepealt tema drafts folderisse
	// uus objekt CL_MESSAGE (et oleksid voimalikud valikud a la Salvesta, objektide lisamine, jne...)
	// parent - folderi id, mille alla objekt luua
	// tegelikult see funktsionaalsus kuulub üleüldse draiveri juurde
	// Tagastab äsja loodud objekti ID
	function _create_empty($args = array())
	{
		$oid = $this->new_object(array(
				"parent" => $args["parent"],
				"class_id" => CL_MESSAGE),false); // hetkel ACL-i teate jaoks ei looda
		$q = "INSERT INTO messages (id,draft) VALUES ('$oid',1)";
		$this->db_query($q);
		return $oid;
	}
		
	////
	// !Kuvab uue teate sisestamise/muutmise vormi
	function gen_edit_form($args = array())
	{
		extract($args);
		$menu = $this->gen_msg_menu(array(
				"l1" => "write",
				));
	
		if ($args["reply"])
		{
			$msg = $this->driver->msg_get(array("id" => $reply));
			if ($msg["type"] == 2)
			{
				$msg["etargets"] = $msg["mfrom"];
				$msg["etargets"] = str_replace("\"","",$msg["etargets"]);
			}
			else
			{
				$msg["mtargets"] = $msg["createdby"];
			};
			$msg["subject"] = "Re: " . $this->MIME_decode($msg["subject"]);
			$qchar = $this->conf["quotechar"];
			$msg["message"] = $this->MIME_decode($msg["message"]);
			$msg["message"] = str_replace("\n","\n$qchar",$msg["message"]);
			$msg["message"] = "\n$qchar" . $msg["message"];
			$msg_id = $reply;
		}
		elseif ($args["forward"])
		{
			$msg = $this->driver->msg_get(array("id" => $forward));
			$msg["subject"] = "Fwd: " . $this->MIME_decode($msg["subject"]);
			$qchar = $this->conf["quotechar"];
			$msg["message"] = $this->MIME_decode($msg["message"]);
			$msg["message"] = str_replace("\n","\n$qchar",$msg["message"]);
			$msg["message"] = "\n$qchar" . $msg["message"];
			$msg_id = $forward;
		} elseif (!isset($args["id"]))
		// kui id-d kaasa ei antud, siis järeldame, et tegemist on päris uue kirja kirjutamisega
		{
			// loome uue teate kasutaja drafts folderisse
			// this can be potentionally very, very bad
			$msg_id = $this->_create_empty(array("parent" => $this->conf["msg_draft"]));
			$msg = array();
		};

		// loome nimekirja signatuuridest
		$siglist = "";
		if (is_array($this->conf["signatures"]))
		{	
			// ilge vägistamine käib
			$siglist = array();
			foreach($this->conf["signatures"] as $sigkey => $sigdata)
			{
				$siglist[$sigkey] = $sigdata["name"];
			};
			$siglist = $this->picker($this->conf["defsig"],$siglist);
		};

		$attach = "";	
		$this->read_template("write.tpl");
		for ($i = 1; $i <= $this->msgconf["msg_cnt_att"]; $i++)
		{
			$this->vars(array("anum" => $i));
			$attach .= $this->parse("attach");
		};
	
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
			"prilist" => $this->picker($this->msgconf["msg_default_pri"],array("0","1","2","3","4","5","6","7","8","9")),
			"attach" => $attach,
			"menu" => $menu,
			"reforb" => $this->mk_reforb("post",array()),
		));

		return $this->parse();
	}

	////
	// !Saadab teate
	// id - /opt/ teate id, mida postitatakse, kui see on olemas, siis veotakse teade drafts folderist
	// Siia joutakse nii reply, kui write seest. Reply puhul create_message täidab vormiväljad dataga ainult,
	// enne kui siia satub. Seega, selle funktsiooni seisukohast pole vahet

	function send_message($args = array())
	{
		global $status_msg;
		// Shucks. Aga teist voimalust ei ole nende kättesaamiseks
		global $attach;
		global $attach_name;
		global $attach_type;
		global $udata;
		$this->quote($args);
		extract($args);

		#$etargets = explode(",",$etargets);

		$external = false;
		if ($etargets)
		{
			$external = true;
			classload("aw_mail");
			$awm = new aw_mail();
			$awm->create(array(
					"froma" => $udata["email"],
					"subject" => $subject,
					"to" => $etargets,
					"body" => stripslashes($message),
				));
		};
			

		// koigepealt siis serializeme lisatud failid äsjakirjutatud kirjale külge
		foreach($attach as $idx => $tmpname)
		{
			// opera paneb siia tyhja stringi, mitte none
			if (($tmpname != "none") && ($tmpname))
			{
				 // fail sisse
				$fc = $this->get_file(array(
					"file" => $tmpname,
				));
				$row = array();
				$row["type"] = $attach_type[$idx];
                                $row["class_id"] = CL_FILE;
                                $row["name"] = basename($attach_name[$idx]);
                                $row["content"] = $fc;
				if ($external)
				{
					$awm->fattach(array(
						"path" => $tmpname,
						"name" => basename($attach_name[$idx]),
						"contenttype" => $attach_type[$idx],
					));
				}
				else
				{
                                	$ser = serialize($row);
					$this->quote($ser);
					$this->quote($ser);
					$q = "INSERT INTO msg_objects (message_id,content)
							VALUES('$msg_id','$ser')";
					$this->db_query($q);
				};
			}
		};
		
		if ($external)
		{
			$awm->gen_mail();
			$status_msg = "Meil on saadetud";
			session_register("status_msg");
			return $this->mk_site_orb(array(
				"action" => "folder",
			));
		};
		
		$uid = UID;
		$t = time();
		
		// signatuur loppu
		$message = $args["message"];
		$message .= $this->conf["sigsep"] . "\n";
		$message .= $this->conf["signatures"][$this->conf["defsig"]]["content"];
		$this->quote($message);

		// $mto sisaldab aadresse
		// now, kui seal on komasid
		$mtargets = $args["mtargets"];
		$mtargets2 = $args["mtargets2"];
		
		// strip whitespace, if any
		$mtargets = preg_replace("/\s+?/","",$mtargets);
		// $mtargets2 = preg_replace("/\s+?/","",$mtargets2);

		// first, we need to find out who the message was directed to
		$parts = array();
		$_parts = explode(",",$mtargets);
		foreach($_parts as $key => $val)
		{
			$parts[$val] = $val;
		};
		$gids = explode(",",$mtargets2);
		if ($gids[0])
		{
			$uid_list = array();
			classload("users_user");
			$u = new users_user();
			foreach($gids as $gkey => $gid)
			{
				$parts = array_merge($parts,$u->getgroupmembers($gid));
			};
		};
		// esialgu ei tee me grupinimedega midagi
		
		// explode tagastab alati array
		if (sizeof($parts) == 0)
		{
			$status_msg = "Ühtegi addressaati polnud määratud. Teadet ei saadetud";
			session_register("status_msg");
			// see on ka miski kahtlane action
			return $this->mk_site_orb(array(
				"action" => "folders",
			));
		};
			
		$results = "";
		$cnt["delivered"] = $cnt["failed"] = 0;
		foreach($parts as $idx => $addr)
		{
			$target = $this->get_user(array(
				"uid" => $addr,
			));
			$folder = $target["msg_inbox"];
			// loeme kasutaja ruulid sisse
			$q = "SELECT * FROM msg_rules WHERE uid = '$addr'";
			$this->db_query($q);
			$rules = array();
			$rule_matched = false;
			while(($row = $this->db_next()) && (!$rule_matched))
			{
				$field = $row["field"];
				$text = $row["rule"];
				if (preg_match("/$text/",$args[$field]))
				{
					$delivery = (strlen($row["delivery"]) > 0) ? $row["delivery"] : "fldr";
					$m_addr = $row["addr"];
					$folder = $row["folder"];
					$rule_matched = true;
				};
			};
			if (!$rule_matched)
			{
				$folder = $target["msg_inbox"];
				$delivery = "fldr";
			};
			// loome targeti jaoks uue teate objekti
			if ($args["sendmail"])
			{
				$delivery = "mail";
				$m_addr = $target["email"];
			};
			if ($delivery == "fldr")
			{
				$oid = $this->new_object(array(
					"parent" => $folder,
					"name" => $args["subject"],
					"class_id" => CL_MESSAGE),false);
				$args["mtargets1"] = $mtargets;
				$args["mtargets2"] = $mtargets2;
				$args["folder"] = $folder;
				$args["message"] = $message;
				$args["oid"] = $oid;
				$args["reply"] = ($reply) ? $reply : 0;
				$success = $this->driver->msg_send($args);
			}
			else
			{
				global $udata;
				$from = $udata["email"];
				$message = stripslashes($message);
				mail($m_addr,$subject,$message,"From: $from");
				$success = true;
			};
				
			
			// hetkel me veel ei oska mailidele attache külge panna. So here.
			if ($success || ($delivery == "mail"))
			{
				// kloonime koik attachid
				$q = "SELECT * FROM msg_objects WHERE message_id = '$msg_id'";
				$this->db_query($q);
				while($row = $this->db_next())
				{
					$this->save_handle();
					$msg_id = $row["message_id"];
					$content = $row["content"];
					$c = unserialize($content);
					$content = serialize($c);
					$this->quote($content);
					$this->quote($content);
					$xoid = $row["oid"];
					$q  = "INSERT INTO msg_objects (message_id,content,oid)
						VALUES('$oid','$content','$xoid')";
					$this->db_query($q);
					$this->restore_handle();
				};
				$results .= "* Teade saadetud kasutajale $addr<br>\n";
				$cnt["delivered"]++;
			}
			else
			{
				$results .= "* Saatmine ebaõnnestus. Tundmatu kasutaja? $addr.<br>\n";
				$cnt["failed"]++;
			};
		};
		$results .= sprintf("Teade saadeti %d kasutajale",$cnt["delivered"]);
		// store a copy in outbox
		$status_msg = $results;
		session_register("status_msg");
		return $this->mk_site_orb(array(
			"action" => "folder",
		));
	}

	function search($args = array())
	{
		$menu = $this->gen_msg_menu(array(
				"l1" => "search",
				"l2" => "newsearch",
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
				"l1" => "search",
				));
		//$folder_list = $this->_folder_list();
		if (!is_array($folders))
		{
			$folders = array($folders => $folders);
		};
		$results = $this->driver->msg_search(array(
					"field" => $field,
					"value" => $value,
					"folders" => array_keys($folders),
		));
		$this->read_template("searchresults.tpl");
		$c = "";
		foreach ($results as $id => $contents)
		{
			$contents["tm"] = $this->time2date($row["tm"],3);
			$contents["from"] = $contents["modifiedby"];
			$contents["folder"] = $folder_list[$contents["parent"]];
			$contents["fid"] = $contents["parent"];
			$contents["mid"] = $contents["oid"];
			$this->vars($contents);
			$c .= $this->parse("line");
		};
		$this->vars(array(
				"line" => $c,
				"field" => $field,
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
		$user = $this->get_user(array(
			"uid" => UID,
		));
		$msg = $this->driver->msg_get(array(
			"id" => $id,
			"mark_as_read" => 1,
		));
		$folder_list = $this->_folder_list();
		$this->read_template("message.tpl");
		$vars = array();
		if ($msg["type"] == 2)
		{
			$vars = array(
				"mfrom" => htmlspecialchars($msg["mfrom"]),
				"mtargets" => htmlspecialchars($msg["mto"]),
				"subject" => htmlspecialchars($this->MIME_decode($msg["subject"])),
			);
		}
		else
		{
			$vars = array(
				"mfrom" => $msg["createdby"],
				"mto" => $msg["mto"],
				"mtargets" => $msg["mtargets1"],
				"subject" => $msg["subject"],
			);
		};

		$this->vars($vars);	
		$this->vars(array(
			"tm" => $this->time2date($msg["tm"]),
			"mtargets2" => $msg["mtargets2"],
			"id" => $msg["id"],
			"msg_id" => $id,
			"status" => $msg["status"],
			"message" => nl2br($this->MIME_decode($msg["message"])),
			"del_reforb" => $this->mk_reforb("delete",array("id" => $msg["id"])),
			"reply_reforb" => $this->mk_reforb("reply",array("id" => $msg["id"])),
			"mailbox" => $this->picker($mailbox,$mboxes),
			"mbox_name" => $mboxes[$mailbox],
			"folders_dropdown" => $this->picker($user["msg_inbox"],$folder_list),
		));
		$q = "SELECT * FROM msg_objects WHERE message_id = '$id'";
		$this->db_query($q);
		$att_list = array();
		while($row = $this->db_next())
		{
			$_tmp = unserialize($row["content"]);
			$_tmp["idd"] = $row["id"];
			$att_list[] = $_tmp;
		};
		global $class_defs;
		while(list($k,$v) = each($att_list))
		{
			$data = $class_defs[$v["class_id"]];
			$icon = sprintf("<img src='%s'>",get_icon_url($v["class_id"],"test"));
			$name = $v["name"];
			$this->vars(array("attach" => $data["name"] . $icon . " | " . $name));
			$this->vars(array("id" => $v["idd"]));
			$att .= $this->parse("att");
		};
		$this->vars(array(
			"att" => $att,
			"menu" => $menu,
		));
		// kui kasutaja soovib teateid liigutada, siis teeme seda nüüd
		if ($this->msgconf["msg_move_read"])
		{
			$this->driver->msg_move(array(
					"id" => $id,
					"folder" => $this->msgconf["msg_move_read_folder"],
			));
		}

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

		// vaikimisi signatuurieraldaja
		if (!isset($raw["msg_sigsep"]))
		{
			$raw["msg_sigsep"] = "--";
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
	// FIXME: for some weird reason tundub mulle, et seda funktsiooni kutsutakse
	// iga konfimislehe jaoks mitu korda. Praegu pole aega fixida, märkus
	// tulevikuks
	function configure($args = array())
	{
		extract($args);

		// Menüü koostamine
		$page = ($page) ? $page : "general"; // see viimane kehtib by default
		$menu = $this->gen_msg_menu(array(
				"l1" => "configure",
				"l2" => $page,
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
				if (is_array($conf["signatures"]))
				{
					$this->read_template("signatures.tpl");
					foreach($conf["signatures"] as $signum => $sigdat)
					{
						$cnt++;
						$this->vars(array(
								"signum" => $signum,
								"cnt" => $cnt, 
								"signame" => $sigdat["name"],
								"signature" => nl2br($sigdat["content"]),
								"default" => ($this->conf["defsig"] == $signum) ? "checked" : "",
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
				$pop3conf = $this->msgconf["msg_pop3servers"];
				if (is_array($pop3conf))
				{
					foreach($pop3conf as $accid => $cvalues)
					{
						$this->vars(array(
								"id" => $accid,
								"name" => $cvalues["name"],
								"checked" => ($cvalues["default"]) ? "checked" : "",
								"type" => "POP3", // *pun intended*
						));
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
						"msg_store_sent" => ($conf["msg_store_sent"]) ? "checked" : "",
						"msg_ondelete" => $this->picker($conf["msg_ondelete"],array("delete" => "Kustutakse", "move" => "Viiakse Trash folderisse")),
						"msg_confirm_send" => ($conf["msg_confirm_send"]) ? "checked" : "",
						"msg_quote_list" => $this->picker($conf["msg_quotechar"],array(">" => ">",":" => ":")),
						"msg_default_pri" => $this->picker($conf["msg_default_pri"],array(0,1,2,3,4,5,6,7,8,9)),
						"msg_sigsep" => $conf["msg_sigsep"],
						"msg_cnt_att" => $this->picker($conf["msg_cnt_att"],array("1" => "1","2" => "2","3" => "3","4" => "4","5" => "5")),
						"msg_move_read_folder" => $this->picker($conf["msg_move_read_folder"],$folder_list),
						"msg_move_read" => ($conf["msg_move_read"]) ? "checked" : "",
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
		$this->read_template("account1.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_account_type",array()),
		));
		return $this->parse();
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
				"l1" => "configure",
				"l2" => "accounts",
				));
		extract($args);
		$pop3conf = $this->msgconf["msg_pop3servers"];
		$this->read_template("pop3conf.tpl");
		$this->vars(array(
			"name" => $pop3conf[$id]["name"],
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
					"name" => $name,
					"server" => $server,
					"uid" => $uid,
					"password" => $password,
				);
					
		global $status_msg;
		if ($id == "new")
		{
			$pop3conf[] = $confblock;
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
		return $this->mk_my_orb("configure",array("page" => "accounts"));
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
		classload("pop3");
		$pop3 = new pop3();
		$msgs = $pop3->get_messages($server,$uid,$password,false,$uidls);

		$c = 0;
		if (is_array($msgs))
		{
			foreach($msgs as $data)
			{
				$c++;
				// siin peab olema mingi tsykkel, mis leiab kirjale ntx "Subject" välja
				$msglines = explode("\n",$data["msg"]);
				$header = "";
				$content = "";
				$inheader = true;
				foreach($msglines as $line)
				{
					if (preg_match("/^Subject: (.*)$/",$line,$mt))
					{
						$subject = trim($mt[1]);
					};

					if (preg_match("/^Date: (.*)$/",$line,$mt))
					{
						$tm = strtotime($mt[1]);
					};
					
					if (preg_match("/^From: (.*)$/",$line,$mt))
					{
						$from = trim($mt[1]);
					};
					
					if (preg_match("/^To: (.*)$/",$line,$mt))
					{
						$to = trim($mt[1]);
					};

					if (strlen(trim($line)) == 0)
					{
						$inheader = false;
					};

					if ($inheader)
					{
						$header .= $line;
					}
					else
					{
						$content .= $line;
					};
				};
				$uidl = trim($data["uidl"]);
				// registreerime vastse teate
				$this->quote($subject);
				$this->quote($from);
				$this->quote($to);
				$this->quote($uidl);
				$this->quote($uidl);
				$this->quote($content);
				$this->quote($header);
				$oid = $this->new_object(array(
						"parent" => $parent,
						"name" => $subject,
						"class_id" => CL_MESSAGE),false);
				// tyypi 2 on välised kirjad. as simpel as that.
				$q = "INSERT INTO messages (id,pri,mfrom,mto,folder,subject,tm,type,uidl,message,headers)
					VALUES('$oid','0','$from','$to','$parent','$subject','$tm','2','$uidl','$content','$header')";
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
				"l1" => "rules",
				"l2" => "list",
				));
		$fields = array("mfrom" => "Kellelt",
				"subject" => "Teema",
				"message" => "Sisu",
		);
		$folders = $this->_folder_list();
		$q = "SELECT * FROM msg_rules WHERE uid = '" . UID . "'";
		$this->db_query($q);
		$this->read_template("rules.tpl");
		$c = "";
		while($row = $this->db_next())
		{
			if ($row["delivery"] == "mail")
			{
				$endpoint = $row["addr"];
			}
			else
			{
				$endpoint = "Folder: " . $folders[$row["folder"]];
			};
			$this->vars(array(
				"field" => $fields[$row["field"]],
				"endpoint" => $endpoint,
				"rule" => $row["rule"],
				"id" => $row["id"],
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
				"l1" => "rules",
				"l2" => "addrule",
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
		if ($id)
		{
			$title = "Muuda reeglit";
			$btn_cap = "Salvesta";
			$q = "SELECT * FROM msg_rules WHERE id = '$id' AND uid = '" . UID . "'";
			$this->db_query($q);
			$row = $this->db_next();
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
			"folder_checked" => ($delivery == "fldr") ? "checked" : "",
			"addr_checked" => ($delivery == "mail") ? "checked" : "",
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
		if ($id)
		{
			$q = "UPDATE msg_rules SET field = '$field', rule='$rule',folder='$folder',
				addr = '$addr',delivery = '$delivery'
				WHERE id = '$id'";
			$status_msg = "Reegel salvestatud";
		}
		else
		{
			$uid = UID;
			$q = "INSERT INTO msg_rules (uid,field,rule,folder,addr,delivery)
				VALUES ('$uid','$field','$rule','$folder','$addr','$delivery')";
			$status_msg = "Reegel lisatud";
		};
		$this->db_query($q);
		session_register("status_msg");
		return $this->mk_site_orb(array(
			"action" => "rules",
		));
	}
		

	// Kuvab uue folderi lisamise vormi
	function new_folder($args = array())
	{
		$menu = $this->gen_msg_menu(array(
				"l1" => "folders",
				"l2" => "newfolder",
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
	// !Liigutab teateid
	function move_msgs($args = array())
	{
		extract($args);
		$cnt = 0;
		global $status_msg;
		if (is_array($check))
		{
			while(list($k,) = each($check))
			{
				$cnt++;
				$this->driver->msg_move(array(
					"id" => $k,
					"folder" => $folder,
				));
			};
			$status_msg = "$cnt teadet viidud teise folderisse<br>";
			session_register("status_msg");
		};
		return $this->mk_site_orb(array(
			"action" => "folder",
			"id" => $aft,
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
		if ($pos == false)
		{
			return $string;
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

		return $preceding . $decoded . $this->MIME_decode($rest);
	}

};
?>
