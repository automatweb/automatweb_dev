<?php
// Retrieves a list of temperatures for different cities in Estonia
// and outputs a csv file of the data we are interested in, that
// can then be loaded into a MySQL database with LOAD DATA infile
/* sample XML snippet
<blokk id="R">
	<linn1>Jõgeva</linn1><temp1>3.8</temp1><linn2>Pärnu</linn2><temp2>2</temp2><linn3>Rapla</linn3><temp3>2.4/temp3>
</blokk>
*/
$table = "ilmee_data";
$file = join("",file("http://www.ilm.ee/~data/include/ilm_xml.php3"));
$p = xml_parser_create();
xml_parse_into_struct($p,$file,$vals,$index);
xml_parser_free($p);
$tmps = array();
foreach($vals as $key => $val)
{
	if (preg_match("/^LINN(\d*)$/",$val["tag"],$m))
	{
		$tmps[$m[1]]["linn"] = $val["value"];
	};
	if (preg_match("/^TEMP(\d*)$/",$val["tag"],$m))
	{
		$tmps[$m[1]]["temp"] = $val["value"];
	};
};
if (sizeof($tmps) > 0)
{
	foreach($tmps as $block)
	{
		printf("%s;%s\n",$block["linn"],$block["temp"]);
	};
}
?>
