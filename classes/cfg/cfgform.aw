<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.3 2002/11/12 16:23:55 duke Exp $
// cfgform.aw - configuration form
// adds, changes and in general manages configuration forms

/*
	@default table=objects
	@default field=meta
	@default method=serialize

	@property classes type=generated generator=callback_get_class_list 
	@caption Klassid

	@property properties type=generated generator=callback_get_prop_list group=advanced
	@caption Omadused

	@property ord type=hidden group=advanced
	@caption Jrk
*/
class cfgform extends aw_template
{
	function cfgform($args = array())
	{
		$this->init(array(
			"tpldir" => "cfgform",
			"clid" => CL_CFGFORM,
		));
	}

	////
	// !Use this instead of ch_form
	function change_properties($args = array())
	{
		$args["no_filter"] = 1;
		return $this->ch_form($args);
	}
	

	function ch_form($args = array())
	{
		extract($args);
		if (!is_object($clid))
		{
			// get lost!
			return false;
		};
	
		$this->clid = $clid;
		$this->parent = $parent;
		$this->reforb = $reforb;
		$this->no_filter = $no_filter;
		$this->submit = $submit;
		$this->no_filter = true;
		$this->obj = $obj;

		$this->odata = array_merge($obj,$obj["meta"]);

		// so first we retrieve the list of _all_ properties that apply
		// to this object

		// then we retrieve a list of _all_ visible properties
		// and then we cycle over the first, and if requested, filtering
		// out elements that are NOT in the second array.

		// Perhaps there is a way to make this easier?

		// like - first fetching the visible properties and then retrieving
		// only the keys that are in that array _or_ marked as private

		// get the generic properties
		$props = $this->get_obj_properties($this->odata);
		if (method_exists($clid,"get_properties"))
		{
			// the thing is - you can override the generic fields
			// in your get_properties - if for some weird reason
			// you'd want to do that.
			$props = array_merge($props,$clid->get_properties($this->odata));
		};


		$this->create_path();
		$this->start_form();
		$this->html = get_instance("html");

		#$this->filter_properties = $this->get_visible_properties();

		$this->level = 0;
		$this->path = array();
		$this->req_draw_properties($props);

		$this->end_form();

		return $this->tb;
	}
	
	// !That was all nice and good .. but I also need means to do the save queries
	// for me instead of leaving that up to the caller.
	function submit_properties($args = array())
	{
		extract($args);
		if (!is_object($clid))
		{
			// get lost!
			return false;
		};
		// I need to fetch all the properties once again as I do in 
		// form drawing code and also filter them in the same way
		// so that I don't overwrite stuff which does not exist in the form


		// And I need to figure out the best save strategy
		// in cases I need to store data into multiple fields


	}

	////
	// !This will cycle over the results of get_properties, doing
	// callbacks in the progress, if needed
	function req_draw_properties($block = array())
	{
		$this->level++;
		foreach($block as $key => $val)
		{
			if ($this->level > 1)
			{
				array_push($this->path,"[" . $key . "]");
			};

			if ($this->level == 1)
			{
				$this->name = $key;
			};

			if (is_array($val))
			{
				// we do not show the element
				$show = false;
				// unless now filtering was explicitly requested
				if ($this->no_filter)
				{
					$show = true;
				}
				// or we are in a nested deeped than 1 level
				elseif ($this->level > 1)
				{
					$show = true;
				}
				// or this property is marked as private - and therefore
				// is alway shown
				elseif ($val["private"])
				{
					$show = true;
				}
				// or the variable is in the whitelist
				elseif ($this->filter_properties[$key])
				{
					$show = true;
				};

				if ($show)
				{
					$val["name"] = $this->name . join("",$this->path);
					$this->draw_property($val);
				};

			}
			elseif (gettype($val) == "string")
			{
				if (method_exists($this->clid,$val))
				{
					$props = new aw_array($this->clid->$val($this->odata));
					$this->req_draw_properties($props->get());
				};
			};
		
			if ($this->level > 1)
			{
				array_pop($this->path);
			};
		};
		$this->level--;
	}
	

