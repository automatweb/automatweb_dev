<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/mail_message.aw,v 1.15 2003/11/09 22:24:49 duke Exp $
// mail_message.aw - Mail message

/*
	@default group=general
	@default table=messages

	@property mailtoolbar type=toolbar store=no no_caption=1
	@caption Toolbar

	@property uidl type=hidden
	@caption UIDL
	
	@property mfrom type=callback callback=callback_get_identities
	@caption Kellelt
	
	@property mto type=textbox size=80
	@caption Kellele

	@property name type=textbox size=80 table=objects
	@caption Teema

	@property date type=text store=no
	@caption Kuupäev

	@property message type=textarea cols=80 rows=40 
	@caption Sisu

	@property attachments type=text store=no
	@caption Manused
	
	property send type=submit value=Saada store=no 
	caption Saada

	property aliasmgr type=aliasmgr store=no
	caption Aliased

	@property msgrid type=hidden 
	@caption Msgrid

	@property msgid type=hidden
	@caption Msgid

	@property mailbox type=hidden
	@caption Mailbox

	classinfo relationmgr=yes

	@groupinfo general submit=no
	@tableinfo messages index=id master_table=objects master_index=oid

*/
class mail_message extends class_base
{
	function mail_message()
	{
		$this->init(array(
			"clid" => CL_MESSAGE,
			"tpldir" => "mail_message",
		));
	}

	// so I have a bunch of fields .. like .. lessay a toolbar .. from, to, reply-to, content .. attachments
	// I need to show different things based on whether I'm viewing a message, composing a new one
	// or doing something else fancy .. so how am I going to do that?


	// things get a bit complicated by the fact that I need to be able to load an external 
	// message .. straight from the IMAP server

	// so, how do I go between the change thingie? .. cause change wants to load an internal
	// object and I do not have an internal object for external messages .. and yet I need
	// to be able to load and edit them .. geezas, what a mind job

	function callback_load_object($arr)
	{
		$msgr = get_instance("messenger/messenger_v2");
		$msgr->set_opt("use_mailbox",$arr["request"]["mailbox"]);
                $msgr->_connect_server(array(
                        "msgr_id" => $arr["request"]["msgrid"],
                ));
		$msgrobj = new object($arr["request"]["msgrid"]);
		
		$this->act = $arr["request"]["subgroup"];

		if ($this->act == "delete")
		{
			$msgr->drv_inst->delete_msgs_from_folder(array($arr["request"]["msgid"]));
			print "kustutatud!";
			print "<script>window.opener.location.reload();</script>";
			print "<a href='javascript:window.close();'>sulge aken</a>";
			exit;
		};

                $msgdata = $msgr->drv_inst->fetch_message(array(
                                "msgid" => $arr["request"]["msgid"],
                ));

		// kui ma nüüd vastan, siis ....
		if ($this->act == "reply")
		{
			$msgdata["to"] = $msgdata["reply_to"];
			$msgdata["from"] = $msgrobj->prop("fromname");
			$msgdata["subject"] = "Re: " . $msgdata["subject"];
			$msgdata["content"] = "\n\n\n" . str_replace("\n","\n> ",$msgdata["content"]);
		}
		elseif ($this->act == "reply2")
		{
			$msgdata["to"] = $msgdata["reply_to"];
			$msgdata["from"] = $msgrobj->prop("fromname");
			$msgdata["subject"] = "Re: " . $msgdata["subject"];
			$msgdata["content"] = "";
			$this->act = "reply";
		}
		elseif ($this->act == "reply3")
		{
			$addrs = explode(",",$msgdata["to"]);
			$addrs = array_merge($addrs,explode(",",$msgdata["from"]));
			$addrs = array_merge($addrs,explode(",",$msgdata["reply_to"]));
			$uniqs = array_unique($addrs);
			$msgdata["to"] = join(",",$uniqs);
			$msgdata["from"] = $msgrobj->prop("fromname");
			$msgdata["subject"] = "Re: " . $msgdata["subject"];
			$msgdata["content"] = "\n\n\n" . str_replace("\n","\n> ",$msgdata["content"]);
		}
		elseif ($this->act == "forward")
		{
			$hdr = "----Forwarded message-----\n";
			$hdr .= "From: $msgdata[from]\n";
			$hdr .= "To: $msgdata[to]\n";
			$hdr .= "Subject: $msgdata[subject]\n";
			$hdr .= "Date: $msgdata[date]\n\n";
			$msgdata["to"] = "";
			$msgdata["from"] = $msgrobj->prop("fromname");
			$msgdata["subject"] = "Fwd: " . $msgdata["subject"];
			$msgdata["content"] = "\n\n\n" . $hdr . str_replace("\n","\n> ",$msgdata["content"]);
		};

		aw_global_set("title_action",$msgdata["subject"]);
		
		$this->msgdata = $msgdata;

		// uue kirja jaoks tuleks uus subaction teha siis on my wild guess
		if ($arr["request"]["subgroup"] == "show")
		{
			$this->state = "show";
		};

	}

