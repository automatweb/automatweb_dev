<?php
// $Header: /home/cvs/automatweb_dev/automatweb/index.aw,v 2.2 2001/06/18 21:34:25 kristo Exp $
session_name("automatweb");
session_start();
if (!$uid) 
{
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
