<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/calendar.aw,v 2.3 2001/07/10 21:48:58 duke Exp $
// Generic calendar class

// php arvab by default, et pühapäev on 0.
// setlocalega saab seda muuta, aga ma pole kindel, et see funktsioon windoze veebiserverites töötab
// niisiis workaround
define(CAL_SUNDAY,7);
// mis päevast nädal algab
define(CAL_WEEK_START,1);
define(ROLL_OVER,6 + CAL_WEEK_START);

classload("defs");
global $orb_defs;
$orb_defs["calendar"] = "xml";

class calendar extends aw_template
{
	////
	// !Konstruktor
	function calendar()
	{
		$this->tpl_init("calendar");
	}

	function draw_month($args = array())
	{
		// millise kuu kohta kalendrit joonistame?
		$year	= ($args["year"]) ? $args["year"] : date("Y");
		$mon	= ($args["mon"]) ? $args["mon"] : date("m");
		$contents = $args["contents"];
		$tpl 	= ($args["tpl"]) ? $args["tpl"] : "plain.tpl";
		$misc   = (is_array($args["misc"])) ? $args["misc"] : array();
		$act_day = ($args["day"]) ? $args["day"] : date("d");


		$add	= $args["add"]; // miski räga, mis linkidele otsa pannakse

		// mitu päeva selles kuus on?
		$days_in_mon = $this->get_days_in_month($mon,$year);


		// mis nädalapäevad on kuu esimene ja viimane päev?
		list($start_wday,$end_wday) = $this->get_weekdays_for_month($mon,$year);

		// template sisse
		$this->tpl_reset();
		$this->read_template($tpl);
		
		// blatant hack. 	
		// leiame selle kuu jaoks uue algus ja lopukuupäeva.
		// kui kuu algab teisipäevaga, siis käivitame päeade loenduri 0-ist,
		// kui kolmapäevast, siis (-1)-st jne.
		$start =  2 - $start_wday;
		$end = $days_in_mon + (7 - $end_wday);

		// kalendrit joonistades kontrollime, kas käsiloleva päeva indeks
		// on kehtivas vahemikus (0 < day <= days_in_month)
		// kui ei, siis joonistame tühja ruudu

		// initsialiseerime nädalapäevaloenduri
		$wday = CAL_WEEK_START;

		// initsialieerime muutuja, kuhu sisse kalender tekib
		// koigepealt joonistame headeri
		$headers = array("E","T","K","N","R","L","P");
		$header = "";
		foreach($headers as $el)
		{
			$this->vars(array("head" => $el));
			$header .= $this->parse("week.header");
		};
		$this->vars(array("header" => $header));
		$month = $this->parse("week");
		$this->vars(array("header" => ""));

		// initsialieerime muutuja, mille sisse joonistame ühe nädala
		$line = "";

		$baselink = $misc + array("year" => $year,"mon" => $mon);
			
		for ($day = $start; $day <= $end; $day++)
		{
			// kui on lubatud vahemikus, siis joonistame päeva,
			// muidu tühiku
			if (is_array($contents) && $contents[$day])
			{
				$this->vars(array(
					"contents" => $contents[$day],
				));
			}
			else
			{
				$this->vars(array(
					"contents" => "&nbsp;",
				));
			};
			$this->vars(array(
				"nday" => $day,
				"daylink" => $this->mk_link($baselink + array("day" => $day)),
				"day" => $day,
			));
			$tpl = ($day == $act_day) ? "week.activecell" : "week.cell";
			if (($day <= 0) || ($day > $days_in_mon))
			{
				$tpl = "empty";
			};
			$line .= $this->parse($tpl);

			$wday++;
			if ($wday > ROLL_OVER)
			{
				// nädal lõppes, lisame selle kalendrisse
				$this->vars(array("cell" => $line));
				$month .= $this->parse("week");
				$line = "";

				// ja saadame päevaloenduri tagasi algusse
				$wday = CAL_WEEK_START;
			};
		}
		list($prevmon,$prevyear) = explode("|",date("n|Y",mktime(0,0,0,$mon-1,1,$year)));	
		list($nextmon,$nextyear) = explode("|",date("n|Y",mktime(0,0,0,$mon+1,1,$year)));	

		$mname = get_lc_month($mon);

		$this->vars(array(
			"caption" => $mname . date(" Y",mktime(0,0,0,$mon,1,$year)),
			"prev" => $this->mk_link($misc + array("year" => $prevyear,"mon" => $prevmon)),
			"next" => $this->mk_link($misc + array("year" => $nextyear,"mon" => $nextmon)),
			"week" => $month,
			"prefix" => $prefix,
			"prevmon" => $prevmon,
			"prevyear" =>$prevyear,
			"nextmon" => $nextmon,
			"nextyear" => $nextyear,
			"mon"	=> $mon,
			"year"	=> $year,
			"add" => $add));
		return $this->parse();
	}

