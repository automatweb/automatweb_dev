<?php
include("const.aw");
include("admin_header.aw");

session_register("msgboard_type");
session_register("msgboard_order");

if ($type == "flat")
	$msgboard_type = "flat";
else
if ($type == "nested")
	$msgboard_type = "nested";
else
if ($type == "threaded")
	$msgboard_type = "threaded";

if ($msgboard_type == "")
	$msgboard_type = "threaded";

if ($order == "forward")
	$msgboard_order = "forward";
else
if ($order == "reverse")
	$msgboard_order = "reverse";

if ($msgboard_order == "")
	$msgboard_order = "reverse";

classload("msgboard");
classload("menuedit");
$t = new msgboard;
$m = new menuedit;
global $baseurl;
switch($action)
{
	case "submit_votes":
		$t->submit_votes($HTTP_POST_VARS);
		header("Location: $baseurl/comments.aw?action=topics");
		print " ";
		exit;
	case "topics":
		$string = $t->list_topics($forum_id);
		break;

	case "topic_detail":
		$string = $t->list_topics_detail();
		break;

	case "addtopic":
		if (!$t->prog_acl("view",PRG_MENUEDIT))
		{
			header("Location: $baseurl");
			exit;
		};
		$string = $t->add_topic($forum_id);
		break;

	case "delete_topic":

		if (!$t->prog_acl("view",PRG_MENUEDIT))
		{
			header("Location: $baseurl");
			exit;
		};
		$t->delete_topic($id,$forum_id);
		header("Location: comments.aw?action=topics&forum_id=$forum_id");
		break;

	case "markallread":
		$t->markallread($forum_id);
		header("Location: comments.aw?action=topics&forum_id=$forum_id");
		break;

	case "search_comments":
		$string = $t->do_search($HTTP_GET_VARS);
		break;

	case "search":
		$string = $t->search($section,$forum_id);
		break;

	case "add":
		$string = $t->add($parent, $section,$page,$forum_id);
		break;

	case "delete":
		if (!$t->prog_acl("view",PRG_MENUEDIT))
		{
			header("Location: $baseurl");
			exit;
		};
		if ($uid != "")
			$t->delete_comment($parent,$forum_id);

	default:
		if ($from == "search")
			$page = $t->get_page_for_comment($section,$cid,$forum_id);
		$string = $t->show($section,$page,$forum_id);
}

$content = $string;

include("admin_footer.aw");
?>
