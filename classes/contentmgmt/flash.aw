<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/flash.aw,v 1.4 2005/10/21 21:56:34 duke Exp $
// flash.aw - Deals with flash applets
/*

	@default table=objects
	@default group=general
	@default method=serialize

	@property file type=fileupload field=meta
        @caption Vali fail

	@property width type=textbox size=4 field=meta
	@caption Laius

	@property height type=textbox size=4 field=meta
	@caption Kõrgus

	@property preview type=text store=no
	@caption Eelvaade

	@classinfo syslog_type=ST_FLASH

*/

class flash extends class_base
{
	function flash()
	{
		$this->init(array(
			'tpldir' => 'flash',
			'clid' => CL_FLASH
		));
	}

	function get_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "preview":
				if ($arr["obj_inst"]->prop("file"))
				{
					$data["value"] = $this->view(array("id" => $arr["obj_inst"]->id()));
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "file":
				$data["value"] = "";
				break;

		};
		return $retval;
	}
	

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		if ($prop["name"] == "file")
		{
			$fdata = $_FILES["file"];
			if ($fdata["type"] == "application/x-shockwave-flash" && is_uploaded_file($fdata["tmp_name"]))
			{
				$awf = get_instance(CL_FILE);
				$final_name = $awf->generate_file_path(array(
					"type" => $fdata["type"],
				));

				$imgdata = getimagesize($fdata["tmp_name"]);
				if (true || is_array($imgdata) && ($fc != ""))
				{
					$this->real_width = $imgdata[0];
					$this->real_height = $imgdata[1];
					move_uploaded_file($fdata["tmp_name"],$final_name);
					$prop["value"] = $final_name;

					if (!$arr["obj_inst"]->prop("name"))
					{
						$arr["obj_inst"]->set_prop("name",$fdata["name"]);
					};
				};
			}
			else
			{
				$retval = PROP_IGNORE;
			};
		};
		return $retval;
	}

	function callback_pre_save($arr = array())
	{
		// right now it's impossible to set those in the file upload
		// handler, because the original values from the form
		// will overwrite the values I'm going to set there
		if (isset($this->real_width) && isset($this->real_height))
		{
			$arr["obj_inst"]->set_prop("width",$this->real_width);
			$arr["obj_inst"]->set_prop("height",$this->real_height);
		};
	}

	function get_url($url)
	{
		if ($url)
		{
			$url = $this->mk_my_orb("show", array("file" => basename($url)),"flash",false,true,"/");
			$url = str_replace("automatweb/","",$url);
		}
		else
		{
			$url = "";
		};
		return $url;
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->view(array('id' => $alias['target']));
	}

	function request_execute($obj_inst)
	{
		die($this->view(array("id" => $obj_inst->id())));
	}


	/**  
		
		@attrib name=show params=name nologin="1" default="0"
		
		@param file required
		
		@returns
		
		
		@comment

	**/
	function show($arr)
	{
		extract($arr);
		$rootdir = $this->cfg["site_basedir"];
		$f1 = substr($file,0,1);
		$fname = $rootdir . "/img/$f1/" . $file;
		if ($file) 
		{
			if (strpos("/",$file) !== false) 
			{
				header("Content-type: text/html");
				print "access denied,";
			} 

			// the site's img folder
			$passed = false;	
			if (is_file($fname) && is_readable($fname)) 
			{
				$passed = true;
			}

			if (!$passed)
			{
				$rootdir = $this->cfg["site_basedir"];
				$fname = $rootdir . "/files/$f1/" . $file;
				if (is_file($fname) && is_readable($fname)) 
				{
					$passed = true;
				}
			}

			if ($passed)
			{

				header("Content-type: application/x-shockwave-flash");
				readfile($fname);
			} 
			else 
			{
				print "access denied:";
			};
		} 
		else 
		{
			print "access denied;";
		};
		die();
	}

	function view($args = array())
	{
		extract($args);

		$ob = new object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			"id" => $ob->prop("name"),
			"url" => $this->get_url($ob->prop("file")),
			"width" => $ob->prop("width"),
			"height" => $ob->prop("height"),
		));

		return $this->parse();
	}
}
?>
