<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_KROONIKA_BANNER))
{
	$tt->prog_acl_error("view", PRG_KROONIKA_BANNER);
}
classload("periods","images");
$periods = new db_periods($per_oid);
$dbi = new db_images;
$active = $periods->get_active_period(5);
$per_oid = 5;
$next = $periods->get_next($active,$per_oid);
$prev = $periods->get_prev($active,$per_oid);
if (!$period) 
{
	$period = $periods->get_active_period(5);
} 
else
if ($period == "next") 
{
	$active = $periods->get_active_period(5);
  $period = $next;
  if (!$period) 
	{
		print "Sellist perioodi pole";
    exit;
  };
  header("Location: banner.$ext?period=$period&op=$op");
  exit;
} 
else
if ($period == "prev") 
{
	$active = $periods->get_cval("activeperiod");
  // $period = $periods->get_prev($active,$per_oid);
  $period = $prev;
  if (!$period) 
	{
		print "Sellist perioodi pole";
    exit;
  };
  header("Location: banner.$ext?period=$period&op=$op");
  exit;
};
$sf->tpl_init("automatweb/banner");
$dbi->list_by_object(12,$period);
while($row = $dbi->db_next()) 
{
	$idata[$row[name]] = $row;
};

switch($op) 
{
	case "banner":
		$site_title = "<a href='$PHP_SELF?period=$period'>Headeri koostamine</a> > Banner";
		$sf->read_template("banner.tpl");
		$sf->sub_merge = 1;
		if ($period == $prev) 
		{
			$sf->parse("PREV");
		} 
		else 
		{
			$sf->parse("PREV_ACT");
		};
		if ($active == $period) 
		{
			$sf->parse("CUR");
		} 
		else 
		{
			$sf->parse("CUR_ACT");
		};
		if ($period == $next) 
		{
			$sf->parse("NEXT");
		} 
		else 
		{
			$sf->parse("NEXT_ACT");
		};
		if (!$idata[banner]) 
		{
			$sf->vars(array("imgref" => "/images/trans.gif",
				        "period" => $period,
					"type"   => "new"));
		} 
		else 
		{
			$sf->vars(array("imgref" => $dbi->get_url($idata[banner][file]),
					"period" => $period,
					"type"   => "replace"));
		};
		$content = $sf->parse();
		break;
	case "upload_banner";
		if ($pilt) 
		{
			if ($type == "new") 
			{
				$dbi->_upload(array("filename" => $pilt,
				                    "file_type" => $pilt_type,
						    "oid"    => 12,
						    "name"  => "banner",
						    "period" => $period));
			} 
			else 
			{
				$dbi->_replace(array("filename" => $pilt,
				                     "file_type" => $pilt_type,
						     "oid"      => 12,
						     "name"    => "banner",
						     "period"  => $period,
								 "poid"		=> $idata[banner][oid]));
			};
		};
		header("Refresh: 0;url=$PHP_SELF?period=$period&op=banner");
		print " ";
		exit;
	case "upload_kaas":
		if ($pilt != "none") {
			if ($type == "new") {
				$dbi->_upload(array("filename" => $pilt,
				                    "file_type" => $pilt_type,
						    "oid"    => 12,
						    "name"  => "kaas",
						    "period" => $period));
			} else {
				$dbi->_replace(array("filename" => $pilt,
				                     "file_type" => $pilt_type,
						     "oid"      => 12,
						     "name"    => "kaas",
						     "period"  => $period,
								 "poid"		=> $idata[kaas][oid]));
			};
		};
		header("Refresh: 0;url=$PHP_SELF?period=$period&op=kaas");
		print " ";
		exit;
	case "kaas":
		$site_title = "<a href='$PHP_SELF?period=$period'>Headeri koostamine</a> > Kaanepilt";
		if ($period == $prev) {
			$menu[] = "Eelmine periood";
		} else {
			$menu[] = "<a href='$PHP_SELF?period=prev&op=kaas'>Eelmine periood</a>";
		};
		if ($active == $period) {
			$menu[] = "Aktiivne periood";
		} else {
			$menu[] = "<a href='banner.$ext?op=kaas'>Aktiivne periood</a>";
		};
		if ($period == $next) {
			$menu[] = "Järgmine periood";
		} else {
			$menu[] = "<a href='$PHP_SELF?period=next&op=kaas'>Järgmine periood</a>";
		};
		$sf->read_template("kaan.tpl");
		if (!$idata[kaas]) {
			$sf->vars(array("imgref" => "/images/trans.gif",
				        "period" => $period,
					"type"   => "new"));
		} else {
			$sf->vars(array("imgref" => $dbi->get_url($idata[kaas][file]),
					"period" => $period,
					"type"   => "replace"));
		};
		$content = $sf->parse();
		break;
	default:
		$site_title = "Headeri koostamine";
		$sf->read_template("list.tpl");
		$sf->vars(array("banner" => $dbi->get_url($idata[banner][file]),
		                "kaas"   => $dbi->get_url($idata[kaas][file])));
		$content = $sf->parse();
		$menu[] = "<a href='$PHP_SELF?op=banner&period=$period'>Muuda bannerit</a>";
		$menu[] = "<a href='$PHP_SELF?op=kaas&period=$period'>Muuda kaanepilti</a>";
		break;
};
$sf->reset();
$sf->tpl_init("automatweb");
include("admin_footer.$ext");
?>
