<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/htmlclient.aw,v 1.140 2006/02/28 10:13:05 kristo Exp $
// htmlclient - generates HTML for configuration forms

// The idea is that if we want to implement other interfaces
// for editing objects, then we can just add other clients
// (xmlrpc, rdf, tty, etc) which take care of converting the data
// from the cfgmanager to the required form. 

class htmlclient extends aw_template
{
	function htmlclient($arr = array())
	{
		if($arr["no_form"])
		{
			$this->no_form = true;
		}
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
		$this->form_layout = "";
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
	
	/** this can be used to set the different aspects of the behaviour of the client. 
	**/
	function configure($arr)
	{
		// help_url - set to the thing that should give you more information about the place you are in 
		// .. i need a few strings too, like "help", "close", "more" .. and also inline help about the group
		// but that should probably be somewhere in the finish_output
		$this->config = $arr;
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
		if (!$args["handler"])
		{
			$handler = empty($script) ? "index" : "orb";
		}
		else
		{
			$handler = $args["handler"];
		}
		if ($this->embedded)
		{
			$handler = "index";
		};
		$this->vars(array(
			"handler" => $handler,
		));
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

		// but actually, settings parets should take place in class_base itself
		if (isset($args["items"]) && is_array($args["items"]))
		{
			$res = "";
			foreach($args["items"] as $el)
			{
	 			$this->mod_property(&$el);
				$res .= $this->put_subitem($el);
			};
			$args["value"] = $res;
			$args["type"] = "text";
		}
		else
		{
			$this->mod_property(&$args);
		};
		$type = isset($args["type"]) ? $args["type"] : "";
		if ($type == "hidden")
		{
			$this->orb_vars[$args["name"]] = $args["value"];
		}
		if ($args["layout"])
		{
			$lf = $this->layoutinfo[$args["layout"]];
		}
		else
		{
			$lf = array();
		};

		if ($args["parent"] && !empty($this->layoutinfo[$args["parent"]]))
		{
			//$this->layoutinfo[$args["parent"]]["items"][] = $args["html"];
			$this->proplist[$args["name"]] = $args;

		}
		// now I have to check whether this property is placed in a grid
		// if so, place this thing int he grid
		elseif (!empty($args["layout"]) && 
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
			"error_text" => t("Viga sisendandmetes"),
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
				STAT_ACTIVE => t("Jah"),
				STAT_NOTACTIVE => t("Ei"),
			);
		};

		if (empty($args["value"]) && is_callable(array($args["vcl_inst"], "get_html")))
		{
			$args["value"] = $args["vcl_inst"]->get_html();
		}
		if($args["type"] == "reset" || $args["type"] == "button")
		{
			//$args["no_caption"] = 1;
			$args["value"] = $args["caption"];
			//$args["caption"] = "&nbsp;";
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
				3 => t("K&otilde;ik"),
				STAT_ACTIVE => t("Aktiivne"),
				STAT_NOTACTIVE => t("Deaktiivne"),
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

			$tx = "<a href=\"javascript:colorpicker('$args[name]')\">".t("Vali")."</a>";
	
			$val .= html::text(array("value" => $script . $tx));
			$args["value"] = $val;
		};

		if ($args["type"] == "submit")
		{
			if (empty($args["value"]))
			{
				$args["value"] = $args["caption"];
			};
			unset($args["caption"]);
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
			if ($this->config["show_help"])
			{
				$title = "";
				$help_url = "javascript:show_property_help(\"" . $args["name"] . "\")";
			}
			else
			{
				$help_url = "javascript:void(0);";
				$title = $args["comment"];
			};
			$caption = html::href(array(
				"url" => $help_url,
				"caption" => substr($caption,0,1),
				"title" => $title,
				"tabindex" => 1000
				)) . substr($caption,1);
		};
		$tpl_vars = array(
				"caption" => $caption,
				"element" => $this->draw_element($args),
				"webform_caption" => !empty($args["style"]["caption"]) ? "st".$args["style"]["caption"] : "",
				"webform_element" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
		);
		$add = "";
		if(!empty($args["capt_ord"]))
		{
			$add = strtoupper("_".$args["capt_ord"]);
		}
		// I wanda mis kammi ma selle tmp-iga tegin
		// different layout mode eh? well, it sucks!
		if (isset($this->tmp) && is_object($this->tmp))
		{
			$this->tmp->vars($tpl_vars);
			$rv = $this->tmp->parse("LINE".$add);
		}
		else
		{
			$this->vars($tpl_vars);
			$rv = $this->parse("LINE".$add);
		}
		return $rv;
	}

