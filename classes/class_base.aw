<?php
// $Id: class_base.aw,v 2.12 2002/11/21 17:25:42 duke Exp $
// Common properties for all classes
/*
	@default table=objects
	@default corefield=yes

	@property name type=textbox group=general
	@caption Objekti nimi
	
	@property comment type=textbox group=general
	@caption Kommentaar
	
	@property alias type=textbox group=general 
	@caption Alias

	@property status type=status group=general 
	@caption Staatus

	@property jrk type=textbox size=4 group=general
	@caption Jrk

	@property created type=date access=ro
	@caption Loomise kuupäev

	@property createdby type=uid access=ro
	@caption Looja

	@property modified type=date access=ro
	@caption Muutmise kuupäev

	@property modifiedby type=uid access=ro
	@caption Muutja

	@property oid type=int access=ro
	@caption Objekti id

	@property parent type=int access=ro
	@caption Parenti id

	@property class_id type=class_id access=ro
	@caption Klassi id
*/


// some contants for internal use

// possible return values for set_property
// everything's ok, property can be saved
define('PROP_OK',1);

// drop this property from the save queue
define('PROP_IGNORE',2);

// error occured while saving this property, notify
// the user, but still save the rest of the
// object data (if any)
define('PROP_ERROR',3);

// something went very very wrong, 
// notify the user and DO NOT save the object
define('PROP_FATAL_ERROR',4);

classload("aliasmgr");
class class_base extends aliasmgr
{

	function class_base($args = array())
	{
		$this->init("");
		$this->output_client = "htmlclient";
		$this->ds_name = ($ds_name) ? $ds_name : "ds_local_sql";
	}

