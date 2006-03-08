<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/quickmessage/quickmessagebox.aw,v 1.8 2006/03/08 15:15:03 kristo Exp $
// quickmessagebox.aw - Kiirsõnumite haldus 
/*

@classinfo syslog_type=ST_QUICKMESSAGEBOX relationmgr=yes no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property maxsize type=textbox
@caption Kirja maksimaalne suurus (märkides)

@groupinfo newmessage caption="Uus teade"

//@property newmessage type=form sclass=applications/quickmessage/quickmessage group=newmessage sform=message store=no
//@caption Uue teate kirjutamine

@property newmessage type=callback callback=callback_new_message group=newmessage store=no


@groupinfo inbox caption="Inbox" submit=no

@property inbox_toolbar type=toolbar no_caption=1 store=no group=inbox
@property Inboxi toolbar

@property inbox type=table no_caption=1 store=no group=inbox
@caption Sissetulnud kirjad


@groupinfo outbox caption="Outbox" submit=no

@property outbox_toolbar type=toolbar no_caption=1 store=no group=outbox
@caption Outboxi toolbar

@property outbox type=table no_caption=1 store=no group=outbox
@caption Välja saadetud kirjad


@groupinfo archive caption="Arhiiv" submit=no

@property archive_toolbar type=toolbar no_caption=1 store=no group=archive
@caption Arhiivi toolbar

@property archive type=table no_caption=1 store=no group=archive
@caption Arhiiv

@reltype OWNER value=4 clid=CL_USER
@caption Omanik

@reltype CONTACT_LIST value=5 clid=CL_CONTACT_LIST
@caption Aadressiraamat

*/
define("QUICKMESSAGE_INCOMING", 1);
define("QUICKMESSAGE_OUTGOING", 2);
define("QUICKMESSAGE_ARCHIVED", 3);

