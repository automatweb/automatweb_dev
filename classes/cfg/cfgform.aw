<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.8 2002/11/26 18:30:29 duke Exp $
// cfgform.aw - configuration form
// adds, changes and in general manages configuration forms

/*
	@default table=objects

	@property subclass type=generated generator=callback_get_class_list field=subclass
	@caption Klass
	
	@default field=meta
	@default method=serialize

	@property properties type=generated generator=callback_get_prop_list editonly=1
	@caption Omadused

	@property ord type=hidden
	@caption Jrk
	
	@classinfo corefields=name,comment,status
*/
class cfgform extends aw_template
{
	function cfgform($args = array())
	{
		$this->init(array(
			"clid" => CL_CFGFORM,
		));
	}
		
	function get_visible_properties()
	{
		$result = false;
		if ($this->obj["parent"])
		{
			$par = $this->get_menu($this->obj["parent"]);
			if ($par["meta"]["cfgmanager"])
			{
				$cfgmanager = get_instance("cfg/cfgmanager");
				$cfgo = $cfgmanager->get_active_cfg_object($par["meta"]["cfgmanager"]);
				$co = $this->get_object($cfgo);

				// need to get the alphanumeric id for this class
				$cp = $this->get_class_picker(array("field" => "file"));
				$aid = $cp[$this->obj["class_id"]];
				// this should now contain a list of properties for this object type
				$result = $co["meta"]["properties"][$aid];
				/*
				print "<pre>";
				print_r($filter_properties);
				print "</pre>";
				*/
			}
		};
		return $result;
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

	////
	// !Returns a list of checkboxes for selecting properties
	function callback_get_prop_list($args = array())
	{
		$sel_class = $args['obj']['subclass'];
		$cfgu = get_instance('cfg/cfgutils');
		$res = array();

		// now I need to create a VCL table
		load_vcl('table');
		$this->t = new aw_table(array('prefix' => 'cfgform'));
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
		
		
		$this->t->define_field(array(
			'name' => 'group',
			'caption' => 'Grupp',
			'talign' => 'center',
			'nowrap' => 1,
		));

		$has_props = $cfgu->has_properties(array('clid' => $sel_class));
		$clid = $sel_class;
		if ($has_props)
		{
			$selprops = $args['prop']['value'];
			$props = $cfgu->load_properties(array('clid' => $clid));
			foreach($props as $property)
			{
				if ($property['access'] != 'ro')
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
				}; // if !ro
			}; // forach $props

			$res[] = array(
				'value' => $this->t->draw(),
			);
		};

		return $res;

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
