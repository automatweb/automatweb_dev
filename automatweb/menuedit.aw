<?php
include("const.aw");
include("$basedir/automatweb/admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_MENUEDIT))
{
	$tt->prog_acl_error("view", PRG_MENUEDIT);
}

if ($type != "objects")
{
	session_register("back");
	$back = $REQUEST_URI;
}

classload("menuedit");
classload("acl");
if ($period || $periodic) 
{
	classload("periods");
	$per_oid = ($oid) ? $oid : $per_oid;
	$periods = new db_periods($per_oid);
	if ($period == "next") 
	{
		$active = $periods->get_active_period();
		$period = $periods->get_next($active,$per_oid);
		if (!$period) 
		{
			print "Sellist perioodi pole";
			exit;
		};
		header("Location: menuedit.$ext?oid=$oid&period=$period");
		exit;
	};
	if ($period == "prev") 
	{
		$active = $periods->get_active_period();
		$period = $periods->get_prev($active,$per_oid);
		if (!$period) 
		{
			print "Sellist perioodi pole";
			exit;
		};
		header("Location: menuedit.$ext?oid=$oid&period=$period");
		exit;
	};
	if (!$period) 
	{
		$period = $periods->get_active_period();
	};
	$pdata = $periods->get($period);
	$main_menu[] = "<a href='$PHP_SELF?period=$period'>Periood $pdata[description]</a>";
};

if (!isset($parent)) 
{
	$parent = 1;
};

$m = new menuedit($period,$pdata[description]);

switch ($type)
{
	case "new":
		switch($class_id)
		{
			case CL_PSEUDO:
				$site_title = $m->gen_header($parent)." - Lisa sektsioon";
				$content = $m->gen_add_html($parent);
				break;
		}
		break;

	case "add_promo":
		$content = $m->add_promo($parent);
		$site_title = $m->gen_header($parent)." / Lisa promo kast";
		break;

	case "popup":
		echo $m->gen_folders($period,1);
		exit;
		break;

	case "folders":
		echo $m->gen_folders($period);
/*		// peatame koik taimerid ja kysime nende kohta info
		$alltimers = $awt->summaries();

		// siia paigutame koikide taimerite väärtused
		$timers_arr = array(); 

		// labelid erinevate taimerite jaoks
		$tlabels = array("__global"  => "Kokku");
		while(list($k,$v) = each($alltimers)) {
			$label = ($tlabels[$k]) ? $tlabels[$k] : $k;
			$timers_arr[] = "$label = $v" . "s";
		};
		die("<br><br><br><font size=1>".join(" | ",$timers_arr)."</font>");*/
		break;

	case "menus":
		die($m->gen_list($parent,$period));
		break;

	case "objects":
		die($m->gen_list_objs($parent));
		break;

	case "popup_objects":
		die($m->gen_picker(array("parent" => $parent,
					 "tpl"    => $tpl)));
		break;

	case "submit_popup_section":
		print "received event $source";
		print "<script language='javascript'>\n";
		print "window.parent.close()";
		print "</script>\n";
		die;
	case "make_active":
		$m->upd_object(array("oid" => $id, "status" => 2));
		header("Location: $PHP_SELF?parent=$parent&menu=menu");
		//header("Location: menuedit.$ext?parent=$parent&menu=menu");
		break;

	case "make_nactive":
		$m->upd_object(array("oid" => $id, "status" => 1));
		header("Location: $PHP_SELF?parent=$parent&menu=menu");
		break;

	case "add_menu":
		$site_title = $m->gen_header($parent)." - Lisa sektsioon";
		$content = $m->gen_add_html($parent);
		break;

	case "add_menu_l3":
		$site_title = $m->gen_header($parent)." - Lisa 3nda taseme men&uuml;&uuml;";
		$content = $m->gen_add_l3_html($parent);
		break;

	case "add_menu_admin":
		$site_title = $m->gen_header($parent)." - Lisa men&uuml;&uuml;";
		$content = $m->gen_add_admin_html($parent);
		break;

	case "change_menu":
		// sektsiooni metainfo muutmine
		$site_title = $m->gen_header($parent)." - Muuda men&uuml;&uuml;d";
		$content = $m->gen_change_html($id);
		break;

	case "delete_menu":
		$m->delete($id);
		header("Location: $PHP_SELF?parent=1&menu=menu");
		break;

	case "paste":
		$m->paste($parent);
		header("Location: $PHP_SELF?parent=$parent&menu=menu");
		break;

	case "prygikoll":
		$content = $m->mk_prygikoll();
		$title = "<a href='$PHP_SELF?menu=menu'>Men&uuml;&uuml;editor</a> / Pr&uuml;gikoll";
		break;

	default:
		$site_title = $m->gen_header($parent);
		$m->rootmenu = $rootmenu;
		if ($menu == "menu") {
			$content = $m->gen_admin_html($parent);
		} else {
			$content = $m->gen_admin_html($parent,"documents.tpl");
		};
}

include("admin_footer.$ext");
?>
