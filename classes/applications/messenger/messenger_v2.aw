<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/messenger/messenger_v2.aw,v 1.4 2004/11/19 00:36:16 duke Exp $
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

@property treeview type=text parent=msg_cont group=main_view no_caption=1
@caption Folderid

@property message_list type=table no_caption=1 group=main_view parent=msg_cont no_caption=1
@caption Kirjad

// muu mudru

@property msg_outbox type=relpicker reltype=RELTYPE_FOLDER
@caption Saadetud kirjad

@property msg_drafts type=relpicker reltype=RELTYPE_FOLDER
@caption Mustandite kataloog

@property autofilter_delay type=select
@caption Filtrite käivitamise intervall
@comment Minutites

@property testfilters type=text 
@caption Testi filtreid

@default group=search 

@property s_toolbar type=toolbar no_caption=1
@caption Otsingu toolbar

@property s_from type=textbox store=no
@caption From

@property s_subject type=textbox store=no
@caption Subject

@property s_submit type=submit
@caption Otsi

@property no_reforb type=hidden value=1
@caption lolo

@property s_results type=table no_caption=1
@caption Tulemused

@property rule_editor type=releditor reltype=RELTYPE_RULE mode=manager group=rules table_fields=id,rule_from,rule_subject props=rule_from,rule_subject,target_folder,on_server
@caption Reeglid

