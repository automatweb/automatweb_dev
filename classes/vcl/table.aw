<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/table.aw,v 1.57 2005/09/21 12:47:06 kristo Exp $
// aw_table.aw - generates the html for tables - you just have to feed it the data
//
class aw_table extends aw_template
{
	////
	// !constructor - paramaters:
	// prefix - a symbolic name for the table so we could tell it apart from the others
	// tbgcolor - default cell background color
	var $scripts;
	var $id = 'table_0';
	var $filter_name = "aw_table_filters";

	function aw_table($data = array())
	{
		$this->id = uniqid('table_');

		if (file_exists(aw_ini_get("site_basedir")."/public/img/up.gif"))
		{
			$this->imgurl = aw_ini_get("baseurl")."/img";
		}
		else
		{
			$this->imgurl = aw_ini_get("baseurl")."/automatweb/images";
		}
		$this->init(array(
			"tpldir" => "table",
		));
		$this->up_arr = sprintf("<img src='%s' border='0' />",$this->imgurl . "/up.gif");
		$this->dn_arr = sprintf("<img src='%s' border='0' />",$this->imgurl . "/down.gif");

		// prefix - kasutame sessioonimuutujate registreerimisel
		$this->prefix = isset($data["prefix"]) ? $data["prefix"] : "";
		// table cell background color
		$this->tbgcolor = isset($data["tbgcolor"]) ? $data["tbgcolor"] : "";
		$this->filter_name = $this->prefix . $this->filter_name;

		$this->header_attribs = array();

		// ridade värvid (och siis stiilid) muutuvad
		// siin defineerime nad
		$this->style1 = "#AAAAAA";
		$this->style2 = "#CCCCCC";

		// initsialiseerime muutujad
		$this->rowdefs = array();
		$this->data = array();
		$this->actions = array();
		$this->col_styles = array();
		$this->nfields = array();
		$this->filters = array();
		$this->selected_filters = array();
		$this->filter_index = array();
		$this->sortable = true;

		// esimene kord andmeid sisestada?
		// seda on vaja selleks, et määrata default sort order.
		$this->first = true;
		$layout = !empty($data["layout"]) ? $data["layout"] : "generic";
		$this->set_layout($layout);
		if (isset($data["xml_def"]))
		{
			$this->parse_xml_def($data["xml_def"]);
		};
		$this->use_chooser = false;
		// if true and chooser is used, checking chooser checkboxes changes the style of the row as well
		$this->chooser_hilight = true;

		### maintain all selected filters
		$saved_filters = aw_global_get ($this->filter_name . "_saved");
		$this->selected_filters = $saved_filters ? aw_unserialize ($saved_filters) : array ();

		### set/clear new selected filters
		$request = aw_global_get ("request");

		if ($selected_filter = $request[$this->filter_name])
		{
			list ($filter_key, $filter_selection) = explode (",", $selected_filter);

			if ($filter_selection)
			{
				$this->selected_filters[$filter_key] = $filter_selection;
			}
			else
			{
				unset ($this->selected_filters[$filter_key]);
			}
		}

		aw_session_set ($this->filter_name . "_saved", aw_serialize ($this->selected_filters));
	}

	function set_sortable($arg)
	{
		$this->sortable = $arg;
	}

	function set_rgroupby($arg)
	{
		$this->rgroupby = $arg;
	}

	/** some users need to put simple plain text above the
	    table, give them a setter for this
	**/
	function set_header($arr)
	{
		$this->table_header = $arr;
	}

	////
	// !sisestame andmed
	function define_data($row)
	{
		### apply filter
		foreach ($this->selected_filters as $filter_key => $filter_selection)
		{
			$filter_name = $this->filter_index[$filter_key];

			if ($row[$filter_name] != $this->filters[$filter_name]["filter"][$filter_selection - 1])
			{
				return;
			}
		}

		$this->data[] = $row;

		if(!$this->no_recount)
		{
			$this->d_row_cnt++;
		}
	}

	function set_data($idx, $row)
	{
		$this->data[$idx] = $row;
	}

	function get_data()
	{
		return $this->data;
	}

	////
	// !Clear the data
	function clear_data()
	{
		$this->data = array();
	}

	////
	// !merge the given data with the last data entered
	// XXX: does not seem to be used?
	function merge_data($row)
	{
		$cnt = sizeof($this->data);
		$this->data[$cnt-1]  = array_merge($this->data[$cnt-1],$row);
	}

	////
	// !here you can add action rows to the table
	// link - part of the action's link,
	// field - the field that will be used to complete the action link,
	// caption - action text
	// cspan - colspan
	// rspan - rowspan
	// remote - if specified, the link will open in a new window and this parameter also must contain the height,width of the popup
	//
	function define_action($row)
	{
		$this->actions[] = $row;
	}

	////
	// !Defines a chooser (a column of checkboxes
	function define_chooser($arr)
	{
		$this->chooser_config = $arr;
		$this->use_chooser = true;
	}

	function remove_chooser()
	{
		$this->chooser_config = NULL;
		$this->use_chooser = false;
	}

	////
	// !here you can define additional headers
	// caption - the caption at the left of the header
	// links - an array of link => text pairs that will be put in the header
	function define_header($caption,$links = array())
	{
		$this->headerstring = $caption;
		$hlinks = array();
		if ($this->headerlinkclassid)
		{
			$hlcl=" class='".$this->headerlinkclassid."' ";
		};
		reset($links);
		while(list($k,$v) = each($links))
		{
			if ($k=="extra")
			{
				$this->headerextra = $v;
			}
			else
			if ($k=="extrasize")
			{
				$this->headerextrasize = $v;
			}
			else
			{
				$hlinks[] = sprintf("<a href='$k' $hlcl>$v</a>",$k,$v);
			};
		};
		$this->headerlinks = join(" | ",$hlinks);
	}

	////
	// !this lets you set a field as numeric, so that they will be sorted correctly
	function set_numeric_field($elname)
	{
		$this->nfields[$elname] = 1;
	}

	////
	// !sets the default sorting element(s) for the table
	// if the sorting function finds that there are no other sorting arrangements made, then it will use
	// the element(s) specified here.
	// sortby - a single element or an array of elements.
	//   sometimes the array can be a bit weird though - namely, the key specifies the column in which the
	//   element is and that is used when determining if a column is sorted
	//   but when doing the actual sorting, the value is used and that contains an ordinary element, not a column name
	//   right now this only applies to form tables
	//   - so, when setting an array, always have the key and value be the same, unless you really know what you are doing
	function set_default_sortby($sortby)
	{
		$this->default_order = $sortby;
	}

	////
	// !sets the default sorting order
	// if the sorting function finds that there are no other sorting arrangements made, then it will use
	// the order specified here.
	// dir - a string (asc/desc) or an array of strings - it will be linked to the sort element by index
	function set_default_sorder($dir)
	{
		$this->default_odir = $dir;
	}

	/** defines that the table has a pager

		@attrib api=1

		@comment
			type -> "text" || "buttons" || "lb"
			records_per_page -> number of records per page

	**/
	function define_pageselector($arr)
	{
		$this->has_pages = true;
		$this->records_per_page = $arr["records_per_page"];
		$this->pageselector = $arr["type"];
		if($arr["d_row_cnt"])
		{
			$this->d_row_cnt = $arr["d_row_cnt"];
		}
		if($arr["no_recount"])
		{
			$this->no_recount = 1;
		}
	}

