<?php
// $Id: cfgutils.aw,v 1.64 2005/10/17 16:38:55 duke Exp $
// cfgutils.aw - helper functions for configuration forms
class cfgutils extends aw_template
{
	function cfgutils($args = array())
	{
		$this->init("");
		$this->fbasedir = $this->cfg["basedir"] . "/xml/properties/";
		$this->clist_init_done = false;
	}

	function _init_clist()
	{
		if ($this->clist_init_done)
		{
			return false;
		};

		$this->clist = array();
		$tmp = aw_ini_get("classes");
		foreach($tmp as $key => $val)
		{
			if (empty($val["file"]))
			{
				continue;
			};
			$fname = $val["file"];
			// cause property catalog is flat - alltho maybe it shouldn't be
			$fl = strpos($fname,"/") ? substr(strrchr($fname,"/"),1) : $fname;
			// for each thingie check, whether the property file for the class
			// exists.
			$this->clist[$key] = $fl;
		};
		// XXX: remove this after document class has been converted
		$this->clist[7] = "doc";
		
		$this->clist_init_done = true;
	}
	////
	// !Checks whether a class has any properties
	// file(string) - name of the class
	// clid(int) - id of the class
	// here we can add different checks later on.
	function has_properties($args = array())
	{
		if ($args['file'])
		{
			$fname = basename($args['file']);
		}
		elseif ($args['clid'])
		{
			$cldat = aw_ini_get("classes");
			$fname = basename($cldat[$args['clid']]["file"]);
		};

		$retval = false;
		if ($fname)
		{
			// that check is a bit of stupid, OTOH it needs to be fast
			$retval = file_exists($this->fbasedir . $fname . '.xml');
		};
		return $retval;
	}

	////
	// !I also need to generate a list of all class id's, which have
	// any properties defined.
	// value(string) - contents of that field are used as values
	// in the returned list
	function get_classes_with_properties($args = array())
	{
		$result = array();
		$value = ($value) ? $value : "name";
		$clist = aw_ini_get("classes");

		$tmp = aw_ini_get("classes");
		foreach($tmp as $clid => $desc)
		{
			if ($this->has_properties(array("clid" => $clid)))
			{
				$result[$clid] = $desc[$value];
			};
		}
		asort($result);
		return $result;
	}

