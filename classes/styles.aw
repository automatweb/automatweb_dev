<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/styles.aw,v 2.2 2001/05/25 09:07:36 kristo Exp $
class styles extends aw_template
{
	function styles($parent, $pacl = 0)
	{
		$this->tpl_init("styles");
		$this->db_init();
			
		$this->parent = $parent;
		$this->back = $back;
		$this->vars(array("parent" => $parent));
		$this->acl = new acl;
		if (is_object($pacl))
			$this->pacl = $pacl;
		else
			$this->pacl = 0;
	}
		
	// generates a list of all the parents of $parent, so we know to include all the styles
	function gen_parent_arr($parent, $skipfirst=true)
	{
		if ($skipfirst)
		{
			$this->db_query("SELECT parent FROM objects where oid = $parent");
			$row = $this->db_next();
			$parent = $row["parent"];
		}
		$first = true;
		while ($parent != 0)	// loop through all categories, until we reach the top
		{
			if ($first)
				$ret = " ( parent = $parent ";
			else
				$ret.= " OR parent = $parent ";

			$this->db_query("SELECT parent FROM objects where oid = $parent");
			$row = $this->db_next();
			$parent = $row["parent"];
			$first = false;
		}
		$ret.=" ) ";
		return $ret;
	}

	function get_parent_acl()
	{
		if (!is_object($this->pacl))
		{
			$this->pacl = new acl;
			$this->pacl->query_parent($this->parent);
		}
		return $this->pacl;
	}

