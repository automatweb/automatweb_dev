<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/planner.aw,v 2.17 2001/05/31 16:19:14 duke Exp $
// planner.aw - päevaplaneerija
// CL_CAL_EVEN on kalendri event
classload("calendar","defs");
global $orb_defs;
$orb_defs["planner"] = "xml";
define(DAY,86400);
define(WEEK,DAY * 7);
define(REP_DAY,1);
define(REP_WEEK,2);
define(REP_MONTH,3);
define(REP_YEAR,4);
lc_load("calendar");
// Klassi sees me kujutame koiki kuupäevi kujul dd-mm-YYYY (ehk d-m-Y date format)

class planner extends calendar {
	function planner($args = array())
	{
		extract($args);
		$this->date = ($date) ? $date : date("d-m-Y");
		$this->tpl_init("planner");
		$this->db_init();
	}

	////
	// !Loob uue kalendriobjejkti
	function add($args = array())
	{
		$this->tpl_init("automatweb/planner");
		$this->read_template("add.tpl");
		$this->vars(array(
				"reforb" => $this->mk_reforb("submit_add",array(
									"parent" => $args["parent"],
									)),
			));
		return $this->parse();
	}

	////
	// !Submitib kalendriobjekti
	function submit_add($args = array())
	{
		$this->quote($args);
		extract($args);
		$id = $this->new_object(array(
			"class_id" => CL_CALENDAR,
			"parent" => $parent,
			"name" => $name,true));
		return $this->mk_orb("change",array("id" => $id));
	}


	function user_planner($args = array())
	{
		extract($args);
		$this->tpl_init("planner");
		$args["act"] = "user";
		$ids = "uid=" . UID;
		$args["ids"] = $ids;
		$args["ctype"] = "uid";
		if (!$date)
		{
			$date = date("d-m-Y");
		};
		// menüü def
		$args["date"] = $date;
		$menu = array(
			"today" => "?class=planner",
			"overview" => "?class=planner&action=overview&date=$date",
			"day" => "?class=planner&date=$date",
			"week" => "?class=planner&action=show_week&date=$date",
			"month" => "?class=planner&action=show_month&date=$date",
			"new" => "?class=planner&action=addevent&date=$date",
		);
		$args["menu"] = $menu;
		return $this->change($args);
	}

	function admin_planner($args = array())
	{
		extract($args);
		$this->tpl_init("automatweb/planner");
		$args["act"] = "change";
		$args["ids"] = "id=" . $args["id"];
		$args["ctype"] = "oid";
		$args["date"] = $date;
		if (!$date)
		{
			$date = date("d-m-Y");
		};
		// menüü def
		$menu = array(
			"today" => "?class=planner&action=$act&$ids",
			"overview" => "?class=planner&action=$act&disp=overview&$ids&date=$date",
			"day" => "?class=planner&action=$act&$ids&date=$date",
			"week" => "?class=planner&action=$act&disp=week&$ids&date=$date",
			"month" => "?class=planner&action=$act&disp=month&$ids&date=$date",
			"new" => "?class=planner&action=addevent&parent=$id&date=$date",
		);
		$args["menu"] = $menu;
		return $this->change($args);
	}
	
	////
	// !Kuvab kalendri muutmiseks (eelkoige adminnipoolel)
	// id - millist kalendrit näidata
	// disp - vaate tüüp
	// date - millisele kuupäevale keskenduda
	function change($args = array())
	{
		extract($args);
		$object = $this->get_object($id);

			
		list($d,$m,$y) = split("-",$date);
		
		// X marks the spot
		$xdate = $d . $m . $y;

		$this->mk_path($object["parent"],CAL_CH_TITLE);


		$titles = array(
			"week" => CAL_WEEK,
			"month" => CAL_MONTH,
			"overview" => CAL_OVERVIEW,
			"day" => CAL_DAY,
			"new" => "Lisa uus",
			"today" => "Täna",
		);

		if (!$disp)
		{
			$disp = "day";
		};

		$di = $this->get_date_range(array(
				"date" => $date,
				"type" => $disp,
		));
		
		$repeaters = $this->_get_event_repeaters(array(
				"id"	=> $id,
				"start" => date("d-m-Y",$di["start"]),
				"end" => date("d-m-Y",$di["end"])));

		$rlist = array();
	
		// see on fawking catch22 olukord.
		// Yhest kyljest oleks vaja lugeda repeaterid enne sisse, et me saaksime
		// planner tabelist lugeda info ka korduvate kirjete kohta.

		// teisest kyljest oleks vaja teada ka infot eventite kohta enne,
		// et me saaksime teada millise kalendri juurde need repeaterid käivad

		// lahendus? planner_repeater tabelisse panna samuti kirja selle
		// kalendri ID, mille juurde repeater kuulub.
		foreach($repeaters as $key => $val)
		{
			$rlist[] = $val["eid"];
		};
		if (sizeof($rlist) > 0)
		{
			$rinlist = " OR (planner.id IN ( " . join(",",$rlist) . " )) ";
		}
		else
		{
			$rinlist = "";
		};
		$rxlist = array_flip($rlist);
	

		$events = array();
		$events2 = array();
		if ($ctype == "oid")
		{
			$supp = "parent = '$id'";
		}
		else
		{
			$supp = "uid = '" . UID . "'";
		};
		$q = "SELECT * FROM planner
			LEFT JOIN objects ON (planner.id = objects.oid)
			WHERE $supp  AND objects.status = 2 AND ((start >= '$di[start]' AND start <= '$di[end]') $rinlist) 
			ORDER BY start";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			if (!isset($rxlist[$row["id"]]))
			{
				$events[date("dmY",$row["start"])][$row["start"]] = $row;
			};
			$events2["$row[id]"] = $row;
		};

