<?php

/** aw code analyzer viewer

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_viewer.aw,v 1.4 2004/03/24 11:00:20 kristo Exp $

	@comment 
		displays the data that the docgen analyzer generates
**/

class docgen_viewer extends class_base
{
	function docgen_viewer()
	{
		$this->init("core/docgen");
	}

	/**  
		
		@attrib name=class_list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function class_list()
	{
		$p = get_instance("parser");
		$files = array();
		$p->_get_class_list(&$files, $this->cfg["classdir"]);
		
		sort($files);
		foreach($files as $file)
		{
			$file = str_replace($this->cfg["basedir"]."/classes", "", $file);
			$f .= html::href(array(
				"url" => $this->mk_my_orb("class_info", array("file" => $file)),
				"caption" => $file,
				"target" => "classinfo"
			))."<Br>";
		}

		die("<style type=\"text/css\">
.text {
font-family:  Verdana, Arial, sans-serif;
font-size: 11px;
color: #000000;
line-height: 18px;
text-decoration: none;
}
.text a {color: #058AC1; text-decoration:underline;}
.text a:hover {color: #000000; text-decoration:underline;}
</style><span class=\"text\">".$f."</span>");
	}

	/**  
		
		@attrib name=frames params=name default="1"
		
		@param aa define value="100"
		
		@returns
		
		
		@comment

	**/
	function frameset()
	{
		$this->read_template("frameset.tpl");

		$this->vars(array(
			"left" => $this->mk_my_orb("class_list"),
			"right" => "about:blank"
		));
		die($this->parse());
	}

	function display_class($data, $cur_file)
	{
		$this->read_template("class_info.tpl");

		$f = "";
		foreach($data["functions"] as $func => $f_data)
		{
			$arg = "";

			$_ar = new aw_array($f_data["arguments"]);
			foreach($_ar->get() as $a_var => $a_data)
			{
				$this->vars(array(
					"arg_name" => $a_data["name"],
					"def_val" => $a_data["default_val"],
					"is_ref" => ($a_data["is_ref"] ? "X" : "")
				));

				$arg .= $this->parse("ARG");
			}

			$this->vars(array(
				"proto" => "function $func()",
				"name" => $func,
				"view_func" => aw_global_get("REQUEST_URI")."#fn.$func",
				"start_line" => $f_data["start_line"],
				"end_line" => $f_data["end_line"],
				"returns_ref" => ($f_data["returns_ref"] ? "X" : "&nbsp;"),
				"ARG" => $arg,
				"short_comment" => ($f_data["doc_comment"]["short_comment"] == "" ? "" : $f_data["doc_comment"]["short_comment"]."<Br>"),
				"doc_comment" => htmlspecialchars($f_data["doc_comment_str"]),
				"view_source" => $this->mk_my_orb("view_source", array("file" => $cur_file, "v_class" => $data["name"],"func" => $func))
			));
			$f .= $this->parse("FUNCTION");
			$fl .= $this->parse("LONG_FUNCTION");
		}

		if ($data["extends"] != "")
		{
			$this->_display_extends($data);
		}

		if (is_array($data["dependencies"]))
		{
			$this->_display_dependencies($data["dependencies"]);
		}

		// do properties
		$clid = $this->_find_clid_for_name($data["name"]);
		if ($clid)
		{
			$this->_display_properties($clid, $data);
		}

		$this->vars(array(
			"name" => $data["name"],
			"extends" => $data["extends"],
			"end_line" => $data["end_line"],
			"start_line" => $data["start_line"],
			"FUNCTION" => $f,
			"LONG_FUNCTION" => $fl,
		));

		return $this->parse();
	}

	/** displays information to the user about a class

		@attrib params=name nologin=0 is_public=0 all_args=0 caption="N&auml;ita klassi infot" default=0 name=class_info

		@param file required 

		@returns 
		html with class info

		@comment
		shows detailed info about a class
	**/
	function class_info($arr)
	{
		extract($arr);

		$da = get_instance("core/docgen/docgen_analyzer");
		$data = $da->analyze_file($file);
echo dbg::dump($data);
		foreach($data["classes"] as $class => $c_data)
		{
			$op .= $this->display_class($c_data, $file);
		}

		return $op;
	}

	/**
		@attrib name=search_method
		@param method required 
	**/
	function search_method($arr)
	{
		set_time_limit(0);
		$method = $arr["method"];
		$p = get_instance("parser");
		$files = array();
		$p->_get_class_list(&$files, $this->cfg["classdir"]);
		
		sort($files);
		$found = 0;
		foreach($files as $file)
		{
			$da = get_instance("core/docgen/docgen_analyzer");
			$fdat = $da->analyze_file($file,true);
			$bn = basename($file,".aw");
			$check = $fdat["classes"][$bn]["functions"][$method];
			if ($check)
			{
				print "fl = $file<br>";
				$start = $check["start_line"];
				$offset = $check["end_line"] - $start;
				$fc = join("",array_slice(file($file),$start-1,$offset+1));
				$fc = "<" . "?\n" . $fc . "\n" . "?" . ">"; 
				print "<pre>";
				print highlight_string($fc,true);
				//print_r($fdat["classes"][$bn]["functions"][$method]);
				print "</pre>";
				$found++;
			};



		}
		print "Found $found instances<br>";
	}

