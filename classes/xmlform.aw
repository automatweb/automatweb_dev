<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/xmlform.aw,v 2.4 2002/11/07 10:52:25 kristo Exp $
// handles xml based configuration forms
class xmlform extends aw_template
{
	function xmlform($args = array())
	{
		$this->init("forms");
		$this->lc_load("form","lc_form");
	}

	function gen_preview($args = array())
	{
		extract($args);	
		/*
		print "<pre>";
		print_r($xmldata);
		print "</pre>";
		*/
		$fname = aw_ini_get("basedir") . "/xml/config/planner.xml";
		list($t1,$t2) = $this->get_xml_definition($fname);

		$this->read_template("show.tpl");


		$fe = get_instance("formgen/form_element");
		// XXX: need to load the correct form
		$fe->form->lang_id = aw_global_get("lang_id");

		$lang_id = aw_global_get("lang_id");

		$res = "";

		// to create the map I first need to figure out how many lines would I have to show
		$rowcount = 0;

		foreach($t1 as $token)
		{
			$tmp = array();
			$attr = $token["attributes"];
			if ( ($token["type"] == "complete") && ($token["tag"] == "key") )
			{
				if ($attr["type"] == "checkbox")
				{
					$tmp["type"] = $attr["type"];
					$key = $attr["name"];

					if (isset($attr["index"]))
					{
						$checked = $xmldata[$key][$attr["index"]];
						//$key .= "[" . $attr["index"] . "]";
					}
					else
					{
						$checked = $xmldata[$key];
					};

					$tmp["default"] = $checked;
				}

				if ($attr["type"] == "time")
				{
					$tmp["type"] = "date";
					$tmp["has_hr"] = 1;
					$tmp["hour_ord"] = 1;
					$tmp["has_minute"] = 1;
					$tmp["minute_ord"] = 2;
					list($d,$m,$y) = explode("-",date("d-m-Y"));
					$tm = mktime($xmldata[$attr["name"]]["hour"],$xmldata[$attr["name"]]["minute"],0,$m,$d,$y);
					$tmp["default"] = $tm;
					$key = $attr["name"];
				}

				if ($attr["type"] == "submit")
				{
					$tmp["type"] = "submit";
					$tmp["button_text"] = $attr["caption"];
					$key = $attr["name"];



				}
				
				if ($attr["type"] == "textbox")
				{
					$tmp["type"] = "textbox";
					$key = $attr["name"];
				}

				$tmp["text"] = $attr["caption"];
				$tmp["sep_type"] = 1;
				$fe->arr = $tmp; // this sucks
				$rowcount++;
				$res .= $fe->do_core_userhtml($key,"","");
			};

			if ( ($token["type"] == "open") && ($token["tag"] == "key") && ($attr["type"] == "select"))
			{
				$name = $attr["name"];
				$caption = $attr["caption"];
				$options = array();
				$opentag = "select";;
			};

			if ( ($token["tag"] == "option") && ($token["type"] == "complete") )
			{
				$options[] = $attr["name"];
				//$options[$attr["id"]] = $attr["name"];
			};

			if ( ($token["tag"] == "key") && ($token["type"]  ==  "close") && ($opentag == "select") )
                        {
                                $element = "<select name='conf[$name]'>" . $this->picker(-1,$options) . "</select>";
				$opentag = "";
				$tmp["text"] = $caption;
				$tmp["sep_type"] = 1;
				$tmp["type"] = "listbox";
				$x = array_search($xmldata[$name],$options);
				$tmp["listbox_count"] = sizeof($options);
				$tmp["listbox_default"] = $x;
				$tmp["listbox_items"] = $options;
				$fe->arr = $tmp; // this sucks
				$rowcount++;
				$res .= $fe->do_core_userhtml("xml","","");
				
                        };

		}
		$t = $this->gen_fg_map($rowcount);
		return $res;
		//print_r($t1);
	}

