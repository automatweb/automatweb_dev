<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/htmlclient.aw,v 1.29 2003/04/01 17:32:29 duke Exp $
// htmlclient - generates HTML for configuration forms

// The idea is that if we want to implement other interfaces
// for editing objects, then we can just add other clients
// (xmlrpc, rdf, tty, etc) which take care of converting the data
// from the cfgmanager to the required form. 

class htmlclient extends aw_template
{
	function htmlclient($args = array())
	{
		$this->init(array("tpldir" => "htmlclient"));
		$this->res = "";
		$this->start_output();
	}


	////
	// !Starts the output 
	function start_output($args = array())
	{
		$this->read_template("default.tpl");
		$this->orb_vars = array();
		$this->submit_done = false;
	}

	function add_property($args = array())
	{
		// if value is array, then try to interpret
		// it as a list of elements.
		if (isset($args["items"]) && is_array($args["items"]))
		{
			$res = "";
			foreach($args["items"] as $el)
			{
	 			$this->mod_property(&$el);
				$res .= $this->draw_element($el);
			};
			$args["value"] = $res;
			$args["type"] = "text";
		}
		else
		{
			$this->mod_property(&$args);
		};

		$type = isset($args["type"]) ? $args["type"] : "";

		if ($type == "submit")
		{
			$this->submit_done = true;
		};

		// hidden elements end up in the orb_vars
		if ($type == "hidden")
		{
			$this->orb_vars[$args["name"]] = $args["value"];
		}
		else
		if (isset($args["no_caption"]))
		{
			$this->put_content($args);
		}
		else
		if (isset($args["subtitle"]))
		{
			$this->put_header_subtitle($args);
		}
		else
		if ($type)
		{
			$this->put_line($args);
		}
		elseif (!empty($args["caption"]))
		{
			$this->put_header($args);
		}
		else
		{
			$this->put_content($args);
		};
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
			if (empty($args["value"]))
			{
				// default to deactive
				$args["value"] = STAT_NOTACTIVE;
			};
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => STAT_ACTIVE,
						"checked" => ($args["value"] == STAT_ACTIVE),
						"caption" => "Aktiivne",
			));
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => STAT_NOTACTIVE,
						"checked" => ($args["value"] == STAT_NOTACTIVE),
						"caption" => "Deaktiivne",
			));
			
			$args["value"] = $val;
		};
		
		if ($args["type"] == "s_status")
		{
			if (empty($args["value"]))
			{
				// default to deactive
				$args["value"] = STAT_NOTACTIVE;
			};
			// hm, do we need STAT_ANY? or should I just fix the search
			// do not use dumb value like 3 -- duke
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => 3,
						"checked" => ($args["value"] == 3),
						"caption" => "Kõik",
			));
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => STAT_ACTIVE,
						"checked" => ($args["value"] == STAT_ACTIVE),
						"caption" => "Aktiivne",
			));
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => STAT_NOTACTIVE,
						"checked" => ($args["value"] == STAT_NOTACTIVE),
						"caption" => "Deaktiivne",
			));
			
			$args["value"] = $val;
		};

		if ($args["type"] == "imgupload")
		{
			$args["type"] = "fileupload";
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
	}

	////
	// !hmm, I should use templates instead of generating all that HTML shit
	function put_line($args)
	{
		$caption = $args["caption"];
		unset($args["caption"]);

		$this->vars(array(
			"caption" => $caption,
			"element" => $this->draw_element($args),
		));
		$this->res .= $this->parse("LINE");
	}

	function put_header($args)
	{
		$this->vars(array(
			"caption" => $args["caption"],
		));
		$this->res .= $this->parse("HEADER");
	}
	
	function put_header_subtitle($args)
	{
		$this->vars(array(
			"value" => $args["value"],
		));
		$this->res .= $this->parse("SUB_TITLE");
	}
	
	function put_content($args)
	{
		$this->vars(array(
			"value" => $args["value"],
		));
		$this->res .= $this->parse("CONTENT");
	}

	////
	// !Finished the output
	function finish_output($args = array())
	{
		extract($args);
		$sbt = "";

		if ($this->submit_done)
		{
		
		}
		else
		if (empty($submit) || $submit !== "no")
		{
			$sbt = $this->parse("SUBMIT");
		};
		$orb_class = ($data["orb_class"]) ? $data["orb_class"] : "cfgmanager";
		unset($data["orb_class"]);
		$data = $data + $this->orb_vars;
		$this->vars(array(
			"content" => $this->res,
			"reforb" => $this->mk_reforb($action,$data,$orb_class),
			"SUBMIT" => $sbt,
		));
	}

	function get_result()	
	{
		return $this->parse();
	}

	function draw_element($args = array())
	{
		$tmp = new aw_array($args);
		$arr = $tmp->get();

		// Check the types and call their counterparts
		// from the HTML class. If you want to support
		// a new object type, this is where you will have
		// to register it.
		switch($args["type"])
		{
			case "select":
				$retval = html::select($arr);
				break;

			case "textbox":
				$retval = html::textbox($arr);
				break;

			case "textarea":
				$retval = html::textarea($arr);
				break;

			case "password":
				$retval = html::password($arr);
				break;

			case "hidden":
				$retval = html::hidden($arr);
				break;

			case "fileupload":
				$retval = html::fileupload($arr);
				break;

			case "checkbox":
				$retval = html::checkbox(array(
					"label" => isset($arr["label"]) ? $arr["label"] : "",
					"name" => $arr["name"],
					"value" => isset($arr["ch_value"]) ? $arr["ch_value"] : "",
					"checked" => isset($arr["value"]) && isset($arr["ch_value"]) && ($arr["value"] == $arr["ch_value"])
				));
				break;

			case "radiobutton":
				$retval = html::radiobutton(array(
					"name" => $arr["name"],
					"value" => $arr["rb_value"],
					"checked" => ($arr["value"] == $arr["rb_value"])
				));
				break;

			case "submit":
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
			
			case "popup_objmgr":
				$retval = html::popup_objmgr($arr);
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
