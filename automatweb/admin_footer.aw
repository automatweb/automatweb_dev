<?php
// siin imporditakse muutujad saidi raami sisse
// ja väljastatakse see

$sf->read_template("index.tpl");

$ta = aw_global_get("title_action");
if ($ta != "")
{
	$ta.=" / ";
}

// I need the styles in the HEADER of the page, otherwise I get dump whitespace
// at the bottom of the page which will bite me, if I'm using iframe based layout
// thingie, and styles have to be in the page header anyway according to the W3C specs

// so this code checks whether aw_styles subtemplate exists and if so, replaces
// it with the style definition .. otherwise it will add them at the bottom of the page
// as before
$apd = get_instance("layout/active_page_data");
$styles = $apd->on_shutdown_get_styles();
$styles_done = false;

// do not display the YAH bar, if site_title is empty
$sf->vars(array(
	"site_title" => $site_title,
));

$t = new languages;
$sf->vars(array(
	"content"	=> $content,
	"charset" => $t->get_charset(),
	"uid" => aw_global_get("uid"),
	"title_action" => $ta,
	"YAH" => empty($site_title) || aw_global_get("hide_yah") ? "" : $sf->parse("YAH"),
));


if ($sf->is_template("aw_styles"))
{
	$sf->vars(array("aw_styles" => $styles));
	$styles_done = true;
};

echo $sf->parse();

if (!$styles_done)
{
	echo $styles;
};
aw_shutdown();
?>