	//// 
	// !tagastab mingi päeva kohta selle nädala algus ja lõpukuupäeva
	function get_week_range($args)
	{
		extract($args);
		if (!is_date($date))
		{
			// kui kuupäev ei vasta reeglitele siis kasutame tänast kuupäeva
			$date = date("d-m-Y");
		}

		// arvutusi on lihtsam timestambiga teha.
		list($day,$mon,$year) = explode("-",$date);
		$datestamp = mktime(0,0,0,$mon,$day,$year);
		
		$daycode = date("w",$datestamp); // 0 - pühapäev .. 6 - laupäev

		// aga meil siin algab nädal siiski esmaspäevast
		if ($daycode == 0)
		{
			$daycode = 7;
		};		
	
		$mday = $day - $daycode + 1;	
		$monday = mktime(0,0,0,$mon,$mday,$year);
		$sunday = mktime(23,59,59,$mon,$mday + 7,$year);
		return array($date,$monday,$sunday);	
	}

	////
	// !Joonistab kalendri ühe nädala kohta
	// argumendina võtab kuupäeva
	function draw_week($args)
	{
		extract($args);
		list($date,$monday,$sunday) = $this->get_week_range(array("date" => $date));
		global $uid;
		$q = "SELECT * FROM events WHERE uid = '$uid' AND start >= '$monday' AND start <= '$sunday' ORDER BY start";
		$this->db_query($q);
		$counts = array();
		while($row = $this->db_next())
		{
			$daycode = date($row["start"]);
			if ($counts[$daycode])
			{
				$counts[$daycode]++;
			}
			else
			{
				$counts[$daycode] = 0;
			};
		};
			
		$this->read_template("week.tpl");

		// arvutusi on lihtsam timestambiga teha.
		list($day,$mon,$year) = explode("-",$date);
		$datestamp = mktime(0,0,0,$mon,$day,$year);

		$daycode = date("w",$datestamp); // 0 - pühapäev .. 6 - laupäev

		// aga meil siin algab nädal siiski esmaspäevast
		if ($daycode == 0)
		{
			$daycode = 7;
		};		
		$mday = $day - $daycode + 1;	
		$week_starts = $mday;	
		$week_ends = date("d",$sunday);
		$c = "";
		$this->vars(array("date" => $date,
				"add" => $args["add"]));
		for ($i = $week_starts; $i <= ($week_starts+6);  $i++)
		{
			$today = mktime(0,0,0,$mon,$i,$year);
			$dx = date("w",$today);
			if ($dx == 0)
			{
				$dx = 7;
			};
			$this->vars(array(
				"weekday" => get_lc_weekday($dx),
				"day" => date("d",$today),
				"mon"	=> date("m",$today),
				"month" => get_lc_month(date("n",$today)),
			));
			if ($daycode == $dx)
			{
				$tpl = "active";
			} 		
			elseif (intval($i) == intval($args["day"]))
			{
				$tpl = "active";
			}
			else
			{
				$tpl = "line";
			};
			$c .= $this->parse($tpl);
		};
		$next = date("d-m-Y",mktime(0,0,0,$mon,$day+7,$year));
		$prev = date("d-m-Y",mktime(0,0,0,$mon,$day-7,$year));
		$this->vars(array(
			"line" => $c,
			"prev" => $prev,
			"next" => $next,
			"add" => $args["add"],
		));
		return $this->parse();

	}
		

