<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/css.aw,v 2.35 2002/12/24 15:08:15 kristo Exp $
// css.aw - CSS (Cascaded Style Sheets) haldus
/*

@classinfo syslog_type=ST_CSS relationmgr=yes
@groupinfo preview caption=Eelvaade

@default group=general 
@default table=objects
@default field=meta
@default method=serialize

@property ffamily type=select
@caption Font

@property italic type=checkbox ch_value=1 
@caption <i>Italic</i>

@property bold type=checkbox ch_value=1 
@caption <b>Bold</b>

@property underline type=checkbox ch_value=1
@caption <u>Underline</u>

@property size type=textbox size=5
@caption Suurus

@property units type=select 
@caption Suuruse &uuml;hikud

@property fgcolor type=colorpicker
@caption Teksti v&auml;rv

@property bgcolor type=colorpicker 
@caption Tausta v&auml;rv

@property lineheight type=textbox size=5
@caption Joone k&otilde;rgus

@property lhunits type=select 
@caption Joone k&otilde;rguse &uuml;hikud

@property border type=textbox size=5
@caption Border width

@property bordercolor type=colorpicker 
@caption Border color

@property align type=select 
@caption Align

@property valign type=select 
@caption Valign

@property width type=textbox size=5
@caption Laius

@property w_units type=select 
@caption Laiuse &uuml;hikud

@property height type=textbox size=5
@caption K&otilde;rgus

@property h_units type=select 
@caption K&otilde;rguse &uuml;hikud

@property a_style type=relpicker reltype=RELTYPE_CSS
@caption Lingi stiil

@property a_hover_style type=relpicker reltype=RELTYPE_CSS
@caption Lingi stiil (hover)

@property a_visited_style type=relpicker reltype=RELTYPE_CSS
@caption Lingi stiil (visited)

@property a_active_style type=relpicker reltype=RELTYPE_CSS
@caption Lingi stiil (active)

@property user_css type=textarea width=30 height=5 
@caption Kasutaja css

@property pre type=text group=preview no_caption=1
@caption Eelvaade

*/

define("RELTYPE_CSS", 1);

class css extends class_base
{
	function css ($args = array())
	{
		// kuidas ma seda edimisvormi alati automawebi juurest lugeda saan?
		$this->init(array(
			"tpldir" => "css",
			"clid" => CL_CSS
		));

		$this->lc_load("css","lc_css");

		// fondifamilyd, do not change the order
		$this->font_families = array(
			"0" => "Verdana,Helvetica,sans-serif",
			"1" => "Arial,Helvetica,sans-serif",
			"2" => "Tahoma,sans-serif",
			"3" => "serif",
			"4" => "sans-serif",
			"5" => "monospace",
			"6" => "cursive",
			"7" => "Trebuchet MS,Tahoma,sans-serif"
		);
	}


	/** I could not think of another place to stick this. oh well. whatever

		@attrib name=colorpicker params=name all_args="1" default="0"


		@returns


		@comment

	**/
	function colorpicker($arr)
	{
		if (method_exists($this,"db_init"))
		{
			$this->db_init();
		};
		if (method_exists($this,"tpl_init"))
		{
			$this->tpl_init("automatweb");
		}
		$this->read_template("colorpicker.tpl");
		die($this->parse());
	}

	function get_style_data_by_id($id)
	{
		if (!empty($id))
		{
			$style_obj = new object($id);
			$css = $style_obj->meta("css");
			if (!is_array($css) || count($css) < 1)
			{
				$css = $style_obj->meta();
			}
			return $this->_gen_css_style("st${id}",$css);
		};
	}

