<?php
// $Id: class_base.aw,v 2.140 2003/09/08 11:20:45 duke Exp $
// Common properties for all classes
/*
	@default table=objects
	@default group=general

	@property name type=textbox rel=1 trans=1
	@caption Nimi
	@comment Objekti nimi

	@property comment type=textbox 
	@caption Kommentaar
	@comment Vabas vormis tekst objekti kohta

	// translated objects have their own status fields .. they don't
	// have to sync with the original .. allthu .. I do feel that
	// we need to do this in a different way
	@property status type=status trans=1
	@caption Aktiivne
	@comment Kas objekt on aktiivne 

	@property needs_translation type=checkbox field=flags method=bitmask ch_value=2 // OBJ_NEEDS_TRANSLATION
	@caption Vajab tõlget

	// see peaks olemas olema ainult siis, kui sellel objekt on _actually_ mingi asja tõlge
	@property is_translated type=checkbox field=flags method=bitmask ch_value=4 // OBJ_IS_TRANSLATED
	@caption Tõlge kinnitatud

	@groupinfo general caption=Üldine default=1
	@classinfo trans_id=TR_CLASS_BASE
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

// translation
define('RELTYPE_TRANSLATION',102);

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
	// !Generate a form for adding or changing an object
	// id _always_ refers to the objects table. Always. If you want to load
	// any other data, then you'll need to use other field name
	function change($args = array())
	{
		$this->init_class_base();

		$cfgform_id = "";
		$this->subgroup = $this->reltype = "";
		$this->is_rel = false;

		$this->orb_action = $args["action"];
		

		if ($args["action"] == "new")
		{
			// that just checks whether the parent allows to add stuff
			// below it
			if (!$this->ds->ds_can_add($args))
			{
				die($this->ds->get_error_text());
			};

			$this->parent = $args["parent"];
			$this->id = "";
			$this->reltype = isset($args["reltype"]) ? $args["reltype"] : "";
		}
		elseif (($args["action"] == "change") || ($args["action"] == "view"))
		{
			$this->id = $args["id"];
			$obj = $this->get_object($this->id);
			$this->toolbar_type = ((isset($obj['meta']['use_menubar']) && ($obj['meta']['use_menubar'] == '1')) ? 'menubar' : 'tabs');
			$this->parent = "";


			if ($obj["class_id"] == CL_RELATION)
			{
				$this->is_rel = true;
				$def = $this->cfg["classes"][$this->clid]["def"];
				$this->values = $obj["meta"]["values"][$def];
				$this->values["name"] = $obj["name"];
			};

			$this->subgroup = isset($args["subgroup"]) ? $args["subgroup"] : "";
		};

		$cfgform_id = $this->get_cfgform_for_object(array(
			"meta" => $obj["meta"],
			"args" => $args,
		));

		$this->validate_cfgform($cfgform_id);


		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => isset($args["group"]) ? $args["group"] : "",
				"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
				"rel" => $this->is_rel,
		));

		$this->request = $args;

		if (!empty($this->id))
		{
			$this->load_obj_data(array("id" => $this->id));

			if (method_exists($this->inst,"callback_pre_edit"))
			{
				$this->inst->callback_pre_edit(array(
					"id" => $this->id,
					"coredata" => &$this->coredata,
					"data" => &$this->data,
					"request" => $this->request,
				));

			};
		};

		// parse the properties - resolve generated properties and
		// do any callbacks

		$resprops = $this->parse_properties(array(
			"properties" => &$realprops,
		));

		// so now I have a list of properties along with their values,

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

		$gdata = isset($this->subgroup) ? $this->groupinfo[$this->subgroup] : $this->groupinfo[$this->activegroup];


		$argblock = array(
			"id" => $this->id,
			// this should refer to the active group
			"group" => isset($args["group"]) ? $args["group"] : $this->activegroup,
			"orb_class" => $orb_class,
			"parent" => $this->parent,
			"section" => aw_global_get("section"),
			"period" => isset($args["period"]) ? $args["period"] : "",
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
			"alias_to" => isset($this->request["alias_to"]) ? $this->request["alias_to"] : "",
			"reltype" => $this->reltype,
			"cfgform" => isset($this->cfgform_id) && is_numeric($this->cfgform_id) ? $this->cfgform_id : "",
			"return_url" => isset($this->request["return_url"]) ? urlencode($this->request["return_url"]) : "",
			"subgroup" => $this->subgroup,
		) + (isset($args["extraids"]) && is_array($args["extraids"]) ? array("extraids" => $args["extraids"]) : array());

		if (method_exists($this->inst,"callback_mod_reforb"))
		{
			$this->inst->callback_mod_reforb(&$argblock);
		};

		$cli->finish_output(array(
			"action" => "submit",
			"submit" => isset($gdata["submit"]) ? $gdata["submit"] : "",
			"data" => $argblock,
		));

		extract($args);
		if (empty($content))
		{
			$content = $cli->get_result();
		};

		
		//this hack is for desktop. we need to show new object in desktop 'realtime'
		$added = '';
		if (isset($GLOBALS['added_object']))
		{
			$added = "
			
<script type=\"text/javascript\">
<!--
	if (window != window.top)
	{
		if (top.document.getElementById('status') == '[object HTMLDivElement]') //et siis kontrollime kas aken on desktopis
		{
			top.fetch_object('".$GLOBALS['added_object']."');
		}
	}
// -->
</script>			
			";
			aw_session_del('added_object');
		}

		return $this->gen_output(array(
			"parent" => $this->parent,
			"content" => $added.(isset($content) ? $content : ""),//axel häkkis
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
		));
	}

	////
	// !Saves the data that comes from the form generated by change
	function submit($args = array())
	{
		// check whether this current class is based on class_base
		$this->init_class_base();
		$this->orb_action = $args["action"];

		$this->quote($args);
		extract($args);

		$form_data = $args;

		// I need to know the id of the configuration form, so that I
		// can load it. Reason being, the properties can be grouped
		// differently in the config form then they are in the original
		// properties
		$this->is_rel = false;
		if (!empty($id))
		{
			$_tmp = $this->get_object($id);
			if ($_tmp["class_id"] == CL_RELATION)
			{
				$this->is_rel = true;
			};
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

		// heh.
		$args["rawdata"] = $args;
		$this->process_data($args);

		$this->log_obj_change();

		$args = array(
			"id" => $this->id,
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
				"clid" => $this->clid,
			));
		};
		// rrrr, temporary hack
		if (isset($this->id_only))
		{
			$retval = $this->id;
		}
		else
		{
			$use_orb = true;
			if (!empty($form_data["section"]))
			{
				$args["section"] = $form_data["section"];
				$args["_alias"] = get_class($this);
				$use_orb = false;
			};
			$retval = $this->mk_my_orb($action,$args,$orb_class,false,$use_orb);
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
		if ($id && is_numeric($id))
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
		}
		elseif ($this->clid == CL_DOCUMENT)
		{
			// I should be able to override this from the doc class somehow
			if (aw_ini_get("document.default_cfgform") != 0)
			{
				$_xid = aw_ini_get("document.default_cfgform");
				$_tmp = $this->get_object(array(
					"oid" => $_xid,
					"class" => CL_CFGFORM,
					"subclass" => $this->clid,
				));

				if ($_tmp)
				{
					$this->cfgform_id = $_tmp["oid"];
					$this->cfgform = $_tmp;
				};
			}
			else
			{
				$cfgu = get_instance("cfg/cfgutils");
				$def = file_get_contents(aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml");
				list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
				$this->cfg_proplist = $proplist;
				$this->cfg_groups = $grplist;
				$this->cfgform["meta"]["cfg_groups"] = $grplist;
				$this->cfgform["meta"]["cfg_proplist"] = $proplist;
				// heh
				$this->cfgform_id = "notempty";
			};
		};
	}

	function get_cfgform_for_object($args = array())
	{
		// or, if configuration form should be loaded from somewhere
		// else, this is the place to do it
		$retval = "";
		if (($args["args"]["action"] == "new") && !empty($args["args"]["cfgform"]))
		{
			$retval = $args["args"]["cfgform"];
		};

		if (($args["args"]["action"] == "change") && !empty($args["meta"]["cfgform_id"]))
		{
			$retval = $args["meta"]["cfgform_id"];
		};
		return $retval;
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
		// fuck me plenty! .. orb.aw sets $this->orb_class
		if (is_object($this->orb_class))
		{
			$this->inst = $this->orb_class;
		}
		// but I'm keeping the old approach too, just to be sure that
		// nothing breaks
		else
		{
			$this->inst = get_instance($clfile);
		};
	}

	function load_obj_data($args = array())
	{	
		// load the object data, if there is anything to load at all
		// but if no tables are defined, then it seems we don't load anything at all
		if (!is_array($this->tables))
		{
			return false;
		};

		$this->_obj = new object($args["id"]);

		foreach($this->tables as $key => $val)
		{
			// that we already got
			if (($key != "objects") && isset($this->realfields[$key]) && (sizeof($this->realfields[$key]) > 0) )
			{
				// this is a bit awkard, since it assumes that the data from objects
				// table is already loaded .. but it should be anyway
				if ($val["master_table"] == "objects")
				{
					if (isset($this->coredata[$val["master_index"]]))
					{
						$id_arg = $this->coredata[$val["master_index"]];
					}
					else
					{
						$id_arg = $args["id"];
					};
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
				$fields["brother_of"] = "direct";
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

		if (($this->toolbar_type == 'menubar') || (isset($this->classinfo['toolbar_type']) && ($this->classinfo['toolbar_type']['text'] == 'menubar')))
		{
			$this->toolbar_type = 'menubar';
			$this->tp = get_instance("vcl/menubar");
		}
		else
		{
			$this->tp = get_instance("vcl/tabpanel");
		}


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

		$tab_callback = (method_exists($this->inst,"callback_mod_tab")) ? true : false;

		foreach($this->groupinfo as $key => $val)
		{
			// we only want subgroups that are children of the currently active group
			if (isset($val["parent"]) && isset($this->classinfo["hide_tabs_L2"])) //"hide_tabs_L2" kas seda kasutatakse kuskil??
			{
				continue;
			}
			elseif (isset($val["parent"]) && $val["parent"] != $this->activegroup)
			{
				if ($this->toolbar_type != 'menubar')
				{
					continue;
				}
			}
			elseif (empty($val["parent"]) && isset($this->classinfo["hide_tabs"]))
			{
				continue;
			}

			if ($this->id)
			{
				$link_args->set_at("group",$key);
				if (aw_global_get("section"))
				{
					$link_args->set_at("section",aw_global_get("section"));
				};
				if ($this->embedded)
				{
					$link_args->set_at("_alias",get_class($this));
				};
				$link = $this->mk_my_orb($orb_action,$link_args->get(),get_class($this->orb_class));
			}
			else
			{
				$link = ($activegroup == $key) ? "#" : "";
			};

			$tabinfo = array(
				"link" => &$link,
				"caption" => &$val["caption"],
				"id" => $key,
				"tp" => &$this->tp,
				"coredata" => $this->coredata,
				"request" => $this->request,
			);

			$res = true;
			if ($tab_callback)
			{
				$res = $this->inst->callback_mod_tab($tabinfo);
			};

			if ($res !== false)
			{
				$this->tp->add_tab(array(
					'id' => $tabinfo['id'],
					"level" => empty($val["parent"]) ? 1 : 2,
					'parent' => $val['parent'],
					"link" => $tabinfo["link"],
					"caption" => $tabinfo["caption"],
					"active" => ($key == $activegroup) || ($key == $this->subgroup),
				));
			};
		};

		// XX: I need a better way to handle relationmgr, it should probably be a special
		// property type instead of being hardcoded.

		// well, there is a "relationmgr" property type and if used the property is drawn
		// in an iframe. But what I really need is an argument to the group definition,
		// .. which .. makes the group into a relation manager. eh? Or perhaps I should
		// just go with the iframe layout thingie. This frees us from the unneccessary
		// wrappers inside the class_base.
		if (isset($this->classinfo["relationmgr"]) && $this->classinfo["relationmgr"] && empty($this->request["cb_view"]) && !$this->is_rel)
		{
			$link = "";
			if (isset($this->id))
			{
				$link = $this->mk_my_orb("list_aliases",array("id" => $this->id,"return_url" => $return_url),get_class($this->orb_class));
			};

			$this->tp->add_tab(array(
				'id' => 'list_aliases',
				"link" => $link,
				"caption" => 'Seostehaldur',
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
			$id_arg = $id;
			if ($data["master_table"] == "objects")
			{
				if (isset($this->coredata[$data["master_index"]]))
				{
					$id_arg = $this->coredata[$data["master_index"]];
				};
			};
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
					"id" => $id_arg),$args["data"][$table]
				);
			};	
		};	

		// well now .. how do I propagate the changes to the original object .. if this
		// thing is a translation?
	}

	////
	// !Returns a list of properties for generating an output
	// or saving data. 
	function get_active_properties($args = array())
	{
		// properties are all saved in a single file, so sadly
		// we do need to load them all
		$no_group = !empty($args["all"]) ? $args["all"] : false;
		$this->get_all_properties(array(
			"classonly" => isset($args["classonly"]) ? $args["classonly"] : "",
			"cb_view" => isset($args["cb_view"]) ? $args["cb_view"] : "",
			"content" => isset($args["content"]) ? $args["content"] : "",
			"rel" => isset($args["rel"]) ? $args["rel"] : "",
		));


		// figure out which group is active
		// it the group argument is a defined group, use that
		if (isset($this->action))
		{
			$use_group = $this->action;
		}
		else
		if ( $args["group"] && !empty($this->groupinfo[$args["group"]]) )
		{
			$use_group = $args["group"];
		}
		else
		{
			// otherwise try to figure out whether any of the groups
			// has been set to default, if so, use it
			foreach($this->groupinfo as $gkey => $ginfo)
			{
				if (isset($ginfo["default"]))
				{
					$use_group = $gkey;
				};
			};
		};

		
		if (empty($this->id))
		{
			$use_group = "general";
		};

		// and if nothing suitable was found, use the first group from the list
		if (empty($use_group))
		{
			reset($this->groupinfo);
			list($use_group,) = each($this->groupinfo);
		};

		if (isset($this->grp_children[$use_group]))
		{
			list(,$use_group) = each($this->grp_children[$use_group]);
		};


		if (!empty($this->groupinfo[$use_group]["parent"]) && isset($this->groupinfo[$this->groupinfo[$use_group]["parent"]]))
		{
			$sub_group = $use_group;
			$use_group = $this->groupinfo[$use_group]["parent"];
		};


		$this->activegroup = $use_group;
		$this->subgroup = $sub_group;

		// now I know the group
		$property_list = array();

		$this->cb_views = array();

		$retval = $tables = $fields = $realfields = array();
		if (!empty($this->id))
		{
			$tables["objects"] = array("index" => "oid");
		};
		
		if (isset($this->role) && ($this->role == "obj_edit"))
		{
			$tables["objects"] = array("index" => "oid");
		};

		// well .. this is the place where I should somehow someway leave out the
		// "translation confirmed" property ... if this object is not a translation

		foreach($this->all_props as $key => $val)
		{
			if (isset($val["view"]) && empty($this->cb_views[$val["view"]]))
			{
				$this->cb_views[$val["view"]] = 1;
			};

			// multiple groups for properties are supported too
			if ($no_group === false)
			{
				$_tgr = new aw_array($val["group"]);
				foreach($_tgr->get() as $_grp)
				{
					$tmp = $val;
					if (isset($sub_group) && $_grp == $sub_group)
					{
						$tmp["group"] = $this->activegroup;
						$property_list[$key] = $tmp;
					}
					elseif (isset($sub_group) && $_grp == $this->activegroup && $sub_group == $this->grp_children[$this->activegroup][0])
					{
						// remap to the first child group
						$property_list[$key] = $tmp;
					}
					elseif (empty($sub_group) && $_grp == $this->activegroup)
					//elseif (empty($sub_group) && $tmp["group"] == $this->activegroup)
					{
						$property_list[$key] = $tmp;
					}
					elseif ($args["load_defaults"] && !empty($tmp["default"]))
					{
						$tmp["group"] = $this->activegroup;
						$property_list[$key] = $tmp;
					}
				};
			}
			else
			{
				$property_list[$key] = $val;
			};

			if (isset($val["table"]) && empty($tables[$val["table"]]))
			{
				if (isset($this->tableinfo[$val["table"]]))
				{
					$tables[$val["table"]] = $this->tableinfo[$val["table"]];
				}
				else
				{
					$tables[$val["table"]] = "";
				};
			};

			$fval = $val["method"];
			$_field = $val["field"];

			if ($_field == "meta")
			{
				$_field = "metadata";
			};


			if (isset($val["table"]))
			{
				if ($_field)
				{
					if (($val["type"] != "callback") && ($val["store"] != "no") )
					{
						$fields[$val["table"]][$_field] = $fval;
					};
				};

				if (($val["type"] != "callback") && ($val["store"] != "no"))
				{
					$realfields[$val["table"]][$_field] = $fval;
				};
			};
		};

		// I need to replace this with a better check if I want to be able
		// to use config forms in other situations besides editing objects

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


			// nyah .. do not create the toolbar instance, if we are submitting the
			// form. but this approach really sucks
			if (($property["type"] == "toolbar") && ($this->orb_action != "submit"))
			{
				classload("toolbar");
				$property["toolbar"] = new toolbar();
			};
			
			if (($property["type"] == "table") && ($this->orb_action != "submit"))
			{
				load_vcl("table");
				$property["obj_inst"] = new aw_table(array(
					"layout" => "generic",
				));
			};

			// properties with no group end up in default group
			if (isset($val["group"]))
			{
				if (is_array($val["group"]))
				{
					$in_groups = $val["group"];
				}
				else
				{
					$in_groups = array($val["group"]);
				};
			}
			else
			{
				$in_groups = array($this->default_group);
			};

			if ($no_group || (in_array($use_group,$in_groups)))
			{
				$retval[$key] = $property;
			};
		};

		$this->tables = $tables;
		$this->fields = $fields;
		$this->realfields = isset($realfields) ? $realfields : NULL;

		$idx = $this->default_group;

		return $retval;
	}

	////
	// !Load all properties for the current class
	function get_all_properties($args = array())
	{
		$filter = $args["rel"] ? array("rel" => 1) : "";
		$cb_view = $args["cb_view"];

		if (isset($this->cfgform["meta"]["cfg_proplist"]))
		{
			// load a list of properties and groups in the config form
			$proplist = $this->cfgform["meta"]["cfg_proplist"];
			$grplist = $this->cfgform["meta"]["cfg_groups"];

		}

		$cfgu = get_instance("cfg/cfgutils");

		// content comes from the config form
		if (!empty($args["content"]))
		{
			$_all_props = $cfgu->parse_definition(array(
				"content" => $args["content"],
			));
		}
		else
		// this handles some embedding cases
		if ($args["classonly"])
		{
			$_all_props = $cfgu->load_class_properties(array(
				"clid" => $this->clid,
			));
		}
		// and this handles the generic cases
		else
		{
			$_all_props = $cfgu->load_properties(array(
				"clid" => $this->clid,
				"filter" => $filter,
			));
		};

		$this->cb_views = $group_el_cnt = $this->all_props = array();

		// use the group list defined in the config form, if we are indeed using a config form
		if (!is_array($grplist))
		{
			$grplist = $cfgu->get_groupinfo();
		};

		$this->grp_children = array();
		
		foreach($grplist as $key => $val)
		{
			// don't even try that
			if (!empty($val["parent"]) && $val["parent"] != $key)
			{
				$this->grp_children[$val["parent"]][] = $key;
			};
						
			// first default group is used
			if (isset($val["default"]) && empty($this->default_group))
			{
				$this->default_group = $key;
			};
		};				

		$tmp = empty($this->cfgform_id) ? $_all_props : $proplist;
		foreach($tmp as $k => $val)
		{
			// if a config form is loaded, then ignore stuff that isn't
			// defined in there. I really shouldn't cause any problems
			// with well working code.
			if (!empty($this->cfgform_id))
			{
				// we can have as many relpickers as we want
				if ($val["type"] == "relpicker")
				{
				}
				// but for other element types we ignore things that
				// are not defined by the class
				else if (empty($_all_props[$val["name"]]))
				{
					continue;
				};
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
			
				// reset the richtext attribute, if it was disabled in the config form
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
					// subgroups count as children of the parent group as well
					if (isset($grplist[$_grp]["parent"]))
					{
						$group_el_cnt[$grplist[$_grp]["parent"]] = 1;
					};
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
		if (is_array($this->classconfig))
		{
			$this->classinfo = array_merge($this->classinfo,$this->classconfig);
		};
		$grpinfo = array();
		if (is_array($grplist))
		{
			foreach($grplist as $key => $val)
			{
				if (in_array($key,array_keys($group_el_cnt)))
				{
					// skip the group, if it is not listed in the config form object
					if (!empty($this->cfgform_id) && empty($grplist[$key]))
					{
						continue;
					}
					else
					{
						// grplist comes from CL_CFGFORM and can be used
						// to override the default settings for a group

						// XX: add a list of settings that can be overrided,
						// allowing everything is probably not a good idea
						if (is_array($grplist) && isset($grplist[$key]))
						{
							$val = array_merge($val,$grplist[$key]);
						};

						$grpinfo[$key] = $val;

					};
				};
			};
		};

		$this->groupinfo = $grpinfo;
		$this->tableinfo = $cfgu->get_opt("tableinfo");

		return $this->all_props;
	}

	function convert_element(&$val)
	{
		// no type? get out then
		if (empty($val["type"]))
		{
			return false;
		};

		if ($val["type"] == "toolbar")
		{
			$val["value"] = $val["toolbar"]->get_toolbar();
		};
		
		if ($val["type"] == "table")
		{
			$val["value"] = $val["obj_inst"]->draw();
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
		if ($property["type"] == "relpicker" && isset($property["pri"]))
		{
			$realclid = constant($property["clid"]);
			$q = sprintf("SELECT target FROM aliases WHERE source = %d AND pri = %d AND type = %d",
					$this->id,$property["pri"],$realclid);
			$property["value"] = $this->db_fetch_field($q,"target");
		}
		else
		if (empty($this->id) && isset($property["default"]))
		{
			$property["value"] = $property["default"];
		}
		else
		if (empty($this->id) && $property["type"] == "datetime_select")
		{
			$property["value"] = time();
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
		if (($property["trans"] == 0) && is_object($this->_obj) && $this->_obj->prop($property["name"]) != NULL)
		{
			$property["value"] = $this->_obj->prop($property["name"]);
		}
		else
		{
			$_field = ($property["name"] != $property["field"]) ? $property["field"] : $property["name"];
			if ($table == "objects")
			{
				if ($field == "meta")
				{
					//if (isset($this->coredata["meta"][$property["name"]]))
					if (isset($this->data["objects"]["meta"][$property["name"]]))
					{
						//$property["value"] = $this->coredata["meta"][$property["name"]];
						$property["value"] = $this->data["objects"]["meta"][$property["name"]];
					};
				}
				else
				{
					//if (isset($this->coredata[$_field]))
					if (isset($this->data["objects"][$_field]))
					{
						//$property["value"] = $this->coredata[$_field];
						$property["value"] = $this->data["objects"][$_field];
					};
				};
			}
			else
			{
				if ($property["method"] == "serialize")
				{
					if (isset($this->data[$table][$field]))
					{
						// meaning that you cannot serialize more than one property
						// into a single field. 
						$property["value"] = aw_unserialize($this->data[$table][$field]);
					};
				}
				else
				{
					if (isset($this->data[$table][$_field]))
					{
						$property["value"] = $this->data[$table][$_field];
					};
				};
			};
		};
		
		if ($property["method"] == "bitmask")
		{
			$property["value"] = $property["value"] & $property["ch_value"];
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
			"data" => &$this->data,
		);

		$this->cfgu = get_instance("cfg/cfgutils");

		foreach($properties as $key => $val)
		{

			$name = $val["name"];
			if (is_array($val))
			{
				$this->get_value(&$val);
			};

			if (is_object($this->tr))
			{
				$prop_id = md5($val["caption"]);
				if (!empty($val["comment"]))
				{
					$comm_id = md5($val["comment"]);
					$commtrans = $this->tr->get_by_id($comm_id);
					if (!empty($commtrans))
					{
						$val["comment"] = $commtrans;
					};
				};
				$trans = $this->tr->get_by_id($prop_id);
				if (!empty($trans))
				{
					$val["caption"] = $trans;
				};
			}

			$argblock["prop"] = &$val;

			// callbackiga saad muuta ühe konkreetse omaduse sisu
			if ($callback)
			{
				$status = $this->inst->get_property($argblock);
			};

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
				$resprops[$name] = $val;
			}
			else
			{
				$this->convert_element(&$val);
				if (empty($name))
				{
					$name = $key;
				};
				if (!empty($val["field"]) && ($name != $val["field"]) && ($val["method"] == "direct"))
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

		// if name_prefix given, prefixes all element names with the value 
		// e.g. if name_prefix => "emb" and there is a property named comment,
		// then the result will be name => emb[comment], this simplifies 
		// processing of embedded config forms
		if ($args["name_prefix"])
		{
			$tmp = $resprops;
			$resprops = array();
			foreach($tmp as $key => $el)
			{
				$bracket = strpos($el["name"],"[");
				if ($bracket > 0)
				{
					$pre = substr($el["name"],0,$bracket);
					$aft = substr($el["name"],$bracket);
					$newname = $args["name_prefix"] . "[$pre]" . $aft;
				}
				else
				{
					$newname = $args["name_prefix"] . "[" . $el["name"] . "]";
				};
				$el["name"] = $newname;
				// just to get an hopefully unique name .. 
				$resprops[$args["name_prefix"] . "_" . $key] = $el;
			}
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
		if ($this->cfgform_id)
		{
			$toolbar->add_cdata(html::href(array(
				"url" => $this->mk_my_orb("change",array("id" => $this->cfgform_id),"cfgform"),
				"caption" => "Aktiivne konfivorm: " . $this->cfgform["name"],
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
		$this->request = $args;

		$this->id = $args["id"];
		$obj = $this->get_object($this->id);
		$this->toolbar_type = ((isset($obj['meta']['use_menubar']) && ($obj['meta']['use_menubar'] == '1')) ? 'menubar' : 'tabs');

		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $group,
		));

		$reltypes = $this->get_rel_types();

		$clid_list = array();
		if (method_exists($this->inst,"callback_get_classes_for_relation"))
		{
			foreach ($reltypes as $key => $val)
			{
				$rel_type_classes[$key] = $this->inst->callback_get_classes_for_relation(array(
					"reltype" => $key,
				));
			}
		}
		
		$gen = $almgr->list_aliases(array(
			"id" => $id,
			"reltypes" => $reltypes,
			'rel_type_classes' => $rel_type_classes,//$this->get_rel_type_classes(),
			"return_url" => $this->mk_my_orb("list_aliases",array("id" => $id),get_class($this->orb_class)),
		));
		return $this->gen_output(array("content" => $gen));
	}

	////"log.txt"
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

		$obj = $this->get_object($args["id"]);
		$this->toolbar_type = ((isset($obj['meta']['use_menubar']) && ($obj['meta']['use_menubar'] == '1')) ? 'menubar' : 'tabs');


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
			foreach ($reltypes as $key => $val)
			{
				$rel_type_classes[$key] = $this->inst->callback_get_classes_for_relation(array(
					"reltype" => $key,
				));
			}
		}

		$args["clid_list"] = $clid_list;

		$args["return_url"] = $this->mk_my_orb("change",array("id" => $id,"group" => $group),get_class($this->orb_class));
		$gen = $almgr->search($args + array(
			"reltypes" => $this->get_rel_types(),
			'rel_type_classes' => $rel_type_classes,//$this->get_rel_type_classes(),
		));
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

	////
	// !_serialize replacement for class_base based objects
	function _serialize($args = array())
	{
		$this->init_class_base();
		$this->id = $args["oid"];

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"all" => true,
		));

		$this->load_obj_data(array("id" => $this->id));

		$result = array();

		foreach($realprops as $key => $val)
		{
			$this->get_value(&$val);
			if (!empty($val["value"]) || $val["store"] != "no")
			{
				$result[$val["name"]] = $val["value"];
			};
		}

		$retval = isset($args["raw"]) ? $result : aw_serialize($result, SERIALIZE_NATIVE);
		return $retval;
	}

	////
	// !_unserialize replacement for class_base based objects
	function _unserialize($args = array())
	{
		$raw = isset($args["raw"]) ? $args["raw"] : aw_unserialize($args["str"]);
		$this->init_class_base();

		$this->process_data(array(
			"parent" => $args["parent"],
			"rawdata" => $raw,
		));
		
		// object_translation depends on getting the id from here
		return $this->id;
	}

	////
	// !Processes and saves form data
	function process_data($args = array())
	{
		extract($args);
		$this->init_class_base();

		// and this of course should handle both creating new objects and updating existing ones
		
		$callback = method_exists($this->inst,"set_property");
		
		$new = false;
		$this->id = isset($id) ? $id : "";
		

		// basically, if this is a new object, then I need to load all the properties 
		// that have default values and add them to the bunch.

		// only create the object, if one of the tables used by the object
		// is the objects table
		if (empty($id))
		{
			$period = aw_global_get("period");
			$id = $this->ds->ds_new_object(array(),array(
				"parent" => $parent,
				"class_id" => $this->clid,
				"status" => !empty($status) ? $status : 1,
				"period" => $period,
				"lang_id" => isset($rawdata["lang_id"]) ? $rawdata["lang_id"] : false,
			));
			aw_session_set("added_object",$id);//axel häkkis
			
			if ($alias_to || $rawdata["alias_to"])
			{
				$almgr = get_instance("aliasmgr");
				$almgr->create_alias(array(
					"alias" => $id,
					"id" => $rawdata["alias_to"],
					"reltype" => $rawdata["reltype"],
				));
			};
			
			$new = true;
			$this->id = $id;
		};

		$this->new = $new;
		
		// the question is .. should I call set_property for those too?
		// and how do I load the stuff with defaults?
		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"all" => empty($group) ? true : false,
				"group" => $group,
				"cb_view" => $cb_view,
				"rel" => $this->is_rel,
				"load_defaults" => $new ? true : false,
		));

		if (isset($this->tables["objects"]))
		{
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
		};


		$this->load_object(array("id" => $this->id));
                $this->load_obj_data(array("id" => $this->id));
		
		// load the freaking thing as an object.. so that I can 
		// access it's properties

		$obj = new object($this->id);

		$metadata = array();

		$sync_properties = array();

		$pvalues = array();

		foreach($realprops as $key => $property)
		{
			//do not call set_property for edit_only properties when a new
			// object is created.
			if ($new && isset($property["editonly"]))
			{
				continue;
			};

			$name = $property["name"];
			$type = $property["type"];
			$method = $property["method"];
			$table = $property["table"];
			$field = !empty($property["field"]) ? $property["field"] : $property["name"];
				
                        $xval = isset($rawdata[$name]) ? $rawdata[$name] : "";

			if ($new && empty($xval) && !empty($property["default"]))
			{
				$xval = $property["default"];
			};

                        if ($property["type"] == "checkbox")
                        {
                                // set value to 0 for unchecked checkboxes
				$xval = (int)$xval;
                        };

			if ($method == "bitmask" && empty($pvalues[$field]))
			{
				$pvalues[$field] = $obj->prop($field);
			};

			$property["value"] = $xval;


                        $argblock = array(
                                "prop" => &$property,
                                "obj" => &$this->coredata,
                                "objdata" => &$this->objdata,
                                "metadata" => &$metadata,
                                "form_data" => &$rawdata,
                                "new" => $new,
                        );

			$status = PROP_OK;

			// give the class a possiblity to execute some action
			// while we are saving it.

			// for callback, the return status of the function decides
			// whether to save the data or not, so please, make sure
			// that your set_property returns PROP_OK for stuff
			// that you want to save
			if ($callback)
			{
				$status = $this->inst->set_property($argblock);
			};

			// oh well, bail out then.
			if ($status != PROP_OK)
                        {
				continue;
			};


			// don't care about text elements
			if ($type == "text")
			{
				continue;	
			};

			if ($property["store"] == "no")
			{
				continue;
			};
		
			/*
			if ($property["type"] == "callback")
			{
				continue;
			};
			*/

			if (empty($property["trans"]))
			{
				$sync_properties[] = $property;
			};
			


			if (($type == "date_select") || ($type == "datetime_select"))
			{
				if (is_array($rawdata[$name]))
				{
					$property["value"] = date_edit::get_timestamp($rawdata[$name]);
				};
			};

			if (($type == "select") && isset($property["multiple"]))
			{
				$property["value"] = $this->make_keys($rawdata[$name]);
			};

			// XXX: investigate the possibility of moving this out of class_base
			if ($type == "imgupload")
			{
				if (isset($rawdata["del_" . $name]))
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
			};
			if ($method == "bitmask")
			{
				// shift to the left, shift to the right
				// pop, push, pop, push
				if ( ($pvalues[$field] & $property["ch_value"]) && !($rawdata[$name] & $property["ch_value"]))
				{
					$pvalues[$field] -= $property["ch_value"]; 
				}       
				elseif (!($pvalues[$field] & $property["ch_value"]) && ($rawdata[$name] & $property["ch_value"]))
				{
					$pvalues[$field] += $property["ch_value"];
				};     
				//print "name = $property[name]<br>";
				//print "val = $property[value]<br>";
			};

			// XXX: this is not good!
			if ( ($type == "relpicker") && isset($property["pri"]) )
			{
				$realclid =  constant($property["clid"]);
				// first zero out all other pri fields
				$q = sprintf("UPDATE aliases SET pri = 0 WHERE source = %d AND type = %d",
					$this->id,$realclid);
				$this->db_query($q);
				if (!empty($property["value"]))
				{
					// and now .. if a value is set, update the pri of _that_
					$q = sprintf("UPDATE aliases SET pri = %d WHERE source = %d AND target = %d AND type = %d",
					$property["pri"],$this->id,$property["value"],$realclid);
					$this->db_query($q);
				};
			}


			if ($this->is_rel)
			{
				if ($name == "name")
				{
					$this->coredata["name"] = $property["value"];
				}
				else
				{
					$values[$name] = $property["value"];
				};
			}
			else if ($method == "serialize")
			{
				$metadata[$name] = $property["value"];
			}
			elseif ($table == "objects")
			{
				if ($method == "bitmask")
				{
					$this->coredata[$field] = $pvalues[$field];
				} 
				else
				if (isset($rawdata[$name]))
				{
					$this->coredata[$field] = $property["value"];
				};
			}
			else
			{
				if (isset($property["value"]) && !empty($table))
				{
					$_field = ($name != $field) ? $field : $name;
					$this->objdata[$table][$_field] = $property["value"];
				};
			};
		};

		if (sizeof($metadata) > 0)
		{
			$this->coredata["metadata"] = $metadata;
		};

		if ($this->is_rel && is_array($values) && sizeof($values) > 0)
		{
			$def = $this->cfg["classes"][$this->clid]["def"];
			$_tmp = $this->get_object($this->id);
			$old = $_tmp["meta"]["values"][$def];
			$new = array_merge($old,$values);
			$this->coredata["metadata"]["values"][$def] = $new;
		}

		$this->coredata["id"] = $this->id;
		
		if (method_exists($this->inst,"callback_pre_save"))
		{
			$this->inst->callback_pre_save(array(
				"id" => $this->id,
				"coredata" => &$this->coredata,
				"objdata" => &$this->objdata,
				"form_data" => &$args,
				"object" => array_merge($this->coredata,$this->objdata),
			));
		}

                // it is set (or not) on validate_cfgform
                //if (isset($this->cfgform_id) && is_numeric($this->cfgform_id))
                if (isset($this->cfgform_id))
                {
                        $this->coredata["metadata"]["cfgform_id"] = $this->cfgform_id;
                };

		$this->ds->ds_save_object(array("id" => $this->id,"clid" => $this->clid),$this->coredata);
		$this->save_object(array("data" => $this->objdata));




		if (method_exists($this->inst,"callback_post_save"))
		{
			$this->inst->callback_post_save(array(
				"id" => $this->id,
				"coredata" => $this->coredata,
				"objdata" => $this->objdata,
				"form_data" => &$args,
				"new" => $new,
			));
		}
		
		/*
		print "<pre>";
		print_r($sync_properties);
		print "</pre>";
		*/

		// alright then .. I'll do the object syncing here?
		// cause .. that object class thingie feels like trying to drive a bike
		// with square wheels -- duke
		/*
			1) figure out all the the sources and targets of this object
			2) cycle over them .. and overwrite the fields that are not to 
				be translated with the current values ... 
			3) general nastyness

			$orig = $this->db_fetch_row("SELECT source FROM aliases WHERE target = '$oid' AND reltype = " . RELTYPE_TRANSLATION);
			 1) set base_oid to the current id

			 2) set exclude list to empty array

			 3) check whether base_oid is a target of any translation, if so add it to 
			    exclude list and reset base_oid to the source of the translation

			 4) get all the translation targets of the base_oid except the ones
			    in the exclude list

		 	 5) sync them .. and we should be done.
			 	// yeah well .. that syncing operation is the meat of the whole task ...
				// I need to overwrite all the values of not-translatable fields

				// faaaak .. this is awful .. it's a freaking reentrant
				// thing

		*/
		return true;
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

		$this->rel_type_classes = $this->get_rel_type_classes();
		return $reltypes;
	}

	function get_rel_type_classes()
	{
		if (method_exists($this->inst,"callback_get_rel_type_classes"))
		{
			$reltypes = $this->inst->callback_get_rel_type_classes();
		}
		else
		{
			$reltypes = array();
		};
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
//arr($resprops);
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
		//print "syncing oid = " . $this->id . "<br />";
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
