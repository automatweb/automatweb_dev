<?php
// fetches a CSS file
// arguments:
// name(string) - faili nimi
include("const.aw");
if (strpos("/",$name) === false)
{
	$css = join("",file(AW_PATH . "/files/$name"));
	print $css;
};
?>
