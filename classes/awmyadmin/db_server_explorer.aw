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

	@property conf type=objpicker clid=CL_DB_VIEW_CONF
	@caption Vali konfiguratsioon

*/

class db_server_explorer extends class_base
{
	function db_server_explorer()
	{
		$this->class_base();
		$this->init(array(
			'tpldir' => 'awmyadmin/db_server_explorer',
			'clid' => CL_DB_VIEW
		));
	}

	function submit($arr)
	{
		parent::submit($arr);
		return $this->mk_my_orb('change_conf', array('id' => $arr['id']));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function show($arr)
	{
		extract($arr);
		$this->read_template('frameset.tpl');
		$this->vars(array(
			'tree_url' => $this->mk_my_orb('tree', array('id' => $id)),
			'right_url' => $this->mk_my_orb('blank', array(),'menuedit')
		));
		die($this->parse());
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

	function tree($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		// build the tree of servers.
		$tree = get_instance('vcl/treeview');
		$tree->start_tree(array(
			'root_name' => 'Konfiguratsioon',
			'root_url' => $this->mk_my_orb('change_conf', array('id' => $arr['id'])),
			'root_icon' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
		));
		
		$db_inst = get_instance('awmyadmin/db_login');
		$cfg_inst = get_instance('awmyadmin/db_server_explorer_conf');
		$servers = $cfg_inst->get_servers($ob['meta']['conf']);
		$databases = $cfg_inst->get_databases_by_server($ob['meta']['conf']);
		foreach($servers as $serv_id => $server)
		{
			$tree->add_item(0,array(
				'id' => $serv_id,
				'name' => $server,
				'url' => $this->mk_my_orb('show_server', array('id' => $id, 'server_id' => $serv_id)),
				'icon' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
				'target' => 'se_right'
			));

			// add the databases for the server
			$ar = new aw_array($databases[$serv_id]);
			foreach($ar->get() as $dbid => $dbname)
			{
				$tree->add_item($serv_id,array(
					'id' => $dbid,
					'name' => $dbname,
					'url' => $this->mk_my_orb('show_database', array('id' => $id, 'server_id' => $serv_id, 'db_id' => $dbid)),
					'icon' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
					'target' => 'se_right'
				));

				// add all the tables for the datbase
				if ($db_inst->login_as($dbid))
				{
					$db_inst->db_list_tables();
					while ($tbl = $db_inst->db_next_table())
					{
						$tree->add_item($dbid,array(
							'id' => $dbid.$tbl,
							'name' => $tbl,
							'url' => $this->mk_my_orb('show_table', array('id' => $id, 'table' => $tbl, 'db_id' => $dbid,'server_id' => $serv_id)),
							'icon' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
							'target' => 'se_right'
						));
					}
				}
			}
		}

		return $tree->finalize_tree();
	}

	function show_server($arr)
	{
		extract($arr);
		$s_o = $this->get_object($server_id);
		$this->mk_path(array(
			'id' => $id,
			'server_name' => $s_o['name'],
			'server_id' => $server_id
		));

		$server = get_instance('awmyadmin/db_server_login');
		$server->login_as($server_id);

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'show_server',
		));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');
		$t->define_field(array(
			'name' => 'var',
			'caption' => 'Muutuja',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'val',
			'caption' => 'V&auml;&auml;rtus',
		));

		$stat = $server->db_server_status();
		foreach($stat as $k => $v)
		{
			$t->define_data(array(
				'var' => $k, 
				'val' => $v
			));
		}
		$t->sort_by(array('field' => 'var'));
		return $t->draw();
	}

	////
	// !makes path
	// parameters
	//   id - the id of the server_explorer object
	//   server_name 
	//   server_id
	function mk_path($arr, $sec = false)
	{
		if (!is_array($arr))
		{
			return parent::mk_path($arr,$sec);
		}

		extract($arr);
		$path = 'Serverid';
		if ($server_name != '' && $server_id)
		{
			$path .= " / <a href='".$this->mk_my_orb('show_server', array('id' => $id, 'server_id' => $server_id))."'>$server_name</a>";
			if ($db_name != '' && $db_id)
			{
				$path .= " / <a href='".$this->mk_my_orb('show_database', array('id' => $id, 'server_id' => $server_id, 'db_id' => $db_id))."'>$db_name</a>";

				if ($table != '')
				{
					$path .= " / <a href='".$this->mk_my_orb('show_table', array('id' => $id, 'server_id' => $server_id, 'db_id' => $db_id,'table' => $table))."'>$table</a>";
				}
			}
		}
		return parent::mk_path(0,$path);
	}

	function show_database($arr)
	{
		extract($arr);
		$s_o = $this->get_object($server_id);
		$db_o = $this->get_object($db_id);
		$this->mk_path(array(
			'id' => $id,
			'server_name' => $s_o['name'],
			'server_id' => $server_id,
			'db_name' => $db_o['name'],
			'db_id' => $db_id
		));

		$server = get_instance('awmyadmin/db_login');
		$server->login_as($db_id);

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'show_database',
		));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');
		
		$fields = false;
		$fields_tbl = '';
		$tbldat = array();
		$server->db_list_tables();
		while ($tbl = $server->db_next_table())
		{
			$server->save_handle();
			$tbldat[$tbl] = $server->db_get_table_info($tbl);
			if (!is_array($fields))
			{
				$fields = array_keys($tbldat[$tbl]);
				$fields_tbl = $tbl;
			}
			$server->restore_handle();
		}

		$field_h = '';
		$ar = new aw_array($fields);
		foreach($ar->get() as $fname)
		{
			$t->define_field(array(
				'name' => $fname,
				'caption' => $fname,
				'sortable' => 1,
				'numeric' => (is_number($tbldat[$fields_tbl][$fname]))
			));
		}

		foreach($tbldat as $tbl => $td)
		{
			$t->define_data($td);
		}

		$t->set_default_sortby('Name');
		$t->sort_by();
		return $t->draw();
	}

	function show_table($arr)
	{
		extract($arr);
		$s_o = $this->get_object($server_id);
		$db_o = $this->get_object($db_id);
		$this->mk_path(array(
			'id' => $id,
			'server_name' => $s_o['name'],
			'server_id' => $server_id,
			'db_name' => $db_o['name'],
			'db_id' => $db_id,
			'table' => $table,
		));


		$tbp = get_instance('vcl/tabpanel');

		$tbp->add_tab(array(
			'active' => ($type == 'admin' ? true : false),
			'caption' => 'Administreeri',
			'link' => $this->mk_my_orb('show_table', array('id' => $id, 'type' => 'admin'))
		));

		$tbp->add_tab(array(
			'active' => ($type == 'admin_indexes' ? true : false),
			'caption' => 'Administreeri indekseid',
			'link' => $this->mk_my_orb('show_table', array('id' => $id, 'type' => 'admin_indexes'))
		));

		$tbp->add_tab(array(
			'active' => ($type == 'content' ? true : false),
			'caption' => 'Sisu',
			'link' => $this->mk_my_orb('show_table', array('id' => $id, 'type' => 'content'))
		));

		$tbp->add_tab(array(
			'active' => ($type == 'query' ? true : false),
			'caption' => 'P&auml;ring',
			'link' => $this->mk_my_orb('show_table', array('id' => $id, 'type' => 'query'))
		));

		return $tbp->get_tabpanel(array('content' => 'hello'));
	}
}
?>