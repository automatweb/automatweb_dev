<?php
$uid = "";	// for the extra paranoid
ini_set("session.save_handler", "files");
session_name("automatweb");
session_start();

unset($_SESSION["nliug"]);

if (isset($_SESSION["auth_redir_post"]) && is_array($_SESSION["auth_redir_post"]))
{
	$_POST = $HTTP_POST_VARS = $_SESSION["auth_redir_post"];
	extract($_POST);
	$REQUEST_METHOD = "POST";
}


if (!empty($_GET["set_ui_lang"]))
{
	$_SESSION["user_adm_ui_lc"] = $_GET["set_ui_lang"];
}

lc_init();

classload("core/util/timer");
classload("aw_template");
classload("defs");
classload("users");
classload("languages");
classload("core/error", "core/obj/object");

// you cannot aw_startup() here, it _will_ break things
// reset aw_cache_* function globals
$GLOBALS["__aw_cache"] = array();
_aw_global_init();

aw_set_exec_time(AW_SHORT_PROCESS);

check_pagecache_folders();

$u = new users;
$u->request_startup();
$l = new languages;
$l->request_startup();

if (!empty($set_ct_lang_id))
{
	$_SESSION["ct_lang_id"] = $set_ct_lang_id;
	$l = get_instance("languages");
	$_SESSION["ct_lang_lc"] = $l->get_langid($set_ct_lang_id);
	aw_global_set("ct_lang_lc", $_SESSION["ct_lang_lc"]);
	aw_global_set("ct_lang_id", $_SESSION["ct_lang_id"]);
}

$LC = aw_global_get("LC");

@include(aw_ini_get("basedir")."/lang/" . $LC . "/errors.".aw_ini_get("ext"));
@include(aw_ini_get("basedir")."/lang/" . $LC . "/common.".aw_ini_get("ext"));


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
if ($_GET["id"] || $_GET["parent"])
{
	$sc = get_instance("contentmgmt/site_cache");
	$sc->ip_access(array("force_sect" => $_GET["parent"] ? $_GET["parent"] : $_GET["id"]));
}

lc_load("automatweb");
?>
