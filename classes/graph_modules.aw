<?
class graph_modules extends aw_template
{
		//Constructor
		function graph_modules()
		{
			$this->db_init();
			$this->tpl_init();
		}
		//Et saada datat syslogi tabelist ja n2idata aktiivsust saidil
		//Asi ei kipu töötama enam korralikult kuna andmehulk l2biparsimiseks on liiga suur
		function get_syslog()
		{
			$q="SELECT tm FROM syslog WHERE type='auth'";
			$this->db_query($q);
			$i=0;
			while($row = $this->db_next())
			{
				$tmp=explode(":",$this->time2date($row[when]));
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
			$jur=array("xdata"=>$array_x,"ydata"=>$arr_slog);
			return $jur;
		}
		//Et n2idata koodamise statsi
		function get_stats()
		{
			//Pane siin paika kus kurat see stati fail asub... ja tee seda 6ieti, eks
			$stat_file="/www/automatweb/public/scripts/stats";

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
			$data=array(
				"xdata" => $arr_date,
				"yrows" => $arr_rows,
				"ywords" => $arr_words,
				"ybytes" => $arr_bytes
				);
			return $data;
		}

		function get_10_stats()
		{
			$data=$this->get_stats();	
			$split=count($data[xdata])/10;
			$xdata=array();
			$y0=array();
			$y1=array();
			$y2=array();
			$a=0;
			for ($i=0;$i<count($data[xdata]);$i+=$split)
			{
				$xdata[$a]=$data[xdata][$i];
				$y0[$a]=$data[yrows][$i];
				$y1[$a]=$data[ywords][$i];
				$y2[$a]=$data[ybytes][$i];
				$a++;
/*				print($arr_date[$i]." ");
				echo $arr_rows[$i]." ";
				echo $arr_words[$i]." ";
				echo $arr_bytes[$i]."<br>";
*/			}
	

			$ydata=array(				
				"ydata_0" => $y0,
				"ydata_1" => $y1,
				"ydata_2" => $y2
				);
			$ycol=array(
				"ycol_0" => "ff0000",
				"ycol_1" => "asdf",
				"ycol_2" => "badbad",
			);
			$data=array(
				"xdata" => $xdata,
				"ydata" => $ydata,
				"ycol" => $ycol
				);
			
			return $data;
		}
		//Selleks, et kasutajalt datat saada
		function get_user_data($id)
		{
			$q="SELECT data FROM graphs WHERE id=$id";
			$this->db_query($q);
			$row = $this->db_next();
			$arr=unserialize($row[data]);
			$data=array("xdata" => explode(",",$arr[x]));

			for($i=0;$i<((count($arr)-1)/2);$i++)
			{
				$ydata["ydata_".$i]=explode(",",$arr["y".$i]);
				$ycol["ycol_".$i]=$arr["yc".$i];
			}
			$data["ydata"]=$ydata;
			$data["ycol"]=$ycol;
			return $data;
		}
}
?>