<?php
include("const.aw");
include("admin_header.$ext");
classload("acl","groups");

session_register("back");

$acl = new acl;
$odata = $acl->get_object($oid);

switch($type)
{
	case "addgrp":
		$site_title = "<a href='$back'>Tagasi</a> / $odata[name] / <a href='editacl.$ext?oid=$oid&file=$file'>ACL</a> / Lisa gruppe";
		$t = new groups;
		$content = $t->gen_pick_list();
		break;

	default:
		$site_title = "<a href='$back'>Tagasi</a> / $odata[name] / ACL";
		$content = $acl->gen_acl_form($oid,$file);
};
include("admin_footer.$ext");
?>
