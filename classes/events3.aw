<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/events3.aw,v 2.3 2001/11/15 13:10:28 duke Exp $
// events.aw - the sucky sucky version of the PIKK calendar

//  kalendri vaated teemade kaupa:
// laadad, messid, näitused
// rahvakalender
// kylvikalender
// maksukalender
// pikki kalender
//  koolituskalender


global $orb_defs;
$orb_defs["events3"] = "xml";

class events3 extends aw_template {

	function events3($args = array())
	{
		$this->tpl_init("events");
		$this->db_init("events");
		$this->types = array(
			"1" => "Laadad",
			"2" => "Messid",
			"3" => "Näitused",
			"4" => "Rahvakalender",
			"5" => "Külvikalender",
			"6" => "Maksukalender",
			"7" => "PIKK kalender",
			"8" => "Koolituskalender",
		);

		$this->places = array(
			"1" => "Harjumaa",
			"2" => "Hiiumaa",
			"3" => "Ida-Virumaa",
			"4" => "Jõgevamaa",
			"5" => "Järvamaa",
			"6" => "Lääne-Virumaa",
			"7" => "Läänemaa",
			"8" => "Põlvamaa",
			"9" => "Pärnumaa",
			"9" => "Raplamaa",
			"11" => "Saaremaa",
			"12" => "Tartumaa",
			"13" => "Valgamaa",
			"14" => "Viljandimaa",
			"15" => "Võrumaa",
		);
	}
	
	////
	// !Kuvab eventi lisamis/muutmisvormi
	// eventi lisamisel kuvatakse koigepealt esimene leht sisestamisvormist, 
	// selle submittimisel genereeritakse ID, mille abil siis kuvatakse
	// edaspidi koik teised muutmislehed.
	function event_edit($args = array())
	{
		extract($args);

		if ($args["id"])
		{
			$action = "edit";
			$act = "Muuda üritust/sündmust";
			$old = $this->get_record("events3","id",$args["id"]);
			$obj = $this->get_object($id);
		}
		else
		{
			$obj = array();
			$action = "add";
			$act = "Lisa üritus/sündmus";
			$old = array();
		};

		$this->read_template("edit.tpl");
		
		load_vcl("date_edit");
               	$start = new date_edit("start");
		$start->set("minute_step",30);
               	$start->configure(array("day" => 1,"month" => 2,"year" => 3,"hour" => 4,"minute" => 5));
		$old_st = ($old["start"]) ? $old["start"] : time();
		$start_ed = $start->gen_edit_form("start",$old_st);

		$this->vars(array(
			"action" => $act,
			"type" => $this->picker($old["type"],$this->types), 
			"place" => $this->picker($old["place"],$this->places),
			"location" => $old["location"],
			"name" => $obj["name"],
			"info" => $old["info"],
			"organizer" => $old["organizer"],
			"contact" => $old["contact"],
			"additional" => $old["additional"],
			"ticket" => $old["ticket"],
			"start" => $start_ed,
			"reforb" => $this->mk_reforb("submit",array("id" => $args["id"],"page" => $page)),
		));
		
                
		return $this->parse();
	}

	////
	// !Submits an event
	function event_submit($args = array())
	{
		$this->quote($args);
		extract($args);
		// it's a new event. Register it.
		if (not($id))
		{
			global $rootmenu;
			$parent = $rootmenu;
			$id = $this->new_object(array(
						"parent" => $parent,
						"class_id" => CL_EVENT,
						"name" => $name,
						"status" => 2,
			));
				
			$st = mktime($start["hour"],$start["minute"],0,$start["month"],
							$start["day"],$start["year"]);


			$q = "INSERT INTO events3 (id,type,start,place,location,info,organizer,contact,additional,ticket)
				VALUES ('$id','$type','$st','$place','$location','$info','$organizer','$contact','$additional','$ticket')";

			$this->db_query($q);
				
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
			));

			$st = mktime($start["hour"],$start["minute"],0,$start["month"],
							$start["day"],$start["year"]);