	////
	// !sorts the data previously entered
	// field - optional, what to sort by. you really don't need to specify this, the table can manage it on it's own
	// sorder - sorting order - asc/desc. you really don't need to specify this, the table can manage it on it's own
	// rgroupby - an array of elements whose values will be grouped in the table
	// vgroupby - array of elements that will be vertically grouped
	function sort_by($params = array())
	{
		if (!$this->sortable)
		{
			return false;
		};

		// see peaks olema array,
		// kus on regitud erinevate tabelite andmed
		$aw_tables = aw_global_get("aw_tables");
		$sess_field_key   = $this->prefix . "_sortby";
		$sess_field_order = $this->prefix . "_sorder";

		// figure out the column by which we must sort
		// start from the parameters
		if (!($this->sortby = (isset($params["field"]) ? $params["field"] : NULL)))
		{
			// if it was not specified as a parameter the next place is the url
			if (!($this->sortby = aw_global_get("sortby")))
			{
				// and if it is not in the url either, we will try the session
				if (!($this->sortby = $aw_tables[$sess_field_key]))
				{
					// and finally we get the default
					$this->sortby = isset($this->default_order) ? $this->default_order : NULL;
				}
			}
		}

		// now figure out the order of sorting
		// start with parameters
		if (!($this->sorder = $params["sorder"]))
		{
			// if it was not specified as a parameter the next place is the url
			if (!($this->sorder = aw_global_get("sort_order")))
			{
				// and if it is not in the url either, we will try the session
				if (!($this->sorder = $aw_tables[$sess_field_order]))
				{
					// and finally we get the default
					if (!($this->sorder = $this->default_odir))
					{
						$this->sorder = "asc";
					}
				}
			}
		}


		// we should mark this down only when we have clicked on a link and thus changed something from the default
		// what's the difference? well - if the defaults change and this is written a reload does not change things
		//
		// and well, I think this kinda sucks - I don't want the damn thing to remember the state.
/*		if (aw_global_get("sort_order") || aw_global_get("sortby"))
		{
			$aw_tables[$sess_field_key] = $this->sortby;
			$aw_tables[$sess_field_order] = $this->sorder;
			aw_session_set("aw_tables", $aw_tables);
		}*/

		// grouping - whenever a value of one of these elements changes an extra row gets inserted into the table
		$this->rgroupby = isset($params["rgroupby"]) ? $params["rgroupby"] : $this->rgroupby;
		$this->rgroupsortdat = isset($params["rgroupsortdat"]) ? $params["rgroupsortdat"] : "";
		$this->vgroupby = isset($params["vgroupby"]) ? $params["vgroupby"] : "";
		$this->vgroupdat = isset($params["vgroupdat"]) ? $params["vgroupdat"] : "";

		// ok, all those if sentences are getting on my nerves - we will make sure that all sorting options
		// after this point are always arrays
		$this->make_sort_prop_arrays();

		// switch to estonian locale
		$old_loc = setlocale(LC_COLLATE,0);
		setlocale(LC_COLLATE, 'et_EE');


		// sort the data
		usort($this->data,array($this,"sorter"));

		// switch back to estonian
		setlocale(LC_COLLATE, $old_loc);

		// now go over the data and make the rowspans for the vertical grouping elements
		if (is_array($this->vgroupby))
		{
			$tmp = $this->vgroupby;
			$this->vgrowspans = array();

			foreach($this->vgroupby as $_vgcol => $_vgel)
			{
				foreach($this->data as $row)
				{
					$val = "";
					foreach($tmp as $__vgcol => $__vgel)
					{
						$val .= $row[$__vgel];
						if ($_vgcol == $__vgcol && $_vgel == $__vgel)
						{
							break;
						}
					}
					$this->vgrowspans[$val]++;
				}
			}
		}
	}

	////
	// !the sorting function. iow - the tricky bit
	// it must sort the data correctly, taking into account whether it is numerical or not
	// then it must sort first by the vgroup element(s), then rgroup element(s), and then the sorting element(s)
	function sorter($a,$b)
	{
		// what the hell is going on here you ask? well.
		// basically the idea is that we go over the sorted columns until we find two values that are different
		// and then we can compare them and thus we get to sort the entire array.
		// why don't we just concatenate the strings together? well, because what if the first is a text element, the next
		// a number and the 3rd a text element - if we cat them together we lose the ability to do numerical comparisons..
		$v1=NULL;$v2=NULL;
		$skip = false;

		if (is_array($this->vgroupby))
		{
			// $_vgcol and $vgel are the same
			foreach($this->vgroupby as $_vgcol => $vgel)
			{
				// if there is a sorting element for this vertical group, then actually use it's value for sorting
				if ($this->vgroupdat[$vgel]["sort_el"])
				{
					$v1 = $a[$this->vgroupdat[$vgel]["sort_el"]];
					$v2 = $b[$this->vgroupdat[$vgel]["sort_el"]];
					$this->sort_flag = $this->nfields[$this->vgroupdat[$vgel]["sort_el"]] ? SORT_NUMERIC : SORT_REGULAR;
					if ($v1 == $v2)
					{
						// if they are equal, then try to sort by the display element
						$v1 = $a[$vgel];
						$v2 = $b[$vgel];
						$this->sort_flag = $this->nfields[$vgel] ? SORT_NUMERIC : SORT_REGULAR;
					}
				}
				else
				{
					$v1 = $a[$vgel];
					$v2 = $b[$vgel];
					$this->sort_flag = $this->nfields[$vgel] ? SORT_NUMERIC : SORT_REGULAR;
				}
				// the sort numeric is specified for the actual sorting element, but the order for the column
				$this->u_sorder = $this->sorder[$vgel];
				if ($v1 != $v2)
				{
					$skip = true;
					break;
				}
			}
		}

		if (is_array($this->rgroupby) && !$skip)
		{
			foreach($this->rgroupby as $_rgcol => $rgel)
			{
				if ($this->rgroupsortdat[$_rgcol]["sort_el"])
				{
					$v1 = $a[$this->rgroupsortdat[$_rgcol]["sort_el"]];
					$v2 = $b[$this->rgroupsortdat[$_rgcol]["sort_el"]];
					$this->sort_flag = $this->nfields[$this->rgroupsortdat[$_rgcol]["sort_el"]] ? SORT_NUMERIC : SORT_REGULAR;
					if ($v1 == $v2)
					{
						// if they are equal, then try to sort by the display element
						$v1 = $a[$rgel];
						$v2 = $b[$rgel];
						$this->sort_flag = $this->nfields[$_rgcol] ? SORT_NUMERIC : SORT_REGULAR;
					}
				}
				else
				{
					$v1 = $a[$rgel];
					$v2 = $b[$rgel];
					$this->sort_flag = $this->nfields[$_rgcol] ? SORT_NUMERIC : SORT_REGULAR;
				}
				$this->u_sorder = $this->sorder[$_rgcol];
				if ($v1 != $v2)
				{
					$skip = true;
					break;
				}
			}
		}

		if (is_array($this->sortby) && !$skip)
		{
			foreach($this->sortby as $_coln => $_eln)
			{
				$this->u_sorder = $this->sorder[$_eln];
				$this->sort_flag = isset($this->nfields[$_eln]) ? SORT_NUMERIC : SORT_REGULAR;
				$v1 = $a[$_eln];
				$v2 = $b[$_eln];
				if ($v1 != $v2)
				{
					break;
				}
			}
		}

		if (isset($this->sort_flag) && ($this->sort_flag == SORT_NUMERIC))
		{
			$_a = (float)strtolower(strip_tags($v1));
			$_b = (float)strtolower(strip_tags($v2));

			if ($this->u_sorder == "asc")
			{
				if ($_a < $_b)
				{
					return -1;
				}
				else
				if ($_a > $_b)
				{
					return 1;
				}
				return 0;
			}
			else
			{
				if ($_a > $_b)
				{
					return -1;
				}
				else
				if ($_a < $_b)
				{
					return 1;
				}
				return 0;
			}
		}
		else
		{
			$_a = strtolower(strip_tags($v1));
			$_b = strtolower(strip_tags($v2));
			$ret = strcoll($_a, $_b);
			if (isset($this->u_sorder) && ($this->u_sorder == "asc"))
			{
				return $ret;
			}
			else
			{
				return -$ret;
			}
		}
	}

