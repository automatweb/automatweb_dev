<?php
include("const.aw");
include("admin_header.$ext");

classload("form_cell");


if ($action)
{
	$t = new form;
	$t->load($id);
	$t->arr[contents][$row][$col]->set_style($style,&$t);
	$t->save();
}

$t = new form();
$t->load($id);
$content = $t->arr[contents][$row][$col]->pickstyle();

include("admin_footer.$ext");
?>