		$ddiff1 = $this->get_day_diff($di["start"],$di["end"]);
		// tsükkel yle koigi selles perioodis asuvate päevade, et
		// leida ja paigutada events massiivi koik korduvad üritused
		for ($i = 0; $i <= $ddiff1; $i++)
		{
			// ja nüüd käime labi koik repeaterid, et teha kindlaks
			// kas antud ajahetkele (päevale) tuleks moni neist kuvada

			// siin peab kasutama while tsüklit, sest meil on vaja
			// array labimise ajal sealt elemente maha rippida.
			// ja nuh. ma tean, et see ei ole parim lahendus
			reset($repeaters);
			$more = true;
			$yearpwhen = "";
			while(list($key,$rep) = each($repeaters))
			{
				$rep["end"] = $events2[$rep["eid"]]["rep_until"];
				$sx = strtotime("+$i days",$rep["start"]);
				$ddiff2 = $this->get_day_diff($rep["start"],$di["start"]);
				$j = $i + $ddiff2;
				$sx = strtotime("+$j days",$rep["start"]);
				$s1 = date("dmY",$sx);
				$s2 = date("dmY",$rep["start"]);
				$ddiff = $this->get_day_diff($rep["start"],$sx);
				if ($s1 == $s2)
				{
					// do nothing
					// otherwise we would get 2 copies of that event for 
					// that day
				
				}
				elseif ($rep["end"] <= $sx)
				{
					// kui yritus on sellest perioodist expirenud
					// do also nothing
					//unset($repeaters[$key]);

				}
				elseif ($rep["start"] >= $sx)
				{
					// we are ignoring it
				}
				else
				// Lisa see eventite plaani
				{
					switch($rep["type"])
					{
						case REP_YEAR:
							if ($rep["skip"] > 0)
							{
								$startyear = date("Y",$rep["start"]);	
								$thisyear = date("Y",strtotime("+$i days",$di["start"]));
								if ( (($thisyear - $startyear) % $rep["skip"]) != 0)
								{
									// we are looking at a year we are not really interested in
									$more = false;
								};
								$yearpwhen = $rep["pwhen"];
							};
							break;

						case REP_MONTH:
							// jätkame töötlemist, if we have the required data
							if ($more && (($rep["skip"] > 0) || ($yearpwhen)) )
							{
								$startmon = date("m",$di["start"]);
								$thismon = (int)date("m",strtotime("+$i days",$di["start"]));
								if ($yearpwhen)
								{
									$yplist = explode(",",$yearpwhen);
									$yplist = array_flip($yplist);
									if (!isset($yplist[$thismon]))
									{
										$more = false;
									};
								}
								else
								{
									$mdiff = $this->get_mon_diff($di["start"],strtotime("+$i days",$i["start"]));
									if (($mdiff % $rep["skip"]) != 0)
									{
										$more = false;
									};
								};
									
							};
								

						case REP_DAY:
							if ($more && (($ddiff % $rep["skip"]) == 0))
							{
								$sd = date("dmY",strtotime("+$i days",$di["start"]));
								$events[$sd][$rep["start"]] = $events2[$rep["eid"]];
								//print "<pre>";
								//print_r($events2[$rep["eid"]]);
								//print "</pre>";
								// now, I do realize, that this is a bit 
								// ineffective, but until I can code this better
								// we are going to sort the list right here
								//ksort($events[$s1][$rep["start"]]);
								ksort($events[$sd]);
							};
							break;

						case REP_WEEK:
							//$wx = date("w",strtotime("+$i days",$di["start"]));
							//$sz = date("dmY",strtotime("+$i days",$di["start"]));
							//if ($wx == 0)
							//{
							//		$wx = 7;
							//};
							//$wxl = explode(",",$rep["pwhen"]);
							//$wxl = array_flip($wxl);
							// so we are supposed to do something with 
							// repeaters that have the type "week"

							// well. right now are only going to use pwhen
							//if (isset($wxl[$wx]))
							//{
							//		$events[$sz][$rep["start"]] = $events2[$rep["eid"]];
							//	ksort($events[$sz]);
							//};
							break;

					}; // switch
				};
			}
		};
		list($d,$m,$y) = split("-",$date);
		switch($disp)
		{
			case "week":
				$title = CAL_WEEK;
				list($d1,$m1,$y1) = split("-",date("d-m-Y",$di["start"]));
				list($d2,$m2,$y2) = split("-",date("d-m-Y",$di["end"]));
				$mon1 = get_lc_month($m1);
				$mon2 = get_lc_month($m2);
				$caption = sprintf("%d.%s %d - %d.%s %d",$d1,$mon1,$y1,$d2,$mon2,$y2);
				$this->read_template("sub_week.tpl");
				$c = "";
				$head = "";
				$cnt = "";
				$d1 = date("d",$di["start"]);
				for ($i = 0; $i <= 6; $i++)
				{
					$thisday = strtotime("+$i days",$di["start"]);
					$dx = date("dmY",$thisday);
					$d = date("d",$thisday);
					$c1 = "";
					if (is_array($events[$dx]))
					{
						foreach($events[$dx] as $key => $e)
						{
							$this->vars(array(
									"color" => $e["color"],
									"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
									 "title" => $e["title"],
									 "id" => $e["id"],
							));
							$c1 .= $this->parse("line.event");
						};
					};
					$bgcolor = ($xdate == $dx) ? "#DDDDDD" : "#FFFFFF";
					$this->vars(array("event" => $c1,
							"head" => get_lc_weekday($i+1),
							"did" => $id,
							"hid" => $args["id"],
							"disp" => "week",
							"date" => date("d-m-Y",$thisday),
							"dateinfo" => "$d. " . get_lc_month(date("m",$thisday)),
							"bgcolor" => $bgcolor));
					$c .= $this->parse("line");
					$head .= $this->parse("header");
				};
				$this->vars(array("line" => $c,
						"header" => $head));
				$content = $this->parse();
				$start = $date;
				break;
			
			case "month":
				$this->read_template("sub_mon.tpl");
				$title = CAL_MONTH;
				list($m1,$y1) = split("-",date("m-Y",$di["start"]));
				$caption = sprintf("%s %d",get_lc_month($m1),$y1);
				$start_d = date("d",$di["start"]);
				$end_d = date("d",$di["end"]);
				$start = (int)$start_d + 1 - $di["start_wd"];
				$end = (int)$end_d + (7 - $di["end_wd"]);
				$wx = 0;
				$c = "";
				$c1 = "";
				$c2 = "";
				$head = "";
				for ($i = 1; $i <= 7; $i++)
				{
					$this->vars(array("headline" => get_lc_weekday($i)));
					$head .= $this->parse("header");
				};
				for ($i = $start; $i <= $end; $i++)
				{
					$wx++;
					if (($i >= $start_d) && ($i <= $end_d))
					{
						$title = "";
						$j = $i - 1;
						$dx = date("dmY",strtotime("+$j days",$di["start"]));
						if (is_array($events[$dx]))
						{
							foreach($events[$dx] as $key => $e)
							{
								$this->vars(array(
										"color" => $e["color"],
										"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
										"title" => $e["title"],
										"id" => $e["id"],
								));
								$c2 .= $this->parse("line.subline.element");
							};
						};
						$this->vars(array("element" => $c2));
						$c2 = "";
						$bgcolor = ($dx == $xdate) ? "#DDDDDD" : "#FFFFFF";
						$thisday = date("d",strtotime("+$j days",$di["start"])) . "." . get_lc_month(date("m",$di["start"]));
						$this->vars(array(
								"dayname" => $thisday,
								"bgcolor" => $bgcolor,
								"did" => $args["id"],
								"date" => date("d-m-Y",strtotime("+$j days",$di["start"])),
						));
						$showday = $this->parse("showday");
						$this->vars(array("showday" => $showday));
						$c1 .= $this->parse("line.subline");
					}
					else
					{
						$this->vars(array(
								"element" => "&nbsp;",
								"dayname" => "",
								"showday" => "",
								"bgcolor" => "#ffffff"
						));
						$c1 .= $this->parse("line.subline");
					};

					if ($wx == 7)
					{
						$this->vars(array("subline" => $c1));
						$c1 = "";
						$c .= $this->parse("line");
						$wx = 0;
					};
				};
				$this->vars(array("line" => $c,
						"header" => $head));
				$content = $this->parse();
				break;

			case "overview":
				$title = CAL_OVERVIEW;
				list($d1,$m1,$y1) = split("-",date("d-m-Y",$di["start"]));
				list($d2,$m2,$y2) = split("-",date("d-m-Y",$di["end"]));
				$mon1 = get_lc_month($m1);
				$mon2 = get_lc_month($m2);
				$caption = sprintf("%d.%s %d - %d.%s %d",$d1,$mon1,$y1,$d2,$mon2,$y2);
				$this->read_template("sub_day.tpl");
				$c = "";
				$cnt = 0;
				$slice = date("dmY",$di["start"]);
				if (is_array($events[$slice]))
				{
				foreach($events[$slice] as $key => $e)
				{
					$cnt++;
					$this->vars(array(
							"color" => $e["color"],
							"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
							"id" => $e["id"],
							"title" => $e["title"],
							"contents" => nl2br($e["description"]),
				 	));
					$c .= $this->parse("line");
				};
				};
				$this->vars(array("line" => $c,
						"total" => $cnt));
				$content = $this->parse();
				$this->read_template("sub_week.tpl");
				$c = "";
				$head = "";
				$cnt = "";
				$d1 = date("d",$di["start"]);
				for ($i = 1; $i <= 7; $i++)
				{
					$thisday = strtotime("+$i days",$di["start"]);
					$dx = date("dmY",$thisday);
					$d = date("d",$thisday);
					$c1 = "";
					if (is_array($events[$dx]))
					{
					foreach($events[$dx] as $key => $e)
					{
						$this->vars(array(
								"color" => $e["color"],
								"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
								 "title" => $e["title"],
								 "id" => $e["id"],
						));
						$c1 .= $this->parse("line.event");
					};
					};
					$bgcolor = "#FFFFFF";
					$ri = $di["start_wd"] + $i;
					if ($ri > 7)
					{
						$ri = $ri - 7;
					};
					$this->vars(array("event" => $c1,
							"head" => get_lc_weekday($ri),
							"did" => $args["id"],
							"hid" => $args["id"],
							"date" => date("d-m-Y",$thisday),
							"disp" => "overview",
							"dateinfo" => "$d. " . get_lc_month(date("m",$thisday)),
							"bgcolor" => $bgcolor));
					$c .= $this->parse("line");
					$head .= $this->parse("header");
				};
				$this->vars(array("line" => $c,
						"header" => $head));
				$content .= $this->parse();
				$start = $date;
				break;

			case "day":
				$title = CAL_DAY;
				$start = $date;
				list($d1,$m1,$y1) = split("-",date("d-m-Y",$di["start"]));
				$caption = sprintf("%s, %d.%s %d",get_lc_weekday($di["wd"]),$d1,get_lc_month($m1),$y1);
				$this->read_template("sub_day.tpl");
				$c = "";
				$cnt = 0;
				$slice = date("dmY",$di["start"]);
				if (is_array($events[$slice]))
				{
					foreach($events[$slice] as $key => $e)
					{
						$cnt++;
						$this->vars(array(
								"color" => $e["color"],
								"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
								"id" => $e["id"],
								"title" => $e["title"],
								"contents" => nl2br($e["description"]),
				 		));
						$c .= $this->parse("line");
					};
				};
				$this->vars(array("line" => $c,
						"total" => $cnt));
				$content = $this->parse();
				break;
		};