	function draw($arr = array())
	{
		// väljastab tabeli
		if (!is_array($this->rowdefs))
		{
			print "Don't know what to do";
			return;
		}

		if ($this->rgroupby && !$arr["rgroupby"])
		{
			$arr["rgroupby"] = $this->rgroupby;
		}

		if (isset($arr["rgroupby"]) && is_array($arr["rgroupby"]))
		{
			$this->do_rgroup_counts($arr["rgroupby"]);
		}

		extract($arr);
		$PHP_SELF = aw_global_get("PHP_SELF");
		$REQUEST_URI = aw_global_get("REQUEST_URI");
		$this->titlebar_under_groups = isset($arr["titlebar_under_groups"]) ? $arr["titlebar_under_groups"] : $this->titlebar_under_groups;
		$tbl = "";

		foreach($this->rowdefs as $rd)
		{
			$this->sh_counts_by_parent[$rd["name"]] = $this->_get_sh_count_by_parent($rd["name"]);
		}

		$this->_get_max_level_cnt("");
		$this->max_sh_count = $this->_max_gml-1;

		if (!empty($this->table_header))
		{
			$tbl .= $this->table_header;
		}

		if (!empty($pageselector))
		{
			$this->pageselector = $pageselector;
		}

		if (!empty($records_per_page))
		{
			$this->records_per_page = $records_per_page;
		}

		if (!empty($has_pages))
		{
			$this->has_pages = $has_pages;
		}

		$this->vars(array(
			"sel_row_style" => $this->tr_sel,
		));

		$this->read_template("scripts.tpl");
		if ($this->use_chooser && !$arr["no_chooser_script"])
		{
			$tbl .= $this->parse("selallscript");
			if ($this->chooser_hilight)
			{
				$tbl .= $this->parse("hilight_script");
			};
		}

		if (!empty($this->pageselector))
		{
			switch($this->pageselector)
			{
				case "text":
					$tbl .= $this->draw_text_pageselector(array(
						"records_per_page" => $this->records_per_page
					));
					break;
				case "buttons":
					$tbl .= $this->draw_button_pageselector(array(
						"records_per_page" => $this->records_per_page
					));
					break;
				case "lb":
				default:
					$tbl .= $this->draw_lb_pageselector(array(
						"records_per_page" => $this->records_per_page
					));
			}
		}




		// moodustame välimise raami alguse
		if (is_array($this->frameattribs))
		{
			$tmp = $this->frameattribs;
			$tmp["name"] = "table";
			$tbl .= $this->opentag($tmp);
			$tbl .= "<tr>\n";
			$tattr = array(
				"name" => "td",
				"bgcolor" => $this->framebgcolor,
			);
			if ($this->framebgclass != "")
			{
				$tattr["class"] = $this->framebgclass;
				unset($tattr["bgcolor"]);
			}
			$tbl .= $this->opentag($tattr);
		};

		// moodustame tabeli alguse
		if (is_array($this->tableattribs))
		{
			$tmp = $this->tableattribs;
			$tmp["name"] = "table";
			$tbl .= $this->opentag($tmp);
		}

		if (!empty($this->pageselector_string))
		{
			$colspan = sizeof($this->rowdefs) + sizeof($this->actions)-(int)$this->headerextrasize;
			$tbl .= "<tr>\n";
			$tbl .= $this->opentag(array("name" => "td","colspan" => $colspan, "classid" => $this->style1));
			$tbl .= $this->pageselector_string;
			$tbl .= "</td>\n";
			$tbl .= "</tr>\n";
		}

		if (!empty($this->headerstring))
		{
			$colspan = sizeof($this->rowdefs) + sizeof($this->actions)-(int)$this->headerextrasize;
			$tbl .= "<tr>\n";
			$tbl .= $this->opentag(array("name" => "td","colspan" => $colspan,"classid" => $this->titlestyle));
			$tbl .= "<strong>" . $this->headerstring . ": ". $this->headerlinks . "</strong>";
			$tbl .= "</td>\n";
			$tbl .= $this->headerextra;
			$tbl .= "</tr>\n";
		}

		// if we show title under grouping elements, then we must not show it on the first line!
		if (empty($this->titlebar_under_groups) && empty($arr["no_titlebar"]))
		{
			$tbl .= $this->_req_draw_header("");
		}

		$this->lgrpvals = array();

		if (!isset($act_page))
		{
			$act_page = $GLOBALS["ft_page"];
		}

		// koostame tabeli sisu
		if (is_array($this->data))
		{
			// tsükkel üle data
			$counter = 0; // kasutame ridadele erineva värvi andmiseks
			$p_counter = 0;
			foreach($this->data as $k => $v)
			{
				$counter++;
				$p_counter++;
				// if this is not on the active page, don't show the damn thing
				if (isset($this->has_pages) && $this->has_pages && isset($this->records_per_page) && $this->records_per_page && !$this->no_recount)
				{
					$cur_page = (int)(($p_counter-1) / $this->records_per_page);
					if ($cur_page != $act_page)
					{
						continue;
					}
				}

				$row_style = $counter % 2 == 0 ? $this->tr_style2 : $this->tr_style1;
				if ($v["_active"])
				{
					$row_style = $this->tr_active;
				};

				// rida algab
				// rowid/domid is needed for the selector script
				$rowid = $this->prefix . $this->id . $counter;
				$tbl .= $this->opentag(array("name" => "tr", "domid" => $rowid, "class" => $row_style));

				$tmp = "";
				// grpupeerimine
				if (isset($rgroupby) && is_array($rgroupby))
				{
					$tmp = $this->do_col_rgrouping($rgroupby, $rgroupdat, $rgroupby_sep, $v, $rowid, $row_style);
				};
				if ($tmp != "")
				{
					$counter = 1;
				}
				$tbl .= $tmp;

				// tsükkel üle rowdefsi, et andmed oleksid oiges järjekorras
				foreach($this->rowdefs as $k1 => $v1)
				{
					if ($this->sh_counts_by_parent[$v1["name"]] > 0)
					{
						continue;
					}
					$rowspan = 1;
					$style = false;
					if (isset($this->vgroupby) && is_array($this->vgroupby))
					{
						if (isset($this->vgroupby[$v1["name"]]))
						{
							// if this column is a part of vertical grouping, check if it's value has changed
							// if it has, then set the new rowspan
							// if not, then skip over this column

							// build the value from all higher grouping els and this one as well
							$_value = "";
							foreach($this->vgroupby as $_vgcol => $_vgel)
							{
								$_value .= $v[$_vgel];
								if ($v1["name"] == $_vgcol)
								{
									break;
								}
							}
							if (!isset($this->vgrouplastvals[$_value]))
							{
								$this->vgrouplastvals[$_value] = $_value;
								$rowspan = $this->vgrowspans[$_value];
								$style = $this->group_style;
							}
							else
							{
								continue;
							}
						}
					}

					// määrame ära staili
					if (!$style)
					{
						if (isset($this->sortby[$v1["name"]]))
						{
							$style_key = (($counter % 2) == 0) ? "content_sorted_style2" : "content_sorted_style1";
							$bgcolor = ($counter % 2) ? $this->selbgcolor1 : $this->selbgcolor2;
						}
						else
						{
							$style_key = (($counter % 2) == 0) ? "content_style2" : "content_style1";
							$bgcolor = ($counter % 2) ? $this->bgcolor1 : $this->bgcolor2;
						};

						if (isset($this->col_styles[$v1["name"]][$style_key]))
						{
							$style = $this->col_styles[$v1["name"]][$style_key];
						}
						else
						{
							$style = "";
						};

						if (!$style)
						{
							if (isset($this->sortby[$v1["name"]]))
							{
								$style = (($counter % 2) == 0) ? $this->selected2 : $this->selected1;
								$bgcolor = ($counter % 2) ? $this->selbgcolor1 : $this->selbgcolor2;
							}
							else
							{
								$style = (($counter % 2) == 0) ? $this->style2 : $this->style1;
								$bgcolor = ($counter % 2) ? $this->bgcolor1 : $this->bgcolor2;
							};
						}
					}

					// moodustame celli
					$rowspan = isset($this->actionrows) ? $this->actionrows : $rowspan;
					$tbl .= $this->opentag(array(
						"name"    => "td",
						"classid" => $style,
						"width" => isset($v1["width"]) ? $v1["width"] : "",
						"rowspan" => ($rowspan > 1) ? $rowspan : 0,
						"style" => ((!empty($v1["chgbgcolor"]) && !empty($v[$v1["chgbgcolor"]])) ? ("background:".$v[$v1["chgbgcolor"]]) : ""),
						"align" => isset($v1["align"]) ? $v1["align"] : "",
						"valign" => isset($v1["valign"]) ? $v1["valign"] : "",
						"nowrap" => isset($v1["nowrap"]) ? 1 : "",
						"bgcolor" => isset($v["bgcolor"]) ? $v["bgcolor"] : $bgcolor,
					));

					if ($v1["name"] == "rec")
					{
						$val = $counter;
					}
					else
					{
						if (isset($v1["strformat"]))
						{
							$format = localparse($v1["strformat"],$v);
							$val = sprintf($format,$v[$v1["name"]]);
						}
						else
						{
							$val = $v[$v1["name"]];
						};
					};

					if (empty($v1["type"]))
					{
						$v1["type"] = "";
					};

					if (isset($v1["type"]) && $v1["type"] == "time")
					{
						if (!empty($v1["smart"]))
						{
							$today = date("dmY");
							$thisdate = date("dmY",$val);
							if ($today == $thisdate)
							{
								// XX: make it translatable
								$val = date("H:i",$val) . " täna";
							}
							else
							{
								$val = date($v1["format"],$val);
							};
						}
						else
						{
							$is_link = false;
							if (preg_match("/<a (.*)>(.*)<\/a>/U",$val, $tmt))
							{
								$is_link = true;
								$val = $tmt[2];
							}

							if ($val < 1)
							{
								$val = "n/a";
							}
							else
							{
								$val = date($v1["format"],$val);
							};
							if ($is_link)
							{
								$val = "<a ".$tmt[1].">".$val."</a>";
							}
						};
					};

					if (empty($val) && $v1["type"]!="int")
					{
						$val = "&nbsp;";
					};

					//võeh, ühesõnaga laseme $val läbi functsiooni, mis on defineeritud väljakutsuva klassi sees
					//ja $t->define_field(array(
					//	...
					//	"callback" => array(&$this, "method")
					//   ));
					if (isset($v1["callback"]) && is_callable($v1["callback"]))
					{
						$v["_this_cell"] = $v1["name"];
						$val = call_user_func ($v1["callback"], isset($v1['callb_pass_row']) ? $v : $val);
					}

					if (isset($v1["thousands_sep"]))
					{
						// insert separator every after every 3 chars, starting from the end.
						$val = strrev(chunk_split(strrev(trim($val)),3,$v1["thousands_sep"]));
						// chunk split adds one too many separators, so remove that
						$val = substr($val,strlen($v1["thousands_sep"]));
					}

					$tbl .= str_replace("[__jrk_replace__]",$counter,$val);
					$tbl .= "</td>\n";
				};

				// joonistame actionid
				$actionridu = isset($this->actionrows) ? $this->actionrows : 1;

				for ($arow = 1; $arow <= $actionridu; $arow++)
				{
					// uutele actioni ridadele tuleb teha uus <tr>
					if ($arow > 1)
					{
						$tbl .= "<tr>\n";
					};
					$style = (($counter % 2) == 0) ? $this->style1 : $this->style2;
					// joonistame actionid
					foreach($this->actions as $ak => $av)
					{
						// joonista ainult need actionid, mis siia ritta kuuluvad
						if ($this->actionrows ? ($arow == $av["row"] || ($arow==1 && !$av["row"]) ):1)
						{
							$tbl .= $this->opentag(array(
								"name"=>"td",
								"classid" => ($av["style"]) ? $av["style"] : $style,
								"align" => "center",
								"colspan" => ($av["cspan"] ? $av["cspan"] : ""),
								"rowspan" => ($av["rspan"] ? $av["rspan"] : ""),
							));

							$tbl.=$av["remote"]?
								"<a href='javascript:remote(0,".$av["remote"].",\"$PHP_SELF?".$av["link"]."&id=".$v[$av["field"]].'");\'>'.$av["caption"]."</a>":
								"<a href='$PHP_SELF?" . $av["link"] . "&id=" . $v[$av["field"]] . "&" . $av["field"] . "=" . $v[$av["field"]] . "'>$av[caption]</a>";
							$tbl .= "</td>\n";

						};
					};

					// rida lopeb

					if ($this->use_chooser)
					{
						$chooser_value = $v[$this->chooser_config["field"]];
						$name = $this->chooser_config["name"] . "[${chooser_value}]";
						$onclick = "";
						if ($this->chooser_hilight)
						{
							$onclick = " onClick=\"hilight(this,'${rowid}')\" ";
						};
						$stl = "";
						if (!empty($this->chooser_config["chgbgcolor"]) && !empty($v[$this->chooser_config["chgbgcolor"]]))
						{
							$stl =  "style=\"background:".$v[$this->chooser_config["chgbgcolor"]]."\"";
						}
						if($chooser_value)
						{
							$tbl .= "<td align='center' $stl><input type='checkbox' name='${name}' value='${chooser_value}' ${onclick} ".($v[$this->chooser_config["name"]] ? "checked" : "")."></td>";
						}
						else
						{
							$tbl .= "<td align='center'>&nbsp;</td>";
						}
					};
					$tbl .= "</tr>\n";
				};
			};
		};
		// sisu joonistamine lopeb

		// tabel kinni
		if (is_array($this->tableattribs))
		{
			$tbl .= "</table>\n";
		}

		// raam kinni
		if (is_array($this->frameattribs))
		{
			$tbl .= "</td></tr></table>\n";
		};

		// tagastame selle käki
		return $tbl;
	}

