<?php
include("const.aw");
include("site_header.".aw_ini_get("ext"));

$te = new aw_template;
$te->init("");
$te->read_template("login.tpl");

$m = new menuedit(aw_ini_get("per_oid"));

$si =&__get_site_instance();

$content = $m->gen_site_html(array(
	"section"  => $section,
	"vars" => $si->on_page(),
	"text" => $te->parse(),
	"no_right_pane" => ($content) ? true : false,
	"sub_callbacks" => $si->get_sub_callbacks()
));

include("site_footer.".aw_ini_get("ext"));
?>
