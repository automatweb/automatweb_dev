<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/clients/stat/ekomar/Attic/ekomar_show.aw,v 1.4 2005/03/24 10:13:00 ahti Exp $
// ekomar_show.aw - Ekomar 
/*

@classinfo syslog_type=ST_EKOMAR relationmgr=yes

@default table=objects
@default group=general

@property notfound type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Kuhu suunata kui ei leitud &uuml;htegi tulemust

@reltype FOLDER value=1 clid=CL_MENU,CL_DOCUMENT
@caption kuhu suunata

*/

//define("RELTYPE_FOLDER", 1);


class ekomar_show extends class_base
{
	function ekomar_show()
	{
		$this->init(array(
			'tpldir' => 'ekomar_show',
			'clid' => CL_EKOMAR_SHOW
		));
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = obj($id);

		if ($GLOBALS["ekomar_search"] == 1)
		{
			return $this->search_ekomar($ob);
		}
		else
		{
			return $this->ekomar_form($ob);
		}
	}

	function search_ekomar($ob)
	{
		$this->sub_merge = 1;
		$this->tpl_init("automatweb/documents");

		global $f_ark, $f_name;
		$ss = array();
		if (($f_ark != "")&&(strlen($f_ark)>7) && ($f_ark > 10000000 && $f_ark < 11000000))
		{
			$ss[] = "ark = '$f_ark'";
		}
		if (($f_name != "")&&(strlen($f_name)>2))
		{
			$ss[] = "name LIKE '%$f_name%'";
		}
		$sstr = join(" OR ",$ss);
		if ($sstr != "")
		{
			$this->read_template("erkomar_co.tpl");
			$this->db_query("SELECT * FROM ekomar_cos WHERE $sstr");
			$cnt=0;
			while ($row = $this->db_next())
			{
				$cnt ++;
				$this->save_handle();
				$this->db_query("SELECT id FROM ekomar_files WHERE name LIKE '".$row["filename"]."%'");
				$fr = $this->db_next();
				$this->restore_handle();

				$this->vars(array(
					"ark" => $row["ark"], 
					"name" => $row["name"],
					"file" => $this->mk_my_orb("show_file", array("id" => $fr["id"]),"", false, true, "/")."/EKOMAR2000.XLS",
					"contact" => $row["contact"],
					"phone" => $row["phone"]
				));
				if ($fr)
				{
					$f = $this->parse("FILE");
				}
				else
				{
					$f = "";
				}
				$this->vars(array(
					"FILE" => $f
				));
				$l .= $this->parse("LINE");
			}
			if ($cnt == 0)
			{
				header("Location: ".aw_ini_get("baseurl")."/index.".aw_ini_get("ext")."/section=".$ob->meta("notfound")."/s_name=$f_name/s_ark=$f_ark");
			}
			$this->vars(array(
				"LINE" => $l
			));
			return $this->parse();
		}
		else
		{
			$this->read_template("ekomar_form.tpl");
			$this->vars(array(
				"section" => aw_global_get("section"),
				"notfound" => $ob->meta('notfound')
			));
			if ($f_ark != "" && ($f_ark <= 10000000 || $f_ark >= 11000000))
			{
				$this->vars(array(
					"ERROR_ARK" => $this->parse("ERROR_ARK")
				));
			}
			return $this->parse();
		}
	}

	function ekomar_failid()
	{
		$this->sub_merge = 1;
		$this->tpl_init("automatweb/documents");
		$this->read_template("ekomar_failid.tpl");

		$this->db_query("SELECT name,comment,id FROM ekomar_files");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"], 
				"comment" => $row["comment"],
				"link" => $this->mk_my_orb("show_file", array("id" => $row["id"]))."/EKOMAR2000.XLS"
			));
			$this->parse("LINE");
		}
		return $this->parse();
	}

	function ekomar_form($ob)
	{
		$this->sub_merge = 1;
		$this->tpl_init("automatweb/documents");
		$this->read_template("ekomar_form.tpl");
		$this->vars(array(
			"section" => aw_global_get("section"),
			"notfound" => $ob->meta('notfound')
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=show_file params=name nologin="1" default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function show_file($arr)
	{
		$e = get_instance(CL_EKOMAR_SHOW);
		die($e->show($arr["id"]));
	}
}
?>
