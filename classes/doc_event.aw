<?php
// deals with those pesky doc_events
class doc_event extends core
{
	function doc_event()
	{
		$this->db_init();
	}

	function _get_event_folders($args = array())
	{
		$calendars = array($args["folder"]);
		$calstring = join(",",$calendars);
		return $calstring;
	}

	////
	// !Retrieves a list of events in the given date range
	// conf(array) - metadata of the planner object we are using.
	// start(timestamp) - start of the period
	// end(timestamp) - end of the period
	// type(type of the range) - day, or something
	// folder(int) - id of the event folder of the calendar - but we can get that from the conf as well
	// id(int) - id of the planner object itself
	function get_events_in_range($args = array())
	{
		extract($args);
		
		$calstring = $this->_get_event_folders($args);
                
		$content_generator = $args["conf"]["content_generator"];
                $generator_class = $generator_method = "";
                list($cl,$met) = explode("/",$content_generator);
                if (isset($cl) && isset($met))
                {
                        $generator_class = $cl;
                        $generator_method = $met;
                };
	
		$q = "SELECT objects2.name AS name, objects2.class_id AS class_id,objects2.metadata AS metadata,
			objects.parent,planner.*,documents.lead,documents.moreinfo,documents.content FROM objects
			LEFT JOIN objects AS objects2 ON (objects.brother_of = objects2.oid)
			LEFT JOIN planner ON (objects.brother_of = planner.id)
			LEFT JOIN documents ON (objects.brother_of = documents.docid)
			WHERE objects.parent IN ($calstring) AND ((start >= '$start') AND (start <= '$end')) AND objects.status != 0
			ORDER BY planner.start";
		$this->db_query($q);
		$results = array();
		$count = $this->num_rows();
		$almgr = get_instance("aliasmgr");
		$next_active = false;
		if (($type == "day") && ($count == 0) && $conf["only_days_with_events"])
		{
			$this->_get_next_event(array(
				"calstring" => $calstring,
				"start" => $start,
			));
			$next_active = true;
			$count = $this->num_rows();

			if ($count == 0)
			{
				$this->_get_prev_event(array(
					"calstring" => $calstring,
					"start" => $start,
				));
			};
		};

		$xstart = 0;

		while($row = $this->db_next())
		{
			$xstart = $row["start"];
			if ($next_active)
			{
				$this->start_date = date("d-m-Y",$row["start"]);
			};
			$row["meta"] = aw_unserialize($row["metadata"]);
			unset($row["metadata"]);

			$gx = date("dmY",$row["start"]);
			$fl = $this->cfg["classes"][$row["class_id"]]["file"];
			if ($fl == "document")
			{
				$row["caption"] = html::href(array(
					"url" => "/" . $row["id"],
					"caption" => $row["name"],
				));
			}
			else
			{
				$row["caption"] = html::href(array(
					"url" => $this->mk_my_orb("view",array("id" => $row["id"]),$fl),
					"caption" => $row["name"],
				));
			};
			$row["title"] = $row["name"];
			if (!empty($generator_class) && !empty($generator_method))
			{
				$this->save_handle();
				$row["lead"] = $this->do_orb_method_call(array(
					"class" => $generator_class,
					"action" => $generator_method,
					"params" => array(
					"id" => $row["id"],
					),
				));
				$row["moreinfo"] = $row["name"] = $row["caption"] = $row["title"] = "";
				$this->restore_handle();
			}
			else
			{
				$almgr->parse_oo_aliases($row["id"],$row["lead"]);
				$almgr->parse_oo_aliases($row["id"],$row["moreinfo"]);
			};
			$results[$gx][] = $row;
		}
		if ($type == "day")
		{
			$this->_get_next_event(array(
				"calstring" => $calstring,
				"start" => $xstart+1,
			));
			$next = $this->db_next();
			if (isset($next["start"]))
			{
				$this->next_event = "/section=" . aw_global_get("section") . "/" . "date=" . date("d-m-Y",$next["start"]);
			};

			$this->_get_prev_event(array(
				"calstring" => $calstring,
				"start" => $xstart-1,
			));
			
			$prev = $this->db_next();
			if (isset($prev["start"]))
			{
				$this->prev_event = "/section=" . aw_global_get("section") . "/" . "date=" . date("d-m-Y",$prev["start"]);
			};
		}
		return $results;
	}

	////
	// !Retrieves the next event for the calendar
	function _get_next_event($args = array())
	{
		extract($args);
		$q = "SELECT name,class_id,parent,metadata,planner.*,documents.lead,documents.moreinfo,documents.content FROM objects LEFT JOIN planner ON (objects.brother_of = planner.id) LEFT JOIN documents ON (objects.brother_of = documents.docid) WHERE objects.status != 0 AND parent IN ($calstring) AND (start >= '$start') ORDER BY planner.start LIMIT 1";
		$this->db_query($q);
	}

	////
	// !Retrieves the previous event for the calendar 
	function _get_prev_event($args = array())
	{
		extract($args);
		$q = "SELECT name,class_id,parent,metadata,planner.*,documents.lead,documents.moreinfo,documents.content FROM objects LEFT JOIN planner ON (objects.brother_of = planner.id) LEFT JOIN documents ON (objects.brother_of = documents.docid) WHERE objects.status != 0 AND parent IN ($calstring) AND (start <= '$start') ORDER BY planner.start DESC LIMIT 1";
		$this->db_query($q);
	}

};
?>