	function _format_csv_field($d, $sep = ";")
	{
		$new=strtr($d,array('"'=>'""'));
		if (!(strpos($d,$sep)===false) || $new != $d || strpos($d, "\n") !== false)
		{
			$new='"'.$new.'"';
		};
		return strip_tags($new);
	}

	// tagastab csv andmed, kustuda välja draw asemel
	function get_csv_file($sep = ";")
	{
		//$sep = "\t";
		$d = array();
		reset($this->rowdefs);
		$tbl = "";
		if(is_array($this->rowdefs))
		{
			foreach($this->rowdefs as $v)
			{
				$tbl .= ($tbl ? $sep : "").$this->_format_csv_field($v["caption"], $sep);
			}
		}
		$d[] = $tbl;

		// koostame tabeli sisu
		if(is_array($this->data))
		{
			reset($this->data);
			$cnt = 0;
			foreach($this->data as $k => $v)
			{
				$tbl = "";
				$cnt++;
				reset($this->rowdefs);
				if(is_array($this->rowdefs))
				foreach($this->rowdefs as $k1 => $v1)
				{
					if ($v1["name"] == "rec")
					{
						$val = $cnt;
					} else
					{
						if ($v1["strformat"])
						{
							$format = localparse($v1["strformat"], $v);
							$val = sprintf($format, $v[$v1["name"]]);
						}
						else
						{
							$val = $v[$v1["name"]];
						};
					};

					if ($v1["type"] == "time")
					{
						$val = date($v1["format"], $val);
					};

					if (!$val && $v1["type"] != "int")
					{
						$val = "";
					};

					$val = str_replace("[__jrk_replace__]",$cnt,$val);

					$tbl .= ($tbl ? $sep : "").$this->_format_csv_field($val, $sep);
				};
				$d[] = $tbl;
			};
		};
		// sisu joonistamine lopeb
		return join("\r\n", $d);
	}

