<?php
// Retrieves weather forecast information for next 4 days
// sample crontab line:
// * */4 * * * /www/automatweb_dev/scripts/php -q /www/automatweb_dev/addons/import/ilmee_xml2csv.php > /tmp/ilmee.txt 
$table = "ilmee_data";
# ttw needs forecast in english
$exp_file = "ilm-eng_xml.php3";
# but liiklus.ee in estonian.
# so, if an argumeo

if ($_SERVER["argv"][1] == "est")
{
	$exp_file = "ilm_xml.php3";
};

$file = join("",file("http://ilm.ee/~data/include/$exp_file"));
$p = xml_parser_create();
xml_parse_into_struct($p,$file,$vals,$index);
xml_parser_free($p);
$tmps = array();
$ids = array();
foreach($vals as $key => $val)
{
	if ( ($val["tag"] == "RUBRIIK") && ($val["attributes"]["NIMI"] == "EMHI") )
	{
		$open = true;
	};
	if ( ($val["tag"] == "BLOKK") && ($val["type"] == "open"))
	{
		$id = $val["attributes"]["ID"];
	};

	if ($val["tag"] == "DATE")
	{
		$date = $val["value"];
	};

	if ($val["tag"] == "OO")
	{
		$oo = $val["value"];
	};

	if ($val["tag"] == "PAEV")
	{
		$paev = $val["value"];
	};

	if ($val["tag"] == "NAHTUS")
	{
		$nahtus = $val["value"];
	};
	
	if ($val["tag"] == "JUTT")
	{
		$jutt = $val["value"];
	};

	if ( ($val["tag"] == "BLOKK") && ($val["type"] == "close") && $open)
	{
		print "$id|$date|$oo|$paev|$nahtus|$jutt\n";
	};

	if ( ($val["tag"] == "RUBRIIK") && ($val["type"] == "close") )
	{
		$open = false;
	};
		
}
?>