	////
	// !tagastab array, #1 on 1. kuupäeva nädalapäev, #2 on viimase kuupäeva nädalapäev
	// (0 - pühapäev,.. 6 - laupäev
	function get_weekdays_for_month($mon,$year)
	{
		$start_wday = date("w",mktime(0,0,0,$mon,1,$year));
		$end = $this->get_days_in_month($mon,$year);
		$end_wday = date("w",mktime(0,0,0,$mon,$end,$year));
		// kui on tegemist pühapäevaga, siis anname neile uue väärtuse, mis on alguses konstandina defineeritud
		$start_wday = ($start_wday == 0) ? CAL_SUNDAY : $start_wday;
		$end_wday = ($end_wday == 0) ? CAL_SUNDAY : $end_wday;
		return array($start_wday,$end_wday);
	}

	////
	// !tagastab päevade arvu kuus
	function get_days_in_month($mon,$year)
	{
		return date("t",mktime(0,0,0,$mon,1,$year));
	}

	function draw_year($params = array())
	{
		$year = $params["year"];
		$xmon = $params["mon"];
		$day = $params["day"];
		$local = new aw_template();
		$local->tpl_init("calendar");
		$local->read_template("year.tpl");
		$lines = "";
		for ($i = 0; $i <= 2; $i++)
		{
			$line = "";
			for ($j = 1; $j <= 4; $j++)
			{
				$mon = 4 * $i + $j;
				$content = $this->draw_month(array(
					"year" => $year,
					"mon" => $mon,
					"tpl" => "plain2.tpl"));
				$local->vars(array("ycontent" => $content));
				$line .= $local->parse("ycell");
			};
			$local->vars(array("ycell" => $line));
			$lines .= $local->parse("yline");
			$line = "";
		};
		$local->vars(array("yline" => $lines,
					"year" => $year));
		return $local->parse();
	}
	
	////
	// !get_date_range
	function get_date_range($args = array())
	{
		extract($args);
		list($d,$m,$y) = split("-",$date);
		$timestamp = mktime(0,0,0,$m,$d,$y);
		switch($type)
		{
			case "month":
				$start_ts = mktime(0,0,0,$m,1,$y);
				$end_ts = mktime(23,59,59,$m+1,0,$y);
				// siin on next ja prev-i arvutamine monevorra special
				// kui päev on suurem, kui järgmises kuus päevi kokku
				// järgmise kuu viimase päeva. Sama kehtib eelmise kohta
				$next_mon = date("d",mktime(0,0,0,$m+2,0,$y));
				$prev_mon = date("d",mktime(0,0,0,$m,0,$y));
		
				if ($d > $next_mon)
				{
					$next = mktime(0,0,0,$m+1,$next_mon,$y);
				}
				else
				{
					$next = mktime(0,0,0,$m+1,$d,$y);
				};

				if ($d > $prev_mon)
				{
					$prev = mktime(0,0,0,$m-1,$prev_mon,$y);
				}
				else
				{
					$prev = mktime(0,0,0,$m-1,$d,$y);
				};
				break;
			
			
			case "week":
				$next = mktime(0,0,0,$m,$d+7,$y);
				$prev = mktime(0,0,0,$m,$d-7,$y);
				$daycode = $this->_convert_wday(date("w",$timestamp));
				// aga meil siin algab nädal siiski esmaspäevast
				$monday = $d - $daycode + 1;
				$start_ts = mktime(0,0,0,$m,$monday,$y);
				$end_ts = mktime(23,59,59,$m,$monday+6,$y);
				break;

			case "overview":
				$next = mktime(0,0,0,$m,$d+7,$y);
				$prev = mktime(0,0,0,$m,$d-7,$y);
				$start_ts = $timestamp;
				$end_ts = mktime(23,59,59,$m,$d+6,$y);
				break;
 
			case "day":
				$next = mktime(0,0,0,$m,$d+1,$y);
				$prev = mktime(0,0,0,$m,$d-1,$y);
				$start_ts = $timestamp;
				$end_ts = mktime(23,59,59,$m,$d,$y);
				break;
 
		};
		$arr = array(
			"start" => $start_ts,
			"end" => $end_ts,
			"start_wd" => $this->_convert_wday(date("w",$start_ts)),
			"end_wd" => $this->_convert_wday(date("w",$end_ts)),
			"wd" => $this->_convert_wday(date("w",$timestamp)),
			"prev" => date("d-m-Y",$prev),
			"next" => date("d-m-Y",$next),
			"timestamp" => $timestamp,
		);
		return $arr;
	}	
	
	function _convert_wday($daycode)
	{
		return ($daycode == 0) ? 7 : $daycode;
	}
};
?>
