<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/htmlclient.aw,v 1.53 2004/03/17 17:39:25 duke Exp $
// htmlclient - generates HTML for configuration forms

// The idea is that if we want to implement other interfaces
// for editing objects, then we can just add other clients
// (xmlrpc, rdf, tty, etc) which take care of converting the data
// from the cfgmanager to the required form. 

class htmlclient extends aw_template
{
	function htmlclient($arr = array())
	{
		$this->init(array("tpldir" => "htmlclient"));
		$this->res = "";
		$this->layout_mode = "default";
		$this->form_target = "";
		if (!empty($arr["layout_mode"]))
		{
			$this->set_layout_mode($arr["layout_mode"]);
		};
		$this->group_style = "";
		$this->start_output();
	}

	function set_form_target($target = "_top")
	{
		$this->form_target = $target;
		if (!empty($this->form_target))
		{
			$this->vars(array(
				"form_target" => "target='" . $this->form_target . "' ",
			));
		};
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

	////
	// !Starts the output 
	function start_output($args = array())
	{
		$this->set_parse_method("eval");
		$tpl = "default.tpl";
		$this->read_template("default.tpl");
		//if (aw_global_get("uid") == "duke")
		//{
			$script = aw_global_get("SCRIPT_NAME");
			//$handler = empty($script) ? "index" : "orb";
			$this->vars(array(
				"handler" => empty($script) ? "index" : "orb",
			));


		//};
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
		if ($args["parent"])
		{
			$this->proplist[$args["parent"]]["html"] .= $this->put_subitem($args);
		}
		else
		// hidden elements end up in the orb_vars
		if ($type == "hidden")
		{
			$this->orb_vars[$args["name"]] = $args["value"];
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
		$this->proplist[$args["name"]] = $args;
	}

	////
	// !Shows an error indicator in the form
	function show_error()
	{
		$this->vars(array(
			"error_text" => "Viga sisendandmetes",
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
				$script .= "<script type='text/javascript'>\n";
				$script .= "var element = 0;\n";
				$script .= "function set_color(clr) {\n";
				$script .= "document.getElementById(element).value=clr;\n";
				$script .= "}\n";

				$script .= "function colorpicker(el) {\n";
				$script .= "element = el;\n";
				$script .= "aken=window.open('$cplink','colorpickerw','height=220,width=310');\n";
				$script .= "aken.focus();\n";
				$script .= "};\n";
				$script .= "</script>";
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

		// I wanda mis kammi ma selle tmp-iga tegin
		// different layout mode eh? well, it sucks!
		if (is_object($this->tmp))
		{
			$this->tmp->vars(array(
				"caption" => $caption,
				"element" => $this->draw_element($args),
			));
			$rv = $this->tmp->parse("LINE");
		}
		else
		{
			$this->vars(array(
				"caption" => $caption,
				"element" => $this->draw_element($args),
			));
			$rv = $this->parse("LINE");
		};
		return $rv;
	}

	function put_subitem($args)
	{
		$this->vars(array(
			"caption" => $args["caption"],
			"element" => $this->draw_element($args),
		));
		// SUBITEM - element first, caption right next to it
		// SUBITEM2 - caption first, element right next to it
		$tpl = $args["type"] == "checkbox" ? "SUBITEM" : "SUBITEM2";
		return $this->parse($tpl);
	}

	function put_header($args)
	{
		
		$this->vars(array(
			"caption" => $args["caption"],
		));
		return $this->parse("HEADER");
	}
	
	function put_header_subtitle($args)
	{
		$this->vars(array(
			"value" => !empty($args["value"]) ? $args["value"] : $args["caption"],
		));
		return $this->parse("SUB_TITLE");
	}
	
	function put_content($args)
	{
		$this->vars(array(
			//"value" => $args["value"],
			"value" => $this->draw_element($args),
		));
		return $this->parse("CONTENT");
	}
	
	////
	// !Finished the output
	function finish_output($arr)
	{
		extract($arr);
		$sbt = "";

		if (!is_array($data))
		{
			$data = array();
		};

		if ($this->submit_done)
		{
		
		}
		else
		if (empty($submit) || $submit !== "no")
		{
			// I need to figure out whether I have a relation manager
			$sbt = $this->parse("SUBMIT");
		};
		$orb_class = ($data["orb_class"]) ? $data["orb_class"] : "cfgmanager";
		unset($data["orb_class"]);
	
		$data = $data + $this->orb_vars;

		$res = "";

		if ($this->error)
		{
			$res .= $this->error;
		};

		if (sizeof($this->proplist) > 0)
		{
			foreach($this->proplist as $ki => $item)
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
				$res .= $item["html"];
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

		$this->vars(array(
			"submit_handler" => $submit_handler,
			"method" => !empty($method) ? $method : "POST",
			"content" => $res,
			"reforb" => $this->mk_reforb($action,$data,$orb_class),
			"SUBMIT" => $sbt,
		));

	}

	function get_result($arr)	
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
			return $rv;
		}
		else
		{
			$rv = $this->parse();
			return $rv;
		};
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
					if ($arr["multiple"])
					{
						$retval .= html::checkbox(array(
							"label" => $val,
							"name" => $arr["name"] . "[" . $key . "]",
							"checked" => ($arr["value"][$key]),
							"value" => $key,
						));
					}
					else
					{
						$retval .= html::radiobutton(array(
							"caption" => $val,
							"name" => $arr["name"],
							"checked" => ($arr["value"] == $key),
							"value" => $key,
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
};
?>
