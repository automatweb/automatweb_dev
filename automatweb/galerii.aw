<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_GALERII))
{
	$tt->prog_acl_error("view", PRG_GALERII);
}

classload("galleries");
classload("gallery");
$t = new galleries;

if ($page < 1)
	$page = 0;

switch($type)
{
	case "new":
		$content = $t->add($parent,$alias_doc);
		$site_title = "<a href='".$PHP_SELF."'>Galeriid</a> / Lisa uus";
		break;

	case "change":
		$content = $t->change($id);
		$site_title = "<a href='".$PHP_SELF."'>Galeriid</a> / Muuda";
		break;

	case "delete":
		$t->delete($id);
		header("Location: ".$PHP_SELF);
		break;

	case "content":
		$g = new gallery($id);
		$content = $g->admin($page);
		$site_title = "<a href='".$PHP_SELF."'>Galeriid</a> / Sisu";
		break;

	case "add_row":
		$g = new gallery($id);
		$g->add_rows($page,$rows);
		header("Location: $PHP_SELF?type=content&id=$id&page=$page");
		break;

	case "add_col":
		$g = new gallery($id);
		$g->add_cols($page,$cols);
		header("Location: $PHP_SELF?type=content&id=$id&page=$page");
		break;

	case "del_row":
		$g = new gallery($id);
		$g->del_row($page);
		header("Location: $PHP_SELF?type=content&id=$id&page=$page");
		break;

	case "del_col":
		$g = new gallery($id);
		$g->del_col($page);
		header("Location: $PHP_SELF?type=content&id=$id&page=$page");
		break;

	case "add_page":
		$g = new gallery($id);
		$page = $g->add_page();
		header("Location: $PHP_SELF?type=content&id=$id&page=$page");
		break;

	default:
		$content = $t->gen_list();
		$site_title = "Galeriid";
		break;
}

include("admin_footer.$ext");
?>
