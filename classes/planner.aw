<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/planner.aw,v 2.83 2003/01/16 16:36:29 duke Exp $
// planner.aw - kalender
// CL_CAL_EVENT on kalendri event

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property default_view type=select 
	@caption Default vaade

	@property day_start type=time_select
	@caption Päev algab

	@property day_end type=time_select
	@caption Päev lõpeb

	@property tab_add_visible type=checkbox ch_value=1
	@caption "Lisa event" nähtav

	@property tab_day_visible type=checkbox ch_value=1
	@caption "Päev" nähtav

	@property tab_overview_visible type=checkbox ch_value=1
	@caption "Ülevaade" nähtav

	@property tab_week_visible type=checkbox ch_value=1
	@caption "Nädal" nähtav

	@property tab_month_visible type=checkbox ch_value=1
	@caption "Kuu" nähtav

	@property navigator_visible type=checkbox ch_value=1
	@caption Näita navigaatorit

	@property navigator_months type=select 
	@caption Kuud navigaatoris

	@property workdays type=generated generator=callback_get_workday_choices
	@caption Tööpäevad

	@property preview type=text callback=callback_get_preview_link
	@caption Eelvaade

	@classinfo relationmgr=yes

*/

define(WEEK,DAY * 7);
define(REP_DAY,1);
define(REP_WEEK,2);
define(REP_MONTH,3);
define(REP_YEAR,4);
lc_load("calendar");
// Klassi sees me kujutame koiki kuupäevi kujul dd-mm-YYYY (ehk d-m-Y date format)

classload("calendar");
class planner extends class_base
{
	function planner($args = array())
	{
		$this->init(array(
			"tpldir" => "planner",
			"clid" => CL_PLANNER,
		));
		extract($args);
		$this->date = ($date) ? $date : date("d-m-Y");
		lc_load("definition");
		$this->lc_load("planner","lc_planner");
			
		$this->viewtypes = array(
				"0" => "default",
				"1" => "day",
				"2" => "overview",
				"3" => "week",
				"4" => "month",
		);
	}

	function callback_get_rel_types()
        {
                return array(
			"1" => "näita kokkuvõtte paanis",
			"2" => "sündmused teistest kalendritest",
		);
        }

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "default_view":
				$data["options"] = $this->viewtypes;
				break;