	function callback_save_object($arr)
	{
		if ($arr["request"]["subgroup"] == "send")
		{
			// send the bloody message already
			$part1["type"]= "TEXT";
                        $part1["subtype"]="PLAIN";
                        $part1["charset"] = "ISO-8859-4";
                        $part1["contents.data"] = $arr["request"]["message"];

			$body = array();
			$partnum = 1;
			$body[$partnum] = $part1;
			
			$envelope = array();

			$msgr = get_instance("messenger/messenger_v2");
			$msgr->set_opt("use_mailbox",$arr["request"]["mailbox"]);
			$msgr->_connect_server(array(
				"msgr_id" => $arr["request"]["msgrid"],
			));

			$identities = $msgr->_get_identity_list(array(
				"id" => $arr["request"]["msgrid"],	
			));

                        $envelope["from"] = $identities[$arr["request"]["mfrom"]];
                        $envelope["subject"] = $arr["request"]["name"];
                        $envelope["date"] = date('r');

                        $msg = imap_mail_compose($envelope,$body);

			// but I do need to save the message in the Sent items folder!
			$msgr->drv_inst->store_message(array(
                                "from" => $identities[$arr["request"]["mfrom"]],
                                "to" => $arr["request"]["mto"],
                                "subject" => $arr["request"]["name"],
                                "message" => $msg,
                        ));

                        mail($arr["request"]["mto"],$arr["request"]["name"],"",$msg);


			print "saadetud<p>";
			print "<a href='javascript:window.close();'>sulge aken</a>";
			exit;

		}
		else
		{
			// redirect back
			return $this->mk_my_orb("change",array(
				"msgrid" => $arr["request"]["msgrid"],
				"msgid" => $arr["request"]["msgid"],
				"mailbox" => $arr["request"]["mailbox"],
				"subgroup" => $arr["request"]["subgroup"],
			));	
		};
	}


	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "comment":
			case "status":
				$retval = PROP_IGNORE;
				break;

			case "mto":
				$data["value"] = htmlspecialchars($this->msgdata["to"]);
				if ($this->state == "show")
				{
					$data["type"] = "text";
				};
				break;