	function _find_clid_for_name($name)
	{
		if ($name == "doc")
		{
			return CL_DOCUMENT;
		}
		foreach($this->cfg["classes"] as $clid => $cld)
		{
			if (basename($cld["file"]) == basename($name))
			{
				return $clid;
			}
		}
	}

	function _get_clid_names($ar)
	{
		$ara = array();
		foreach($ar as $clid)
		{
			$ara[] = basename($this->cfg["classes"][$clid]["file"]);
		}

		return join(",", $ara);
	}

	/** displays function source 

		@attrib name=view_source

		@param file required
		@param v_class required
		@param func required

	**/
	function view_source($arr)
	{
		extract($arr);
		
		$da = get_instance("core/docgen/docgen_analyzer");
		$data = $da->analyze_file($file);

		$start_line = $data["classes"][$v_class]["functions"][$func]["start_line"];
		$end_line = $data["classes"][$v_class]["functions"][$func]["end_line"];
		
		$fd = file($this->cfg["basedir"]."/classes".$file);
		$line = 1;
		$str = "<?php\n";
		foreach($fd as $l)
		{
			if ($line >= $start_line && $line <= $end_line)
			{
				$str .= $l;
			}
			$line++;
		}
		$str .= "?>";

		return highlight_string($str,true);
	}

	function _display_dependencies($dependencies)
	{
		// build nice dep array
		$dep = array();
		$has_var = false;
		foreach($dependencies as $d_dat)
		{
			if ($d_dat["is_var"])
			{
				$has_var = true;
			}
			else
			{
				$dep[$d_dat["dep"]]["lines"][] = $d_dat["line"];
			}
		}

		$d_str = "";
		$d_str_var = "";
		if ($has_var)
		{
			$d_str_var = $this->parse("VAR_DEP");
		}

		foreach($dep as $d_class => $d_ar)
		{
			$this->vars(array(
				"name" => $d_class,
				"lines" => join(",", $d_ar["lines"]),
				"link" => $this->mk_my_orb("class_info" , array("file" => "/".$d_class.".".$this->cfg["ext"]))
			));
			$d_str .= $this->parse("DEP");
		}

		$this->vars(array(
			"DEP" => $d_str,
			"VAR_DEP" => $d_str_var
		));
	}

	function _display_properties($clid, $data)
	{
		$cln = $data["name"];
		if ($cln == "document" || $cln == "document_brother")
		{
			$cln = "doc";
		}
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
			"file" => $cln,
			"clid" => $clid
		));

		$p2t = array();
		foreach($props as $prop)
		{
			$this->vars(array(
				"name" => $prop["name"],
				"type" => $prop["type"],
				"comment" => $prop["caption"]
			));
			$p_tbl .= $this->parse("PROP");

			$p2t[$prop["table"]][] = $prop["name"];
		}

		$ri = $cfgu->get_relinfo();
		$i_ri = array();
		foreach($ri as $ri_v => $ri_d)
		{
			if (substr($ri_v, 0, strlen("RELTYPE")) == "RELTYPE")
			{
				$i_ri[$ri_d["value"]]["name"] = $ri_v;
			}

			if (!isset($i_ri[$ri_d["value"]]))
			{
				$i_ri[$ri_d["value"]] = $ri_d;
			}
		}

		$s_ri = "";
		foreach($i_ri as $ri_vl => $ri_d)
		{
			$this->vars(array(
				"name" => $ri_d["name"],
				"clids" => $this->_get_clid_names($ri_d["clid"]),
				"comment" => $ri_d["caption"]
			));

			$s_ri .= $this->parse("RELTYPE");
		}

		$t_str = "";
		$awt = new aw_array($cfgu->tableinfo);
		foreach($awt->get() as $tb => $tbd)
		{
			$this->vars(array(
				"name" => $tb,
				"index" => $tbd["index"],
				"properties" => join(", ", $p2t[$tb])
			));
			$t_str .= $this->parse("TABLE");
		}

		$this->vars(array(
			"PROP" => $p_tbl,
			"RELTYPE" => $s_ri,
			"TABLE" => $t_str
		));
	}

	function _display_extends($dat)
	{
		$orb = get_instance("orb");
		$that = get_instance("core/docgen/docgen_analyzer");

		// now, do extended classes. we do that by parsing all the extends classes
		// which of course slows us to hell and beyond. these parses should be cached or something
		do {
			$level++;

			if ($dat["extends"] == "db_connector")
			{
				$_extends = "db";
			}
			else
			{
				$_extends = $dat["extends"];
			}

			// get the file the class is in.
			// for that we have to load it's orb defs to get the folder below the classes folder
			$orb_defs = $orb->load_xml_orb_def($_extends);
			$ex_fname = $this->cfg["basedir"]."/classes/".$orb_defs[$dat["extends"]]["___folder"]."/".$_extends.".".$this->cfg["ext"];

			$this->vars(array(
				"spacer" => str_repeat("&nbsp;", $level * 3),
				"inh_link" => $this->mk_my_orb("class_info", array("file" => "/".$_extends.".".$this->cfg["ext"])),
				"inh_name" => $dat["extends"]
			));
			$ex .= $this->parse("EXTENDER");

			$_dat = $that->analyze_file($ex_fname, true);
			$dat = $_dat["classes"][$dat["extends"]];
		} while ($dat["extends"] != "");

		$this->vars(array(
			"EXTENDER" => $ex,
		));
	}
}
?>
