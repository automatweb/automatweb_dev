<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/gantt_chart.aw,v 1.4 2005/02/07 13:18:37 voldemar Exp $
// gantt_chart.aw - Gantti diagramm
/*

@classinfo syslog_type=ST_GANTT_CHART relationmgr=yes

@default table=objects
@default field=meta
@default method=serialize
@default group=general

*/

class gantt_chart extends class_base
{
	var $data = array ();
	var $rows = array ();
	var $navigation = true;

	function gantt_chart ()
	{
		$this->init (array (
			"tpldir" => "gantt_chart",
			"clid" => CL_GANTT_CHART,
		));
	}

	function init_vcl_property ($arr)
	{
		$property = $arr["property"];
		$this->chart_id = $property["name"];
		$property["vcl_inst"] = $this;
		return array($property["name"] => $property);
	}

	// Configure chart
	// Arguments:
	// string chart_id - ...
	// timestamp start - chart start time. defaults to start of week back from current time.
	// int columns - number of divisions in chart (e.g. 7 days for a chart depicting one week). default is 7.
	// int column_length - length of one division in seconds. default is 86400.
	// int chart_width - chart width in pixels. default is 1000.
	// string row_dfn - title for row-titles column. default is "Ressurss".
	// string style - style to use (default| ... ).
	// bool row_anchors - make hyperlinks of row names (not impl. yet).
	// bool bar_anchors - make hyperlinks of bars (not impl. yet).
	// int time_stops - number of time stops at the top
	function configure_chart ($arr)
	{
		$this->start = (int) (empty ($arr["start"]) ? (time () - 302400) : $arr["start"]);
		$this->column_length = (int) (empty ($arr["column_length"]) ? 86400 : $arr["column_length"]);
		$this->columns = (empty ($arr["columns"]) ? range (1, 7) :  range (1, (int) $arr["columns"]));
		$this->chart_width = (int) (empty ($arr["chart_width"]) ? 1000 : $arr["chart_width"]);
		$this->chart_id = empty ($arr["chart_id"]) ? "0" : $arr["chart_id"];
		$this->style = empty ($arr["style"]) ? "default" : $arr["style"];
		$this->row_dfn = empty ($arr["row_dfn"]) ? "Ressurss" : $arr["row_dfn"];
		$this->time_stops = empty ($arr["time_stops"]) ? 8 : $arr["time_stops"];
		$this->end = (int) ($this->start + count ($this->columns) * $this->column_length);
		// $this->row_anchors = $arr["row_anchors"] ? "anchors" : "noanchors";
		// $this->bar_anchors = $arr["bar_anchors"] ? "anchors" : "noanchors";

		$this->scale_quotient = (count ($this->columns) * $this->column_length) / $this->chart_width;
		$this->cell_length = ceil ($this->column_length / $this->scale_quotient);
	}

	// Configure chart navigation
	// Arguments:
	// bool show - if set to "false", navigation won't be shown, default is "true".
	function configure_navigation ($arr)
	{
		$this->navigation = (bool) (empty ($arr["show"]) ? true : $arr["show"]);
	}

	// Adds one row.
	// Arguments:
	// string type - row type data|separator. default is data.
	// string name - identifier for the row
	// string title - title for the row
	// string uri - uri for row title. Applies if row_anchors property is set to true for chart.
	// string target - uri target for row title. Applies if row_anchors property is set to true for chart.
	function add_row ($arr)
	{
		$row_name = $arr["name"];
		$row_title = $arr["title"];
		$row_type = empty ($arr["type"]) ? "data" : $arr["type"];
		$row_title_uri = empty ($arr["uri"]) ? false : $arr["uri"];
		$row_title_uri_target = empty ($arr["target"]) ? "_self" : $arr["target"];

		$this->rows[$row_name] = array (
			"type" => $row_type,
			"name" => $row_name,
			"title" => $row_title,
			"uri" => $row_title_uri,
			"target" => $row_title_uri_target,
		);
	}

	// Defines column. Columns can be defined only after calling configure_chart.
	// Arguments:
	// int col - column number from left, 0 is row definitions column.
	// string title - title for the column.
	// string uri - uri for column title.
	// string target - uri target for column title.
	function define_column ($arr)
	{
		$col = $arr["col"];
		$title = $arr["title"];
		$uri = $arr["uri"];
		$target = empty ($arr["target"]) ? "_self" : $arr["target"];

		$this->columns[($col - 1)] = array (
			"title" => $title,
			"uri" => $uri,
			"target" => $target,
		);
	}

