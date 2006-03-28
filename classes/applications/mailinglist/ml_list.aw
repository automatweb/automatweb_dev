<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/mailinglist/ml_list.aw,v 1.61 2006/03/28 13:37:51 markop Exp $
// ml_list.aw - Mailing list
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_MENU, on_mconnect_to)
@classinfo syslog_type=ST_MAILINGLIST relationmgr=yes no_status=1 r2=yes

@default table=objects
@default field=meta
@default method=serialize

@default group=general

------------------------------------------------------------------------

@property choose_menu type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 multiple=1
@caption vali kaustad millega liituda

@property choose_languages type=select multiple=1 field=meta method=serialize
@caption vali keeled millega võib liituda

@property multiple_folders type=checkbox ch_value=1
@caption Lase liitumisel kausta valida

@property multiple_languages type=checkbox ch_value=1
@caption Lase liitumisel valida keelt

@property msg_folder type=relpicker reltype=RELTYPE_MSG_FOLDER
@caption Kirjade asukoht

@property sub_form_type type=select rel=1
@caption Vormi tüüp

@property redir_obj type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
@caption Dokument millele suunata

@property redir_unsubscribe_obj type=relpicker reltype=RELTYPE_REDIR_OBJECT rel=1
@caption Dokument millele suunata lahkujad

@property member_config type=relpicker reltype=RELTYPE_MEMBER_CONFIG rel=1
@caption Listi liikmete seadetevorm


@groupinfo membership caption=Liikmed 
------------------------------------------------------------------------

@groupinfo subscribing caption=Liitumine parent=membership
@default group=subscribing

@property admin_subscribe_folders type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 multiple=1
@caption Kaustad, kuhu liituda

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

@groupinfo unsubscribing caption=Lahkumine parent=membership
@default group=unsubscribing

@property admin_unsubscribe_folders type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 multiple=1
@caption Kaustad, kust lahkuda

@property confirm_unsubscribe type=checkbox ch_value=1 
@caption Lahkumiseks on vaja kinnitust

@property confirm_unsubscribe_msg type=relpicker reltype=RELTYPE_ADM_MESSAGE 
@caption Lahkumise kinnituseks saadetav kiri

@property delete_textfile type=fileupload store=no
@caption Kustuta tekstifailis olevad aadressid

@property mass_unsubscribe type=textarea rows=25 store=no 
@caption Massiline kustutamine
------------------------------------------------------------------------

@groupinfo member_list caption=Nimekiri submit=no parent=membership
@default group=member_list

@property member_list_tb type=toolbar no_caption=1
@caption Listi staatuse toolbar

@property def_user_folder type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 multiple=1
@caption Listi liikmete allikas

@property member_list type=table store=no no_caption=1
@caption Liikmed

------------------------------------------------------------------------

@groupinfo export_members caption=Eksport parent=membership
@default group=export_members

@property export_type type=chooser orient=vertical store=no
@caption Formaat

@property export_from_date type=date_select store=no default=-1
@caption Alates kuupäevast

@property exp_sbt type=submit
@caption Ekspordi

------------------------------------------------------------------------

@groupinfo export_to_file caption="Eksport faili" parent=membership
@default group=export_to_file

@property expf_path type=textbox 
@caption Kataloog serveris

@property expf_num_per_day type=textbox size=5
@caption Mitu korda p&auml;evas eksport teha

@property expf_next_time type=text store=no
@caption Millal j&auml;rgmine eksport toimub

------------------------------------------------------------------------

@groupinfo raports caption=Kirjad

@groupinfo list_status caption="Saadetud kirjad" parent=raports submit=no

@default group=list_status

@property list_status_tb type=toolbar store=no no_caption=1
@caption Listi staatuse toolbar

@property list_status_table type=table store=no no_caption=1
@caption Listi staatus

------------------------------------------------------------------------

@groupinfo unsent caption="Saatmata kirjad" parent=raports submit=no
@default group=unsent

@property unsent_tb type=toolbar store=no no_caption=1
@caption Listi staatuse toolbar

@property unsent_table type=table store=no no_caption=1
@caption Listi staatus

------------------------------------------------------------------------

@groupinfo write_mail caption="Saada kiri" parent=raports 
@default group=write_mail

@property mail_toolbar type=toolbar no_caption=1
@caption Maili toolbar

@property write_user_folder type=relpicker reltype=RELTYPE_MEMBER_PARENT editonly=1 multiple=1
@caption Grupid kellele kiri saata

