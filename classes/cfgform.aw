<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/cfgform.aw,v 2.2 2002/10/15 20:32:29 duke Exp $
// cfgform.aw - configuration form
class cfgform extends aw_template
{
	function cfgform($args = array())
	{
		$this->init("cfgform");
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

	function ch_form($args = array())
	{
		extract($args);
		if (!is_object($clid))
		{
			// get lost!
			return false;
		};

		$odata = array_merge($obj,$obj["meta"]);
		
		$props = $this->get_obj_properties($odata);


		if (method_exists($clid,"get_properties"))
		{
			$props = array_merge($props,$clid->get_properties($odata));
		};

		$tb = sprintf("<form action='reforb.%s' method='post' name='changeform'>",aw_ini_get("ext"));
		$tb .= "\n<table border='0' cellspacing='1' cellpadding='1' bgcolor='#CCCCCC'>\n";

		$html = get_instance("html");

		$this->obj = $obj;

		/*
		print "<pre>";
		print_r($obj);
		print "</pre>";
		*/
		// I need to figure out the bloody current value for the item
		// for this I need to parse over the properties

		// now I have to draw the bloody change form
		foreach($props as $key => $val)
		{
			$tb .= "<tr>\n";
			$tb .= "<td class='hele_hall_taust' width='250'>";
			$tb .= $val["caption"];
			$tb .= "</td>";

			$tb .= "<td class='fgtext'>";
			$val["name"] = $key;
			$tb .= $html->draw($val);
			$tb .= "</td>";
			$tb .= "</tr>\n";
		};

		// and should we also add a submit button to the end?
		// or deal with the save function using a toolbar?

		if ($submit)
		{
			$tb .= "<tr><td class='hele_hall_taust' colspan='2' align='center'>";
			$tb .= "<input type='submit' value='Salvesta' class='small_button'>";
			$tb .= "</td></tr>";
		};

		$tb .= "\n</table>\n";
		$tb .= $reforb;
		$tb .= "</form>\n";

		return $tb;
	}

};
?>
