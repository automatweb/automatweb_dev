<?php
// there are 2 roles here
// one is the usual AW object, which is used to set up the correct environment
// the other is the client side form which will be used to upload files
/*
@default group=general
@default form=stat

@property header type=text no_caption=1
@caption Header

@property reg type=textbox 
@caption Äriregistrikood

@property password type=password 
@caption Tunnuskood

@property report_type type=select
@caption Aruande nimetus

@property file type=fileupload
@caption Aruande fail 

@property name type=textbox
@caption Aruande esitaja

@property email type=textbox
@caption Kontaktandmed

@property comment type=textarea
@caption Kommentaar

@property MAX_FILE_SIZE type=hidden
@caption Faili max. suurus

@property submit type=submit value=Saada

@classinfo hide_tabs=1
@forminfo stat onsubmit=submit_upload
*/

class stat_uploader extends class_base
{
	function stat_uploader()
	{
		$this->init("");
		$this->filestore = "/www/aruanded.stat.ee/aruanded";
		// XXX: make this configurable. how?
		$this->obj = new object(130830);
		$whitelist = $this->obj->prop("whitelist");
		$items = preg_split("/\s+/",$whitelist);
		foreach($items as $item)
		{
			$item = trim($item);		
			$this->whitelist[] = ".*\.$item$";
		};

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

		asort($this->choices);

	}

	/**
		@attrib name=upload_final nologin="1"
	
	**/
	function upload_final()
	{
		return $this->obj->prop("final");
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
		$propvalues["report_type"]["error"] = "";
		$propvalues["file"]["error"] = "";
		$propvalues["comment"]["error"] = "";

		$authfile = aw_ini_get("site_basedir") . "/ettevotted.csv";
		$authcontents = file_get_contents($authfile);

		$authcontents = str_replace("\"","",$authcontents);

		$mx = preg_match("/^$reg,(.*)$/m",$authcontents,$m);
		$this->password = trim($m[1]);

		if (empty($reg))
		{
			$errors = true;
			$propvalues["reg"]["value"] = " ";
			$propvalues["reg"]["error"] = "Sisestage äriregistrikood!";
		}
		//else if (!in_array($reg,$this->reg))
		else if (!$mx)
		{
			$errors = true;
			$propvalues["reg"]["value"] = $reg;
			$propvalues["reg"]["error"] = "Sellist äriregistrikoodi ei ole olemas!";
		};

		// parool
		if (empty($password))
		{
			$errors = true;
			$propvalues["password"]["error"] = "Sisestage tunnuskood!";
		}
		else if ($password != $this->password)
		{
			$errors = true;
			$propvalues["password"]["error"] = "Äriregistrikood ja tunnuskood ei klapi!";
		};

		if (empty($name))
		{
			$errors = true;
			$propvalues["name"]["error"] = "Sisestage oma ees- ja perekonnanimi!";
		};
		if (empty($email))
		{
			$errors = true;
			$propvalues["email"]["error"] = "Sisestage oma telefoninumber või e-post!";
		};

		$filedat = $_FILES["file"];
		if (empty($filedat["tmp_name"]))
		{
			$errors = true;
			$propvalues["file"]["error"] = "Palun valige aruande fail!";
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
				$propvalues["file"]["error"] = "Selline failitüüp ($filename) ei ole lubatud";
			};
		};

		if (!$errors)
		{
			// now fetch the file and do something with it
			$pi = pathinfo($filename);
			$ext = $pi["extension"];
			$n_basename = $reg . "_" . $arr["report_type"] . "#" . date("YmdHis");
			$new_name = $this->filestore . "/" . $n_basename . "." . $ext;
			$stat = move_uploaded_file($filedat["tmp_name"],$new_name);
			chmod($new_name,0666);
			$this->log_upload(array(
				"file" => $this->filestore . "/log/" . $n_basename . ".txt",
				"ip" => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
				"name" => $name,
				"reg" => $reg,
				"report_type" => $report_type,
				"contact" => $email,
				"comment" => $comment,
			));
			return $this->mk_my_orb("upload_final",array());
		}
		else
		{
			$propvalues["reg"]["value"] = $reg;
			$propvalues["name"]["value"] = $name;
			$propvalues["report_type"]["value"] = $report_type;
			$propvalues["email"]["value"] = $email;
			$propvalues["file"]["value"] = $file;
			$propvalues["comment"]["value"] = $comment;
			aw_session_set("cb_values",$propvalues);
			
			// log error
			$els = array();
			$els[] = time();
			$els[] = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
			$els[] = $reg;
			$els[] = $report_type;
			$els[] = $name;
			$els[] = $email;

			foreach($propvalues as $key => $item)
			{
				if (!empty($item["error"]))
				{
					$els[] = $item["error"];
				};
			};

			$fh = fopen($this->filestore . "/log/error.log","a");
			fwrite($fh,join("\t",$els) . "\n");
			fclose($fh);

			// ---
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
				if (!empty($_GET["statement"]))
				{
					$prop["value"] = $_GET["statement"];
				};
				break;

			case "reg":
				if (!empty($_GET["reg"]))
				{
					$prop["value"] = $_GET["reg"];
				};
				break;

			case "MAX_FILE_SIZE":
				$prop["value"] = $this->obj->prop("filesize") * 1024;
				break;

		};
		return $rv;
	}

	function log_upload($arr)
	{
		$this->put_file(array(
			"file" => $arr["file"],
			"content" => $arr["name"] . "\n" . $arr["ip"] . "\n" . $arr["contact"] . "\n" . $arr["comment"],
		));
		/*
		Lisaks on kataloogis aruanded.stat.ee/public/aruanded/log tekstifail aruanded.log, mis sisaldab
		tab-iga eraldatud kujul infot kõikide failiuploadide kohta:
			uploadi aeg unix timestampina
			uploadija IP aadress
			ettevõtte registrikood
			etteõtte esindaja nimi
			faili uploadija kontaktandmed
		*/
		$els = array();
		$els[] = time();
		$els[] = $arr["ip"];
		$els[] = $arr["reg"];
		$els[] = $arr["report_type"];
		$els[] = $arr["name"];
		$els[] = $arr["contact"];

		$fh = fopen($this->filestore . "/log/aruanded.log","a");
		fwrite($fh,join("\t",$els) . "\n");
		fclose($fh);
	}
};
?>
