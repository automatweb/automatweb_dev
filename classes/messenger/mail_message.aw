<?php
// $Header: /home/cvs/automatweb_dev/classes/messenger/Attic/mail_message.aw,v 1.3 2003/08/01 13:27:53 axel Exp $
// mail_message.aw - Mail message

/*
	@default table=objects
	@default group=general

	@default table=messages

	@property mfrom type=textbox size=80
	@caption Kellelt
	
	@property mto type=textbox size=80
	@caption Kellele
	
	@property name type=textbox size=80 table=objects
	@caption Subjekt

	@property message type=textarea cols=80 rows=40 
	@caption Sisu

	@property send type=text value=Saada store=no editonly=1
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
				$delurl = $this->mk_my_orb("deliver",array("id" => $args["obj"]["oid"]));
				$data["value"] = html::href(array(
					"url" => "javascript:remote(0,300,400,\"$delurl\")",
					"caption" => "Saada",
				));
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
			
		};
		return $retval;
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

};
?>
