<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_LANG))
{
	$tt->prog_acl_error("view", PRG_LANG);
}

classload("languages");
$t = new languages;

switch($type)
{
	case "add":
		$title = "<a href='languages.$ext'>Keeled</a> / Lisa";
		$content = $t->add();
		break;

	case "change":
		$title = "<a href='languages.$ext'>Keeled</a> / Muuda";
		$content = $t->change($id);
		break;

	case "set_active":
		$t->set_status($id,2);
		header("Location: languages.$ext");
		break;

	case "set_nactive":
		$t->set_status($id,1);
		header("Location: languages.$ext");
		break;

	case "delete":
		$t->set_status($id,0);
		header("Location: languages.$ext");
		break;

	case "set_sel":
		$t->set_active($id);
		header("Location: languages.$ext");
		break;

	default:
		$title = "Keeled";
		$content = $t->gen_list();
}

include("admin_footer.$ext");
?>