	////
	// !Creates a submit button
	function put_submit($arr)
	{
		$name = "SUBMIT";
		$tpl_vars = array(
			"sbt_caption" => $arr["value"] ? $arr["value"] : t("Salvesta"),
			"name" => $arr["name"] ? $arr["name"] : "",
			"action" => $arr["action"] ? $arr["action"] : "",
			"webform_element" => !empty($arr["style"]["prop"]) ? "st".$arr["style"]["prop"] : "",
			"webform_caption" => !empty($arr["style"]["prop"]) ? "st".$arr["style"]["prop"] : ""
		);
		if($arr["capt_ord"] == "right")
		{
			 $name .= strtoupper("_".$arr["capt_ord"]);
		}
		$this->vars($tpl_vars);
		$rv = $this->parse($name);
		return $rv;
	}

	function put_subitem($args)
	{
		$tpl_vars = array(
			"caption" => $args["caption"],
			"element" => $this->draw_element($args),
			"space" => $args["space"],
		);
		// SUBITEM - element first, caption right next to it
		// SUBITEM2 - caption first, element right next to it
		$tpl = $args["type"] == "checkbox" ? "SUBITEM" : "SUBITEM2";
		$this->vars($tpl_vars);
		$rv = $this->parse($tpl);
		return $rv;
	}

	function put_header($args)
	{
		$name = "HEADER";
		$tpl_vars = array(
			"caption" => $args["caption"],
			"webform_header" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
		);
		$this->vars($tpl_vars);
		$rv = $this->parse($name);
		return $rv;
	}
	
	function put_header_subtitle($args)
	{
		$name = "SUB_TITLE";
		$tpl_vars = array(
			"value" => !empty($args["value"]) ? $args["value"] : $args["caption"],
			"webform_subtitle" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
			"st_id" => $args["name"]
		);
		$this->vars($tpl_vars);
		$rv = $this->parse($name);
		return $rv;
	}
	
