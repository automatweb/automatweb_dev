<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/interface.aw,v 2.3 2002/12/03 13:00:32 kristo Exp $
// interface.aw - class interface manager
class interface extends aw_template
{
	function interface($args = array())
	{
		$this->init("");
	}

	////
	// !Retrieves and parses an interface XML file
	function get_if($args = array())
	{
		extract($args);
		$source = $this->get_file(array("file" => $this->cfg["basedir"] . "/xml/interfaces/$name.xml"));
		if (not($source))
		{
			$this->raise_error(ERR_CORE_NOFILE,"Cannot find interface $name",true);
		};

		list($values,$tags) = parse_xml_def(array("xml" => $source));

		$retval = array();
	
		// this is where we store the id-s of objects we can serve.
		// should be accessed with get_opt
		$this->clid = array();

		foreach($values as $key => $val)
		{
			$attr = $val["attributes"];
			if ( ($val["tag"] == "class") && ($val["type"] == "complete") )
			{
				$cid = constant($attr["cldef"]);
				$attr["name"] = $this->cfg["classes"][$cid]["name"];
				$attr["clid"] = $cid;
				$retval[$attr["cldef"]][$attr["action"]] = $attr;
			};

		};

		return $retval;
	}

};
?>
