<?php
// type of the data, I'm storing it in the subclass field of the objects table
// so that I can retrieve all sources with the same type with one query
define("DS_XML",1);

class datasource extends aw_template
{
	function datasource($args = array())
	{
		enter_function("datasource::datasource",array());
		$this->init("datasource");
		$this->types = array(
			"0" => "lokaalne fail (serveris)",
			"1" => "http",
			"2" => "https",
		);
		exit_function("datasource::datasource");
	}

	////
	// !Displays form for adding new or modifying an existing datasource objekt
	function change($args = array())
	{
		enter_function("datasource::change",array());
		extract($args);
		$this->read_template("change.tpl");
		if ($parent)
		{
			$caption = "Lisa uus datasource";
			$prnt = $parent;
			$type = 1;
			$datatype = 1;
		}
		else
		{
			$caption = "Muuda datasource't";
			$obj = $this->get_object($id);
			$prnt = $obj["parent"];
			$type = $obj["meta"]["type"];
			$datatype = $obj["subclass"];
		};

		$this->mk_path($prnt,$caption);


		$datatypes = array(
			DS_XML => "XML",
		);

		$this->vars(array(
			"fullpath" => $obj["meta"]["fullpath"],
			"url" => $obj["meta"]["url"],
		));

		// different subtemplate for each source type
		switch($type)
		{
			case "0":
				$src = $this->parse("localfile");
				break;

			case "1":
				$src = $this->parse("http");
				break;

			case "2":
				$src = $this->parse("http");
				break;

		};

		$this->vars(array(
			"name" => $obj["name"],
			"types" => $this->picker($type,$this->types),
			"datatypes" => $this->picker($datatype,$datatypes),
			"src" => $src,
			"reforb" => $this->mk_reforb("submit",array("id" => $id,"parent" => $parent)),
		));
		exit_function("datasource::change");
		return $this->parse();
	}

	////
	// !Adds new or submits changes to an existing XML import objekt
	function submit($args = array())
	{
		enter_function("datasource::submit",array());
		$this->quote($args);
		extract($args);
		if ($parent)
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_DATASOURCE,
				"name" => $name,
				"status" => 2,
				"subclass" => $datatype,
			));
			$this->_log("datasource","Lisas datasource nimega '$name'",$id);
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"subclass" => $subtype,
			));
			$this->_log("datasource","Muutis datasourcet nimega '$name'",$id);
		};

		$meta = array(
			"fullpath" => $fullpath,
			"url" => $url,
			"type" => $type,
		);

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => $meta,
		));

		exit_function("datasource::submit");
		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Retrieves data from a datasource - at the moment works with 
	// http(s) only.
	function retrieve($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$type = $obj["meta"]["type"];
		$url = $obj["meta"]["url"];
		if ($type == 2)
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
