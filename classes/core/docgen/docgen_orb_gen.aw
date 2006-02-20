<?php

/** aw orb def generator

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_orb_gen.aw,v 1.8 2006/02/20 13:50:21 kristo Exp $

	@comment 
	generates orb defs, based on information from docgen_analyzer
**/

class docgen_orb_gen extends class_base
{
	function docgen_orb_gen()
	{
		$this->init("core/docgen");
	}
	
	function _get_orb_defs2($data)
	{
		$folder = substr(dirname($data["file"]), 1);
		$rv = array();
		foreach($data["functions"] as $f_name => $f_data)
		{
			// func is public if name attrib is set
			$attr = $f_data["doc_comment"]["attribs"];
			if (($a_name = $attr["name"]) != "")
			{
				$xml .= "\t\t<action name=\"$a_name\"";
				$x_a = array();
				if (isset($attr["default"]) && $attr["default"] == 1)
				{
					$x_a["default"] = 1;
				}

				if (isset($attr["nologin"]) && $attr["nologin"] == 1)
				{
					$x_a["nologin"] = 1;
				}

				if (isset($attr["is_public"]) && $attr["is_public"] == 1)
				{
					$x_a["is_public"] = 1;
				}

				if (isset($attr["all_args"]) && $attr["all_args"] == 1)
				{
					$x_a["all_args"] = 1;
				}

				if (isset($attr["is_content"]) && $attr["is_content"] == 1)
				{
					$x_a["is_content"] = 1;
				}

				if (isset($attr["caption"]) && $attr["caption"] != "")
				{
					// php5 compliance
					$x_a["caption"] = htmlentities($attr["caption"]);
					//$x_a["caption"] = str_replace("&", "&amp;", $attr["caption"]);
				}

				// make parameters
				$par = new aw_array($f_data["doc_comment"]["params"]);

				// 
				$arguments = array();
				foreach($par->get() as $p_name => $p_dat)
				{
					$x_p = array();
					$x_p["req"] = $p_dat["req"];
					if (isset($p_dat["type"]) && $p_dat["type"] != "")
					{
						$x_p["type"] = $p_dat["type"];
					}
					
					if(isset($p_dat["class_id"]) && $p_dat["class_id"] != "")
					{
						$x_p["class_id"] = $p_dat["class_id"];
					}

					if (isset($p_dat["acl"]) && $p_dat["acl"] != "")
					{
						$x_p["acl"] = $p_dat["acl"];
					}

					if (isset($p_dat["default"]) && $p_dat["default"] != "")
					{
						$x_p["default"] = $p_dat["default"];
					}

					if (isset($p_dat["value"]) && $p_dat["value"] != "")
					{
						$x_p["value"] = $p_dat["value"];
					}
					$arguments[$p_name] = $x_p;
				}

				$rv[$a_name] = array(
					"function" => $f_name,
					"actionattribs" => $x_a,
					"arguments" => $arguments,
				);

			}
		}
		return $rv;
	}

	function _get_orb_xml($arr,$classdata)
	{
		$xml  = "<?xml version='1.0'?>\n";
		$xml .= "<orb>\n";
		$folder = str_replace($this->cfg["classdir"],"",dirname($classdata["file"]));
		if (substr($folder,0,1) == "/")
		{
			$folder = substr($folder,1);
		};
		$xml .= "\t<class name=\"".$classdata["name"]."\" folder=\"".$folder."\" extends=\"".$classdata["extends"]."\">\n";
		foreach($arr as $aname => $adata)
		{
			// tuleb moodustada action string
			// tuleb moodustada function string
			$xml .= "\t\t<action name=\"$aname\"";
			foreach($adata["actionattribs"] as $act_name => $act_value)
			{
				$xml .= " $act_name=\"$act_value\"";
			};
			$xml .= ">\n";
			$xml .= "\t\t\t<function name=\"" . $adata["function"] . "\">\n";
			$xml .= "\t\t\t\t<arguments>\n";
			foreach($adata["arguments"] as $arg_name => $arg_data)
			{
					$xml .= "\t\t\t\t\t<".$arg_data["req"]." name=\"$arg_name\"";
					unset($arg_data["req"]);
					foreach($arg_data as $akey => $aval)
					{
						$xml .= " $akey=\"$aval\"";
					};
					// kuidas ma edastan kas argument on required või optional?

					$xml .= " />\n";
			};
			$xml .= "\t\t\t\t</arguments>\n";
			$xml .= "\t\t\t</function>\n";
			$xml .= "\t\t</action>\n\n";
		}

		$xml .= "\t</class>\n";
		$xml .= "</orb>\n";
		return $xml;
	}