			$q = "UPDATE events3 SET 
				type = '$type',
				start = '$st',
				place = '$place',
				location = '$location',
				info = '$info',
				organizer = '$organizer',
				contact = '$contact',
				additional = '$additional',
				ticket = '$ticket'
			 	WHERE id = '$id'";
			$this->db_query($q);
		};
		
		list($year,$month,$day) = split("-",date("Y-m-d",$st));
		$link = $this->mk_link(array("section" => "events","year" => $year,"mon" => $month,"day" => $day));
		header("Location: /?$link");
		print "#";
		exit;

		#return $this->mk_my_orb("edit",array("id" => $id));
	}
	
	////
	// !Kuvab eventite nimekirja mingi tunnuse alusel
	// Sellele saaks ette anda mitmesuguseid argumente, a la
	// my events, list by place, list by organizer .. vmt.
	function event_list($args = array())
	{
		extract($args);
		global $rootmenu,$year,$mon,$day,$op;
			
		classload("calendar");
		$cal = new calendar();

		$template = "short.tpl";

		if ($mon && $year)
		{
			if (not($day))
			{
				$day = date("d");
			};
			$start = mktime(0,0,0,$mon,$day,$year);
			$end = mktime(23,59,59,$mon,$day,$year);
			$limits = " AND events3.start >= $start AND events3.start <= $end";
			$this->vars(array("date" => date("d-m-Y",$start)));
		}
		elseif ($op == "view")
		{
			global $id;
			$limits = " AND id = '$id' ";
		}
		elseif ($op == "tomorrow")
		{
			$_tmp = $cal->get_date_range(array("time" => time(),"type" => "day"));
			$start = strtotime("+1 day",$_tmp["start"]);
			list($mon,$day,$year) = split("-",date("m-d-Y",$start));
			$end = strtotime("+1 day",$_tmp["end"]);
			$limits = " AND events3.start >= $start AND events3.start <= $end";
		}
		elseif ($uid)
		{
			// peame hoopis kasutaja kalendrit näitama
			$limits = " AND objects.createdby = '$uid'";
		}
		elseif ($search == true)
		{
			if ($stylemask == 0)
			{
				$stylemask = 32767;
			};
			$limits = " AND (events3.start >= $start) AND (events3.start <= $end) AND ((style & $stylemask) > 0)";
			if ($tpl)
			{
				$template = $tpl;
			};
		}
		else
		{
			$_tmp = $cal->get_date_range(array("time" => time(),"type" => "week"));
			$start = $_tmp["start"];
			$end = $_tmp["end"];

			// kristian tahtis et aint neid n2idatakse mis pole veel alanud
			$limits = " AND events3.start >= ".time()." AND events3.start <= $end";
		};

		$this->read_template($template);

		$cl = CL_EVENT;

		$q = "SELECT objects.*,events3.* FROM objects
			LEFT JOIN events3 ON (objects.oid = events3.id)
			WHERE objects.parent = $rootmenu AND class_id=$cl AND status = 2
			$limits	
			ORDER BY events3.start";

		//print "<pre>";
		//print $q;
		//print "</pre>";
		$this->db_query($q);

		while($row = $this->db_next())
		{
			$cnt++;
			
			$c .= $this->_draw_event($row);
		};

		$p = "";
		if ($search)
		{
			foreach($pagenums as $pnum => $nevermind)
			{
				$this->vars(array(
					"pagenum" => $pnum,
				));

				$pg = ($activepage == $pnum) ? "activepage" : "page";
				$p .= $this->parse($pg);

			}
			$this->vars(array("page" => $p));
		};
			
		$this->vars(array(
				"line" => $c,
				"add_link" => $this->mk_my_orb("add",array()),
				"count" => $cnt));
		
		if ($this->prog_acl("view", PRG_MENUEDIT))
		{
			$adm = $this->parse("adm");
		}
		else
		{
			$adm = "";
		};
		$this->vars(array("adm" => $adm));
		return $this->parse();
	}

	function event_delete($args = array())
	{
		extract($args);

		$this->upd_object(array(
			"oid" => $id,
			"status" => 1,
		));
		$old = $this->get_record("events3","id",$args["id"]);
		list($year,$month,$day) = split("-",date("Y-m-d",$old["start"]));
		$link = $this->mk_link(array("section" => "events","year" => $year,"mon" => $month,"day" => $day));
		header("Location: /?$link");
		print "#";
		exit;
	}

	function _draw_event($args = array())
	{
		$row = $args;
		$this->vars(array(
			"edlink" => $this->mk_my_orb("edit",array("id" => $row["oid"])),
			"dellink" => $this->mk_my_orb("delete",array("id" => $row["oid"])),
		));

		if ($this->prog_acl("view", PRG_MENUEDIT))
		{
			$change = $this->parse("admin");
		}
		else
		{
			$change = "";
		};

		$this->vars(array(
			"name" => $row["name"],
			"start" => $this->time2date($row["start"],3),
			"location" => $row["location"],
			"place" => $this->places[$row["place"]],
			"info" => $row["info"],
			"organizer" => $row["organizer"],
			"time" => $this->time2date($row["start"],4),
			"shorttime" => date("H:i",$row["start"]),
			"contact" => $row["contact"],
			"additional" => $row["additional"],
			"ticket" => $row["ticket"],
			"link_detail" => $this->mk_my_orb("view",array("id" => $row["oid"])),
			"admin" => $change,

		));

		return $this->parse("line");
	}



	function event_search($args = array())
	{
		extract($args);
		load_vcl("date_edit");
               	$start = new date_edit("start");
		$start->set("minute_step",30);
               	$start->configure(array("day" => 1,"month" => 2,"year" => 3));
		list($d,$m,$y) = explode("-",date("d-m-Y"));
		$sx = ($st) ? $st : mktime(0,0,0,$m,$d,$y);
		$start_ed = $start->gen_edit_form("start",$sx);
               	$end = new date_edit("end");
		list($d,$m,$y) = explode("-",date("d-m-Y",strtotime("+1 week")));
		$ex = ($et) ? $et : mktime(23,59,59,$m,$d,$y);
               	$end->configure(array("day" => 1,"month" => 2,"year" => 3));
		$end_ed = $start->gen_edit_form("end",$ex);
		$this->read_template("search.tpl");
		$type = ($type) ? $type : -1;
		$place = ($place) ? $place : -1;
	
		$this->vars(array(
			"type" => $this->picker($type,array_merge(array("0" => "kõik"),$this->types)),
			"place" => $this->picker($place,array_merge(array("0" => "kõik"),$this->places)),
			"start" => $start_ed,
			"end" => $end_ed,
			"reforb" => $this->mk_reforb("do_search",array()),
		));
		return $this->parse();
	}

	function do_search($args = array())
	{
		extract($args);
		$st = mktime(0,0,0,$start["month"],$start["day"],$start["year"]);
		$et = mktime(23,59,59,$end["month"],$end["day"],$end["year"]);
		global $rootmenu;
		$cl = CL_EVENT;
		$type_search = ($type != 0) ? " AND events3.type = '$type' " : "";
		$place_search = ($place != 0) ? " AND events3.place = '$place' " : "";
		$this->read_template("search_results.tpl");
		$q = "SELECT objects.*,events3.* FROM objects
			LEFT JOIN events3 ON (objects.oid = events3.id)
			WHERE objects.parent = $rootmenu AND class_id=$cl AND status = 2
			AND start >= '$st' AND start <= '$et' $type_search $place_search
			ORDER BY events3.start";
		$this->db_query($q);
		$cnt = 0;
		while($row = $this->db_next())
		{
			$cnt++;
			$c .= $this->_draw_event($row);
		};
		$this->vars(array(
			"line" => $c,
			"cnt" => $cnt,
			"date" => (int)$cnt,
		));
		return $this->parse() . $this->event_search(array("st" => $st,"et" => $et,"type" => $type, "place" => $place));
	}

	////
	// !Performs the actual alias parse
	function parse_alias($args = array())
	{
		extract($args);
		global $ext,$baseurl;
		switch($matches[2])
		{
			case "calendar":
				list($start,$end,$calendar) = $this->_draw_calendar(array("oid" => $args["oid"]));
				$retval = $calendar;
				break;

			default:
				// list joonistatakse varem, kui kalender
				$retval = $this->event_list();
				break;
		}
		return $retval;
	}

	////
	// !
	function _draw_calendar($args = array())
	{
		// I don't like this a single bit.
		global $year,$mon,$day;

		$year   = ($year) ? $year : date("Y");
		$mon    = ($mon) ? $mon : date("m");
		$day = ($day) ? $day : date("d");
		
		classload("calendar");
		$cal = new calendar();

		$range = $cal->get_date_range(array(
					"date" => "$day-$mon-$year",
					"type" => "day",
				));

		$_timestamp = time();	
		$ts1 = "$year-$mon";
		$ts2 = date("Y-m",$_timestamp);
		if ($ts1 == $ts2)
		{
			$_tmp = $cal->get_date_range(array("time" => time(),"type" => "month"));
			$start = $_tmp["start"];
			$end = $_tmp["end"];
			$limits = " AND events3.start >= $start AND events3.start <= $end";
			global $rootmenu;
			$cl = CL_EVENT;
			$q = "SELECT objects.*,events3.* FROM objects
				LEFT JOIN events3 ON (objects.oid = events3.id)
				WHERE objects.parent = $rootmenu AND class_id=$cl AND status = 2
				$limits	
				ORDER BY events3.start";

			//print "<pre>";
			//print $q;
			//print "</pre>";
			$this->db_query($q);
			$marked = array();
			while($row = $this->db_next())
			{
				$marked[date("d",$row["start"])] = 1;
			};
		}
		else
		{
			$marked = array();
		};
					
		$calendar = $cal->draw_month(array(
					"year" => $year,
					"mon" => $mon,
					"day" => $day,
					"marked" => $marked,
					"misc" => array("section" => "events"),
		));
	

		$start = $range["start"];
		$end = $range["end"];
		return array($start,$end,$calendar);
	}

	function my_events($args = array())
	{
		if (not(defined("UID")))
		{
			// return, if not logged in. actually, ORB should take care of this check
			return false;
		}
		else
		{
			$this->read_template("table.tpl");
			classload("xml");
			$xml = new xml(array("ctag" => "metadata"));

			$cl = CL_EVENT;
			$uid = UID;
			global $rootmenu;

			$q = "SELECT objects.*,events.* FROM objects
				LEFT JOIN events ON (objects.oid = events.id)
				WHERE objects.parent = $rootmenu AND class_id=$cl AND status > 0
				AND objects.createdby = '$uid'
				ORDER BY events.start";

			$this->db_query($q);
			$c = "";
			while($row = $this->db_next())
			{
				$this->vars(array(
					"name" => ($row["name"]) ? $row["name"] : "(no name)",
					"start" => $this->time2date($row["start"],1),
					"oid" => $row["oid"],
					"active" => ($row["status"] == 2) ? "checked" : "",
					"link_edit" => $this->mk_my_orb("edit",array("id" => $row["oid"])),
				));

				$c .= $this->parse("line");
			};

			$this->vars(array(
				"line" => $c,
				"reforb" => $this->mk_reforb("submit_my_events",array()),
			));
			return $this->parse();
		};
	}

	function submit_my_events($args = array())
	{
		extract($args);
		$cl = CL_EVENT;
		$uid = UID;
		// määrame hetkeks koik kasutaja eventid deaktiivseks
		if ($save)
		{
			$q = "UPDATE objects SET status = 1 WHERE createdby = '$uid' AND class_id=$cl"; 
			$this->db_query($q);
			if (is_array($act))
			{
				$actlist = join(",",$act);
				$q = "UPDATE objects SET status = 2 WHERE createdby = '$uid' AND class_id=$cl AND oid IN ($actlist)";
				$this->db_query($q);
			};	
		}
		elseif ($delete)
		{
			if (is_array($mark))
			{
				$marked = join(",",$mark);
				$q = "UPDATE objects SET status = 0 WHERE createdby = '$uid' AND class_id=$cl AND OID in($marked)";
				$this->db_query($q);
			};
		};
		return $this->mk_my_orb("my_events",array());
	}

	function invite($args = array())
	{
		$this->read_template("invite.tpl");
		if (defined("UID"))
		{
			classload("users");
			$u = new users();
			$udata = $u->get_user_info(UID);
		};
		$this->vars(array(
			"yname" => $udata["First Name: element"] . " " . $udata["Last Name: element"],
			"ymail" => $udata["E-mail"],
			"reforb" => $this->mk_reforb("submit_invite",array("id" => $id)),
		));
		return $this->parse();
	}

	function submit_invite($args = array())
	{
		extract($args);
		classload("xml");
		$xml = new xml(array("ctag" => "metadata"));
		$cl = CL_EVENT;
		$q = "SELECT objects.*,events.* FROM objects
			LEFT JOIN events ON (objects.oid = events.id)
			WHERE oid = '$id' AND class_id=$cl AND status = 2";
		$this->db_query($q);
		$event = $this->db_next();
		$_tmp = $xml->xml_unserialize(array("source" => $event["metadata"]));
		$meta = $_tmp["meta"];
		$subject = "Invitation to $event[name]";
		$to = sprintf("%s <%s>",$fname,$fmail);
		$from = sprintf("%s <%s>",$yname,$ymail);
		$msg = $meta["full"];
		$msg = "$fname has invited you to $event[name], click the following link for more details: http://www.vibe.ee/?class=events&action=view&id=$id\n\n" . $msg;
		mail($to,$subject,$msg,"From: $from");
		print "<script language='javascript'>window.close()</script>";
		exit;
	}

	function event_view($args = array())
	{
		extract($args);
		classload("xml");
		$xml = new xml(array("ctag" => "metadata"));
		$cl = CL_EVENT;
		$q = "SELECT objects.*,events.* FROM objects
			LEFT JOIN events ON (objects.oid = events.id)
			WHERE oid = '$id' AND class_id=$cl AND status = 2";
		$this->db_query($q);
		$event = $this->db_next();
		global $no_menus;
		if ($no_menus)
		{	
			$tpl = "popup.tpl";
		}
		else
		{
			$tpl = "details.tpl";
		};
		$this->read_template($tpl);
		$_tmp = $xml->xml_unserialize(array("source" => $event["metadata"]));
		$meta = $_tmp["meta"];

		$styles = array();	
		foreach($this->styles as $key1 => $val1)
		{
			if ($key1 & $event["style"])
			{
				$styles[] = $val1;
			};
		};

		$this->vars(array(
			"style" => join(", ",$styles),
			"name" => $event["name"],
			"start" => $this->time2date($event["start"],3),
			"end" => $this->time2date($event["start"],10) . " to " . $this->time2date($event["end"],10),
			"place" => $event["place"],
			"city" => $event["city"],
			"address" => $meta["address"],
			"full" => $meta["full"],
			"artist1" => $event["artist1"],
			"art1info" => $meta["art1info"],
			"artist2" => $event["artist2"],
			"art2info" => $meta["art2info"],
			"artist3" => $event["artist3"],
			"art3info" => $meta["art3info"],
			"misc" => $meta["misc"],
			"agelimit" => $meta["agelimit"],
			"price_flyer" => $meta["price_flyer"],
			"price_noflyer" => $meta["price_noflyer"],
			"places" => $meta["places"],
			"price" => ($meta["free"] != 1 ? $meta["price"] : ""),
			"organizer" => $event["organizer"],
			"contact" => $meta["contact"],
			"flyer_url" => $meta["url"],
			"poster" => $event["createdby"],
			"till" => $this->time2date($meta["t_start"],3),
			"printlink" => sprintf(" onClick='javascript:window.open(\"%s\",\"printevent\",\"toolbar=1,menubar=1,scrollbars=1,width=500,height=600\")' ",$this->mk_my_orb("view",array("id" => $id,"no_menus" => 1))),
			"invlink" => sprintf(" onClick='javascript:window.open(\"%s\",\"sendevent\",\"toolbar=0,menubar=0,scrollbars=0,width=370,height=250\")' ",$this->mk_my_orb("invite",array("id" => $id,"no_menus" => 1))),
		));
		if ($meta["presale"])
		{
			$this->vars(array(
				"presale" => $this->parse("presale"),
			));
		};
		$subtplname = ($meta["free"]) ? "free" : "price";
		$subtpl = $this->parse($subtplname);
		$this->vars(array(
			$subtplname => $subtpl,
		));
		if ($meta["url"])
		{
			$this->vars(array(
				"flyerurl" => $this->parse("flyerurl"),
			));	
		};


		$retval = $this->parse();
		return $retval;
	}
};
?>
