<?php

class ml_list_report extends aw_template
{
	function ml_list_report()
	{
		$this->init("mailinglist/ml_list_report");
	}

	////
	// !called, when adding a new object 
	// parameters:
	//    parent - the folder under which to add the object
	//    return_url - optional, if set, the "back" link should point to it
	//    alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa ml_list_report");
		}
		else
		{
			$this->mk_path($parent,"Lisa ml_list_report");
		}
		$this->read_template("change.tpl");

		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));

		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
			"lists" => $this->mpicker(array(), $this->list_objects(array("class" => CL_ML_LIST))),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
		));
		return $this->parse();
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_ML_LIST_REPORT
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"lists" => $this->make_keys($lists)
			)
		));
		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ml_list_report");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda ml_list_report");
		}
		$this->read_template("change.tpl");
	
		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));

		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "ml_list_report",
		));
		if (!$mail_id)
		{
			$t->parse_xml_def($this->cfg["basedir"] . "/xml/mlist/report_mails.xml");
			$q = "
				SELECT m_objects.name as member, l_objects.name as lid, tm, subject, id, mail
				FROM ml_sent_mails 
				LEFT JOIN objects AS m_objects ON m_objects.oid = ml_sent_mails.member 
				LEFT JOIN objects AS l_objects ON l_objects.oid = ml_sent_mails.lid
				WHERE lid IN (".join(",", $ob["meta"]["lists"]).")
				GROUP BY mail
			";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$row['subject'] = html::href(array(
					'url' => $this->mk_my_orb("change", array("id" => $id,"mail_id" => $row['mail'])),
					'caption' => $row['subject']
				));
				$t->define_data($row);
			}
			$t->sort_by();
			$rt = $t->draw();
		}
		else
		{
			$t->parse_xml_def($this->cfg["basedir"] . "/xml/mlist/report.xml");
			$q = "
				SELECT m_objects.name as member, l_objects.name as lid, tm, subject, id
				FROM ml_sent_mails 
				LEFT JOIN objects AS m_objects ON m_objects.oid = ml_sent_mails.member 
				LEFT JOIN objects AS l_objects ON l_objects.oid = ml_sent_mails.lid
				WHERE lid IN (".join(",", $ob["meta"]["lists"]).") AND mail = '$mail_id'
			";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$row["member"] = "<a href='".$this->mk_my_orb("show_mail", array("id" => $id, "mail_id" => $row["id"]))."'>".$row["member"]."</a>";
				$t->define_data($row);
			}
			$t->sort_by();
			$rt = $t->draw();
		}

		$this->vars(array(
			"res_tbl" => $rt,
			"toolbar" => $tb->get_toolbar(),
			"lists" => $this->mpicker($ob["meta"]["lists"], $this->list_objects(array("class" => CL_ML_LIST))),
			"name" => $ob["name"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

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
		$row["parent"] = $parent;
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	function show_mail($arr)
	{
		extract($arr);
		$this->read_template("show_mail.tpl");
		$ob = $this->get_object($id);
		$this->mk_path($ob["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Mailide nimekiri</a> / Vaata maili");

		$row = $this->db_fetch_row("SELECT * FROM ml_sent_mails WHERE id = '$mail_id'");
		$this->vars(array(
			"from" => $row["mailfrom"],
			"subject" => $row["subject"],
			"message" => nl2br($row["message"])
		));
		return $this->parse();
	}
}
?>
