<?php
// $Header: /home/cvs/automatweb_dev/vcl/Attic/table.aw,v 2.26 2002/07/23 13:04:04 kristo Exp $
// aw_table.aw - generates the html for tables - you just have to feed it the data
//

class aw_table
{
	////
	// !contructor - paramaters:
	// prefix - a symbolic name for the table so we could tell it apart from the others
	// tbgcolor - default cell background color
	function aw_table($data)
	{
		if (file_exists(aw_ini_get("site_basedir")."/public/img/up.gif"))
		{
			$this->imgurl = aw_ini_get("baseurl")."/img";
		}
		else
		{
			$this->imgurl = aw_ini_get("baseurl")."/automatweb/images";
		}
		$this->up_arr = sprintf("<img src='%s' border='0' />",$this->imgurl . "/up.gif");
		$this->dn_arr = sprintf("<img src='%s' border='0' />",$this->imgurl . "/down.gif");

		// prefix - kasutame sessioonimuutujate registreerimisel
		$this->prefix = $data["prefix"];
		// table cell background color
		$this->tbgcolor = $data["tbgcolor"];

		$this->header_attribs = array();

		// ridade värvid (och siis stiilid) muutuvad
		// siin defineerime nad
		$this->style1 = "#AAAAAA";
		$this->style2 = "#CCCCCC";

		// initsialiseerime muutujad
		$this->rowdefs = array();
		$this->data = array();
		$this->actions = array();

		// esimene kord andmeid sisestada?
		// seda on vaja selleks, et määrata default sort order.
		$this->first = true;
	}

	////
	// !sisestame andmed
	function define_data($row) 
	{
		$this->data[] = $row;
	}

	////
	// !merge the given data with the last data entered
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

