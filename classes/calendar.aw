<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/calendar.aw,v 2.14 2002/09/05 16:06:14 duke Exp $
// Generic calendar class

// php arvab by default, et p�hap�ev on 0.
// setlocalega saab seda muuta, aga ma pole kindel, et see funktsioon windoze veebiserverites t��tab
// niisiis workaround
define(CAL_SUNDAY,7);
// mis p�evast n�dal algab
define(CAL_WEEK_START,1);
define(ROLL_OVER,6 + CAL_WEEK_START);
define(DAY,86400);
classload("defs");

class calendar extends aw_template
{
	////
	// !Konstruktor
	function calendar()
	{
		$this->init("calendar");
		$this->lc_load("calendar","lc_calendar");
	}

	function draw_month($args = array())
	{
		$year	= ($args["year"]) ? $args["year"] : date("Y");
		$mon	= ($args["mon"]) ? $args["mon"] : date("m");
		$act_day = ($args["day"]) ? $args["day"] : date("d");
		$contents = $args["contents"];
		$tpl 	= ($args["tpl"]) ? $args["tpl"] : "plain.tpl";
		$misc   = (is_array($args["misc"])) ? $args["misc"] : array();
		$marked = (is_array($args["marked"])) ? $args["marked"] : array();
		$id = $args["id"];
		$type = $args["type"];
		$ctrl = $args["ctrl"];

		$add	= $args["add"]; // miski r�ga, mis linkidele otsa pannakse

		// mitu p�eva selles kuus on?
		$days_in_mon = $this->get_days_in_month($mon,$year);

		// mis n�dalap�evad on kuu esimene ja viimane p�ev?
		list($start_wday,$end_wday) = $this->get_weekdays_for_month($mon,$year);

		// template sisse
		$this->tpl_reset();
		$this->read_template($tpl);
		
		// blatant hack. 	
		// leiame selle kuu jaoks uue algus ja lopukuup�eva.
		// kui kuu algab teisip�evaga, siis k�ivitame p�eade loenduri 0-ist,
		// kui kolmap�evast, siis (-1)-st jne.
		$start =  2 - $start_wday;
		$end = $days_in_mon + (7 - $end_wday);

		// kalendrit joonistades kontrollime, kas k�siloleva p�eva indeks
		// on kehtivas vahemikus (0 < day <= days_in_month)
		// kui ei, siis joonistame t�hja ruudu

		// initsialiseerime n�dalap�evaloenduri
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

		// initsialieerime muutuja, mille sisse joonistame �he n�dala
		$line = "";

		$baselink = $misc + array("year" => $year,"mon" => $mon);

		
		for ($day = $start; $day <= $end; $day++)
		{
			// kui on lubatud vahemikus, siis joonistame p�eva,
			// muidu t�hiku
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
				if ($marked[sprintf("%02d",$day)])
				{
					$markup = "<b>%s</b>";
				}
				elseif ($marked[sprintf("%02d%02d%04d",$day,$mon,$year)])
				{
					$markup_style = "caldayevent";
				}
				else
				{
					$markup_style = "calday";
					$markup = "%s";
				};
			$this->vars(array(
				"nday" => sprintf($markup,$day),
				"daylink" => $this->mk_link($baselink + array("day" => sprintf("%02d",$day))),
				"dayorblink" => $this->mk_my_orb("view",array("id" => $id,"ctrl" => $ctrl,"type" => "day","date" => "$day-$mon-$year")),
				"day" => sprintf($markup,$day),
				"markup_style" => $markup_style,
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
				// n�dal l�ppes, lisame selle kalendrisse
				$this->vars(array(
					"cell" => $line,
					"weekorblink" => $this->mk_my_orb("view",array("id" => $id,"ctrl" => $ctrl,"type" => "week","date" => "$day-$mon-$year")),
				));
				$month .= $this->parse("week");
				$line = "";

				// ja saadame p�evaloenduri tagasi algusse
				$wday = CAL_WEEK_START;
			};
		}
		list($prevmon,$prevyear) = explode("|",date("m|Y",mktime(0,0,0,$mon-1,1,$year)));	
		list($nextmon,$nextyear) = explode("|",date("m|Y",mktime(0,0,0,$mon+1,1,$year)));	

		$mnames = array("boo","january","february","march","april","may","june","jule",
			"august","september","october","november","december");
		$mname = get_lc_month($mon);
		#$mname = $mnames[$mon];

		$cap = $mname . date(" Y",mktime(0,0,0,$mon,1,$year));

		if ($args["titlelink"])
		{
			$caption = sprintf("<a href='%s'>%s</a>",$args["titlelink"],$cap);
		}
		else
		{
			$caption = $cap;
		};

		$this->vars(array(
			"caption" => $caption,
			"prev" => $this->mk_link($misc + array("year" => $prevyear,"mon" => $prevmon)),
			"next" => $this->mk_link($misc + array("year" => $nextyear,"mon" => $nextmon)),
			"prevorb" => $this->mk_my_orb("view",array("type" => $type,"id" => $id,"ctrl" => $ctrl,"date" => "$act_day-$prevmon-$prevyear")),
			"nextorb" => $this->mk_my_orb("view",array("type" => $type,"id" => $id,"ctrl" => $ctrl, "date" => "$act_day-$nextmon-$nextyear")),
			"week" => $month,
			"prefix" => $prefix,
			"prevmon" => $prevmon,
			"prevyear" =>$prevyear,
			"nextmon" => $nextmon,
			"nextyear" => $nextyear,
			"mon"	=> $mon,
			"year"	=> $year,
			"add" => $add,
		));
		$retval = $this->parse();
		return $retval;
	}

	//// 
	// !tagastab mingi p�eva kohta selle n�dala algus ja l�pukuup�eva
	function get_week_range($args)
	{
		extract($args);
		if (!is_date($date))
		{
			// kui kuup�ev ei vasta reeglitele siis kasutame t�nast kuup�eva
			$date = date("d-m-Y");
		}

		// arvutusi on lihtsam timestambiga teha.
		list($day,$mon,$year) = explode("-",$date);
		$datestamp = mktime(0,0,0,$mon,$day,$year);
		
		$daycode = date("w",$datestamp); // 0 - p�hap�ev .. 6 - laup�ev

		// aga meil siin algab n�dal siiski esmasp�evast
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
	// !Joonistab kalendri �he n�dala kohta
	// argumendina v�tab kuup�eva
	function draw_week($args)
	{
		extract($args);
		list($date,$monday,$sunday) = $this->get_week_range(array("date" => $date));
		$uid = aw_global_get("uid");
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

		$daycode = date("w",$datestamp); // 0 - p�hap�ev .. 6 - laup�ev

		// aga meil siin algab n�dal siiski esmasp�evast
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
	// !tagastab array, #1 on 1. kuup�eva n�dalap�ev, #2 on viimase kuup�eva n�dalap�ev
	// (0 - p�hap�ev,.. 6 - laup�ev
	function get_weekdays_for_month($mon,$year)
	{
		$start_wday = date("w",mktime(0,0,0,$mon,1,$year));
		$end = $this->get_days_in_month($mon,$year);
		$end_wday = date("w",mktime(0,0,0,$mon,$end,$year));
		// kui on tegemist p�hap�evaga, siis anname neile uue v��rtuse, mis on alguses konstandina defineeritud
		$start_wday = ($start_wday == 0) ? CAL_SUNDAY : $start_wday;
		$end_wday = ($end_wday == 0) ? CAL_SUNDAY : $end_wday;
		return array($start_wday,$end_wday);
	}

	////
	// !tagastab p�evade arvu kuus
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
	// !get_date_range - for backwards compatibility
	function get_date_range($args = array())
	{
		classload("date_calc");
		return get_date_range($args);
	}	
	
	
};
?>
