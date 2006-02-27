<?php
// creates xml files out of property definitions
class propcollector extends aw_template
{
	function propcollector($args = array())
	{
		$this->init(array("no_db" => 1));

		// there are 3 ways to define a tag
		// with a context
		// 	@tag name key1=value1 key2=value2 .. keyN=valueN
		// without a context, key=value pairs
		// 	@tag key1=value1 key2=value2 .. keyN=valueN
		// simple value
		//	@tag value, always (except for "extends") belongs to some previous tag

		define('TAG_CTX',1);
		define('TAG_PAIRS',2);
		define('TAG_VALUE',3);

		$this->tags = array(
			"extends" => TAG_VALUE,
			"classinfo" => TAG_PAIRS,
			"default" => TAG_PAIRS,
			"groupinfo" => TAG_CTX,
			"tableinfo" => TAG_CTX,
			"property" => TAG_CTX,
			"layout" => TAG_CTX,
			"forminfo" => TAG_CTX,
			"reltype" => TAG_CTX,
			"column" => TAG_CTX,
			"caption" => TAG_VALUE,
			"comment" => TAG_VALUE,
		);
	}

	function req_dir($args = array())
    {
		$path = $args["path"];
		$paths = is_array($path) ? $path : array($path);
		foreach($paths as $path)
		{
			if ($dir = opendir($path))
			{
				while (($file = readdir($dir)) !== false)
				{
					# skip the stuff that starts with .
					if (substr($file,0,1) == ".")
					{
						continue;
					}

					$fqfn = $path . "/" . $file;
					if (is_dir($fqfn) && !is_link($fqfn) && ($file != "CVS"))
					{
						$this->req_dir(array("path" => $fqfn));
					}
					elseif (is_file($fqfn) && is_readable($fqfn) && (preg_match("/\.aw$/",$fqfn)))
					{
						$this->files[] = $fqfn;
					}
				}

				closedir($dir);
			}
		}
	}

	function run($args = array())
	{
		$cdir = $this->cfg["basedir"] . "/classes";
		$sdir = $this->cfg["basedir"] . "/scripts";
		$this->files = array();
		$this->req_dir (array("path" => array($cdir,$sdir)));
		$files = $this->files;
		asort($files);
		$this->count_modified = 0;
		$this->count_total = 0;

		foreach($files as $key => $name)
		{
			$cname = substr(basename($name),0,-3);
			$this->cl_start($cname);
			$this->_parse_file ($name);
			$success = $this->cl_end();

			if ($success)
			{
				$this->count_modified++;
			}
		}

		printf("Updated %d files out of %d\nAll done.\n", $this->count_modified, $this->count_total);
	}

	////
	// !parse a single file
	function parse_file($arr)
	{
		$name = $arr["file"];
		if (file_exists($name))
		{
			$lines = @file($name);
		}
		else
		if (!empty($arr["data"]))
		{
			$lines = explode("\n",$arr["data"]);
		};


		if (!is_array($lines))
		{
			return false;
		};

		$tags = $this->tags;

		$this->cl_start($cname);
		foreach($lines as $line)
		{
			$taginfo = preg_match("/^\s*@(\w*) (.*)/",$line,$m);
			$tagname = $m[1];
			$tagdata = $m[2];
			if (isset($tags[$tagname]) && $tags[$tagname] == TAG_PAIRS)
			{
				$attribs = $this->_parse_attribs($m[2]);
				if ($tagname == "classinfo")
				{
					$this->classinfo = array_merge($this->classinfo,$attribs);
				};
				if ($tagname == "default")
				{
					$this->defaults = array_merge($this->defaults,$attribs);
				};

			};

			if (isset($tags[$tagname]) && $tags[$tagname] == TAG_CTX)
			{
				preg_match("/(\w+?) (.*)/",$tagdata,$m);
				$aname = $m[1];
				$attribs = $m[2];
				if ($tagname == "groupinfo")
				{
					$this->set_groupinfo($aname,$attribs);
				}
				else
				if ($tagname == "tableinfo")
				{
					$this->set_tableinfo($aname,$attribs);
				}
				else
				if ($tagname == "property")
				{
					$this->add_property($aname,$attribs);
				}
				else
				if ($tagname == "layout")
				{
					$this->add_layout($aname,$attribs);
				}
				else
				if ($tagname == "reltype")
				{
					$this->add_reltype($aname,$attribs);
				}
				else
				if ($tagname == "forminfo")
				{
					$this->add_forminfo($aname,$attribs);
				}
				else
				{
					$this->classdef[$tagname][$aname] = $this->_parse_attribs($attribs);
					$this->name = $aname;
					$this->last_element = $tagname;
				};
			};

			if (isset($tags[$tagname]) && $tags[$tagname] == TAG_VALUE)
			{
				if ($tagname == "caption")
				{
					$this->add_caption($tagdata);
				};
				if ($tagname == "comment")
				{
					$this->add_comment($tagdata);
				};
			};
		};

		$success = $this->cl_end(0);
		return $this->cdata;
	}