	////
	// !sorts the data previously entered
	// field - optional, what to sort by. you really don't need to specify this, the table can manage it on it's own
	// sorder - sorting order - asc/desc. you really don't need to specify this, the table can manage it on it's own
	// rgroupby - an array of elements whose values will be grouped in the table
	// vgroupby - array of elements that will be vertically grouped
	function sort_by($params = array()) 
	{
		// see peaks olema array,
		// kus on regitud erinevate tabelite andmed
		$aw_tables = aw_global_get("aw_tables"); 
		$sess_field_key   = $this->prefix . "_sortby";
		$sess_field_order = $this->prefix . "_sorder";

		// figure out the column by which we must sort
		// start from the parameters
		if (!($this->sortby = $params["field"]))
		{
			// if it was not specified as a parameter the next place is the url
			if (!($this->sortby = aw_global_get("sortby")))
			{
				// and if it is not in the url either, we will try the session
				if (!($this->sortby = $aw_tables[$sess_field_key]))
				{
					// and finally we get the default
					$this->sortby = $this->default_order;
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
		$this->rgroupby = $params["rgroupby"];
		$this->vgroupby = $params["vgroupby"];

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
	// then it must sort first by the rgroup element(s), and then the sorting element(s) 
	function sorter($a,$b)
	{
		// what the hell is going on here you ask? well. 
		// basically the idea is that we go over the sorted columns until we find two values that are different
		// and then we can compare them and thus we get to sort the entire array.
		// why don't we just concatenate the strings together? well, because what if the first is a text element, the next
		// a number and the 3rd a text element - if we cat them together we lose the ability to do numerical comparisons..

		$skip = false;

		if (is_array($this->vgroupby))
		{
			foreach($this->vgroupby as $_rgcol => $rgel)
			{
				$v1 = $a[$rgel];
				$v2 = $b[$rgel];
				$this->u_sorder = $this->sorder[$_rgcol];
				$this->sort_flag = $this->nfields[$_rgcol] ? SORT_NUMERIC : SORT_REGULAR;
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
				$v1 = $a[$rgel];
				$v2 = $b[$rgel];
				$this->u_sorder = $this->sorder[$_rgcol];
				$this->sort_flag = $this->nfields[$_rgcol] ? SORT_NUMERIC : SORT_REGULAR;
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
				$v1 =$a[$_eln];
				$v2 =$b[$_eln];
				$this->u_sorder = $this->sorder[$_eln];
				$this->sort_flag = $this->nfields[$_eln] ? SORT_NUMERIC : SORT_REGULAR;
				if ($v1 != $v2)
				{
					break;
				}
			}
		}

		if ($this->sort_flag == SORT_NUMERIC)
		{
			if (((int)$v1) == ((int)$v2))
			{
				return 0;
			}

			if ($this->u_sorder == "asc")
			{
				return ((int)$v1) < ((int)$v2) ? -1 : 1;
			}
			else
			{
				return ((int)$v1) > ((int)$v2) ? -1 : 1;
			}
		}
		else
		{
			$_a = strtolower(preg_replace("/<a (.*)>(.*)<\/a>/U","\\2",$v1));
			$_b = strtolower(preg_replace("/<a (.*)>(.*)<\/a>/U","\\2",$v2));
			$ret = strcoll($_a, $_b);
			if ($this->u_sorder == "asc")
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
		};

		extract($arr);
		$PHP_SELF = aw_global_get("PHP_SELF");
		$REQUEST_URI = aw_global_get("REQUEST_URI");
		$tbl = "";

		// moodustame välimise raami alguse
		if (is_array($this->frameattribs))
		{
			$tmp = $this->frameattribs;
			$tmp["name"] = "table";
			$tbl .= $this->opentag($tmp);
			$tbl .= $this->opentag(array("name" => "tr"));
			$tbl .= $this->opentag(array("name" => "td","bgcolor" => $this->framebgcolor));
		};

		// moodustame tabeli alguse
		if (is_array($this->tableattribs))
		{
			$tmp = $this->tableattribs;
			$tmp["name"] = "table";
			$tbl .= $this->opentag($tmp);
		}

		if ($this->headerstring)
		{
			// lauri muudetud
			$colspan = sizeof($this->rowdefs) + sizeof($this->actions)-(int)$this->headerextrasize;
			$tbl .= $this->opentag(array("name" => "tr"));
			$tbl .= $this->opentag(array("name" => "td","colspan" => $colspan,"classid" => $this->titlestyle));
			$tbl .= "<strong>" . $this->headerstring . ": ";
			$tbl .= $this->headerlinks;
			$tbl .= "</strong>";
			$tbl .= $this->closetag(array("name" => "td"));
			// lauri muudetud
			$tbl .= $this->headerextra;
			$tbl .= $this->closetag(array("name" => "tr"));
		}
		
		$tbl .= $this->opentag(array("name" => "tr"));

		// moodustame headeri
		reset($this->rowdefs);
		while(list($k,$v) = each($this->rowdefs)) 
		{
			$ta = array("name" => "td");
	
			// määrame ära headeri stiili
			if ($v["sortable"]) 
			{
				// kui on sorteeritud selle välja järgi
				if ($this->sortby[$v["name"]])
				{
					// peab tegema workaroundi, sest class on reserved word,
					// samas on seda vaja kasutada tabeli cellile stiili andmiseks
					$ta["classid"] = $this->header_sorted;
				} 
				else 
				{
					$ta["classid"] = $this->header_sortable;
				};
			} 
			else 
			{
				// ei ole sorteeritav
				$ta["classid"] = $this->header_normal;
			};

			if ($v["talign"]) 
			{
				$ta["align"] = $v["talign"];
			};

			if ($v["tvalign"]) 
			{
				$ta["valign"] = $v["tvalign"];
			};

			if ($this->tbgcolor) 
			{
				$ta["bgcolor"] = $this->tbgcolor;
			};

			if ($v["nowrap"])
			{
				$ta["nowrap"] = "";
			}

			if ($v["width"])
			{
				$ta["width"] = $v["width"];
			};
				
			$tbl .= $this->opentag($ta);

			// kui on sorteeritav, siis kuvame lingina
			if ($v["sortable"]) 
			{
				// vaikimis näitame allanoolt:
				$sufix = $this->dn_arr;
				// by default, if a column is not sorted and you click on it, it should be sorted asc
				$so = "asc";

				// kui on sorteeritud selle välja järgi
				if ($this->sortby[$v["name"]])
				{
					$sufix = $this->sorder[$v["name"]] == "desc" ? $this->up_arr : $this->dn_arr;
					$so = $this->sorder[$v["name"]] == "desc" ? "asc" : "desc";
				}

				$url = $REQUEST_URI;
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
			$tbl .= $this->closetag(array("name" => "td"));
		};

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
			$tbl .= $this->closetag(array("name" => "td"));
		};

		// header kinni
		$tbl .= $this->closetag(array("name" => "tr"));

		$lgrpval = array();
		// koostame tabeli sisu
		if (is_array($this->data)) 
		{
			reset($this->data);

			// tsükkel üle data
			$counter = 0; // kasutame ridadele erineva värvi andmiseks
			while(list($k,$v) = each($this->data)) 
			{
				$counter++;

				// rida algab
				$tbl .= $this->opentag(array("name" => "tr"));
				
				// grpupeerimine
				if (is_array($rgroupby))
				{
					foreach($rgroupby as $rgel)
					{
						$_a = preg_replace("/<a (.*)>(.*)<\/a>/U","\\2",$v[$rgel]);
						if ($lgrpvals[$rgel] != $_a)
						{
							// kui on uus v22rtus grupeerimistulbal, siis paneme rea vahele
							$tbl.=$this->opentag(array(
								"name" => "td",
								"colspan" => count($this->rowdefs),
								"classid" => $this->group_style
							));
							$tbl.=$_a;
							$lgrpvals[$rgel] = $_a;
							// if we should display some other elements after the group element
							// they will be passed in the $rgroupdat array
							if (is_array($rgroupdat[$rgel]))
							{
								$tbl.=$rgroupby_sep[$rgel]["pre"];
								foreach($rgroupdat[$rgel] as $rgdat)
								{
									$tbl.=$v[$rgdat["el"]].$rgdat["sep"];
								}
								$tbl.=$rgroupby_sep[$rgel]["after"];
							}
							$tbl.=$this->closetag(array(
								"name" => "td"
							));
							$tbl .= $this->closetag(array("name" => "tr"));
							$tbl .= $this->opentag(array("name" => "tr"));
						}
					}
				}

				// tsükkel üle rowdefsi, et andmed oleksid oiges järjekorras
				reset($this->rowdefs);
				while(list($k1,$v1) = each($this->rowdefs)) 
				{
					$rowspan = 1;
					$style = false;
					if (is_array($this->vgroupby))
					{
						if (isset($this->vgroupby[$v1["name"]]))
						{
							// if this column is a part of vertical grouping, check if it's value has changed
							// if it has, then set the new rowspan
							// if not, then skip over this row

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
						if ($this->sortby[$v1["name"]]) 
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
						
					// moodustame celli
					$cell_attribs = array(
						"name"    => "td",
						"classid" => $style,
						"width" => $v1["width"],
						"bgcolor" => $bgcolor,
						"rowspan" => $rowspan
					);

					if ($this->actionrows)
					{
						$cell_attribs["rowspan"]=$this->actionrows;
					};
					
					// eri värvi cellide jaoks muutus
					if ($v1["chgbgcolor"] && $v[$v1["chgbgcolor"]])
					{
						$cell_attribs["style"]="background:".$v[$v1["chgbgcolor"]];
					};

					
					if ($v1["align"]) 
					{
						$cell_attribs["align"] = $v1["align"];
					};

					if ($v1["valign"]) 
					{
						$cell_attribs["valign"] = $v1["valign"];
					};

					if ($v1["nowrap"]) 
					{
						$cell_attribs["nowrap"] = "";
					};

	
					if ($v["bgcolor"]) 
					{
						$cell_attribs["bgcolor"] = $v["bgcolor"];
					};

					// this one overrides the definition given in the table header
					if ($v["style"]) 
					{
						$cell_attribs["classid"] = $v["style"];
					};

					$tbl .= $this->opentag($cell_attribs);

					if ($v1["name"] == "rec") 
					{
						$val = $counter;
					} 
					else 
					{
						if ($v1["strformat"])
						{
							$format = localparse($v1["strformat"],$v);
							$val = sprintf($format,$v[$v1["name"]]);
						}
						else
						{
							$val = $v[$v1["name"]];	
						};
					};

					if ($v1["type"] == "time") 
					{
						$val = date($v1["format"],$val);
					};

					if (!$val && $v1["type"]!="int") 
					{
						$val = "&nbsp;";
					};

					if ($v1["thousands_sep"] != "")
					{
						// insert separator every after every 3 chars, starting from the end. 
						$val = strrev(chunk_split(strrev(trim($val)),3,$v1["thousands_sep"]));
						// chunk split adds one too many separators, so remove that
						$val = substr($val,strlen($v1["thousands_sep"]));
					}
					$val = str_replace("[__jrk_replace__]",$counter,$val);	
					$tbl .= $val;
					$tbl .= $this->closetag(array("name" => "td"));
				};

				// joonistame actionid
				$actionridu = $this->actionrows ? $this->actionrows : 1;

				for ($arow = 1; $arow <= $actionridu; $arow++)
				{
					// uutele actioni ridadele tuleb teha uus <tr>
					if ($arow > 1)
					{
						$tbl.= $this->opentag(array("name"=>"tr"));
					};
					// joonistame actionid
					reset($this->actions);
					$style = (($counter % 2) == 0) ? $this->style1 : $this->style2; 
					while(list($ak,$av) = each($this->actions)) 
					{
						// joonista ainult need actionid, mis siia ritta kuuluvad
						if ($this->actionrows ? ($arow == $av["row"] || ($arow==1 && !$av["row"]) ):1)
						{
							$tdtag=array(
								"name"=>"td",
								"classid" => ($av["style"]) ? $av["style"] : $style,
								"align" => "center"
							);

							$av["cspan"] ? $tdtag["colspan"] = $av["cspan"] : "";
							$av["rspan"] ? $tdtag["rowspan"] = $av["rspan"] : "";

							$tbl .= $this->opentag($tdtag);

							$tbl.=$av["remote"]?
								"<a href='javascript:remote(0,".$av["remote"].",\"$PHP_SELF?".$av["link"]."&id=".$v[$av["field"]].'");\'>'.$av["caption"]."</a>":
								"<a href='$PHP_SELF?" . $av["link"] . "&id=" . $v[$av["field"]] . "&" . $av["field"] . "=" . $v[$av["field"]] . "'>$av[caption]</a>";

							$tbl .= $this->closetag(array("name" => "td"));
						};
					};

					// rida lopeb
					$tbl .= $this->closetag(array("name" => "tr"));
				};
			};
		};
		// sisu joonistamine lopeb
	
		// tabel kinni
		if (is_array($this->tableattribs))
		{
			$tbl .= $this->closetag(array("name" => "table"));
		}

		// raam kinni
		if (is_array($this->frameattribs))
		{
			$tbl .= $this->closetag(array("name" => "td"));
			$tbl .= $this->closetag(array("name" => "tr"));
			$tbl .= $this->closetag(array("name" => "table"));
		};

		// tagastame selle käki
		return $tbl;
	}

	function _format_csv_field($d)
	{
		$new=strtr($d,array('"'=>'""'));
		if (!(strpos($d,';')===false) || $new != $d)
		{
			$new='"'.$new.'"';
		};
		return strip_tags($new);
	}

	// tagastab csv andmed, kustuda välja draw asemel
	function get_csv_file()
	{
		$d=array();
		reset($this->rowdefs);
		$tbl="";
		if (is_array($this->rowdefs))
		while(list($k,$v) = each($this->rowdefs)) 
		{
				$tbl .= ($tbl?";":"").$this->_format_csv_field($v["caption"]);
		};
		$d[]=$tbl;

		
		// koostame tabeli sisu
		if (is_array($this->data)) 
		{
			reset($this->data);
			$cnt=0;
			while(list($k,$v) = each($this->data)) 
			{
				$tbl="";
				$cnt++;
				reset($this->rowdefs);
				if (is_array($this->rowdefs))
				while(list($k1,$v1) = each($this->rowdefs)) 
				{
					if ($v1["name"] == "rec") 
					{
						$val = $cnt;   
					} else 
					{
						if ($v1["strformat"])
						{
							$format = localparse($v1["strformat"],$v);
							$val = sprintf($format,$v[$v1["name"]]);
						}
						else
						{
							$val = $v[$v1["name"]];	
						};
					};

					if ($v1["type"] == "time")
					{
						$val = date($v1["format"],$val);
					};

					if (!$val && $v1["type"]!="int")
					{
						$val = "";
					};

					$tbl .= ($tbl?";":"").$this->_format_csv_field($val);
				};
				$d[]=$tbl;
			};
		};
		// sisu joonistamine lopeb
		return join("\r\n",$d);
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
		while(list($k,$v) = each($data)) 
		{
			if ($k == "name") 
			{
				$name = $v;
			} 
			else
			if ($k == "id") 
			{
				$attribs["name"] = $v;
			} 
			else 
			{
				$attribs[$k] = $v;
			};
		};

		// moodustame atribuutidest stringi
		$attr_list = "";
		if (is_array($attribs)) 
		{
			reset($attribs);
			while(list($k,$v) = each($attribs)) 
			{
				if ($k == "nowrap")
				{
					$attr_list.= " $k ";
				}
				else
				{
					$attr_list .= " $k=\"$v\"";
				}
			};
			// see on workaround, sest "class" on reserved ja seda
			// ei saa array indexina kasutada
			$attr_list = str_replace("classid","class",$attr_list);
		};

		// koostame tagi
		$retval = sprintf("<%s%s>\n",$name,$attr_list);

		// ja tagastame selle
		return $retval;
	}

	// alias eelmisele, monikord voiks selle kasutamine loetavusele kaasa aidata
	function opentag($data) 
	{
		return $this->tag($data);
	}

	// sulgeb tag-i
	function closetag($data) 
	{
		$retval = sprintf("\n</%s>\n",$data["name"]);
		return $retval;
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
		switch($name) 
		{
			// vaikimisi määratud sorteerimisjärjekord
			case "default_order":
				$this->default_order = $attrs["value"];
				$this->default_odir = $attrs["order"];
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
				$this->framebgcolor = $attrs["bgcolor"];
				break;

			case "titlebar":
				$this->titlestyle = $attrs["style"];
				// lauri muudetud
				$this->headerlinkclassid = $attrs["linkclass"];
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

			// stiil, mida kasutada parajasti sorteeritud välja headeri näitamiseks
			case "header_sorted":
				$this->header_sorted = $attrs["value"];
				break;

			// stiilid contenti kuvamiseks
			case "content_style1":
				$this->style1 = $attrs["value"];
				$this->bgcolor1 = $attrs["bgcolor"];
				break;

			case "content_style2":
				$this->style2 = $attrs["value"];
				$this->bgcolor2 = $attrs["bgcolor"];
				break;

			// stiilid millega kuvatakse sorteeritud välja sisu
			case "content_style1_selected":
				$this->selected1 = $attrs["value"];
				$this->selbgcolor1 = $attrs["bgcolor"];
				break;

			case "content_style2_selected":
				$this->selected2 = $attrs["value"];
				$this->selbgcolor2 = $attrs["bgcolor"];
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
				
				if ($attrs["numeric"]) 
				{
					$this->nfields[$attrs["name"]] = 1;
				};
				break;

			default:
				// do nothing
		}; // end of switch
	}

	function define_field($args = array())
	{
		$this->rowdefs[] = $args;
		if ($args["numeric"])
		{
			$this->nfields[$args["name"]] = 1;
		};
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
		return $this->data;
	}

	function parse_xml_def($file) 
	{
		$xml_data = $this->get_file_contents($file);
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
	}
};
?>
