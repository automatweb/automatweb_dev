<?php
// siin imporditakse muutujad saidi raami sisse
// ja väljastatakse see
global $awt;
$sf->read_template("index.tpl");

$i = get_instance(CL_ADMIN_IF);
$i->insert_texts($sf);

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

/*$pm = get_instance("vcl/popup_menu");
$pm->begin_menu("ui_lang");

$i = get_instance("core/trans/pot_scanner");
foreach($i->get_langs() as $_uil)
{
	$pm->add_item(array(
		"text" => $_uil,
		"link" => aw_url_change_var("set_ui_lang", $_uil)
	));
}*/

// check the url for classes and if any of those are in a prod family, then set that
$pf = "";
$clss = aw_ini_get("classes");
if (!empty($_GET["class"]))
{
	$clid = clid_for_name($_GET["class"]);
	if (!empty($clss[$clid]["prod_family"]))
	{
		$pf = $clss[$clid]["prod_family"];
	}
}
$ru = $_GET["return_url"];
while (!empty($ru))
{
	$url_bits = parse_url($ru);
	$vals = array();
	parse_str($url_bits["query"], $vals);
	if (!empty($vals["class"]))
	{
		$clid = clid_for_name($vals["class"]);
		if (!empty($clss[$clid]["prod_family"]))
		{
			$pf = $clss[$clid]["prod_family"];
		}
	}
	$ru = $vals["return_url"];
}

$p = get_current_person();
$co = get_current_company();
$clss = aw_ini_get("classes");
$cur_obj = obj();
if ($sf->can("view", $_GET["id"]))
{
	$cur_obj = obj($_GET["id"]);
}
// do not display the YAH bar, if site_title is empty
$bmb = get_instance("vcl/popup_menu");
$bmb->begin_menu("settings_pop");
$bml = get_instance("vcl/popup_menu");
$bml->begin_menu("lang_pop");

$l = get_instance("languages");
if (aw_ini_get("user_interface.full_content_trans"))
{
	$ld = $l->fetch(aw_global_get("ct_lang_id"));
	$page_charset = $charset = $ld["charset"];
}
else
{
	$ld = $l->fetch(aw_global_get("lang_id"));
	$page_charset = $charset = aw_global_get("charset");
}

$sf->vars(array(
	"prod_family" => $pf,
	"cur_p_name" => $p->name(),
//	"cur_p_url" => html::obj_change_url($p),
	"cur_p_url" => html::get_change_url($p->id(), array('return_url' => get_ru())),
//	"cur_co_url" => html::obj_change_url($co),
	"cur_co_url" => html::get_change_url($co->id(), array('return_url' => get_ru())),
	"cur_co_name" => $co->name(),
	"cur_class" => $clss[clid_for_name($_GET["class"])]["name"],
	"cur_obj_name" => $cur_obj->name(),
	"site_title" => $site_title,
/*	"ui_lang" => $pm->get_menu(array(
		"text" => t("[Liidese keel]")
	)),*/
	"settings_pop" => $bmb->get_menu(array(
		"load_on_demand_url" => $sf->mk_my_orb("settings_lod", array("url" => get_ru()), "user"),
		"text" => '<img src="/automatweb/images/aw06/ikoon_seaded.gif" alt="seaded" width="17" height="17" border="0" align="left" style="margin: -1px 5px -3px -2px" />'.t("Seaded").' <img src="/automatweb/images/aw06/ikoon_nool_alla.gif" alt="#" width="5" height="3" border="0" class="nool" />'
	)),
	"lang_pop" => $bml->get_menu(array(
		"load_on_demand_url" => $sf->mk_my_orb("lang_pop", array("url" => get_ru()), "language"),
		"text" => $ld["name"].' <img src="/automatweb/images/aw06/ikoon_nool_alla.gif" alt="#" width="5" height="3" border="0" class="nool" />'
	)),
));
$shwy = (empty($site_title) || aw_global_get("hide_yah")) && $_GET["class"] != "admin_if";
$sf->vars(array(
	"YAH" => $shwy ? ($site_title != "" ? "&nbsp;" : "") : $sf->parse("YAH"),
	"YAH2" => $shwy ? ($site_title != "" ? "&nbsp;" : "") : $sf->parse("YAH2"),
));

