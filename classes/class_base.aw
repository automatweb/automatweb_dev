<?php
// $Id: class_base.aw,v 2.231 2003/01/28 12:38:11 duke Exp $
// the root of all good.
// 
// ------------------------------------------------------------------
// Do not be writink any HTML in this class, it defeats half
// of the purpose of this class. If you really absolutely
// must, then do it in the htmlclient class.
// ------------------------------------------------------------------:w
// 
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
	@property status type=status trans=1 default=1
	@caption Aktiivne
	@comment Kas objekt on aktiivne 

	@property needs_translation type=checkbox field=flags method=bitmask ch_value=2 // OBJ_NEEDS_TRANSLATION
	@caption Vajab tõlget

	// see peaks olemas olema ainult siis, kui sellel objekt on _actually_ mingi asja tõlge
	@property is_translated type=checkbox field=flags method=bitmask ch_value=4 trans=1 // OBJ_IS_TRANSLATED
	@caption Tõlge kinnitatud

	@groupinfo general caption=Üldine default=1 icon=edit

	@forminfo add onload=init_storage_object 
	@forminfo edit onload=load_storage_object

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
define('RELTYPE_ORIGINAL',103);

class class_base extends aw_template
{
	function class_base($args = array())
	{
		$this->init("");

	}

	function init($arg = array())
	{
		global $XUL;
		$this->output_client = "htmlclient";
		if ($XUL)
		{
			$this->output_client = "xulclient";
		};
		$this->default_group = "general";
		$this->features = array();

		$this->vcl_register = array(
			"classificator" => "classificator",
		);
		parent::init($arg);
	}

	function init_storage_object($arr)
	{
		$this->use_mode = "new";

		$this->parent = $arr["parent"];
		$this->id = "";
		$this->new = 1;
		$this->obj_inst = new object();
		$this->reltype = isset($arr["reltype"]) ? $arr["reltype"] : "";


	}

	function load_storage_object($arr)
	{
		$this->id = $arr["id"];

		$this->obj_inst = new object($this->id);

		$this->parent = "";

		$this->use_mode = "edit";
		$this->subgroup = isset($args["subgroup"]) ? $args["subgroup"] : "";
	}


	/** Generate a form for adding or changing an object 
		
		@attrib name=new params=name all_args="1" 
		
		@param parent optional type=int acl="add"
		@param period optional
		@param alias_to optional
		@param return_url optional
		@param reltype optional type=int

		
		@returns
		
		
		@comment
		id _always_ refers to the objects table. Always. If you want to load
		any other data, then you'll need to use other field name

	**/
	function new_change($args)
	{
		return $this->change($args);
	}

	/**  
		
		@attrib name=change params=name all_args="1"
		
		@param id optional type=int acl="edit"
		@param group optional
		@param period optional
		@param alias_to optional
		@param return_url optional
		
		@returns
		
		
		@comment

	**/
	function change($args = array())
	{
		$this->init_class_base();

		$cb_values = aw_global_get("cb_values");
		$has_errors = false;
		if (!empty($cb_values))
		{
			$this->cb_values = $cb_values;
			$has_errors = true;
			aw_session_del("cb_values");
		};

		$cfgform_id = "";
		$this->subgroup = $this->reltype = "";
		$this->is_rel = false;

		$this->orb_action = $args["action"];
		
		$this->is_translated = 0;

		if (empty($args["action"]))
		{
			$args["action"] = "view";
		};

		$use_form = $args["form"];

		if (empty($use_form))
		{
			if ($args["action"] == "new")
			{
				$this->init_storage_object($args);
			}
			elseif (($args["action"] == "change") || ($args["action"] == "view"))
			{
				$this->load_storage_object($args);
				if ($this->obj_inst->class_id() == CL_RELATION)
				{
					// this is a relation!
					$this->is_rel = true;
					$def = $this->cfg["classes"][$this->clid]["def"];
					$meta = $this->obj_inst->meta("values");
					$this->values = $meta[$def];
					$this->values["name"] = $this->obj_inst->name();
				};

			};
		}
		else
		{
			// because if I don't have a object id, I'm going to have a hard time drawing a yah line
			$this->set_classinfo(array("name" => "no_yah", "value" => 1));
		};

		// now i need to do something with that translation thingie

		// yees, this means that other forms besides add and edit cannot use custom config forms
		// at least not yet.
		if (empty($use_form))
		{
			// a class should be able to override it
			$cfgform_id = $this->get_cfgform_for_object(array(
				"obj_inst" => $this->obj_inst,
				"args" => $args,
			));
		};

		$cfgform_id = $args["cfgform"];
		if (empty($cfgform_id) && is_object($this->obj_inst))
		{
			$cfgform_id = $this->obj_inst->meta("cfgform_id");
		};
		
		$filter = array(
			"clid" => $this->clid,
			"clfile" => $this->clfile,
			"group" => $args["group"],
			"cfgform_id" => $cfgform_id,
			"cb_part" => $args["cb_part"],
		);

		if (!empty($args["form"]))
		{
			$filter["form"] = $args["form"];
		};

		if ($this->is_rel)
		{
			$filter["rel"] = 1;
		};

		// XXX: temporary -- duke
		if ($args["fxt"])
		{
			$this->layout_mode = "fixed_toolbar";
			$filter["layout_mode"] == "fixed_toolbar";
		}

		// Now I need to deal with relation elements
		$properties = $this->get_property_group($filter);

		if ($this->classinfo(array("name" => "trans")) == 1 && $this->id)
		{
			$o_t = get_instance("translate/object_translation");
			$t_list = $o_t->translation_list($this->id, true);
			if (in_array($this->id, $t_list))
			{
				$this->is_translated = 1;
				$tmp = $properties;
				foreach($tmp as $pkey => $pval)
				{
					if ($pval["trans"] != 1 && $pval["name"] != "is_translated")
					{
						unset($properties[$pkey]);
					};
				};
			}
			else
			{
				unset($properties["is_translated"]);
			};
		}
			
		// XXX: temporary -- duke
		if ($args["fxt"])
		{
			$this->set_classinfo(array("name" => "hide_tabs","value" => 1));
		}

		if (!empty($args["form"]))
		{
			$onload_method = $this->forminfo(array(
				"form" => $args["form"],
				"attr" => "onload",
			));

			if (method_exists($this->inst,$onload_method))
			{
				$this->inst->$onload_method($args);
			}
		};
	
		$this->request = $args;

		if (!empty($this->id))
		{
			// it is absolutely essential that pre_edit is called
			// only for existing objects
			if (method_exists($this->inst,"callback_pre_edit"))
			{
				$this->inst->callback_pre_edit(array(
					"id" => $this->id,
					"request" => $this->request,
					"obj_inst" => &$this->obj_inst,
				));

			};
		};
		
		$gdata = !empty($this->subgroup) ? $this->groupinfo[$this->subgroup] : $this->groupinfo[$this->use_group];

		$lm = $this->classinfo(array("name" => "fixed_toolbar"));

		// turn of submit button, if the toolbar is being shown
		if (!empty($lm))
		{
			$gdata["submit"] = "no";
		};

		// and, if we are in that other layout mode, then we should probably remap all
		// the links in the toolbar .. augh, how the hell do I do that?
		if (!empty($lm) && empty($args["cb_part"]))
		{
			$new_uri = aw_url_change_var(array("cb_part" => 1));
			$cli = get_instance("cfg/" . $this->output_client,array("layout_mode" => "fixed_toolbar"));

			$properties["iframe_container"] = array(
				"type" => "iframe",
				"src" => $new_uri,
				"value" => " ",
			);

			$this->layout_mode = "fixed_toolbar";

			// show only the elements and not the frame (because it contains some design
			// elements and "<form>" tag that I really do not need

			// this really could use some generic solution!
			$this->raw_output = 1;

		}
		else
		{
			$cli = get_instance("cfg/" . $this->output_client);
			if (!empty($lm))
			{
				if ($this->use_mode == "new")
				{
					$cli->set_form_target("_parent");
				};
			};
		};

		if ($args["cb_part"] == 1)
		{
			// tabs and YAH are in the upper frame, so we don't show them below
			$this->set_classinfo(array("name" => "hide_tabs","value" => 1));
			$this->set_classinfo(array("name" => "no_yah","value" => 1));
		};
		

		// parse the properties - resolve generated properties and
		// do any callbacks

		// and the only user of that is the crm_company class. Would be _really_ nice
		// to beg rid of all that shit
		$this->inst->relinfo = $this->relinfo;

		$resprops = $this->parse_properties(array(
			"properties" => &$properties,
		));

		// what exactly is going on with that subgroup stuff?
		if (isset($resprops["subgroup"]))
		{
			$this->subgroup = $resprops["subgroup"]["value"];
		};

		if ($has_errors)
		{
			// give the output client a chance to display a message stating
			// that there were errors in entered data. Individual error 
			// messages will be next to their respective properties, this
			// is just the caption
			$cli->show_error();
		}

		// so now I have a list of properties along with their values,

		// and, if we are in that other layout mode, then we should probably remap all
		// the links in the toolbar .. augh, how the hell do I do that?
		foreach($resprops as $val)
		{
			$cli->add_property($val);
		};

		$orb_class = $this->cfg["classes"][$this->clid]["file"];
		if (empty($orb_class))
		{
			$orb_class = $this->clfile;
		};

		if ($orb_class == "document")
		{
			$orb_class = "doc";
		};

		$argblock = array(
			"id" => $this->id,
			// this should refer to the active group
			"group" => isset($this->request["group"]) ? $this->request["group"] : $this->activegroup,
			"orb_class" => $orb_class,
			"parent" => $this->parent,
			"section" => $_REQUEST["section"],
			"period" => isset($this->request["period"]) ? $this->request["period"] : "",
			"alias_to" => isset($this->request["alias_to"]) ? $this->request["alias_to"] : "",
			"reltype" => $this->reltype,
			"cfgform" => isset($this->cfgform_id) && is_numeric($this->cfgform_id) ? $this->cfgform_id : "",
			"return_url" => !empty($this->request["return_url"]) ? urlencode($this->request["return_url"]) : "",
			"subgroup" => $this->subgroup,
		) + (isset($this->request["extraids"]) && is_array($this->request["extraids"]) ? array("extraids" => $this->request["extraids"]) : array());

		if (method_exists($this->inst,"callback_mod_reforb"))
		{
			$this->inst->callback_mod_reforb(&$argblock);
		};

		$submit_action = "submit";

		$form_submit_action = $this->forminfo(array(
			"form" => $use_form,
			"attr" => "onsubmit",
		));

		if (!empty($form_submit_action))
		{
			$submit_action = $form_submit_action;
		}

		// forminfo can override form post method
		$form_submit_method = $this->forminfo(array(
			"form" => $use_form,
			"attr" => "method",
		));
			
		$method = "POST";
		if (!empty($form_submit_method))
		{
			$method = "GET";
		};

		$cli->finish_output(array(
			"method" => $method,
			"action" => $submit_action,
			// hm, dat is weird!
			"submit" => isset($gdata["submit"]) ? $gdata["submit"] : "",
			"data" => $argblock,
		));

		extract($args);
		if (empty($content))
		{
			$content = $cli->get_result(array(
				"raw_output" => $this->raw_output,
			));
		};
		
		$rv =  $this->gen_output(array(
			"parent" => $this->parent,
			"content" => isset($content) ? $content : "",
		));
		return $rv;
	}

