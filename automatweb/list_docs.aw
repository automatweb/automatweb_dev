<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_DOCLIST))
{
	$tt->prog_acl_error("view", PRG_DOCLIST);
}
if ($period || $periodic) {
	classload("periods");
	$per_oid = ($oid) ? $oid : $per_oid;
	$periods = new db_periods($per_oid);
	if ($period == "next") {
		$active = $periods->get_active_period();
		$period = $periods->get_next($active,$per_oid);
		if (!$period) {
			print "Sellist perioodi pole";
			exit;
		};
		header("Location: list_docs.$ext?period=$period");
		exit;
	};
	if ($period == "prev") {
		$active = $periods->get_active_period();
		$period = $periods->get_prev($active,$per_oid);
		if (!$period) {
			print "Sellist perioodi pole";
			exit;
		};
		header("Location: list_docs.$ext?period=$period");
		exit;
	};
	if (!$period) {
		$period = $periods->get_active_period();
	};
	$pdata = $periods->get($period);
	$main_menu[] = "<a href='$PHP_SELF?period=$period'>Periood $pdata[description]</a>";
};


classload("document");
$t = new document;
$title = "Dokumendid perioodis ".$pdata[description];

switch($action)
{
	case "adddoc":
		$t->read_template("fadd.tpl");
		$par_data = $t->get_object($parent);
		$section = $par_data[name];
		if ($period > 0) {
			$periods = new db_periods($per_oid);
			$pdata = $periods->get($period);
			$pername = $pdata[description];			
		} else {
			$period = 0;
			$pername = "staatiline";
		};
		classload("objects");
		$ob = new db_objects;
		$t->vars(array("section" => $t->picker($section,$ob->get_list()),
				  "period"  => $period,
				  "parent"  => $parent,
				  "pername" => $pername));
		$content = $t->parse();
		break;

	default:
		print "searching for documents<br>";
		$content = $t->list_docs_a(array("period" => $period));
}
include("admin_footer.$ext");
?>