$tmp = array();
if ($site_title != "")	// weird, but lots of places rely on the yah line being empty and thus having no height.
{
	// do the language selecta
	$baseurl = aw_ini_get("baseurl");
	$lang_id = aw_global_get("lang_id");
	$li = $l->get_list();
	foreach($li as $lid => $ln)
	{
		$url = $baseurl."/automatweb/index.aw?set_lang_id=".$lid;
		$target = "_top";
		$tmp[] = html::href(array(
			"url" => $url,
			"target" => $target,
			"caption" => ($lid == $lang_id ? "<b><font color=\"#FF0000\">".$ln."</font></b>" : $ln)
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




// if you set this global variable in your code, then the whole page will be converted and shown
// in the requested charset. This will be handy for translation forms .. and hey .. perhaps one
// day we are going to move to unicode for the whole interface

$output_charset = aw_global_get("output_charset");

if (!empty($output_charset))
{
	$charset = $output_charset;
};

$sf->vars(array(
	"content"	=> $content,
	"charset" => $charset,
	"uid" => aw_global_get("uid"),
	"title_action" => $ta,
));


if ($sf->is_template("aw_styles"))
{
	$sf->vars(array("aw_styles" => $styles));
	$styles_done = true;
};

//if (!empty($output_charset))
//{
//	echo iconv($page_charset,$output_charset . "//TRANSLIT",$sf->parse());
//}
//else
//{
$str= $sf->parse();
if ($_SESSION["last_cache_clear"] < (time() - 3600))
{
	$str .= "<img src='".aw_ini_get("baseurl")."/orb.aw?class=maitenance&action=cache_update' alt='' height='1' width='1'>";
	$_SESSION["last_cache_clear"] = time();
	file_get_contents(aw_ini_get("baseurl")."/orb.aw?class=scheduler&action=static_sched");
}
//};

if (!$styles_done)
{
	$str .= $styles;
};

if ($GLOBALS["__aw_op_handler"])
{
	$f = $GLOBALS["__aw_op_handler"][1];
	$GLOBALS["__aw_op_handler"][0]->$f($str);
}
else
{
	echo $str;
}
aw_shutdown();

flush();

if ($_SESSION["user_history_count"] > 0)
{
	if (!is_array($_SESSION["user_history"]))
	{
		$_SESSION["user_history"] = array();
		$_SESSION["user_history_sets"] = array();
	}
	$pu = parse_url(get_ru());
	parse_str($pu["query"], $bits);
	$st = $site_title;
	if ($bits["id"])
	{
		$o = obj($bits["id"]);
		$st = $o->name();
	}

	if ($bits["group"])
	{
		$gl = $o->get_group_list();
		$st .= " - ".$gl[$bits["group"]]["caption"];
	}
	if ($st != "")
	{
		if ($_SESSION["user_history_has_folders"])
		{
			$has = false;
			foreach(safe_array($_SESSION["user_history"][$bits["class"]]) as $_url => $_t)
			{
				$_pu = parse_url($_url);
				parse_str($_pu["query"], $_bits);
				if ($_bits["class"] == $bits["class"] && $_bits["id"] == $bits["id"] && $_bits["group"] == $bits["group"])
				{
					$has = true;
					break;
				}
			}

			if (!$has)
			{
				$_SESSION["user_history"][$bits["class"]][get_ru()] = strip_tags($st);
			}

			if (count($_SESSION["user_history"][$bits["class"]]) > $_SESSION["user_history_count"])
			{
				array_shift($_SESSION["user_history"][$bits["class"]]);
			}
		}
		else
		{
			$has = false;
			foreach(safe_array($_SESSION["user_history"]) as $_url => $_t)
			{
				$_pu = parse_url($_url);
				parse_str($_pu["query"], $_bits);
				if ($_bits["class"] == $bits["class"] && $_bits["id"] == $bits["id"] && $_bits["group"] == $bits["group"])
				{
					$has = true;
					break;
				}
			}

			if (!$has)
			{
				$_SESSION["user_history"][get_ru()] = strip_tags($st);
			}
			if (count($_SESSION["user_history"]) > $_SESSION["user_history_count"])
			{
				array_shift($_SESSION["user_history"]);
			}
		}
	}
}

?>
