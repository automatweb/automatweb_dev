<?php

class ekomar extends aw_template
{
	function ekomar()
	{
		$this->db_init();
		$this->tpl_init("ekomar");
		$this->sub_merge = 1;
	}

	/**  
		
		@attrib name=list_files params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function list_files()
	{
		$this->read_template("list_files.tpl");
		$this->db_query("SELECT * FROM ekomar_files");
		while ($row = $this->db_next())
		{
			$this->save_handle();
//			$pos = strpos($row[name], ".");
//			$nn = substr($row[name],0,$pos);
			$nn = $row["comment"];
			$cf = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM ekomar_cos WHERE filename = '$nn'","cnt");
			$this->restore_handle();
			$this->vars(array("name"		=> $row[name], 
												"ck"			=> $cf,
												"id"			=> $row[id],
												"comment"	=> $row[comment],
												"change"	=> $this->mk_orb("change_file", array("id" => $row[id])),
												"delete"	=> $this->mk_orb("delete_file", array("id" => $row[id])),
												"modified"	=> $this->time2date($row[modified],2)));
			$this->parse("LINE");
			$co+=$cf;
		}
		$this->vars(array("ck" => $co));
		$cnt = $this->db_fetch_field("SELECT COUNT(*) as cnt FROM ekomar_cos","cnt");

		$this->vars(array("addfile" => $this->mk_orb("add_file", array()),
											"cnt"			=> $cnt,
											"upload_cos"	=> $this->mk_orb("upload_cos", array())));
		return $this->parse();
	}

	/**  
		
		@attrib name=add_file params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function add_file($arr)
	{
		$this->read_template("add_file.tpl");
		$this->vars(array("addfile"			=> $this->mk_orb("add_file", array()),
											"upload_cos"	=> $this->mk_orb("upload_cos", array()),
											"list_files"	=> $this->mk_orb("list_files", array())));

		$this->vars(array("reforb" => $this->mk_reforb("submit_file", array("id" => 0))));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_file params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_file($arr)
	{
		extract($arr);

		global $fail,$fail_name;

		if ($id)
		{
			// change
			if ($fail == "none")
			{
				$this->db_query("UPDATE ekomar_files SET comment='$comment' , modified = ".time()." WHERE id = $id");
			}
			else
			{
				$f = fopen($GLOBALS["fail"],"r");
				$fc = fread($f, filesize($GLOBALS["fail"]));
				fclose($f);

				$this->quote(&$fc);
				$this->quote(&$fc);

				$this->db_query("UPDATE ekomar_files SET file='$fc', name='$fail_name',comment='$comment', modified=".time()." WHERE id = $id");
			}
		}
		else
		{
			// upload
			if ($GLOBALS["fail"] == "none")
				$this->raise_error("ekomar->submit_file(): Te ei valinud faili uploadimiseks!", true);
			$id = $this->db_fetch_field("SELECT max(id) as id from ekomar_files","id")+1;

			$f = fopen($GLOBALS["fail"],"r");
			$fc = fread($f, filesize($GLOBALS["fail"]));
			fclose($f);

			$this->quote(&$fc);
			$this->quote(&$fc);

			$this->db_query("INSERT INTO ekomar_files(id, name, file,comment,modified) values($id,'$fail_name','$fc','$comment','".time()."')");
		}
		return $this->mk_orb("list_files", array());
	}

	/**  
		
		@attrib name=change_file params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function change_file($arr)
	{
		$this->mk_path(0,"<a href='".$this->mk_orb("list_files", array())."'>Failide nimekiri</a> / Muuda faili");
		extract($arr);
		$this->db_query("SELECT * FROM ekomar_files where id = $id");
		$row = $this->db_next();
		$this->read_template("add_file.tpl");
		$this->vars(array("comment" => $row[comment],"reforb" => $this->mk_reforb("submit_file", array("id" => $id))));
		return $this->parse();
	}

	/**  
		
		@attrib name=delete_file params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function delete_file($arr)
	{
		extract($arr);
		$this->db_query("DELETE FROM ekomar_files WHERE id = $id");
		header("Location: ".$this->mk_orb("list_files", array()));
	}

	/**  
		
		@attrib name=import_cos params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function import_firms($arr)
	{
		extract($arr);
		global $fail;

		$this->db_query("DELETE FROM ekomar_cos");
		$cnt = 1;
		$fl = file($fail);
		reset($fl);
		while (list(,$line) = each($fl))
		{
			$firm = explode("\t",$line);
			reset($firm);
			list(,$f_ark) = each($firm);
			list(,$f_name) = each($firm);
			list(,$f_fname) = each($firm);
			list(,$f_contact) = each($firm);
			list(,$f_phone) = each($firm);
			list(,$f_email) = each($firm);
			list(,$f_has_file) = each($firm);
			list(,$f_filename) = each($firm);
			

			if ($f_ark[0] == "\"")	// stripime " m2rgid
			{
				$f_ark = substr($f_ark,1,strlen($f_ark)-2);
				$f_ark = str_replace("\"\"","\"",$f_ark);
			}
			$f_ark = (int)$f_ark;
			if ($f_name[0] == "\"")	// stripime " m2rgid
			{
				$f_name = substr($f_name,1,strlen($f_name)-2);
				$f_name = str_replace("\"\"","\"",$f_name);
			}
			if ($f_fname[0] == "\"")	// stripime " m2rgid
			{
				$f_fname = substr($f_fname,1,strlen($f_fname)-2);
				$f_fname = str_replace("\"\"","\"",$f_fname);
			}
			if ($f_contact[0] == "\"")	// stripime " m2rgid
			{
				$f_contact = substr($f_contact,1,strlen($f_contact)-2);
				$f_contact = str_replace("\"\"","\"",$f_contact);
			}
			if ($f_phone[0] == "\"")	// stripime " m2rgid
			{
				$f_phone = substr($f_phone,1,strlen($f_phone)-2);
				$f_phone = str_replace("\"\"","\"",$f_phone);
			}
			if ($f_email[0] == "\"")	// stripime " m2rgid
			{
				$f_email = substr($f_email,1,strlen($f_email)-2);
				$f_email = str_replace("\"\"","\"",$f_email);
			}
			if ($f_has_file[0] == "\"")	// stripime " m2rgid
			{
				$f_has_file = substr($f_has_file,1,strlen($f_has_file)-2);
				$f_has_file = str_replace("\"\"","\"",$f_has_file);
			}
			if ($f_filename[0] == "\"")	// stripime " m2rgid
			{
				$f_filename = substr($f_filename,1,strlen(trim($f_filename))-2);
				$f_filename = str_replace("\"\"","\"",$f_filename);
			}
			$this->quote(&$f_name);
			$this->db_query("INSERT INTO ekomar_cos 
VALUES($cnt,'$f_ark','$f_name','$f_fname','$f_contact','$f_phone','$f_email','$f_has_file','$f_filename')");
			$cnt++;
		}
		return $this->mk_orb("list_files", array());
	}

	function show($id)
	{
		$this->db_query("SELECT file FROM ekomar_files WHERE id = $id");
		$row = $this->db_next();
		header("Content-type: ekomar/file");
		return $row[file];
	}

	/**  
		
		@attrib name=upload_cos params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function uplaod_cos($arr)
	{
		$this->read_template("upload_cos.tpl");
		$this->vars(array("addfile" => $this->mk_orb("add_file", array()),
											"reforb"	=> $this->mk_reforb("import_cos", array()),
											"list_files"	=> $this->mk_orb("list_files", array())));
		return $this->parse();
	}
}
?>
