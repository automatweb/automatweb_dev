<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/messenger_v2.aw,v 1.20 2003/11/09 22:15:55 duke Exp $
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

@property user_messenger type=checkbox 
@caption Kasutaja default messenger

@property mailbox type=hidden group=main_view
@caption Mailbox ID (sys)


// --- mailbox view group
@property mail_toolbar type=toolbar no_caption=1 group=main_view store=no
@caption Msg. toolbar

@property msg_cont type=text group=main_view no_caption=1 wrapchildren=1
@caption Konteiner

@property treeview type=text parent=msg_cont group=main_view
@caption Folderid

@property message_list type=table no_caption=1 group=main_view parent=msg_cont store=no 
@caption Kirjad

// muu mudru

property main_view type=callback callback=callback_gen_main_view no_caption=1 group=main_view 
caption Peavaade

@property msg_outbox type=relpicker reltype=RELTYPE_FOLDER
@caption Outbox

@property msg_drafts type=relpicker reltype=RELTYPE_FOLDER
@caption Mustandite kataloog

@property autofilter_delay type=select
@caption Filtrite käivitamise intervall
@comment Minutites

@property testfilters type=text 
@caption Testi filtreid

@groupinfo main_view caption="Kirjad" submit=no

@reltype MAIL_IDENTITY value=1 clid=CL_MESSENGER_IDENTITY
@caption messengeri identiteet

@reltype MAIL_SOURCE value=2 clid=CL_PROTO_IMAP
@caption mailikonto

@reltype MAIL_CONFIG value=3 clid=CL_MESSENGER_CONFIG
@caption messengeri konfiguratsioon

@reltype FOLDER value=4 clid=CL_MENU
@caption kataloog

@reltype ADDRESS value=5 clid=CL_ML_LIST
@caption adressaat

@reltype RULE value=6 clid=CL_MAIL_RULE
@caption maili ruul
                        
