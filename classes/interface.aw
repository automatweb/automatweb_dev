<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/interface.aw,v 2.1 2002/07/23 21:14:00 duke Exp $
// interface.aw - class interface manager
class interface extends aw_template
{
	function interface($args = array())
	{
		$this->init("");
	}

	////
	// !Retrieves and parses an interface XML file
	function _get_if($args = array())
	{
		extract($args);
		$source = get_file(array("file" => $this->cfg["basedir"] . "/xml/interfaces/$name.xml"));
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
				$retval[$attr["id"]][$attr["action"]] = $attr;
				$_clids = explode(",",$attr["clid"]);
				foreach($_clids as $_clid)
				{
					$this->clid[constant($_clid)] = constant($_clid);
				}
			};

		};

		return $retval;
	}

};
?>
