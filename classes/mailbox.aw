<?php
	load_vcl("table");
	classload("pop3");
	classload("smtp");

	session_register("mail_folders");

	// objects.status values for e-mails
	// 0 - deleted
	// 1 - read
	// 2 - unread
	// 4 - sent
	// 8 - unsent (draft)
	define(DELETED, 0);
	define(READ, 1);
	define(UNREAD, 2);
	define(SENT, 4);
	define(UNSENT, 8);
	define(REPLIED, 16);
	define(FORWARDED, 32);

	// sent / unsent ja read / unread v6ib kombineerida

	class mailbox extends aw_template
	{
		function mailbox()
		{
			$this->db_init();
			$this->tpl_init("mailbox");

			// read logged in user config
			global $uid;
			$this->conf = unserialize($this->db_fetch_field("SELECT mailbox_conf FROM users WHERE uid = '$uid'","mailbox_conf"));
		}

		function msg_list($parent)
		{
			if (!$parent)
				$parent = $this->conf[inbox_id];

			$this->read_template("msg_list.tpl");

			global $sortby;
			$t = new aw_table(array("prefix" => "mailbox","imgurl" => $baseurl."/vcl/img","self" => $PHP_SELF, "sortby" => $sortby));
			$t->parse_xml_def($GLOBALS["basedir"]."/xml/mailbox.xml");

			if ($parent == $this->conf[drafts_id])
				$type = "change_mail";
			else
				$type = "show_mail";

			$this->db_query("SELECT mailbox.*,objects.name as subject, objects.created as recieved, objects.status as status FROM mailbox 
											 LEFT JOIN objects ON objects.oid = mailbox.id 
											 WHERE mailbox.uid = '".$GLOBALS["uid"]."' AND objects.parent=$parent AND objects.status != ".DELETED);

			while ($row = $this->db_next())
			{
				if ($row[sender_name] != "")
					$row[sender] = $row[sender_name];
				if ($row[reciever_name] != "")
					$row[reciever] = $row[reciever_name];

				$row[sender] = htmlspecialchars($row[sender]);
				$row[reciever] = htmlspecialchars($row[reciever]);

				if ($row[subject] == "")
					$row[subject] = str_repeat("&nbsp;", 20);

				$ss = "";
				if ($row[status] & UNREAD) 
				{
					$img = "mail_new.gif";
					$ss = "setRead(\"im_$row[id]\");";
				}
				else
				if ($row[status] & REPLIED) $img = "mail_replied.gif"; else
				if ($row[status] & FORWARDED) $img = "mail_forwarded.gif"; else
				$img = "mail.gif";

				$row[sender] = "<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td class='fgtext' ><span ID='surround_".$row[id]."_1'><span ID='l_".$row[id]."_1' CLASS='abs'><img src='/images/transa.gif' width=1 height=1><table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td class='sels' >&nbsp;".$row[sender]."</td></tr></table></span>&nbsp;".$row[sender]."</span></td></tr></table>";

				$row[subject] = "<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td class='fgtext'><span ID='surround_".$row[id]."_2'><span ID='l_".$row[id]."_2' CLASS='abs'><img src='/images/transa.gif' width=1 height=1><table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td class='sels'>&nbsp;<a onClick='setSel($row[id]);$ss' class='mlink' target=message  href='mail.".$GLOBALS["ext"]."?type=$type&id=".$row[id]."'>".$row[subject]."</a></td></tr></table></span>&nbsp;<a  target=message onClick='setSel($row[id]);$ss' href='mail.".$GLOBALS["ext"]."?type=$type&id=".$row[id]."'>".$row[subject]."</a></span></td></tr></table>";

				$row[reciever] = "<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td class='fgtext'><span ID='surround_".$row[id]."_3'><span ID='l_".$row[id]."_3' CLASS='abs'><img src='/images/transa.gif' width=1 height=1><table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td class='sels'>&nbsp;".$row[reciever]."</td></tr></table></span>&nbsp;".$row[reciever]."</span></td></tr></table>";
				
				$row[status] = "<a onClick='setSel($row[id]);$ss' target=message href='mail.".$GLOBALS["ext"]."?type=$type&id=".$row[id]."'><img src='/images/$img' name='im_$row[id]' border=0></a>";
				$row[check] = "<input type='checkbox' NAME='ch_".$row[id]."' VALUE=1>";
				$t->define_data($row);

				$this->vars(array("id" => $row[id]));
				$spans.=$this->parse("SPANS");
			}

			$t->sort_by(array("field" => $sortby));

			$this->vars(array("table" => $t->draw(),"SPANS" => $spans, "folders" => $this->mk_folder_sel($parent),"parent"=>$parent));

			return $this->parse();
		}

		function mk_folder_sel($parent)
		{
			// later, cache these, since we can do that easily without acl
			$this->menucache = array();
			// show all folders that belong to the logged in user
			// and are not deleted
			$this->db_query("SELECT objects.oid as oid, 
															objects.parent as parent,
															objects.name as name
												FROM objects 
												WHERE objects.class_id = 27 AND objects.status != 0 AND objects.last = '".$GLOBALS["uid"]."'
												ORDER BY objects.parent,objects.oid");
			while ($row = $this->db_next())
			{
				// now build a data structure of the menus in an easily accessible mode
				// and also include with each menu it's acl entry
				$this->menucache[$row[parent]][] = $row;
			}
			$this->ret = array();
			$this->rec_folder_sel(1,"");
			
			return $this->option_list($parent,$this->ret);
		}

		function rec_folder_sel($parent,$pre)
		{
			if (!is_array($this->menucache[$parent]))
				return;

			reset($this->menucache[$parent]);
			while (list(,$v) = each($this->menucache[$parent]))
			{
				$this->ret[$v[oid]] = $pre.$v[name];
				$this->rec_folder_sel($v[oid],$pre."&nbsp;&nbsp;");
			}
		}

		function check_mail()
		{
			if ($this->conf[username] == "" || $this->conf[server_type] == "" || $this->conf[server_name] == "")
				return $this->configure();

			if ($this->conf[server_type] == "pop3")
			{
				$p = new pop3;
				$delete_messages = $this->conf[leave_messages] == 1 ? false : true;

				// collect all the already recieved messages' UIDLS so that we can check if they are already downloaded
				// TODO: hm, maybe we sould do this the other way around, 
				// download all messages uidls and then select from the database messages with those uidls. 
				// hm. yeah. it depends. if the user keeps messages off the server, then that way is more efficient
				// but if the user keeps all messages on the server, this way is more efficient. 
				// later maybe we'll do it differently according to the option
				// and it's a non-issue with less than ~1000 messages anyway
				$uidls = array();
				$this->db_query("SELECT uidl,id FROM mailbox WHERE uid = '".$GLOBALS["uid"]."' ");
				while ($row = $this->db_next(false))
					$uidls[$row[uidl]] = $row[id];

				$msgs = $p->get_messages($this->conf[server_name], $this->conf[username], $this->conf[password],$delete_messages,$uidls);
				reset($msgs);
				while (list(,$v) = each($msgs))
					$this->add_msg($v);
			}
			else
			{
				$this->read_template("server_nosup.tpl");
				return $this->parse();
			}
		}

		function configure()
		{
			$this->read_template("conf.tpl");
			$this->vars(array("username"		=> $this->conf[username],
												"password"		=> $this->conf[password],
												"server_name"	=> $this->conf[server_name],
												"leave_messages_sel"	=> ($this->conf[leave_messages] == 1 ? "CHECKED" : ""),
												"out_server_name"	=> $this->conf[out_server_name],
												"pop3_sel"		=> ($this->conf[server_type] == "pop3" ? "CHECKED" : ""),
												"imap_sel"		=> ($this->conf[server_type] == "imap" ? "CHECKED" : "")));
			return $this->parse();
		}

		function configure_submit($arr)
		{
			$this->conf[username] = $arr["conf"][username];
			$this->conf[password] = $arr["conf"][password];
			$this->conf[server_name] = $arr["conf"][server_name];
			$this->conf[out_server_name] = $arr["conf"][out_server_name];
			$this->conf[server_type] = $arr["conf"][server_type];
			$this->conf[leave_messages] = $arr["conf"][leave_messages];

			$str = serialize($this->conf);
			$this->db_query("UPDATE users SET mailbox_conf = '$str' WHERE uid = '".$GLOBALS["uid"]."'");
			$this->create_user_folders();
		}

		function parse_headers($msg)
		{
			preg_match("/(.*)\x0d\x0a\x0d\x0a(.*?)/msU",$msg,$br);
			$ret[content] = $br[2];
			// put all headers on their own lines
			$hdr = preg_replace("/\x0d\x0a\s+/m"," ",$br[1]);
			
			if (!preg_match("/^from\s*?:\s*\"*(.+?)\"*\s*?<(.+?)>\x0d\x0a/im",$hdr,$br)) 	// if no from header, check for sender header 
				preg_match("/^sender\s*?:\s*\"*(.+?)\"*\s*?<(.+?)>\x0d\x0a/im",$hdr,$br);
			$ret[sender] = $br[2];
			$ret[sender_name] = $br[1];

			preg_match("/^to\s*?:\s*\"*(.+?)\"*\s*?<(.+?)>\x0d\x0a/im",$hdr,$br);
			$ret[reciever] = $br[2];
			$ret[reciever_name] = $br[1];

			preg_match("/^return-path\s*?:\s*?<(.+?)>\x0d\x0a/im",$hdr,$br);
			$ret[return_path] = $br[1];

			preg_match("/^message-id\s*?:\s*?<(.+?)>\x0d\x0a/im",$hdr,$br);
			$ret[message_id] = $br[1];

			// we put headers on different lines b4, so this works
			preg_match("/^subject\s*?:\040*(.*?)\x0d\x0a/im",$hdr,$br);
			$ret[subject] = $br[1];

			preg_match("/^date\s*?:\s*(.+?)\x0d\x0a/im",$hdr,$br);
			$ret[date] = strtotime($br[1]);
			
			return $ret;
		}

		function add_msg($msg)
		{
			$headers = $this->parse_headers($msg[msg]);

			$ui = addslashes($msg[uidl]);
			$this->quote(&$headers);
			$this->quote(&$msg);
			extract($headers);

			$id = $this->new_object(array("parent" => $this->conf["inbox_id"],"name" => $subject,"class_id" => CL_MAIL,"status" => UNREAD,"visible" => 1,"created" => $date));
			$q ="INSERT INTO 
			mailbox(id,sender,sender_name,reciever,reciever_name,return_path,message_id,content,full_text,uid,uidl) 		
			values($id,'$sender', '$sender_name', '$reciever', '$reciever_name', '$return_path', '$message_id', '$content', '".$msg[msg]."', '".$GLOBALS["uid"]."', '".$ui."')";

//			echo "query = '$q'<br>";
			$this->db_query($q);

			return $id;
		}

		function show_mail($id)
		{
			if (!$id || !is_number($id))
				return "";
				
			$this->read_template("show_mail.tpl");

			$this->db_query("SELECT mailbox.*,objects.name as subject FROM mailbox 
											 LEFT JOIN objects ON objects.oid = mailbox.id 
											 WHERE objects.oid = $id AND mailbox.uid = '".$GLOBALS["uid"]."'");	// make sure noone can read others mail
			if (!($row = $this->db_next()))
				$this->raise_error("mailbox->show_mail($id): no such mail!", true);

			$this->upd_object(array("oid" => $id, "status" => READ));	// mark message read

			$content = preg_replace("/(http|ftp):\/\/(\S*)/mi","<a target='_blank' href='\\1://\\2'>\\1://\\2</a>",htmlspecialchars($row[content]));

			$content = preg_replace("/(\S*@\S*(\.\S*)+)/mi","<a href='mail.".$GLOBALS["ext"]."?type=new_mail&to=\\1'>\\1</a>",$content);

			$this->vars(array("from"		=> htmlspecialchars($row[sender_name] == "" ? $row[sender] : $row[sender_name]),
												"to"			=> htmlspecialchars($row[reciever_name] == "" ? $row[reciever] : $row[reciever_name]),
												"subject"	=> htmlspecialchars($row[subject]),
												"message"	=> $content,
												"id"			=> $id));
			return $this->parse();
		}

		function make_tree($selected)
		{
			$this->selected = $selected;

			global $op,$mail_folders;
			if ($op == "close")
				$mail_folders[$selected] = 1;
			else
			if ($op == "open")
				$mail_folders[$selected] = 0;

			$news = array();
			$this->db_query("SELECT count(objects.oid) as cnt, objects.parent as parent FROM mailbox 
											 LEFT JOIN objects ON mailbox.id = objects.oid
											 WHERE mailbox.uid = '".$GLOBALS["uid"]."' AND objects.status = ".UNREAD."
											 GROUP BY objects.parent");
			while ($row = $this->db_next())
				$news[$row[parent]] = $row[cnt];

			$found = false;
			while (!$found)
			{
				$this->menucache = array();
				// show all folders that belong to the logged in user
				// and are not deleted
				$this->db_query("SELECT objects.oid as oid, 
																objects.parent as parent,
																objects.name as name,
																objects.visible as type,
																objects.last as last
													FROM objects 
													WHERE objects.class_id = 27 AND objects.status != 0 AND objects.last = '".$GLOBALS["uid"]."'
													ORDER BY objects.parent,objects.oid");
				while ($row = $this->db_next())
				{
					// now build a data structure of the menus in an easily accessible mode
					// and also include with each menu it's acl entry
					$this->menucache[$row[parent]][] = array("data" => $row, "unread" => $news[$row[oid]]);
					$found = true;
				}

				if (!$found)
					$this->create_user_folders();
			}

			$this->vars(array("space_images" => "", "image" => "<img src='/images/puu_site.gif'>", "cat_id" => "1", "op" => "", "cat_name" => "Kataloogid"));
			$ret = $this->parse("C_LINE");
			// now recursively show the menu
			$this->sel_level = 0;
			$this->level =0;
			return $ret.$this->rec_menu(1,"");
		}

		function rec_menu($parent,$space_images)
		{
			global $ext,$mail_folders;

 			if (!is_array($this->menucache[$parent]))	// if no items on this level return immediately
				return;

			$this->level++;
			$ret = "";
			reset($this->menucache[$parent]);
			$num_els = count($this->menucache[$parent]);
			$cnt = 1;
			while (list(,$v) = each($this->menucache[$parent]))
			{
				$spim = $space_images;

				if ($mail_folders[$v[data][oid]] == 1)	// if it's closed
					$op = "open";
				else
					$op = "close";

				if (is_array($this->menucache[$v[data][oid]]))	// has subitems
				{
					$image = "<a href='mail.$ext?type=folders&parent=".$v[data][oid]."&op=$op'><img src='";

					if ($mail_folders[$v[data][oid]] == 1)	// if closed
						$image.="/images/puu_plus";
					else
						$image.="/images/puu_miinus";

					if ($cnt == $num_els)
						$image.="l.gif";
					else
						$image.=".gif";

					$image.="' border=0>";
				}
				else	// does not have subitems
				{
					$image = "<img src='";
					if ($cnt == $num_els)
						$image.="/images/puu_lopp.gif";
					else
						$image.="/images/puu_rist.gif";
					$image.="' border=0><a href='mail.$ext?type=folders&parent=".$v[data][oid]."&op=$op'>";
				}

				$image.="<img src='/images/";
				if ($this->selected == $v[data][oid])
				{
					$image.="puu_folderl.gif";
					$this->sel_type = $v[data][type];
				}
				else
				{
					if ($v[data][type] == 0)
						$image.="puu_folder_mail.gif";
					else
						$image.="puu_folder.gif";
				}
				$image.="' border=0></a>";

				if ($v[unread] > 0)
					$cname = "<span class='fgtext_bold'>".$v[data][name]." (".$v[unread].")</span>";
				else
					$cname = $v[data][name];

				$this->vars(array("space_images"	=> $spim, 
													"image"					=> $image,
													"cat_name"			=> $cname,
													"cat_id"				=> $v[data][oid],
													"op"						=> "&op=open",
													"parent"				=> $this->selected));

				$ret.=$this->parse("C_LINE");

				if ($cnt == $num_els)			// if we are not at the end of this level we need to show a line, otherwise empty space.
					$spim.="<img src='/images/puu_tyhi.gif' border=0>";
				else
					$spim.="<img src='/images/puu_joon.gif' border=0>";

				if ($mail_folders[$v[data][oid]] == 0)	// if the folder is open
					$ret.=$this->rec_menu($v[data][oid],$spim);

				$cnt++;
			}
			$this->level--;
			return $ret;
		}
		
		function gen_folders($parent)
		{
			$this->read_template("folders.tpl");
			
			if ($parent < 1)
				$parent = $this->conf[inbox_id];

			// topmost folder's type is -1, set here
			// "inbox" "outbox" and stuff are type 0
			// user folders are type 1
			$this->sel_type = -1;

			$l = $this->make_tree($parent);
			$this->vars(array("C_LINE" => $l,"parent" => $parent));

			// so he can add, unless the top folder is selected
			$ca = $this->sel_type == -1 ? "" : $this->parse("CAN_ADD");

			// and can delete/change if user folders are selected
			$ce = $this->sel_type < 1 ? "" : $this->parse("CAN_CHANGE");

			$this->vars(array("CAN_CHANGE" => $ce, "CAN_ADD" => $ca));
			return $this->parse();
		}

		function add_folder($parent)
		{
			$this->read_template("add_folder.tpl");
			$this->vars(array("parent" => $parent, "name" => "", "id" => 0));
			return $this->parse();
		}
		
		function submit_folder(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);

			if ($id)
				$this->upd_object(array("oid" => $id, "name" => $name));
			else
				$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_MAIL_FOLDER,"status" => 2,"last" => $GLOBALS["uid"]));

			return $parent;
		}

		function change_folder($id)
		{
			$this->db_query("SELECT * FROM objects WHERE oid = $id");
			if (!($row = $this->db_next()))
				$this->raise_error("mailbox->change_cat($id): No such folder!", true);

			$this->read_template("add_folder.tpl");

			$this->vars(array("parent"			=> $row[parent], 
												"name"				=> $row[name], 
												"id"					=> $id));
			return $this->parse();
		}

		function del_folder($id)
		{
			$this->delete_object($id);
		}

		function create_user_folders()
		{
			global $uid;

			$num = $this->db_fetch_field("SELECT count(oid) as cnt FROM objects where class_id = 27 AND status != 0 AND last = '".$GLOBALS["uid"]."'","cnt");
			if ($num < 1)
			{
				$this->conf["inbox_id"] = $this->new_object(array("parent" => 1,"name" => "Inbox","class_id" => CL_MAIL_FOLDER,"status" => 2,"last" => $uid));
				$this->conf["sent_items_id"] = $this->new_object(array("parent" => 1,"name" => "Sent Items","class_id" => CL_MAIL_FOLDER,"status" => 2,"last" => $uid));
				$this->conf["del_items_id"] = $this->new_object(array("parent" => 1,"name" => "Deleted Items","class_id" => CL_MAIL_FOLDER,"status" => 2,"last" => $uid));
				$this->conf["drafts_id"] = $this->new_object(array("parent" => 1,"name" => "Drafts","class_id" => CL_MAIL_FOLDER,"status" => 2,"last" => $uid));
			}

			$str = serialize($this->conf);
			$this->db_query("UPDATE users SET mailbox_conf = '$str' WHERE uid = '".$GLOBALS["uid"]."'");
		}

		function new_mail($to = "")
		{
			$this->read_template("send_mail.tpl");
			$this->vars(array("from" => "", "to" => $to, "subject" => "", "content" => "", "id" => "","mail_action" => "new","in_reply_to" => ""));
			return $this->parse();
		}

		function change_mail($id)
		{
			$this->read_template("send_mail.tpl");

			$row = $this->db_get_msg($id);

			$this->vars(array("from"			=> $row[sender], 
												"to"				=> $row[reciever], 
												"subject"		=> $row[subject], 
												"content"		=> $row[content],
												"id"				=> $id,
												"mail_action" => "change",
												"in_reply_to"	=> ""));
			return $this->parse();
		}

		function mail_submit($arr)
		{
			$this->quote(&$arr);
			extract($arr);

			if ($id)
			{
				$this->upd_object(array("oid" => $id, "name" => $subject));
				$this->db_query("UPDATE mailbox set sender='$from' , reciever = '$to', content = '$content' WHERE id = $id");
			}
			else
			{
				$status = 0;												// if replying of forwarding, update the message. we do it here, 
				if ($mail_action == "reply")				// because the user mihgt	abort replying
					$status = $status | REPLIED;
				if ($mail_action == "forward")
					$status = $status | FORWARDED;
				if ($status)
				{
					$status |= $this->db_query("SELECT status FROM objects WHERE oid = $in_reply_to", "status");
					$this->upd_object(array("oid" => $in_reply_to, "status" => $status));
				}

				$id = $this->new_object(array("parent" => $this->conf["drafts_id"],"name" => $subject,"class_id" => CL_MAIL));
				$this->db_query("INSERT INTO mailbox(id,sender,reciever,content,uid) 		
													values($id,'$from','$to','$content','".$GLOBALS["uid"]."')");
			}

			if ($send)
			{
				$msg = "Return-path: <$from>\n";
				$msg.= "Date: ".gmdate("D, j M Y H:i:s T",time())."\n";
				$msg.= "From: <$from>\n";
				$msg.= "Subject: $subject\n";
				$msg.= "To: <$to>\n";
				$msg.= "X-Mailer: Autom@tMail\n\n";
				$msg.= $content;

				$t = new smtp;
				$t->send_message($this->conf[out_server_name], $from, $to, $msg);

				// move the meesage to "sent items" folder
				$this->upd_object(array("oid" => $id, "parent" => $this->conf[sent_items_id],"status" => READ|SENT));

				// and also write whole text of message
				$this->db_query("UPDATE mailbox SET full_text = '$msg' WHERE id = $id");
			}
			return $id;
		}

		function db_get_msg($id)
		{
			$this->db_query("SELECT mailbox.*,objects.name as subject, objects.created as recieved, objects.parent as parent, objects.status as status FROM mailbox 
											 LEFT JOIN objects ON objects.oid = mailbox.id 
											 WHERE objects.oid = $id");
			if (!($row = $this->db_next()))
				$this->raise_error("mailbox->change_mail($id): no such mail!", false);

			return $row;
		}

		function reply($id)
		{
			if (!$id || !is_number($id))
				return "";

			$this->read_template("send_mail.tpl");

			$row = $this->db_get_msg($id);

			if (substr($row[subject],0,3) != "Re:")
				$row[subject] = "Re: ".$row[subject];

			$msg = "";
			$larr = explode("\n",$row[content]);
			reset($larr);
			while (list(,$v) = each($larr))
				$msg.="> ".$v."\n";

			$this->vars(array("from"				=> $row[reciever], 
												"to"					=> $row[sender], 
												"subject"			=> $row[subject], 
												"content"			=> $msg,
												"id"					=> "",
												"mail_action"	=> "reply",
												"in_reply_to"	=> $id));
			return $this->parse();
		}

		function forward($id)
		{
			if (!$id || !is_number($id))
				return "";

			$this->read_template("send_mail.tpl");
			$row = $this->db_get_msg($id);

			$msg = "------- Original Message ----------\nFrom: ".$row[sender]."\nTo: ".$row[reciever]."\nSent: ".$this->time2date($row[recieved],2)."\nSubject: ".$row[subject]."\n\n";

			$row[subject] = "Fw: ".$row[subject];

			$larr = explode("\n",$row[content]);
			reset($larr);
			while (list(,$v) = each($larr))
				$msg.="> ".$v."\n";

			$this->vars(array("from"				=> $row[reciever], 
												"to"					=> "", 
												"subject"			=> $row[subject], 
												"content"			=> $msg,
												"id"					=> "",
												"mail_action"	=> "forward",
												"in_reply_to"	=> $id));
			return $this->parse();
		}

		function do_print($id)
		{
			if (!$id || !is_number($id))
				return "";

			$this->read_template("print.tpl");
			$row = $this->db_get_msg($id);

			$this->vars(array("from"			=> $row[sender], 
												"to"				=> $row[reciever], 
												"subject"		=> $row[subject], 
												"message"		=> $row[content]));
			return $this->parse();
		}

		function delete_message($id)
		{
			if (!$id || !is_number($id))
				return "";

			$row = $this->db_get_msg($id);

			// if the message is in the deleted items folder, delete it really
			if ($this->conf[del_items_id] == $row[parent])
				$this->delete_object($id);
			else
				$this->upd_object(array("oid" => $id, "parent" => $this->conf[del_items_id]));
		}

		function sendback($mail)
		{
			$t = new smtp;
			$this->db_query("SELECT mailbox.*,objects.name as subject, objects.created as recieved, objects.status as status FROM mailbox 
												 LEFT JOIN objects ON objects.oid = mailbox.id 
												 WHERE mailbox.uid = '".$GLOBALS["uid"]."' AND objects.parent=".$this->conf[inbox_id]." AND objects.status != ".DELETED);
			while ($row = $this->db_next())
			{
				$p = strpos($row[sender],"<");
				if ($p)
				{
					$row[sender] = substr($row[sender], $p+1,strlen($row[sender])-($p+2));
				}

				$p = strpos($row[reciever],"<");
				if ($p)
				{
					$row[reciever] = substr($row[reciever], $p+1,strlen($row[sender])-($p+2));
				}

				$msg = "From: ".$row[sender]."\n";
				$msg.= "To: ".$row[reciever]."\n";
				$msg.= "Subject: ".$row[subject]."\n";
				$msg.= "X-Mailer: Autom@tMail\n\n";
				$msg.= $row[content];

				echo "message from ", $row[sender], " to ", $row[reciever], " subject: ", $row[subject], "<br>\n";
				$t->send_message("mail.struktuur.ee", $row[sender],$row[reciever],$msg);
			}
		}

		function submit_list($arr)
		{
			// find all the messages that were selected and move them!
			$mar = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "ch_" && $v == 1)
					$mar[] = substr($k,3);
			}

			$marstr = join(",", $mar);
			if ($marstr != "")
			{
				if ($arr[move])
					$this->db_query("UPDATE objects SET parent = ".$arr[folder]." WHERE oid IN ($marstr)");

				if ($arr[del])
				{
					if ($this->conf[del_items_id] == $arr[parent])
						$this->db_query("UPDATE objects SET status = 0 WHERE oid IN ($marstr)");
					else
						$this->db_query("UPDATE objects SET parent = ".$this->conf[del_items_id]." WHERE oid IN ($marstr)");
				}
			}
		}
	}
?>