	////
	// !Generate a form for adding or changing an object
	function change($args = array())
	{
		// XXX: this needs some SERIOUS cleanup
		if ($args["ds_name"])
		{
			$this->ds_name = $args["ds_name"];
		};

		$this->check_class();

		extract($args);
		$this->_init_object(array("id" => $id,"parent" => $parent));

		$active = ($group) ? $group : "general";
		$this->group = $active;

		// I need an easy way to turn off individual properties
		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		// if the object is divided between 2 tables, then this
		// loads data from the second table
		$this->load_object();

		// here be some magic to determine the correct output client
		// this means we could create a TTY client for AW :)
		// actually I'm thinking of native clients and XML-RPC
		// output client is probably the first that should be
		// implemented.
		$cli = get_instance("cfg/" . $this->output_client);
		$cli->start_output();

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

		// need to cycle over the property nodes, do replacements
		// where needed and then cycle over the result and generate
		// the output
		$resprops = array();
                foreach($realprops as $key => $val)
                {
                        if (is_array($val))
                        {
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
                                $status = $this->inst->get_property($argblock);
                        };

			// I need other way to retrieve a list of dynamically
			// generated properties from the class and display those
			if ($status === PROP_IGNORE)
			{
				// do nothing
			}
			else
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

		$orb_class = $this->cfg["classes"][$this->clid]["file"];

		if ($orb_class == "document")
		{
			$orb_class = "doc";
		};

                $cli->finish_output(array(
					"action" => "submit",
					"data" => array(
						"id" => $id,
						"group" => $group,
						"orb_class" => $orb_class,
						"parent" => $parent,
						"period" => $period,
						"ds_name" => $this->ds_name,
					),
		));

		if (!$content)
		{
			$content = $cli->get_result();
		};

		return $this->gen_output(array(
			"parent" => $parent,
			"content" => $content,
		));

	}

	////
	// !Saves the data that comes from the form generated by change
	function submit($args = array())
	{
		$this->quote($args);

		// check whether this current class is based on class_base
		if ($args["ds_name"])
		{
			$this->ds_name = $args["ds_name"];
		};

		$this->check_class();

		// if this is a new object, then _init_object tries to load
		// the parent object as a menu. If that fails, the code
		// will never return here.
		$this->_init_object(array(
			"id" => $args["id"],
			"parent" => $args["parent"],
		));

		if (method_exists($this->inst,"callback_pre_save"))
		{
			// nb! the handler gets quoted data
			$this->inst->callback_pre_save(array(
				"id" => $this->id,
				"form_data" => &$args,
			));
		}
		
		extract($args);

		if (!$id)
		{
			// create the object, if it wasn't already there
			$id = $this->ds->ds_new_object(array(),array(
					"parent" => $parent,
					"name" => $name,
					"comment" => $comment,
					"class_id" => $this->clid,
					"alias" => $alias,
					"status" => isset($status) ? $status : 1,
			));

			$this->new = true;
			$this->id = $id;
		};

		// and read it back again
		$this->coredata = $this->ds->ds_get_object(array(
			"id" => $this->id,
			"class_id" => $this->clid,
		));

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
		// give the object a chance to change the data that
		// before it's written out to the table
		$callback = method_exists($this->inst,"set_property");

		// collect the list of properties we want to save.
		// we will do that in 2 stages, since some set_property
		// calls might actually want to modify (GASP!) the
		// data, that gets saved

		$this->load_object();

		$resprops = array();
		$savedata = array();
		$form_data = $args;
                foreach($realprops as $property)
                {
                        if (is_array($property))
                        {
				// this return the old/saved value of the property
                                $this->get_value(&$property);
				// new data is in $args
                        };

                        $argblock = array(
                                "prop" => &$property,
                                "obj" => &$this->coredata,
                                "objdata" => &$this->objdata,
				"form_data" => &$form_data,
                        );

			// give the class a possiblity to execute some action
			// while we are saving it.

			// for callback, the return status of the function decides
			// whether to save the data or not, so please, make sure
			// that your set_property returns PROP_OK for stuff
			// that you want to save
                        if ($callback)
                        {
                                $status = $this->inst->set_property($argblock);
                        }
			else
			{
				$status = PROP_OK;
			};

			// move the data into save queue only of set_property
			// returns nothing
			if ($status == PROP_OK)
			{
				$savedata[$property["name"]] = $form_data[$property["name"]];
				$resprops[] = $property;
			};

		};

		if (sizeof($savedata) == 0)
		{
			die("Nothing to save! Error in the code?<br>");
		};

		foreach($resprops as $property)
		{
			$name = $property["name"];
			$table = $property["table"];
			$field = $property["field"];
			$method = $property["method"];
			$handler = $property["handler"];
			$type = $property["type"];
			if ($type == "text")
			{
				continue;
			};
			if (($type == "select") && $property["multiple"])
			{
				$savedata[$name] = $this->make_keys($savedata[$name]);
			};
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
				$metadata[$name] = $savedata[$name];
			}
			elseif ($table == "objects")
			{
				if (isset($savedata[$name]))
				{
					$coredata[$name] = $savedata[$name];
				};
			}
			else
			{
				if (isset($savedata[$name]))
				{
					$objdata[$name] = $savedata[$name];
				};
			};
		};


		if (sizeof($metadata) > 0)
		{
			$coredata["metadata"] = $metadata;
		};

		$coredata["id"] = $id;
		$this->ds->ds_save_object(array("id" => $id,"clid" => $this->clid),$coredata);
		$this->save_object(array("data" => $objdata));

		if (method_exists($this->inst,"callback_post_save"))
		{
			$this->inst->callback_post_save(array("id" => $this->id));
		}

		// logging
		$classname = get_class($this->orb_class);
		$name = $this->coredata["name"];

		if ($this->new)
		{
			$this->_log($classname, "Lisas $classname objekti $name ($id)", $id);
		}
		else
		{
			$this->_log($classname, "Muutis $classname objekti $name ($id)", $id);
		};


		if ($this->ds_name == "ds_local_file")
		{
			$ds_name = $this->ds_name;
		}
		else
		{
			$ds_name = "";
		};
                return $this->mk_my_orb("change",array("id" => $id,"group" => $group,"ds_name" => $ds_name),get_class($this->orb_class));
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
		$this->ds = get_instance("datasource/" . $this->ds_name);
	}

	////
	// !Stuff that is shared between all tabs goes into here
	function _init_object($args = array())
	{
		$cp = $this->get_class_picker(array("field" => "file"));
		$this->clid = $this->orb_class->get_opt("clid");

		if ($args["id"])
		{
			$this->coredata = $this->ds->ds_get_object(array(
				"id" => $args["id"],
				"class_id" => $this->clid,
			));

			$this->id = $this->coredata["oid"];
			$this->parent = $this->coredata["parent"];
                        $this->clfile = $cp[$this->coredata["class_id"]];
			
		}
		else
		{
			if (!$this->ds->ds_can_add($args))
			{
				die($this->ds->get_error_text());
			};

			$this->parent = $args["parent"];
			$this->clfile = $cp[$this->clid];

		}

		// temporary - until we are sure that will will not go back to
		// the old interface
		if ($this->clid == 1)
		{
			$this->clfile = "menu";
		};

		// temporary - until we switch document editing back to new interface
		if ($this->clid == 7)
		{
			$this->clfile = "doc";
		};

		if (!$this->clfile)
		{
			die("coult not identify object " . $this->clfile);
		};
		
		// get an instance of the class that handles this object type
		$this->inst = get_instance($this->clfile);
	}