	////
	// !Loads, unserializes and returns properties for a single class,
	// optionally also caches them
	// file(string) - name of the class
	// clid(int) - class_id
	// filter(string) - filter the properties based on a attribute
	function load_class_properties($args = array())
	{
		enter_function("load_class_properties");
		extract($args);
		if (empty($args['source']) && !$file && !$this->clist_init_done)
		{
			$this->_init_clist();
			$file = $this->clist[$clid];
		};

		$system = isset($args["system"]) ? 1 : 0;

		// if system is set, then no captions/translations/etc will be loaded,
		// since storage really doesn't care. so why should property loader?

		// you can also directly parse XML, in which case we do not cache anything.
		// the only sad user of this feature is document class and it's def_cfgform.xml functionality,
		// which really should die.
		if (empty($args['source']))
		{
			$fqfn = $this->fbasedir . $file . ".xml";
			$cachename = aw_ini_get("cache.page_cache") . "/propdef_" . $file . ".cache";


			if (!$system)
			{
				load_class_translations($file);
			};
		}

		$from_cache = false;

		if (empty($args['source']) && file_exists($cachename) && (filemtime($cachename) > filemtime($fqfn)))
		{
			include($cachename);
			$from_cache = true;
		}
		else
		{
			if ($args['source'])
			{
				$source = $args['source'];
			}
			else
			{
				$source = $this->get_file(array("file" => $fqfn));
			};
			$p = xml_parser_create();
			$x = xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
			$x = xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
			xml_parse_into_struct($p, $source, $vals, $index);
			xml_parser_free($p);

			$props = array();
			$containers = array("property","classinfo","groupinfo","tableinfo","reltypes","forminfo","layout");
			$propdef = array();
			$propkey = false;
			$tagname = false;
			// if only the XML file would have a bit saner structure, the following could be a lot easier
			foreach($vals as $val)
			{
				if (2 == $val["level"] && "open" == $val["type"] && in_array($val["tag"],$containers))
				{
					$propkey = $val["tag"];
				}
				else
				if (2 == $val["level"] && "close" == $val["type"] && in_array($val["tag"],$containers))
				{
					$propkey = false;
				}
				else
				if ("property" == $propkey && "complete" == $val["type"])
				{
					if ("name" == $val["tag"])
					{
						$propname = $val["value"];
					};
					// if this tags parent is a 'container' (containing multiple values),
					// then add to that, otherwise just use the name of the tag
					if ($tagname) 
					{
						$propdef[$propkey][$propname][$tagname][] = $val["value"];
					}
					else
					{
						$tag = $val["tag"];
						$propdef[$propkey][$propname][$tag] = $val["value"];
					};
				}
				else
				/*** some attributes (props, table_fields) contain multiple values, the following 2
					ifs deal with that **/
				if ("property" == $propkey && "open" == $val["type"])
				{
					$tagname = $val["tag"];
				}
				else
				if ("property" == $propkey && "close" == $val["type"])
				{
					$tagname = false;
				}
				else
				/*** multiple value handling ends **/
				if ("groupinfo" == $propkey || "reltypes" == $propkey || "tableinfo" == $propkey ||
						"layout" == $propkey || "forminfo" == $propkey)
				{
					// level 3 is the direct child of propkey
					if (3 == $val["level"] && "open" == $val["type"])
					{
						$propname = $val["tag"];
					}
					elseif (3 == $val["level"] && "close" == $val["type"])
					{
						$propname = false;
					}
					// level 4 is the direct child of level 3 tag, this deals with multiple values
					elseif (4 == $val["level"] && "open" == $val["type"])
					{
						if ("open" == $val["type"]) $tagname = $val["tag"];
					}
					elseif (4 == $val["level"] && "close" == $val["type"])
					{
						if ("close" == $val["type"]) $tagname = false;

					}
					elseif ($val["level"] > 3 && $val["type"] == "complete")
					{
						if ($tagname)
						{
							$propdef[$propkey][$propname][$tagname][] = $val["value"];
						}
						else
						{
							$propdef[$propkey][$propname][$val["tag"]] = $val["value"];
						};
					};
				}
				else if (!empty($val["value"]))
				{
					$propdef[$propkey][$val["tag"]] = $val["value"];
				};
			};
		}

		$properties = $propdef["property"];
		$classinfo = $this->tableinfo = $relinfo = $groupinfo = array();

		$this->propdef = $propdef;

		if (isset($propdef["classinfo"]))
		{
			$classinfo = $propdef["classinfo"];
		};

		if (isset($propdef["tableinfo"]))
		{
			$this->tableinfo = $propdef["tableinfo"];
		};

		if (isset($propdef["relinfo"]))
		{
			$relinfo = $propdef["reltypes"];
		};

		if (isset($propdef["groupinfo"]))
		{
			$groupinfo = $propdef["groupinfo"];
		};

		// translate
		if (!$system)
		{
			foreach($properties as $k => $d)
			{
				if (!isset($d['caption']))
				{
					$d['caption']['text'] = "";
				}
				$t_str = "Omaduse ".$d["caption"]["text"]." (".$d["name"]["text"].") caption";
				$tmp = t2($t_str);
				//echo "reans $t_str = $tmp <br>";
				if ($tmp !== NULL)
				{
					$properties[$k]["caption"]["text"] = $tmp;
				}

				$tmp = t2("Omaduse ".$d["caption"]["text"]." (".$d["name"]["text"].") kommentaar");
				if ($tmp !== NULL)
				{
					$properties[$k]["comment"]["text"] = $tmp;
				}

				$tmp = t2("Omaduse ".$d["caption"]["text"]." (".$d["name"]["text"].") help");
				if ($tmp !== NULL)
				{
					$properties[$k]["help"]["text"] = $tmp;
				}
			}

			foreach($groupinfo as $k => $d)
			{
				$tmp = t2("Grupi ".$d["caption"]." (".$k.") pealkiri");
				if ($tmp !== NULL)
				{
					$groupinfo[$k]["caption"] = $tmp;
				}
			}

			if (is_array($relinfo))
			{
				foreach($relinfo as $k => $dat)
				{
					if (!isset($dat[0]['caption']))
					{
						$dat[0]['caption']['text'] = "";
					}

					$tmp = "Seose ".$dat[0]["caption"]["text"]." (RELTYPE_".$k.") tekst";
					$tmp = t2($tmp);
					if ($tmp !== NULL)
					{
						$relinfo[0][$k][0]["caption"]["text"] = $tmp;
					}
					
				}
			};
		};


		
		$this->classinfo = $classinfo;
		$tmp = array();

		if (isset($this->groupinfo) && is_array($this->groupinfo))
		{
			if (is_array($groupinfo))
			{
				$this->groupinfo = array_merge($this->groupinfo,$groupinfo);
			};
		}
		else
		{
			$this->groupinfo = $groupinfo;
		};
		$tmp = array();

		if (is_array($relinfo))
		{
			foreach($relinfo as $key => $val)
			{
				$_name = "RELTYPE_" . $key;
				$relx = $val;
				if (!is_array($relx["clid"]))
				{
					$relx["clid"] = array($relx["clid"]);
				};
				$_clidlist = array();
				foreach($relx["clid"] as $clid)
				{
					if (@constant($clid))
					{
						$_clidlist[] = constant($clid);
					};

				};
				$relx["clid"] = $_clidlist;
				$tmp[$key] = $relx;
				$tmp[$_name] = $relx;
				// define the constant
				//if (!defined($_name))
				//{
					@define($_name,$tmp[$key]["value"]);
				//}
				$tmp[$tmp[$key]["value"]] = $relx;
			};
		};
		$this->relinfo = $tmp;

		$res = array();

		$do_filter = false;

		// naw, it cannot really be empty, can it?
		if (empty($filter["form"]) && !$system)
		{
			$filter["form"] = array("","add","edit");
		};

		if (isset($filter) && is_array($filter) && sizeof($filter) > 0)
		{
			$do_filter = true;
			if (in_array("group",array_keys($filter)) && strlen($filter["group"]) == 0 )
			{
				$filter["group"] = "general";
			};
			$pass_count = sizeof($filter);
		}

		if (is_array($properties))
		{
			foreach($properties as $key => $val)
			{
				$_tmp = $val;
				$name = $_tmp["name"];
				if (empty($_tmp["form"]))
				{
					$_tmp["form"] = "";
				};
				if ($do_filter)
				{
					$pass = 0;
					foreach($filter as $key => $val)
					{
						// all is a special value, this makes it appear regardless of the filter value
						if (isset($_tmp[$key]) && $_tmp[$key] == "all")
						{
							$pass++;
						}
						else if (is_array($val))
						{
							if (isset($_tmp[$key]) && is_array($_tmp[$key]))
							{
								$intersect = array_intersect($_tmp[$key],$val);
								if (sizeof($intersect) > 0)
								{
									$pass++;
								};
							}
							else
							{
								if (in_array($_tmp[$key],$val))
								{
									$pass++;
								};
							};
						}
						else if (is_array($_tmp[$key]) && in_array($val,$_tmp[$key]))
						{
							$pass++;
						}
						else if ($_tmp[$key] == $val)
						{
							$pass++;
						};
					}
					if ($pass == $pass_count)
					{
						$res[$name] = $_tmp;
					};
				}
				else
				{
					$res[$name] = $_tmp;
				};
			};
		};
		if (!$from_cache)
		{
			//print "writing out";
			$fp = fopen($cachename, "w");
			$str = "<?php\n";
			$str .= aw_serialize($propdef,SERIALIZE_PHP_FILE,array("arr_name" => "propdef"));
			$str .= "?>";
			fwrite($fp, $str);
			fclose($fp);
		};
		exit_function("load_class_properties");
		return $res;
	}

