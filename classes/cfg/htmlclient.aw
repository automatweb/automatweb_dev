<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/htmlclient.aw,v 1.88 2004/12/08 20:03:14 duke Exp $
// htmlclient - generates HTML for configuration forms

// The idea is that if we want to implement other interfaces
// for editing objects, then we can just add other clients
// (xmlrpc, rdf, tty, etc) which take care of converting the data
// from the cfgmanager to the required form. 

class htmlclient extends aw_template
{
	function htmlclient($arr = array())
	{
		$tpldir = isset($arr["tpldir"]) ? $arr["tpldir"] : "htmlclient";
		$this->init(array("tpldir" => $tpldir));
		$this->res = "";
		$this->layout_mode = "default";
		$this->form_target = "";
		$this->tpl_vars = $arr["tpl_vars"] ? $arr["tpl_vars"] : array();
		$this->styles = $arr["styles"] ? $arr["styles"] : array();
		$this->tabs = ($arr["tabs"] === false) ? false : true;
		if (!empty($arr["embedded"]))
		{
			$this->embedded = true;
		};
		if (!empty($arr["layout_mode"]))
		{
			$this->set_layout_mode($arr["layout_mode"]);
		};
		if($arr["tplmode"] == "groups")
		{;
			$this->tplmode = "groups";
			$this->sub_tpl = new aw_template();
			$this->sub_tpl->tpl_init($arr["tpldir"]);
			$this->sub_tpl->read_template("group_".$arr["group"].".tpl");
			$this->prop_style = 0;
			$this->use_template = "grouptpl_default.tpl";
		}
		if (!empty($arr["template"]))
		{
			// apparently some places try to specify a template without an extension,
			// deal with it
			if (strpos($arr["template"],".tpl"))
			{
				$this->use_template = $arr["template"];
			}
			else
			{
				$this->use_template = $arr["template"] . ".tpl";
			};
		};
		$this->group_style = "";
		$this->layoutinfo = array();
		$this->start_output();
		if ($this->tabs)
		{
			$this->tp = get_instance("vcl/tabpanel");
		};

		// I even need those in the tabpanel
	}

	function set_layout($arr)
	{
		$this->layoutinfo = $arr;
	}

	function set_form_layout($l)
	{
		$this->form_layout = $l;
	}

	function set_form_target($target = "_top")
	{
		$this->form_target = $target;
		if (!empty($this->form_target))
		{
			if($this->tplmode == "group")
			{
				$this->sub_tpl->vars(array(
					"form_target" => "target='" . $this->form_target . "' ",
				));	
			}
			else
			{
				$this->vars(array(
					"form_target" => "target='" . $this->form_target . "' ",
				));
			}
		};
	}

	function add_content_element($location,$content)
	{
		$this->additional_content[$location] = $content;
	}

	function set_layout_mode($mode)
	{
		$this->layout_mode = $mode;
	}

	function set_group_style($styl)
	{
		$this->group_style = $styl;
		$this->tmp = get_instance("cfg/htmlclient",array("tpldir" => "htmlclient"));
		$this->tmp->read_template($styl . ".tpl");
	}

	// I need to remap all properties, no?

	// so, how do I add a header and a footer?

	// do I make the tabpanel a separate element? and then make it possible to add elements outside
	// the current panel?

	////
	// !Starts the output 
	function start_output($args = array())
	{
		$this->set_parse_method("eval");

	
		$tpl = "default.tpl";
		if (!empty($this->use_template))
		{
			$tpl = $this->use_template;
		};
		$this->read_template($tpl);
		if(!empty($this->tpl_vars))
		{
			$this->vars($this->tpl_vars);
		}
		$script = aw_global_get("SCRIPT_NAME");
		// siia vaja kirjutada see embedded case
		$handler = empty($script) ? "index" : "orb";
		if ($this->embedded)
		{
			$handler = "index";
		};
		if($this->tplmode == "groups")
		{
			$this->sub_tpl->vars(array(
				"handler" => $handler,
			));
		}
		else
		{
			$this->vars(array(
				"handler" => $handler,
			));
		}
		$this->orb_vars = array();
		$this->submit_done = false;
		$this->proplist = array();

		// I need some handler code in the output form, if we have any RTE-s
		$this->rte = false;

	}

