<?php
// siin imporditakse muutujad saidi raami sisse
// ja v�ljastatakse see
$sf->read_template("popup.tpl");
$sf->vars(array("content" 	=> $content));
echo $sf->parse();
?>
