<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/events.aw,v 2.3 2001/08/12 23:21:14 kristo Exp $
// events.aw - the sucky sucky version of Vibe events

// sisestamis/muutmisvorm peab nagu praegunegi muutmisvorm,
// koosnema neljast lehest.

// tableistruktuur:
// mille järgi otsida peab saama?
// stiil, aeg, nimi, place, city, organizer, 
// ülejäänud data voiks ju vabalt kokku pakkida ühte välja, 
// xml serializeri abil
global $orb_defs;
$orb_defs["events"] = "xml";

class events extends aw_template {

	function events($args = array())
	{
		$this->tpl_init("events");
		$this->db_init("events");
		$this->styles = array(
			"1" => "House",
			"2" => "Hip-Hop",
			"4" => "Rn-B",
			"8" => "Techno",
			"16" => "Electro",
			"32" => "Trance",
			"64" => "2Step",
			"128" => "DN-B",
			"256" => "Jungle",
			"512" => "Reggae",
			"1024" => "Dub",
			"2048" => "Jazz",
			"4096" => "Pop",
			"8192" => "Euro",
			"16384" => "Other",
		);

		// edimisvormi tabid
		$this->tabs = array(
			"1" => "General info",
			"2" => "Artists info",
			"3" => "Special info",
			"4" => "Ticket info",
		);
	}