	function add_property($args = array())
	{
		// if value is array, then try to interpret
		// it as a list of elements.

		// the (possibly bad) side effect is that the first added panel will be used
		// as the main tab panel. OTOH, the first should always be the one that
		// is added by class_base, so we should be covered
		if ($args["type"] == "tabpanel" && !is_object($this->tabpanel))
		{
			$this->tabpanel = &$args["vcl_inst"];
			return false;
		};

		// I need to redo this
		$wrapchildren = false;

		// but actually, settings parets should take place in class_base itself
		if (isset($args["items"]) && is_array($args["items"]))
		{
			$res = "";
			// if wrapchildren is set, then we attempt to place the properties
			// next to each other using a HTML table. Other output clients
			// can probably just ignore it, since it really is only used
			// to lay out blocks of HTML
			if (isset($args["wrapchildren"]))
			{
				$wrapchildren = true;
				$cnt = count($args["items"]);
			};

			$i = 1;
			foreach($args["items"] as $el)
			{
				if ($wrapchildren)
				{
					if ($i == 1)
					{
						$res .= $this->draw_element(array(
							"type" => "text",
							"value" => "<table border='0' width='100%'><tr><td valign='top' width='200'><small>",
						));
					};
				};
	 			$this->mod_property(&$el);
				$res .= $this->put_subitem($el);
				//$res .= $this->draw_element($el);
				if ($wrapchildren)
				{
					if ($i == $cnt)
					{
						$res .= $this->draw_element(array(
							"type" => "text",
							"value" => "</td></tr></table>",
						));
					}
					else
					{
						$res .= $this->draw_element(array(
							"type" => "text",
							"value" => "</td><td valign='top'>",
						));
					};
				};
				$i++;
			};
			$args["value"] = $res;
			$args["type"] = "text";
		}
		else
		{
			$this->mod_property(&$args);
		};

		$type = isset($args["type"]) ? $args["type"] : "";

		if ($args["layout"])
		{
			$lf = $this->layoutinfo[$args["layout"]];
		}
		else
		{
			$lf = array();
		};

		if ($type == "iframe")
		{
			$src = $args["src"];
			$args["html"] = "<iframe id='contentarea' name='contentarea' src='${src}' style='width: 100%; height: 95%; border-top: 1px solid black;' frameborder='no' scrolling='yes'></iframe>";
		}
		else
		if ($this->layout_mode == "fixed_toolbar")
		{
			$args["html"] = $args["value"];
		}
		else
		if ($args["parent"] && empty($this->layoutinfo[$args["parent"]]))
		{
			$this->proplist[$args["parent"]]["html"] .= $this->put_subitem($args);
		}
		else
		if ($args["layout"] && sizeof($lf["items"]) < ($lf["cols"] * $lf["rows"]))
		{
			$args["html"] = $this->put_subitem($args);
		}
		else
		// hidden elements end up in the orb_vars
		if ($type == "hidden")
		{
			$this->orb_vars[$args["name"]] = $args["value"];
		}
		else
		if ($args["type"] == "submit")
		{
			$args["html"] = $this->put_submit($args);
			$this->submit_done = true;
		}
		else
		if (isset($args["no_caption"]))
		{
			$args["html"] = $this->put_content($args);
		}
		else
		if (isset($args["subtitle"]))
		{
			$args["html"] = $this->put_header_subtitle($args);
		}
		else
		if ($type)
		{
			$args["html"] = $this->put_line($args);
		}
		elseif (!empty($args["caption"]))
		{
			$args["html"] = $this->put_header($args);
		}
		else
		{
			$args["html"] = $this->put_content($args);
		};

		if ($args["parent"] && !empty($this->layoutinfo[$args["parent"]]))
		{
			$this->layoutinfo[$args["parent"]]["items"][] = $args["html"];
		}
		// now I have to check whether this property is placed in a grid
		// if so, place this thing int he grid
		elseif (	!empty($args["layout"]) && 
				!empty($this->layoutinfo[$args["layout"]]) &&
				$this->layoutinfo[$args["layout"]]["type"] == "grid")
		{
			// now for starters lets assume that this grid thingie uses autoflow, I'll implement
			// other things later on. properties come in and will be placed in the correct places
			// in that grid
			$lf = $this->layoutinfo[$args["layout"]];
			$size = $lf["cols"] * $lf["rows"];
			if (sizeof($lf["items"]) < $size)
			{
				// temporary solution to deal with colspans
				$this->layoutinfo[$args["layout"]]["items"][] = $args;
				// but I also need to know how to add the fukken spans!

				// so what happens if a element is put in a cell .. now, I do not want to think
				// about that right now

				// colspan 2, rowspan 2 .. yees?
			}
			else
			{
				$this->proplist[$args["name"]] = $args;
			};
			//$this->proplist[$args["name"]] = $args;

		}
		else
		{
			$this->proplist[$args["name"]] = $args;
		};
	}

