<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/planner.aw,v 2.36 2001/06/28 22:17:04 duke Exp $
// fuck, this is such a mess
// planner.aw - päevaplaneerija
// CL_CAL_EVENT on kalendri event
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
	// !Joonistab menüü
	// argumendid:
	// activelist(array), levelite kaupa info selle kohta, millised elemendid
	// aktiivsed on
	// vars(array) - muutujad, mida xml-i sisse pannakse
	function gen_menu($args = array())
	{
		extract($args);
		global $basedir;
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$xm->vars($vars);
		$xm->load_from_files(array(
					"xml" => $basedir . "/xml/planner/menucode.xml",
					"tpl" => $this->template_dir . "/menus.tpl",
				));
		return $xm->create(array(
				"activelist" => $activelist,
			));
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
		$this->id = $id;
		return $this->mk_orb("change",array("id" => $id));
	}

	function edit_todo_item($args = array())
	{
		extract($args);
		$ftitle = ($op == "add") ? "Lisa TODO" : "Muuda TODO-d";
		$this->read_template("edit_todo.tpl");
		if ($id)
		{
			$q = "SELECT * FROM planner WHERE id = '$id'";
			$this->db_query($q);
			$row = $this->db_next();
		}
		else
		{
			$row = array();
		};
		$this->vars(array(
			"ftitle" => $ftitle,
			"title" => $row["title"],
			"description" => $row["description"],
			"reforb" => $this->mk_reforb("submit_todo_item",array("id" => $id,"date" => $date)),
		));
		return $this->parse();
	}

	function submit_todo_item($args = array())
	{
		$this->quote($args);
		extract($args);
		global $udata;
		if ($id)
		{
			$q = "UPDATE planner 
				SET title = '$title',
					description = '$description'
				WHERE id = '$id'";
		}
		else
		{
			
			$id = $this->new_object(array(
					"name" => "$title",
					"parent" => $udata["home_folder"],
					"class_id" => CL_CAL_EVENT,
				));
		
			list($d,$m,$y) = split("-",$date);
			$start = mktime(0,0,0,$m,$d,$y);
			$q = "INSERT INTO planner (id,title,description,start,type)
				VALUES ($id,'$title','$description',$start,1)";
		};
		$this->db_query($q);
		global $status_msg;
		$status_msg = "TODO on salvestatud";
		session_register("status_msg");
		return $this->mk_site_orb(array("date" => $date));
	}
				


	function user_planner($args = array())
	{
		extract($args);
		$this->tpl_init("planner");
		return $this->change($args);
	}

	function admin_planner($args = array())
	{
		extract($args);
//		$this->tpl_init("planner");
		$args["act"] = "change";
		$args["ids"] = "id=" . $args["id"];
		$args["ctype"] = "oid";
		$args["date"] = $date;
		if (!$date)
		{
			$date = date("d-m-Y");
		};
		return $this->change($args);
	}

	function export_event($args = array())
	{
		extract($args);
		$q = "SELECT * FROM planner WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		classload("xml");
		$xml = new xml(array("ctag" => "event"));
		$block = array(
			"start" => $row["start"],
			"end" => $row["end"],
			"title" => $row["title"],
			"description" => $row["description"],
			"color" => $row["color"],
			"place" => $row["place"],
		);
		$data = $xml->xml_serialize(array("data" => $block));
		
		classload("messenger","file");
		$msng = new messenger();
		$msg_id = $msng->init_message();
		$awf = new file();
		$awf->put(array(
				"store" => "fs",
				"filename" => "event.xml",
				"type" => "text/aw-event",
				"content" => $data,
				"parent" => $msg_id,
			));
			
		// step 1, create a new empty message object
		// step 2, create a file from this event
		// step 3, bounce the user into the editor
		return $this->mk_site_orb(array(
				"class" => "messenger",
				"action" => "edit",
				"id" => $msg_id,
			));
	}
	
	////
	// !Kuvab kalendri muutmiseks (eelkoige adminnipoolel)
	// id - millist kalendrit näidata
	// disp - vaate tüüp
	// date - millisele kuupäevale keskenduda
	function change($args = array())
	{
		extract($args);

		// kui kuupäeva pole defineeritud, siis defaultime tänasele
		if (!$date)
		{
			$date = date("d-m-Y");
		};

		if ($disp == "day")
		{
			$act = ($date == date("d-m-Y")) ? "today" : "day";
		}
		else
		{
			$act = $disp;
		};
		
		$menubar = $this->gen_menu(array(
				"activelist" => array($act),
				"vars" => array("date" => $date),
			));

		$object = $this->get_object($id);

			
		list($d,$m,$y) = split("-",$date);
		
		// X marks the spot
		$xdate = $d . $m . $y;

		$this->mk_path($object["parent"],CAL_CH_TITLE);

		if (!$disp)
		{
			$disp = "day";
		};

		$di = $this->get_date_range(array(
				"date" => $date,
				"type" => $disp,
		));
		
		$events = $this->get_events(array(
					"start" => $di["start"],
					"end" => $di["end"],
					"uid" => UID,
				));

		$ddiff1 = $this->get_day_diff($di["start"],$di["end"]);
		
		// tsükkel yle koigi selles perioodis asuvate päevade, et
		// leida ja paigutada events massiivi koik korduvad üritused
		
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

				// todo list
				$q = "SELECT * FROM planner WHERE ((start >= $di[start]) AND (start <= $di[end])) AND type = 1";
				$this->db_query($q);
				$tc = "";
				$cnt = 0;
				while($row = $this->db_next())
				{
					$cnt++;
					$this->vars(array(
						"id" => $row["id"],
						"title" => $row["title"],
						"content" => (strlen($row["description"]) > 30) ? substr($row["description"],0,30) . "..." : $row["description"],
						"num" => $cnt,
					));
					$tc .= $this->parse("todoline");
				};
				$this->vars(array("line" => $c,
						"date" => $date,
						"todoline" => $tc,
						"total" => $cnt));
				$content = $this->parse();
				break;
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
			"date" => $date,
			"menubar" => $menubar,
			"next" => $next));
		return $this->parse();
	}

	////
	// !tagastab eventid mingis ajavahemikus
	// argumendid:
	// start(timestamp), end(timestamp)
	// parent(int) - kalendri ID
	//  voi
	// uid(char) - kasutaja id, kui tegemist on kasutaja kalendriga
	// index_time - if set, the returned array is indexed by the event start time
	
	function get_events($args = array())
	{
		classload("repeater");
		$repeater = new repeater();
		extract($args);
		if ($uid)
		{
			$selector = " AND planner.uid = '$uid'";
		}
		elseif ($parent)
		{
			$selector = " AND objects.parent = '$parent'";
		};
		
		if (!$end)
		{
			// note, the repeater parser is horribly ineffective with repeaters
			// that span over a long time period.
			$end = mktime(23,59,59,12,31,2002);
		};

		// defaultima näitma lihtsalt tavalisi eventeid (neid millel type on NULL)
		// tekalt, ma kardan, et sellega voib tekkida probleeme, kui me AW-d
		// mone teise DB peale portima hakkame.
		$tp = ($type) ? " AND planner.type = $type " : " AND planner.type = NULL ";

		$eselect = (isset($event)) ? "AND planner.id = '$event'" : "";
		$limit = ($limit) ? $limit : 999999;
		$retval = array();
		$reps = array();
		if (isset($event))
		{
			$q = "SELECT * FROM planner
				LEFT JOIN objects ON (planner.id = objects.oid)
				WHERE objects.status = 2 AND planner.id = $event";
		}
		else
		{
			$q = "SELECT * FROM planner
			LEFT JOIN objects ON (planner.id = objects.oid)
			WHERE objects.status = 2 $selector $eselect $tp
				AND ( (start >= '$start') OR (start <= '$end') OR (rep_until >= '$start'))
				ORDER BY start";
		};	
		$this->db_query($q);
		while($row = $this->db_next())
		{
			// sx-i peab kuidagi teisiti arvutama
			// fawking kala seisneb selles, et kui sa oled 
			// repeateritega määratud ajaloigu sees, siis
			// ntx päevade puhul hakatakse tsyklit valest kohast ..
			// ehk siis repeateri algusest. 
			$ex = ($end > $row["rep_until"]) ? $row["rep_until"] : $end;
			if (isset($event))
			{
				$start2 = ($row["start"] > $start) ? $row["start"] : $start;
				$repeater->init($start,$start2,$ex);
			}
			else
			{
				$repeater->init($start,$row["start"],$ex);
			};
			
			$this->save_handle();
			$q = "SELECT * FROM planner_repeaters WHERE eid = '$row[oid]' ORDER BY type DESC";
			$this->db_query($q);
			while($rep = $this->db_next())
			{
				$repeater->handle($rep);
			};
			$this->restore_handle();
			$vector = $repeater->get_vector();
			if ($vector)
			{
				$size = sizeof($vector);
				$continue = true;
				reset($vector);
				$cnt = 0;
				while($continue)
				{
					$cnt++;
					list(,$slice) = each($vector);
					list($slice_start,$slice_end) = each($slice);
					$index = date("dmY",$slice_start);
					if ($index_time)
					{
						$retval[$slice_start] = $row;
					}
					else
					{
						$retval[$index][] = $row;
					}
					if ( ($cnt >= $size) || ($cnt >= $limit) )
					{
						$continue = false;
					};
				};
			}
			else
			{
				$index = date("dmY",$row["start"]);
				if ($index_time)
				{
					$retval[$row["start"]] = $row;
				}
				else
				{
					$retval[$index][] = $row;
				}
			};
		};
		return (sizeof($retval) > 0) ? $retval : false;
	}

	// aga voib-olla luua nende vahemike kujutamiseks hoopis eraldi objekt?
	function parse_repeater($args = array())
	{
		extract($args);
		

	}


	function adm_event($args = array())
	{
		extract($args);
		// kui me maandume siia läbi eventi lisamise lingi, siis teeme objekti ära,
		// ning suuname kasutaja äsjaloodud eventi muutmisse ümber
		if ($op == "add")
		{
			// date on hidden field muutmis/lisamisvormist. Sisaldab eventi kuupäeva dd-mm-yyyy
			list($d,$m,$y) = split("-",$date);
			// sellest teeme timestampi
			$start = mktime(0,0,0,$m,$d,$y);
			// kui parent on defineerimata, siis savelstame ta kodukataloogi alla
			if (!$parent)
			{
				global $udata;
				$parent = $udata["homefolder"];
			};
			$uid = UID;
			$id = $this->new_object(array(	
				"class_id" => CL_CAL_EVENT,
				"parent" => $parent,
				"name" => "",
			),true);

			$q = "INSERT INTO planner (id,uid,start) VALUES ('$id','$uid','$start')";
			$this->db_query($q);
			$status_msg = "Event on lisatud";
			$retval = $this->mk_my_orb("editevent",array("id" => $id));
			return $retval;
		};
			
		$menubar = $this->gen_menu(array(
			"activelist" => array("add","edit"),
			"vars" => array("id" => $id),
		));
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
				"rep_until" => $row["rep_until"],
				"repcheck" => ($rc > 0) ? "checked" : "",
				"color" => $this->picker($row["color"],$colors),
				"description" => $row["description"],
				"reminder" => $row["reminder"],
				"menubar" => $menubar,
				"id" => $id,
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
		extract($args);
		$menubar = $this->gen_menu(array(
			"activelist" => array("add","repeaters"),
			"vars" => array("id" => $id),
		));
		$caldata = $this->get_object_metadata(array(
						"oid" => $id,
						"key" => "calconfig",
				));
		if (is_array($caldata))
		{
			extract($caldata);
		}
		$this->tpl_init("planner");
		$this->read_template("reps.tpl");
		// oh, I know, this is sooo ugly
		$this->vars(array(
				"pri1" => ($pri[1]) ? $pri[1] : 1,
				"pri2" => ($pri[2]) ? $pri[2] : 2,
				"pri3" => ($pri[3]) ? $pri[3] : 3,
				"pri4" => ($pri[4]) ? $pri[4] : 4,
				"pri5" => ($pri[5]) ? $pri[5] : 5,
				"pri6" => ($pri[6]) ? $pri[6] : 6,
				"pri7" => ($pri[7]) ? $pri[7] : 7,
				"pri8" => ($pri[8]) ? $pri[8] : 8,
				"use1" => checked($use[1]),
				"use2" => checked($use[2]),
				"use3" => checked($use[3]),
				"use4" => checked($use[4]),
				"use5" => checked($use[5]),
				"use6" => checked($use[6]),
				"use7" => checked($use[7]),
				"use8" => checked($use[8]),
				"weekpwhen1" => checked($weekpwhen[1]),
				"weekpwhen2" => checked($weekpwhen[2]),
				"weekpwhen3" => checked($weekpwhen[3]),
				"weekpwhen4" => checked($weekpwhen[4]),
				"weekpwhen5" => checked($weekpwhen[5]),
				"weekpwhen6" => checked($weekpwhen[6]),
				"weekpwhen7" => checked($weekpwhen[7]),
				"monpwhen1" => checked($monpwhen[1]),
				"monpwhen2" => checked($monpwhen[2]),
				"monpwhen3" => checked($monpwhen[3]),
				"monpwhen4" => checked($monpwhen[4]),
				"monpwhen5" => checked($monpwhen[5]),
				"monpwhen6" => checked($monpwhen[6]),
				"mp2" => $monpwhen2,
				"yearpwhen" => $yearpwhen,
				"dayskip" => ($skip["day"]) ? $skip["day"] : 1,
				"weekskip" => ($skip["week"]) ? $skip["week"] : 1,
				"monthskip" => ($skip["month"]) ? $skip["month"] : 1,
				"yearskip" => ($skip["year"]) ? $skip["year"] : 1,
				"menubar" => $menubar,
				"reforb" => $this->mk_reforb("submit_repeaters",array("id" => $id)),
			));
		return $this->parse();
	}

	function submit_repeaters($args = array())
	{
		extract($args);
		// reap the old repeaters
		// first we will calculate the end date for those repeaters
		switch($rep)
		{
			case "3":
				$repend_date = sprintf("%02d-%02d-%04d",$repend["day"],$repend["mon"],$repend["year"]);
				if (!is_date($repend_date))
				{
					$repend_date = "31-12-2037";
				};
				break;

			case "2":
				// kui joudsime siia, siis on meil kordade arv $repeats muutujas ja me peame välja arvutama
				// selle, kui kaugele tulevikku kordused lähevad
				
				break;

			default:
				$repend_date = "31-12-2037";
				break;
		};
		$q = "DELETE FROM planner_repeaters WHERE eid = '$id'";
		$this->db_query($q);
		// right now we will just stick with events repeating forever
		list($d,$m,$y) = explode("-",$repend_date);
		$rep_until = mktime(0,0,0,$m,$d,$y);

		$uid = ($oid) ? "" : UID;
		// blah. But we really really don't have to do it. We only have to calculate the dates when the events take
		// place.
		
		if ($use[1])
		{
			$_type = REP_DAY;
			$_skip = $skip["day"];
			$q = "INSERT INTO planner_repeaters (eid,type,skip) VALUES ('$id','$_type','$_skip')";
			$this->db_query($q);
		};

		if ($use[5] && is_array($weekpwhen))
		{
			$weekpwhen = join(",",array_keys($weekpwhen));
		}
		else
		{
			$weekpwhen = "";
		};

		if ($use[2] || $weekpwhen)
		{
			$_type = REP_WEEK;
			$_skip = ($weekpwhen) ? 0 : $skip["week"];
			$q = "INSERT INTO planner_repeaters (eid,type,skip,pwhen) VALUES ('$id','$_type','$_skip','$weekpwhen')";
			$this->db_query($q);
		};

		if ($use[6] && is_array($monpwhen))
		{
			$monpwhen = join(",",array_keys($monpwhen));
		}
		else
		{
			$monpwhen = "";
		};

		$monpwhen2 = ($use[7]) ? $monpwhen2 : "";

		if ($use[3] || $monpwhen || $monpwhen2)
		{
			$_type = REP_MONTH;
			$_skip = ($monpwhen) ? 0 : $skip["month"];
			$q = "INSERT INTO planner_repeaters (eid,type,skip,pwhen,pwhen2) VALUES ('$id','$_type','$_skip','$monpwhen','$monpwhen2')";
			$this->db_query($q);
		};

		$yearpwhen = ($use[8]) ? $yearpwhen : "";

		if ($use[4] || $yearpwhen)
		{
			$_type = REP_YEAR;
			$_skip = $skip["year"];
			//$_skip = ($yearpwhen) ? 0 : $skip["year"];
			$q = "INSERT INTO planner_repeaters (eid,type,skip,pwhen) VALUES ('$id','$_type','$_skip','$yearpwhen')";
			$this->db_query($q);
		};

		// and now we will update the record for event itself
		$q = "UPDATE planner SET rep_until = '$rep_until' WHERE id = '$id'";

		$caldata = array(
					"pri" => $pri,
					"use" => is_array($use) ? $use : array(),
					"skip" => $skip,
					"weekpwhen" => is_array($args["weekpwhen"]) ? $args["weekpwhen"] : array(),
					"monpwhen" => is_array($args["monpwhen"]) ? $args["monpwhen"] : array(),
					"monpwhen2" => $args["monpwhen2"],
					"yearpwhen" => $args["yearpwhen"],
					"rep" => $rep,
					"repeats" => $repeats,
					"repend" => $repend,
				);
		$this->set_object_metadata(array(
					"oid" => $id,
					"key" => "calconfig",
					"value" => $caldata,
				));
				
					
		$this->db_query($q);
		return $this->mk_my_orb("repeaters",array("id" => $id));
	}

	function importfile($args = array())
	{
		classload("file","xml");
		$awf = new file();
		$xml = new xml();
		extract($args);
		$fdat = $awf->get(array("id" => $id));
		$edata = $xml->xml_unserialize(array("source" => $fdat["file"]));
		$start = $edata["data"]["start"];
		$end = $edata["data"]["end"];
		$title = $edata["data"]["title"];
		$description = $edata["data"]["description"];
		$this->quote($title); $this->quote($description);
		global $udata;
		$uid = UID;
		$parent = $udata["home_folder"];
		$id = $this->new_object(array(	
			"class_id" => CL_CAL_EVENT,
			"parent" => $parent,
			"name" => $title,
		),true);

		$q = "INSERT INTO planner 
			(id,uid,start,end,title,description)
			VALUES ('$id','$uid','$start','$end','$title','$description')";

		$this->db_query($q);
		global $status_msg;
		$status_msg = " päevaplaan on salvestatud";
		session_register("status_msg");
		$obj = $this->get_object($args["id"]);
		return $this->mk_site_orb(array(
					"class" => "messenger",
					"action" => "show",
					"id" => $obj["parent"],
				));
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

		// siin tuleks koigepealt vanad repeaterid mine visata, sest nende kontroll voib osutuda liiga tylikaks
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
		return $this->mk_my_orb("editevent",array("id" => $id));
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
		return $id;
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
		$params = array();
		$params["date"] = $date;
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
	
		$date = ($date) ? $date : date("d-m-Y");
		$slice_id = join("",explode("-",$date));

		$di = $this->get_date_range(array(
				"date" => $date,
				"type" => "day",
		));

		$events = $this->get_events(array(
				"start" => $di["start"],
				"end" => $di["end"],
				"uid" => UID,
			));

		foreach($events[$slice_id] as $key => $val)
		{
			print $val["title"] . "<br>";
			print date("d-m-Y H:i",$val["start"]) . "<br>";
			print date("d-m-Y H:i",$val["end"]) . "<br>";

		};

		print $retval;
		exit;
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
		$end = strtotime("+1 week",$start) + 86399; //23h59m59s
		$events = $this->get_events(array(
				"start" => $start,
				"end" => $end,
				"uid" => UID,
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
		
		$today = date("dmY");

		// Joonistame nädala
		for ($i = 0; $i <= 6; $i++)
		{
			$current = $start + ($i * 60 * 60 * 24);
			$current_long = date("dmY",$current);
			$subtpl = "line";
			if ($today == $current_long)
			{
				if ($short)
				{
					$subtpl = "line";
				}
				else
				{
					$subtpl = $this->templates["line2"] ? "line2" : "line";
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
				"day2" => date("d",$current) . "." . get_lc_month(date("m",$current)),
				"wday" => get_lc_weekday($i+1),
				"sufix" => $sufix,
				"date" => date("d-m-Y",$current),
				"contents" => $contents[date("d",$current)],
				"events" => sizeof($events[$current_long]),
			));
			$c .= $this->parse($subtpl);
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
