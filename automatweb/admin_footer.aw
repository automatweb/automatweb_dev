<?php
// siin imporditakse muutujad saidi raami sisse
// ja väljastatakse see
$sf->read_template("index.tpl");
if (isset($menu) && is_array($menu)) 
{
	$menustr = join(" | ",$menu);
} 
else 
{
	$menustr = "";
};
if (isset($mainmenu) && is_array($mainmenu)) 
{
	$site_title = join(" / ",$mainmenu);
} 
else
if (isset($title) && $title) 
{
	$site_title = $title;
};
// peatame koik taimerid ja kysime nende kohta info
$alltimers = $awt->summaries();

// siia paigutame koikide taimerite väärtused
$timers_arr = array(); 

// labelid erinevate taimerite jaoks
$tlabels = array("__global"  => "Kokku");

// tsykkel, mis taimerite info sobivale kujule vormistab
while(list($k,$v) = each($alltimers)) 
{
	$label = isset($tlabels[$k]) && $tlabels[$k] ? $tlabels[$k] : $k;
	$timers_arr[] = "$label = $v" . "s";
};

classload("languages");
$t = new languages;

$vars = array(
			"content"			=> $content,
			"site_title"			=> (isset($site_title) ? $site_title : ""),
			"menu"				=> $menustr,
			"custom_css"			=> (isset($custom_css) ? $custom_css : ""),
			"menubar"			=> (isset($menubar) ? $menubar : ""),
			"jsinclude"  			=> (isset($js_include) ? $js_include : ""),
			"qcount"				=> $qcount,
		 	"timers"				=> join("\n",$timers_arr),
			"charset"				=> $t->get_charset());
$sf->vars($vars);
echo $sf->parse();

?>
