<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/htmlclient.aw,v 1.23 2003/03/18 13:34:51 duke Exp $
// htmlclient - generates HTML for configuration forms

// The idea is that if we want to implement other interfaces
// for editing objects, then we can just add other clients
// (xmlrpc, rdf, tty, etc) which take care of converting the data
// from the cfgmanager to the required form. 

class htmlclient extends aw_template
{
	function htmlclient($args = array())
	{
		$this->init("");
		$this->res = "";
		$this->style1 = "chformleftcol";
		$this->style2 = "chformrightcol";
		$this->style_subheader = "chformsubheader";
		$this->style_content = "chformrightcol";

		$this->start_output();
	}


	////
	// !Starts the output 
	function start_output($args = array())
	{
		$this->res .= sprintf("<form action='reforb.%s' method='post' name='changeform' enctype='multipart/form-data'>\n",aw_ini_get("ext"));
		$this->res .= "<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='500000'>\n";
		$this->res .= "\n<table border='0' width='100%' cellspacing='1' cellpadding='1' bgcolor='#FFFFFF'>\n";
		$this->orb_vars = array();
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

		if ($args["type"] == "hidden")
		{
			$this->orb_vars[$args["name"]] = $args["value"];
		}
		else
		if (isset($args["no_caption"]))
		{
			$this->put_content($args);
		}
		else
		if ($args["type"])
		{
			$this->put_line($args);
		}
		elseif ($args["caption"])
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
		$val = "";
		if ($args["type"] == "status")
		{
			if (!$args["value"])
			{
				// default to deactive
				$args["value"] = 1;
			};
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => 2,
						"checked" => ($args["value"] == 2),
						"caption" => "Aktiivne",
			));
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => 1,
						"checked" => ($args["value"] == 1),
						"caption" => "Deaktiivne",
			));
			
			$args["value"] = $val;
		};
		
		if ($args["type"] == "s_status")
		{
			if (!$args["value"])
			{
				// default to deactive
				$args["value"] = 1;
			};
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => 3,
						"checked" => ($args["value"] == 3),
						"caption" => "Kõik",
			));
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => 2,
						"checked" => ($args["value"] == 2),
						"caption" => "Aktiivne",
			));
			$val .= html::radiobutton(array(
						"name" => $args["name"],
						"value" => 1,
						"checked" => ($args["value"] == 1),
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
					"value" => $args["value"],
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

	function put_line($args)
	{
		$this->res .= "<tr>\n";
		$this->res .= "\t<td class='" . $this->style1 . "' width='160' nowrap>";
		$this->res .= "<label for='$args[name]'> " . $args["caption"] . "</label>&nbsp;";
		$this->res .= "</td>\n";

		$this->res.= "\t<td class='" . $this->style2 . "'>";
		unset($args["caption"]);
		$this->res .= $this->draw_element($args);
		$this->res .= "</td>\n";
		$this->res .= "</tr>\n";
	}

	function put_header($args)
	{
		$this->res .= "<tr>\n";
		$this->res .= "\t<td colspan='2' class='" . $this->style_subheader . "' width='160'>";
		$this->res .= $args["caption"];
		$this->res .= "</td>\n";
		$this->res .= "</tr>\n";
	}
	
	function put_content($args)
	{
		$this->res .= "<tr>\n";
		$this->res .= "\t<td colspan='2' class='" . $this->style_content . "'>";
		$this->res .= $args["value"];
		$this->res .= "</td>\n";
		$this->res .= "</tr>\n";
	}

	////
	// !Finished the output
	function finish_output($args = array())
	{
		extract($args);
		if ($submit !== "no")
		{
			$this->res .= "<tr>\n\t<td class='chformleftcol' align='center'>&nbsp;</td>\n";
			$this->res .= "\t<td class='chformrightcol'>";
			$this->res .= "<input type='submit' value='Salvesta' class='small_button'>";
			$this->res .= "</td>\n";
			$this->res .= "</tr>\n";

		};
		$this->res .= "</table>\n";
		$orb_class = ($data["orb_class"]) ? $data["orb_class"] : "cfgmanager";
		unset($data["orb_class"]);
		$data = $data + $this->orb_vars;
		$this->res .= $this->mk_reforb($action,$data,$orb_class);
		$this->res .= "</form>\n";
	}

	function get_result()	
	{
		return $this->res;
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
					"value" => $arr["ch_value"],
					"checked" => ($arr["value"] == $arr["ch_value"])
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