	function put_content($args)
	{
		$tpl_vars = array(
			//"value" => $args["value"],
			"cell_id" => $args['name']."_cell",
			"value" => $this->draw_element($args),
			"webform_content" => !empty($args["style"]["prop"]) ? "st".$args["style"]["prop"] : "",
		);
		$this->vars($tpl_vars);
		$rv = $this->parse("CONTENT");
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

		$orb_class = ($data["orb_class"]) ? $data["orb_class"] : "cfgmanager";
		unset($data["orb_class"]);
	
		$data = $data + $this->orb_vars;

		$res = "";
		if ($this->error)
		{
			$res .= $this->error;
		};

		$vars = array();
		$this->layout_by_parent = array();
		$this->lp_chain = array();

		foreach($this->layoutinfo as $key => $val)
		{
			$lparent = isset($val["parent"]) ? $val["parent"] : "_main";
			$val["name"] = $key;
			$this->layout_by_parent[$lparent][$key] = $val;
			$this->lp_chain[$key] = $lparent;
			// mul on iga layoudi kohta vaja teada tema kõige esimest layouti
		};

		$this->properties_by_parent = array();

		if (sizeof($this->proplist) > 0)
		{
			foreach($this->proplist as $ki => $item)
			{
				$pp = isset($item["parent"]) ? $item["parent"] : "_main";
				$this->properties_by_parent[$pp][$ki] = $ki;
				// track usage of submit button, if one does not exist in class properties
				// then we add one ourself. This is not a good way to do this, but hey ..
				// and it gets worse...
				if ($item["type"] == "submit")
				{
					$this->submit_done = true;
				};
			};
		};
		if ($this->submit_done || $this->view_mode == 1)
		{
		
		}
		else
		if (empty($submit) || $submit !== "no")
		{
			$var_name = "SUBMIT";
			$tpl_vars = array(
				"sbt_caption" => $sbt_caption != "" ? $sbt_caption : t("Salvesta"),
			);
			// I need to figure out whether I have a relation manager
			$this->vars($tpl_vars);
			$sbt = $this->parse($var_name);
			
		};

		$this->layoutinfo["_main"] = array(
			"type" => "vbox",
		);

		$xxx = $this->parse_layouts("_main");

		$property_help = "";
		if (sizeof($this->proplist) > 0)
		{
			foreach($this->proplist as $ki => $item)
			{
				// this was set in parse_layout
				if (isset($item["__ignore"]))
				{
					continue;
				};

				$this->vars(array(
					"property_name" => $item["name"],
					"property_caption" => isset($item["caption"]) ? $item["caption"] : "",
					"property_comment" => isset($item["comment"]) ? $item["comment"] : "",
					"property_help" => isset($item["help"]) ? $item["help"] : "",
				));

				$property_help .= $this->parse("PROPERTY_HELP");
				$item["html"] = $this->create_element($item);
				if (!empty($item["error"]))
				{
					$this->vars(array(
						"err_msg" => $item["error"],
					));
					$res .= $this->parse("PROP_ERR_MSG");
				};

				// this is what I was talking about before ...
				// move submit button _before_ the aliasmgr
				if (!empty($sbt) && $item["type"] == "aliasmgr")
				{
					$res .= $sbt;
					unset($sbt);
				};
				$res .= $item["html"];
			};
		};

		$submit_handler = $txt = "";
		if ($this->rte)
		{
			if($this->rte_type == 2)
			{
				$rte = get_instance("vcl/fck_editor");
				$res .= $rte->draw_editor(array(
					"lang" => aw_ini_get("user_interface.default_language"),
					"props" => $this->rtes,
				));
			}
			else
			{
				// make a list of of all RTE-s

				// would be nice if I could update the textareas right when the iframe loses focus ..
				// I'm almost sure I can do that.
				$baseurl = aw_ini_get("baseurl");

				foreach($this->rtes as $rte)
				{
					$txt .= "if (document.getElementById('${rte}_edit'))\n";
					$txt .= "{\n";
					$txt .= "tmpdat = document.getElementById('${rte}_edit').contentWindow.document.body.innerHTML;\n";
					$txt .= "document.changeform.elements['${rte}'].value=document.getElementById('${rte}_edit').contentWindow.document.body.innerHTML;\n";
					$txt .= "}\n";
					$data["cb_nobreaks[${rte}]"] = 1;
				};
				$submit_handler = $txt;
			}
		}

		$scripts = isset($scripts) ? $scripts : "";

		if (!empty($arr["focus"]))
		{
			$scripts .= "if (typeof(document.changeform['" . $arr["focus"] ."'].focus) != \"undefined\") { document.changeform['" . $arr["focus"] ."'].focus();\n}";
		}

		$fn = basename($_SERVER["SCRIPT_FILENAME"],".aw");
		$data["ret_to_orb"] = $fn == "orb" ? 1 : 0;
	
		// let's hope that nobody uses that vbox and hbox spagetti with grouptemplates -- ahz
		// groupboxes where implemented for rateme .. the code is not exactly elegant .. can I kill it?
		// please-please-please?
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
			"scripts" => $scripts,
			"method" => !empty($method) ? $method : "POST",
			"content" => $res,
			"reforb" => $this->mk_reforb($action,$data,$orb_class),
			"form_handler" => !empty($form_handler) ? $form_handler : "orb.aw",
			"SUBMIT" => isset($sbt) ? $sbt : "",
			"help" => $arr["help"],
			"PROPERTY_HELP" => $property_help,
			//"form_handler" => isset($form_handler) ? "orb.aw" : $form_handler,
		));
		
		if ($no_insert_reforb)
		{
			$ds = array();
			foreach($data as $k => $v)
			{
				$ds[] = "<input type='hidden' name='$k' value='$v'>";
			}
			$this->vars(array(
				"reforb" => join("\n", $ds)
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
		if(empty($this->no_form))
		{
			$this->vars(array(
				"SHOW_CHANGEFORM" => $this->parse("SHOW_CHANGEFORM"),
				"SHOW_CHANGEFORM2" => $this->parse("SHOW_CHANGEFORM2"),
			));
		}
		if ($arr["confirm_save_data"] == 1 && !($_GET["action"] == "check_leave_page" || $_GET["group"] == "relationmgr"))
		{
			$this->vars(array(
				"CHECK_LEAVE_PAGE" => $this->parse("CHECK_LEAVE_PAGE")
			));
		}

		if (!empty($arr["raw_output"]))
		{
			$rv = $this->vars["content"];
		}
		elseif (isset($arr["form_only"]))
		{
			return $this->parse();
		}
		else
		{
		
			if (empty($arr["content"]))
			{
				$rv = $this->parse();
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
			$tp->vars(array(
				"help" => $this->vars["help"],
				"help_url" => $this->config["help_url"],
				"translate_url" => $this->config["translate_url"],
				"translate_text" => $this->config["translate_text"],
				"more_help_text" => $this->config["more_help_text"],
				"close_help_text" => $this->config["close_help_text"],
				"open_help_text" => $this->config["open_help_text"],
			));
			if ($this->config["show_help"])
			{
				$tp->vars(array(
					"SHOW_HELP" => $tp->parse("SHOW_HELP"),
				));
			};
			if ($this->config["add_txt"])
			{
				$cust_url = $this->mk_my_orb('new',array(
						'parent' => $_REQUEST["id"],
						'alias_to' => $_REQUEST["id"],
						'reltype' => 22, // crm_company.CUSTOMER,
						'return_url' => get_ru()
					),
					'crm_company'
				);
				$cust_url_pri = $this->mk_my_orb('new',array(
						'parent' => $_REQUEST["id"],
						'alias_to' => $_REQUEST["id"],
						'reltype' => 22, // crm_company.CUSTOMER,
						'return_url' => get_ru()
					),
					CL_CRM_PERSON
				);
				$proj_url = html::get_new_url(
						CL_PROJECT, 
						$_REQUEST["id"], 
						array(
							"return_url" => get_ru(),
							"connect_impl" => $_REQUEST["id"],
						)
				);
				if ($_GET["group"] == "relorg")
				{
					$proj_url = 'submit_changeform("add_proj_to_co_as_impl");';
				}

				$u = get_instance(CL_USER);
				$cur_co = $u->get_current_company();

				$pl = get_instance(CL_PLANNER);
				$this->cal_id = $pl->get_calendar_for_user(array(
					"uid" => aw_global_get("uid"),
				));
				$task_url = $this->mk_my_orb('new',array(
					'alias_to_org' => $_REQUEST["id"] == $cur_co ? null : $_REQUEST["id"],
					'reltype_org' => 13,
					'class' => 'task',
					'add_to_cal' => $this->cal_id,
					'clid' => CL_TASK,
					'title' => t("Toimetus"),
					'parent' => $_REQUEST["id"],
					'return_url' => get_ru()
				));
				if ($_GET["group"] == "projs" || $_GET["group"] == "my_projects")
				{
					$task_url = "submit_changeform(\"add_task_to_proj\");";
				}
				else
				if ($_GET["group"] == "relorg")
				{
					$task_url = "submit_changeform(\"add_task_to_co\");";
				}
				$call_url = $this->mk_my_orb('new',array(
					'alias_to_org' => $_REQUEST["id"] == $cur_co ? null : $_REQUEST["id"],
					'reltype_org' => 12,
					'class' => 'crm_call',
					'add_to_cal' => $this->cal_id,
					'title' => t("K&otilde;ne"),
					'parent' => $_REQUEST["id"],
					'return_url' => get_ru()
				));
				$meeting_url = $this->mk_my_orb('new',array(
					'alias_to_org' => $_REQUEST["id"] == $cur_co ? null : $_REQUEST["id"],
					'reltype_org' => 11,
					'class' => 'crm_meeting',
					'add_to_cal' => $this->cal_id,
					'clid' => CL_CRM_MEETING,
					'title' => t("Kohtumine"),
					'parent' => $_REQUEST["id"],
					'return_url' => get_ru()
				));
				if ($_GET["group"] == "projs" || $_GET["group"] == "my_projects")
				{
					$meeting_url = "submit_changeform(\"add_meeting_to_proj\");";
				}
				else
				if ($_GET["group"] == "relorg")
				{
					$meeting_url = "submit_changeform(\"add_meeting_to_co\");";
				}
				$offer_url = $this->mk_my_orb('new',array(
					'alias_to_org' => $_REQUEST["id"],
					'reltype_org' => 9,
					'class' => 'crm_offer',
					'add_to_cal' => $this->cal_id,
					'clid' => CL_CRM_OFFER,
					'title' => t("Pakkumine"),
					'parent' => $_REQUEST["id"],
					'return_url' => get_ru()
				));
				if ($_GET["group"] == "projs" || $_GET["group"] == "my_projects")
				{
					$offer_url = "submit_changeform(\"add_offer_to_proj\");";
				}
				else
				if ($_GET["group"] == "relorg")
				{
					$offer_url = "submit_changeform(\"add_offer_to_co\");";
				}

				$job_url = html::get_new_url(CL_CRM_JOB_ENTRY, $_GET["id"], array("return_url" => get_ru()));

				$bill_url = aw_ini_get("baseurl").aw_url_change_var("group", "bills", aw_url_change_var("proj", NULL));
				$adds =  $this->picker("", array(
					"" => t("Lisa"),
					$job_url => t("T&ouml;&ouml;"),
					$cust_url => t("Organisatsioon"),
					$cust_url_pri => t("Eraisik"),
					$proj_url => t("Projekt"),
					$task_url => t("Toimetus"),
					$bill_url => t("Arve"),
					$call_url => t("K&otilde;ne"),
					$meeting_url => t("Kohtumine"),
					$offer_url => t("Pakkumine"),
					aw_ini_get("baseurl")."/orb.aw?class=users&action=logout" => t("Logi v&auml;lja")
				));

				$tp->vars(array(
					"adds" => $adds
				));
				//Klient (jaguneb eraklient/organisatsioon), Projekt, Ülesanne, Arve
				$tp->vars(array(
					"ADDITIONAL_TEXT" => $tp->parse("ADDITIONAL_TEXT"),
				));
			}
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
				if (isset($arr["richtext"]))
				{
					$arr["rte_type"] = $this->rte_type;
					$this->rte = true;
					$this->rtes[] = $arr["name"];
				}
				else
				{
					//$arr["style"] = "width: 100%;";
				};
				//$arr["divcols"] = 8 * $arr["cols"];
				//$arr["divrows"] = 12 * $arr["rows"];
				$this->vars($arr);
				//$retval = $this->parse("my_textarea");
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
					"caption" => isset($arr["caption"]) ? $arr["caption"] : "",
					"checked" => ($arr["value"] && ( (isset($arr["ch_value"]) && $arr["value"] == $arr["ch_value"]) || !isset($arr["ch_value"]) ) ),
					"onclick" => $arr["onclick"]
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
				if (isset($arr['action']))
				{
					$arr["onclick"] = "submit_changeform('" . $arr['action'] . "'); return false;";
				}
				else
				{
					$arr["onclick"] = "submit_changeform();";
				}
				$arr["class"] = "sbtbutton";
				$retval = html::submit($arr);
				break;
				
			case "reset":
				$arr["class"] = "sbtbutton";
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
			case "hidden":	
				// hidden elements end up in the orb_vars
				$this->orb_vars[$item["name"]] = $item["value"];
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
	
	function create_element($item)
	{
		$type = isset($item["type"]) ? $item["type"] : "";
		$item["html"] = "";

		if ($type == "iframe")
		{
			$src = $item["src"];
			$item["html"] = "<iframe id='contentarea' name='contentarea' src='${src}' style='width: 100%; height: 95%; border-top: 1px solid black;' frameborder='no' scrolling='yes'></iframe>";
		}
		else if ($this->layout_mode == "fixed_toolbar")
		{
			$item["html"] = $item["value"];
		}
		else if ($type == "hidden")
		{
			// hidden elements end up in the orb_vars
			$this->orb_vars[$item["name"]] = $item["value"];
		}
		else if ($item["type"] == "submit")
		{
			$item["html"] = $this->put_submit($item);
			$this->submit_done = true;
		}
		else if (isset($item["no_caption"]))
		{
			$item["html"] = $this->put_content($item);
		}
		else if (isset($item["subtitle"]))
		{
			$item["html"] = $this->put_header_subtitle($item);
		}
		else if ($type)
		{
			$item["html"] = $this->put_line($item);
		}
		// this I do not like
		elseif (!empty($item["caption"]))
		{
			$item["html"] = $this->put_header($item);
		}
		else
		{
			$item["html"] = $this->put_content($item);
		};
		return $item["html"];
	}
	
	function parse_layouts($layout_name)
	{
		$layout_items = array();
		$sub_layouts = array();
		foreach($this->layout_by_parent[$layout_name] as $lkey => $lval)
		{
			$html = $this->parse_layouts($lkey);

			$sub_layouts[$lkey] = $html;

			if (!empty($html))
			{
				$layout_items[] = $html;
			};
		}

		$html = "";
		$ldata = $this->layoutinfo[$layout_name];
		$location = false;
		if ($layout_name == "_main")
		{
			// put already parsed layouts in their correct places
			// first property in the layout sets the location
			foreach($this->proplist as $pkey => $pval)
			{
				if (!empty($pval["parent"]))
				{
					$gx = $this->lp_chain[$pval["parent"]];
					if ($sub_layouts[$gx])
					{
						$this->proplist[$pkey]["value"] = $sub_layouts[$gx];
						$this->proplist[$pkey]["type"] = "text";
						$this->proplist[$pkey]["caption"] = $this->layoutinfo[$gx]["caption"];
						// XXX: right now this is rewriting the first property in a box to contain
						// the rest of the parsed properties in that box, probably shouldn't 
						// do that though. 

						// set no_caption, if layout has no caption, otherwise the output
						// will contain a property with an empty caption, which will look
						// ugly in htmlclient at least
						if (empty($this->proplist[$pkey]["caption"]))
						{
							$this->proplist[$pkey]["no_caption"] = 1;
						};
						unset($this->proplist[$pkey]["__ignore"]);
						unset($sub_layouts[$gx]);
					}
					// this deals with lp_chain thingie .. I need to fix that too
					elseif ($sub_layouts[$pval["parent"]])
					{
						$gx = $pval["parent"];
						$this->proplist[$pkey]["value"] = $sub_layouts[$gx];
						$this->proplist[$pkey]["type"] = "text";
						// XXX: this will probably cause me problems later on ...
						unset($this->proplist[$pkey]["caption"]);
						if (!empty($this->layoutinfo[$gx]["caption"]))
						{
							$this->proplist[$pkey]["caption"] = $this->layoutinfo[$gx]["caption"];
							unset($this->proplist[$pkey]["no_caption"]);
						};
						unset($this->proplist[$pkey]["__ignore"]);
						unset($sub_layouts[$gx]);
					};
				};
			};
		}
		else
		{
			// this deals with  deepers levels
			foreach($this->properties_by_parent[$layout_name] as $pkey => $pval)
			{
				$layout_items[] = $this->put_griditem($this->proplist[$pkey]);
				$this->proplist[$pkey]["__ignore"] = 1;
			};
		};

		if ("hbox" == $ldata["type"])
		{
			$cell_widths = array();
			if (!empty($ldata["width"]))
			{
				$cell_widths = explode(":",$ldata["width"]);
			};

			$content = "";
			foreach($layout_items as $cell_nr => $layout_item)
			{
				$cell_width = isset($cell_widths[$cell_nr]) ? " width='" . $cell_widths[$cell_nr] . "'" : "";
				$this->vars(array(
					"item" => $layout_item,
					"item_width" => $cell_width,
				));
				$content .= $this->parse("GRID_HBOX_ITEM");
			};

			$this->vars(array(
				"GRID_HBOX_ITEM" => $content,
			));
			$html .= $this->parse("GRID_HBOX");

		}
		elseif ("vbox" == $ldata["type"])
		{
			$content = "";
			foreach($layout_items as $cell_nr => $layout_item)
			{
				$this->vars(array(
					"item" => $layout_item,
				));
				$content .= $this->parse("GRID_VBOX_ITEM");
			};

			$this->vars(array(
				"GRID_VBOX_ITEM" => $content,
			));
			
			$html .= $this->parse("GRID_VBOX");
		};

		return $html;
	}

	function put_griditem($arr)
	{
		$captionside = "left";
		// support TOP and LEFT for now only
		$sufix = "";
		if ($arr["captionside"] == "top")
		{
			$captionside = $arr["captionside"];
		};

		// subtemplate names are uper case:
		$captionside = strtoupper($captionside);

		// reset all captions
		$this->vars(array(
			"caption" => $arr["caption"],
			"CAPTION_LEFT" => "",
			"CAPTION_TOP" => "",
			"element" => $this->draw_element($arr),
		));
		// name refers to a VAR inside the template
		$caption_template = "CAPTION_${captionside}";
		$this->vars(array(
			$caption_template => $this->parse($caption_template),
		));
		$tpl = "GRIDITEM";
		if (!empty($arr["no_caption"]))
		{
			$tpl = "GRIDITEM_NO_CAPTION";
		};
		return $this->parse($tpl);
	}
};
?>
