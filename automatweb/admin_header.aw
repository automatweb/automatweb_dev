<?php
$uid = "";	// for the extra paranoid 
session_name("automatweb");
session_start();

classload("timer","aw_template","defs","users","objects","languages");

// there is no need to do aw_startup() here, it will probably do bad things anyway
/*
$u = new users;
$u->request_startup();
$l = new languages;
$l->request_startup();
*/
aw_startup();

$awt = new aw_timer;

$sf = new aw_template;
$sf->db_init();
$sf->tpl_init("automatweb");
if (!$sf->prog_acl_auth("view", PRG_MENUEDIT))
{
	$sf->auth_error();
}

lc_load("automatweb");

$LC=$admin_lang_lc;
setcookie("LC",$LC,time()+24*1000,"/");
aw_global_set("LC", $LC);
?>
