<?php
// siin imporditakse muutujad saidi raami sisse
// ja väljastatakse see
$sf->read_template("index.tpl");

// tehakse kindlaks, milline custom_css
$sf->vars(array("custom" => aw_global_get("uid")));
$sf->vars(array("custom_css" => $sf->parse("custom_css")));

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

aw_shutdown();
?>