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
		//	@tag value, always belongs to some previous tag

		define('TAG_CTX',1);
		define('TAG_PAIRS',2);
		define('TAG_VALUE',3);

		$this->tags = array(
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
                if ($dir = opendir($path))
                {
                        while (($file = readdir($dir)) !== false)
                        {
                                # skip the stuff that starts with .
                                if (substr($file,0,1) == ".")
                                {
                                        continue;
                                };

                                $fqfn = $path . "/" . $file;
                                if (is_dir($fqfn) && !is_link($fqfn) && ($file != "CVS"))
                                {
                                        $this->req_dir(array("path" => $fqfn));
                                }
                                elseif (is_file($fqfn) && is_readable($fqfn) && (preg_match("/\.aw$/",$fqfn)))
                                {
                                        $this->files[] = $fqfn;
                                };
                        };
                        closedir($dir);
                };
        }

	function run($args = array())
	{
		$cdir = $this->cfg["basedir"] . "/classes";
		$this->files = array();
		$this->req_dir(array("path" => $cdir));
		$files = $this->files;
		asort($files);
		$counter = 0;
		$total = 0;

		$tags = $this->tags;

		// now we need to parse all the files for properties
		foreach($files as $key => $name)
		{
			// before doing that, we need to check whether
			// xml file is already up to date
			$cname = substr(basename($name),0,-3);
			$targetfile = $this->cfg["basedir"] . "/xml/properties/$cname" . ".xml";
			$skip = false;
			if (file_exists($targetfile))
			{
				$total++;
				$target_mtime = filemtime($targetfile);
				$source_mtime = filemtime($name);

				if ($source_mtime < $target_mtime)
				{
					//print "$targetfile is up to date\n";
					$skip = true;
				};
			};

			if ($skip == true)
			{
				continue;
			};

			$lines = @file($name);

			$outdir = $this->cfg["basedir"] . "/xml/properties/";
			if (is_array($lines))
			{
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

				$success = $this->cl_end();
				if ($success)
				{
					$counter++;
				};
			};
		};
		printf("Updated %d files out of %d\nAll done.\n",$counter,$total);
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
		$no_store = array("table","container","calendar","toolbar","releditor");
		if (in_array($fields["type"],$no_store))
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
		$this->layout[$name] = $fields;
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
				$this->properties[$this->name]["caption"] = $caption;
				break;

			case "relation":
				$this->reltypes[$this->name]["caption"] = $caption;
				break;

			case "column":
				$this->classdef["column"][$this->name]["caption"] = $caption;
				break;
		};
	}
	
	function add_comment($comment)
	{
		if ($this->last_element == "property")
		{
			$this->properties[$this->name]["comment"] = $comment;
		};
	}

	////
	// !Ends a class
	function cl_end($write = 1)
	{
		$sr = get_instance("xml",array("ctag" => ""));
		$sr->set_child_id("properties","property");
		$outdir = $this->cfg["basedir"] . "/xml/properties/";
		$success = false;
		if (sizeof($this->properties) > 0 || sizeof($this->classinfo) > 0)
		{
			$fullname = $outdir . $this->cl_name . ".xml";
			print "Creating $fullname\n";
			//print "writing out $fullname\n";
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
			};
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
				};
			};
		};
		return $fields;
	}
	
};
?>
