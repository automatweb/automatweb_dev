<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/Attic/htmlpreview.aw,v 1.3 2005/04/21 08:48:48 kristo Exp $
// htmlpreview - generates HTML views for objects

class htmlpreview extends aw_template
{
	function htmlpreview($args = array())
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
		$this->res .= "\n<table border='0' width='100%' cellspacing='1' cellpadding='1' bgcolor='#FFFFFF'>\n";
	}

	function add_property($args = array())
	{
		// if value is array, then try to interpret
		// it as a list of elements.
		if (is_array($args["items"]))
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

		if ($args["no_caption"])
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
		if ($args["type"] == "status")
		{
			if (!$args["value"])
			{
				// default to deactive
				$args["value"] = 1;
			};
			if ($args["value"] == 2)
			{
				$val = "Aktiivne";
			}
			elseif ($args["value"] == 1)
			{
				$val = "Deaktiivne";
			};
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
		$this->res .= "</table>\n";
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
			case "xselect":
				$retval = html::select($arr);
				break;

			case "xtextbox":
				$retval = html::textbox($arr);
				break;

			case "xtextarea":
				$retval = html::textarea($arr);
				break;

			case "xpassword":
				$retval = html::password($arr);
				break;

			case "xhidden":
				$retval = html::hidden($arr);
				break;

			case "xfileupload":
				$retval = html::fileupload($arr);
				break;

			case "checkbox":
				$retval = html::text(array(
					'value' => ($arr['value'] == $arr['ch_value']) ? "Jah" : "Ei",
				));
				break;

			case "xradiobutton":
				$retval = html::radiobutton($arr);
				break;

			case "xsubmit":
				$retval = html::submit($arr);
				break;

			case "xbutton":
				$retval = html::button($arr);
				break;

			case "xtime_select":
				$retval = html::time_select($arr);
				break;

			case "xdate_select":
				$retval = html::date_select($arr);
				break;
			
			case "xpopup_objmgr":
				$retval = html::popup_objmgr($arr);
				break;

			case "ximg":
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
