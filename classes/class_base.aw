<?php
// $Id: class_base.aw,v 2.23 2002/12/19 15:51:51 duke Exp $
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
// notify the user and DO NOT display the form/save the object
define('PROP_FATAL_ERROR',4);

classload("aliasmgr");
class class_base extends aliasmgr
{
	function class_base($args = array())
	{
		$this->init("");
	}

	function init($arg)
	{
		$this->output_client = "htmlclient";
		$this->ds_name = "ds_local_sql";
		$this->default_group = "general";
		parent::init($arg);
	}

	////
	// !Generates a form for adding an object
	function add($args = array())
	{
		$this->init_class_base();
		if (!$this->ds->ds_can_add($args))
		{
			die($this->ds->get_error_text());
		};

		$this->parent = $args["parent"];
		extract($args);

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $args["group"],
		));
		
		// parse the properties - resolve generated properties and
		// do any callbacks
		$resprops = $this->parse_properties(array(
			"properties" => &$realprops,
		));
		
		$cli = get_instance("cfg/" . $this->output_client);

		if (is_array($this->layout))
		{
			foreach($this->layout as $key => $val)
			{
				if (is_array($val))
				{
					$_tmp["caption"] = $val["caption"];
					foreach($val["items"] as $item)
					{
						$_tmp["items"][] = $resprops[$item];
					};

				}
				else
				{
					$_tmp = $resprops[$key];
				};
				
				$cli->add_property($_tmp);
			};
		}
		else
		{
			foreach($resprops as $val)
                        {
                                $cli->add_property($val);
                        };
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
			),
		));

		$content = $cli->get_result();

		return $this->gen_output(array(
			"parent" => $parent,
			"content" => $content,
		));
	}

	////
	// !Generate a form for adding or changing an object
	// id _always_ refers to the objects table. Always. If you want to load
	// any other data, then you'll need to use other field name
	function change($args = array())
	{
		// create an instance of the class servicing the object ($this->inst)
		// create an instance of the datasource ($this->ds)
		// set $this->clid and $this->clfile (Do I need the latter at all?)
		$this->init_class_base();
		
		extract($args);

		$this->id = $id;
		
		// get a list of active properties for this object
		// I need an easy way to turn off individual properties
		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $args["group"],
		));

		// load the object data, if there is anything to load at all
		foreach($this->tables as $key => $val)
		{
			// that we already got
			if ($key != "objects")
			{
				if ($val["master_table"] == "objects")
				{
					$id_arg = $args["id"];
				};
		
				$tmp = $this->load_object(array(
					"table" => $key,
					"idfield" => $val["index"],
					"id" => $id_arg,
					"fields" => $this->fields[$key],
				));
				$this->data[$key] = $tmp;
				$this->objdata = $tmp;
			}
			else
			{
				// load the core data (cause the parent might have a configuration
				// form set) and then I need to know the id of the parent, before
				// I can fetch all the properties
				$fields = $this->fields[$key];
				// for objects, we always load the parent field as well
				$fields["parent"] = "direct";
				$tmp = $this->load_object(array(
					"id" => $args["id"],
					"table" => "objects",
					"idfield" => "oid",
					"fields" => $fields,
				));
				$tmp["oid"] = $this->id;
				$this->data[$key] = $tmp;
				$this->parent = $tmp["parent"];
				$this->coredata = $tmp;
			};
		};

		// parse the properties - resolve generated properties and
		// do any callbacks
		$resprops = $this->parse_properties(array(
			"properties" => &$realprops,
		));

		// so now I have a list of properties along with their values,
		// and some information about the layout - and I want to display
		// that stuff now
		
		// here be some magic to determine the correct output client
		// this means we could create a TTY client for AW :)
		// actually I'm thinking of native clients and XML-RPC
		// output client is probably the first that should be
		// implemented.
		$cli = get_instance("cfg/" . $this->output_client);

		if (is_array($this->layout))
		{
			foreach($this->layout as $key => $val)
			{
				$_tmp = $resprops[$key];
				if (is_array($_tmp))
				{
					$cli->add_property($_tmp);
				};
			};
		}
		else
		{
			foreach($resprops as $val)
			{
				$cli->add_property($val);
			};
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
			)+(is_array($extraids) ? array('extraids' => $extraids) : array()),
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
		// check whether this current class is based on class_base
		$this->init_class_base();

		// right now, this callback is not used.
		if (method_exists($this->inst,"callback_on_submit"))
		{
			// nb! the handler gets quoted data

			$this->inst->callback_on_submit(array(
				"id" => $this->id,
				"form_data" => &$args,
			));
		}
		
		extract($args);

		// here we need to check whether this record really belongs to the objects
		// table or not
		
		$realprops = $this->get_active_properties(array(
			"clfile" => $this->clfile,
			"group" => $args["group"],
		));

		if (!$id)
		{
			// create the object, if it wasn't already there
			$id = $this->ds->ds_new_object(array(),array(
					"parent" => $parent,
					"name" => $name,
					"comment" => $comment,
					"period" => $period,
					"class_id" => $this->clid,
					"alias" => $alias,
					"status" => isset($status) ? $status : 1,
			));

			$this->new = true;
		}
		$this->id = $id;
		$fields = $this->fields["objects"];
		// for objects, we always load the parent field as well
		$fields["parent"] = "direct";
		$tmp = $this->load_object(array(
			"id" => $args["id"],
			"table" => "objects",
			"idfield" => "oid",
			"fields" => $fields,
		));

		$this->coredata = $tmp;

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

		$this->load_object(array("id" => $id));

		$resprops = array();
		$savedata = array();
		$form_data = $args;

		foreach($realprops as $property)
		{
                        //if (is_array($property))
                        //{
                                // this return the old/saved value of the property
                         //       $this->get_value(&$property);
                                // new data is in $args
                        //};

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
                                // I need a way to let set_property insert
                                // data back into the save queue
                                $status = $this->inst->set_property($argblock);
                        }
                        else
                        {
                                $status = PROP_OK;
                        };

                        // move the data into save queue only of set_property
                        // returns nothing

			// move the data into save queue only of set_property
			// returns nothing
			if ($status == PROP_OK)
			{
				// that is not set for checkboxes
				$xval = ($form_data[$property["name"]]) ? $form_data[$property["name"]] : "";
				if (($property["type"] == "checkbox") && ($property["method"] != "serialize"))
				{
					$xval = (int)$xval;
				};

				$savedata[$property["name"]] = $xval;
				$resprops[] = $property;
			};

		};

