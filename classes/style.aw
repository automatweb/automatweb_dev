<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/style.aw,v 2.16 2002/12/02 11:18:52 kristo Exp $

define("ST_TABLE",0);
define("ST_CELL",1);
define("ST_ELEMENT",2);

$style_cache = array();

class style extends aw_template
{
	var $type_names = array(0 => LC_STYLE_TABLE_STYLE, 1 => LC_STYLE_CELL_STYLE, 2 => LC_STYLE_ELEMENT_STYLE);

	function style()
	{
		$this->init("style");
		$this->sub_merge = 1;
		lc_load("definition");
		$this->lc_load("style","lc_style");
	}

	function db_listall($parent,$type = -1)
	{	
		if ($type != -1)
		{
			$ss = " AND styles.type = $type ";
		}
		if ($parent > 0)
		{
			$pt = "AND parent = $parent";
		}
		$this->db_query("SELECT objects.*,styles.* FROM objects LEFT JOIN styles ON styles.id = objects.oid WHERE status != 0 AND class_id = ".CL_STYLE." $pt ".$ss);
	}

	function get($id)
	{
		if (!$id)
		{
			return false;
		}
		$this->db_query("SELECT objects.*,styles.* FROM objects LEFT JOIN styles ON styles.id = objects.oid WHERE oid = $id ");
		return $this->db_next();
	}

	function get_select($parent, $type, $addempty = false)
	{
		$this->db_listall(0,$type);
		if ($addempty)
		{
			$arr = array(0 => "");
		}
		else
		{
			$arr = array();
		}
		while ($row = $this->db_next())
		{
			$arr[$row["id"]] = $row["name"];
		}

		return $arr;
	}

