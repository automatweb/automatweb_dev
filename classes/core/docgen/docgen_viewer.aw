<?php

/** aw code analyzer viewer

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_viewer.aw,v 1.10 2004/05/26 22:19:43 kristo Exp $

	@comment 
		displays the data that the docgen analyzer generates
**/

/*

@classinfo no_status=1 no_comment=1 relationmgr=yes

@default group=general 

@property foorum type=relpicker reltype=RELTYPE_FORUM field=meta method=serialize table=objects

@property view type=text store=no 

@reltype FORUM value=1 clid=CL_FORUM_V2
@caption foorum

*/
class docgen_viewer extends class_base
{
	function docgen_viewer()
	{
		$this->init(array(
			"tpldir" => "core/docgen",
			"clid" => CL_AW_DOCGEN_VIEWER
		));
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "view":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("frames", array("id" => $arr["obj_inst"]->id())),
					"caption" => "Open DocGen"
				));
				break;
		}
		return PROP_OK;
	}

	/**  
		
		@attrib name=class_list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function class_list()
	{
		$this->read_template("classlist.tpl");

		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgclsss",
			"persist_state" => true,
			"root_name" => "Classes",
			"url_target" => "list"
		));

		$this->ic = get_instance("icons");
		$this->_req_mk_clf_tree($tv, $this->cfg["classdir"]);

		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => $this->cfg["classdir"],
			))
		));

		return $this->parse();
	}

	function _req_mk_clf_tree(&$tv, $path)
	{
		$dc = array();
		$fc = array();
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false)
		{
			$fp = $path."/".$file;
			if ($file != "." && $file != ".." && $file != "CVS" && substr($file, 0,2) != ".#")
			{
				if (is_dir($fp))
				{
					$dc[] = $file;
				}
				else
				{
					$fc[] = $file;
				}
			}
		}
		closedir($dh);

		sort($dc);
		sort($fc);

		foreach($dc as $file)
		{
			$fp = $path."/".$file;
			$tv->add_item($path, array(
				"name" => $file,
				"id" => $fp,
				"url" => "#",
			));
			$this->_req_mk_clf_tree($tv, $fp);
		}
		foreach($fc as $file)
		{
			$fp = $path."/".$file;
			$tv->add_item($path, array(
				"name" => $file,
				"id" => $fp,
				"url" => $this->mk_my_orb("class_info", array("file" => str_replace($this->cfg["classdir"], "", $fp))),
				"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
				"target" => "classinfo"
			));
		}
	}

	/**  
		
		@attrib name=frames params=name default="1"
		
		@param id optional type=int 
		
		@returns
		
		
		@comment

	**/
	function frameset($arr)
	{
		$this->read_template("frameset.tpl");

		$this->vars(array(
			"left" => $this->mk_my_orb("class_list"),
			"right" => "about:blank",
			"doclist" => $this->mk_my_orb("doclist"),
			"topf" => $this->mk_my_orb("topf", array("id" => $arr["id"]))
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

			$doc_file = dirname($cur_file)."/".basename($cur_file, ".aw")."/".$data["name"].".".$func.".txt";
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
				"view_source" => $this->mk_my_orb("view_source", array("file" => $cur_file, "v_class" => $data["name"],"func" => $func)),
				"view_usage" => $this->mk_my_orb("view_usage", array("file" => $cur_file, "v_class" => $data["name"],"func" => $func)),
				"doc" => $this->show_doc(array("file" => $doc_file))
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

		$str = $this->parse();
		return $str;
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
		foreach($data["classes"] as $class => $c_data)
		{
			if ($class != "")
			{
				$op .= $this->display_class($c_data, $file);
			}
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
		foreach(aw_ini_get("classes") as $clid => $cld)
		{
			if (basename($cld["file"]) == basename($name))
			{
				return $clid;
			}
		}
	}

	function _get_clid_names($ar)
	{
		$tmp = aw_ini_get("classes");
		$ara = array();
		foreach($ar as $clid)
		{
			$ara[] = basename($tmp[$clid]["file"]);
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

	/**

		@attrib name=doclist

		@param type optional default="classes"

	**/
	function doclist($arr)
	{
		extract($arr);
		$this->read_template("doclist.tpl");

		/*if ($type == "classes")
		{
			$list = $this->do_class_doclist();
		}
		else
		{*/
			$list = $this->do_tut_doclist();
		//}

		$this->vars(array(
			"classdoc" => $this->mk_my_orb("doclist", array("type" => "classes")),
			"tutorials" => $this->mk_my_orb("doclist", array("type" => "tutorials")),
			"list" => $list
		));

		return $this->parse();
	}

	function do_class_doclist()
	{
		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgdoclss",
			"persist_state" => true,
			"root_name" => "Classes",
			"url_target" => "list"
		));

		$this->basedir = $this->cfg["basedir"]."/docs/classes";
		$this->ic = get_instance("icons");
		$this->_req_mk_clfdoc_tree($tv, $this->basedir);

		return $tv->finalize_tree(array(
			"rootnode" => $this->basedir,
		));
	}

	function _req_mk_clfdoc_tree(&$tv, $path)
	{
		$dc = array();
		$fc = array();
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false)
		{
			$fp = $path."/".$file;
			if ($file != "." && $file != ".." && $file != "CVS" && substr($file, 0,2) != ".#")
			{
				if (is_dir($fp))
				{
					$dc[] = $file;
				}
				else
				{
					$fc[] = $file;
				}
			}
		}
		closedir($dh);

		sort($dc);
		sort($fc);

		foreach($dc as $file)
		{
			$fp = $path."/".$file;
			$tv->add_item($path, array(
				"name" => $file,
				"id" => $fp,
				"url" => "#",
			));
			$this->_req_mk_clfdoc_tree($tv, $fp);
		}
		foreach($fc as $file)
		{
			$fp = $path."/".$file;
			$tv->add_item($path, array(
				"name" => $file,
				"id" => $fp,
				"url" => $this->mk_my_orb("show_doc", array("file" => str_replace($this->basedir, "", $fp))),
				"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
				"target" => "classinfo"
			));
		}
	}

	function do_tut_doclist()
	{
		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgdoclss",
			"persist_state" => true,
			"root_name" => "Classes",
			"url_target" => "list"
		));

		$this->basedir = $this->cfg["basedir"]."/docs/tutorials";
		$this->ic = get_instance("icons");
		$this->_req_mk_clfdoc_tree($tv, $this->basedir);

		return $tv->finalize_tree(array(
			"rootnode" => $this->basedir,
		));
	}

	/** displays the documentation file $file

		@attrib name=show_doc

		@param file required

	**/
	function show_doc($arr)
	{
		extract($arr);
		$file = preg_replace("/(\.){2,}/", "", $file);
		if (file_exists($this->cfg["basedir"]."/docs/classes".$file))
		{
			$fp = $this->cfg["basedir"]."/docs/classes".$file;
		}
		else
		{
			$fp = $this->cfg["basedir"]."/docs/tutorials".$file;
		}
		$str = $this->get_file(array(
			"file" => $fp
		));

		$str = preg_replace("/(#code#)(.+?)(#\/code#)/esm","\"<pre>\".htmlspecialchars(stripslashes('\$2')).\"</pre>\"",$str);
		$str = preg_replace("/(#php#)(.+?)(#\/php#)/esm","highlight_string(stripslashes('<'.'?'.'\$2'.'?'.'>'),true)",$str);

		$tpl = get_instance("core/docgen/docgen_viewer");
		$tpl->read_template("style.tpl");
		$tpl->vars(array(
			"content" => nl2br($str)
		));
		return $tpl->parse();
	}

	/** updates the class/function definitions in the database

		@attrib name=do_db_update
	**/
	function do_db_update($arr)
	{
		extract($arr);
		$files = array();
		$p = get_instance("parser");
		$p->_get_class_list($files,$this->cfg["classdir"]);

		foreach($files as $file)
		{
			$da = get_instance("core/docgen/docgen_analyzer_simple_db_writer");
			$data = $da->analyze_file($file, true);
			foreach($data["classes"] as $class => $c_data)
			{
				$this->db_query("DELETE FROM aw_da_funcs WHERE class = '$class'");
				echo "writing class $class... <br>\n";
				flush();
				foreach($c_data["functions"] as $fname => $fdata)
				{
					//echo "&nbsp;&nbsp;&nbsp;writing function $fname... <br>\n";
					//flush();
					$this->db_query("INSERT INTO aw_da_funcs(class,func, ret_class) 
						values(
							'$class',
							'$fname',
							'".$fdata["return_var"]["class"]."'
						)
					");
				}
			}
		}

		$this->db_query("DELETE FROM aw_da_callers");
		foreach($files as $file)
		{
			$da = get_instance("core/docgen/docgen_analyzer");
			$data = $da->analyze_file($file, true);
			foreach($data["classes"] as $class => $c_data)
			{
				echo "writing class $class... <br>\n";
				flush();
				foreach($c_data["functions"] as $fname => $fdata)
				{
					$awa = new aw_array($fdata["local_calls"]);
					foreach($awa->get() as $calld)
					{
						$calld["class"] = basename($calld["class"]);
						$class = basename($class);
						$this->db_query("
							INSERT INTO 
								aw_da_callers(
									caller_class,			caller_func,			caller_line,
									callee_class,			callee_func
								) 
							values(
									'$class',				'$fname',				'".$calld["line"]."',
									'$class',				'".$calld["func"]."'
							)
						");
					}

					$awa = new aw_array($fdata["foreign_calls"]);
					foreach($awa->get() as $calld)
					{
						$calld["class"] = basename($calld["class"]);
						$class = basename($class);
						$this->db_query("
							INSERT INTO 
								aw_da_callers(
									caller_class,			caller_func,			caller_line,
									callee_class,			callee_func
								) 
							values(
									'$class',				'$fname',				'".$calld["line"]."',
									'".$calld["class"]."',				'".$calld["func"]."'
							)
						");
					}
				}
			}
		}
	}

	/** displays where the class::function is called from. wildly inaccurate at the moment.

		@attrib name=view_usage

		@param file required
		@param v_class required
		@param func required
	**/
	function view_usage($arr)
	{
		extract($arr);
		$this->read_template("view_usage.tpl");

		$l = "";

		$q = "SELECT * FROM aw_da_callers WHERE callee_class = '$v_class' AND callee_func = '$func'";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$tmp = aw_ini_get("classes");
			foreach($tmp as $tclass)
			{
				if (basename($tclass["file"]) == $row["caller_class"])
				{
					$cl_file = "/".$tclass["file"].".aw";
				}
			}
			$this->vars(array(
				"from_class" => $row["caller_class"],
				"from_func" => $row["caller_func"],
				"from_line" => $row["caller_line"],
				"link" => $this->mk_my_orb("class_info", array("file" => $cl_file))."#fn.".$row["caller_func"]
			));

			$l .= $this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $l,
			"class" => $v_class,
			"func" => $func
		));
		return $this->parse();
	}

	/** displays top frame 

		@attrib name=topf 

		@param id optional

	**/
	function topf($arr)
	{
		$ret = array();

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("class_list"),
			"target" => "classlist",
			"caption" => "K&otilde;ik klassid"
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("doc_class_list"),
			"target" => "classlist",
			"caption" => "Dokumenteeritud klassid"
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("doclist"),
			"target" => "classlist",
			"caption" => "Eraldi dokumentatsioon"
		));

		if ($arr["id"])
		{
			$o = obj($arr["id"]);
			$f_id = $o->prop("foorum");

			$ret[] = html::href(array(
				"url" => $this->mk_my_orb("change", array("id" => $f_id, "group" => "contents"), CL_FORUM_V2),
				"target" => "list",
				"caption" => "Foorum"
			));
		}


		$this->read_template("style.tpl");
		$this->vars(array(
			"content" => "&nbsp;&nbsp;".join(" | ", $ret)
		));
		return $this->parse();
	}

	/**

		@attrib name=doc_class_list

	**/
	function doc_class_list($arr)
	{
		$this->read_template("classlist.tpl");

		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgclsss",
			"persist_state" => true,
			"root_name" => "Classes",
			"url_target" => "list"
		));

		$this->ic = get_instance("icons");
		$this->_req_mk_clf_doc_tree($tv, $this->cfg["classdir"]);

		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => $this->cfg["classdir"],
			))
		));

		return $this->parse();
	}

	function _req_mk_clf_doc_tree(&$tv, $path)
	{
		$dc = array();
		$fc = array();
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false)
		{
			$fp = $path."/".$file;
			if ($file != "." && $file != ".." && $file != "CVS" && substr($file, 0,2) != ".#")
			{
				if (is_dir($fp))
				{
					$dc[] = $file;
				}
				else
				{
					$fc[] = $file;
				}
			}
		}
		closedir($dh);

		sort($dc);
		sort($fc);

		$hasf = false;
		foreach($dc as $file)
		{
			$fp = $path."/".$file;
			$_hasf = $this->_req_mk_clf_doc_tree($tv, $fp);

			if ($_hasf)
			{
				$tv->add_item($path, array(
					"name" => $file,
					"id" => $fp,
					"url" => "#",
				));
				$hasf = true;
			}
		}

		foreach($fc as $file)
		{
			$fp = $path."/".$file;
			$awpath = str_replace($this->cfg["classdir"], "", $fp);

			// check if documentation exists
			$doc_file = $this->cfg["basedir"]."/docs/classes/".dirname($awpath)."/".basename($awpath, ".aw")."/_has_docs";
			if (!file_exists($doc_file))
			{
				continue;
			}
			
			$tv->add_item($path, array(
				"name" => $file,
				"id" => $fp,
				"url" => $this->mk_my_orb("class_info", array("file" => $awpath)),
				"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
				"target" => "classinfo"
			));
			$hasf = true;
		}

		return $hasf;
	}
}
?>
