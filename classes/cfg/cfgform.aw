<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.12 2003/04/24 07:47:35 duke Exp $
// cfgform.aw - configuration form
// adds, changes and in general manages configuration forms

/*
	@default table=objects
	@default group=general

	@property subclass type=callback callback=callback_get_class_list field=subclass
	@caption Klass

	@default field=meta
	@default method=serialize

	@property xml_definition type=fileupload editonly=1
	@caption Uploadi vormi fail

	@property preview type=text store=no editonly=1
	@caption Definitsioon
	
	@property property_list type=callback callback=callback_gen_property_list store=no group=prplist
	@caption Omadused

	@property group_list type=callback callback=callback_gen_group_list store=no group=grplist
	@caption Grupid

	@groupinfo prplist caption=Omadused
	@groupinfo grplist caption=Grupid
	
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
			case "xml_definition":
				// I don't want to show the contents of the file here
				$data["value"] = "";
				break;

			case "preview":
				$data["value"] = "here be dragons";
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
					$retval = $this->_load_xml_definition($contents);
				};
				break;

			case "subclass":
				// do not overwrite subclass if it was not in the form
				// hum .. this is temporary fix of course. yees --duke
				if (empty($args["form_data"]["subclass"]))
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "group_list":
				$metadata = &$args["metadata"];
				$metadata["cfg_groups"] = $args["form_data"]["cfg_group"];
				break;
			
			case "property_list":
				$metadata = &$args["metadata"];
				$old = $args["obj"]["meta"]["cfg_proplist"];
				foreach($args["form_data"]["cfg_property"] as $key => $val)
				{
					$old[$key] = array_merge($old[$key],$val);
				};
				$metadata["cfg_proplist"] = $old;
				break;
			
		}
		return $retval;
	}

	function _load_xml_definition($contents)
	{
		$cfgu = get_instance("cfg/cfgutils");
		list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $contents));
		$this->cfg_proplist = $proplist;
		$this->cfg_groups = $grplist;
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
		$cp = get_class_picker(array("field" => "def"));
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
		$coredata = &$args["coredata"];
		if (isset($args["form_data"]["subclass"]))
		{
			$coredata["subclass"] = $args["form_data"]["subclass"];
		};
		if (isset($this->cfg_proplist))
		{
			$coredata["metadata"]["cfg_proplist"] = $this->cfg_proplist;
			$coredata["metadata"]["cfg_groups"] = $this->cfg_groups;
		};
		return true;
	}

	////
	// !
	function callback_gen_group_list($args = array())
	{
		load_vcl("table");
                $t = new aw_table(array("xml_def" => "cfgforms/grplist"));
		$grplist = $args["obj"]["meta"]["cfg_groups"];
		foreach($grplist as $key => $val)
		{
			$t->define_data(array(
				"name" => html::textbox(array(
					"name" => "cfg_group[$key][caption]",
					"size" => 40,
					"value" => $val["caption"],
				)),
			));
		};
		$item = array(
			"type" => "text",
			"caption" => $args["prop"]["caption"],
			"value" => $t->draw(),
		);
		return array($item);
	}
	
	////
	// !
	function callback_gen_property_list($args = array())
	{
		load_vcl("table");
                $t = new aw_table(array("xml_def" => "cfgforms/prplist"));
		$prplist = $args["obj"]["meta"]["cfg_proplist"];
		// oh, but I need the whole list of properties here
		foreach($prplist as $key => $val)
		{
			$t->define_data(array(
				"name" => html::textbox(array(
					"name" => "cfg_property[$key][caption]",
					"size" => 40,
					"value" => $val["caption"],
				)),
				"type" => $val["type"],
			));
		};
		$item = array(
			"type" => "text",
			"caption" => $args["prop"]["caption"],
			"value" => $t->draw(),
		);
		return array($item);
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
