<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_orb.aw,v 2.1 2002/07/11 20:10:33 duke Exp $
// aw_orb.aw - new and improved ORB

class aw_orb extends aw_template
{
	function aw_orb($args = array())
	{
		$this->init("");
	}

	////
	// !Returns a list of all defined ORB classes
	function get_public_classes($args = array())
	{
		$basedir = $this->cfg["basedir"];
		// klassi definitsioon sisse
		$xmldef = $this->get_file(array(
			"file" => "$basedir/xml/interfaces/public.xml"
		));

		// loome parseri
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		// xml data arraysse
		xml_parse_into_struct($parser,$xmldef,&$values,&$tags);
		// R.I.P. parser
		xml_parser_free($parser);

		$pclasses = array();

		foreach($values as $key => $val)
		{
			if ( ($val["tag"] == "class") && ($val["type"] == "complete") )
			{
				$attr = $val["attributes"];
				$pm = $this->get_public_methods(array(
					"id" => $attr["id"],
					"name" => $attr["name"],
				));
				if (sizeof($pm)  > 0)
				{
					$pclasses = $pclasses + $pm;
				};

			}

		}

		return $pclasses;

	}

	function get_public_methods($args = array())
	{
		extract($args);
		classload("orb");
		$orb_defs = orb::load_xml_orb_def($id);
		$pmethods = array();
		foreach($orb_defs[$id] as $key => $val)
		{
			if ($val["public"])
			{
				$pmethods[$id . "/" . $key] = $name . "/" . $val["function"];
			}
		};

		return $pmethods;
	}
	
	function get_public_method($args = array())
	{
		extract($args);
		classload("orb");
		$orb_defs = orb::load_xml_orb_def($id);
		$meth = $orb_defs[$id][$action];
		$meth["values"] = array();
		$cl = get_instance($id);
		$ar = array();
		if ($id == "document")
		{
			if ($cl->get_opt("cnt_documents") == 1)
			{
				$meth["values"]["id"] = $cl->get_opt("shown_document");
			}
			$meth["values"]["period"] = $cl->get_opt("period");
			$data = $cl->get_opt("data");
			$meth["values"]["parent"] = $data["parent"];
		};
		return $meth;
	}
}
?>