	function start_form()
	{
		$this->tb = sprintf("<form action='reforb.%s' method='post' name='changeform'>",aw_ini_get("ext"));
		$this->tb .= "\n<table border='0' cellspacing='1' cellpadding='1' bgcolor='#CCCCCC'>\n";
	}

	function end_form()
	{
		// and should we also add a submit button to the end?
		// or deal with the save function using a toolbar?

		if ($this->submit)
		{
			$this->tb .= "<tr><td class='chformleftcol' colspan='2' align='center'>";
			$this->tb .= "<input type='submit' value='Salvesta' class='small_button'>";
			$this->tb .= "</td></tr>";
		};

		$this->tb .= "\n</table>\n";
		$this->tb .= $this->reforb;
		$this->tb .= "</form>\n";
	}

	////
	// !Draw a single line in the editing form.
	function draw_property($data)
	{
		$this->tb .= "<tr>";
		$this->tb .= "<td class='" . $this->leftcolstyle . "' width='150'>";
		$this->tb .= $data["caption"];
		$this->tb .= "</td>";

		$this->tb .= "<td class='" . $this->rightcolstyle . "'>";
		$this->tb .= $this->html->draw($data);
		$this->tb .= "</td>";
		$this->tb .= "</tr>\n";
	}

	////
	// !Creates the YAH line - if possible
	function create_path()
	{
		if (method_exists($this->clid,"get_metainfo"))
		{
			if (is_array($this->obj))
			{
				$title = $this->clid->get_metainfo("title_change");
				if (strlen($title) == 0)
				{
					// default title
					$title = "Muuda objekti";
				};
				$path_parent = $this->obj["parent"];
			}
			elseif ($this->parent)
			{
				$title = $this->clid->get_metainfo("title_add");
				if (strlen($title) == 0)
				{
					// default title
					$title = "Lisa objekt";
				};
				$path_parent = $this->parent;
			};

			$this->mk_path($path_parent,$title);
		};
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
		$cx = get_instance('cfg/cfgutils');
		$class_list = new aw_array($cx->get_classes_with_properties());
		$cp = $this->get_class_picker(array('field' => 'def'));
		$nodes = array();
		$nodes[] = array('caption' => 'Klassid');
		foreach($class_list->get() as $key => $val)
		{
			$ckey = $cp[$key];
			$nodes[] = array(
				'caption' => $val,
				'type' => 'checkbox',
				'name' => "classes[$ckey]",
				'checked' => $args['prop']['value'][$ckey],
			);
		};
		return $nodes;
	}

	////
	// !Returns a list of checkboxes for selecting properties
	function callback_get_prop_list($args = array())
	{
		$sel_classes = new aw_array($args['obj']['meta']['classes']);
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

		foreach($sel_classes->get() as $key => $val)
		{
			$has_props = $cfgu->has_properties(array('cldef' => $key));
			$clid = $cfgu->get_clid_by_cldef(array('cldef' => $key));
			if ($has_props)
			{
				$selprops = $args['prop']['value'][$key];
				$res[] = array(
					'caption' => $this->cfg['classes'][$clid]['name'],
				);
			
				$props = $cfgu->load_properties(array('clid' => $clid));
				foreach($props as $property)
				{
					if ($property['access']['text'] != 'ro')
					{
						$name = $property['name']['text'];
						$caption = $property['caption']['text'];
						$ord = $args["obj"]["meta"]["ord"][$key][$name];
						$this->t->define_data(array(
							'caption' => $caption,
							'group' => $property['group']['text'],
							'check' => html::checkbox(array('name' => "properties[$key][$name]",'checked' => $selprops[$name])),
							'ord' => html::textbox(array('size' => 4, 'name' => "ord[$key][$name]", 'value' => $ord)),
						));
					}; // if !ro
				}; // forach $props

				$res[] = array(
					'value' => $this->t->draw(),
				);
				$this->t->clear_data();
			};
		};

		return $res;

	}


};
?>