	////
	// !Shows an error indicator in the form
	function show_error()
	{
		$this->vars(array(
			"error_text" => "Viga sisendandmetes",
			"webform_error" => $this->style["error"] ? "st".$this->style["error"] : "",
		));
		$this->error = $this->parse("ERROR");
	}

	function mod_property(&$args)
	{
		// that too should not be here. It only forms 2 radiobuttons ...
		// which could as well be done some place else

		// of course this should be here, where the hell else do you
		// want it to be?
		if (empty($args["type"]))
		{
			return false;
		};

		$val = "";
		if ($args["type"] == "status")
		{
			$args["type"] = "chooser";
			$args["options"] = array(
				STAT_ACTIVE => "Jah",
				STAT_NOTACTIVE => "Ei",
			);
		};

		if (empty($args["value"]) && is_callable(array($args["vcl_inst"],"get_html")))
		{
			$args["value"] = $args["vcl_inst"]->get_html();
		}
		if($args["type"] == "reset")
		{
			$args["no_caption"] = 1;
			$args["value"] = $args["caption"];
			unset($args["caption"]);
		}
		if ($args["type"] == "s_status")
		{
			if (empty($args["value"]))
			{
				// default to deactive
				$args["value"] = STAT_NOTACTIVE;
			};
			$args["type"] = "chooser";
			// hm, do we need STAT_ANY? or should I just fix the search
			// do not use dumb value like 3 -- duke
			$args["options"] = array(
				3 => "Kõik",
				STAT_ACTIVE => "Aktiivne",
				STAT_NOTACTIVE => "Deaktiivne",
			);
		};

		if ($args["type"] == "colorpicker")
		{
			$val .= html::textbox(array(
					"name" => $args["name"],
					"size" => 7,
					"maxlength" => 7,
					"value" => isset($args["value"]) ? $args["value"] : "",
			));

			$cplink = $this->mk_my_orb("colorpicker",array(),"css");

			static $colorpicker_script_done = 0;

			$script = "";
			if (!$colorpicker_script_done)
			{
				$script .= "<script type='text/javascript'>\n".
						"var element = 0;\n".
						"function set_color(clr) {\n".
						"document.getElementById(element).value=clr;\n".
						"}\n".
						"function colorpicker(el) {\n".
						"element = el;\n".
						"aken=window.open('$cplink','colorpickerw','height=220,width=310');\n".
						"aken.focus();\n".
						"};\n".
						"</script>";
				$colorpicker_script_done = 1;
			};

			$tx = "<a href=\"javascript:colorpicker('$args[name]')\">Vali</a>";
	
			$val .= html::text(array("value" => $script . $tx));
			$args["value"] = $val;
		};

		if ($args["type"] == "submit")
		{
			if (empty($args["value"]))
			{
				$args["value"] = $args["caption"];
			};
		};

		if ($args["type"] == "container")
		{
			$args["type"] = "text";
		};
	}

