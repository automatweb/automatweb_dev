<?php
//if ($uid != "")
//	$uid  = "";
session_name("automatweb");
session_start();
$uid = $HTTP_SESSION_VARS["uid"];
if (!$uid) {
	include("sorry.aw");
	exit;
};
define(UID,$uid);
classload("timer","defs","aw_template","users","objects");
lc_load("automatweb");
$awt = new aw_timer;
$sf = new aw_template;
$users = new users;
$gidlist = $users->get_gids_by_uid($uid);
// pgn. igal adminnilehe laadimisel käperdame kasutajat? not a good idea I think.
// $users->touch(UID);

$udata = $users->fetch($uid);
$user_email = $udata["email"];

$sf->tpl_init("automatweb");
$sf->db_init();
// do we really need that?
$ob = new db_objects;

if ($lang_id < 1)
	$lang_id = 1;


?>
