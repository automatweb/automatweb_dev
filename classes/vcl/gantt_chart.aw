<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/gantt_chart.aw,v 1.2 2004/12/09 11:51:18 kristo Exp $
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
	// int cells - number of divisions in chart (e.g. 7 days for a chart depicting one week). default is 7.
	// int cell_size - length of one division in seconds. default is 86400.
	// int chart_width - chart width in pixels. default is 1000.
	// string row_dfn - title for row-titles column. default is "Ressurss".
	// string style - style to use (default| ... ).
	// bool row_anchors - make hyperlinks of row names (not impl. yet).
	// bool bar_anchors - make hyperlinks of bars (not impl. yet).
	// int time_stops - number of time stops at the top
	function configure_chart ($arr)
	{
		$this->start = (int) (empty ($arr["start"]) ? (time () - 302400) : $arr["start"]);
		$this->end = (int) (empty ($arr["end"]) ? (time () + 302400) : $arr["end"]);
		$this->cell_size = (int) (empty ($arr["cell_size"]) ? 86400 : $arr["cell_size"]);
		$this->cells = (int) (empty ($arr["cells"]) ? 7 : $arr["cells"]);
		$this->chart_width = (int) (empty ($arr["chart_width"]) ? 1000 : $arr["chart_width"]);
		$this->chart_id = empty ($arr["chart_id"]) ? "gantt_chart" : $arr["chart_id"];
		$this->style = empty ($arr["style"]) ? "default" : $arr["style"];
		$this->row_dfn = empty ($arr["row_dfn"]) ? "Ressurss" : $arr["row_dfn"];
		$this->time_stops = empty ($arr["time_stops"]) ? 8 : $arr["time_stops"];
		// $this->row_anchors = $arr["row_anchors"] ? "anchors" : "noanchors";
		// $this->bar_anchors = $arr["bar_anchors"] ? "anchors" : "noanchors";

		$this->scale_quotient = ($this->cells * $this->cell_size) / $this->chart_width;
	}

	// Adds one row.
	// Arguments:
	// string name - identifier for the row
	// string title - title for the row
	// string uri - uri for row title. Applies if row_anchors property is set to true for chart.
	// string target - uri target for row title. Applies if row_anchors property is set to true for chart.
	function add_row ($arr)
	{
		$row_name = $arr["name"];
		$row_title = $arr["title"];
		$row_title_uri = empty ($arr["uri"]) ? false : $arr["uri"];
		$row_title_uri_target = empty ($arr["target"]) ? "_self" : $arr["target"];

		$this->rows[$row_name] = array (
			"name" => $row_name,
			"title" => $row_title,
			"uri" => $row_title_uri,
			"target" => $row_title_uri_target,
		);
	}

	function define_column ($arr)
	{
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

		$this->data[] = array (
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
		$this->sort_data();

		$this->read_template ($this->style . "_style.tpl");
		$this->vars = array (
			"chart_width" => $this->chart_width,
		);
		$style = $this->parse ();

		$this->read_template ("gantt.tpl");

		$row_switch = 0;
		$bar_switch = 0;
		$rows = "";

		// go over all bars and check if any are out of chart
		foreach($this->data as $row)
		{
			foreach(safe_array($row) as $bar)
			{
				if (($bar["start"] + $bar["length"]) > $this->end)
				{
					$this->end = $bar["start"] + $bar["length"];
				}
			}
		}
		$this->scale_quotient = (($this->end-$this->start) / $this->chart_width);

		foreach ($this->rows as $row)
		{
			$length = 0;
			$row_contents = "";
			$cell_end = $this->start + $this->cell_size;
			$pointer = $this->start ;
			$previous_bar_end = 0;
			$cells = $this->cells;

			while ($cells)
			{
				$cell_contents = "";

				while ($pointer < $cell_end)
				{
					$bar = array_shift ($this->data[$row["name"]]);
					$bar_type = $bar["hilight"] ? "hilighted" : ($row_switch . ($bar_switch));

					### trim bars starting before chart start
					if ($bar["start"] < $this->start)
					{
						if (($bar["start"] + $bar["length"]) > $this->start)
						{
							$bar_length = ($bar["start"] + $bar["length"]) - $this->start;
							$bar["start"] = $this->start;
						}
						else
						{
							break;
						}
					}

					### split bars longer than free space in one cell
					if (($pointer + $bar["length"]) >= $cell_end)
					{
						$bar_length = $cell_end - $bar["start"];
						$bar["length"] = $bar["start"] + $bar["length"] - $cell_end;
						$bar["start"] = $cell_end;
						array_unshift ($this->data[$row["name"]], $bar);
					}
					else
					{
						$bar_length = $bar["length"];
					}

					### insert preceeding whitespace
					if ($bar["start"] > $pointer)
					{
						$this->vars (array (
							"length" => ceil (($bar["start"] - $pointer) / $this->scale_quotient),
						));
						$cell_contents .= trim ($this->parse ("MAIN.data_row" . $row_switch . ".data_cell.cell_contents.bar_empty"));
					}

					### parse bar
					$this->vars (array (
						"length" => ceil ($bar_length / $this->scale_quotient),
						"title" => $bar["title"]." (".date("d.m.Y / H:i", $bar["start"])." - ".date("d.m.Y / H:i", $bar["start"] + $bar["length"])." )",
						"bar_uri" => $bar["bar_uri"],
						"bar_uri_target" => $bar["bar_uri_target"],
						"baseurl" => $this->cfg["baseurl"],
					));
					$cell_contents .= trim ($this->parse ("MAIN.data_row" . $row_switch . ".data_cell.cell_contents.bar_" . $bar_type));
					### ...
					$pointer = $bar["start"] + $bar["length"];
					$bar_switch = $bar_switch ? 0 : 1;
				}

				### fill remaining empty space
				if ($pointer < $cell_end)
				{
					//echo "eempty len = ".ceil (($cell_end - $pointer) / $this->scale_quotient)." end = $cell_end , pointer = $pointer scale = ".$this->scale_quotient." <br>";
					$this->vars (array (
						"length" => ceil (($cell_end - $pointer) / $this->scale_quotient),
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
				$cells--;
				$cell_end += $this->cell_size;
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
		$cells = $this->cells;
		$header_row = "";

		while ($cells)
		{
			$this->vars (array (
				"col_dfn" => $this->cells - $cells, ///!!! teha veerunimed/pealkijrad? v6tab palju ruumi?!
			));
			$header_row .= $this->parse ("header");
			$cells--;
		}

		$this->_get_timespans();

		### parse table
		$this->vars (array (
			"row_dfn" => $this->row_dfn,
			"header" => $header_row,
			"data_row0" => $rows,
			"data_row1" => ""
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

	function sort_data()
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
