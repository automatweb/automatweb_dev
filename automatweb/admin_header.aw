<?php
session_name("automatweb");
session_start();

classload("timer","aw_template","defs");
$sf = new aw_template;
$sf->db_init();

$uid = $HTTP_SESSION_VARS["uid"];
// it doesn't matter if the uid is not set, prog_acl performs that check 
// and responds appropriately
define("UID",$uid);
$awt = new aw_timer;

if (strpos($HTTP_HOST,"horizon") !== false)
{
	if (!$sf->prog_acl("view", PRG_MENUEDIT))
	{
		include("sorry.aw");
		exit;
	}
}
else
{
	if (!$sf->prog_acl_auth("view", PRG_MENUEDIT))
	{
		include("sorry.aw");
		exit;
	}
}

if ($lang_id < 1)
{
	$lang_id = 1;
}

lc_load("automatweb");
classload("users","objects");

$LC=$admin_lang_lc;
setcookie("LC",$LC,time()+24*1000,"/");


$users = new users;
$gidlist = $users->get_gids_by_uid($uid);
$udata = $users->fetch(UID);
$user_email = isset($udata["email"]) ? $udata["email"] : "" ;

$sf->tpl_init("automatweb");
// do we really need that?
$ob = new db_objects;

?>
