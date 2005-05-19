<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.76 2005/05/19 11:22:18 kristo Exp $
// html.aw - helper functions for generating HTML
class html extends aw_template
{
	////
	// !html select
	// name(string)
	// options(array)
	// selected(int)
	// onchange(string)
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function select($args = array())
	{
		extract($args);
		$disabled = ($disabled ? " disabled" : "");
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$sz = $mz = $onc = $cl = "";
		// things that make one go humm.. -- duke
		if (empty($selected) && isset($value))
		{
			$selected = $value;
		};

		if (isset($size))
		{
			$sz = "size=\"$size\" ";
		};

		if (!empty($class))
		{
			$cl = "class=\"$class\"";
		}

		if (isset($multiple))
		{
			$mz = "multiple ";
			$name .= "[]";
		};

		if (isset($selected) && is_array($selected))
		{
			$sel_array = $selected;
		}
		elseif (isset($selected) && $selected !== false)
		{
			$sel_array = array($selected);
		}
		else
		{
			$sel_array = array();
		};
		// hmhm. dunno, really. but it was in aw_template->mpicker -- duke
		$sel_array = @array_flip($sel_array);

		$optstr = "";

		foreach(safe_array($options) as $k => $v)
		{
			$selected = isset($sel_array[$k]) ? " selected " : "";
			$optstr .= "<option $selected value=\"$k\">$v</option>\n";
		}
		// implementing a thing called optgroup -- ahz
		foreach(safe_array($optgroup) as $key => $val)
		{
			$optstr .= "<optgroup label=\"".$optgnames[$key]."\">\n";
			foreach(safe_array($val) as $key2 => $val2)
			{
				$selected = isset($sel_array[$key2]) ? " selected " : "";
				$optstr .= "<option $selected value=\"$key2\">$val2</option>\n";
			}
			$optstr .= "</optgroup>\n";
		}
		if (!empty($onchange))
		{
			$onc = 'onchange="'.$onchange.'"';
		}
		return "<select name=\"$name\" $cl id=\"$name\" $sz $mz $onc $disabled $textsize>\n$optstr</select>\n";
	}

	////
	// !html text input
	// name(string)
	// value(string)
	// size(int)
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function textbox($args = array())
	{
		extract($args);
		$disabled = ($disabled ? " disabled" : "");
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$size = isset($size) ? $size : 40;
		$maxlength = isset($maxlength) ? $maxlength : "";
		$id = str_replace("[","_",$name);
		$id = str_replace("]","_",$id);
		$value = isset($value) ? $value : "";
		$value = str_replace('"' , '&quot;',$value);
		return "<input type=\"text\" id=\"$id\" name=\"$name\" size=\"$size\" value=\"$value\" maxlength=\"$maxlength\" $disabled $textsize />\n";
	}

	////
	// !html textarea
	// name(string)
	// value(string)
	// cols(int)
	// rows(int)
	// wrap(string)
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function textarea($args = array())
	{
		extract($args);
		$cols = isset($cols) ? $cols : 40;
		$rows = isset($rows) ? $rows : 5;
		$value = isset($value) ? $value : "";
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		// now, the browser detection is best done in javascript
		if (!empty($richtext))
		{
			$args["type"] = "richtext";
			$args["width"] = $cols;
			$args["height"] = $rows;
			$args["value"] = str_replace("\"" , "&quot;",$args["value"]); //"
			$rte = get_instance("vcl/rte");
			$retval = $rte->draw_editor($args);
		}
		else
		{
			$disabled = ($disabled ? " disabled" : "");
			$wrap = isset($wrap) ? $wrap : "soft";
			$style = isset($style) ? " style='$style' " : "";
			$retval = "<textarea $onchange id='$name' name='$name' cols='$cols' rows='$rows' wrap='$wrap' $style $disabled $textsize>$value</textarea>\n";
		};
		return $retval;
	}

	////
	//draws nice border around html content and put cute label on it, not all browsers support this
	//caption
	//content
	function fieldset($args = array())
	{
		extract($args);
		$caption = isset($caption) ? '<legend>'.$caption.'</legend>' : '';

		return '<fieldset>'.$caption.$content.'</fieldset>';
	}

	////
	// !html iframe
	// name(string)
	// width(string)
	// height(integer)
	// src(string)  - url
	function iframe($args = array())
	{
		extract($args);
		$width = isset($width) ? $width : '300';
		$height = isset($height) ? $height : '200';
		return "<iframe src='$src' name='$name' width='$width' height='$height'></iframe>\n";
	}

