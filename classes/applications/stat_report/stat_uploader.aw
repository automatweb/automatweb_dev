<?php
// there are 2 roles here
// one is the usual AW object, which is used to set up the correct environment
// the other is the client side form which will be used to upload files
/*
@default group=general
@default form=stat

@property reg type=textbox 
@caption Reg. kood

@property password type=password 
@caption Parool

@property report_type type=select
@caption Aruande tüüp

@property file type=fileupload
@caption Fail 

@property name type=textbox
@caption Aruande esitaja

@property email type=textbox
@caption Kontaktandmed

@classinfo hide_tabs=1
@forminfo stat onsubmit=submit_upload
*/

class stat_uploader extends class_base
{
	function stat_uploader()
	{
		$this->init("");
		$this->password = "1234";
		$this->filestore = "/www/aruanded.stat.ee/aruanded";
		$this->reg = array(1234,2234,3234);
		$this->whitelist = array(
			".*\.xls$",
			".*\.sxc$",
			".*\.ddoc$",
		);
		$this->obj = new object(130830);
		$conns = $this->obj->connections_from(array(
			"reltype" => 1,
		));
		foreach($conns as $conn)
		{
			$to_obj = $conn->to();
		};

		$choices = new object_list(array(
			"class_id" => CL_META,
			"parent" => $to_obj->id(),
			"lang_id" => array(),
		));

		$this->choices = array();
		for ($o =& $choices->begin(); !$choices->end(); $o =& $choices->next())
		{
			$this->choices[$o->comment()] = $o->name();
		};

	}

	/**
		@attrib name=final nologin="1"
	
	**/
	function final()
	{
		print "No kuule aitähh, faili sain kätte";
		exit;
	}

	/** 
		@attrib name=show default="1" nologin="1"
	**/
	function show($arr)
	{
		$o = new object($arr["id"]);
		$arr["form"] = "stat";
		return $this->change($arr);
	}

	/**
		@attrib name=submit_upload nologin="1"
	**/
	function submit_upload($arr)
	{
		$errors = false;
		extract($arr);
		$propvalues = array();
		$cb_values = array();
		$propvalues["reg"]["error"] = "";
		$propvalues["name"]["error"] = "";
		$propvalues["email"]["error"] = "";
		$propvalues["file"]["error"] = "";

		$authfile = aw_ini_get("site_basedir") . "/auth.csv";
		$authcontents = file_get_contents($authfile);

		$mx = preg_match("/^$reg,(.*)$/m",$authcontents,$m);
		$this->password = $m[1];

		if (empty($reg))
		{
			$errors = true;
			$propvalues["reg"]["value"] = " ";
			$propvalues["reg"]["error"] = "Sisestage registrikood";
		}
		//else if (!in_array($reg,$this->reg))
		else if (!$mx)
		{
			$errors = true;
			$propvalues["reg"]["value"] = $reg;
			$propvalues["reg"]["error"] = "Sellist registrikoodi ei ole olemas!";
		};

		// parool
		if (empty($password))
		{
			$errors = true;
			$propvalues["password"]["error"] = "Sisestage parool!";
		}
		else if ($password != $this->password)
		{
			$errors = true;
			$propvalues["password"]["error"] = "Registrikood ja parool ei klapi!";
		};

		if (empty($name))
		{
			$errors = true;
			$propvalues["name"]["error"] = "Sisestage oma nimi!";
		};
		if (empty($email))
		{
			$errors = true;
			$propvalues["email"]["error"] = "Sisestage oma kontaktandmed!";
		};

		$filedat = $_FILES["file"];
		if (empty($filedat["tmp_name"]))
		{
			$errors = true;
			$propvalues["file"]["error"] = "Palun valige fail!";
		}
		else
		{
			$filename = strtolower($filedat["name"]);
			$match = false;
			foreach($this->whitelist as $item)
			{
				if (preg_match("/$item/",$filename))
				{
					$match = true;
				};
			};
			if (!$match)
			{
				$errors = true;
				$propvalues["file"]["value"] = " ";
				$propvalues["file"]["error"] = "$filename pole lubatud failide nimekirjas";
			};
		};

		if (!$errors)
		{
			// now fetch the file and do something with it
			$pi = pathinfo($filename);
			$ext = $pi["extension"];
			$new_name = $this->filestore . "/" . $reg . "_" . $arr["report_type"] . "#" . date("YmdHi") . "." . $ext;
			$stat = move_uploaded_file($filedat["tmp_name"],$new_name);
			return $this->mk_my_orb("final",array());
		}
		else
		{
			$propvalues["reg"]["value"] = $reg;
			$propvalues["name"]["value"] = $name;
			$propvalues["email"]["value"] = $email;
			$propvalues["file"]["value"] = $file;
			aw_session_set("cb_values",$propvalues);
			return $this->mk_my_orb("show",array());
		};
	}

	function get_property($arr)
	{	
		$prop = &$arr["prop"];
		$rv = PROP_OK;
		switch($prop["name"])
		{
			case "report_type":
				$prop["options"] = $this->choices;
				break;
		};
		return $rv;
	}
};
?>
