<?php
// siin imporditakse muutujad saidi raami sisse
// ja väljastatakse see
$sf->read_template("index.tpl");

// tehakse kindlaks, milline custom_css
$sf->vars(array("custom" => aw_global_get("uid")));
$ta = aw_global_get("title_action");
if ($ta != "")
{
	$ta.=" / ";
}
$t = new languages;
$sf->vars(array(
	"content"	=> $content,
	"site_title" => $site_title,
	"charset" => $t->get_charset(),
	"uid" => aw_global_get("uid"),
	"title_action" => $ta
));

echo $sf->parse();

$apd = get_instance("layout/active_page_data");
echo $apd->on_shutdown_get_styles();

aw_shutdown();
?>
