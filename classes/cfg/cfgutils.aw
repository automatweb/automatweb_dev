<?php
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
		foreach($this->cfg["classes"] as $key => $val)
		{
			$fname = $val["file"];
			// cause property catalog is flat - alltho maybe it shouldn't be
			$fl = strpos($fname,"/") ? substr(strrchr($fname,"/"),1) : $fname;
			// for each thingie check, whether the property file for the class
			// exists.
			$this->clist[$key] = $fl;
		};
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
		if ($args["file"])
		{
			$fname = $args["file"];
		}
		elseif ($args["clid"])
		{
			$fname = $this->clist[$args["clid"]];
		};

		$retval = false;
		
		if ($fname)
		{
			$retval = file_exists($this->fbasedir . $fname . ".xml");
		};

		return $retval;
	}

	function get_clid_by_file($args = array())
	{
		$this->_init_clist();
		return array_search($args["file"],$this->clist);
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
		foreach($this->cfg["classes"] as $clid => $desc)
                {
                        if ($this->has_properties(array("clid" => $clid)))
                        {
				$result[$clid] = $desc[$value];
                        };
                }
		return $result;
	}

	////
	// !Loads, unserializes and returns properties for a single class,
	// optionally also caches them
	// file(string) - name of the class
	// clid(int) - class_id
	function load_properties($args = array())
	{
                extract($args);
		if (!$file)
		{
			$file = $this->clist[$clid];
		};
                // full cavity search
                if (preg_match("/\W/",$file))
                {
                        die("Invalid clid - $file<bR>");
                };
                // here be cache.
		$fqfn = $this->fbasedir . $file . ".xml";
                $source = $this->get_file(array("file" => $fqfn));
		$properties = array();
                if ($source)
                {
                        $parser = get_instance("xml/xml_path_parser");
                        $parser->parse_data(array("content" => $source));
                        $properties = $parser->get_data("/properties/property");
			$classinfo = $parser->get_data("/properties/classinfo");
			$this->classinfo = $classinfo[0];
                };
                return $properties;
	}

	function get_classinfo()
	{
		return $this->classinfo;
	}
	
};
?>
