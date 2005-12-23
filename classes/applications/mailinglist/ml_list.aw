<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/mailinglist/ml_list.aw,v 1.36 2005/12/23 09:41:43 kristo Exp $
// ml_list.aw - Mailing list
/*
@default table=objects
@default field=meta
@default method=serialize

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_MENU, on_mconnect_to)

------------------------------------------------------------------------
@default group=general

@property def_user_folder type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 multiple=1
@caption Listi liikmete allikas

@property multiple_folders type=checkbox ch_value=1
@caption Lase liitumisel kausta valida

@property msg_folder type=relpicker reltype=RELTYPE_MSG_FOLDER
@caption Kirjade asukoht

@property sub_form_type type=select rel=1
@caption Vormi tüüp

@property redir_obj type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
@caption Dokument millele suunata

@property member_config type=relpicker reltype=RELTYPE_MEMBER_CONFIG rel=1
@caption Listi liikmete seadetevorm
------------------------------------------------------------------------
@default group=member_list

@property member_list_tb type=toolbar store=no no_caption=1
@caption Listi staatuse toolbar

@property member_list type=table store=no no_caption=1
@caption Liikmed

------------------------------------------------------------------------
@default group=subscribing

@property confirm_subscribe type=checkbox ch_value=1 
@caption Liitumiseks on vaja kinnitust

@property confirm_subscribe_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE
@caption Liitumise kinnituseks saadetav kiri

@property import_textfile type=fileupload store=no
@caption Impordi liikmed tekstifailist

@property mass_subscribe type=textarea rows=25 store=no
@caption Massiline liitmine
@comment Iga aadress eraldi real, nimi ja aadress komaga eraldatud

------------------------------------------------------------------------
@default group=unsubscribing

@property confirm_unsubscribe type=checkbox ch_value=1 
@caption Lahkumiseks on vaja kinnitust

@property confirm_unsubscribe_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE 
@caption Lahkumise kinnituseks saadetav kiri

@property delete_textfile type=fileupload store=no
@caption Kustuta tekstifailis olevad aadressid

@property mass_unsubscribe type=textarea rows=25 store=no 
@caption Massiline kustutamine

------------------------------------------------------------------------
@default group=export_members
@property export_type type=chooser orient=vertical store=no
@caption Formaat

@property export_from_date type=date_select store=no default=-1
@caption Alates kuupäevast

@property exp_sbt type=submit
@caption Ekspordi

------------------------------------------------------------------------
@default group=list_status

@property list_status_tb type=toolbar store=no no_caption=1
@caption Listi staatuse toolbar

@property list_status_table type=table store=no no_caption=1
@caption Listi staatus

------------------------------------------------------------------------
@default group=unsent

@property unsent_tb type=toolbar store=no no_caption=1
@caption Listi staatuse toolbar

@property unsent_table type=table store=no no_caption=1
@caption Listi staatus

------------------------------------------------------------------------
@default group=write_mail

@property mail_toolbar type=toolbar no_caption=1
@caption Maili toolbar

@property write_mail type=callback callback=callback_gen_write_mail store=no no_caption=1
@caption Maili kirjutamine

------------------------------------------------------------------------
@default group=mail_report

@property mail_subject type=text store=no 
@caption Teema

@property mail_percentage type=text store=no 
@caption Saadetud

@property mail_start_date type=text store=no 
@caption Saatmise algus

@property mail_last_batch type=text store=no 
@caption Viimane kiri saadeti

@property list_source type=text store=no
@caption Listi liikmete allikas

@property mail_report table type=table store=no no_caption=1
@caption Meili raport

------------------------------------------------------------------------
@default group=show_mail
@property show_mail_subject type=text store=no
@caption Teema

@property show_mail_from type=text store=no
@caption Kellelt

@property show_mail_message type=text store=no no_caption=1
@caption Sisu

------------------------------------------------------------------------
@default group=export_to_file

@property expf_path type=textbox 
@caption Kataloog serveris

@property expf_num_per_day type=textbox size=5
@caption Mitu korda p&auml;evas eksport teha

@property expf_next_time type=text store=no
@caption Millal j&auml;rgmine eksport toimub

------------------------------------------------------------------------
@groupinfo membership caption=Liikmed 
@groupinfo member_list caption=Nimekiri submit=no parent=membership
@groupinfo subscribing caption=Liitumine parent=membership
@groupinfo unsubscribing caption=Lahkumine parent=membership
@groupinfo export_members caption=Eksport parent=membership
@groupinfo export_to_file caption="Eksport faili" parent=membership

@groupinfo raports caption=Kirjad
@groupinfo list_status caption="Saadetud kirjad" parent=raports submit=no
@groupinfo unsent caption="Saatmata kirjad" parent=raports submit=no
@groupinfo write_mail caption="Saada kiri" parent=raports 
@groupinfo mail_report caption="Kirja raport" parent=raports submit=no
@groupinfo show_mail caption="Listi kiri" parent=raports submit=no

------------------------------------------------------------------------
@classinfo syslog_type=ST_MAILINGLIST
@classinfo relationmgr=yes
@classinfo no_status=1

@reltype MEMBER_PARENT value=1 clid=CL_MENU,CL_GROUP,CL_USER
@caption Listi liikmete allikas

@reltype REDIR_OBJECT value=2 clid=CL_DOCUMENT
@caption ümbersuunamine

@reltype ADM_MESSAGE value=3 clid=CL_MESSAGE
@caption administratiivne teade 

@reltype TEMPLATE value=4 clid=CL_MESSAGE_TEMPLATE
@caption kirja template

@reltype MEMBER_CONFIG value=5 clid=CL_CFGFORM
@caption Listi liikme seadetevorm

@reltype MSG_FOLDER value=6 clid=CL_MENU
@caption Kirjade kaust

*/

define("ML_EXPORT_CSV",1);
define("ML_EXPORT_NAMEADDR",2);
define("ML_EXPORT_ADDR",3);
define("ML_EXPORT_ALL", 4);

class ml_list extends class_base
{
	function ml_list()
	{
		$this->init(array(
			"tpldir" => "automatweb/mlist",
			"clid" => CL_ML_LIST,
		));
		lc_load("definition");
	}

	function on_mconnect_to($arr)
	{
		$con = &$arr["connection"];
		if($con->prop("from.class_id") == CL_ML_LIST)
		{
			if($con->prop("reltype") == 6)
			{
				$obj = $con->from();
				$fld = $obj->prop("msg_folder");
				if(empty($fld))
				{
					$obj->set_prop("msg_folder", $con->prop("to"));
					$obj->save();
				}
			}
			elseif($con->prop("reltype") == 1)
			{
				$to = $con->to();
				$nlg = $this->get_cval("non_logged_in_users_group");
				$g_oid = users::get_oid_for_gid($nlg);
				$group = obj($g_oid);
				$to->acl_set($group, array("can_view" => 1, "can_add" => 1));
				$to->save();
			}
		}
	}