	////
	// !Creates a normal line
	function put_line($args)
	{
		$rv = "";
		$caption = $args["caption"];
		unset($args["caption"]);

		// give the first letter of a caption a tooltip
		if (!empty($args["comment"]))
		{
			$caption = html::href(array(
				"url" => "javascript:void(0)",
				"caption" => substr($caption,0,1),
				"title" => $args["comment"],
				)) . substr($caption,1);
		};
		$tpl_vars = array(
				"caption" => $caption,
				"element" => $this->draw_element($args),
				"webform_caption" => !empty($args["style"]["caption"]) ? "st".$args["style"]["caption"] : "",
				"webform_element" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
		);
		if($this->tplmode == "groups" && $this->sub_tpl->is_template($args["name"]))
		{
			//echo "jee";
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($args["name"]);
		}
		else
		{
			$add = "";
			if(!empty($args["capt_ord"]))
			{
				$add = strtoupper("_".$args["capt_ord"]);
			}
			// I wanda mis kammi ma selle tmp-iga tegin
			// different layout mode eh? well, it sucks!
			if (is_object($this->tmp))
			{
				$this->tmp->vars($tpl_vars);
				$rv = $this->tmp->parse("LINE".$add);
			}
			else
			{
				$this->vars($tpl_vars);
				$rv = $this->parse("LINE".$add);
			}
		}
		return $rv;
	}

