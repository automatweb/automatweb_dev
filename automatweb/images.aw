<?php
include("const.aw");
require("admin_header.$ext");
require("$classdir/images.$ext");
require("$classdir/acl.$ext");
session_register("back");
$img = new db_images;
$img->tpl_init("automatweb/images");
if ($action == "upload") {
	$img->_upload(array("filename" => $pilt,"file_type" => $pilt_type,"oid" => $parent,"descript" => $comment, "link" => $link));
	header("Location: documents.$ext?docid=$parent");
	exit;
} elseif ($action == "replace") {
	$img->_replace(array("pilt" => $pilt,
		       "pilt_type" => $pilt_type,
		       "parent" => $parent,
		       "idx"    => $idx,
		       "comment" => $comment,
		       "poid" => $poid,
					 "link" => $link));
	header("Location: documents.$ext?docid=$parent");
	exit;
};

classload("document");
$doc = new document;

switch($type) {
	case "new":
		switch($class_id)
		{
			case CL_IMAGE:
				$site_title = "Lisa pilt";
				$content = $img->add($parent);
				break;
		}
		break;

	case "list":
		$p_obj = $doc->fetch($parent);
		$p_name = (strlen($p_obj[title]) > 0) ? "Dokument: $p_obj[title]" : "Nimetu dokument";
		$site_title = "<a href='documents.$ext?docid=$parent'>$p_name</a> - Nimekiri";
		$content = $img->gen_list($parent);
		break;

	case "add_image":
		// kui siia tullakse dokumendi muutmisest, siis on dokumendi ID muutujas docid
		//  .. ma ei tea, kust see parent pärineb, aga igaks juhuks ma ei muuda seda
		if (!$parent) {
			$parent = $docid;
		};
		$p_obj = $doc->fetch($parent);
		$p_name = (strlen($p_obj[title]) > 0) ? "Dokument: $p_obj[title]" : "Nimetu dokument";
		$site_title = "<a href='documents.$ext?docid=$parent'>$p_name</a> - Lisa pilt";
		$content = $img->add($parent);
		break;
	case "delete_image":
		$img->del_image_by_oid($id);
		header("Location: images.$ext?type=list&parent=$parent");
		break;

	default:
		$p_obj = $doc->fetch($parent);
		$p_name = (strlen($p_obj[title]) > 0) ? "Dokument: $p_obj[title]" : "Nimetu dokument";
		$site_title = "<a href='documents.$ext?docid=$parent'>$p_name</a> - Muuda pilti";
		if (!$parent) {
			$parent = $docid;
		};
		$content = $img->edit($oid,$parent);
		break;
};
require("admin_footer.$ext");
?>
