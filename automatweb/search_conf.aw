<?php
include("const.aw");
include("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_SEARCH))
{
	$tt->prog_acl_error("view", PRG_SEARCH);
}

classload("search_conf");

$t = new search_conf;
$content = $t->gen_admin($level);

include("admin_footer.$ext");
?>
