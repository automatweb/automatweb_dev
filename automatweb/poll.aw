<?php
include("const.aw");
include("admin_header.$ext");

classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_POLL))
{
	$tt->prog_acl_error("view", PRG_POLL);
}

classload("poll");
$t = new poll;

switch($type)
{
	case "new":
		switch($class_id)
		{
			case CL_POLL:
				$content = $t->add();
				$site_title = "Lisa";
				break;
		}
		break;

	case "set_active":
		$t->set_active($id);
		header("Location: poll.$ext");
		break;

	case "delete":
		$t->delete_object($id);
		header("Location: poll.$ext");
		break;

	case "change":
		$content = $t->change($id);
		$site_title = "<a href='poll.$ext'>Pollid</a> / Muuda";
		break;

	case "add":
		$content = $t->add();
		$site_title = "<a href='poll.$ext'>Pollid</a> / Lisa";
		break;

	default:
		$content = $t->admin();
		$site_title = "Pollid";
}

include("admin_footer.$ext");
?>
