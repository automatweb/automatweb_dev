<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/datasource.aw,v 2.14 2004/12/01 14:01:19 ahti Exp $
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

	@property url type=textbox editonly=1 size=60
	@caption Faili url

	@classinfo no_status=1
*/

class datasource extends class_base
{
	function datasource($args = array())
	{
		$this->init(array(
			"clid" => CL_DATASOURCE,
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
				if ($args["obj_inst"]->prop("type") != 0)
				{
					$retval = PROP_IGNORE;
				};
				break;
			
			case "url":
				if ($args["obj_inst"]->prop("type") == 0)
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
		$obj = new object($id);
		$type = $obj->prop("type");
		$url = escapeshellarg($obj->prop("url"));
		if (($type == 2) || ($type == 1))
		{
			$read = "";
			$curl = $this->cfg["curl_path"];
			/*
			if (!file_exists($curl))
			{
				 error::raise(array(
					"id" => "ERR_DS_NO_AGENT",
					"msg" => "$curl not found"
				));
			};
			*/
			$fp = popen ("$curl $url", "r");
			while(!feof($fp))
			{
				$read.= fread($fp,16384);
			}
			pclose($fp);
			return $read;
		}
	}

	/** Raw interface for accessing the data from a source. Mainly for debugging 
		
		@attrib name=fetch params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment
		purposes.

	**/
	function fetch($args = array())
	{
		$read = $this->retrieve($args);
		header("Content-Type: text/xml");
		print $read;
		exit;
	}

}
?>
