<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/datasource.aw,v 2.8 2002/12/19 18:05:57 duke Exp $
// type of the data, I'm storing it in the subclass field of the objects table
// so that I can retrieve all sources with the same type with one query
define("DS_XML",1);

/*
	@default table=objects
	@default field=meta
	@default group=general

	@property subclass type=select field=subclass 
	@caption Andmete tüüp
	
	@default method=serialize

	@property type type=select 
	@caption Source tüüp

	@property fullpath type=textbox editonly=1
	@caption Faili asukoht

	@property url type=textbox editonly=1
	@caption Faili url
*/

class datasource extends class_base
{
	function datasource($args = array())
	{
		$this->init(array(
			"clid" => CL_DATASOURCE,
			"tpldir" => "datasource",
		));
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "type":
				$data["options"] = array(
					"0" => "lokaalne fail (serveris)",
					"1" => "http",
					"2" => "https",
				);
				break;

			case "subclass":
				$data["options"] = array(
					DS_XML => "XML",
				);
				break;

			case "fullpath":
				if ($args["obj"]["meta"]["type"] != 0)
				{
					$retval = PROP_IGNORE;
				};
				break;
			
			case "url":
				if ($args["obj"]["meta"]["type"] == 0)
				{
					$retval = PROP_IGNORE;
				};
				break;
			
		};
		return $retval;
	}

	////
	// !Retrieves data from a datasource - at the moment works with 
	// http(s) only.
	function retrieve($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$type = $obj["meta"]["type"];
		$url = escapeshellarg($obj["meta"]["url"]);
		if (($type == 2) || ($type == 1))
		{
			$read = "";
			$curl = $this->cfg["curl_path"];
			$fp = popen ("$curl $url", "r");
			while(!feof($fp))
			{
				$read.= fread($fp,16384);
			}
			pclose($fp);
			return $read;
		}
	}

	////
	// !Raw interface for accessing the data from a source. Mainly for debugging
	// purposes.
	function fetch($args = array())
	{
		$read = $this->retrieve($args);
		header("Content-Type: text/xml");
		print $read;
		exit;
	}

}
?>
