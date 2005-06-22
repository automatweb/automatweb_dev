<?php

class tvkavad extends aw_template
{
	function tvkavad()
	{
		$this->init("tvkavad");
		lc_load("definition");
	}

	function hetkel_eetris()
	{
		$t2na = mktime(0,0,0,date("m"),date("d"),date("Y"));
		$kell = time() - $t2na;
	
		$this->read_template("hetkel.tpl");

		$kanalid = array();
		$this->db_query("SELECT tv_kavad.*, tv_kanalid.name as name FROM tv_kavad LEFT JOIN tv_kanalid ON tv_kanalid.id = tv_kavad.kanal_id WHERE kuup = $t2na AND kell < $kell");
		while ($row = $this->db_next())
		{
			if ($row["kell"] > $kanalid[$row["name"]]["kell"])
			{
				$kanalid[$row["name"]] = $row;
			}
		}

		reset($kanalid);
		while (list(,$v) = each($kanalid))
		{
			$hr = (int)($v["kell"] / 3600);
			$min = ($v["kell"] - ($hr * 3600)) / 60;
			$this->vars(array(
				"kanal_id" => $v["kanal_id"], 
				"kanal" => $v["name"], 
				"kell" => sprintf("%02.0f:%02.0f",$hr, $min), 
				"pealkiri" => $v["pealkiri"], 
				"kirjeldus" => $v["comment"],
				"v2rv" => ($cnt & 1 ? LC_TVKAVAD_COLOR_WHITE : LC_TVKAVAD_COLOR_GREY)
			));
			$k.=$this->parse("KANAL");
			$cnt++;
		}
		$this->vars(array("date" => $this->time2date(time(),2), "KANAL" => $k));
		return $this->parse();
	}

	function kanal($kanal,$date = "")
	{
		if (!$date)
		{
			$date = mktime(0,0,0,date("m"),date("d"),date("Y"));
			$kell = time() - $date;
		}
	
		$t2na = $date == mktime(0,0,0,date("m"),date("d"),date("Y")) ? true : false;

		$kname = $this->db_fetch_field("SELECT name FROM tv_kanalid WHERE id = $kanal","name");

		$this->read_template("kanal.tpl");

		$on_id = 0; $nomore = false;
		$kava = array();
		$this->db_query("SELECT * FROM tv_kavad WHERE kanal_id = $kanal AND kuup = $date");
		while ($row = $this->db_next())
		{
			if (($t2na == true) && ($row["kell"] < $kell) && ($nomore == false))
			{
				$on_id = $row["id"];
			}
			else
			{
				$nomore = true;
			}
			$kava[] = $row;
		}

		reset($kava);
		while (list(,$row) = each($kava))
		{
			if ($on_id == $row["id"])
			{
				$this->vars(array("ON" => $this->parse("ON"), "NOT_ON" => ""));
			}
			else
			{
				$this->vars(array("ON" => $this->parse("NOT_ON"), "NOT_ON" => ""));
			}

			$hr = (int)($row["kell"] / 3600);
			$min = ($row["kell"] - ($hr * 3600)) / 60;
			$this->vars(array(
				"kell" => sprintf("%02.0f:%02.0f",$hr, $min), 
				"title" => $row["pealkiri"], 
				"comment" => $row["comment"],
				"v2rv" => ($cnt & 1 ? LC_TVKAVAD_COLOR_WHITE : LC_TVKAVAD_COLOR_GREY)
			));
			$k.=$this->parse("KANAL");
			$cnt++;
		}
		$this->vars(array(
			"date" => $this->time2date(time(),2), 
			"KANAL" => $k,
			"kanal" => $kname, 
			"kdate" => $this->time2date($date,3)
		));
		return $this->parse();
	}

