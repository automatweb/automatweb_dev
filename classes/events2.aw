<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/events2.aw,v 2.0 2001/07/11 22:54:57 duke Exp $
// events2.aw - FormGen based events
global $orb_defs;
$orb_defs["events2"] = "xml";

class events2 extends aw_template {
	function events2($args = array())
	{
		$this->db_init();
		$this->tpl_init();
		lc_load("definition");
	}

	////
	// !Recognized tags
	// #event_list# - kuvab eventite nimekirja
	// #event_edit# - kuvab eventi lisamis/muutmisvormi.
	function parse_alias($args = array())
	{
		extract($args);
		global $ext,$baseurl;
		switch($matches[2])
		{
			case "add";
				classload("form");
				$f = new form();
				$f->load(11575);
				$form = $f->gen_preview(array(
                                        "id" => 11575,
                                        "reforb" => $this->mk_reforb("submit_add_event",array()),
                                ));
				$replacement = $form;
				
				break;

			default:
			
				list($start,$end,$calendar) = $this->_draw_calendar(array("oid" => $args["oid"]));
				
				$search_form = $this->_gen_search_form(array("oid" => $args["oid"]));

				// õudne
				$q = "UPDATE form_12168_entries 
					SET  el_12064 = '$start'
					WHERE id = '12212'";

				$this->db_query($q);
				
				classload("form");
				$f = new form();
		
				$search_form = $this->_gen_search_form(array("entry_id" => 12212, "oid" => $args["oid"]));
		
				$lines = $f->show(array(
						"id" => 12168,
						"entry_id" => 12212,
						"op_id" => 12163,
				));

				list($start,$end,$calendar) = $this->_draw_calendar(array("oid" => $args["oid"]));

				$retval = $search_form . $lines;

				$replacement = $retval;
				$replacement = "<p><a href='/?section=11642'><font color=white><u>Lisa uus</u></font></a><p>" . $replacement;
				
				$this->blocks[] = array(
						"template" => "LEFT_PROMO",
						"title" => " SEARCH ",
						"content" => " search links<br>here",
				);

				$this->blocks[] = array(
						"template" => "LEFT_PROMO",
						"title" => " CALENDAR ",
						"content" => $calendar,
				);
				
				break;
		};
		return $replacement;
	}

	function _gen_search_form($args = array())
	{
		extract($args);
		global $ext;
		classload("form");
		$f = new form();
				
               	$form = $f->gen_preview(array(
				"id" => 12168,
				"reforb" => $this->mk_reforb("submit_search",array()),
				"form_action" => "/index.$ext",
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
					"misc" => array("section" => 20),
		));
				
		$start = $range["start"];
		$end = $range["end"];
		return array($start,$end,$calendar);
	}

	function submit_add_event($args = array())
	{
		classload("form");
		$args["id"] = 11575;
		$args["parent"] = 11694;
		$f = new form(11575);
		$f->process_entry($args);
		$eid = $f->entry_id;
		global $baseurl;		
		header("Location: $baseurl/index.aw/section=11598");
		exit;
	}

	function submit_search($args = array())
	{
		classload("form");
		$f = new form();
				
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
