<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_LISTS))
{
	$tt->prog_acl_error("view", PRG_LISTS);
}

classload("lists");
classload("list");
classload("vars");
classload("email");
classload("acl");

switch($type) {
	case "new":
		switch($class_id)
		{
			case CL_MAILINGLIST:
				$site_title = "Lisa list";
				$t = new lists;
				$content = $t->add_list($parent);
				break;

			case CL_MAILINGLIST_VARIABLE:
				$site_title = "Lisa muutuja";
				$t = new variables;
				$content = $t->add_var($parent);
				break;

			case CL_MAILINGLIST_STAMP:
				$site_title = "Lisa stamp";
				$t = new variables;
				$content = $t->add_stamp();
				break;

			case CL_EMAIL:
				$t = new email;
				$site_title = "Uus mail";
				$content =$t->new_mail($parent);
				break;
		}
		break;

	case "add_cat":
		$site_title = "<a href='list.$ext?parent=$parent'>Listid</a> / Lisa kategooria";
		$t = new lists;
		$content = $t->add_cat($parent);
		break;

	case "add_var_cat":
		$site_title = "<a href='list.$ext?type=list_vars&parent=$parent'>Muutujad</a> / Lisa kategooria";
		$t = new variables;
		$content = $t->add_cat($parent);
		break;

	case "change_cat":
		$site_title = "<a href='list.$ext?parent=$parent'>Listid</a> / Muuda kategooriat";
		$t = new lists;
		$content = $t->change_cat($id);
		break;

	case "change_var_cat":
		$site_title = "<a href='list.$ext?type=list_vars&parent=$parent'>Muutujad</a> / Muuda kategooriat";
		$t = new variables;
		$content = $t->change_cat($id);
		break;

	case "delete_cat":
		$t = new lists;
		$t->delete_cat($id);
		header("Location: list.$ext?parent=$parent");
		break;

	case "delete_var_cat":
		$t = new variables;
		$t->delete_cat($id);
		header("Location: list.$ext?type=list_vars&parent=$parent");
		break;
		
	case "add_list":
		$site_title = "<a href='list.$ext?parent=$parent'>Listid</a> / Lisa list";
		$t = new lists;
		$content = $t->add_list($parent);
		break;

	case "change_list":
		$t = new lists;
		$content = $t->change_list($id);
		$site_title = "<a href='list.$ext?parent=$t->parent'>Listid</a> / Muuda listi";
		break;

	case "delete_list":
		$t = new lists;
		$t->delete_list($id);
		header("location: list.$ext?type=list_lists?parent=$parent");
		break;

	case "list_mails":
		$t = new email;
		$parent = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $id","parent");
		$site_title = "<a href='list.$ext?parent=$parent'>Listid</a> / Mailide nimekiri";
		$content = $t->list_mails($id);
		break;
	
	case "submit_default":
		$t = new email;
		$t->submit_default($HTTP_POST_VARS);
		header("Location: list.$ext?type=list_mails&id=$id");
		print "\n\n";
		exit;
	
	case "new_mail":
		$t = new email;
		$pid = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $parent","parent");
		$site_title = "<a href='list.$ext?parent=$pid'>Listid</a> / <a href='list.$ext?type=list_mails&id=$parent'>Mailide nimekiri</a> / Uus mail";
		$content =$t->new_mail($parent);
		break;
		
	case "change_mail":
		$t = new email;
		$pid = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $parent","parent");
		$site_title = "<a href='list.$ext?parent=$pid'>Listid</a> / <a href='list.$ext?type=list_mails&id=$parent'>Mailide nimekiri</a> / Muuda maili";
		$content = $t->change_mail($id);
		break;
			
	case "delete_mail":
		$t = new email;
		$t->delete_mail($id);
		header("Location: list.$ext?type=list_mails&id=$parent");
		break;
		
	case "mail_preview":
		$t = new email;
		$pid = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $parent","parent");
		$site_title = "<a href='list.$ext?parent=$pid'>Listid</a> / <a href='list.$ext?type=list_mails&id=$parent'>Mailide nimekiri</a> / Maili eelvaade";
		$content = $t->mail_preview($id);
		break;
			
	case "send_mail":
		$t = new email;
		$t->send_mail($id);
		die("<a href='list.$ext'>Tagasi</a>");
		break;
	
	case "list_inimesed":
		$t = new mlist($id);
		$pid = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $id","parent");
		$site_title = "<a href='list.$ext?parent=$pid'>Listid</a> / Listi ".$t->name." liikmed";
		$content = $t->list_users();
		break;
				
	case "add_user":
		$t = new mlist($id);
		$pid = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $id","parent");
		$site_title = "<a href='list.$ext?parent=$pid'>Listid</a> / <a href='list.$ext?type=list_inimesed&id=$id'>Listi $t->name liikmed</a> / Lisa kasutaja";
		$content = $t->add_user();
		break;
		
	case "change_user":
		$t = new mlist($id);
		$pid = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $id","parent");
		$site_title = "<a href='list.$ext?parent=$pid'>Listid</a> / <a href='list.$ext?type=list_inimesed&id=$id'>Listi $t->name liikmed</a> / Muuda kasutajat";
		$content = $t->change_user($user_id);
		break;
			
	case "paste_user":
		$t = new mlist($id);
		$t->paste($id);
		header("Location: list.$ext?type=list_inimesed&id=$id");
		break;

	case "import_file":
		$t = new mlist($id);
		$pid = $t->db_fetch_field("SELECT parent FROM objects WHERE oid = $id","parent");
		$site_title = "<a href='list.$ext?parent=$pid'>Listid</a> / Impordime failist mailiaadresse listi $t->name";
		$content = $t->import_mails();
		break;

	case "export_file":
		$t = new mlist($id);
		$t->export_mails();
		die();
		break;

	case "list_vars":
		$site_title = "Muutjate nimekiri";
		$t = new variables;
		$content = $t->gen_list($parent);
		break;
		
	case "add_var":
		$site_title = "<a href='list.$ext?type=list_vars&parent=$parent'>Muutujate nimekiri</a> / Lisa muutuja";
		$t = new variables;
		$content = $t->add_var($parent);
		break;
			
	case "change_var":
		$t = new variables;
		$content = $t->change_var($id);
		$site_title = "<a href='list.$ext?type=list_vars&parent=$t->parent'>Muutujate nimekiri</a> / Muuda muutujat";
		break;
		
	case "delete_var":
		$t = new variables;
		$t->delete_var($id);
		header("Location: list.$ext?type=list_vars&parent=$parent");
		break;

	case "list_stamps":
		$site_title = "Stampide nimekiri";
		$t = new variables;
		$content = $t->list_stamps();
		break;
		
	case "change_stamp":
		$site_title = "<a href='list.$ext?type=list_stamps'>Stampide nimekiri</a> / Muuda stampi";
		$t = new variables;
		$content = $t->change_stamp($id);
		break;
		
	case "delete_stamp":
		$t = new variables;
		$t->delete_stamp($id);
		header("Location: list.$ext?type=list_stamps");
		break;
		
	case "add_stamp":
		$site_title = "<a href='list.$ext?type=list_stamps'>Stampide nimekiri</a> / Lisa stamp";
		$t = new variables;
		$content = $t->add_stamp();
		break;
		
	case "change_list_vars":
		$site_title = "<a href='list.$ext?parent=$parent'>Listide nimekiri</a> / Vali listi muutujad";
		$t = new mlist($id);
		$content = $t->change_vars($parent);
		break;
	
	case "submit_default_list":
		$t = new lists;
		$t->submit_default_list($HTTP_POST_VARS);
		header("Location: list.$ext?parent=$parent&op=open");
		print "\n\n";
		exit;

	case "list_lists":
	default:
		$site_title = "Listide nimekiri";
		$t = new lists;
		$content = $t->gen_list($parent);
};

session_register("back");
$back = $REQUEST_URI;
include("admin_footer.$ext");
?>
