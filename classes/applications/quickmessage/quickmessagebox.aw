<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/quickmessage/quickmessagebox.aw,v 1.2 2004/08/25 07:13:39 ahti Exp $
// quickmessagebox.aw - Kiirsõnumite haldus 
/*

@classinfo syslog_type=ST_QUICKMESSAGEBOX relationmgr=yes

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

*/

class quickmessagebox extends class_base
{
	function quickmessagebox()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/quickmessage/quickmessagebox",
			"clid" => CL_QUICKMESSAGEBOX
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			/*
			case "tabpanel":
				$tp = &$prop["vcl_inst"];
				$tp->add_tab(array("disabled" => true, "caption" => "Priit on jobu!"));
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
					"tooltip"	=> "Uus kiri",
					"img"		=> "class_20.gif",
					"url"	=> aw_url_change_var(array("group" => "newmessage")),
				));
				$tb->add_separator();
				if($o = $this->is_that_class(array("id" => $arr["request"]["mid"], "class" => CL_QUICKMESSAGE)))
				{
					$users = get_instance("users");
					$tb->add_button(array(
						"name"		=> "answer",
						"tooltip"	=> "Vasta kirjale",
						"img"		=> "edit.gif",
						"url"		=> aw_url_change_var(array(
							"user" => $users->get_uid_for_oid($o->prop("user_from")),
							"group" => "newmessage",
							"subject" => urlencode("Re: ".$o->prop("subject")),
						)),
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "forward",
						"tooltip"	=> "Edasta kiri",
						"img"		=> "mail_send.gif",
						"url"		=> aw_url_change_var(array(
							"group" => "newmessage",
							"forward" => 1,
						)),
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "archive",
						"tooltip"	=> "Arhiveeri valitud kirjad",
						"img"		=> "archive.gif",
						"url"		=> aw_url_change_var(array("action" => "archive_message", "sel[".$arr["request"]["mid"]."]" => $arr["request"]["mid"])),
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "delete",
						"tooltip"	=> "Kustuta valitud kirjad",
						"img"		=> "delete.gif",
						"url"		=> aw_url_change_var(array("action" => "delete_message", "sel[".$arr["request"]["mid"]."]" => $arr["request"]["mid"])),
					));
				}
				else
				{
					$tb->add_button(array(
						"name"		=> "archive",
						"tooltip"	=> "Arhiveeri valitud kirjad",
						"img"		=> "archive.gif",
						"action"	=> "archive_message",
					));
					$tb->add_separator();
					$tb->add_button(array(
						"name"		=> "delete",
						"tooltip"	=> "Kustuta valitud kirjad",
						"img"		=> "delete.gif",
						"action"	=> "delete_message",
						"confirm" => "Oled kindel, et tahad valitud kirjad kustutada?",
					));
				}
			break;
			case "outbox":
			case "archive":
			case "inbox":
				// seems like an unnecessary doubling, but saves a few rows of code -- ahz
				switch($prop["name"])
				{
					case "outbox":
						$boxprop = array("user_from");
						$archive = false;
					break;
					case "inbox":
						$boxprop = array("user_to");
						$archive = false;
					break;
					case "archive":
						$boxprop = array("user_from", "user_to");
						$archive = true;
					break;
				}
				$vars = array(
					"group" => $prop["name"],
					"boxprop" => $boxprop,
					"archive" => $archive,
					"vcl_inst" => &$prop["vcl_inst"],
					"id" => $arr["obj_inst"]->id(),
				);
				if($o = $this->is_that_class(array("id" => $arr["request"]["mid"], "class" => CL_QUICKMESSAGE)))
				{
					$args = array(
						"o" => $o,
						"vcl_inst" => &$prop["vcl_inst"],
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
	
	// check, if this is the object of needed class and returns the object, else returns false -- ahz
	function is_that_class($arr)
	{
		$val = false;
		if(is_oid($arr["id"]))
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
		$user_to = $users->get_uid_for_oid($o->prop("user_to"));
		$t->define_data(array(
			"name" => "Kellelt",
			"content" => html::href(array(
				"caption" => $user_from,
				"url" => aw_url_change_var(array(
					"group" => "newmessage",
					"user" => $user_from,
				)),
			)),
		));
		$t->define_data(array(
			"name" => "Kellele",
			"content" => html::href(array(
				"caption" => $user_to,
				"url" => aw_url_change_var(array(
					"group" => "newmessage",
					"user" => $user_to,
				)),
			)),
		));
		$t->define_data(array(
			"name" => "Saadetud",
			"content" => $this->time2date($o->created(), 2),
		));
		$t->define_data(array(
			"name" => "Pealkiri",
			"content" => $o->prop("subject"),
		));
		$t->define_data(array(
			"name" => "Sisu",
			"content" => nl2br($o->prop("content")),
		));
		//arr($o->properties());
	}
	function get_box_for_user($arr)
	{
		$ol = new object_list(array(
			$arr["boxprop"] => $arr[$arr["boxprop"]],
			"class_id" => CL_QUICKMESSAGE,
			"sort_by" =>  "objects.created DESC",
		));
		$msgs = array();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if($arr["archive"])
			{
				if(in_array($arr[$arr["boxprop"]], $o->meta("archive")))
				{
					$msgs[] = $o->properties();
				}
			}
			else
			{
				if(in_array($arr[$arr["boxprop"]], $o->meta("view")))
				{
					$msgs[] = $o->properties();
				}
			}
		}
		return $msgs;
	}
	function create_box($arr)
	{
		//arr($arr);
		$users = get_instance("users");
		$messages = array();
		foreach($arr["boxprop"] as $boxprop)
		{
			$messages = $messages + $this->get_box_for_user(array(
				"archive" => $arr["archive"],
				"boxprop" => $boxprop,
				$boxprop => $users->get_oid_for_uid(aw_global_get("uid")),
			));
		}
		$t = &$arr["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => "ID",
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => "Aeg",
			"sortby" => 1,
		));
		$t->define_field(array(
			"name" => "user",
			"caption" => "Kasutaja",
		));
		$t->define_field(array(
			"name" => "subject",
			"caption" => "Pealkiri",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		$t->draw_text_pageselector(array(
			"records_per_page" => 25,
		));
		$t->set_default_sortby("time");
		$t->set_default_sorder("desc");
		// "subject" => urlencode("Re: ".$message["subject"]),
		//arr($arr);
		
		// a small unnoticable hack to show thing right in other class also -- ahz
		if(!empty($arr["class"]))
		{
			$class = $arr["class"];
			$id = $arr["class_id"];
		}
		else
		{
			$class = CL_QUICKMESSAGEBOX;
			$id = $arr["id"];
		}
		foreach($messages as $message)
		{
			$user = obj($message["parent"]);
			$t->define_data(array(
				"id" => $message["brother_of"],
				"time" => $this->time2date($message["created"], 2),
				"user" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $id,
						"group" => "newmessage",
						"user" => $user->name(),
					), $class),
					"caption" => $user->name(),
				)),
				"subject" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $id,
						"mid" => $message["brother_of"],
						"group" => $arr["group"],
					), $class),
					"caption" => $message["subject"],
				)),
			));
		}
		$t->sort_by();
		//$t->draw();
	}
	function callback_new_message($arr)
	{
		$arr = $arr["request"];
		// how the hell do i connect this thing to a contact list? -- ahz
		//arr($arr);
		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array("file" => "quickmessage", "clid" => CL_QUICKMESSAGE));
		//arr($props);
		$needed_props = array("user_from", "user_to", "subject", "content");
		$gotit_props = array();
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
			$gotit_props["content"]["value"] = $user_from." kirjutas:\n".$o->prop("content");
		}
		else
		{
			$gotit_props["user_to"]["value"] = $arr["user"];
			$gotit_props["subject"]["value"] = urldecode($arr["subject"]);
		}
		//arr($gotit_props);
		return $gotit_props;
	}
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			/*
			case "user_to":
				$prop["error"] = "sellist kasutajat ei ole olemas!";
				$users = get_instance("users");
				$t_id = $users->get_oid_for_uid($prop["value"]);
				if(!$this->can("view", $t_id) || empty($t_id))
				{
					return PROP_FATAL_ERROR;
				}
				break;
			*/
			case "newmessage":
				$vars = array(
					"obj_inst" => &$arr["obj_inst"],
					"request" => $arr["request"],
				);
				$this->save_new_message($vars);
				break;
		}
		return $retval;
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
	
	// get the messagebox of the user -- ahz
	function get_message_box_for_user($user)
	{
		//arr($user->properties());
		$message_boxes = $user->connections_to(array(
			"type" => 4, // RELTYPE_OWNER
			"from.class_id" => 816,
		));
		foreach($message_boxes as $msgb)
		{
			$message_box = $msgb;
		}
		//reset($message_box);
		
		// if he doesn't have the connection, then he doesn't have the box... simple -- ahz
		if(!is_object($message_box))
		{
			$box = $this->create_message_box_for_user($user);
		}
		else
		{
			$box = $message_box->to();
		}
		return $box;
	}
	
	function save_new_message($arr)
	{
		$vars = $arr["request"];
		// yeah, the magic of messaging:
		// 1. we get the object id's of sender and reciever
		// 2. we check, whether the receiver is a user and he has a messagebox
		// 3. we create the necessary obects
		// 4. we connect the message to sender and reciever
		// 5. done!
		$users = get_instance("users");
		$u_id = $users->get_oid_for_uid(aw_global_get("uid"));
		$t_id = $users->get_oid_for_uid($vars["user_to"]);

		if(!$this->can("view", $t_id) || empty($t_id))
		{
			return PROP_FATAL_ERROR;
		}
		$user_to = obj($t_id);
		// if this person doesn't have a inbox, then we will currently add it to him brute-force -- ahz
		$this->get_message_box_for_user($user_to);
		$user = obj($u_id);
		$asd = $arr["obj_inst"]->prop("maxsize");
		if(strlen($vars["content"]) > $asd && !empty($asd))
		{
			return PROP_ERROR;
		}
		$o = new object();
		$o->set_class_id(CL_QUICKMESSAGE);
		$o->set_parent($u_id);
		$o->set_status(STAT_ACTIVE);
		// need to resolve it!
		$o->set_prop("user_from",$u_id);
		$o->set_prop("user_to",$t_id);
		$o->set_prop("subject",$vars["subject"]);
		$o->set_prop("content",$vars["content"]);
		$o->set_meta("view",array($t_id,$u_id));
		$o->set_meta("archive",array());
		$o->save();
		/*
		$arr["obj_inst"]->connect(array(
			"to" => $o->id(),
			"reltype" => "RELTYPE_OUTBOX_MESSAGE",
		));
		$mbox->connect(array(
			"to" => $o->id(),
			"reltype" => "RELTYPE_INBOX_MESSAGE",
		));
		*/
	}
	
	/**
		@attrib name=delete_message
		@param sel required type=int acl=delete
	**/
	function delete_message($arr)
	{
		$users = get_instance("users");
		$user_id = $users->get_oid_for_uid(aw_global_get("uid"));
		foreach($arr["sel"] as $sel)
		{
			// filter the meta and count the leftover
			$obj = obj($sel);
			$vmetas = array();
			$ovmetas = $obj->meta("view");
			foreach($ovmetas as $meta)
			{
				if($meta != $user_id)
				{
					$vmetas[] = $meta;
				}
			}
			$aometas = $obj->meta("archive");
			$ametas = array();
			foreach($aometas as $meta)
			{
				if($meta != $user_id)
				{
					$ametas[] = $meta;
				}
			}
			
			// if the meta has run out, delete the object
			
			if(count($vmetas) == 0 && count($ametas) == 0)
			{
				$obj->delete();
			}
			else
			{
				$obj->set_meta("view", $vmetas);
				$obj->set_meta("archive", $ametas);
				$obj->save();
			}
		}
		return $this->mk_my_orb("change", array(
			"group" => $arr["group"],
			"id" => $arr["id"],
		), $arr["class"]);
	}
	/**
		@attrib name=archive_message
		@param sel required type=int acl=view
	**/
	function archive_message($arr)
	{
		$users = get_instance("users");
		$user_id = $users->get_oid_for_uid(aw_global_get("uid"));
		foreach($arr["sel"] as $sel)
		{
			$obj = obj($sel);
			$vmetas = array();
			$ovmetas = $obj->meta("view");
			foreach($ovmetas as $meta)
			{
				if($meta != $user_id)
				{
					$vmetas[] = $meta;
				}
			}
			$obj->set_meta("view", $vmetas);
			$ametas = $obj->meta("archive");
			$ametas[] = $user_id;
			$obj->set_meta("archive", $ametas);
			$obj->save();
		}
		return $this->mk_my_orb("change", array(
			"group" => "archive",
			"id" => $arr["id"],
		), $arr["class"]);
	}
	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