	function load_properties($args = array())
	{
		extract($args);
		$filter = isset($args["filter"]) ? $args["filter"] : array();
		$clinf = aw_ini_get("classes");
		if (empty($file))
		{
			$file = basename($clinf[$clid]["file"]);
			if ($clid == 7) $file = "doc";
		};
		$this->groupinfo = array();
		$coreprops = $this->load_class_properties(array(
			"file" => "class_base",
			"filter" => $filter,
			"system" => $args["system"],
		));

		$cldat = $clinf[$clid];

		if (isset($cldat["generated"]))
		{
			$fld = $this->cfg["site_basedir"]."/files/classes";
			$loc = $fld . "/" . $cldat["file"] . "." . aw_ini_get("ext");

			$anakin = get_instance("analyzer/propcollector");
			$result = $anakin->parse_file(array(
				"file" => $loc,
			));

			$objprops = array();

			foreach($result["properties"] as $key => $val)
			{
				$objprops[$val["name"]] = $val;
			};

			// XXX: wtf?
			$this->tableinfo = $result["properties"]["tableinfo"];
		}
		else
		{
			$objprops = $this->load_class_properties(array(
				"file" => $file,
				"filter" => $filter,
				"system" => $args["system"],
			));


		};

		if (empty($this->classinfo["trans"]))
		{
			unset($coreprops["needs_translation"]);
			unset($coreprops["is_translated"]);
		};

		if (is_array($objprops))
		{
			foreach($objprops as $name => $objprop)
			{
				if (is_array($objprop["group"]))
				{
					foreach($objprop["group"] as $_group)
					{
						if (empty($this->groupinfo[$_group]))
						{
							$this->groupinfo[$_group] = array("caption" => $_group);
						};

					};
				}
				else
				{
					if (empty($this->groupinfo[$objprop["group"]]))
					{
						$this->groupinfo[$objprop["group"]] = array("caption" => $objprop["group"]);
					};
				};

				if (isset($coreprops[$name]))
				{
					unset($coreprops[$name]);
				};

			};
		};

		$rv = array_merge($coreprops,$objprops);
		return $rv;
	}

	function parse_cfgform($args = array())
	{
		$proplist = $grplist = array();
		if (isset($args["xml_definition"]))
		{
			$proplist = $this->load_class_properties(array(
				'source' => $args['xml_definition'],
			));
			$grplist = $this->groupinfo;
		}
		return array($proplist,$grplist);
	}

	function parse_definition($args = array())
	{
		if ($args["content"])
		{
			$proplist = $this->load_class_properties(array(
				'source' => $args['xml_definition'],
			));
			return $proplist;
		}
	}

	function get_classinfo()
	{
		return $this->classinfo;
	}

	function get_layoutinfo()
	{
		return isset($this->propdef["layout"]) ? $this->propdef["layout"] : array();
	}

	function get_relinfo()
	{
		return is_array($this->relinfo) ? $this->relinfo : array();
	}
	
	function get_forminfo()
	{
		return isset($this->propdef["forminfo"]) ? $this->propdef["forminfo"] : array();
	}

	function get_groupinfo()
	{
		return $this->groupinfo;
	}

	function gen_valid_id($src)
	{
		$rv = strtolower(preg_replace("/\s/","_",$src));
		$rv = preg_replace("/\W/","",$rv);
		return $rv;
	}
};
?>
