<?php
include("formgen.aw");
$fg = new aw_formgen;
$fg->parse_xml_def("form.xml");
echo "loaded";
?>
