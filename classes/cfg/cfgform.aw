<?php
// $Header: /home/cvs/automatweb_dev/classes/cfg/cfgform.aw,v 1.1 2002/10/29 13:29:30 duke Exp $
// cfgform.aw - configuration form
class cfgform extends aw_template
{
	function cfgform($args = array())
	{
		$this->init("cfgform");

		// styles used to draw the change form
		// these should probably come from ini file or smth.
		$this->leftcolstyle = "chformleftcol";
		$this->rightcolstyle = "chformrightcol";
	}

	////
	// !Adds a new configuration form
	function add($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		$toolbar = get_instance("toolbar");
		$toolbar->add_button(array(
                        "name" => "add",
                        "tooltip" => "Lisa",
                        "url" => "javascript:document.clform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));
		
		$this->mk_path($parent,"Lisa konfivorm");

		$this->vars(array(
			"toolbar" => $toolbar->get_toolbar(),
			"class_container" => $this->_draw_fields(),
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));

		return $this->parse();
	}

	////
	// !Allows to change the configuration form
	function change($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);

		$toolbar = get_instance("toolbar");
		$this->read_template("add.tpl");
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Salvesta",
                        "url" => "javascript:document.clform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));

		$this->mk_path($obj["parent"],"Muuda konfivormi");

		$this->vars(array(
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"toolbar" => $toolbar->get_toolbar(),
			"class_container" => $this->_draw_fields($obj["meta"]["properties"]),
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Submits the configuration form
	function submit($args = array())
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
					"properties" => $properties,
				),
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_CFGFORM,
				"metadata" => array(
					"properties" => $properties,
				),
			));
		};
		return $this->mk_my_orb("change",array("id" => $id));
	}

	function _draw_fields($fields = array())
	{
		$source = get_file(array("file" => $this->cfg["basedir"] . "/xml/interfaces/config.xml"));
		list($values,$tags) = parse_xml_def(array("xml" => $source));
		$c = "";
		$cp = $this->get_class_picker(array("index" => "file"));
		foreach($values as $val)
		{
			$attr = $val["attributes"];
			if ( ($val["tag"] == "class") && ($val["type"] == "complete") )
			{
				$l = "";
				$clid[$attr["id"]] = $attr["name"];
				$prefix = $attr["id"];
				$t = get_instance($attr["id"]);
				if (method_exists($t,"get_properties"))
				{
					$properties = new aw_array($t->get_properties());
					foreach($properties->get() as $pkey => $property)
					{
						$check = checked($fields[$prefix][$pkey]);
						$this->vars(array(
							"clid" => $prefix,
							"pkey" => $pkey,
							"pname" => $property["caption"],
							"checked" => $check,
						));

						$l .= $this->parse("line");
					}
				};
				$this->vars(array(
					"line" => $l,
					"cname" => $cp[$prefix],
				));

				$c .= $this->parse("class_container");
			};
		};
		return $c;
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
		
		$this->obj = $obj;
		$this->clid = $clid;
		$this->parent = $parent;
		$this->reforb = $reforb;
		$this->no_filter = $no_filter;
		$this->submit = $submit;

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

		$this->filter_properties = $this->get_visible_properties();

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
				$cfgmanager = get_instance("cfgmanager");
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


};
?>
