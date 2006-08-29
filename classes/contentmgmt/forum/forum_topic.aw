<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_topic.aw,v 1.19 2006/08/29 13:27:48 dragut Exp $
// forum_comment.aw - foorumi kommentaar
/*
@classinfo relationmgr=yes syslog_type=ST_FORUM_TOPIC no_status=1

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

	@property image type=releditor reltype=RELTYPE_FORUM_IMAGE rel_id=first use_form=emb field=meta method=serialize
	@caption Pilt

	@property image_verification type=text stoer=no
	@caption Kontrollkood

@groupinfo subscribers caption="Mailinglist"

	@property subscribers_editor type=releditor store=no mode=manager reltype=RELTYPE_SUBSCRIBER props=mail,name group=subscribers no_caption=1


@reltype SUBSCRIBER value=1 clid=CL_ML_MEMBER
@caption Tellija

@reltype FORUM_IMAGE value=2 clid=CL_IMAGE
@caption Pilt

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
					$prop["error"] = $prop["caption"] . " ei tohi olla t�hi!";
					$retval = PROP_FATAL_ERROR;
				};
				break;
			case 'image_verification':
				if ($this->can('view', $arr['request']['forum_id']))
				{
					$forum_obj = new object($arr['request']['forum_id']);
					if ($forum_obj->prop('use_image_verification'))	
					{
						$image_verification_inst = get_instance('core/util/image_verification/image_verification');
						if ( !$image_verification_inst->validate($arr['request']['ver_code']) )
						{
							$prop['error'] = t('Sisestatud kontrollkood on vale!');
							$retval = PROP_FATAL_ERROR;
						}
					}	
				}
				break;

		}
		return $retval;
	}

	////
	// !Well. Mails all the subscribers of a topic
	// id - id of the topic object
	// forum_id - id of the forum object
	// subject - subject of the message
	// message - contents of the message
	// topic_url - url to topic where comment was added
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

		// composing the message:
		$message = $args['title']."\n\n";
		$message .= $args['message']."\n\n";
		$message .= $args['topic_url'];

		$targets = array();
		$targets = $topic_obj->connections_from(array(
			"type" => "RELTYPE_SUBSCRIBER",
		));
		$targets += $forum_obj->connections_from(array(
			'type' => 'RELTYPE_EMAIL'
		));
		foreach($targets as $target)
		{
			$target_obj = $target->to();
			send_mail($target_obj->prop("mail"),$subject,$message,$from);
		};

	}
}
?>
