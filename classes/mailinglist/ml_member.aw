<?php
// $Header: /home/cvs/automatweb_dev/classes/mailinglist/Attic/ml_member.aw,v 1.27 2004/05/06 12:37:15 duke Exp $
// ml_member.aw - Mailing list member

/*
	@default table=objects
	@default group=general

	@property name type=textbox table=ml_users
	@caption Nimi

	@property mail type=textbox table=ml_users
	@caption E-post

	@classinfo syslog_type=ST_MAILINGLIST_MEMBER
	@classinfo no_status=1 no_comment=1

	@tableinfo ml_users index=id master_table=objects master_index=oid
*/

class ml_member extends class_base
{
	function ml_member()
	{
		$this->init(array(
			"clid" => CL_ML_MEMBER,
		));
		lc_load("definition");
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
		}
		return $retval;
	}

	function set_property($arr)
        {
                $data = &$arr["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
		}
		return $retval;
	}

	////
	// email(string) - email addy
	// folder(string) - id of the folder to check
	function check_member($args = array())
	{
		$this->quote($args);
		extract($args);
		// XXX: can I do this with object_list/search?
		$q = "SELECT oid FROM objects LEFT JOIN ml_users ON (objects.oid = ml_users.id) WHERE mail = '$email' AND parent = '$folder' AND status != 0";
		$this->db_query($q);
		return $this->db_next();
	}

	function get_member_by_id($id)
	{
		$id = (int)$id;
		$q = "SELECT ml_users.name,ml_users.mail FROM objects LEFT JOIN ml_users ON (objects.oid = ml_users.id) WHERE ml_users.id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		return $row["name"] . " " . $row["mail"];
	}

	////
	// !Creates a new subscribe, the other version, deals with members with no config form
	// name - name of the subscriber
	// email - email addy
	// list_id - id of the list to use for subscribing
	function subscribe_member_to_list($args = array())
	{
		// it would be _really_, _really_ nice if I could init 
		// the data from the class_base, but it's not yet possible
		$this->quote($args);
		$name = $args["name"];
		$email = $args["email"];
		$list_id = $args["list_id"];

		$list_obj = new object($list_id);
		$user_folder = $list_obj->prop("def_user_folder");

		$section = aw_global_get("section");
		
		if (empty($user_folder))
		{
			return $this->cfg["baseurl"] . "/" . $section;
		};

		$status = 2;

		
		// I need to validate that stuff as well
		if ($list_obj->prop("confirm_subscribe") != "")
		{
			// generate the confirm code
			$status = 1; 
			$ts = time();
			$hash = substr(gen_uniq_id(),0,15);
			// now I need to generate the confirm url
			$url = $this->mk_my_orb("confirmsub",array("hash" => $hash,"addr" => $email));
		};
			
		$objname = $name . " <" . $email . ">";

		if (sizeof($args["use_folders"]) > 0)
		{
			$user_folders = $args["use_folders"];
		}
		else
		{
			$user_folders = array($user_folder);
		};

		foreach($user_folders as $user_folder)
		{
			if (!$this->check_member(array("email" => $email,"folder" => $user_folder)))
			{
				$objname = htmlspecialchars($objname);
				// Why do we duplicate name and email in object metadata?
				$member_obj = new object();
				$member_obj->set_class_id($this->clid);
				$member_obj->set_parent($user_folder);
				$member_obj->set_status($status);
				$member_obj->set_name($objname);

				$member_obj->set_prop("name",$name);
				$member_obj->set_prop("mail",$email);

				$member_obj->set_meta("name",$name);
				$member_obj->set_meta("email",$email);
				$member_obj->set_meta("hash",$hash);
				$member_obj->set_meta("time",$ts);

				$member_obj->save();
		
			};
		};
		
		if ($list_obj->prop("confirm_subscribe") != "" && $list_obj->prop("confirm_subscribe_msg") != "")
		{
			// now generate and send the bloody message
			$msg = get_instance("messenger/mail_message");
			$msg->process_and_deliver(array(
				"id" => $list_obj->prop("confirm_subscribe_msg"),
				"to" => $objname,
				"replacements" => array(
					"#list#" => parse_obj_name($list_obj->name()),
					"#url#" => $url,
				),
			));
		}

		return $this->cfg["baseurl"] . "/" . $section;

	}

	////
	// !Removes a member from list
	// email - email addy
	// list_id - id of the list to unsubscribe from
	function unsubscribe_member_from_list($args = array())
	{
		$this->quote($args);
		$email = $args["email"];
		$list_id = $args["list_id"];

		$list_obj = new object($list_id);

		$section = aw_global_get("section");

		$user_folder = $list_obj->prop("def_user_folder");

		if (empty($user_folder))
		{
			return $this->cfg["baseurl"] . "/" . $section;
		};

		$check = $this->check_member(array(
			"email" => $args["email"],
			"folder" => $user_folder,
		));

		if ($check)
		{
			$member_obj = new object($check["oid"]); 
			$member_obj->delete();
		};

		// fuck me plenty
		return isset($args["ret_status"]) ? $check : $this->cfg["baseurl"] . "/" . $section;
	}

	////
	// !Returns member information (e-mail address and variables)
	// lid (int) - list id
	// member (int) - member id
	function get_member_information($args = array())
	{
		extract($args);

		$memberdata = array();
		$mailto = "";

		$list_obj = new object($lid);

		$m = new object($member);

		$replica = $this->db_fetch_row("SELECT name,mail FROM ml_users WHERE id = '$member'");

		$ml_list_inst = get_instance(CL_ML_LIST);

		if (is_array($replica))
		{
			$mailto = $replica["mail"];
			$memberdata["name"] = $replica["name"];
		}
		return array($mailto,$memberdata);
	}

	function callback_pre_save($arr)
	{
		$request = $arr["request"];
		if (empty($request["name"]) && !empty($request["mail"]))
		{
			$arr["obj_inst"]->set_name($request["name"] . " &lt;" .$request["mail"] . "&gt;");
		};
	}		
};
?>
