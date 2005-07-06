<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug.aw,v 1.4 2005/07/06 19:05:20 duke Exp $
// bug.aw - Bugi 
/*

@classinfo syslog_type=ST_BUG relationmgr=yes no_comment=1 

@tableinfo aw_bugs index=aw_id master_index=brother_of master_table=objects

@default group=general

@property name type=textbox table=objects
@caption Lühikirjeldus

@default table=aw_bugs
@property bug_content type=textarea rows=5 cols=80
@caption Sisu

@property bug_status type=select
@caption Staatus

@property bug_priority type=select
@caption Prioriteet

@property bug_severity type=select
@caption T&ouml;sidus

//////// inf 
property reporter_browser type=classificator
caption Brauser

property reporter_os type=classificator
caption OS

@property bug_class type=select
@caption Klass

@property bug_component type=textbox 
@caption Komponent

@property bug_url type=textbox size=100
@caption URL

@property bug_mail type=textbox size=60
@caption Bugmail CC

@property comms type=comments group=comments store=no
@caption Kommentaarid

@groupinfo comments caption="Kommentaarid"

@reltype MONITOR value=1 clid=CL_CRM_PERSON
@caption Jälgija



*/

class bug extends class_base
{
	function bug()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug",
			"clid" => CL_BUG
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "comms":
				// email any persons interested in status changes of that bug
				$this->notify_monitors($arr);
				break;
		}
		return $retval;
	}	

	function notify_monitors($arr)
	{
		$monitors = $arr["obj_inst"]->connections_from(array(
			"type" => "RELTYPE_MONITOR",
		));

		// I should add a way to send CC-s to arbitraty e-mail addresses as well
		foreach($monitors as $person)
		{
			$person_obj = $person->to();
			$email = $person_obj->prop("email");
			$notify_addresses = array();
			if (is_oid($email))
			{
				$email_obj = new object($email);
				$addr = $email_obj->prop("mail");
				if (is_email($addr))
				{
					$notify_addresses[] = $addr;
				};
			};
		};

		$addrs = explode(",",$arr["obj_inst"]->prop("bug_mail"));
		foreach($addrs as $addr)
		{
			if (is_email($addr))
			{
				$notify_addresses[] = $addr;
			}; 
		};
		if (sizeof($notify_addresses) == 0)
		{
			return false;
		};

		$notify_list = join(",",$notify_addresses);

		$oid = $arr["obj_inst"]->id();
		$name = $arr["obj_inst"]->name();
		$uid = aw_global_get("uid");

		$msgtxt = t("Bug") . ": " . $oid . "\n";
		$msgtxt .= t("Summary") . ": " . $name . "\n";
		$msgtxt .= t("URL") . ": " . $this->mk_my_orb("change",array("id" => $oid)) . "\n";
		$msgtxt .= "-------------\n\nNew comment from " . $uid . " at " . date("Y-m-d H:i") . "\n";
		$msgtxt .= $arr["request"]["comms"]["comment"];

		send_mail($notify_list,"Bug #" . $oid . ": " . $name . " : " . $uid . " lisas kommentaari",$msgtxt,"From: automatweb@automatweb.com");

		/*
		print "<pre>";
		print $msgtxt;
		print "</pre>";
		*/
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