			case "navigator_months":
				$data["options"] = array("1" => "1","2" => "2");
				break;
			
		}
		return $retval;
	}

	function callback_get_preview_link($args = array())
	{
		$nodes[] = array(
			"caption" => "Näita",
			"type" => "text",
			"value" => html::href(array(
				"url" => $this->mk_my_orb("view",array("id" => $args["obj"]["oid"])),
				"caption" => "Näita kalendrit",
				"target" => "_blank",
			)),
		);
		return $nodes;
	}

	function callback_get_workday_choices($args = array())
	{
		$tmp = array(
			"name" => "workdays",
			"caption" => "Tööpäevad",
		);
		$obj = $this->get_object($args["oid"]);
		$daynames = explode("|",LC_WEEKDAY);
		for($i = 1; $i <= 7; $i++)
		{
			$tmp["items"][] = array(
				"type" => "checkbox",
				"name" => "workdays[$i]",
				"label" => $daynames[$i],
				"ch_value" => 1,
				"value" => $obj["meta"]["workdays"][$i],
			);
		};
		$retval = array("workdays" => $tmp);
		return $retval;
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
		// this whole XML menu concept sucks, I'm telling ya
		extract($args);
		load_vcl("xmlmenu");
		$xm = new xmlmenu();

		// base for all other navigation links
		$baselink = array("id" => $vars["id"],"ctrl" => $vars["ctrl"],"ctrle" => $vars["ctrle"]);

		// base with date
		$baselink2 = array("id" => $vars["id"],"date" => $vars["date"],"ctrl" => $vars["ctrl"],"ctrle" => $vars["ctrle"]);

		$links = array(
			"view_today" => $this->mk_my_orb("view",$baselink),
			"view_overview" => $this->mk_my_orb("view",array_merge($baselink2,array("type" => "overview"))),
			"view_day" => $this->mk_my_orb("view",array_merge($baselink2,array("type" => "day"))),
			"view_week" => $this->mk_my_orb("view",array_merge($baselink2,array("type" => "week"))),
			"view_month" => $this->mk_my_orb("view",array_merge($baselink2,array("type" => "month"))),
		);

		$xm->vars(array_merge($vars,$links));
		$xm->load_from_files(array(
			"xml" => $this->cfg["basedir"] . "/xml/planner/menucode.xml",
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
		$xml = get_instance("xml",array("ctag" => "event"));
		$block = array(
			"start" => $row["start"],
			"end" => $row["end"],
			"title" => $row["title"],
			"description" => $row["description"],
			"color" => $row["color"],
			"place" => $row["place"],
		);
		$data = $xml->xml_serialize(array("data" => $block));
		
		if ($new)
		{
			$msng = get_instance("messenger");
			$msg_id = $msng->init_message();
		};
		$awf = get_instance("file");
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
	// !used to sort events by start date
	function __x_sort($el1,$el2)
	{
		if ($el1["start"] < $el2["start"])
		{
			return -1;
		}
		elseif ($el1["start"] > $el2["start"])
		{
			return 1;
		}
		else
		{
			return 0;
		};
	}
	
	////
	// !Kuvab kalendri muutmiseks (eelkoige adminnipoolel)
	// id - millist kalendrit näidata
	// disp - vaate tüüp
	// date - millisele kuupäevale keskenduda
	function view($args = array())
	{
		$date = $args["date"];
		$id = $args["id"];
		$ctrl = $args["ctrl"];
		$type = $args["type"];

		$obj = $this->get_object($id);
		$parent = $obj["parent"];

		// kui kuupäeva pole defineeritud, siis defaultime tänasele
		if (!$date)
		{
			$date = date("d-m-Y");
		};

		$this->date = $date;

		if ($type == "day")
		{
			// lame check whether to show today or just day
			// I don't know why those are different anyway
			$act = ($date == date("d-m-Y")) ? "today" : "day";
			$act = "day";
		}
		else
		{
			$act = $type;
		};

		if (!$act)
		{
			$act = "day";
		};
		
		$actlink = $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => $act));
		$this->conf = $obj["meta"];
		$this->actlink = $actlink;
		// generate a menu bar
		// tabpanel really should be in the htmlclient too
                $this->tp = get_instance("vcl/tabpanel");			

		$today = date("d-m-Y");
		
		$this->tp->add_tab(array(
			"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $today,"ctrl" => $ctrl,"type" => "day")),
			"caption" => "Täna",
			"active" => ($act == $today),
		));

		if ($this->conf["tab_day_visible"] == 1)
		{	
			$this->tp->add_tab(array(
				"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => "day")),
				"caption" => "Päev",
				"active" => ($act == "day"),
			));
		};

		if ($this->conf["tab_overview_visible"] == 1)
		{
			$this->tp->add_tab(array(
				"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => "overview")),
				"caption" => "Ülevaade",
				"active" => ($act == "overview"),
			));
		};

		if ($this->conf["tab_week_visible"] == 1)
		{
			$this->tp->add_tab(array(
				"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => "week")),
				"caption" => "Nädal",
				"active" => ($act == "week"),
			));
		};
	
		if ($this->conf["tab_month_visible"] == 1)
		{	
			$this->tp->add_tab(array(
				"link" => $this->mk_my_orb("view",array("id" => $id,"date" => $date,"ctrl" => $ctrl,"type" => "month")),
				"caption" => "Kuu",
				"active" => ($act == "month"),
			));
		};

		if ($this->conf["tab_add_visible"] == 1)
		{
			$this->tp->add_tab(array(
				"link" => $this->mk_my_orb("new",array("parent" => $parent,"date" => $date,"alias_to" => $id,"return_url" => urlencode($actlink)),"cal_event"),
				"caption" => "Lisa sündmus",
				"active" => 0,
			));
		};

		// ctrl is a form controller object
		// if it's set, we get the information about possible ranges from that form

		$this->id = $id;
		$this->ctrl = $ctrl;

		$object = $this->get_object($id);

		//$this->conf = $args["config"];

		if (not($type))
		{
			$type = $types[$this->conf["default_view"]];
		}
		
		if (!$type)
		{
			$type = "day";
		};

		// parent_class ?
		$this->parent_class = $object["class_id"];

		$xdate = $d . $m . $y;

		$this->mk_path($object["parent"],"Kalender $object[name]");

		$_cal = get_instance("calendar",array("tpldir" => "planner"));
		$di = $_cal->get_date_range(array(
			"date" => $date,
			"type" => $type,
		));

		/// XXX: check whether that object has OBJ_HAS_CALENDAR flag
		if ($object["class_id"] == CL_FORM_CHAIN)
		{
			$fch = get_instance("formgen/form_chain");
			$fch->load_chain($object["oid"]);

			$vac_cont = (int)$fch->chain["cal_controller"];

			$q = "SELECT ev_table FROM calendar2forms WHERE cal_id = '$object[oid]'";
			$this->db_query($q);
			$row = $this->db_next();
			$ev_table = $row["ev_table"];


		}
		elseif ($object["class_id"] == CL_FORM)
		{
			$vac_cont = $object["oid"];
			$q = "SELECT ev_table FROM calendar2forms WHERE cal_id = '$object[oid]'";
			$this->db_query($q);
			$row = $this->db_next();
			$ev_table = $row["ev_table"];

		}
		else
		// otherwise just load the plain old event objects
		{
			$events = $this->get_events2(array(
				"start" => $di["start"],
				"end" => $di["end"],
				"folder" => $id,
				"conf" => $this->conf,
			));
		};

		if ($ev_table)
		{
			// retrieve all entries that belong to this calendar.
			$fc = get_instance("formgen/form_calendar");
			$events = $fc->get_events(array(
				"eid" => $object["oid"],
				"start" => $di["start"],
				"end" => $di["end"],
				"eform" => $vac_cont,
				"ctrl" => $ctrl,
			));

			$this->raw_events = $fc->raw_events;
			$this->raw_headers = $fc->raw_headers;
			$this->cached_chain_ids = array();
			$this->ft = get_instance("formgen/form_table");
			$this->table_id = $ev_table;
			// event_display_table can be empty
			if ($this->table_id)
			{
				$this->ft->load_table($this->table_id);
			};
		};
		
		$ddiff1 = $this->get_day_diff($di["start"],$di["end"]);
		
		// tsükkel yle koigi selles perioodis asuvate päevade, et
		// leida ja paigutada events massiivi koik korduvad üritused
		
		list($d,$m,$y) = split("-",$date);


		$this->events = $events;

		$this->ev = get_instance("event");
		// template has to come from calendar config
		$this->ev->actlink = $this->actlink;
		$this->ev->start(array("tpl" => "simple_event.tpl"));

		list($d,$m,$y) = split("-",$date);
		switch($type)
		{
			case "week":
				$content = $this->disp_week(array("events" => $events,"di" => $di,"tpl" => "disp_week.tpl"));
				$caption = sprintf("%s - %s",$this->time2date($di["start"],2),$this->time2date($di["end"],2));
				$start = $date;
				break;
			
			case "month":
				$content = $this->disp_month(array("events" => $events,"di" => $di,"tpl" => "disp_week.tpl"));
				$caption = sprintf("%s - %s",$this->time2date($di["start"],2),$this->time2date($di["end"],2));
				$start = $date;
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
				$this->vars(array(
					"line" => $c,
					"total" => $cnt
				));
				$content = $this->disp_day(array("events" => $events,"di" => $di,"tpl" => "disp_day.tpl"));
				$di["start"] = $di["start"] + 86400;
				$content .= $this->disp_week(array("events" => $events,"di" => $di,"tpl" => "disp_week.tpl"));
				$start = $date;
				break;

			case "day":
				if (is_array($this->conf))
				{
					$tpl = "disp_day2.tpl";
				}
				else
				{
					$tpl = "disp_day.tpl";
				};
				$content = $this->disp_day(array("events" => $events,"di" => $di,"tpl" => $tpl));
				$caption = sprintf("%s - %s",$this->time2date($di["start"],2),$this->time2date($di["end"],2));
				$start = $date;
				break;

		};

		// that is the outer frame
		$navigator = "";

		if ($this->conf["navigator_visible"])
		{
			$this->vars(array("cell" => ""));
			$navi1 = $_cal->draw_month(array(
					"year" => $y,
					"mon" => $m,
					"id" => $id,
					"day" => $d,
					"type" => $type,
					"marked" => $events,
					"use_class" => "planner",
					"ctrl" => $this->ctrl,
					"tpl" =>  "small_month.tpl",
			));

			$this->vars(array("cell" => ""));

			if ($this->conf["navigator_months"] == 2)
			{

				$navi2 = $_cal->draw_month(array(
						"year" => $y,
						"mon" => $m + 1,
						"id" => $id,
						"day" => "666",
						"type" => $type,
						"ctrl" => $this->ctrl,
						"use_class" => "planner",
						"tpl" =>  "small_month.tpl",
				));
			}
			else
			{
				$navi2 = "";
			};

			$this->read_template("planner.tpl");
			$this->vars(array(
				"navi1" => $navi1,
				"navi2" => $navi2,
			));
			$navigator = $this->parse("navigator");
		};

		$summary_pane = $this->mk_summary_pane($this->conf);

		$this->read_template("planner.tpl");
		$this->vars(array(
			"navi1" => "",
			"navi2" => "",
			"summary_header" => $summary_pane,
			"cell" => "",
			"week" => "",
		));

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
		$prev = $this->mk_my_orb("view",array("id" => $id,"type" => $type,"date" => $di["prev"],"id" => $id,"ctrl" => $ctrl,"ctrle" => $ctrle));
		$next = $this->mk_my_orb("view",array("id" => $id,"type" => $type,"date" => $di["next"],"id" => $id,"ctrl" => $ctrl,"ctrle" => $ctrle));
		$this->vars(array(
			"menudef" => $menudef,
			"caption" => $caption,
			"navigator" => $navigator,
			"disp"	=> $disp,
			"id" => $id,
			"content" => $content,
			"mreforb" => $this->mk_reforb("redir",array("day" => $d,"disp" => $disp,"id" => $id,"type" => $type,"ctrl" => $ctrl,"ctrle" => $ctrle)),
			"mlist" => $this->picker($m,$mlist),
			"ylist" => $this->picker($y,$ylist),
			"prev" => $prev,
			"date" => $date,
			"menubar" => $menubar,
			"next" => $next,
		));

		$vars["content"] = $this->parse();
                return $this->tp->get_tabpanel($vars);

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
				$link = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=0,location=0,menubar=0,scrollbars=1,width=400,height=500,resizable=yes\")'","orb.aw?class=objects&action=show&id=$obj[oid]",$obj["oid"]);
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

		if ($this->actlink)
		{
			$ev_link = $this->mk_my_orb("new",array("parent" => $parent,"date" => $date,"alias_to" => $id,"return_url" => urlencode($this->actlink)),"cal_event");
		};

		$e["title"] = $e["name"];
		$e["description"] = $e["comment"];

		$vars = array(
			"color" => $e["color"],
			"time" => date("H:i",$e["start"]) . "-" . date("H:i",$e["end"]),
			"event_link" => $ev_link,
			"id" => $e["id"],
			"title" => ($e["title"]) ? $e["title"] : "(nimetu)",
			"object" => $name,
			"contents" => nl2br($e["description"]),
		);
		return $vars;
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
		// figure out which other calendars we are interested in
		$alias_reltype = new aw_array($conf["alias_reltype"]);
		$calendars = array($folder);
		foreach($alias_reltype->get() as $key => $val)
		{
			if ($val == 2)
			{
				$calendars[] = $key;
			};
		}
		$calstring = join(",",$calendars);
		$q = "SELECT * FROM aliases LEFT JOIN planner ON (aliases.target = planner.id) LEFT JOIN objects ON (aliases.target = objects.oid) WHERE source IN ($calstring) AND ((start >= '$start') OR (start <= '$end'))";
		$this->db_query($q);
		$results = array();
		while($row = $this->db_next())
		{
			$gx = date("dmY",$row["start"]);
			if ($row["source"] != $folder)
			{
				$this->save_handle();
				$target = $this->get_object($row["source"]);
				$row["caption"] = html::href(array(
					"url" => $this->mk_my_orb("view",array("id" => $row["source"])),
					"caption" => $target["name"],
				));
				$this->restore_handle();
			};
			$row["title"] = $name;
			$results[$gx][] = $row;
		}
		return $results;


		/*
		$q = "SELECT *,planner.oid AS aid FROM planner
			LEFT JOIN objects ON (planner.id = objects.oid)
			WHERE objects.status = 2 AND planner.folder = '$folder'
			AND ( (rep_from >= '$start') OR (rep_from <= '$end') OR (rep_until >= '$start')
			OR (start >= '$start') OR (start <= '$end') )
			ORDER BY rep_from";
		$this->db_query($q);
		*/
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
					if ($ts >= $row["rep_from"])
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
		$repeater = get_instance("repeater");
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
		$results = array();
		$timebase = mktime(0,0,0,1,1,2001);
		$start_gdn = sprintf("%d",($start - $timebase) / 86400);
		$end_gdn = sprintf("%d",(($end - $timebase) / 86400) + 1);
		$gdn = $start_gdn;
		$range = range($start_gdn,$end_gdn);	
		while($row = $this->db_next())
		{
			$reps = aw_unserialize($row["repeaters"]);
			$meta = aw_unserialize($row["metadata"]);
			if ($meta["repeaters1"]["own_time"])
			{
				$hour = $meta["repeaters1"]["reptime"]["hour"];
				$minute = $meta["repeaters1"]["reptime"]["minute"];
				list($d,$m,$y) = explode("-",date("d-m-Y",$start));
				$row["start"] = mktime($hour,$minute,0,$m,$d,$y);
			}
			else
			{
				$hour = $minute = 0;
			};
			if (is_array($reps))
			{
				$intersect = array_intersect($reps,$range);
			};
			// always show the event at the day it was added
			$idx = ($index_time) ? $row["start"] : date("dmy",$row["start"]);
			$retval[$idx][] = $row;
			if (is_array($intersect))
			{
				foreach($intersect as $xgdn)
				{
					$ts = mktime($hour,$minute,0,1,$xgdn,2001);
					if ($ts >= $row["rep_from"])
					{
						$gx = ($index_time) ? $ts : date("dmY",$ts);
						$retval[$gx][] = $row;
					};
				};
			};
			$intersect = "";
				
			$gdn++;
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
			$this->ob = get_instance("objects");
		}

		$replacement = $this->ob->show(array("id" => $args["oid"]));
		return $replacement;

	}

	function importfile($args = array())
	{
		$awf = get_instance("file");
		$xml = get_instance("xml");
		extract($args);
		$fdat = $awf->get_file_by_id($id);
		$edata = $xml->xml_unserialize(array("source" => $fdat["content"]));
		$start = $edata["data"]["start"];
		$end = $edata["data"]["end"];
		$title = $edata["data"]["title"];
		$description = $edata["data"]["description"];
		$this->quote($title); $this->quote($description);
		$udata = $this->get_user();
		$uid = aw_global_get("uid");
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
		$retval = $this->mk_my_orb("view",array("type" => $type,"date" => $date,"id" => $id,"ctrl" => $ctrl,"ctrle" => $ctrle),"",false,true);
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
			$where = " uid = '".aw_global_get("uid")."'";
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
		$baseurl = $this->cfg["baseurl"];
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
		$u = get_instance("users");
		$cal_id = $u->get_user_config(array("uid" => aw_global_get("uid"),"key" => "calendar"));
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
		$uid = aw_global_get("uid");
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
		$udata = $this->get_user();
		$q = "SELECT * FROM msg_objects WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$data = unserialize($row["content"]);
		extract($data);
		$uid = aw_global_get("uid");
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
	// !Since we need to show the planner tabs when adding an event we need to wrap
	// those functions from cal_event here
	function new_event($args = array())
	{
		extract($args);
		$menubar = $this->gen_menu(array(
			"activelist" => array("add"),
			"vars" => array("id" => $parent,"date" => $date),
		));
		$ce = get_instance("cal_event");
		$html = $ce->add(array(
			"parent" => $parent,
			"folder" => $parent,
			"date" => $date,
			"time" => $time,
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
		$ce = get_instance("cal_event");
		$html = $ce->change(array(
			"id" => $id,
		));
		$this->mk_path($par_obj["parent"],"Kalender / Muuda sündmust");
		return $menubar . $html;
	}

	////
	// !Embed the repeater editor form inside the planner interface
	function event_repeaters($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$menubar = $this->gen_menu(array(
			"activelist" => array("xxx"),
			"vars" => array("id" => $obj["parent"]),
		));
		$ce = get_instance("cal_event");
		$html = $ce->repeaters(array(
			"id" => $id,
			"cycle" => $cycle,
		));
		$this->mk_path($par_obj["parent"],"Kalender / Muuda sündmust");
		return $menubar . $html;
	}

	////
	// !Deletes a repeater.
	function delete_repeater($args = array())
	{
		extract($args);
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "repeaters" . $cycle,
			"delete_key" => true,
		));

		return $this->mk_my_orb("event_repeaters",array("id" => $id));
	}

	// ??
	function event_reminder($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$menubar = $this->gen_menu(array(
			"activelist" => array("xxx"),
			"vars" => array("id" => $obj["parent"]),
		));
		$ce = get_instance("cal_event");
		$html = $ce->reminder(array(
			"id" => $id,
		));
		$this->mk_path($par_obj["parent"],"Kalender / Muuda sündmust");
		return $menubar . $html;
	}

	// ??
	function event_object_search($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$menubar = $this->gen_menu(array(
			"activelist" => array("xxx"),
			"vars" => array("id" => $obj["parent"]),
		));
		$ce = get_instance("cal_event");
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


	// new display functions

	////
	// !Draws a single day inside the calendar
	// dx - day id 
	// it needs an argument which shows how to draw a single event. or day. or whatever
	function _disp_day($args = array())
	{

		if ($this->table_id)
		{
			$c = $this->raw_headers[$args["dx"]];
			if (not(is_array($this->raw_events[$args["dx"]])))
			{
				return $c;
			}

			$this->ft->start_table($this->table_id);
			foreach($this->raw_events[$args["dx"]] as $row)
			{
				$__ch = $this->ft->get_chains_for_form($row["form_id"]);
				list($_ch,) = each($__ch);
				/*
				print "ch = $_ch<br>";
				print "cid = ";
				print $row["chain_id"];
				print "<br>";
				*/
				$mx = $this->get_object((int)$row[form_id]);
				/*
				print "<pre>";
				print_r($mx["meta"]);
				print "</pre>";
				*/
				$cx = $this->get_object((int)$_ch);

				//if ($mx["meta"]["calendar_chain"])
				if ($cx["flags"] & OBJ_HAS_CALENDAR)
				{
					$cctrl = $row["chain_id"];
				}
				else
				{
					$cctrl = $this->ctrl;
				};
				$this->ft->row_data($row,$row["form_id"],$section,0,$_ch,$cctrl);
			}
			$c .= $this->ft->finalize_table();

		}
		else
		{
			// drop out if this day has no events
			if (not(is_array($this->events[$args["dx"]])))
			{
				return false;
			};
		
			$events = $this->events[$args["dx"]];

			// moo. miks neid eventeid varem ära ei sorteerita?
			// sort the events by start date
			uasort($events,array($this,"__x_sort"));
			$c = "";

	
			foreach($events as $key => $e)
			{
				// draws single cells inside the day
				$c .= $this->ev->draw($e);
			};
		};

		return $c;
	}
	////
	// !Tegelikult joonistamise primitiivid võiksid asuda kalendri klassi
	// ning ma peaksin saama neile kaasa anda callbacke, et too kalendri-
	// joonistaja siis kutsuks välja mingi funktsiooni minu klassist, mis
	// tagastakse joonistajale eventi html-i

	
	////
	// !Displays the month view
	function disp_month($args = array())
	{ 
		extract($args);
		$title = CAL_WEEK;
		list($d1,$m1,$y1) = split("-",date("d-m-Y",$di["start"]));
		list($d2,$m2,$y2) = split("-",date("d-m-Y",$di["end"]));
		$mon1 = get_lc_month($m1);
		$mon2 = get_lc_month($m2);
		
		$caption = sprintf("%d.%s %d - %d.%s %d",$d1,$mon1,$y1,$d2,$mon2,$y2);

		// load the frame for the month
		$this->read_template($tpl);
		$c = "";
		$head = "";
		$cnt = "";
		$d1 = date("d",$di["start"]);
		
		list($mon,$year) = explode("-",date("m-Y",$di["start"]));	
		$_cal = get_instance("calendar");
		list($start_wday,$end_wday) = $_cal->get_weekdays_for_month($mon,$year);

		// ma pean leidma timestambi selle nädala esmaspäevaks, mil minu kuu algab,
		$day = 86400;
		$week = 7 * $day;

		$realstart = ($di["start"] - ($start_wday - 1) * $day);
		
		// ja siis selle nädala pühapäevaks, mil minu kuu lõpeb
		$realend = ($di["end"] + (7 - $end_wday) * $day);

		// ja siis pean tegema tsükli üle kõigi nende nädalate, mis minu kuu sisse
		// jäävad, ning iga nädala jaoks callima disp_week-i
		for ($i = $realstart; $i <= $realend; $i = $i + $week)
		{
			$di = array(
				"start" => $i,
				"end" => $i + $week - 1,
			);

			$c .= $this->disp_week(array("di" => $di,"tpl" => "disp_week.tpl"));

		};

		return $c;

		// finishing, compile the table

		$this->vars(array(
			"header_cell" => $head,
			"content_cell" => $c,
		));

		$this->vars(array(
			"content_row" => $this->parse("content_row"),
			"header" => $this->parse("header"),
		));
		
		$retval =  $this->parse();
		return $retval;
	}

	////
	// !Displays the week view
	function disp_week($args = array())
	{ 
		extract($args);
		$title = CAL_WEEK;
		list($d1,$m1,$y1) = split("-",date("d-m-Y",$di["start"]));
		list($d2,$m2,$y2) = split("-",date("d-m-Y",$di["end"]));
		$mon1 = get_lc_month($m1);
		$mon2 = get_lc_month($m2);
		
		$caption = sprintf("%d.%s %d - %d.%s %d",$d1,$mon1,$y1,$d2,$mon2,$y2);

		// load the frame for the week
		$this->read_template($tpl);
		$c = "";
		$head = "";
		$cnt = "";
		$d1 = date("d",$di["start"]);
		for ($i = 0; $i <= 6; $i++)
		{
			$thisday = strtotime("+$i days",$di["start"]);
			$dx = date("dmY",$thisday);
			$d = date("d",$thisday);
			$w = date("w",$thisday);

			if ($w == 0)
			{
				$w = 7;
			};
			
			if ($this->conf["workday"])
			{
				$draw = false;
				if (isset($this->conf["workday"][$w]))
				{
					$draw = true;
				};
			}
			else
			{
				$draw = true;
			};

			$size = sizeof($this->conf["workday"]);
			if ($size == 0)
			{
				$size = 7;
			};

			$width = (int)(100 / $size);

			if ($draw)
			{
				// draws day
				$c1 = $this->_disp_day(array("dx" => $dx));

				list($day,$mon,$year) = explode("-",date("d-m-Y",$thisday));

				// draw header
				$this->vars(array(
					"cellwidth" => $width . "%",
					"hcell" => strtoupper(substr(get_lc_weekday($w),0,1)) . " " . date("d-M",$thisday),
					"hcell_weekday" => strtoupper(substr(get_lc_weekday($w),0,1)),
					"hcell_date" =>  date("d-M",$thisday),
					"dayorblink" => $this->mk_my_orb("view",array("id" => $this->id,"ctrl" => $this->ctrl, "type" => "day","date" => "$day-$mon-$year")),
					"cell" => $c1,
				));

				$head .= $this->parse("header_cell");
				$c .= $this->parse("content_cell");

				$this->vars(array(
					"event" => $c1,
					"head" => strtoupper(substr(get_lc_weekday($i+1),0,1)),
					"did" => $id,
					"hid" => $args["id"],
					"type" => "week",
					"date" => date("d-m-Y",$thisday),
					"dateinfo" => "$d. " . get_lc_month(date("m",$thisday)),
					"bgcolor" => $bgcolor
				));
			};
		};
		// finishing, compile the table
		$this->vars(array(
			"header_cell" => $head,
			"content_cell" => $c,
		));

		$this->vars(array(
			"content_row" => $this->parse("content_row"),
			"header" => $this->parse("header"),
		));
		
		$retval =  $this->parse();
		return $retval;
	}

	////
	// !Displays the day view
	function disp_day($args = array())
	{
		extract($args);
		// load the frame for the day
		$this->read_template($tpl);
		$c = "";
		$head = "";
		$cnt = "";
		$d1 = date("d",$di["start"]);
		$i = date("w",$di["start"]);
		if ($i == 0)
		{
			$i = 7;
		};

		$thisday = $di["start"];
		$dx = date("dmY",$thisday);
		$d = date("d",$thisday);
		$dm = date("d-m-Y",$thisday);

		$ranges = "";

		if (is_array($this->conf))
		{
			list($d,$m,$y) = explode("-",date("d-m-Y"));
			$this->ts_daystart = mktime(0,0,0,$m,$d,$y) + ($this->conf["day_start"]["hour"] * 3600) + ($this->conf["day_start"]["minute"] * 60);
			$this->ts_dayend = mktime(0,0,0,$m,$d,$y) + ($this->conf["day_end"]["hour"] * 3600) + ($this->conf["day_end"]["minute"] * 60);
			for ($ts = $this->ts_daystart; $ts <= $this->ts_dayend; $ts = $ts + (30*60))
			{
				$min = date("i",$ts);
				$this->vars(array(
					"add_link" => $this->mk_my_orb("new_event",array("parent" => $this->id,"date" => $dm,"time" => date("H:i",$ts))),
					
					"time" => date("H:i",$ts),
				));
				$_tpl = ($min == 30) ? "timestamp2" : "timestamp";
				$ranges .= $this->parse($_tpl);
			};
		};

		// draws day
		$c1 = $this->_disp_day(array("dx" => $dx));

		// draw header
		$this->vars(array(
			"hcell" => strtoupper(substr(get_lc_weekday($i),0,1)) . " " . date("d-M",$thisday),
			"cell" => $c1,
		));

		$this->vars(array(
			"event" => $c1,
			"head" => strtoupper(substr(get_lc_weekday($i),0,1)),
			"did" => $id,
			"hid" => $args["id"],
			"type" => "week",
			"date" => date("d-m-Y",$thisday),
			"dateinfo" => "$d. " . get_lc_month(date("m",$thisday)),
			"bgcolor" => $bgcolor,
			"timestamp" => $ranges,

		));
		
		$this->vars(array(
			"content" => $this->parse("content"),
			"header" => $this->parse("header"),
		));

		return $this->parse();
	}

	function mk_summary_pane($args = array())
	{
		$summary_objects = array();
		$alias_reltype = new aw_array($args["alias_reltype"]);
		foreach($alias_reltype->get() as $key => $val)
		{
			if ($val == 1)
			{
				$summary_objects[] = $key;
			};
		};
		// now cycle over all the summary_objects and generate the previews
		$summary = "";
		$sc = get_instance("search");
		if (sizeof($summary_objects) > 0)
		{
			// right now I only support searches
			$q = sprintf("SELECT oid,name FROM objects WHERE class_id = %d AND status = 2 AND oid IN (%s)",CL_SEARCH,join(",",$summary_objects));
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$this->vars(array(
					"caption" => $row["name"],
					"url" => $this->mk_my_orb("view",array("id" => $row["oid"]),"search"),
				));
				$summary .= $this->parse("summary_header");
				// I need to execute search for each search object
				$results = new aw_array($sc->get_search_results(array("id" => $row["oid"])));
				foreach($results->get() as $obj)
				{
					$use_class = $this->cfg["classes"][$obj["class_id"]]["file"];
					$this->vars(array(
						"caption" => ($obj["name"]) ? $obj["name"] : "(nimetu)",
						"desc" => $obj["comment"],
						"url" => $this->mk_my_orb("view",array("id" => $obj["oid"]),$use_class),
					));
					$summary .= $this->parse("summary_line");
				};


			};
		


		};
		return $summary;


	}


  
};
?>
