<?php
//session_name("automatweb");
//session_start();

//uid = "";	// for the extra paranoid 

include("const.aw");

ini_set("session.save_handler", "files");
session_name("automatweb");
session_start();
unset($_SESSION["nliug"]);

if (is_array($_SESSION["auth_redir_post"]))
{
	$_POST = $HTTP_POST_VARS = $_SESSION["auth_redir_post"];
	extract($_POST);
	$REQUEST_METHOD = "POST";
}


if ($_GET["set_ui_lang"] != "")
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

check_pagecache_folders();

$u = new users;
$u->request_startup();
$l = new languages;
$l->request_startup();

if ($set_ct_lang_id)
{
	$_SESSION["ct_lang_id"] = $set_ct_lang_id;
	$l = get_instance("languages");
	$_SESSION["ct_lang_lc"] = $l->get_langid($set_ct_lang_id);
	aw_global_set("ct_lang_lc", $_SESSION["ct_lang_lc"]);
	aw_global_set("ct_lang_id", $_SESSION["ct_lang_id"]);
}

$LC = aw_global_get("LC");

@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/errors.".$GLOBALS["cfg"]["__default"]["ext"]);
@include($GLOBALS["cfg"]["__default"]["basedir"]."/lang/" . $LC . "/common.".$GLOBALS["cfg"]["__default"]["ext"]);


$awt = new aw_timer;
register_shutdown_function("log_pv", $GLOBALS["awt"]->timers["__global"]["started"]);

__init_aw_session_track();

$sf = new aw_template;
$sf->db_init();
$sf->tpl_init("automatweb");
//if (!$sf->prog_acl_auth("view", PRG_MENUEDIT))
//{
//	$sf->auth_error();
//}

//lc_load("automatweb");


//siit hakkab siis alles pangast tuleva infoga tegelemine

$_SESSION["bank_return"]["data"] = null;
foreach ($_POST as $key => $val)
{
	$_SESSION["bank_return"]["data"][$key] = $val;
}

if($_POST["VK_REF"])
{
	$id = substr($_POST["VK_REF"], 0, -1);
}
if($_GET["ecuno"])
{
	$id = substr($_GET["ecuno"], 0, -1);}
	foreach ($_GET as $key => $val)
	{
		$_SESSION["bank_return"]["data"][$key] = $val;
	}
	//see siis automaatse tagasituleku puhul pangast, miskip'rast teeb hansa get meetodika selle
if($_SESSION["bank_return"]["data"]["VK_REF"])
{
	$id = substr($_SESSION["bank_return"]["data"]["VK_REF"] ,0 , -1 );
}


//logimine
$log = date("d/m/Y H:i : ",time());
foreach($_SESSION["bank_return"]["data"] as $key => $val)
{
	$log.= $key." = ".$val.", ";
}
$log.="\n";
$myFile = $site_dir."/bank_log.txt";
$fh = fopen($myFile, 'a');
fwrite($fh, $log);
fclose($fh);


//esimene on hansapanga, EYP, sampo ja krediidipanga positiivne vastus, teine krediitkaardikeskuse
	if($_SESSION["bank_return"]["data"]["VK_SERVICE"] == 1101  || $_POST["VK_SERVICE"] == 1101 || ($_GET["action"] == "afb" && $_GET["respcode"] == "000"))
{
	$url = $_SESSION["bank_payment"]["url"];
	if(!$url)
	{
		$obj = obj($id);
		$inst = $obj->instance();
		$inst->bank_return(array("id" => $obj->id()));
	}
}
else
{
	$url = $_SESSION["bank_payment"]["cancel"];
}
/*
if(!$url)
{
	$obj = obj($id);
	$inst = $obj->instance();
	$inst->bank_return(array("id" => $obj->id()));
}
*/

header("Location:".$url);
die();

?>