	function otsing($kuupaev,$algus_k,$lopp_k,$s_string)
	{
		$kuupaev = $kuupaev+0;
		$algus_k = $algus_k+0;
		$lopp_k = $lopp_k+0;
		$this->quote(&$s_string);

		$os = array("kuup = $kuupaev");
		if ($algus_k != -1)
		{
			$os[]="kell >= $algus_k";
		}

		if ($lopp_k != -1)
		{
			$os[]="kell <= $lopp_k";
		}

		if ($s_string != "")
		{
			$os[] = "(pealkiri LIKE '%$s_string%' OR comment LIKE '%$s_string%')";
		}

		$this->read_template("search_res.tpl");

		$search = join(" AND ", $os);
		$this->db_query("SELECT tv_kavad.*, tv_kanalid.name as name FROM tv_kavad LEFT JOIN tv_kanalid ON tv_kanalid.id = tv_kavad.kanal_id WHERE $search ORDER BY kuup");
		while ($row = $this->db_next())
		{
			$hr = (int)($row["kell"] / 3600);
			$min = ($row["kell"] - ($hr * 3600)) / 60;
			$this->vars(array(
				"kanal_id" => $row["kanal_id"], 
				"kanal" => $row["name"], 
				"kell" => sprintf("%02.0f:%02.0f",$hr, $min), 
				"pealkiri" => $row["pealkiri"], 
				"v2rv" => ($cnt & 1 ? "Valge" : "Hall"),
				"kuup" => $kuupaev
			));
			$k.=$this->parse("KANAL");
			$cnt++;
		}
		$this->vars(array("date" => $this->time2date(time(),2), "KANAL" => $k));
		return $this->parse();
	}

	function kanalid_list($content)
	{
		$paevad = array("4" => "#telekava_neljapaev#", "5" => "#telekava_reede#", "6" => "#telekava_laupaev#", "0" => "#telekava_pyhapaev#", "1" => "#telekava_esmaspaev#", "2" => "#telekava_teisipaev#", "3" => "#telekava_kolmapaev#");
		reset($paevad);
                while (list($num, $v) = each($paevad))
                {
			if (strpos($content,$v) === false)
			{
				continue;
			}
			else
			{
				break;
			}
                }



                $wday = date("w");
                if ($num < $wday)
                {
                        $rdate = mktime(0,0,0,date("m"),date("d")+7,date("Y"));
                        $rdate = $rdate + (($num - $wday) * 86400);
                }
                else if ($num == $wday)
                {
                        $rdate = mktime(0,0,0,date("m"),date("d"),date("Y"));
                }
                else
                {
                        //print "num = $num<br>";
                        $rdate = mktime(0,0,0,date("m"),date("d"),date("Y"));
                        $rdate += ($num-$wday) * 86400;
                };



		// arvutame v2lja, et millal oli eelmine neljap2ev
		/*
		$sub_arr = array("0" => "3", "1" => "4", "2" => "5", "3" => "6", "4" => "0", "5" => "1", "6" => "2");
		$date = mktime(0,0,0,date("m"),date("d"),date("Y"));

		$d_begin = $date - $sub_arr[date("w")]*24*3600;
		$date = $d_begin+$num*24*3600;


		if ($date < time() && (date("w") > 4 || date("w") == 0))
		{
			$date += 24 * 3600 * 7;
		}	
		*/
		$date = $rdate;
		$this->read_template("kanalid_list.tpl");
		$this->db_query("SELECT * FROM tv_kanalid");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"kanal_id" => $row["id"], 
				"kanal" => $row["name"], 
				"kuup" => $date,
				"v2rv" => ($cnt & 1 ? LC_TVKAVAD_COLOR_WHITE : LC_TVKAVAD_COLOR_GREY)
			));
			$k.=$this->parse("KANAL");
			$cnt++;
		}
		$p2evad = array(
                        0 => "P&uuml;hap&auml;ev",
                        1 => "Esmasp&auml;ev",
                        2 => "Teisip&auml;ev",
                        3 => "Kolmap&auml;ev",
                        4 => "Neljap&auml;ev",
                        5 => "Reede",
                        6 => "Laup&auml;ev",
                );
	
		$this->vars(array(
			"date" => date("d.m.Y", $date), 
			"p2ev" => $p2evad[date("w", $date)],
			"KANAL" => $k
		));
		return $this->parse();
	}
}
?>