	/** Saves the data that comes from the form generated by change 
		
		@attrib name=submit params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit($args = array())
	{
		// check whether this current class is based on class_base
		$this->init_class_base();
		$this->orb_action = $args["action"];

		$this->is_translated = 0;

		// object framework does it's own quoting
		//$this->quote($args);
		extract($args);


		$request = $args;

		// I need to know the id of the configuration form, so that I
		// can load it. Reason being, the properties can be grouped
		// differently in the config form then they are in the original
		// properties
		$this->is_rel = false;
		if (!empty($id))
		{
			// aha .. so .. if we are editing an relation object, then set $this->is_rel to true
			$_tmp = $this->get_object($id);
			if ($_tmp["class_id"] == CL_RELATION)
			{
				$this->is_rel = true;
			};
		};

		$args["rawdata"] = $args;
		$save_ok = $this->process_data($args);


		$args = array(
			"id" => $this->id,
			"group" => $group,
			"return" => $args["return"],
			"period" => aw_global_get("period"),
			"alias_to" => $request["alias_to"],
			"return_url" => $request["return_url"],
		) + ( (isset($extraids) && is_array($extraids)) ? $extraids : array());

		if (!$save_ok)
		{
			$args["parent"] = $request["parent"];
			if ($this->new)
			{
				$action = "new";
				unset($args["id"]);
			}
			else
			{
				$action = "change";
			};
		}
		else
		{
			$action = "change";
		};
		$orb_class = get_class($this->orb_class);
	
		if ($save_ok)
		{
			// logging should be defined by the form info
			$this->log_obj_change();
			// as well as this
			if (method_exists($this->inst,"callback_mod_retval"))
			{
				$this->inst->callback_mod_retval(array(
					"action" => &$action,
					"args" => &$args,
					"form_data" => &$request,
					"request" => &$request,
					"orb_class" => &$orb_class,
					"clid" => $this->clid,
					"new" => $this->new,
				));
			};
		};

		// and I need a workaround for this id_only thingie!!!

		// rrrr, temporary hack
		if (isset($this->id_only))
		{
			$retval = $this->id;
		}
		else
		{
			//$use_orb = true;
			if (!empty($request["section"]))
			{
				$args["section"] = $request["section"];
				$args["_alias"] = get_class($this);
				$use_orb = false;
			};
			if ($request["XUL"])
			{
				$args["XUL"] = 1;
			};
			$retval = $this->mk_my_orb($action,$args,$orb_class);
			if ($args["return"] == "id")
			{
				$retval = $this->id;
			};

		};
		return $retval;
	}

	////
	// ! Log the action
	function log_obj_change()
	{
		$name = $this->obj_inst->name();

		$syslog_type = ST_CONFIG;
		if (!empty($this->classinfo['syslog_type']))
		{
			$syslog_type = @constant($this->classinfo['syslog_type']);
		}

		// XXX: if I want to save data that does not belong to 
		// objects table, then I don't want to log it like this --duke
		$this->_log($syslog_type, isset($this->new) ? SA_ADD : SA_CHANGE, $name, $this->id);
	}

	function get_cfgform_for_object($args = array())
	{
		// or, if configuration form should be loaded from somewhere
		// else, this is the place to do it

		$action = $args["change"];

		$retval = "";
		$cgid = false;

		// 1. if there is a cfgform specified in the url, then we will use that
		if (!empty($args["args"]["cfgform"]))
		{
			// I need additional check, whether that config form really exists!
			$cgid = $args["args"]["cfgform"];
			// I need to check whether that config form is really
			// a config form with correct subclass
			if ($this->object_exists($cgid))
			{
				return $cgid;
			};	
		};

		// 2. failing that, if there is a config form specified in the object metainfo,
		//  we will use it
		if (($action == "change") && $args["obj_inst"]->meta("cfgform_id") != "")
		{
			$cgid = $args["obj_inst"]->meta("cfgform_id");
			if ($this->object_exists($cgid))
			{
				return $cgid;
			};
		};
		
		// 3. failing that too, we will check whether this class has a default cfgform
		// and if so, use it
		if ($this->clid == CL_DOCUMENT)
		{
			// I should be able to override this from the doc class somehow
			$def_cfgform = aw_ini_get("document.default_cfgform");
			if (!empty($def_cfgform) && $this->object_exists($def_cfgform))
			{
				return $cgid;
			}
		
			/*
			$cfgu = get_instance("cfg/cfgutils");
			$def = $this->get_file(array("file" => (aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml")));
			list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
			$this->classinfo = $cfgu->get_classinfo();
			*/
		};

		// okey, I need a helper class. Something that I can create, something can load
		// properties into and then query them. cfgform is taken, what name will I use?

		

		// right now .. only document class has the default config form .. or the default
		// file. Ungh.

		// 4. failing that, we will check whether this class has a default cfgform _file_
		// and if so, use it

		// 5. if all above fails, simply load the default properties. that means do nothing
		return false; 
	}
	
	////
	// !This checks whether we have all required data and sets up the correct
	// environment if so.
	function init_class_base()
	{
		// only classes which have defined properties
		// can use class_base
		
		// create an instance of the class servicing the object ($this->inst)
		// set $this->clid and $this->clfile
		$cfgu = get_instance("cfg/cfgutils");
		$orb_class = $this->cfg["classes"][$this->clid]["file"];
		if (empty($orb_class) && is_object($this->orb_class))
		{
			$orb_class = get_class($this->orb_class);
		};

		if (empty($orb_class) && is_string($this->orb_class))
		{
			$orb_class = $this->orb_class;
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

		$clid = $this->clid;
		if (empty($clid) && method_exists($this->orb_class,"get_opt"))
		{
			$clid = $this->orb_class->get_opt("clid");
		};
		$clfile = $this->cfg["classes"][$clid]["file"];

		// temporary - until we switch document editing back to new interface
		if ($clid == 7)
		{
			$clfile = "doc";
		};

		// classes with no CLID use class_base too
		if (empty($clfile) && !$has_properties)
		{
			die("coult not identify object " . $this->clfile);
		};

		if (empty($clfile))
		{
			$this->clfile = $orb_class;
		}
		else
		{
			$this->clfile = $clfile;
		};
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
			$this->inst = get_instance($this->clfile);
		};
		
	}

	function gen_output($args = array())
	{
		$classname = $this->cfg["classes"][$this->clid]["name"];

		if (is_object($this->obj_inst))
		{
			$name = $this->obj_inst->name();
		};
		$return_url = !empty($this->request["return_url"]) ? urlencode($this->request["return_url"]) : "";
		// XXX: pathi peaks htmlclient tegema
		$title = isset($args["title"]) ? $args["title"] : "";
		if ($this->id)
		{
			if (empty($title))
			{
				$title = $name;
			};
			$parent = $this->obj_inst->parent();
		}
		else
		{
			if (empty($title))
			{
				$title = "Uus $classname";
			};
			$parent = $args["parent"];
		};

		// let the class specify it's own title
		if (method_exists($this->inst,"callback_gen_path"))
		{
			$title = $this->inst->callback_gen_path(array(
				"id" => $this->id,
				"parent" => $args["parent"],
			));
		};

		if (!empty($this->request["return_url"]))
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

		$no_yah = $this->classinfo(array("name" => "no_yah"));

		// but that doesn't really belong to classinfo
		if (empty($no_yah))
		{
			$this->mk_path($parent,$title,aw_global_get("period"));
		};

		$this->tp = get_instance("vcl/tabpanel");


		// I need a way to let the client (the class using class_base to
		// display the editing form) to add it's own tabs.

		$activegroup = isset($this->activegroup) ? $this->activegroup : $this->group;
		$activegroup = isset($this->action) ? $this->action : $activegroup;

		$activegroup = $this->use_group;

		$orb_action = isset($args["orb_action"]) ? $args["orb_action"] : "";

		if (empty($orb_action))
		{
			$orb_action = "change";
		};	
			
		$link_args = new aw_array(array(
			"id" => isset($this->id) ? $this->id : false,
			"group" => "",
			"return_url" => $return_url,
		));

		// so .. what .. do I add tabs as well now?
		$tab_callback = (method_exists($this->inst,"callback_mod_tab")) ? true : false;

		$hide_tabs = $this->classinfo["hide_tabs"];
		if (!$hide_tabs)
		{
			$groupinfo = $this->get_visible_groups();
			foreach($groupinfo as $key => $val)
			{
				if ($this->id)
				{
					$link_args->set_at("group",$key);
					if (aw_global_get("section"))
					{
						$link_args->set_at("section",aw_global_get("section"));
					};
					if ($_REQUEST["cb_part"])
					{
						$link_args->set_at("cb_part",$_REQUEST["cb_part"]);
					};
					if ($this->embedded)
					{
						$link_args->set_at("_alias",get_class($this));
					};
					$link = $this->mk_my_orb($orb_action,$link_args->get(),get_class($this->orb_class));
				}
				else
				{
					$link = !empty($val["active"]) ? "#" : "";
				};
				
				if (is_object($this->tr))
				{
					$commtrans = $this->tr->get_by_id("group",$key,"caption");
					if (!empty($commtrans))
					{
						$val["caption"] = $commtrans;
					};
				};

				$tabinfo = array(
					"link" => &$link,
					"caption" => &$val["caption"],
					"id" => $key,
					"obj_inst" => &$this->obj_inst,
					"request" => $this->request,
					"activegroup" => $activegroup,
				);

				$res = true;
				if ($tab_callback)
				{
					// mod_tab can block the display of a tab
					$res = $this->inst->callback_mod_tab($tabinfo);
				};

				if ($this->action == "search_aliases" || $this->action == "list_aliases")
				{
					unset($val["active"]);
				};

				if ($res !== false)
				{
					// so, how do I figure out
					$this->tp->add_tab(array(
						"id" => $tabinfo["id"],
						"level" => empty($val["parent"]) ? 1 : 2,
						"parent" => $val["parent"],
						"link" => $tabinfo["link"],
						"caption" => $tabinfo["caption"],
						"active" => !empty($val["active"]) || ($key == $this->subgroup),
					));
				};
			};

		};

		if (1 == $this->classinfo["disable_relationmgr"])
		{
			$this->classinfo["relationmgr"] = false;
		};


		// XX: I need a better way to handle relationmgr, it should probably be a special
		// property type instead of being hardcoded.

		// well, there is a "relationmgr" property type and if used the property is drawn
		// in an iframe. But what I really need is an argument to the group definition,
		// .. which .. makes the group into a relation manager. eh? Or perhaps I should
		// just go with the iframe layout thingie. This frees us from the unneccessary
		// wrappers inside the class_base.
		if (empty($this->request["cb_part"]) && $this->classinfo(array("name" => "relationmgr")))
		{
			$link = "";
			if (isset($this->id))
			{
				$link = $this->mk_my_orb("list_aliases",array(
					"id" => $this->id,
					"return_url" => $return_url),
				get_class($this->orb_class));
			};

			$this->tp->add_tab(array(
				"id" => "list_aliases",
				"link" => $link,
				"caption" => "Seostehaldur",
				"active" => isset($this->action) && (($this->action == "list_aliases") || ($this->action == "search_aliases")),
				"disabled" => empty($this->id),
			));
		};

		$vars = array();

		$vars["content"] = $args["content"];

		if (isset($this->raw_output))
		{
			return $args["content"];
		}
		else
		{
			return $this->tp->get_tabpanel($vars);
		};
	}

	////
	// !Returns a list of properties for generating an output
	// or saving data. 
	// DEPRECATED!!
	function get_active_properties($args = array())
	{

		$no_group = !empty($args["all"]) ? $args["all"] : false;

		$this->get_all_properties(array(
			"classonly" => isset($args["classonly"]) ? $args["classonly"] : "",
			"content" => isset($args["content"]) ? $args["content"] : "",
			"rel" => isset($args["rel"]) ? $args["rel"] : "",
			"type" => isset($args["type"]) ? $args["type"] : "",
			"form" => isset($args["form"]) ? $args["form"] : "",
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

		// this does something with second level groups
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
		
		$retval = array();

		foreach($this->all_props as $key => $val)
		{
			// multiple groups for properties are supported too
			// no_group needs to return all properties
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
					{
						$property_list[$key] = $tmp;
					}
					elseif ($args["load_defaults"] && !empty($tmp["default"]))
					{
						// what exactly does this thing do?
						$tmp["group"] = $this->activegroup;
						$property_list[$key] = $tmp;
					}
				};
			}
			else
			{
				$property_list[$key] = $val;
			};

		};

		// I need to replace this with a better check if I want to be able
		// to use config forms in other situations besides editing objects

		foreach($property_list as $key => $val)
		{
			$property = $this->all_props[$key];

			// give it the default value to silence warnings
			$property["store"] = isset($property["store"]) ? $property["store"] : "";
			// it escapes me why a property would not have a type. but some do not. -- duke
			$property["type"] = isset($property["type"]) ? $property["type"] : "";


			if (isset($property_list[$key]["caption"]))
			{
				$property["caption"] = $property_list[$key]["caption"];
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

		return $retval;
	}

	////
	// !Load all properties for the current class
	// DEPRECATED!!!!
	function get_all_properties($args = array())
	{
		$filter = $args["rel"] ? array("rel" => 1) : "";

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
			if (!empty($args["form"]))
			{
				$filter["form"] = $args["form"];
			};

			$_all_props = $cfgu->load_properties(array(
				"file" => empty($this->clid) ? $this->clfile : "",
				"clid" => $this->clid,
				"filter" => $filter,
			));
		};

		if (!is_array($this->classinfo))
		{
			$this->classinfo = array();
		};
		
		$clif = new aw_array($cfgu->get_classinfo());
		$this->classinfo = $this->classinfo + $clif->get();
		$this->relinfo = $cfgu->get_relinfo();
		$this->forminfo = $cfgu->get_forminfo();

		// this comes from the forum thingie
		if (is_array($this->classconfig))
		{
			$this->classinfo = array_merge($this->classinfo,$this->classconfig);
		};
		

		$group_el_cnt = $this->all_props = array();

		// use the group list defined in the config form, if we are indeed using a config form
		if (!is_array($grplist))
		{
			$grplist = $cfgu->get_groupinfo();
		};

		$this->grp_children = array();

		// I need a hook somewhere to add those dynamic properties
		
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

		//var_dump($this->cfgform_id);

		$tmp = empty($this->cfgform_id) ? $_all_props : $proplist;
		foreach($tmp as $k => $val)
		{
			// if a config form is loaded, then ignore stuff that isn't
			// defined in there. I really shouldn't cause any problems
			// with well working code.
			if (!empty($args["type"]) && $_all_props[$val["name"]]["type"] != $args["type"])
			{
				continue;
			};
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

				$allow_rte = $this->classinfo(array(
					"name" => "allow_rte",
				));

				if ($this->classinfo["allow_rte"] != 1)
				{
					unset($val["richtext"]);
				};
			}
			
			// if it is a translated object, then don't show properties that can't be translated
			if ($this->is_translated && $val["trans"] != 1 && $val["name"] != "is_translated")
			{
				continue;
			};

			if (!$this->is_translated && $val["name"] == "is_translated")
			{
				continue;
			}

			$argblock = array(
				"id" => isset($this->id) ? $this->id : "",
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


		$grpinfo = array();

		if (is_array($grplist))
		{
			foreach($grplist as $key => $val)
			{
				if (!empty($args["type"]) || in_array($key,array_keys($group_el_cnt)))
				{
					// skip the group, if it is not listed in the config form object
					if (!empty($this->cfgform_id) && empty($grplist[$key]) )
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

		$this->inst->all_props = $this->all_props;

		return $this->all_props;
	}

	function convert_element(&$val)
	{
		// no type? get out then
		if (empty($val["type"]))
		{
			return false;
		};

		if (($val["type"] == "toolbar") && is_object($val["toolbar"]))
		{
			$val["value"] = $val["toolbar"]->get_toolbar();
		};
		
		if (($val["type"] == "table") && is_object($val["vcl_inst"]))
		{
			$val["vcl_inst"]->sort_by();
			$val["value"] = $val["vcl_inst"]->draw();
		};

		if (($val["type"] == "relmanager") && is_object($val["vcl_inst"]))
		{
			$val["value"] = $val["vcl_inst"]->get_html();
		};
		
		if (($val["type"] == "releditor") && is_object($val["vcl_inst"]))
		{
			$val["value"] = $val["vcl_inst"]->get_html();
		};
		
		if (($val["type"] == "calendar") && is_object($val["vcl_inst"]))
		{
			$val["value"] = $val["vcl_inst"]->get_html();
		};

		if ($val["type"] == "date_select")
		{
			// set the date to "now" for empty date_selects
			if (empty($this->id))
			{
				$val["value"] = time();
			};
		}

		if (($val["type"] == "objpicker") && isset($val["clid"]) && defined($val["clid"]))
		{
			$val["type"] = "select";
			$filter = array(
				"class_id" => constant($val["clid"]),
				"lang_id" => array(),
			);

			if (isset($val["subclass"]))
			{
				$filter["subclass"] = constant($val["subclass"]);
			};

			$ol = new object_list($filter);

			$names = $ol->names();
			asort($names);

			$val["options"] = array("" => "") + $names;
		};

		if (empty($val["value"]) && ($val["type"] == "aliasmgr") && isset($this->id))
		{
			$link = $this->mk_my_orb("list_aliases",array("id" => $this->obj_inst->brother_of()),"aliasmgr",false,true);
			$val["value"] = "<iframe width='100%' name='aliasmgr' height='800' frameborder='0' src='$link'></iframe>";
			$val["no_caption"] = 1;
		};

		if (($val["type"] == "relpicker") && ($val["reltype"]))
		{
			$this->cfgu->el_relpicker_reltype(array(
				"id" => $this->target_obj,
				"val" => &$val,
				"relinfo" => $this->relinfo[$val["reltype"]],
			));
		};

	}

	////
	// !Figures out the value for property
	function get_value(&$property)
	{
		// cb_values comes from session and is set, if the previous process_request
		// run encounterend any PROP_ERRORS, this takes care of displaying the
		// error messages in correct places
		if (is_array($this->cb_values) && !empty($this->cb_values[$property["name"]]["value"]))
		{
			$property["value"] = $this->cb_values[$property["name"]]["value"];
			if (!empty($this->cb_values[$property["name"]]["error"]))
			{
				$property["error"] = $this->cb_values[$property["name"]]["error"];
			};
		};

		// this was implemented for BDG, because I needed to allow the user to 
		// choose one connected image to be used as the flyer for an event.
		// would be nice to get rid of this.
		if ($property["type"] == "relpicker" && isset($property["pri"]))
		{
			$realclid = constant($property["clid"]);
			$q = sprintf("SELECT target FROM aliases WHERE source = %d AND pri = %d AND type = %d",
					$this->id,$property["pri"],$realclid);
			$property["value"] = $this->db_fetch_field($q,"target");
		}
		else
		// if this is a new object and the property has a default value, use it
		if (empty($this->id) && isset($property["default"]))
		{
			$property["value"] = $property["default"];
		}
		else
		// current time for datetime_select properties for new objects
		if (empty($this->id) && $property["type"] == "datetime_select")
		{
			$property["value"] = time();
		}
		else
		// this values thingie is a hack
		if (isset($this->values) && is_array($this->values))
		{
			if (isset($this->values[$property["name"]]))
			{
				$property["value"] = $this->values[$property["name"]];
			};
		}
		else
		if ( 	/*($property["trans"] == 0) &&*/
			empty($property["emb"]) &&
			is_object($this->obj_inst) &&
			$this->obj_inst->is_property($property["name"]) && 
			$this->obj_inst->prop($property["name"]) != NULL )
		{
			$property["value"] = $this->obj_inst->prop($property["name"]);
		}

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

		if (is_object($args["obj_inst"]))
		{
			$this->obj_inst = $args["obj_inst"];
			$this->id = $this->obj_inst->id();
		};

		// only relation object uses this. But hey, if the relation object
		// thingie is now done differently then I do not need this, yees?
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

		$resprops = array();

		$argblock = array(
			"request" => isset($this->request) ? $this->request : "",
			"obj_inst" => &$this->obj_inst,
			"groupinfo" => &$this->groupinfo,
			"new" => $this->new,
		);

		/*
		if ($this->is_rel)
		{
			$conn = $this->_get_connection_for_relobj($this->obj_inst);
			var_dump($conn);
		};
		*/

		$this->cfgu = get_instance("cfg/cfgutils");

		$remap_children = false;

		// First we resolve all callback properties, so that get_property calls will
		// be valid for those as well
		$has_rte = false;
		foreach($properties as $key => $val)
		{

			if (isset($val["callback"]) && method_exists($this->inst,$val["callback"]))
			{
				$meth = $val["callback"];
				$argblock["prop"] = &$val;
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
			elseif ($val["type"] == "releditor")
			{
				if (!is_object($val["vcl_inst"]))
				{
					classload("vcl/releditor");
					$val["vcl_inst"] = new releditor();
				};
				$argblock["prop"] = &$val;
				$target_reltype = @constant($val["reltype"]);
				$argblock["prop"]["reltype"] = $target_reltype;
				$argblock["prop"]["clid"] = $this->relinfo[$target_reltype]["clid"];
				$relres = $val["vcl_inst"]->init_rel_editor($argblock);

				if (is_array($relres))
				{
					foreach($relres as $rkey => $rval)
					{
						$this->convert_element(&$rval);
						$resprops[$rkey] = $rval;
					};
				};

			}
			elseif ($val["type"] == "form")
			{
				// I need a list of those bloody properties, eh?
				// how?
				$filter = array("form" => $val["sform"]);
				$cfgu = get_instance("cfg/cfgutils");

				// oh, but that is wrong .. I need to query the class somehow
				// and not read properties directly. They properties _need_
				// to come through that classes get_property and whatever else
				// calls

				// so .. I need to load the class instance, invoke get_property 
				// calls on it .. and then get the results and inject those
				// into my output stream. Uh, that's going to be hard.

				$_all_props = $cfgu->load_properties(array(
					"file" => $val["sclass"],
					"filter" => $filter,
				));

				// and how I get the class_instance?
				$clx_name = "crm/" . $val["sclass"];
				$clx_inst = get_instance($clx_name);

				$clx_inst->orb_class = $clx_name;
				$clx_inst->init_class_base();

				// this needs to change the form method, urk, urk
				$clx_inst->request = $this->request[$val["name"]];

				$xprops = $clx_inst->parse_properties(array(
					"properties" => $_all_props,
					"name_prefix" => $val["name"],
				));

				foreach($xprops as $rkey => $rprop)
				{
					$rprop["emb"] = 1;
					$resprops[$rkey] = $rprop;
				};

			}
			else
			{
				$resprops[$key] = $val;
			};

			if (isset($val["richtext"]) && 1 == $val["richtext"])
			{
				$has_rte = true;
			};
				

		}
		
		if (1 != $this->classinfo(array("name" => "allow_rte")))
		{
			$has_rte = false;
		}
		else
		{
			$has_rte = true;
		};


		$properties = $resprops;

		$resprops = array();

		// need to cycle over the property nodes, do replacements
		// where needed and then cycle over the result and generate
		// the output


		foreach($properties as $key => $val)
		{
			// XXX: need to get rid of that "text" index
			if ($val["name"] == "status" && $this->classinfo["no_status"]["text"] == 1)
			{
				continue;
			};
			
			if ($val["name"] == "comment" && $this->classinfo["no_comment"]["text"] == 1)
			{
				continue;
			};
				
			if (isset($val["emb"]) && $val["emb"] == 1)
			{
				// embedded properties have already passed through parse_properties
				// and there is no need to do that again
				$resprops[$key] = $val;
				continue;
			};

			// right now this only supports classificator
			// eventually all VCL component will have to implement their
			// own init_vcl_property method
			if ($this->vcl_register[$val["type"]])
			{
				$reginst = $this->vcl_register[$val["type"]];
				$ot = get_instance($reginst);
				if (is_callable(array($ot,"init_vcl_property")))
				{
					$ot->init_vcl_property(array(
						"property" => &$val,
						"clid" => $this->clid,
					));
				};
			};
			if (($val["type"] == "toolbar") && !is_object($val["toolbar"]))
			{
				classload("toolbar");
				$val["toolbar"] = new toolbar();
				if ($this->layout_mode == "fixed_toolbar")
				{
					$val["toolbar"]->set_opt("button_target","contentarea");
				};
			};


			if (($val["type"] == "relmanager") && !is_object($val["vcl_inst"]))
			{
				classload("vcl/relmanager");
				$val["vcl_inst"] = new relmanager();
			};

			if (($val["type"] == "table") && !is_object($val["vcl_inst"]))
			{
				classload("vcl/table");
				$val["vcl_inst"] = new aw_table(array(
					"layout" => "generic",
				));
			};
			
			if (($val["type"] == "calendar") && !is_object($val["vcl_inst"]))
			{
				classload("vcl/calendar");
				$val["vcl_inst"] = new vcalendar();
			};

			if (!empty($val["parent"]))
			{
				$remap_children = true;
			};

			$name = $val["name"];
			if (is_array($val) && $val["type"] != "callback" && $val["type"] != "submit")
			{
				$this->get_value(&$val);
			};

			if (is_object($this->tr))
			{
				$commtrans = $this->tr->get_by_id("prop",$val["name"],"comment");
				if (!empty($commtrans))
				{
					$val["comment"] = $commtrans;
				};
				$trans = $this->tr->get_by_id("prop",$val["name"],"caption");
				if (!empty($trans))
				{
					$val["caption"] = $trans;
				};
			}


			$argblock["prop"] = &$val;
			
			if ($val["type"] == "relmanager")
			{
				$target_reltype = @constant($val["reltype"]);
				$argblock["prop"]["reltype"] = $target_reltype;
				$argblock["prop"]["clid"] = $this->relinfo[$target_reltype]["clid"];
				$val["vcl_inst"]->init_rel_manager($argblock);
			};
			
			if ( isset($val["editonly"]) && empty($this->id))
			{
				// this should be form depenent
				continue;
			}
			else
			if ($val["type"] == "aliasmgr" && empty($this->id))
			{
				// do not show alias manager if  no id
				// and this too
				continue;
			}
			else
			if ( isset($val["newonly"]) && !empty($this->id))
			{
				// and this as well
				continue;
			};

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
			if ($val["type"] == "hidden")
			{
				$resprops[$name] = $val;
			}
			else
			{
				if ($val["type"] == "toolbar")
				{
					if ($this->layout_mode == "fixed_toolbar")
					{
						//$this->groupinfo = $this->groupinfo();
						foreach($this->groupinfo as $grp_id => $grp_data)
						{
							// disable all other buttons besides the general when
							// adding a new object
							if ($this->use_mode == "new" && $grp_id != $this->active_group)
							{
								continue;
							};
							$val["toolbar"]->add_button(array(
								"name" => "grp_" . $grp_id,
								"img" => empty($grp_data["icon"]) ? "" : $grp_data["icon"] . ".gif",
								"tooltip" => $grp_data["caption"],
								"target" => "contentarea",
								"url" => ($grp_id == "relationmgr") ? $this->mk_my_orb("change",array("id" => $this->id,"action" => "list_aliases","cb_part" => 1)) : $this->mk_my_orb("change",array("id" => $this->id,"group" => $grp_id,"cb_part" => 1)),
							));
							
						}
					};

					// if we are using rte, then add RTE buttons to the toolbar
					if (1 == $this->has_feature("has_rte"))
					{
						$rte = get_instance("vcl/rte");
						$rte->get_rte_toolbar(array(
							"toolbar" => &$val["toolbar"],
							"target" => $this->layout_mode == "fixed_toolbar" ? "contentarea" : "",
						));
					};
				};

				// this deals with subitems .. what a sucky approach
				if (is_array($val["items"]) && sizeof($val["items"]) > 0)
				{
					$tmp = array();
					foreach($val["items"] as $item)
					{
						$this->convert_element(&$item);
						$tmp[] = $item;
					};
					$val["items"] = $tmp;
				};

				$this->convert_element(&$val);

				// hm, how the fuck can the name be empty anyway?
				if (empty($name))
				{
					$name = $key;
				};
				$resprops[$name] = $val;
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
				// I need to rename the parent attribute as well
				if ($bracket > 0)
				{
					$pre = substr($el["name"],0,$bracket);
					$aft = substr($el["name"],$bracket);
					$newname = $args["name_prefix"] . "[$pre]" . $aft;
				}
				else
				{
					$newname = $args["name_prefix"] . "[" . $el["name"] . "]";
					if (!empty($el["parent"]))
					{
						$el["parent"] = $args["name_prefix"] . "_" . $el["parent"];
					};
				};
				$el["name"] = $newname;
				// just to get an hopefully unique name .. 
				$resprops[$args["name_prefix"] . "_" . $key] = $el;
			}
		}

		// now check whether any properties had parents. if so, remap them
		if ($remap_children)
		{
			$tmp = $resprops;
			foreach($tmp as $key => $prop)
			{
				if (!empty($prop["parent"]))
				{
					$resprops[$prop["parent"]]["items"][] = $prop;
					unset($resprops[$key]);
				}
			}
		}

		return $resprops;
	}

	// wrappers for alias manager

	/** Displays alias manager inside the configuration manager interface 
		
		@attrib name=list_aliases params=name all_args="1" 
		
		
		@returns
		
		
		@comment
		this means I have to generate a list of group somewhere

	**/
	function list_aliases($args = array())
	{
		extract($args);

		$this->init_class_base();

		$this->action = $action;
		$this->obj_inst = new object($args["id"]);
		$this->request = $args;

		$this->id = $args["id"];

		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));
		
		$cfgform_id = $args["args"]["cfgform"];
		if (empty($cfgform_id) && is_object($this->obj_inst))
		{
			$cfgform_id = $this->obj_inst->meta("cfgform_id");
		};

		$defaults = $this->get_property_group(array(
			"clid" => $this->clid,
			"clfile" => $this->clfile,
			"group" => $args["group"],
			"cfgform_id" => $cfgform_id,
		));

		$reltypes = $this->get_rel_types();

		$clid_list = array();
		if (sizeof($this->relinfo) > 0)
		{
			foreach($reltypes as $key => $val)
			{
				$rel_type_classes[$key] = $this->relclasses[$key];
			};
		}
		elseif (method_exists($this->inst,"callback_get_classes_for_relation"))
		{
			foreach ($reltypes as $key => $val)
			{
				$rel_type_classes[$key] = $this->inst->callback_get_classes_for_relation(array(
					"reltype" => $key,
				));

			}
		}

		if (!empty($args["cb_part"]))
		{
			$this->classinfo["no_yah"] = 1;
			$this->classinfo["hide_tabs"] = 1;
			aw_global_set("hide_yah",1);
		};

		$gen = $almgr->list_aliases(array(
			"id" => $id,
			"reltypes" => $reltypes,
			"rel_type_classes" => $rel_type_classes,
			"return_url" => !empty($return_url) ? $return_url : $this->mk_my_orb("list_aliases",array("id" => $id),get_class($this->orb_class)),
		));


		if ($args["no_op"] == 1)
		{
			return $gen;
		}
		else
		{
			return $this->gen_output(array("content" => $gen));
		}
	}

	////"log.txt"
	// !Displays alias manager search form inside the configuration manager interface
	/**  
		
		@attrib name=search_aliases params=name all_args="1" 
		
		
		@returns
		
		
		@comment

	**/
	function search_aliases($args = array())
	{
		extract($args);
		$this->init_class_base();

		$this->action = $action;
		$this->obj_inst = new object($args["id"]);
		$this->id = $args["id"];
		$this->request = $args;

		$almgr = get_instance("aliasmgr",array("use_class" => get_class($this->orb_class)));
		
		$cfgform_id = $args["args"]["cfgform"];
		if (empty($cfgform_id) && is_object($this->obj_inst))
		{
			$cfgform_id = $this->obj_inst->meta("cfgform_id");
		};

		$defaults = $this->get_property_group(array(
			"clid" => $this->clid,
			"clfile" => $this->clfile,
			"group" => $args["group"],
			"cfgform_id" => $cfgform_id,
		));

		$reltypes = $this->get_rel_types();

		$clid_list = array();
		if (sizeof($this->relinfo) > 0)
		{
			foreach($reltypes as $key => $val)
			{
				$rel_type_classes[$key] = $this->relclasses[$key];
			};
		}
		else
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

		if (empty($args["return_url"]))
		{
			$args["return_url"] = $this->mk_my_orb("change",array("id" => $id,"group" => $group),get_class($this->orb_class));
		};
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

	/** Handles the "saving" of relation list 
		
		@attrib name=submit_list params=name all_args="1" 
		
		
		@returns
		
		
		@comment

	**/
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

	/** Handles creating of new relations between the object and 
		
		@attrib name=addalias params=name all_args="1" 
		
		
		@returns
		
		
		@comment
		selected search results

	**/
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

	/** _serialize replacement for class_base based objects 
		
		@attrib name=ng_serialize params=name 
		
		@param oid required type=int
		
		@returns
		
		
		@comment

	**/
	function _serialize($args = array())
	{
		$this->init_class_base();
		$this->id = $args["oid"];

		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"all" => true,
		));

		$this->obj_inst = new object($this->id);
		$result = array();

		foreach($realprops as $key => $val)
		{
			$this->get_value(&$val);
			if (!empty($val["value"]) || $val["store"] != "no")
			{
				if ($val["type"] == "fileupload" && is_readable($val["value"]))
				{
					$name = $val["name"];
					$src = $this->get_file(array(
						"file" => $val["value"],
					));
					if (aw_global_get("__is_rpc_call"))
					{
						$result[$name] = array("base64" => $src);
					}
					else
					{
						$result[$name] = $src;
					};
					$mimeinfo = get_instance("core/aw_mime_types");
					$result[$name . "_type"] = $mimeinfo->type_for_file($val["value"]);
				}
				else
				{
					$result[$val["name"]] = $val["value"];
				};
			};
		}


		// also add relations
		$obj = new object($this->id);
		$conns = $obj->connections_from();
		foreach($conns as $conn)
		{
			// I also need to the connection type (at least)
			$result["connections"][] = array(
				"to" => $conn->prop("to"),
				"reltype" => $conn->prop("reltype"),
			);
		};

		if (aw_global_get("__is_rpc_call"))
		{
			$result["class_id"] = $this->clid;
			$retval = $result;
		}
		else
		{
			$retval = isset($args["raw"]) ? $result : aw_serialize($result, SERIALIZE_NATIVE);
		};
		return $retval;
	}

	////
	// !_unserialize replacement for class_base based objects
	function _unserialize($args = array())
	{
		$raw = isset($args["raw"]) ? $args["raw"] : aw_unserialize($args["str"]);
		$this->init_class_base();

		// quoting thins here _seriosly_ fucks us over with binary data
		//$this->quote(&$raw);

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

		// this object creation thingie should also only be defined in the forminfo

		if (empty($id))
		{
			$period = aw_global_get("period");
			$o = new object;
			$o->set_class_id($this->clid);
			$o->set_parent($parent);
			$o->set_status($status ? $status : STAT_ACTIVE);
			if ($period)
			{
				$o->set_period($period);
			}
			if (isset($rawdata["lang_id"]))
			{
				$lg = get_instance("languages");
				$o->set_lang($lg->get_langid($rawdata["lang_id"]));
			}

			$new = true;
			$this->id = $id;
			
		};

		// new object should not have any translation connections, so skip it
		if (!$new && $this->classinfo["trans"]["text"] == 1)
		{
			$o_t = get_instance("translate/object_translation");
			$t_list = $o_t->translation_list($this->id, true);
			if (in_array($this->id, $t_list))
			{
				$this->is_translated = 1;
			}
		}

		$this->new = $new;

		// the question is .. should I call set_property for those too?
		// and how do I load the stuff with defaults?
		if (!$new)
		{
			$this->obj_inst = new object($this->id);
		}
		else
		{
			$this->obj_inst = $o;
		};

		$filter = array();
		$filter["clfile"] = $this->clfile;
		$filter["clid"] = $this->clid;
		$filter["group"] = $group;
		$filter["rel"] = $this->is_rel;
		$filter["ignore_layout"] = 1;
		$filter["cfgform_id"] = $this->obj_inst->meta("cfgform_id");
	
		$properties = $this->get_property_group($filter);


		$pvalues = array();

		// this is here so I can keep things in the session
		$propvalues = array();
		$tmp = array();

		// first, gather all the values.
		foreach($properties as $key => $property)
		{
			//do not call set_property for edit_only properties when a new
			// object is created.
			if ($new && isset($property["editonly"]))
			{
				continue;
			};
			// status has already been written out, no need to do this again
			/*
			if ($property["name"] == "status")
			{
				continue;
			};
			*/
			// don't save or display un-translatable fields if we are editing a translated object
			if (!$this->is_translated && $property["name"] == "is_translated")
			{
				$xval = 1;
			}
			else
			if ($this->is_translated && $property["trans"] != 1)
			{
				continue;
			}

			$name = $property["name"];
			$type = $property["type"];
				
			$xval = isset($rawdata[$name]) ? $rawdata[$name] : "";

			if ($new && empty($xval) && !empty($property["default"]))
			{
				$xval = $property["default"];
			};

			if ($property["type"] == "checkbox")
			{
				// set value to 0 for unchecked checkboxes
				// well, shit, I need to figure out another way to do checkboxes
				// because if I do not have a group identifier with me, then
				// I might not be able to get a value for an item.

				// also .. what if there are readlonly attributes on some
				// fields .. those will then not have a value either and saving
				// such a form would case a disaster.
				$xval = (int)$xval;
			};

			if ($method == "bitmask" && empty($pvalues[$name]))
			{
				$pvalues[$name] = $this->obj_inst->prop($name);
			};

			$property["value"] = $xval;

			$tmp[$key] = $property;

			$propvalues[$property["name"]] = array(
				"value" => $property["value"],
			);
		}

		$realprops = $tmp;

		// now do the real job.
		foreach($realprops as $key => $property)
		{
			$name = $property["name"];
			$type = $property["type"];

                        $argblock = array(
                                "prop" => &$property,
                                "form_data" => &$rawdata,
				"request" => &$rawdata,
                                "new" => $new,
				"obj_inst" => &$this->obj_inst,
				"relinfo" => $this->relinfo,
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
				// XXX: what if one set_property changes a value and
				// other raises an error. Then we will have the original
				// value in the session. Is that a problem?
			};

			if (PROP_FATAL_ERROR == $status)
			{
				// so what the fuck do I do now?
				// I need to give back a sensible error message
				// and allow the user to correct the values in the form

				// I need to remember the values .. oh fuck, fuck, fuck, fuck
				
				// now register the variables in the session

				// I don't even want to think about serializers right about now.
				$propvalues[$name]["error"] = $argblock["prop"]["error"];
				aw_session_set("cb_values",$propvalues);
				return false;
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
			
			if ($property["type"] == "releditor")
			{
				/// XXX: right now I can only have one type of rel editor
				classload("vcl/releditor");
				$vcl_inst = new releditor();

				$argblock["prop"] = $property;
				$target_reltype = constant($property["reltype"]);
				$argblock["prop"]["reltype"] = $target_reltype;
				$argblock["prop"]["clid"] = $this->relinfo[$target_reltype]["clid"];


				$vcl_inst->process_releditor($argblock);
			};

			if ($property["store"] == "no")
			{
				continue;
			};

			if ($property["type"] == "relpicker" && $property["automatic"] == 1)
			{
				if (!$this->new)
				{
					$conns = $this->obj_inst->connections_from(array(
						"type" => $this->relinfo[$property["reltype"]]["value"],
					));
				};

				// no existing connection, create a new one
				if ($this->new || sizeof($conns) == 0)
				{
					if ($property["value"] != 0)
					{
						$this->obj_inst->connect(array(
							"to" => $property["value"],
							"reltype" => $this->relinfo[$property["reltype"]]["value"],
						));
					};
				}
				else
				{
					// alter existing connection
					list(,$existing) = each($conns);
					if ($property["value"] == 0)
					{
						$existing->delete();
					}
					else
					{
						$existing->change(array(
							"to" => $property["value"],
						));
					};
				};

			};
		
			/*
			if ($property["type"] == "callback")
			{
				continue;
			};
			*/

			if (($type == "date_select") || ($type == "datetime_select"))
			{
				if (is_array($rawdata[$name]))
				{
					$property["value"] = date_edit::get_timestamp($rawdata[$name]);
				};
			};

			if ($type == "relmanager")
			{
				$argblock["prop"] = $property;
				//$target_reltype = $this->relinfo[$property["reltype"]];
				//$argblock["prop"]["reltype"] = $target_reltype;
				//var_dump($this->relinfo);
				$argblock["prop"]["relinfo"] = $this->relinfo[$property["reltype"]];

				classload("vcl/relmanager");
				$vcl_inst = new relmanager();
				// XXX: would be nice if this could return an error message as well
				$vcl_inst->process_relmanager($argblock);
			};

			if (($type == "select") && isset($property["multiple"]))
			{
				$property["value"] = $this->make_keys($rawdata[$name]);
			};

			if ($property["method"] == "bitmask")
			{
				// shift to the left, shift to the right
				// pop, push, pop, push
				if ( ($pvalues[$name] & $property["ch_value"]) && !($rawdata[$name] & $property["ch_value"]))
				{
					$pvalues[$name] -= $property["ch_value"]; 
				}       
				elseif (!($pvalues[$name] & $property["ch_value"]) && ($rawdata[$name] & $property["ch_value"]))
				{
					$pvalues[$name] += $property["ch_value"];
				};     
			};


			// XXX: this is not good!
			if (!$new &&  ($type == "relpicker") && isset($property["pri"]) )
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
					$this->obj_inst->set_name($property["value"]);
				}
				else
				{
					$values[$name] = $property["value"];
				};
			}
			else
			{
				if ($property["method"] == "bitmask")
				{
					$val = ($property["ch_value"] == $property["value"]) ? $property["ch_value"] : 0;
					$this->obj_inst->set_prop($name,$val);
				} 
				else
				{
					
					$this->obj_inst->set_prop($name,$property["value"]);
				};
			};
		};

		if ($this->is_rel && is_array($values) && sizeof($values) > 0)
		{
			$def = $this->cfg["classes"][$this->clid]["def"];
			$_tmp = $this->get_object($this->id);
			$old = $_tmp["meta"]["values"];

			$old2 = $old[$def];
			$new = array_merge($old2,$values);
			$old[$def] = $new;

			$this->obj_inst->set_meta("values",$old);
		}

		// translation. if the object is is_translated or needs_translation, it gets the has_translation flag
		if ($this->obj_inst->flag(OBJ_NEEDS_TRANSLATION) || $this->obj_inst->flag(OBJ_IS_TRANSLATED) || 
			$this->obj_inst->prop("needs_translation") || $this->obj_inst->prop("is_translated"))
		{
			$this->obj_inst->set_flag(OBJ_HAS_TRANSLATION, true);
		}

		// gee, I wonder how many pre_save handlers do I have to fix to get this thing working
		// properly
		
		if (method_exists($this->inst,"callback_pre_save"))
		{
			$this->inst->callback_pre_save(array(
				"new" => $new,
				"id" => $this->id,
				"form_data" => &$args,
				"request" => &$args,
				"obj_inst" => &$this->obj_inst,
			));
		}

		// it is set (or not) on validate_cfgform
		if (isset($this->cfgform_id))
		{
			$this->obj_inst->set_meta("cfgform_id",$this->cfgform_id);
		};

		// this is here to solve the line break problems with RTE
		if (is_array($args["cb_nobreaks"]))
		{
			$this->obj_inst->set_meta("cb_nobreaks",$args["cb_nobreaks"]);
		}

		// there is a bug somewhere which causes certain objects to get a 
		// status of 0, until I figure it out, the first part of this if clause
		// deals with it -- duke
		if ($this->obj_inst->prop("status") == 0 || $this->classinfo["no_status"]["text"] == 1)
		{
			$this->obj_inst->set_status(STAT_ACTIVE);
		};

		$this->obj_inst->save();

		$this->id = $this->obj_inst->id();

		if ($new)
		{
			aw_session_set("added_object",$id);//axel häkkis

			if ($alias_to || $rawdata["alias_to"])
			{
				$_to = obj(($rawdata["alias_to"] ? $rawdata["alias_to"] : $alias_to));
				$_to->connect(array(
					"to" => $this->obj_inst->id(),
					"reltype" => ($rawdata["reltype"] ? $rawdata["reltype"] : $reltype),
				));
			};
		};

		if (method_exists($this->inst,"callback_post_save"))
		{
			// you really shouldn't attempt something fancy like trying
			// to set properties in there. Well, you probably can
			// but why would you want to, it's called post_save handler
			// for a reason
			$this->inst->callback_post_save(array(
				"id" => $this->obj_inst->id(),
				"obj_inst" => $this->obj_inst,
				"form_data" => &$args,
				"request" => &$args,
				"obj_inst" => &$this->obj_inst,
				"new" => $new,
			));
		}
		
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
		$reltypes = array();
		if (sizeof($this->relinfo) > 0)
		{
			foreach($this->relinfo as $item)
			{
				$reltypes[$item["value"]] = $item["caption"];
				$clidlist = array();
				$_tmp = new aw_array($item["clid"]);
				foreach($_tmp->get() as $clid)
				{
					$clidlist[] = $clid;
				};
				$this->relclasses[$item["value"]] = $clidlist;
			};
		}
		elseif (method_exists($this->inst,"callback_get_rel_types"))
		{
			$reltypes = $this->inst->callback_get_rel_types();
		}

		$reltypes[0] = "alias";
		return $reltypes;
	}

	/**  
		
		@attrib name=view params=name 
		
		@param id required type=int
		@param group optional
		@param period optional
		
		@returns
		
		
		@comment

	**/
	function view($args = array())
	{
		// create an instance of the class servicing the object ($this->inst)
		// create an instance of the datasource ($this->ds)
		// set $this->clid and $this->clfile (Do I need the latter at all?)
		$this->init_class_base();

		extract($args);

		$this->id = $id;
		$this->obj_inst = new object($id);

		// get a list of active properties for this object
		// I need an easy way to turn off individual properties
		$realprops = $this->get_active_properties(array(
				"clfile" => $this->clfile,
				"group" => $args["group"],
		));

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

	function alter_property($arr)
	{
		$arr["type"] = "text";
	}

	////
	// !Returns a list of config forms used by this
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

	// needs either clid or clfile
	function get_property_group($arr)
	{
		// load defaults (from the generated properties XML file) first

		$filter = array();
		if (!empty($arr["form"]))
		{
			$filter["form"] = $arr["form"];
		};
		if (!empty($arr["rel"]))
		{
			$filter["rel"] = 1;
		};

		// XXX: add some checks
		$all_properties = $this->load_defaults(array(
			"clid" => $arr["clid"],
			"clfile" => $arr["clfile"],
			"filter" => $filter,
		));

		// I could use a different approach here ... for example, if I'm saving then
		// only the properties that should be saved should be returned. or not?

		$this->features["has_rte"] = false;

		$cfg_props = $all_properties;

		$tmp = array();

		if (is_oid($arr["cfgform_id"]) && $this->object_exists($arr["cfgform_id"]))
		{
			$cfg_props = $this->load_from_storage(array(
				"id" => $arr["cfgform_id"],
			));


			// if there is a bug in config form which caused the groupdata
			// to be empty, then this is the place where we should fix it.	
		}
		else
		{
			// no config form? alright, load the default one then!
			if ($arr["clid"] == CL_DOCUMENT )
			{
				$def_cfgform = aw_ini_get("document.default_cfgform");
				if (is_oid($def_cfgform) && $this->object_exists($def_cfgform))
				{
					$cfg_props = $this->load_from_storage(array(
						"id" => $def_cfgform,
					));
				}
				else
				{
					list($cfg_props,$grplist) = $this->load_from_file();
					if (empty($this->groupinfo) || !empty($grplist))
					{
						$this->groupinfo = $grplist;
					};
				};
			};
		};

		// I need group and caption from each one

		$first_subgrp = array();
		$groupmap = $rgroupmap = array();

		foreach($this->groupinfo as $gkey => $ginfo)
		{
			if (!empty($ginfo["parent"]) && empty($first_subgrp[$ginfo["parent"]]))
			{
				$first_subgrp[$ginfo["parent"]] = $gkey;
			};

			if (!empty($ginfo["parent"]))
			{
				$groupmap[$ginfo["parent"]][] = $gkey;
				$rgroupmap[$gkey] = $ginfo["parent"];
			};
		}

		/// XXX: group remapping is NOT done!

		// that is all nice and perty .. but. I also need to take into account the
		// fact that .. how the hell do I put properties into those dynamic groups?

		// the very default group comes from arr
		if (isset($this->groupinfo[$arr["group"]]))
		{
			$use_group = $arr["group"];
		}
		else
		{
			// use first group as default
			reset($this->groupinfo);
			list($use_group,) = each($this->groupinfo);

			// maybe some other group is set a default? scan them;
			// latter default overwrites former, otherwise it would
			// not be possible for a class to override the default group
			foreach($this->groupinfo as $gkey => $ginfo)
			{
				if (isset($ginfo["default"]))
				{
					$use_group = $gkey;
				};
			};
		};

		// I need to detect whether a group has children groups. If it has, then all
		// properties that belong to this group will have to be remapped to the
		// first subgroup

		$this->use_group = $use_group;

		if ($rgroupmap[$use_group])
		{
			$this->parent_group = $rgroupmap[$use_group];
		}
		elseif (isset($groupmap[$use_group][0]))
		{
			$this->parent_group = $use_group;
			$use_group = $groupmap[$use_group][0];

		};


		$this->prop_by_group = array();

		// I need to create a remap map for groups .. but that is only a comfortability 
		// feature. I don't really _need_ to do this.
		// Leave out properties that are not listed in the class property declaration
		$tmp = array();
		foreach($cfg_props as $key => $val)
		{
			// ignore properties that are not defined in the defaults
			if (!$all_properties[$key])
			{
				continue;
			};

			$propdata = array_merge($all_properties[$key],$val);

			if (is_array($first_subgrp) && !is_array($propdata["group"]) && $first_subgrp[$propdata["group"]])
			{
				$val["group"] = $first_subgrp[$propdata["group"]];
			};
			
			// deal with properties belonging to multiple groups
			$propgroups = is_array($val["group"]) ? $val["group"] : array($val["group"]);
			$this->prop_by_group = array_merge($this->prop_by_group,array_flip($propgroups));
			// tyri, kuidas ma siis nyyd arvestan siin järgmise taseme asju ah?

			// skip anything that is not in the active group
			if (!in_array($use_group,$propgroups))
			{
				continue;
			};
			
			if (1 == $propdata["richtext"] && 0 == $this->classinfo["allow_rte"])
			{
				unset($propdata["richtext"]);
			};

			if ($propdata["richtext"] == 1)
			{
				$this->features["has_rte"] = true;
			};

			// return only toolbar, if this is a config form with fixed toolbar
			if (empty($arr["ignore_layout"]) && 1 == $this->classinfo["fixed_toolbar"] && empty($arr["cb_part"]))
			{
				if ($propdata["type"] != "toolbar")
				{
					continue;
				};
			};

			// shouldn't I do some kind of overriding?
			$tmp[$key] = $propdata;

		};

		$this->use_group = $use_group;

		// Very good. Now how do I deal with groups that contain subgroups?

		// okey, now that I have the group, I need to filter the properties
		// to return only the stuff that is the group that was requested
		//print "use_group = $use_group<br>";

		// if a group has properties and subgroups, then the properties need
		// to be remapped to the first child group. 

		// and I need to deal with multiple groups
		
		// and then I should leave out the groups with no properties

		return $tmp;



	}

	////
	// !Returns a list of properties having the requested type
	function get_properties_by_type($arr)
	{
		// load defaults first
		$all_properties = $this->load_defaults(array(
			"clid" => $arr["clid"],
			"clfile" => $arr["clfile"],
		));

		$rv = array();

		foreach($all_properties as $key => $val)
		{

			if ($val["type"] == $arr["type"])
			{
				$rv[$key] = $val;
			};
		};

		return $rv;

	}

	// and then I'll also need a method to load properties by their names
	// relmanager and releditor need it

	////
	// !id - config form id	
	function load_from_storage($arr)
	{
		extract($arr);
		$this->classinfo = array();
		$cfg_flags = array(
			"classinfo_fixed_toolbar" => "fixed_toolbar",
			"classinfo_allow_rte" => "allow_rte",
			"classinfo" => "disable_relationmgr",
		);
		$rv = false;
		if ($this->object_exists($id))
		{
			$cfgform_obj = new object($id);

			$prps = $cfgform_obj->meta("cfg_proplist");
			$rv = $prps;
			$grps = $cfgform_obj->meta("cfg_groups");

			foreach($cfg_flags as $key => $val)
			{
				$this->classinfo[$val] = $cfgform_obj->prop($key);
			};

			// sometimes the grplist is empty in config form.
			// I don't know why, but it is, and in this case
			// I'll load the groups from the file
			$sbc = $cfgform_obj->prop("subclass");

			// config form overloads original properties
			if ($sbc == CL_DOCUMENT)
			{
				list($prplist,$grplist) = $this->load_from_file();
				if (empty($grps))
				{
					$grps = $grplist;
				}
				else
				{
					foreach($grps as $gkey => $gitem)
					{
						if (empty($gitem["icon"]) && !empty($grplist[$gkey]["icon"]))
						{
							$grps[$gkey]["icon"] = $grplist[$gkey]["icon"];
						};
					};
				};
			};
			$this->groupinfo = $grps;
			// if the class has a default config file, then load 
			// that as well
		};
		return $rv;
	}

	// right now only the document class supports this
	function load_from_file()
	{
		$cfgu = get_instance("cfg/cfgutils");
		$def = $this->get_file(array("file" => (aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml")));
		$rv = $cfgu->parse_cfgform(array("xml_definition" => $def));
		if (!is_array($this->classinfo))
		{
			$this->classinfo = array();
		};
		$tmp = $cfgu->normalize_text_nodes($cfgu->get_classinfo());
		$this->classinfo = array_merge($cfgu->normalize_text_nodes($cfgu->get_classinfo()),$this->classinfo);
		return $rv;
	}

	// Defaults always get loaded, even if only for validation purposes
	function load_defaults($arr)
	{
		$cfgu = get_instance("cfg/cfgutils");

		$defaults = $cfgu->load_properties(array(
			"file" => empty($arr["clid"]) ? $arr["clfile"] : "",
			"clid" => $arr["clid"],
			"filter" => $arr["filter"],
		));
	
		$this->groupinfo = $cfgu->get_groupinfo();
		$this->forminfo = $cfgu->get_forminfo();
		if (!is_array($this->classinfo))
		{
			$this->classinfo = array();
		};
		$this->classinfo = array_merge($this->classinfo,$cfgu->normalize_text_nodes($cfgu->get_classinfo()));

		$this->relinfo = $cfgu->get_relinfo();

		return $defaults;

	}

	////
	// !Can be used to query classinfo
	function classinfo($arr)
	{
		return $this->classinfo[$arr["name"]];
	}

	// name - name
	// value - value
	function set_classinfo($arr)
	{
		$this->classinfo[$arr["name"]] = $arr["value"];
	}

	function groupinfo($arr = array())
	{
		return $this->groupinfo;
	}

	function set_groupinfo($name,$val)
	{
		$this->groupinfo[$name] = $val;
	}

	////
	// !Returns a list of currently visible groups, should be called after property retrieving
	function get_visible_groups()
	{
		$rv = array();
		foreach($this->groupinfo as $gkey => $gval)
		{
			// remove groups with no properties. CL_RELATION is one of the big 
			// users of this
			if (!isset($this->prop_by_group[$gkey]))
			{
				if (!empty($gval["parent"]))
				{
					continue;
				};
			};

			// skip all second level groups that are not children of the currently used group
			// alltho some output clients might actually want all groups and all properties
			if (!empty($gval["parent"]) && $gval["parent"] != $this->parent_group)
			{
				continue;
			};
			if ($gkey == $this->use_group)
			{
				$gval["active"] = 1;
			};

			if ($gkey == $this->parent_group)
			{
				$gval["active"] = 1;
			};
			$rv[$gkey] = $gval;
		};
		return $rv;
	}

	////
	// form - name of the form
	// attr - name of the attribute
	function forminfo($arr = array())
	{
		return $this->forminfo[$arr["form"]][$arr["attr"]];
	}

	function has_feature($name)
	{
		return $this->features[$name];
	}
};
?>
