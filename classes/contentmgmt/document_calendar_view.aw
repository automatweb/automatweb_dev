<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/document_calendar_view.aw,v 1.3 2008/01/31 12:15:15 kristo Exp $
// document_calendar_view.aw - Dokumentide kalendrivaade 
/*

@classinfo syslog_type=ST_DOCUMENT_CALENDAR_VIEW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property folder type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Dokumentide kataloog

@reltype FOLDER value=1 clid=CL_MENU
@caption Kataloog

*/

class document_calendar_view extends class_base
{
	function document_calendar_view()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/document_calendar_view",
			"clid" => CL_DOCUMENT_CALENDAR_VIEW
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$start = mktime(0,0,0, date("m"), 1, date("Y"));
		if ($_GET["date"] != "")
		{
			list($d, $m, $y) = explode("-", $_GET["date"]);
			$start = mktime(0,0,0, $m, 1, $y);
		}

		$this->read_template("show.tpl");
		$realstart = mktime(0,0,0, date("m", $start), 1, date("Y", $start));
		$realend = mktime(0,0,0, date("m", $start)+1, 1, date("Y", $start));

		classload("core/date/date_calc");
		$realstart = ($start - (convert_wday(date("w",$start)) - 1) * 86400);
		$realend = ($realend + (7 - convert_wday(date("w",$realend))) * 86400);

		$now = date("Ymd");

		$active_day = date("d-m-Y");
		// perhaps the date was in dd-mm-YYYY form?
		if (empty($y))
		{
			list($d,$m,$y) = explode("-",$active_day);
		};
		$act_tm = mktime(0,0,0,$m,$d,$y);
		$act_stamp = date("Ymd",$act_tm);

		// modes
		//  0: day with events
		//  1: day with no events
		//  2: day outside the current range

		// styles
		// minical_cell - usual cell with no events  - day_without_events
		// minical_cellact - usual cell with events  - day_with_events 
		// minical_cellselected - selected (active) cell - day_active
		// minical_cell_today - day_today
		// minical_cell_deact  - deactiv (outside teh current range) - day_deactive

		$style_day_with_events = "minical_cellact";
		$style_day_without_events = "minical_cell";
		$style_day_active = "minical_cellselected";
		$style_day_deactive = "minical_cell_deact";
		$style_day_today = "minical_cell_today";
		$style_title = "minical_table";
		$style_background = "minical_table";

		// fetch all doc times
		$ss = get_instance("contentmgmt/site_show");
		$ss->_init_path_vars();

		$o = obj($arr["id"]);
		if ($this->can("view", $o->prop("folder")))
		{
			$docs = $ss->get_default_document(array("obj" => obj($o->prop("folder"))));
		}
		else
		{
			$docs = $ss->get_default_document(array("obj" => obj(aw_global_get("section"))));
		}
		$awa = new aw_array($docs);
		foreach($awa->get() as $docid)
		{
			$doco = obj($docid);
			$this->overview_items[date("Ymd", $doco->prop("doc_modified"))] = $doco;
			$this->overview_items_oids[date("Ymd", $doco->prop("doc_modified"))][] = $doco->id();
			$this->overview_urls[date("Ymd", $doco->prop("doc_modified"))] = aw_url_change_var(array(
				"day" => date("d", $doco->prop("doc_modified")),
				"month" => date("m", $doco->prop("doc_modified")),
				"year" => date("Y", $doco->prop("doc_modified")),
				"date" => $_GET["date"]
			));
		}

		$done_days = array();
		$j = $realstart;
		$cur_tm = $realstart;
		while($j <= $realend)
		{
			$i = $j;
			$day = "";
			while($i <= $j + (7*86400)-1)
			{
				$reals = $cur_tm;
				$cur_tm += 86400;
				$dstamp = date("Ymd",$reals);

				$done_days[$dstamp] = 1;
				$has_events = $this->overview_items[$dstamp];
				$style = $has_events ? $style_day_with_events : $style_day_without_events;
				if (between($i,$realstart,$realend))
				{
					$mode = 0;
					// if a day has no events and "cell_empty" sub is defined, use it.
					if (empty($has_events))
					{
						$mode = 1;
					};
					if ($now == $dstamp)
					{
						$style = $style_day_today;
					}
					if ($act_stamp == $dstamp)
					{
						$style = $style_day_active;
					}
				}
				else
				{
					// cells outside the current range will always be drawn with
					// this subtemplate
					$mode = 2;
					$style = $style_day_deactive;
				};
				if (!empty($this->overview_urls[$dstamp]))
				{
					$day_url = $this->overview_urls[$dstamp];
				}
				else
				{
					$day_url = aw_ini_get('baseurl').aw_url_change_var(array(
						"event_id" => "",
						"evt_id" => "",
						"date" => date("d-m-Y",$reals),
						// try to unset the sbt variable in url, so i can check in event_search
						// if it is there or not 
						"sbt" => "",
					));
				};

				// cell_empty has class, doesn't have a link, used to show days with no events
				// cell - has class, has link, used to show days with events
				// cell_deact - has a class, doesn't have a link, used to show days outside the current range

				// and that pretty much is it.

				// I set default styles in the container template and let them be overriden
				$caption = date("j",$reals);
				if($mode == 0)
				{
					$link = "<a href='$day_url'>$caption</a>";
				}
				else
				{
					$link = $caption;
				}

				$events_str = ""; 
				if (is_array($this->overview_items_oids[$dstamp]))
				{
					foreach ($this->overview_items_oids[$dstamp] as $event_oid)
					{
						if ($this->can('view', $event_oid))
						{
							$event_obj = new object($event_oid);
							$this->vars_safe(array(
								'event_id' => $event_oid,
								'event_title' => $event_obj->name(),
								'event_content' => $event_obj->prop('content'),
								'event_comment' => $event_obj->comment()
							));
							$events_str .= $this->parse('EVENT');
						}
					}
				}
				$this->vars_safe(array(
					"style" => $style,
					"link" => $link,
					"link2" => $day_url,
					"caption" => $caption,
					"event_title" => "",
					"event_content" => "",
					"event_comment" => "",
					"EVENT" => $events_str
				));

				if($this->is_template("CLICKABLE") && $mode == 0)
				{
					$this->vars_safe(array(
						"link" => $this->parse("CLICKABLE"),
					));
				}
				$day .= $this->parse("DAY");
				$i = $i + 86400;
			};
			$rv = "";
			$this->vars_safe(array(
				"DAY" => $day,
			));
			$week .= $this->parse("WEEK");
			$j = $j + (7*86400);
		};
		// now, how to make those configurable?
		$this->vars_safe(array(
			"WEEK" => $week,
			"style_title" => $style_title,
			"style_background" => $style_background,
			"caption" => locale::get_lc_month(date("m", $start)) . " " . date("y",$start),
			"caption_url" => aw_ini_get('baseurl').aw_url_change_var(array(
				"date" => date("d-m-Y",$start),
			)),
			"prev_date" => date("d-m-Y",mktime(0,0,0,$m-1,$d,$y)),
			"next_date" => date("d-m-Y",mktime(0,0,0,$m+1,$d,$y)),
			"section_id" => aw_global_get("section"),
			"next_url" => aw_ini_get('baseurl').aw_url_change_var(array(
				"date" => date("d-m-Y",mktime(0,0,0,$m+1,$d,$y)),
			)),
			"prev_url" => aw_ini_get('baseurl').aw_url_change_var(array(
				"date" => date("d-m-Y",mktime(0,0,0,$m-1,$d,$y)),
			))
		));
		return $this->parse();
	}
}
?>
