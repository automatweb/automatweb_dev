<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgmanager.aw,v 1.6 2002/11/08 15:03:19 duke Exp $
// cfgmanager.aw - Object configuration manager
// deals with drawing add and change forms and submitting data
class cfgmanager extends aw_template
{
	function cfgmanager($args = array())
	{
		$this->init("cfgmanager");
		$this->output_client = "htmlclient";
	}

	////
	// !Displays a form for editing an object
	// id(int) - id of the object
	// don't need anything else really
	function change($args = array())
	{
		extract($args);

		// load the selected configuration forms for each class
		$cfg = get_instance("config");
		$cs = aw_unserialize($cfg->get_simple_config("class_cfgforms"));

		$cp = $this->get_class_picker(array("field" => "file"));

		if ($id)
		{
			// retrieve the object
			$this->coredata = $this->get_object($id);
			$parent = $this->coredata["parent"];
			// get an instance of the class that handles this object type
			$clfile = $cp[$this->coredata["class_id"]];
			$title = "Muuda objekti";
			
			$filter = (int)$cs[$this->coredata["class_id"]];
		}
		else
		{
			$clfile = $cp[$class_id];
			$parent = $args["parent"];
			$title = "Lisa objekt";
			
			$filter = (int)$cs[$class_id];
		};

		$this->id = $id;

		$filter = 0;

		if (!$clfile)
		{
			die("coult not identify object $id");
		};

		$inst = get_instance($clfile);

		// tabpanel really should be in the htmlclient too
		$tp = get_instance("vcl/tabpanel");
		
		$this->mk_path($parent,$title);

		$callback = false;
		if (method_exists($inst,"get_property"))
		{
			$callback = true;
		};

		$realprops = $this->get_active_properties(array(
					"clfile" => $clfile,
					"group" => $group,
		));

		// this removes the properties that have been excluded by a 
		// configuration form object

		// the big question is - why don't we do that inside the get_active_properties
		// function?
		if ($filter)
		{
			$saved = $objprops;
			$objprops = array();
			$fdata = $this->get_object($filter);
			$def = $this->cfg["classes"][$this->coredata["class_id"]]["def"];
			$fdat = $fdata["meta"]["properties"][$def];
			foreach($saved as $key => $val)
			{
				if ($fdat[$val["name"]["text"]])
				{
					$objprops[] = $val;
				};
			};
		};

		// I really really need to get rid of class specific code
		if ($clfile == "menuedit")
		{
			$this->objdata = $this->get_menu($id);
		};
		
		$grpnames = new aw_array($this->groupnames);
		$active = ($group) ? $group : "general";

		if (!$orb_class)
		{
			$orb_class = "cfgmanager";
		};

		foreach($grpnames->get() as $key => $val)
		{
			$link = $this->mk_my_orb("change",array("id" => $id,"group" => $key),$orb_class);
			$tp->add_tab(array(
				"link" => $link,
				"caption" => $key,
				"active" => ($key == $active),
			));
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

		// 2 - getter - which should return full property definitions
		// in the same format they come from load_properties.
		// those can be called dynamic properties I suppose
		// since they don't have to exist anywhere in the property
		// definitions


		// XXX: clean up this get_property/getter/array handling mess
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

			if ($callback)
			{
				$inst->get_property($argblock);
			};
			
			
			
			// if the property has a getter, call it directly
			if (is_array($val) && $val["getter"])
			{
				if ($XXX)
				{
					print "getter";
				};
				$meth = $val["getter"];
				if (method_exists($inst,$meth))
				{
					while($prop = $inst->$meth($argblock))
					{
						if ($prop["type"] == "subnodes")
						{
							foreach($prop["content"] as $subkey => $subval)
							{
								$resprops[] = $subval;
							};
						}
						else
						{
							if (sizeof($prop) != 0)
							{
								$resprops[] = $prop;
							};
						};
					};

				};
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
				$content = $this->$val();
			};
			$cli->add_property($val);
		};

		$cli->finish_output(array(
					"action" => "submit",
					"data" => array(
						"id" => $id,
						"group" => $group,
						"orb_class" => $orb_class,
						"parent" => $parent),
		));

		if (!$content)
		{
			$content = $cli->get_result();
		};

		return $tp->get_tabpanel(array(
			"content" => $content,
		));

	}

