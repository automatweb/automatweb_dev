<?php
session_name("automatweb");
session_start();
$uid = $HTTP_SESSION_VARS["uid"];
if (!$uid) 
{
	include("sorry.aw");
	exit;
};

if ($lang_id < 1)
{
	$lang_id = 1;
}

define("UID",$uid);
lc_load("automatweb");
classload("timer","defs","aw_template","users","objects");

$awt = new aw_timer;

$users = new users;
$gidlist = $users->get_gids_by_uid($uid);
$udata = $users->fetch($uid);
$user_email = isset($udata["email"]) ? $udata["email"] : "" ;

$sf = new aw_template;
$sf->tpl_init("automatweb");
$sf->db_init();
// do we really need that?
$ob = new db_objects;

?>
