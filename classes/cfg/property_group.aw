<?php
// property_group.aw - Property group object
/*
	@default table=objects
	
	@property rootmenu type=select field=meta method=serialize
	@caption Rootmenüü

	@property subclass type=generated generator=callback_get_class_list field=subclass
	@caption Klassid

	@property generate editonly=1 type=text
	@caption Genereeri objektid

	@classinfo corefields=name,comment,status
*/
class property_group extends aw_template
{
        function property_group($args = array())
        {
                $this->init(array(
                        "clid" => CL_PROPERTY_GROUP,
                ));
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
			$nodes[] = array(
				"caption" => $val,
				"type" => "radiobutton",
				"name" => "subclass",
				"value" => $key,
				"checked" => checked($args["obj"]["subclass"] == $key),
			);
		};
		return $nodes;
	}

	function callback_get_property_list($args = array())
	{
		$sel_class = $args['obj']['subclass'];
		$cfgu = get_instance('cfg/cfgutils');
		$res = array();
		$has_props = $cfgu->has_properties(array('clid' => $sel_class));
		$clid = $sel_class;

		load_vcl('table');
		$this->t = new aw_table(array('prefix' => 'property_group'));
		$this->t->parse_xml_def($this->cfg['basedir'].'/xml/cool_table.xml');

                $this->t->define_field(array(
                        'name' => 'caption',
                        'caption' => 'Nimi',
                        'talign' => 'center',
                        'width' => 300,
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
                        'name' => 'ord',
                        'caption' => 'jrk',
                        'talign' => 'center',
                        'align' => 'center',
                        'nowrap' => 1,
                ));

		if ($has_props)
		{
			$selprops = $args['prop']['value'];
			$props = $cfgu->load_class_properties(array('clid' => $clid));
			foreach($props as $property)
			{
				$name = $property['name'];
				$caption = $property['caption'];
				$ord = $args["obj"]["meta"]["ord"][$name];
				$this->t->define_data(array(
					'caption' => $caption,
					'type' => $property["type"],
					'group' => $property['group'],
					'check' => html::checkbox(array('name' => "properties[$name]",'checked' => $selprops[$name])),
					'ord' => html::textbox(array('size' => 4, 'name' => "ord[$name]", 'value' => $ord)),
				));

			};

			$res[] = array(
				'value' => $this->t->draw(),
			);
		};
		return $res;
	}


	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "rootmenu":
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list();
				break;

			case "generate":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("generate",array("id" => $args["obj"]["oid"])),
					"caption" => "Genereeri",
				));
				break;

		}
		return PROP_OK;
	}

	function generate($args = array())
	{
		$id = $args["id"];
		$obj = $this->get_object($id);
		// retrieve a list of properties for this class_id
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_class_properties(array(
			"clid" => $obj["subclass"],
		));
		// create a menu under the rootmenu with the name of this object
		$name = $obj["name"];
		$comment = $obj["comment"];
		$parent = $obj["meta"]["rootmenu"];
		$subclass = $obj["subclass"];
		$grandparent = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"comment" => $comment,
			"class_id" => CL_PSEUDO,
			"status" => 2,
			"no_flush" => 1,
		));
		$q = "INSERT INTO menu (id,type) VALUES ($grandparent," . MN_CONTENT . ");";
		$this->db_query($q);
		$groups = array();
		$properties = array();
		foreach($props as $val)
		{
			$groupname = $val["group"];
			$groups[$groupname] = $groupname;
			$properties[$groupname][] = $val;
		};
		foreach($groups as $group)
		{
			print "creating group $group<br>";
			$group_id = $this->new_object(array(
				"parent" => $grandparent,
				"name" => $group,
				"class_id" => CL_PSEUDO,
				"status" => 2,
				"no_flush" => 1,
			));
			$q = "INSERT INTO menu (id,type) VALUES ($group_id," . MN_CONTENT . ");";
			$this->db_query($q);
			flush();
			sleep(1);

			foreach($properties[$group] as $key => $val)
			{
				print "<pre>";
				print_r($val);
				print "</pre>";
				$this->new_object(array(
					"parent" => $group_id,
					"name" => $val["caption"],
					"class_id" => CL_PROPERTY,
					"status" => 2,
					"subclass" => $subclass,
					"metadata" => $val,
					"no_flush" => 1,
				));
			};
		};
		$this->flush_cache();
		print "all done!<br>";
	}
};
?>