	// that is just plain wrong .. it should return orb defs, not create xml out of them
	function _get_orb_defs($data)
	{
		$xml  = "<?xml version='1.0'?>\n";
		$xml .= "<orb>\n";

		$folder = substr(dirname($data["file"]), 1);

		$xml .= "\t<class name=\"".$data["name"]."\" folder=\"".$folder."\" extends=\"".$data["extends"]."\">\n";

		foreach($data["functions"] as $f_name => $f_data)
		{
			// func is public if name attrib is set
			$attr = $f_data["doc_comment"]["attribs"];
			if (($a_name = $attr["name"]) != "")
			{
				$xml .= "\t\t<action name=\"$a_name\"";
				$x_a = array();
				if (isset($attr["default"]) && $attr["default"] == 1)
				{
					$x_a[] = "default=\"1\"";
				}

				if (isset($attr["nologin"]) && $attr["nologin"] == 1)
				{
					$x_a[] = "nologin=\"1\"";
				}

				if (isset($attr["is_public"]) && $attr["is_public"] == 1)
				{
					$x_a[] = "is_public=\"1\"";
				}

				if (isset($attr["all_args"]) && $attr["all_args"] == 1)
				{
					$x_a[] = "all_args=\"1\"";
				}

				if (isset($attr["is_content"]) && $attr["is_content"] == 1)
				{
					$x_a[] = "is_content=\"1\"";
				}

				if (isset($attr["caption"]) && $attr["caption"] != "")
				{
					// php5 compliance
					$x_a[] = "caption=\"".htmlentities($attr["caption"])."\"";
					//$x_a[] = "caption=\"".str_replace("&", "&amp;", $attr["caption"])."\"";
				}

				$xml .= " ".join(" ", $x_a).">\n";

				$xml .= "\t\t\t<function name=\"$f_name\">\n";
				$xml .= "\t\t\t\t<arguments>\n";
	
				// make parameters
				$par = new aw_array($f_data["doc_comment"]["params"]);

				foreach($par->get() as $p_name => $p_dat)
				{
					$xml .= "\t\t\t\t\t<".$p_dat["req"]." name=\"$p_name\"";
					
					$x_p = array();
					if (isset($p_dat["type"]) && $p_dat["type"] != "")
					{
						$x_p[] = "type=\"".$p_dat["type"]."\"";
					}
					
					if(isset($p_dat["class_id"]) && $d_dat["class_id"] != "")
					{
						$x_p[] = "class_id=\"".$p_dat["class_id"]."\"";
					}

					if (isset($p_dat["acl"]) && $p_dat["acl"] != "")
					{
						$x_p[] = "acl=\"".$p_dat["acl"]."\"";
					}

					if (isset($p_dat["default"]) && $p_dat["default"] != "")
					{
						$x_p[] = "default=\"".$p_dat["default"]."\"";
					}

					if (isset($p_dat["value"]) && $p_dat["value"] != "")
					{
						$x_p[] = "value=\"".$p_dat["value"]."\"";
					}

					$xml .= " ".join(" ", $x_p)."/>\n";
				}
				$xml .= "\t\t\t\t</arguments>\n";
				$xml .= "\t\t\t</function>\n";
				$xml .= "\t\t</action>\n\n";
			}
		}
		$xml .= "\t</class>\n";
		$xml .= "</orb>\n";
		return $xml;
	}
	
	function make_orb_defs_from_doc_comments()
	{
		$p = get_instance("core/docgen/parser");
		$files = array();
		$p->_get_class_list(&$files, $this->cfg["basedir"]."/classes");

		foreach($files as $file)
		{
			/*
			$ignp = $this->cfg["basedir"]."/classes/core/locale";
			if (substr($file, 0, strlen($ignp)) == $ignp)
			{
				continue;
			}
			*/
			// check if file is modified
			$clmod = @filemtime($file);
			$xmlmod = @filemtime($this->cfg["basedir"]."/xml/orb/".basename($file, ".aw").".xml");

			if ($clmod >= $xmlmod)
			{
				$da = get_instance("core/docgen/docgen_analyzer");
				$cld = $da->analyze_file($file, true);
				// if there are no classes in the file then it gets ignored
				if (!is_array($cld["classes"]) || count($cld["classes"]) < 1)
				{
					continue;
				}
				foreach($cld["classes"] as $class => $cldat)
				{
					if (is_array($cldat["functions"]) && $class != "" && strtolower($class) == strtolower(basename($file, ".aw")))
					{
						// count orb methods
						$orb_method_count = 0;

						// XXX: figure out what the duke is going on here?
						$od = $this->_get_orb_defs2($cldat);

						if (sizeof($od) == 0)
						{	
							// check if parent class has orb actions
							if ($cldat["extends"] != "")
							{
								$orb_i = get_instance("core/orb/orb");
								$pr_defs = $orb_i->load_xml_orb_def($cldat["extends"]);
								$has = false;	
								if (is_array($pr_defs))
								{
									foreach($pr_defs[$cldat["extends"]] as $def)
									{
										if ($def["function"] != "")
										{
											$has = true;
										}
									}
								}
								if (!$has)
								{
									continue;
								}
							}
							else
							{
								continue;
							}
						};
						echo "make orb defs for $file\n";
						$xml = $this->_get_orb_xml($od,$cldat);
						//print_r($xml);
						//continue;
						//$od = str_replace(substr($this->cfg["basedir"]."/classes/",1), "", $this->_get_orb_defs($cldat));
						//$od = str_replace(substr($this->cfg["basedir"]."/classes",1), "", $od);

						$this->put_file(array(
							"file" => $this->cfg["basedir"]."/xml/orb/".$class.".xml",
							"content" => $xml
						));
					}
				}
				flush();
			}
		}
		echo ("all done\n");
	}
}
?>
