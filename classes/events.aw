<?php
classload("events_user","defs");
global $orb_defs;
$orb_defs["events"] = array(
			"show"	=> array("function" => "show", "params" => array("id")),
			"edit_place"	=> array("function" => "edit_place", "params" => array("id")),
			"add_place"	=> array("function" => "edit_place", "params" => array("type")),
			"submit_place"	=> array("function" => "submit_place", "params" => array()),
			"delete_place"	=> array("function" => "delete_place", "params" => array()),
			"edit_event"	=> array("function" => "edit_event", "params" => array("id")),
			"add_event"	=> array("function" => "edit_event", "params" => array("type")),
			"submit_event"	=> array("function" => "submit_event", "params" => array()),
			"delete_event"	=> array("function" => "delete_event", "params" => array()),
			"add_type"	=> array("function" => "edit_type", "params" => array("type"),"opt" => array("parent")),
			"edit_type"	=> array("function" => "edit_type", "params" => array("id")),
			"submit_type"	=> array("function" => "submit_type", "params" => array()),
			"list_events"	=> array("function" => "list_events", "params" => array(),"opt" => array("sortby","type")),
			"list_places"	=> array("function" => "list_places", "params" => array(),"opt" => array("sortby","type")),
			"list_types"	=> array("function" => "list_types", "params" => array("type"),"opt" => array("id")),
);



class events extends events_user 
{
	//Array tyybi klasside jaoks.
	var $tclasses;

	function events()
	{
		$this->events_user();
		$this->tpl_init("automatweb/events");
	
	}
	
	////
	// !Kuvab vormi olemasoleva koha muutmiseks/uue lisamiseks
	function edit_place($args)
	{
		extract($args);
		$this->read_template("edit_place.tpl");
		
		if ($id)
		{
			$caption = "Muuda kohta";
			$q = "SELECT objects.*,event_places.*
				FROM objects
				LEFT JOIN event_places ON (objects.oid = event_places.id)
				WHERE objects.oid = '$id'";
			$this->db_query($q);
			$data = $this->db_next();
			$data["type"] = $data["parent"];
		}
		else
		{
			$caption = "Uus koht";
			$data["type"] = $args["type"];
		};
		$this->_gen_type_list(array("class_id" => CL_LOCATION_TYPE));

		$this->vars(array(
			"caption" 	=> $caption,
			"id"		=> ($id) ? $id : "Uus",
			"name"		=> $data["name"],
			"description"	=> $data["description"],
			"address"	=> $data["address"],
			"phone"		=> $data["phone"],
			"url"		=> $data["url"],
			"type"		=> $this->picker($data["type"],$this->linear),
			"reforb"	=> $this->mk_reforb("submit_place",array(
						"id" => $data["oid"],
						"type" => $args["type"])),
		));

		return $this->parse();
	}