	// Adds one bar/data object to specified row.
	// Arguments:
	// string row - row name to which to add new bar. Required.
	// timestamp start - bar starting place on timeline. Required.
	// int length - bar length in seconds. Required.
	// string title - title for the bar.
	// bool hilight - bar will be hilighted if set to true.
	// string uri - uri for bar hyperlink. Applies if bar_anchors property is set to true for chart.
	// string target - uri target for bar hyperlink. Applies if bar_anchors property is set to true for chart.
	function add_bar ($arr)
	{
		$row = $arr["row"];
		$start = (int) $arr["start"];
		$length = (int) $arr["length"];
		$title = empty ($arr["title"]) ? "" : $arr["title"];
		$hilight = (bool) (empty ($arr["hilight"]) ? false : $arr["hilight"]);
		$uri = empty ($arr["uri"]) ? "#" : $arr["uri"];
		$uri_target = empty ($arr["target"]) ? "_self" : $arr["target"];

		$this->data[$row][$start] = array (
			"start" => $start,
			"length" => $length,
			"title" => $title,
			"hilight" => $hilight,
			"bar_uri" => $uri,
			"bar_uri_target" => $uri_target,
			"row" => $row
		);
	}

	function draw_chart ()
	{
		### parse style
		$this->read_template ("style_" . $this->style . ".tpl");
		$this->vars = array (
			"chart_id" => $this->chart_id,
			"chart_width" => $this->chart_width,
		);
		$style = $this->parse ();

		### create navigation
		// $navigation = "";

		// if ($this->navigation)
		// {
			// $start = (int) ($arr["request"]["gantt_chart_start"] ? $arr["request"]["gantt_chart_start"] : time ());
			// $columns = (int) ($arr["request"]["gantt_chart_length"] ? $arr["request"]["gantt_chart_length"] : 7);
			// $start = ($columns == 7) ? $this->get_week_start ($start) : $start;
			// $period_length = $columns * 86400;
			// $length_nav = array ();
			// $start_nav = array ();

			// for ($days = 1; $days < 8; $days++)
			// {
				// if ($columns == $days)
				// {
					// $length_nav[] = $days;
				// }
				// else
				// {
					// $length_nav[] = html::href (array (
						// "caption" => $days,
						// "url" => aw_url_change_var ("gantt_chart_length", $days),
					// ));
				// }
			// }

			// $start_nav[] = html::href (array (
				// "caption" => "Täna",
				// "url" => aw_url_change_var ("gantt_chart_start", $this->get_week_start (time ())),
			// ));

			// $this->read_template ("navigation_" . $this->style . ".tpl");
			// $this->vars = array (
				// "rewind_uri" => aw_url_change_var ("gantt_chart_start", ($start - $hop_length * $period_length)),
				// "prev_uri" => aw_url_change_var ("gantt_chart_start", ($start - $period_length)),
				// "current_uri" => ,
				// "next_uri" => aw_url_change_var ("gantt_chart_start", ($start + $period_length + 1)),
				// "ffwd_uri" => aw_url_change_var ("gantt_chart_start", ($start + $hop_length * $period_length)),
				// "hop_length" => ,
				// "chart_id" => $this->chart_id,
			// );
			// $navigation = $this->parse ();
		// }

		### compose chart table
		$row_switch = 0;
		$bar_switch = 0;
		$rows = "";
		$this->scale_quotient = (($this->end - $this->start) / $this->chart_width);
		$this->read_template ("chart_" . $this->style . ".tpl");
		$this->sort_data ();

		foreach ($this->rows as $row)
		{
			$row_contents = "";
			$cell_end = $this->start + $this->column_length;
			$pointer = $this->start ;
			$columns = count ($this->columns);

			switch ($row["type"])
			{
				case "data":
					break;

				case "separator":
					$this->vars (array (
						"colspan" => $columns + 1,
					));
					$rows .= trim ($this->parse ("separator_row"));
					continue;
			}

			while ($columns)
			{
				$cell_contents = "";
				$content_length = 0;

				while ($pointer < $cell_end)
				{
					if (!is_array ($this->data[$row["name"]]))
					{
						break;
					}
					else
					{
						$bar = array_shift ($this->data[$row["name"]]);

						if ($bar["start"] >= $cell_end)
						{
							### no bars or no bars left in cell
							array_unshift ($this->data[$row["name"]], $bar);
							break;
						}
					}

					### set bar type
					if (isset ($bar["force_type"]))
					{
						$bar_type = $bar["force_type"];
					}
					else
					{
						$bar_type = $bar["hilight"] ? "hilighted" : $bar_switch;
					}

					### trim bars ending before chart start
					if ($bar["start"] < $this->start)
					{
						if (($bar["start"] + $bar["length"]) > $this->start)
						{
							$bar["length"] = ($bar["start"] + $bar["length"]) - $this->start;
							$bar["start"] = $this->start;
						}
						else
						{
							break;
							continue;
						}
					}

					### split bars longer than free space in one cell
					if ( (($bar["start"] + $bar["length"]) >= $cell_end) and ($bar["start"] < $cell_end) )
					{
						$split_bar = $bar;
						$split_bar["length"] = $bar["length"] - ($cell_end - $bar["start"]);
						$split_bar["start"] = $cell_end;
						$split_bar["force_type"] = $bar_type;
						array_unshift ($this->data[$row["name"]], $split_bar);
						$bar["length"] = $cell_end - $bar["start"];
					}

					### insert preceeding whitespace
					if ( ($bar["start"] > $pointer) and ($bar["start"] < $cell_end) )
					{
						$length = ceil (($bar["start"] - $pointer) / $this->scale_quotient);
						$this->vars (array (
							"length" => $length,
							"baseurl" => $this->cfg["baseurl"],
						));
						$cell_contents .= trim ($this->parse ("MAIN.data_row" . $row_switch . ".data_cell.cell_contents.bar_empty"));
						$pointer += $bar["start"];
						$content_length += $length;
					}

// /* dbg */ if (strstr ($bar["title"], "job: 6978")){ arr ($bar); echo "[".date ("j/m/Y H.i", $bar["start"]) . "] - [".date ("j/m/Y H.i", $bar["start"] + $bar["length"]) . "]"; echo htmlentities ($row_contents);}

					### parse bar
					$length = ceil ($bar["length"] / $this->scale_quotient);
					$this->vars (array (
						"length" => $length,
						"title" => $bar["title"],
						"bar_uri" => $bar["bar_uri"],
						"bar_uri_target" => $bar["bar_uri_target"],
						"baseurl" => $this->cfg["baseurl"],
					));
					$bar_rendered = trim ($this->parse ("MAIN.data_row" . $row_switch . ".data_cell.cell_contents.bar_" . $bar_type));
					$cell_contents .= $bar_rendered;

					### ...
					$pointer = $bar["start"] + $bar["length"];
					$content_length += $length;
					$bar_switch = $bar_switch ? 0 : 1;
				}

				### fill remaining empty space
				if ($content_length < $this->cell_length)
				{
					$length = $this->cell_length - $content_length;
					$this->vars (array (
						"length" => $length,
						"baseurl" => $this->cfg["baseurl"],
					));
					$cell_contents .= trim ($this->parse ("MAIN.data_row" . $row_switch . ".data_cell.cell_contents.bar_empty"));
					$pointer = $cell_end;
				}

				### parse cell
				$this->vars (array (
					"cell_contents" => $cell_contents,
				));
				$row_contents .= trim ($this->parse ("MAIN.data_row" . $row_switch . ".data_cell"));

				### ...
				$columns--;
				$cell_end += $this->column_length;
			}

			### parse row
			$this->vars (array (
				"row_name" => $row["title"],
				"row_uri" => $row["uri"],
				"row_uri_target" => $row["target"],
				"data_cell" => $row_contents,
			));
			$rows .= trim ($this->parse ("data_row" . $row_switch));

			$row_switch = $row_switch ? 0 : 1;
		}

		### parse header
		$header_row = "";

		foreach ($this->columns as $nr => $definition)
		{
			if (is_array ($definition))
			{
				if ($definition["uri"])
				{
					$this->vars (array (
						"title" => ($definition["title"] ? $definition["title"] : ($nr + 1)),
						"uri" => $definition["uri"],
						"target" => $definition["target"],
					));
					$header_row .= $this->parse ("column_head_link");
				}
				else
				{
					$this->vars (array (
						"title" => $definition["title"],
					));
					$header_row .= $this->parse ("column_head");
				}
			}
			else
			{
				$this->vars (array (
					"title" => $nr + 1,
				));
				$header_row .= $this->parse ("column_head");
			}
		}

		$this->_get_timespans();

		### parse table
		$this->vars (array (
			"chart_id" => $this->chart_id,
			"row_dfn" => $this->row_dfn,
			"column_head" => $header_row,
			"data_row0" => $rows,
		));
		$table = $this->parse ();

		### cat all & return
		$chart = $style . $navigation . $table;
		return $chart;
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	/** draws given amount of times to the top of the graph, based on the start/end
	**/
	function _get_timespans()
	{
		$ts = "";
		$step = ($this->end - $this->start) / $this->time_stops;
		for ($i = 0; $i < $this->time_stops; $i++)
		{
			if ($i == ($this->time_stops-1))
			{
				$time = date("d.m.Y / H:i", $this->end);
				$align = "right";
			}
			else
			if ($i == 0)
			{
				$time = date("d.m.Y / H:i", $this->start);
				$align = "left";
			}
			else
			{
				$time = date("d.m.Y", $this->start + ($i*$step) + $step/2);
				$align = "center";
			}
			$this->vars(array(
				"time" => $time,
				"align" => $align
			));
			$ts .= $this->parse("TIMESPAN");
		}

		$this->vars(array(
			"TIMESPAN" => $ts
		));
	}

	function sort_data ()
	{
		foreach ($this->data as $row => $data)
		{
			ksort ($this->data[$row], SORT_NUMERIC);
		}
	}

	function sort_data_old()
	{
		usort($this->data, create_function('$a,$b','if ($a["start"] == $b["start"]) { return 0; } return ($a["start"] > $b["start"] ? 1 : -1);'));
		$tmpr = $this->rows;
		$tmpd = array();
		$this->rows = array();
		foreach($this->data as $bar)
		{
			if (!isset($this->rows[$bar["row"]]))
			{
				$this->rows[$bar["row"]] = $tmpr[$bar["row"]];
			}
			$tmpd[$bar["row"]][$bar["start"]] = $bar;
		}
		$this->data = $tmpd;
	}
}

?>
