<?php
include("const.aw");
include("admin_header.$ext");
classload("groups");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_GROUPS))
{
	$tt->prog_acl_error("view", PRG_GROUPS);
}

$t = new groups;

switch($type)
{
	case "new":
		switch($class_id)
		{
			case CL_GROUP:
				$content = $t->gen_add($parent,$level,$grp_level);
				$site_title = "Lisa grupp";
				break;
		}
		break;

	case "add":
		$content = $t->gen_add($parent,$level,$grp_level);
		$site_title = "<a href='groups.$ext'>Grupid</a> / Lisa grupp";
		break;

	case "change":
		$content = $t->gen_change($gid,$level);
		$site_title = "<a href='groups.$ext'>Grupid</a> / Muuda gruppi";
		break;

	case "delete":
		$t->deletegroup($gid);
		header("Location: groups.$ext?parent=0");
		break;

	default:
		$content = $t->gen_list($parent,$all,$groups);
//		$site_title = "Grupid";
}

include("admin_footer.$ext");
?>