	////
	// !Salvestab edit_place-ga kuvatud vormi andmed
	function submit_place($args)
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$this->_update_place($args);
		}
		else
		{
			$this->_add_place($args);
		};
		return $this->mk_orb("list_places",array("type" => $type));
	}

	////
	// !Listib koik kohad
	function list_places($args)
	{
		extract($args);
		global $baseurl;
		global $class;
		global $action;
		//$q = "SELECT event_places.*, event_types.name AS ptype 
		//	FROM event_places 
		//	LEFT JOIN event_types ON (event_places.type = event_types.id)
		//	ORDER BY id";
		//$this->db_query($q);
		//$c = "";
		//while($row = $this->db_next())
		//{
		//	$this->vars(array(
		//		"id" => $row["id"],
		//		"name" => $row["name"],
		//		"address" => $row["address"],
		//		"phone" => $row["phone"],
		//		"type"	=> $row["ptype"],
		//		"url" => $row["url"]));
		//	$c .= $this->parse("line");
		//};
		//$this->vars(array("line" => $c));
		$types = $this->list_types(array(
			"class_id" => CL_LOCATION_TYPE,
			"type" => CL_LOCATION_TYPE,
			"tpl" => "list_types2.tpl",
			"action" => "list_places",
			"selected" => $args["type"],
			"sortby" => $args["sortby"],
		));
		if ($type)
		{
			// kohtade nimekirja näitame ainult siis, kui mõni tüüp on selectitud
			global $PHP_SELF;
			$t = new aw_table(array(
				"prefix" => "places",
				"self" => $PHP_SELF,
				"imgurl" => $baseurl . "/automatweb/images",
			));
			$t->set_header_attribs(array(
				"class" => $class, 
				"action" => $action,
				"type" => $type,
			));
			$t->define_header("Kohad",array(
				"orb.aw?class=events&action=add_place&type=$type" => "Lisa uus",
			));
			$t->parse_xml_def($this->basedir . "/xml/events/places.xml");
			extract($args);
			$this->_list_places(array(
				"type" => $type
			));
			while($row = $this->db_next())
			{
				$t->define_data($row);
			};
			$t->sort_by(array("field" => $args["sortby"]));
			$placelist = $t->draw();
		};
		return $types . "<p>&nbsp;</p>" . $placelist;
		#return $this->parse();
	}



	////
	// !Kuvab vormi olemasoleva ürituse muutmiseks/uue lisamiseks
	function edit_event($args)
	{
		extract($args);
		load_vcl("date_edit");
		$date_edit = new date_edit(time());
		$date_edit->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => ""));

		$this->read_template("edit_event.tpl");
		
		if ($id)
		{
			// muudame olemasolevate
			$caption = "Muuda üritust";
			$q = "SELECT * FROM objects LEFT JOIN events ON (objects.oid = events.id) WHERE objects.oid = '$id'";
			$this->db_query($q);
			$data = $this->db_next();
			$start = $data["start"];
			$end = $data["end"];
		}
		else
		{
			// lisame uue
			$caption = "Uus üritus";
			$start = time();
			$end = "+24h";
			$data["type"] = $args["type"];
		};
		$loclist = $this->_gen_place_list();
		$this->_gen_type_list(array("class_id" => CL_EVENT_TYPE));
		$this->vars(array(
			"caption" 	=> $caption,
			"id"		=> ($id) ? $id : "Uus",
			"name"		=> $data["name"],
			"description"	=> $data["description"],
			"contact"	=> $data["contact"],
			"price"		=> $data["price"],
			"priceflyer"	=> $data["priceflyer"],
			"url"		=> $data["url"],
			"free"		=> checked($data["free"]),
			"flyer"		=> $data["flyer"],
			"flyeronly"	=> checked($data["flyeronly"]),
			"agelimit"	=> $data["agelimit"],
			"reservation"	=> $data["reservation"],
			"start"		=> $date_edit->gen_edit_form("start",$start),
			"end"		=> $date_edit->gen_edit_form("end",$end),
			"place"		=> $this->picker($data["place"],$loclist),
			"type"		=> $this->picker($data["type"],$this->linear),
			"reforb"	=> $this->mk_reforb("submit_event",array(
						"id" => $data["id"],
						"type" => $args["type"]
						)),
		));

		return $this->parse();
	}

	////
	// !Salvestab edit_event-iga kuvatud vormi andmed
	function submit_event($args)
	{
		$this->quote($args);
		extract($args);
		extract($start);
		$args["start"] = mktime($hour,$minute,0,$month,$day,$year);
		extract($end);
		$args["end"] = mktime($hour,$minute,0,$month,$day,$year);
		if ($id)
		{
			$this->_update_event($args);
		}
		else
		{
			$this->_add_event($args);
		};
		return $this->mk_orb("list_events",array("type" => $type));
	}

	////
	// !Listib kõik eventid (mingis kohas)
	function list_events($args)
	{
		load_vcl("table");
		global $baseurl;
		global $class;
		global $action;
		extract($args);
		$eventlist = "";
		$types = $this->list_types(array(
			"class_id" => CL_EVENT_TYPE,
			"type" => CL_EVENT_TYPE,
			"tpl" => "list_types2.tpl",
			"action" => "list_events",
			"selected" => $type,
			"sortby" => $args["sortby"],
		));
		if ($type)
		{
			// ürituste nimekirja näitame ainult siis, kui mõni tüüp on selectitud
			global $PHP_SELF;
			$t = new aw_table(array(
				"prefix" => "events",
				"self" => $PHP_SELF,
				"imgurl" => $baseurl . "/automatweb/images",
			));
			$t->set_header_attribs(array(
				"class" => $class,
				"action" => $action,
				"type" => $type,
			));
			$t->define_header("ÜRITUSED",array(
				"orb.aw?class=events&action=add_event&type=$type" => "Lisa uus",
			));
			$t->parse_xml_def($this->basedir . "/xml/events/events.xml");
			extract($args);
			$this->_list_events(array(
				"type" => $type
			));
			while($row = $this->db_next())
			{
				$t->define_data($row);
			};
			$t->sort_by(array("field" => $args["sortby"]));
			$eventlist = $t->draw();
		};
		$this->read_template("list_events.tpl");
		$this->vars(array(
			"types" => $types,
			"eventlist" => $eventlist,
		));
		return $this->parse();
	}
	
	////
	// !Listib mingi konkreetse klassi tüübid
	function list_types($args)
	{
		extract($args);
		switch($type)
		{
			case CL_EVENT_TYPE:
				$typestring = "Ürituste tüübid";
				break;
			case CL_LOCATION_TYPE:
				$typestring = "Kohtade tüübid";
				break;
			default:
				$this->raise_error("Vale tüüp list_types jaoks",true);
		};
		$this->_gen_type_list(array(
			"class_id" => $args["class_id"],
		));
		reset($this->linear);
		$tpl = ($args["tpl"]) ? $args["tpl"] : "list_types.tpl";
		$this->read_template($tpl);
		$link = "";
		while(list($k,$v) = each($this->linear))
		{
			if ($args["action"])
			{
				$link = $this->mk_orb($args["action"],array(
					"sortby" => $args["sortby"],
					"type" => $k,
				));
				$this->vars(array(
					"link" => $link,
					"name" => $v,
				));
				if ($selected == $k)
				{
					$name = $this->parse("active");
				}
				else
				{
					$name = $this->parse("plain");
				};
			}
			else
			{
				$name = $v;
			};
			$this->vars(array("id" => $k,
					"link" => $link,
					"type" => $args["class_id"],
					"name" => $name));
			$c .= $this->parse("line");
		};
		$this->vars(array("line" => $c,
					"type" => $type,
					"caption" => $typestring));
		return $this->parse();
	}
	


	////
	// !Kuvab tüübi muutmise/uue lisamise vormi
	function edit_type($args)
	{
		extract($args);
		$this->read_template("edit_type.tpl");
		if ($id)
		{
			$data = $this->get_object($id);
			$type = $data["class_id"];
			$cif = ($type == CL_EVENT_TYPE) ? "ürituse" : "koha";
			$caption = "Muuda $cif" . "tüüpi";
		}
		else
		{
			$data = array();
			$cif = ($type == CL_EVENT_TYPE) ? "ürituse" : "koha";
			$caption = "Lisa uus $cif" . "tüüp";
		};
		$p = "";
		if ($parent)
		{
			$tp = $this->get_object($parent);
			$this->vars(array("parentname" => $tp["name"]));
			$p = $this->parse("parent");
		};
		$this->vars(array(
			"id" => ($data["oid"]) ? $data["oid"] : "Uus",
			"name" => $data["name"],
			"caption" => $caption,
			"parent" => $p,
			"class"		=> $this->picker($type,$this->tclasses),
			"reforb" => $this->mk_reforb("submit_type",array(
					"id" => $data["oid"],
					"type" => $type,
					"parent" => $parent))
			));
		return $this->parse();
	}

	////
	// !Submitid uue tüübi
	function submit_type($args)
	{
		$this->quote($args);
		extract($args);
		switch($type)
		{
			case CL_EVENT_TYPE:
				$retval = $this->mk_orb("list_events",array());
				break;
			case CL_LOCATION_TYPE:
				$retval = $this->mk_orb("list_places",array());
				break;
			default;
				$this->raise_error("Vale tüüp submit_type jaoks",true);
				break;
		};
		if ($id)
		{
			$this->_update_type($args);
		}
		else
		{
			$this->_add_type($args);
		};
		return $retval;
	}


};

?>
