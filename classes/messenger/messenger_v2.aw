<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/messenger_v2.aw,v 1.5 2003/09/17 12:45:22 duke Exp $
// messenger_v2.aw - Messenger V2 
/*

@classinfo syslog_type=ST_MESSENGER relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

property identity type=relpicker reltype=RELTYPE_MAIL_IDENTITY
caption Identiteet

@property fromname type=textbox 
@caption Kellelt

@property config type=relpicker reltype=RELTYPE_MAIL_CONFIG
@caption Konfiguratsioon

@property write_mail type=callback callback=callback_write_mail no_caption=1 group=write_mail
@caption Uus kiri

@property mail_toolbar type=toolbar no_caption=1 group=message_view store=no
@caption Msg. toolbar

@property msg_cont type=text group=message_view no_caption=1 wrapchildren=1
@caption Konteiner

@property mailbox type=hidden group=message_view 
@caption Mailbox ID (sys)

@property treeview type=text group=message_view parent=msg_cont
@caption Folderid

@property message_view type=table no_caption=1 group=message_view store=no parent=msg_cont
@caption Kirjad

@property msg_outbox type=relpicker reltype=RELTYPE_FOLDER
@caption Outbox

@property msg_drafts type=relpicker reltype=RELTYPE_FOLDER
@caption Mustandite kataloog

@property autofilter_delay type=select
@caption Filtrite käivitamise intervall

@property testfilters type=text 
@caption Testi filtreid

@groupinfo message_view caption="Kirjad" submit=no
@groupinfo write_mail caption="Uus kiri" submit=no

*/
define("RELTYPE_MAIL_IDENTITY",1); // things that appear on the From lines etc...
define("RELTYPE_MAIL_SOURCE",2); // pop3, imap, etc..
define("RELTYPE_MAIL_CONFIG",3); // millist konfiguratsiooni kasutada
define("RELTYPE_FOLDER",4); // kataloog kuhu maile salvestada .. kehtib ainult local delivery korral
define("RELTYPE_ADDRESS",5); // used to specify delivery addresses .. which can be AW lists for example
define("RELTYPE_RULE",6); // maili ruul

