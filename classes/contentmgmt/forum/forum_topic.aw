<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_topic.aw,v 1.13 2005/08/19 12:18:20 dragut Exp $
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

@property locked type=checkbox ch_value=1 field=meta method=serialize
@caption Teema lukus
@comment Lukus teemale uusi kommentaare lisada ei saa

@property answers_to_mail type=checkbox ch_value=1 store=no
@caption Soovin vastuseid e-mailile

@property image type=releditor reltype=RELTYPE_IMAGE rel_id=first use_form=emb field=meta method=serialize
@caption Pilt

@property subscribers_editor type=releditor store=no mode=manager reltype=RELTYPE_SUBSCRIBER props=mail,name group=subscribers

@classinfo relationmgr=yes syslog_type=ST_FORUM_TOPIC

@groupinfo subscribers caption="Mailinglist"

@reltype SUBSCRIBER value=1 clid=CL_ML_MEMBER
@caption Tellija

@reltype IMAGE value=2 clid=CL_IMAGE
@caption Pilt

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
	
	function callback_post_save($arr)
	{
		
		if($arr["request"]["answers_to_mail"] && is_email($arr["request"]["author_email"]))
		{
			
			$mail_addres = new object(array(
				"class_id" => CL_ML_MEMBER,
				"name" => $arr["request"]["author_name"],
				"parent" => $arr["obj_inst"]->id(),
			));
			//It fucking sucks, i have to save this object twice, to set mail property
			$mail_addres->save();
			$mail_addres->set_prop("mail", $arr["request"]["author_email"]);
			$mail_addres->save();
			
			$arr["obj_inst"]->connect(array(
				"to" => $mail_addres->id(),
				"reltype" => "RELTYPE_SUBSCRIBER",
			));
		}
	}
	
	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "author_name":
			case "author_email":
			case "comment":
			case "name":
				if (empty($prop["value"]))
				{
					$prop["error"] = $prop["caption"] . " ei tohi olla tühi";
					$retval = PROP_FATAL_ERROR;
				};
				break;
/*
			case "image":
				if ($arr["new"]) 
				{
					$retval = PROP_IGNORE;
				};
				break;
*/			
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
		$forum_obj = &obj($args["forum_id"]);
		$topic_obj = &obj($args["id"]);
		if($forum_obj->prop("mail_subject"))
		{
			$subject = $forum_obj->prop("mail_subject");
		}
		else
		{
			$subject = $topic_obj->name();
		}
		
		if($forum_obj->prop("mail_address") || $forum_obj->prop("mail_from"))
		{
			$from = "From:".$forum_obj->prop("mail_from")."<".$forum_obj->prop("mail_address").">\n";
		}
		else
		{
			$from = "From: automatweb@automatweb.com\n";
		}
		
		$targets = array();
		$targets = $topic_obj->connections_from(array(
			"type" => "RELTYPE_SUBSCRIBER",
		));
		foreach($targets as $target)
		{
			$target_obj = $target->to();
			send_mail($target_obj->prop("mail"),$subject,$args["message"],$from);
		};

	}
}
?>
