<?php
// $Id: cfgutils.aw,v 1.10 2003/01/15 16:20:39 duke Exp $
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
		foreach($this->cfg["classes"] as $key => $val)
		{
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
			$retval = file_exists($this->fbasedir . $fname . '.xml');
		};

		return $retval;
	}

	function get_clid_by_file($args = array())
	{
		$this->_init_clist();
		return array_search($args['file'],$this->clist);
	}

	function get_clid_by_cldef($args = array())
	{
		return constant($args['cldef']);
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
		$clist = $this->cfg["classes"];

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
                        $parser->parse_data(array("content" => $source));
                        $properties = $parser->get_data("/properties/property");
			$classinfo = $parser->get_data("/properties/classinfo");
			$groupinfo = $parser->get_data("/properties/groupinfo");
			$tableinfo = $parser->get_data("/properties/tableinfo");
			// XXX: this means that we cannot specify any names or groups
			// in the class_base definition - those will simply be overwritten
			$this->classinfo = $classinfo[0];
			if (is_array($this->groupinfo))
			{
				if (is_array($groupinfo[0]))
				{
					$this->groupinfo = $this->groupinfo + $groupinfo[0];
				};
			}
			else
			{
				$this->groupinfo = $groupinfo[0];
			};
			$this->tableinfo = $tableinfo[0];
                };
		$res = array();
		foreach($properties as $key => $val)
		{
			$_tmp = $this->normalize_text_nodes($val);
			$name = $_tmp["name"];
			$res[$name] = $_tmp;
		};
		return $res;
	}

	function load_properties($args = array())
	{
		$this->_init_clist();
		extract($args);
		$coreprops = $this->load_class_properties(array("file" => "class_base"));
		if (!$file)
		{
			$file = $this->clist[$clid];
		};
                // full cavity search
                if (preg_match("/\W/",$file))
                {
                        die("Invalid clid - $file<bR>");
                };
		$objprops = $this->load_class_properties(array("file" => $file));

		if (is_array($this->groupinfo))
		{
			$tmp = array();
			foreach($this->groupinfo as $key => $val)
			{
				$tmp[$key] = $this->normalize_text_nodes($val[0]);

			};
		};
		$this->groupinfo = $tmp;
		if (is_array($objprops))
		{
			foreach($objprops as $objprop)
			{
				if (!$this->groupinfo[$objprop["group"]])
				{
					$this->groupinfo[$objprop["group"]] = array("caption" => $objprop["group"]);
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
		$this->tableinfo = $tmp;
		return array_merge($coreprops,$objprops);
	}

	function get_classinfo()
	{
		return $this->classinfo;
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
				$res[$key] = $val["text"];
			};
		}
		else
		{
			$res = $val;
		};
		return $res;
	}


	
};
?>