	////
	// !Starts a new class
	function cl_start($cname)
	{
		$this->cl_name = $cname;
		$this->properties = array();
		$this->defaults = array();
		$this->classinfo = array();
		$this->groupinfo = array();
		$this->tableinfo = array();
		$this->views = array();
		$this->reltypes = array();
		$this->forminfo = array();
		$this->layout = array();
		$this->classdef = array();
	}

	function add_property($name,$data)
	{
		$_x = new aw_array(explode(" ",$data));
		$fields = array("name" => $name);
		// add defaults first, propery definition can override those.
		foreach($this->defaults as $key => $val)
		{
			if (!$fields[$key])
			{
				$fields[$key] = $val;
			};
		};
		foreach($_x->get() as $field)
		{
			list($fname,$fvalue) = explode("=",$field);
			if ($fname && $fvalue)
			{
				// try to split fvalue
				$_split = explode(",",$fvalue);
				if (sizeof($_split) > 1)
				{
					$fields[$fname] = $_split;
				}
				else
				{
					if ($fname == "form" && substr($fvalue,0,1) == "+")
					{
						$fields[$fname] = array("add","edit",substr($fvalue,1));
						// add to defaults, otherwise overwrite
					}
					else
					{
						$fields[$fname] = $fvalue;
					};
				};
			};
		};

		// things listed here have automatically set their store attribute to "no"
		// unless explicitly requested otherwise
		$no_store = array("table","calendar","toolbar","treeview","submit");
		if (in_array($fields["type"],$no_store) && empty($fields["store"]))
		{
			$fields["store"] = "no";
		}

		// field defaults to the name of the property
		if (!$fields["field"])
		{
			$fields["field"] = $fields["name"];
		};

		if ($fields["store"] == "no")
		{
			//unset($fields["table"]);
			//unset($fields["method"]);
			//unset($fields["field"]);
		}

		if ($fields["view"] && !$this->views[$fields["view"]])
		{
			$this->views[$fields["view"]] = 1;
		};

		$this->properties[$name] = $fields;
		$this->name = $name;
		$this->last_element = "property";

	}

	function add_reltype($name,$data)
	{
		$fields = $this->_parse_attribs($data);
		$this->reltypes[$name] = $fields;
		$this->name = $name;
		$this->last_element = "relation";

	}

	function add_layout($name,$data)
	{
		$fields = $this->_parse_attribs($data);
		if (empty($fields["group"]) && !empty($this->defaults["group"]))
		{
			$fields["group"] = $this->defaults["group"];
		};
		$this->layout[$name] = $fields;
		$this->name = $name;
		$this->last_element = "layout";
	}

	function add_forminfo($name,$data)
	{
		$this->forminfo[$name] = $this->_parse_attribs($data);
	}