	// parent
	function glist($arr)
	{
		extract($arr);

		$this->mk_path($parent,LC_STYLE_STYLES);

		$this->read_template("list.tpl");
		$this->db_listall($parent);
		$this->vars(array("parent" => $parent,"add" => $this->mk_orb("add",array("parent" => $parent))));
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"], 
				"type" => $this->type_names[$row["type"]], 
				"style_id" => $row["id"],
				"change"	=> $this->mk_orb("change",array("parent" => $parent, "id" => $row["id"])),
				"delete"	=> $this->mk_orb("delete",array("parent" => $parent, "id" => $row["id"]))
			));
			$this->parse("LINE");
		}
		return $this->parse();
	}

	// parent
	function add($arr)
	{
		extract($arr);

		$this->mk_path($parent,LC_STYLE_ADD_STYLE);

		$this->read_template("add_sel.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_sel",array("parent" => $parent))));
		return $this->parse();
	}

	// parent, id
	function change($arr)
	{
		extract($arr);

		$this->mk_path($parent,LC_STYLE_CHANGE_STYLE);

		$this->style = $this->get($id);
		switch($this->style["type"])
		{
			case ST_TABLE:
				return $this->change_table($arr);
			case ST_CELL:
				return $this->change_cell($arr);
			case ST_ELEMENT:
				return $this->change_element($arr);
		}
	}

	function change_table($arr)
	{
		extract($arr);
		$this->read_template("change_table.tpl");

		$style = unserialize($this->style["style"]);

		$sel = $this->get_select(0,ST_CELL);

		$this->vars(array(
			"name" => $this->style["name"], 
			"comment" => $this->style["comment"], 
			"bgcolor"	=> $style["bgcolor"],
			"cellpadding"	=> $style["cellpadding"],
			"cellspacing"	=> $style["cellspacing"],
			"border"			=> $style["border"],
			"height"			=> $style["height"],
			"width"				=> $style["width"],
			"hspace"			=> $style["hspace"],
			"vspace"			=> $style["vspace"],
			"header_style"	=> $this->picker($style["header_style"],$sel),
			"footer_style"	=> $this->picker($style["footer_style"],$sel),
			"even_style"	=> $this->picker($style["even_style"],$sel),
			"odd_style"	=> $this->picker($style["odd_style"],$sel),
			"num_frows"			=> $style["num_frows"],
			"num_fcols"			=> $style["num_fcols"],
			"frow_style"	=> $this->picker($style["frow_style"],$sel),
			"fcol_style"	=> $this->picker($style["fcol_style"],$sel),
			"reforb"			=> $this->mk_reforb("submit",array("parent" => $parent, "id" => $id))
		));

		return $this->parse();
	}

	function change_cell($arr)
	{
		extract($arr);
		
		$fonts = array("" => "", "arial" => "Arial","times" => "Times", "verdana" => "Verdana","tahoma" => "Tahoma", "geneva"  => "Geneva", "helvetica" => "Helvetica", "Trebuchet MS" => "Trebuchet MS");

		$fontsizez = array("" => "","-1" => -1, "0" => 0, "1" => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);

		$fontstyles = array('normal' => "Tavaline", 'bold' => "Bold",'italic' => "Italic", 'underline' => "Underline");

		$style = unserialize($this->style["style"]);

		$this->read_template("change_cell.tpl");
		$this->vars(array(
			"name" => $this->style["name"], 
			"comment" => $this->style["comment"], 
			"font1"		=> $this->option_list($style["font1"], $fonts),
			"font2"		=> $this->option_list($style["font2"], $fonts),
			"font3"		=> $this->option_list($style["font3"], $fonts),
			"fontsize"	=> $this->option_list($style["fontsize"], $fontsizez),
			"color"		=> $style["color"],
			"bgcolor"	=> $style["bgcolor"],
			"fontstyles"	=> $this->option_list($style["fontstyle"], $fontstyles),
			"align_left"	=> $style["align"] == "left" ? "CHECKED" : "",
			"align_right"	=> $style["align"] == "right" ? "CHECKED" : "",
			"align_center"	=> $style["align"] == "center" ? "CHECKED" : "",
			"valign_top"	=> $style["valign"] == "top" ? "CHECKED" : "",
			"valign_center"	=> $style["valign"] == "center" ? "CHECKED" : "",
			"valign_bottom"	=> $style["valign"] == "bottom" ? "CHECKED" : "",
			"height"	=> $style["height"],
			"width"		=> $style["width"],
			"css_class"		=> $style["css_class"],
			"nowrap"	=> $style["nowrap"]  ? "CHECKED" : "",
			"reforb"	=> $this->mk_reforb("submit",array("parent" => $parent, "id" => $id))
		));
		return $this->parse();
	}

	function submit_sel($arr)
	{
		// lisame 6ige tyybiga
		$id = $this->new_object(array(
			"parent" => $arr["parent"], 
			"class_id" => CL_STYLE, 
			"name" => $arr["name"], 
			"comment" => $arr["comment"]
		));
		$this->db_query("INSERT INTO styles (id,type) values($id,".$arr["type"].")");
		return $this->mk_orb("change", array("parent" => $arr["parent"], "id" => $id));
	}

	function submit($arr)
	{
		extract($arr);

		$sts = serialize($st);

		$this->db_query("UPDATE styles SET style = '$sts' WHERE id = $id");

		$this->_log("style", "changed table $name");
		$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		return $this->mk_orb("change",array("parent" => $parent, "id" => $id));
	}

	function delete($arr)
	{
		extract($arr);

		$this->delete_object($id);
		header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent),"menuedit"));
	}

	function get_table_string($id)
	{
		$st = $this->mk_cache($id);

		if ($st["type"] != ST_TABLE)
			$this->raise_error(ERR_STYLE_WTYPE,"style->get_table_string($id): Style is not for tables!");

		if ($st["bgcolor"] != "")
		{
			$str.="bgcolor=\"".$st["bgcolor"]."\" ";
		}
		if ($st["border"] != "")
		{
			$str.="border=\"".$st["border"]."\" ";
		}
		if ($st["cellpadding"] != "")
		{
			$str.="cellpadding=\"".$st["cellpadding"]."\" ";
		}
		if ($st["cellspacing"] != "")
		{
			$str.="cellspacing=\"".$st["cellspacing"]."\" ";
		}
		if ($st["height"] != "")
		{
			$str.="height=\"".$st["height"]."\" ";
		}
		if ($st["width"] != "")
		{
			$str.="width=\"".$st["width"]."\" ";
		}
		if ($st["hspace"] != "")
		{
			$str.="hspace=\"".$st["hspace"]."\" ";
		}
		if ($st["vspace"] != "")
		{
			$str.="vspace=\"".$st["vspace"]."\" ";
		}

		return $str;
	}

	function get_cell_begin_str($id,$colspan = -1, $rowspan = -1)
	{
		$st = $this->mk_cache($id);

		$fstr = array();
		if ($st["font1"] != "")		$fstr[] = $st["font1"];
		if ($st["font2"] != "")		$fstr[] = $st["font2"];
		if ($st["font3"] != "")		$fstr[] = $st["font3"];
		$fstr = join(",", $fstr);
		$fstyles = array();
		if ($fstr != "")
		{
			$fstyles[] = "face=\"".$fstr."\"";
		}

		if ($st["fontsize"])
		{
			$fstyles[] = "size=\"".$st["fontsize"]."\"";
		}

		if ($st["color"] != "")
		{
			$fstyles[] = "color=\"".$st["color"]."\"";
		}

		$fsstr = join(" ",$fstyles);
		if ($fsstr != "")
		{
			$str = "<font ".$fsstr.">";
		}

		if (isset($st["bgcolor"]) && $st["bgcolor"] != "")
		{
			$cstyles[] = "bgcolor=\"".$st["bgcolor"]."\"";
		}
		if (isset($st["align"]) && $st["align"] != "")
		{
			$cstyles[] = "align=\"".$st["align"]."\"";
		}
		if (isset($st["valign"]) && $st["valign"] != "")
		{
			$cstyles[] = "valign=\"".$st["valign"]."\"";
		}
		if (isset($st["height"]) && $st["height"] != "")
		{
			$cstyles[] = "height=\"".$st["height"]."\"";
		}
		if (isset($st["width"]) && $st["width"] != "")
		{
			$cstyles[] = "width=\"".$st["width"]."\"";
		}
		if (isset($st["nowrap"]) && $st["nowrap"] != "")
		{
			$cstyles[] = "nowrap";
		}
		if ($colspan > 0)
		{
			$cstyles[] = "colspan=\"".$colspan."\"";
		}
		if ($rowspan > 0)
		{
			$cstyles[] = "rowspan=\"".$rowspan."\"";
		}

		if ($st["fontstyle"] == "bold")
		{
			$str.="<b>";
		}
		else
		if ($st["fontstyle"] == "italic")
		{
			$str.="<i>";
		}
		else
		if ($st["fontstyle"] == "underline")
		{
			$str.="<u>";
		}

		$cstr = join(" ",$cstyles);
		if ($st["css_class"] != "")
		{
			$cssc=" class=\"".$st["css_class"]."\" ";
		}
		if ($cstr != "")
		{
			$cell = "<td ".$cssc.$cstr.">".$str;
		}
		else
		{
			$cell = "<td".$cssc.">".$str;
		}

		return $cell;
	}

	function get_cell_end_str($id)
	{
		$st = $this->mk_cache($id);

		$str = "";
		if ($st["fontstyle"] == "bold")
		{
			$str = "</b>";
		}
		else
		if ($st["fontstyle"] == "italic")
		{
			$str = "</i>";
		}
		else
		if ($st["fontstyle"] == "underline")
		{
			$str = "</u>";
		}

		if ($st["font1"] != "" || $st["font2"] != "" || $st["font3"] != "" || $st["fontsize"] || $st["color"] != "")
		{
			$str.= "</font>";
		}

		return $str;
	}

	function get_frow_style($id)
	{
		$st = $this->mk_cache($id);

		return $st["frow_style"];
	}

	function get_fcol_style($id)
	{
		$st = $this->mk_cache($id);

		return $st["fcol_style"];
	}

	function get_num_frows($id)
	{
		$st = $this->mk_cache($id);

		return $st["num_frows"];
	}

	function get_num_fcols($id)
	{
		$st = $this->mk_cache($id);

		return $st["num_fcols"];
	}

	function get_text_begin_str($id)
	{
		$st = $this->mk_cache($id);

		$fstr = array();
		if ($st["font1"] != "")		$fstr[] = $st["font1"];
		if ($st["font2"] != "")		$fstr[] = $st["font2"];
		if ($st["font3"] != "")		$fstr[] = $st["font3"];
		$fstr = join(",", $fstr);
		if ($fstr != "")
		{
			$fstyles[] = "face=\"".$fstr."\"";
		}

		if ($st["fontsize"] != "")
		{
			$fstyles[] = "size=\"".$st["fontsize"]."\"";
		}

		if ($st["color"] != "")
		{
			$fstyles[] = "color=\"".$st["color"]."\"";
		}

		$fsstr = join(" ",$fstyles);
		if ($fsstr != "")
		{
			$str = "<font ".$fsstr.">";
		}

		if ($st["fontstyle"] == "bold")
		{
			$str.="<b>";
		}
		else
		if ($st["fontstyle"] == "italic")
		{
			$str.="<i>";
		}
		else
		if ($st["fontstyle"] == "underline")
		{
			$str.="<u>";
		}

		return $str;
	}

	function get_text_end_str($id)
	{
		$st = $this->mk_cache($id);

		if ($st["fontstyle"] == "bold")
		{
			$str = "</b>";
		}
		else
		if ($st["fontstyle"] == "italic")
		{
			$str = "</i>";
		}
		else
		if ($st["fontstyle"] == "underline")
		{
			$str = "</u>";
		}

		if ($st["font1"] != "" || $st["font2"] != "" || $st["font3"] != "" || $st["fontsize"] != "" || $st["color"] != "")
		{
			$str.= "</font>";
		}

		return $str;
	}

	function get_header_style($id)
	{
		$st = $this->mk_cache($id);
		return $st["header_style"];
	}

	function get_footer_style($id)
	{
		$st = $this->mk_cache($id);
		return $st["footer_style"];
	}

	function get_odd_style($id)
	{
		$st = $this->mk_cache($id);
		return $st["odd_style"];
	}

	function get_even_style($id)
	{
		$st = $this->mk_cache($id);
		return $st["even_style"];
	}

	function get_css_class($id)
	{
		$st = $this->mk_cache($id);
		return $st["css_class"];
	}

	function mk_cache($id)
	{
		if (aw_cache_get("style_cache",$id))
		{
			$stl = aw_cache_get("style_cache",$id);
		}
		else
		{
			$st = $this->get($id);
			$stl = unserialize($st["style"]);
			$stl["name"] = $st["name"];
			aw_cache_set("style_cache",$id,$stl);
		}
		return $stl;
	}

	function _get_css($st)
	{
		$fstr = array();
		if ($st["font1"] != "")		
		{
			$fstr[] = $st["font1"];
		}
		if ($st["font2"] != "")		
		{
			$fstr[] = $st["font2"];
		}
		if ($st["font3"] != "")
		{
			$fstr[] = $st["font3"];
		}
		$fstyles = array();
		$fstr = join(",", $fstr);
		if ($fstr != "")
		{
			$fstyles[] = "font-family: ".$fstr.";";
		}

		if ($st["fontsize"] != "")
		{
			$fstyles[] = "font-size: ".(4+$st["fontsize"]*3)."pt;";
		}

		if ($st["color"] != "")
		{
			$fstyles[] = "color: ".$st["color"].";";
		}

		if ($st["fontstyle"] == "bold")
		{
			$fstyles[] = "font-weight: bold;";
		}
		else
		if ($st["fontstyle"] == "italic")
		{
			$fstyles[] = "font-style: italic;";
		}
		else
		if ($st["fontstyle"] == "underline")
		{
			$fstyles[] = "text-decoration: underline;";
		}

		if (isset($st["bgcolor"]) && $st["bgcolor"] != "")
		{
			$fstyles[] = "background-color: ".$st["bgcolor"].";";
		}
		if (isset($st["align"]) && $st["align"] != "")
		{
			$fstyles[] = "text-align: ".$st["align"].";";
		}
		if (isset($st["valign"]) && $st["valign"] != "")
		{
			$fstyles[] = "vertical-align: ".$st["valign"].";";
		}
		if (isset($st["height"]) && $st["height"] != "")
		{
			$fstyles[] = "height: ".$st["height"].";";
		}
		if (isset($st["width"]) && $st["width"] != "")
		{
			$fstyles[] = "width: ".$st["width"].";";
		}

		if (isset($st["nowrap"]) && $st["nowrap"] == 1)
		{
			$fstyles[] = "white-space: nowrap;";
		}

		return  join("\n",$fstyles);
	}

	////
	// !returns the css definition that matches style $id
	function get_css($id,$a_id = 0)
	{
		$st = $this->mk_cache($id);
		
		$fsstr = $this->_get_css($st);
		if ($fsstr != "")
		{
			$str = ".style_".$id." { \n".$fsstr." \n} \n";
		}

		if ($a_id)
		{
			$st = $this->mk_cache($a_id);
			
			$fsstr = $this->_get_css($st);
			if ($fsstr != "")
			{
				$str = $str."\n.style_".$id." a { \n".$fsstr." \n} \n";
			}
		}
		return $str;
	}

	function check_environment(&$sys, $fix = false)
	{
		$op_table = array(
			"name" => "styles", 
			"fields" => array(
				"id" => array("name" => "id", "length" => 11, "type" => "int", "flags" => ""),
				"style" => array("name" => "style", "length" => 65535, "type" => "blob", "flags" => ""),
				"type" => array("name" => "type", "length" => 11, "type" => "int", "flags" => ""),
			)
		);

		$ret = $sys->check_admin_templates("style", array("list.tpl","add_sel.tpl","change_table.tpl","change_cell.tpl"));
		$ret.= $sys->check_db_tables(array($op_table),$fix);

		return $ret;
	}


	function _serialize($arr)
	{
		extract($arr);
		$this->db_query("SELECT objects.*, styles.* FROM objects LEFT JOIN styles ON styles.id = objects.oid WHERE oid = $oid");
		$row = $this->db_next();
		if (!$row)
		{
			return false;
		}
		return serialize($row);
	}

	function _unserialize($arr)
	{
		extract($arr);

		$row = unserialize($str);
		// basically, we create a new object and insert the stuff in the array right back in it. 
		$oid = $this->new_object(array("parent" => $parent, "name" => $row["name"], "class_id" => CL_STYLE, "status" => $row["status"], "comment" => $row["comment"], "last" => $row["last"], "jrk" => $row["jrk"], "visible" => $row["visible"], "period" => $period, "alias" => $row["alias"], "periodic" => $row["periodic"], "doc_template" => $row["doc_template"], "activate_at" => $row["activate_at"], "deactivate_at" => $row["deactivate_at"], "autoactivate" => $row["autoactivate"], "autodeactivate" => $row["autodeactivate"], "brother_of" => $row["brother_of"]));

		// same with the style. 
		$this->quote(&$row);
		$this->db_query("INSERT INTO	styles(id,style,type) values($oid,'".$row["style"]."','".$row["type"]."')");

		return $oid;
	}
}
?>