class quickmessagebox extends class_base
{
	function quickmessagebox()
	{
		$this->init(array(
			"tpldir" => "applications/quickmessage/quickmessagebox",
			"clid" => CL_QUICKMESSAGEBOX
		));
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			/*
			case "tabpanel":
				$tp = &$prop["vcl_inst"];
				$tp->add_tab(array("disabled" => true, "caption" => t("Priit on jobu!")));
			break;
			*/
			case "newmessage":
				//$prop["user_to"]["value"] = $arr["request"]["user"];
			break;
			case "archive_toolbar":
			case "inbox_toolbar":
			case "outbox_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name"		=> "new",
					"tooltip"	=> t("Uus kiri"),
					"img"		=> "class_20.gif",
					"url"	=> html::get_change_url($arr["request"]["id"], array(
					"group" => "newmessage",
					)),
				));
				$tb->add_separator();
				if($o = $this->is_that_class(array("id" => $arr["request"]["mid"], "class" => CL_QUICKMESSAGE)))
				{
					$users = get_instance("users");
					$tb->add_button(array(
						"name"		=> "answer",
						"tooltip"	=> t("Vasta kirjale"),
						"img"		=> "edit.gif",
						"url"		=> html::get_change_url($arr["request"]["id"], array(
							"cuser" => $users->get_uid_for_oid($o->prop("user_from")),
							"group" => "newmessage",
							"mid" => $arr["request"]["mid"],
							"subject" => "Re: ".$o->prop("subject"),
						)),
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "forward",
						"tooltip"	=> t("Edasta kiri"),
						"img"		=> "mail_send.gif",
						"url"		=> html::get_change_url($arr["request"]["id"], array(
							"mid" => $arr["request"]["mid"],
							"group" => "newmessage",
							"forward" => 1,
						)),
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "archive",
						"tooltip"	=> t("Arhiveeri valitud kirjad"),
						"img"		=> "archive.gif",
						"url"		=> html::get_change_url($arr["request"]["id"], array(
							"group" => $arr["request"]["group"],
							"action" => "archive_message",
							"mid" => $arr["request"]["mid"],
							"sel[".$arr["request"]["mid"]."]" => $arr["request"]["mid"],
						)),
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "delete",
						"tooltip"	=> t("Kustuta valitud kirjad"),
						"img"		=> "delete.gif",
						"url"		=> "javascript:window.location='".html::get_change_url($arr["request"]["id"], array(
							"group" => $arr["request"]["group"],
							"action" => "delete_message", 
							"mid" => $arr["request"]["mid"],
							"sel[".$arr["request"]["mid"]."]" => $arr["request"]["mid"],
						))."'",
						"confirm" => t("oled sa kindel, et tahad valitud kirja kustutada?"),
					));
				}
				else
				{
					$tb->add_button(array(
						"name"		=> "archive",
						"tooltip"	=> t("Arhiveeri valitud kirjad"),
						"img"		=> "archive.gif",
						"action"	=> "archive_message",
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "delete",
						"tooltip"	=> t("Kustuta valitud kirjad"),
						"img"		=> "delete.gif",
						"action"	=> "delete_message",
						"confirm" => t("Oled kindel, et tahad valitud kirjad kustutada?"),
					));
				}
			break;
			case "outbox":
			case "archive":
			case "inbox":
				$vars = array(
					"outbox" => QUICKMESSAGE_OUTGOING,
					"inbox" => QUICKMESSAGE_INCOMING,
					"archive" => QUICKMESSAGE_ARCHIVED,
				);
				// seems like an unnecessary doubling, but saves a few rows of code -- ahz
				$vars = array(
					"mstatus" => $vars[$prop["name"]],
					"group" => $arr["request"]["group"],
					"vcl_inst" => &$prop["vcl_inst"],
					"id" => $arr["obj_inst"]->id(),
					"class_id" => $arr["request"]["id"],
				);
				if($o = $this->is_that_class(array("id" => $arr["request"]["mid"], "class" => CL_QUICKMESSAGE)))
				{
					$args = array(
						"o" => $o,
						"vcl_inst" => &$prop["vcl_inst"],
						"request" => $arr["request"],
					);
					$this->show_message($args);
				}
				else
				{
					$this->create_box($vars);
				}
			break;
		};
		return $retval;
	}
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			/*
			case "user_to":
				$prop["error"] = t("sellist kasutajat ei ole olemas!");
				$users = get_instance("users");
				$t_id = $users->get_oid_for_uid($prop["value"]);
				if(!$this->can("view", $t_id) || empty($t_id))
				{
					return PROP_FATAL_ERROR;
				}
				break;
			*/
			case "newmessage":
				$retval = $this->save_new_message(array(
					"obj_inst" => &$arr["obj_inst"],
					"request" => $arr["request"],
				));
				break;
		}
		return $retval;
	}
	
	// check, if this is the object of needed class and returns the object, else returns false -- ahz
	function is_that_class($arr)
	{
		$val = false;
		if(is_oid($arr["id"]) && $this->can("view", $arr["id"]))
		{
			$o = obj($arr["id"]);
			if($o->class_id() == $arr["class"])
			{
				$val = $o;
			}
		}
		return $val;
	}
	
	function show_message($arr)
	{
		$users = get_instance("users");
		$o = $arr["o"];
		$t = &$arr["vcl_inst"];
		$t->set_sortable(false);
		$t->define_field(array(
			"name" => "name",
		));
		$t->define_field(array(
			"name" => "content",
		));
		$user_from = $users->get_uid_for_oid($o->prop("user_from"));
		$user_to = $o->prop("user_to");
		$_users = array();
		$tmp = explode(",", $user_to);
		foreach($tmp as $value)
		{
			$t_users[] = $users->get_uid_for_oid($value);
		}
		$user_to = array();
		foreach($t_users as $value)
		{
			$user_to[] = html::get_change_url($arr["request"]["id"], array(
				"mid" => $arr["request"]["mid"],
				"group" => "newmessage",
				"cuser" => $value,
			), $value);
		}
		$t->define_data(array(
			"name" => t("Kellelt"),
			"content" => html::get_change_url($arr["request"]["id"], array(
				"mid" => $arr["request"]["mid"],
				"group" => "newmessage",
				"cuser" => $user_from,
			), $user_from ? $user_from : " "),
		));
		$t->define_data(array(
			"name" => t("Kellele"),
			"content" => implode(", ", $user_to),
		));
		$t->define_data(array(
			"name" => t("Saadetud"),
			"content" => get_lc_date($o->created(), 7),
		));
		$t->define_data(array(
			"name" => t("Pealkiri"),
			"content" => $o->prop("subject"),
		));
		$t->define_data(array(
			"name" => t("Sisu"),
			"content" => nl2br($o->prop("content")),
		));
	}
	
	function get_box_for_user($arr)
	{
		$ol = new object_list(array(
			"parent" => $arr["id"],
			"class_id" => CL_QUICKMESSAGE,
			"sort_by" =>  "objects.created DESC",
			"mstatus" => $arr["mstatus"],
		));
		$msgs = array();
		foreach($ol->arr() as $o)
		{
			$msgs[] = $o->properties();
		}
		return $msgs;
	}
	
	function create_box($arr)
	{
		//arr($arr);
		$users = get_instance("users");
		$messages = $this->get_box_for_user(array(
			"id" => $arr["id"],
			"mstatus" => $arr["mstatus"],
		));
		$t = &$arr["vcl_inst"];
		$r_on_page = 30;
		$pageselector = $t->draw_text_pageselector(array(
			"records_per_page" => $r_on_page, // rows per page
			"d_row_cnt" => count($messages), // total rows 
		));
		$t->table_header = $pageselector;
		
		$ft_page = $arr["request"]["ft_page"] ? $arr["request"]["ft_page"] : 0;
		$messages = array_slice($messages, ($ft_page * $r_on_page), $r_on_page);
		$fields = array(
			"id" => t("ID"),
			"time" => t("Aeg"),
			"user" => t("Kasutaja"),
			"subject" => t("Pealkiri"),
		);
		foreach($fields as $key => $value)
		{
			$t->define_field(array(
				"name" => $key,
				"caption" => $value,
			));
		}
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		foreach($messages as $message)
		{
			if($this->can("view", $message["user_from"]) && is_oid($message["user_from"]))
			{
				$user = obj($message["user_from"]);
				$name =  $user->name();
			}
			$t->define_data(array(
				"id" => $message["brother_of"],
				"time" => get_lc_date($message["created"], 7),
				"user" => html::get_change_url($arr["class_id"], array(
					"group" => "newmessage",
					"cuser" => $name,
				), $name ? $name : " "),
				"subject" => html::get_change_url($arr["class_id"], array(
					"mid" => $message["brother_of"],
					"group" => $arr["group"],
				), $message["subject"]),
			));
		}
	}
	
	function callback_new_message($arr)
	{
		$sent = aw_global_get("has_sent_message");
		if(!empty($sent))
		{
			$msg = $sent == 1 ? t("teadet ei õnnestunud saata, proovi uuesti") : t("teade edukalt kohale toimetatud!");
			aw_session_del("has_sent_message");
			$gotit_props["one"] = array(
				"name" => "has_msg",
				"type" => "text",
				"no_caption" => 1,
				"value" => $msg,
			);
		}
		else
		{
			$obj_inst = $arr["obj_inst"];
			$arr = $arr["request"];
			$c_list = $obj_inst->get_first_conn_by_reltype("RELTYPE_CONTACT_LIST");
			// how the hell do i connect this thing to a contact list? -- ahz
			//arr($arr);
			//arr($c_list);
			$cu = get_instance("cfg/cfgutils");
			$props = $cu->load_properties(array(
				"file" => "quickmessage", 
				"clid" => CL_QUICKMESSAGE,
			));
			//arr($props);
			$needed_props = array("user_from", "user_to", "subject", "content");
			/*
			if(!empty($c_list))
			{
				$gotit_props = array(
					"contact_list" => array(
						"name" => "contact_list",
						"type" => "text",
						//"no_caption" => 1,
						"value" => html::href(array(
							"url" => "javascript:void(0)",
							"onClick" => "javascript:window.open(\"".$this->mk_my_orb("show_list", array(
								"id" => $c_list->prop("to"),
							), CL_CONTACT_LIST, false, true)."\",\"\",\" toolbar=no,directories=no,status=no,location=no,resizable=no,scrollbars=yes,menubar=no,height=300,width=500\");return false;",
							"caption" => t("Aadressiraamat"),
						)),
					),
				);
			}
			else
			{
			*/
				$gotit_props = array();
			//}
			foreach($props as $key => $value)
			{
				if(in_array($key, $needed_props))
				{
					$gotit_props[$key] = $value;
				}
			}
				$gotit_props["user_from"]["value"] = aw_global_get("uid");
			if($arr["forward"] == 1 && $o = $this->is_that_class(array("id" => $arr["mid"], "class" => CL_QUICKMESSAGE)))
			{
				$users = get_instance("users");
				$user_from = $users->get_uid_for_oid($o->prop("user_from"));
				$gotit_props["subject"]["value"] = "Fwd: ".$o->prop("subject");
				$gotit_props["content"]["value"] = $user_from." ".t("kirjutas").":\n".$o->prop("content");
			}
			else
			{
				$gotit_props["user_to"]["value"] = $arr["cuser"];
				$gotit_props["subject"]["value"] = urldecode($arr["subject"]);
			}
		}
		//arr($gotit_props);
		return $gotit_props;
		
	}

	// user gets a message box if he doesn't have it already -- ahz
	function create_message_box_for_user($user)
	{
		$o = new object();
		$o->set_class_id(CL_QUICKMESSAGEBOX);
		$o->set_parent($user->id());
		$o->set_status(STAT_ACTIVE);
		$o->save();
		$o->connect(array(
			"to" => $user->id(),
			"reltype" => "RELTYPE_OWNER",
		));
		return $o;
	}
	
	function dispatch_message($arr)
	{
		// commune / prop / to / message / from
		if(!$templates_folder = $arr["commune"]->prop("message_templates_folder"))
		{
			return false;
		}
		$messages = new object_list(array(
			"parent" => $templates_folder,
			"name" => $arr["prop"],
		));
		if(!$message = $messages->begin())
		{
			return false;
		}
		if($arr["msg1"] == true)
		{
			$awm = get_instance("protocols/mail/aw_mail");
			$awm->create_message(array(
				"froma" => !empty($arr["from"]) ? $arr["from"] : $arr["commune"]->prop("sender_mail"),
				"subject" => $message->prop("subject"),
				"to" => $arr["to"]->prop("email"),
				"body" => str_replace("#content#", $arr["message"], $message->prop("content")),
			));
			$awm->gen_mail();
		}
		if($arr["msg2"] == true)
		{
			if($msgbox = $this->get_message_box_for_user($arr["to"]))
			{
				$o = new object();
				$o->set_class_id(CL_QUICKMESSAGE);
				$o->set_parent($msgbox->id());
				$o->set_status(STAT_ACTIVE);
				$o->set_prop("mstatus", 1);
				if(!empty($arr["from"]))
				{
						$o->prop("user_from", $arr["from"]);
				}
				$o->set_prop("subject", $message->prop("subject"));
				$o->set_prop("content", str_replace("#content#", $arr["message"], $message->prop("content")));
				$o->set_prop("user_to", $arr["to"]->id());
				$o->set_name($message->prop("subject"));
				$o->save();
			}
		}
		return true;
	}
	
	// get the messagebox of the user -- ahz
	function get_message_box_for_user($user)
	{
		$box = false;
		$message_box = reset($user->connections_to(array(
			"type" => 4, // RELTYPE_OWNER
			"from.class_id" => CL_QUICKMESSAGEBOX,
		)));
		// if he doesn't have the connection, then he doesn't have the box... simple -- ahz
		if(!is_object($message_box))
		{
			$box = $this->create_message_box_for_user($user);
		}
		else
		{
			$box = $message_box->from();
		}
		return $box;
	}
	
	function save_new_message($arr)
	{
		// yeah, the magic of messaging:
		// 1. we get the object id's of sender and reciever
		// 2. we check, whether the receiver is a user and he has a messagebox
		// 3. we create the necessary objects
		// 4. we connect the message to sender and reciever
		// 5. done!
		$vars = $arr["request"];
		
		if(!$u_id = aw_global_get("uid_oid"))
		{
			return PROP_FATAL_ERROR;
		}
		$user = obj($u_id);
		$person = $user->get_first_obj_by_reltype("RELTYPE_PERSON");
		$users = get_instance("users");
		
		// this be spam
		$user_to = array();
		// if the name contains commas, then there are multiple names
		if(strpos($vars["user_to"], ",") !== false)
		{
			$user_to = explode(",", $vars["user_to"]);
			foreach($user_to as $key => $value)
			{
				$user_to[$key] = trim($value);
			}
		}
		else
		{
			$user_to[] = $vars["user_to"];
		}
		$tmp = array();
		foreach($user_to as $value)
		{
			$tmp[] = $users->get_oid_for_uid($value);
		}
		$user_to = $tmp;
		if(is_array($vars["mgroup"]))
		{
			unset($vars["mgroup"][0]);
			unset($vars["mgroup"][1]);
			if(is_array($vars["mgroup"]))
			{
				$objs = new object_list(array("oid" => array_keys($vars["mgroup"])));
				foreach($objs->arr() as $obj)
				{
					$friends = $obj->meta("friends");
					if(is_array($friends))
					{
						$user_to = $user_to + $friends;
					}
				}
			}
		}
		$q_users = array();
		foreach($user_to as $value)
		{
			if(!$this->can("view", $value) || empty($value))
			{
				continue;
			}
			$r_obj = obj($value);
			// if this person doesn't have a inbox, then we will currently add it to him brute-force -- ahz
			if(!$mbox = $this->get_message_box_for_user($r_obj))
			{
				continue;
			}
			$q_users[$r_obj->id()] = $mbox->id();
		}
		$tmp = array();
		
		foreach($q_users as $m_id => $mbox_id)
		{
			$mflag = true;
			$m_user = obj($m_id);
			$nfm_flag = false;
			$fm_flag = false;
			if($m_person = $m_user->get_first_obj_by_reltype("RELTYPE_PERSON"))
			{
				if($m_message = $m_person->meta("message_conditions"))
				{
					if($m_message["nfm"][1] == 1)
					{
						$nfm_flag = true;
					}
					if($m_message["fm"][1] == 1)
					{
						$fm_flag = true;
					}
				}
			}
			if($fm_flag === true)
			{
				if(!$m_person->is_connected_to(array(
					"to" => $person->id(),
					"type" => "RELTYPE_FRIEND",
				)))
				{
					$nfm_flag = false;
					continue;
				}
			}
			if($nfm_flag === true)
			{
				if($m_person->is_connected_to(array(
					"to" => $person->id(),
					"type" => "RELTYPE_FRIEND",
				)) && $fm_flag === false)
				{
					continue;
				}
			}
			$tmp[$m_id] = $mbox_id;
		}
		$q_users = $tmp;
		/*
		$asd = $arr["obj_inst"]->prop("maxsize");
		if(strlen($vars["content"]) > $asd && !empty($asd))
		{
			$vars["content"] = substr($vars["content
		}
		*/
		if(count($q_users) <= 0)
		{
			aw_session_set("has_sent_message", 1);
			return;
		}
		aw_session_set("has_sent_message", 2);
		$o = new object();
		$o->set_class_id(CL_QUICKMESSAGE);
		$o->set_parent($arr["obj_inst"]->id());
		$o->set_status(STAT_ACTIVE);
		$o->set_prop("user_from", $u_id);
		$o->set_prop("user_to", implode(",", array_keys($q_users)));
		$o->set_prop("subject", $vars["subject"]);
		$o->set_prop("content", $vars["content"]);
		$o->set_prop("mstatus", QUICKMESSAGE_OUTGOING);
		$o->set_name($vars["subject"]);
		$o->save();
		
		foreach($q_users as $key => $value)
		{
			$brother = obj($o->save_new());
			$brother->set_parent($value);
			$brother->set_prop("user_to", $key);
			$brother->set_prop("mstatus", QUICKMESSAGE_INCOMING);
			$brother->save();
		}
	}
	
	/**
		@attrib name=delete_message
		@param sel required 
		
	**/
	function delete_message($arr)
	{
		if(is_array($arr["sel"]))
		{
			foreach($arr["sel"] as $sel)
			{
				$obj = obj($sel);
				if($this->can("delete", $sel) && $obj->class_id() == CL_QUICKMESSAGE)
				{
					$obj->delete();
				}
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
	
	/**
		@attrib name=archive_message
		@param sel required type=int acl=view
	**/
	function archive_message($arr)
	{
		if(is_array($arr["sel"]))
		{
			$user_id = aw_global_get("uid_oid");
			foreach($arr["sel"] as $sel)
			{
				$obj = obj($sel);
				$obj->set_prop("mstatus", QUICKMESSAGE_ARCHIVED);
				$obj->save();
			}
		}
		return html::get_change_url($arr["id"], array("group" => "archive"));
	}
}
?>
