<?php

$sf = new file();
$sf->tpl_init();
$sf->read_template(!empty($index_template) ? $index_template : "index.tpl");

$meta = aw_unserialize($sf->get_special_file(array("name" => "meta.tags")));

$_t = aw_global_get("act_period");

$lang = get_instance("languages");
$ld = $lang->fetch(aw_global_get("lang_id"));

$sf->vars(array(
	"content" => $content,
	"per_string" => $_t["description"],
	"keywords" => $meta[$section]["keywords"],
	"description" => $meta[$section]["description"],
	"date" => $sf->time2date(time(),2),
	"charset" => $ld["charset"],
	"sel_charset" => $ld["charset"],
	"title_action" => aw_global_get("title_action"),
));

if (aw_global_get("uid")) 
{
	$sf->vars(array(
		"login" => "",
		"uid"  => aw_global_get("uid"),	
	));
	$sf->parse("logged");
} 
else 
{
	$sf->vars(array("logged" => ""));
	$sf->parse("login");
}

if (aw_ini_get("menuedit.protect_emails") == 1)
{
	$i = get_instance("contentmgmt/mail_protector");
	$str = $i->protect($sf->parse());
}
else
{
	$str = $sf->parse();
}
// do a cache clean every hour for this session
if ($_SESSION["last_cache_clear"] < (time() - 3600))
{
	$str .= "<img src='".aw_ini_get("baseurl")."/orb.aw?class=maitenance&action=cache_update' alt='' height='1' width='1'>";
	$_SESSION["last_cache_clear"] = time();
}
echo $str;

aw_shutdown();
?>
