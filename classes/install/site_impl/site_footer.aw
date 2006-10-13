<?php

$sf = new aw_template();
$sf->tpl_init();
$sf->read_template(!empty($index_template) ? $index_template : "index.tpl");

$_t = aw_global_get("act_period");

$sf->vars(array(
	"content" => $content,
	"per_string" => $_t["description"],
	"date" => $sf->time2date(time(),2),
	"charset" => aw_global_get("charset"),
	"sel_charset" => aw_global_get("charset"),
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
	$astr = "<img src='".aw_ini_get("baseurl")."/orb.aw?class=maitenance&amp;action=cache_update' alt='' height='1' width='1'/>";
	$astr .= "<img src='".aw_ini_get("baseurl")."/orb.aw?class=scheduler&amp;action=static_sched' alt='' height='1' width='1'/>";

	$str = str_replace("</body>", $astr."</body>", $str);
	$str = str_replace("</BODY>", $astr."</BODY>", $str);
	$_SESSION["last_cache_clear"] = time();
}
ob_start();
echo $str;
ob_end_flush();

aw_shutdown();
?>
