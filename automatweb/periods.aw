<?php
include("const.aw");
include("admin_header.$ext");
include("$classdir/periods.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_PERIODS))
{
	$tt->prog_acl_error("view", PRG_PERIODS);
}

$use_per = ($oid) ? $oid : $per_oid;
$periods = new db_periods($use_per);
$content = "";
$site_title = "<a href='periods.$ext'>Perioodid</a>";
switch($type) {
	case "add":
		$periods->tpl_init("automatweb/periods");
		$periods->read_template("add.tpl");
		$periods->vars(array("oid" => $oid));
		$site_title .= " &gt; Uus";
		$content = $periods->parse();
		break;
	case "savestatus":
		header("Refresh: 0;url=periods.$ext?oid=$oid");
		print "Salvestan..<br>";
		$periods->savestatus($HTTP_POST_VARS);
		exit;
	case "edit":
		$periods->read_template("edit.tpl");
		$site_title .= " &gt; Muuda";
		$cper = $periods->get($id);
		$periods->vars(array("ID" => $cper[id],
				     "description" => $cper[description],
				     "plist" => $periods->period_olist(),
				     "arc" => $periods->option_list($cper[archived],
									array("0" => "Ei",
						                              "1" => "Jah"))));
		$content = $periods->parse();
		break;
	default:
		$periods->read_template("list.tpl");
		$active = $periods->rec_get_active_period();
		$periods->clist();
		while($row = $periods->db_next()) {
			$style = ($row[id] == $active) ? "selected" : "plain";
			$archived = ($row[archived] == 1) ? "checked" : "";
			$actstr = ($row[id] == $active) ? "checked" : "";
			$periods->vars(array("id"          => $row[id],
					     "archived"    => $archived,
					     "active"	   => $actstr,
					     "jrk"	   => $row[jrk],
					     "oldarc"      => $row[archived],
					     "created"     => $periods->time2date($row[created],1),
				     	     "description" => $row[description],
					     "rs"	   => $style));
			$content .= $periods->parse("LINE");
		};
		$periods->vars(array("LINE" => $content,
				     "oldactiveperiod" => $active,
						 "oid" => $use_per));
		$content = $periods->parse();
		$menu[] = "<a href='periods.$ext?type=add'>Lisa</a>";
};
include("admin_footer.$ext");
?>
