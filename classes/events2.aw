<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/events2.aw,v 2.5 2002/10/30 11:07:28 kristo Exp $
// events2.aw - FormGen based events

class events2 extends aw_template 
{
	function events2($args = array())
	{
		$this->init("");
		lc_load("definition");
		$this->lc_load("events","lc_events");
	}

	////
	// !Recognized tags
	// #event_list# - kuvab eventite nimekirja
	// #event_edit# - kuvab eventi lisamis/muutmisvormi.
	function parse_alias($args = array())
	{
		extract($args);
		list($start,$end,$calendar) = $this->_draw_calendar(array("oid" => $args["oid"]));
		switch($matches[2])
		{
			case "calendar":
				list(,,$replacement) = $this->_draw_calendar(array("oid" => $args["oid"]));
				break;

			default:
				list($start,$end,$calendar) = $this->_draw_calendar(array("oid" => $args["oid"]));
			
				$q = "UPDATE form_12438_entries 
					SET  el_12064 = '$start',el_12065 = '$end'
					WHERE id = '13421'";

				$this->db_query($q);
				
				$f = get_instance("formgen/form");
		
				$lines = $f->show(array(
						"id" => 12438,
						"entry_id" => 13421,
						"op_id" => 12163,
				));
				
				list($start,$end,$calendar) = $this->_draw_calendar(array("oid" => $args["oid"]));

				$retval = $lines;

				$replacement = $retval;
				break;
		};
		return $replacement;
	}

	function _create_boxes($calendar)
	{
		return;
	}

	function _gen_search_form($args = array())
	{
		extract($args);
		$f = get_instance("formgen/form");
				
   	$form = $f->gen_preview(array(
				"id" => 12168,
				"reforb" => $this->mk_reforb("submit_search",array()),
				"form_action" => "/index.".$this->cfg["ext"],
				"entry_id" => $entry_id,
				"extraids" => array("oid" => $args["oid"]),
		));
		return $form;
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

	function submit_search($args = array())
	{
		$f = get_instance("formgen/form");
				
		$retval = "";

		$GLOBALS["year"] = $args[0]["year"];
		$GLOBALS["mon"] = $args[0]["month"];
		$GLOBALS["day"] = $args[0]["day"];

		// see peaks laadima otsinguvormi, ning processima vormist tulnud andmed,
		// lugedes need sisse globaalsest skoobist
		
		$f->process_entry(array("id" => 12168)); 

		$search_form = $this->_gen_search_form(array("entry_id" => $f->entry_id, "oid" => $args["oid"]));
		
		$lines = $f->show(array(
				"id" => 12168,
				"entry_id" => $f->entry_id,
				"op_id" => 12163,
		));

		list($start,$end,$calendar) = $this->_draw_calendar(array("oid" => $args["oid"]));

		$retval = $search_form . $lines . $calendar;
		return $retval;
	}
};
?>
