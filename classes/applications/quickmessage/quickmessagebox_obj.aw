<?php

/*
@classinfo maintainer=voldemar
*/

class quickmessagebox_obj extends _int_object
{
	const APPROVED_SENDERS_ANYONE = 1;
	const APPROVED_SENDERS_CONTACTS = 2;
	const APPROVED_SENDERS_NONE = 3;

	public function __construct()
	{
	}

	/**
	@attrib api=1
	@returns array
	@comment
		Returns message objects that are marked unread in this box.
	**/
	public function get_unread_msgs()
	{
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

	/**
	@attrib api=1 params=pos
	@param msgs required type=array
		Array of message object instances or id-s
	@returns void
	@comment
		Deletes messages from this box.
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
			catch ($e)
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

class awex_qmsg extends aw_exception
{
	public $qmsg_affected_msgs = array(); // array of quickmessage object id-s as index and exception or errorstring as element
}
class awex_qmsg_param extends awex_qmsg {}

?>
