<?php
class propcollector extends aw_template
{
	function propcollector($args = array())
	{
		$this->init("");
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
		// now we need to parse all the files for properties
		foreach($files as $key => $name)
		{
			// before doing that, we need to check whether
			// xml file is already up to date
			$cname = substr(basename($name),0,-3);
			$targetfile = $this->cfg["basedir"] . "/xml/properties/$cname" . ".xml";
			if (file_exists($targetfile))
			{
				$target_mtime = filemtime($targetfile);
				$source_mtime = filemtime($name);

				if ($source_mtime < $target_mtime)
				{
					print "$targetfile is up to date\n";
					continue;
				}
				else
				{
					print "Creating $targetfile\n";
				};
			};

			$lines = @file($name);

			$outdir = $this->cfg["basedir"] . "/xml/properties/";
			if (is_array($lines))
			{
				$this->cl_start($cname);
				foreach($lines as $line)
				{
					if (preg_match("/\s+@default (\w+?)=(.*)/",$line,$m))
					{
						$this->set_default($m[1],$m[2]);
					};
					if (preg_match("/\s+@property (\w+?) (.*)/",$line,$m))
					{
						$this->add_property($m[1],$m[2]);
					};

					if (preg_match("/\s+@comment (.*)/",$line,$m))
					{
						$this->add_caption($m[1]);
					};
				};
				$this->cl_end();
				//print "parsed $name<br>";
			};
		};
	}

	////
	// !Starts a new class
	function cl_start($cname)
	{
		$this->cl_name = $cname;
		$this->properties = array();
		$this->defaults = array();
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
				$fields[$fname] = $fvalue;
			};
		};
		// add defaults as well
		foreach($this->defaults as $key => $val)
		{
			$fields[$key] = $val;
		};
		$this->properties[$name] = $fields;
		$this->name = $name;

	}

	function set_default($key,$value)
	{
		$this->defaults[$key] = $value;
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
			//print "writing out $fullname\n";
			$res = $sr->xml_serialize(array("properties" => array_values($this->properties)));
			//print_r($res);
			$this->put_file(array(
				"file" => $fullname,
				"content" => $res,
			));
		};
	}
	
};
?>
