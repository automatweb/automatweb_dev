<?php

/*
@classinfo maintainer=voldemar
*/

class quickmessagebox_obj extends _int_object
{
	const APPROVED_SENDERS_ANYONE = 1;
	const APPROVED_SENDERS_CONTACTS = 2;
	const APPROVED_SENDERS_NONE = 3;

	const STATUS_READ = 1;
	const STATUS_UNREAD = 2;

	public function set_status($status)
	{// only one msgbox active for a user
		// if (STAT_$this->status())
		// {
		// }
	}

	/**
	@attrib api=1
	@param msgs required type=object_list
		Messages to change status in this box
	@param status required type=string
		Options: read, unread.
	@returns void
	@comment
		Returns message objects corresponding to status parameter in this box. Read, unread, or all.
	@errors
		Throws awex_qmsg_param on parameter errors.
	**/
	public function set_msgs_status(object_list $msgs, $status)
	{
		switch ($status)
		{
			case "read":
				$type = "RELTYPE_READ_MESSAGE";
				break;
			case "unread":
				$type = "RELTYPE_UNREAD_MESSAGE";
				break;
			default:
				throw new awex_qmsg_param("Invalid status parameter value.");
		}

		$this->connect(array("to" => $msgs->ids(), "type" => $type));
	}

	/**
	@attrib api=1
	@param status optional type=string
		Options: read, unread. All messages in this box returned if not specified.
	@returns array
	@comment
		Returns message objects corresponding to status parameter in this box. Read, unread, or all.
	**/
	public function get_received_msgs($status = null)
	{
		switch ($status)
		{
			case "read":
				$type = "RELTYPE_READ_MESSAGE";
				break;
			case "unread":
				$type = "RELTYPE_UNREAD_MESSAGE";
				break;
			default:
				$type = array("RELTYPE_UNREAD_MESSAGE", "RELTYPE_READ_MESSAGE");
		}

		$ol = new object_list($this->connections_from(array("type" => $type)));
		return $ol;
	}

	/**
	@attrib api=1
	@param msg required type=cl_quickmessage
		Message object
	@returns void
	@comment
		Posts a message to this box.
	**/
	public function post_msg(object $msg)
	{
		if (
			(APPROVED_SENDERS_CONTACTS === $this->prop("approved_senders") and !in_array($msg->prop("from"), $this->prop("contactlist"))) or
			APPROVED_SENDERS_NONE === $this->prop("approved_senders")
		)
		{
			throw new awex_qmsg_unwanted_msg("Message poster is not in this user's approved senders list.");
		}

		$this->connect(array(
			"to" => $msg->id(),
			"type" => "UNREAD_MESSAGE"
		));
	}

	/**
	@attrib api=1
	@returns array
	@comment
		Options for selecting value for approved_senders setting.
	**/
	public static function get_approved_senders_options()
	{
		$options = array(
			APPROVED_SENDERS_ANYONE => t("anyone"),
			APPROVED_SENDERS_CONTACTS => t("users in my contact list"),
			APPROVED_SENDERS_NONE => t("no-one")
		);
		return $options;
	}

	public static function get_msgbox_for_user(object $user)
	{
		$c = $user->connections_to(array(
			"class" => CL_QUICKMESSAGEBOX,
			// "type" => "RELTYPE_OWNER",
			"type" => 4,
			"from.status" => object::STAT_ACTIVE
		));

		if (1 === count($c))
		{
			$c = reset($c);
			return $c->from();
		}
		elseif (0 === count($c))
		{
			throw new awex_qmsg_no_box("User has no messagebox configured.");
		}
		else
		{
			throw new awex_qmsg_cfg("Messagebox configuration error. User has more than one messagebox.");
		}
	}

	/**
	@attrib api=1 params=pos
	@param msgs required type=array
		Array of message object instances or id-s
	@returns void
	@comment
		Deletes messages from this box.
	@errors
		throws awex_qmsg_param when
		- msgs parameter is empty
		- msgs contains a msg that doesn't belong to this msgbox
		throws awex_qmsg when
		- some messages couldn't be deleted for unspecified reasons
	**/
	public function delete_msgs($msgs)
	{
		if (empty($msgs) or !is_array($msgs))
		{
			throw new awex_qmsg_param("No messages to delete.");
		}

		$delete_q = array();
		$failed = array();

		foreach ($msgs as $msg)
		{
			if (!is_a($msg, "object"))
			{
				if (is_oid($msg))
				{
					$msg = new object($msg);
				}
				else
				{
					throw new awex_qmsg_param("Invalid message id '" . $msg . "'.");
				}
			}

			if ($msg->prop("box") !== $this->id())
			{
				$e = new awex_qmsg_param("Trying to delete message not belonging to this messagebox.");
				$e->qmsg_affected_msgs = array($msg->id() => "wrong box");
				throw $e;
			}

			$delete_q[] = $msg;
		}

		foreach ($delete_q as $msg)
		{
			try
			{
				$msg->delete();
			}
			catch (Exception $e)
			{
				$failed[$msg->id()] = $e;
			}
		}

		if (count($failed))
		{
			$e = new awex_qmsg("Some messages couldn't be deleted.");
			$e->qmsg_affected_msgs = $failed;
			throw $e;
		}
	}
}

class awex_qmsg extends awex_obj
{
	public $qmsg_affected_msgs = array(); // array of quickmessage object id-s as index and exception or errorstring as element
}
class awex_qmsg_param extends awex_qmsg {}
class awex_qmsg_unwanted_msg extends awex_qmsg {}
class awex_qmsg_box extends awex_qmsg {}
class awex_qmsg_no_box extends awex_qmsg_box {}
class awex_qmsg_cfg extends awex_qmsg_box {}


?>
