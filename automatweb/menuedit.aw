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
$pdata = array("description" => "");
$period = isset($period) ? $period : 0;
$periodic = isset($periodic) ? $periodic : 0;

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

$m = new menuedit($period,$pdata["description"]);

switch ($type)
{
	case "popup":
		echo $m->gen_folders($period,1);
if ($acl_server_socket)
{
	echo "closing socket <Br>\n";
	fclose($acl_server_socket);
}
		exit;
		break;

	case "folders":
		classload("languages");
		$t = new languages;
		$sf->read_template("index_folders.tpl");
		$sf->vars(array("charset" => $t->get_charset(),"content" => $m->gen_folders($period)));
		echo $sf->parse();
$sums = $awt->summaries();
$site_stop = $awt->get_time();

echo "<!--\n";
while(list($k,$v) = each($sums))
{
	print "$k = $v\n";
};
echo " querys = $qcount \n";
echo "-->\n";
if ($acl_server_socket)
{
	echo "closing socket <Br>\n";
	fclose($acl_server_socket);
}
die();
		break;

	case "menus":
		$content = $m->gen_list($parent,$period);
		break;

	case "objects":
		$content = $m->gen_list_objs($parent);
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

}

include("admin_footer.$ext");
?>
