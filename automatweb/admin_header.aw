<?php
$uid = "";	// for the extra paranoid 
session_name("automatweb");
session_start();

classload("timer");
classload("aw_template");
classload("defs");
classload("users");
classload("languages");

// you cannot aw_startup() here, it _will_ break things
// reset aw_cache_* function globals
$GLOBALS["__aw_cache"] = array();
_aw_global_init();

$u = new users;
$u->request_startup();
$l = new languages;
$l->request_startup();

$LC = aw_global_get("LC");

@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/errors.".$GLOBALS["cfg"]["__default"]["ext"]);
@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/common.".$GLOBALS["cfg"]["__default"]["ext"]);


$awt = new aw_timer;

$sf = new aw_template;
$sf->db_init();
$sf->tpl_init("automatweb");
if (!$sf->prog_acl_auth("view", PRG_MENUEDIT))
{
	$sf->auth_error();
}

lc_load("automatweb");
?>
