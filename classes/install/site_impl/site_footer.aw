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

if (aw_ini_get("content.doctype") == "html" )
{
	$str = str_replace  ( "<br />", "<br>", $str);
}
else if (aw_ini_get("content.doctype") == "xhtml" )
{
	$str = str_replace  ( "<br>", "<br />", $str);
}

// this will add google analytics code to html if id is set in ini
if (aw_ini_get("ga_id"))
{
	$sf->read_template("applications/google_analytics/tracking_code.tpl");
	$sf->vars(array(
		"ga_id" => aw_ini_get("ga_id"),
		"ga_page_name" => aw_global_get("ga_page_name")
	));
	$s_code = $sf->parse();
	$str = preg_replace  ( "/<\/body>.*<\/html>/imsU", $s_code."</body>\n</html>" , $str);
}

if (aw_ini_get("content.compress") == 1)
{
	ob_start( 'ob_gzhandler' );
	echo $str;
}
else
{
	ob_start();
	echo $str;
	ob_end_flush();
}

aw_shutdown();

// do a cache clean every hour
if (filectime(aw_ini_get("cache.page_cache")."/temp/lmod") < (time() - 3600))
{
	$m = get_instance("core/maitenance");
	$m->cache_update(array());

	$m = get_instance("scheduler");
	$m->static_sched(array());
}
?>
