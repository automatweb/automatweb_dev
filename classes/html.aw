<?php
// $Header: /home/cvs/automatweb_dev/classes/html.aw,v 2.110 2006/05/02 09:57:49 kristo Exp $
// html.aw - helper functions for generating HTML
class html extends aw_template
{
	/**
	@attrib api=1 params=name

	@param name optional type=string
		selection name
	@param options optional type=array
		selection options array(value => text)
	@param selected optional type=int
		already selected options
	@param onchange optional type=string
		action starts if selection changes
	@param disabled optional type=bool
		if true, selection is disabled
	@param textsize optional type=string
		font size . examples: "10px", "0.7em", "smaller"
	@returns string / html select

	@comment creates html select
	**/
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

		if (!empty($multiple))
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

		return "<select name=\"$name\" $cl id=\"$name\" $sz $mz $onc $disabled $textsize>\n$optstr</select>$post_append_text\n";
	}

	/**
	@attrib api=1 params=name

	@param name optional type=string
		textbox name
	@param value optional type=string
		textbox value
	@param contenr optional type=string
		text visible to user when $option_is_tuple is set to TRUE.
	@param size optional type=int
		textbox size
	@param disabled optional type=bool
		if true, textbox is disabled
	@param textsize optional type=string
		font size . examples: "10px", "0.7em", "smaller"

	@param onkeypress optional type=string
		If set, then onkeypress=$onkeypress. Not allowed if autocomplete used.

	@param autocomplete_source optional type=string
		Relative (to web root -- it seems that certain browsers don't allow javascript http connections to absolute paths) http URL that refers to source of autocomplete options. Response expected in JSON format (http://www.json.org/)(classes/protocols/data/aw_json). Response is an array:
		array(
			"error" => boolean,// recommended
			"errorstring" => error string description,// optional
			"options" => array(value1 => text1, ...),// required
			"limited" => boolean,// whether option count limiting applied or not. applicable only for real time autocomplete.
		)

	@param autocomplete_source_method optional type=string
		Alternative to $autocomplete_source parameter. AW ORB method to be called to get options.

	@param autocomplete_source_class optional type=string
		AW class to look for autocomplete_source_method. default is the class that requests the textbox.

	@param autocomplete_params optional type=array
		Array of form element names whose values will be arguments to autocomplete_source. If self form element name included, real time autocomplete options retrieving enabled (i.e. for each key typed, if not in cache).

	@param autocomplete_limit optional type=int
		Number of options autocomplete can show (-1: no limit). Default 20.

	@param autocomplete_match_anywhere optional type=bool
		Should the auto complete match input with options from first char or anywhere in an option? Default FALSE.

	@param autocomplete_delimiters optional type=array
		Delimiter strings for multiple part autocomplete (autocomplete options given again after typing text after a delimiter string). Default empty.

	@param options optional type=array
		Initial autocomplete options. If $option_is_tuple then associative. Default empty.

	@param content optional type=string
		Text visible to user when $option_is_tuple is set to TRUE.

	@param option_is_tuple optional type=bool
		Indicates whether autocomplete options are values (FALSE) or names associated with values (TRUE) iow autocomplete options are key/value pairs. If set to TRUE, $content should be set to what the user will see in the textbox. If set to TRUE then the value returned by POST request under property name is $key if an autocomplete option was selected, $value if new value was entered. Note that user may type an option without selecting it from autocomplete list in which case posted value will not be $key.


	@returns string / html textbox

	@comment creates html textbox
	**/

	function textbox($args = array())
	{
		extract($args);
		$disabled = ($disabled ? " disabled" : "");
		$textsize = ($textsize ? ' style="font-size: ' . $textsize . ';"' : "");
		$size = isset($size) ? $size : 40;
		$maxlength = isset($maxlength) ? $maxlength : "";
		$id = str_replace("[","_",$name);
		$id = str_replace("]","_",$id);
		$value = isset($value) ? $value : "";
		$value = str_replace('"' , '&quot;',$value);
		settype ($option_is_tuple, "boolean");
		$onkeypress = isset($onkeypress) ? ' onkeypress="'.$onkeypress.'"' : "";
		$autocomplete = "";
		$js_name = str_replace(array("[", "]", "-"), "_", $name);

		### compose autocompletes source url
		if ($autocomplete_source or is_array($options) or $autocomplete_source_method)
		{
			if (!defined("AW_AUTOCOMPLETE_INITIALIZED"))
			{
				$baseurl = aw_ini_get("baseurl") . "/automatweb/js/";
				$autocomplete = '<script type="text/javascript" src="' . $baseurl . 'autocomplete_lib.js"></script><script type="text/javascript" src="' . $baseurl . 'autocomplete.js"></script>';
				define("AW_AUTOCOMPLETE_INITIALIZED", 1);
			}

			$autocomplete .= '<script type="text/javascript">';

			if ($option_is_tuple)
			{
				$autocomplete .= "var awAc_{$js_name} = new awActb(document.getElementsByName('{$name}_awAutoCompleteTextbox')[0], document.getElementsByName('{$name}')[0]);\n";
			}
			else
			{
				$autocomplete .= "var awAc_{$js_name} = new awActb(document.getElementsByName('{$name}')[0]);\n";
			}

			if (is_array($options))
			{
				$autocomplete .= "var awAc_{$js_name}Opts = new Array();\n";

				foreach ($options as $key => $value)
				{
					$autocomplete .= "awAc_{$js_name}Opts['{$key}'] = '" . str_replace("'", "\\'", $value) . "';\n";
				}

				$autocomplete .= "awAc_{$js_name}.actb_setOptions(awAc_{$js_name}Opts);\n";
			}
			else
			{
				if ($autocomplete_source_method)
				{
					$autocomplete_source_class = $autocomplete_source_class ? $autocomplete_source_class : $_GET["class"];
					$params = array(
						"id" => $_GET["id"],
					);
					$autocomplete_source = $this->mk_my_orb($autocomplete_source_method, $params, $autocomplete_source_class, false, true);
					$autocomplete_source = parse_url ($autocomplete_source);
					$autocomplete_source = $autocomplete_source["path"] . "?" . $autocomplete_source["query"];
				}

				$autocomplete .= "awAc_{$js_name}.actb_optionURL = '{$autocomplete_source}';\n";
				$autocomplete .= "awAc_{$js_name}.actb_setParams(" . (count ($autocomplete_params) ? "new Array ('" . implode ("','", $autocomplete_params) . "')" : "new Array ()") . ");\n";
			}

			if ($textsize)
			{
				$autocomplete .= "awAc_{$js_name}.actb_fontSize = '{$textsize}';\n";
			}

			if ($autocomplete_limit)
			{
				$autocomplete .= "awAc_{$js_name}.actb_lim = {$autocomplete_limit};\n";
			}

			if ($autocomplete_match_anywhere)
			{
				$autocomplete .= "awAc_{$js_name}.actb_firstText = false;\n";
			}

			if (is_array($autocomplete_delimiters) and count($autocomplete_delimiters))
			{
				$autocomplete .= "awAc_{$js_name}.actb_delimiter = new Array ('" . implode ("','", $autocomplete_delimiters) . "');\n";
			}

			$autocomplete .= '</script>';
		}

		$value_elem = "";

		$ac_off = "";
		if ($autocomplete)
		{
			$onkeypress = "";
			$ac_off = "autocomplete=\"off\"";
			if ($option_is_tuple)
			{
				$value_elem = "<input type=\"hidden\" id=\"$id\" name=\"$name\" value=\"$value\">\n";
				$id .= "AWAutoCompleteTextbox";
				$name .= "_awAutoCompleteTextbox";
				$value = $content;
			}
		}

		return "<input type=\"text\" id=\"$id\" $ac_off name=\"$name\" size=\"$size\" value=\"$value\" maxlength=\"$maxlength\"{$onkeypress}{$disabled}{$textsize} />$post_append_text\n{$value_elem}{$autocomplete}";
	}

	/**
	@attrib api=1 params=name

	@param name optional type=string
		textarea name
	@param value optional type=string
		textarea value
	@param cols optional type=int
		number of columns
	@param rows optional type=int
		number of rows
	@param wrap optional type=string
		if set, wrap='$wrap'
	@param disabled optional type=bool
		if true, textarea is disabled
	@param textsize optional type=string
		font size . examples: "10px", "0.7em", "smaller"

	@returns string / html textarea

	@comment creates html textarea
	**/
	function textarea($args = array())
	{
		extract($args);
		$cols = isset($cols) ? $cols : 40;
		$rows = isset($rows) ? $rows : 5;
		$value = isset($value) ? $value : "";
		if (strpos($value, "<") !== false)
		{
			$value = htmlspecialchars($value);
		}
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		// now, the browser detection is best done in javascript
		if (!empty($richtext))
		{
			if($rte_type == 2)
			{
				$retval .= "<textarea $onchange id='$name' name='$name' cols='$cols' rows='$rows' wrap='$wrap' $style $disabled $textsize>$value</textarea>\n";
			}
			else
			{
				$args["type"] = "richtext";
				$args["width"] = $cols;
				$args["height"] = $rows;
				$args["value"] = str_replace("\"" , "&quot;",$args["value"]); //"
				$rte = get_instance("vcl/rte");
				$retval = $rte->draw_editor($args);
			}
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

	/**
	@attrib api=1 params=name

	@param caption optional type=string
		legend
	@param content optional type=string
		html content
	@returns string

	@comment draws nice border around html content and put cute label on it, not all browsers support this
	**/
	function fieldset($args = array())
	{
		extract($args);
		$caption = isset($caption) ? '<legend>'.$caption.'</legend>' : '';
		return '<fieldset>'.$caption.$content.'</fieldset>';
	}

	/**
	@attrib api=1 params=name

	@param name optional type=string
		iframe name
	@param width optional type=integer default=300
		iframe width
	@param height optional type=integer default=200
		iframe height
	@param src optional type=string
		url

	@returns string/html iframe

	@comment draws html iframe
	**/
	function iframe($args = array())
	{
		extract($args);
		$width = isset($width) ? $width : '300';
		$height = isset($height) ? $height : '200';
		return "<iframe src='$src' name='$name' width='$width' height='$height'></iframe>\n";
	}

	/**
	@attrib api=1 params=name

	@param name optional type=string
		password input name
	@param value optional type=string
		password input value
	@param size optional type=int
		number of html password input
	@param textsize optional type=string
		font size . examples: "10px", "0.7em", "smaller"

	@returns string /html password input

	@comment creates html password input
	**/
	function password($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$size = isset($size) ? $size : 40;
		return "<input type='password' id='$name' name='$name' size='$size' value='$value' maxlength='$maxlength' $textsize />\n";
	}

	/**Simple text
	@attrib api=1 params=name

	@param value required type=string
		text
	@param textsize optional type=string
		text size - examples: "10px", "0.7em", "smaller".
	@returns string/html text

	@comment draws simple html text with given textsize
	**/
	function text($args = array())
	{
		if ($args["textsize"])
		{
			$element = '<span style="font-size: ' . $args["textsize"] . ';">' . $args["value"] . '</span>';
		}
		else
		{
			$element = $args["value"];
		}
		return $element;
	}

	/**Hidden field
	@attrib api=1 params=name

	@param name optional type=string
		hidden field name
	@param value optional type=string
		hidden field value
	@returns string/html Hidden field
	**/
	function hidden($args = array())
	{
		extract($args);
		$value = isset($value) ? $value : '';
		return "<input type='hidden' id='$name' name='$name' value='$value' />\n";
	}

	/**File upload
	@attrib api=1 params=name

	@param name optional type=string
		fileupload name
	@param textsize optional type=string
		examples: "10px", "0.7em", "smaller".
	@param disabled optional type=bool
		if set, fileupload is disabled
	@param value optional type=string
		if set, then all that stuff appears last row before the file upload
	@returns strng/html fileupload
	**/
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

	/**Checkbox
	@attrib api=1 params=name

	@param name optional type=string
		Checkbox name
	@param value optional type=string
		Checkbox value
	@param checked optional type=bool
		If set, the checkbox is checked
	@param disabled optional type=bool
		If set, the checkbox is disabled
	@param textsize optional type=string
		Examples: "10px", "0.7em", "smaller"
	@param label optional type=string
		Checkbox label
	@param caption optional type=string
		Checkbox caption
	@param onclick optional type=string
		stuff what will happen if you click on checkbox - javascript
	@returns string/html checkbox
	**/
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
		if (isset($onclick))
		{
			$onc = "onClick='$onclick'";
		}
		$rv = "<input type='checkbox' id='$name' name='$name' value='$value' $onc $checked $disabled /> $capt\n";
		return $rv;
	}

	/**Radiobutton
	@attrib api=1 params=name

	@param name optional type=string
		button's name
	@param value optional type=string
		button's value
	@param checked optional type=bool
		If set, the radiobutton is checked
	@param disabled optional type=bool
		If set, the radiobutton is disabled
	@param textsize optional type=string
		Examples: "10px", "0.7em", "smaller"
	@param caption optional type=string
		button's caption
	@param onclick optional type=string
		stuff what will happen if you click the radiobutton - javascript
	@returns string/html radiobutton
	**/
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

	/**Submit button
	@attrib api=1 params=name

	@param name optional type=string
		button name
	@param value optional type=string
		button value
	@param class optional type=string
		style class name
	@param textsize optional type=string
		Examples: "10px", "0.7em", "smaller"
	@param onclick optional type=string
		stuff what will happen if you click the button - javascript
	@returns string/html submit button
	**/
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

	/**Simple button
	@attrib api=1 params=name

	@param type optional type=string
		button type
	@param value optional type=string
		button value
	@param class optional type=string
		style class name
	@param textsize optional type=string
		Examples: "10px", "0.7em", "smaller"
	@param disabled optional type=bool
		If set, the button is disabled
	@param onclick optional type=string
		stuff what will happen if you click the button - javascript
	@returns string/html submit button
	**/
	function button($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$disabled = ($disabled ? " disabled" : "");
		return "<input type='".($type ? $type : "button")."' class='$class' value='$value' onClick=\"".$onclick."\" $disabled $textsize />\n";
	}

	/**Time selector
	@attrib api=1 params=name

	@param name optional type=string
		Time selector name
	@param minute_step optional type=int default=1
		Time selector minute step
	@param textsize optional type=string
		Examples: "10px", "0.7em", "smaller"
	@param disabled optional type=bool
		If set, the time selector is disabled
	@param value optional type=array
		array("hour" - the number of the hour, "minute" - the number of the minute)
	@returns string/html time selector

	@comments
		draws several selectboxes , can be used for selecting time
	**/
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

	/**Date & time selector
	@attrib api=1 params=name

	@param name optional type=string
		Datetime selector name
	@param minute_step optional type=int default=1
		Datetime selector minute step
	@param day optional type=string
		if day = "text" then day is shown as textbox, not selectbox
	@param month optional type=string
		if month = "text" then month is shown as textbox, not selectbox
	@param value optional type=array/int/string
		array("hour" - the number of the hour, "minute" - the number of the minute, "month" - the number of the month, "year"  - The number of the year, may be a two or four digit value, with values between 0-69 mapping to 2000-2069 and 70-100 to 1970-2000. On systems where time_t is a 32bit signed integer, as most common today, the valid range for year is somewhere between 1901 and 2038)
		if not an array , value should be Unix timestamp or if value = "+24h" or "+48h", 24 or 48 hours will be add to timestamp
	@param disabled optional type=bool
		If set, the datetime selector is disabled
	@param textsize optional type=string
		Examples: "10px", "0.7em", "smaller". If set, the datetime selector is disabled

	@returns string/html datetime selector

	@comments
		draws several selectboxes (with textboxes) , can be used for selecting time and date
	**/
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

	/**Date selector
	@attrib api=1 params=name

	@param name optional type=string
		Date selector name
	@param format optional type=string
		if day = "text" then day is shown as textbox, not selectbox
	@param mon_for optional type=string
		if set, 0 appears before every month's signifier witch has value < 10
	@param value optional type=array/int/string
		array("day" - the number of the day, "month" - the number of the month, "year"  - The number of the year, may be a two or four digit value, with values between 0-69 mapping to 2000-2069 and 70-100 to 1970-2000. On systems where time_t is a 32bit signed integer, as most common today, the valid range for year is somewhere between 1901 and 2038)
		if not an array , value should be Unix timestamp or if value = "+24h" or "+48h", 24 or 48 hours will be add to timestamp
	@param default optional type=int
		if value is not set, timestamp = default // it is meaningless
	@param year_from optional type=int default=current year - 5
		the number where year counting starts
	@param year_to optional type=int default=current year + 5
		the number where year counting ends
	@param post_append_text optional type=string
		any text or html code you want to see after date selector // meaningless
	@param disabled optional type=bool
		If set, the date selector is disabled
	@param textsize optional type=string
		Examples: "10px", "0.7em", "smaller". If set, the datetime selector is disabled

	@returns string/html date selector

	@comments
		draws several selectboxes , can be used for selecting time and date
	**/
	function date_select($args = array())
	{
		load_vcl("date_edit");
		$selector = new date_edit($args["name"]);

		if (!empty($args["format"]) && is_array($args["format"]) && count($args["format"]))
		{
			$a = array();
			foreach($args["format"] as $fldn)
			{
				$a[$fldn] = 1;
			}
			$selector->configure($a);
		}
		else
		{
			$selector->configure(array("day" => 1, "month" => 1, "year" => 1));
		}
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

		$res = $selector->gen_edit_form($name, $val, $year_from, $year_to, true);
		$res .= $args["post_append_text"];
		return $res;
	}

	/**Image
	@attrib api=1 params=name

	@param url optional type=string
		image url
	@param width optional type=int
		image width
	@param height optional type=int
		image height
	@param border optional type=int
		border size
	@param alt optional type=string
		text you can see when you scroll over the image
	@param title optional type=string
		image title
	@param class optional type=string
		style class name
	@param id optional type=string
		image id

	@returns string/html image

	@comments
		draws html image tag
	**/
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
		if(isset($id))
		{
			$ret.=" id='$id'";
		}
		return $ret.">";
	}

	/**Link
	@attrib api=1 params=name

	@param onclick optional type=string
		stuff that will happen , if you press the link - javascript
	@param textsize optional type=string
		examples: "10px", "0.7em", "smaller"
	@param target optional type=int
		frame name where stuff should open
	@param title optional type=int
		you can see this text when scrolling over the link
	@param class optional type=string
		style class name
	@param tabindex optional type=string
		tab index
	@param id optional type=string
		element id (for css mainly)
	@param caption optional type=string
		the text user can see

	@returns string/html href

	@comments
		draws html href tag
	**/
	function href($args = array())
	{
		extract($args);
		if (!isset($onClick) && isset($onclick))
		{
			$onClick = $onclick;
		}
		$textsize = isset($textsize) ? ' style="font-size: ' . $textsize . ';"' : "";
		$target = isset($target) ? " target='$target'" : "";
		$onClick = isset($onClick) ? " onClick='$onClick'" : "";
		$title = isset($title) ? " alt='$title' title='$title'" : "";
		$class = isset($class) ? " class='$class'" : "";
		$ti = isset($tabindex) ? " tabindex='$tabindex'" : "";
		$id = isset($id) ? " id='$id'" : "";
		return "<a href='{$url}'" . $target . $title . $onClick . $ti . $textsize . $class . $id . ">{$caption}</a>";
	}

	/**Popup
	@attrib api=1 params=name

	@param quote optional type=string default = '"'
		Quotation mark
	@param url optional type=string
		A string containing the URL of the document to open in the new window. If no URL is specified, an empty window will be created
	@param target optional type=string
		A string containing the name of the new window. This can be used as the 'target' attribute of a <FORM> or <A> tag to point to the new window.
	@param toolbar optional type=string default=no
		When set to yes the new window will have the standard browser tool bar (Back, Forward, etc.).
	@param directories optional type=string default = no
		When set to yes, the new browser window has the standard directory buttons.
	@param status optional type=string  type=string default = no
		When set to yes, the new window will have the standard browser status bar at the bottom.
	@param location optional type=string  type=string default = no
		When set to yes, this creates the standard Location entry feild in the new browser window.
	@param resizable optional type=string  type=string default = no
		When set to yes this allows the resizing of the new window by the user.
	@param scrollbars  optional type=string default = no
		When set to yes the new window is created with the standard horizontal and vertical scrollbars, where needed
	@param menubar optional  type=string default = no
		When set to yes, this creates a new browser window with the standard menu bar (File, Edit, View, etc.).
	@param height optional type=int default=400
		This sets the height of the new window in pixels.
	@param width optional type=int default=400
		This sets the width of the new window in pixels.
	@param no_link optional type=bool
		If set, returns javascritp text instead of the href html tag
	@returns string/html popup

	@comments
		draws html pupup link href tag or javascript text
	**/
	function popup($arr = array())
	{
		extract($arr);
		$quote = isset($arr["quote"]) ? $arr["quote"] : "\"";
		$arr["onClick"] = 'javascript:window.open('.$quote.''.$url.''.$quote.', '.$quote.''.$target.
		''.$quote.', '.$quote.'toolbar='.($toolbar ? "yes" : "no").
		',directories='.($directories ? "yes" : "no").
		',status='.($status ? "yes" : "no").
		',location='.($location ? "yes" : "no").
		',resizable='.($resizable ? "yes" : "no").
		',scrollbars='.($scrollbars ? "yes" : "no").
		',menubar='.($menubar ? "yes" : "no").
		',height='.($height ? $height : 400).
		',width='.($width ? $width : 400).
		''.$quote.');'.(!$arr["no_return"] ? 'return false;' : '');
		if($no_link)
		{
			return $arr["onClick"];
		}
		return html::href($arr);
	}

	/**HTML form
	@attrib api=1 params=name

	@param action optional type=string default = '"'
		form action
	@param method optional type=string
		form method
	@param name optional type=int default=400
		form name
	@param content optional type=bool
		html to insert between form tags
	@returns string/html form
	**/
	function form($args = array())
	{
		extract($args);
		return '<form action="'.$action.'" method="'.$method.'" name="'.$name.'">'.$content.'</form>';
	}

	/**Link
	@attrib api=1 params=name

	@param class optional type=string
		style class name
	@param textsize optional type=string
		examples: "10px", "0.7em", "smaller"
	@param content optional type=int
		fhtml to insert between span tags
	@returns string/html

	@comments
		draws <span class='$class'>$content</span>
	**/
	function span($args = array())
	{
		extract($args);
		$textsize = ($textsize ? 'style="font-size: ' . $textsize . ';"' : "");
		$class = ($class ? 'class="' . $class . '"' : "");
		return "<span $class $textsize>$content</span>";
	}

	/**
	@attrib api=1 params=pos

	@param o required type=object
		object to be changed
	@param caption optional type=string
		the text user can see,(objects name, or "(nimetu)" if the object has no name) if set, returns html href tags.
	@returns string/url or string/html href

	@comments
		returns the url where can change the given object in AW
	@example
		$url = html::obj_change_url($object);
	**/
	function obj_change_url($o, $caption = NULL)
	{
		if (is_array($o))
		{
			$res = array();
			foreach($o as $id)
			{
				$res[] = html::obj_change_url($id);
			}
			return join(", ", $res);
		}

		if (!is_object($o))
		{
			if ($this->can("view", $o))
			{
				$o = obj($o);
			}
			else
			{
				return "";
			}
		}
		return html::get_change_url($o->id(), array("return_url" => get_ru()), $caption === null ? parse_obj_name($o->name()) : $caption);
	}

	/**
	@attrib api=1 params=pos
		
	@param o required type=object
		object to be changed
	@param caption optional type=string
		the text user can see,(objects name, or "(nimetu)" if the object has no name) if set, returns html href tags.
	@returns string/url or string/html href
	
	@comments
		returns the url where can change the given object in AW
	@example
		$url = html::obj_view_url($object);
	**/
	function obj_view_url($o, $caption = NULL)
	{
		if (is_array($o))
		{
			$res = array();
			foreach($o as $id)
			{
				$res[] = html::obj_change_url($id);
			}
			return join(", ", $res);
		}

		if (!is_object($o))
		{
			if ($this->can("view", $o))
			{
				$o = obj($o);
			}
			else
			{
				return "";
			}
		}
		return html::get_change_url($o->id(), array("action" => "view", "return_url" => get_ru()), $caption === null ? parse_obj_name($o->name()) : $caption);
	}

	
	/**
	@attrib api=1 params=pos

	@param oid required type=oid
		objects oid witch is going to be changed
	@param params optional type=array
		url parameters: array("parameter name" - "parameter value", ...)
	@param caption optional type=string
		the text user can see, if set, returns html href tags
	@param title optional type=string
		you can see this text when scrolling over the link
	@returns string/url or string/html href

	@comments
		returns the url where can change the given object in AW
	@example
		$url = html::get_change_url($val["oid"], array("return_url" => get_ru()), $val["name"];
	**/
	function get_change_url($oid, $params = array(), $caption = false, $title=NULL)
	{
		if (!$this->can("view", $oid))
		{
			if ($caption != "")
			{
				return $caption;
			}
			return "";
		}
		$obj = &obj($oid);
		$params["id"] = $obj->id();
		if ($_GET["action"] != "view" && $this->can("edit", $oid))
		{
			$act = "change";
		}
		else
		{
			$act = "view";
		}
		$retval = $this->mk_my_orb($act, $params, $obj->class_id());
		if($caption || (is_integer($caption) && $caption == 0))
		{
			$retval = html::href(array(
				"url" => $retval,
				"caption" => $caption,
				"title" => $title
			));
		}
		return $retval;
	}

	/**
	@attrib api=1 params=pos

	@param class_id required type=clid
		new object class id
	@param parent optional type=oid
		new object parent oid
	@param caption optional type=bool
		the text user can see, if caption is set, returns html href
	@param params optional type=array
		url parameters: array("parameter name" - "parameter value", ...)
	@returns string/url or string/html href

	@comments
		returns the url where can make a new object with given class_id
	@example
		$url = html::get_change_url($arr["class_id"] , $arr["parent_id"] , array("do" => "die" , "message" => "RIP")));
	**/
	function get_new_url($class_id, $parent, $params = array(), $caption = false)
	{
		$params = array("parent" => $parent) + $params;
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
