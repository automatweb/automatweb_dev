<?php
// $Header: /home/cvs/automatweb_dev/automatweb/index.aw,v 2.1 2001/05/31 18:17:02 duke Exp $
session_name("automatweb");
session_start();
if (!$uid) {
	include("sorry.aw");
       exit;
};
global $HTTP_HOST;
include("const.aw");
include("admin_header.$ext");

sysload("aw_template");
$t = new aw_template;
$t->db_init();
if (!$t->prog_acl("view", PRG_MENUEDIT))
{
	include("sorry.aw");
	exit;
}
header("Location: nmenuedit.aw");
exit;
?>
