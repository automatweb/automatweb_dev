<?php
require("const.aw");
session_name("xid");
session_start();
if (!$uid) {
	include("sorry.aw");
	exit;
};
classload("aw_template");
classload("users","defs");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_AWMAIL))
{
	$tt->prog_acl_error("view", PRG_AWMAIL);
}

$users = new users;
$gidlist = $users->get_gids_by_uid($uid);
$users->touch($uid);

classload("pop3");
classload("mailbox");

$t = new mailbox;

switch ($type)
{
	case "sendback":
		$t->sendback($mail);
		break;

	case "delete":
		$t->delete_message($id);
		header("Location: mail_frameset.html");
		break;

	case "print":
		$content = $t->do_print($id);
		break;

	case "forward":
		$content = $t->forward($id);
		break;

	case "reply":
		$content = $t->reply($id);
		break;

	case "config":
		$content = $t->configure();
		break;

	case "new_mail":
		$content = $t->new_mail($to);
		break;

	case "change_mail":
		$content = $t->change_mail($id);
		break;

	case "mod_folder":
		$content = $t->change_folder($id);
		break;

	case "add_folder":
		$content = $t->add_folder($parent);
		break;

	case "del_folder":
		$t->del_folder($id);
		header("Location: mail.aw?type=folders");
		break;

	case "folders":
		$content = $t->gen_folders($parent);
		break;

	case "toolbar":
		$t->read_template("toolbar.tpl");
		$content = $t->parse();
		break;

	case "check_mail":
		$content = $t->check_mail();
		if (!$content)
		{
			header("Location: mail_frameset.html");
			die();
		}
		break;

	case "show_mail":
		$content = $t->show_mail($id);
		break;

	default:
		$content = $t->msg_list($parent);
}

$sf = new aw_template;
$sf->tpl_init("mailbox");
$sf->read_template("index.tpl");
$sf->vars(array("content" => $content));
echo $sf->parse();
?>
