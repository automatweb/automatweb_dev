<?php
unset($uid);
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

$LC=$admin_lang_lc;
setcookie("LC",$LC,time()+24*1000,"/");

$awt = new aw_timer;

$users = new users;
$gidlist = $users->get_gids_by_uid($uid);
$udata = $users->fetch(UID);
$user_email = isset($udata["email"]) ? $udata["email"] : "" ;

$sf = new aw_template;
$sf->tpl_init("automatweb");
$sf->db_init();
// do we really need that?
$ob = new db_objects;

?>
