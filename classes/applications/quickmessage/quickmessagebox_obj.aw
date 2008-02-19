<?php

class quickmessagebox_obj extends _int_object
{
	const APPROVED_SENDERS_ANYONE = 1;
	const APPROVED_SENDERS_CONTACTS = 2;
	const APPROVED_SENDERS_NONE = 3;

	public function __construct()
	{
	}

	public function get_unread_msgs()
	{
	}

	public function get_approved_senders_options()
	{
		$options = array(
			APPROVED_SENDERS_ANYONE => t("anyone"),
			APPROVED_SENDERS_CONTACTS => t("users in my contact list"),
			APPROVED_SENDERS_NONE => t("no-one")
		);
		return $options;
	}
}

?>
