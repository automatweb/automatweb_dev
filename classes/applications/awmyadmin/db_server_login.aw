<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/awmyadmin/db_server_login.aw,v 1.2 2005/01/26 12:09:11 kristo Exp $
// db_server_login.aw - Andmebaasi serveri login

/*
	@classinfo syslog_type=ST_DB_SERVER_LOGIN relationmgr=yes no_status=1 no_comment=1

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property server_host type=textbox 
	@caption Server

	@property server_admin_user type=textbox 
	@caption Admin kasutaja

	@property server_admin_pass type=password
	@caption Admin parool

	@property server_driver type=select 
	@caption Andmebaasi driver

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
		return PROP_OK;
	}

	function login_as($oid)
	{
		$ob = obj($oid);
		if ($ob->prop('server_driver') != '' && $ob->prop('server_host') != '' && $ob->prop('server_admin_user') != '')
		{
			$this->db_connect(array(
				'driver' => $ob->prop('server_driver'),
				'server' => $ob->prop('server_host'),
				'base' => aw_ini_get('db.base'),
				'username' => $ob->prop('server_admin_user'),
				'password' => $ob->prop('server_admin_pass')
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
		$ob = obj($oid);
		return $ob->prop('server_host');
	}

	function get_host_driver($oid)
	{
		$ob = obj($oid);
		return $ob->prop('server_driver');
	}
}
?>
