<?php
class propcollector extends aw_template
{
	function propcollector($args = array())
	{
		$this->init(array("no_db" => 1));
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
		// now we need to parse all the files for properties
		foreach($files as $key => $name)
		{
			// before doing that, we need to check whether
			// xml file is already up to date
			$cname = substr(basename($name),0,-3);
			$targetfile = $this->cfg["basedir"] . "/xml/properties/$cname" . ".xml";
			if (file_exists($targetfile))
			{
				$total++;
				$target_mtime = filemtime($targetfile);
				$source_mtime = filemtime($name);

				if ($source_mtime < $target_mtime)
				{
					//print "$targetfile is up to date\n";
					continue;
				};
			};


			$lines = @file($name);

			$outdir = $this->cfg["basedir"] . "/xml/properties/";
			if (is_array($lines))
			{
				$this->cl_start($cname);
				foreach($lines as $line)
				{
					if (preg_match("/^\s*@classinfo (.*)/",$line,$m))
					{
						$this->set_classinfo($m[1]);
					};
					if (preg_match("/^\s*@groupinfo (\w+?) (.*)/",$line,$m))
					{
						$this->set_groupinfo($m[1],$m[2]);
					};
					if (preg_match("/^\s*@tableinfo (\w+?) (.*)/",$line,$m))
					{
						$this->set_tableinfo($m[1],$m[2]);
					};
					if (preg_match("/^\s*@default (\w+?)=(.*)/",$line,$m))
					{
						$this->set_default($m[1],$m[2]);
					};
					if (preg_match("/^\s*@property (\w+?) (.*)/",$line,$m))
					{
						$this->add_property($m[1],$m[2]);
					};

					if (preg_match("/^\s*@caption (.*)/",$line,$m))
					{
						$this->add_caption($m[1]);
					};
				};
				if (sizeof($this->properties) > 0)
				{
					$counter++;
				};
				$this->cl_end();
				//print "parsed $name<br>";
			};
		};
		printf("Updated %d files out of %d\nAll done.\n",$counter,$total);
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
	}

	function add_property($name,$data)
	{
		$_x = new aw_array(explode(" ",$data));
		$fields = array("name" => $name);
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
		// add defaults as well
		foreach($this->defaults as $key => $val)
		{
			if (!$fields[$key])
			{
				$fields[$key] = $val;
			};
		};

		// field defaults to the name of the property
		if (!$fields["field"])
		{
			$fields["field"] = $fields["name"];
		};

		if ($fields["store"] == "no")
		{
			unset($fields["table"]);
			unset($fields["method"]);
			unset($fields["field"]);
		}

		if ($fields["view"] && !$this->views[$fields["view"]])
		{
			$this->views[$fields["view"]] = 1;
		};

		$this->properties[$name] = $fields;
		$this->name = $name;

	}

	function set_default($key,$value)
	{
		$this->defaults[$key] = $value;
	}

	function set_classinfo($data)
	{
		$_x = new aw_array(explode(" ",$data));
		foreach($_x->get() as $field)
		{
			list($fname,$fvalue) = explode("=",$field);
			if ($fname && $fvalue)
			{
				$this->classinfo[$fname] = $fvalue;
			};
		};
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
		$_x = new aw_array(explode(" ",$data));
		foreach($_x->get() as $field)
		{
			list($fname,$fvalue) = explode("=",$field);
			if ($fname && $fvalue)
			{
				$this->tableinfo[$id][$fname] = $fvalue;
			};
		};
	}


	function add_caption($caption)
	{
		$this->properties[$this->name]["caption"] = $caption;
	}

	////
	// !Ends a class
	function cl_end()
	{
		$sr = get_instance("xml",array("ctag" => ""));
		$sr->set_child_id("properties","property");
		$outdir = $this->cfg["basedir"] . "/xml/properties/";
		if (sizeof($this->properties) > 0)
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

			$res = $sr->xml_serialize($arr);
			//print_r($res);
			$this->put_file(array(
				"file" => $fullname,
				"content" => $res,
			));
		};
	}
	
};
?>