	// genereerib html tagi
	function tag($data)
	{
		if (!is_array($data))
		{
			// kui anti vigased andmed, siis bail out
			return;
		};

		// eraldame nime ja atribuudid
		// moodustame atribuutidest stringi
		$attr_list = "";
		$name = "";
		foreach($data as $k => $v)
		{
			if ($k == "name")
			{
				$name = $v;
			}
			// whats up with this id?
			elseif ($k == "id")
			{
				$attr_list .= " name='$v'";
			}
			elseif ($k == "domid")
			{
				$attr_list .= " id='$v'";
			}
			elseif ($k == "title" and !empty ($v))
			{
				$attr_list .= " title='$v'";
			}
			elseif ($v != "")
			{
				if ($k == "nowrap")
				{
					$attr_list .= " $k";
				}
				elseif ($k == "classid")
				{
					$attr_list .= " class='$v'";
				}
				else
				{
					$attr_list .= " $k='$v'";
				};
			};
		};

		// koostame tagi
		$retval = "";
		if (!empty($name))
		{
			$retval = "<" . $name . $attr_list . ">\n";
		};
		// ja tagastame selle
		return $retval;
	}

	// alias eelmisele, monikord voiks selle kasutamine loetavusele kaasa aidata
	function opentag($data)
	{
		return $this->tag($data);
	}

	// loeb faili. Hiljem liigutame selle kuhugi baasklassi
	function get_file_contents($name,$bytes = 8192)
	{
		$fh = fopen($name,"r");
		$data = fread($fh,$bytes);
		fclose($fh);
		return $data;
	}

	// xml funktsioonid
	function _xml_start_element($parser,$name,$attrs)
	{
		if (!isset($attrs["value"]))
		{
			$attrs["value"] = "";
		};
		switch($name)
		{
			// vaikimisi määratud sorteerimisjärjekord
			case "default_order":
				$this->default_order = $attrs["value"];
				$this->default_odir = isset($attrs["order"]) ? $attrs["order"] : "";
				break;

			// tabeli atribuudid
			case "tableattribs":
				$this->tableattribs = $attrs;
				break;

			// välimise tabeli atribuudid
			case "frameattribs":
				$this->frameattribs = $attrs;
				break;

			case "framebgcolor":
				$this->framebgcolor = isset($attrs["bgcolor"]) ? $attrs["bgcolor"] : "";
				$this->framebgclass = isset($attrs["class"]) ? $attrs["class"] : "";
				break;

			case "titlebar":
				$this->titlestyle = isset($attrs["style"]) ? $attrs["style"] : "";
				// lauri muudetud
				$this->headerlinkclassid = isset($attrs["linkclass"]) ? $attrs["linkclass"] : "";
				break;

			// tavalise (mittesorteeritava) headeri stiil
			case "header_normal":
				$this->header_normal = $attrs["value"];
				break;

			// sorteeritava headeri stiil
			case "header_sortable":
				$this->header_sortable = $attrs["value"];
				break;

			case "group_style":
				$this->group_style = $attrs["value"];
				break;

			case "group_add_els_style":
				$this->group_add_els_style = $attrs["value"];
				break;

			// filtri stiil
			case "filter_normal":
				$this->filter_normal = $attrs["value"];
				break;

			// valitud filtri stiil
			case "filter_active":
				$this->filter_active = $attrs["value"];
				break;

			// stiil, mida kasutada parajasti sorteeritud välja headeri näitamiseks
			case "header_sorted":
				$this->header_sorted = $attrs["value"];
				break;

			// stiilid contenti kuvamiseks
			case "content_style1":
				$this->style1 = $attrs["value"];
				$this->bgcolor1 = isset($attrs["bgcolor"]) ? $attrs["bgcolor"] : "";
				break;

			case "content_style2":
				$this->style2 = $attrs["value"];
				$this->bgcolor2 = isset($attrs["bgcolor"]) ? $attrs["bgcolor"] : "";
				break;

			// stiilid millega kuvatakse sorteeritud v?lja sisu
			case "content_style1_selected":
				$this->selected1 = $attrs["value"];
				$this->selbgcolor1 = isset($attrs["bgcolor"]) ? $attrs["bgcolor"] : "";
				break;

			case "content_style2_selected":
				$this->selected2 = $attrs["value"];
				$this->selbgcolor2 = isset($attrs["bgcolor"]) ? $attrs["bgcolor"] : "";
				break;

			// stiilid contenti kuvamiseks <tr> jaoks
			case "content_tr_style1":
				$this->tr_style1 = $attrs["value"];
				break;

			case "content_tr_style2":
				$this->tr_style2 = $attrs["value"];
				break;

			case "content_tr_sel":
				$this->tr_sel = $attrs["value"];
				break;

			case "content_tr_active":
				$this->tr_active = $attrs["value"];
				break;

			// actionid
			case "action":
				$this->actions[] = $attrs;
				break;

			case "actionrows":
				$this->actionrows = $attrs["value"];
				break;

			// väljad
			case "field":
				$temp = array();
				while(list($k,$v) = each($attrs))
				{
					$temp[$k] = $v;
				};
				$this->rowdefs[] = $temp;

				if (isset($attrs["numeric"]))
				{
					$this->nfields[$attrs["name"]] = 1;
				};

				if (!empty($attrs["header_normal"]))
				{
					$this->col_styles[$attrs["name"]]["header_normal"] = $attrs["header_normal"];
				}
				if (!empty($attrs["header_sortable"]))
				{
					$this->col_styles[$attrs["name"]]["header_sortable"] = $attrs["header_sortable"];
				}
				if (!empty($attrs["header_sorted"]))
				{
					$this->col_styles[$attrs["name"]]["header_sorted"] = $attrs["header_sorted"];
				}
				if (!empty($attrs["content_style1"]))
				{
					$this->col_styles[$attrs["name"]]["content_style1"] = $attrs["content_style1"];
				}
				if (!empty($attrs["content_style2"]))
				{
					$this->col_styles[$attrs["name"]]["content_style2"] = $attrs["content_style2"];
				}
				if (!empty($attrs["content_sorted_style1"]))
				{
					$this->col_styles[$attrs["name"]]["content_sorted_style1"] = $attrs["content_sorted_style1"];
				}
				if (!empty($attrs["content_sorted_style2"]))
				{
					$this->col_styles[$attrs["name"]]["content_sorted_style2"] = $attrs["content_sorted_style2"];
				}
				if (!empty($attrs["group_style"]))
				{
					$this->col_styles[$attrs["name"]]["group_style"] = $attrs["group_style"];
				}
				if (!empty($attrs["filter_normal"]))
				{
					$this->col_styles[$attrs["name"]]["filter_normal"] = $attrs["filter_normal"];
				}
				if (!empty($attrs["filter_active"]))
				{
					$this->col_styles[$attrs["name"]]["filter_active"] = $attrs["filter_active"];
				}
				break;

			default:
				// do nothing
		}; // end of switch
	}

