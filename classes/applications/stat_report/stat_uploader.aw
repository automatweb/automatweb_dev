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

@property file type=fileupload
@caption Fail 

@property name type=textbox
@caption Aruande esitaja

@property email type=textbox
@caption Aruande esitaja meiliaadress

@classinfo hide_tabs=1
@forminfo stat onsubmit=submit_upload
*/

class stat_uploader extends class_base
{
	function stat_uploader()
	{
		$this->init("");
		$this->password = "1234";
		$this->filestore = "/www/dev/duke/site/public/vvstat";
		$this->reg = array(1234,2234,3234);
		$this->whitelist = array(
			".*\.xls$",
			".*\.sxc$",
			".*\.ddoc$",
		);
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

		if (empty($reg))
		{
			$errors = true;
			$propvalues["reg"]["error"] = "Registrikood ei tohi olla tühi";
		}
		else if (!in_array($reg,$this->reg))
		{
			$errors = true;
			$propvalues["reg"]["error"] = "Tundmatu registrikood";
		};

		// parool
		if (empty($password))
		{
			$errors = true;
			$propvalues["password"]["error"] = "Parool ei tohi olla tühi";
		}
		else if ($password != $this->password)
		{
			$errors = true;
			$propvalues["password"]["error"] = "Parool ei klapi";
		};

		if (empty($name))
		{
			$errors = true;
			$propvalues["name"]["error"] = "Nimi ei tohi olla tühi";
		};
		if (empty($email))
		{
			$errors = true;
			$propvalues["email"]["error"] = "Meiliaadress tohi olla tühi";
		};

		$filedat = $_FILES["file"];
		if (empty($filedat["tmp_name"]))
		{
			$errors = true;
			$propvalues["file"]["error"] = "Vali fail ka";
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
			move_uploaded_file($filedat["tmp_name"],$this->filestore . "/" . $reg . ".xls");
			return $this->mk_my_orb("final",array());
		}
		else
		{
			$propvalues["reg"]["value"] = $reg;
			$propvalues["name"]["value"] = $name;
			$propvalues["email"]["value"] = $email;
			aw_session_set("cb_values",$propvalues);
			return $this->mk_my_orb("show",array());
		};
	}
};
?>