		// joonistame menüü
		$menudef = "";
		foreach($menu as $key => $val)
		{
			if ($key == $disp)
			{
				$menudef .= $title . " &nbsp;&nbsp; ";
			}
			else
			{
				$menudef .= "<a href='$val'>$titles[$key]</a> &nbsp;&nbsp; ";
			};
		};
		$this->read_template("planner.tpl");
		$ylist = array(
			"2001" => "2001",
			"2002" => "2002",
			"2003" => "2003",
			"2004" => "2004",
			"2005" => "2005",
			"2006" => "2006",
			"2007" => "2007",
			"2008" => "2008",
			"2009" => "2009",
			"2010" => "2010",
		);
		$mlist = explode("|",LC_MONTH);
		unset($mlist[0]);
		if ($ctype == "oid")
		{
			$prev = "class=planner&action=change&id=$id&disp=$disp&date=$di[prev]";
			$next = "class=planner&action=change&id=$id&disp=$disp&date=$di[next]";
		}
		else
		{
			$prev = "class=planner&action=$disp&date=$di[prev]";
			$next = "class=planner&action=$disp&date=$di[next]";
		};
		$this->vars(array(
			"menudef" => $menudef,
			"caption" => $caption,
			"disp"	=> $disp,
			"id" => $id,
			"content" => $content,
			"mreforb" => $this->mk_reforb("redir",array("day" => $d,"disp" => $disp,"id" => $id,"ctype" => $ctype)),
			"mlist" => $this->picker($m,$mlist),
			"ylist" => $this->picker($y,$ylist),
			"prev" => $prev,
			"next" => $next));
		return $this->parse();
	}

	////
	// !tagastab eventid mingis ajavahemikus
	// argumendid:
	// start(timestamp), end(timestamp)
	// parent(int) - kalendri ID
	function get_events($args = array())
	{
		extract($args);
		$retval = array;
		$q = "SELECT * FROM planner
			LEFT JOIN objects ON (planner.id = objects.oid)
			WHERE objects.status = 2 AND objects.parent = '$parent' AND (start >= '$start' AND start <= '$end') ORDER BY start";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$retval[] = $row;
		};
		return (sizeof($retval) > 0) ? $retval : false;
	}
	


	function adm_event($args = array())
	{
		extract($args);
		if ($parent)
		{
			$this->tpl_init("automatweb/planner");
		}
		else
		{
			$this->tpl_init("planner");
		};
		$this->read_template("event.tpl");
		if ($op == "edit")
		{
			$par = $this->get_object($id);
			$parent = $par["parent"];
			$caption = "Muuda eventit";
			$q = "SELECT * FROM planner WHERE id = '$id'";
			$this->db_query($q);
			$row = $this->db_next();
			list($shour,$smin) = split("-",date("G-i",$row["start"]));
			$dsec = $row["end"] - $row["start"];
			$dhour = (int)($dsec / (60 * 60));
			$dsec = $dsec - ($dhour * 60 * 60);
			$delbut = $this->parse("delete");
			// nüüd on meil jargi sekundid, mis tulevad minutiteks teha
			$dmin = (int)($dsec / 60);
			$date = date("d-m-Y",$row["start"]);

		}
		else
		{
			$caption = "Lisa uus event";
			$shour = 9;
			$delbut = "";
			$dhour = 1;
		};
		$this->mk_path($parent,CAL_CH_TITLE);
		// nimekiri tundidest
		$h_list = range(0,23);
		// nimekiri minutitest
		$m_list = array("0" => "00", "15" => "15", "30" => "30", "45" => "45");
		list($d,$m,$y) = split("-",$date);
		$q = "SELECT * FROM planner_repeaters WHERE eid = '$id'";
		$this->db_query($q);
		$dayskip = $weekskip = $monskip = $yearskip = 0;
		$daypwhen = $weekpwhen = $monpwhen = $monpwhen2 = $yearpwhen = "";
	
		$rc = 0;
		while($rep = $this->db_next())
		{
			$rc++;
			switch($rep["type"])
			{
				case REP_DAY:
					$dayskip = $rep["skip"];
					$daypwhen = $rep["pwhen"];
					break;

				case REP_WEEK:
					$weekskip = $rep["skip"];
					$weekpwhen = $rep["pwhen"];
					break;

				case REP_MONTH:
					$monskip = $rep["skip"];
					$monpwhen = $rep["pwhen"];
					$monpwhen2 = $rep["pwhen2"];
					break;

				case REP_YEAR:
					$yearskip = $rep["skip"];
					$yearpwhen = $rep["pwhen"];
					break;
			};
		};
		$colors = array(
				"#000000" => "must",
				"#990000" => "punane",
				"#009900" => "roheline",
				"#000099" => "sinine",
			);
		$this->vars(array(
				"today" => "$d." . get_lc_month($m) . " $y",
				"shour" => $this->picker($shour,$h_list),
				"smin" => $this->picker($smin,$m_list),
				"dhour" => $this->picker($dhour,$h_list),
				"dmin" => $this->picker($dmin,$m_list),
				"title" => $row["title"],
				"wd" => $rep["pwhen"],
				"caption" => $caption,
				"rep_type" => $row["rep_type"],
				"rep_dur" => $row["rep_dur"],
				"rep_forever" => $row["rep_forever"],
				"rep_until" => $row["rep_until"],
				"repcheck" => ($rc > 0) ? "checked" : "",
				"color" => $this->picker($row["color"],$colors),
				"description" => $row["description"],
				"dayskip" => $dayskip,
				"daypwhen" => $daypwhen,
				"weekskip" => $weekskip,
				"weekpwhen" => $weekpwhen,
				"monskip" => $monskip,
				"monpwhen" => $monpwhen,
				"monpwhen2" => $monpwhen2,
				"yearskip" => $yearskip,
				"yearpwhen" => $yearpwhen,
				"reminder" => $row["reminder"],
				"delete" => $delbut,
				"private" => ($row["private"]) ? "checked" : "",
				"place" => $row["place"],
				"reforb" => $this->mk_reforb("submit_adm_event",array("parent" => $parent,
										"date" => $date,
										"id" => $id,
									)),
		));
		return $this->parse();
	}
	
	function repeaters($args = array())
	{
		$this->tpl_init("automatweb/planner");
		$this->read_template("repeaters.tpl");
		return $this->parse();
	}

	function submit_adm_event($args = array())
	{
		$this->quote($args);
		extract($args);
		// date on hidden field muutmis/lisamisvormist. Sisaldab eventi kuupäeva dd-mm-yyyy
		list($d,$m,$y) = split("-",$date);
		// sellest teeme timestampi
		$start = mktime($shour,$smin,0,$m,$d,$y);
		// lopu aeg
		$end = mktime($shour + $dhour,$smin + $dmin,59,$m,$d,$y);
		// uuendame olemasolevat
		global $status_msg;
		if ($id)
		{
			if ($delete)
			{
				$q = "UPDATE objects SET status = 1 WHERE oid = '$id'";
				$this->db_query($q);
				$status_msg = "Event on kustutatud";
			}
			else
			{
				$object = $this->get_object($id);
				$parent = $object["parent"];
				if (!$private)
				{
					$private = 0;
				};
				if ($rep_forever)
				{
					// well. as you can see not exactly forever. But hey.
					$rep_until = strtotime("+30 years",$start);
				}
				else
				{
					switch($rep_type)
					{
						case "1":
							$units = "days";
							break;

						case "2":
							$units = "weeks";
							break;

						case "3":
							$units = "months";
							break;

						case "4":
							$units = "years";
							break;
					};
					$rep_until = strtotime("+$rep_dur $units",$start);
				};
				if (!$rep_type)
				{
					$rep_type = 0;
				};

				if (!$rep_forever)
				{
					$rep_forever = 0;
				};

				if (!$rep_dur)
				{
					$rep_dur = 0;
				};

					
				$q = "UPDATE planner SET
					start = '$start',
					end = '$end',
					title = '$title',
					color = '$color',
					place = '$place',
					private = '$private',
					rep_type = '$rep_type',
					rep_dur = '$rep_dur',
					rep_forever = '$rep_forever',
					rep_until = '$rep_until',
					reminder = '$reminder',
					description = '$description'
				WHERE id = '$id'";
				$status_msg = "Eventi muudatused on salvestatud";
			};
		}
		// lisame uue
		else
		{
			$uid = "";
			if (!$parent)
			{
				global $udata;
				$parent = $udata["homefolder"];
				$uid = UID;
			};
			$id = $this->new_object(array(	
				"class_id" => CL_CAL_EVENT,
				"parent" => $parent,
				"name" => $title,
			),true);

			$q = "INSERT INTO planner 
				(id,uid,start,end,title,place,private,reminder,description,rep_type,rep_dur,rep_forever,rep_until)
				VALUES ('$id','$uid','$start','$end','$title','$place','$private','$reminder','$description','$rep_type','$rep_dur','$rep_forever','$rep_until')";
			$status_msg = "Event on lisatud";
		};
		$this->db_query($q);
		if ($repeater && (!$delete))
		{
			// vanad minema
			$q = "DELETE FROM planner_repeaters WHERE eid = '$id'";
			$this->db_query($q);
			if (isset($dayskip))
			{
				$reptype = REP_DAY;
				$q = "INSERT INTO planner_repeaters
					(eid,cid,type,start,end,skip,pwhen)
					VALUES
					('$id','$parent','$reptype','$start','$end','$dayskip','$daypwhen')";
				$this->db_query($q);
			};
			
			if (isset($weekskip))
			{
				$reptype = REP_WEEK;
				$q = "INSERT INTO planner_repeaters
					(eid,cid,type,start,end,skip,pwhen)
					VALUES
					('$id','$parent','$reptype','$start','$end','$weekskip','$weekpwhen')";
				$this->db_query($q);
			};
			
			if (isset($monskip))
			{
				$reptype = REP_MONTH;
				$q = "INSERT INTO planner_repeaters
					(eid,cid,type,start,end,skip,pwhen,pwhen2)
					VALUES
					('$id','$parent','$reptype','$start','$end','$monskip','$monpwhen','$monpwhen2')";
				$this->db_query($q);
			};
			
			if (isset($monskip))
			{
				$reptype = REP_YEAR;
				$q = "INSERT INTO planner_repeaters
					(eid,cid,type,start,end,skip,pwhen)
					VALUES
					('$id','$parent','$reptype','$start','$end','$yearskip','$yearpwhen')";
				$this->db_query($q);
			};

				
		}
		else
		{
			// ripime kogu info korduvate yrituste kohta maha
			$q = "DELETE FROM planner_repeaters WHERE eid = '$id'";
			$this->db_query($q);
		};
				

		session_register("status_msg");
		// suunab tagasi default lehele, ehk tänasele päevale
		if ($parent)
		{
			return $this->mk_my_orb("change",array("id" => $parent,"date" => $date));
		}
		else
		{
			return $this->mk_my_orb("day",array("date" => $date));
		};
	}

	////
	// !Lisab eventi
	// argumendid:
	// parent(int) - kalendri ID
	// title(string)
	// start(timestamp)
	// end(timestamp)
	// place(string)
	// description(string)
	function add_event($args = array())
	{
		$this->quote($args);
		extract($args);
		$id = $this->new_object(array(	
			"class_id" => CL_CAL_EVENT,
			"parent" => $parent,
			"name" => $title,
		),true);

		$q = "INSERT INTO planner 
			(id,uid,start,end,title,place,description)
			VALUES ('$id','$uid','$start','$end','$title','$place','$description')";
		$this->db_query($q);
	}


	function redir($args = array())
	{
		extract($args);
		$max_day = date("d",mktime(0,0,0,$month,$day,$year));
		if ($day > $max_day)
		{
			$day = $max_day;
		};
		$date = "$day-$month-$year";
		$parms = array();
		$parms["date"] = $date;
		if ($ctype == "oid")
		{
			$params["id"] = $id;
			$params["disp"] = $disp;
			$action = "change";
		}
		else
		{
			$action = $disp;
		};
		$retval = $this->mk_orb($action,$params);
		return $retval;

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

	////
	// Takes 2 timestamps and calculates the difference between them in days
	//	args: time1, time2
	function get_day_diff($time1,$time2)
	{
		$diff = $time2 - $time1;
		$days = (int)($diff / DAY);
		return $days;
	}

	////
	// Takes 2 timestamps and calculates the difference between them in months
	function get_mon_diff($time1,$time2)
	{
		$date1 = date("d-m-Y",$time1);
		$date2 = date("d-m-Y",$time2);
		$d1 = explode('-', $date1);
		$d2 = explode('-', $date2);
		$diff = ($d2[2] * 12 + $d2[1]) - ($d1[2] * 12 + $d1[1]) - 1;
		return $diff;
	}


	function _convert_wday($daycode)
	{
		return ($daycode == 0) ? 7 : $daycode;
	}

	////
	// !Listib koik eventid mingis vahemikus
	// date - dd-mm-yyyy
	// delta - string, määratleb perioodi lopu
	// clsid - klassifikaator (eventeid saab jagada kategooriatesse) (optional)
	function _get_events_in_range($args = array())
	{
		extract($args);
		$date = ($date) ? $date : $this->date;

		list($d1,$m1,$y1) = split("-",$date);
		$start = mktime(0,0,0,$m1,$d1,$y1);

		if ($oid)
		{
			$where = " oid = '$oid'";
		}
		else
		{
			global $uid;
			$where = " uid = '$uid'";
		};
	
		if (!$delta)
		{
			$delta = "now";
		};

		$end = strtotime($delta,$start) + 86399; //23h59m59s
		$q = "SELECT * FROM planner
			WHERE $where AND start >= '$start' AND start <= '$end'
			ORDER BY start";
		$this->db_query($q);
	}

	function _get_event_repeaters($args = array())
	{
		extract($args);
		list($d1,$m1,$y1) = split("-",$start);
		list($d2,$m2,$y2) = split("-",$end);
		$start = mktime(0,0,0,$m1,$d1,$y1);
		$end = mktime(23,59,59,$m2,$d2,$y2);
		// I am sure this could be optimized to read only
		// those repeaters that do fall into our frame, but it would
		// be a monster SQL clause and at this moment I do not
		// think I would be able to do this.
		$q = "SELECT * FROM planner_repeaters
			WHERE cid = '$id' ORDER BY eid,type DESC";
		$this->db_query($q);
		$res = array();
		while($row = $this->db_next())
		{
			$res[$row["id"]] = $row;
		};
		return $res;
	}

	////
	// !Joonistab kuu kalendri
	function draw_mon($args = array())
	{
		extract($args);
		$navigator = $this->draw_navigator(array(
			"active" => "month",
		));
		list($year,$mon) = split("-",date("Y-m"));
		$start = mktime(0,0,0,$mon,1,$year);
		$end = mktime(23,59,59,$mon+1,0,$year);
		$events = $this->_get_events_in_range(array(
			"start" => $start,
			"end" => $end,
			"raw" => 1,
		));
		$contents = array();
		$this->read_template("month.tpl");
		while($row = $this->db_next())
		{
			$this->vars(array(
				"start" => date("H:i",$row["start"]),
				"end" => date("H:i",$row["end"]),
				"id" => $row["id"],
				"title" => $row["title"],
			));
			$contents[date("j",$row["start"])] .= $this->parse("event");
		};
		$content = $this->draw_month(array(
			"year" => $year,
			"mon" => $mon,
			"contents" => $contents,
		));
		$this->read_template("month.tpl");
		$this->vars(array(
			"navigator" => $navigator,
			"content" => $content,
		));
		return $this->parse();
	}

	//// 
	// !Joonistab ühe päeva kalendri
	function draw_day($args = array())
	{
		extract($args);
		global $uid;
		// first. We need to get all events that start on $date
		// this should give me todays events
		$date = ($date) ? $date : date("d-m-Y");
		list($d1,$m1,$y1) = split("-",$date);
		$today = mktime(0,0,0,$m1,$d1,$y1);
		$wday = get_lc_weekday(date("w",$today));
		$todaystr = sprintf("%s, %d.%s %d",$wday,$d1,get_lc_month($m1),$y1);

		$this->_get_events_in_range(array());

		$events = array();
		$event_starts = array();

		// Sorteerime üritused ära ajaühikute kaupa
		while($row = $this->db_next())
		{
			// G = hour without leading zeroes
			// i = minutid
			// arvutame yrituse kestvuse tundides

			// kestvusest arvestame yhe sekundi maha, muidu spanniks
			// kell 9.00-11.00 kestev yritus yle 3 lahtri (9,10,11)
			$row["duration"] = (int)(($row["end"] - $row["start"] -1) / 3600); 
			$event_starts[date("G",$row["start"])][] = $row["id"];
			$events[$row["id"]] = $row;

		};

		$navigator = $this->draw_navigator(array(
			"active" => "day",
		));
		$this->read_template("day.tpl");
		$c = "";

		$in_cell = 0;
		$start_cell = 0;


		$cells = array();
		$cell_contents = array();


		// leiame info, mis on vajalik päevaplaani joonistamiseks
		for ($d = 0; $d <= 23; $d++)
		{
			if ($in_cell > 0)
			{
				$in_cell--;
			}
			elseif ($in_cell == 0)
			{
				$cells[$d] = "0";
			};
			// kui selles ajaühikus algab mõni event, siis...
			if (is_array($event_starts[$d]))
			{
				if ($in_cell == 0)
				{
					$start_cell = $d;
				};
				// kaime yle koigi siin algavate eventite
				reset($event_starts[$d]);
				while(list(,$_event_id) = each($event_starts[$d]))
				{
					$_event = $events[$_event_id];
					if ($_event["duration"] > $in_cell)
					{
						$cells[$start_cell] += ($_event["duration"] - $in_cell);
						$in_cell = $_event["duration"];
					};
					$this->vars(array(
						"start" => date("H:i",$_event["start"]),
						"end" => date("H:i",$_event["end"]),
						"id" => $_event["id"],
						"title" => $_event["title"],
					));
					$cell_contents[$start_cell] .= $this->parse("event");
				};
			};
		};


		// Ja nüüd joonistame tabeli enda
		// tsükkel üle kõikide ajaühikute, ehk tundide selles kontekstis
		$in_cell = 0;
		for ($d = 0; $d <= 23; $d++)
		{
			if ($in_cell > 0)
			{
				$in_cell--;
			};
			if ($in_cell > 0)
			{
				$data = "";
			};
			// kontrollime, kas selles ajaühikus on mingit sisu
			if ($cells[$d] > 0)
			{
				$in_cell = $cells[$d];
				$this->vars(array(
					"rowspan" => $cells[$d],
					"content" => $cell_contents[$d]
				));
				$data = $this->parse("data");
			}
			elseif ($in_cell > 0)
			{
				$data = "";
			}
			else
			{
				$this->vars(array(
					"rowspan" => 1,
					"content" => "&nbsp;",
				));
				$data = $this->parse("data");
			};
			
			$this->vars(array(
				"time" => sprintf("%02d:00",$d),
				"timeslice" => $d,
				"date" => $date,
				"data" => $data,
			));
			$c .= $this->parse("line");
		};
		list($d,$m,$y) = split("-",$date);
		$smon = get_lc_month($m);
		$next = 		$this->vars(array(
			"line" => $c,
			"prev" => date("d-m-Y",mktime(0,0,0,$m,$d-1,$y)),
			"next" => date("d-m-Y",mktime(0,0,0,$m,$d+1,$y)),
			"today" => $todaystr,
			"navigator" => $navigator,
			"thisdate" => $date,
		));
		return $this->parse();
	}

	//// joonistab navigaatori
	//
	function draw_navigator($args = array())
	{
		global $baseurl;
		$navs = array(
			"today" => array(
				"link" => "$baseurl/?class=planner",
				"caption" => " Täna ",
			),
			"overview" => array(
				"link" => "$baseurl/?class=planner&action=overview",
				"caption" => " Ülevaade ",
			),
			"day" => array(
				"link" => "$baseurl/?class=planner",
				"caption" => " Päev ",
			),
			"week" => array(
				"link" => "$baseurl/?class=planner&action=show_week",
				"caption" => " Nädal ",
			),
			"month" => array(
				"link" => "$baseurl/?class=planner&action=show_month",
				"caption" => " Kuu ",
			),
			"add" => array(
				"link" => "$baseurl/?class=planner&action=add_event",
				"caption" => " Lisa uus ",
			),
		);
		load_vcl("smenu");
		$this->tpl_init("planner");
		$this->read_template("navigator.tpl");
		$smenu = new smenu(array(
			"tpl_act" => $this->templates["active_menu"],
			"tpl_deact" => $this->templates["deactive_menu"],
		));
		reset($navs);
		while(list($nav_id,$contents) = each($navs))
		{
			$contents["active"] = ($nav_id == $args["active"]);
			$smenu->add_menu($contents);
		};
		$this->vars(array("menu" => $smenu->get_menu()));
		return $this->parse();
	}


	////
	// draw_week (joonistab nädala vaate)
	function draw_week($args = array())
	{
		extract($args);
		$date = ($args["date"]) ? $args["date"] : date("d-m-Y");
		if ($op == "overview")
		{
			list($d,$m,$y) = split("-",$date);
			$start = $date;
			$start = mktime(0,0,0,$m,$d,$y);
	                $end = date("d-m-Y",mktime(0,0,0,$m,$d + 7,$y));
		}
		else
		{
			list($date,$start,$end) = $this->get_week_range(array(
				"date" => $date,
				));
			$next = date("d-m-Y",strtotime("+1 week",$start));
			$prev = date("d-m-Y",strtotime("-1 week",$start));
		};
		$this->_get_events_in_range(array(
			"start" => $start,
			"delta" => "+1 week",
		));

		$contents = array();
		if ($user)
		{
                	global $uid;
			if ($op == "overview")
			{
				$navigator = $this->draw_navigator(array(
					"active" => "overview",
				));
			}
			else
			{
				$navigator = $this->draw_navigator(array(
					"active" => "week",
				));
			};
			$tpl = "week_big.tpl";
			$this->read_template($tpl);
			while($row = $this->db_next())
			{
				$this->vars(array(
					"start" => date("H:i",$row["start"]),
					"end" => date("H:i",$row["end"]),
					"id" => $row["id"],
					"title" => $row["title"],
				));
				$contents[date("d",$row["start"])] .= $this->parse("event");
			};
				
		}
		else
		{
                	$counts = array();
			$tpl = "week.tpl";
			$this->read_template($tpl);
			while($row = $this->db_next())
			{
				$daycode = date("d",$row["start"]);
				$counts[$daycode]++;
			};
		};
		
		$c = "";
		
		$today = date("d-m-Y");

		// Joonistame nädala
		for ($i = 0; $i <= 6; $i++)
		{
			$current = $start + ($i * 60 * 60 * 24);
			$current_long = date("d-m-Y",$current);
			if ($today == $current_long)
			{
				if ($short)
				{
					$bgcolor = "#669966";
				}
				else
				{
					$bgcolor = "#eeeeee";
				};
			}
			else
			{
				$bgcolor = "";
			};
			if ($current_long == $date)
			{
				$sufix = "<b>&lt;&lt;</b>";
			}
			else
			{
				$sufix = "";
			};
			$this->vars(array(
				"day" => date("d-m",$current) . $sufix,
				"bgcolor" => $bgcolor,
				"day2" => date("d",$current) . "." . get_lc_month(date("m",$current)),
				"wday" => get_lc_weekday($i+1),
				"sufix" => $sufix,
				"date" => date("d-m-Y",$current),
				"contents" => $contents[date("d",$current)],
				"events" => $counts[date("d",$current)],
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
			"prev" => $prev,
			"next" => $next,
			"navigator" => $navigator,
		));
		$content = $this->parse();
		return $content;
	}

	////
	// !Tagastab timestambi mingi kuupäevastambi kohta
	// $date - d-m-Y
	function tm_convert($date)
	{
		extract($args);
		list($d,$m,$y) = split("-",$args["date"]);
		// miski modification voiks ka olla
		$retval = mktime(0,0,0,$m,$d,$y);
		return $retval;
	}


	////
	// !Näitab infot mingi eventi kohta
	function show_event($args = array())
	{
		extract($args);
		global $uid;
		// $q = "SELECT * FROM planner WHERE id = '$id' AND uid = '$uid'";
		// $q = "SELECT * FROM msg_objects WHERE id = '$id'";
		// $this->db_query($q);
		// $row = $this->db_next();
		//$_x = unserialize($row["content"]);
		//$row = unserialize($_x["str"]);
		$row = $args;
		$this->read_template("show.tpl");
		$this->vars(array(
			"title" => $row["title"],
			"id" => $row["att_id"],
			"description" => $row["description"],
			"start" => date("d-M H:i",$row["start"]),
			"end" => date("d-M H:i",$row["end"]),
		));
		return $this->parse();
	}
		

	////
	// !edit_event - vorm uue sündmuse lisamiseks/olemasoleva muutmiseks 
	// date - sisaldab eventi kuupäeva (kasutatakse ainult uue lisamisel
	// time - sisaldab ajaühikut. ilmselt tund siis (uue lisamisel)
	function edit_event($args = array())
	{
		extract($args);
		global $uid;
		$this->read_template("event.tpl");
		// nimekiri tundidest
		$h_list = range(0,23);
		// nimekiri minutitest
		$m_list = array("0" => "00", "15" => "15", "30" => "30", "45" => "45");
		if ($id)
		{
			$q = "SELECT * FROM planner WHERE id = '$id' AND uid = '$uid'";
			$this->db_query($q);
			$row = $this->db_next($q);
			if (!$row)
			{
				$this->raise_error("You can't do that",true);
			};
			list($shour,$smin) = split("-",date("G-i",$row["start"]));
			$dsec = $row["end"] - $row["start"];
			$dhour = (int)($dsec / (60 * 60));
			$dsec = $dsec - ($dhour * 60 * 60);
			// nüüd on meil jargi sekundid, mis tulevad minutiteks teha
			$dmin = (int)($dsec / 60);
			$date = date("d-m-Y",$row["start"]);
		}
		else
		{
			// need on siis need muutujad, mida kasutame by default uue aja kuvamisel
			// time tuli url-ist
			$shour = $time;
			// minutid 0
			$smin = 0;
			// kestvus 1 tund
			$dhour = 1;
			// ja 0 minutit
			$dmin = 0;
			// --
			$row = array();
			$duration = 1;
			list($d,$m,$y) = split("-",$date);
			$sdate = mktime(0,0,0,$m,$d,$y);
			list($hr,$day) = split("-",date("G-d",$sdate));
			$hr = $time;
			// we need to get the next event as well, to avoid overlapping times
			// ja nu. ega ma ei oska seda teisiti teha, kui kysida kui selle päeva
			// eventid
			list($h,$y,$m,$d) = split("-",date("H-Y-m-d",$sdate));
		};
		list($d,$m,$y) = split("-",$date);
		$this->vars(array(
			"shour" => $this->picker($shour,$h_list),
			"smin" => $this->picker($smin,$m_list),
			"dhour" => $this->picker($dhour,$h_list),
			"dmin" => $this->picker($dmin,$m_list),
			"title" => $row["title"],
			"today" => $date,
			"description" => $row["description"],
			"hours" => $this->picker($duration,$durs),
			"reforb" => $this->mk_reforb("submit_event",array(
							"time" => $time,
							"date" => $date,
							"id" => $id,
							)),
					
		));
		return $this->parse();
	}

	////
	// !Lisab uue eventi / salvestab olemasoleva. Seda siis sõltuvalt id argumendi olemasolust
	// date - eventi kuupäev dd-mm-yyyy
	// shour - alguse tund (s = start)
	// smin - alguse minut
	// dhour - kestvus tundides (d = duration)
	// dmin - kestvus minutites
	function submit_event($args = array())
	{
		$this->quote($args);
		extract($args);
		// suckaz
		global $uid;
		global $udata;
		// date on hidden field muutmis/lisamisvormist. Sisaldab eventi kuupäeva dd-mm-yyyy
		list($d,$m,$y) = split("-",$date);
		// sellest teeme timestampi
		$start = mktime($shour,$smin,0,$m,$d,$y);
		// lopu aeg
		$end = mktime($shour + $dhour,$smin + $dmin,59,$m,$d,$y);
		// uuendame olemasolevat
		if ($id)
		{
			$q = "UPDATE planner SET
				start = '$start',
				end = '$end',
				title = '$title',
				description = '$description'
			WHERE id = '$id'";
		}
		// lisame uue
		else
		{
			$id = $this->new_object(array(	
				"class_id" => CL_CAL_EVENT,
				"parent" => $udata["home_folder"],
				"name" => '$title',
			),true);

			$q = "INSERT INTO planner 
				(id,start,end,uid,title,description)
				VALUES ('$id','$start','$end','$uid','$title','$description')";
		};
		$this->db_query($q);
		// suunab tagasi default lehele, ehk tänasele päevale
		return $this->mk_site_orb(array("date" => $date));
	}

	////
	// !Impordib serializetud objekti
	function import($args = array())
	{
		extract($args);
		global $udata;
		$q = "SELECT * FROM msg_objects WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$data = unserialize($row["content"]);
		extract($data);
		$uid = UID;
		$id = $this->new_object(array(
			"class_id" => CL_CAL_EVENT,
			"parent" => $udata["home_folder"],
			"name" => "$title",
			),true);
		$q = "INSERT INTO planner
			(id,start,end,uid,title,description)
			VALUES('$id','$start','$end','$uid','$title','$description')";
		$this->db_query($q);
		global $status_msg;
		$date = date("d-m-Y",$start);
		$status_msg = "Sündmus on lisatud";
		session_register("status_msg");
		$retval = $this->mk_site_orb(array(
			"class" => "planner",
			"action" => "dummy",
			"date" => $date,
		));
		return $retval;
	}	

	function dummy($args = array())
	{
		$this->read_template("finish_import.tpl");
		extract($args);
		$this->vars(array(
			"date" => $date,
		));
		print $this->parse();
		exit;
	}
		
		

	//// 
	// Kustutab eventid, mille id-d olid antud
	function delete_events($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			while(list($k,) = each($check))
			{
				$q = "DELETE FROM planner WHERE id = '$k'";
				$this->db_query($q);
			};
		};
		return $this->mk_site_orb(array(
			"date" => $thisdate,
		));
	}


	////
	// Muudab eventite staatust
	function set_status($args = array())
	{
		extract($args);
		if($action == "mark_as_closed")
		{
			$status = 1;
		}
		elseif ($action == "mark_as_problem")
		{
			$status = 2;
		};
		if ($status && is_array($check))
		{
			while(list($k,) = each($check))
			{
				$q = "UPDATE planner SET status = '$status' WHERE id = '$k'";
				$this->db_query($q);
			};
		};
		return $this->mk_site_orb(array(
			"date" => $thisdate,
		));
			
	}

	function _serialize($args = array())
	{
		extract($args);
		$q = "SELECT *,objects.* FROM planner LEFT JOIN objects ON (planner.id = objects.oid) WHERE id = '$oid'";
		$this->db_query($q);
		$row = $this->db_next();
		return serialize($row);
	}

	function _unserialize($args = array())
	{
		extract($args);
		$q = "SELECT * FROM msg_objects WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next($q);
		$msg_id = $row["message_id"];
		global $udata;
		$row["parent"] = $udata["home_folder"];
		$row["class_id"] = CL_CAL_EVENT;
		$row["name"] = $row["title"];

		extract($row);
		$oid = $this->new_object($row);
		$row = unserialize($row["content"]);
		$row = unserialize($row["str"]);

		$q = "INSERT INTO planner 
			(id,start,end,uid,title,description)
			VALUES ('$oid','$start','$end','$uid','$title','$description')";
		$this->db_query($q);
		global $status_msg;
		$status_msg = "Sündmus on päevaplaani lisatud";
		session_register("status_msg");
		return $this->mk_site_orb(array(
			"class" => "messenger",
			"action" => "show",
			"ref_orb" => 1,
			"id" => $msg_id,
		));
	}
		
};
?>
