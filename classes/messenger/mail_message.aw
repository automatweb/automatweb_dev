<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/mail_message.aw,v 1.5 2003/09/11 13:26:41 duke Exp $
// mail_message.aw - Mail message

/*
	@default table=objects
	@default group=general

	@default table=messages

	@property uidl type=textbox 
	@caption UIDL

	@property mfrom type=textbox size=80
	@caption Kellelt
	
	@property mto type=textbox size=80
	@caption Kellele
	
	@property name type=textbox size=80 table=objects
	@caption Subjekt

	@property message type=textarea cols=80 rows=40 
	@caption Sisu

	@property send type=submit value=Saada store=no 
	@caption Saada
	
	@tableinfo messages index=id master_table=objects master_index=oid

*/
class mail_message extends class_base
{
	function mail_message()
	{
		$this->init(array(
			"clid" => CL_MESSAGE,
		));
	}

	// I need a simple way to write the contents of one fields into another ..
	// e.g. sync them somehow

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "comment":
			case "status":
				$retval = PROP_IGNORE;
				break;

			case "send":
				/*
				$delurl = $this->mk_my_orb("deliver",array("id" => $args["obj"]["oid"]));
				$data["value"] = html::href(array(
					"url" => "javascript:remote(0,300,400,\"$delurl\")",
					"caption" => "Saada",
				));
				*/
				break;

		}
		return $retval;
	}

	function set_property($args)
	{
		$retval = PROP_OK;
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "status":
				$data["value"] = STAT_ACTIVE;
				break;
	
			case "send":
				if ($args["form_data"]["send"])
				{
					$this->deliver_message = true;
				};
				break;

					
			
		};
		return $retval;
	}

	function callback_post_save($arr)
	{
		if ($this->deliver_message)
		{
			$this->deliver(array("id" => $arr["id"]));
		};
	}
		

	// basically the same as deliver, except that this one is _not_
	// called through ORB, and you can specify replacements here
	function process_and_deliver($args)
	{
		$oid = $args["id"];
		$q = "SELECT name,mfrom,mto,message FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE objects.oid = $oid";
		$this->db_query($q);
		$row = $this->db_next();

		$message = $row["message"];
		if (is_array($args["replacements"]))
		{
			foreach($args["replacements"] as $source => $target)
			{
				$message = str_replace($source,$target,$message);
			}

		}
		
		$awm = get_instance("aw_mail");

		$awm->create_message(array(
			"froma" => $row["mfrom"],
			"subject" => $row["name"],
			"to" => $args["to"],
			"body" => $message,
		));

		$awm->gen_mail();

	}

	function deliver($args)
	{
		$oid = $args["id"];
		$q = "SELECT name,mfrom,mto,message FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE objects.oid = $oid";
		$this->db_query($q);
		$row = $this->db_next();
		$awm = get_instance("aw_mail");

		$awm->create_message(array(
			"froma" => $row["mfrom"],
			"subject" => $row["name"],
			"to" => $row["mto"],
			"body" => $row["message"],
		));

		$awm->gen_mail();
		print "Selle akna võib peale kirja saatmist sulgeda<br />";
		print "-------<br />";
		print "saadetud<br />";
		die();
	}

	function show($arr)
	{
		$obj = new object($arr["id"]);
		print "<b>Kellelt:</b> " . parse_obj_name($obj->prop("mfrom")) . "<br>";
		print "<b>Kellele:</b>" . parse_obj_name($obj->prop("to")) . "<br>";
		print "<b>Teema:</b>" . parse_obj_name($obj->prop("name")) . "<br>";
		print "<br><pre>" . htmlspecialchars($obj->prop("message")) . "</pre><br>";
		exit;
	}
	
	////
	// !fetches a message by it's ID
	// arguments:
	// id(int) - message id
	function msg_get($args = array())
	{
		// Will show only users own messages
		//$q = sprintf("UPDATE messages SET status = %d WHERE id = %d",MSG_STATUS_READ,$args["id"]);
		//$this->db_query($q);
		$q = sprintf("SELECT *,objects.* 
				FROM messages
				LEFT JOIN objects ON (messages.id = objects.oid)
				WHERE id = '%d'",
				$args["id"]);
		$this->db_query($q);
		$row = $this->db_next();
		$row["meta"] = $this->get_object_metadata(array(
			"metadata" => $row["metadata"]
		));
		// get subject from object name, since that is where the new mail_message class keeps
		// it -- duke
		if (empty($row["subject"]) && !empty($row["name"]))
		{
			$row["subject"] = $row["name"];
		};
		return $row;
	}
		

};
?>
