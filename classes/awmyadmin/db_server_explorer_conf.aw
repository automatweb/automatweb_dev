<?php

/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property name type=textbox field=name group=general 
	@caption Nimi

	@property status type=status 
	@caption Staatus

	@property all_servers type=checkbox
	@caption K&otilde;ik serverid

	@property servers type=select multiple=1 size=5
	@caption Serverid, millest baase n&auml;idatakse

	@property all_databases type=checkbox
	@caption K&otilde;ik andmebaasid

	@property databases type=select multiple=1 size=10
	@caption Andmebaasid, mida n&auml;idatakse


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

	function get_property($args)
	{
		$data = &$args['prop'];
		switch($data['name'])
		{
			case 'servers':
				$data['options'] = $this->list_objects(array('class' => CL_DB_SERVER_LOGIN));
				break;

			case 'databases':
				$data['options'] = $this->list_objects(array('class' => CL_DB_LOGIN));
				break;
		}
		return PROP_OK;
	}

	////
	// !returns a list of all servers for this cfg object
	function get_servers($oid)
	{
		$ret = array();
		$ob = $this->get_object($oid);
		$ret = $this->list_objects(array('class' => CL_DB_SERVER_LOGIN));
		if (!$ob['meta']['all_servers'])
		{
			foreach($ret as $seid => $name)
			{
				if (!$ob['meta']['servers'][$seid])
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
		$ob = $this->get_object($oid);

		$servers = $this->get_servers($oid);
		$databases = $this->list_objects(array('class' => CL_DB_LOGIN, 'return' => ARR_ALL));

		foreach($servers as $seid => $sename)
		{
			foreach($databases as $dbid => $dbrow)
			{
				$dbmeta = $this->get_object_metadata(array(
					'metadata' => $dbrow['metadata']
				));
				if ($dbmeta['db_server'] == $seid)
				{
					$ret[$seid][$dbid] = $dbrow['name'];
				}
			}
		}
		return $ret;
	}
}
?>