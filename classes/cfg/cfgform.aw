<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.18 2003/07/28 13:53:42 duke Exp $
// cfgform.aw - configuration form
// adds, changes and in general manages configuration forms

/*
	@default table=objects
	@default group=general

	@property subclass type=select 
	@caption Klass

	@property ctype type=text editonly=1 field=subclass
	@caption Tüüp

	@default field=meta
	@default method=serialize

	@property xml_definition type=fileupload editonly=1
	@caption Uploadi vormi fail

	@property preview type=text store=no editonly=1
	@caption Definitsioon

	@property navtoolbar type=toolbar group=layout store=no no_caption=1
	@caption Toolbar

	@property layout type=callback callback=callback_gen_layout store=no group=layout no_caption=1
	@caption Layout
	
	@property availtoolbar type=toolbar group=avail store=no no_caption=1
	@caption Av. Toolbar

	@property availprops type=callback callback=callback_gen_avail_props store=no group=avail no_caption=1
	@caption Kõik omadused

	@property cfg_proplist type=hidden field=meta method=serialize
	@caption Omadused
	
	@property cfg_groups type=hidden field=meta method=serialize
	@caption Grupid

	@groupinfo layout caption=Layout submit=no
	@groupinfo avail caption="Kõik omadused" submit=no

	@classinfo relationmgr=yes

*/
define(RELTYPE_PROP_GROUP,1);
class cfgform extends class_base
{
	function cfgform($args = array())
	{
		$this->init(array(
			"clid" => CL_CFGFORM,
			"tpldir" => "cfgform",
		));
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_PROP_GROUP => "omaduste kataloog",
                );
        }

	function callback_get_classes_for_relation($args = array())
        {
		$retval = false;
		switch($args["reltype"])
		{
                        case RELTYPE_PROP_GROUP:
                                $retval = array(CL_PSEUDO);
                                break;
		}
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

			case "xml_definition":
				// I don't want to show the contents of the file here
				$data["value"] = "";
				break;

			case "preview":
				$data["value"] = "";
				break;

			case "extract":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("extract",array("id" => $args["obj"]["oid"])),
					"caption" => $data["caption"],
				));
				break;

			case "subclass":
				$retval = PROP_IGNORE;
				if (empty($args["obj"]))
				{
					$retval = PROP_OK;
					$cx = get_instance("cfg/cfgutils");
					$class_list = new aw_array($cx->get_classes_with_properties());
					$cp = get_class_picker(array("field" => "def"));
					foreach($class_list->get() as $key => $val)
					{
						$data["options"][$key] = $val;
					};	
				};
				break;

			case "ctype":
				classload("icons");
				$iu = html::img(array(
					"url" => icons::get_icon_url($args["obj"]["subclass"],""),
				));
				$data["value"] = $iu . " " . $this->cfg["classes"][$args["obj"]["subclass"]]["name"];
				break;

			case "navtoolbar":
				$this->gen_navtoolbar($args);
				break;
			
			case "availtoolbar":
				$this->gen_availtoolbar($args);
				break;
		};
		return $retval;
	}

	function callback_pre_edit($arr)
	{
		$this->_init_cfgform_data($arr["coredata"]);
	}

	function _init_cfgform_data($obj)
	{
		$fl = $this->cfg["classes"][$obj["subclass"]]["file"];
		if ($fl == "document")
		{
			$fl = "doc";
		};
		$inst = get_instance($fl);
		$this->all_props = $inst->get_all_properties();

		$this->grplist = $obj["meta"]["cfg_groups"];
		$this->prplist = $obj["meta"]["cfg_proplist"];
	}

	function set_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;

		switch($data["name"])
		{
			case "cfg_proplist":
			case "cfg_groups":
				if (empty($data["value"]))
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "xml_definition":
				if ($_FILES[$data["name"]]["type"] !== "text/xml")
				{
					$retval = PROP_IGNORE;
				}
				else
				if (!is_uploaded_file($_FILES[$data["name"]]["tmp_name"]))
				{
					$retval = PROP_IGNORE;
				}
				else
				{
					$contents = $this->get_file(array(
						"file" => $_FILES[$data["name"]]["tmp_name"],
					));
					if ($contents)
					{
						$data["value"] = $contents;
					};
					$retval = $this->_load_xml_definition($contents);
				};
				break;

			case "subclass":
				// do not overwrite subclass if it was not in the form
				// hum .. this is temporary fix of course. yees --duke
				if (empty($args["form_data"]["subclass"]))
				{
					$retval = PROP_IGNORE;
				}
				// cfg_proplist is in "formdata" only if this a serialized object
				// being unserialized
				elseif ($args["new"] && empty($args["form_data"]["cfg_proplist"]))
				{
					// fool around a bit to get the correct data
					$subclass = $args["form_data"]["subclass"];
					$obj = array(
						"subclass" => $args["form_data"]["subclass"],
					);

					// now that's the tricky part ... this thingsbum overrides
					// all the settings in the document config form
					$this->_init_cfgform_data($obj);
					if ($subclass == CL_DOCUMENT)
					{
						$cfgu = get_instance("cfg/cfgutils");
						$def = join("",file(aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml"));
						list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
						$this->cfg_proplist = $proplist;
						$this->cfg_groups = $grplist;
					};
				};
				break;

			case "availprops":
				$this->add_new_properties($args);
				break;

			case "layout":
				$this->save_layout($args);
				break;
		}
		return $retval;
	}

	function _load_xml_definition($contents)
	{
		// right now I can load whatever I want, but I really should validate that stuff
		// first .. and keep in mind that I want to have as many relation pickers
		// as I want to.

		$cfgu = get_instance("cfg/cfgutils");
		list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $contents));
		$this->cfg_proplist = $proplist;
		$this->cfg_groups = $grplist;
	}
		
	function callback_pre_save($args = array())
	{
		$coredata = &$args["coredata"];
		if (isset($args["form_data"]["subclass"]))
		{
			$coredata["subclass"] = $args["form_data"]["subclass"];
		};
		if (isset($this->cfg_proplist))
		{
			uasort($this->cfg_proplist,array($this,"__sort_props_by_ord"));
			$coredata["metadata"]["cfg_proplist"] = $this->cfg_proplist;
		};
		if (isset($this->cfg_groups))
		{
			$coredata["metadata"]["cfg_groups"] = $this->cfg_groups;
		};
		return true;
	}

	////
	// !
	function callback_gen_layout($args = array())
	{
		$this->read_template("layout.tpl");
		$used_props = array();
		$by_group = array();

		if (is_array($this->grplist))
		{
			foreach($this->grplist as $key => $val)
			{
				// we should not have numeric group id-s
				// actually it's more about a few ghosts I had lying 
				// around, and this will get rid of them but we
				// really don't NEED numeric group id-s
				// /me does the jedi mind trick - duke
				if (!is_numeric($key))
				{
					$by_group[$key] = array();
				};
			};
		};

		if (is_array($this->prplist))
		{
			foreach($this->prplist as $property)
			{
				if (!empty($property["group"]))
				{
					$by_group[$property["group"]][] = $property;
				};
			};
		};

		$c = "";
		foreach($by_group as $key => $proplist)
		{
			$this->vars(array(
				"grp_caption" => $this->grplist[$key]["caption"],
				"grpid" => $key,
			));

			$sc = "";
			foreach($proplist as $property)
			{
				$prpdata = $this->all_props[$property["name"]];
				if (!$prpdata)
				{
					continue;
				};
				$used_props[$property["name"]] = 1;
				$this->vars(array(
					"prp_caption" => $property["caption"],
					"prp_type" => $prpdata["type"],
					"prp_key" => $prpdata["name"],
					"prp_order" => $property["ord"],
				));
				$sc .= $this->parse("property");
			};
			$this->vars(array(
				"property" => $sc,
			));
			$c .= $this->parse("group");
		};

		$this->vars(array(
			"group" => $c,
		));

		$item = $args["prop"];
		$item["value"] = $this->parse();
		return array($item);
	}

	function __sort_props_by_ord($el1,$el2)
	{
		return (int)($el1["ord"] - $el2["ord"]);
	}

	////
	// !
	function callback_gen_avail_props($args = array())
	{
		$this->read_template("avail_props.tpl");
		$used_props = array();

		if (is_array($this->prplist))
		{
			foreach($this->prplist as $property)
			{
				$prpdata = $this->all_props[$property["name"]];
				$used_props[$property["name"]] = 1;
			};
		};

		$av = "";
		$sc = "";
		foreach($this->all_props as $key => $property)
		{
			if (empty($used_props[$property["name"]]))
			{
				$this->vars(array(
					"prp_caption" => $property["caption"],
					"prp_type" => $property["type"],
					"prp_key" => $property["name"],
				));
				$sc .= $this->parse("avail_property");
			};
		}

		$this->vars(array(
			"avail_property" => $sc,
		));

		$this->vars(array(
			"avail" => $this->parse("avail"),
		));

		$item = $args["prop"];
		$item["value"] = $this->parse();
		return array($item);
	}

	function gen_navtoolbar($arr)
	{
		$id = $arr["obj"]["oid"];
		if ($id)
		{
			// which links do I need on the toolbar?
			// 1- lisa grupp
			$toolbar = &$arr["prop"]["toolbar"];

			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "javascript:document.changeform.submit()",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
			
			$toolbar->add_button(array(
				"name" => "delete",
				"tooltip" => "Kustuta valitud omadused",
				"url" => "javascript:document.changeform.subaction.value='delete';document.changeform.submit();",
				"imgover" => "delete_over.gif",
				"img" => "delete.gif",
			));

			$toolbar->add_separator();
			
			$toolbar->add_cdata("<small>Liiguta omadused gruppi:</small>");
			$opts = array();
			if (is_array($this->grplist))
			{
				foreach($this->grplist as $key => $grpdata)
				{
					$opts[$key] = $grpdata["caption"];
				};
			}
			else
			{
				$opts["none"] = "Ühtegi gruppi pole veel!";
			};
			
			$toolbar->add_cdata(html::select(array(
				"options" => $opts,
				"name" => "target",
			)));
			
			$toolbar->add_button(array(
				"name" => "move",
				"tooltip" => "Liiguta",
				"url" => "javascript:document.changeform.subaction.value='move';document.changeform.submit();",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
			
			$toolbar->add_separator();

			$toolbar->add_cdata("<small>Lisa grupp:</small>");
			$toolbar->add_cdata(html::textbox(array(
				"name" => "newgrpname",
				"size" => "20",
			)));
			
			$toolbar->add_button(array(
				"name" => "addgrp",
				"tooltip" => "Lisa grupp",
				"url" => "javascript:document.changeform.subaction.value='addgrp';document.changeform.submit()",
				"imgover" => "new_over.gif",
				"img" => "new.gif",
			));

			$toolbar->add_separator();
			$add_grp_url = $this->mk_my_orb("add_grp",array("id" => $id));
			//$toolbar->add_cdata($add_grp_url);



		}
	}
	
	function gen_availtoolbar($arr)
	{
		$id = $arr["obj"]["oid"];
		if ($id)
		{
			$toolbar = &$arr["prop"]["toolbar"];
			$opts = array();
			if (is_array($this->grplist))
			{
				foreach($this->grplist as $key => $grpdata)
				{
					$opts[$key] = $grpdata["caption"];
				};
			}
			else
			{
				$opts["none"] = "Ühtegi gruppi pole veel!";
			};

			$toolbar->add_cdata(html::select(array(
				"options" => $opts,
				"name" => "target",
			)));

			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "javascript:document.changeform.submit()",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
		}
	}

	function add_new_properties($arr)
	{
		$target = $arr["form_data"]["target"];
		// first check, whether a group with that id exists
		if (isset($arr["obj"]["meta"]["cfg_groups"][$target]))
		{
			$this->_init_cfgform_data($arr["obj"]);
			// and now I just have to modify the proplist, eh?
			$prplist = $this->prplist;
			$mark = $arr["form_data"]["mark"];
			if (is_array($mark))
			{
				foreach($mark as $pkey => $pval)
				{
					if ($this->all_props[$pkey])
					{
						$prplist[$pkey] = array(
							"name" => $pkey,
							"caption" => $this->all_props[$pkey]["caption"],
							"group" => $target,
						);
					};
				};
				$this->cfg_proplist = $prplist;
			};
		};
	}

	function save_layout($arr)
	{
		$subaction = $arr["form_data"]["subaction"];
		$this->_init_cfgform_data($arr["obj"]);
		switch($subaction)
		{
			case "addgrp":
				$newgrpname =$arr["form_data"]["newgrpname"];
				$grpid = strtolower(preg_replace("/\W/","",$newgrpname));
				if ((strlen($grpid) > 2) && empty($this->grplist[$grpid]))
				{
					$grplist = $this->grplist;
					$grplist[$grpid] = array(
						"caption" => $newgrpname,
					);
					$this->cfg_groups = $grplist;
				};
				break;

			case "delete":
				$mark = $arr["form_data"]["mark"];
				$prplist = $this->prplist;
				if (is_array($mark))
				{
					foreach($mark as $pkey => $val)
					{
						unset($prplist[$pkey]);
					};
					$this->cfg_proplist = $prplist;
				};
				break;

			case "move":
				$mark = $arr["form_data"]["mark"];
				$target = $arr["form_data"]["target"];
				$prplist = $this->prplist;
				if (is_array($mark))
				{
					foreach($mark as $pkey => $val)
					{
						$prplist[$pkey]["group"] = $target;
					};
					$this->cfg_proplist = $prplist;
				};
				break;
			default:
				// well, save the names then
				$grplist = $this->grplist;
				$prplist = $this->prplist;
				if (is_array($arr["form_data"]["grpnames"]))
				{
					foreach($arr["form_data"]["grpnames"] as $key => $val)
					{
						$grplist[$key]["caption"] = $val;
					};
					$this->cfg_groups = $grplist;
				};

				if (is_array($arr["form_data"]["prpnames"]))
				{
					foreach($arr["form_data"]["prpnames"] as $key => $val)
					{
						$prplist[$key]["caption"] = $val;
						$prplist[$key]["ord"] = $arr["form_data"]["prop_ord"][$key];
					};
					$this->cfg_proplist = $prplist;
				};

				break;

			// järjekorranumbritega on muidugi natuke raskem, ma peaksin neile
			// mingid default väärtused andma. Or it won't work. Or perhaps it will?
				
		};
	}	

	function callback_mod_reforb($args = array())
	{
		// we use this to make the toolbar magic work
		$args["subaction"] = "";
	}
};
?>
