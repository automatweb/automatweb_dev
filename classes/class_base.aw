<?php
// $Id: class_base.aw,v 2.99 2003/04/23 16:07:25 duke Exp $
// Common properties for all classes
/*
	@default table=objects
	@default corefield=yes
	@default group=general

	@property name type=textbox group=general
	@caption Nimi

	@property comment type=textbox group=general
	@caption Kommentaar

	@property status type=status group=general
	@caption Staatus

	@groupinfo general caption=Üldine default=1
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

// reltypes starting from id-s with 100 are reserved and should not be used
// anywhere else

// a special type for relations - link. This can be used to create links
// between objects - defining "link" is left to the owning class of the
// object. Basically it's just like "alias" but the alias textbox is 
// _not_ show in the relation manager
define('RELTYPE_LINK',100);

// link to the config form that was used to create the object
define('RELTYPE_CFGFORM',101);

class class_base extends aw_template
{
	function class_base($args = array())
	{
		$this->init("");
	}

	function init($arg = array())
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

		// if you give a id of cfgform as an argument to the new action, then
		// we attempt to use that cfgform to generate the screen
		if (!empty($args["cfgform"]))
		{	
			$this->validate_cfgform($args["cfgform"]);
		};

		$this->parent = $args["parent"];
		extract($args);

		$this->id = "";

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => isset($args["group"]) ? $args["group"] : "",
				"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
		));

		$this->request = $args;

		// parse the properties - resolve generated properties and
		// do any callbacks

		$resprops = $this->parse_properties(array(
			"properties" => &$realprops,
		));

		$cli = get_instance("cfg/" . $this->output_client);

		foreach($resprops as $val)
		{
			$cli->add_property($val);
		};

		$orb_class = $this->cfg["classes"][$this->clid]["file"];

		if ($orb_class == "document")
		{
			$orb_class = "doc";
		};
	
		$argblock = array(
			"group" => isset($group) ? $group : "",
			"orb_class" => $orb_class,
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
			"parent" => $parent,
			"period" => isset($period) ? $period : "",
			"alias_to" => isset($this->request["alias_to"]) ? $this->request["alias_to"] : "",
			"reltype" => isset($this->request["reltype"]) ? $this->request["reltype"] : "",
			"cfgform" => isset($this->cfgform_id) ? $this->cfgform_id : "",
			"return_url" => isset($this->request["return_url"]) ? urlencode($this->request["return_url"]) : "",
		);

		if (method_exists($this->inst,"callback_mod_reforb"))
		{
			$this->inst->callback_mod_reforb(array(
				"args" => &$argblock,
			));
		};

		$cli->finish_output(array(
			"action" => "submit",
			"data" => $argblock,
		));

		$content = $cli->get_result();

		return $this->gen_output(array(
			"parent" => $parent,
			"content" => $content,
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
		));
	}

	////
	// !Generate a form for adding or changing an object
	// id _always_ refers to the objects table. Always. If you want to load
	// any other data, then you'll need to use other field name
	function change($args = array())
	{
		$this->init_class_base();

		extract($args);
			
		// I need to get that stuff working in situations where there is
		// no id .. login forms, and stuff like that
		$this->id = $id;
		$obj = $this->get_object($this->id);

		$this->validate_cfgform($obj["meta"]["cfgform_id"]);

		// get a list of active properties for this object
		// I need an easy way to turn off individual properties
		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => isset($args["group"]) ? $args["group"] : "",
				"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",

		));

		$this->load_obj_data(array("id" => $this->id));

		if (method_exists($this->inst,"callback_pre_edit"))
		{
			$this->inst->callback_pre_edit(array(
				"id" => $this->id,
				"coredata" => &$this->coredata,
			));

		};


		$this->request = $args;

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

		foreach($resprops as $val)
		{
			$cli->add_property($val);
		};

		$orb_class = $this->cfg["classes"][$this->clid]["file"];

		if ($orb_class == "document")
		{
			$orb_class = "doc";
		};

		$gdata = $this->groupinfo->get_at($this->activegroup);

		$argblock = array(
			"id" => $id,
			"group" => isset($group) ? $group : "",
			"orb_class" => $orb_class,
			"parent" => isset($parent) ? $parent : "",
			"period" => isset($period) ? $period : "",
			"cb_view" => isset($cb_view) ? $cb_view : "",
			"alias_to" => isset($this->request["alias_to"]) ? $this->request["alias_to"] : "",
			"return_url" => isset($this->request["return_url"]) ? urlencode($this->request["return_url"]) : "",
			"subgroup" => isset($this->request["subgroup"]) ? $this->request["subgroup"] : "",
		) + (isset($extraids) && is_array($extraids) ? array('extraids' => $extraids) : array());

		if (method_exists($this->inst,"callback_mod_reforb"))
		{
			$this->inst->callback_mod_reforb(&$argblock);
		};

		$cli->finish_output(array(
			"action" => "submit",
			"submit" => isset($gdata["submit"]) ? $gdata["submit"] : "",
			"data" => $argblock,
		));

		if (empty($content))
		{
			$content = $cli->get_result();
		};

		return $this->gen_output(array(
			"parent" => isset($parent) ? $parent : "",
			"content" => isset($content) ? $content : "",
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
		));
	}

	////
	// !Saves the data that comes from the form generated by change
	function submit($args = array())
	{
		// check whether this current class is based on class_base
		$this->init_class_base();

		$this->quote($args);
		extract($args);

		// I need to know the id of the configuration form, so that I 
		// can load it. Reason being, the properties can be grouped
		// differently in the config form then they are in the original
		// properties
		if (isset($id))
		{
			$_tmp = $this->get_object($id);
			$cgid = 0;
			if (isset($_tmp["meta"]["cfgform_id"]))
			{
				$cgid = $_tmp["meta"]["cfgform_id"];
			};
		}
		else
		{
			$cgid = isset($args["cfgform"]) ? $args["cfgform"] : 0;
		};

		$this->validate_cfgform($cgid);

		$this->id = isset($id) ? $id : "";

		
		// get the list of properties in the active group
		// actually, it does a little more than dat, it also
		// sorts the data into different variables
		$realprops = $this->get_active_properties(array(
			"clfile" => $this->clfile,
			"group" => $args["group"],
			"cb_view" => $args["cb_view"],
		));

		// now, in embedded cases, we don't want to create any objects,
		// I just want to get the information sorted by tables.

		// now, how do I figure out whether we want to save an object
		// or just use the form interactively.

		if (empty($id))
		{
			// create the object, if it wasn't already there

			// I need a nice elegant way to override the parent 
			// so that added objects can land where I want them 
			// to land. 
			$id = $this->ds->ds_new_object(array(),array(
					"parent" => $parent,
					"name" => isset($name) ? $name : "",
					"comment" => isset($comment) ? $comment : "",
					"period" => isset($period) ? $period : "",
					"class_id" => $this->clid,
					"alias" => isset($alias) ? $alias : "",
					"status" => isset($status) ? $status : 1,
			));

			if ($alias_to)
			{
				$almgr = get_instance("aliasmgr");
				$almgr->create_alias(array(
					"alias" => $id,
					"id" => $alias_to,
					"reltype" => $reltype,
				));
			};

			$this->new = true;
			$this->id = $id;
		}

		$fields = $this->fields["objects"];
		// for objects, we always load the parent field as well
		$fields["parent"] = "direct";
		$fields["metadata"] = "serialize";
		$tmp = $this->load_object(array(
			"id" => $this->id,
			"table" => "objects",
			"idfield" => "oid",
			"fields" => $fields,
		));

		$tmp["oid"] = $this->id;

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
		
		load_vcl('date_edit');


		foreach($realprops as $property)
		{
			// do not call set_property for edit_only properties when a new
			// object is created.
			if ($this->new && isset($property["editonly"]))
			{
				continue;
			};

			// that is not set for checkboxes
			$xval = (isset($form_data[$property["name"]])) ? $form_data[$property["name"]] : "";
			if (($property["type"] == "checkbox") && ($property["method"] != "serialize"))
			{
				// set value to 0 for unchecked checkboxes, which are not to be saved
				// into metainfo. 
				$xval = (int)$xval;
			};

			$property["value"] = $xval;
                        
			$argblock = array(
                                "prop" => &$property,
                                "obj" => &$this->coredata,
                                "objdata" => &$this->objdata,
				"metadata" => &$metadata,
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
                        // returns PROP_OK
			if ($status == PROP_OK)
			{
				$name = $property["name"];

				$savedata[$name] = ($property["value"]) ? $property["value"] : $xval;
		
				$table = isset($property["table"]) ? $property["table"] : "";
				$field = $property["field"];
				$method = $property["method"];
				$type = $property["type"];
				if ($type == "text")
				{
					continue;
				};

				if ($property["store"] == "no")
				{
					continue;
				};

				if ($type == "date_select")
				{
					// turn the array into a timestamp
					$savedata[$name] = date_edit::get_timestamp($savedata[$name]);
				};
				if (($type == "select") && isset($property["multiple"]))
				{
					$savedata[$name] = $this->make_keys($savedata[$name]);
				};
				if ($type == "imgupload")
				{
					if (isset($form_data["del_" . $name]))
					{
						$metadata[$name . "_id"] = 0;
						$metadata[$name . "_url"] = "";
					}
					else
					{
						// upload the bloody image.
						$t = get_instance("image");
						$key = $name . "_id";
						$oldid = (int)$this->coredata["meta"][$key];
						$ar = $t->add_upload_image($name, $this->coredata["parent"], $oldid);
						$metadata[$key] = $ar["id"];
						$key = $name . "_url";
						$metadata[$key] = image::check_url($ar["url"]);
					};
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
						$_field = ($name != $field) ? $field : $name;
						$objdata[$table][$_field] = $savedata[$name];
					};
				};
			};
		};


		if (sizeof($metadata) > 0)
		{
			$coredata["metadata"] = $metadata;
		};


		// I only want to call those functions below this line in case I'm saving an actual object
		// otherwise the caller will probably want to do something else with the data I gathered
		// from here. He probably needs only saveadata anyway, since what good is that stuff
		// sorted into separate arrays by the table
		$coredata["id"] = $id;

		$period = aw_global_get("period");
		if ($period)
		{
			$coredata["period"] = $period;
		};

		// ex-fucking-xactly, I need another way to parse the data that enters from the form,
		// and get back the contens of coredata and objdata
		
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

		// it is set (or not) on validate_cfgform
		if ($this->cfgform_id)
		{
			$coredata["metadata"]["cfgform_id"] = $this->cfgform_id;
		};

		$this->ds->ds_save_object(array("id" => $id,"clid" => $this->clid),$coredata);

		$this->save_object(array("data" => $objdata));

		if (method_exists($this->inst,"callback_post_save"))
		{
			$this->inst->callback_post_save(array("id" => $this->id));
		}

		$this->log_obj_change();

		$args = array(
			"id" => $id,
			"group" => $group,
			"period" => aw_global_get("period"),
			"alias_to" => $form_data["alias_to"],
			"return_url" => $form_data["return_url"],
			"cb_view" => $form_data["cb_view"],
		) + ( (isset($extraids) && is_array($extraids)) ? $extraids : array());

		$action = "change";
		$orb_class = get_class($this->orb_class);

		//$this->sync_object();
		if (method_exists($this->inst,"callback_mod_retval"))
		{
			$this->inst->callback_mod_retval(array(
				"action" => &$action,
				"args" => &$args,
				"form_data" => &$form_data,
				"orb_class" => &$orb_class,
			));
		};
		// rrrr, temporary hack
		if (isset($this->id_only))
		{
			$retval = $id;
		}
		else
		{
			$retval = $this->mk_my_orb($action,$args,$orb_class);
		};
		return $retval;
	}

	////
	// ! Log the action
	function log_obj_change()
	{
		$name = isset($this->coredata["name"]) ? $this->coredata["name"] : "";

		$syslog_type = ST_CONFIG;
		if (isset($this->classinfo['syslog_type']))
		{
			$syslog_type = constant($this->classinfo['syslog_type']['text']);
		}

		// XXX: if I want to save data that does not belong to 
		// objects table, then I don't want to log it like this --duke
		if (isset($this->new))
		{
			$this->_log($syslog_type, SA_ADD, $name, $this->id);
		}
		else
		{
			$this->_log($syslog_type, SA_CHANGE, $name, $this->id);
		};
	}

	function validate_cfgform($id = false)
	{
		// try to load the bastard
		$this->cfgform_id = 0;
		$this->cfgform = array();
		if ($id)
		{
			$_tmp = $this->get_object(array(
				"oid" => $id,
				"class" => CL_CFGFORM,
				"subclass" => $this->clid,
			));

			if ($_tmp)
			{
				$this->cfgform_id = $_tmp["oid"];
				$this->cfgform = $_tmp;
			};
		};
	}
	
	////
	// !Processes form data
	function process_form_data($args = array())
	{
		// check whether this current class is based on class_base
		$this->init_class_base();
		
		// get the list of properties in the active group	
		// actually, it does a little more than dat, it also
		// sorts the data into different variables
		$realprops = $this->get_active_properties(array(
			"classonly" => $args["classonly"],
			"clfile" => $this->clfile,
			"group" => $args["group"],
			"content" => $args["content"],
		));


		if (isset($args["group_by"]))
		{
			// right now this is the only group option I support anyway
			$group_by = "table";

		};

		$savedata = array();

		$form_data = $args["form_data"];

		load_vcl('date_edit');

		foreach($realprops as $property)
		{
			// that is not set for checkboxes
			$xval = (isset($form_data[$property["name"]])) ? $form_data[$property["name"]] : "";
			if (($property["type"] == "checkbox") && ($property["method"] != "serialize"))
			{
				// set value to 0 for unchecked checkboxes, which are to be saved
				// into metainfo. Dunno about usual fields.
				$xval = (int)$xval;
			};
			
			$name = $property["name"];

			$val = $xval;
		
			$table = $property["table"];
			$field = $property["field"];
			$method = $property["method"];
			$type = $property["type"];
			if (($type == "text") || ($type == "callback") || ($property["store"] == "no"))
			{
				continue;
			};
			if ($type == "date_select")
			{
				// turn the array into a timestamp
				$val = date_edit::get_timestamp($val);
			};
			if (($type == "select") && $property["multiple"])
			{
				$val = $this->make_keys($savedata[$name]);
			};

			if (isset($group_by))
			{
				if ($method == "serialize")
				{
					$savedata[$property[$group_by]][$field][$name] = $val;
				}
				else
				{
					$savedata[$property[$group_by]][$name] = $val;
				};
			}
			else
			{
				$savedata[$name] = $val;
			};
		};
		// yah, but it would be rather nice, if I could let this same function
		// handle the saving as well
		return $savedata;
	}


	////
	// !This checks whether we have all required data and sets up the correct
	// environment if so.
	function init_class_base()
	{
		// only classes which have defined properties
		// can use class_base
		
		// create an instance of the class servicing the object ($this->inst)
		// create an instance of the datasource ($this->ds)
		// set $this->clid and $this->clfile
		$cfgu = get_instance("cfg/cfgutils");
		$orb_class = $this->cfg["classes"][$this->clid]["file"];
		if (empty($orb_class))
		{
			$orb_class = get_class($this->orb_class);
		};

		if ($orb_class == "document")
		{
			$orb_class = "doc";
		};

		$has_properties = $cfgu->has_properties(array("file" => $orb_class));
		if (empty($has_properties))
		{
			die(sprintf("this class (%s) does not have any defined properties ",$orb_class));
		};

		// some day I might want to be able to edit remote objects
		// and this is how I will do it (unless I get a better idea)
		$this->ds = get_instance("datasource/" . $this->ds_name);

		$clid = $this->clid;
		if (empty($clid))
		{
			$clid = $this->orb_class->get_opt("clid");
		};
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

		if (empty($clfile))
		{
			die("coult not identify object " . $this->clfile);
		};

		$this->clfile = $clfile;
		$this->clid = $clid;
		
		// get an instance of the class that handles this object type
		$this->inst = get_instance($clfile);
	}

	function load_obj_data($args = array())
	{	
		// load the object data, if there is anything to load at all
		// but if no tables are defined, then it seems we don't load anything at all
		if (!is_array($this->tables))
		{
			return false;
		};

		foreach($this->tables as $key => $val)
		{
			// that we already got
			if (($key != "objects") && isset($this->realfields[$key]) && (sizeof($this->realfields[$key]) > 0) )
			{
				if ($val["master_table"] == "objects")
				{
					$id_arg = $args["id"];
				};

				$tmp = $this->load_object(array(
					"table" => $key,
					"idfield" => $val["index"],
					"id" => $id_arg,
					"fields" => $this->realfields[$key],
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
				$fields["metadata"] = "serialize";
				$tmp = $this->load_object(array(
					"id" => $args["id"],
					"table" => "objects",
					"idfield" => "oid",
					"fields" => $fields,
				));
				$tmp["oid"] = $args["id"];
				$this->data[$key] = $tmp;
				$this->parent = $tmp["parent"];
				$this->coredata = $tmp;
			};
		};

	}	

	function gen_output($args = array())
	{
		$classname = $this->cfg["classes"][$this->clid]["name"];

		$name = isset($this->coredata["name"]) ? $this->coredata["name"] : "";
		$return_url = isset($this->request["return_url"]) ? urlencode($this->request["return_url"]) : "";
		// XXX: pathi peaks htmlclient tegema
		$title = isset($args["title"]) ? $args["title"] : "";
		if ($this->id)
		{
			if (empty($title))
			{
				$title = "Muuda $classname objekti " . $name;
			};
			$parent = $this->coredata["parent"];
		}
		else
		{
			if (empty($title))
			{
				$title = "Lisa $classname";
			};
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

		if (isset($this->request["return_url"]))
		{
			$parent = -1;
			if (strpos($this->request["return_url"],"b1=1"))
			{
				$target = "_top";
			}
			else
			{
				$target = "_self";
			};
			$title = html::href(array(
				"url" => $this->request["return_url"],
				"caption" => "Tagasi",
				"target" => $target,
			)) . " / " . $title;
		};

		$this->mk_path($parent,$title,aw_global_get("period"));
		
		$this->tp = get_instance("vcl/tabpanel");

		$grpnames = new aw_array($this->groupnames);
		

		// I need a way to let the client (the class using class_base to
		// display the editing form) to add it's own tabs.

		$activegroup = isset($this->activegroup) ? $this->activegroup : $this->group;
		$activegroup = isset($this->action) ? $this->action : $activegroup;

		$orb_action = isset($args["orb_action"]) ? $args["orb_action"] : "";

		if (empty($orb_action))
		{
			$orb_action = "change";
		};	
			
		$link_args = new aw_array(array(
			"id" => isset($this->id) ? $this->id : false,
			"group" => "",
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
			"return_url" => $return_url,
		));
			
		if (empty($this->classinfo["hide_tabs"]))
		{
			$tab_callback = (method_exists($this->inst,"callback_mod_tab")) ? true : false;

			foreach($grpnames->get() as $key => $val)
			{
				if ($this->id)
				{
					$link_args->set_at("group",$key);
					$link = $this->mk_my_orb($orb_action,$link_args->get(),get_class($this->orb_class));
				}
				else
				{
					$link = ($activegroup == $key) ? "#" : "";
				};
				

				$tabinfo = array(
					"link" => &$link,
					"caption" => &$val,
					"id" => $key,
					"tp" => &$this->tp,
					"coredata" => $this->coredata,
				);

				$res = true;	
				if ($tab_callback)
				{
					$res = $this->inst->callback_mod_tab($tabinfo);
				};

				if ($res !== false)
				{
					$this->tp->add_tab(array(
						"link" => $tabinfo["link"],
						"caption" => $tabinfo["caption"],
						"active" => ($key == $activegroup),
					));
				};
			};
		};
		
		if (isset($this->classinfo["relationmgr"]) && empty($this->request["cb_view"]))
		{
			$link = "";
			if (isset($this->id))
			{
				$link = $this->mk_my_orb("list_aliases",array("id" => $this->id,"return_url" => $return_url),get_class($this->orb_class));
			};
			$this->tp->add_tab(array(
				"link" => $link,
				"caption" => "Seostehaldur",
				"active" => isset($this->action) && (($this->action == "list_aliases") || ($this->action == "search_aliases")),
				"disabled" => empty($this->id),
			));
		};

		$vars = array();
		if (isset($this->classinfo["toolbar"]))
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
			"table" => "objects",
			"idfield" => "oid",
			"fields" => array("oid" => "oid","parent" => "parent","name" => "name"),
		));

		$this->id = $this->coredata["oid"];
		$this->parent = $this->coredata["parent"];
	}

	// Loads an object. Any object
	function load_object($args = array())
	{
		if (empty($args["table"]))
		{
			return false;
		};

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
		$id = $this->id;
		if (!is_array($this->tableinfo))
		{
			return;
		};

		foreach($this->tableinfo as $table => $data)
		{
			$idfield = $data["index"];
			// NB! no record is created in the "other" table before we
			// actually have something to write there besides the id itself
			if (isset($table) && isset($idfield) && isset($args["data"][$table]))
			{
				// ds_save_object creates the record in the other table,
				// if it does not exist yet.
				$this->ds->ds_save_object(array(
					"table" => $table,
					"idfield" => $idfield,
					"replace" => true,
					"id" => $id),$args["data"][$table]
				);
			};	
		};	
	}

	////
	// !Returns a list of properties for generating an output
	// or saving data. 
	function get_active_properties($args = array())
	{
		// properties are all saved in a single file, so sadly
		// we do need to load them all
		$this->get_all_properties(array(
			"classonly" => isset($args["classonly"]) ? $args["classonly"] : "",
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
			"content" => isset($args["content"]) ? $args["content"] : "",
		));
		

		// figure out which group is active
		// it the group argument is a defined group, use that
		if ( $args["group"] && $this->groupinfo->key_exists($args["group"]) )
		{
			$use_group = $args["group"];
		}
		else
		{
			// otherwise try to figure out whether any of the groups
			// has been set to default, if so, use it
			foreach($this->groupinfo->get() as $gkey => $ginfo)
			{
				if (isset($ginfo["default"]))
				{
					$use_group = $gkey;
				};
			};
		};

		// and if nothing suitable was found, default to the "general" group
		if (empty($use_group))
		{
			if ($this->groupinfo->key_exists("general"))
			{
				$use_group = "general";
			}
			// otherwise, take the first group
			else
			{
				list($use_group,) = $this->groupinfo->first();
			};
		};

		$this->activegroup = $use_group;

		// get the list of all groups
		$groupnames = array();
		foreach($this->groupinfo->get() as $key => $val)
		{
			$groupnames[$key] = $val["caption"];
		};
			
		$this->groupnames = $groupnames;
		$this->cb_views = array();

		$property_list = array();

		foreach($this->all_props as $key => $val)
		{
			if (isset($val["view"]) && empty($this->cb_views[$val["view"]]))
			{
				$this->cb_views[$val["view"]] = 1;
			};
			// handle multiple groups
			if (is_array($val["group"]))
			{
				$tmp = $val;
				foreach($val["group"] as $_group)
				{
					$tmp["group"] = $_group;
					if ($_group == $this->activegroup)
					{
						$property_list[$key] = $tmp;
					};
				}
			}
			else
			{
				if ($val["group"] == $this->activegroup)
				{
					$property_list[$key] = $val;
				};
			};
		};

		$retval = $tables = $fields = $realfields = array();

		// I need to replace this with a better check if I want to be able
		// to use config forms in other situations besides editing objects

		if (!empty($this->id))
		{
			$tables["objects"] = array("index" => "oid");
		};
		
		if (isset($this->role) && ($this->role == "obj_edit"))
		{
			$tables["objects"] = array("index" => "oid");
		};
		
		foreach($property_list as $key => $val)
		{
			$property = $this->all_props[$key];

			// give it the default value to silence warnings
			$property["store"] = isset($property["store"]) ? $property["store"] : "";
			$property["field"] = isset($property["field"]) ? $property["field"] : "";
			$property["method"] = isset($property["method"]) ? $property["method"] : "direct";
			// it escapes me why a property would not have a type. but some do not. -- duke
			$property["type"] = isset($property["type"]) ? $property["type"] : "";


			if (isset($property_list[$key]["caption"]))
			{
				$property["caption"] = $property_list[$key]["caption"];
			};

			// properties with no group end up in default group
			$grpid = isset($property["group"]) ? $property["group"] : $this->default_group;

			if (isset($val["group"]))
			{
				$grpid = $val["group"];
			};

			if ($grpid == $use_group)
			{
				$retval[$key] = $property;
			};

			// figure out information about the table
			if (isset($property["table"]) && empty($tables[$property["table"]]))
			{
				if (isset($this->tableinfo[$property["table"]]))
				{
					$tables[$property["table"]] = $this->tableinfo[$property["table"]];
				}
				else
				{
					$tables[$property["table"]] = "";
				};
			};

			$fval = $property["method"];
			$_field = $property["field"];

			if ($_field == "meta")
			{
				$_field = "metadata";
			};

			if (isset($property["table"]))
			{
				if ($_field)
				{
					if (($property["type"] != "callback") && ($property["store"] != "no") )
					{
						$fields[$property["table"]][$_field] = $fval;
					};
				};
				if (($property["type"] != "text") && ($property["type"] != "callback") && ($property["store"] != "no"))
				{
					$realfields[$property["table"]][$_field] = $fval;
				};
			};

		};



		$this->tables = $tables;
		$this->fields = $fields;
		$this->realfields = isset($realfields) ? $realfields : NULL;

		$idx = $this->default_group;

		return $retval;
	}

	function get_all_properties($args = array())
	{
		// load all properties for the current class
		$cfgu = get_instance("cfg/cfgutils");
		// actually, config forms should hold a serialized form 
		// of the data, and not the raw XML source. And I should
		// validate the XML before I upload it to the object -- duke
		if (isset($this->cfgform["meta"]["cfg_proplist"]))
		{
			$proplist = $this->cfgform["meta"]["cfg_proplist"];
			$grplist = $this->cfgform["meta"]["cfg_groups"];
		}

		if ($args["content"])
		{
			$_all_props = $cfgu->parse_definition(array(
				"content" => $args["content"],
			));
		}
		else
		if ($args["classonly"])
		{
			$_all_props = $cfgu->load_class_properties(array(
				"clid" => $this->clid,
			));
		}
		else
		{
			$_all_props = $cfgu->load_properties(array(
				"clid" => $this->clid,
			));
		};
	
		$argblock = array(
			"oid" => isset($this->id) ? $this->id : "",
			"request" => isset($this->request) ? $this->request : "",
		);

		// 1) generate a list of all views
		$this->cb_views = array();
		// 2) then count the elements in all group using those which match the active group
		$group_el_cnt = array();
		// 3) skip empty groups

		$cb_view = $args["cb_view"];

		// ok, first add all the generated props to the props array 
		$this->all_props = array();

		$tmp = empty($this->cfgform_id) ? $_all_props : $proplist;

		foreach($tmp as $k => $val)
		{
			// if a config form is loaded, then ignore stuff that isn't
			// defined in there. I really shouldn't cause any problems
			// with well working code.
			if (!empty($this->cfgform_id) && empty($_all_props[$val["name"]]))
			{
				continue;
			};

			// override original property definitions with those in config form
			$orig = $val;
			if (!empty($this->cfgform_id))
			{
				$val = array_merge($_all_props[$k],$val);
				// use the default caption, if the one in config form
				// is empty. oh, and for consistency, I should do the
				// same when I save the config form
				if (empty($val["caption"]))
				{
					$val["caption"] = $_all_props[$k]["caption"];
				};	
				
				if (($_all_props[$k]["type"] == "textarea") && (empty($orig["richtext"])))
				{
					unset($val["richtext"]);
				};



			}

			if (empty($val["view"]))
			{
				$val["view"] = "";
			};

			if ($val["view"])
			{
				$this->cb_views[$val["view"]] = 1;
			};

			// list only the properties in the requested view
			if ($val["view"] != $args["cb_view"])
			{
				continue;
			};
	
			$argblock = array(
				"id" => isset($this->id) ? $this->id : "",
				"obj" => &$this->coredata,
                                "objdata" => &$this->objdata,
			);

			// generated elements count as one for this purpose
			$_grplist = is_array($val["group"]) ? $val["group"] : explode(",",$val["group"]);
			foreach($_grplist as $_grp)
			{
				if (isset($group_el_cnt[$_grp]))
				{
					$group_el_cnt[$_grp]++;
				}
				else
				{
					$group_el_cnt[$_grp] = 1;
				};
			};

			if (isset($val["type"]) && isset($val["generator"]) && ($val["type"] == "generated") && method_exists($this->inst,$val["generator"]))
			{
				$meth = $val["generator"];
				$vx = new aw_array($this->inst->$meth($argblock));
				foreach($vx->get() as $vxk => $vxv)
				{
					if (empty($vxv["group"]))
					{
						$vxv["group"] = $val["group"];
					};
					$this->all_props[$vxk] = $vxv;
				}
			}
			else
			{
				$this->all_props[$k] = $val;
			}
		}

		$this->classinfo = $cfgu->get_classinfo();
		$tmp_grpinfo = $cfgu->get_groupinfo();
		$grpinfo = array();
		if (is_array($tmp_grpinfo))
		{
			foreach($tmp_grpinfo as $key => $val)
			{
				if (in_array($key,array_keys($group_el_cnt)))
				{
					if (!empty($this->cfgform_id) && empty($grplist[$key]))
					{
						continue;
					}
					else
					{
						// grplist comes from CL_CFGFORM
						if (is_array($grplist) && isset($grplist[$key]))
						{
							$val = array_merge($val,$grplist[$key]);
						};
						$grpinfo[$key] = $val;
					};
				};
			};
		};
		$this->groupinfo = new aw_array($grpinfo);
		$this->tableinfo = $cfgu->get_opt("tableinfo");
	}

	function convert_element(&$val)
	{
		// no type? get out then
		if (empty($val["type"]))
		{
			return false;
		};

		if ($val["type"] == "date_select")
		{
			// set the date to "now" for empty date_selects
			if (empty($this->id))
			{
				$val["value"] = time();
			};
		}

		if (($val["type"] == "popup_objmgr"))
		{
			if ($val['multiple'])
			{
				if ($val['value'])
				{
				$val['value']=(array)$val['value'];
				foreach($val['value'] as $va)
				{
					$obj=$this->get_object($va);
					$options_[$va]=htmlentities($obj['name']);
				}
				$val['selected'] = $val['value'];
				$val['options'] = $options_;
				}
			}
			else
			{
				$val['selected'] = $val['value'];
				if (is_numeric($val['value']))
				{
					$obj=$this->get_object($val['value']);
				}
				$val['options'] = array($val['value']=>$obj['name'],0 => ' - ');
			}

			$val['popup_objmgr'] = $this->mk_my_orb('search',array(
				'check_name' => isset($val['check_name']) ? $val['check_name'] : NULL,
				'multiple' => $val['multiple'],
//				'check_name' => $val['check_name']
				//'parent' => 50477,
				"parent" => $this->parent,
				'return_url' => 'plaa',
				),'popup_objmgr');

		};


		if (($val["type"] == "objpicker") && isset($val["clid"]) && defined($val["clid"]))
		{
			$val["type"] = "select";
			$val["options"] = $this->list_objects(array(
				"class" => constant($val["clid"]),
				"subclass" => isset($val["subclass"]) ? constant($val["subclass"]) : "",
				"addempty" => true,
				"truncate_names" => 1,
				"add_folders" => true,
			));
		};

		if (($val["type"] == "cfgform_picker") && $val["clid"])
		{
			// now I need to figure out the list of files for thiss
			// class type
			$class_id = constant($val["clid"]);
			$val["options"] = $this->list_objects(array(
				"class" => CL_CFGFORM,
				"subclass" => isset($class_id) ? $class_id : "",
				"addempty" => true,
			));
			$val["type"] = "select";
		};

		if (($val["type"] == "aliasmgr") && isset($this->id))
		{
			$link = $this->mk_my_orb("list_aliases",array("id" => $this->id),"aliasmgr");
			$val["value"] = "<iframe width='100%' name='aliasmgr' height='800' frameborder='0' src='$link'></iframe>";
			$val["type"] = "";
			$val["caption"] = "";
		};

		if (($val["type"] == "relpicker") && isset($val["clid"]))
		{
			$this->cfgu->el_relpicker_clid(array(
				"id" => $this->target_obj,
				"val" => &$val,
			));
		};

		if (($val["type"] == "relpicker") && ($val["reltype"]))
		{
			$this->cfgu->el_relpicker_reltype(array(
				"id" => $this->target_obj,
				"meta" => $this->coredata["meta"],
				"val" => &$val,
			));
		};
	}

	////
	// !Figures out the value for property
	function get_value(&$property)
	{
		$field = trim(($property["field"]) ? $property["field"] : $property["name"]);
		$table = isset($property["table"]) ? $property["table"] : "";
		if (empty($this->id) && isset($property["default"]))
		{
			$property["value"] = $property["default"];
		}
		else
		if (isset($this->values) && is_array($this->values))
		{
			if (isset($this->values[$property["name"]]))
			{
				$property["value"] = $this->values[$property["name"]];
			};
		}
		else
		{
			if ($table == "objects")
			{
				if ($field == "meta")
				{
					if (isset($this->coredata["meta"][$property["name"]]))
					{
						$property["value"] = $this->coredata["meta"][$property["name"]];
					};
				}
				else
				{
					if (isset($this->coredata[$property["name"]]))
					{
						$property["value"] = $this->coredata[$property["name"]];
					};
				};
			}
			else
			{
				if ($property["method"] == "serialize")
				{
					if (isset($this->data[$table][$field]))
					{
						$property["value"] = aw_unserialize($this->data[$table][$field]);
					};
				}
				else
				{
					$_field = ($property["name"] != $property["field"]) ? $property["field"] : $property["name"];
					if (isset($this->data[$table][$_field]))
					{
						$property["value"] = $this->data[$table][$_field];
					};
				};
			};
		};
	}


	function parse_properties($args = array())
	{
		$properties = &$args["properties"];
		if (!is_array($properties))
		{
			return false;
		};

		if (isset($args["target_obj"]))
		{
			$this->target_obj = $args["target_obj"];
		}
		else
		{
			$this->target_obj = $this->id;
		};

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
			"request" => isset($this->request) ? $this->request : "",
		);

		$this->cfgu = get_instance("cfg/cfgutils");

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
			if ($status == PROP_ERROR)
			{
				$val["type"] = "text";
				$val["value"] = "Viga: $val[error]";
				$resprops[$key] = $val;
			}
			else
			if ( isset($val["editonly"]) && empty($this->id))
			{
				// do nothing
			}
			else
			if ($val["type"] == "aliasmgr" && empty($this->id))
			{
				// do not show alias manager if  no id
			}
			else
			if (isset($val["callback"]) && method_exists($this->inst,$val["callback"]))
			{
				$meth = $val["callback"];
				// I need a way to figure out whether that callback method
				// did actually set any groups or not?

				// and just how exactly am I going to do that?
				$vx = $this->inst->$meth($argblock);
				if (is_array($vx))
				{
					foreach($vx as $ekey => $eval)
					{
						$this->convert_element(&$eval);
						$resprops[$ekey] = $eval;
					};
				}
			}
			elseif ($val["type"] == "hidden")
			{
				// do nothing
			}
			else
			{
				$this->convert_element(&$val);
				if (empty($name))
				{
					$name = $key;
				};
				if (!empty($val["field"]) && ($name != $val["field"]) && ($val["method"] != "serialize"))
				{
					$_field = $val["field"];
				}
				else
				{
					$_field = $name;
				};
				$resprops[$_field] = $val;
			};
		}
		return $resprops;
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
		/*
		if ($this->cfgform_id)
		{
			$toolbar->add_cdata(html::href(array(
				"url" => $this->mk_my_orb("change",array("id" => $this->cfgform_id),"cfgform"),
				"caption" => "Aktiivne konfivorm: " . $this->cfgform["name"],
				"target" => "_blank",
			)));
		};
		*/
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
		$this->request = $args;

		$this->id = $args["id"];

		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		$reltypes = $this->get_rel_types();

		$gen = $almgr->list_aliases(array(
			"id" => $id,
			"reltypes" => $reltypes,
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
		$this->request = $args;
		
		$reltypes = $this->get_rel_types();
		
		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		$clid_list = array();
		if (method_exists($this->inst,"callback_get_classes_for_relation"))
		{
			$clid_list = $this->inst->callback_get_classes_for_relation(array(
				"reltype" => $reltype,
			));
		}

		$args["clid_list"] = $clid_list;

		$args["return_url"] = $this->mk_my_orb("change",array("id" => $id,"group" => $group),get_class($this->orb_class));
		$gen = $almgr->search($args + array("reltypes" => $this->get_rel_types()));
		$classname = get_class($this->orb_class);
		if (isset($reltype))
		{
			$title = "Loo seos $reltypes[$reltype] $classname objektiga " . $this->coredata["name"];
		};
		return $this->gen_output(array(
			"content" => $gen,
			"title" => $title,
		));
	}	

	////
	// !Handles the "saving" of relation list
	function submit_list($args = array())
	{
		$this->init_class_base();
		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));
		$retval = $almgr->submit_list($args);
		if (method_exists($this->inst,"callback_on_submit_relation_list"))
		{
			$this->inst->callback_on_submit_relation_list($args);
		};
		return $retval;
	}

	////
	// !Handles creating of new relations between the object and
	// selected search results
	function orb_addalias($args = array())
	{
		$this->init_class_base();
		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));
		$retval = $almgr->orb_addalias($args);
		if (method_exists($this->inst,"callback_on_addalias"))
		{
			$this->inst->callback_on_addalias($args);
		};
		return $retval;
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

	function get_rel_types()
	{
		if (method_exists($this->inst,"callback_get_rel_types"))
		{
			$reltypes = $this->inst->callback_get_rel_types();
		}
		else
		{
			$reltypes = array();
		};

		$reltypes[0] = "alias";
		return $reltypes;
	}

	////
	function view($args = array())
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
				"cb_view" => $args["cb_view"],
		));

		$this->load_obj_data(array("id" => $this->id));
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
		$cli = get_instance("cfg/htmlpreview");

		foreach($resprops as $val)
		{
			$cli->add_property($val);
		};

		$cli->finish_output(array());

		$content = $cli->get_result();
		$classname = get_class($this->orb_class);
		$title = "Vaata $classname objekti " . $this->coredata["name"];

		return $this->gen_output(array(
			"parent" => $parent,
			"content" => $content,
			"title" => $title,
			"orb_action" => "view",
		));
	}

	////
	// !Returns a list of config forms used by thiss
	function get_cfgform_list($args = array())
	{
		$this->get_objects_by_class(array(
			"class" => CL_CFGFORM,
			"subclass" => $this->clid,
			"fields" => "oid,name",
		));

		$retval = array();

		// I also have to add a list of the plain old document types here
		while ($row = $this->db_next())
		{
			$retval[$row["oid"]] = $row["name"];
		};	
		return $retval;
	}

	function get_properties_by_group($args = array())
	{
		$this->init_class_base();
		// get a list of active properties for this object
		// I need an easy way to turn off individual properties
		$realprops = $this->get_active_properties(array(
				"classonly" => isset($args["classonly"]) ? $args["classonly"] : false,
				"clfile" => $this->clfile,
				"content" => $args["content"],
				"group" => $args["group"],
		));

		// parse the properties - resolve generated properties and
		// do any callbacks
		$this->values = &$args["values"];

		if ($this->values["id"])
		{
			$this->id = $this->values["id"];
		};

		$resprops = $this->parse_properties(array(
			"properties" => &$realprops,
		));

		return $resprops;
	}
	
	////
	// !This works in 2 ways
	// 1 - sync a single object
	// 2 - sync a whole mouthful of objects (after a definition is changed for example)

	// fuck, oh fuck, I hate this SO much
	function sync_property_table($args = array())
	{




	}

	function sync_object($args = array())
	{
		//print "syncing oid = " . $this->id . "<br>";
		// remove current fields belonging to this object from the property table
		$id = $this->id;

		$q = "DELETE FROM properties WHERE oid = $id";
		$this->db_query($q);

		// load the current object data
		$this->load_obj_data(array("id" => $this->id));

		// figure out all fields that have the search flag set
		foreach($this->all_props as $key => $val)
		{
			if ($val["search"] == 1 && ($val["method"] == "serialize"))
			{
				$this->get_value($val);
				$searchfields[$val["name"]] = $val["value"];
			};
		};

		// create new records
		$fs = new aw_array($searchfields);
		foreach($fs->get() as $key => $val)
		{
			$q = "INSERT INTO properties (oid,pname,pvalue)
				VALUES ($id,'$key','$val')";
			$this->db_query($q);
		};

	}

/*
mysql> explain select objects.oid,name from objects inner join properties as p_val ON (p_val.oid = objects.oid and p_val.pname = 'right_pane' and p_val.pvalue = 1) inner join properties as p_val2 ON (p_val2.oid = objects.oid and p_val2.pname = 'left_pane' and p_val2.pvalue = 1);
+---------+--------+---------------+---------+---------+-----------+------+------------+
| table   | type   | possible_keys | key     | key_len | ref       | rows | Extra      |
+---------+--------+---------------+---------+---------+-----------+------+------------+
| p_val   | ref    | oid,name      | name    |      51 | const     |    2 | where used |
| objects | eq_ref | PRIMARY       | PRIMARY |       4 | p_val.oid |    1 | where used |
| p_val2  | ref    | oid,name      | name    |      51 | const     |    2 | where used |
+---------+--------+---------------+---------+---------+-----------+------+------------+
3 rows in set (0.00 sec)
*/
};
?>
