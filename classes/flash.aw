<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/flash.aw,v 1.1 2003/03/27 17:27:23 duke Exp $
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

	@groupinfo general caption=Üldine
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

	function get_property($args = array())
        {
                $data = &$args["prop"];
                $retval = PROP_OK;
		switch($data["name"])
		{
			case "preview":
				$data["value"] = $this->view(array("id" => $args["obj"]["oid"]));
				break;

			case "file":
				$data["value"] = "";
				break;

		};
		return $retval;
	}
	

	function set_property($args = array())
	{
                $data = &$args["prop"];
                $form_data = &$args["form_data"];
		$retval = PROP_OK;
                if ($data["name"] == "file")
                {
			$fdata = $_FILES["file"];
                        if ($fdata["type"] == "application/x-shockwave-flash" &&
				is_uploaded_file($fdata["tmp_name"]))
                        {
                                // fail sisse
                                $fc = $this->get_file(array(
                                        "file" => $fdata["tmp_name"],
                                ));

				if ($fc != "")
				{
					// stick the file in the filesystem
					$awf = get_instance("file");
					$fs = $awf->_put_fs(array(
						"type" => $fdata["type"],
						"content" => $fc,
                                        ));

                                        $data["value"] = $fs;
                                        $this->file_name = $fdata["name"];
                                };

			};

		};
		return $retval;
	}

	function callback_pre_save($args = array())
	{
		$coredata = &$args["coredata"];
		if (!$coredata["name"] && isset($this->file_name))
		{
			$coredata["name"] = $this->file_name;
		};
        }


	function get_url($url)
	{
                $url = $this->mk_my_orb("show", array("fastcall" => 1,"file" => basename($url)),"flash",false,
true,"/");
                return str_replace("automatweb/", "", $url);
        }



	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->view(array('id' => $alias['target']));
	}

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

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function view($args = array())
	{
		extract($args);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			"id" => $ob["name"],
			"url" => $this->get_url($ob["meta"]["file"]),
			"width" => $ob["meta"]["width"],
			"height" => $ob["meta"]["height"],
		));

		return $this->parse();
	}
}
?>
