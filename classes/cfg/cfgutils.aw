<?php
// $Id: cfgutils.aw,v 1.51 2005/04/05 13:52:35 kristo Exp $
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
		$this->_init_clist();
		if ($args['file'])
		{
			$fname = basename($args['file']);
		}
		elseif ($args['cldef'])
		{
			$fname = $this->clist[constant($args['cldef'])];
		}
		elseif ($args['clid'])
		{
			$fname = $this->clist[$args['clid']];
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

	// I need a more generic solution for caching files
	// basically, I want to be able to request a file and
	// let the wrapper class figure out whether it needs
	// to be reloaded/reparsed or whether we can simply
	// decompress the serialized representation

	// I think a separate class should do it? Or perhaps not
	// should I just let the cache class handle it? 

	// actually, the more I think about it the more I prefer
	// the latter variant

	////
	// !Loads, unserializes and returns properties for a single class,
	// optionally also caches them
	// file(string) - name of the class
	// clid(int) - class_id
	// filter(string) - filter the properties based on a attribute
	function load_class_properties($args = array())
	{
		$this->_init_clist();
		 extract($args);
		 // here be cache.
		if (!$file)
		{
			$file = $this->clist[$clid];
		};
		$fqfn = $this->fbasedir . $file . ".xml";

		$source = $this->get_file(array("file" => $fqfn));
		$properties = array();

		if ($source)
		{
			$parser = get_instance("xml/xml_path_parser");
			//$parser->parse_data(array("content" => $source));

			$parser->parse_file(array("fname" => "/xml/properties/$file" . ".xml"));

			// XXX: I hate this parser thingie
			$properties = $parser->get_data("/properties/property");
			// translate
			foreach($properties as $k => $d)
			{
				$tmp = t2("Omaduse ".$d["caption"]["text"]." (".$d["name"]["text"].") caption");
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

			$classinfo = $parser->get_data("/properties/classinfo");
			$layoutinfo = $parser->get_data("/properties/layout");

			$groupinfo = $parser->get_data("/properties/groupinfo");
			foreach($groupinfo as $k => $d)
			{
				foreach($d as $a => $gr)
				{
					$tmp = t2("Grupi ".$gr[0]["caption"]["text"]." (".$a.") pealkiri");
					if ($tmp !== NULL)
					{
						$groupinfo[$k][$a][0]["caption"]["text"] = $tmp;
					}
				}
			}

			$tableinfo = $parser->get_data("/properties/tableinfo");
			$relinfo = $parser->get_data("/properties/reltypes");
			foreach($relinfo[0] as $k => $dat)
			{
				$tmp = "Seose ".$dat[0]["caption"]["text"]." (RELTYPE_".$k.") tekst";
				$tmp = t2($tmp);
				if ($tmp !== NULL)
				{
					$relinfo[0][$k][0]["caption"]["text"] = $tmp;
				}
			}

			$forminfo = $parser->get_data("/properties/forminfo");
			$columns = $parser->get_data("/properties/columns");
			$tmp = array();
			if (is_array($columns[0]))
			{
				foreach($columns[0] as $key => $val)
				{
					$tmp[$key] = $this->normalize_text_nodes($val[0]);
				};
			};
			$this->columns = $tmp;
			$tmp = array();
			if (is_array($groupinfo[0]))
			{	
				foreach($groupinfo[0] as $key => $val)
				{
					$tmp[$key] = $this->normalize_text_nodes($val[0]);

				};
			};
			$groupinfo = $tmp;
			
			$this->classinfo = $classinfo[0];
			$tmp = array();
			if (is_array($layoutinfo[0]))
			{
				foreach($layoutinfo[0] as $key => $val)
				{
					$tmp[$key] = $this->normalize_text_nodes($val[0]);
				};
				$this->layoutinfo = $tmp;
			};

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
			if (is_array($forminfo[0]))
			{	
				foreach($forminfo[0] as $key => $val)
				{
					$tmp[$key] = $this->normalize_text_nodes($val[0]);

				};
				$this->forminfo = $tmp;
			};
			
			$this->tableinfo = $tableinfo[0];
			$tmp = array();

			if (is_array($relinfo[0]))
			{
				foreach($relinfo[0] as $key => $val)
				{
					$_name = "RELTYPE_" . $key;
					$relx = $this->normalize_text_nodes($val[0]);
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
					define($_name,$tmp[$key]["value"]);
					$tmp[$tmp[$key]["value"]] = $relx;
				};
			};
			$this->relinfo = $tmp;
                };
		$res = array();

		$do_filter = false;

		// naw, it cannot really be empty, can it?
		if (empty($filter["form"]))
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
				$_tmp = $this->normalize_text_nodes($val);
				$name = $_tmp["name"];
				if ($do_filter)
				{
					$pass = 0;
					foreach($filter as $key => $val)
					{
						// all is a special value, this makes it appear regardless of the filter value
						if ($_tmp[$key] == "all")
						{
							$pass++;
						}
						else if (is_array($val))
						{
							if (is_array($_tmp[$key]))
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

				$this->normalize_prop_array($res[$name]);
			};
		};


		return $res;
	}

	function load_properties($args = array())
	{
		extract($args);
		// this is the stuff we need to cache
		// maybe I should implement some kind of include for properties?
		$filter = isset($args["filter"]) ? $args["filter"] : array();
		$this->_init_clist();
		if (empty($file))
		{
			$file = $this->clist[$clid];
		};
		$this->groupinfo = array();
		$coreprops = $this->load_class_properties(array(
			"file" => "class_base",
			"filter" => $filter,
		));

		$objprops = $this->load_class_properties(array(
			"file" => $file,
			"filter" => $filter,
		));


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



		if (is_array($this->tableinfo))
		{
			$tmp = array();
			foreach($this->tableinfo as $key => $val)
			{
				$tmp[$key] = $this->normalize_text_nodes($val[0]);
			};
		};

		if (isset($tmp))
		{
			$this->tableinfo = $tmp;
		};
		$rv = array_merge($coreprops,$objprops);
		return $rv;
	}

	function parse_cfgform($args = array())
	{
		$proplist = $grplist = array();
		if (isset($args["xml_definition"]))
		{
			$parser = get_instance("xml/xml_path_parser");
			$parser->parse_data(array("content" => $args["xml_definition"]));
			$properties = $parser->get_data("/properties/property");
			$groupinfo = $parser->get_data("/properties/groupinfo");
			$classinfo = $parser->get_data("/properties/classinfo");
			$this->classinfo = $classinfo[0];

			// config forms have no business with other stuff in the properties definition
			// e.g. they cannot decide where the contents of their values are saved, because
			// config form definitions can be uploaded by the user and if some kind of moron
			// decides to experiment with those, then it can have catastrophous results for AW

			// so we at least _try_ to protect ourselves against attacks like this
			$safe_nodes = array("name","caption","group","size","cols","rows","richtext","value","ch_value");

			// unless! it's a relpicker, in which case I will allow additional field types
			$relpicker_safenodes = array("type","clid","reltype","pri");

			$magic = array_flip($safe_nodes);

			foreach($properties as $key => $val)
			{
				// xml_path_parser sucks donkey balls :(
				$xval = $this->normalize_text_nodes($val);
				$use_safenodes = $safe_nodes;
				if ($xval["type"] == "relpicker")
				{
					$use_safenodes = array_merge($use_safenodes,$relpicker_safenodes);
				};
				$tmp = array_intersect(array_keys($xval),array_values($use_safenodes));
				// compact does not work on arrays :(
				// so, do not use the variables defined in safe_nodes in here
				extract($xval);
				$tmp2 = compact($tmp);
				$proplist[$xval["name"]] = compact($tmp);
			}

			if (is_array($groupinfo[0]))
			{
				foreach($groupinfo[0] as $key => $val)
				{
					$grplist[$key] = $this->normalize_text_nodes($val[0]);
				};
			};
		}
		return array($proplist,$grplist);
	}

	function parse_definition($args = array())
	{
		if ($args["content"])
		{
			$parser = get_instance("xml/xml_path_parser");
			$parser->parse_data(array("content" => $args["content"]));

			// how on earth do I invoke functions on 
			$properties = $parser->get_data("/properties/property");

			$classinfo = $parser->get_data("/properties/classinfo");
			$groupinfo = $parser->get_data("/properties/groupinfo");
			$tableinfo = $parser->get_data("/properties/tableinfo");
			$columns = $parser->get_data("/properties/column");

			$tmp = array();
			if (is_array($groupinfo[0]))
			{	
				foreach($groupinfo[0] as $key => $val)
				{
					$tmp[$key] = $this->normalize_text_nodes($val[0]);

				};
			};
			$this->groupinfo = $tmp;

			$this->classinfo = $classinfo[0];
			if (is_array($this->groupinfo))
			{
				if (is_array($groupinfo))
				{
					$this->groupinfo = $this->groupinfo + $groupinfo;
				};
			}
			else
			{
				$this->groupinfo = $groupinfo;
			};
			$this->tableinfo = $tableinfo[0];
	
			$res = array();
			foreach($properties as $key => $val)
			{
				$_tmp = $this->normalize_text_nodes($val);
				$name = $_tmp["name"];
				$res[$name] = $_tmp;
			};
			return $res;
		}
	}

	function get_classinfo()
	{
		return $this->classinfo;
	}

	function get_layoutinfo()
	{
		return $this->layoutinfo;
	}

	function get_relinfo()
	{
		return is_array($this->relinfo) ? $this->relinfo : array();
	}
	
	function get_forminfo()
	{
		return $this->forminfo;
	}

	function get_columninfo()
	{
		return $this->columns;
	}

	function get_groupinfo()
	{
		return $this->groupinfo;
	}


	function normalize_text_nodes($val)
	{
		if (is_array($val))
		{
			$res = array();
			foreach($val as $key => $val)
			{
				if (isset($val["text"]))
				{
					$res[$key] = $val["text"];
				}
				else
				{
					$res[$key] = array_values($this->normalize_text_nodes($val[0]));
				};
			};
		}
		else
		{
			$res = $val;
		};
		return $res;
	}

	function normalize_prop_array(&$p)
	{
		if (!isset($p["method"]))
		{
			$p["method"] = "";
		}

		if (!isset($p["store"]))
		{
			$p["store"] = "";
		}
	}
};
?>
