<?php
// siin imporditakse muutujad saidi raami sisse
// ja väljastatakse see
$sf->read_template("index.tpl");

// tehakse kindlaks, milline custom_css, ja kas üldse laadida?
if (not($no_custom_css))
{
	$sf->vars(array("custom" => $uid));
	$custom = $sf->parse("custom_css");
	$sf->vars(array("custom_css" => $custom));
};

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
			"menubar"			=> (isset($menubar) ? $menubar : ""),
			"jsinclude"  			=> (isset($js_include) ? $js_include : ""),
			"qcount"				=> $qcount,
		 	"timers"				=> join("\n",$timers_arr),
			"charset"				=> $t->get_charset());
$sf->vars($vars);
echo $sf->parse();
if ($acl_server_socket)
{
	echo "closing socket <Br>\n";
	fclose($acl_server_socket);
}

?>
