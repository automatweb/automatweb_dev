<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/messenger/mail_message.aw,v 1.17 2005/10/19 19:06:41 duke Exp $
// mail_message.aw - Mail message

/*
	@classinfo no_comment=1 no_status=1 syslog_type=ST_MAIL_MESSAGE
	@default group=general
	@default table=messages

	@property edit_toolbar type=toolbar store=no no_caption=1
	@caption Kirja redigeerimise toolbar

	@property uidl type=hidden
	@caption UIDL
	
	@property mfrom_name type=hidden table=objects field=meta method=serialize
	@caption Kellelt nimi
	
	@property mfrom type=select 
	@caption Kellelt
	
	@property mto type=textbox size=80
	@caption Kellele

	@property cc type=textbox field=mtargets1 size=80
	@caption Koopia

	@property bcc type=textbox field=mtargets2 size=80
	@caption Pimekoopia

	@property name type=textbox size=80 table=objects
	@caption Teema

	property date type=text store=no
	caption Kuupäev
	
	@property html_mail type=checkbox ch_value=1 field=type method=bitmask ch_value=1024
	@caption HTML kiri

	@property message type=textarea cols=80 rows=40
	@caption Sisu

	@property attachments type=relmanager table=objects field=meta method=serialize reltype=RELTYPE_ATTACHMENT props=comment,file chooser=no new_items=5
	@caption Manused

	property send type=submit value=Saada store=no 
	caption Saada

	property aliasmgr type=aliasmgr store=no
	caption Aliased

	@property msgrid type=hidden store=no form=all
	@caption Msgrid

	@property msgid type=hidden store=no form=all
	@caption Msgid

	@property mailbox type=hidden store=no form=all
	@caption Mailbox

	@property cb_part type=hidden store=no form=all
	@caption Cb part

	classinfo relationmgr=yes

	@groupinfo general caption="Üldine" submit=no
	@groupinfo add caption="Lisa"

	@tableinfo messages index=id master_table=objects master_index=oid
	
	@property view_toolbar type=toolbar store=no no_caption=1 form=showmsg
	@caption Kirja vaatamise toolbar

	@property msg_headers type=text store=no form=showmsg no_caption=1
	@caption Kirja päised

	@property msg_content type=text store=no form=showmsg no_caption=1
	@caption Kirja sisu
	
	@property msg_contener_title type=textbox field=meta method=serialize table=objects group=add
	@caption Konteineri pealkiri
	
	@property msg_contener_content type=textarea field=meta method=serialize table=objects group=add
	@caption Konteineri sisu

	@property msg_attachments type=text store=no form=showmsg no_caption=1
	@caption Manused

	@forminfo showmsg onload=load_remote_message
	@forminfo showheaders onload=load_remote_message

	@reltype ATTACHMENT value=1 clid=CL_FILE
	@caption Manus

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

	////
	// !Retrieves a message from specified messenger, specified folder,
	// with specified id
	function fetch_message($arr)
	{
		$msgr = get_instance(CL_MESSENGER_V2);
		$msgr->set_opt("use_mailbox",$arr["mailbox"]);
		$msgr->_connect_server(array(
			"msgr_id" => $arr["msgrid"],
		));
                
		$rv = $msgr->drv_inst->fetch_message(array(
			"msgid" => $arr["msgid"],
		));

		if ($rv && isset($arr["fullheaders"]))
		{
			$rv["fullheaders"] = $msgr->drv_inst->fetch_headers(array(
				"msgid" => $arr["msgid"],
			));
		};

		return $rv;
	}

	function load_remote_message($arr)
	{
		// now I have to create a connection to a remote object
		$msgdata = $this->fetch_message(array(
			"mailbox" => $arr["mailbox"],
			"msgrid" => $arr["msgrid"],
			"msgid" => $arr["msgid"],
			"fullheaders" => $arr["viewmode"] == "headers",
		));
		
		if (empty($msgdata))
		{
			print sprintf(t("couldn't retrieve message %s, perhaps it has been deleted or moved?<bR>"), $arr["request"]["msgid"]);
			die();
		};

		if ($arr["viewmode"] == "headers")
		{
			$this->rawheaders = $msgdata["fullheaders"];
		};
		$this->msgdata = $msgdata;

	}

	// Retrieves a message object from storage and delivers it
	function send_message($arr)
	{
		$this->awm = get_instance("protocols/mail/aw_mail");

		$msgobj = new object($arr["id"]);

		//$fxt = false;

		// no! I do not need to connect to the server, unless I'm actually saving
		// something there.

		// this should only need the message id from local filesystem, it then
		// will read in the object and do the actual delivery

		// the thing is, this is not hard at all, I'm just having some kind of mental block
		/*
		if ($arr["request"]["msgrid"])
		{
			$msgr = get_instance(CL_MESSENGER_V2);
			$msgr->set_opt("use_mailbox",$arr["request"]["mailbox"]);
			$msgr->_connect_server(array(
				"msgr_id" => $arr["request"]["msgrid"],
			));

			$msgrobj = new object($arr["request"]["msgrid"]);
			//$outbox = $msgrobj->prop("msg_outbox");
			$arr["request"]["parent"] = $msgrobj->prop("msg_outbox");
			$fxt = true;
		};	
		*/

		$to_addr = $msgobj->prop("mto");
		// jesus fucking christ, I hate this approach
		// now I need to fix sending from lists as well. How tha fuck am I going to do that?
		if (is_numeric($to_addr))
		{
			$target_obj = new object($to_addr);
			if ($target_obj->prop("class_id") == CL_ML_LIST)
			{
				#$lists = array(":" . $target_obj->prop("name"));
				$lists = $target_obj->id();
				#$to_addr = join(",",$lists);
			};
			$to_list = true;
		};


		if ($to_list)
		{
			$qid = $this->db_fetch_field("SELECT max(qid) as qid FROM ml_queue", "qid")+1;
			$mllist = get_instance(CL_ML_LIST);
			// if sending from messenger, then we are inside a popup
			// and don't want to display the rest of the list interface 
			// form (or perhaps I do?)
			if ($fxt)
			{
				$route_back = $this->mk_my_orb("change",array(
					"id" => $target_obj->id(),
					"mail_id" => $this->id,
					"group" => "mail_report",
					"qid" => $qid,
					"cb_part" => 1,
					"fxt" => 1),
				CL_ML_LIST);
			}
			else
			{
				$route_back = $this->mk_my_orb("change",array(
					"id" => $target_obj->id(),
					"mail_id" => $this->id,
					"qid" => $qid,
					"group" => "mail_report"),
				CL_ML_LIST);
			};
			aw_session_set("route_back", $route_back);
			// scheduleerib kirjade saatmise
			$url = $mllist->route_post_message(array(
				"id" => $this->id,
				"targets" => $lists,
			));
			Header("Location: $url");
			die();
		};

		if ($msgobj->prop("html_mail") == 1)
		{
			$this->awm->create_message(array(
				"froma" => $msgobj->prop("mfrom"),
				"subject" => $msgobj->name(),
				"to" => $msgobj->prop("mto"),
				"cc" => $msgobj->prop("cc"),
				"bcc" => $msgobj->prop("bcc"),
				"body" => t("Kahjuks sinu meililugeja ei oska näidata HTML formaadis kirju"),
			));
			$this->awm->htmlbodyattach(array(
				"data" => $msgobj->prop("message"),
			));
		}
		else
		{
			$this->awm->create_message(array(
				"froma" => $msgobj->prop("mfrom"),
				"subject" => $msgobj->name(),
				"to" => $msgobj->prop("mto"),
				"cc" => $msgobj->prop("cc"),
				"bcc" => $msgobj->prop("bcc"),
				"body" => $msgobj->prop("message"),
			));
		};

		$conns = $msgobj->connections_from(array(
			"type" => "RELTYPE_ATTACHMENT",
		));

		$mimeregistry = get_instance("core/aw_mime_types");



		foreach($conns as $conn)
		{
			$to_o = $conn->to();
			// XXX: is this check correct?
			if ($to_o->prop("file") == "")
			{
				continue;
			};
			$realtype = $mimeregistry->type_for_file($to_o->name());
			$this->awm->fattach(array(
				"path" => $to_o->prop("file"),
				"contenttype"=> $mimeregistry->type_for_file($to_o->name()),
				"name" => $to_o->name(),
			));
		};

		$this->awm->gen_mail();

		// but I do need to save the message in the Sent items folder!

		// this is where I need to connect to the remote server! This! And Not Before!
	}


	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "view_toolbar":
				$this->view_toolbar(&$arr);
			break;
			
			case "msg_headers":
				$this->read_template("headers.tpl");
				$rv = "";
				if (($this->rawheaders))
				{
					$rh = $this->rawheaders;
					$rh = preg_replace("/\n\s/"," ",$rh);
					$rh = htmlspecialchars($rh);
					$lines = explode("\n",$rh);
					foreach($lines as $line)
					{
						if (strlen($line) == 0)
						{
							continue;
						};
						preg_match("/^(.+?):(.*)$/",$line,$m);
						$this->vars(array(
							"caption" => $m[1],
							"content" => $m[2],
						));
						if ($m[1] == "Subject")
						{
							aw_global_set("title_action",$m[2]);
						};
						$rv .= $this->parse("header_line");



					};
				}
				else
				{
					$keys = array("from","reply_to","cc","to","subject","date");
					foreach($this->msgdata as $key => $value)
					{
						if (empty($value))
						{
							continue;
						};
						if (in_array($key,$keys))
						{
							$rkey = ucfirst(str_replace("_","-",$key));
							$value = htmlspecialchars($value);
							$this->vars(array(
								"caption" => $rkey,
								"content" => $value,
							));
							if ($rkey == "Subject")
							{
								aw_global_set("title_action",$value);
							};
							$rv .= $this->parse("header_line");
						};
					};
				};
				$this->vars(array(
					"header_line" => $rv,
				));
				$data["value"] = $this->parse();
				break;

			case "msg_content":
				$data["value"] = nl2br(create_links(htmlspecialchars($this->msgdata["content"])));
				break;

			case "msg_attachments":
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
						$rv .= $this->parse("att_line");
					};
					$this->vars(array(
						"att_line" => $rv,
					));
					$data["value"] = $this->parse();
				};
				break;

			case "mto":
				// check whether the messenger object has any connections to 
				// a list object

				// XXX: this kind of lock-down is bad, ok?
				$msgr = get_instance(CL_MESSENGER_V2);
				$opts = array();
				$msgrobj = new object($arr["request"]["msgrid"]);
				$outbox = $msgrobj->prop("msg_outbox");

				if (!empty($outbox))
				{
					$opts = $msgr->_gen_address_list(array(
						"id" => $arr["request"]["msgrid"],
					));


					if (sizeof($opts) > 0)
					{
						$data["type"] = "select";
						$data["options"] = $opts;
						$data["size"] = 1;
					}
				}
				else
				{
					$data["type"] == "textbox";
					//$data["value"] = $this->msgdata["to"];
				};

				break;

			case "mfrom":
				$this->gen_identities(&$arr);
				break;

			case "edit_toolbar":
				$this->edit_toolbar(&$data);
				break;

			case "msgrid":
			case "msgid":
			case "mailbox":
			case "cb_part":
				$data["value"] = $arr["request"][$data["name"]];
				break;

			case "attachments":
				$msgr = get_instance(CL_MESSENGER_V2);
				$opts = array();
				$msgrobj = new object($arr["request"]["msgrid"]);
				$data["new_items"] = $msgrobj->prop("num_attachments");

				break;

		}
		return $retval;
	}

	/*
	function set_property($arr)
	{
		$retval = PROP_OK;
		$data = &$arr["prop"];
		switch($data["name"])
		{
		};
		return $retval;
	}
	*/

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
		
		$awm = get_instance("protocols/mail/aw_mail");

		$awm->create_message(array(
			"froma" => $row["mfrom"],
			"subject" => $row["name"],
			"to" => $args["to"],
			"body" => $message,
		));

		$awm->gen_mail();

	}
	
	function edit_toolbar($arr)
	{
		$tb = &$arr["toolbar"];
		// now, how do I figure out that the send button was clicked in my set_property calls?
		// it needs to act exactly like save in every aspect, except that it has to 
		// send the message as well
		$tb->add_button(array(
			"name" => "send",
			"action" => "mail_send",
			"img" => "mail_send.gif",
			"tooltip" => t("Saada"),
		));

		$tb->add_button(array(
			"name" => "save",
			"action" => "",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
		));

	}

	// listile peale valik - "kasuta seda malli"
	// kui ole valikut, siis dropdownis mallid .....

	// kirja objektile user defined välju - by default väljas

	// uus seos - listi liikmete allikaks saaks panna gruppi ja ka kasutajale meili saatmine

	// listi juurde statistika tab, et oleks näha kes on kirju lugenud

	// alias mis kuvab parooli muutmise lehe


	function view_toolbar($arr)
	{
		$tb = &$arr["prop"]["toolbar"];
		$tb->add_button(array(
			"name" => "reply",
			"action" => "mail_reply",
			"tooltip" => t("Vasta"),
			"img" => "mail_reply.gif",
		));

		$tb->add_button(array(
			"name" => "reply3",
			"action" => "mail_reply_all",
			"tooltip" => t("Vasta/kõigile"),
			"img" => "mail_reply_all.gif",
		));

		$tb->add_button(array(
			"name" => "forward",
			"action" => "mail_forward",
			"tooltip" => t("Edasta"),
			"img" => "mail_fwd.gif",
		));

		$tb->add_separator();
		$tb->add_button(array(
			"name" => "delete",
			"action" => "mail_delete",
			"confirm" => t("Kustutada see kiri? (tagasi ei saa!)"),
			"tooltip" => t("Kustuta"),
			"img" => "delete.gif",
		));



		$pl = get_instance(CL_PLANNER);
		$cal_o = $pl->get_calendar_obj_for_user(array(
			"uid" => aw_global_get("uid"),
		));

		$user_cal = $cal_o->id();

		$req = $arr["request"];

		if (is_oid($user_cal))
		{
			$tb->add_separator();
			$tb->add_menu_button(array(
				"name" => "calendar",
				"tooltip" => t("Lisa kalendrisse"),
				"img" => "icon_cal_today.gif",
			));
			$ev_classes = $pl->get_event_entry_classes($cal_o);

			$clinf = aw_ini_get("classes");
			foreach($ev_classes as $clid)
			{
				$tb->add_menu_item(array(
					"parent" => "calendar",
					"text" => $clinf[$clid]["name"],
					"url" => $this->mk_my_orb("register_event",array(
						"mailbox" => $req["mailbox"],
						"msgrid" => $req["msgrid"],
						"msgid" => $req["msgid"],
						"create_class" => $clid,
					)),
					//"action" => "register_event",
				));
			};
		};

		$tb->add_separator();

		$tb->add_menu_button(array(
			"name" => "viewmode",
			"img" => "preview.gif",
			"tooltip" => t("Päised"),
		));

		$tb->add_menu_item(array(
			"parent" => "viewmode",
			"text" => t("Tavaline"),
			"url" => aw_url_change_var("viewmode",""),
		));

		$tb->add_menu_item(array(
			"parent" => "viewmode",
			"text" => t("Kõik päised"),
			"url" => aw_url_change_var("viewmode","headers"),
		));
	}


	/**  
		
		@attrib name=deliver params=name 
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function deliver($args)
	{
		$oid = $args["id"];
		$q = "SELECT name,mfrom,mto,message FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE objects.oid = $oid";
		$this->db_query($q);
		$row = $this->db_next();
		$awm = get_instance("protocols/mail/aw_mail");

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

	function callback_mod_retval($arr)
	{
		$args = &$arr["args"];
		if ($arr["request"]["msgrid"])
		{
			$args["msgrid"] = $arr["request"]["msgrid"];
		};
		if ($arr["request"]["cb_part"])
		{
			$args["cb_part"] = $arr["request"]["cb_part"];
		};
	}

	function gen_identities($arr)
	{
		$rv = &$arr["prop"];
		// grr, I hate this
		if (isset($arr["request"]["msgrid"]))
		{
			$msgr = get_instance(CL_MESSENGER_V2);
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
	
	/** Can be used to download message parts 
		
		@attrib name=get_part params=name 
		
		@param msgrid required type=int
		@param msgid required type=int
		@param mailbox required
		@param part required type=int
		
		@returns
		
		
		@comment

	**/
	function get_part($arr)
	{
		$msgid = $arr["msgid"];
		$msgr = get_instance(CL_MESSENGER_V2);
		$msgr->set_opt("use_mailbox",$arr["request"]["mailbox"]);
                $msgr->_connect_server(array(
                        "msgr_id" => $arr["msgrid"],
                ));

		$msgr->drv_inst->fetch_part(array(
			"msgid" => $msgid,
			"part" => $arr["part"],
		));
	}
	
	/** Creates a message draft
		
		@attrib name=create_draft params=name 
		
		@param msgrid required type=int
		@param cb_part optional type=int
		
		@returns
		
		
		@comment

	**/
	function create_draft($arr)
	{
		$msgobj = $this->_create_draft(array(
			"msgrid" => $arr["msgrid"],
		));
	
		$arr["id"] = $msgobj->id();
		return $this->_gen_edit_url($arr);
	}

	/** Creates a calendar event from a message object 
		
		@attrib name=register_event

		@param msgrid required type=int
		@param msgid required type=int
		@param mailbox required
		@param create_class required type=int

	**/
	function register_event($arr)
	{
		$msgdata = $this->fetch_message(array(
			"mailbox" => $arr["mailbox"],
			"msgrid" => $arr["msgrid"],
			"msgid" => $arr["msgid"],
		));

		$tc = get_instance("applications/messenger/calendar_connector");
		$props = $tc->get_property_group(array("form" => "connector","clfile" => "calendar_connector"));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		
		$pl = get_instance(CL_PLANNER);
		$cal_o = $pl->get_calendar_obj_for_useR();
		$user_cal = $cal_o->id();
		$clids = $pl->get_event_entry_classes($cal_o);

		$props["title"]["value"] = $msgdata["subject"];
		$props["start"]["value"] = time();

		$calendars = array();

		$cal_list = new object_list(array(
			"class_id" => CL_PLANNER,
			"sort_by" => "name",
			"site_id" => array(),
		));


		// not using names(), because I only need calendars with valid event_folders
		foreach($cal_list->arr() as $cal)
		{
			$event_folder = $cal->prop("event_folder");
			if (is_oid($event_folder))
			{
				$calendars[$cal->id()] = $cal->name();
			};
		};

		$props["main_calendar"]["options"] = $calendars;
		$props["main_calendar"]["value"] = $user_cal;
		unset($calendars[$user_cal]);
		$props["calendars"]["options"] = $calendars;

		$props["content"]["value"] = sprintf("From: %s\nTo: %s\nSubject: %s\nDate: %s\n\n%s",
					$msgdata["from"],$msgdata["to"],$msgdata["subject"],$msgdata["date"],
					$msgdata["content"]);

		$clinf = aw_ini_get("classes");
		foreach($clids as $key => $val)
		{
			$props["class_id"]["options"][$val] = $clinf[$val]["name"];
		};

		$props["class_id"]["value"] = $arr["create_class"];
		// kuidas ma teen siia nimekirja kõigist kasutaja projektidest?

		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
		$conns = $user->connections_to(array(
			"from.class_id" => CL_PROJECT,
			"sort_by" => "from.name",
			"type" => "RELTYPE_PARTICIPANT",
		));
				
		foreach($conns as $conn)
		{
			$props["projects"]["options"][$conn->prop("from")] = $conn->prop("from.name");
		};

		foreach($props as $pn => $pd)
		{
                        $htmlc->add_property($pd);
                }

                $htmlc->finish_output(array(
			"data" => array(
				"action" => "submit_register_event",
				"class" => get_class($this),
				"mailbox" => $arr["mailbox"],
				"msgrid" => $arr["msgrid"],
				"msgid" => $arr["msgid"],
			),
		));

                $html = $htmlc->get_result(array());

		return $html;
	}

	/**
		@attrib name=submit_register_event all_args=1
	**/
	function submit_register_event($arr)
	{
		load_vcl("date_edit");
		
		$msgdata = $this->fetch_message(array(
			"mailbox" => $arr["mailbox"],
			"msgrid" => $arr["msgrid"],
			"msgid" => $arr["msgid"],
		));
		
		$main_calendar = new object($arr["main_calendar"]);
		$event_folder = $main_calendar->prop("event_folder");
		$evt = new object();
		$evt->set_parent($event_folder);
		$evt->set_class_id($arr["class_id"]);
		$evt->set_name($arr["title"]);
		$evt->set_status(STAT_ACTIVE);
		$evt->set_prop("start1",date_edit::get_timestamp($arr["start"]));
		$evt->set_prop("content",$arr["content"]);
		$evt->save();

		if (is_array($arr["calendars"]))
		{
			foreach($arr["calendars"] as $calendar)
			{
				$cal_obj = new object($calendar);
				$evt->create_brother($cal_obj->prop("event_folder"));
			};
		};

		if (is_array($arr["projects"]))
		{
			foreach($arr["projects"] as $project)
			{
				$evt->create_brother($project);
			}
		};
	
		$msgr = get_instance(CL_MESSENGER_V2);
		$msgr->set_opt("use_mailbox",$arr["mailbox"]);
                $msgr->_connect_server(array(
                        "msgr_id" => $arr["msgrid"],
                ));

		$awf = get_instance(CL_FILE);


		//$awf = get_instance(CL_FILE);

		foreach($msgdata["attachments"] as $num => $pdata)
		{
			// 0 is the message itself
			if ($num == 0) continue;
			$att = $msgr->drv_inst->fetch_part(array(
				"msgid" => $arr["msgid"],
				"part" => $num,
				"return" => 1,
			));

			$fdat = array(
				"parent" => $event_folder,
				"file" => array(
					"content" => $att["content"],
					"name" => $att["name"],
				),
				"return" => "id",
			);

			$file_id = $awf->submit($fdat);

			// create alias
			$evt->connect(array(
				"to" => $file_id,
			));

		};
		
		//print "creating the fucking event";


		return $this->mk_my_orb("change",array(
			"msgrid" => $arr["msgrid"],
			"msgid" => $arr["msgid"],
			"form" => "showmsg",
			"cb_part" => 1,
			"mailbox" => $arr["mailbox"]),"mail_message");

	}
	
	/** Deletes a message 
		
		@attrib name=mail_delete

	**/
	function mail_delete($arr)
	{
		$msgr = get_instance(CL_MESSENGER_V2);
		$msgr->set_opt("use_mailbox",$arr["mailbox"]);
                $msgr->_connect_server(array(
                        "msgr_id" => $arr["msgrid"],
                ));
		$msgr->drv_inst->delete_msgs_from_folder(array($arr["msgid"]));
		print t("kustutatud!");
		print "<script>window.opener.location.reload();</script>";
		print "<a href='javascript:window.close();'>".t("sulge aken")."</a>";
		exit;
	}
	
	/** Sends a message stored in local folders
		
		@attrib name=mail_send

	**/
	function mail_send($arr)
	{
		$this->id_only = true;

		// mfrom is a select box containing all the different identities for
		// this messenger, but I need the textual value for it
		// field, I'll resolve the numeric 
		$msgr = get_instance(CL_MESSENGER_V2);
		$msgr->_connect_server(array(
                        "msgr_id" => $arr["msgrid"],
                ));

		if (is_numeric($arr["mfrom"]) && isset($arr["msgrid"]))
		{
			$identities = $msgr->_get_identity_list(array(
				"id" => $arr["msgrid"],	
			));
			$arr["mfrom"] = $identities[$arr["mfrom"]];
		};


		// this is the place where I need to resolve the from address
		$msgid = $this->submit($arr);

		$this->send_message(array(
			"id" => $msgid,
		));

		// I'll also have to move the message from drafts folder to the outbox
		// if there is such a thing that is

		// would be nice to set the replied flag for the original message too
		// but I really don't know how on earth I'm going to do that
		$msgr->drv_inst->store_message(array(
			"from" => $arr["mfrom"],
			"date" => $arr["date"],
			"to" => $arr["mto"],
			"cc" => $arr["cc"],
			"subject" => $arr["name"],
			"message" => $this->awm->bodytext,
		));
                
		
		print t("saadetud<p>");
		print "<a href='javascript:window.close();'>".t("sulge aken")."</a>";
		exit;

	}

	////
	// !Creates a draft message, needs messenger id, returns the id of the empty message body
	function _create_draft($arr)
	{
		$msgr_obj = new object($arr["msgrid"]);
		$drafts_folder = $msgr_obj->prop("msg_drafts");
		$o = new object();
		$o->set_class_id($this->clid);
		$o->set_parent($drafts_folder);
		$o->set_status(STAT_ACTIVE);
		$o->save();
		return $o;
	}

	function _gen_edit_url($arr)
	{
		return $this->mk_my_orb("change",array(
			"id" => $arr["id"],
			"msgrid" => $arr["msgrid"],
			"cb_part" => $arr["cb_part"],
		));
	}
	
	/** Prepares a message for replying
		
		@attrib name=mail_reply  
	**/
	function mail_reply($arr)
	{
		$msgdata = $this->fetch_message(array(
			"mailbox" => $arr["mailbox"],
			"msgrid" => $arr["msgrid"],
			"msgid" => $arr["msgid"],
		));

		$msgobj = $this->_create_draft(array(
			"msgrid" => $arr["msgrid"],
		));

		$msgobj->set_name("Re: " . $msgdata["subject"]);
		$msgobj->set_prop("mto",!empty($msgdata["reply_to"]) ? $msgdata["reply_to"] : $msgdata["from"]);
		$msgobj->set_prop("message","\n\n\n> " . str_replace("\n","\n> ",$msgdata["content"]));
		$msgobj->save();

		$arr["id"] = $msgobj->id();
		return $this->_gen_edit_url($arr);
	}
	
	/** Prepares a message for replying to all addresses
		
		@attrib name=mail_reply_all  
	**/
	function mail_reply_all($arr)
	{
		$msgdata = $this->fetch_message(array(
			"mailbox" => $arr["mailbox"],
			"msgrid" => $arr["msgrid"],
			"msgid" => $arr["msgid"],
		));

		$msgobj = $this->_create_draft(array(
			"msgrid" => $arr["msgrid"],
		));
		// Replying to all
		//  1. from/reply_to becomes to
		//  2. to goes to cc
		//  3. cc goes to cc
		$addrs1 = explode(",",$msgdata["to"]);
		if ($msgdata["cc"])
		{
			$addrs1 = array_merge($addrs1,explode(",",$msgdata["cc"]));
		};
		// XXX: implement something to exclude any addresses in the identities 
		// from the aadress list
		$to = $msgdata["reply_to"] ? $msgdata["reply_to"] : $msgdata["from"];
		$uniqs = array_unique($addrs1);

		// try remove to aadress from CC
		$rf = array_search($to,$uniqs);
		if ($rf !== false)
		{
			unset($uniqs[$rf]);
		};

		$msgobj->set_prop("mto",$to);
		$msgobj->set_prop("cc",join(",",$uniqs));
		$msgobj->set_name("Re: " . $msgdata["subject"]);
		$msgobj->set_prop("message","\n\n\n> " . str_replace("\n","\n> ",$msgdata["content"]));
		$msgobj->save();
		
		$arr["id"] = $msgobj->id();
		return $this->_gen_edit_url($arr);
		
	}
	
	/** Prepares a message for forwarding
		
		@attrib name=mail_forward
	**/
	function mail_forward($arr)
	{
		$msgdata = $this->fetch_message(array(
			"mailbox" => $arr["mailbox"],
			"msgrid" => $arr["msgrid"],
			"msgid" => $arr["msgid"],
		));

		$msgobj = $this->_create_draft(array(
			"msgrid" => $arr["msgrid"],
		));

		$hdr = t("----Forwarded message-----\n");
		$hdr .= "> From: $msgdata[from]\n";
		$hdr .= "> To: $msgdata[to]\n";
		$hdr .= "> Subject: $msgdata[subject]\n";
		$hdr .= "> Date: $msgdata[date]\n\n";

		$msgobj->set_name("Fwd: " . $msgdata["subject"]);
		$msgobj->set_prop("message","\n\n\n" . $hdr . "> " . str_replace("\n","\n> ",$msgdata["content"]));
		$msgobj->save();
		
		$arr["id"] = $msgobj->id();
		return $this->_gen_edit_url($arr);
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
		$row["meta"] = aw_unserialize($row["metadata"]);
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