*/

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

	function my_messages($arr)
	{
		$users = get_instance("users");
		$obj_id = $users->get_user_config(array(
				"uid" => aw_global_get("uid"),
				"key" => "user_messenger"));

		if (empty($obj_id))
		{
			return "kulla mees, sa pole omale default messengeri ju valinud?";
		};
		$arr["id"] = $obj_id;
		$arr["group"] = "main_view";
		return $this->change($arr);


	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "user_messenger":
				$users = get_instance("users");
				$obj_id = $arr["obj_inst"]->id();

				$data['value'] = $users->get_user_config(array(
					"uid" => aw_global_get("uid"),
					"key" => "user_messenger",
				));
                                $data['ch_value'] = $arr["obj_inst"]->id();
				break;

			case "message_list":
				$data["value"] = $this->gen_message_list($arr);
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
					"url" => $this->mk_my_orb("test_filters",array("id" => $arr["obj_inst"]->id())),
					"caption" => $data["caption"],
				));
				break;

			case "currentfolder":
				$data["value"] = $this->use_mailbox;
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

			case "user_messenger":
				$users = get_instance("users");
				$users->set_user_config(array(
					"uid" => aw_global_get("uid"),
					"key" => "user_messenger",
					"value" => $data["value"],
				));
				break;

                        break;

		}
		return $retval;
	}	

	function callback_pre_edit($arr)
        {
		$mailbox = isset($arr["request"]["mailbox"]) ? $arr["request"]["mailbox"] : "INBOX"; 
		$name = $arr["obj_inst"]->name();
		aw_global_set("title_action",$name);
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
			global $awt;
			$this->msgobj = new object($arr["msgr_id"]);
			$conns = $this->msgobj->connections_from(array("type" => RELTYPE_MAIL_SOURCE));

			
			// right now it only deals with a single server.
			$_sdat =$conns[0];
			$sdat = new object($_sdat->to());

			$this->_name = $sdat->prop("name");

			$this->drv_inst = get_instance("protocols/mail/imap");
			$this->drv_inst->set_opt("use_mailbox",$this->use_mailbox);
			$this->drv_inst->set_opt("outbox",$this->outbox);
			$awt->start("imap-server-connect");
			$this->drv_inst->connect_server(array("obj_inst" => $_sdat->to()));
			$awt->stop("imap-server-connect");
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
			$awt->start("imap-list-folders");	
			if (!$arr["no_folders"])
			{
				$this->mailboxlist = $this->drv_inst->list_folders();
			};
			$awt->stop("imap-list-folders");
		};
	}

	function gen_message_list(&$arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["obj_inst"]->id(),
			"no_folders" => true,
		));

		$perpage = empty($this->perpage) ? 50 : $this->perpage;

		$ft_page = (int)$GLOBALS["ft_page"];

		global $awt;
		$awt->start("list-folder-contents");
		$contents = $this->drv_inst->get_folder_contents(array(
			"from" => $perpage * $ft_page + 1,
			"to" => $perpage * ($ft_page + 1),
		));

		$awt->stop("list-folder-contents");

		$count = $this->drv_inst->count;

		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("messenger/mailbox_view");


		$t->d_row_cnt = $count;

		$pageselector = "";

		if ($t->d_row_cnt > $perpage)
		{
			$pageselector = $t->draw_lb_pageselector(array(
				"records_per_page" => $perpage
			));
		};

		$fldr = $this->use_mailbox;	

		$t->table_header = $dump . $pageselector;

		foreach($contents as $key => $message)
		{
			$seen = $message["seen"];
			$fromline = "";
			if (!empty($message["fromn"]))
			{
				$fromline = html::href(array(
					"url" => "javascript:void();",
					"title" => $message["froma"],
					"caption" => substr($message["fromn"],0,1),
				)) . substr($message["fromn"],1);
			}
			else
			{
				$fromline = $message["from"];
			};

			// this should be unique enough
			$wname = "msgr" . $key;

			$t->define_data(array(
				"mark" => html::checkbox(array(
					"name" => "mark[" . $key . "]",
					"value" => 1,
				)),
				"from" => $this->_format($fromline,$seen),
				"subject" => html::href(array(
					"url" => "javascript:aw_popup_scroll(\"" . $this->mk_my_orb("change",array(
							"msgrid" => $arr["obj_inst"]->id(),
							"msgid" => $key,
							"mailbox" => $this->use_mailbox,
							"subgroup" => "show",
					),"mail_message",false,true) . "\",\"$wname\",800,600)",
					"caption" => $this->_format(parse_obj_name($message["subject"]),$seen),
				)),
				"date" => $message["tstamp"],
				"size" => $this->_format(sprintf("%d",$message["size"]/1024),$seen),
				"answered" => $this->_format($this->_conv_stat($message["answered"]),$seen),
				"attach" => $message["has_attachments"] ? html::img(array("url" => $this->cfg["baseurl"] . "/automatweb/images/attach.gif")) : "",
			));
		};

		$t->set_default_sortby("date");
		$t->set_default_sorder("desc");

	}


	function _format($str,$flag)
	{
		//$str = wordwrap($str,20,"\n",1);
		return ($flag) ? $str : "<strong>$str</strong>";


	}

	function _conv_stat($code)
	{
		return ($code == 0) ? "ei" : "jah";
	}

	////
	// !Returns full name from the address
	function _conv_addr($addr)
	{
		if (preg_match("/(.*)</",$addr,$m))
		{
			return $m[1];
		}
		else
		{
			return $addr;
		};
	}

	function make_folder_tree($arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["obj_inst"]->id(),
		));

		$rv = "";

		// I have to enumerate those mailboxes, because the current DHTML
		// trees uses names as unique identifiers for tree branches ..
		// having special characters in them breaks javascript syntax
		$enum = array();

		$tree = get_instance("vcl/treeview");
		$tree->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "msgr_tree", // what if there are multiple messengers?
			"persist_state" => 1,
		));
		
		$i = 1;

		$tree->add_item(0,array(
			"name" => parse_obj_name($this->_name),
			"id" => $i,
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["obj_inst"]->id(),
				"group" => "main_view",
			)),
		));

		$i++;

		$tree->add_item(0,array(
			"name" => "Local folders",
			"id" => $i,
		));

		$local_fld = $i;
		$this->localfolders = array();
		$conns = $this->msgobj->connections_from(array("type" => RELTYPE_FOLDER));
		foreach($conns as $folder_item)
		{
			$i++;
			$sdat = new object($folder_item->to());
			$tree->add_item($local_fld,array(
				"name" => $sdat->prop("name"),
				"id" => $i,
				"url" => $this->mk_my_orb("change",array(
					"id" => $arr["obj_inst"]->id(),
					"group" => $arr["prop"]["group"],
					"localmailbox" => $sdat->id(),
				)),
			));
			$this->localfolders[$sdat->id()] = $sdat->prop("name");
		};

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

			$tree->add_item($parent,array(
				"name" => $name . " " . $val["count"],
				"id" => $i,
				"url" => $this->mk_my_orb("change",array(
					"id" => $arr["obj_inst"]->id(),
					"group" => "main_view",
					"mailbox" => $val["int_name"],
				)),
			));
				
		}

		$res .= $tree->finalize_tree();

		$rv .= $res;
		return $rv;
	}


	function gen_mail_toolbar($arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["obj_inst"]->id(),
		));
		
		$drafts = $this->msgobj->prop("msg_drafts");

		$toolbar = &$arr["prop"]["toolbar"];

		// this is wrong, I have to create the the empty message body first and then allow 
		// editing it. For this to work, I need to figure out the location of the drafts folder ..
		// mind you .. that folder can also be on the server

		// aga kuda kurat ma seda popupi kaudu teen, see peaks mingi redirect olema siis
		$toolbar->add_button(array(
			"name" => "newmessage",
			"tooltip" => "Uus kiri",
			"url" => "javascript:aw_popup_scroll('" . $this->mk_my_orb("new",array("parent" => $drafts,"msgrid" => $this->msgobj->id()),"mail_message",false,true) . "','msgr',800,600)",
			"img" => "new.gif",
		));

		$toolbar->add_separator();

	
		$_tmp = array("0" => "Vii kirjad");
		foreach($this->mailboxlist as $item)
		{
			$_tmp[$item["name"]] = str_repeat("&nbsp;",4*(substr_count($item["name"],".")+1)) . $item["realname"];
		}

		$toolbar->add_cdata(html::select(array(
			"name" => "move_to_folder",
			"selected" => 0,
			"options" => $_tmp,
		)),"right");
		$toolbar->add_button(array(
			"name" => "move",
			"tooltip" => "Vii valitud kirjad kataloogi",
			"url" => "javascript:document.changeform.subgroup.value='move_messages';document.changeform.submit();",
			"img" => "import.gif",
			"side" => "right",
		));

		$toolbar->add_separator(array(
			"side" => "right",
		));
			
		
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta märgitud kirjad",
			"url" => "javascript:document.changeform.subgroup.value='delete_messages';document.changeform.submit();",
			"img" => "delete.gif",
			"side" => "right",
		));
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

	////
	// !this is the main function that will generate different views .. and really all
	// the other stuff we are going to possibly need
	function callback_gen_main_view($arr)
	{
		// okey, lets try this thing out
		// I'm going to return different sets of properties each time

		// would be nice if I could get a list of all active properties

		$prop1 = $this->all_props["mail_toolbar"];
		$prop2 = $this->all_props["msg_cont"];
		$prop3 = $this->all_props["treeview"];
		$prop3["group"] = $arr["request"]["group"];
		$prop3["cb_view"] = $arr["request"]["cb_view"];
		if ($arr["request"]["msgid"])
		{
			$prop4 = $this->all_props["message_view"];
		}
		else
		if ($arr["request"]["write"])
		{
			$prop4 = $this->all_props["write_mail"];
		}
		else
		{
			$prop4 = $this->all_props["message_list"];
		};

		$rv = array($prop1,$prop2,$prop3,$prop4);

		return $rv;

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
			$t = get_instance("messenger/mail_message");
                	$t->id_only = true;
			unset($emb["send"]);
			$msg_id = $t->submit($emb);

			if ($emb["savedraft"])
			{
				// this should redirect us right back to at where we were before
				$this->msgobj = $emb["id"];
				return;
			};

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
			$this->redir_to_group = "main_view";
		};

                return PROP_OK;

	}

	function schedule_filtering($arr)
	{
		if ($arr["prop"]["value"] > 0)
		{
			$sched = get_instance("scheduler");
			$sched->add(array(
				"event" => $this->mk_my_orb("run_filters", array("id" => $arr["obj_inst"]->id()), "", false, true),
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

	function callback_post_save($arr)
	{
		$this->redir_to_mailbox = "";
		if (!empty($arr["request"]["mailbox"]) && ($arr["request"]["mailbox"] != "INBOX"))
		{
			$this->redir_to_mailbox = $arr["request"]["mailbox"];
		};

		$marked = is_array($arr["request"]["mark"]) && sizeof($arr["request"]["mark"]) > 0 ? $arr["request"]["mark"] : false;

		if (($arr["request"]["subgroup"] == "delete_messages") && is_array($marked))
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));
			$this->drv_inst->delete_msgs_from_folder(array_keys($marked));
		};

		if (($arr["request"]["subgroup"] == "move_messages") &&
			$arr["request"]["move_to_folder"] !== 0 &&
			is_array($marked))
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));

			$rv = $this->drv_inst->move_messages(array(
				"id" => array_keys($marked),
				"to" =>  $arr["request"]["move_to_folder"],
			));

			$this->redir_to_mailbox = $arr["request"]["mailbox"];

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

	function _get_identity_list($arr)
	{
		$msgrobj = new object($arr["id"]);
		$rv = array($msgrobj->prop("fromname"));
		$conns = $msgrobj->connections_from(array(
			"type" => RELTYPE_MAIL_IDENTITY,
		));
		foreach($conns as $conn)
		{
			$obj = new object($conn->to());
			$rv[$obj->id()] = $obj->prop("name") . " <" . $obj->prop("email") . ">";
		};
		return $rv;
	}

}
?>