	/** saadab teate $id listidesse $targets(array stringidest :listinimi:grupinimi)
		
		@attrib name=post_message
		
		@param id required 
		@param targets optional 
		
	**/
	function post_message($args)
	{
		extract($args);
		if($to_post)
		{
			return $this->submit_post_message(array(
				"list_id" => $mto,
				"id" => $targets,
			));
		}
		$this->mk_path(0, "<a href='".aw_global_get("route_back")."'>".t("Tagasi")."</a>&nbsp;/&nbsp;".t("Saada teade"));

		$this->read_template("post_message.tpl");

		load_vcl("date_edit");
		$date_edit = new date_edit(time());
		$date_edit->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => "",
			"classid" => "small_button",
		));

		$id = (int)$id;//teate id
		$listinfo = new object($targets);
		$listrida = "";

		$this->vars(array(
			"title" => $target,
			"date_edit" => $date_edit->gen_edit_form("start_at", time() - 13),
		));
		$listrida .= $this->parse("listrida");
		
		$this->vars(array(
			"listrida" => $listrida,
			"reforb" => $this->mk_reforb("submit_post_message", array(
				"id" => $id,
				"list_id" => $listinfo->id(),
			)),
		));

		return $this->parse();
	}

	/** See händleb juba õiget postitust, siis kui on valitud saatmise ajavahemikud
		
		@attrib name=submit_post_message
		
		
	**/
	function submit_post_message($args)
	{
		extract($args);
		
		
		$id = (int)$id;
		load_vcl('date_edit');
		unset($aid);
		$total = 0;

		$list_id = $args["list_id"];
		$_start_at = date_edit::get_timestamp($start_at);
		$_delay = $delay * 60;
		$_patch_size = $patch_size;

		$count = $this->get_member_count($list_id);
		$total++;

		// mark the queue as "processing" - 5
		$qid = $this->db_fetch_field("SELECT max(qid) as qid FROM ml_queue", "qid")+1;

		$this->db_query("INSERT INTO ml_queue (qid,lid,mid,gid,uid,aid,status,start_at,last_sent,patch_size,delay,position,total)
			VALUES ('$qid','$list_id','$id','$gid','".aw_global_get("uid")."','$aid','5','$_start_at','0','$_patch_size','$_delay','0','$count')");

		$mlq = get_instance("applications/mailinglist/ml_queue");
		$mlq->preprocess_messages(array(
			"mail_id" => $id,
			"list_id" => $list_id,
			"qid" => $qid,
		));

		// now I should mark the queue as "ready to send" or 0
		$q = "UPDATE ml_queue SET status = 0 WHERE qid = '$qid'";
		$this->db_query($q);
		
		$this->_log(ST_MAILINGLIST, SA_SEND, sprintf(t("saatis meili %s listi %s:%s"),$id, $v["name"], $gname) ,$lid);
		return aw_global_get("route_back");
	}

	/** (un)subscribe an address from(to) a list 
		
		@attrib name=subscribe nologin="1" 
		
		@param id required type=int 
		@param rel_id required type=int 
		
	**/
	function subscribe($args = array())
	{
		$list_id = $args["id"];
		$rel_id = $args["rel_id"];

		$list_obj = new object($list_id);
		// I have to check whether subscribing requires confirmation, and if so, send out the confirm message
		// subscribe confirm works like this - we still subscribe the member to the list, but make
		// her status "deactive" and generate her a confirmation code
		// confirm code is added to the metad
		$ml_member = get_instance(CL_ML_MEMBER);

		$allow = false;
		$use_folders = array();

		// need to check those folders
		if ($list_obj->prop("multiple_folders") == 1)
		{
			if (!empty($args["subscr_folder"]))
			{
				// check the list of selected folders against the actual connections to folders
				// and ignore ones that are not connected - e.g. don't take candy from strangers
				$conns = $list_obj->connections_from(array(
					"type" => "RELTYPE_MEMBER_PARENT",
				));
				foreach($conns as $conn)
				{
					if (!empty($args["subscr_folder"][$conn->prop("to")]))
					{
						$use_folders[] = $conn->prop("to");
					}
				}
			}
			if (sizeof($use_folders) > 0)
			{
				$allow = true;
			}
		}
		else
		{
			$allow = true;
		};
		
		if (empty($args["email"]))
		{
			$args["email"] = $args["mail"];
		}
		if (empty($args["mail"]))
		{
			$args["mail"] = $args["email"];
		}
		$request = $args;
		if (is_array($args["udef_txbox"]))
		{
			foreach($args["udef_txbox"] as $key => $val)
			{
				$request["udef_txbox" . $key] = $val;
			}
		}

		$cfgform = $list_obj->prop("member_config");
		$errors = $ml_member->validate_data(array(
			"request" => $request,
			"cfgform_id" => $cfgform,
		));
		$fld = $list_obj->prop("def_user_folder");
		$members = $this->get_all_members($fld);
		$erx = array();
		if(in_array($args["mail"], $members) || in_array($args["email"], $members) || empty($args["mail"]))
		{
			$allow = false;
			$erx["XXX"]["msg"] = t("Sellise aadressiga inimene on juba listiga liitunud");
		}
		if (sizeof($errors) > 0 or (!$allow && $args["op"] == 1))
		{
			$errors = $errors + $erx;
			$errmsg = "";
			foreach($errors as $errprop)
			{
				$errmsg .= $errprop["msg"] . "<br>";
			}

			aw_session_set("no_cache", 1);
		
			// fsck me plenty
			$request["mail"] = $_POST["mail"];
			aw_session_set("cb_reqdata", $request);
			aw_session_set("cb_errmsg", $errmsg);
			return aw_global_get("HTTP_REFERER");
		};
		
		$udef_fields["textboxes"] = $args["udef_txbox"];
		$udef_fields["textareas"] = $args["udef_txtarea"];
		$udef_fields["checkboxes"] = $args["udef_checkbox"];
		$udef_fields["classificators"] = $args["udef_classificator"];
		$udef_fields["date1"] = $args["udef_date1"];
		if ($allow === true)
		{
			if ($args["op"] == 1)
			{
				$retval = $ml_member->subscribe_member_to_list(array(
					"name" => $args["name"],
					"email" => $args["mail"],
					"use_folders" => $use_folders,
					"list_id" => $list_obj->id(),
					"confirm_subscribe" => $list_obj->prop("confirm_subscribe"),
					"confirm_message" => $list_obj->prop("confirm_subscribe_msg"),
					"udef_fields" => $udef_fields,
				));
			}
		}
		if ($args["op"] == 2)
		{
			$retval = $ml_member->unsubscribe_member_from_list(array(
				"email" => $args["email"],
				"list_id" => $list_obj->id(),
			));
		}

		$relobj = new object($rel_id);

		$mx1 = $relobj->meta("values");
		$mx = $mx1["CL_ML_LIST"];

		// XXX: need to give some kind of feedback to the user, if subscribing did not succeed
		if (!empty($mx["redir_obj"]))
		{
			$retval = $this->cfg["baseurl"] . "/" . $mx["redir_obj"];
		}
		else
		if  (is_oid($list_obj->prop("redir_obj")) && $this->can("view", $list_obj->prop("redir_obj")))
		{
			$ro = obj($list_obj->prop("redir_obj"));
			$retval = $this->cfg["baseurl"] . "/" . $ro->id();
		}
		return $retval;
	}
	
	/** previews a mailing list message 
		
		@attrib name=msg_preview  
		
		@param id required type=int 
		@param msg_id required type=int 
		
	**/
	function msg_preview($arr)
	{
		//arr($arr);
		extract($arr);
		$msg_obj = new object($arr["msg_id"]);
		//arr($msg_obj->properties());
		$message = nl2br($msg_obj->prop("message"));
		$al = get_instance("aliasmgr");
		$al->parse_oo_aliases($msg_obj->id(), &$message);
		
		$c_title = $msg_obj->prop("msg_contener_title");
		$c_content = nl2br($msg_obj->prop("msg_contener_content"));
		
		$message = str_replace("#username#", t("Kasutajanimi"), $message);
		$message = str_replace("#name#", t("Nimi Perenimi"), $message);
		
		$message = preg_replace("#\#pea\#(.*?)\#/pea\##si", '<div class="doc-title">\1</div>', $message);
		$message = preg_replace("#\#ala\#(.*?)\#/ala\##si", '<div class="doc-titleSub">\1</div>', $message);
		$message = str_replace("#subject#", $msg_obj->name(), $message);
		$message = str_replace("#traceid#", "?t=".md5(uniqid(rand(), true)), $message);
		$tpl_sel = $msg_obj->meta("template_selector");
		if (is_oid($tpl_sel) && $this->can("view", $tpl_sel))
		{
			$tpl_obj = new object($tpl_sel);
			$tpl_content = $tpl_obj->prop("content");
			$tpl_content = str_replace("#title#", $c_title, $tpl_content);
			$tpl_content = str_replace("#content#", $message, $tpl_content);
			$tpl_content = str_replace("#container#", $c_content, $tpl_content);	
			echo $tpl_content;
		}
		else
		{
			echo $message;
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			/*
			case "msg_folder":
				if(empty($prop["value"]) && !$arr["new"])
				{
					$obj = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MSG_FOLDER");
					if(is_object($obj))
					{
						$arr["obj_inst"]->set_prop("msg_folder", $obj->id());
						$prop["value"] = $obj->id();
					}
				}
				break;
			*/
			case "sub_form_type":
				$prop["options"] = array(
					"0" => t("liitumine"),
					"1" => t("lahkumine"),
				);
				break;

			case "member_list":
				$this->gen_member_list($arr);
				break;
	
			case "list_status_table":
				$this->gen_list_status_table($arr);
				break;
				
			case "unsent_table":
				$this->gen_unsent_table($arr);
				break;
				
			case "unsent_tb":
				$this->gen_unsent_tb($arr);
				break;
				
			case "mail_report":
				$this->gen_mail_report_table($arr);
				break;

			case "mail_percentage":
				$prop["value"] = $this->gen_percentage($arr);
				break;

			case "mail_subject":
				$prop["value"] = $this->gen_mail_subject($arr);
				break;

			case "mail_toolbar":
				$tb = &$prop["vcl_inst"];
				$tb->add_button(array(
					"name" => "save",
					"img" => "save.gif",
					"tooltip" => t("Salvesta"),
					"action" => "submit",
				));
				$msg = $arr["request"]["msg_id"];
				if (is_oid($msg) && $this->can("view", $msg))
				{
					$link = $this->mk_my_orb("msg_preview",array(
						"id" => $arr["obj_inst"]->id(),
						"msg_id" => $msg,
					), $this->clid, false, true);

					$tb->add_button(array(
						"name" => "preview",
						"img" => "preview.gif",
						"tooltip" => t("Eelvaade"),
						"url" => $link,
						"target" => "_blank",
					));
				};
				/*
				$tb->add_separator();
				$tb->add_button(array(
					"name" => "send",
					"img" => "mail_send.gif",
					"tooltip" => t("Saada"),
					"confirm" => t("Saata kiri ära?"),
				));
				*/
				break;
				
			case "list_source":
				$m_id = $arr["request"]["mail_id"];
				if(is_oid($m_id) && $this->can("view", $m_id))
				{
					$msg = obj($m_id);
					$s = new aw_array($msg->meta("list_source"));
					$val = array();
					foreach($s->get() as $s_id)
					{
						if(is_oid($s_id) && $this->can("view", $s_id))
						{
							$source = obj($s_id);
							$val[] = $source->name();
						}
					}
					$prop["value"] = implode(", ", $val);
				}
				break;
				
			case "mail_start_date":
			case "mail_last_batch":
				$list_id = $arr["obj_inst"]->id();
				$mail_id = $arr["request"]["mail_id"];
				$row = $this->db_fetch_row("SELECT * FROM ml_queue WHERE lid = ${list_id} ANd mid = ${mail_id}");
				if ($prop["name"] == "mail_start_date")
				{
					$prop["value"] = $this->time2date($row["start_at"],2);
				}
				else
				{
					if ($row["last_sent"] == 0)
					{
						$prop["value"] = t("Midagi pole veel saadetud");
					}
					else
					{
						$prop["value"] = $this->time2date($row["last_sent"],2);
					}
				}
				break;

			case "member_list_tb":
				$this->gen_member_list_tb($arr);
				break;
			
			case "list_status_tb":
				$this->gen_list_status_tb($arr);
				break;

			case "export_type":
				$prop["options"] = array(
					ML_EXPORT_CSV => t("nimi,aadress"),
					ML_EXPORT_NAMEADDR => t("nimi &lt;aadress&gt;"),
					ML_EXPORT_ADDR => t("aadress"),
					ML_EXPORT_ALL => t("Kõik andmed"),
				);
				$prop["value"] = 1;
				break;

			case "show_mail_subject":
			case "show_mail_from":
			case "show_mail_message":
				$prop["value"] = $this->gen_ml_message_view($arr);
				break;

			case "expf_next_time":
				$url = str_replace("automatweb/", "", $this->mk_my_orb("exp_to_file", array("id" => $arr["obj_inst"]->id())));
				$sc = get_instance("scheduler");
				$exp = safe_array($sc->find(array(
					"event" => $url
				)));
				if (count($exp) == 0)
				{
					return PROP_IGNORE;
				}
				$event = reset($exp);
				$prop["value"] = date("d.m.Y H:i", $event["time"]);
				break;
		}
		return $retval;
	}
	

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			/*
			case "msg_folder":
				if(empty($prop["value"]) && !$arr["new"])
				{
					$obj = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MSG_FOLDER");
					if(is_object($obj))
					{
						$prop["value"] = $obj->id();
					}
				}
				break;
			*/
			case "import_textfile":
				$imp = $_FILES["import_textfile"]["tmp_name"];
				if (!is_uploaded_file($imp))
				{
					return PROP_OK;
				}
				$contents = file_get_contents($imp);
				if(!$this->mass_subscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $contents,
				)))
				{
					$prop["error"] = t("Selle toimingu jaoks peab listiliikmete allikas olema kaust");
					return PROP_FATAL_ERROR;
				}
				break;
	
			case "delete_textfile":
				$imp = $_FILES["delete_textfile"]["tmp_name"];
				if (!is_uploaded_file($imp))
				{
					return PROP_OK;
				}
				$contents = file_get_contents($imp);
				if(!$this->mass_unsubscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $contents,
				)))
				{
					$prop["error"] = t("Selle toimingu jaoks peab listiliikmete allikas olema kaust");
					return PROP_FATAL_ERROR;
				}
				break;
				

			case "mass_subscribe":
				if(!$this->mass_subscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $prop["value"],
				)))
				{
					$prop["error"] = t("Selle toimingu jaoks peab listiliikmete allikas olema kaust");
					return PROP_FATAL_ERROR;
				}
				break;

			case "mass_unsubscribe":
				if(!$this->mass_unsubscribe(array(
					"list_id" => $arr["obj_inst"]->id(),
					"text" => $prop["value"],
				)))
				{
					$prop["error"] = t("Selle toimingu jaoks peab listiliikmete allikas olema kaust");
					return PROP_FATAL_ERROR;
				}
				break;

			case "write_mail":
				$this->submit_write_mail($arr);
				break;

			case "exp_sbt":
				$this->do_export = true;
				$this->export_type = $arr["request"]["export_type"];
				break;
		}
		return $retval;
	}

	function callback_mod_retval($arr)
	{
		if (isset($this->do_export))
		{
			$arr["action"] = "export_members";
			$arr["args"]["filename"] = "members.txt";
			$arr["args"]["export_type"] = $this->export_type;
			$arr["args"]["export_date"] = date_edit::get_timestamp($arr["request"]["export_from_date"]);
		}
		if (isset($this->edit_msg))
		{
			$arr["args"]["msg_id"] = $this->edit_msg;
		}
	}
	
	function get_all_members($id)
	{
		$member_list = array();
		$mem_list = new object_list(array(
			"parent" => $id,
			"class_id" => CL_ML_MEMBER,
		));
		foreach($mem_list->arr() as $mem)
		{
			$member_list[$mem->prop("name")] = $mem->prop("mail");
		}
		return $member_list;
	}
	
	////
	// !Imports members from a text file / or text block
	// text(string) - member list, comma separated
	// list_id(id) - which list?
	function mass_subscribe($arr)
	{
		aw_global_set("no_cache_flush", 1);
		$lines = explode("\n", $arr["text"]);
		$list_obj = new object($arr["list_id"]);
		$fld = new aw_array($list_obj->prop("def_user_folder"));
		foreach($fld->get() as $fold)
		{
			if(!is_oid($fold) || !$this->can("add", $fold))
			{
				continue;
			}
			$fld_obj = new object($fold);
			if($fld_obj->class_id() != CL_MENU)
			{
				continue;
			}
			$is_fold = true;
			break;
		}
		if(!$is_fold)
		{
			return false;
		}
		$members = $this->get_all_members($fold);
		$name = $fld_obj->name();
		echo "Impordin kasutajaid kataloogi $fld / $name... <br />";
		set_time_limit(0);
		$ml_member = get_instance(CL_ML_MEMBER);
		$cnt = 0;
		if (sizeof($lines) > 0)
		{
			foreach($lines as $line)
			{
				$line = trim($line);
				if (strlen($line) == 0)
				{
					continue;
				}
				if (strpos($line,",") !== false)
				{
					list($name,$addr) = explode(",", $line);
				}
				elseif (strpos($line,";") !== false)
				{
					list($name,$addr) = explode(";",$line);
				}
				else
				{
					$name = "";
					$addr = $line;
				}
				$name = trim($name);
				$addr = trim($addr);

				if (is_email($addr) && !in_array($addr, $members))
				{
					print "OK - nimi: $name, aadress: $addr<br />";
					flush();
					$cnt++;
					$retval = $ml_member->subscribe_member_to_list(array(
						"name" => $name,
						"email" => $addr,
						"list_id" => $list_obj->id(),
						"use_folders" => $fold,
					));
					usleep(500000);
					$members[] = $addr;
				}
				elseif(in_array($addr, $members))
				{
					print "Juba olemas listis - nimi: $name, aadress: $addr<br />";
					flush();
				}
				else
				{
					print "Vale aadress - nimi: $name, aadress: $addr<br />";
					flush();
				}
			}
		}
		print "Importisin $cnt aadressi<br>";
		return true;
	}

	////
	// !Mass unsubscribe of addresses
	function mass_unsubscribe($arr)
	{
		$lines = explode("\n", $arr["text"]);
		$list_obj = new object($arr["list_id"]);
		$fold = new aw_array($list_obj->prop("def_user_folder"));
		foreach($fold->get() as $fld)
		{
			if(!is_oid($fld) || !$this->can("add", $fld))
			{
				continue;
			}
			$fld_obj = new object($fld);
			if($fld_obj->class_id() != CL_MENU)
			{
				continue;
			}
			$is_fold = true;
			break;
		}
		if(!$is_fold)
		{
			return false;
		}
		$name = $fld_obj->name();
		echo "Kustutan kasutajaid kataloogist $fld / $name... <br />";
		set_time_limit(0);
		$ml_member = get_instance(CL_ML_MEMBER);
		$cnt = 0;
		if (sizeof($lines) > 0)
		{
			foreach($lines as $line)
			{
				if (strlen($line) == 0)
				{
					continue;
				}
				// no, this is different, no explode. I need to extract an email address from the
				// line
				preg_match("/(\S*@\S*)/",$line,$m);
				$addr = $m[1];
				if (is_email($addr))
				{
					$retval = $ml_member->unsubscribe_member_from_list(array(
						"email" => $addr,
						"list_id" => $list_obj->id(),
						"ret_status" => true,
						"use_folders" => $fold,
					));
					if ($retval)
					{
						$cnt++;
						print "OK a:$addr<br />";
					}
					else
					{
						print "Ei leidnud $addr<br />";
					}
					flush();
					usleep(500000);
				}
				else
				{
					print "IGN - a:$addr<br />";
					flush();
				}
			}
		}
		print "Kustutasin $cnt aadressi<br>";
		return true;
	}

	function gen_unsent_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"type" => "time",
			"format" => "H:i d-M-y",
			"numeric" => 1,
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		$t->set_default_sortby("created");
		$t->set_default_sorder("desc");
		$q = "SELECT DISTINCT m.mail FROM ml_sent_mails m LEFT JOIN objects ON (objects.oid = m.mail) WHERE objects.status != 0";
		$fld = $arr["obj_inst"]->prop("msg_folder");
		//arr($fld);
		$mls = array();
		$this->db_query($q);
		while($w = $this->db_next())
		{
			$mls[] = $w["mail"];
		}
		$mails = new object_list(array(
			"class_id" => CL_MESSAGE,
			"parent" => !empty($fld) ? $fld : $arr["obj_inst"]->parent(),
		));
		foreach($mails->arr() as $mail)
		{
			if(!in_array($mail->id(), $mls))
			{
				$t->define_data(array(
					"id" => $mail->id(),
					"name" => html::get_change_url($arr["obj_inst"]->id(), array(
						"group" => "write_mail",
						"msg_id" => $mail->id(),
					), ($mail->name() ? $mail->name() : t("(pealkiri puudub)"))),
					"created" => $mail->created(),
				));
			}
		}
		$t->sort_by();
	}
	
	function gen_unsent_tb($arr)
	{
		$toolbar = &$arr["prop"]["toolbar"];
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"action" => "delete_mails",
			"confirm" => t("Eemaldan valitud kirjad?"),
			"img" => "delete.gif",
		));
	}
	
	function gen_member_list_tb($arr)
	{
		$toolbar = &$arr["prop"]["toolbar"];
		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"action" => "delete_members",
			"confirm" => t("Kustutada need liikmed?"),
			"img" => "delete.gif",
		));
	}

	/** Exports list members as a plain text file
		@attrib name=export_members
		@param id required type=int 
		@param filename optional
		@param export_type optional type=int
		@param export_date optional type=int


	**/
	function export_members($arr)
	{
		$arr["obj_inst"] = &obj($arr["id"]);
		$members = $this->get_members($arr["id"]);
		$ml_member_inst = get_instance(CL_ML_MEMBER);
		$ser = "";
		
		if($arr["obj_inst"]->prop("member_config"))
		{
			$config_obj = &obj($arr["obj_inst"]->prop("member_config"));
			$config_data = array();
			$config_data = $config_obj->meta("cfg_proplist");
			uasort($config_data, array($this,"__sort_props_by_ord"));
		}
		
		foreach($members as $key => $val)
		{
			list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
				"lid" => $arr["id"],
				"member" => $val["oid"],
			));
			$member = &obj($memberdata["id"]);
			if($member->created() > $arr["export_date"] || ($arr["export_date"] < 100))
			{
				switch($arr["export_type"])
				{
					case ML_EXPORT_ADDR:
						$ser .= $mailto;
						break;
	
					case ML_EXPORT_NAMEADDR:
						$ser .= $memberdata["name"] . " <" . $mailto . ">";
						break;
					
					case ML_EXPORT_ALL:
						$ser .= $memberdata["name"] . ";" . $mailto . ";";
						foreach ($config_data as $key2 => $value)
						{
							if(strpos($key2, "def_"))
							{
								if(strpos($key2, "def_date"))
								{
									$ser .= get_lc_date($member->prop($key2));
								}
								else
								{
									$ser .= $member->prop($key2);
								}
								$ser .= ";";
							}
						}
						break;
					default:
						$ser .= $memberdata["name"] . "," . $mailto;
						break;
				}
				$ser .= "\n";
			}
		}
		if ($arr["ret"] == true)
		{
			return $ser;
		}

		header("Content-Type: text/plain");
		header("Content-length: " . strlen($ser));
		header("Content-Disposition: filename=members.txt");
		print $ser;
		exit;
	}

	function gen_member_list($arr)
	{
		$perpage = 100;
		$ft_page = (int)$GLOBALS["ft_page"];
		$ml_list_members = $this->get_members($arr["obj_inst"]->id(), $perpage * $ft_page , $perpage * ($ft_page + 1));
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"width" => 50,
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "email",
			"caption" => t("Aadress"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "joined",
			"caption" => t("Liitunud"),
			"sortable" => 1,
			"type" => "time",
			"format" => "H:i d-m-Y",
			"smart" => 1,
		));
		$t->set_default_sortby("id");
		$t->set_default_sorder("desc");
		$cfg = $arr["obj_inst"]->prop("member_config");
		if(is_oid($cfg) && $this->can("view", $cfg))
		{
			$config_obj = &obj($cfg);
			
			$config_data = $config_obj->meta("cfg_proplist");
			uasort($config_data, array($this,"__sort_props_by_ord"));
			
			foreach($config_data as $key => $item)
			{
				strpos($key, "def_txbox");
				if(strpos($key, "def_txbox"))
				{
					$t->define_field(array(
						"name" => $item["name"],
						"caption" => $item["caption"],
						"sortable" => 1,
					));
				}
				
				if(strpos($key, "def_date"))
				{
					$t->define_field(array(
						"name" => $item["name"],
						"caption" => $item["caption"],
						"sortable" => 1,
					));	
				}
			}
		}
		$member_config = $arr["obj_inst"]->prop("member_config");
		if(!empty($member_config))
		{
			$t->define_field(array(
				"name" => "others",
				"caption" => t("Liitumisinfo"),
			));
		}
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));

		$ml_member_inst = get_instance(CL_ML_MEMBER);

		if (is_array($ml_list_members))
		{
			foreach($ml_list_members as $key => $val)
			{
				list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
					"lid" => $arr["obj_inst"]->id(),
					"member" => $val["oid"],
					"from_user" => true,
				));
				$tabledata = array(
					"id" => $val["oid"],
					"email" => $mailto,
					"joined" => $memberdata["joined"],
					"others" => html::href(array(
						"caption" => t("Vaata"),
						"url" => $this->mk_my_orb("change", array(
							"id" => $memberdata["id"],
							"group" => "udef_fields",
							"cfgform" => $arr["obj_inst"]->prop("member_config"),
							), CL_ML_MEMBER),
					)), 
					"change" => html::get_change_url($memberdata["id"], array(), "Muuda"),
					"name" => $memberdata["name"],
				);
				$member_obj = &obj($memberdata["id"]);
				for($i = 0; $i < 10; $i++)
				{
					$tabledata["udef_txbox$i"] = $member_obj->prop("udef_txbox$i");
					if($member_obj->prop("udef_date$i"))
					{
						$tabledata["udef_date$i"] = get_lc_date($member_obj->prop("udef_date$i"));
					}
				}
				$t->define_data($tabledata);
		
			}
		}
		$t->d_row_cnt = $this->member_count;
		$pageselector = "";

		if($t->d_row_cnt > $perpage)
		{
			$pageselector = $t->draw_lb_pageselector(array(
				"records_per_page" => $perpage
			));
		}
		$t->table_header = $pageselector;
		$t->sort_by();
	}

	function __sort_props_by_ord($el1,$el2)
	{
		if (empty($el1["ord"]) && empty($el2["ord"]))
		{
			return (int)($el1["tmp_ord"] - $el2["tmp_ord"]);
			//return 0;
		}
		return (int)($el1["ord"] - $el2["ord"]);
	}
	
	function gen_list_status_tb($arr)
	{
		/*
		$sched = get_instance("scheduler");
		$sched->do_events(array(
			"event" => $this->mk_my_orb("process_queue", array(), "", false, true),
			"time" => time()-120,
		));
		*/
		$toolbar = &$arr["prop"]["toolbar"];
		$toolbar->add_button(array(
			"name" => "new",
			"tooltip" => t("Uus kiri"),
			"url" => $this->mk_my_orb("change", array(
					"id" => $arr["obj_inst"]->id(),
					"group" => "write_mail",
				)),
			"img" => "new.gif",
		));

		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"action" => "delete_queue_items",
			"img" => "delete.gif",
			"confirm" => t("Oled kindel, et soovid valitud kirjad kustutada?"),
		));
	}

	function gen_list_status_table($arr)
	{
		/*
		$sched = get_instance("scheduler");
		$sched->add(array(
			"event" => $this->mk_my_orb("process_queue", array(), "", false, true),
			"time" => time()+120, // every 2 minutes
		));
		*/
		$mq = get_instance("applications/mailinglist/ml_queue");
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/queue");
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "qid",
		));
		$t->set_default_sortby("last_sent");
		$t->set_default_sorder("desc");
		$q = "SELECT ml_queue.* FROM ml_queue LEFT JOIN objects ON (ml_queue.mid = objects.oid) WHERE objects.status != 0 AND lid = " . $arr["obj_inst"]->id() . " ORDER BY start_at DESC";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$mail_obj = new object($row["mid"]);
			/*
			if ($row["status"] != 2)
			{
				$stat_str = $mq->a_status[$row["status"]];
				$status_str = "<a href='javascript:remote(0,450,270,\"".$this->mk_my_orb("queue_change", array("id"=>$row["qid"]))."\");'>$stat_str</a>";
			}
			else
			{
			*/
				$status_str = $mq->a_status[$row["status"]];
			//};
			$row["subject"] = html::get_change_url($arr["obj_inst"]->id(), array(
				"group" => "write_mail",
				"msg_id" => $mail_obj->id(),
			), $mail_obj->name());
			//$row["mid"] = $mail_obj->name();
			if (!$row["patch_size"])
			{
				$row["patch_size"] = t("kõik");
			};
			$row["delay"]/=60;
			
			$row["status"] = html::href(array(
				"url" => $this->mk_my_orb("change", array(
					"group" => "mail_report", 
					"id" => $arr["obj_inst"]->id(),
					"mail_id" => $row['mid'],
					"qid" => $row["qid"],
				)),
				"caption" => $status_str,
			));
			$row["protsent"] = $this->queue_ready_indicator($row["position"], $row["total"]);
			$row["perf"] = sprintf("%.2f", $row["total"] / ($row["last_sent"] - $row["start_at"]) * 60);
			$t->define_data($row);
		};
		$t->sort_by();
	}
	
	/** removes queue items 
		
		@attrib name=delete_queue_items 
		
		@param id required type=int 
		
	**/
	function delete_queue_items($arr)
	{
		if (is_array($arr["sel"]))
		{
			$q = sprintf("DELETE FROM ml_queue WHERE qid IN (%s)",join(",",$arr["sel"]));
			$this->db_query($q);
		};
		return $this->mk_my_orb("change",array("id" => $arr["id"],"group" => "raports"));
	}

	// --------------------------------------------------------------------
	// messengerist saatmise osa

	////
	//! Messenger kutsub välja kui on valitud liste targetiteks
	// vajab targets ja id
	function route_post_message($args = array())
	{
		extract($args);
		$url = $this->mk_my_orb("post_message", array("id" => $id, "targets" => $targets), "", 1);
		$sched = get_instance("scheduler");
		$sched->add(array(
			"event" => $this->mk_my_orb("process_queue", array(), "ml_queue", false, true),
			"time" => time() + 120,	// every 2 minutes
		));
		return $url;
	}

	function get_members_ol($id)
	{
		$obj = &obj($id);
		$fld = new aw_array($obj->prop("def_user_folder"));
		$objects = new object_list();
		foreach($fld->get() as $folder_id)
		{
			if(!is_oid($folder_id) || !$this->can("view", $folder_id))
			{
				continue;
			}
			$source_obj = obj($folder_id);
			if($source_obj->class_id() == CL_MENU)
			{
				$member_list = new object_list(array(
					"parent" => $source_obj->id(),
					"class_id" => CL_ML_MEMBER,
					"lang_id" => array(),
					"site_id" => array(),
				));
				foreach($member_list->arr() as $member)
				{
					$objects->add($member);
				}
			}
			elseif ($source_obj->class_id() == CL_GROUP)
			{
				$users_list = new object_list(($source_obj->connections_from(array(
					"type" => "RELTYPE_MEMBER",
				))));
				
				$member_list = new object_list();
				
				foreach ($users_list->arr() as $user)
				{
					if($tmp = $user->get_first_obj_by_reltype("RELTYPE_EMAIL"))
					{
						$objects->add($tmp);
					}
					unset($tmp);
				}
			}
			elseif($source_obj->class_id() == CL_USER)
			{
				$member_list = new object_list();
				if($tmp = $source_obj->get_first_obj_by_reltype("RELTYPE_EMAIL"))
				{
					$objects->add($tmp);
				}
			}
		}
		return $objects;
	}
	
	function get_members($id, $from = 0, $to = 0)
	{
		$obj_inst = obj($id);
		$ret = array();
		$cnt = 0;
		$fld = new aw_array($obj_inst->prop("def_user_folder"));
		foreach($fld->get() as $folder_id)
		{
			if($cnt > ($to - $from))
			{
				break;
			}
			if(!is_oid($folder_id) || !$this->can("view", $folder_id))
			{
				continue;
			}
			$source_obj = obj($folder_id);
			//$this->get_members_ol($source_obj);
			if($source_obj->class_id() == CL_MENU)
			{
				if ($from != 0 && $to != 0)
				{
					$q = sprintf("SELECT oid,parent FROM objects WHERE parent = %d AND class_id = %d AND status != 0 ORDER BY objects.created DESC LIMIT %d,%d", $folder_id, CL_ML_MEMBER, $from, $to);
				}
				else
				{
					$q = sprintf("SELECT oid,parent FROM objects WHERE parent = %d AND class_id = %d AND status != 0 ORDER BY objects.created DESC", $folder_id, CL_ML_MEMBER);
				}
	
				// why oh why is it so bloody slow with 1600 objects :(
				/*
				$member_list = new object_list(array(
					"parent" => $list_obj->prop("def_user_folder"),
					"class_id" => CL_ML_MEMBER,
					"lang_id" => array(),
					"site_id" => array(),
				));
				*/
	
				//$this->member_count = $member_list->count();
				$this->db_query($q);
	
				while($row = $this->db_next())
				//for($o = $member_list->begin(); !$member_list->end(); $o = $member_list->next())
				{
					/*
					$ret[$o->id()] = array(
						"oid" => $o->id(),
						"parent" => $o->parent(),
					);
					*/
					$ret[$row["oid"]] = array(
						"oid" => $row["oid"],
						"parent" => $row["parent"],
					);
					$cnt++;
				}
			}
			elseif ($source_obj->class_id() == CL_GROUP)
			{
				$members = $source_obj->connections_from(array(
					"type" => "RELTYPE_MEMBER",
				));
				foreach ($members as $member)
				{
					if($cnt > ($to - $from))
					{
						break;
					}
					$member = $member->to();
					$email = $member->get_first_obj_by_reltype("RELTYPE_EMAIL");
					if(!$email)
					{
						continue;
					}
					$ret[] = array(
						"oid" => $email->id(),
						"parent" => $email->parent(),
					);
					$cnt++;
				}
			}
			elseif($source_obj->class_id() == CL_USER)
			{
				if($email = $source_obj->get_first_obj_by_reltype("RELTYPE_EMAIL"))
				{
					$ret[] = array(
						"oid" => $email->id(),
						"parent" => $email->parent(),
					);
					$cnt++;
				}
			}
		}
		$this->member_count = $cnt;
		return $ret;
	}	

	function get_member_count($id)
	{
		return count($this->get_members($id));
	}


	function parse_alias($args = array())
	{
		enter_function("ml_list::parse_alias");
		$cb_errmsg = aw_global_get("cb_errmsg");
		$cb_reqdata = aw_global_get("cb_reqdata");
		aw_session_del("cb_errmsg", "");
		aw_session_del("cb_reqdata", "");
		$tobj = new object($args["alias"]["target"]);
		$sub_form_type = $tobj->prop("sub_form_type");
		if (!empty($args["alias"]["relobj_id"]))
		{
			$relobj = new object($args["alias"]["relobj_id"]);
			$meta = $relobj->meta("values");
			if (!empty($meta["CL_ML_LIST"]["sub_form_type"]))
			{
				$sub_form_type = $meta["CL_ML_LIST"]["sub_form_type"];
			}
		}
		$tpl = ($sub_form_type == 0) ? "subscribe.tpl" : "unsubscribe.tpl";
		$this->read_template($tpl);
		lc_site_load("ml_list", &$this);
		if ($this->is_template("FOLDER") && $tobj->prop("multiple_folders") == 1)
		{
			$folders = $tobj->connections_from(array(
				"type" => "RELTYPE_MEMBER_PARENT",
			));
			$c = "";
			foreach($folders as $folder_conn)
			{
				$this->vars(array(
					"folder_name" => $folder_conn->prop("to.name"),
					"folder_id" => $folder_conn->prop("to"),
				));
				$c .= $this->parse("FOLDER");
			};
			$this->vars(array(
				"FOLDER" => $c,
			));	
		};
		
		// this is sl8888w and otto needs to be fffffaaassssttt
		/*
		$classificator_inst = get_instance(CL_CLASSIFICATOR);
		
		for ($i = 1; $i <= 5; $i++)
		{
			$options = $classificator_inst->get_options_for(array(
				"clid" => CL_ML_MEMBER,
				"name" => "udef_classificator$i",
			));
			
			$this->vars(array(
				"classificator$i" => html::select(array(
					"name" => "udef_classificator[$i]",
					"options" => $options,
				)),
			));
		}
		*/

		if (is_array($cb_reqdata))
		{
			$this->vars($cb_reqdata);
		};
		
		$targ = obj($args["alias"]["target"]);
		$this->vars(array(
			"listname" => $tobj->name(),
			"cb_errmsg" => $cb_errmsg,
			"reforb" => $this->mk_reforb("subscribe",array(
				"id" => $targ->id(),
				"rel_id" => $relobj->id(),
				"section" => aw_global_get("section"),
			)),
		));
		exit_function("ml_list::parse_alias");
		return $this->parse();

	}

	       ////
	//! teeb progress bari
	// tegelt saax seda pitidega teha a siis tekib iga progress bari kohta oma query <img src=
	// see olex overkill kui on palju queue itemeid
	function queue_ready_indicator($osa,$kogu)
	{
		if (!$kogu)
		{
			$p = 100;
		}
		else
		{
			$p = (int)((int)$osa * 100 / (int)$kogu);
		}
		$not_p = 100 - $p;
		//echo("qri($osa,$kogu)=$p");//dbg
		// tekst pane sinna, kus on rohkem ruumi.
		if ($p > $not_p)
		{
			$p1t = "<span Style='font-size:10px;font-face:verdana;'><font color='white'>".$p."%</font></span>";
		}
		else
		{
			$p2t = "<span Style='font-size:10px;font-face:verdana;'><font color='black'>".$p."%</font></span>";
		}
		// kommentaar on selleks, et sorteerimine töötaks (hopefully)
		return "<!-- $p --><table bgcolor='#CCCCCC' Style='height:12;width:100%'><tr><td width=\"$p%\" bgcolor=\"blue\">$p1t</td><td width=\"$not_p%\">$p2t</td></tr></table>";
	}
	
	////
	// !This will generate a raport for a single mail sent to a list.
	// Ungh, shouldn't this be a separate class then?
	function gen_mail_report_table($arr)
	{
		$perpage = 100;
		$t = &$arr["prop"]["vcl_inst"];
		$t->parse_xml_def("mlist/report");
		$_mid = $arr["request"]["mail_id"];
		$qid = $arr["request"]["qid"];
		$id = $arr["obj_inst"]->id();
		$q1 = "SELECT COUNT(*) as cnt FROM ml_sent_mails WHERE lid = '$id' AND mail='$_mid' AND qid = '$qid' AND mail_sent = 1";
		$cnt = $this->db_fetch_field($q1, "cnt");
		
		$q = "
			SELECT target, tm, subject, id, vars
			FROM ml_sent_mails
			WHERE lid = '$id' AND mail = '$_mid' AND qid = '$qid' AND mail_sent = 1 ORDER BY tm DESC LIMIT ".(100*$arr["request"]["ft_page"]).", 100";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$tgt = htmlspecialchars($row["target"]);
			$row["member"] = html::href(array(
				"url" => $this->mk_my_orb("change", array(
					"id" => $id, 
					"group" => "show_mail", 
					"mail_id" => $arr["request"]["mail_id"], 
					"s_mail_id" => $row["id"],
				)),
				"caption" => $tgt,
			));
			$row["clicked"] = ($row["vars"] == 1 ? t("jah") : t("ei"));
			$t->define_data($row);
		}
		$t->d_row_cnt = $cnt;
		$t->table_header = $t->draw_text_pageselector(array(
			"records_per_page" => $perpage
		));
		$t->sort_by();
	}

	function gen_mail_subject($arr)
	{
		$mail_id = $arr["request"]["mail_id"];
		$mail_obj = new object($mail_id);
		return $mail_obj->name();
	}

	function gen_percentage($arr)
	{
		// how many members does this list have?
		$list_id = $arr["obj_inst"]->id();
		$_members = $this->get_members($list_id);
		$member_count = sizeof($_members);

		$mail_id = $arr["request"]["mail_id"];

		$mail_obj = new object($mail_id);
		$name = $mail_obj->name();
		// how many members have been served?	
		$row = $this->db_fetch_row("SELECT count(*) AS cnt FROM ml_sent_mails WHERE lid = '$list_id' AND mail = '$mail_id'");
		$served_count = $row["cnt"];

		$q2 = "SELECT total,position FROM ml_queue WHERE lid = '$list_id' AND mid = '$mail_id'";
		$row2 = $this->db_fetch_row("SELECT total,position FROM ml_queue WHERE lid = '$list_id' AND mid = '$mail_id'");

		$served_count = $row2["position"];
		$member_count = $row2["total"];

		$url = $_SERVER["REQUEST_URI"];

		if (!headers_sent() && $served_count < $member_count)
		{
			$refresh_rate = 30;
			header("Refresh: $refresh_rate; url=$url");
			$str = ", värskendan iga ${refresh_rate} sekundi järel";
		}
		return sprintf(t("Liikmeid: %s, saadetud: %s %s"), $member_count, $served_count, $str);
	}

	function callback_mod_tab($arr)
	{
		// hide it, if no mail report is open
		if ($arr["id"] == "mail_report" && empty($arr["request"]["mail_id"]))
		{
			return false;
		}
		if ($arr["id"] == "show_mail" && empty($arr["request"]["s_mail_id"]))
		{
			return false;
		}
		if ($arr["id"] == "mail_report")
		{
			$arr["link"] .= "&mail_id=" . $arr["request"]["mail_id"];
		}
		if ($arr["id"] == "write_mail" && $arr["request"]["group"] != "write_mail")
		{
			return false;
		}
	}

	function gen_ml_message_view($arr)
	{
		$mail_id = $arr["request"]["s_mail_id"];
		if (!is_array($this->msg_view_data))
		{
			$this->msg_view_data = $this->db_fetch_row("SELECT * FROM ml_sent_mails WHERE id = '$mail_id'");
		}

		$rv = "";

		switch($arr["prop"]["name"])
		{
			case "show_mail_from":
				$rv = htmlspecialchars($this->msg_view_data["mailfrom"]);
				break;

			case "show_mail_subject":
				$rv = $this->msg_view_data["subject"];
				break;

			case "show_mail_message":
				$rv = nl2br($this->msg_view_data["message"]);
				break;
		}
		return $rv;
	}

	function callback_gen_write_mail($arr)
	{
		$writer = get_instance(CL_MESSAGE);
		$writer->init_class_base();
		$all_props = $writer->get_property_group(array(
			"group" => "general",
		));

		if (is_oid($arr["request"]["msg_id"]))
		{
			$msg_obj = new object($arr["request"]["msg_id"]);
		}
		
		else
		{
			$msg_obj = new object();
			$msg_obj->set_class_id(CL_MESSAGE);
			$folder = $arr["obj_inst"]->prop("msg_folder");
			$msg_obj->set_parent((!empty($folder) ? $folder : $arr["obj_inst"]->parent()));
			//$msg_obj->save();
		};
		

		$templates = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_TEMPLATE",
		));

		$filtered_props = array();
		// insert a template selector, if there are any templates available
		if (sizeof($templates) > 0)
		{
			$options = array(0 => t(" - vali - "));
			foreach($templates as $template)
			{
				$options[$template->prop("to")] = $template->prop("to.name");
			}
			$filtered_props["template_selector"] = array(
				"type" => "select",
				"name" => "template_selector",
				"options" => $options,
				"caption" => t("Vali template"),
				"value" => $msg_obj->meta("template_selector"),
			);
		}
		
		$filtered_props["send_away"] = array(
			"name" => "send_away",
			"type" => "checkbox",
			"ch_value" => 1,
			"caption" => t("Saada peale salvestamist ära"),
		);

		// narf, can I make this work better perhaps? I really do hate callback ..
		// and I want to embed a new object. And I have to functionality in form
		// of releditor. So why can't I use _that_ to write a new mail. Eh?

		// would be nice to have some other and better method to do this
		
		$prps = array("mfrom", "name", "html_mail", "message", "msg_contener_title", "msg_contener_content", "mfrom_name"); 
		
		foreach($all_props as $id => $prop)
		{
			if (in_array($id, $prps))
			{
				if ($id == "mfrom")
				{
					$prop["caption"] = t("Saatja e-maili aadress");
					$prop["type"] = "textbox";
				}
				elseif($id == "mfrom_name")
				{
					$prop["caption"] = t("Saatja nimi");
					$prop["type"] = "textbox";
				}
				elseif($id == "message")
				{
					$filtered_props["legend"] = array(
						"type" => "text",
						"name" => "legend",
						"caption" =>  t("Asenduste legend"),
						"value" => t("Meili sisus on võimalik kasutada järgnevaid asendusi:<br /><br />
							#username# - AutomatWebi kasutajanimi<br />
							#name# - Listi liikme nimi<br />
							#subject# - Kirja teema<br />
							#pea#(pealkiri)#/pea# - (pealkiri) asemele kirjutatud tekst muutub 1. taseme pealkirjaks<br />
							#ala#(pealkiri)#/ala# - (pealkiri) asemele kirjutatud tekst muutub 2. taseme pealkirjaks<br /><br />
							Kui soovid kirja pandava lingi puhul teada saada, kas saaja sellele ka klikkis, lisa lingi aadressi lõppu #traceid#
							Näiteks: http://www.struktuur.ee/aw#traceid#"),
					);
				}
				$filtered_props[$id] = $prop;
			}
		}

		$filtered_props["id"] = array(
			"name" => "id",
			"type" => "hidden",
			"value" => $msg_obj->id(),
		);

		$filtered_props["aliasmgr"] = array(
			"name" => "aliasmgr",
			"type" => "aliasmgr",
			"editonly" => 1,
			"trans" => 1,
		);
		if($msg_obj)
		{
			$xprops = $writer->parse_properties(array(
				"obj_inst" => $msg_obj,
				"properties" => $filtered_props,
				"name_prefix" => "emb",
			));
		}
		
	
		return $xprops;
	}

	function submit_write_mail($arr)
	{
		$msg_data = $arr["request"]["emb"];
		// 1. create an object. for this I need to know the parent
		// for starters I'll use the one from the list object itself
		#$msg_data["parent"] = $arr["obj_inst"]->parent();
		$msg_data["mto"] = $arr["obj_inst"]->id();
		$folder = $arr["obj_inst"]->prop("msg_folder");
		if(!is_oid($msg_data["id"]) || !$this->can("view", $msg_data["id"]))
		{
			$msg_obj = obj();
			$msg_obj->set_parent((!empty($folder) ? $folder : $arr["obj_inst"]->parent()));
			$msg_obj->set_class_id(CL_MESSAGE);
			$msg_obj->save();
			$msg_data["id"] = $msg_obj->id();
		}
		else
		{
			$msg_obj = &obj($msg_data["id"]);
		}
		$tpl = $msg_data["template_selector"];
		if ($msg_data["send_away"] == 1)
		{
			$msg_obj->set_meta("list_source", $arr["obj_inst"]->prop("def_user_folder"));
		}

		$writer = get_instance(CL_MESSAGE);
		$writer->init_class_base();
		// it does it's own redirecting .. duke

		$msg_data["return"] = "id";
		// no, it fucking does not!
		$message_id = $writer->submit($msg_data);
		if (is_oid($tpl) && $this->can("view", $tpl))
		{
			$msg_obj->set_meta("template_selector", $tpl);

			$tpl_obj = new object($tpl);
			if ($tpl_obj->prop("is_html") == 1)
			{
				$msg_obj->set_prop("html_mail", 1024);
			}	
			$msg_obj->save();
		}

		if ($msg_data["send_away"] == 1)
		{
			// XXX: work out a way to save the message and not send it immediately
			$writer->send_message(array(
				"id" => $message_id,
				"to_post" => $arr["to_post"],
			));
		}
		else
		{
			$this->edit_msg = $message_id;
		}

		// 
	}
	// this one can be used to send a message to mailinglist from code
	// following parameters can be used
        //      Array
        //      (
        //              [send_away] => 1 // sends mesage away (int)
        //              [mfrom_name] => sender name (string)
        //              [mfrom] => name@mail.ee (string)
        //              [name] => topic (string)
        //              [message] => message content (string)
        //              [msg_contener_title] => have no idea what for (string)
        //              [msg_contener_content] => have no idea what for (string)
        //              [id] => I assume that this is message oid (int)
        //              [mto] => mailing list oid (int) (required)
        //              [return] => id  // if this is set, it gives back created object id
        //                              // else it gives back the redirection url
	//		[submit_post_message] => 0|1 (int) // if 0, then the message wouldn't
	//						   // be sent right away
	//						   // submit_post_message method will not be called
        //      )

	function send_message($arr)
	{
		$mailinglist_obj = obj($arr["mto"]);
		// mail messages folder:
		$folder = $mailinglist_obj->prop("msg_folder");

		$msg_obj = obj();
		$msg_obj->set_parent((!empty($folder) ? $folder : $mailinglist_obj->parent()));
		$msg_obj->set_class_id(CL_MESSAGE);
		$msg_obj->save();
		$arr["id"] = $msg_obj->id();
		
		$this->submit_write_mail(array(
			"request" => array(
				"emb" => $arr,
			),
			"obj_inst" => obj($arr["mto"]),
		));
		if($arr["submit_post_message"] == 1)
		{
			$sched = get_instance("scheduler");
			$sched->add(array(
				"event" => $this->mk_my_orb("process_queue", array(), "ml_queue", false, true),
				"time" => time() + 120,	// every 2 minutes
			));
			$time = time();
			$this->submit_post_message(array(
				"list_id" => $arr["mto"],
				"id" => $arr["id"],
				"start_at" => array(
					"day" => date("d", $time),
					"month" => date("m", $time),
					"year" => date("Y", $time),
					"hour" => date("H", $time),
					"minute" => date("i", $time),
				),
			));
		}
	}

	/** delete members from list
		
		@attrib name=delete_mails 
		
		@param id required type=int 
		@param group optional
		
	**/
	function delete_mails($arr)
	{
		foreach(safe_array($arr["sel"]) as $member_id)
		{
			if(is_oid($member_id) && $this->can("delete", $member_id))
			{
				$member_obj = new object($member_id);
				if($member_obj->class_id() == CL_MESSAGE)
				{
					$member_obj->delete();
				}
			}
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]));
	}
		
	/** delete members from list
		
		@attrib name=delete_members 
		
		@param id required type=int 
		
	**/
	function delete_members($arr)
	{
		foreach(safe_array($arr["sel"]) as $member_id)
		{
			if(is_oid($member_id) && $this->can("delete", $member_id))
			{
				$member_obj = new object($member_id);
				$member_obj->delete();
			}
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "membership"));
	}

	function callback_post_save($arr)
	{
		$sc = get_instance("scheduler");
		$url = str_replace("automatweb/", "", $this->mk_my_orb("exp_to_file", array("id" => $arr["obj_inst"]->id())));
		$sc->remove(array(
			"event" => $url
		));

		if ($arr["obj_inst"]->prop("expf_path") != "" && $arr["obj_inst"]->prop("expf_num_per_day") > 0)
		{
			$this->_add_expf_sched($arr["obj_inst"]);
		}
	}

	function _add_expf_sched($o)
	{
		$sc = get_instance("scheduler");
		$url = str_replace("automatweb/", "", $this->mk_my_orb("exp_to_file", array("id" => $o->id())));
		$sc->remove(array(
			"event" => $url
		));

		// get start of day
		$time = time() - (time() % (24*3600));

		// get num of hours between exports
		$numh = 24 / $o->prop("expf_num_per_day");

		// get num secs
		$nums = $numh * 3600;

		// make next time
		while( $time < time())
		{
			$time += $nums;
		}

		$sc->add(array(
			"event" => $url,
			"time" => $time
		));
	}

	/** exports list members to textfile

		@attrib name=exp_to_file

		@param id required type=int acl=view
	**/
	function exp_to_file($arr)
	{
		$last_time = $this->get_cval("ml_list::exp_to_file::".$arr["id"]."::time");

		$ser = $this->export_members(array(
			"ret" => true,
			"id" => $arr["id"],
			"export_type" => ML_EXPORT_ALL,
			"export_date" => $last_time
		));

		// get file name
		$l = obj($arr["id"]);
		$num = 0;
		do {
			$num++;
			$fn = $l->prop("expf_path")."/".date("Y")."-".date("m")."-".date("d")."-".$num.".csv";
		} while(file_exists($fn));

		$this->put_file(array(
			"file" => $fn,
			"content" => $ser
		));

		$this->set_cval("ml_list::exp_to_file::time", $last_time);

		// add to scheduler
		$this->_add_expf_sched($l);
		die(t("all done"));
	}
}
?>