	////
	// !genereerib arrayst tegeliku stiili
	// name - stiili nimi, data, array css atribuutidest
	function _gen_css_style($name,$data = array())
	{
		//echo "name = $name , data = ".dbg::dump($data);
		$retval = ".$name {\n";
		if (!(is_array($data)))
		{
			return false;
		};

		foreach($data as $key => $val)
		{
			if ($val === "")
			{
				continue;
			}
			$ign = false;
			switch($key)
			{
				case "ffamily":
					if (is_numeric($val))
					{
						$val = $this->font_families[$val];
					}
					$mask = "font-family: %s;\n";
					break;

				case "fstyle":
					$mask = "font-style: %s;\n";
					break;

				case "fweight":
					$mask = "font-weight: %s;\n";
					break;

				case "bold":
					if ($val == 1)
					{
						$mask = "font-weight: bold;\n";
					}
					else
					{
						$ign = true;
					};
					break;

				case "fgcolor":
					$mask = "color: %s;\n";
					break;

				case "bgcolor":
					$mask = "background-color: %s;\n";
					break;

				case "textdecoration":
					$mask = "text-decoration: %s;\n";
					break;

				case "lineheight":
					$mask = "line-height: %s".$data["lhunits"].";\n";
					break;

				case "border":
					$mask = "border-width: %spx;\n";
					break;
				
				case "valign":
					$mask = "vertical-align: %s;\n";
					break;
				
				case "align":
					$mask = "text-align: %s;\n";
					break;
				
				case "width":
					$mask = "width: %s".$data["w_units"].";\n";
					break;

				case "height":
					$mask = "height: %s".$data["h_units"].";\n";
					break;

				default:
					$ign = true;
					break;
			};

			if ($key == "size")
			{
				$retval .= "\tfont-size: $val" . $data["units"] . ";\n";
			}
			else
			if ($key != "units" && !$ign)
			{
				$retval .= sprintf("\t" . $mask,$val);
			};
		}

		$retval .= $data["user_css"];

		$retval .= "}\n";

		if (!$this->in_gen)
		{
			$this->in_gen = true;
			if ($data["a_style"])
			{
				$retval.=$this->_gen_css_style($name." a:link",$this->get_cached_style_data($data["a_style"]));
			}
			if ($data["a_hover_style"])
			{
				$retval.=$this->_gen_css_style($name." a:hover",$this->get_cached_style_data($data["a_hover_style"]));
			}
			if ($data["a_visited_style"])
			{
				$retval.=$this->_gen_css_style($name." a:visited",$this->get_cached_style_data($data["a_visited_style"]));
			}
			if ($data["a_active_style"])
			{
				$retval.=$this->_gen_css_style($name." a:active",$this->get_cached_style_data($data["a_active_style"]));
			}
			$this->in_gen = false;
		}
		return $retval;
	}

	function get_cached_style_data($id)
	{
		if (!aw_cache_get("AW_CSS_STYLE_CACHE",$id))
		{
			$css_info = $this->get_obj_meta($id);
			aw_cache_set("AW_CSS_STYLE_CACHE",$id,$css_info["meta"]["css"]);
		}
		return aw_cache_get("AW_CSS_STYLE_CACHE",$id);
	}

	function get_select($addempty = false)
	{
		$ret = array();
		if ($addempty)
		{
			$ret[0] = "";
		}
		$this->db_query("SELECT oid,name FROM objects WHERE class_id = ".CL_CSS." AND status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_CSS => "css stiil"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_CSS)
		{
			return array(CL_CSS);
		}
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		switch($prop['name'])
		{
			case "ffamily":
				$prop['options'] = $this->font_families;
				break;

			case "align":
				$prop['options'] = array(
					"" => "",
					"left" => "Vasak",
					"center" => "Keskel",
					"right" => "Paremal"
				);
				break;

			case "valign":
				$prop['options'] = array(
					"" => "",
					"top" => "&Uuml;leval",
					"middle" => "Keskel",
					"bottom" => "All"
				);
				break;

			case "units":
			case "lhunits":
			case "w_units":
			case "h_units":
				$prop['options'] = array(
					"px" => "pikslit",
					"pt" => "punkti (1pt=1/72in)",
					"in" => "tolli",
					"em" => "ems (suhteline)",
					"cm" => "sentimeetrit",
					"mm" => "millimeetrit",
				);
				break;

			case "pre":
				$this->read_template("preview.tpl");
				$prop['value'] = $this->parse();
				break;
		}
		return PROP_OK;
	}

	function callback_pre_edit($arr)
	{
		$cssmeta = $arr["obj_inst"]->meta("css");
		if (is_array($cssmeta) && count($cssmeta) > 0)
		{
			return;
		}
		$_t = new aw_array($cssmeta);
		foreach($_t->get() as $k => $v)
		{
			$arr["obj_inst"]->set_meta($k,$v);
		}
	}

	function callback_pre_save($arr)
	{
		$meta = $arr["obj_inst"]->meta();
		$cssmeta = array(); //$arr["obj_inst"]->meta("css");
		$_t = new aw_array($meta);
		foreach($_t->get() as $k => $v)
		{
//			echo "set $k $v ";
			$cssmeta[$k] = $v;
		}
		unset($cssmeta["css"]);
		$arr["obj_inst"]->set_meta("css",$cssmeta);
	}
};
