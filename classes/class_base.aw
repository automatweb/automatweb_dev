<?php
// $Id: class_base.aw,v 2.5 2002/11/14 18:39:38 duke Exp $
classload("aliasmgr");
class class_base extends aliasmgr
{

	function class_base($args = array())
	{
		$this->init("");
		$this->output_client = "htmlclient";
	}

	function change($args = array())
	{
		$this->check_class();

		extract($args);
		$this->_init_object(array("id" => $id,"parent" => $parent));

		
		$active = ($group) ? $group : "general";
		$this->group = $active;

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		$this->load_object();

		global $XXX;
		if ($XXX)
		{
			print "<pre>";
			print_r($this->coredata);
			print "</pre>";
		};

		// here be some magic to determine the correct output client
		// this means we could create a TTY client for AW :)
		// actually I'm thinking of native clients and XML-RPC
		// output client is probably the first that should be
		// implemented.
		$cli = get_instance("cfg/" . $this->output_client);
		$cli->start_output();

		// need to cycle over the property nodes, to replacemenets
		// where needed and then cycle over the result and generate
		// the output
		$resprops = array();

		// there are two ways for a class to change the properties
		// 1 - get_property callback - usually used to change some
		// fields of the property - common use is to set the contents
		// of an select field

		// 2 - generator - which should return full property definitions
		// in the same format they come from load_properties.
		// those can be called dynamic properties I suppose
		// since they don't have to exist anywhere in the property
		// definitions
	
		// I really doubt that get_property appears out of blue
		// while we are generating the output form
		$callback = method_exists($this->inst,"get_property");

                foreach($realprops as $key => $val)
                {
                        if (is_array($val))
                        {
                                $val = $this->normalize_text_nodes($val);
                                $this->get_value(&$val);
                        };

                        $argblock = array(
                                "prop" => &$val,
                                "obj" => &$this->coredata,
                                "objdata" => &$this->objdata,
                        );

			// callbackiga saad muuta ühe konkreetse omaduse sisu
                        if ($callback)
                        {
                                $this->inst->get_property($argblock);
                        };

			// I need other way to retrieve a list of dynamically
			// generated properties from the class and display those

			if ($val["editonly"] && !$this->id)
			{
				// skip editonly elements for new objects
			}
			else
			if ($val["type"] == "generated" && method_exists($this->inst,$val["generator"]))
			{
				$meth = $val["generator"];
				$vx = $this->inst->$meth($argblock);
				$resprops = array_merge($resprops,$vx);
			}
			elseif ($val["type"] == "hidden")
			{
				// do nothing
			}
                        else
                        {
                                $resprops[] = $val;
                        };
                }

                $content = "";

                foreach($resprops as $val)
                {
                        if (is_array($val["items"]))
                        {
                                foreach($val["items"] as $subkey => $subval)
                                {
                                        $this->convert_element(&$subval);
                                        $val["items"][$subkey] = $subval;
                                }
                        }
                        else
                        {
                                $this->convert_element(&$val);
                        };

                        // add properties - one line at a time
                        if (is_string($val) && method_exists($this,$val))
                        {
				// this should NOT be here
                                $content = $this->$val();
                        };
                        $cli->add_property($val);
                };

                $cli->finish_output(array(
					"action" => "submit",
					"data" => array(
						"id" => $id,
						"group" => $group,
						"orb_class" => $class,
						"parent" => $parent),
		));

		if (!$content)
		{
			$content = $cli->get_result();
		};

		return $this->gen_output(array("content" => $content));

	}

	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		$this->check_class();
		$this->_init_object(array("id" => $id,"parent" => $parent));
		if (!$id)
		{
			// create the object, if it wasn't already there
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => $this->clid,
				"alias" => $alias,
				"status" => $status,
			));
		};

		// and read it back again
		$this->coredata = $this->get_object($id);

		$realprops = $this->get_active_properties(array(
			"clfile" => $this->clfile,
			"group" => $group,
		));

		// now we need to figure out the save strategy
		// cycle over the properties and sort out all the stuff
		// that has values in the form
		$objdata = array();
		$coredata = array();
		$metadata = array();
		// give the object a change to change the data that
		// before it's written out to the table
		$callback = method_exists($this->inst,"set_property");
                foreach($realprops as $property)
                {
                        if (is_array($property))
                        {
                                $property = $this->normalize_text_nodes($property);
                                $this->get_value(&$property);
                        };

                        $argblock = array(
                                "prop" => &$property,
                                "obj" => &$this->coredata,
                                "objdata" => &$this->objdata,
                        );

			// callbackiga saad teha mingeid operatsioone,
			// kui mingit omadust salvestatakse
                        if ($callback)
                        {
                                $retval = $this->inst->set_property($argblock);
                        };
			$name = $property["name"];
			$table = $property["table"];
			$field = $property["field"];
			$method = $property["method"];
			$handler = $property["handler"];
			$type = $property["type"];
			if ($handler == "callback")
			{
				// how on earth to I get the values
				// back from the callback routine?
			}
			else
			if ($type == "imgupload")
			{
				// upload the bloody image.
				$t = get_instance("image");
				$key = $name . "_id";
				$oldid = (int)$this->coredata["meta"][$key];
				$ar = $t->add_upload_image($name, $this->coredata["parent"], $oldid);
				$metadata[$key] = $ar["id"];
				$key = $name . "_url";
				$metadata[$key] = image::check_url($ar["url"]);
			}
			if ($method == "serialize")
			{
				$metadata[$name] = $args[$name];
			}
			elseif ($args[$name] && ($table == "objects"))
			{
				$coredata[$name] = $args[$name];
			}
			elseif ($table == "menu")
			{
				$objdata[$name] = $args[$name];
			};
		};

		if (sizeof($metadata) > 0)
		{
			$coredata["metadata"] = $metadata;
		};
		$coredata["oid"] = $id;

		$this->upd_object($coredata);

		$this->save_object(array("data" => $objdata));

                return $this->mk_my_orb("change",array("id" => $id,"group" => $group),get_class($this->orb_class));
	}

	////
	// !This decides whether to perform the requested action or not
	// acl checks for example
	function check_class()
	{
		$cfgu = get_instance("cfg/cfgutils");
		$has_properties = $cfgu->has_properties(array("file" => get_class($this->orb_class)));
		if (!$has_properties)
		{
			die("this class does not have any defined properties ");
		};
	}

	////
	// !Stuff that is shared between all tabs goes into here
	function _init_object($args = array())
	{
		$cp = $this->get_class_picker(array("field" => "file"));
		$this->clid = $this->orb_class->get_opt("clid");

		if ($args["id"])
		{
                        // retrieve the object
			// NB! get_object dies if the object does not have the 
			// correct type
                        $this->coredata = $this->get_object(array(
				"oid" => $args["id"],
				"class_id" => $this->clid,
			));

			$this->id = $this->coredata["oid"];
                        $this->clfile = $cp[$this->coredata["class_id"]];
			// temporary - until we switch menu editing over to new interface
			if ($this->coredata["class_id"] == 1)
			{
				$this->clfile = "menu";
			};
			
		}
		else
		{
			// object should only be saved under menus
			// NB! get_object dies if the object does not have the 
			// correct type
			$parobj = $this->get_object(array(
				"oid" => $args["parent"],
				"class_id" => CL_PSEUDO,
			));

			$this->parent = $parobj["oid"];
			$this->clfile = $cp[$this->clid];
			// temporary - until we switch menu editing over to new interface
			if ($this->clid == 1)
			{
				$this->clfile = "menu";
			};

		}

		if (!$this->clfile)
		{
			die("coult not identify object " . $this->clfile);
		};

		// if there is a configuration form set for the current 
		// class, load it and filter our display according to that
		$cfg = get_instance("config");
		$classconf = aw_unserialize($cfg->get_simple_config("class_cfgforms"));

		$use_form = $classconf[$this->clid];
		$cfgform = $this->get_object($use_form);

//                $def = $this->cfg["classes"][$this->clid]["def"];
//                $this->visible_properties = $cfgform["meta"]["properties"][$def];
//                $this->el_ord = $cfgform["meta"]['ord'][$def];

		
		// get an instance of the class that handles this object type
		$this->inst = get_instance($this->clfile);
	}

	function gen_output($args = array())
	{
		// XXX: figure out a way to do better titles
		if ($this->id)
		{
			$title = "Muuda objekti";
			$parent = $this->coredata["parent"];
		}
		else
		{
			$title = "Lisa objekt";
			$parent = $this->parent;
		};

		$this->mk_path($parent,$title);

		$grpnames = new aw_array($this->groupnames);
		
		// tabpanel really should be in the htmlclient too
		$this->tp = get_instance("vcl/tabpanel");

		foreach($grpnames->get() as $key => $val)
		{
			if ($this->id)
			{
				$link = $this->mk_my_orb("change",array("id" => $this->id,"group" => $key),get_class($this->orb_class));
			}
			else
			{
				$link = "";
			};

			$this->tp->add_tab(array(
				"link" => $link,
				"caption" => $key,
				"active" => ($key == $this->group),
			));
		};
		
		if ($this->id && $this->classinfo["relationmgr"])
		{
			$link = $this->mk_my_orb("list_aliases",array("id" => $this->id),get_class($this->orb_class));
			$this->tp->add_tab(array(
				"link" => $link,
				"caption" => "related_objects",
				"active" => ( ($this->action == "list_aliases") || ($this->action == "search_aliases") ),
			));
		};
		
		return $this->tp->get_tabpanel(array(
			"content" => $args["content"],
		));


	}

	function load_object($args = array())
	{
		$objtable = $this->classinfo["objtable"]["text"];
		$objtable_index = $this->classinfo["objtable_index"]["text"];
		$id = $this->id;
		if ($id && $objtable && $objtable_index)
		{
			$q = "SELECT * FROM $objtable WHERE $objtable_index = '$id'";
			$this->objdata = $this->db_fetch_row($q);
		};
	}

	function save_object($args = array())
	{
		$objtable = $this->classinfo["objtable"]["text"];
		$objtable_index = $this->classinfo["objtable_index"]["text"];
		$id = $this->id;
		$data = new aw_array($args["data"]);
		$parts = array();
		foreach($data->get() as $key => $val)
		{
			$parts[] = " $key = '$val' ";
		};
		if ((sizeof($parts) > 0) && $id && $objtable && $objtable_index)
		{
			$q = sprintf("UPDATE %s SET %s WHERE %s = %d",
				$objtable,
				join(",",$parts),
				$objtable_index,
				$id);
			$this->db_query($q);
		};
	}
	
	function get_active_properties($args = array())
	{
		// load all properties
		$cfgu = get_instance("cfg/cfgutils");
//                $coreprops = $cfgu->load_properties(array("file" => "core"));
		$all_props = $cfgu->load_properties(array("file" => basename($args["clfile"])));
		$this->classinfo = $cfgu->get_classinfo();
	
		// I need names of all group and the contents of active group
		$by_group = array();
		$activegroup = ($args["group"]) ? $args["group"] : "general";
		$elements = array();
		$this->groupnames = array();
		$default_ord = 0;
		foreach($all_props as $val)
		{
			$use = true;

			if ($val["access"]["text"] == "ro")
			{
				$use = false;
			};

			$name = $val["name"]["text"];
			if (is_array($this->visible_properties) && (!$this->visible_properties[$name]))
			{
				$use = false;
			};

			if ($use)
			{
				$grpname = ($val["group"]["text"]) ? $val["group"]["text"] : "general";
				// stuff with no group name goes into general. 
				if ($grpname == $activegroup)
				{
					$elkey = (int)$this->el_ord[$name];
					if (!$elkey)
					{
						$elkey = $default_ord;
					};
					$elkey .= $name;
					$elements[$elkey] = $val;
				};
				$this->groupnames[$grpname] = $grpname;
				$default_ord++;
			};
		}
	
		ksort($elements,SORT_NUMERIC);
		return $elements;
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

	function convert_element(&$val)
	{
		if (($val["type"] == "objpicker") && $val["clid"])
		{
			$val["type"] = "select";
                        $val["options"] = $this->list_objects(array(
					"class" => constant($val["clid"]),
					"addempty" => true,
			));
		};

		if (($val["type"] == "relpicker") && ($val["clid"]))
		{
			// retrieve the list of all aliases first time this is invoked
			if (!is_array($this->alist))
			{
				$almgr = get_instance("aliasmgr");
				if ($this->id)
				{
					$this->alist = $almgr->get_oo_aliases(array(
								"oid" => $this->id,
					));
				}
				else
				{
					$this->alist = array();
				};
			};

			$objlist = new aw_array($this->alist[constant($val["clid"])]);

			$options = array("0" => "--vali--");
			// generate option list
			foreach($objlist->get() as $okey => $oval)
			{
				$options[$oval["target"]] = $oval["name"];
			}

			$val["type"] = "select";
			$val["options"] = $options;
		};
	}
	
	////
	// !Figures out the value for property
	function get_value(&$property)
	{
		$field = ($property["field"]) ? $property["field"] : $property["name"];
		if ($property["table"] == "objects")
		{
			if ($field == "meta")
			{
				$property["value"] = $this->coredata["meta"][$property["name"]];
			}
			else
			{
				$property["value"] = $this->coredata[$property["name"]];
			};
		}
		else
		{
			if ($property["method"] == "serialize")
			{
				$property["value"] = aw_unserialize($this->objdata[$field]);
			}
			else
			{
				$property["value"] = $this->objdata[$property["name"]];
			};
		};
	}

	// wrappers for alias manager

	////
	// !Displays alias manager inside the configuration manager interface
	// this means I have to generate a list of group somewhere
	function list_aliases($args = array())
	{
		extract($args);
		$this->_init_object(array("id" => $id));

		$this->action = $action;

		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		$gen = $almgr->new_list_aliases(array(
			"id" => $id,
			"return_url" => $this->mk_my_orb("change",array("id" => $id,"group" => $group),get_class($this->orb_class)),
		));
		return $this->gen_output(array("content" => $gen));
	}

	////
	// !Displays alias manager search form inside the configuration manager interface
	function search_aliases($args = array())
	{
		extract($args);
		$this->_init_object(array("id" => $id));

		$this->action = $action;

		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		$args["return_url"] = $this->mk_my_orb("change",array("id" => $id,"group" => $group),get_class($this->orb_class));
		$gen = $almgr->search($args);
		return $this->gen_output(array("content" => $gen));
	}	


	

};
?>
