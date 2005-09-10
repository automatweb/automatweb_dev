<?php

class user_object extends _int_object
{
	function set_prop($k, $v)
	{
		if ($prop == "password")
		{
			if (aw_ini_get("auth.md5_passwords") == 1)
			{
				$v = md5($v);
			}
		}
		parent::set_prop($k,$v);
	}
}
?>