@groupinfo main_view caption="Kirjad" submit=no 
@groupinfo search caption=Otsing submit=no submit_action=change submit_method=GET
@groupinfo rules caption=Reeglid submit=no

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

	/**  
		
		@attrib name=my_messages params=name is_public="1" caption="Minu kirjad" 
		
		
		@returns
		
		
		@comment

	**/
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
				$retval = $this->gen_message_list($arr);
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

			case "s_from":
				$data["value"] = $arr["request"]["s_from"];
				break;

			case "s_subject":
				$data["value"] = $arr["request"]["s_subject"];
				break;

			case "s_results":
				$this->do_search(&$arr);
				break;

			case "s_toolbar":
				$t = &$data["toolbar"];
				$t->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
					"confirm" => "Kustutada?",
					"tooltip" => "Kustuta märgitud kirjad",
					"action" => "delete_search_results",
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

			if (!extension_loaded("imap"))
			{
				$this->connect_errors = "IMAP extension not available";
				return false;
			};
			$this->msgobj = new object($arr["msgr_id"]);
			$conns = $this->msgobj->connections_from(array("type" => RELTYPE_MAIL_SOURCE));


			// right now it only deals with a single server.
			list(,$_sdat) = each($conns);
			//$_sdat =$conns[0];
			if (empty($_sdat))
			{
				$this->connect_errors = "IMAP sissepääs on konfigureerimata";
				return false;
			};
			$sdat = new object($_sdat->to());

			$this->_name = $sdat->prop("name");

			$this->drv_inst = get_instance("protocols/mail/imap");
			$this->drv_inst->set_opt("use_mailbox",$this->use_mailbox);
			$this->drv_inst->set_opt("outbox",$this->outbox);
			$awt->start("imap-server-connect");
			$errors = $this->drv_inst->connect_server(array("obj_inst" => $_sdat->to()));
			if ($errors)
			{
				$this->connect_errors = $errors;
				return false;
			}			
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
		return true;
	}

	function gen_message_list(&$arr)
	{
		$this->_connect_server(array(
			"msgr_id" => $arr["obj_inst"]->id(),
			"no_folders" => true,
		));

		if ($this->connect_errors)
		{
			$arr["prop"]["error"] = "Login failed, check whether server name, user and password are correct.<br>" . $this->connect_errors; 
			return PROP_ERROR;
		};

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
			$pageselector = $t->draw_button_pageselector(array(
				"records_per_page" => $perpage
			));
		};

		$fldr = $this->use_mailbox;	

		$t->table_header = $pageselector;

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
				"id" => $key,
				"from" => $this->_format($fromline,$seen),
				"subject" => html::href(array(
					"url" => "javascript:aw_popup_scroll(\"" . $this->mk_my_orb("change",array(
							"msgrid" => $arr["obj_inst"]->id(),
							"msgid" => $key,
							"form" => "showmsg",
							"cb_part" => 1,
							"mailbox" => $this->use_mailbox,
					),"mail_message",false,true) . "\",\"$wname\",800,600)",
					"caption" => $this->_format(parse_obj_name($message["subject"]),$seen),
				)),
				"date" => $message["tstamp"],
				"size" => $this->_format(sprintf("%d",$message["size"]/1024),$seen),
				"answered" => $this->_format($this->_conv_stat($message["answered"]),$seen),
				"attach" => $message["has_attachments"] ? html::img(array("url" => $this->cfg["baseurl"] . "/automatweb/images/attach.gif")) : "",
			));
		};
		$t->define_chooser(array(
			"name" => "mark",
			"field" => "id",
		));
		$t->set_default_sortby("date");
		$t->set_default_sorder("desc");

		return PROP_OK;
	}


	function _format($str,$flag)
	{
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
		$conn = $this->_connect_server(array(
			"msgr_id" => $arr["obj_inst"]->id(),
		));

		if (!$conn)
		{
			return false;
		};

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
		$rv = $this->_connect_server(array(
			"msgr_id" => $arr["obj_inst"]->id(),
		));

		$drafts = $this->msgobj->prop("msg_drafts");
		$toolbar = &$arr["prop"]["vcl_inst"];

		$toolbar->add_button(array(
			"name" => "newmessage",
			"tooltip" => "Uus kiri",
			"url" => "javascript:aw_popup_scroll('" . $this->mk_my_orb("create_draft",array(
				"msgrid" => $this->msgobj->id(),
				"cb_part" => 1,
			),"mail_message",false,true) . "','msgr',800,600)",
			"img" => "new.gif",
		));
		
		$toolbar->add_separator();

		if ($rv == false)
		{
			return false;
		};
	
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
			"action" => "move_messages",
			"img" => "import.gif",
			"side" => "right",
		));

		$toolbar->add_separator(array(
			"side" => "right",
		));
			
		
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta märgitud kirjad",
			"confirm" => "Kustutada märgitud kirjad?",
			"action" => "delete_messages",
			"img" => "delete.gif",
			"side" => "right",
		));
	}
	
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

	/** called from ORB/scheduler, runs all the filter on INBOX 
		
		@attrib name=run_filters params=name nologin="1" 
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=test_filters params=name 
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
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

	////
	// !Creates hash tables from connected filter objects to make any following
	// processing of messages easier.
	function preprocess_filters()
	{
		$conns = $this->msgobj->connections_from(array(
			"type" => RELTYPE_RULE,
		));

		if (sizeof($conns) == 0)
		{
			return "ühtegi ruuli pole seostatud";
		};

		$rv = "";

		$this->subjrules = $this->fromrules = $this->targets = array();

		foreach($conns as $item)
		{
			$filter_obj = new object($item->to());

			if (1 == $filter_obj->prop("on_server"))
			{
				continue;
			};

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

		foreach($this->subjrules as $key => $val)
		{
			$matches = $this->drv_inst->search_folder(sprintf('SUBJECT "%s"',$val));
			$target = $this->targets[$key];
			if (is_array($matches))
			{
				$move_ops[$target] = $matches;
				$this->done = true;
			};
		};

		foreach($this->fromrules as $key => $val)
		{
			$matches = $this->drv_inst->search_folder(sprintf('FROM "%s"',$val));
			$target = $this->targets[$key];
			if (is_array($matches))
			{
				$move_ops[$target] = $matches;
				$this->done = true;
			};
		};

		/*
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
		*/

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

	/** Deletes messages from server

		@attrib name=delete_messages

	**/
	function delete_messages($arr)
	{
		$marked = is_array($arr["mark"]) && sizeof($arr["mark"]) > 0 ? $arr["mark"] : false;

		if (is_array($marked))
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));
			$this->drv_inst->delete_msgs_from_folder(array_keys($marked));
		};

		// those have to return links, and how do I do that?
		return $this->mk_my_orb("change",array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"ft_page" => $arr["ft_page"],
			"mailbox" => $arr["mailbox"],
		));
	}
	
	/** Deletes messages from server

		@attrib name=delete_search_results all_args="1"

	**/
	function delete_search_results($arr)
	{
		$marked = is_array($arr["mark"]) && sizeof($arr["mark"]) > 0 ? $arr["mark"] : false;

		if (is_array($marked))
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));
			$this->drv_inst->delete_msgs_from_folder(array_keys($marked));
		};

		// those have to return links, and how do I do that?
		return $this->mk_my_orb("change",array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"s_subject" => $arr["s_subject"],
			"s_from" => $arr["s_from"],

		));
	}

	/** Moves messages to another server

		@attrib name=move_messages

	**/
	function move_messages($arr)
	{
		$marked = is_array($arr["mark"]) && sizeof($arr["mark"]) > 0 ? $arr["mark"] : false;
		if ($arr["move_to_folder"] !== 0 && is_array($marked))
		{
			$this->_connect_server(array(
				"msgr_id" => $arr["id"],
			));

			$rv = $this->drv_inst->move_messages(array(
				"id" => array_keys($marked),
				"to" =>  $arr["move_to_folder"],
			));
		};
		return $this->mk_my_orb("change",array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"ft_page" => $arr["ft_page"],
			"mailbox" => $arr["mailbox"],
		));
	}

	function callback_mod_retval($arr)
	{
		$args = &$arr["args"];
		if (!empty($arr["request"]["ft_page"]))
		{
			$args["ft_page"] = $arr["request"]["ft_page"];
		};
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

	function _gen_address_list($arr)
	{
		$msgrobj = new object($arr["id"]);
		$rv = array();
		$conns = $msgrobj->connections_from(array(
			"type" => RELTYPE_ADDRESS,
		));
		foreach($conns as $conn)
		{
			$obj = new object($conn->to());
			$rv[$obj->id()] = $obj->prop("name");
		};
		return $rv;
	}

	function do_search($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("messenger/mailbox_view");
		$t->define_chooser(array(
			"field" => "id",
			"name" => "mark",
		));
		$from = $arr["request"]["s_from"];
		$subj = $arr["request"]["s_subject"];
		$this->_connect_server(array(
			"msgr_id" => $arr["obj_inst"]->id(),
			"no_folders" => true,
		));
		if (!empty($subj) || !empty($from))
		{
			$str = array();
			if (!empty($subj))
			{
				$str[] = sprintf('SUBJECT "%s"',$subj);
			};
			if (!empty($from))
			{
				$str[] = sprintf('FROM "%s"',$from);
			};
			$matches = $this->drv_inst->search_folder(join(" ",$str));
			if (is_array($matches))
			{
				foreach($matches as $msg_uid)
				{
					$message = $this->drv_inst->fetch_headers(array(
						"msgid" => $msg_uid,
						"arr" => 1,

					));

					$seen = ($message->Unseen != "U");
					$fromline = "";

					$addrinf = $this->drv_inst->_extract_address($message->fromaddress);
					$fromn = $this->drv_inst->MIME_decode($addrinf["name"]);
					if (!empty($fromn))
					{
						$fromline = html::href(array(
							"url" => "javascript:void();",
							"title" => $message->fromaddress,
							"caption" => substr($fromn,0,1),
						)) . substr($fromn,1);
					}
					else
					{
						$fromline = $message->from;
					};

					// this should be unique enough
					$wname = "msgr" . $key;

					$t->define_data(array(
						"id" => $msg_uid,
						"from" => $this->_format($fromline,$seen),
						"subject" => html::href(array(
							"url" => "javascript:aw_popup_scroll(\"" . $this->mk_my_orb("change",array(
									"msgrid" => $arr["obj_inst"]->id(),
									"msgid" => $msg_uid,
									"form" => "showmsg",
									"cb_part" => 1,
									"mailbox" => $this->use_mailbox,
							),"mail_message",false,true) . "\",\"$wname\",800,600)",
							"caption" => $this->_format(parse_obj_name($this->drv_inst->_parse_subj($message->subject)),$seen),
						)),
						"date" => strtotime($message->date),
						"size" => $this->_format(sprintf("%d",$message->Size/1024),$seen),
						"answered" => $this->_format($this->_conv_stat($message->answered),$seen),
						"attach" => $message["has_attachments"] ? html::img(array("url" => $this->cfg["baseurl"] . "/automatweb/images/attach.gif")) : "",
					));
				};
			};

		};
	}

}
?>
