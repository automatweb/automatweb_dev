<?php
// $Header
// Apache alias management

class apache_alias extends aw_template
{
	function apache_alias()
	{
		$this->init("apache_alias");
	}

	////
	// !Generates a list of registered aliases
	function gen_list($args = array())
	{
		extract($args);
		$this->read_template("list.tpl");
	
		$link = $this->mk_my_orb("list",array(),"apache_alias",1,0);
		$this->mk_path(-1,"<a href='$link'>Aliased</a>");

		$q = "SELECT count(*) AS cnt FROM apache_aliases";
		$this->db_query($q);
		$row = $this->db_next();

		$cnt = $row["cnt"];

		$page = ($page) ? $page : 0;
		$on_page = 50;

		$from = $page * $on_page;

		$pages = (($cnt - 1)/ $on_page);

		$pager = "";
		for ($i = 0; $i <= $pages; $i++)
		{
			$this->vars(array(
				"pagelink" => $this->mk_my_orb("list",array("page" => $i)),
				"pagetitle" => ($i * $on_page + 1) . "-" . ($i * $on_page + $on_page),
			));
			if ($i == $page)
			{
				$pager .= $this->parse("act_page");
			}
			else
			{
				$pager .= $this->parse("page");
			};
		};


		$q = "SELECT * FROM apache_aliases LIMIT $from,$on_page";
		$this->db_query($q);
		$lines = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
				"id" => $row["id"],
				"alias" => $row["alias"],
				"dir" => $row["dir"],
				"modified" => $this->time2date($row["modified"],2),
				"created" => $this->time2date($row["created"],2),
				"modifiedby" => $row["modifiedby"],
				"createdby" => $row["createdby"],
				"change" => $this->mk_my_orb("change",array("id" => $row["id"])),
				"remove" => $this->mk_my_orb("remove",array("id" => $row["id"])),
			));

			$lines .= $this->parse("LINE");
		};


		$this->vars(array(
			"add" => $this->mk_my_orb("new",array()),
			"LINE" => $lines,
			"page" => $pager,
			"conf" => $this->gen_conf(),
		));
		return $this->parse();
	}

	////
	// !Allows to add or change apache aliases
	function change($args = array())
	{
		extract($args);
		$this->read_template("edit.tpl");

		if ($id)
		{
			$rec = $this->get_record("apache_aliases","id",$id);
			$title = "Muuda aliast";
		}
		else
		{
			$rec = array();
			$rec["id"] = "Uus";
			$title = "Lisa alias";
		};

		$error = aw_global_get("ap_error");


		$link = $this->mk_my_orb("list",array(),"apache_alias",1,0);
		$this->mk_path(-1,"<a href='$link'>Aliased</a> / $title");

		$this->vars(array(
			"id" => $rec["id"],
			"alias" => ($error) ? aw_global_get("ap_alias") : $rec["alias"],
			"dir" => ($error) ? aw_global_get("ap_dir") : $rec["dir"],
			"error" => $error,
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));

		return $this->parse();
	}
	
	////
	// !Submits a new or existing alias
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);

		if (strlen($alias) == 0)
		{
			$error .= "Alias ei saa olla tühi!<br>";
		};

		if (strlen($dir) == 0)
		{
			$error .= "Kataloog ei saa olla tühi!<br>";
		};

		$check = $this->get_record("apache_aliases","alias",$alias);
		if ($check)
		{
			$error .= "Sellise nimega alias on juba olemas!<br>";
		};

		if ($error)
		{
			aw_session_set("ap_alias",$alias);
			aw_session_set("ap_dir",$dir);
			aw_session_set("ap_error",$error);
			return $this->mk_my_orb($id ? "change" : "new",array("id" => $id));
		};

		$uid = aw_global_get("uid");
		$t = time();
		if ($id)
		{
			$q = "UPDATE apache_aliases SET
				alias = '$alias',
				dir = '$dir',
				modified = '$t',
				modifiedby = '$uid'
			      WHERE id = '$id'";
		}
		else
		{
			$q = "INSERT INTO apache_aliases
				(alias,dir,modified,modifiedby,created,createdby)
				VALUES ('$alias','$dir','$t','$uid','$t','$uid')";
		};
		$this->db_query($q);
		return $this->mk_my_orb("list",array());
	}

	////
	// !Deletes an alias
	function remove($args = array())
	{
		extract($args);
		$q = "DELETE FROM apache_aliases WHERE id = '$id'";
		$this->db_query($q);
		return $this->mk_my_orb("list",array());
	}

	function gen_conf($args = array())
	{
		$q = "SELECT alias,dir FROM apache_aliases ORDER by alias";
		$this->db_query($q);
		$conf = "";
		while($row = $this->db_next())
		{
			$conf .= "Alias /$row[alias] $row[dir]\n";
		};
		return $conf;
	}

}

?>