	function gen_output($args = array())
	{
		// XXX: figure out a way to do better titles
		$classname = get_class($this->orb_class);
		if ($this->id)
		{
			$title = "Muuda $classname objekti " . $this->coredata["name"];
			$parent = $this->coredata["parent"];
		}
		else
		{
			$title = "Lisa $classname objekt";
			$parent = $args["parent"];
		};

		$this->mk_path($parent,$title);

		$grpnames = new aw_array($this->groupnames);
		
		// tabpanel really should be in the htmlclient too
		$this->tp = get_instance("vcl/tabpanel");

		// I need a way to let the client (the class using class_base to
		// display the editing form) to add it's own tabs.

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
		$table = $this->classinfo["objtable"]["text"];
		$idfield = $this->classinfo["objtable_index"]["text"];
		$id = $this->id;
		if ($id && $table && $idfield)
		{
			$this->objdata = $this->ds->ds_get_object(array(
				"id" => $id,
				"table" => $table,
				"idfield" => $idfield,
			));
		};
	}

	////
	// !Saves the object
	function save_object($args = array())
	{
		$table = $this->classinfo["objtable"]["text"];
		$idfield = $this->classinfo["objtable_index"]["text"];
		$id = $this->id;
		if ($table && $idfield)
		{
			// create the new record
			if ($this->new)
			{
				$this->ds->ds_new_object(array(
					"table" => $table,
					"idfield" => $idfield,
					"id" => $id,
				));
			};
		
			$this->ds->ds_save_object(array(
				"table" => $table,
				"idfield" => $idfield,
				"id" => $id),$args["data"]
			);
		};
	}
	
