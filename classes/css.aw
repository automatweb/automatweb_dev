<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/css.aw,v 2.26 2003/07/04 14:10:11 duke Exp $
// css.aw - CSS (Cascaded Style Sheets) haldus
/*

@classinfo syslog_type=ST_CSS relationmgr=yes
@groupinfo general caption=Üldine
@groupinfo preview caption=Eelvaade

@default group=general 
@default table=objects

@property ffamily type=select field=meta method=serialize group=general
@caption Font

@property italic type=checkbox ch_value=1 field=meta method=serialize group=general
@caption <i>Italic</i>

@property bold type=checkbox ch_value=1 field=meta method=serialize group=general
@caption <b>Bold</b>

@property underline type=checkbox ch_value=1 field=meta method=serialize group=general
@caption <u>Underline</u>

@property size type=textbox field=meta method=serialize group=general size=5
@caption Suurus

@property units type=select field=meta method=serialize group=general
@caption Suuruse &uuml;hikud

@property fgcolor type=colorpicker field=meta method=serialize group=general
@caption Teksti v&auml;rv

@property bgcolor type=colorpicker field=meta method=serialize group=general
@caption Tausta v&auml;rv

@property lineheight type=textbox field=meta method=serialize group=general size=5
@caption Joone k&otilde;rgus

@property lhunits type=select field=meta method=serialize group=general
@caption Joone k&otilde;rguse &uuml;hikud

@property border type=textbox field=meta method=serialize group=general size=5
@caption Border width

@property bordercolor type=colorpicker field=meta method=serialize group=general
@caption Border color

@property align type=select field=meta method=serialize group=general
@caption Align

@property valign type=select field=meta method=serialize group=general
@caption Valign

@property width type=textbox field=meta method=serialize group=general size=5
@caption Laius

@property w_units type=select field=meta method=serialize group=general
@caption Laiuse &uuml;hikud

@property height type=textbox field=meta method=serialize group=general size=5
@caption K&otilde;rgus 

@property h_units type=select field=meta method=serialize group=general
@caption K&otilde;rguse &uuml;hikud

@property a_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil

@property a_hover_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil (hover)

@property a_visited_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil (visited)

@property a_active_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil (active)

@property user_css type=textarea width=30 height=5 field=meta method=serialize group=general
@caption Kasutaja css

@property pre type=text field=meta method=serialize group=preview no_caption=1

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

		$this->units = array(
			"px" => "pikslit",
			"pt" => "punkti (1pt=1/72in)",
			"in" => "tolli",
			"em" => "ems (suhteline)",
			"cm" => "sentimeetrit",
			"mm" => "millimeetrit",
		);

		// siin on süsteemsed stiilid defineeritud.
		$this->styles = array(
			"0" => "ftitle",
			"1" => "fcaption",
			"2" => "title",
			"3" => "plain",
			"4" => "header1",
			"5" => "fgtitle",
			"6" => "fgtext",
		);

		$this->aligns = array(
			"" => "",
			"left" => "Vasak",
			"center" => "Keskel",
			"right" => "Paremal"
		);

		$this->valigns = array(
			"" => "",
			"top" => "&Uuml;leval",
			"middle" => "Keskel",
			"bottom" => "All"
		);

		// siin peaks diferentseerima selle, kas tegu on süsteemste voi kasutaja
		// stiilidega.
		$udata = $this->get_user();
		$this->rootmenu = $udata["home_folder"];
	}

	////
	// !genereerib arrayst tegeliku stiili
	// name - stiili nimi, data, array css atribuutidest
	function _gen_css_style($name,$data = array())
	{
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
					$mask = "font-family: %s;\n";
					break;

				case "fstyle":
					$mask = "font-style: %s;\n";
					break;

				case "fweight":
					$mask = "font-weight: %s;\n";
					break;
				
				case "bold":
					$mask = "font-weight: bold;\n";
					break;
				
				case "fgcolor":
					$mask = "color: %s;\n";
					break;

				case "bgcolor":
					$mask = "background: %s;\n";
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

	////
	// !I could not think of another place to stick this. oh well. whatever
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
				$prop['options'] = $this->aligns;
				break;

			case "valign":
				$prop['options'] = $this->valigns;
				break;

			case "units":
			case "lhunits":
			case "w_units":
			case "h_units":
				$prop['options'] = $this->units;
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
		$dc =& $arr["coredata"];
		$_t = new aw_array($dc['meta']['css']);
		foreach($_t->get() as $k => $v)
		{
			$dc['meta'][$k] = $v;
		}
	}

	function callback_pre_save($arr)
	{
		$dc =& $arr["coredata"];
		$_t = new aw_array($dc['metadata']);
		foreach($_t->get() as $k => $v)
		{
			$dc['metadata']['css'][$k] = $v;
		}
	}
};