	function gen_list($tpl="style_list.tpl")
	{
		global $back;
		$this->read_template($tpl);
		
		$c="";
		$p = $this->gen_parent_arr($this->parent, false);
		$tacl = new acl;
		$this->db_query("SELECT styles.*, objects.name, ".$tacl->sql()."
										 FROM styles 
										 LEFT JOIN objects ON objects.oid = styles.id
										 LEFT JOIN acl ON acl.oid = objects.oid
										 WHERE $p AND objects.status != 0
										 GROUP BY objects.oid");
		while ($row = $this->db_next())
		{

			$this->vars(array("style_name" => $row["name"], "style_id" => $row["id"]));
			$cc = "";
			$cc = $this->parse("CAN_CHANGE");
			$cd = "";
			$cd = $this->parse("CAN_DELETE");
			$ce = "";
			$ce = $this->parse("CAN_EXPORT");
			$this->vars(array("CAN_CHANGE"=>$cc,"CAN_DELETE"=>$cd,"CAN_EXPORT"=>$ce));
			$c.=$this->parse("LINE");
		}
		$ca = ""; $ci = "";$ceb="";
		$ca = $this->parse("CAN_ADD");

		$ci = $this->parse("CAN_IMPORT");

		$ceb = $this->parse("CAN_EXPORT_B");

		$this->vars(array("LINE" => $c, "CAN_ADD"=>$ca, "CAN_IMPORT" => $ci,"CAN_EXPORT_B"=>$ceb));
		return $this->parse();
	}
		
	function add_style($tpl="add_style.tpl")
	{
		$pacl = $this->get_parent_acl();

		$this->read_template($tpl);
		$fz="";
		for ($i = -5; $i < 6; $i++)
		{
			$this->vars(array("fontsize_value" => $i, "fontsize_selected" => ($i == 2 ? " SELECTED " : "")));
			$fz.=$this->parse("FONTSIZE");
		}
		$this->vars(array(
					"parent" 						=> $this->parent,
					"FONTSIZE" 					=> $fz,
			));
		return $this->parse();
	}
		
	function change_style($id, $tpl = "add_style.tpl")
	{
		$pacl = $this->get_parent_acl();

		global $back;
		$this->read_template($tpl);
		$this->db_query("SELECT styles.*, objects.name ,sql 
										 FROM styles 
										 LEFT JOIN objects ON objects.oid = styles.id
										 LEFT JOIN acl ON acl.oid = objects.oid
										 WHERE styles.id = $id
										 GROUP BY objects.oid");
		if (!($r = $this->db_next()))
			$this->raise_error("NO SUCH STYLE id = $id", true);

			$sa = unserialize($r["style"]);

			$fz="";
			for ($i = -5; $i < 6; $i++)
			{
				$this->vars(array("fontsize_value" => $i, "fontsize_selected" => ($sa["fontsize"] == $i ? " SELECTED " : "")));
				$fz.=$this->parse("FONTSIZE");
			}
			
			$this->vars(array("style_name" 	=> $r[name], 			
									"font1_sel_arial" 	=> ($sa["font1"] == "arial" ? " SELECTED " : ""),
									"font1_sel_times"		=> ($sa["font1"] == "times" ? " SELECTED " : ""),
									"font1_sel_verdana"	=> ($sa["font1"] == "verdana" ? " SELECTED " : ""),
									"font1_sel_tahoma"	=> ($sa["font1"] == "tahoma" ? " SELECTED " : ""),
									"font1_sel_geneva"	=> ($sa["font1"] == "geneva" ? " SELECTED " : ""),
									"font1_sel_helvetica"	=> ($sa["font1"] == "helvetica" ? " SELECTED " : ""),
									"font2_sel_arial" 	=> ($sa["font2"] == "arial" ? " SELECTED " : ""),
									"font2_sel_times"		=> ($sa["font2"] == "times" ? " SELECTED " : ""),
									"font2_sel_verdana"	=> ($sa["font2"] == "verdana" ? " SELECTED " : ""),
									"font2_sel_tahoma"	=> ($sa["font2"] == "tahoma" ? " SELECTED " : ""),
									"font2_sel_geneva"	=> ($sa["font2"] == "geneva" ? " SELECTED " : ""),
									"font2_sel_helvetica"	=> ($sa["font2"] == "helvetica" ? " SELECTED " : ""),
									"font3_sel_arial" 	=> ($sa["font3"] == "arial" ? " SELECTED " : ""),
									"font3_sel_times"		=> ($sa["font3"] == "times" ? " SELECTED " : ""),
									"font3_sel_verdana"	=> ($sa["font3"] == "verdana" ? " SELECTED " : ""),
									"font3_sel_tahoma"	=> ($sa["font3"] == "tahoma" ? " SELECTED " : ""),
									"font3_sel_geneva"	=> ($sa["font3"] == "geneva" ? " SELECTED " : ""),
									"font3_sel_helvetica"	=> ($sa["font3"] == "helvetica" ? " SELECTED " : ""),
									"FONTSIZE"					=> $fz,
									"font_colour"				=> $sa["colour"] ,
									"bgcolour" 					=> $sa["bgcolour"], 
									"font_style_bold_selected" 			=> ($sa["style"] == "bold" ? " SELECTED " : ""),
									"font_style_italic_selected" 		=> ($sa["style"] == "italic" ? " SELECTED " : ""),
									"font_style_underline_selected" => ($sa["style"] == "underline" ? " SELECTED " : ""),
									"font_style_normal_selected" 		=> ($sa["style"] == "normal" ? " SELECTED " : ""),
									"style_id" 					=> $r["id"],
									"parent"						=> $this->parent,
									"align_left"				=> ($sa["align"] == "left" ? " CHECKED " : ""),
									"align_center"			=> ($sa["align"] == "center" ? " CHECKED " : ""),
									"align_right"				=> ($sa["align"] == "right" ? " CHECKED " : ""),
									"valign_top"				=> ($sa["valign"] == "top" ? " CHECKED " : ""),
									"valign_center"			=> ($sa["valign"] == "center" ? " CHECKED " : ""),
									"valign_bottom"			=> ($sa["valign"] == "bottom" ? " CHECKED " : ""),
									"height"						=> $sa["height"],
									"width"							=> $sa["width"],
									"nowrap_checked"		=> ($sa["nowrap"] == 1 ? " CHECKED " : "" )));

			return $this->parse();
		}
		
		function admin_style($arr)
		{
			$this->quote(&$arr);
			extract($arr);
			
			$st = array("font1"			=> $font1, 
									"font2"			=> $font2, 
									"font3"			=> $font3, 
									"fontsize"	=> $fontsize, 
									"colour"		=> $colour, 
									"bgcolour"	=> $bgcolour, 
									"style"			=> $font_style,
									"align"			=> $align,
									"valign"		=> $valign,
									"height"		=> $height,
									"width"			=> $width,
									"nowrap"		=> $nowrap);

			$c = serialize($st);
			
			if ($id)
			{
				$this->upd_object(array("oid" => $id,"name" => $name));
				$this->db_query("UPDATE styles SET style='$c' WHERE id = $id");
				$this->_log("style", "Muutis stiili $name");
			}
			else
			{
				$id = $this->new_object(array("parent" => $this->parent, "name" => $name,"class_id" => CL_STYLE));
				$this->db_query("INSERT INTO styles VALUES($id, '$c')");
				$this->_log("style", "Lisas stiili $name");
			}
			return $id;
		}
		
		function delete_style($id)
		{
			$this->delete_object($id);
		}

		function export($arr)
		{

			header("Content-type: text/aw-style");
			$p = $this->gen_parent_arr($this->parent, false);
			$this->db_query("SELECT styles.*, objects.name,
												".$pacl->sql()."
											 FROM styles 
											 LEFT JOIN objects ON objects.oid = styles.id
											 LEFT JOIN acl ON acl.oid = objects.oid
											 WHERE $p AND objects.status != 0
											 GROUP BY objects.oid");
			while ($row = $this->db_next())
			{
				$var = "style_".$row["id"];
				global $$var;
				if ($$var == 1)
					echo $row["id"],",",$row["name"],",",$row["style"], "\n";
			}
			$this->_log("style", "Eksportis stiile");
			die();
		}

		function import($arr)
		{
			extract($arr);

			global $file;
			$ar = file($file);
			reset($ar);
			while (list(,$line) = each($ar))
			{
				$idx = split(",",$line);
				$this->quote(&$idx[1]);
				$id = $this->new_object(array("parent" => $this->parent, "name" => $idx[1],"class_id" => CL_STYLE));
				$this->db_query("INSERT INTO styles VALUES($id,'".$idx[2]."')");
			}
			$this->_log("style", "Importis stiile");
		}

		function mk_style_vars($sr)
		{
			global $clipboard;
			if ($sr[id] == $clipboard["id"])		// if this is the selected style
				$this->found = true;

			$s = unserialize($sr["style"]);

			$flookup = array("" => 0, "arial" => 1, "times" => 2, "verdana" => 3, "tahoma" => 4, "geneva" => 5, "helvetica" =>6);
			$f1 = ($f1=$flookup[$s["font1"]]) == "" ? 0 : $f1;
			$f2 = ($f2=$flookup[$s["font2"]]) == "" ? 0 : $f2;
			$f3 = ($f3=$flookup[$s["font3"]]) == "" ? 0 : $f3;

			$slookup = array("normal" => 0, "bold" => 1, "italic" => 2, "underline" => 3);
			$fs = ($fs=$slookup[$s["style"]]) == "" ? 0 : $fs;

			$this->vars(array("style_item_active"		=> ($clipboard["id"] == $sr["id"] ? " SELECTED " : "" ),
												"style_item_value"		=> $sr["id"],
												"style_item_text"			=> $sr["name"],
												"style_name"					=> ($sr["name"] == "" ? "''" : "'".$sr["name"]."'"),
												"style_font1"					=> $f1,
												"style_font2"					=> $f2,
												"style_font3"					=> $f3,
												"style_fontsize"			=> $s["fontsize"]+5,		// need algavad -5'st
												"style_color"					=> ($s["colour"] == "" ? "''" : "'".$s["colour"]."'"),
												"style_bgcolor"				=> ($s["bgcolour"] == "" ? "''" : "'".$s["bgcolour"]."'"),
												"style_fstyle"				=> $fs,
												"style_align_left"		=> ($s["align"] == "left" ? 1 : 0),
												"style_align_center"	=> ($s["align"] == "center" ? 1 : 0),
												"style_align_right"		=> ($s["align"] == "right" ? 1 : 0),
												"style_valign_top"		=> ($s["valign"] == "top" ? 1 : 0),
												"style_valign_center"	=> ($s["valign"] == "center" ? 1 : 0),
												"style_valign_bottom"	=> ($s["valign"] == "bottom" ? 1 : 0),
												"style_valign"				=> ($s["valign"] == "" ? "''" : "'".$s["valign"]."'"),
												"style_height"				=> ($s["height"] == "" ? "''" : "'".$s["height"]."'"),
												"style_width"					=> ($s["width"] == "" ? "''" : "'".$s["width"]."'"),
												"style_id"						=> $sr["id"],
												"style_nowrap"				=> ($s["nowrap"] == 1 ? 1 : 0)));
		}

		function select_style()
		{
			global $clipboard;
			$this->read_template("admin_style.tpl");

			$this->found=false;
			$sd = "";
			$p = $this->gen_parent_arr($this->parent);
			$tacl = new acl;
			$pacl = $this->get_parent_acl();
			$this->db_query("SELECT styles.style as style, objects.name as name, styles.id as id, objects.parent as parent,sql 
											 FROM styles 
											 LEFT JOIN objects ON objects.oid = styles.id
											 LEFT JOIN acl ON acl.oid = objects.oid
											 WHERE $p AND objects.status != 0
											 GROUP BY objects.oid");
			while ($sr = $this->db_next())
			{
				$this->mk_style_vars($sr);

				$str.=$this->parse("STYLE_LIST");
				$sd.=$this->parse("STYLE_DEFS");
			}

			if ($clipboard["id"] != 0 && $this->found == true)		// if a style is selected and it was also found , then it is a predefined style
			{																										// and thus we must add an empty style
				$this->mk_style_vars(array());
				$str =$this->parse("STYLE_LIST").$str;
				$sd.=$this->parse("STYLE_DEFS"); // avoid those nasty-ass javascript errors
			}
			else
			if ($this->found == false && $clipboard["id"] != 0)	// if a styles was not found, but one is selected
			{																									// then it's a temporary style and we must load it
				$this->db_query("SELECT styles.style as style, objects.name as name, styles.id as id, objects.parent as parent
											 FROM styles 
											 LEFT JOIN objects ON objects.oid = styles.id
											 WHERE styles.id = ".$clipboard[id]);
				$st = $this->db_next();
				$this->mk_style_vars($st);
				$str =$this->parse("STYLE_LIST").$str;
				$sd.=$this->parse("STYLE_DEFS");
			}
			else
			if ($this->found == false && $clipboard[id] == 0)	// no style was selected and thus we didn't dind any 
			{																									// so we add an empty entry
				$this->mk_style_vars(array());
				$str =$this->parse("STYLE_LIST").$str;
				$sd.=$this->parse("STYLE_DEFS");
			}
			// and since we know, that ignorance is bliss, we ignore the erroneous situation, where we found a style, but the clipboard is empty... ;)
			
			$fz="";
			for ($i = -5; $i < 6; $i++)
			{
				$this->vars(array("fontsize_value" => $i));
				$fz.=$this->parse("FONTSIZE");
			}

			$this->vars(array("form_col"	=> $this->col,
												"form_row"	=> $this->row,
												"STYLE_LIST"	=> $str,
												"STYLE_DEFS"	=> $sd,
												"FONTSIZE"		=> $fz));
			return $this->parse();
		}

		function submit_select(&$arr)
		{
			global $clipboard;
			$this->quote(&$arr);
			extract($arr);

			$st+=0;

			// right. here we must detect if the user selected a predefined style and a temp style was previously selected, because
			// if we did no such thing, then we would have a lot of temp styles laying around soon, because they are not deleted anywhere
			// and when the user defines different attributes a new temp style is always created.
			if ($id != $clipboard["id"] && $id != 0 && $clipboard["id"] != 0)	// if a new style is selected and the previous style was not empty
			{
				$this->db_query("SELECT styles.style as style, objects.name as name, styles.id as id, objects.parent as parent
											 FROM styles 
											 LEFT JOIN objects ON objects.oid = styles.id
											 WHERE styles.id = ".$clipboard["id"]);
				$str = $this->db_next();	// get the previous style

				if ($str["parent"] == $this->parent)	// and if it's a temp style
				{
					$this->db_query("DELETE FROM objects where oid = ".$clipboard["id"]);	// delete it
					$this->db_query("DELETE FROM styles where id = ".$clipboard["id"]);
				}
			}
			
			$t_id = $this->admin_style($arr);

			// ok, this is the sucky part right here. really bad object interdependencies. but I'm tired, so who the fuck cares anyway
			global $back, $classdir, $ext;
			if(strstr($back, "form")!="")
			{
				// assign to form cell
				parse_str($back);
				include("$classdir/form_cell.$ext");
				include("$classdir/form_element.$ext");
				include("$classdir/form_entry_element.$ext");
				include("$classdir/form_search_element.$ext");
				$f = new form_cell($f_id, $row, $col);
				$f->set_style($t_id);
			}
			else
			{
				;// assign to table
			}

			$clipboard["id"] = $t_id;
			$clipboard["type"] = "sel_style";
		}

		function load_style($id)
		{
			$this->db_query("SELECT * FROM styles WHERE id = $id");
			if (!($row = $this->db_next()))
				$this->raise_error("styles->load_style($id) , no such style!", true);

			$this->arr = unserialize($row["style"]);
		}

		function get($name)
		{
			return $this->arr[$name];
		}

		function get_font_begin()
		{
			switch($this->arr[style])
			{
				case "bold":
					$stb = "<b>";
					break;
				case "italic":
					$stb = "<i>";
					break;
				case "underline":
					$stb = "<u>";
					break;
				default:
					$stb = "";
					break;
			};
	
			if ($this->arr["font1"] == "" && $this->arr["font2"] == "" && $this->arr["font3"] == "" && $this->arr["fontsize"] == "" && $this->arr["colour"] == "")
				$fnt = "";
			else
			{
				$fnt = "<font";
				$ffac = "";
				if ($this->arr["font1"] != "")
					$ffac.=$this->arr["font1"];

				if ($this->arr["font2"] != "")
					$ffac = ($ffac == "" ? $this->arr["font2"] : $ffac.",".$this->arr["font2"]);

				if ($this->arr["font3"] != "")
					$ffac = ($ffac == "" ? $this->arr["font3"] : $ffac.",".$this->arr["font3"]);

				if ($ffac != "")
					$fnt.=" face='".$ffac."'";

				if ($this->arr["fontsize"] != "")
					$fnt.=" size='".$this->arr["fontsize"]."'";

				if ($this->arr["colour"] != "")
					$fnt.=" color='".$this->arr["colour"]."'";

				$fnt.=">";
			}

			return $fnt.$stb;
		}

		function get_font_end()
		{
			switch($this->arr["style"])
			{
				case "bold":
					$ste = "</b>";
					break;
				case "italic":
					$ste = "</i>";
					break;
				case "underline":
					$ste = "</u>";
					break;
				default:
					$ste = "";
					break;
			};

			return $ste."</font>";
		}

	};
?>
