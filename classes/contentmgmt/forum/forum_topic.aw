<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_topic.aw,v 1.5 2004/09/09 20:28:45 sven Exp $
// forum_comment.aw - foorumi kommentaar
/*

@default table=objects
@default group=general

@property name type=textbox
@caption Pealkiri

@property comment type=textarea
@caption Sisu

@property author_name type=textbox field=meta method=serialize
@caption Autori nimi

@property author_email type=textbox field=meta method=serialize
@caption Autori meil

@property mail_answers type=checkbox store=no
@caption Saada vastused meiliga

@classinfo relationmgr=yes

@reltype SUBSCRIBER value=1 clid=CL_ML_MEMBER
@caption tellija

@classinfo no_status=1

*/


class forum_topic extends class_base
{
	function forum_topic()
	{
		$this->init(array(
			"tpldir" => "forum",
			"clid" => CL_MSGBOARD_TOPIC,
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "mail_answers":
				$request = $arr["request"];
				if (is_email($request["author_email"]) && !empty($request["author_name"]))
				{
					$forum_obj = new object($request["forum_id"]);

					$t = get_instance(CL_ML_MEMBER);
					$t->id_only = true;
					$member_id = $t->submit(array(
						"name" => $request["author_name"],
						"mail" => $request["author_email"],
						"parent" => $forum_obj->prop("address_folder"),
					));

					$forum_obj->connect(array(
						"to" => $member_id,
						"reltype" => RELTYPE_SUBSCRIBER,
					));
				};
				
				break;


		}
		return $retval;
	}

	////
	// !Well. Mails all the subscribers of a topic
	// id - id of the topic object
	// subject - subject of the message
	// message - contents of the message
	function mail_subscribers($args = array())
	{
		// well. this should be easy. figure out a list of addressess
		// and then send mail to those?
		$targets = $pr_obj->connections_from(array(
			"type" => RELTYPE_SUBSCRIBER,
		));

		$iidrid = "From: automatweb@automatweb.com\n";

		foreach($targets as $target)
		{
			$target_obj = $target->to();
			send_mail($target_obj->prop("mail"),$args["subject"],$args["message"],$iidrid);
		};

	}
}
?>
