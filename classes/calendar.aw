<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/calendar.aw,v 2.15 2002/09/17 15:58:21 duke Exp $
// Generic calendar class

// php arvab by default, et pühapäev on 0.
// setlocalega saab seda muuta, aga ma pole kindel, et see funktsioon windoze veebiserverites töötab
// niisiis workaround
define(CAL_SUNDAY,7);
// mis päevast nädal algab
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

	function add($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa kalender");
		}
		else
		{
			$this->mk_path($parent,"Lisa kalender");
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
		));
		return $this->parse();
	}

	function change($args = array())
	{
		extract($args);
		$this->read_template("change.tpl");
		$obj = $this->get_object($id);
		$doc = $this->get_object($obj["meta"]["target_document"]);
		$docid = $doc["oid"];
		if ($return_url != "")
                {
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda kalendrit");
		}
		else
		{
			$this->mk_path($obj["parent"], "Muuda kalendrit");
		}

		// if docid is set, figure out a list of all searches inside the document
		if ($docid)
		{
				$almgr = get_instance("aliasmgr");
				$ali = $almgr->get_oo_aliases(array(
						"oid" => $docid,
						"filter" => array(&$this,"_filter_doc_aliases"),
						"modifier" => "aliases.target",
				));
				$searches = array("0" => " -- vali --");
				// for each search alias, I need to figure out the name of the form
				// and the names of all date elements in those forms
				$form_base = get_instance("form_base");
				foreach($ali as $key => $val)
				{
					$fid = $form_base->get_form_for_entry($key);
					$form_base->load($fid);
					$name = $form_base->name;
					$els = $form_base->get_all_elements(array("typematch" => "date"));
					if (is_array($els))
					{
						foreach($els as $ekey => $eval)
						{
							$searches["$fid/$ekey"] = $name . "/" . $eval;
						};
					
					};
				};

		};

		$sel_search = $obj["meta"]["target_form"] . "/" . $obj["meta"]["target_element"];

		load_vcl("date_edit");
                $day_start = new date_edit("day_start");
                $day_start->configure(array("hour" => 1, "minute" => 1));
                
		$day_end = new date_edit("day_end");
                $day_end->configure(array("hour" => 1, "minute" => 1));

		list($d,$m,$y) = explode("-",date("d-m-Y"));
		if (is_array($obj["meta"]["day_start"]))
		{
			$shour = $obj["meta"]["day_start"]["hour"];
			$smin = $obj["meta"]["day_start"]["minute"];
		}
		else
		{
			$shour = $smin = 0;
		};
		
		if (is_array($obj["meta"]["day_end"]))
		{
			$ehour = $obj["meta"]["day_end"]["hour"];
			$emin = $obj["meta"]["day_end"]["minute"];
		}
		else
		{
			$ehour = 23;
			$emin = 59;
		};

		$ds_val = mktime($shour,$min,0,$m,$d,$y);
		$de_val = mktime($ehour,$emin,59,$m,$d,$y);

		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"searches" => $this->picker($sel_search,$searches),
			"search_disabled" => disabled(sizeof($searches) == 1),
			"day_start" => $day_start->gen_edit_form("day_start", $ds_val),
			"day_end" => $day_end->gen_edit_form("day_end", $de_val),
			"docid" => $doc["oid"],
			"docname" => $doc["name"],
			"doc_link" => $this->mk_my_orb("search_document",array("id" => $id, "return_url" => urlencode($return_url))),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
                ));

		return $this->parse();
	}

	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($search_el != 0)
		{
			list($target_form,$target_element) = explode("/",$search_el);
		};

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"target_form" => $target_form,
					"target_element" => $target_element,
					"day_start" => $day_start,
					"day_end" => $day_end,
				),
			));
		}
		else
		{
			// add.tpl does not have fields for metadata, so we don't
			// care about that here.
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_CALENDAR,
			));
		};

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

                return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	function parse_alias($args = array())
	{
		$cal = $this->get_object($args["alias"]["target"]);
		$args = $cal["meta"] + array("id" => $args["oid"],"target" => $cal["oid"]);
		return $this->gen_month($args);
	}

	function search_document($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$this->read_template("search.tpl");
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda kalenderit / Vali dokument");
		}
		else
		{
			$this->mk_path($obj["parent"], "Muuda kalendrit / Vali dokument");
		}

		$table = "";

		if (isset($name))
		{
			$almgr = get_instance("aliasmgr");
			load_vcl("table");
			$t = new aw_table(array("prefix" => "caldocsearch"));
			$t->parse_xml_def($this->cfg["basedir"]."/xml/calendar/search_document.xml");

			$q = sprintf("SELECT oid,name,modified,modifiedby FROM objects WHERE class_id = %d AND name LIKE '%%%s%%' AND lang_id = %d AND site_id = %d AND status = 2",CL_DOCUMENT,$name,aw_global_get("lang_id"),aw_ini_get("site_id"));
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$row["select"] = sprintf("<a href='%s'>Vali see</a>",$this->mk_my_orb("pick_document",array("id" => $id,"return_url" => urlencode($return_url),"docid" => $row["oid"])));
				$t->define_data($row);
			};

			$t->sort_by(array(
				"field" => ($sortby) ? $sortby : "name",
				"sorder" => ($sort_order) ? $sort_order : "asc",
			));

			$table = $t->draw();
		};

		$this->vars(array(
			"table" => $table,
			"name" => $name,
			"reforb" => $this->mk_reforb("search_document", array("no_reforb" => 1,"id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	function _filter_doc_aliases($row = array())
	{
		$retval = false;
		if ($row["class_id"] == CL_FORM_ENTRY)
		{
			$retval[$row["target"]] = ($row["name"]) ? $row["name"] : "nimetu (oid = $row[target])";
		};
		return $retval;
	}

	function pick_document($args = array())
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"metadata" => array("target_document" => $docid),
		));
                return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}


	////
	// !Draws a calendar for one month
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
				// nädal lõppes, lisame selle kalendrisse
				$this->vars(array(
					"cell" => $line,
					"weekorblink" => $this->mk_my_orb("view",array("id" => $id,"ctrl" => $ctrl,"type" => "week","date" => "$day-$mon-$year")),
				));
				$month .= $this->parse("week");
				$line = "";

				// ja saadame päevaloenduri tagasi algusse
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
	// !get_date_range - for backwards compatibility
	function get_date_range($args = array())
	{
		classload("date_calc");
		return get_date_range($args);
	}

	//// Generates a month calendar
	// date (datestamp)(optional) - dd-mm-yyyy
	function gen_month($args = array())
	{
		extract($args);

		// things I want to configure:
		// * which day starts the week (monday or sunday)?
		// * do we show the days for next and previous months
 		//	if there are empty cells at the start or at the end
		//	of the calendar weeks

		// defaults
		global $d;
		$date = $d;

		if (isset($date))
		{
			list($dd,$mm,$yy) = explode("-",$date);
		};

		$month = (isset($mm)) ? $mm : date("n",time()); 
		$year = (isset($yy)) ? $yy : date("Y",time()); 
		// if not set, default to current day
		$day = (isset($dd))? $dd : date("j", time());
		
		$ts = mktime(0,0,0,$month,$day,$year);

		$numdays = date("t",$ts);

		$start = mktime(0,0,0,$month,1,$year);
		$end = mktime(0,0,0,$month,$numdays,$year);

		$start_wday = $this->convert_wday(date("w",$start));
		$end_wday = $this->convert_wday(date("w",$end));

		//$start_day = 1 - ((2 * $start_wday) + 1);
		$start_day = 1 - $start_wday;
		$end_day = $numdays + (6 - $end_wday);

		$tpl = (isset($tpl)) ? $tpl : "small_month.tpl";
		$this->read_template($tpl);

		$_wdays = array('P','E','T','K','N','R','L'); 

		$vector = $this->get_wday_vector();


		foreach($vector as $val)
		{
			$wdays[] = $_wdays[$val];
		};

		// generate the calendar
		$header = "";
		$line = "";
		foreach($wdays as $value)
		{
			$this->vars(array(
				"header_content" => $value,
			));

			$header .= $this->parse("header_cell");
		}

		$content = "";


		$i = 0;
		for ($zz = $start_day; $zz <= $end_day; $zz++)
		{
			$empty = false;
			if (($zz < 1) || ($zz > $numdays))
			{
				$cell = "&nbsp;";
				$empty = true;
				$color = "#FFFFFF";
			}
			elseif ($zz == $day)
			{
				$cell = $zz;
				$color = "#FFCC66";
			}
			else
			{
				$cell = $zz;
				$color = "#FFFFFF";
			}

			if (!$empty)
			{
				$date = "$zz-$month-$year";
				$cell = sprintf("<a href='%s/section=%s/docid=%d/cal=%d/d=%s'>%s</a>",aw_ini_get("baseurl"),aw_global_get("section"),$args["target_document"],$args["target"],$date,$cell);
			};

			$this->vars(array(
				"content" => $cell,
				"bgcolor" => $color,
			));

			$line .= $this->parse("cell");
			
			if ($i >= 6)
			{
				$this->vars(array(
					"cell" => $line,
				));
				$content .= $this->parse("line");
				$line = "";
				$i=0;
			}
			else
			{
				$i++;
			};
		};

		if ($i < 7)
		{
			$this->vars(array(
					"cell" => $line,
			));
			$content .= $this->parse("line");
		};

		$this->vars(array(
			"month_name" => get_lc_month($month),
			"header_cell" => $header,
			"line" => $content,
		));

		return $this->parse();
	}   

	function get_wday_vector()
	{
		if (1)
		{
			return array(1,2,3,4,5,6,0);
		}
		else
		{
			return array(0,1,2,3,4,5,6);
		};
	}

	function convert_wday($day)
	{
		if (1)
		{
			$retval = $day - 1;
			if ($retval == - 1)
			{
				$retval = 6;
			};
		}
		else
		{
			$retval = $day;
		};

		return $retval;
	}

	
};
?>
