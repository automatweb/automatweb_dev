<?php
class calendar extends aw_template
{
	function calendar()
	{
		$this->init(array(
			"tpldir" => "calendar",
		));
	}

	function init_calendar($arr)
	{
		// this is the place where I need to calculate the range
		$this->items = array();
	}

	////
	// date - timestamp
	function get_range($arr = array())
	{
		// called from get_property to determine the range of events to be shown
		$viewtype = !empty($arr["viewtype"]) ? $arr["viewtype"] : "month";
		classload("date_calc");
		$range_args = array(
			"type" => $viewtype,
		);
		if (empty($arr["date"]))
		{
			$range_args["time"] = time();
		}
		else
		{
			$range_args["date"] = $arr["date"];
		};
		$range = get_date_range($range_args);
		$range["viewtype"] = $viewtype;
		$this->range = $range;
		return $range;
	}

	// I need methods for adding item AND for drawing
	// timestamp 
	// data - arr
	function add_item($arr)
	{
		// convert timestamp to day, since calendar is usually day based
		$use_date = date("Ymd",$arr["timestamp"]);
		$data = $arr["data"];
		$data["timestamp"] = $arr["timestamp"];
		$this->items[$use_date][] = $data;
	}

	function get_html()
	{
		// let's start with a random view
		switch($this->range["viewtype"])
		{
			case "month":
				$content = $this->draw_month();
				$caption = date("F Y",$this->range["timestamp"]);
				break;

			case "week":
				$content = $this->draw_week();
				$caption = date("d F",$this->range["start"]) . " - " . date("d F",$this->range["end"]);
				break;
	
			default:
				$content = $this->draw_day();
				$caption = date("F d",$this->range["timestamp"]);
		};

		$this->read_template("container.tpl");
		$types = array(
			"day" => "Päev",
			"week" => "Nädal",
			"month" => "Kuu",
		);
		$ts = "";
		foreach($types as $type => $name)
		{
			$this->vars(array(
				"link" => aw_url_change_var("viewtype",$type),
				"text" => $name,
			));
			$ts .= $this->parse(($type == $this->range["viewtype"]) ? "SEL_PAGE" : "PAGE");
		};

		$this->vars(array(
			"PAGE" => $ts,
			"content" => $content,
			"caption" => $caption,
			"prevlink" => aw_url_change_var("date",$this->range["prev"]),
			"nextlink" => aw_url_change_var("date",$this->range["next"]),
		));
		return $this->parse();
	}

	function draw_month()
	{
		$this->read_template("month_view.tpl");
		$rv = "";

		$realstart = ($this->range["start"] - ($this->range["start_wd"] - 1) * 86400);
		$realend = ($this->range["end"] + (7 - $this->range["end_wd"]) * 86400);

		for ($j = $realstart; $j <= $realend; $j = $j + (7*86400))
		{
			for ($i = $j; $i <= $j + (7*86400)-1; $i = $i + 86400)
			{
				$dstamp = date("Ymd",$i);
				$events_for_day = "";
				if (is_array($this->items[$dstamp]))
				{
					$events = $this->items[$dstamp];
					uasort($events,array($this,"__asc_sort"));
					foreach($events as $event)
					{
						$this->vars(array(
							"time" => date("H:i",$event["timestamp"]),
							"name" => $event["name"],
						));
						$events_for_day .= $this->parse("EVENT");
					};
				};
				$this->vars(array(
					"EVENT" => $events_for_day,
					"daynum" => date("j",$i),
					"dayname" => date("F d, Y",$i),
				));
				$rv .= $this->parse("DAY");
			};
			$this->vars(array(
				"DAY" => $rv,
			));
			$rv = "";
			$w .= $this->parse("WEEK");
		};
		$this->vars(array(
			"WEEK" => $w,
		));
		return $this->parse();
	}
	
	function draw_week()
	{
		$this->read_template("week_view.tpl");
		for ($i = $this->range["start"]; $i <= $this->range["end"]; $i = $i + 86400)
		{
			$dstamp = date("Ymd",$i);
			$events_for_day = "";
			if (is_array($this->items[$dstamp]))
			{
				$events = $this->items[$dstamp];
				uasort($events,array($this,"__asc_sort"));
				foreach($events as $event)
				{
					$this->vars(array(
						"time" => date("H:i",$event["timestamp"]),
						"name" => $event["name"],
					));
					$events_for_day .= $this->parse("EVENT");
				};
			};
			$this->vars(array(
				"EVENT" => $events_for_day,
				"daynum" => date("j",$i),
				"dayname" => date("F d, Y",$i),
			));
			$rv .= $this->parse("DAY");
		};
		$this->vars(array(
			"DAY" => $rv,
		));
		return $this->parse();
	}
	
	function draw_day()
	{
		$this->read_template("day_view.tpl");
		$dstamp = date("Ymd",$this->range["start"]);
		$events_for_day = "";
		if (is_array($this->items[$dstamp]))
		{
			$events = $this->items[$dstamp];
			uasort($events,array($this,"__asc_sort"));
			foreach($events as $event)
			{
				$this->vars(array(
					"time" => date("H:i",$event["timestamp"]),
					"name" => $event["name"],
				));
				$events_for_day .= $this->parse("EVENT");
			};
		};
		$this->vars(array(
			"EVENT" => $events_for_day,
			"daynum" => date("j",$this->range["start"]),
			"dayname" => date("F d, Y",$this->range["start"]),
		));
		return $this->parse();
	}


	function __asc_sort($el1,$el2)
	{
		return (int)($el1["timestamp"] - $el2["timestamp"]);
	}

};
?>