	function set_groupinfo($id,$data)
	{
		$open_token = false;
		# so that we get the last token as well
		$data .= " ";
		# this could be rewritten to be shorter, of course. Feel free to do it
		for ($i = 0; $i < strlen($data); $i++)
		{
			$chr = $data[$i];
			if ($open_token)
			{
				if ($chr == "\"")
				{
					if (strlen($tmp) > 0)
					{
						list($_name,$_value) = explode("=",$tmp);
						if($_name == "caption")
						{
							$_value = htmlentities($_value);
						}
						$this->groupinfo[$id][$_name] = $_value;
						$tmp = "";
					};
					$open_token = false;
				}
				else
				{
					$tmp .= $chr;
				};
			}
			else
			{
				if ($chr == "\"")
				{
					$open_token = true;
				}
				elseif ($chr == " ")
				{
					if (strlen($tmp) > 0)
					{
						list($_name,$_value) = explode("=",$tmp);
						if($_name == "caption")
						{
							$_value = htmlentities($_value);
						}
						$this->groupinfo[$id][$_name] = $_value;
						$tmp = "";
					};
					$open_token = false;
				}
				else
				{
					$tmp .= $chr;
				};
			};

		};
	}

	function set_tableinfo($id,$data)
	{
		$attr = $this->_parse_attribs($data);
		if (empty($attr["master_index"]) && $attr["master_table"] == "objects")
		{
			$attr["master_index"] = "brother_of";
		};
		$this->tableinfo[$id] = $attr;
	}

	function add_caption($caption)
	{
		switch($this->last_element)
		{
			case "property":
				$this->properties[$this->name]["caption"] = htmlentities($caption);
				break;

			case "relation":
				$this->reltypes[$this->name]["caption"] = htmlentities($caption);
				break;

			case "column":
				$this->classdef["column"][$this->name]["caption"] = htmlentities($caption);
				break;

			case "layout":
				$this->layout[$this->name]["caption"] = htmlentities($caption);
				break;

		};
	}

	function add_comment($comment)
	{
		if ($this->last_element == "property")
		{
			$this->properties[$this->name]["comment"] = htmlentities($comment);
		};
	}

	////
	// !Ends a class
	function cl_end($write = 1)
	{
		$sr = get_instance("core/serializers/xml",array("ctag" => ""));
		$sr->set_child_id("properties","property");
		$outdir = $this->cfg["basedir"] . "/xml/properties/";
		$success = false;

		if (sizeof($this->properties) > 0 || sizeof($this->classinfo) > 0)
		{
			$fullname = $outdir . $this->cl_name . ".xml";
			if (1 == $write)
			{
				print "Creating $fullname\n";
			};
			$arr = array();
			$arr["properties"] = array_values($this->properties);

			if (sizeof($this->classinfo) > 0)
			{
				$arr["properties"]["classinfo"] = $this->classinfo;
			};

			if (sizeof($this->groupinfo) > 0)
			{
				$arr["properties"]["groupinfo"] = $this->groupinfo;
			};

			if (sizeof($this->tableinfo) > 0)
			{
				$arr["properties"]["tableinfo"] = $this->tableinfo;
			};

			if (sizeof($this->views) > 0)
			{
				$arr["properties"]["views"] = $this->views;
			};

			if (sizeof($this->reltypes) > 0)
			{
				$arr["properties"]["reltypes"] = $this->reltypes;
			};

			if (sizeof($this->layout) > 0)
			{
				$arr["properties"]["layout"] = $this->layout;
			};

			if (sizeof($this->forminfo) > 0)
			{
				$arr["properties"]["forminfo"] = $this->forminfo;
			};

			if (sizeof($this->classdef["column"]) > 0)
			{
				$arr["properties"]["columns"] = $this->classdef["column"];
			};

			if ($write == 1)
			{
				$res = $sr->xml_serialize($arr);
				//print_r($res);
				$this->put_file(array(
					"file" => $fullname,
					"content" => $res,
				));
			}
			else
			{
				$this->cdata = $arr;
			}

			$success = true;
		};
		return $success;
	}