	////
	// !html password input
	// name(string)
	// value(string)
	// size(int)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function password($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$size = isset($size) ? $size : 40;
		return "<input type='password' id='$name' name='$name' size='$size' value='$value' maxlength='$maxlength' $textsize />\n";
	}

	////
	// !Simple text
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function text($args = array())
	{
		if ($textsize)
		{
			$element = '<span style="font-size: ' . $textsize . ';">' . $args["value"] . '</span>';
		}
		else
		{
			$element = $args["value"];
		}

		return $element;
	}

	////
	// !Hidden field
	// name(string)
	// value(string)
	function hidden($args = array())
	{
		extract($args);
		$value = isset($value) ? $value : '';
		return "<input type='hidden' id='$name' name='$name' value='$value' />\n";
	}

	////
	// !File upload
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function fileupload($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$disabled = ($disabled ? " disabled" : "");
		$rv = "";

		if (!empty($value))
		{
			$rv = $value . "<br />";
		}

		return $rv . "<input type='file' id='$name' name='$name' $disabled $textsize />\n";
	}

	////
	// !Checkbox
	// name(string)
	// value(string)
	// checked(bool)
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function checkbox($args = array())
	{
		extract($args);
		$checked = isset($checked) ? checked($checked) : '';
		$disabled = ($disabled ? "disabled" : "");
		$capt = '';
		if (empty($value))
		{
			$value = 1;
		};
		if (isset($label))
		{
			$capt .= $label;
		};
		if (isset($caption))
		{
			$capt .= " " . $caption;
		}

		if ($textsize and $capt)
		{
			$capt = '<span style="font-size: ' . $textsize . ';">' . $capt . '</span>';
		}

		$rv = "<input type='checkbox' id='$name' name='$name' value='$value' $checked $disabled /> $capt\n";
		return $rv;
	}

	////
	// !Radiobutton
	// name(string)
	// value(string)
	// checked(bool)
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function radiobutton($args = array())
	{
		extract($args);
		$checked = checked($checked);
		$disabled = ($disabled ? "disabled" : "");

		if ($textsize and $caption)
		{
			$caption = '<span style="font-size: ' . $textsize . ';">' . $caption . '</span>';
		}

		return "<input type='radio' name='$name' value='$value' $checked onClick='$onclick' $disabled />\n $caption";
	}

	////
	// !Submit button
	// value(string)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function submit($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");

		if (isset($onclick))
		{
			$onclick = 'onclick="'.$onclick.'"';
		}

		return "<input id='cbsubmit' type='submit' name='$name' value='$value' class='$class' $onclick $textsize />\n";
	}

	////
	// !Simple button
	// value(string)
	// onclick(string)
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function button($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$disabled = ($disabled ? " disabled" : "");
		return "<input type='".($type ? $type : "button")."' class='$class' value='$value' onClick=\"".$onclick."\" $disabled $textsize />\n";
	}

	////
	// !Time selector
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function time_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->set("minute_step", ($args["minute_step"] ? $args["minute_step"] : 1));
		$selector->configure(array("hour" => 1, "minute" => 1));
		list($d,$m,$y) = explode("-",date("d-m-Y"));
		$val = mktime($args["value"]["hour"], $args["value"]["minute"], 0, $m, $d, $y);

		if ($args["disabled"] or $args["textsize"])
		{
			$name = array ("name" => $args["name"]);

			if ($args["disabled"])
			{
				$name["disabled"] = true;
			}

			if ($args["textsize"])
			{
				$name["textsize"] = $args["textsize"];
			}
		}
		else
		{
			$name = $args["name"];
		}

		return $selector->gen_edit_form($name, $val);
	}

	////
	// !Datetime selector
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function datetime_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->set("minute_step", ($args["minute_step"] ? $args["minute_step"] : 1));
		$set = array();
		if (!empty($args["day"]) && $args["day"] == "text")
		{
			$set["day_textbox"] = 1;
		}
		else
		{
			$set["day"] = 1;
		};
		if (!empty($args["month"]) && $args["month"] == "text")
		{
			$set["month_textbox"] = 1;
		}
		else
		{
			$set["month"] = 1;
		};
		$set["year"] = 1;
		$set["hour"] = 1;
		$set["minute"] = 1;
		/*	unset($set["day"]);
			"year" =>1,
			"hour" => 1,
			"minute" => 1,
			*/

		$selector->configure($set);
		if (is_array($args['value']))
		{
			$val = mktime($args["value"]["hour"], $args["value"]["minute"], 0, $args["value"]["month"], $args["value"]["day"], $args["value"]["year"]);
		}
		else
		{
			$val = $args['value'];
		}

		if ($args["disabled"] or $args["textsize"])
		{
			$name = array ("name" => $args["name"]);

			if ($args["disabled"])
			{
				$name["disabled"] = true;
			}

			if ($args["textsize"])
			{
				$name["textsize"] = $args["textsize"];
			}
		}
		else
		{
			$name = $args["name"];
		}

		return $selector->gen_edit_form($name, $val, 2003, 2010, true);
	}

	////
	// !Date selector
	// disabled(bool)
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function date_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);
		$selector->configure(array("day" => 1, "month" => 1, "year" => 1));
		if(!empty($args["mon_for"]))
		{
			$selector->set("mon_for", $args["mon_for"]);
		}
		if (is_array($args["value"]))
		{
			$val = mktime(0, 0, 0, $args["value"]["month"], $args["value"]["day"], $args["value"]["year"]);
		}
		elseif($args["value"])
		{
			$val = $args["value"];
		}
		elseif($args["default"])
		{
			$val = $args["default"];
		}
		else
		{
			$val = time();
		}

		$year_from = isset($args["year_from"]) ? $args["year_from"] : date("Y") - 5;
		$year_to = isset($args["year_to"]) ? $args["year_to"] : date("Y") + 5;

		if ($args["disabled"] or $args["textsize"])
		{
			$name = array ("name" => $args["name"]);

			if ($args["disabled"])
			{
				$name["disabled"] = true;
			}

			if ($args["textsize"])
			{
				$name["textsize"] = $args["textsize"];
			}
		}
		else
		{
			$name = $args["name"];
		}

		return $selector->gen_edit_form($name, $val, $year_from, $year_to, true);
	}

	function img($args = array())
	{
		extract($args);
		$ret = "<img src='$url'";
		if (isset($width))
		{
			$ret.=" width='$width'";
		}
		if (isset($height))
		{
			$ret.=" height='$height'";
		}
		if (isset($border))
		{
			$ret.=" border='$border'";
		}
		if(isset($alt))
		{
			$ret.=" alt='$alt'";
		}
		if(isset($title))
		{
			$ret.=" title='$title'";
		}
		if(isset($class))
		{
			$ret.=" class='$class'";
		}
		return $ret.">";
	}

	/*
		$args
			url - url, kuhu peale klikki peaks browser suuna võtma
			target - kus freimis peax avanema
			onClick - onClick aktsioon
			title - Kui mouse hoverib peal, siis mis info juttu näidata
			caption - tekst mida näeb kasutaja
			textsize(string) -- examples: "10px", "0.7em", "smaller".
	*/
	function href($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$target = isset($target) ? " target='$target' " : "";
		$onClick = isset($onClick) ? " onClick='$onClick' " : "";
		$title = isset($title) ? " alt='$title' title='$title' " : "";
		return "<a href='$url' $target $title $onClick $textsize>$caption</a>";
	}

	////
	//
	//
	//
	function popup($arr = array())
	{
		extract($arr);
		$arr["onClick"] = 'javascript:window.open("'.$url.'", "'.$target.
		'", "toolbar='.($toolbar ? "yes" : "no").
		',directories='.($directories ? "yes" : "no").
		',status='.($status ? "yes" : "no").
		',location='.($location ? "yes" : "no").
		',resizable='.($resizable ? "yes" : "no").
		',scrollbars='.($scrollbars ? "yes" : "no").
		',menubar='.($menubar ? "yes" : "no").
		',height='.($height ? $height : 400).
		',width='.($width ? $width : 400).
		'");return false;';
		if($no_link)
		{
			return $arr["onClick"];
		}
		return html::href($arr);
	}
	////
	// !html form,
	// params:
	// method - form method
	// action - form action
	// name - form name
	// content - html to insert between form tags
	function form($args = array())
	{
		extract($args);
		return '<form action="'.$action.'" method="'.$method.'" name="'.$name.'">'.$content.'</form>';
	}

	////
	// !html <span class='$class'>$content</span>
	// textsize(string) -- examples: "10px", "0.7em", "smaller".
	function span($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$class = ($class ? 'class="' . $class . '"' : "");
		return "<span $class $textsize>$content</span>";
	}

	function get_change_url($oid, $params = array(), $caption = false)
	{
		if (!$this->can("view", $oid))
		{
			return "";
		}
		$obj = &obj($oid);
		$params["id"] = $obj->id();
		if ($this->can("edit", $oid))
		{
			$act = "change";
		}
		else
		{
			$act = "view";
		}
		$retval = $this->mk_my_orb($act, $params, $obj->class_id());
		if($caption)
		{
			$retval = html::href(array(
				"url" => $retval,
				"caption" => $caption
			));
		}
		return $retval;
	}

	function get_new_url($class_id, $parent, $params = array(), $caption = false)
	{
		$params["parent"] = $parent;
		$retval =  $this->mk_my_orb("new", $params, $class_id);
		if($caption)
		{
			$retval = html::href(array(
				"url" => $retval,
				"caption" => $caption
			));
		}
		return $retval;
	}
};
?>