	function get_active_properties($args = array())
	{
		// load all properties
		$cfgu = get_instance("cfg/cfgutils");
		$cfile = basename($args["clfile"]);

		// XXX: temporary
		if ($cfile == "document")
		{
			$cfile = "doc";
		};

		// this finds out whether we have to load a config form
		// and if so, do it and set $this->active_properties
		// and $this->property_order - which we will then use
		// for generating the list of active properties
		$this->get_active_cfgform();

		// loads all properties for this class
		$all_props = $cfgu->load_properties(array("file" => $cfile));

		$this->classinfo = $cfgu->get_classinfo();
		$corefields = $this->get_visible_corefields();

		// I need names of all group and the contents of active group
		// group means the contents of a tab
		$activegroup = ($args["group"]) ? $args["group"] : "general";
		
		$elements = array();
		$this->groupnames = array();
		$default_ord = 0;

		// now, cycle over all the properties and do all necessary filtering
		foreach($all_props as $val)
		{
			$use = true;
			$name = $val["name"];

			// skip all Read-Only properties - but those will probably go away anyway
			if ($val["access"] == "ro")
			{
				$use = false;
			};


			// skip if the active cfgform hides it
			if (is_array($this->active_properties) && (!$this->active_properties[$name]))
			{
				$use = false;
			};

			// skip if property is a core fields. Core fields are common to all objects
			// and they are stored in the objects table
			if ($val["corefield"] && !isset($corefields[$name]))
			{
				$use = false;
			};

			if ($use)
			{
				// stuff with no group name goes into general. 
				$grpname = ($val["group"]) ? $val["group"] : "general";

				if ($grpname == $activegroup)
				{
					$elkey = (int)$this->property_order[$name];
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

		// reorder the elements
		ksort($elements,SORT_NUMERIC);

		return $elements;
	}
	
	function convert_element(&$val)
	{
		if (($val["type"] == "objpicker") && $val["clid"])
		{
			$val["type"] = "select";
			$val["options"] = $this->list_objects(array(
				"class" => constant($val["clid"]),
				"subclass" => ($val["subclass"]) ? constant($val["subclass"]) : "",
				"addempty" => true,
			));
		};

		if (($val["type"] == "cfgform_picker") && $val["clid"])
		{
			// now I need to figure out the list of files for thiss
			// class type
			$class_id = constant($val["clid"]);
			$cf = get_instance("cfg/cfgform");
			$val["options"] = $cf->get_cfgforms_by_class(array(
				"clid" => $val["clid"],
			));
			$val["type"] = "select";
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

	////
	// !Figures out which corefields should be shown
	function get_visible_corefields()
	{
		// figure out which core fields are to be shown
		if ($this->classinfo["corefields"]["text"])
		{
			$corefields = array_flip(explode(",",$this->classinfo["corefields"]["text"]));
		}
		else
		{
			$corefields = array("name" => 0);
		};
		
		// by default only the name is shown
		if ( sizeof($corefields) == 0 )
		{
			$corefields = array("name" => 0);
		};

		return $corefields;
	}

	////
	// !Figures out the correct configuration form that should be used
	// for displaying the object editing form

	// right now this only works for documents and the "doc" class
	function get_active_cfgform($args = array())
	{
		if ($this->clid == 7)
		{
			// first, check the parent menu
			if ($this->parent)
			{
				$parobj = $this->get_object($this->parent);
				$this->parse_cfgform(array("id" => $parobj["meta"]["tpl_edit_cfgform"]));
			};

		};

//                 if there is a configuration form set for the current 
//                 class, load it and filter our display according to that

//                $cfg = get_instance("config");
//                $classconf = aw_unserialize($cfg->get_simple_config("class_cfgforms"));
//
//                $use_form = $classconf[$this->clid];
//                $cfgform = $this->get_object($use_form);

//                $def = $this->cfg["classes"][$this->clid]["def"];
//                $this->visible_properties = $cfgform["meta"]["properties"][$def];
//                $this->el_ord = $cfgform["meta"]['ord'][$def];
	}

	////
	// !Loads and parses a configuration form
	// id(int) - id of the config form
	function parse_cfgform($args = array())
	{
		$id = $args["id"];
		if ($id)
		{
			$cf = get_instance("cfg/cfgform");
			$forms = $cf->get_cfgforms_by_class(array(
				"clid" => $this->cfg["classes"][$this->clid]["def"],
			));
			
//                        $cfgform = $this->get_object($id);
			if ($forms[$id])
			{
				// load the file
				$cfgform = $cf->get_cfgform_from_file(array(
					"clid" => $this->cfg["classes"][$this->clid]["def"],
					"id" => $id,
				));

			};

			// XXX: false means that no filtering should be done
			// but I think that this should really be decided by the ini file
			// maybe it's better if we are "locked down" by default
			$this->active_properties = false;

			if (is_array($cfgform["properties"]))
			{
				$this->active_properties = $cfgform["properties"];
			};

			$this->property_order = false;

			if (is_array($cfgform["ord"]))
			{
				$this->property_order = $cfgform["ord"];
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
		$this->check_class();
		$this->_init_object(array("id" => $id));

		$this->action = $action;

		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		$gen = $almgr->new_list_aliases(array(
			"id" => $id,
			"return_url" => $this->mk_my_orb("list_aliases",array("id" => $id),get_class($this->orb_class)),
		));
		return $this->gen_output(array("content" => $gen));
	}

	////
	// !Displays alias manager search form inside the configuration manager interface
	function search_aliases($args = array())
	{
		extract($args);
		$this->check_class();
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


	//////////////////////////////////////////////////////////////////////
	// 
	// init functions for classes that do not use automatic form generator
	//
	//////////////////////////////////////////////////////////////////////

	////
	// !initializes the add function UI
	// params:
	// $args - the arguments to the add function
	// $classname - the name that will be used in the path
	// $tpl - the template to read
	function _add_init($args, $classname, $tpl)
	{
		// check if we can add objects under the parent menu
		if (!$this->can("add", $args["parent"]))	
		{
			$this->acl_error("add", $args["parent"]);
		}
		// make the path
		$self_url = aw_global_get("REQUEST_URI");
		if ($args["return_url"] != "")
		{
			// if return url is set, we must make the path point to the url gievn
			$this->mk_path(0,"<a href='$args[return_url]'>Tagasi</a> / <a href='$self_url'>Lisa $classname</a>");
		}
		else
		{
			$this->mk_path($args["parent"],"<a href='$self_url'>Lisa $classname</a>");
		}
		$this->read_template($tpl);
	}

	function _change_init($args, $classname, $tpl = "")
	{
		if (!$this->can("edit", $args["id"]))
		{
			$this->acl_error("edit", $args["id"]);
		}
		$ob = $this->get_object($args["id"]);
		$self_url = aw_global_get("REQUEST_URI");
		if ($args["return_url"] != "")
		{
			$this->mk_path(0,"<a href='$args[return_url]'>Tagasi</a> / <a href='$self_url'>Muuda $classname</a>");
		}
		else
		{
			$this->mk_path($ob["parent"], "<a href='$self_url'>Muuda $classname</a>");
		}
		if ($tpl != "")
		{
			$this->read_template($tpl);
		}
		return $ob;
	}
};
?>
