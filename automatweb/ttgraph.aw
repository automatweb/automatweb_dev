<?php
//
//Version 0.3 by AQ
//
include("const.aw");
include("admin_header.$ext");
classload("tt");
//include("tt.aw");
//classload("aw_template");

$tt = new aw_template;
$tt->db_init();
if (!$tt->prog_acl("view", PRG_GRAPH))
{
	$tt->prog_acl_error("view", PRG_GRAPH);
}

$draw=1;
$stat_file="/www/struktuur/struktuur.ee/scripts/stats";
switch ($f) 
{
	case "demo":
		$arrayx = array("Jan.","Feb.","Mar.","Apr.","May","Jun.","Jul.");
		$arr=array(
					array(1000,120,500,1000,890)
					);

/*		 array(100,903,107,502,100,0,903,107,102,106,131,123,223,237,160,131,123,223,237,100) 
					array(1.2,0,3.4,4.6,7.8,1,34),
					array(32,45,56,67,78,89,90)
			   );
*/
		$myUnits = "Super-Tons";
		$Im = new LineGraph(2,30,1);

		//Apply options

		//Draw Base (Borders and Background)
		$Im->GraphBase(550,346,c0c000);

		//Draw Title
		$Im->title();
		$Im->parseData($arrayx,$arr);

		//Draw text on x axis
		$Im->xaxis($arrayx,"Aeg/Kuud");

		//Draw Grid
		$Im->grid(10,TRUE);
		$Im->yaxis(TRUE,EEK,"00009b");

		//Draw data lines
		$Im->makeLine($arr[0], "0000FF");
//		$Im->makeLine($arr[1], 255, 0, 0);
		//$Im->makeLine($arr[2], 255, 255, 0);

		//Output all as image
		$Im->draw();
		break;
	case "stat":
		$f = fopen($stat_file, "r");
		$i=0;$l=0;
		while (!feof ($f)) {
			$i++;
			if ($i<2)
			{
				$buffer = fgets($f, 4096);
				//echo $buffer."<BR><BR>";
			} 
			else if ($i==2)
			{
				$buffer = fgets($f, 4096);
			} else 
			{
				$buffer = fgets($f, 4096);
				$buffer = ereg_replace(" +", " ", $buffer );
				$temp=explode(" ",$buffer);
				$arr_date[$i-2]=$temp[0];
				$arr_rows[$i-2]=$temp[2];
				$arr_words[$i-2]=$temp[3];
				$arr_bytes[$i-2]=$temp[4];
			}
		}
		fclose($f);
		$arr_date=array_slice($arr_date,0,-1);
		$arr_rows=array_slice($arr_rows,0,-1);
		$arr_words=array_slice($arr_words,0,-1);
		$arr_bytes=array_slice($arr_bytes,0,-1);

		if ($draw) 
		{
		$Im = new LineGraph(2,40,1);

		//Draw Base (Borders and Background)
		$Im->GraphBase(350,186,DCDCDC);

		switch($t) {
			case "bytes": 
				$Im->title("Coding Stats: Bytes");
				$Im->parseData($arr_date,$arr_bytes);
				$Im->xaxis($arr_date,"Aeg/Päevad");
				$Im->grid(5,TRUE,"00009B");
				$Im->yaxis(TRUE,Bytes,"00009B");
				$Im->makeLine($arr_bytes, "0000FF");
				break;
			case "rows":
				$Im->title("Coding Stats: Rows");
				$Im->parseData($arr_date,$arr_rows);
				$Im->xaxis($arr_date,"Aeg/Päevad");
				$Im->grid(5,TRUE,"00009B");
				$Im->yaxis(TRUE,Rows,"00009B");
				$Im->makeLine($arr_rows,"0000FF");
				break;
			case "words":
				$Im->title("Coding Stats: Words");
				$Im->parseData($arr_date,$arr_words);
				$Im->xaxis($arr_date,"Aeg/Päevad");
				$Im->grid(5,TRUE,"00009B");
				$Im->yaxis(TRUE,Words,"00009B");
				$Im->makeLine($arr_words, "0000FF");
				break;
			default: 
				$Im->parseData($arr_date,$arr_bytes);
				$Im->xaxis($arr_date,"Aeg/Päevad");
				$Im->grid(5,TRUE,"00009B");
				$Im->yaxis(TRUE,Bytes,"00009B");
				$Im->makeLine($arr_bytes, "0000FF");
				break;

		}
		//Output all as image
		$Im->draw();
		} else 
		{
			for ($k=0;$k<count($arr_date);$k++) print($arr_date[$k]."<br>");
//			for ($k=0;$k<count($arr_bytes);$k++) print ($arr_bytes[$k]."<br>");	
//			for ($k=0;$k<count($arr_bytes);$k++) print ($arr_rows[$k]."<br>");	
		}
		break;
	case "sysl":
		$tpl = new aw_template;
		$q="SELECT syslog.when FROM syslog";
		$tpl->db_init();
		$tpl->db_query($q);
		$i=0;
		while($row = $tpl->db_next())
		{
			$tmp=explode(":",$tpl->time2date($row[when]));
			$arr_slog[$i++]=$tmp[0];
		}
		for ($k=0;$k<24;$k++) $temp[$k]=0;
		for ($i=0;$i<24;$i++)
			for ($k=0;$k<count($arr_slog);$k++) 
			{
				if ($i>9&&($i==$arr_slog[$k])) $temp[$i]++;
				else if ("0".$i==$arr_slog[$k]){
					$temp[$i]++;
				}
			}
		$i=0;$j=0;
		$arr_slog=$temp;
		for($i=0;$i<24;$i++)
		{
			$i>9?$array_x[$i]=(string)$i:$array_x[$i]=(string)"0".$i;
		}
//		for ($k=0;$k<count($arr_slog);$k++) print ($k.": ".$arr_slog[$k]."<br>");
//		for ($k=0;$k<count($array_x);$k++) print ($array_x[$k]."<br>");
		$Im = new LineGraph;
		//Apply options
		$Im->setFrameWidth(2);
		$Im->setInsideWidth(30);
		$Im->setBorderWidth(1);
		//Draw Base (Borders and Background)
		$Im->GraphBase(350,186,220,220,220);
		$Im->title("Logimiste arv sõltuvalt kellaajast");
		$Im->parseData($array_x,$arr_slog);
		$Im->xaxis($array_x,"Kellaaeg");
		$Im->grid(5,TRUE,0,0,155);
		$Im->yaxis(TRUE,"Arv",0,0,155);
		$Im->makeLine($arr_slog, 0, 0, 255);
		$Im->draw();
		break;
	default:

}

?>