//                if (sizeof($savedata) == 0)
//                {
//                        die("Nothing to save! Error in the code?<br>");
//                };

		load_vcl('date_edit');
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
			if ($type == "date_select")
			{
				// turn the array into a timestamp
				$savedata[$name] = date_edit::get_timestamp($savedata[$name]);
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

		$period = aw_global_get("period");
		if ($period)
		{
			$coredata["period"] = $period;
		};
		
		if (method_exists($this->inst,"callback_pre_save"))
		{
			$this->inst->callback_pre_save(array(
				"id" => $this->id,
				"coredata" => &$coredata,
				"objdata" => &$objdata,
				"form_data" => &$args,
				"object" => array_merge($this->coredata,$this->objdata),
			));
		}

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

		$args = array("id" => $id,"group" => $group,"period" => aw_global_get("period")) + (is_array($extraids) ? $extraids : array());
		return $this->mk_my_orb("change",$args,get_class($this->orb_class));
	}

	////
	// !This checks whether we have all required data and sets up the correct
	// environment if so.
	function init_class_base()
	{
		// only classes which have defined properties
		// can use class_base
		$cfgu = get_instance("cfg/cfgutils");
		$has_properties = $cfgu->has_properties(array("file" => get_class($this->orb_class)));
		if (!$has_properties)
		{
			die("this class does not have any defined properties ");
		};

		// some day I might want to be able to edit remote objects
		// and this is how I will do it (unless I get a better idea)
		$this->ds = get_instance("datasource/" . $this->ds_name);

		$clid = $this->orb_class->get_opt("clid");
		$clfile = $this->cfg["classes"][$clid]["file"];

		// temporary - until we are sure that will will not go back to
		// the old interface
		if ($clid == 1)
		{
			$clfile = "menu";
		};

		// temporary - until we switch document editing back to new interface
		if ($clid == 7)
		{
			$clfile = "doc";
		};

		if (!$clfile)
		{
			die("coult not identify object " . $this->clfile);
		};

		$this->clfile = $clfile;
		$this->clid = $clid;
		
		// get an instance of the class that handles this object type
		$this->inst = get_instance($clfile);
	}

	function gen_output($args = array())
	{
		// XXX: figure out a way to do better titles
		$classname = get_class($this->orb_class);

		// XXX: pathi peab htmlclient tegema
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

		// let the class specify it's own title
		if (method_exists($this->inst,"callback_gen_path"))
		{
			$title = $this->inst->callback_gen_path(array(
				"id" => $this->id,
				"parent" => $args["parent"],
				"object" => $this->coredata,
			));
		};

		$this->mk_path($parent,$title,aw_global_get("period"));

		$grpnames = new aw_array($this->groupnames);
		
		// tabpanel really should be in the htmlclient too
		$this->tp = get_instance("vcl/tabpanel");

		// I need a way to let the client (the class using class_base to
		// display the editing form) to add it's own tabs.

		$activegroup = ($this->activegroup) ? $this->activegroup : $this->group;

		foreach($grpnames->get() as $key => $val)
		{
			if ($this->id)
			{
				$link = $this->mk_my_orb("change",array("id" => $this->id,"group" => $key),get_class($this->orb_class));
			}
			else
			{
				$link = "#";
			};

			if (!$this->classinfo["hide_tabs"])
			{
				$this->tp->add_tab(array(
					"link" => $link,
					"caption" => $val,
					"active" => ($key == $activegroup),
				));
			};
		};
		
		if ($this->id && $this->classinfo["relationmgr"])
		{
			$link = $this->mk_my_orb("list_aliases",array("id" => $this->id),get_class($this->orb_class));
			$this->tp->add_tab(array(
				"link" => $link,
				"caption" => "Seostehaldur",
				"active" => ( ($this->action == "list_aliases") || ($this->action == "search_aliases") ),
			));
		};

		$vars = array();
		if ($this->classinfo["toolbar"])
		{
			$this->gen_toolbar();
			$vars = array(
				"toolbar" => $this->toolbar,
			);
		};

		$vars["content"] = $args["content"];

		return $this->tp->get_tabpanel($vars);
	}

	////
	// !Loads the core data
	function load_coredata($args = array())
	{
		$this->coredata = $this->ds->ds_get_object(array(
			"id" => $args["id"],
			"class_id" => $this->clid,
		));

		$this->id = $this->coredata["oid"];
		$this->parent = $this->coredata["parent"];
	}

	// Loads an object. Any object
	function load_object($args = array())
	{
		$tmp = $this->ds->ds_get_object(array(
			"id" => $args["id"],
			"table" => $args["table"],
			"idfield" => $args["idfield"],
			"fields" => $args["fields"],
		));
		return $tmp;
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

	////
	// !Returns a list of properties for generating an output
	// or saving data. 
	function get_active_properties($args = array())
	{
		
		// load all properties for the current class
		$cfgu = get_instance("cfg/cfgutils");
		$all_props = $cfgu->load_properties(array(
			"clid" => $this->clid,
		));

		// first I need to collect the names of all groups, e.g.
		// reorder the $all_props list based on group name
		$bygroup = array();

		foreach($all_props as $key => $val)
		{
			$bygroup[$val["group"]][$key] = $val;
		};

		// loads all properties for this class
		$this->classinfo = $cfgu->get_classinfo();
		$groupinfo = new aw_array($cfgu->get_opt("groupinfo"));
		$this->tableinfo = $cfgu->get_opt("tableinfo");

		if ($args["group"] && $bygroup[$args["group"]])
		{
			$use_group = $args["group"];
		}
		else
		{
			foreach($groupinfo->get() as $gkey => $ginfo)
			{
				if ($ginfo["default"])
				{
					$use_group = $gkey;
				};
			};
		};

		if (!$use_group)
		{
			$use_group = "general";
		};

		$this->activegroup = $use_group;

		$property_list = $bygroup[$use_group];
		// loads all properties for this class

		$_all_props = $all_props;

		// This concept of core fields sucks beams through a garden hose
		$corefields = $this->get_visible_corefields();
		
		// if any configuration form applies to the current object,
		// then load it
		$cfgform_id = (int)$this->get_active_cfgform();

		$this->cfgform_id = $cfgform_id;

		// the thing is - if there is a configuration form defined,
		// then all layout information should be loaded from that form
		// that includes the name of forms

		if ($cfgform_id)
		{
			$cfgform = $this->get_object(array(
				"oid" => $cfgform_id,
				"class_id" => CL_CFGFORM,
			));

			// XXX: property_list should be an array of
			// keys, not key => 1 pairs

			// oh, and I need to deal with groups in here as well

			// btw, some clients will probably want the contents
			// of all tabs
			$property_list = $cfgform["meta"]["property_list"];
			$layout = $cfgform["meta"]["layout"];
			$groupinfo = array();
			// I need to create a flat property list from all the stuff in the layout
			if (!$property_list)
			{
				$this->default_group = "";
				foreach($layout as $key => $val)
				{
					if (!$this->default_group)
					{
						$this->default_group = $key;
					};

					$groupinfo[$key] = array(
						"caption" => $val["caption"],
					);

					foreach($val as $skey => $items)
					{
						if (is_array($items))
						{
							foreach($items as $item)
							{
								$property = $item;
								$name = $item["name"];
								$property = $all_props[$name];
								$property["group"] = $key;
								$property_list[$name] = $property;
							};
						};
					};
				};

			}
		}
		else
		{
			// I need names of all group and the contents of active group
			// group means the contents of a tab
			foreach($all_props as $key => $val)
			{
				$use = true;
				$name = $val["name"];
			};
		};

		$retval = array();

		$this->groupnames = array();

		// ok, first add all the generated props to the props array 
		$all_props = array();
		foreach($_all_props as $k => $val)
		{
			if ($val["type"] == "generated" && method_exists($this->inst,$val["generator"]))
			{
				$meth = $val["generator"];
				$vx = new aw_array($this->inst->$meth($argblock));
				foreach($vx->get() as $vxk => $vxv)
				{
					$all_props[$vxk] = $vxv;
				}
			}
			else
			{
				$all_props[$k] = $val;
			}
		}

		// now, cycle over all the properties and do all necessary filtering
		foreach($groupinfo as $key => $val)
		{
			$groupnames[$key] = $val["caption"];
		};
			
		$this->groupnames = $groupnames;

		$tables = array();

		foreach($property_list as $key => $val)
		{
			$property = $all_props[$key];

			if ($property_list[$key]["caption"])
			{
				$property["caption"] = $property_list[$key]["caption"];
			};

			$grpid = ($property["group"]) ? $property["group"] : $this->default_group;

			if ($val["group"])
			{
				$grpid = $val["group"];
			};

			if ($grpid == $use_group)
			{
				$retval[$key] = $property;
			};

			if ($groupinfo[$grpid])
			{
				$grpname = $groupinfo[$grpid]["caption"];
			}
			else
			{
				$grpname = $grpid;
			};

			// figure out information about the table
			if (!$tables[$property["table"]])
			{
				$tables[$property["table"]] = $this->tableinfo[$property["table"]];
			};

			$fval = ($property["method"]) ? $property["method"] : "direct";
			$_field = $property["field"];
			if ($_field == "meta")
			{
				$_field = "metadata";
			};
			$fields[$property["table"]][$_field] = $fval;

		};

		$this->tables = $tables;
		$this->fields = $fields;

		if (is_array($layout[$activegroup]["items"]))
		{
			$idx = $activegroup;
		}
		else
		{
			$idx = $this->default_group;
		};

		if (is_array($layout[$idx]["items"]))
		{
			$this->layout = $layout[$idx]["items"];
		};

		return $retval;
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
			$val["options"] = $this->list_objects(array(
				"class" => CL_CFGFORM,
				"subclass" => ($class_id) ? $class_id : "",
				"addempty" => true,
			));
			$val["type"] = "select";
		};

		if (($val["type"] == "aliasmgr"))
		{
			$link = $this->mk_my_orb("list_aliases",array("id" => $this->id),"aliasmgr");
			$val["value"] = "<iframe width='100%' height='800' frameborder='0' src='$link'></iframe>";
			$val["type"] = "";
			$val["caption"] = "";
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
		$field = trim(($property["field"]) ? $property["field"] : $property["name"]);
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
		$retval = false;
		if ($this->clid == CL_DOCUMENT)
		{
			// first, check the parent menu
			if ($this->parent)
			{
				$parobj = $this->get_object($this->parent);
				$retval = (int)$parobj["meta"]["tpl_edit_cfgform"];
			};

		};
		if ($this->clid == CL_PSEUDO)
		{
			if ($this->parent)
			{
				$parobj = $this->get_object($this->parent);
				$retval = (int)$parobj["meta"]["cfgmanager"];
			};
		};
		return $retval;
	}

	function parse_properties($args = array())
	{
		$properties = &$args["properties"];
	
		// I really doubt that get_property appears out of blue
		// while we are generating the output form
		$callback = method_exists($this->inst,"get_property");
		
		// need to cycle over the property nodes, do replacements
		// where needed and then cycle over the result and generate
		// the output
		$resprops = array();

		$argblock = array(
			"obj" => &$this->coredata,
			"objdata" => &$this->objdata,
		);

                foreach($properties as $key => $val)
                {
			$name = $val["name"];
                        if (is_array($val))
                        {
                                $this->get_value(&$val);
                        };

			$argblock["prop"] = &$val;

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
			if ( ($val["editonly"] == 1) && !$this->id)
			{
				// do nothing
			}
			else
			// this doesn't let me use layout
			if ($val["generator"] && method_exists($this->inst,$val["generator"]))
			{
				$meth = $val["generator"];
				$vx = $this->inst->$meth($argblock);
				if (is_array($vx))
				{
					foreach($vx as $ekey => $eval)
					{
						$resprops[$ekey] = $eval;
					};
//                                        $resprops[$name]["items"] = $vx;
				}
			}
			elseif ($val["type"] == "hidden")
			{
				// do nothing
			}
                        else
                        {
				$this->convert_element(&$val);
                                $resprops[$name] = $val;
                        };
                }
		return $resprops;
	}

	////
	// !Loads and parses a configuration form
	// returns the layout array of config form
	// id(int) - id of the config form
	// type(string) - layout|list - type of data to return
	function parse_cfgform($args = array())
	{
		$id = (int)$args["id"];
		$retval = false;
		if ($id)
		{
                        $cfgform = $this->get_object($id);

			$this->cfgform_id = $id;

			// XXX: false means that no filtering should be done
			// but I think that this should really be decided by the ini file
			// maybe it's better if we are "locked down" by default
			$this->active_properties = false;

			$proplist = array_keys($cfgform["meta"]["ord"]);

			$this->active_properties = array_flip($proplist);

			if ( is_array($cfgform["meta"]["property_list"]) )
			{
				$retval = $cfgform["meta"]["property_list"];
			};

			if ( is_array($cfgform["meta"]["layout"]) )
			{
				$this->layout = $cfgform["meta"]["layout"];
			};

			$this->property_order = false;

			if (is_array($cfgform["meta"]["ord"]))
			{
				$this->property_order = $cfgform["meta"]["ord"];
			};
		};
		return $retval;
	}

	function gen_toolbar($args = array())
	{
		$toolbar = get_instance("toolbar");
		if (method_exists($this->inst,"callback_get_toolbar"))
		{
			$this->inst->callback_get_toolbar(array(
				"toolbar" => &$toolbar,
				"id" => $this->id,
			));
		};
		if ($this->cfgform_id)
		{
			$ac = $this->get_object($this->cfgform_id);
			$toolbar->add_cdata(html::href(array(
				"url" => $this->mk_my_orb("change",array("id" => $this->cfgform_id),"cfgform"),
				"caption" => "Aktiivne konfivorm: " . $ac["name"],
				"target" => "_blank",
			)));
		};
		$this->toolbar = $toolbar->get_toolbar();
		$this->toolbar2 = $toolbar->get_toolbar(array("id" => "bottom"));


	}

	// wrappers for alias manager

	////
	// !Displays alias manager inside the configuration manager interface
	// this means I have to generate a list of group somewhere
	function list_aliases($args = array())
	{
		extract($args);
		$this->init_class_base();

		$this->action = $action;
		$this->load_coredata(array(
			"id" => $args["id"],
		));

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
		$this->init_class_base();

		$this->action = $action;
		$this->load_coredata(array(
			"id" => $args["id"],
		));

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
