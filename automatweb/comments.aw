<?php
include("const.aw");
include("$basedir/automatweb/admin_header.$ext");
classload("defs","timer","aw_template","document","menuedit");
$awt = new aw_timer();

session_register("msgboard_type");

if ($type == "flat")
	$msgboard_type = "flat";
else
if ($type == "nested")
	$msgboard_type = "nested";

if ($msgboard_type == "")
	$msgboard_type = "nested";

classload("msgboard");
$t = new msgboard;

switch($action)
{
		
	case "search_comments":
		$content = $t->do_search($HTTP_GET_VARS);
		break;

	case "search":
		$content = $t->search($section);
		break;

	case "add":
		$content = $t->add($parent, $section,$page);
		break;

	case "delete":
		if ($uid != "")
			$t->delete_comment($parent);

	default:
		if ($from == "search")
			$page = $t->get_page_for_comment($section,$cid);
		$content = $t->show($section,$page);
}

include("admin_footer.aw");
?>