			case "date":
				if ($this->state == "show")
				{
					$data["value"] = $this->msgdata["date"];
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
	
			case "name":
				$data["value"] = htmlspecialchars($this->msgdata["subject"]);
				if ($this->state == "show")
				{
					$data["type"] = "text";
				};
				break;

			case "message":
				$data["value"] = htmlspecialchars($this->msgdata["content"]);
				if ($this->state == "show")
				{
					$data["value"] = nl2br(create_links($data["value"]));
					$data["type"] = "text";
				};
				break;

			case "mailtoolbar":
				// aga vaat sellega on nyyd niuke asi, et ta ei peaks salvestama ega
				// midagi tegema .. ta peaks lihtsalt redirectima tagasi muutmisele. ..
				$this->gen_mail_toolbar(&$data);
				break;

			case "attachments":
				if (empty($this->msgdata["attachments"]))
				{
					$retval = PROP_IGNORE;
				}
				else
				{
					$this->read_template("attachment.tpl");
					foreach($this->msgdata["attachments"] as $num => $pdata)
					{
						$this->vars(array(
							"part_name" => $pdata,
							"get_part_url" => $this->mk_my_orb("get_part",array(
								"msgrid" => $arr["request"]["msgrid"],
								"msgid" => $arr["request"]["msgid"],
								"mailbox" => $arr["request"]["mailbox"],
								"part" => $num,
							),"mail_message","false",true),
						));
						$rv .= $this->parse();
					};
					$data["value"] = $rv;
				};
				break;

			case "msgrid":
			case "msgid":
			case "mailbox":
				$data["value"] = $arr["request"][$data["name"]];
				break;

		}
		return $retval;
	}

	function set_property($arr)
	{
		$retval = PROP_OK;
		$data = &$arr["prop"];
		switch($data["name"])
		{
			case "status":
				$data["value"] = STAT_ACTIVE;
				break;
	
			case "send":
				if ($arr["form_data"]["send"])
				{
					$this->deliver_message = true;
				};
				break;

					
			
		};
		return $retval;
	}

	function callback_post_save($arr)
	{
		if ($this->deliver_message)
		{
			$this->deliver(array("id" => $arr["id"]));
		};
	}

	// basically the same as deliver, except that this one is _not_
	// called through ORB, and you can specify replacements here
	function process_and_deliver($args)
	{
		$oid = $args["id"];
		$q = "SELECT name,mfrom,mto,message FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE objects.oid = $oid";
		$this->db_query($q);
		$row = $this->db_next();

		$message = $row["message"];
		if (is_array($args["replacements"]))
		{
			foreach($args["replacements"] as $source => $target)
			{
				$message = str_replace($source,$target,$message);
			}

		}
		
		$awm = get_instance("aw_mail");

		$awm->create_message(array(
			"froma" => $row["mfrom"],
			"subject" => $row["name"],
			"to" => $args["to"],
			"body" => $message,
		));

		$awm->gen_mail();

	}
				
			
	function gen_mail_toolbar($arr)
	{
		// urk .. what a mind job
		$tb = &$arr["toolbar"];
		if ($this->act != "reply" && $this->act != "forward" && $this->act != "")
		{
			$tb->add_button(array(
				"name" => "reply",
				"url" => "javascript:document.changeform.subgroup.value='reply';document.changeform.submit();",
				"tooltip" => "Vasta/kvoodi",
			));
			
			$tb->add_button(array(
				"name" => "reply2",
				"url" => "javascript:document.changeform.subgroup.value='reply2';document.changeform.submit();",
				"tooltip" => "Vasta/tühjalt",
			));
			
			$tb->add_button(array(
				"name" => "reply3",
				"url" => "javascript:document.changeform.subgroup.value='reply3';document.changeform.submit();",
				"tooltip" => "Vasta/kõigile",
			));

			$tb->add_button(array(
				"name" => "delete",
				"url" => "javascript:document.changeform.subgroup.value='delete';document.changeform.submit();",
				"img" => "delete.gif",
				"tooltip" => "Kustuta",
			));
		};

		if ($this->act != "reply" && $this->act != "forward" && $this->act != "")
		{
			$tb->add_button(array(
				"name" => "forward",
				"url" => "javascript:document.changeform.subgroup.value='forward';document.changeform.submit();",
				"tooltip" => "Forward",
			));
		};

		if ($this->act != "show")
		{
			$tb->add_button(array(
				"name" => "send",
				"url" => "javascript:document.changeform.subgroup.value='send';document.changeform.submit();",
				"tooltip" => "Saada",
			));
		};
	}

	function deliver($args)
	{
		$oid = $args["id"];
		$q = "SELECT name,mfrom,mto,message FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE objects.oid = $oid";
		$this->db_query($q);
		$row = $this->db_next();
		$awm = get_instance("aw_mail");

		$awm->create_message(array(
			"froma" => $row["mfrom"],
			"subject" => $row["name"],
			"to" => $row["mto"],
			"body" => $row["message"],
		));

		$awm->gen_mail();
		print "<script>window.close();</script>";
		print "Selle akna võib peale kirja saatmist sulgeda<br />";
		print "-------<br />";
		print "saadetud<br />";
		die();
	}

	function callback_get_identities($arr)
	{
		$rv = $arr["prop"];
			
		if ($this->state == "show")
		{
			$rv["value"] = htmlspecialchars($this->msgdata["from"]);
			$rv["type"] = "text";
		}
		else
		{
			$msgr = get_instance("messenger/messenger_v2");
			$rv["type"] = "select";
			$opts = $msgr->_get_identity_list(array(
				"id" => $arr["request"]["msgrid"],
			));
			foreach($opts as $key => $item)
			{
				$rv["options"][$key] = htmlspecialchars($item);
			};
		};	
		return array($rv);
	}
	
	////
	// !Can be used to download message parts
	function get_part($arr)
	{
		$msgid = $arr["msgid"];
		$msgr = get_instance("messenger/messenger_v2");
		$msgr->set_opt("use_mailbox",$arr["request"]["mailbox"]);
                $msgr->_connect_server(array(
                        "msgr_id" => $arr["msgrid"],
                ));

		$msgr->drv_inst->fetch_part(array(
			"msgid" => $msgid,
			"part" => $arr["part"],
		));
	}

	////
	// !fetches a message by it's ID
	// arguments:
	// id(int) - message id
	function msg_get($args = array())
	{
		// Will show only users own messages
		//$q = sprintf("UPDATE messages SET status = %d WHERE id = %d",MSG_STATUS_READ,$args["id"]);
		//$this->db_query($q);
		$q = sprintf("SELECT *,objects.* 
				FROM messages
				LEFT JOIN objects ON (messages.id = objects.oid)
				WHERE id = '%d'",
				$args["id"]);
		$this->db_query($q);
		$row = $this->db_next();
		$row["meta"] = $this->get_object_metadata(array(
			"metadata" => $row["metadata"]
		));
		// get subject from object name, since that is where the new mail_message class keeps
		// it -- duke
		if (empty($row["subject"]) && !empty($row["name"]))
		{
			$row["subject"] = $row["name"];
		};
		return $row;
	}
		

};
?>
