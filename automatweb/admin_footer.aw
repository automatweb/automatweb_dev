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

$sf->vars(array(
	"YAH" => empty($site_title) || aw_global_get("hide_yah") ? ($site_title != "" ? "&nbsp;" : "") : $sf->parse("YAH"),
));

$tmp = array();
if ($site_title != "")	// weird, but lots of places rely on the yah line being empty and thus having no height.
{
	// do the language selecta
	$l = get_instance("languages");
	$li = $l->get_list();
	foreach($li as $lid => $ln)
	{
		if (false && aw_ini_get("config.object_translation"))
		{
			$url = aw_url_change_var("set_lang_id", $lid);//$l->mk_my_orb("right_frame", array("parent" => $GLOBALS["parent"], "period" => $GLOBALS["period"], "set_lang_id" => $lid), "admin_menus");
			$target = "";
		}
		else
		{
			$url = aw_ini_get("baseurl")."/automatweb/index.aw?set_lang_id=".$lid;
			$target = "_top";
		}
		$tmp[] = html::href(array(
			"url" => $url,
			"target" => $target,
			"caption" => ($lid == aw_global_get("lang_id") ? "<b><font color=\"#FF0000\">".$ln."</font></b>" : $ln)
		));
	}

	$sf->vars(array(
		"lang_string" => join("|", $tmp),
		"header_text" => aw_call_header_text_cb()
	));
	$sf->vars(array(
		"LANG_STRING" => $sf->parse("LANG_STRING")
	));
}


$t = new languages;
$sf->vars(array(
	"content"	=> $content,
	"charset" => $t->get_charset(),
	"uid" => aw_global_get("uid"),
	"title_action" => $ta,
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
