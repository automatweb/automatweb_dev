<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.6 2002/11/24 21:42:49 duke Exp $
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

	function get_cfgforms_by_class($args = array())
	{
		$clid = $args["clid"];
		$class_id = constant($clid);
		$folder = $this->cfg["cfgfolders"][$class_id];
		// XXX: check whether the directory actually exists, is inside
		// the AW tree and is readable
		if (!$folder)
		{
			return false;
		};

		// now, prep a list of files inside that directory
		$dircontents = $this->get_directory(array(
			"dir" => $folder,
		));

		// cycle over the files and figure out the names
		$flist = new aw_array($dircontents);
		$retval = array("" => "");
		foreach($flist->get() as $val)
		{
			$fqfn = $folder . "/$val";
			$contents = $this->get_file(array(
				"file" => $fqfn,
			));
			$data = aw_unserialize($contents);
			if ($data["oid"] && $data["name"] && $data["class_id"] == $class_id)
			{
				$retval[$val] = $data["name"];
			};

		};
		return $retval;
	}

	////
	// !Retrieves a config form from a file
	// clid(string) - symbolic id of the class
	// id(id) - file id
	function get_cfgform_from_file($args = array())
	{
		$folder = $this->cfg["cfgfolders"][constant($args["clid"])];
		if (!$folder)
		{
			return false;
		};
		$fqfn = $folder . "/" . $args["id"];
		$contents = $this->get_file(array(
			"file" => $fqfn,
		));
		$data = aw_unserialize($contents);
		$retval = false;
		$class_id = constant($args["clid"]);
		if ($data["oid"] && $data["name"] && $data["class_id"] == $class_id)
		{
			$retval = $data;
		};
		return $retval;
	}

	////
	// !called after a configuration form is saved
	function callback_post_save($args = array())
	{
		$id = (int)$args["id"];
		$cfgform = $this->get_object($id);
		$subclass = $cfgform["subclass"];
		$active_properties = $cfgform["meta"]["properties"];
		$ord = $cfgform["meta"]["ord"];
		$savefolder = $this->cfg["cfgfolders"][$subclass];
		/// XXX: better checking needed
		if ($this->cfg["save_to_files"] && is_array($active_properties) && is_array($ord))
		{
			// reap the stuff with no ords
			$useful_ord = array();
			foreach($ord as $key => $val)
			{
				if ($val)
				{
					$useful_ord[$key] = $val;
				};
			};

			$data = array(
				"name" => $cfgform["name"],
				"class_id" => $cfgform["subclass"],
				"oid" => $cfgform["oid"],
				"properties" => $active_properties,
				"property_order" => $useful_ord,
			);
			$ser = aw_serialize($data,SERIALIZE_XML,array("ctag" => "template"));
			$fname = $savefolder . "/template" . $id . ".xml";

			$this->put_file(array(
				"file" => $fname,
				"content" => $ser,
			));
		};
	}


};
?>
