<?php
// $Header: /home/cvs/automatweb_dev/classes/mailinglist/Attic/ml_list_report.aw,v 1.3 2003/04/07 13:17:40 duke Exp $
// ml_list_report - listi raport

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property lists type=objpicker clid=CL_ML_LIST multiple=1
	@caption Listid

	@property result_table type=text store=no no_caption=1
	@caption Tabel

*/

class ml_list_report extends class_base
{
	function ml_list_report()
	{
		$this->init(array(
			"clid" => CL_ML_LIST_REPORT,
			"tpldir" => "mailinglist/ml_list_report",
		));
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
                        case "result_table":
				$data["value"] = $this->gen_result_table($args);
				break;
		};
		return $retval;
	}

	function gen_result_table($args = array())
	{
		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "ml_list_report",
		));

		$id = $args["obj"]["oid"];
		
		if (empty($args["request"][$mail_id]))
		{
			$t->parse_xml_def($this->cfg["basedir"] . "/xml/mlist/report_mails.xml");
			$q = "
				SELECT m_objects.name as member, l_objects.name as lid, tm, subject, id, mail
				FROM ml_sent_mails 
				LEFT JOIN objects AS m_objects ON m_objects.oid = ml_sent_mails.member 
				LEFT JOIN objects AS l_objects ON l_objects.oid = ml_sent_mails.lid
				WHERE lid IN (".join(",", $args["obj"]["meta"]["lists"]).")
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
				WHERE lid IN (".join(",", $args["obj"]["meta"]["lists"]).") AND mail = '$args[request][$mail_id]'
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

		return $rt;
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
