<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/cfgform.aw,v 2.1 2002/10/10 13:23:55 duke Exp $
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
			"line" => $this->_draw_fields(),
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));

		return $this->parse();
	}

	////
	// !Allows to change the configuration object
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
			"line" => $this->_draw_fields($obj["meta"]["properties"]),
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Submits the configuration object
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
		$l = "";
		foreach($values as $val)
		{
			$attr = $val["attributes"];
			if ( ($val["tag"] == "class") && ($val["type"] == "complete") )
			{
				$clid[$attr["id"]] = $attr["name"];
				$prefix = $attr["id"];
				$this->vars(array(
					"cname" => $attr["name"],
				));
				$l .= $this->parse("cline");
				$t = get_instance($attr["id"]);
				if (method_exists($t,"get_properties"))
				{
					$properties = $t->get_properties();
					if (is_array($properties))
					{
						foreach($properties as $pkey => $property)
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
				};
			};
		};
		return $l;
	}

};
?>