	function define_field($args = array())
	{
		$this->rowdefs[] = $args;
		if (isset($args["numeric"]))
		{
			$this->nfields[$args["name"]] = 1;
		}

		### filter definition for UI parsing
		if (!empty ($args["filter"]))
		{
			### add filter definition
			$filter = $args["filter"];
			$filter_key = count ($this->filters) + 1;
			$this->filters[$args["name"]] = array (
				"key" => $filter_key,
				"filter" => $filter,
				"name" => $args["name"],
				"active" => false,
			);
			$this->filter_index[$filter_key] = $args["name"];
		}
	}

	function remove_field($name)
	{
		$tmp = $this->rowdefs;
		foreach($tmp as $k => $v)
		{
			if ($v["name"] == $name)
			{
				unset($this->rowdefs[$k]);
			}
		}
	}

	function _xml_end_element($parser,$name)
	{
		// actually, this is only a dummy function that does nothing
	}

	function parse_xml_def_string($xml_data)
	{
		$xml_parser = xml_parser_create();
		xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
		xml_set_object($xml_parser,&$this);
		xml_set_element_handler($xml_parser,"_xml_start_element","_xml_end_element");
		if (!xml_parse($xml_parser,$xml_data))
		{
			echo(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($xml_parser)),
			xml_get_current_line_number($xml_parser)));
		};
	}

	function set_layout($def)
	{
		$realdef = "generic";
		if ($def == "cool")
		{
			$realdef = "cool";
		};
		$this->parse_xml_def($realdef . "_table");
	}

	function parse_xml_def($file)
	{
		//if (substr($file,0,1) != "/"  && substr($file,0,2) != "C:")
		if (substr($file,0,1) != "/" && !preg_match("/^[a-z]:/i", substr($file,0,2)))
		{
			//if (!is_admin())
			//{
			$path = aw_ini_get("site_basedir") . "/xml/" . $file . ".xml";
			//}

			if (!file_exists($path))
			{
				$path = aw_ini_get("basedir") . "/xml/" . $file . ".xml";
			}
		}
		else
		{
			$path = $file;
		};
		$xml_data = $this->get_file_contents($path);
		return $this->parse_xml_def_string($xml_data);
	}

	////
	// !this makes sure that all sort properties (sortby, sort_order) are
	// arrays and not strings - just to unify things
	function make_sort_prop_arrays()
	{
		if (!is_array($this->sortby))
		{
			$this->sortby = ($this->sortby == "" ? array() : array($this->sortby => $this->sortby));
		}
		if (!is_array($this->sorder))
		{
			$tmp = $this->sorder;
			$this->sorder = array();
			foreach($this->sortby as $_coln => $_eln)
			{
				$this->sorder[$_coln] = $tmp == "" ? "asc" : $tmp;
				$this->sorder[$_eln] = $tmp == "" ? "asc" : $tmp;
			}
		}
		if (is_array($this->vgroupdat))
		{
			foreach($this->vgroupdat as $_eln => $dat)
			{
				if ($dat["sort_el"])
				{
					$this->sorder[$_eln] = $dat["sort_order"];
				}
			}
		}
		if (is_array($this->rgroupsortdat))
		{
			foreach($this->rgroupsortdat as $_eln => $dat)
			{
				if ($dat["sort_el"])
				{
					$this->sorder[$_eln] = $dat["sort_order"];
				}
			}
		}
	}

	function draw_titlebar_under_rgrp()
	{
		$tbl .= "<tr>\n";
		foreach($this->rowdefs as $k => $v)
		{
			// the headers between groups are never clickable - less confusing that way
			$tbl .= $this->opentag(array(
				"name" => "td",
				"title" => $v["tooltip"],
				"classid" => ($this->col_styles[$v["name"]]["header_normal"] ? $this->col_styles[$v["name"]]["header_normal"] : $this->header_normal),
				"align" => ($v["talign"] ? $v["talign"] : "center"),
				"valign" => ($v["tvalign"] ? $v["tvalign"] : ""),
				"bgcolor" => ($this->tbgcolor ? $this->tbgcolor : ""),
				"width" => ($v["width"] ? $v["width"] : "")
			));

			// if the column is sortable, turn it into a link
			if ($v["sortable"])
			{
				// by default (the column is not sorted) don't show any arrows
				$sufix = "";
				// by default, if a column is not sorted and you click on it, it should be sorted asc
				$so = "asc";

				// kui on sorteeritud selle v?lja j?rgi
				if ($this->sortby[$v["name"]])
				{
					$sufix = $this->sorder[$v["name"]] == "desc" ? $this->up_arr : $this->dn_arr;
					$so = $this->sorder[$v["name"]] == "desc" ? "asc" : "desc";
				}
				if ($_GET["sort_order"])
				{
					$so = $_GET["sort_order"] == "asc" ? "desc" : "asc";
				}
				$url = aw_global_get("REQUEST_URI");
				$url = preg_replace("/sortby=[^&$]*/","",$url);
				$url = preg_replace("/sort_order=[^&$]*/","",$url);
				$url = preg_replace("/&{2,}/","&",$url);
				$url = str_replace("?&", "?",$url);
				$sep = (strpos($url,"?") === false) ?	"?" : "&";
				$url .= $sep."sortby=".$v["name"]."&sort_order=".$so;

				$tbl .= "<b><a href='$url'>$v[caption] $sufix</a></b>";
			}
			else
			{
				$tbl .= $v["caption"];
			};
			$tbl .= "</td>\n";
		}

		// kui actionid on defineeritud, siis joonistame nende jaoks vajaliku headeri
		if (is_array($this->actions) && (sizeof($this->actions) > 0))
		{
			$tbl .= $this->opentag(array(
				"name" => "td",
				"align" => "center",
				"classid" => $this->header_normal,
				"colspan" => sizeof($this->actions)
			));
			$tbl .= "Tegevused";
			$tbl .= "</td>\n";
		};
		$tbl .= "</tr>";
		return $tbl;
	}

	function do_col_rgrouping($rgroupby, $rgroupdat, $rgroupby_sep, $v, $rowid, $row_style)
	{
		$tbl = "";
		foreach($rgroupby as $rgel)
		{
			$_a = preg_replace("/<a (.*)>(.*)<\/a>/U","\\2",$v[$rgel]);
			if ($this->lgrpvals[$rgel] != $_a)
			{
				// kui on uus v22rtus grupeerimistulbal, siis paneme rea vahele
				if (is_array($rgroupdat[$rgel]) && count($rgroupdat[$rgel]) > 0)
				{
					$tbl.=$this->opentag(array(
						"name" => "td",
						"colspan" => count($this->rowdefs),
					));

					if (isset($rgroupby_sep[$rgel]["real_sep_before"]))
					{
						$tbl .= $rgroupby_sep[$rgel]["real_sep_before"];
					}

					$tbl .= $this->opentag(array(
						"name" => "span",
						"classid" => ($this->col_styles[$v["name"]]["group_style"] ? $this->col_styles[$v["name"]]["group_style"] : $this->group_style)
					));
					$tbl.= create_links($_a);
					$tbl .= "</span>";
				}
				else
				{
					$tbl.=$this->opentag(array(
						"name" => "td",
						"colspan" => count($this->rowdefs),
						"classid" => ($this->col_styles[$v["name"]]["group_style"] ? $this->col_styles[$v["name"]]["group_style"] : $this->group_style)
					));
					if (isset($rgroupby_sep[$rgel]["real_sep_before"]))
					{
						$tbl .= $rgroupby_sep[$rgel]["real_sep_before"];
					}
					$tbl.= create_links($_a);
				}

				$this->lgrpvals[$rgel] = $_a;
				// if we should display some other elements after the group element
				// they will be passed in the $rgroupdat array
				if (isset($this->group_add_els_style) && $this->group_add_els_style != "")
				{
					$tbl .= $this->opentag(array(
						"name" => "span",
						"classid" => $this->group_add_els_style
					));
				}
				$val = "";
				if (is_array($rgroupdat[$rgel]))
				{
					$val .= $rgroupby_sep[$rgel]["pre"];
					$_ta = array();
					foreach($rgroupdat[$rgel] as $rgdat)
					{
						if (trim($v[$rgdat["el"]]) != "")
						{
							$_ta[] = $rgdat["sep"].$v[$rgdat["el"]].$rgdat["sep_after"];
						}
					}
					$val .= join($rgroupby_sep[$rgel]["mid_sep"],$_ta);
					$val .= $rgroupby_sep[$rgel]["after"];
				}
				$tbl .= create_links(str_replace("[__jrk_replace__]",$this->rgroupcounts[$_a],$val));

				if (isset($this->group_add_els_style) && $this->group_add_els_style != "")
				{
					$tbl .= "</span>";
				}
				$tbl .= "</td></tr>";

				// draw the damn titlebar under the grouping element if so instructed
				if ($this->titlebar_under_groups)
				{
					$tbl.=$this->draw_titlebar_under_rgrp();
				}

				$tbl .= $this->opentag(array("name" => "tr", "domid" => $rowid, "class" => $row_style));
			}
		}
		return $tbl;
	}

	////
	// !this calculates how many elements are there in each rgroup and puts them in $this->rgroupcounts
	function do_rgroup_counts($rgroupby)
	{
		$this->rgroupcounts = array();
		foreach($this->data as $row)
		{
			foreach($rgroupby as $rgel)
			{
				$this->rgroupcounts[$row[$rgel]] ++;
			}
		}
	}

	////
	// !draws a listbox pageselector.
	// parameters:
	//	style - id of the css style to apply to the page
	//	records_per_page - number of records on each page
	function draw_lb_pageselector($arr)
	{
		$this->read_template("lb_pageselector.tpl");
		return $this->finish_pageselector($arr);
	}

	function draw_text_pageselector($arr)
	{
		$this->read_template("text_pageselector.tpl");
		return $this->finish_pageselector($arr);
	}

	function draw_button_pageselector($arr)
	{
		$this->read_template("button_pageselector.tpl");
		return $this->finish_pageselector($arr);
	}

	function finish_pageselector($arr)
	{
		extract($arr);
		$ru = preg_replace("/ft_page=\d*/", "", aw_global_get("REQUEST_URI"));
		$sep = "&";
		if (strpos($ru, "?") === false)
		{
			$sep = "?";
		}
		$ru = $ru.$sep;
		$url = preg_replace("/\&{2,}/","&",$ru);
		$style = "";
		if ($arr["style"])
		{
			$style = "class=\"style_".$style."\"";
		}
		$_drc = ($arr["d_row_cnt"] ? $arr["d_row_cnt"] : $this->d_row_cnt);
		$num_pages = $_drc / $records_per_page;
		for ($i = 0; $i < $num_pages; $i++)
		{
			$from = $i*$records_per_page+1;
			$to = min(($i+1)*$records_per_page, $_drc);
			$this->vars(array(
				"style" => $arr["style"],
				"url" => $url . "ft_page=".$i,
				"pageurl" => $url,
				"text" => $from . " - " . $to,
				"ft_page" => $i,
				"pagenum" => $i+1,
			));
			$rv .= $this->parse($GLOBALS["ft_page"] == $i ? "sel_page" : "page");
			if ($i < ($num_pages - 1) && $this->is_template("sep"))
			{
				$rv .= $this->parse("sep");
			}
		}
		$this->vars(array(
			"page" => $rv,
		));
		return $this->parse();
	}

	function _get_sh_count_by_parent($parent)
	{
		$ret = 0;
		foreach($this->rowdefs as $rd)
		{
			if ($rd["parent"] == $parent)
			{
				$tmp = $this->_get_sh_count_by_parent($rd["name"]);
				if ($tmp == 0)
				{
					$ret++;
				}
				else
				{
					$ret += $tmp;
				}
			}
		}
		return $ret;
	}

	function _get_max_level_cnt($parent)
	{
		$this->_gml++;
		if ($this->_gml > $this->_max_gml)
		{
			$this->_max_gml = $this->_gml;
		}
		foreach($this->rowdefs as $rd)
		{
			if ($rd["parent"] == $parent)
			{
				$this->_get_max_level_cnt($rd["name"]);
			}
		}
		$this->_gml--;
	}

	function _req_draw_header($parent)
	{
		$this->_sh_req_level++;
		$tbl = "";
		$subs = array();
		$cell_count = 0;
		// make header!
		$tbl .= "<tr>\n";
		foreach($this->rowdefs as $k => $v)
		{
			if (!(($parent == "" && empty($v["parent"])) || isset($parent[$v["parent"]])))
			{
				continue;
			}

			$subs[$v["name"]] = $v["name"];
		}

		if (!count($subs))
		{
			$this->_sh_req_level--;
			return "";
		}

		$tbl2 = $this->_req_draw_header($subs);

		foreach($this->rowdefs as $k => $v)
		{
			if (!(($parent == "" && empty($v["parent"])) || isset($parent[$v["parent"]])))
			{
				continue;
			}

			$style = false;
			if (isset($v["sortable"]))
			{
				if (isset($this->sortby[$v["name"]]))
				{
					$style_key = "header_sorted";
				}
				else
				{
					$style_key = "header_sortable";
				};
			}
			else
			{
				$style_key = "header_normal";
			};
			$style = isset($this->col_styles[$v["name"]][$style_key]) ? $this->col_styles[$v["name"]][$style_key] : "";
			if (!$style)
			{
				$style = (isset($v["sortable"]) ? (isset($this->sortby[$v["name"]]) ? $this->header_sorted : $this->header_sortable) : $this->header_normal);
			}

			$sh_cnt = $this->sh_counts_by_parent[$v["name"]];
			$tbl.=$this->opentag(array(
				"name" => "td",
				"title" => $v["tooltip"],
				"classid"=> $style,
				"align" => isset($v["talign"]) ? $v["talign"] : "center",
				"valign" => isset($v["tvalign"]) ? $v["tvalign"] : "",
				"bgcolor" => isset($this->tbgcolor) ? $this->tbgcolor : "",
				"nowrap" => isset($v["nowrap"]) ? 1 : "",
				"width" => isset($v["width"]) ? $v["width"] : "",
				"colspan" => ($sh_cnt > 0 ? $sh_cnt : 1),
				"rowspan" => ($sh_cnt == 0 ? $this->max_sh_count - ($this->_sh_req_level-1) : 1)
			));

			// if the column is sortable, turn it into a link
			if (isset($v["sortable"]))
			{
				// by default (the column is not sorted) don't show any arrows
				$sufix = "";
				// by default, if a column is not sorted and you click on it, it should be sorted asc
				$so = "asc";

				// kui on sorteeritud selle välja järgi
				if (isset($this->sortby[$v["name"]]))
				{
					$sufix = $this->sorder[$v["name"]] == "desc" ? $this->up_arr : $this->dn_arr;
					$so = $this->sorder[$v["name"]] == "desc" ? "asc" : "desc";
				}
				else
				if ($_GET["sort_order"] && $_GET["sortby"] == $v["name"])
				{
					$sufix = $_GET["sort_order"] == "desc" ? $this->up_arr : $this->dn_arr;
					$so = $_GET["sort_order"] == "desc" ? "asc" : "desc";
				}
				$url = aw_global_get("REQUEST_URI");
				$url = preg_replace("/sortby=[^&$]*/","",$url);
				$url = preg_replace("/sort_order=[^&$]*/","",$url);
				$url = preg_replace("/&{2,}/","&",$url);
				$url = str_replace("?&", "?",$url);
				$sep = (strpos($url,"?") === false) ?	"?" : "&";
				$url .= $sep."sortby=".$v["name"]."&sort_order=".$so;

				$tbl .= "<b><a href='$url'>$v[caption] $sufix</a></b>";
			}
			else
			{
				$tbl .= $v["caption"];
			};

			// ### add filter if defined for current column
			// if (isset ($this->filters[$v["name"]]))
			// {
				// $filter_values = $this->filters[$v["name"]]["filter"];
				// $filter_active = $this->filters[$v["name"]]["active"];
				// $filter_name = $this->filter_name;

				// ### get filter change url
				// $url = preg_replace("/{$this->filter_name}=\d,\d/", "", aw_global_get("REQUEST_URI"));
				// $sep = (strpos($url, "?") === false) ? "?" : "&";
				// $url = $url . $sep;
				// $url = preg_replace("/\&{2,}/","&",$url);

				// ### format active filter
				// $filter_active = $filter_active ? 'red' : 'white';

				// ### add filter selectbox
				// $args = array (
					// "name" => $filter_name,
					// "options" => $filter_values,
					// "onchange" => "window.location='{$url}{$filter_name}='+{$this->filters[$v["name"]]["key"]}+','+this.options[this.selectedIndex].value",
					// "textsize" => "10px",
				// );
				// $tbl .= '<div style="width: 100%; background-color: ' . $filter_active . ';">' . html::select ($args) . '</div>';
			// }

			$tbl .= "</td>\n";
			$cell_count++;
		}

		// kui actionid on defineeritud, siis joonistame nende jaoks vajaliku headeri
		if (is_array($this->actions) && (sizeof($this->actions) > 0))
		{
			$tbl .= $this->opentag(array(
				"name" => "td",
				"align" => "center",
				"classid" => $this->header_normal,
				"colspan" => sizeof($this->actions)
			));
			$tbl .= "Tegevused";
			$tbl .= "</td>\n";
			$cell_count++;
		};

		if ($this->use_chooser)
		{
			$tbl .= $this->opentag(array(
				"name" => "td",
				"align" => "center",
				"classid" => $this->header_normal,
			));
			$name = $this->chooser_config["name"];
			$caption = isset($this->chooser_config["caption"]) ? $this->chooser_config["caption"] : "X";
			$tbl .= "<a href='javascript:selall(\"${name}\")'>" . $caption . "</a>";
			$tbl .= "</td>";
			$cell_count++;
		};

		// header kinni
		$tbl .= "</tr>";

		if (empty ($tbl2) and count ($this->filters))
		{
			$tbl2 = "<tr>\n";

			foreach($this->rowdefs as $k => $v)
			{
				$filter_style = "filter_normal";

				### add filter if defined for current column
				if (isset ($this->filters[$v["name"]]))
				{
					$filter_values = $this->get_filter ($v["name"]);
					$filter_name = $this->filter_name;

					### get filter change url
					$url = preg_replace("/{$this->filter_name}=\d,\d/", "", aw_global_get("REQUEST_URI"));
					$sep = (strpos($url, "?") === false) ? "?" : "&";
					$url = $url . $sep;
					$url = preg_replace("/\&{2,}/","&",$url);

					### (re)set filter style
					$filter_style = $this->filters[$v["name"]]["active"] ? "filter_active" : $filter_style;

					### add filter selectbox
					$args = array (
						"name" => $filter_name,
						"options" => $filter_values,
						"onchange" => "window.location='{$url}{$filter_name}='+{$this->filters[$v["name"]]["key"]}+','+this.options[this.selectedIndex].value",
					);
					$filter_contents = html::select ($args);
				}
				else
				{
					$filter_contents = "&nbsp;";
				}

				$tbl2 .= $this->opentag(array(
					"name" => "td",
					"align" => "center",
					"classid" => $this->$filter_style,
				));
				$tbl2 .= $filter_contents;
				$tbl2 .= "</td>\n";
				$cell_count--;
			}

			while ($cell_count--)
			{
				$tbl2 .= $this->opentag(array(
					"name" => "td",
					"align" => "center",
					"classid" => $this->filter_normal,
				));
				$tbl2 .= "&nbsp;";
				$tbl2 .= "</td>\n";
			}

			$tbl2 .= "</tr>\n";
		}

		$this->_sh_req_level--;
		return $tbl.$tbl2;
	}

	function get_automatic_filter ($field_name)
	{
		$filter = array ();

		foreach ($this->data as $row)
		{
			$filter[] = $row[$field_name];
		}

		$filter = array_unique ($filter);
		$filter = (count ($filter) > 1) ? $filter : array ();
		return $filter;
	}

	function get_filter ($field_name)
	{
		### get filter values
		if ($this->filters[$field_name]["filter"] === "automatic")
		{
			$filter = $this->get_automatic_filter ($field_name);
		}
		else
		{
			$filter = $this->filters[$field_name]["filter"];
		}

		### add "All" selection
		$filter = array_merge (array (0 => t("Kõik")), $filter);

		foreach ($this->selected_filters as $selected_filter_key => $filter_selection)
		{
			if ($selected_filter_key == $this->filters[$field_name]["key"])
			{ ### add selected item to first position
				$filter = array_merge (array ("_" => $filter[$filter_selection]), $filter);
				$this->filters[$field_name]["active"] = true;
				break;
			}
		}

		$this->filters[$field_name]["filter"] = $filter;
		return $filter;
	}
}

