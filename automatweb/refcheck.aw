<?php
if ($uid != "")
	$uid  = "";
session_name("automatweb");
session_start();
include("const.aw");
classload("timer","aw_template","users","acl","images");
$awt = new aw_timer;
$users = new users_user;
$gidlist = $users->get_gids_by_uid($uid);
session_register("error");

// check referer
#if (substr($HTTP_REFERER,0,strlen($baseurl)) != $baseurl)
#{
#	include("sorry.aw");
#	exit;
#}

if (!$lang_id)
{
	$lang_id = 1;
}

switch($action) 
{
	case "addcomment":
		classload("msgboard");
		$t = new msgboard;
		$t->submit_add($HTTP_POST_VARS);
		header("Location: comments.$ext?section=$section&page=$page");
		break;

	case "period":
		classload("periods");
    $periods = new db_periods($oid);
		switch($subaction) 
		{
			case "add":
				if (strlen($description) > 2) 
				{
					$periods->add($archived,$description);
        };
				break;
			case "save":
				if (strlen($description) > 2) 
				{
					$periods->save($HTTP_POST_VARS);
				};
				break;
			case "activate":
				$periods->activate_period($id);
		};	
		header("Location: periods.$ext?oid=$oid");
		exit;

// ----------- formide funktsioonid												
		case "admin_output":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->admin_output($HTTP_POST_VARS);
			header("Location: forms.$ext?type=output_list&id=$id");
			break;

		case "save_output_grid":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->save_output_grid($HTTP_POST_VARS);
			header("Location: forms.$ext?type=output_grid&id=$id&op_id=$op_id");
			break;

		case "save_output_settings":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->save_output_settings($HTTP_POST_VARS);
			header("Location: forms.$ext?type=output_settings&id=$id&op_id=$op_id");
			break;

		case "save_output_metadata":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->save_output_metadata($HTTP_POST_VARS);
			header("Location: forms.$ext?type=output_meta&id=$id&op_id=$op_id");
			break;

		case "save_metadata":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->save_metainfo($HTTP_POST_VARS);
			header("Location: forms.$ext?type=metainfo&id=$id");
			break;
		
		case "export_data":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->export_data($HTTP_POST_VARS);
			die();
			break;

		case "import_data":
			include("$classdir/form.$ext");
			$t = new form($id);
			$fn = $t->import_data();
			header("Location: forms.$ext?type=import_contents&step=2&id=$id&fname=$fn&ftype=$ftype&numrows=$numrows");
			break;

		case "import_data_step2":
			include("$classdir/form.$ext");
			$t = new form($id);
			$fn = $t->import_data();
			die();
			break;

		case "admin_cell":
			include("$classdir/form.$ext");
			$t = new form_cell($id, $row, $col);
			$t->save(&$HTTP_POST_VARS);
			header("Location: forms.$ext?type=change_form_cell&f_id=$id&col=$col&row=$row#el_".$savedfrom);
			break;

		case "save_form_grid":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->save_grid();
			header("Location: forms.$ext?type=grid&id=$id");
			break;

		case "save_form_settings":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->save_settings($HTTP_POST_VARS);
			header("Location: forms.$ext?type=settings&id=$id");
			break;

		case "save_form_actions":
			include("$classdir/form.$ext");
			$t = new form($id);
			$t->save_actions($HTTP_POST_VARS);
			header("Location: forms.$ext?type=actions&id=$id");
			break;

	case "save_entry":
		include("$classdir/form.$ext");
		$f = new form($id);
		$entry_id = $f->process_entry($entry_id, true);
		switch ($f->get_location())
		{
			case "text":
				header("Location: forms.$ext?type=ae_text&id=$id&entry_id=$entry_id");
				break;
			case "redirect":
				header("Location: ".$f->get_ae_location());
				break;
			case "search_results":
				header("Location: forms.$ext?type=show_entry&id=$id&entry_id=$entry_id");
				break;
			default:
				header("Location: forms.$ext?type=change_entry&id=$id&entry_id=$entry_id");
				break;
		}
		break;

	case "add_form":
		include("$classdir/form_categories.$ext");
		$t = new form_categories;
		$t->add_form_submit($HTTP_POST_VARS);
		header("Location: forms.$ext?type=list&parent=$parent");
		break;

	case "submit_form_category":
		include("$classdir/form_categories.$ext");
		$t = new form_categories;
		$parent = $t->submit_category($HTTP_POST_VARS);
		header("Location: forms.$ext?type=list&parent=$parent");
		break;
	
	case "set_category_style":
		include("$classdir/form_categories.$ext");
		$t = new form_categories;
		$t->save_default_style($HTTP_POST_VARS);
		header("Location: forms.$ext?type=list&parent=$parent");
		break;

	case "export_forms":
		classload("form_categories");
		classload("form");
		$t = new form_categories;
		$t->export_forms();
		break;

	case "import_forms":
		classload("form_categories");
		classload("form");
		$t = new form_categories;
		$t->import_forms($level,$parent);
		header("Location: forms.$ext?type=list&parent=$parent");
		break;

	case "save_search_sel":
		classload("form");
		$t = new form($id);
		$t->save_search_sel($HTTP_POST_VARS);
		header("Location: forms.aw?type=sel_search&id=$id");
		break;

	case "submit_menu":
		classload("objects");
		classload("menuedit");
		$m = new menuedit;
		$id = $m->submit_menu($HTTP_POST_VARS);
		header("Location: menuedit.$ext?type=change_menu&id=$id&parent=$parent&menu=menu");
		break;

	case "save_menu_dox":
		classload("objects");
		classload("menuedit");
		$m = new menuedit;
		if ($exp_all)
			$m->open_all(1);
		else
		if ($close_all)
			$m->open_all(0);
		else
			$m->save_menu_dox($HTTP_POST_VARS);
		if ($period) {
			$hsuff = "&period=$period";
		} else {
			$hsuff = "";
		};
		header("Location: menuedit.$ext?parent=$parent&menu=menu$hsuff");
		break;

	case "submit_filled_cat":
		classload("form");
		$t = new form($id);
		$parent = $t->submit_filled_cat($HTTP_POST_VARS);
		header("Location: forms.$ext?type=filled_forms&level=1&parent=$parent&id=$id&op_id=$op_id");
		break;

	case "submit_action":
		classload("form");
		$t = new form($id);
		$aid = $t->submit_action($HTTP_POST_VARS);
		if ($level < 1)
			header("Location: forms.$ext?type=change_action&level=1&action_id=$aid&id=$id");
		else
			header("Location: forms.$ext?type=actions&id=$id");
		break;

	case "submit_list":
		classload("lists");
		$t = new lists;
		$t->add_list_submit($HTTP_POST_VARS);
		header("Location: list.$ext?type=list_lists&parent=$parent");
		break;

	case "admin_mail":
		classload("email");
		$t = new email;
		$id = $t->save_mail($HTTP_POST_VARS);
		if ($send_mail)
			header("Location: list.$ext?type=send_mail&id=$id&parent=$parent");
		else
			header("Location: list.$ext?type=change_mail&id=$id&parent=$parent");
		break;
			
	case "new_user":
		classload("list");
		classload("vars");
		$t = new mlist($id);
		$t->add_user_submit($HTTP_POST_VARS);
		header("Location: list.$ext?type=list_inimesed&id=$id");
		break;

	case "submit_list_cat":
		classload("lists");
		$t = new lists;
		$t->submit_cat($HTTP_POST_VARS);
		header("Location: list.$ext?parent=$parent");
		break;

	case "submit_var_cat":
		classload("vars");
		$t = new variables;
		$t->submit_cat($HTTP_POST_VARS);
		header("Location: list.$ext?type=list_vars&parent=$parent");
		break;

	case "people_list":
		classload("list");
		$t = new mlist($list_id);
		if ($delete == 1)
			$t->delete($HTTP_POST_VARS);
		else
		if ($copy == 1)
			$t->copy($HTTP_POST_VARS);
		else
		if ($cut == 1)
			$t->cut($HTTP_POST_VARS);

		header("Location: list.$ext?type=list_inimesed&id=$list_id");
		break;

	case "import_mails":
		classload("list");
		$t = new mlist($id);
		$t->import_mail_submit($HTTP_POST_VARS);
		die("<a href='list.$ext?list_inimesed&id=$id'>Tagasi</a>");
		break;

	case "admin_var":
		classload("vars");
		$t = new variables;
		$t->add_var_submit($HTTP_POST_VARS);
		header("Location: list.$ext?type=list_vars&parent=$parent");
		break;
			
	case "admin_stamp":
		classload("vars");
		$t = new variables;
		$t->add_stamp_submit($HTTP_POST_VARS);
		header("Location: list.$ext?type=list_stamps");
		break;

	case "cut_forms":
		classload("form_categories");
		classload("form");
		$t = new form_categories;
		$t->cut_forms();
		header("Location: forms.$ext?type=list&parent=$parent");
		break;

	case "delete_forms":
		classload("form_categories");
		classload("form");
		$t = new form_categories;
		$t->delete_forms();
		header("Location: forms.$ext?type=list&parent=$parent");
		break;

	case "alias_type":
		classload("documents");
		$docs = new db_documents;
		$docs->add_alias($docid,$alias,serialize(array("type" => $type, "output" => $output, "form_id" => $form_id)));
		header("Location: documents.$ext?docid=$docid");
		break;

	case "admin_promo":
		classload("menuedit");
		$t = new menuedit;
		$t->submit_promo($HTTP_POST_VARS);
		if ($interface == "new")
		{
			header("Location: ".$t->mk_orb("change", array("parent" => $parent, "id" => $id, "period" => $period ), "menuedit"));
		}
		else
		{
			header("Location: menuedit.$ext?parent=$parent&menu=menu");
		}
		break;

	case "sel_vars":
		classload("list");
		$t = new mlist($list_id);
		$t->submit_change_vars($HTTP_POST_VARS);
		header("Location: list.$ext?parent=$parent");
		break;

	case "expander":
		classload("menuedit");
		$m = new menuedit;
		if ($exp_all)
			$m->open_all(1);
		else
			$m->open_all(0);
		header("Location: menuedit.$ext?parent=$parent");
		break;

	case "submit_mailbox_conf":
		classload("mailbox");
		$t = new mailbox;
		$t->configure_submit($HTTP_POST_VARS);
		header("Location: mail_frameset.html");
		break;

	case "submit_mail_folder":
		classload("mailbox");
		$t = new mailbox;
		$t->submit_folder($HTTP_POST_VARS);
		header("Location: mail.aw?type=folders&parent=$parent");
		break;

	case "submit_mail":
		classload("mailbox");
		$t = new mailbox;
		$t->mail_submit($HTTP_POST_VARS);
		header("Location: mail.aw?type=show_mail");
		break;

	//Graafikute asjad
	case "graph_conf":
		classload("graph");
		$t = new graph;
		$gid=$t->graph_add($HTTP_POST_VARS);
		header("Location: graphs.aw?type=conf&id=$gid");
		break;
	case "graph_meta":
		classload("graph");
		$t = new graph;
		$t->graph_save_meta($HTTP_POST_VARS,$id);	
		header("Location: graphs.aw");
		break;
	case "graph_add":
		classload("graph");
		$t = new graph;
		$parent=$t->graph_new();
		header("Location: graphs.aw?type=add");
		break;
	case "graph_save":
		classload("graph");
		$g = new graph;
		$g->graph_save($HTTP_POST_VARS,$id);
		header("Location: graphs.aw?type=conf&id=$id");
		break;
	case "graph_delete":
		classload("graph");
		$g = new graph;
		echo $g->graph_delete();
		header("Location: graphs.aw");
		break;
	case "graph_enter_data":
		classload("graph");
		$g = new graph;
		echo $g->graph_save_data($HTTP_POST_VARS,$id);
		header("Location: graphs.aw?type=data&id=$id");
		break;
	//Graafikute asjade l6pp

	case "submit_msg_list":
		classload("mailbox");
		$t = new mailbox;
		$t->submit_list($HTTP_POST_VARS);
		header("Location: mail.$ext?parent=$parent");
		break;

	case "submit_change_user":
		$t = new users;
		$t->submit_change($HTTP_POST_VARS);
		header("Location: users.$ext?gid=$gid");
		break;

	case "user_change_pwd":
		$t = new users;
		if (!$t->submit_change_pwd($HTTP_POST_VARS))
		{
			header("Location: users.$ext?type=change_pwd&uid=$uid&gid=$gid");
			$error = "Passwordid polnud samad!";
		}
		else
			header("Location: users.$ext?gid=$gid");
		break;

	case "adduser":
		$u = new users;
		if ($u->submit_add($HTTP_POST_VARS))
			header("Location: users.$ext");
		else
			header("Location: users.$ext?type=add_user");
		break;

	case "submit_group":
		classload("groups");
		$t = new groups;
		if ($t->submit_group($HTTP_POST_VARS))
			header("Location: groups.$ext?parent=$parent");
		else
			header("Location: groups.$ext?type=add&level=1&parent=$parent&name=$name&grp_level=$grp_level");
		break;

	case "submit_group_change":
		classload("groups");
		$t = new groups;
		if ($t->submit_group($HTTP_POST_VARS))
			header("Location: groups.$ext?parent=$parent");
		else
			header("Location: groups.$ext?type=change&level=1&gid=$gid&name=$name");
		break;

	case "update_grp_members":
		classload("groups");
		$g = new groups;
		$g->update_grp_members($HTTP_POST_VARS);
		header("Location: $from");
		break;

	case "menuedit_prygikoll":
		classload("menuedit");
		$t = new menuedit;
		$t->do_prygikoll($HTTP_POST_VARS);
		header("Location: menuedit.$ext?menu=menu&parent=$destination");
		break;
	case "savedocuments":
		classload("menuedit");
		$t = new menuedit;
		$t->submit_save_documents($HTTP_POST_VARS);
		$sufix = "";
		if ($period) {
			$sufix .= "&period=$period";
		};
		if ($periodic) {
			$sufix .= "&periodic=$periodic";
		};
		header("Location: menuedit.$ext?parent=$parent" . $sufix);
		print " ";
		exit;

	case "savedocuments2":
		classload("menuedit");
		$t = new menuedit;
		$t->submit_save_documents($HTTP_POST_VARS);
		$sufix = "";
		if ($period) {
			$sufix .= "period=$period";
		};
		if ($periodic) {
			$sufix .= "periodic=$periodic";
		};
		header("Location: list_docs.$ext?" . $sufix);
		print " ";
		exit;

	case "move_documents":
		classload("menuedit");
		$t = new menuedit;
		$t->submit_move_docs($HTTP_POST_VARS);
		header("Location: menuedit.$ext?parent=$dest");
		break;

	case "update_grp_priorities":
		classload("groups");
		$t = new groups;
		$t->update_priorities($HTTP_POST_VARS);
		header("Location: $from");
		break;

	case "submit_acl_groups":
		classload("acl");
		$t = new acl;
		$t->submit_acl_groups($HTTP_POST_VARS);
		header("Location: $from");
		break;

	case "submit_grp_groups":
		classload("groups");
		$t = new groups;
		$t->submit_grp_groups($HTTP_POST_VARS);
		header("Location: $from");
		break;

	case "save_acl":
		classload("acl");
		$t = new acl;
		$t->ui_save_acl($HTTP_POST_VARS);
		header("Location: editacl.$ext?oid=$oid&file=$file");
		break;

	case "sel_kroonika_pilt":
		classload("kroonika_top");
		$t = new kroonika_top;
		$t->save($HTTP_POST_VARS);
		header("Location: kroonika_top.aw");
		break;

	case "admin_search_conf":
		classload("search_conf");
		$t = new search_conf;
		$level = $t->submit($HTTP_POST_VARS);
		header("Location: search_conf.$ext?level=$level");
		break;

	case "submit_poll":
		classload("poll");
		$t = new poll;
		$id = $t->submit($HTTP_POST_VARS);
		header("Location: poll.$ext?type=change&id=$id");
		break;

	case "admin_languages":
		classload("languages");
		$t = new languages;
		$t->submit($HTTP_POST_VARS);
		header("Location: languages.$ext");
		break;

	case "submit_gallery":
		classload("galleries");
		$t = new galleries;
		$t->submit($HTTP_POST_VARS);
		header("Location: ".$from);
		break;

	case "upload_gallery":
		classload("gallery");
		$t = new gallery($id);
		$t->submit();
		header("Location: galerii.$ext?type=content&id=$id&page=$page");
		break;

	case "submit_nagu":
		classload("nagu");
		$t = new nagu();
		$fid = $t->submit($HTTP_POST_VARS);
		if ($type == "textonly")
			header("Location: nagu.$ext?type=texts&id=$id");
		else
			header("Location: nagu.$ext?type=change_tyyp&id=$id&fid=$fid");
		break;

	case "submit_nagu_ooc":
		classload("nagu");
		$t = new nagu();
		$t->submit_ooc($HTTP_POST_VARS);
		header("Location: nagu.$ext?type=change_ooc&id=$id");
		break;

	case "admin_icons":
		classload("menuedit");
		$m = new menuedit;
		$m->submit_icon();
		header("Location: config.aw");
		break;

	case "menuedit_redirect":
		classload("menuedit");
		$m = new menuedit;
		$m->command_redirect($HTTP_POST_VARS);
		break;

	case "menuedit_newobj":
		classload("menuedit");
		$m = new menuedit;
		$m->menuedit_newobj($HTTP_POST_VARS);
		break;

	case "save_doc_brother":
		classload("documents");
		$d = new db_documents;
		$d->submit_brother($HTTP_POST_VARS);
		header("Location: documents.$ext?type=bro&oid=$docid");
		break;

	case "submit_icon":
		classload("icons");
		$t = new icons;
		$t->submit_icon($HTTP_POST_VARS);
		header("Location: config.$ext?type=icon_db");
		break;

	case "submit_file_icon":
		classload("config");
		$t = new db_config;
		$t->submit_filetype($HTTP_POST_VARS);
		header("Location: config.$ext?type=file_icons");
		break;

	case "save_styles":
		classload("form_cell");
		$t = new form_cell($id,$row,$col);
		$t->set_style($style);
		header("Location: forms.$ext?type=select_cell_style&id=$id&row=$row&col=$col");
		break;

	case "export_icons":
		classload("icons");
		$t = new icons;
		die($t->export($HTTP_POST_VARS));
		break;

	case "import_icons":
		classload("icons");
		$t = new icons;
		$t->import($level);
		break;

	case "export_class_icons":
		classload("config");
		$t = new db_config;
		die($t->export_class_icons($HTTP_POST_VARS));
		break;

	case "import_class_icons":
		classload("config");
		$t = new db_config;
		$t->import_class_icons($level);
		break;

	case "export_file_icons":
		classload("config");
		$t = new db_config;
		die($t->export_file_icons($HTTP_POST_VARS));
		break;

	case "import_file_icons":
		classload("config");
		$t = new db_config;
		$t->import_file_icons($level);
		break;

	case "export_program_icons":
		classload("config");
		$t = new db_config;
		die($t->export_program_icons($HTTP_POST_VARS));
		break;

	case "import_program_icons":
		classload("config");
		$t = new db_config;
		$t->import_program_icons($level);
		break;

	case "export_other_icons":
		classload("config");
		$t = new db_config;
		die($t->export_other_icons($HTTP_POST_VARS));
		break;

	case "import_other_icons":
		classload("config");
		$t = new db_config;
		$t->import_other_icons($level);
		break;

	case "import_all_icons":
		classload("config");
		$t = new db_config;
		$t->import_all_icons($level);
		break;

	case "save_jf":
		classload("config");
		$t = new db_config;
		$t->save_jf($HTTP_POST_VARS);
		header("Location: config.$ext?type=join_form");
		break;

	case "exp_icons":
		classload("config");
		$t = new db_config;
		die($t->do_export($HTTP_POST_VARS));
		break;

	case "submit_icon_zip":
		classload("icons");
		$t = new icons;
		die($t->upload_zip($HTTP_POST_VARS));
		break;

	case "del_icons":
		classload("icons");
		$t = new icons;
		$t->del_icons($sel);
		header("Location: config.$ext?type=icon_db");
		break;

	case "grp_icons":
		classload("icons");
		$t = new icons;
		$t->grp_icons($HTTP_POST_VARS);
		die();
		break;

	case "submit_ic_grp":
		classload("icons");
		$t = new icons;
		$id = $t->submit_ic_grp($HTTP_POST_VARS);
		header("Location: config.$ext?type=icon_db&grp=$id");
		break;

	case "sel_grp":
		header("Location: config.$ext?type=icon_db&grp=$grp");
		break;

	case "del_grp":
		classload("icons");
		$t = new icons;
		$t->del_grp($grp);
		header("Location: config.$ext?type=icon_db");
		break;

	default:
		include("sorry.aw");
	};	

?>
	
