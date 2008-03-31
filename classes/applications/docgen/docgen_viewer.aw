<?php

/** aw code analyzer viewer

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_viewer.aw,v 1.20 2008/03/31 06:57:41 kristo Exp $

	@comment 
		displays the data that the docgen analyzer generates
**/

/*

@classinfo no_status=1 no_comment=1 relationmgr=yes syslog_type=ST_DOCGEN_VIEWER prop_cb=1 maintainer=kristo
@default table=objects
@default group=general 

@property foorum type=relpicker reltype=RELTYPE_FORUM field=meta method=serialize table=objects

@property view type=text store=no 

@groupinfo more_options caption="Seaded"
@default group=more_options

@property refresh_text type=text store=no value=Uuenda
@caption Uuenda

@property refresh_properties type=submit store=no value=Uuenda
@caption Uuenda


@default group=class_overview

	@layout ver_split type=hbox width=30%:70%

		@layout tree type=vbox closeable=1 area_caption=Klasside&nbsp;puu parent=ver_split 

			@property class_tree type=text parent=tree store=no no_caption=1

		@layout tbl type=hbox closeable=1 area_caption=Klassi&nbsp;info parent=ver_split 

			@property class_inf type=text store=no no_caption=1 parent=tbl

@groupinfo class_overview caption="Klasside &uuml;levaade" submit=no
@groupinfo all_classes caption="K&otilde;ik klassid" submit=no
@groupinfo api_classes caption="API Klassid" submit=no
@groupinfo tutorials caption="Eraldi dokumentatsioon" submit=no
@groupinfo cb_tags caption="Classbase tagid" submit=no
@groupinfo forum caption="Foorum" submit=no

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

		$this->cb_callbacks = array(
			"callback_on_load" => 1,
			"callback_pre_edit" => 1,
			"callback_get_add_txt" => 1,
			"callback_mod_layout" => 1,
			"callback_mod_reforb" => 1,
			"callback_generate_scripts" => 1,
			"callback_mod_retval" => 1,
			"callback_get_cfgform" => 1,
			"callback_gen_path" => 1,
			"callback_mod_tab" => 1,
			"get_property" => 1,
			"set_property" => 1,
			"callback_pre_save" => 1,
			"callback_post_save" => 1,
			"callback_get_cfgmanager" => 1,
			"callback_get_group_display" => 1,
			"callback_get_default_group" => 1
		);
	}

	function set_property($arr)
	{
		$prop = &$arr['prop'];
		
		switch($prop['name'])
		{
			case 'refresh_properties':
				$documenter = new aw_language_documenter;
				$documenter->parse_files($this->cfg['classdir']);
				$arr['obj_inst']->set_meta('properties_data',serialize($documenter));
				$arr['obj_inst']->save();
			break;
		}
		
		return PROP_OK;
	}

	function _get_class_tree($arr)
	{
		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgclsss",
			"persist_state" => true,
			"root_name" => t("Classes"),
			"url_target" => "list"
		));
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgclsss",
			"persist_state" => true,
			"root_name" => t("Classes"),
		));

		/* 	lihtsalt nii infi m6ttes - kui keegi hakkab seda puud siin feikima samasuguseks nagu on rohelise nupu puu
			siis juhtub kaks asja: 
				- see aptch reverditakse
				- ta j22b cvs commit accessist ilma

			kui tekib selline tahtmine, siis selleks tehke uus puu uude kohta. 

			- terryf.
		*/

		$this->ic = get_instance("core/icons");
		$this->_req_mk_clf_tree($tv, $this->cfg["classdir"]);

		$arr["prop"]["value"] = $tv->finalize_tree(array(
			"rootnode" => $this->cfg["classdir"],
		));

	}


	function _get_class_inf($arr)
	{
		$analyzer = get_instance("core/aw_code_analyzer/aw_code_analyzer");

		$data = $analyzer->analyze_file($arr["request"]["tf"]);
//die(dbg::dump($data));
		foreach($data["classes"] as $class => $class_data)
		{
			if ($class != "")
			{
				$op .= $this->display_class($class_data, $file, array(
					"api_only" => $api_only,
					"defines" => $data["defines"]
				));
			}
		}
		$arr["prop"]["value"] = $op;
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "view":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("frames", array("id" => $arr["obj_inst"]->id())),
					"caption" => t("Open DocGen")
				));
				break;

			case "class_tree":
				$this->_get_class_tree($arr);
				break;
		}
		return PROP_OK;
	}

	/**
		@attrib name=intro
	**/
	function intro($arr)
	{
		$this->read_template("intro.tpl");
		$fc = file(aw_ini_get("basedir")."/docs/tutorials/overview.txt");

		// parse out descriptions for classes
		foreach($fc as $line)
		{
			$line = trim($line);
			if ($line[0] == "*")
			{
				if ($desc != "")
				{
					$classes[$l_path][basename($l_class)] = $desc;
				}
				$desc = "";
				$l_class = trim(substr($line, 1, strlen($line)-2));
				$l_path = dirname($l_class);
			}
			else
			if ($line != "")
			{
				$desc .= $line."\n";
			}
		}

		ksort($classes);

		$fc = "";
		foreach($classes as $path_str => $path)
		{
			$fc .= "<div class='folder'>$path_str/</div><table border=0 width='100%' cellpadding=5 cellspacing=20>";
			foreach($path as $class => $desc)
			{
				$cl_url = $this->mk_my_orb("class_info", array("api_only" => 1, "file" => "/".$path_str."/".$class.".".aw_ini_get("ext")));
				$fc .= "<tr><td class='classdesc'><a href='$cl_url'><b>$class:</b></a><br>$desc</td></tr>";
			}
			$fc .="</table>";
		}

		$this->vars(array(
			"content" => nl2br($fc)
		));
		return $this->finish_with_style($this->parse());
	}

	/**  
		@attrib name=class_list params=name default="0"
	**/
	function class_list()
	{
		$this->read_template("classlist.tpl");

		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgclsss",
			"persist_state" => true,
			"root_name" => t("Classes"),
			"url_target" => "list"
		));

		/* 	lihtsalt nii infi m6ttes - kui keegi hakkab seda puud siin feikima samasuguseks nagu on rohelise nupu puu
			siis juhtub kaks asja: 
				- see aptch reverditakse
				- ta j22b cvs commit accessist ilma

			kui tekib selline tahtmine, siis selleks tehke uus puu uude kohta. 

			- terryf.
		*/

		// gather data about things in files
		$this->db_query("SELECT * from aw_da_classes");
		$classes = array();
		while ($row = $this->db_next())
		{
			$fp = $this->cfg["basedir"].$row["file"];
			$classes[$fp][] = $row;
		}


		$this->ic = get_instance("core/icons");
		$this->_req_mk_clf_tree($tv, $this->cfg["classdir"], $classes);

		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => $this->cfg["classdir"],
			))
		));

		return $this->finish_with_style($this->parse());
	}

	function _req_mk_clf_tree(&$tv, $path, $classes)
	{
		$dc = array();
		$fc = array();
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false)
		{
			$fp = $path."/".$file;
			if ($file != "." && $file != ".." && $file != "CVS" && substr($file, 0,2) != ".#" && substr($file, -1) != "~" && substr($file, -4) != "orig" && substr($file, -3) != "rej")
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
			$this->_req_mk_clf_tree($tv, $fp, $classes);
		}
		foreach($fc as $file)
		{
			$fp = $path."/".$file;
			$awpath = str_replace($this->cfg["classdir"], "", $fp);
			if ($_GET["group"] != "")
			{
				$url = aw_url_change_var("tf", str_replace($this->cfg["classdir"], "", $fp));
			}
			else
			{
				$url = $this->mk_my_orb("class_info", array("file" => str_replace($this->cfg["classdir"], "", $fp)));
			}
			// if the file only has 1 class in it, direct link to that, else split subs	
			if (count($classes[$fp]) < 2)
			{
				$tv->add_item($path, array(
					"name" => $file,
					"id" => $fp,
					"url" => $url,
					"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
					"target" => "classinfo"
				));
			}
			else
			{
				$tv->add_item($path, array(
					"name" => $file,
					"id" => $fp,
					"url" => $url,
					"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
					"target" => "classinfo"
				));
				foreach($classes[$fp] as $clinf)
				{
					$v = $clinf["class_name"];
					if ($v == "")
					{
						$clinf["class_name"] = "__outer";
						$v = t("Functions not in any class");
					}
					else
					{
						switch($clinf["class_type"])
						{
							case "interface":
								$v = t("Interface: ").$v;
								break;

							case "class":
								$v = t("Class: ").$v;
								break;

							case "exception":
								$v = t("Exception: ").$v;
								break;
						}
					}
					$tv->add_item($fp, array(
						"name" => $v,
						"id" => $fp."::".$clinf["class_name"],
						"url" => $this->mk_my_orb("class_info", array("file" => $awpath, "disp" => $clinf["class_name"])),
						"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
						"target" => "classinfo"
					));
				}
			}
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
			"left" => $this->mk_my_orb("api_class_list"),
			"right" => $this->mk_my_orb("intro"),
			"doclist" => $this->mk_my_orb("doclist"),
			"topf" => $this->mk_my_orb("topf", array("id" => $arr["id"]))
		));
		die($this->parse());
	}

	function display_class($data, $cur_file, $opts = array())
	{
		if ($opts["disp"] == "__outer")
		{
			$this->read_template("function_info.tpl");
		}
		else
		{
			$this->read_template("class_info.tpl");
		}

		$f = array();
		$api_count = 0;
		$orb_count = 0;
		foreach($data["functions"] as $func => $f_data)
		{
			$arg = "";

			if ($opts["api_only"] && !$f_data["doc_comment"]["attribs"]["api"])
			{
				continue;
			}

			$api_count += $f_data["doc_comment"]["attribs"]["api"];
			$orb_count += !empty($f_data["doc_comment"]["attribs"]["name"]) ? 1 : 0 ;

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

			$attribs = "";
			foreach (safe_array($f_data['doc_comment']['attribs']) as $attrib_name => $attrib_value)
			{
				$this->vars(array(
					'attrib_name' => $attrib_name,
					'attrib_value' => $attrib_value
				));
				$attribs .= $this->parse('ATTRIB');
			}

			$params = "";
			foreach (safe_array($f_data['doc_comment']['params']) as $param_name => $param_data)
			{
				$this->vars(array(
					'param_name' => $param_name,
					'param_required' => $param_data['req'],
					'param_type' => $param_data['type'],
					'param_comment' => nl2br(trim($param_data['comment']))
				));
				$params .= $this->parse('PARAM');
			}

			$doc_file = dirname($cur_file)."/".basename($cur_file, ".aw")."/".$data["name"].".".$func.".txt";
			unset($example_links);
			foreach($f_data["doc_comment"]["examples_links"] as $match => $url)
			{
				$example_links .= "<a href=\"".$url."\">$match</a><br />";
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
				'ATTRIB' => $attribs,
				'PARAM' => $params,
				'returns' => (empty($f_data['doc_comment']['returns'])) ? t('nothing') : nl2br($f_data['doc_comment']['returns']),
				'errors' => (empty($f_data['doc_comment']['errors'])) ? t('none') : nl2br($f_data['doc_comment']['errors']),
				'comment' => nl2br($f_data['doc_comment']['comment']),
				'examples' => (empty($f_data['doc_comment']['examples'])) ? t('none') : highlight_string("<?php \n\t\t".$f_data['doc_comment']['examples']."\n?>", true).(strlen($example_links)?"<br>".$example_links:""),
				"view_source" => $this->mk_my_orb("view_source", array("file" => $cur_file, "v_class" => $data["name"],"func" => $func)),
				"view_usage" => $this->mk_my_orb("view_usage", array("file" => $cur_file, "v_class" => $data["name"],"func" => $func)),
				"doc" => $this->show_doc(array("file" => $doc_file))
			));
			if ($f_data["doc_comment"]["attribs"]["api"] == 1)
			{
				$f["API"] .= $this->parse("API_FUNCTION");
			}
			else
			if (!empty($f_data["doc_comment"]["attribs"]["name"]))
			{
				$f["ORB"] .= $this->parse("ORB_FUNCTION");
			}
			else
			if (isset($this->cb_callbacks[$func]))
			{
				$f["CB"] .= $this->parse("CB_FUNCTION");
			}
			else
			if ($f_data["access"] != "public")
			{
				$f["PRIVATE"] .= $this->parse("PRIVATE_FUNCTION");
			}
			else
			{
				$f["OTHER"] .= $this->parse("OTHER_FUNCTION");
			}
			$fl .= $this->parse("LONG_FUNCTION");
		}
		foreach($f as $_f_type => $_f_str)
		{
			if ($_f_str != "")
			{
				$this->vars(array(
					$_f_type."_FUNCTION" => $_f_str
				));
				$this->vars(array(
					"HAS_".$_f_type => $this->parse("HAS_".$_f_type)
				));
			}
		}

		if ($data["extends"] != "")
		{
			$this->_display_extends($data);
		}
		$this->_display_templates($data["functions"]);

		if (is_array($data["dependencies"]))
		{
			$this->_display_dependencies($data["dependencies"]);
		}
		if (is_array($data["implements"]))
		{
			$this->_display_implements($data["implements"]);
		}

		$this->_display_throws($data);
		$this->_display_defines($opts["defines"]);

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
			"LONG_FUNCTION" => $fl,
			"view_class" => $this->mk_my_orb("view_source", array("file" => $cur_file, "v_class" => $data["name"])),
			"maintainer" => $data["maintainer"],
			"cvs_version" => $data["cvs_version"],
			"file" => substr($cur_file, 1),
			"func_count" => count($data["functions"]),
			"api_func_count" => $api_count,
			"orb_func_count" => $orb_count,
			"type_name" => $data["type"],
			"cvsweb_url" => "http://dev.struktuur.ee/cgi-bin/viewcvs.cgi/automatweb_dev/classes".$cur_file,
			"class_comment" => nl2br($data["class_comment"])
		));

		return $this->finish_with_style($this->parse());
	}

	function _display_implements($impl_arr)
	{
		$p = "";
		foreach($impl_arr as $impl)
		{
			try 
			{
				$clf = class_index::get_file_by_name(basename($impl));
			}
			catch (awex_clidx_filesys $e)
			{
				die("ex for class $impl ".$e->getMessage());
			}
			$clf = str_replace(aw_ini_get("classdir"), "", $clf);
			$this->vars(array(
				"link" => $this->mk_my_orb("class_info", array("file" => $clf, "api_only" => $_GET["api_only"], "disp" => basename($impl))),
				"name" => $impl
			));
			$p .= $this->parse("IMPLEMENTS");
		}
		$this->vars(array(
			"IMPLEMENTS" => $p
		));
	}

	function _display_templates($funcs, $class_name)
	{
		// read main tpl folder from init method
		// read template files from read_template methods
		$used_tpls = array();
		$tpl_folder = "";
		foreach($funcs as $f_name => $f_data)
		{
			foreach(safe_array($f_data["local_calls"]) as $lcall_data)
			{
				if ($lcall_data["func"] == "init")
				{
					// read template folder arg
					if (substr($lcall_data["arguments"], 0, 5) == "array")
					{
						// parse from array
						preg_match("/tpldir['\"]\=\>['\"](.*)['\"]/imsU", $lcall_data["arguments"], $mt);
						$tpl_folder = $mt[1];
					}
					else
					{
						$tpl_folder = $lcall_data["arguments"];
					}
				}
				if ($lcall_data["func"] == "read_template")
				{
					$used_tpls[$f_name][$lcall_data["arguments"]] = $lcall_data["arguments"];
				}
			}
		}


		$p = "";
		foreach($used_tpls as $f_name => $tpls)
		{
			foreach($tpls as $tpl)
			{
				$this->vars(array(
					"func" => $f_name,
					"tpl_file" => $this->strip_quotes($tpl)
				));
				$p .= $this->parse("TEMPLATE");
			}
		}

		$this->vars(array(
			"TEMPLATE" => $p,
			"tpl_folder" => $tpl_folder
		));
	}

	private function strip_quotes($str)
	{
		if ($str[0] == "'" || $str[0] == '"')
		{
			return substr(trim($str), 1, -1);
		}
		return $str;
	}

	function _display_throws($data)
	{
		$throws = array();
		foreach(safe_array($data["functions"]) as $func)
		{
			foreach(safe_array($func["throws"]) as $thr)
			{
				$throws[$thr] = $thr;
			}
		}
		$p = "";
		foreach($throws as $impl)
		{
			try 
			{
				$clf = class_index::get_file_by_name(basename($impl));
			}
			catch (awex_clidx_filesys $e)
			{
				die("ex for class $impl ".$e->getMessage());
			}

			$clf = str_replace(aw_ini_get("classdir"), "", $clf);
			$this->vars(array(
				"link" => $this->mk_my_orb("class_info", array("file" => $clf, "api_only" => $_GET["api_only"], "disp" => basename($impl))),
				"name" => $impl
			));
			$p .= $this->parse("THROWS");
		}
		if ($data["throws_undefined"])
		{
			$p .= $this->parse("THROWS_UNSPECIFIC");
		}
		$this->vars(array(
			"THROWS" => $p,
			"THROWS_UNSPECIFIC" => ""
		));
	}

	/**
		@attrib name=prop_info

		@param option required
		@param id required
	**/
	function prop_info($arr)
	{
		echo $this->finish_with_style("");
		$obj = new object($arr['id']);
		$data = unserialize($obj->meta('properties_data'));

		if(isset($data->options[$arr['option']]))
		{
			if ("@property" == $arr['option'])
			{
				foreach($data->prop_attrib_map as $prop_type => $prop_attribs)
				{
					echo "<b>".$arr['option']."</b> ";
					echo " <font color='orange'><b>type=".$prop_type."</b></font> ";
					print aw_language_documenter::format_arr($prop_attribs,array("group","table","field"));
					print "<br>";
					print "<br>";
				};
			}
			else
			{
				echo "<b>".$arr['option']."</b> ";
				print aw_language_documenter::format_arr($data->options[$arr['option']]);
			};
			echo "<br>";
		}
	}

	/** displays information to the user about a class

		@attrib params=name nologin=0 is_public=0 all_args=0 caption="N&auml;ita klassi infot" default=0 name=class_info

		@param file required 
		@param api_only optional
		@param disp optional

		@returns 
		html with class info

		@comment
		shows detailed info about a class
	**/
	function class_info($arr)
	{
		extract($arr);

		$analyzer = get_instance("core/aw_code_analyzer/aw_code_analyzer");

		$data = $analyzer->analyze_file($file);
		foreach($data["classes"] as $class => $class_data)
		{
			if (!empty($arr["disp"]) && $class != $arr["disp"] && !($arr["disp"] == "__outer" && $class == ""))
			{
				continue;
			}
				$op .= $this->display_class($class_data, $file, array(
					"api_only" => $api_only,
					"defines" => $data["defines"],
					"disp" => $arr["disp"]
				));
		}
		return $this->finish_with_style($op);
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

		return join(", ", $ara);
	}

	/** displays function source 

		@attrib name=view_source

		@param file required
		@param v_class required
		@param func optional

	**/
	function view_source($arr)
	{
		extract($arr);

		$file = urldecode($file);
		$file = str_replace(".","",dirname($file)) . "/" . basename($file);

		$da = get_instance("core/aw_code_analyzer/aw_code_analyzer");
		$data = $da->analyze_file($file);

		if ($func)
		{
			$start_line = $data["classes"][$v_class]["functions"][$func]["start_line"];
			$end_line = $data["classes"][$v_class]["functions"][$func]["end_line"];
		}
		else
		{
			$start_line = 0;
			$end_line = 100000;
		}
		
		$fd = file($this->cfg["basedir"]."/classes".$file);
		$line = 1;
		if ($func)
		{
			$str = "<?php\n";
		}
		foreach($fd as $l)
		{
			if ($line >= $start_line && $line <= $end_line)
			{
				$str .= $l;
			}
			$line++;
		}
		if ($func)
		{
			$str .= "?>";
		}

		return $this->finish_with_style(highlight_string($str,true));
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
			try 
			{
				$clf = class_index::get_file_by_name(basename($d_class));
			}
			catch (exception $e)
			{
				die("ex for class $d_class ".$e->getMessage());
			}
			$clf = str_replace(aw_ini_get("classdir"), "", $clf);
			$this->vars(array(
				"name" => $d_class,
				"lines" => join(",", $d_ar["lines"]),
				"link" => $this->mk_my_orb("class_info", array("file" => $clf, "api_only" => $_GET["api_only"], "disp" => basename($d_class))),
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
		$orb = get_instance("core/orb/orb");
		$that = get_instance("core/aw_code_analyzer/aw_code_analyzer");

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

			try 
			{
				if ($dat["extends"] == "Exception")
				{
					$clf = "::internal";
				}
				else
				{
					$clf = class_index::get_file_by_name(basename($dat["extends"]));
				}
			}
			catch (awex_clidx_filesys $e)
			{
				die("ex for class $_extends ".$e->getMessage());
			}
			$clf = str_replace(aw_ini_get("classdir"), "", $clf);


			$this->vars(array(
				"spacer" => str_repeat("&nbsp;", $level * 3),
				"inh_link" => $this->mk_my_orb("class_info", array("file" => $clf, "api_only" => $_GET["api_only"], "disp" => basename($dat["extends"]))),
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

		return $this->finish_with_style($this->parse());
	}

	function do_class_doclist()
	{
		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgdoclss",
			"persist_state" => true,
			"root_name" => t("Classes"),
			"url_target" => "list"
		));

		$this->basedir = $this->cfg["basedir"]."/docs/classes";
		$this->ic = get_instance("core/icons");
		$this->_req_mk_clfdoc_tree($tv, $this->basedir);

		$str = $tv->finalize_tree(array(
			"rootnode" => $this->basedir,
		));
		return $this->finish_with_style($str);
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
			"root_name" => t("Classes"),
			"url_target" => "list"
		));

		$this->basedir = $this->cfg["basedir"]."/docs/tutorials";
		$this->ic = get_instance("core/icons");
		$this->_req_mk_clfdoc_tree($tv, $this->basedir);

		$str = $tv->finalize_tree(array(
			"rootnode" => $this->basedir,
		));
		return $this->finish_with_style($str);
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
		$str = preg_replace("/(#php#)(.+?)(#\/php#)/esm","highlight_string(stripslashes('<'.'?php'.'\$2'.'?'.'>'),true)",$str);

		return $this->finish_with_style(nl2br($str));
	}

	function finish_with_style($str)
	{
		$tpl = get_instance("applications/docgen/docgen_viewer");
		$tpl->read_template("style.tpl");
		$tpl->vars(array(
			"content" => $str
		));
		return $tpl->parse();
	}

	/** displays top frame 

		@attrib name=topf 

		@param id optional

	**/
	function topf($arr)
	{
		$ret = array();

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("intro"),
			"target" => "list",
			"caption" => t("Class overview")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("class_list"),
			"target" => "classlist",
			"caption" => t("All classes")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("api_class_list"),
			"target" => "classlist",
			"caption" => t("API classes")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("maintainer_class_list"),
			"target" => "classlist",
			"caption" => t("Classes by maintainer")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("interface_list"),
			"target" => "classlist",
			"caption" => t("Interfaces")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("exception_list"),
			"target" => "classlist",
			"caption" => t("Exceptions")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("doclist"),
			"target" => "classlist",
			"caption" => t("Separate documentation")
		));


		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("proplist",array('id'=>$arr['id'])),
			"target" => "classlist",
			"caption" => t("Classbase tags")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("doc_search_form", array(), "docgen_search"),
			"target" => "classlist",
			"caption" => t("Search")
		));

		$ret[] = html::href(array(
			"url" => $this->mk_my_orb("do_db_update",array('id'=>$arr['id']), "docgen_db_writer"),
			"target" => "bott",
			"caption" => t("Renew database")
		));

		if ($arr["id"])
		{
			$o = obj($arr["id"]);
			$f_id = $o->prop("foorum");

			$ret[] = html::href(array(
				"url" => $this->mk_my_orb("change", array("id" => $f_id, "group" => "contents"), CL_FORUM_V2),
				"target" => "list",
				"caption" => t("Foorum")
			));
		}


		$this->read_template("style.tpl");
		$this->vars(array(
			"content" => "&nbsp;&nbsp;".join(" | ", $ret)
		));
		return $this->parse();
	}

	/** 
		@attrib name=interface_list
	**/
	function interface_list($arr)
	{
		$this->read_template("classlist.tpl");

		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgifaces",
			"persist_state" => true,
			"root_name" => t("Interfaces"),
			"url_target" => "list"
		));

		$this->ic = get_instance("core/icons");
		// gather data about things in files
		$this->db_query("SELECT * from aw_da_classes WHERE class_type = 'interface'");
		while ($row = $this->db_next())
		{
			$tv->add_item(0, array(
				"name" => $row["class_name"],
				"id" => $row["class_name"],
				"url" => $this->mk_my_orb("class_info", array("file" => str_replace("/classes/", "/",$row["file"]), "disp" => $row["class_name"])),
				"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
				"target" => "classinfo"
			));
		}

		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => 0
			))
		));

		return $this->finish_with_style($this->parse());
	}

	/** 
		@attrib name=maintainer_class_list
	**/
	function maintainer_class_list($arr)
	{
		$this->read_template("classlist.tpl");

		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgmaints",
			"persist_state" => true,
			"root_name" => t("Classes by maintainer"),
			"url_target" => "list"
		));

		$this->ic = get_instance("core/icons");
		// gather data about things in files
		$this->db_query("SELECT * from aw_da_classes ");
		$c = array();
		while ($row = $this->db_next())
		{
			$c[$row["maintainer"]][] = $row;
		}

		foreach($c as $maintainer => $clss)
		{
			if ($maintainer == "")
			{
				$maintainer = "M&auml;&auml;ramata";
			}
			$tv->add_item(0, array(
				"name" => $maintainer,
				"id" => $maintainer,
				"url" => "",
				"iconurl" => $this->ic->get_icon_url(CL_MENU,""),
				"target" => "classinfo"
			));
			foreach($clss as $row)
			{
				$tv->add_item($maintainer, array(
					"name" => $row["class_name"],
					"id" => $row["class_name"],
					"url" => $this->mk_my_orb("class_info", array("file" => str_replace("/classes/", "/",$row["file"]), "disp" => $row["class_name"])),
					"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
					"target" => "classinfo"
				));
			}
		}

		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => 0
			))
		));

		return $this->finish_with_style($this->parse());
	}

	/** 
		@attrib name=exception_list
	**/
	function exception_list($arr)
	{
		$this->read_template("classlist.tpl");

		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgexception",
			"persist_state" => true,
			"root_name" => t("Exceptions"),
			"url_target" => "list"
		));

		$this->ic = get_instance("core/icons");
		// gather data about things in files
		$this->db_query("SELECT * from aw_da_classes WHERE class_type = 'exception'");
		while ($row = $this->db_next())
		{
			$tv->add_item(0, array(
				"name" => $row["class_name"],
				"id" => $row["class_name"],
				"url" => $this->mk_my_orb("class_info", array("file" => str_replace("/classes/", "/",$row["file"]), "disp" => $row["class_name"])),
				"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
				"target" => "classinfo"
			));
		}

		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => 0
			))
		));

		return $this->finish_with_style($this->parse());
	}

	/**
		@attrib name=proplist
		@param id required
	**/
	function doc_proplist($arr)
	{
		$this->read_template("proplist.tpl");
		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgclsss",
			"persist_state" => true,
			"root_name" => t("Classes"),
			"url_target" => "list"
		));

		$this->ic = get_instance("core/icons");
		
		$this->_req_mk_prop_tree(array(
			'id' => $arr['id'],
			'tree' => &$tv, 
			'classdir' => $this->cfg["classdir"],
		));
		
		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => 0,
			))
		));

		return $this->finish_with_style($this->parse());
	}

	function _req_mk_prop_tree($arr)
	{
		$tree = &$arr['tree'];
		$obj = new object($arr['id']);
		$data = unserialize($obj->meta('properties_data'));

		if(!$data)
		{
			//have to generate the data
			$documenter = new aw_language_documenter;
			$documenter->parse_files($arr['classdir']);
			$obj->set_meta('properties_data',serialize($documenter));
			$obj->save();
			$data = $documenter;
		}


		foreach($data->options as $option_name=>$option)
		{
			$tree->add_item(0,array(
				'name' => $option_name,
				'id' => $option_name,
				'url' => $this->mk_my_orb(
					'prop_info',
					array(
						'option' => $option_name,
						'id' => $obj->id(),
					)
				),
				'target' => 'propinfo',
			));
		}
	}

	/**

		@attrib name=api_class_list

	**/
	function api_class_list($arr)
	{
		$this->read_template("classlist.tpl");

		$tv = get_instance(CL_TREEVIEW);
		
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "dcgclsssapi",
			"persist_state" => true,
			"root_name" => t("Classes"),
			"url_target" => "list"
		));

		$this->db_query("SELECT * from aw_da_classes WHERE has_apis=1");
		$api_files = array();
		$classes = array();
		while ($row = $this->db_next())
		{
			$fp = $this->cfg["basedir"].$row["file"];
			$api_files[$fp] = $fp;
			$classes[$fp][] = $row;
		}

		$this->ic = get_instance("core/icons");
		$this->_req_mk_clf_api_tree($tv, $this->cfg["classdir"], $api_files, $classes);

		$this->vars(array(
			"list" => $tv->finalize_tree(array(
				"rootnode" => $this->cfg["classdir"],
			))
		));

		return $this->finish_with_style($this->parse());
	}

	function _req_mk_clf_api_tree(&$tv, $path, $api_files, $classes)
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
					if ($api_files[$fp])
					{
						$fc[] = $file;
					}
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
			$_hasf = $this->_req_mk_clf_api_tree($tv, $fp, $api_files, $classes);

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

			// if the file only has 1 class in it, direct link to that, else split subs	
			if (count($classes[$fp]) < 2)
			{
				$tv->add_item($path, array(
					"name" => $file,
					"id" => $fp,
					"url" => $this->mk_my_orb("class_info", array("file" => $awpath, "api_only" => 1)),
					"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
					"target" => "classinfo"
				));
			}
			else
			{
				$tv->add_item($path, array(
					"name" => $file,
					"id" => $fp,
					"url" => $this->mk_my_orb("class_info", array("file" => $awpath, "api_only" => 1)),
					"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
					"target" => "classinfo"
				));
				foreach($classes[$fp] as $clinf)
				{
					$v = $clinf["class_name"];
					if ($v == "")
					{
						$clinf["class_name"] = "__outer";
						$v = t("Functions not in any class");
					}
					else
					{
						switch($clinf["class_type"])
						{
							case "interface":
								$v = t("Interface: ").$v;
								break;

							case "class":
								$v = t("Class: ").$v;
								break;

							case "exception":
								$v = t("Exception: ").$v;
								break;
						}
					}
					$tv->add_item($fp, array(
						"name" => $v,
						"id" => $fp."::".$clinf["class_name"],
						"url" => $this->mk_my_orb("class_info", array("file" => $awpath, "api_only" => 1, "disp" => $clinf["class_name"])),
						"iconurl" => $this->ic->get_icon_url(CL_OBJECT_TYPE,""),
						"target" => "classinfo"
					));
				}
			}
			$hasf = true;
		}

		return $hasf;
	}


	private function _display_defines($d)
	{
		$s = "";
		foreach(safe_array($d) as $def)
		{
			$this->vars(array(
				"name" => $def["key"],
				"value" => $def["value"]
			));
			$s .= $this->parse("DEFINES");
		}
		$this->vars(array(
			"DEFINES" => $s
		));
	}
}


