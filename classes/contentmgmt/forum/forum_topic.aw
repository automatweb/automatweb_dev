<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/forum/forum_topic.aw,v 1.3 2003/10/06 14:32:26 kristo Exp $
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
@caption Autori nimi

@property mail_answers type=checkbox store=no
@caption Saada vastused meiliga

@classinfo relationmgr=yes
@classinfo trans_id=TR_FORUM

*/

define('RELTYPE_SUBSCRIBER',1);

class forum_topic extends class_base
{
	function forum_topic()
	{
		$this->init(array(
			"tpldir" => "forum",
			"clid" => CL_MSGBOARD_TOPIC,
			"trid" => TR_FORUM,
		));
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_SUBSCRIBER => "tellija",
		);
	}

	function callback_get_classes_for_relation($args)
	{
		$retval = false;

		switch($args["reltype"])
		{
			case RELTYPE_SUBSCRIBER:
				$retval = array(CL_ML_MEMBER);
				break;
		};
		return $retval;
        }

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "status":
				$retval = PROP_IGNORE;
				break;

			case "mail_answers":
				$forum_obj = $this->get_object(array(
					"oid" => $args["request"]["id"],
					"clid" => CL_FORUM_V2,
				));
				// don't show this element if we don't know where to save
				// addresses
				$addr_folder = $forum_obj["meta"]["address_folder"];
				if (empty($addr_folder) || !is_numeric($addr_folder))
				{
					$retval = PROP_IGNORE;
				};
				break;
		};
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "mail_answers":
				if (is_email($args["form_data"]["author_email"]) && !empty($args["form_data"]["author_name"]))
				{
					$oid = $args["obj"]["oid"];

					$forum_obj = $this->get_object(array(
						"oid" => $args["form_data"]["forum_id"],
						"clid" => CL_FORUM_V2,
					));
					// don't show this element if we don't know where to save
					// addresses
					$addr_folder = $forum_obj["meta"]["address_folder"];

					$t = get_instance("mailinglist/ml_member");
					$t->id_only = true;
					$member_id = $t->submit(array(
						"name" => $args["form_data"]["author_name"],
						"mail" => $args["form_data"]["author_email"],
						"parent" => $addr_folder,
					));
	
					$almgr = get_instance("aliasmgr");
					$almgr->create_alias(array(
						"id" => $oid,
						"alias" => $member_id,
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
		$targets = $this->get_aliases_for($args["id"],-1,"","",array("ml_users" => "objects.oid = ml_users.id"),RELTYPE_SUBSCRIBER);
		$addrlist = array();
		$iidrid = "From: automatweb@automatweb.com\n";
		if (is_array($targets))
		{
			foreach($targets as $key => $val)
			{
				$addrlist[] = array(
					"name" => $val["name"],
					"addr" => $val["mail"],
				);
				send_mail($val["mail"],$args["subject"],$args["message"],$iidrid);
			}
		};
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}
}
?>
