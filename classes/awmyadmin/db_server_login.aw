<?php

/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property status type=status group=general 
	@caption Staatus

	@property server_host type=textbox 
	@caption Server

	@property server_admin_user type=textbox 
	@caption Admin kasutaja

	@property server_admin_pass type=textbox 
	@caption Admin parool

	@property server_driver type=select 
	@caption Andmebaasi driver

	@property test_lb type=select 
	@caption Testi listbox

*/

class db_server_login extends class_base
{
	function db_server_login()
	{
		$this->init(array(
			'tpldir' => 'awmyadmin/db_server_login',
			'clid' => CL_DB_SERVER_LOGIN
		));
	}

	function get_property($args)
	{
		if ($args['prop']['name'] == 'server_driver')
		{
			$args['prop']['options'] = $this->list_db_drivers();
		}
		else
		if ($args['prop']['name'] == 'test_lb')
		{
			$args['prop']['options'] = array('a' => 'yx', 'b' => 'kax');
		}
		return PROP_OK;
	}

	function login_as($oid)
	{
		$ob = $this->get_object($oid);
		if ($ob['meta']['server_driver'] != '' && $ob['meta']['server_host'] != '' && $ob['meta']['server_admin_user'] != '')
		{
			$this->db_connect(array(
				'driver' => $ob['meta']['server_driver'],
				'server' => $ob['meta']['server_host'],
				'base' => aw_ini_get('db.base'),
				'username' => $ob['meta']['server_admin_user'],
				'password' => $ob['meta']['server_admin_pass']
			));
		}
		else
		{
			return false;
		}
		return true;
	}

	function get_host($oid)
	{
		$ob = $this->get_object($oid);
		return $ob['meta']['server_host'];
	}

	function get_host_driver($oid)
	{
		$ob = $this->get_object($oid);
		return $ob['meta']['server_driver'];
	}
}
?>
