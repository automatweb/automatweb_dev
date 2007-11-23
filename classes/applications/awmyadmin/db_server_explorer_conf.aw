<?php

/*
	@classinfo syslog_type=ST_DB_SERVER_EXPLORER_CONF relationmgr=yes no_status=1 no_comment=1 mantainer=kristo

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property all_servers type=checkbox ch_value=1
	@caption K&otilde;ik serverid

	@property servers type=relpicker reltype=RELTYPE_SERVER automatic=1 multiple=1 size=5
	@caption Serverid, millest baase n&auml;idatakse

	@property all_databases type=checkbox ch_value=1
	@caption K&otilde;ik andmebaasid

	@property databases type=relpicker reltype=RELTYPE_DBLOGIN automatic=1 multiple=1 size=10
	@caption Andmebaasid, mida n&auml;idatakse

@reltype SERVER value=1 clid=CL_DB_SERVER_LOGIN
@caption andmebaasi serveri login

@reltype DBLOGIN value=1 clid=CL_DB_LOGIN
@caption andmebaasi login

*/

class db_server_explorer_conf extends class_base
{
	function db_server_explorer_conf()
	{
		$this->init(array(
			'tpldir' => 'awmyadmin/db_server_explorer_conf',
			'clid' => CL_DB_VIEW_CONF
		));
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		switch($data['name'])
		{
		}
		return PROP_OK;
	}

	////
	// !returns a list of all servers for this cfg object
	function get_servers($oid)
	{
		$ret = array();
		$ob = obj($oid);
		$ol = new object_list(array(
			"class_id" => CL_DB_SERVER_LOGIN
		));
		$ret = $ol->names();

		if (!$ob->prop('all_servers'))
		{
			$svs = $ob->prop('servers');
			foreach($ret as $seid => $name)
			{
				if (!$svs[$seid])
				{
					unset($ret[$seid]);
				}
			}
		}
		return $ret;
	}

	////
	// !returns an array of databases, grouped by server
	function get_databases_by_server($oid)
	{
		$ret = array();
		$ob = obj($oid);

		$servers = $this->get_servers($oid);
		$ol = new object_list(array(
			"class_id" => CL_DB_LOGIN
		));
		$databases = $ol->names();
		foreach($servers as $seid => $sename)
		{
			foreach($databases as $dbid => $dbn)
			{
				$dbo = obj($dbid);
				if ($dbo->prop('db_server') == $seid)
				{
					$ret[$seid][$dbid] = $dbn;
				}
			}
		}
		return $ret;
	}
}
?>
