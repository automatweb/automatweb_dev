<?php
include("const.aw");
include("admin_header.$ext");

classload("documents");
$t = new db_documents;
$t->convbr();

?>