class aw_language_documenter
{
	var $options = array();
	var $prop_attrib_map = array();
	var $files_parsed = 0;

	function parse_files($dirname)
	{
		$handle = opendir($dirname);
		while($file = readdir($handle))
		{
			if($file=='.' || $file=='..' || $file=="CVS")
				continue;
			if(is_dir($dirname.'/'.$file))
			{
				$this->parse_files($dirname.'/'.$file);
			}
			else
			{
				if(substr($file,strlen($file)-2)=='aw')
				{	
					$this->parse_file($dirname.'/'.$file);
				}
			}
		}
		closedir($handle);
	}

	function parse_file($file)
	{
		$lines = file($file);
		$lines = array_filter($lines,array($this,'is_option'));
		foreach($lines as $line)
		{
			$tmp_arr = explode(' ',trim($line));
			if(sizeof($tmp_arr)==0)
				continue;
			$first_element = $tmp_arr[0];
			unset($tmp_arr[0]);
			if(!array_key_exists($first_element,$this->options))
			{
				$this->options[$first_element] = array();
			}
			$this->generate_option_attributes($first_element,$tmp_arr);
		}
		$lines=array();
	}

	function generate_option_attributes($key,$arr)
	{
		$rtrn = array();
		$attribs = array();
		foreach($arr as $value)
		{
			//if(!in_array($value,$this->options[$key]))
			{
				//if key=value
				$tmp_arr = explode('=',$value);
				if(sizeof($tmp_arr)>1)
				{
					$attribs[$tmp_arr[0]] = $tmp_arr[1];
					//vaatame kas key existib
					if(!array_key_exists($tmp_arr[0],$this->options[$key]))
					{
						$this->options[$key][$tmp_arr[0]] = array();
					}
					//vaatame kas juba selline param v22rtus existeib
					if(!in_array($tmp_arr[1],$this->options[$key][$tmp_arr[0]]))
					{
						$this->options[$key][$tmp_arr[0]][] = $tmp_arr[1];
					}

				}
			}
		}
		
		if ("@property" == $key && isset($attribs["type"]))
		{
			$type = $attribs["type"];
			unset($attribs["type"]);
			foreach($attribs as $akey => $avalue)
			{
				$this->prop_attrib_map[$type][$akey][$avalue] = $avalue;
			};
		};
		return $rtrn;
	}

	function is_option($string)
	{
		if($string{0}=='@')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function format_arr($source,$hide_keys = array())
	{
		$tmp = "";
		foreach($source as $key2=>$value2)
		{
			$tmp .= "<font color='green'>".$key2."</font>=";
			$tmp .= ' ';
			if(!in_array($key2,$hide_keys) && is_array($value2) && sizeof($value2)<10)
			{
				$tmp.= '<b>{</b> <font color="gray">';
				$tmp.= join(' | ',$value2);
				$tmp.=' </font><b>}</b>&nbsp;';
			}
			else
			{
				$tmp.= '<b>{</b><font color="gray">...</font><b>}</b>&nbsp;';
			}
		};
		return $tmp;
	}
}


?>
