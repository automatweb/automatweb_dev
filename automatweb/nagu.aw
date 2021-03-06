<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_FACE))
{
	$tt->prog_acl_error("view", PRG_FACE);
}

classload("nagu");
$t = new nagu;

switch($type)
{
	case "change_ooc":
		$content = $t->change_ooc($id);
		$site_title = "<a href='nagu.$ext'>N&auml;dala n&auml;od</a> / <a href='nagu.$ext?type=change&id=$id'>Muuda</a> / Muuda tegevusi";
		break;

	case "change":
		$content = $t->change($id);
		$site_title = "<a href='nagu.$ext'>H��letus</a> / Muuda";
		break;

	case "add":
		$content = $t->add($id);
		$site_title = "<a href='nagu.$ext'>Valimised</a> / Lisa";
		break;

	case "change_tyyp":
		$content = $t->change($id,$fid);
		$site_title = "<a href='nagu.$ext'>N&auml;dala n&auml;od</a> / <a href='nagu.$ext?type=change&id=$id'>Muuda</a> / Muuda";
		break;

	case "texts":
		$content = $t->texts($id);
		$site_title = "<a href='nagu.$ext'>N&auml;dala n&auml;od</a> / <a href='nagu.$ext?type=change&id=$id'>Muuda</a> / Muuda tekste";
		break;

	case "delete_tyyp":
		$t->delete($id,$fid);
		header("Location: nagu.$ext?type=change&id=$id");
		break;

	case "delete_ooc":
		$t->delete_ooc($nid);
		header("Location: nagu.$ext?type=change_ooc&id=$id");
		break;

	case "change_nagu":
		$content = $t->change($id);
		$site_title = "<a href='nagu.$ext'>N&auml;dala n&auml;od</a> / Muuda";
		break;

	case "edit_texts":
		$content = $t->edit_texts();
		$site_title = "<a href='nagu.$ext'>N&auml;dala n&auml;od</a> / Muuda tekste";
		break;

	case "submit_texts":
		$t->submit_texts($HTTP_POST_VARS);
		header("Location: nagu.$ext");
		exit;

	default:
		$content = $t->list_n2od(aw_ini_get("per_oid"));
		$site_title = "Valimised";
		break;
}

include("admin_footer.$ext");
?>