	function event_delete($args = array())
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"status" => 1,
		));
		return $this->mk_my_orb("list",array());
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
			$limits = " AND events.start >= $start AND events.start <= $end";
		}
		elseif ($op == "tomorrow")
		{
			$_tmp = $cal->get_date_range(array("time" => time(),"type" => "day"));
			$start = strtotime("+1 day",$_tmp["start"]);
			list($mon,$day,$year) = split("-",date("m-d-Y",$start));
			$end = strtotime("+1 day",$_tmp["end"]);
			$limits = " AND events.start >= $start AND events.start <= $end";
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
			$limits = " AND (events.start >= $start) AND (events.start <= $end) AND ((style & $stylemask) > 0)";
			if ($tpl)
			{
				$template = $tpl;
			};
		}
		else
		{
			$_tmp = $cal->get_date_range(array("time" => time(),"type" => "day"));
			$start = $_tmp["start"];
			$end = $_tmp["end"];
			$limits = " AND events.start >= $start AND events.start <= $end";
		};

		$this->read_template($template);

		classload("xml");
		$xml = new xml(array("ctag" => "metadata"));

		$cl = CL_EVENT;

		$q = "SELECT objects.*,events.* FROM objects
			LEFT JOIN events ON (objects.oid = events.id)
			WHERE objects.parent = $rootmenu AND class_id=$cl AND status = 2
			$limits	
			ORDER BY events.start";

		//print "<pre>";
		//print $q;
		//print "</pre>";
		$this->db_query($q);

		$c = "";
		$cnt = 0;
		$onpage = 10;

		$page = $page - 1;

		$startpage = ($page) * $onpage;
		$endpage = $startpage + $onpage - 1;

		$pagenums = array();

		while($row = $this->db_next())
		{
			$cnt++;
			$pagenum = sprintf("%d",$cnt/$onpage) + 1;
			$pagenums[$pagenum] = 1;
			
			$_tmp = $xml->xml_unserialize(array("source" => $row["metadata"]));
			$meta = $_tmp["meta"];

			$this->vars(array(
				"link_change" => $this->mk_my_orb("edit",array("id" => $row["oid"])),
				"link_delete" => $this->mk_my_orb("delete",array("id" => $row["oid"])),
				"flyer_url" => $meta["url"],
			));


			$styles = array();	
			foreach($this->styles as $key1 => $val1)
			{
				if ($key1 & $row["style"])
				{
					$styles[] = $val1;
				};
			};

			$artist = $row["artist1"] . "<br>";
			$artist .= $row["artist2"] . "<br>";
			$artist .= $row["artist3"];

			if (strlen($meta["url"]) > 3)
			{
				$flyer = $this->parse("flyer");
			}
			else
			{
				$flyer = "";
			};
			
			if ($this->prog_acl("view", PRG_MENUEDIT))
			{
				$change = $this->parse("change");
			}
			else
			{
				$change = "";
			};

			$this->vars(array(
				"name" => $row["name"],
				"date" => $this->time2date($row["start"],4),
				"time" => $this->time2date($row["end"],4),
				"place" => $row["place"],
				"change" => $change,
				"brief" => $meta["brief"],
				"style" => join(", ",$styles),
				"artist" => $artist,
				"ticket" => "",
				"flyer" => $flyer,
				"agelimit" => $meta["agelimit"],
				"link_detail" => $this->mk_my_orb("view",array("id" => $row["oid"])),

			));

			if ($search)
			{
				if ( ($cnt >= $startpage) AND ($cnt <= $endpage) )
				{
					$activepage = $pagenum;
					$c .= $this->parse("line");
				};
			}
			else
			{
				$c .= $this->parse("line");
			};
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
				"pgsub" => ($search) ? $this->parse("pgsub") : "",
				"count" => $cnt));
		return $this->parse();
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
			$obj = $this->get_obj_meta($args["id"]);
			$action = "edit";
			$old = $this->get_record("events","id",$args["id"]);
			$style_checked = array();
			$meta = $obj["meta"]["meta"];

			foreach($this->styles as $key => $val)
			{
				if ($key & $old["style"])
				{
					$style_checked[$key] = true;
				};
			};
		}
		else
		{
			$obj = array();
			$action = "add";
			$old = array();
			$meta = array();
		};

		$this->read_template("head.tpl");
		$page = ( ($page > 0) && ($page < 5) ) ? $page : 1;

		$tab_html = array();
		// draw the head of the event editing form
		foreach($this->tabs as $key => $title)
		{
			$tpl = ($key == $page) ? "active_link" : "link";

			$this->vars(array(
				"title" => $title,
				"link" => $this->mk_my_orb($action,array("page" => $key,"id" => $args["id"])),
			));

			$tab_html[] = $this->parse($tpl);
		};

		$this->vars(array("link" => join(" | ",$tab_html)));	
		$head = $this->parse();

		$foot_tpl = ($page == 4) ? "foot2.tpl" : "foot.tpl";
		$this->read_template($foot_tpl);
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("id" => $args["id"],"page" => $page)),
		));
		
		$foot .= $this->parse();
			
		$this->read_template("page" . $page . ".tpl");

		$c = "";

		if ($page == 1)
		{
			foreach($this->styles as $key => $val)
			{
				$this->vars(array(
					"key" => $key,
					"name" => $val,
					"checked" => ($style_checked[$key]) ? "checked" : "",
				));

				$c .= $this->parse("style");

			};
		};
			
		load_vcl("date_edit");
		if ($page == 1)
		{
                	$start = new date_edit("start");
                	$start->configure(array("day" => 1,"month" => 2,"year" => 3,"hour" => 4,"minute" => 5));
			$old_st = ($old["start"]) ? $old["start"] : time();
			$start_ed = $start->gen_edit_form("start",$old_st);
                
			$end = new date_edit("end");
	       	        $end->configure(array("day" => 1,"month" => 2,"year" => 3,"hour" => 4,"minute" => 5));
			$old_et = ($old["end"]) ? $old["end"] : time();
			$end_ed = $start->gen_edit_form("end",$old_et);
		};

		if ($page == 4)
		{
			$start = new date_edit("t_start");
			$start->configure(array("day" => 1,"month" => 2,"year" => 3));
			$old_st = ($meta["t_start"]) ? $meta["t_start"] : time();
			$start_ed = $start->gen_edit_form("t_start",$old_st);
			
			$end = new date_edit("t_end");
			$end->configure(array("day" => 1,"month" => 2,"year" => 3));
			$old_et = ($meta["t_end"]) ? $meta["t_end"] : time();
			$end_ed = $end->gen_edit_form("t_end",$old_et);
		};

		

		$this->vars(array(
			"name" => $obj["name"],
			"start" => $start_ed,
			"end" => $end_ed,
			"style" => $c,
			"address" => $meta["address"],
			"brief" => $meta["brief"],
			"city" => $old["city"],
			"place" => $old["place"],
			"artist1" => $old["artist1"],
			"artist2" => $old["artist2"],
			"artist3" => $old["artist3"],
			"art1info" => $meta["art1info"],
			"art2info" => $meta["art2info"],
			"art3info" => $meta["art3info"],
			"misc" => $meta["misc"],
			"full" => $meta["full"],
			"organizer" => $old["organizer"],
			"contact" => $meta["contact"],
			"url" => $meta["url"],
			"agelimit" => $meta["agelimit"],
			"price_flyer" => $meta["price_flyer"],
			"price_noflyer" => $meta["price_noflyer"],
			"presale" => ($meta["presale"]) ? "checked" : "",
			"price" => $meta["price"],
			"places" => $meta["places"],
			"free" => ($meta["free"]) ? "checked" : "",
		));

		return $head . $this->parse() . $foot;
	}

	////
	// !Submits an event
	function event_submit($args = array())
	{
		$this->quote($args);

		extract($args);
		$q = "";

		if ($page == 1)
		{
			$styl = 0;
			if (is_array($style))
			{
				foreach($style as $key => $val)
				{
					$styl = $styl | $val;
				};
			};
		};

		if (not($id))
		{
			global $rootmenu;
			$parent = $rootmenu;
			$id = $this->new_object(array(
						"parent" => $parent,
						"class_id" => CL_EVENT,
						"name" => $name,
						"status" => 1,
			));
				
			$st = mktime($start["hour"],$start["minute"],0,$start["month"],
							$start["day"],$start["year"]);
				
			$et = mktime($end["hour"],$end["minute"],0,$end["month"],
							$end["day"],$end["year"]);

			$q = "INSERT INTO events (id,city,start,end,place,artist1,artist2,artist3,organizer,style)
				VALUES($id,'$city',$st,$et,'$place','$artist1','$artist2','$artist3','$organizer','$styl')";
			$this->db_query($q);
				
			$meta = array();
			$meta["address"] = $address;
			$meta["brief"] = $brief;

			$this->set_object_metadata(array(
						"oid" => $id,
						"key" => "meta",
						"value" => $meta,
			));
					
		}
		else
		{

			$meta = $this->get_object_metadata(array(
					"oid" => $id,
					"key" => "meta",
			));


			if ($page == 1)
			{
			
				$this->upd_object(array(
						"oid" => $id,
						"name" => $name,
				));

				$meta["address"] = $address;
				$meta["brief"] = $brief;

				$st = mktime($start["hour"],$start["minute"],0,$start["month"],
							$start["day"],$start["year"]);
				
				$et = mktime($end["hour"],$end["minute"],0,$end["month"],
							$end["day"],$end["year"]);

				$stl = 0;
				if (is_array($style))
				{
					foreach($style as $key => $val)
					{
						$stl = $stl | $val;
					};
				};

	
				$q = "UPDATE events SET city = '$city',place = '$place',
						start = '$st',end = '$et',style = '$stl'
					WHERE id = '$id'";

				

			}
			elseif ($page == 2)
			{
				$q = "UPDATE events SET
					artist1 = '$artist1',
					artist2 = '$artist2',
					artist3 = '$artist3'
					WHERE id = '$id'";

				$meta["art1info"] = $art1info;
				$meta["art2info"] = $art2info;
				$meta["art3info"] = $art3info;
				$meta["misc"] = $misc;
			}
			elseif ($page == 3)
			{
				$meta["full"] = $full;
				$meta["contact"] = $contact;
				$meta["agelimit"] = $agelimit;
				$meta["url"] = $url;
				$q = "UPDATE events SET organizer = '$organizer' WHERE id = '$id'";
			}
			elseif ($page == 4)
			{

				$st = mktime(0,0,0,$t_start["month"],$t_start["day"],$t_start["year"]);
				$et = mktime(23,59,59,$t_end["month"],$t_end["day"],$t_end["year"]);
				
				$meta["price_flyer"] = $price_flyer;
				$meta["price_noflyer"] = $price_noflyer;
				$meta["price"] = $price;
				$meta["presale"] = ($presale) ? 1 : 0;
				$meta["places"] = $places;
				$meta["t_start"] = $st;
				$meta["t_end"] = $et;
				$meta["free"] = ($free) ? 1 : 0;
			};

			if ($q)
			{
				$this->db_query($q);	
			};

			if ($activate)
			{
				// potential security breach possible
				$this->db_query("UPDATE objects SET status = 2 WHERE oid = $id");
			};

			$this->set_object_metadata(array(
					"oid" => $id,
					"key" => "meta",
					"value" => $meta,
			));
		}
		$page++;
		return $this->mk_my_orb("edit",array("page" => $page,"id" => $id));
	}

	function event_search($args = array())
	{
		load_vcl("date_edit");
               	$start = new date_edit("start");
               	$start->configure(array("day" => 1,"month" => 2,"year" => 3,"hour" => 4,"minute" => 5));
		list($d,$m,$y) = explode("-",date("d-m-Y"));
		$sx = mktime(0,0,0,$m,$d,$y);
		$start_ed = $start->gen_edit_form("start",$sx);
               	$end = new date_edit("end");
		list($d,$m,$y) = explode("-",date("d-m-Y",strtotime("+1 week")));
		$ex = mktime(23,59,59,$m,$d,$y);
               	$end->configure(array("day" => 1,"month" => 2,"year" => 3,"hour" => 4,"minute" => 5));
		$end_ed = $start->gen_edit_form("end",$ex);
		$this->read_template("search.tpl");
		foreach($this->styles as $key => $val)
		{
			$this->vars(array(
				"key" => $key,
				"name" => $val,
				"checked" => ($style_checked[$key]) ? "checked" : "",
			));

			$c .= $this->parse("style");

		};
		$this->vars(array(
			"style" => $c,
			"start" => $start_ed,
			"end" => $end_ed,
			"reforb" => $this->mk_reforb("do_search",array()),
		));
		return $this->parse();
	}

	function do_search($args = array())
	{
		extract($args);
		global $search_params;
		if ( (not($mask)) and (not($start)) and (not($end)) )
		{
			extract($search_params);
		};
		$mask = 0;
		if (is_array($style))
		{
			foreach($style as $key => $val)
			{
				$mask = $mask | $val;
			};
		};
		$st = mktime($start["hour"],$start["minute"],0,$start["month"],$start["day"],$start["year"]);
		$et = mktime($end["hour"],$end["minute"],59,$end["month"],$end["day"],$end["year"]);

		// yikes
		global $page;
		if ( $page < 1)
		{
			$page = 1;
		};

		$search_params = array(
			"mask" => $mask,
			"start" => $start,
			"end" => $end,
			"type" => $type,
		);

		session_register("search_params");

		$ret = $this->event_list(array(
			"search" => true,
			"stylemask" => $mask,
			"start" => $st,
			"end" => $et,
			"tpl" => ($type == "list") ? "list.tpl" : "",
			"page" => $page,
		));
		return $ret;
	}
	
	function parse_alias($args = array())
	{
		//print "pa called<bR>";
		//print "<pre>";
		//print_r($args);
		//print "</pre>";
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
				
		$calendar = $cal->draw_month(array(
					"year" => $year,
					"mon" => $mon,
					"day" => $day,
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
		$this->read_template("details.tpl");
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
			"start" => $this->time2date($event["start"],4),
			"end" => $this->time2date($event["end"],4),
			"place" => $event["place"],
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
			"price" => $meta["price"],
			"organizer" => $event["organizer"],
			"contact" => $meta["contact"],
		));

		return $this->parse();
	}
};
?>
