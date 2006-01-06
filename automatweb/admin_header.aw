<?php
$uid = "";	// for the extra paranoid 
ini_set("session.save_handler", "files");
session_name("automatweb");
session_start();


if ($_GET["set_ui_lang"] != "")
{
	$_SESSION["user_adm_ui_lc"] = $_GET["set_ui_lang"];
}

lc_init();

classload("timer");
classload("aw_template");
classload("defs");
classload("users");
classload("languages");
classload("core/error", "core/obj/object");

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
register_shutdown_function("log_pv", $GLOBALS["awt"]->timers["__global"]["started"]);

__init_aw_session_track();

$sf = new aw_template;
$sf->db_init();
$sf->tpl_init("automatweb");
if (!$sf->prog_acl_auth("view", PRG_MENUEDIT))
{
	$sf->auth_error();
}

lc_load("automatweb");
?>