class messenger_v2 extends class_base
{
	function messenger_v2()
	{
		$this->init(array(
			"tpldir" => "messenger_v2",
			"clid" => CL_MESSENGER_V2
		));
		$this->connected = false;
		$this->outbox = "INBOX.Sent-mail";
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "message_view":
				if ($arr["request"]["msgid"])
				{
					$data["value"] = $this->gen_message_view($arr);
					$data["type"] = "text";
				}
				else
				{
					$data["value"] = $this->gen_message_list($arr);
				};
				break;

			case "mail_toolbar":
				$data["value"] = $this->gen_mail_toolbar($arr);
				break;

			case "treeview":
				$data["value"] = $this->make_folder_tree($arr);
				break;

			case "mailbox":
				$data["value"] = $this->use_mailbox;
				break;
				
			case "autofilter_delay":
				$data["options"] = array("0" => "--","3" => "3","5" => "5","10" => "10");
				break;

			case "testfilters":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("test_filters",array("id" => $arr["obj"]["oid"])),
					"caption" => $data["caption"],
				));
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
			case "write_mail":
				$this->submit_write_mail($arr);
				break;

			case "autofilter_delay":
				$this->schedule_filtering($arr);
				break;

		}
		return $retval;
	}	

	function callback_pre_edit($arr)
        {
                $id = $arr["coredata"]["oid"];
		$mailbox = isset($arr["request"]["mailbox"]) ? $arr["request"]["mailbox"] : "INBOX"; 
		$this->use_mailbox = $mailbox;
		/*
		print "preedit handler<br>";
		print "this is where we can validate data for messenger $id<br>";
		*/
	

	}	

	function _connect_server($arr)
	{
		if (!$this->connected)
		{
			$this->msgobj = new object($arr["msgr_id"]);
			$conns = $this->msgobj->connections_from(array("type" => RELTYPE_MAIL_SOURCE));
			
			// right now it only deals with a single server.
			$_sdat =$conns[0];
			$sdat = new object($_sdat->to());

			$this->drv_inst = get_instance("protocols/mail/imap");
			$this->drv_inst->set_opt("use_mailbox",$this->use_mailbox);
			$this->drv_inst->set_opt("outbox",$this->outbox);
			$this->drv_inst->connect_server(array("id" => $_sdat->to()));
			$this->drv_inst->set_opt("messenger_id",$arr["msgr_id"]);

			$this->mbox = $this->drv_inst->get_opt("mbox");
			$this->servspec = $this->drv_inst->get_opt("servspec");
                        $this->mboxspec = $this->drv_inst->get_opt("mboxspec");

			$this->connected = true;

			$msg_cfg = $this->msgobj->prop("config");
			if (!empty($msg_cfg))
			{
				$msg_cfg_obj = new object($msg_cfg);
				$this->perpage = $msg_cfg_obj->prop("msgs_on_page");
			};
		
			$this->mailboxlist = $this->drv_inst->list_folders();
		};
	}

	function gen_message_list(&$arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["obj"]["oid"],
		));

		$perpage = empty($this->perpage) ? 50 : $this->perpage;

		$ft_page = (int)$GLOBALS["ft_page"];

		$contents = $this->drv_inst->get_folder_contents(array(
			"from" => $perpage * $ft_page + 1,
			"to" => $perpage * ($ft_page + 1),
		));

		$count = $this->drv_inst->count;

		$t = &$arr["prop"]["obj_inst"];
		$t->parse_xml_def("messenger/mailbox_view");


		$t->d_row_cnt = $count;

		$pageselector = "";

		if ($t->d_row_cnt > $perpage)
		{
			$pageselector = $t->draw_lb_pageselector(array(
				"records_per_page" => $perpage
			));
		};

		$t->table_header = $this->navibar . $pageselector;

		foreach($contents as $key => $message)
		{
			$seen = $message["seen"];
			$t->define_data(array(
				"mark" => html::checkbox(array(
					"name" => "mark[" . $key . "]",
					"value" => 1,
				)),
				"from" => $this->_format(htmlspecialchars($message["from"]),$seen),
				"subject" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
							"id" => $arr["obj"]["oid"],
							"msgid" => $key,
							"group" => $arr["request"]["group"],
							"mailbox" => $this->use_mailbox,
					)),
					"caption" => $this->_format(parse_obj_name($message["subject"]),$seen),
				)),
				"date" => $this->_format(date("H:i d-M",strtotime($message["date"])),$seen),
				"size" => $this->_format(sprintf("%dK",$message["size"]/1024),$seen),
				"seen" => $this->_format($this->_conv_stat($message["seen"]),$seen),
				"answered" => $this->_format($this->_conv_stat($message["answered"]),$seen),
			));
		};

	}

	function _format($str,$flag)
	{
		$str = wordwrap($str,20,"\n",1);
		return ($flag) ? $str : "<strong>$str</strong>";


	}

	function _conv_stat($code)
	{
		return ($code == 0) ? "ei" : "jah";
	}

	function make_folder_tree($arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["obj"]["oid"],
		));

		$rv = "";

		$boxes = array();
		$boxes[0][1] = array("name" => "IMAP");
		
		// I have to enumerate those mailboxes, because the current DHTML
		// trees uses names as unique identifiers for tree branches ..
		// having special characters in them breaks javascript syntax
		$enum = array();
		$i = 1;

		foreach($this->mailboxlist as $key => $val)
		{
			$i++;
			$enum[$val["name"]] = $i;
			
			// kui mailboxi nimi ei sisalda punkti, siis on tegemist esimese taseme folderiga
			// tegelikult .. eraldaja määratakse ära namespacega, ilmselt võib olla see ka
			// midagi muud kui punkt. aga praegu piisab punktist.
			if (strpos($val["name"],".") === false)
			{
				$parent = 1;
				$boxes[1][$i] = array("name" => $val["name"]);
				$name = $val["name"];
			}
			else
			{
				$parent = $enum[substr($val["name"],0,strrpos($val["name"],"."))];
				$name = substr($val["name"],strrpos($val["name"],".")+1);
			};

			if ($val["name"] == $this->use_mailbox)
			{
				$name = "<strong>$name</strong>";
			};

			$boxes[$parent][$i] = array(
				"name" => $name . " " . $val["count"],
				"link" => $this->mk_my_orb("change",array(
					"id" => $arr["obj"]["oid"],
					"group" => "message_view",
					"mailbox" => $val["name"],
				)),
			);
				
		}

		$treeview = get_instance("vcl/treeview");
                $res =  $treeview->create_tree_from_array(array(
                        "parent" => 1,
                        "data" => $boxes,
                        "shownode" => $enum[$this->use_mailbox],
                        "linktarget" => "_self",
                ));

		$rv .= $res;
		return $rv;
	}

	function gen_mail_toolbar($arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["obj"]["oid"],
		));

		$toolbar = &$arr["prop"]["toolbar"];

		$req_uri = aw_global_get("REQUEST_URI");

	
		if (!empty($arr["request"]["msgid"]))
		{
			$toolbar->add_cdata(html::href(array(
				"url" => $this->mk_my_orb("change",array("id" => $arr["obj"]["oid"],"msgid" => $arr["request"]["msgid"],"group" => "write_mail","mailbox" => $this->use_mailbox)),
				"caption" => "Vasta",
			)));
			$toolbar->add_separator();
		}
		else
		{
			$toolbar->add_cdata(html::href(array(
				"url" => "#",
				"caption" => "Tee uus kataloog",
			)));
			$toolbar->add_separator();
		};


		$toolbar->add_cdata("Vii kirjad");

		$_tmp = array();
		foreach($this->mailboxlist as $item)
		{
			$_tmp[$item["name"]] = $item["name"];
		}

		$toolbar->add_cdata(html::select(array(
			"name" => "move_to_folder",
			"options" => $_tmp,
		)));
		$toolbar->add_button(array(
			"name" => "move",
			"tooltip" => "Vii valitud kirjad kataloogi",
			"url" => "javascript:document.changeform.subgroup.value='move_messages';document.changeform.submit();",
			"img" => "save.gif",
			"imgover" => "save_over.gif",
		));

		$toolbar->add_separator();
			
		
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta märgitud kirjad",
			"url" => "javascript:document.changeform.subgroup.value='delete_messages';document.changeform.submit();",
			"img" => "delete.gif",
			"imgover" => "delete_over.gif",
		));

		$pieces = explode(".",$this->use_mailbox);
		$navibar = "";

		if ($this->use_mailbox != "INBOX")
		{
			$navibar = html::href(array(
				"url" => $this->mk_my_orb("change",array("id" => $arr["obj"]["oid"],"group" => "message_view")),
				"caption" => "INBOX",
			));
		};

		for ($i = 0; $i < count($pieces); $i++)
		{
			if ($i > 0)
			{
				$path .= ".";
			};
			$path .= $pieces[$i];
			$navibar .= " / ";
			$navibar .= html::href(array(
				"url" => $this->mk_my_orb("change",array("id" => $arr["obj"]["oid"],"group" => "message_view","mailbox" => $path)),
				"caption" => $pieces[$i],
			));
		}

		$this->navibar = $navibar . "<br>";

	}

	function gen_message_view($arr)
	{
		$msgid = $arr["request"]["msgid"];
		$this->_connect_server(array(
			"msgr_id" => $arr["obj"]["oid"],
		));


		$msgdata = $this->drv_inst->fetch_message(array(
				"msgid" => $msgid,
		));

		$cont = htmlspecialchars($msgdata["content"]);
		$cont = nl2br(create_links($cont));

		// would be nice to have a way to generate the message view from the properties in mail_message
		// holy cow, how on earth am I going to do that?
		$this->read_template("plain.tpl");
		$this->sub_merge = 1;

		$this->vars(array(
			"from" => htmlspecialchars(parse_obj_name($msgdata["from"])),
			"reply_to" => htmlspecialchars(parse_obj_name($msgdata["reply_to"])),
			"to" => htmlspecialchars(parse_obj_name($msgdata["to"])),
			"subject" => htmlspecialchars(parse_obj_name($msgdata["subject"])),
			"date" => $msgdata["date"],
			"content" => $cont,
		));

		if (is_array($msgdata["attachments"]))
		{
			foreach($msgdata["attachments"] as $num => $data)
			{
				$this->vars(array(
					"part_name" => $data,
					"get_part_url" => $this->mk_my_orb("get_part",array(
						"id" => $arr["obj"]["oid"],
						"msgid" => $msgid,
						"mailbox" => $this->use_mailbox,
						"part" => $num,
					)),
				));
				$this->parse("attachment");
			};
		};
		return $this->parse();
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}

	function callback_write_mail($arr)
	{
		$msgobj = new object($arr["obj"]["oid"]);

		$this->_connect_server(array(
			"msgr_id" => $arr["obj"]["oid"],
		));

		$outbox = $msgobj->prop("msg_outbox");

		$drafts = $msgobj->prop("msg_drafts");

		$mailbox = $arr["request"]["mailbox"];
		$msgid = $arr["request"]["msgid"];

		$msgdata = array();
		if (!empty($msgid))
		{
			$msgdata = $this->drv_inst->fetch_message(array(
				"msgid" => $msgid,
			));
		};

		$t = get_instance("messenger/mail_message");

		if (!empty($drafts))
		{
			// I have to create an empty mail_message object
			$t->id_only = true;
			$_msg_id = $t->submit(array(
				"mfrom" => "",
				"mto" => "",
				"message" => "",
				"parent" => $drafts,
			));
		}

                $t->init_class_base();
                $emb_group = "general";

		if (!empty($_msg_id))
		{
			$t->id = $_msg_id;
		};

                $all_props = $t->get_active_properties(array(
                        "group" => $emb_group,
                ));

                $t->request = $args["request"];

                $all_props[] = array("type" => "hidden","name" => "class","value" => "mail_message");
                $all_props[] = array("type" => "hidden","name" => "action","value" => "submit");
                $all_props[] = array("type" => "hidden","name" => "group","value" => $emb_group);
                $all_props[] = array("type" => "hidden","name" => "parent","value" => $outbox);
		$all_props[] = array("type" => "hidden","name" => "id","value" => $_msg_id);

		// yah, I know .. this sucks -- duke
		unset($all_props["uidl"]);

		$all_props["mfrom"]["value"] = $msgobj->prop("fromname");

		$related_lists = $msgobj->connections_from(array(
			"type" => RELTYPE_ADDRESS,
		));

		if (sizeof($related_lists) > 0)
		{
			$all_props["mto"]["type"] = "relpicker";
			$all_props["mto"]["reltype"] = "RELTYPE_ADDRESS";
			$all_props["mto"]["size"] = 1;
		};

		if (sizeof($msgdata) > 0)
		{
			$all_props["mto"]["value"] = !empty($msgdata["reply_to"]) ? $msgdata["reply_to"] : $msgdata["from"];
			$all_props["name"]["value"] = "Re: " . $msgdata["subject"];
			$all_props["message"]["value"] = "\n\n\n" . str_replace("\n","\n> ",$msgdata["content"]);
		};

                return $t->parse_properties(array(
                        "properties" => $all_props,
                        "name_prefix" => "emb",
			// aga raiks .. see viitab ju messengeri objektile nüüd, mitte
			// kirja omale, mida mul ju tegelikult vaja oleks
			"target_obj" => $msgobj->id(),
                ));
	}
	
	function submit_write_mail($arr)
	{
                $emb = $arr["form_data"]["emb"];
		// I will not submit data, instead I'll store it into the "Sent" folder
		// on the IMAP server .. yeees, yees, that is really cool

		$obj_id = $arr["form_data"]["id"];
		$this->_connect_server(array(
			"msgr_id" => $arr["form_data"]["id"],
		));

		$to_addr = $emb["mto"];

		// heh, this means you can send mail directly to any object just by writing it's id here
		// it's bad, and I'll fix it later --duke
		if (is_numeric($to_addr))
		{
			$target_obj = new object($to_addr);
			if ($target_obj->prop("class_id") == CL_ML_LIST)
			{
				// now then .. how the fuck am I going to put that constructed message
				// into the outbound queue of this list? eh? mh? ah?

				// and do I have to save it as an AW object as well? eh?
				if (isset($emb["group"]))
				{
					$this->emb_group = $emb["group"];
				};

				$lists = array(":" . $target_obj->prop("name"));
				
				// store it in the server outbox as well
				$this->drv_inst->store_message(array(
					"from" => $emb["mfrom"],
					"to" => join(",",$lists),
					"subject" => $emb["name"],
					"message" => $emb["message"],
				));

				$t = get_instance("messenger/mail_message");
                		$t->id_only = true;
				unset($emb["send"]);
				$msg_id = $t->submit($emb);
				$mllist=get_instance("mailinglist/ml_list");
                        	$route_back=$this->mk_my_orb("change",array("id" => $obj_id,"group" => "message_view"));
                        	aw_session_set("route_back",$route_back);
                        	// scheduleerib kirjade saatmise
                        	$url=$mllist->route_post_message(array("id" => $msg_id, "targets" => $lists));
                        	Header("Location: $url");
                        	die();
			};
		}
		else
		{
			$this->messageobj = new object($emb["id"]);
			$conns = $this->messageobj->connections_from();

			$partnum = 1;

			$awf = get_instance("file");

			if (sizeof($conns) > 0)
			{
				$partdata = array(
					"type" => TYPEMULTIPART,
					"subtype" => "mixed",
				);
				$body[$partnum] = $partdata;
				$partnum++;
			};
			
			$part1["type"]= "TEXT";
			$part1["subtype"]="PLAIN";
			$part1["charset"] = "ISO-8859-4";
			$part1["contents.data"] = $emb["message"];

			$body[$partnum] = $part1;
			$partnum++;

			// right now it only deals with a single server.
			foreach($conns as $connection)
			{

				// the general idea here is to create a specially crafted attachment,
				// which will contain meta information about all the attached objects
				// .. non-AW clients will then show the plain old attachments
				// but AW messenger will then be able to extract required information
				// from that special block and use it when saving objects inside AW
				$sdat = new object($connection->to());
				// so, yes, I really need to get rid of that cycle here
				if ($sdat->prop("class_id") == CL_FILE)
				{
					$contents = $awf->get_file_by_id($sdat->id());
					$type = explode("/",$contents["type"]);

					$partdata = array(
						"type" => $type[0],
						"encoding" => ENCBINARY,
						"subtype" => $type[1],
						"description" => $sdat->name(),
						"contents.data" => $contents["content"],
						"disposition.type" => "attachment",
						"disposition" => array("filename" => $sdat->name()),
					);

					$body[$partnum] = $partdata;
					$partnum++;

				};
			};


			$envelope = array();
			$envelope["from"] = $emb["mfrom"];
			$envelope["subject"] = $emb["name"];
			$envelope["date"] = date('r');

			$msg = imap_mail_compose($envelope,$body);

			mail($emb["mto"],$emb["name"],"",$msg);
			
			// store it in the server outbox as well
			$this->drv_inst->store_message(array(
				"from" => $emb["mfrom"],
				"to" => $emb["mto"],
				"subject" => $emb["name"],
				"message" => $msg,
			));

			// redirect to inbox .. not the smartest thing to do, but hey
			$this->redir_to_group = "message_view";
		};

                return PROP_OK;

	}

	function schedule_filtering($arr)
	{
		if ($arr["prop"]["value"] > 0)
		{
			$sched = get_instance("scheduler");
			$sched->add(array(
				"event" => $this->mk_my_orb("run_filters", array("id" => $arr["obj"]["oid"]), "", false, true),
				"time" => time()+($arr["prop"]["value"] * 60),   
			));
		};
	}

	////
	// !called from ORB/scheduler, runs all the filter on INBOX
	function run_filters($arr)
	{
		$msgr_obj = new object($arr["id"]);
		if ($msgr_obj->prop("autofilter_delay") > 0)
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));

			$this->preprocess_filters();
			
			$rv = $this->do_filters();
		
			print $rv;
			
			// reschedule
			$sched = get_instance("scheduler");
			$sched->add(array(
				"event" => $this->mk_my_orb("run_filters", array("id" => $arr["id"]), "", false, true),
				"time" => time()+($msgr_obj->prop("autofilter_delay") * 60),   
			));
		
		};
		// stop processing, will ya?
		die();

	}

	function test_filters($arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["id"],
		));
			
		$this->preprocess_filters();

		$rv = $this->do_filters(array("dryrun" => 1));

		if ($this->done === false)
		{
			$rv = "ükski kiri INBOXis ei matchinud ühegi ruuliga<br>";
		};
		return "<pre>" . $rv . "</pre>";

	}

	function preprocess_filters()
	{
		$conns = $this->msgobj->connections_from(array("type" => RELTYPE_RULE));

		if (sizeof($conns) == 0)
		{
			return "ühtegi ruuli pole seostatud";
		};

		$rv = "";

		$this->subjrules = $this->fromrules = $this->targets = array();

		foreach($conns as $item)
		{
			$filter_obj = new object($item->to());

			$from_rule = $filter_obj->prop("rule_from");
			$subj_rule = $filter_obj->prop("rule_subject");
			$target_folder = $filter_obj->prop("target_folder");
			$id = $filter_obj->id();

			if (!empty($from_rule))
			{
				$this->fromrules[$id] = $from_rule;
			}

			if (!empty($subj_rule))
			{
				$this->subjrules[$id] = $subj_rule;
			};

			if (!empty($target_folder))
			{
				$this->targets[$id] = $target_folder;
			};
		};

	}

	function do_filters($arr = array())
	{
		// now I need read the messages
		$contents = $this->drv_inst->get_folder_contents(array(
			"from" => 1,
			"to" => "*",
		));

		$rv = "";

		$this->done = false;

		$move_ops = array();

		foreach($contents as $mkey => $message)
		{
			foreach($this->subjrules as $key => $val)
			{
				if (strpos($message["subject"],$val) !== false)
				{
					$rv .= sprintf("<strong>message with subject: %s</strong><br>",$message["subject"]);
					$target = $this->targets[$key];
					$rv .= " &nbsp; &nbsp; uid = $mkey, matches subject rule $key, moving to $target folder<br>";
					$this->done = true;
					$move_ops[$target][] = $mkey;
					break;

				}
			};
			foreach($this->fromrules as $key => $val)
			{
				if (strpos($message["from"],$val) !== false)
				{
					$rv .= sprintf("<strong>message with subject: %s</strong><br>",$message["subject"]);
					$target = $this->targets[$key];
					$rv .= " &nbsp; &nbsp; matches from rule $key, moving to $target folder<br>";
					$move_ops[$target][] = $mkey;
					$this->done = true;
					break;

				}
			};
		}


		if (empty($arr["dryrun"]) && sizeof($move_ops) > 0)
		{
			foreach($move_ops as $folder => $keys)
			{
				$rv .= $this->drv_inst->move_messages(array(
					"id" => $keys,
					"to" =>  $folder,
				));
			};
		};
		return $rv;
	}

	////
	// !Can be used to download message parts
	function get_part($arr)
	{
		$msgid = $arr["msgid"];
		$this->_connect_server(array(
			"msgr_id" => $arr["id"],
		));

		$this->drv_inst->fetch_part(array(
			"msgid" => $msgid,
			"part" => $arr["part"],
		));
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_MAIL_IDENTITY => "messengeri identiteet",
			RELTYPE_MAIL_SOURCE => "mailikonto",
			RELTYPE_MAIL_CONFIG => "messengeri konfiguratsioon",
			RELTYPE_FOLDER => "kataloog",
			RELTYPE_ADDRESS => "adressaat",
			RELTYPE_RULE => "maili ruul",
		);
	} 

	function callback_get_classes_for_relation($arr)
	{
		$retval = false;
		switch($arr["reltype"])
		{
                        case RELTYPE_MAIL_SOURCE:
                                $retval = array(CL_PROTO_IMAP);
                                break;

			case RELTYPE_ADDRESS:
				$retval = array(CL_ML_LIST);
				break;

			case RELTYPE_FOLDER:
				$retval = array(CL_MENU);
				break;

			case RELTYPE_MAIL_CONFIG:
				$retval = array(CL_MESSENGER_CONFIG);
				break;

			case RELTYPE_RULE:
				$retval = array(CL_MAIL_RULE);
				break;
		};
		return $retval;
	}

	function callback_post_save($arr)
	{
		$this->redir_to_mailbox = "";
		if (!empty($arr["form_data"]["mailbox"]) && ($arr["form_data"]["mailbox"] != "INBOX"))
		{
			$this->redir_to_mailbox = $arr["form_data"]["mailbox"];
		};
		if (($arr["form_data"]["subgroup"] == "delete_messages") && is_array($arr["form_data"]["mark"]) && sizeof($arr["form_data"]["mark"]) > 0)
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));
			$this->drv_inst->delete_msgs_from_folder(array_keys($arr["form_data"]["mark"]));
		};
		if (($arr["form_data"]["subgroup"] == "move_messages") && is_array($arr["form_data"]["mark"]) && sizeof($arr["form_data"]["mark"]) > 0)
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));

			$rv = $this->drv_inst->move_messages(array(
				"id" => array_keys($arr["form_data"]["mark"]),
				"to" =>  $arr["form_data"]["move_to_folder"],
			));

			$this->redir_to_mailbox = $arr["form_data"]["mailbox"];

			#$this->drv_inst->delete_msgs_from_folder(array_keys($arr["form_data"]["mark"]));
		};
	}

	function callback_mod_retval($arr)
	{
		$args = &$arr["args"];
		if (!empty($this->redir_to_mailbox))
		{
			$args["mailbox"] = $this->redir_to_mailbox;
		}
		if (!empty($this->redir_to_group))
		{
			$args["group"] = $this->redir_to_group;
		}
	}

}
?>
