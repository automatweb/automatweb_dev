<?php

/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property status type=status
	@caption Staatus

	@property db_server type=objpicker clid=CL_DB_SERVER_LOGIN
	@caption Server

	@property db_base type=textbox 
	@caption Andmebaas

	@property db_user type=textbox 
	@caption Kasutajanimi

	@property db_pass type=textbox 
	@caption Parool

	@property db_create_ifnexist type=checkbox ch_value=1
	@caption Loo kui olemas pole

*/

class db_login extends class_base
{
	function db_login()
	{
		$this->init(array(
			'tpldir' => 'awmyadmin/db_login',
			'clid' => CL_DB_LOGIN
		));
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	function callback_post_save($args)
	{
		extract($args);
		$ob = $this->get_object($id);
		if ($ob['meta']['db_create_ifnexist'] && $ob['meta']['db_server'])
		{
			$server = get_instance('awmyadmin/db_server_login');
			if (!$server->login_as($ob['meta']['db_server']))
			{
				$this->raise_error(ERR_DB_ADMIN_NOT_SET, 'The admin user for the database server has not been set!', true, false);
			}
			$found = false;
			$server->db_list_databases();
			while ($db = $server->db_next_database())
			{
				if ($db['name'] == $ob['meta']['db_base'])
				{
					$found = true;
				}
			}

			if (!$found)
			{
				$server->db_create_database(array(
					'name' => $ob['meta']['db_base'],
					'user' => $ob['meta']['db_user'],
					'pass' => $ob['meta']['db_pass'],
					'host' => aw_ini_get('server.hostname')
				));
			}
		}
	}

	function login_as($oid)
	{
		$server = get_instance('awmyadmin/db_server_login');
		$ob = $this->get_object($oid);
		if ($ob['meta']['db_server'] && $ob['meta']['db_base'] != '' && $ob['meta']['db_user'] != '')
		{
			$this->db_connect(array(
				'driver' => $server->get_host_driver($ob['meta']['db_server']),
				'server' => $server->get_host($ob['meta']['db_server']),
				'base' => $ob['meta']['db_base'],
				'username' => $ob['meta']['db_user'],
				'password' => $ob['meta']['db_pass']
			));
		}
		else
		{
			return false;
		}
		return true;
	}
}
?>