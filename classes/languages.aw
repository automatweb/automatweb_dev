<?php
lc_load("languages");
class languages extends aw_template
{
	function languages()
	{
		$this->db_init();
		$this->tpl_init("languages");
		lc_load("definition");
		global $lc_languages;
		if (is_array($lc_languages))
		{
			$this->vars($lc_languages);}
	}

	function gen_list()
	{
		$this->read_template("list.tpl");

		global $admin_lang;

		$this->db_query("SELECT * FROM languages WHERE status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array("id" => $row["id"], "name" => $row["name"], "charset" => $row["charset"],"acceptlang" => $row["acceptlang"]));
			$ac = $row["status"] == 1 ? $this->parse("NACTIVE") : $this->parse("ACTIVE");

			$sel = ($GLOBALS["lang_id"]) == $row["id"] ? $this->parse("SEL") : $this->parse("NSEL");

			$this->vars(array(
				"ACTIVE" => $ac, 
				"NACTIVE" => "", 
				"SEL" => $sel, 
				"NSEL" => "",
				"CSEL" => "",
				"check" => checked($admin_lang == $row["id"])
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l));

		return $this->parse();
	}

	function fetch($id,$lang = "")
	{
		if (!$id)
		{
			return false;
		}
		$ss = "";
		if ($lang != "")
		{
			$ss = " AND acceptlang = '$lang' ";
		}
		$this->db_query("SELECT * FROM languages WHERE id = $id $ss");
		return $this->db_next();
	}

	function listall()
	{
		$this->db_query("SELECT * FROM languages WHERE status = 2");
		$ret = array();
		while ($row = $this->db_next())
			$ret[] = $row;

		return $ret;
	}

	function add()
	{
		$this->read_template("add.tpl");
		$this->vars(array("id" => 0, "name" => "", "charset" => "","acceptlang" => ""));
		return $this->parse();
	}

	function change($id)
	{
		$this->read_template("add.tpl");
		$l = $this->fetch($id);
		$this->vars(array("id" => $id, "name" => $l["name"], "charset" => $l["charset"],"acceptlang" => $l["acceptlang"]));
		return $this->parse();
	}

	function submit($arr)
	{
		$this->quote(&$arr);
		extract($arr);

		if ($id)
		{
			$this->db_query("UPDATE languages SET name = '$name' , charset = '$charset' , acceptlang='$acceptlang' WHERE id = $id");
		}
		else
		{
			$id = $this->db_fetch_field("select max(id) as id from languages","id")+1;
			$this->db_query("INSERT INTO languages values($id,'$name','$charset',1,'$acceptlang')");
		}
	}

	function set_status($id,$status)
	{
		$this->db_query("UPDATE languages SET status = $status WHERE id = $id");
	}

	function set_active($id)
	{
		$id = (int)$id;
		$status = $this->db_fetch_field("SELECT status FROM languages WHERE id = $id","status");
		if ($status != 2 && $GLOBALS["uid"] == "")
		{
			return false;
		}
		$this->db_query("UPDATE users SET lang_id = $id WHERE uid = '".$GLOBALS["uid"]."'");
		global $lang_id;
		$lang_id = $id;
		// milleks see cookie vajalik oli?
		// sest ilma selleta ei t88ta. DOH.
		setcookie("lang_id",$id,time()+24*3600*1000,"/");
		session_register("lang_id");
	}

	function find_best()
	{
		global $HTTP_ACCEPT_LANGUAGE;

		$langs = array();
		$this->db_query("SELECT * FROM languages WHERE status = 2");
		while ($row = $this->db_next())
		{
			$langs[$row["acceptlang"]] = $row["id"];
		}

		$larr = explode(",",$HTTP_ACCEPT_LANGUAGE);
		reset($larr);
		while (list(,$v) = each($larr))
		{
			$la = substr($v,0,strcspn($v,"-; "));
			if ($langs[$la])
			{
				return $langs[$la];
			}
		}
		return 1;
	}

	function get_charset()
	{
		$a = $this->fetch($GLOBALS["lang_id"]);
		return $a["charset"];
	}

	function get_langid($id = -1)
	{
		if ($id == -1)
		{
			$id = $GLOBALS["lang_id"];
		}
		$a = $this->fetch($id);
		return $a["acceptlang"];
	}
};
?>
