<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.9 2003/02/05 03:54:31 duke Exp $
// cfgform.aw - configuration form
// adds, changes and in general manages configuration forms

/*
	@default table=objects
	@default group=general

	@property subclass type=callback callback=callback_get_class_list field=subclass
	@caption Klass

	@default field=meta
	@default method=serialize

	@property xml_definition type=fileupload 
	@caption Uploadi vormi fail

	@property preview type=text store=no	
	@caption Definitsioon
	
	property property_list type=callback callback=callback_get_prop_list editonly=1
	caption Omadused
	
	@classinfo corefields=name,comment,status
*/
class cfgform extends class_base
{
	function cfgform($args = array())
	{
		$this->init(array(
			"clid" => CL_CFGFORM,
		));
	}
	
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "generate":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("generate",array("id" => $args["obj"]["oid"])),
					"caption" => "Skaneeri",
					"target" => "_blank",
				));
				break;

			case "xml_definition":
				// I don't want to show the contents of the file here
				$data["value"] = "";
				break;

			case "preview":
				if ($args["obj"]["meta"]["xml_definition"])
				{
					$data["value"] = "<pre>" . htmlspecialchars($args["obj"]["meta"]["xml_definition"]) . "</pre>";
				};
				break;
		};
		return $retval;
	}

	function set_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
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
				};
		}
		return $retval;
	}

		
	////
	// !Returns a list of checkboxes for selecting classes
	function callback_get_class_list($args = array())
	{
		// this is only shown for new objects
		if ($args["obj"])
		{
			return false;
		};

		$cx = get_instance("cfg/cfgutils");
		$class_list = new aw_array($cx->get_classes_with_properties());
		$cp = $this->get_class_picker(array("field" => "def"));
		$nodes = array();
		$nodes[] = array("caption" => "Klassid");
		foreach($class_list->get() as $key => $val)
		{
			$ckey = $cp[$key];
			$nodes[$key] = array(
				"caption" => $val,
				"type" => "radiobutton",
				"name" => "subclass",
				"value" => $key,
				"checked" => checked($args["obj"]["subclass"] == $key),
			);
		};
		return $nodes;
	}

	////
	// !Returns a list of checkboxes for selecting properties
	function callback_get_prop_list($args = array())
	{

		// now I need to create a VCL table
		$obj = $this->get_object($args["obj"]["oid"]);
		load_vcl('table');
		$this->t = new aw_table(array('prefix' => 'cfgform'));
		$this->t->parse_xml_def($this->cfg['basedir'].'/xml/cool_table.xml');

		$this->t->define_field(array(
			'name' => 'caption',
			'caption' => 'Nimi',
			'talign' => 'center',
			'nowrap' => 1,
		));
		
		$this->t->define_field(array(
			'name' => 'type',
			'caption' => 'Tüüp',
			'talign' => 'center',
			'nowrap' => 1,
		));
		
		$this->t->define_field(array(
			'name' => 'check',
			'caption' => 'Vali',
			'talign' => 'center',
			'align' => 'center',
			'nowrap' => 1,
		));
		
		$this->t->define_field(array(
			'name' => 'group',
			'caption' => 'Grupp',
			'talign' => 'center',
			'nowrap' => 1,
		));

		$sel_class = $obj["subclass"];
		$cfgu = get_instance('cfg/cfgutils');
		$res = array();
		$has_props = $cfgu->has_properties(array('clid' => $sel_class));
		$clid = $sel_class;

		if ($has_props)
		{
			$selprops = $args['prop']['value'];
			$props = $cfgu->load_properties(array('clid' => $clid));
			foreach($props as $property)
			{
				$name = $property['name'];
				$caption = $property['caption'];
				$ord = $args["obj"]["meta"]["ord"][$name];
				$this->t->define_data(array(
					'caption' => $caption,
					'type' => $property["type"],
					'group' => $property['group'],
					'check' => html::checkbox(array(
						'name' => "properties[$name]",
						'checked' => $selprops[$name],
					)),
				));
			}; // forach $props

			$res[] = array(
				'value' => $this->t->draw(),
			);
		};
		return $res;

	}

	function callback_get_layout($args = array())
	{
		load_vcl('table');
		$this->t = new aw_table(false);
		$this->t->parse_xml_def($this->cfg['basedir'].'/xml/cool_table.xml');

		$this->t->define_field(array(
			'name' => 'caption',
			'caption' => 'Nimi',
			'talign' => 'center',
			'nowrap' => 1,
		));
		
		$this->t->define_field(array(
			'name' => 'type',
			'caption' => 'Tüüp',
			'talign' => 'center',
			'nowrap' => 1,
		));
		
		$this->t->define_field(array(
			'name' => 'ord',
			'caption' => 'jrk',
			'talign' => 'center',
			'align' => 'center',
			'nowrap' => 1,
		));

		// mix all the properties and property groups together
		$sel_class = $args['obj']['subclass'];
		$cfgu = get_instance('cfg/cfgutils');
		$properties = array();
		$has_props = $cfgu->has_properties(array('clid' => $sel_class));
		$clid = $sel_class;

		if ($has_props)
		{
			$selprops = $args['prop']['value'];
			$props = $cfgu->load_properties(array('clid' => $clid));
			foreach($props as $property)
			{
				$name = $property['name'];
				$caption = $property['caption'];
				$ord = $args["obj"]["meta"]["ord"][$name];
				if ($args["obj"]["meta"]["properties"][$name])
				{
					$properties[] = array(
						"name" => $property["name"],
						"caption" => $property["caption"],
						"type" => $property["type"],
						"ord" => $args["obj"]["meta"]["ord"][$name],
					);
				};
			};
		};

		// now, fetch information about aliases
		$aliases = new aw_array($args["obj"]["meta"]["aliases"][CL_PROPERTY_GROUP]);

		foreach($aliases->get() as $key => $val)
		{
			$name = "group_" . $val["target"];
			$properties[] = array(
				"name" => $name,
				"oid" => $val["target"],
				"caption" => $val["name"],
				"type" => "Omaduste grupp",
				"ord" => $args["obj"]["meta"]["ord"][$name],
			);
		};

		foreach($properties as $key => $val)
		{
			$name = $val["name"];
			$this->t->define_data(array(
				"caption" => $val["caption"],
				"type" => $val["type"],
				"ord" => html::textbox(array(
					"size" => 4,
					"name" => "ord[$name]",
					"value" => $val["ord"],
				)),
				"hidden_ord" => $val["ord"],
			));
		};

                $this->t->sort_by(array("field" => "hidden_ord"));

		$res[] = array(
			"no_caption" => 1,
			"value" => $this->t->draw(),
		);
		
		return $res;

	}

	function callback_pre_save($args = array())
	{
		if ($args["form_data"]["subclass"])
		{
			$coredata = &$args["coredata"];
			$coredata["subclass"] = $args["form_data"]["subclass"];
		};
		return true;
			
		if (!$args["form_data"]["ord"])
		{
			return false;
		};
		$coredata = &$args["coredata"];
		// resolve all property groups

		// this comes from the form
		$_tmp = new aw_array($coredata["metadata"]["ord"]);
		$ord = $_tmp->get();

		asort($ord,SORT_NUMERIC);

		// I want to enumerate all the properties
		$layout = array();
		$property_list = array();
		$item_ord = 0;
		$property_order = array();
		foreach($ord as $key => $val)
		{
			$lkey = $key;
			if ((int)$val != 0)
			{
				$item_ord = $val;
			}
			else
			{
				$item_ord++;
			};
				
			if (preg_match("/^group_(\d+)$/",$key,$m))
			{
				$oid = (int)$m[1];
				$prop_group = $this->get_object(array(
					"oid" => (int)$m[1],
					"class_id" => CL_PROPERTY_GROUP,
				));
				$_props = new aw_array($prop_group["meta"]["properties"]);
				$_ord = new aw_array($prop_group["meta"]["ord"]);
				$layout[$lkey]["caption"] = $prop_group["name"];
				foreach($_props->get() as $pname => $pval)
				{
					$layout[$lkey]["items"][] = $pname;
					$property_list[$pname] = 1;
				};
			}
			else
			{
				$layout[$lkey] = $key;
				$property_list[$lkey] = 1;
			};

			$property_order[$lkey] = $item_ord;
		};
		$coredata["metadata"]["layout"] = $layout;
		$coredata["metadata"]["property_list"] = $property_list;
		$coredata["metadata"]["ord"] = $property_order;
	}

	function generate($args = array())
	{
		extract($args);
		$obj = $this->get_object(array(
			"oid" => $id,
			"class_id" => CL_CFGFORM,
		));
		$sourcemenu = $obj["meta"]["sourcemenu"];
		$subclass = $obj["subclass"];

//                print "looking for properties for clid $subclass under $sourcemenu<br>";
		// first, retrieve a list of all menus
		$menus = $this->get_objects_below(array(
			"parent" => $sourcemenu,
			"class" => CL_PSEUDO,
			"active" => 1,
			"orderby" => "jrk",
		));

		$properties = array();

		// for each menu, retrieve the list of properties for that menu
		foreach($menus as $key => $val)
		{
			$id = $val["oid"];
			$properties[$id]["caption"] = $val["name"];
			$props = $this->get_objects_below(array(
				"parent" => $id,
				"class" => CL_PROPERTY,
				"active" => 1,
				"orderby" => "jrk",
			));

			foreach($props as $pkey => $pval)
			{
				$name = $pval["meta"]["name"];
				$properties[$id]["items"][$name] = array(
					"caption" => $pval["name"],
					"name" => $name,
//                                        "type" => $pval["meta"]["type"],
				);
			};
		};

		$this->upd_object(array(
			"oid" => $args["id"],
			"metadata" => array(
				"layout" => $properties,
			),
		));
		print "<pre>";
		print_r($properties);
		print "</pre>";
		print "all done!<br>";
	}

	function _serialize($arr)
	{
		extract($arr);
		$row = $this->get_object($oid);
		if (!$row)
		{
			return false;
		}
		unset($row["metadata"]);
		return serialize($row);
	}

	function _unserialize($arr)
	{
		extract($arr);
		$row = unserialize($str);
		$oid = $this->new_object(array(
			"parent" => $parent,
			"name" => $row["name"],
			"class_id" => CL_CFGFORM,
			"subclass" => $row["subclass"],
			"status" => $row["status"],
			"comment" => $row["comment"],
			"alias" => $row["alias"],
			"metadata" => $row["meta"],
		));

		return $oid;
	}



};
?>
