<?php
// wrapper. inkluudib saidi const.aw
// keemia on vajalik sest php resolvib symlingid 
// real pathiks enne faili parsimist
// kick ass security
$script_filename = ($SCRIPT_FILENAME) ? $SCRIPT_FILENAME : $_SERVER["SCRIPT_FILENAME"];
$site_dir = dirname($script_filename);
$site_dir = substr($site_dir,0,strrpos($site_dir,"/"));
include_once("$site_dir/const.aw");
parse_config("$site_dir/files/class_designer_cls.ini");
parse_config("$site_dir/files/class_designer_clsfld.ini");
?>