@property write_mail type=callback callback=callback_gen_write_mail store=no no_caption=1
@caption Maili kirjutamine

@property aliasmgr type=aliasmgr store=no editonly=1 group=relationmgr trans=1
@caption Aliastehaldur
------------------------------------------------------------------------

@groupinfo mail_report caption="Kirja raport" parent=raports submit=no
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
@caption Saadetud liikmete allikas

@property mail_report table type=table store=no no_caption=1
@caption Meili raport

------------------------------------------------------------------------

@groupinfo show_mail caption="Listi kiri" parent=raports submit=no
@default group=show_mail
@property show_mail_subject type=text store=no
@caption Teema

@property show_mail_from type=text store=no
@caption Kellelt

@property show_mail_message type=text store=no no_caption=1
@caption Sisu

------------------------------------------------------------------------

@reltype MEMBER_PARENT value=1 clid=CL_MENU,CL_GROUP,CL_USER,CL_FILE
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

@reltype LANGUAGE value=7 clid=CL_LANGUAGE
@caption Keeled

@reltype SENDER value=8 clid=CL_ML_MEMBER
@caption Saatja

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
		lc_site_load("ml_list", &$this);
	}

	function callback_pre_edit($arr)
	{
		if($arr["group"] == "write_mail")
		{
			$arr["classinfo"]["allow_rte"] = 2;
		}
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
		if(is_oid($id))
		{
		$msg = obj($id);
		$mfrom = $msg->prop("mfrom");
		}
		$this->vars(array(

			"listrida" => $listrida,
			"reforb" => $this->mk_reforb("submit_post_message", array(
				"id" => $id,
				"list_id" => $listinfo->id(),
				"mfrom" => $mfrom,
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
		unset($aid);//umm?
		$list_id = $args["list_id"];
		$_start_at = date_edit::get_timestamp($start_at);
		$_delay = $delay * 60;
		$_patch_size = $patch_size;
		$count = 0;
		$this->get_members(array("id" => $id));
		$count = $this->member_count;
		
		// mark the queue as "processing" - 5
		$this->db_query("INSERT INTO ml_queue (lid,mid,gid,uid,aid,status,start_at,last_sent,patch_size,delay,position,total)
			VALUES ('$list_id','$id','$gid','".aw_global_get("uid")."','$aid','5','$_start_at','0','$_patch_size','$_delay','0','$count')");
		$qid = $this->db_fetch_field("SELECT max(qid) as qid FROM ml_queue", "qid");
		$mail_obj = obj($id);
		$mail_obj -> set_meta("mail_data" , array(
			"mail_id" => $id,
			"list_id" => $list_id,
			"qid" => $qid,
			"mfrom" => $mfrom,
		));
		$mlq = get_instance("applications/mailinglist/ml_mail_gen");
		$mlq->bg_control(array("id" => $id, "do" => "start",));

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
		$choose_menu = $list_obj->prop("choose_menu");	
		// need to check those folders
		foreach($choose_menu as $menu_id => $menu)
		{
			if(!is_oid($menu) || !$this->can("add" , $menu))
			{
				unset($choose_menu[$menu_id]);	
			}
		}
		
		if ($list_obj->prop("multiple_folders") == 1)
		{
			if (!empty($args["subscr_folder"]))
			{
				// check the list of selected folders against the actual connections to folders
				// and ignore ones that are not connected - e.g. don't take candy from strangers
				foreach($args["subscr_folder"] as $ml_connect=>$ml_id)
				{ 
//					if (in_array($ml_connect , $choose_menu))
//					{
						$use_folders[] = $ml_connect;
//					}
				}
			}
			if (sizeof($use_folders) > 0)
			{
				$allow = true;
			}
		}
		else
		{
			$use_folders = $list_obj->prop("choose_menu");
			$allow = true;
		};
		if(is_array($args["subscr_lang"]))
		{
//			$lang_id = $list_obj->lang_id();
			$lang_id = aw_global_get("lang_id");
			$temp_use_folders = array();
			foreach ($args["subscr_lang"] as $user_lang => $user_lang_id)
			{
				foreach ($use_folders as $folder_id => $val)
				{
					if ($user_lang == $lang_id)
					{
						$temp_use_folders[] = $val;
					}
					else
					{
						$o = obj($val);
						$conns = $o->connections_from(array(
							"type" => "RELTYPE_LANG_REL",
							"to.lang_id" => $user_lang,
						));
						
						if(count($conns)<1)
						{
							$conns_to_orig = $o->connections_to(array(
							"type" => 22,
					//		"from.lang_id" => $user_lang,
							));
						}
						
						foreach($conns_to_orig as $conn)
						{
							if($conn->prop("from.lang_id") == $user_lang)
							{
							 $temp_use_folders[] = $conn->prop("from");
							}
							else {
								$from_obj = obj($conn->prop("from"));
								$conns = $from_obj->connections_from(array(
									"type" => "RELTYPE_LANG_REL",
									"to.lang_id" => $user_lang,
								));
								foreach($conns as $conn)
								{
									$temp_use_folders[] = $conn->prop("to");
								}			
							}
						}						
						foreach($conns as $conn)
						{
							$temp_use_folders[] = $conn->prop("to");
						}			
					}
				}
			}
			$use_folders = $temp_use_folders;
		}
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
		
		foreach($use_folders as $key => $folder)
		{
			$members = $this->get_all_members(array($folder));
			if(in_array($args["mail"], $members) || in_array($args["email"], $members))
			{
				unset($use_folders[$key]);
			}
		}
	
		$erx = array();
		
		if(count($use_folders) < 1)
		{
			$allow = false;
			$erx["XXX"]["msg"] = t("Sellise aadressiga inimene on juba valitud listidega liitunud");
		}
		
		if(empty($args["name"])){
			$args["name"] = $args["firstname"].' '.$args["lastname"];
		}
		
		if(empty($args["name"]) && empty($args["firstname"]) && empty($args["lastname"]))
		{
			$allow = false;
			$erx["XXX"]["msg"] = t("Liitumisel vaja ka nime");
		}
		
		if(empty($args["email"]))
		{
			$allow = false;
			$erx["XXX"]["msg"] = t("Liitumisel vaja täita aadressi väli");
		}
		
		if (sizeof($errors) > 0 || (!$allow && $args["op"] == 1))
		{
			$errors = $errors + $erx;
			$errmsg = "";
			foreach($errors as $errprop)
			{
				$errmsg .= $errprop["msg"] . "<br>";
			}

			aw_session_set("no_cache", 1);
			//arr($errmsg);
			//* fsck me plenty
			$request["mail"] = $_POST["mail"];
			aw_session_set("cb_reqdata", $request);
			aw_session_set("cb_errmsg", $errmsg);
			//die();
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
					"firstname" => $args["firstname"],
					"lastname" => $args["lastname"],
					"name" => $args["name"],
					"email" => $args["mail"],
					"use_folders" => $use_folders,
					"list_id" => $list_obj->id(),
					"confirm_subscribe" => $list_obj->prop("confirm_subscribe"),
					"confirm_message" => $list_obj->prop("confirm_subscribe_msg"),
					"udef_fields" => $udef_fields,
				));
				
			//	$msg_to_admin = $args["name"].' , aadressiga '.$args["email"].' liitus mailinglistiga kaustadesse :<br>';
			//	foreach ($use_folders as $folder_to_send)
			//	{
			//		$folder = obj($folder_to_send);
			//		$msg_to_admin = $msg_to_admin.($folder->name()).'<br>';
			//	}
			
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
			
			$langid = aw_global_get("lang_id");
			$doc_langid = $ro -> lang_id();
			if($doc_langid != $langid)
			{
				$documents = $ro->connections_from(array(
					"type" => "RELTYPE_LANG_REL",
					"to.lang_id" => $langid,
				));
				if(count($documents) > 0)
				{
					foreach ($documents as $doc_conn)
					{
						$new_doc_id = $doc_conn->prop("to");
						$ro = obj($new_doc_id);
					}
				}
			}
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
		extract($arr);
		$msg_obj = new object($arr["msg_id"]);
		$message = $msg_obj->prop("message");
		if(!$msg_obj->prop("html_mail")) $message = nl2br($message);
		$al = get_instance("aliasmgr");
		$al->parse_oo_aliases($msg_obj->id(), &$message);
		
		$c_title = $msg_obj->prop("msg_contener_title");
		$c_content = $msg_obj->prop("msg_contener_content");
		if(!$msg_obj->prop("html_mail")) $c_content = nl2br($c_content);
		
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
			case "emb[mfrom]":

				$objs = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_SENDER",
				));
				$opts = safe_array($prop["options"]);
				foreach($objs as $obj)
				{
					if(in_array(array_keys($opts), $obj->prop("to")))
					{
						continue;
					}
					$opts[$obj->prop("to")] = $obj->prop("to.name");
				}
				$prop["options"] = $opts;
				break;
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
				$tb = &$prop["vcl_inst"];
				$mem = reset(safe_array($arr["obj_inst"]->prop("def_user_folder")));
				$tb->add_button(array(
					"name" => "new",
					"img" => "new.gif",
					"tooltip" => t("Lisa uus"),
					"url" => html::get_new_url(CL_ML_MEMBER, ($this->can("add", $mem) ? $mem : $arr["obj_inst"]->parent()), array("return_url" => get_ru())),
				));
				$tb->add_button(array(
					"name" => "save",
					"img" => "save.gif",
					"tooltip" => t("Salvesta"),
					"action" => "submit",
				));
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


			case "choose_languages":
				$lg = get_instance("languages");
				$langdata = array();
				$prop["options"] = $lg->get_list();
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
		//	case "write_mail":
		//	break;
			
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
	//def_user_folder
		aw_global_set("no_cache_flush", 1);
		$lines = explode("\n", $arr["text"]);
		$list_obj = new object($arr["list_id"]);
		$fld = new aw_array($list_obj->prop("admin_subscribe_folders"));
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

			if(!$is_fold)
			{
				return false;
			}
			$members = $this->get_all_members($fold);
			$name = $fld_obj->name();
			echo "Impordin kasutajaid kataloogi $fold / $name... <br />";
			set_time_limit(0);
			$ml_member = get_instance(CL_ML_MEMBER);
			$cnt = 0;
			if (sizeof($lines) > 0)
			{
				obj_set_opt("no_cache", 1);
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
//						usleep(500000);
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
				$c = get_instance("cache");
				$c->file_clear_pt("menu_area_cache");
				$c->file_clear_pt("storage_search");
				$c->file_clear_pt("storage_object_data");
			}
			print "Importisin $cnt aadressi<br>";
		}
		return true;
	}
	
	////
	// !Returns redir_unsubscribe_obj if defined ... section, if not

	/** unsubscribe
		@attrib name=unsubscribe
		@param usr required type=int
		@param list_source required
		@param list required type=int
	**/
	function unsubscribe($arr)
	{
		$ml_object = obj($arr["list"]);
		$ml_member = get_instance(CL_ML_MEMBER);
		if(is_oid($arr["usr"]) && $this->can("view", $arr["usr"]))
		{
			$member = obj($arr["usr"]);
			$email = $member->prop("mail");
		}		
		$retval = $ml_member->unsubscribe_member_from_list(array(
			"email" => $email,
			"list_id" => $arr["list"],
			"ret_status" => true,
			"use_folders" => $arr["list_source"],
		));
		if(is_oid($ml_object->prop("redir_unsubscribe_obj")) && $this->can("view", $ml_object->prop("redir_unsubscribe_obj")))
		{
			return aw_ini_get("baseurl")."/".$ml_object->prop("redir_unsubscribe_obj");
		}
		else
		{
			return $retval;
		}
	}
	
	////
	// !Mass unsubscribe of addresses
	function mass_unsubscribe($arr)
	{
		$lines = explode("\n", $arr["text"]);
		$list_obj = new object($arr["list_id"]);
		$fold = new aw_array($list_obj->prop("admin_unsubscribe_folders"));
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
			//break;
			
			$name = $fld_obj->name();
			echo "Kustutan kasutajaid kataloogist $fld / $name... <br />";
			set_time_limit(0);
			$ml_member = get_instance(CL_ML_MEMBER);
			$cnt = 0;
			if (sizeof($lines) > 0)
			{
				foreach($lines as $line)
				{
					if (strlen($line) < 5)
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
							"use_folders" => $fld,
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
		}
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
		$members = $this->get_members(array("id" => $arr["id"]));
		
		$ml_member_inst = get_instance(CL_ML_MEMBER);
		$ser = "";
		
		if($arr["obj_inst"]->prop("member_config"))
		{
			$config_obj = &obj($arr["obj_inst"]->prop("member_config"));
			$config_data = array();
			$config_data = $config_obj->meta("cfg_proplist");
			uasort($config_data, array($this,"__sort_props_by_ord"));
		}
		
		$imported = array();
		foreach($members as $key => $val)
		{
			if($val["file_name"]){
				;
				continue;
			}
			list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
				"lid" => $arr["id"],
				"member" => $val["oid"],
			));
			if(!in_array($mailto, $imported))
			{
				$imported[] = $mailto;
	
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
		$ml_list_members = $this->get_members(array(
			"id" 	=> $arr["obj_inst"]->id(),
			"from"	=> $perpage * $ft_page ,
			"to"	=> $perpage * ($ft_page + 1),
			"all"	=> 1,
		));
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
			"name" => "source",
			"caption" => t("Allikas"),
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
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));

		$ml_member_inst = get_instance(CL_ML_MEMBER);
		if (is_array($ml_list_members))
		{
			foreach($ml_list_members as $key => $val)
			{
				$is_oid = 0;
				if(is_oid($val["oid"]))
				{
					$is_oid = 1;
				}
				if($val["file_name"])
				{
					$memberdata["name"] = $val["name"];
					$parent_name = $val["file_name"];
					$tabledata = array(
						"email" => $val["mail"],
						"source" =>  $parent_name,
						"name" =>  $memberdata["name"],
						"joined" => "",
					);
					$t->define_data($tabledata);
					continue;
				}
				if(!(strlen($val["name"]) > 0))
				{
					$val["name"] = "(nimetu)";
				}
				$parent_obj = obj($val["parent"]);
				$parent_name = $parent_obj->name();
				if($is_oid)
				{
					list($mailto,$memberdata) = $ml_member_inst->get_member_information(array(
						"lid" => $arr["obj_inst"]->id(),
						"member" => $val["oid"],
						"from_user" => true,
					));
					$joined = $memberdata["joined"];
					$source = html::href(array(
						"url" => $this->mk_my_orb("right_frame", array("parent" => $val["parent"], "return_url" => get_ru()), "admin_menus"),
						"caption" => $parent_name,
					));
					$others = html::href(array(
						"caption" => t("Vaata"),
						"url" => $this->mk_my_orb("change", array(
							"id" => $val["oid"],
							"group" => "udef_fields",
							"cfgform" => $arr["obj_inst"]->prop("member_config"),
							), CL_ML_MEMBER),
					));
					$name = html::get_change_url($val["oid"], array("return_url" => get_ru()), $val["name"]);
				}
				else
				{
					$source = $parent_name;
					$name = $val["name"];
				}
				$tabledata = array(
					"id" => $val["oid"],
					"email" => $val["mail"],
					"joined" => $memberdata["joined"],
					"source" => $source,
					"others" => $others,
					"name" => $name,
				);
				if(is_oid($is_oid))
				{
					$member_obj = &obj($val["oid"]);
					for($i = 0; $i < 10; $i++)
					{
						$tabledata["udef_txbox$i"] = $member_obj->prop("udef_txbox$i");
						if($member_obj->prop("udef_date$i"))
						{
							$tabledata["udef_date$i"] = get_lc_date($member_obj->prop("udef_date$i"));
						}
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

	function _gen_ls_table(&$t)
	{
		$t->define_field(array(
			"name" => "qid",
			"caption" => t("#"),
			"talign" => "center",
			"sortable" => 1,
			"type" => "int",
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "subject",
			"caption" => t("Kiri"),
			"talign" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"talign" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "start_at",
			"caption" => t("Algus"),
			"talign" => "center",
			"type" => "time",
			"format" => "H:i d-m-Y",
			"sortable" => 1,
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "last_sent",
			"caption" => t("Viimane"),
			"talign" => "center",
			"type" => "time",
			"format" => "H:i:s",
			"sortable" => 1,
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "perf",
			"caption" => t("Perf"),
			"talign" => "center",
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "patch_size",
			"caption" => t("Korraga"),
			"talign" => "center",
			"sortable" => 1,
			"type" => "int",
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "delay",
			"caption" => t("Oota"),
			"talign" => "center",
			"sortable" => 1,
			"type" => "int",
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "protsent",
			"caption" => t("Valmis"),
			"talign" => "center",
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "qid",
		));
		$t->set_default_sortby("last_sent");
		$t->set_default_sorder("desc");
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
		$this->_gen_ls_table($t);
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

	function get_members_from_file($args)
	{
		extract($args);
		$file = get_instance(CL_FILE);
		$file_data = $file->get_file_by_id($id);
		$rows = explode("\n" , $file_data["content"]);
		foreach($rows as $row)
		{
			$column = explode("," , $row);
			if(!(strlen($column[0]) > 0) || !(strlen($column[1]) > 5)) continue;
			$name = trim($column[0]);
			if($comb)
			{
				$name = "".trim($column[0])." &lt;".trim($column[1])."&gt;";
			}
			if(!(in_array(trim($column[1]) , $already_found)))
			{
				$ret[]  = array(
					"parent" => $file_data["id"],
					"name" => $name,
					"mail" => trim($column[1]),
					"parent_name" => $file_data["name"],
				);
				$cnt++;
				if(!$all) $already_found[] = trim($column[1]);
			}			
		}
		$this->already_found = $already_found;
		$this->member_count= $cnt;
		return $ret;
	}

	/**
	@attrib api=1 params=name
	@param id required type=oid
		message oid or mailinglist oid
	@param all optional type=int
		if 1, then return all member dublicates also
	@returns array
	@comment
		if the source is file, then parent_name is set
		else oid is set
	@examples
		$ml_list_inst = get_instance(CL_ML_LIST);
		$data = $ml_list_inst->get_members($list_id);
		//data = Array(
			[0] => Array(
				[parent] => 7375
				[name] => keegi
				[mail] => keegi@normaalne.ee
				[parent_name] => mailinglist.txt
			)
			[1] => Array(
				[oid] => 7500
				[parent] => 580
				[name] => inimene
				[mail] => inimene@mail.ee
		))
	**/
	function get_members($args)
	{	
		//$id, $from = 0, $to = 0
		extract($args);
		$ret = array();
		$cnt = 0;
		$obj = obj($id);
		$already_found = array();
		if($obj->class_id = CL_MAIL_MESSAGE)
		{	
			$src = $obj->meta("list_source");
		}
		if(!(sizeof($src) > 0))
		{
			$src = $obj->prop("def_user_folder");
		}
		$fld = new aw_array($src);
		foreach($fld->get() as $folder_id)
		{
			if(!is_oid($folder_id) || !$this->can("view", $folder_id))
			{
				continue;
			}
			$source_obj = obj($folder_id);
			if($source_obj->class_id() == CL_MENU)
			{
				if ($from > 0 && $to > 0)
				{
					$q = sprintf("SELECT oid,parent FROM objects WHERE parent = %d AND class_id = %d AND status != 0 ORDER BY objects.created DESC LIMIT %d,%d", $folder_id, CL_ML_MEMBER, $from, $to);
				}
				else
				{
					$q = sprintf("SELECT oid,parent,metadata FROM objects WHERE parent = %d AND class_id = %d AND status != 0 ORDER BY objects.created DESC", $folder_id, CL_ML_MEMBER);
				}
				
				$this->db_query($q);
				while($row = $this->db_next())
				{
					$data = aw_unserialize($row["metadata"]);
					if(!(in_array($data["email"] , $already_found)))
					{
						$ret[] = array(
							"oid" 		=> $row["oid"],
							"parent"	=> $row["parent"],
							"name"		=> $data["name"],
							"mail"		=> $data["email"],
						);
						$cnt++;
					if(!$all) $already_found[] = $data["email"];
					}
				}
			}
			elseif ($source_obj->class_id() == CL_GROUP)
			{
				$members = $source_obj->connections_from(array(
					"type" => "RELTYPE_MEMBER",
				));
				foreach ($members as $member)
				{
					$member = $member->to();
					$email = $member->get_first_obj_by_reltype("RELTYPE_EMAIL");
					if(!$email)
					{
						continue;
					}
					if(!(in_array($data["email"] , $already_found)))
					{
						$ret[] = array(
							"oid" 		=> $email->id(),
							"parent"	=> $email->parent(),
						);
						$cnt++;
					if(!$all) $already_found[] = $data["email"];
					}				
				}
			}
			elseif($source_obj->class_id() == CL_USER)
			{
				if($email = $source_obj->get_first_obj_by_reltype("RELTYPE_EMAIL"))
				{
					if(!(in_array($data["email"] , $already_found)))
					{
						$ret[] = array(
							"oid" => $email->id(),
							"parent" => $email->parent(),
						);
						$cnt++;
					if(!$all) $already_found[] = $data["email"];
					}				
				
				}
			}
			elseif($source_obj->class_id() == CL_FILE)
			{
				$ret = $this->get_members_from_file(array("id" => $source_obj->id() , "ret" => $ret , "cnt" => $cnt , "all" => $all , "already_found" => $aready_found));
				$cnt = $this->member_count;
				$already_found = $this->already_found;
			}
		}
		$this->member_count = $cnt;
		return $ret;
	}

	function parse_alias($args = array())
	{
		$targ = obj($args["alias"]["target"]);
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
			$langid = aw_global_get("lang_id");
			$c = "";
			$choose_menu = $targ->prop("choose_menu");	
			foreach($choose_menu as $folder)
			{
				$folder_obj = obj($folder);
				$folders = $folder_obj->connections_from(array(
					"type" => "RELTYPE_LANG_REL",
					"to.lang_id" => $langid,
				));

				if($langid == $folder_obj -> lang_id())
				{
					$this->vars(array(
						"folder_name" => $folder_obj -> name(),
						"folder_id" => $folder_obj -> id(),
					));
					$c .= $this->parse("FOLDER");
				}
				
				else
				{
					if(count($folders)>1)
					{
						foreach($folders as $folder_conn)
						{
							$conn_fold_obj = obj($folder_conn->prop("to"));
							if(($langid == $conn_fold_obj->lang_id()) && ($folder_conn->prop("from") == $folder))
							{
								$this->vars(array(
									"folder_name" => $folder_conn->prop("to.name"),
									"folder_id" => $folder_conn->prop("to"),
								));
								$c .= $this->parse("FOLDER");
							}
						}
					}
					else
					{
						$conns_to_orig = $folder_obj->connections_to(array(
							"type" => 22,
//							"to.lang_id" => $langid,
						));
						foreach($conns_to_orig as $conn)
						{
							if($conn->prop("from.lang_id") == $langid)
							{
								$this->vars(array(
								"folder_name" => $conn->prop("from.name"),
								"folder_id" => $conn->prop("from"),
								));
								$c .= $this->parse("FOLDER");
							}
							else 
							{
								$from_obj = obj($conn->prop("from"));
								$conns = $from_obj->connections_from(array(
									"type" => "RELTYPE_LANG_REL",
									"to.lang_id" => $user_lang,
								));
								foreach($conns as $conn)
								{
									$this->vars(array(
									"folder_name" => $conn->prop("to.name"),
									"folder_id" => $conn->prop("to"),
									));
									$c .= $this->parse("FOLDER");
								}			
							}
						}						
					}
				}
			}
			$this->vars(array(
				"FOLDER" => $c,
			));	
		}
		if ($this->is_template("LANGFOLDER") && $tobj->prop("multiple_languages") == 1)
		{
			$lg = get_instance("languages");
			$langdata = array();
			$langdata = $lg->get_list();
			$c = "";
			$choose_languages = $targ->prop("choose_languages");
			foreach($langdata as $id => $lang)
			{
				if(in_array($id, $choose_languages))
				{	
					$this->vars(array(
						"lang_name" => $lang,
						"lang_id" => $id,
					));
					$c .= $this->parse("LANGFOLDER");
				}	
			}			
			$this->vars(array(
				"LANGFOLDER" => $c,
			));	
		}
		
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
		$t->define_field(array(
			"name" => "member",
			"caption" => t("Kellele"),
		));
		$t->define_field(array(
			"name" => "tm",
			"caption" => t("Millal"),
			"talign" => "center",
			"type" => "time",
			"format" => "H:i d-m-Y",
			"sortable" => 1,
			"numeric" => 1,
		));
		$t->define_field(array(
			"name" => "clicked",
			"caption" => t("Klikitud"),
		));
		$_mid = $arr["request"]["mail_id"];
		$qid = $arr["request"]["qid"];
		$id = $arr["obj_inst"]->id();
		$q1 = "SELECT COUNT(*) as cnt FROM ml_sent_mails WHERE lid = '$id' AND mail='$_mid' AND qid = '$qid' AND mail_sent = 1";
		$cnt = $this->db_fetch_field($q1, "cnt");
		
		$q = "SELECT target, tm, subject, id, vars 
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
			"records_per_page" => $perpage,
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
		$list_id = $arr["obj_inst"]->id();
		$mail_id = $arr["request"]["mail_id"];
		
		// how many members does this list have?
		$row = $this->db_fetch_row("SELECT total,qid FROM ml_queue WHERE lid = '$list_id' AND mid = '$mail_id'");	
		$member_count = $row["total"];
		$qid = $row["qid"];
		
		// how many members have been served?
		$served_count = 0;
//		$served_count = $this->db_fetch_row("SELECT count(*) AS cnt FROM ml_sent_mails WHERE lid = '$list_id' AND mail = '$mail_id' AND qid = '$qid'");
		$row2 = $this->db_fetch_row("SELECT position FROM ml_queue WHERE lid = '$list_id' AND mid = '$mail_id'");
		$served_count = $row2["position"];
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
			$arr["link"] .= "&mail_id=" . $arr["request"]["mail_id"].'&qid='.$arr["request"]["qid"];
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
				$rv = htmlspecialchars($this->msg_view_data["mfrom"]);
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
			$arr["obj_inst"]->set_prop("write_user_folder", $msg_obj->meta("list_source"));
		}
		else
		{
			$arr["obj_inst"]->set_prop("write_user_folder", null);
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
		
		$prps = array("name", "html_mail", "message", "msg_contener_title", "msg_contener_content" , "mfrom",  "mfrom_name"); 
		foreach($all_props as $id => $prop)
		{
			if (in_array($id, $prps))
			{
				if ($id == "mfrom")
				{
					$prop["caption"] = t("Saatja e-maili aadress");
				}
				elseif($id == "mfrom_name")
				{
					$prop["caption"] = t("Saatja nimi");
					$prop["type"] = "textbox";
				}
				elseif($id == "message")
				{
					$prop["richtext"] = 1;
					$filtered_props["legend"] = array(
						"type" => "text",
						"name" => "legend",
						"caption" =>  t("Asenduste legend"),
						"value" => t("Meili sisus on võimalik kasutada järgnevaid asendusi:<br /><br />
							#username# - AutomatWebi kasutajanimi<br />
							#name# - Listi liikme nimi<br />
							#subject# - Kirja teema<br />
							#pea#(pealkiri)#/pea# - (pealkiri) asemele kirjutatud tekst muutub 1. taseme pealkirjaks<br />
							#ala#(pealkiri)#/ala# - (pealkiri) asemele kirjutatud tekst muutub 2. taseme pealkirjaks<br />
							#lahkumine# - link, millel klikkides inimene saab listist lahkuda<br />
							<br />
							Kui soovid kirja pandava lingi puhul teada saada, kas saaja sellele ka klikkis, lisa lingi aadressi lõppu #traceid#
							Näiteks: http://www.struktuur.ee/aw#traceid#
							"),
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
				"classinfo" => array("allow_rte" => 2),
			));
		}
		if(is_oid($xprops["emb_mfrom"]["value"]))
		{
		$email_obj = obj($xprops["emb_mfrom"]["value"]);
		$mailto = $email_obj->prop("mail");
		$xprops["emb_mfrom"]["value"] = $mailto;
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
		$mail_id = $msg_data["id"];

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
			$msg_obj = obj($msg_data["id"]);
		}
		$tpl = $msg_data["template_selector"];
		//if ($msg_data["send_away"] == 1)
		//{
		$msg_obj->set_meta("list_source", $arr["obj_inst"]->prop("write_user_folder"));
		//}
		$msg_data["return"] = "id";
	
		$writer = get_instance(CL_MESSAGE);
		$writer->init_class_base();
		$message_id = $writer->submit($msg_data);
		
		$sender = $msg_obj->prop("mfrom");
		
		// if you send from this address a mail once, you send from it again,
		// without needance to use that relpickah search -- ahz
		if($this->can("view", $sender))
		{
			$arr["obj_inst"]->connect(array(
				"to" => $sender,
				"reltype" => "RELTYPE_SENDER",
			));
		}

		if ($this->can("view", $tpl))
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
				"mfrom"	=> $msg_data["mfrom"],
			));
		}
		else
		{
			$this->edit_msg = $message_id;
		}
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
		if($arr["submit_post_message"] == 1)
		{
			$sched = get_instance("scheduler");
			$sched->add(array(
				"event" => $this->mk_my_orb("process_queue", array(), "ml_queue", false, true),
				"time" => time() + 120,	// every 2 minutes
			));
			$time = time();
			$this->submit_post_message(array(
				"mfrom" => $mfrom,
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
		$this->submit_write_mail(array(
			"request" => array("emb" => $arr),
			"obj_inst" => obj($arr["mto"]),
		));
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
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "member_list"));
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
