<?php
define(C_TYPE_PLACE,0);
define(C_TYPE_EVENT,1);
classload("defs");
class events_user extends aw_template
{
	function events_user()
	{
		$this->init("events");
		$this->tclasses = array(
			C_TYPE_PLACE => LC_EVENTS_USER_PLACETYPE,
			C_TYPE_EVENT => LC_EVENTS_USERADD_EVENTTYPE
		);
		lc_load("definition");
		$this->lc_load("events","lc_events");
	}

	////
	// !Listib kasutajale eventid
	function list_events_user($args)
	{
		extract($args);
		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "events",
		));
		if ($year)
		{
			classload("calendar");
			$cal = new calendar();
			$calendar = $cal->draw_month(array(
				"year" => $year,
				"mon" => $mon,
				"day" => $day,
				"add" => "&op=show_date",
			));
			$this->read_template("list_date.tpl");
			$this->vars(array("calendar" => $calendar));
		}
		elseif ($date)
		{
			if (!is_date($date))
			{
				$date = date("d-m-Y");
			};
			classload("calendar");
			$cal = new calendar();
			$calendar = $cal->draw_week(array(
				"date" => $date,
				"day" => $day,
				"mon" => $mon,
				"add" => "&op=show_week",
			));
			$this->read_template("list_date.tpl");
			$this->vars(array("calendar" => $calendar));
		}
		elseif ($lookfor)
		{
			$this->read_template("search_event.tpl");
			$this->vars(array("lookfor" => $lookfor));
		}
		else
		{
			$this->read_template("list_events.tpl");
			$eline = "";
			$this->_gen_type_list(array("class_id" => CL_EVENT_TYPE));
			reset($this->linear);
			while(list($k,$v) = each($this->linear))
			{
				$this->vars(array(
					"name" => $v,
					"id" => $k,
				));
				$tpl = ($k == $type) ? "active" : "eventline";
				$eline .= $this->parse($tpl);
			};
		};
		$this->_list_events($args);
		$count = 0;
		$t->parse_xml_def($this->cfg["basedir"] . "/xml/events/showevents.xml"); 
		while($row = $this->db_next())
		{
			$count++;
			$this->vars(array(
				"name" => $row["name"],
				"start" => $this->time2date($row["start"],6),
				"pname" => $row["pname"],
				"id"	=> $row["id"],
				"pid"	=> $row["pid"],
			));
			$c .= $this->parse("line");
			$t->define_data($row);
		};
		if ($args["type"])
		{
			$eventtype = $this->get_object($args["type"],CL_EVENT_TYPE);
			$caption = $eventtype["name"];
		}
		else
		{
			$caption = LC_EVENTS_USER_ALLEVENTS;
		};
		$t->define_header($caption);
		$t->sort_by(); 
		$table = $t->draw();	
		$this->vars(array("table" => $table,
			"total" => $count,
		  "caption" => $caption,
		  "eventline" => $eline
		));
		return $this->parse();
	}


	////
	// !Näitab kasutajale eventit
	function show_event_user($args = array())
	{
		$id = $args["id"];
		$event = $this->_get_event(array("id" => $id));
		$this->read_template("show_event.tpl");
		$event["start"] = $this->time2date($row["start"],6);
		$event["end"] = $this->time2date($row["end"],6);
		extract($event);
		if ($description)
		{
			$this->vars(array("description" => $event["description"]));
			$description = $this->parse("DESC");
		}
		if ($contact)
		{
			$this->vars(array("contact" => $event["contact"]));
			$contact = $this->parse("CONTACT");
		}
		if ($url)
		{
			$this->vars(array("url" => $event["url"]));
			$url = $this->parse(LC_EVENTS_USER_URL);
		}
		if ($free == 1)
		{
			$price = $this->parse(LC_EVENTS_USER_FREE);
		}
		else
		{
			$this->vars(array(
				"price" => $event["price"],
				"priceflyer" => $event["priceflyer"]
			));
			$price = $this->parse(LC_EVENTS_USERADD_PRICE);
		};
		if ($flyer)
		{
			$this->vars(array("flyer" => $event["flyer"]));
			$flyer = $this->parse(LC_EVENTS_USERADD_FLYER);
		};
		$this->vars(array(
			"name" => $name,
			"pid" => $pid,
			"pname" => $pname,
			"CONTACT" => $contact,
			"URL"	=> $url,
			"start" => $start,
			"end" => $end,
			"PRICE" => $price,
			"FLYER" => $flyer,
			"priceflyer" => $priceflyer,
			"DESC" => $description,
		));
		return $this->parse();
	}

	function show_location_user($args = array())
	{
		$id = $args["id"];
		$location = $this->_get_place(array("id" => $id));
		$this->_list_events(array("lid" => $id));
		$c = "";
		$this->read_template("show_location.tpl");
		while($row = $this->db_next())
		{
			$this->vars(array(
				"start" => $this->time2date($row["start"],6),
				"name" => $row["name"],
				"id" => $row["id"]
			));
			$c .= $this->parse("line");
		};
		$this->vars($location);
		$this->vars(array("line" => $c));
		return $this->parse();
	}

	////
  // !Lisab uue koha andmebaasi
  function _add_place($args)
  {
		$this->quote($args);
    extract($args);
		$oid = $this->new_object(array(
			"class_id" => CL_LOCATION,
			"name" => $name,
			"parent" => $type,
		));

    $q = "INSERT INTO event_places (id,name,description,address,phone,url)
                    VALUES ('$oid','$name','$description','$address','$phone','$url')";
    $this->db_query($q);
    // siia peab lisama ka kirjed objektitabeli uuendamiseks
	}
 
  ////
  // !Uuendab baasis koha kohta käivat infot
  function _update_place($args)
  {
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,
			"parent" => $type,
		));
		$q = "UPDATE event_places
						SET     name = '$name',
										type = '$type',
										description = '$description',
										address = '$address',
										phone = '$phone',
										url = '$url'
						WHERE id = '$id'";
		$this->db_query($q);
	}

	////
	// !Listib koik kohad id, name paaridena
	function _list_places($arg = array())
	{
		extract($arg);
		$q = "SELECT *,event_places.* FROM objects 
			LEFT JOIN event_places ON (objects.oid = event_places.id)
			WHERE class_id = " . CL_LOCATION . " AND objects.parent = $type";
    $this->db_query($q);
  }

	////
	// !Tagastab info mingi location kohta, joinituna tüüpide tabeliga
	function _get_place($args = array())
	{
		$id = $args["id"];
		$q = "SELECT *
			FROM event_places
			WHERE event_places.id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		return $row;
	}
 
	////
	// !Listib koik tyybid id, name paaridena
	function _list_types($var)
	{
		$q = "SELECT id,name FROM event_types WHERE class=$var";
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			$retval[$row["id"]] = $row["name"];
		};
		return $retval;
	}


	////
	// !Listib koik eventid
	function _list_events($args = array())
	{	
		extract($args);
		$where = "";
		if ($type)
		{
			$where = "WHERE objects.parent = $type";
		}
		elseif($lid)
		{
			$where = "WHERE events.place = '$lid'";
		}
		elseif($lookfor)
		{
			$where = "WHERE events.name LIKE '%$lookfor%'";
		}
		elseif($year)
		{
			if ($day)
			{
				$startday = $day;
				$endday = $day + 1;
				$endmon = $mon;
			}
			else
			{
				$startday = 1;
				$endday = 0;
				$endmon = $mon+1;
			};
			$start = mktime(6,0,0,$mon,$startday,$year);
			$end = mktime(5,59,59,$endmon,$endday,$year);
			$where = "WHERE ((start >= $start) AND (start <= $end))";
		}
		elseif($date)
		{
			classload("calendar");
			$cal = new calendar();
			if ($day && $mon)
			{
				list(,,$year) = explode("-",$date);
				$start = mktime(6,0,0,$mon,$day,$year);
				$end = mktime(5,59,59,$mon,$day+1,$year);
			}
			else
			{
				list(,$start,$end) = $cal->get_week_range(array("date" => $date));
			};
			$where = "WHERE ((start >= $start) AND (start <= $end))";
		}
		$q = "SELECT objects.parent AS type,
			events.*,event_places.name AS pname,
			event_places.id AS pid
			FROM events
			LEFT JOIN objects ON (events.id = objects.oid)
			LEFT JOIN event_places ON (events.place = event_places.id)
			$where
			ORDER BY id";
		$this->db_query($q);
	}

	////
	// !Tagastab kogu info mingi eventi kohta
	function _get_event($args)
	{
		$id = $args["id"];
		$q = "SELECT *,event_places.name AS pname,
				event_places.id AS pid
			FROM events
			LEFT JOIN objects ON (events.id = objects.oid)
			LEFT JOIN event_places ON (events.place = event_places.id)
			WHERE events.id = '$id'";
		$this->db_query($q);
		return $this->db_next();
	}

	////
	// !Lisab uue eventi
	function _add_event($args)
	{
		extract($args);
		$oid = $this->new_object(array(
			"name" => $name,
			"class_id" => CL_EVENT,
			"parent" => $type
		));
    $q = "INSERT INTO events (id,name,type,description,place,url,start,end,contact,price,priceflyer,flyer,
			free,agelimit,reservation,flyeronly)
                        VALUES('$oid','$name','$type','$description','$place','$url','$start','$end','$contact','$price',
				'$priceflyer','$flyer','$free','$agelimit','$reservation','$flyeronly')";
    $this->db_query($q);
	}
 
	////
	// !Uuendab eventi infot
	function _update_event($args)
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,
			"parent" => $type,
		));
		$q = "UPDATE events
						SET     
										name = '$name',
										description = '$description',
										place = '$place',
										start = '$start',
										end = '$end',
										url = '$url',
										contact = '$contact',
										price = '$price',
										priceflyer = '$priceflyer',
				flyer = '$flyer',
				free = '$free',
				agelimit = '$agelimit',
				reservation = '$reservation',
				flyeronly = '$flyeronly'
        WHERE id = '$id'";
    $this->db_query($q);
  }

	 ////
	// !Salvestab tüübi info
	function _update_type($args)
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,
		));
  }
 
	////
	// !Lisab uue tüübi
	function _add_type($args)
	{
		extract($args);
		$this->new_object(array(
			"class_id" => $type,
			"name" => $name,
			"parent" => $parent,
		));
	}

	function _gen_place_list($args = array())
	{
		extract($args);
		$q = "SELECT * FROM objects WHERE class_id = " . CL_LOCATION;
		$this->db_query($q);
		$retval = array();
		while($row = $this->db_next())
		{
			$retval[$row["oid"]] = $row["name"];
		};
		return $retval;
	}

	function _gen_type_list($args)
	{
		extract($args);
		$q = "SELECT * FROM objects WHERE class_id = '$class_id'";
		$this->db_query($q);
		$this->storage = array();
		$this->linear = array();
		$last = 0;
		while($row = $this->db_next())
		{
			$this->storage[$row["parent"]][] = $row;
			if ($args["remember_last"])
			{
				$last = $row["parent"];
			};
		};
		$this->level = -1;
		$this->_show_branch($last);
	}

	function _show_branch($parent)
	{
		$this->level++;
		if (is_array($this->storage[$parent]))
		{
			while(list($k,$v) = each($this->storage[$parent]))
			{
				$id = $v["oid"];
				$name = str_repeat("&nbsp;&nbsp;&nbsp;",$this->level) . $v["name"];
				$this->linear[$id] = $name;
				if (is_array($this->storage[$v["oid"]]))
				{
					$this->_show_branch($v["oid"]);
				}
			};
		}
		$this->level--;
	}	
};
?>