// this is needed to make this work with get_instance
class vcl_table extends aw_table
{
	function vcl_table($arr = array())
	{
		return $this->aw_table($arr);
	}

	function init_vcl_property($arr)
	{
		// I need access to class information!
		$pr = &$arr["property"];
		if (!is_object($pr["vcl_inst"]))
		{
			$this->set_layout("generic");
			if (is_array($arr["columns"]) && sizeof($arr["columns"]) > 0)
			{
				foreach($arr["columns"] as $ckey => $cval)
				{
					if ($cval["table"] != $pr["name"])
					{
						continue;
					};
					$this->define_field(array(
						"name" => $ckey,
						"caption" => $cval["caption"],
						"sortable" => $cval["sortable"],
					));
				};
			};
			$pr["vcl_inst"] = $this;
		};
		return array($pr["name"] => $pr);
	}

	function get_html()
	{
		$this->sort_by();
		$rv = $this->draw();
		return $rv;
	}

	/** assumes that table columns are defined - iterates over object_list passed and reads correct props from it

		@comment
			$args can contain:

				change_col - column name with change link to object
	**/
	function data_from_ol($ol, $args = array())
	{
		$clss = aw_ini_get("classes");

		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$data = array("oid" => $o->id());
			foreach($this->rowdefs as $k => $v)
			{
				if ($v["name"] == "oid")
				{
					$val = $o->id();
				}
				else
				if ($v["name"] == "createdby")
				{
					$val = $o->createdby();
				}
				else
				if ($v["name"] == "modifiedby")
				{
					$val = $o->modifiedby();
				}
				else
				if ($v["name"] == "created")
				{
					$val = $o->created();
				}
				else
				if ($v["name"] == "modified")
				{
					$val = $o->modified();
				}
				else
				if ($v["name"] == "class_id")
				{
					$val = $clss[$o->class_id()]["name"];
				}
				else
				if ($v["name"] == "ord")
				{
					$val = $o->ord();
				}
				else
				if ($v["name"] == "change")
				{
					$val = html::get_change_url($o->id(), array("return_url" => get_ru()), "Muuda");
				}
				else
				{
					$val = $o->prop_str($v["name"]);
				}

				if (isset($args["change_col"]) && $args["change_col"] == $v["name"])
				{
					$val = html::get_change_url($o->id(), array("return_url" => get_ru()), parse_obj_name($val));
				}
				$data[$v["name"]] = $val;
			}

			if ($this->use_chooser)
			{
				$data[$this->chooser_config["field"]] = $o->id();
			}

			$this->define_data($data);
		}
	}

}

?>
