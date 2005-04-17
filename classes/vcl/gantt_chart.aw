<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/gantt_chart.aw,v 1.15 2005/04/17 20:05:17 voldemar Exp $
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
	// int subdivisions - number of subdivisions in column (e.g. 24 hours for a column depicting one day). default is 1 (meaning subdivision & column coincide).
	// int column_length - length of one division in seconds. default is 86400.
	// int width - chart width in pixels. default is 1000.
	// int row_height - row height in pixels. default is 12.
	// string row_dfn - title for row-titles column. default is "Ressurss".
	// string style - style to use (default| ... ).
	// bool row_anchors - make hyperlinks of row names (not impl. yet).
	// bool bar_anchors - make hyperlinks of bars (not impl. yet).
	// int timespans - number of time stops at the top
	function configure_chart ($arr)
	{
		$this->start = empty ($arr["start"]) ? (time () - 302400) : (int) $arr["start"];
		$this->column_length = empty ($arr["column_length"]) ? 86400 : (int) $arr["column_length"];
		$this->columns = empty ($arr["columns"]) ? range (1, 7) :  range (1, (int) $arr["columns"]);
		$this->subdivisions = empty ($arr["subdivisions"]) ? 1 :  (int) $arr["subdivisions"];
		$this->chart_width = empty ($arr["width"]) ? 1000 : (int) $arr["width"];
		$this->chart_id = empty ($arr["chart_id"]) ? "0" : (string) $arr["chart_id"];
		$this->style = empty ($arr["style"]) ? "default" : (string) $arr["style"];
		$this->row_dfn = empty ($arr["row_dfn"]) ? "Ressurss" : (string) $arr["row_dfn"];
		$this->row_height = empty ($arr["row_height"]) ? 12 : (int) $arr["row_height"];
		$this->timespans = empty ($arr["timespans"]) ? 0 : (int) $arr["timespans"];
		$this->timespan_range = empty ($arr["timespan_range"]) ? 86400 : (int) $arr["timespan_range"];
		$this->end = (int) ($this->start + count ($this->columns) * $this->column_length);
		// $this->row_anchors = $arr["row_anchors"] ? "anchors" : "noanchors";
		// $this->bar_anchors = $arr["bar_anchors"] ? "anchors" : "noanchors";

		$this->pixel_length = ($this->end - $this->start) / $this->chart_width;
		$this->cell_length = (int) ($this->column_length / $this->subdivisions);
		$this->cell_width = ceil ($this->cell_length / $this->pixel_length);
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
	// bool expanded - whether to initially show consequent rows after separator or not. applicable when row type is "separator". default is TRUE.
	// string name - identifier for the row
	// string title - title for the row
	// string uri - uri for row title. Applies if row_anchors property is set to true for chart.
	// string target - uri target for row title. Applies if row_anchors property is set to true for chart.
	function add_row ($arr)
	{
		$row_name = $arr["name"];
		$row_title = $arr["title"];
		$row_type = empty ($arr["type"]) ? "data" : $arr["type"];
		$expanded = (bool) (empty ($arr["expanded"]) ? true : $arr["expanded"]);
		$row_title_uri = empty ($arr["uri"]) ? false : $arr["uri"];
		$row_title_uri_target = empty ($arr["target"]) ? "_self" : $arr["target"];

		$this->rows[$row_name] = array (
			"type" => $row_type,
			"expanded" => $expanded,
			"name" => $row_name,
			"title" => $row_title,
			"uri" => $row_title_uri,
			"target" => $row_title_uri_target,
			"id" => ++$this->row_id_counter,
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
	// string colour - (CSS colour definition, name or rgb value).  default is "silver".
	// bool nostartmark - don't show bar start mark. default is false.
	// string uri - uri for bar hyperlink. Applies if bar_anchors property is set to true for chart.
	// string target - uri target for bar hyperlink. Applies if bar_anchors property is set to true for chart.
	function add_bar ($arr)
	{
		$row = $arr["row"];
		$start = (int) $arr["start"];
		$length = (int) $arr["length"];
		$title = empty ($arr["title"]) ? "" : $arr["title"];
		$colour = empty ($arr["colour"]) ? "silver" : $arr["colour"];
		$nostartmark = empty ($arr["nostartmark"]) ? false : true;
		$uri = empty ($arr["uri"]) ? "#" : $arr["uri"];
		$uri_target = empty ($arr["target"]) ? "_self" : $arr["target"];

		$this->data[$row][] = array (
			"id" => $arr["id"],
			"start" => $start,
			"length" => $length,
			"title" => $title,
			"colour" => $colour,
			"nostartmark" => $nostartmark,
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
			"row_height" => $this->row_height,
			"row_text_height" => ($this->row_height - 1),
		);
		$style = $this->parse ();

		### compose chart table
		$this->sort_data ();
		$rows = "";
		$collapsed = false;
		$this->pending_bars = array ();
		$this->read_template ("chart_" . $this->style . ".tpl");

		foreach ($this->rows as $row)
		{
			$row_contents = "";
			$cell_end = $this->start + $this->cell_length;
			$this->pointer = $this->start;
			$columns = count ($this->columns);

			switch ($row["type"])
			{
				case "data":
					if ($collapsed)
					{
						### go to next row
						continue 2;
					}
					break;

				case "separator":
					if ( ($row["expanded"] == false) or (aw_global_get("aw_gantt_chart_collapsed_" . $row["id"]) == "y") or ($_GET["aw_gantt_chart_collapsed_" . $row["id"]] == "y") )//!!! kust siin _GET asemel v6tta see?
					{
						aw_session_set("aw_gantt_chart_collapsed_" . $row["id"], "y");
						$collapsed = true;
					}
					else
					{
						aw_session_set("aw_gantt_chart_collapsed_" . $row["id"], "n");
						$collapsed = false;
					}

					if ( (aw_global_get("aw_gantt_chart_collapsed_" . $row["id"]) == "n") or ($_GET["aw_gantt_chart_collapsed_" . $row["id"]] == "n") )//!!! kust siin _GET asemel v6tta see?
					{
						aw_session_set("aw_gantt_chart_collapsed_" . $row["id"], "n");
						$collapsed = false;
					}

					$collapse_toggle_value = $collapsed ? "n" : "y";
					$row_state = $collapsed ? "plus" : "minus";
					$expand_collapse_title = $collapsed ? t("Näita") : t("Peida");
					$this->vars (array (
						"expand_collapse_link" => aw_url_change_var ("aw_gantt_chart_collapsed_" . $row["id"], $collapse_toggle_value),
						"expand_collapse_title" => $expand_collapse_title,
						"row_state" => $row_state,
						"row_title" => $row["title"],
						"colspan" => $columns*$this->subdivisions + 1,
					));
					$rows .= trim ($this->parse ("separator_row"));
					continue 2;
			}

			while ($columns)
			{
				$subdivisions = $this->subdivisions;
				$cell_type = "column";

				while ($subdivisions)
				{
					$cell_contents = "";
					$this->content_length = 0;

					while ($this->pointer < $cell_end)
					{
						if (!is_array ($this->data[$row["name"]]))
						{
							break;
						}
						else
						{
							$bar = array_shift ($this->data[$row["name"]]);

							if (!$bar or ((($cell_end - $bar["start"]) / $this->pixel_length) < 0.5))
							{
								### no bars or no bars left in cell
								array_unshift ($this->data[$row["name"]], $bar);
								break;
							}
						}

						### set bar colour
						if (isset ($bar["force_colour"]))
						{
							$bar_type = "continue";
							$bar_colour = $bar["force_colour"];
							unset ($bar["force_colour"]);
						}
						else
						{
							if ($bar["nostartmark"])
							{
								$bar_type = "continue";
							}
							else
							{
								$bar_type = "start";
							}

							$bar_colour = $bar["colour"];
						}

						### trim bars starting/ending before chart start
						if ($bar["start"] < $this->start)
						{
							if ((($bar["start"] + $bar["length"]) - $this->pixel_length) >= $this->start)
							{
								### trim bar ending after chart start
								$bar["length"] = ($bar["start"] + $bar["length"]) - $this->start;
								$bar["start"] = $this->start;
								$bar_type = "continue";
							}
							else
							{
								### bar ends before chart start, go to next bar for current row
								continue;
							}
						}

						### split bars longer than free space in one cell
						if (($bar["start"] + $bar["length"]) > $cell_end)
						{
							if (($bar["start"] + $bar["length"] - $this->pixel_length) >= $cell_end)
							{
								### push overflow to next cell
								$split_bar = $bar;
								$split_bar["length"] = $bar["length"] - ($cell_end - $bar["start"]);
								$split_bar["start"] = $cell_end;
								$split_bar["force_colour"] = $bar_colour;
								array_unshift ($this->data[$row["name"]], $split_bar);
							}

							### set length to fill rest of the cell
							$length = $cell_end - $bar["start"];
							$remainder = ($cell_end - $bar["start"]) % $this->pixel_length;
							$bar["length"] = $remainder ? ($length + $this->pixel_length) : $length;
						}

						### parse bar
						if ($bar["length"] < $this->pixel_length)
						{
							### leave decision for bars that don't cross to next pixel to later
							$this->pending_bars[] = $bar;
						}
						else
						{
							$pending_start = false;
							$pending_length = 0;

							while ($this->pending_bars)
							{
								$pending_bar = array_shift ($this->pending_bars);
								$pending_length += $pending_bar["length"];

								if ($pending_start !== false)
								{
									$pending_start = $pending_bar["start"];
								}

								if ($pending_length >= $this->pixel_length)
								{
									$pending_bar["start"] = $pending_start;
									$pending_bar["length"] = $this->pixel_length;
									$cell_contents .= $this->draw_bar ($pending_bar, $cell_type, $bar_type, $bar_colour);
									$pending_start = false;
									$pending_length = 0;
								}
							}

							$cell_contents .= $this->draw_bar ($bar, $cell_type, $bar_type, $bar_colour);
						}
					}

					### fill remaining empty space
					if ($this->content_length < $this->cell_width)
					{
						$length = $this->cell_width - $this->content_length;
						$this->vars (array (
							"length" => $length,
							"baseurl" => $this->cfg["baseurl"],
						));
						$cell_contents .= trim ($this->parse ("MAIN.data_row.data_cell_" . $cell_type . ".cell_contents.bar_empty"));
						$this->pointer = $cell_end;
					}

					### parse cell
					$this->vars (array (
						"cell_contents" => $cell_contents,
					));
					$row_contents .= trim ($this->parse ("MAIN.data_row.data_cell_" . $cell_type));

					### ...
					$cell_end += $this->cell_length;
					$cell_type = "subdivision";
					$subdivisions--;
				}

				$columns--;
			}

			### parse row
			$this->vars (array (
				"row_name" => $row["title"],
				"row_uri" => $row["uri"],
				"row_uri_target" => $row["target"],
				"data_cell_" . $cell_type => $row_contents,
			));
			$rows .= trim ($this->parse ("data_row"));
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
						"column_width" => $this->cell_width,
						"subdivisions" => $this->subdivisions,
					));
					$header_row .= $this->parse ("column_head_link");
				}
				else
				{
					$this->vars (array (
						"title" => $definition["title"],
						"column_width" => $this->cell_width,
						"subdivisions" => $this->subdivisions,
					));
					$header_row .= $this->parse ("column_head");
				}
			}
			else
			{
				$this->vars (array (
					"title" => $nr + 1,
					"column_width" => $this->cell_width,
				));
				$header_row .= $this->parse ("column_head");
			}
		}

		if ($this->timespans)
		{
			$timespans = $this->get_timespans();
		}
		else
		{
			$timespans = "";
		}

		### parse table
		$this->vars (array (
			"chart_id" => $this->chart_id,
			"row_dfn" => $this->row_dfn,
			"row_dfn_span" => ($this->timespans ? 2 : 1),
			"columns" => count ($this->columns) * $this->subdivisions,
			"subdivision_row" => $timespans,
			"column_head" => $header_row,
			"data_row" => $rows,
		));
		$table = $this->parse ();

		### cat all & return
		$chart = $style . $navigation . $table;
		return $chart;
	}

	function draw_bar ($bar, $cell_type, $bar_type, $bar_colour)
	{
		$drawn_content = "";

		### insert preceeding whitespace
		if ($bar["start"] >= ($this->pointer + $this->pixel_length))
		{
			$length = (int) floor (($bar["start"] - $this->pointer) / $this->pixel_length);
			$this->vars (array (
				"length" => $length,
				"baseurl" => $this->cfg["baseurl"],
			));
			$drawn_content .= trim ($this->parse ("MAIN.data_row.data_cell_" . $cell_type . ".cell_contents.bar_empty"));
			$this->pointer += $length * $this->pixel_length;
			$this->content_length += $length;
		}

		### parse bar
		$length = (int) floor ($bar["length"] / $this->pixel_length);
		$this->vars (array (
			"length" => $length,
			"bar_colour" => $bar_colour,
			"title" => $bar["title"],
			"bar_uri" => $bar["bar_uri"],
			"bar_uri_target" => $bar["bar_uri_target"],
			"baseurl" => $this->cfg["baseurl"],
		));
		$drawn_content .= trim ($this->parse ("MAIN.data_row.data_cell_" . $cell_type . ".cell_contents.bar_normal_" . $bar_type));

		### ...
		$this->pointer += $length * $this->pixel_length;
		$this->content_length += $length;
		return $drawn_content;
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	// alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$this_object = new object($arr["id"]);
		return $this->draw_chart();
	}

	// draws given amount of times to the top of the graph, based on the start/end
	function get_timespans()
	{
		if (!$this->timespans)
		{
			return "";
		}

		$ts = "";
		$step = $this->timespan_range / $this->timespans;
		$time = 0;

		foreach ($this->columns as $column)
		{
			$divisions = "";

			for ($i = 0; $i < $this->timespans; $i++)
			{
				$division = date("H:i", $this->start + $time);
				$align = "left";
				$this->vars(array(
					"time" => $division,
					"align" => $align
				));
				$divisions .= $this->parse("subdivision");
				$time += $step;
			}

			$this->vars(array(
				"subdivision" => $divisions,
			));
			$ts .= $this->parse("subdivision_head");
		}

		$this->vars(array(
			"subdivision_head" => $ts
		));
		$ts = $this->parse("subdivision_row");
		return $ts;
	}

	function sort_data ()
	{
		foreach ($this->data as $row => $data)
		{
			### sort bars
			usort ($this->data[$row], array ($this, "bar_start_sort"));

			### filter bars with same start time
			$same_start_key = false;

			while (isset($this->data[$row][$key]))
			{
				if ($this->data[$row][$key]["start"] == $this->data[$row][$key + 1]["start"])
				{
					if ($this->data[$row][$key]["length"] == $this->data[$row][$key + 1]["length"])
					{
						unset ($this->data[$row][$key]);
					}
					else
					{ ### show shorter bars upon longer
						$same_start_key = $key + 1;
						$last_end = $this->data[$row][$key]["start"] + $this->data[$row][$key]["length"];

						while ($this->data[$row][$key]["start"] == $this->data[$row][$same_start_key]["start"])
						{
							$start = $this->data[$row][$same_start_key]["start"];
							$length = $this->data[$row][$same_start_key]["length"];
							$this->data[$row][$same_start_key]["length"] = $start + $length - $last_end;
							$this->data[$row][$same_start_key]["start"] = $last_end;
							$this->data[$row][$same_start_key]["nostartmark"] = true;
							$last_end = $start + $length;
							$same_start_key++;
						}
					}
				}

				$key++;
			}

			if ($same_start_key !== false)
			{
				### sort bars again
				usort ($this->data[$row], array ($this, "bar_start_sort"));
			}

			### filter overlaps
			$key = 0;

			while (isset($this->data[$row][$key]))
			{
// /* dbg */ if ($this->data[$row][$key]["id"] == 8580) { $this->mrpdbg = 1;}

				$key2 = $key + 1;
				$overlap_end = NULL;
				$overlap_start = NULL;
				$current_bar_end = $this->data[$row][$key]["start"] + $this->data[$row][$key]["length"];

				### find out whether successive bars exist that continuously overlap current. find farthest overlaping bar end.
				while ( isset($this->data[$row][$key2]) and ($this->data[$row][$key2]["start"] < $current_bar_end) and (empty ($overlap_end) or ($this->data[$row][$key2]["start"] <= $overlap_end)) )
				{ ### next bar exists, next bar starts before current ends, overlap_end is set and next bar starts before it.
					$overlap_start = empty ($overlap_start) ? $this->data[$row][$key2]["start"] : $overlap_start;
					$overlap_end = max ($overlap_end, ($this->data[$row][$key2]["start"] + $this->data[$row][$key2]["length"]));
					$key2++;
				}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "overlap_end:" . date (MRP_DATE_FORMAT, $overlap_end) . "<br>";
// /* dbg */ echo "overlap_start:" . date (MRP_DATE_FORMAT, $overlap_start) . "<br>";
// /* dbg */ }

				if (!empty ($overlap_end))
				{
					if ($overlap_end < $current_bar_end)
					{
						### insert remaining end of current bar after last continuously overlapping bar. see if remainder is overlapped by successive when array pointer gets there.
						$key2--;
						$remainder = $this->data[$row][$key];
						$remainder["start"] = $overlap_end;
						$remainder["length"] = $current_bar_end - $overlap_end;
						$remainder["nostartmark"] = true;
						array_splice ($this->data[$row], $key2, 1, array ($this->data[$row][$key2], $remainder));
					}

					### trim current bar to overlap start.
					$this->data[$row][$key]["length"] = $overlap_start - $this->data[$row][$key]["start"];

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "trimmed bar:";
// /* dbg */ arr ($this->data[$row][$key]);
// /* dbg */ echo "remainder start:" . date (MRP_DATE_FORMAT, $remainder["start"]) . "<br>";
// /* dbg */ }
				}

				$key++;
			}
		}
	}

	function bar_start_sort ($a, $b)
	{
		if ($a["start"] == $b["start"])
		{
			### sort by length
			if ($a["length"] == $b["length"])
			{
				return 0;
			}
			else
			{
				return ($a["length"] > $b["length"] ? 1 : -1);
			}
		}

		return ($a["start"] > $b["start"] ? 1 : -1);
	}
}

?>