	////
	// !Generates elements for config form
	// form_id - into which form to put the elements
	// config_file - which config file to use
	// parent - where to store the new elements
	// form - reference to form
	function gen_fg_elements($args = array())
	{
		extract($args);
		$fname = aw_ini_get("basedir") . "/xml/config/$config_file";
		list($t1,$t2) = $this->get_xml_definition($fname);
		$this->parent = $parent;
		$this->fid = $form_id;
		$this->cfg_arr = array();
		
		// to create the map I first need to figure out how many lines would I have to show
		$this->rowcount = 0;

		foreach($t1 as $token)
		{
			$tmp = array();
			$attr = $token["attributes"];
			if ( ($token["type"] == "complete") && ($token["tag"] == "key") )
			{
				if ($attr["type"] == "checkbox")
				{
					$tmp["type"] = $attr["type"];
					$key = $attr["name"];

					if (isset($attr["index"]))
					{
						//$key .= "[" . $attr["index"] . "]";
						$key = "[" . $key . "][" . $attr["index"] . "]";
					}
					
				}

				if ($attr["type"] == "time")
				{
					$tmp["type"] = "date";
					$tmp["has_hr"] = 1;
					$tmp["hour_ord"] = 1;
					$tmp["has_minute"] = 1;
					$tmp["minute_ord"] = 2;
					$key = $attr["name"];
				}
				
				if ($attr["type"] == "submit")
				{
					$tmp["type"] = "submit";
					$tmp["button_text"] = $attr["caption"];
					$key = $attr["name"];

				}

				if ($attr["type"] == "textbox")
				{
					$tmp["type"] = "textbox";
					$key = $attr["name"];
				}

				// create caption
				$text = $attr["caption"];
				$this->create_fg_element($key.":tekst",array("text" => $text),0);
				$this->create_fg_element($key,$tmp,1,true);
				$this->rowcount++;
			};

			if ( ($token["type"] == "open") && ($token["tag"] == "key") && ($attr["type"] == "select"))
			{
				$name = $attr["name"];
				$caption = $attr["caption"];
				$options = array();
				$opentag = "select";;
			};

			if ( ($token["tag"] == "option") && ($token["type"] == "complete") )
			{
				$options[] = $attr["name"];
				//$options[$attr["id"]] = $attr["name"];
			};

			if ( ($token["tag"] == "key") && ($token["type"]  ==  "close") && ($opentag == "select") )
                        {
				$opentag = "";
				//$tmp["text"] = $caption;
				//$tmp["sep_type"] = 1;
				$tmp["type"] = "listbox";
				$tmp["listbox_count"] = sizeof($options);
				$tmp["listbox_items"] = $options;
				$this->create_fg_element($name . ":tekst",array("text" => $caption),0);
				$this->create_fg_element($name,$tmp,1,true);
				$this->rowcount++;
				
                        };

		}
		$map = $this->gen_fg_map($this->rowcount);
		$this->cfg_arr["map"] = $map;
		$this->cfg_arr["cols"] = 2;
		$this->cfg_arr["rows"] = $this->rowcount;
		return $this->cfg_arr;
	}
	
	////
	// !reads the xml definition
	// fname - fq filename
	function get_xml_definition($fname)
	{
		$source = $this->get_file(array("file" => $fname));
		return parse_xml_def(array("xml" => $source));
	}

	////
	// !Generates map for formgen
	function gen_fg_map($rowcount)
	{
		$map = array();
		// map indexes are 0-based
		for ($i = 0; $i < $rowcount; $i++)
		{
			$map[$i][0] = array("row" => $i, "col" => 0);
			$map[$i][1] = array("row" => $i, "col" => 1);
		}

		return $map;
	}

	function create_fg_element($key,$tmp = array(),$col,$gen_config_key = false)
	{
		// adds a single html element
		$this->quote($key);
		$el = $this->new_object(array("parent" => $this->parent, "name" => $key, "class_id" => CL_FORM_ELEMENT));
		$this->db_query("INSERT INTO form_elements (id) values($el)");
		$this->db_query("ALTER TABLE form_".$this->fid."_entries ADD el_$el TEXT, ADD ev_$el TEXT");
		$this->db_query("INSERT INTO element2form(el_id,form_id) VALUES ($el,$this->fid)");

		$arr = array();
		$arr["id"] = $el;
		$arr["name"] = $key;

		$arr["linked_element"] = 0;
		$arr["linked_form"] = 0;
		$arr["linked_element"] = 0;
		$arr["rel_table_id"] = 0;

		$arr = $arr + $tmp;
		if ($gen_config_key == true)
		{
			$arr["config_key"] = $key;
		};

		$this->cfg_arr["elements"][$this->rowcount][$col][$el] = $arr;

	}

	function test($args = array())
	{
		// I need to serialize and cache the data
		// because decoding XML is expensive

		// retrieve information about elements
		$this->html = get_instance("html");
		$data = $this->get_file(array("file" => $this->cfg["basedir"] . "/xml/config/test.xml"));
		$this->_parse_config(array("data" => $data));

		// retrieve information about layoyt map
		$data = $this->get_file(array("file" => $this->cfg["basedir"] . "/xml/maps/test.xml"));

		$this->reforb = $this->mk_reforb("submit_test",array());

		$this->output = "";
		$this->_parse_map(array("data" => $data));
		return $this->output;

	}

	function submit_test($args = array())
	{
		print "<pre>";
		print_r($args);
		print "</pre>";


	}
	
