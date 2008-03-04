<?php
// quickmessagebox.aw - Kiirs5numite haldus
/*

@classinfo syslog_type=ST_QUICKMESSAGEBOX no_status=1 maintainer=voldemar

@groupinfo message_box caption="Messages"
	@groupinfo message_inbox caption="Inbox" parent=message_box
	@groupinfo message_outbox caption="Outbox" parent=message_box
@groupinfo general caption="Settings"

@default table=objects
@default field=meta
@default method=serialize

@default group=general
	@property owner type=relpicker automatic=1 newonly=1 reltype=RELTYPE_OWNER
	@caption Select owner

	@property is_contactlist_add_approval_required type=checkbox ch_value=1
	@caption Require approval

	@property approved_senders type=select
	@caption Allow messages from

	@property contactlist type=relpicker multiple=1 size=7 reltype=RELTYPE_CONTACT automatic=1
	@caption Contacts

@property msg_tbl type=table no_caption=1 store=no group=message_inbox,message_outbox
@caption Messages

@property msg_toolbar type=toolbar no_caption=1 store=no group=message_inbox,message_outbox
@property Inboxi toolbar


////////////////// RELTYPES ////////////////////
@reltype OWNER value=4 clid=CL_USER
@caption Owner

@reltype CONTACT value=3 clid=CL_USER
@caption Contact

*/

class quickmessagebox extends class_base
{
	function quickmessagebox()
	{
		$this->init(array(
			"tpldir" => "applications/quickmessage/quickmessagebox",
			"clid" => CL_QUICKMESSAGEBOX
		));
	}

	function get_msg_tbl($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->init_msg_tbl($t);
	}

	private function init_msg_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "from",
			"caption" => t("From"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "msg",
			"caption" => t("Message"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "sent",
			"caption" => t("Sent"),
			"sortable" => 1
		));
	}

	function get_msg_toolbar($arr)
	{
		$toolbar =& $arr["prop"]["vcl_inst"];
		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Delete"),
			"action" => "delete_message",
			"confirm" => t("Delete selected messages?")
		));
	}

	function get_approved_senders($arr)
	{
		$arr["prop"]["options"] = quickmessagebox_obj::get_approved_senders_options();
	}

	function get_owner($arr)
	{
		$request = aw_request->get_current();
		$uid = aw_global_get("uid");
		$arr["prop"]["options"][] = t;
	}

	/**
		@attrib name=delete_message
		@param id required type=int acl=view
		@param sel required
	**/
	function delete_message($arr)
	{
		$o = new object($arr["id"]);

		try
		{
			$o->delete_msgs($arr["sel"]);
		}
		catch (aw_exception $e)
		{
			echo $e->getMsg();
			flush();
		}
	}
}

?>