	////
	// !Submits the configuration data
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		$cfgu = get_instance("cfg/cfgutils");
		$clid = $cfgu->get_clid_by_file(array("file" => $class));
		if (!$clid)
		{
			die("class_id is missing. please report this to duke");
		};
		if (!$id)
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => $clid,
				"alias" => $alias,
				"status" => $status,
			));
		};
		$cp = $this->get_class_picker(array("field" => "file"));
		// retrieve the object
		$this->coredata = $this->get_object($id);
		$clfile = $cp[$this->coredata["class_id"]];
		
		$realprops = $this->get_active_properties(array(
					"clfile" => $clfile,
					"group" => $group,
		));
			
		// now we need to figure out the save strategy
		// cycle over the properties and sort out all the stuff 
		// that has values in the form
		$objdata = array();
		$coredata = array();
		$metadata = array();
		foreach($realprops as $property)
		{
			$name = $property["name"]["text"];
			$table = $property["table"]["text"];
			$field = $property["field"]["text"];
			$method = $property["method"]["text"];
			$handler = $property["handler"]["text"];
			$type = $property["type"]["text"];
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
		if ($clfile == "menuedit" && (sizeof($objdata) > 0))
		{
			foreach($objdata as $key => $val)
			{
				$qpart[] = " $key = '$val' ";
			};
			$qparts = join(",",$qpart);
			$q = "UPDATE menu SET $qparts WHERE id = '$id'";
			$this->db_query($q);
		};

		return $this->mk_my_orb("change",array("id" => $id,"group" => $group),$class);
	}

	function create_toolbar($args = array())
	{
		$this->toolbar = get_instance("toolbar");
		$this->toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.changeform.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
	}

	////
	// !Metainfo for the class
	function get_metainfo($key)
	{
		// XXX: figure out a better way to load those strings
		$params = array(
			"title_add" => "Lisa konfiguratsioonihaldur",
			"title_change" => "Muuda konfiguratsioonihaldurit",
			"class_id" => CL_CFGMANAGER,
		);

		return $params[$key];
	}

	////
	// !Reference implementation of get_properties
	// $obj contains the object - from which the values for fields can be aquired
	// $fields - contains a list of fields that should be returned.

	function get_properties($obj = array(),$fields = array())
	{

		$props = array();
		// generate the picker for choosing priority objects
		$props["priobj"] = array(
			"type" => "select",
			"options" => $this->list_objects(array("class" => CL_PRIORITY,"addempty" => true)),
			"caption" => "Prioriteedi objekt",
			"selected" => $obj["priobj"],
			"store" => "meta",
                );
	
		// since I dont want to clutter this method more than necessary,
		// I use a callback to retrieve a list of groups
		$props["cfgform"] = "get_groups";
		return $props;
	}

	// but then - isn't there a better way for storing properties?
	// key - name of the property
	function __get_property($args = array())
	{
		$key = $args["key"];
		$prp = false;
		/*
		you can also to stuff like
		if (in_array($key,$props))
		{
			return $props[$key];
		}
		else
		{
			// return something_else
			// creativity is encouraged. You can do whatever you
			// want as long as the stuff you return is a valid property
			// definition
		}

		// or just return $this->props[$key] - e.g. do whatever you want however
		// you want.
		*/

		// but I also need to figure out a way to return the names of _all_
		// the properties - so that configuration forms can work at all.
		// and so that I can check whether the requested property exists
		// or whether access to it is denied

		// then what about providing simple means to access the properties?

		// so then - I need a kind of register, which has the list of all
		// property names and for each of those describes a way to access
		// all those properties

		if ($key == "priobj")
		{
			$prp = array(
				"type" => "select",
				"options" => $this->list_objects(array("class" => CL_PRIORITY,"addempty" => true)),
				"caption" => "Prioriteedi objekt",
				"selected" => $obj["priobj"],
				"store" => "meta",
			);
		}
		elseif ($key == "cfgform")
		{
			$prp = "get_groups";
		};
		return $prp;
	}

	function get_groups($args = array())
	{
		$fields = array();
		// now, if the object is loaded AND has a priority object assigned to it, 
		// generate fields for each member group of the priority object
		if (!$args["priobj"])
		{
			return false;
		};
	
		$ginst = get_instance("users");
		$gdata= $ginst->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));
		// $gdata now contains a list of gid => name pairs

		$pri = get_instance("priority");
		$grps = new aw_array($pri->get_groups($args["priobj"]));
		// $grps now contains a list of gid => priority pairs

		// now we need to create a select element for each
		// member of the group. haha. god dammit, I love this

		// and I also need a list of all configuration forms.
		$cfgforms = $this->list_objects(array("class" => CL_CFGFORM,"addempty" => true));
		$keycount = 0;

		foreach($grps->get() as $gid => $pri)
		{
			$keycount++;
			$fields[$gid] = array(
					"type" => "select",
					"options" => $cfgforms,
					"caption" => $gdata[$gid],
					"selected" => $args["cfgform"][$gid],
					"store" => "meta",
			);
		};
		return $fields;
	}
	
	function get_active_cfg_object($id)
	{
		$ob = $this->get_object($id);
		$gidlist = aw_global_get("gidlist");

		$root_id = 0;
	
		$max_pri = 0;
		$max_gid = 0;
		$pri_inst = get_instance("priority");
		$grps = $pri_inst->get_groups($ob["meta"]["priobj"]);
		foreach($gidlist as $ugid)
		{
			if ($grps[$ugid])
			{
				if ($max_pri < $grps[$ugid])
				{
					$max_pri = $grps[$ugid];
					$max_gid = $ugid;
				}
			}
		}
		// now we have the gid with max priority
		if ($max_gid)
		{
			// find the root menu for this gid
			$max_obj = $ob["meta"]["cfgform"][$max_gid];
		}
		return $max_obj;
	}

	////
	// !Generates a change form for an object
	// clid(string) - name of the class
	// oid(int) - reference to object which should be used to fill the form
	// reforb(string) - reforb
	function gen_change_form($args = array())
	{
		extract($args);
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
				$this->alist = $almgr->get_oo_aliases(array(
							"oid" => $this->id,
				));
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

	function get_active_properties($args = array())
	{
		// load all properties
		$cfgu = get_instance("cfg/cfgutils");
		$coreprops = $cfgu->load_properties(array("file" => "core"));
		$objprops = $cfgu->load_properties(array("file" => basename($args["clfile"])));
		$this->classinfo = $cfgu->get_classinfo();
		$all_props = array_merge($coreprops,$objprops);
	
		// I need names of all group and the contents of active group
		$by_group = array();
		$activegroup = ($args["group"]) ? $args["group"] : "general";
		$elements = array();
		$this->groupnames = array();
		foreach($all_props as $val)
		{
			$grpname = ($val["group"]["text"]) ? $val["group"]["text"] : "general";
			// stuff with no group name goes into general. 
			if ($grpname == $activegroup)
			{
				$elements[] = $val;
			};
			$this->groupnames[$grpname] = $grpname;
		}
		
		if ($this->classinfo["relationmgr"])
		{
			$this->groupnames["related_objects"] = "related_objects";
			if ($activegroup == "related_objects")
			{
				$elements[] = "callback_get_relation_mgr";
			};
		};

		return $elements;
	}

	function callback_get_relation_mgr($args = array())
	{
		$almgr = get_instance("aliasmgr");
		$gen = $almgr->new_list_aliases(array("id" => $this->coredata["oid"]));
		return $gen;
	}
	
	function __add($args = array())
	{
		extract($args);
		$cfgform = get_instance("cfg/cfgform");
		$reforb = $this->mk_reforb("submit",array("parent" => $parent));
		$this->create_toolbar();
		$xf = $cfgform->change_properties(array(
			"clid" => &$this,
			"parent" => $parent,
			"reforb" => $reforb,
                ));
		return $this->toolbar->get_toolbar() . $xf;
	}

	function __change($args = array())
	{
		extract($args);
		$this->obj = $this->get_object($id);
		$this->create_toolbar();
		$cfgform = get_instance("cfg/cfgform");
		$reforb = $this->mk_reforb("submit",array("id" => $id));
		$xf = $cfgform->change_properties(array(
			"clid" => &$this,
			"obj" => $this->obj,
			"reforb" => $reforb,
                ));
		return $this->toolbar->get_toolbar() . $xf;
	}

	function __submit($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"priobj" => $priobj,
					"cfgform" => $cfgform,
				),
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_CFGMANAGER,
				"metadata" => array(
					"priobj" => $priobj,
				),
			));
		};
		// XXX: log the action
		return $this->mk_my_orb("change",array("id" => $id));
	}


};
?>
