<?php
include("const.aw");
include("admin_header.$ext");

classload("menuedit");

$t = new menuedit;
$docid  = $t->get_default_document($section);

classload("documents");
$d = new db_documents;

 $content = $d->show($docid,"undef","admin.tpl");


include("admin_footer.$ext");
?>
