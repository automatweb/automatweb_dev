<?php

/*
@classinfo maintainer=voldemar
*/

class quickmessage_obj extends _int_object
{
	const TYPE_GENERIC = 1;
	const TYPE_SYS = 2;

	public function awobj_get_from_display()
	{
		$u_oid = $this->prop("from");

		try
		{
			$u_o = new object($u_oid);
			$p_oid = user::get_person_for_user($u_o);
			$p_o = new object($p_oid);
			$p_name = $p_o->name();
		}
		catch (awex_obj_acl $e)
		{
			$p_name = t("[No access to user data]");
		}

		return $p_name;
	}

	public function get_to_options()
	{//!!! teha ymber, et msgbox v6etaks current useri kaudu
		if (!$_GET["parent"])
		{
			return;
		}

		try
		{
			$msgbox = new object($_GET["parent"]);
		}
		catch (Exception $e)
		{
			return;
		}

		$options = array();
		$contactlist = $msgbox->prop("contactlist");
		foreach ($contactlist as $u_oid)
		{
			try
			{
				$u_o = new object($u_oid);
				$p_oid = user::get_person_for_user($u_o);
				$p_o = new object($p_oid);
				$options[$u_oid] = $p_o->name();
			}
			catch (Exception $e)
			{
			}
		}

		return $options;
	}

	public function awobj_set_to($value)
	{
		if (!is_array($value) or !count($value))
		{
			throw new awex_qmsg_param("Invalid message recipient parameter specified. [" . var_export($value, true) . "]");
		}

		foreach ($value as $id)
		{
			if (!is_oid($id))
			{
				throw new awex_qmsg_param("Invalid message recipient id specified. [" . $id . "]");
			}
		}

		$value = implode(",", $value);
		return parent::set_prop("to", $value);
	}

	public function awobj_get_to()
	{
		return explode(",", parent::prop("to"));
	}

	public function save()
	{
		$new = !$this->obj["oid"];

		if ($new)
		{
			try
			{
				$to_o = new object($this->prop("to"));
				$msgbox = quickmessagebox_obj::get_msgbox_for_user($to_o);
				$u_oid = aw_global_get("uid_oid");
				$this->set_prop("from", $u_oid);
				$this->set_name(aw_global_get("uid") . " => " .  $to_o->name() . " @ " . date("d.M. Y H:i:s"));
			}
			catch (awex_obj_acl $e)
			{
				throw $e;
			}
			catch (aw_exception $e)
			{
				throw new awex_qmsg_box("Messagebox not defined. Can't send message.");
			}
		}

		$retval = parent::save();

		if ($new)
		{
			$msgbox->post_msg(new object($this->id()));
		}

		return $retval;
	}
}

?>
