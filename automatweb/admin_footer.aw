<?php
// siin imporditakse muutujad saidi raami sisse
// ja väljastatakse see
$sf->read_template("index.tpl");
if (is_array($menu)) {
	$menustr = join(" | ",$menu);
} else {
	$menustr = "";
};
if (is_array($mainmenu)) {
	$site_title = join(" / ",$mainmenu);
} elseif ($title) {
	$site_title = $title;
};
// peatame koik taimerid ja kysime nende kohta info
$alltimers = $awt->summaries();

// siia paigutame koikide taimerite väärtused
$timers_arr = array(); 

// labelid erinevate taimerite jaoks
$tlabels = array("__global"  => "Kokku");

// tsykkel, mis taimerite info sobivale kujule vormistab
while(list($k,$v) = each($alltimers)) {
	$label = ($tlabels[$k]) ? $tlabels[$k] : $k;
	$timers_arr[] = "$label = $v" . "s";
};

classload("languages");
$t = new languages;

$vars = array(
			"content"			=> $content,
			"site_title"			=> $site_title,
		 	"time"				=> sprintf("%0.4f",$time_used),
			"menu"				=> $menustr,
			"custom_css"			=> $custom_css,
			"menubar"			=> $menubar,
			"jsinclude"  			=> $js_include,
			"qcount"				=> $qcount,
		 	"timers"				=> join(" | ",$timers_arr),
			"charset"				=> $t->get_charset(),
			"matches"				=> $preg_matches,
			"replaces"			=> $preg_replaces);
$vars = array_merge($vars,$info);
$sf->vars($vars);
echo $sf->parse();
?>
