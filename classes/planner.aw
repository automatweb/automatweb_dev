<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/planner.aw,v 2.61 2002/02/07 00:06:40 duke Exp $
// fuck, this is such a mess
// planner.aw - päevaplaneerija
// CL_CAL_EVENT on kalendri event
lc_load("planner");
classload("calendar","defs");
global $orb_defs;
$orb_defs["planner"] = "xml";
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
		lc_load("definition");
		global $lc_planner;
		if (is_array($lc_planner))
		{
			$this->vars($lc_planner);
		}
	}
	
	////
	// !Kuvab uue kalendri lisamise vormi
	// parent(id) - int : menüü, mille alla uus kalender lisatakse
	function add($args = array())
	{
		$this->read_template("add.tpl");
		$this->vars(array(
				"reforb" => $this->mk_reforb("submit_add",array("parent" => $args["parent"])),
		));
		return $this->parse();
	}

	////
	// !Submitib uue kalendriobjekti
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

	////
	// !Kuvab kalendri aliase konfimise vormi
	function add_alias($args = array())
	{
		extract($args);
		return $this->mk_my_orb("search",array("s_name" => "%25","s_comment" => "%25","docid" => $parent,"s_type" => CL_CALENDAR),"aliasmgr");

	}
	
	//// 
	// !Kuvab kalendi muutmise vormi
	// id(int) - konfigureeritava kalendri ID
	function change($args = array())
	{
		extract($args);
		$object = $this->get_object($id);
		if (!$date)
		{
			$date = date("d-m-Y");
		};
		$menubar = $this->gen_menu(array(
				"activelist" => array("config"),
				"vars" => array("id" => $id,"date" => $date),
		));
		$this->read_template("change.tpl");
		$this->mk_path($object["parent"],"Muuda kalendrit");
		$this->vars(array(
			"name" => $object["name"],
			"oid" => $object["oid"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
			"menubar" => $menubar,
		));
		return $this->parse();
	}
	
	////
	// !Submitib olemasoleva kalendriobjekti
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,true));
		return $this->mk_orb("change",array("id" => $id));
	}

	////
	// !Parsib kalendrialiast
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->calaliases) || ($oid != $this->cal_oid) )
		{
			$this->calaliases = $this->get_aliases(array(
								"oid" => $oid,
								"type" => CL_CALENDAR,
			));
			$this->cal_oid = $oid;
		};
                $c = $this->calaliases[$matches[3] - 1];
		$replacement = $this->object_list(array("id" => $c["target"],"type" => "day"));
		return $replacement;

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

	function _serialize_event($args = array())
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
		if ($new)
		{
			$msng = new messenger();
			$msg_id = $msng->init_message();
		};
		$awf = new file();
		$name = ($row["title"]) ? $row["title"] : "event";
		$awf->put(array(
				"store" => "fs",
				"filename" => "$name.xml",
				"type" => "text/aw-event",
				"content" => $data,
				"parent" => $msg_id,
			));
	}

	function export_event($args = array())
	{
		extract($args);

		$args["new"] = true;
		$this->_serialize_event($args);
		
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
	function view($args = array())
	{
		extract($args);

		// kui kuupäeva pole defineeritud, siis defaultime tänasele
		if (!$date)
		{
			$date = date("d-m-Y");
		};

		$this->date = $date;

		if ($type == "day")
		{
			$act = ($date == date("d-m-Y")) ? "today" : "day";
		}
		else
		{
			$act = $type;
		};
	
		$menubar = $this->gen_menu(array(
				"activelist" => array($act),
				"vars" => array("date" => $date,"id" => $id),
			));

		$object = $this->get_object($id);
		$par_obj = $this->get_object($object["parent"]);

		$this->parent_class = $object["class_id"];

		list($d,$m,$y) = split("-",$date);
		
		$xdate = $d . $m . $y;

		$this->mk_path($object["parent"],"Kalender");

		if (!$type)
		{
			$type = "day";
		};

		$di = $this->get_date_range(array(
				"date" => $date,
				"type" => $type,
		));
		
		$events = $this->get_events2(array(
					"start" => $di["start"],
					"end" => $di["end"],
					"folder" => $id,
				));

		$ddiff1 = $this->get_day_diff($di["start"],$di["end"]);
		
		// tsükkel yle koigi selles perioodis asuvate päevade, et
		// leida ja paigutada events massiivi koik korduvad üritused
		

		list($d,$m,$y) = split("-",$date);
		switch($type)
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
							$this->_draw_event($e);
							$c1 .= $this->parse("line.event");
						};
					};
					$bgcolor = ($xdate == $dx) ? "#DDDDDD" : "#FFFFFF";
					$this->vars(array("event" => $c1,
							"head" => get_lc_weekday($i+1),
							"did" => $id,
							"hid" => $args["id"],
							"type" => "week",
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
								$this->_draw_event($e);
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
					$this->_draw_event($e);
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
						$this->_draw_event($e);
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
							"type" => "overview",
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
						$this->_draw_event($e);
						$c .= $this->parse("line");
					};
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
		$prev = "class=planner&action=view&id=$id&type=$type&date=$di[prev]&id=$id";
		$next = "class=planner&action=view&id=$id&type=$type&date=$di[next]&id=$id";
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
	// !Draws a single event
	function _draw_event($e = array())
	{
		if ($e["aid"])
		{
			$obj = $this->get_object($e["aid"]);
			$meta = aw_unserialize($e["metadata"]);	
			if ($meta["showtype"] == 0)
			{
				$link = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=0,location=0,menubar=0,scrollbars=1,width=400,height=500\")'","orb.aw?class=objects&action=show&id=$obj[oid]",$obj["oid"]);
				$repl = "<a href='#' $link>$obj[name]</a>";
			}
			else
			{
				$repl = $this->_show_object($obj);
			};
			//$name = sprintf("<br><img src='%s'>%s",get_icon_url($obj["class_id"],""),$obj["name"]);
			$name = "<br>" .$repl;
		}
		else
		{
			$name = "";
		};
		if ($this->parent_class == CL_CALENDAR)
		{
			$ev_link = $this->mk_my_orb("change_event",array("id" => $e["id"],"date" => $this->date),"planner");
		}
		else
		{
			$ev_link = $this->mk_my_orb("change",array("id" => $e["id"],"date" => $this->date),"cal_event");
		};

		$this->vars(array(
			"color" => $e["color"],
			"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
			"event_link" => $ev_link,
			"id" => $e["id"],
			"title" => ($e["title"]) ? $e["title"] : "(nimetu)",
			"object" => $name,
			"contents" => nl2br($e["description"]),
		));


	}

	function object_list($args = array())
	{
		extract($args);
		if (!$date)
		{
			$date = date("d-m-Y");
		};
		$di = $this->get_date_range(array(
				"date" => $date,
				"type" => "day",
		));
		
		$events = $this->get_events2(array(
					"start" => $di["start"],
					"end" => $di["end"],
					"folder" => $id,
		));
		$slice = date("dmY",$di["start"]);
		$repl = "";
		if (is_array($events[$slice]))
		{
			foreach($events[$slice] as $key => $e)
			{
				$emb_obj = $this->db_fetch_field("SELECT oid FROM planner WHERE id = $e[id]","oid");
				$repl .= $this->_show_object(array("oid" => $emb_obj));
			};
		};
		return $repl;

	}

	function display_object($args = array())
	{
		extract($args);
		$obj = $this->get_object($args["id"]);
		$repl = $this->_show_object($obj);
		return $repl;
	}

	////
	// Copies the calendar object together with all repeaters.
	// argumendid:
	// id(int): vana kalendri id
	// parent(int): kuhu kalender kopeerida

	// oh god, this is sooo ugly
	function cp($args = array())
	{
		$id = $args["id"];
		// koigepealt kopeerime kalendri enda objekti
		$old = $this->get_object($args["id"]);
		$old["parent"] = $args["parent"];
		$new_id = $this->new_object($old);
		// now we need to copy the events (planner table)
		$q = "SELECT * FROM objects WHERE parent = $id";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			$row["parent"] = $new_id;
			$new_pid = $this->new_object($row);
			$plx = $this->get_record("planner","id",$row["oid"]);
			extract($plx);
			$q = "INSERT INTO planner (id,uid,start,end,title,description,type,status,oid,
				place,reminder,private,color,rep_type,rep_dur,rep_until)
				VALUES ('$new_pid','$uid','$start','$end','$title','$description','$type',
					'$status','$oid','$place','$reminder','$private','$color',
					'$rep_type','$rep_dur','$rep_until')";
			$this->db_query($q);
			$q = "SELECT * FROM planner_repeaters WHERE eid = '$row[oid]'";
			$this->db_query($q);
			while($row2 = $this->db_next())
			{
				extract($row2);
				$q = "INSERT INTO planner_repeaters (eid,type,start,end,skip,pwhen,cid,pwhen2)
					VALUES ('$new_pid','$type','$start','$end','$skip','$pwhen','$cid','$pwhen2')";
				$this->db_query($q);
			};

			$this->restore_handle();
		}
		return $new_id;
	}

	function get_events2($args = array())
	{
		extract($args);
		$q = "SELECT *,planner.oid AS aid FROM planner
			LEFT JOIN objects ON (planner.id = objects.oid)
			WHERE objects.status = 2 AND planner.folder = '$folder'
			AND ( (start >= '$start') OR (start <= '$end') OR (rep_until >= '$start'))
			ORDER BY start";
		$this->db_query($q);
		$timebase = mktime(0,0,0,1,1,2001);
		$start_gdn = sprintf("%d",($start - $timebase) / 86400);
		$end_gdn = sprintf("%d",(($end - $timebase) / 86400) + 1);
		$gdn = $start_gdn;
		$range = range($start_gdn,$end_gdn);	
		$results = array();
		// I  need to find all the day number for the time period the client asked
		while($row = $this->db_next())
		{
			$reps = aw_unserialize($row["repeaters"]);
			if (is_array($reps))
			{
				$intersect = array_intersect($reps,$range);
			};
			// always show the event at the day it was added
			$results[date("dmY",$row["start"])][] = $row;
			if (is_array($intersect))
			{
				foreach($intersect as $xgdn)
				{
					$ts = mktime(0,0,0,1,$xgdn,2001);
					if ($ts >= $row["start"])
					{
						$gx = date("dmY",$ts);
						$results[$gx][] = $row;
					};
				};
			};
			$intersect = "";
					
			$gdn++;
		}
		return $results;
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
		extract($args);
		classload("repeater");
		$repeater = new repeater();
		if ($uid)
		{
			$selector = " AND planner.uid = '$uid'";
		}
		elseif ($parent)
		{
			$selector = " AND objects.parent = '$parent'";
		}
		elseif ($folder)
		{
			$select = " AND planner.folder = '$folder'";
		}
		
		if (!$end)
		{
			// note, the repeater parser is horribly ineffective with repeaters
			// that span over a long time period.
			$end = mktime(23,59,59,12,31,2002);
		};

		// defaultima näitma lihtsalt tavalisi eventeid (neid millel type on NULL)
		// tekalt, ma kardan, et sellega voib tekkida probleeme, kui me AW-d
		// mone teise DB peale portima hakkame.
		//$tp = ($type) ? " AND planner.type = $type " : " AND planner.type = NULL ";

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
			WHERE objects.status = 2 $select $eselect $tp
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
				#$start2 = ($row["start"] > $start) ? $row["start"] : $start;
				$start2 = ($row["start"] > $start) ? $start : $row["start"];
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
					if (not(is_array($slice)))
					{
						$continue = false;
						continue;
					};
					list($slice_start,$slice_end) = each($slice);
					if ($slice_start < $start)
					{
						continue;
					};
					$_saast = $row["start"];
					$index = date("dmY",$slice_start);
					if ($index_time)
					{
						#if ($_saast > $slice_start)
						#{
						#	$_slice = $s_saast;
						#}
						#else
						#{
						#	$_slice = $slice_start;
						#};
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


	////
	// !Displays an object the screen
	// expects the data from the object array
	function _show_object($args = array())
	{
		extract($args);
		$replacement = "";
		if (not($this->ob))
		{
			classload("objects");
			$this->ob = new objects();
		}

		$replacement = $this->ob->show(array("id" => $args["oid"]));
		return $replacement;

	}

	

	function submit_repeaters($args = array())
	{
		extract($args);
		// store them inside the object so we can use them elsewhere
		$this->repeats = $args;
		$this->set_object_metadata(array(
			"oid" => $id,
			"overwrite" => 1,
			"key" => "repeaters",
			"value" => $args,
		));
		return $this->mk_my_orb("repeaters",array("id" => $id));
		// the "rep" variable contains the type of the repetitions
		/*
			1 - forever (until told otherwise)
			2 - X times : repeats(string)
			3 - until a date : repend(array) - day,mon,year

		*/

		// first, we have to find out when the event starts
		// TODO: replace this with a call to a special function
		$q = "SELECT * FROM planner WHERE id = '$id'";
		$this->db_query($q);
		$event = $this->db_next();

		// remove old repeaters
		$q = "DELETE FROM planner_repeaters WHERE eid = '$id'";
			

		// at first, we find out how many events and on what days we have to create
		if (is_array($use))
		{
			foreach($use as $type)
			{
				switch($type)
				{
					case "4":
				};
						


			}
		};
		print "<pre>";
		print_r($args);
		print "</pre>";
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
		$status_msg = LC_DAY_IS_SAVED;
		session_register("status_msg");
		$obj = $this->get_object($args["id"]);
		return $this->mk_site_orb(array(
					"class" => "messenger",
					"action" => "show",
					"id" => $obj["parent"],
				));
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
		$params["id"] = $id;
		$params["id"] = $id;
		$params["disp"] = $disp;
		$action = "view";
		$retval = $this->mk_my_orb("view",array("type" => $disp,"date" => $date,"id" => $id));
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
		$this->tpl_init("objects");
		$this->read_template("events.tpl");
		$date = ($date) ? $date : date("d-m-Y");
		$slice_id = join("",explode("-",$date));

		$di = $this->get_date_range(array(
				"date" => $date,
				"type" => "day",
		));

		$events = $this->get_events(array(
				"start" => $di["start"],
				"end" => $di["end"],
				"id" => $id,
			));

		$c = "";

		if (!is_array($events[$slice_id]))
		{
			$events[$slice_id] = array();
		};

		foreach($events[$slice_id] as $key => $val)
		{
			$this->vars(array(
					"start" => date("H:i",$val["start"]),
					"end" => date("H:i",$val["end"]),
					"title" => $val["title"],
					"oid" => $val["oid"],
				));
			$c .= $this->parse("line");
		};
		
		$thiswday = get_lc_weekday($this->_convert_wday(date("w",$di["start"])));

		$longname = date("d",$di["start"]) . ". " . get_lc_month(date("m",$di["start"]));
		
		$this->vars(array(
				"msgid" => $msgid,
				"line" => $c,
				"today" => $thiswday . ", " . $longname,
				"prev" => $di["prev"],
				"next" => $di["next"],
				"reforb" => $this->mk_reforb("submit_pick_msg_event",array("msgid" => $msgid)),
		));
		print $this->parse();
		exit;
	}

	function submit_pick_msg_event($args = array())
	{
		extract($args);
		if (is_array($check))
		{
			foreach($check as $id)
			{
				$this->_serialize_event(array(
							"id" => $id,
							"msg_id" => $msgid,
				));
			};
		};
		print "<script language='javascript'>window.close()</script>";
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
				"caption" => LC_PLANNER_TODAY,
			),
			"overview" => array(
				"link" => "$baseurl/?class=planner&action=overview",
				"caption" => LC_PLANNER_OVERVIEW,
			),
			"day" => array(
				"link" => "$baseurl/?class=planner",
				"caption" => LC_PLANNER_DAY,
			),
			"week" => array(
				"link" => "$baseurl/?class=planner&action=show_week",
				"caption" => LC_PLANNER_WEEK,
			),
			"month" => array(
				"link" => "$baseurl/?class=planner&action=show_month",
				"caption" => LC_PLANNER_MONTH,
			),
			"add" => array(
				"link" => "$baseurl/?class=planner&action=add_event",
				"caption" => LC_PLANNER_ADD_NEW,
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
	// draw_week (joonistab nädala vaate, ntx saidis kasutamiseks)
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
		classload("users");
		$u = new users();
		$cal_id = $u->get_user_config(array("uid" => UID,"key" => "calendar"));
		if (not($cal_id))
		{
			$cal_id = -1;
		};
		$end = strtotime("+1 week",$start) + 86399; //23h59m59s
		$events = $this->get_events2(array(
				"start" => $start,
				"end" => $end,
				"parent" => $cal_id,
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
			$date = date("d-m-Y",$current);
			$this->vars(array(
				"day" => date("d-m",$current) . $sufix,
				"day2" => date("d",$current) . "." . get_lc_month(date("m",$current)),
				"wday" => get_lc_weekday($i+1),
				"sufix" => $sufix,
				"date" => date("d-m-Y",$current),
				"event_link" => $this->mk_my_orb("day",array("id" => $cal_id,"date" => $date),"planner"),
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
		$status_msg = LC_PLANNER_EVENT_ADDED2;
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

	////
	// !Since we need to show the planner tabs when adding an event we need to wrap
	// those functions from cal_event here
	function new_event($args = array())
	{
		extract($args);
		$menubar = $this->gen_menu(array(
				"activelist" => array("add"),
				"vars" => array("id" => $parent,"date" => $date),
		));
		classload("cal_event");
		$ce = new cal_event();
		$html = $ce->add(array(
			"parent" => $parent,
			"folder" => $parent,
			"date" => $date,
		));
		return $menubar . $html;
	}
	
	function change_event($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$menubar = $this->gen_menu(array(
				"activelist" => array("xxx"),
				"vars" => array("id" => $obj["parent"]),
		));
		classload("cal_event");
		$ce = new cal_event();
		$html = $ce->change(array(
			"id" => $id,
		));
		$this->mk_path($par_obj["parent"],"Kalender / Muuda sündmust");
		return $menubar . $html;
	}
	
	function event_repeaters($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$menubar = $this->gen_menu(array(
				"activelist" => array("xxx"),
				"vars" => array("id" => $obj["parent"]),
		));
		classload("cal_event");
		$ce = new cal_event();
		$html = $ce->repeaters(array(
			"id" => $id,
			"stage" => $stage,
		));
		$this->mk_path($par_obj["parent"],"Kalender / Muuda sündmust");
		return $menubar . $html;
	}
	
	function event_reminder($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$menubar = $this->gen_menu(array(
				"activelist" => array("xxx"),
				"vars" => array("id" => $obj["parent"]),
		));
		classload("cal_event");
		$ce = new cal_event();
		$html = $ce->reminder(array(
			"id" => $id,
		));
		$this->mk_path($par_obj["parent"],"Kalender / Muuda sündmust");
		return $menubar . $html;
	}
	
	function event_object_search($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$menubar = $this->gen_menu(array(
				"activelist" => array("xxx"),
				"vars" => array("id" => $obj["parent"]),
		));
		classload("cal_event");
		$ce = new cal_event();
		$html = $ce->search(array(
			"id" => $id,
			"s_name" => $s_name,
			"s_type" => $s_type,
			"s_comment" => $s_comment,
		));
		return $menubar . $html;
	}

	function bron_add_event($args = array())
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
	


};
?>
