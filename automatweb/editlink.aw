<?php
include("const.aw");
include("admin_header.$ext");
classload("extlinks");
$extlinks = new extlinks;
$extlinks->db_init();
switch($op) {
	case "savelink":
		$extlinks->save_link($HTTP_POST_VARS);
		print "Muudatused on salvestatud<br>";
		print "<a href='javascript:window.close()'>Sulge see aken</a>";
		exit;
	default:
};
$extlinks->tpl_init("/automatweb/extlinks");
$link = $extlinks->get_link($target);
$extlinks->read_template("edit.tpl");
$extlinks->vars(array("name" => $link[name],
		"url"  => $link[url],
		"lid"  => $target,
		"checked" => checked($link[newwindow]),
	        "desc" => $link[descript]));
$content = $extlinks->parse();
include("popup_footer.$ext");
?>