	function _parse_config($args = array())
	{
		extract($args);
		$this->keys = array();
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, &$this);
		xml_set_element_handler($this->parser, "_config_tag_open", "_config_tag_close");
		xml_parse($this->parser, $data);
	}

	function _config_tag_open($parser,$tag,$attr)
	{
		switch($tag)
		{
			case "key":
				$this->keyname = $attr["name"];
				$this->keytype = $attr["type"];
				$this->args["_keyname"] = $attr["name"];
				$this->args["_keytype"] = $attr["type"];
				if ($attr["value"])
				{
					$this->value = $attr["value"];
				}

				break;

			case "source":
				$this->source = $attr;
				break;

			case "arg":
				$this->args[$attr["name"]] = $attr["value"];
				break;

		};
				
	}
	
	function _config_tag_close($parser,$tag)
	{
		switch($tag)
		{
			case "source":
				$meth = $this->source["method"];
				$this->keys[$this->keyname] = $this->obj->$meth($this->args);
				$this->args = array();
				break;

			case "key":
				$this->_html[$this->keyname] = $this->_draw_form_element();
				break;
		};
	}

	function _draw_form_element($args = array())
	{
		$retval = "";
		switch($this->keytype)
		{
			case "text":
				$retval = $this->html->text(array(
					"name" => $this->keyname,
					"value" => $this->keys[$this->keyname],
				));
				break;

			case "select":
				$retval = $this->html->select(array(
					"name" => $this->keyname,
					"options" => $this->keys[$this->keyname],
				));
				break;

			case "checkbox":
				$retval = $this->html->checkbox(array(
					"name" => $this->keyname,
				));
				break;

			default:

		};
		return $retval;
				

	}

	function _config_get_data($args = array())
	{

	}

	function cfg_get($args = array())
	{
		extract($args);
		$retval = "";
		switch($_keyname)
		{
			case "default_view":
				$values = array(
					"a" => "kala",
					"b" => "liha",
					"c" => "piim",
				);

				$retval = $this->html->select(array(
					"name" => $_keyname,
					"options" => $values,
					"selected" => "b",
				));
				break;
			
			case "subject":
				$value = "sitt";
				$retval = $this->html->text(array(
					"name" => $_keyname,
					"value" => $value,
				));
				break;


		};
		return $retval;
				

	}

	function _parse_map($args = array())
	{
		extract($args);
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, &$this);
		xml_set_element_handler($this->parser, "_map_tag_open", "_map_tag_close");
		xml_parse($this->parser, $data);
	}

	function _map_tag_open($parser,$tag,$attr)
	{
		$html_attribs = $this->_map_gen_attribs($attr);
		switch($tag)
		{
			case "map":
				if (!$this->no_form)
				{
					$this->output .= "<form method='get' action='reforb.aw'>\n";
				};
				$this->output .= "<table border='1' $html_attribs>\n";
				$this->rows = $attr["rows"];
				$this->cols = $attr["cols"];
				break;

			case "row":
				$this->output .= "<tr$html_attribs>\n";
				break;

			case "col":
				$this->output .= "<td$html_attribs>";
				break;

			case "caption":
				$this->output .= $this->obj->vars[$attr["src"]];
				break;

			case "element":
				$this->output .= $this->_html[$attr["src"]];
				break;
		};
				
	}
	
	function _map_tag_close($parser,$tag)
	{
		switch($tag)
		{
			case "col":
				$this->output .= "</td>\n";
				break;

			case "row":
				$this->output .= "</tr>\n";
				break;

			case "map":
				$this->output .= "</table>\n";
				$this->output .= $this->reforb;
				if (!$this->no_form)
				{
					$this->output .= "</form>\n";
				};
				break;
		};
	}

	function _map_gen_attribs($attribs = array())
	{
		$res = "";
		foreach($attribs as $key => $val)
		{
			switch($key)
			{
				case "class":
					$res .= " $key='$val'";
					break;

				default:
			};
		};

		return $res;
	}

	////
	// !Generates a form form elements and maps files
	function gen_html($args = array())
	{
		extract($args);
		global $awt;
		$awt->start("gen_html");
		$this->html = get_instance("html");
		$this->obj = $obj;
		$this->no_form = $no_form;
		$data = $this->get_file(array("file" => $this->cfg["basedir"] . "/xml/forms/$form.xml"));
		$this->_parse_config(array("data" => $data));

		// retrieve information about layoyt map
		$data = $this->get_file(array("file" => $this->cfg["basedir"] . "/xml/maps/$map.xml"));

		$this->reforb = $this->mk_reforb("submit_test",array());

		$this->output = "";
		$this->_parse_map(array("data" => $data));
		$awt->stop("gen_html");
		return $this->output;
	}
};
?>