	function _parse_attribs($data)
	{
		$_x = new aw_array(explode(" ",$data));
		//$fields = array("name" => $name);
		$fields = array();
		foreach($_x->get() as $field)
		{
			if (!$field)
			{
				continue;
			};
			list($fname,$fvalue) = explode("=",$field);
			if ($fname && $fvalue)
			{
				// try to split fvalue
				$_split = explode(",",$fvalue);
				if (sizeof($_split) > 1)
				{
					$fields[$fname] = $_split;
				}
				else
				{
					$fields[$fname] = $fvalue;
				}
			}
			else
			{
				print "Invalid syntax: $field\n";
			};
		}
		return $fields;
	}

	function _parse_file ($name)
	{
		$cname = substr(basename($name),0,-3);
		$targetfile = $this->cfg["basedir"] . "/xml/properties/$cname" . ".xml";
		$outdir = $this->cfg["basedir"] . "/xml/properties/";

		### check whether xml file is already up to date
		if (file_exists($targetfile))
		{
			$this->count_total++;
			$target_mtime = filemtime($targetfile);
			$source_mtime = filemtime($name);

			if ($source_mtime < $target_mtime)
			{
				$modified = false;
			}
			else
			{
				$modified = true;
			}
		}
		else
		{
			$modified = true;
		}

		### parse file
		$lines = @file($name);

		if (is_array($lines))
		{
			$parent = "";

			foreach ($lines as $line)
			{ ### see if current class has a parent
				$taginfo = preg_match("/^\s*@(\w*) (.*)/",$line,$m);
				$tagname = $m[1];
				$tagdata = $m[2];

				if ($tagname == "extends")
				{
					$parent = $this->cfg["basedir"] . "/classes/" . trim ($tagdata) . ".aw";

					if (file_exists ($parent))
					{ ### parse parent class data into current class' data. The fact that this recursive call is made here makes multiple inheritance possible. If that should become undesirable this whole if section can be moved outside innermost foreach loop.
						$this->count_total--;

						### if current was modified, parent has to be parsed too
						if ($modified)
						{
							touch ($parent);
						}

						$parent_modified = $this->_parse_file ($parent);
					}
				}
			}

			if ($modified or $parent_modified)
			{ ### parse current class
				$this->_parse_properties ($lines);
			}
		}

		return $modified;
	}

	function _parse_properties ($lines)
	{
		foreach($lines as $line)
		{
			$taginfo = preg_match("/^\s*@(\w*) (.*)/",$line,$m);
			$tagname = $m[1];
			$tagdata = $m[2];

			switch ($this->tags[$tagname])
			{
				case TAG_PAIRS:
					$attribs = $this->_parse_attribs($m[2]);

					if ($tagname == "classinfo")
					{
						$this->classinfo = array_merge($this->classinfo,$attribs);
					}

					if ($tagname == "default")
					{
						$this->defaults = array_merge($this->defaults,$attribs);
					}
					break;

				case TAG_CTX:
					preg_match("/(\w+?) (.*)/",$tagdata,$m);
					$aname = $m[1];
					$attribs = $m[2];

					if ($tagname == "groupinfo")
					{
						$this->set_groupinfo($aname,$attribs);
					}
					elseif ($tagname == "tableinfo")
					{
						$this->set_tableinfo($aname,$attribs);
					}
					elseif ($tagname == "property")
					{
						$this->add_property($aname,$attribs);
					}
					elseif ($tagname == "layout")
					{
						$this->add_layout($aname,$attribs);
					}
					elseif ($tagname == "reltype")
					{
						$this->add_reltype($aname,$attribs);
					}
					elseif ($tagname == "forminfo")
					{
						$this->add_forminfo($aname,$attribs);
					}
					else
					{
						$this->classdef[$tagname][$aname] = $this->_parse_attribs($attribs);
						$this->name = $aname;
						$this->last_element = $tagname;
					}
					break;

				case TAG_VALUE:
					if ($tagname == "caption")
					{
						$this->add_caption ($tagdata);
					}

					if ($tagname == "comment")
					{
						$this->add_comment ($tagdata);
					}
					break;
			}
		}
	}
}
?>