	////
	// !Creates a submit button
	function put_submit($arr)
	{
		$name = "SUBMIT";
		$tpl_vars = array(
			"sbt_caption" => $arr["caption"] ? $arr["caption"] : "Salvesta",
			"name" => $arr["name"] ? $arr["name"] : "",
			"action" => $arr["action"] ? $arr["action"] : "",
			"webform_content" => !empty($arr["style"]["prop"]) ? "st".$arr["style"]["prop"] : "",
		);
		if($arr["capt_ord"] == "right")
		{
			 $name .= strtoupper("_".$arr["capt_ord"]);
		}
		if($this->tplmode =="groups" && $this->sub_tpl->is_template($arr["name"]))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($arr["name"]);
		}
		elseif($this->tplmode == "groups" && $this->sub_tpl->is_template($name))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($name);
		}
		else
		{
			$this->vars($tpl_vars);
			$rv = $this->parse($name);
		}
		return $rv;
	}

	function put_subitem($args)
	{
		$tpl_vars = array(
			"caption" => $args["caption"],
			"element" => $this->draw_element($args),
		);
		// SUBITEM - element first, caption right next to it
		// SUBITEM2 - caption first, element right next to it
		$tpl = $args["type"] == "checkbox" ? "SUBITEM" : "SUBITEM2";
		if($this->tplmode == "groups" && $this->sub_tpl->is_template($args["name"]))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($args["name"]);
		}
		elseif($this->tplmode == "groups" && $this->sub_tpl->is_template($tpl))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($tpl);
		}
		else
		{
			$this->vars($tpl_vars);
			$rv = $this->parse($tpl);
		}
		return $rv;
	}

	function put_header($args)
	{
		$name = "HEADER";
		$tpl_vars = array(
			"caption" => $args["caption"],
			"webform_header" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
		);
		if($this->tplmode == "groups" && $this->sub_tpl->is_template($args["name"]))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($args["name"]);
		}
		elseif($this->tplmode == "groups" && $this->sub_tpl->is_template($name))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($name);
		}
		else
		{
			$this->vars($tpl_vars);
			$rv = $this->parse($name);
		}
		return $rv;
	}
	
	function put_header_subtitle($args)
	{
		$name = "SUB_TITLE";
		$tpl_vars = array(
			"value" => !empty($args["value"]) ? $args["value"] : $args["caption"],
			"webform_subtitle" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
		);
		if($this->tplmode == "groups" && $this->sub_tpl->is_template($args["name"]))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($args["name"]);
		}
		elseif($this->tplmode == "groups" && $this->sub_tpl->is_template($name))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($name);
		}
		else
		{
			$this->vars($tpl_vars);
			$rv = $this->parse($name);
		}
		return $rv;
	}
	
	function put_content($args)
	{
		$tpl_vars = array(
			//"value" => $args["value"],
			"value" => $this->draw_element($args),
			"webform_content" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
		);
		if($this->tplmode == "groups" && $this->sub_tpl->is_template($args["name"]))
		{
			$this->sub_tpl->vars($tpl_vars);
			$rv = $this->sub_tpl->parse($args["name"]);
		}
		
		else
		{
			$this->vars($tpl_vars);
			$rv = $this->parse("CONTENT");
		}
		return $rv;
	}
	
	////
	// !Finished the output
	function finish_output($arr = array())
	{
		// returns the property names for template debugging
		if(aw_ini_get("debug_mode") != 0 && $GLOBALS["AHTI"] == 1)
		{
			if(is_array($this->proplist))
			{
				foreach($this->proplist as $key => $val)
				{
					echo "$key<br />";
				}
			}
		}
		extract($arr);
		$sbt = "";

		if (!is_array($data))
		{
			$data = array();
		};

		if ($this->submit_done || $this->view_mode == 1)
		{
		
		}
		else
		if (empty($submit) || $submit !== "no")
		{
			$var_name = "SUBMIT";
			$tpl_vars = array(
				"sbt_caption" => "Salvesta",
			);
			if($this->tplmode == "groups" && $this->sub_tpl->is_template($var_name))
			{
				$this->sub_tpl->vars($tpl_vars);
				$sbt = $this->sub_tpl->parse($var_name);
			}
			else
			{
				// I need to figure out whether I have a relation manager
				$this->vars($tpl_vars);
				$sbt = $this->parse($var_name);
			}
			
		};
		$orb_class = ($data["orb_class"]) ? $data["orb_class"] : "cfgmanager";
		unset($data["orb_class"]);
	
		$data = $data + $this->orb_vars;

		$res = "";
		if ($this->error)
		{
			$res .= $this->error;
		};

		// ach! vahi raiska .. siit see jama ju sisse tuleb!
		$vars = array();
		// proplist tehakse enne ja siis layout takka otsa .. and this will break a lot of things
		// now how do I work this out?
		if (sizeof($this->proplist) > 0)
		{
			foreach($this->proplist as $ki => $item)
			{
				if($this->tplmode == "groups")
				{
					if (!empty($item["error"]))
					{
						$var_name = "PROP_ERR_MSG";
						$tpl_vars = array(
							"err_msg" => $item["error"],
						);
						if($this->sub_tpl->is_template($var_name))
						{
							$this->sub_tpl->vars($tpl_vars);
							$vars[$ki] .= $this->sub_tpl->parse($var_name);
						}
						else
						{
							$this->vars($tpl_vars);
							$vars[$ki] .= $this->parse($var_name);
						}
					};
					if (!empty($sbt) && $item["type"] == "aliasmgr")
					{
						$vars[$ki] .= $sbt;
						unset($sbt);
					};
					// noh, aga ega siin ei ole midagi erilist .. kui ma satun gridi otsa,
					// siis ma asendan selle gridi lihtsalt tema leiaudiga
					$vars[$ki] .= $item["html"];
				}
				else
				{
					if (!empty($item["error"]))
					{
						$this->vars(array(
							"err_msg" => $item["error"],
						));
						$res .= $this->parse("PROP_ERR_MSG");
					};
					if (!empty($sbt) && $item["type"] == "aliasmgr")
					{
						$res .= $sbt;
						unset($sbt);
					};
					// noh, aga ega siin ei ole midagi erilist .. kui ma satun gridi otsa,
					// siis ma asendan selle gridi lihtsalt tema leiaudiga
					$res .= $item["html"];
				}
			};
		};

		// i hope that people, who are using those grouptemplates, have decency not to use
		// vbox, hbox and other thatkind of crap -- ahz
		if (!empty($this->layoutinfo))
		{
			// first pass creates contents for all boxes
			foreach($this->layoutinfo as $key => $val)
			{
				// this takes care of all layout boxes with parents 
				if (empty($val["parent"]))
				{
					continue;
				};

				if ("grid" == $val["type"])
				{
					continue;
				};

				$type = $val["type"];
				// it can be one of:
				// 1. hbox
				// 2. vbox
				$tmp = "";
				// geezas christ, this thing is SO bad :(
				if ($type == "vbox")
				{
					$tmp .= "<table border=0 cellpadding=0 cellspacing=0 width='100%'><tr><td valign=top><table border=0 cellspacing=0 cellpadding=0 width=100%>".
					join("</table><table border=0 cellspacing=0 cellpadding=0 width=100%>",$val["items"]).
					"</table></td></tr></table>";
				};
				if ($type == "hbox")
				{
					$tmp .= "<table border=0 cellpadding=0 cellspacing=0><tr><td valign=top><table border=0 cellpadding=0 cellspacing=0>".
					join("</table></td><td valign=top><table border=0 cellpadding=0 cellspacing=0>",$val["items"]).
					"</table></td></tr></table>";
				};
					
				$this->layoutinfo[$val["parent"]]["items"][] = $tmp;
			};


			// second one tries to put together the complete picture
			foreach($this->layoutinfo as $key => $val)
			{
				if (!empty($val["parent"]))
				{
					continue;
				};


				$type = $val["type"];
				// it can be one of:
				// 1. hbox
				// 2. vbox
				if ($type == "vbox")
				{
					$res .= "<table border=0 cellpadding=0 cellspacing=0><tr><td valign=top>".
							join("</td></tr><tr><td valign=top>",$val["items"]).
							"</td></tr></table>";
				};
				if ($type == "hbox")
				{
					if(isset($val['width']))
					{
						$width_array = explode(':',$val['width']);
						$tmp_html = '';
						foreach($val['items'] as $key=>$value)
						{
							$width="";
							if(isset($width_array[$key]))
							{
								$width = 'width="'.$width_array[$key].'"';
							}
							$tmp_html.='<td valign=top '.$width.'>'.$value.'</td>';
						}
						$res.="<table border=0 cellpadding=0 cellspacing=0 width='100%'><tr>$tmp_html</tr></table>";
					}
					else
					{
						$width = (int)100 / sizeof($val["items"]);
						$res .= "<table border=0 cellpadding=0 cellspacing=0 width='100%'><tr><td valign=top width='${width}%'>".
								join("</td><td valign=top width='${width}%'>",$val["items"]).
								"</td></tr></table>";
					}
				};

				if ("grid" == $type)
				{
					$rows = $val["rows"];
					$cols = $val["cols"];
					// siin tuleks siis vast midagi ette võtta nii et näidataks tõesti ainult
					// vajalikke asju. JA, see asi tuleks kuidagi liita seadete vormiga ..
					// oh god, that is just horrible
					$grid = new aw_template();
					$grid->tpl_init("htmlclient");
					$grid->read_template("grid.tpl");

					$items = $val["items"];
					$idx = 0;
					$tres = "";
					$used = array();
					for ($i = 1; $i <= $rows; $i++)
					{
						$cells = "";
						for ($j = 1; $j <= $cols; $j++)
						{
							//print "doing $i * $j<br>";
							if ($used[$i][$j])
							{
								//print "skipping, cause it is used<br>";
								continue;
							};
							// now how do I get the spans to work?
							if (isset($items[$idx]))
							{
								$rowspan = $items[$idx]["rowspan"];
								$colspan = $items[$idx]["colspan"];
								if (empty($rowspan))
								{
									$rowspan = 1;
								};
								if (empty($colspan))
								{
									$colspan = 1;
								};

								if ($rowspan > 1 || $colspan > 1)
								{
									for ($i1 = $i; $i1 <= $i + $rowspan; $i1++)
									{
										for ($j1 = $j; $j1 <= $j + $colspan; $j1++)
										{
											$used[$i1][$j1] = 1;
										}
									};
								};
								// now, how do I leave out stolen cells?
								$grid->vars(array(
									"element" => $this->draw_element($items[$idx]),
									"caption" => $items[$idx]["caption"],
									"colspan" => $colspan,
									"rowspan" => $rowspan,
								));
								// I need to be able to hide cells without a caption
								$tpl = "GRID_CELL";
								if (isset($items[$idx]["no_caption"]))
								{
									$tpl .= "_NO_CAPTION";
								};
								$cells .= $grid->parse($tpl);
							}
							else
							{
								$cells .= $grid->parse("GRID_EMPTY_CELL");
							};
							// now how do I get the spans to work?
							$idx++;
						};
						$grid->vars(array(
							"GRID_CELL" => $cells,
						));
						$tres .= $grid->parse("GRID_ROW");
					};
					$grid->vars(array(
						"GRID_ROW" => $tres,
					));
					$res .= $this->put_content(array(
						"type" => "text",
						"value" => $grid->parse(),
					));
						
				};
			};
		};

		$submit_handler = $txt = "";
		if ($this->rte)
		{
			// make a list of of all RTE-s

			// would be nice if I could update the textareas right when the iframe loses focus ..
			// I'm almost sure I can do that.
			foreach($this->rtes as $rte)
			{
				$txt .= "document.changeform.elements['${rte}'].value=document.getElementById('${rte}_edit').contentWindow.document.body.innerHTML;\n";
				$data["cb_nobreaks[${rte}]"] = 1;
			};

			$submit_handler = $txt;
			// aha, but I have to put the linefeeds into the thing if it has been created with the plain
			// old editor.
		}
		
		// let's hope that nobody uses that vbox and hbox spagetti with grouptemplates -- ahz
		if($this->tplmode == "groups")
		{
			$vars = $vars + array(
				"submit_handler" => $submit_handler,
				"method" => !empty($method) ? $method : "POST",
				"reforb" => $this->mk_reforb($action,$data,$orb_class),
				"SUBMIT" => $sbt,
			);
			$this->sub_tpl->vars($vars);
		}
		else
		{
			if (empty($method))
			{
				$method = "POST";
			};
			if ("POST" != $method)
			{
				$data["no_reforb"] = 1;
			};
			$this->vars(array(
				"submit_handler" => $submit_handler,
				"method" => !empty($method) ? $method : "POST",
				"content" => $res,
				"reforb" => $this->mk_reforb($action,$data,$orb_class),
				"form_handler" => !empty($form_handler) ? $form_handler : "orb.aw",
				"SUBMIT" => $sbt,
				//"form_handler" => isset($form_handler) ? "orb.aw" : $form_handler,
			));

		}
	}

	function get_result($arr = array())
	{
		if ($this->layout_mode == "fixed_toolbar")
		{
			// this will apply a new style to the BODY node, it's required
			// to get the classbase layoyt with iframe working correctly
			$apd = get_instance("layout/active_page_data");
			$apd->add_serialized_css_style($this->parse("iframe_body_style"));
		};

		if ($arr["raw_output"])
		{
			$rv = $this->vars["content"];
		}
		elseif ($arr["form_only"])
		{
			return $this->parse();
		}
		else
		{
		
			if (empty($arr["content"]))
			{
				if($this->tplmode == "groups")
				{
					$rv = $this->sub_tpl->parse();
				}
				else
				{
					$rv = $this->parse();
				}
			}
			else
			{
				$rv = $arr["content"];
			};
			$tp = $this->tp;
			if (is_object($this->tabpanel))
			{
				$tp = $this->tabpanel;
			};
			if ($this->form_layout != "boxed")
			{
				// perhaps, just perhaps I should create a separate property type
				// out of the tabpanel
				//$rv = $this->tp->get_tabpanel(array());
				if ($this->tabs)
				{
					$rv = $tp->get_tabpanel(array(
						"content" => $rv,
					));
				};
			}
			else
			{
				$tabs = $tp->get_tabpanel(array(
					"content" => $rv,
					"panels_only" => true,
				));
				if (is_array($tabs))
				{
					foreach($tabs as $key => $item)
					{
						if (empty($key))
						{
							$loc = "top";
						}
						elseif ($key == "navi")
						{
							$loc = "left";
						}
						else
						{
							$loc = $key;
						};
						$this->additional_content[$loc] .= join("",$item);
					};
				}
				else
				{
					$rv = $tabs;
				};
				//$this->additional_content["top"] .= $tabs;
				//$tabs = $tp->get_tabpanel(array());
				//$this->additional_content["top"] .= $tabs;
			};
		};
		
		if ($this->form_layout == "boxed")
		{
			if($this->tplmode == "groups")
			{
				// now, when we have misleaded the htmlclient, we must safely
				// lead him back to the right template directory -- ahz
				$this->tpl_init("htmlclient");
			}
			$this->read_template("boxed.tpl");
			$this->vars(array(
				"top_content" => $this->additional_content["top"],
				"left_content" => $this->additional_content["left"],
				"right_content" => $this->additional_content["right"],
				"bottom_content" => $this->additional_content["bottom"],
				"content" => $rv,
			));
			return $this->parse();
		};

		return $rv;
	}

	function draw_element($args = array())
	{
		$tmp = new aw_array($args);
		$arr = $tmp->get();
		
		if ($args["type"] == "submit")
		{
			$this->submit_done = true;
		};

		// Check the types and call their counterparts
		// from the HTML class. If you want to support
		// a new property type, this is where you will have
		// to register it.
		switch($args["type"])
		{
			case "chooser":
				$options = new aw_array($arr["options"]);
				$retval = "";

				foreach($options->get() as $key => $val)
				{
					$caption = $val;
					if ($args["edit_links"])
					{
						$o = new object($key);
						$caption = html::href(array(
							"url" => $this->mk_my_orb("change",array("id" => $key),$o->class_id()),
							"caption" => $caption,
						));
					};
					if ($arr["multiple"])
					{
						$retval .= html::checkbox(array(
							"label" => $caption,
							"name" => $arr["name"] . "[" . $key . "]",
							"checked" => ($arr["value"][$key]),
							"value" => $key,
						));
					}
					else
					{
						$retval .= html::radiobutton(array(
							"caption" => $caption,
							"name" => $arr["name"],
							"checked" => isset($arr["value"]) && ($arr["value"] == $key),
							"value" => $key,
							"onclick" => $arr["onclick"],
						));
					};
					if ($arr["orient"] == "vertical")
					{
						$retval .= "<br />";

					};
						
				};
				break;

			case "select":
				$retval = html::select($arr);
				break;

			case "textbox":
				$retval = html::textbox($arr);
				$id = str_replace("[","_",$arr["name"]);
				$id = str_replace("]","_",$id);
				if (isset($arr["zee_shaa_helper"]))
				{
					$name = $arr["name"];
					$retval .= html::button(array(
						"value" => "ð",
						"onclick" => "el=document.getElementById('${id}');el.value=el.value+'ð';el.focus();",
					)) . html::button(array(
						"value" => "þ",
						"onclick" => "el=document.getElementById('${id}');el.value=el.value+'þ';el.focus();",
					));
				};
				break;

			case "textarea":
				if ($arr["richtext"])
				{
					$this->rte = true;
					$this->rtes[] = $arr["name"];
				}
				else
				{
					//$arr["style"] = "width: 100%;";
				};
				$retval = html::textarea($arr);
				break;

			case "password":
				$retval = html::password($arr);
				break;

			case "fileupload":
				$retval = html::fileupload($arr);
				break;

			case "checkbox":
				$retval = html::checkbox(array(
					"label" => isset($arr["label"]) ? $arr["label"] : "",
					"name" => $arr["name"],
					"value" => isset($arr["ch_value"]) ? $arr["ch_value"] : "",
					"caption" => $arr["caption"],
					"checked" => ($arr["value"]) && isset($arr["ch_value"]) && ($arr["value"] == $arr["ch_value"])
				));
				break;

				// will probably be deprecated, after all what good is a 
				// single 
			case "radiobutton":
				$retval = html::radiobutton(array(
					"name" => $arr["name"],
					"value" => $arr["rb_value"],
					"caption" => $arr["caption"],
					"checked" => ($arr["value"] == $arr["rb_value"])
				));
				break;

			case "submit":
				// but what if there is more than 1 of those?
				// attaching this might just break something somewhere
				$arr["onclick"] = "submit_changeform();";
				$arr["class"] = "sbtbutton";
				$retval = html::submit($arr);
				break;
			case "reset":
			case "button":
				$retval = html::button($arr);
				break;

			case "time_select":
				$retval = html::time_select($arr);
				break;

			case "date_select":
				$retval = html::date_select($arr);
				break;
			
			case "datetime_select":
				$retval = html::datetime_select($arr);
				break;
			
			case "img":
				$retval = html::img($arr);
				break;

			case "href":
				$retval = html::href($arr);
				break;

			default:
				$retval = html::text($arr);
				break;
		};
		return $retval;
	}

	function add_tab($arr)
	{
		if (is_object($this->tabpanel))
		{
			return $this->tabpanel->add_tab($arr);
		}
		else
		{
			return $this->tp->add_tab($arr);
		};
	}
};
?>
