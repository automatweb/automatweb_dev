<?php
// siin imporditakse muutujad saidi raami sisse
// ja v2ljastatakse see
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
// check the url for classes and if any of those are in a prod family, then set that
$pf = "";
$clss = aw_ini_get("classes");
if (!empty($_GET["class"]))
{
	$clid = clid_for_name($_GET["class"]);
	if (!empty($clss[$clid]["prod_family"]))
	{
		$pf = $clss[$clid]["prod_family"];
		$pf_url = aw_global_get("REQUEST_URI");
	}
}
$ru = isset($_GET["return_url"]) ? $_GET["return_url"] : null;
while (!empty($ru) && empty($pf))
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
			$pf_url = $ru;
		}
	}
	$ru = $vals["return_url"];
}
aw_disable_acl();
$p = get_current_person();
$co = get_current_company();
if (!$co)
{
	$co = obj();
}
aw_restore_acl();
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

$l = get_instance("core/languages");
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

if ($pf_url == "")
{
	$pf_url = aw_ini_get("baseurl")."/automatweb/";
}

$class_names = array(
	"doc" => t("Dokument"),
	"config" => t("Seaded"),
);
$cur_class = empty($clss[clid_for_name($_GET["class"])]["name"]) ? $class_names[$_GET["class"]] : $clss[clid_for_name($_GET["class"])]["name"];
$parent = max(1, $_GET["parent"] ? $_GET["parent"] : $cur_obj->parent());
$sf->vars(array(
	"parent" => $_GET["parent"],
	"prod_family" => $pf,
	"prod_family_href" => $pf_url,
	"cur_p_name" => $p->name(),
	"cur_p_url" => html::get_change_url($p->id(), array('return_url' => get_ru())),
	"cur_co_url" => html::get_change_url($co->id(), array('return_url' => get_ru())),
	"cur_co_url_view" => $sf->mk_my_orb("view", array("id" => $co->id(), 'return_url' => get_ru()), CL_CRM_COMPANY),
	"cur_co_name" => $co->name(),
	"cur_class" => $cur_class,
	"cur_obj_name" => $cur_obj->name(),
	"site_title" => $site_title,
	"stop_pop_url_add" => $sf->mk_my_orb("stopper_pop", array(
		"s_action" => "start",
		"new" => 1,
	), CL_TASK),
	"stop_pop_url_quick_add" => $sf->mk_my_orb("stopper_pop", array(
		"source" => $_GET["class"],
		"source_id" => $_GET["id"],
		"s_action" => "start",
		"new" => 1,
	), CL_TASK),
	"stop_pop_url_qw" => $sf->mk_my_orb("stopper_pop", array(), CL_TASK),
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
	"parent" => $parent,
	"random" => rand(100000,1000000),
));

if ($sf->prog_acl("view", "disp_person"))
{
	$sf->vars(array(
		"SHOW_CUR_P" => $sf->parse("SHOW_CUR_P")
	));
}
if ($sf->prog_acl("view", "disp_co_edit"))
{
	$sf->vars(array(
		"SHOW_CUR_CO" => $sf->parse("SHOW_CUR_CO")
	));
}
if ($sf->prog_acl("view", "disp_co_view") && !$sf->prog_acl("view", "disp_co_edit"))
{
	$sf->vars(array(
		"SHOW_CUR_CO_VIEW" => $sf->parse("SHOW_CUR_CO_VIEW")
	));
}
if ($sf->prog_acl("view", "disp_object_type"))
{
	$sf->vars(array(
		"SHOW_CUR_CLASS" => $sf->parse("SHOW_CUR_CLASS")
	));
}
if ($sf->prog_acl("view", "disp_object_link"))
{
	$sf->vars(array(
		"SHOW_CUR_OBJ" => $sf->parse("SHOW_CUR_OBJ")
	));
}
$shwy =  (empty($site_title) || aw_global_get("hide_yah")) && $_GET["class"] != "admin_if";

if (!empty($_GET["in_popup"]))
{
	$sf->vars(array(
		"NO_HEADER" => $sf->parse("NO_HEADER")
	));
	$site_title = "";
	$shwy = true;
}
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
}

// compose html title
$html_title = aw_ini_get("stitle");
$html_title_obj = (CL_ADMIN_IF == $cur_obj->class_id()) ? aw_global_get("site_title_path_obj_name") : $cur_obj->name();

if (!empty($html_title))
{
	$html_title .= " - " . $cur_class;
}

if (!empty($html_title_obj))
{
	$html_title .=  ": " . $html_title_obj;
}

$cache = get_instance("cache");

classload("core/util/minify_js_and_css");

$sf->vars(array(
	"content"	=> $content,
	"charset" => $charset,
	"title_action" => $ta,
	"html_title" => $html_title,
	"MINIFY_JS_AND_CSS" => minify_js_and_css::parse_admin_header($sf->parse("MINIFY_JS_AND_CSS")),
	"POPUP_MENUS" => $cache->file_get("aw_toolbars"),
));
$cache->file_set("aw_toolbars", "");

if ($sf->is_template("aw_styles"))
{
	$sf->vars(array("aw_styles" => $styles));
	$styles_done = true;
}

// include those javascript files to header which are loaded in application classes
$sf->vars(array("javascript" => $apd->get_javascript()));


//if (!empty($output_charset))
//{
//	echo iconv($page_charset,$output_charset . "//TRANSLIT",$sf->parse());
//}
//else
//{
$str= $sf->parse();
classload("core");
if ($_SESSION["last_cache_clear"] < (time() - 3600))
{
	$str .= "<img src='".aw_ini_get("baseurl")."/orb.aw?class=maitenance&action=cache_update' alt='' height='1' width='1'>";
	$_SESSION["last_cache_clear"] = time();
	$str .= "<img src='".aw_ini_get("baseurl")."/orb.aw?class=scheduler&action=static_sched' alt='' height='1' width='1'>";
}
//};
if (!$styles_done)
{
	$str .= $styles;
};

if (function_exists("get_time"))
{
	$GLOBALS["__END_DISP"] = get_time();
}

if (!empty($GLOBALS["__aw_op_handler"]))
{
	$f = $GLOBALS["__aw_op_handler"][1];
	$GLOBALS["__aw_op_handler"][0]->$f($str);
}
else
{
	if (aw_ini_get("content.compress") == 1)
	{
		ob_start( 'ob_gzhandler' );
	}
	echo $str;
}
if (aw_ini_get("content.compress") != 1)
{
	ob_end_flush();
}
aw_shutdown();


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
	$o = obj();
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
