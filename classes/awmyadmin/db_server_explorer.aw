<?php

/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

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

		$arr['type'] = 'admin';
		$tbp->add_tab(array(
			'active' => (in_array($type,array('admin_col','admin')) ? true : false),
			'caption' => 'Administreeri',
			'link' => $this->mk_my_orb('show_table', $arr)
		));

		$arr['type'] = 'admin_indexes';
		$tbp->add_tab(array(
			'active' => (in_array($type,array('admin_indexes','admin_index')) ? true : false),
			'caption' => 'Administreeri indekseid',
			'link' => $this->mk_my_orb('show_table', $arr)
		));

		$arr['type'] = 'content';
		$tbp->add_tab(array(
			'active' => ($type == 'content' ? true : false),
			'caption' => 'Sisu',
			'link' => $this->mk_my_orb('show_table', $arr)
		));

		$arr['type'] = 'query';
		$tbp->add_tab(array(
			'active' => ($type == 'query' ? true : false),
			'caption' => 'P&auml;ring',
			'link' => $this->mk_my_orb('show_table', $arr)
		));
		unset($arr['type']);

		$content = '';
		switch($type)
		{
			case 'admin':
				$content = $this->_sht_do_admin($arr);
				break;

			case 'admin_col':
				$content = $this->_sht_do_admin_col($arr);
				break;

			case 'admin_indexes':
				$content = $this->_sht_do_admin_indexes($arr);
				break;

			case 'admin_index':
				$content = $this->_sht_do_admin_index($arr);
				break;

			case 'content':
				$content = $this->_sht_do_content($arr);
				break;

			case 'query':
				$content = $this->_sht_do_query($arr);
				break;
		}

		return $tbp->get_tabpanel(array('content' => $content));
	}

	function _sht_do_admin($arr)
	{
		extract($arr);
		$this->read_template('admin.tpl');

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'db_table_admin'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');
		$t->define_field(array('name' => 'name','caption' => 'Nimi','sortable' => 1));
		$t->define_field(array('name' => 'type','caption' => 'T&uuml;&uuml;p','sortable' => 1));
		$t->define_field(array('name' => 'flags','caption' => 'Attribuudid','sortable' => 1));
		$t->define_field(array('name' => 'null','caption' => 'NULL','sortable' => 1));
		$t->define_field(array('name' => 'default','caption' => 'Default','sortable' => 1));
		$t->define_field(array('name' => 'change','caption' => 'Muuda'));
		$t->define_field(array('name' => 'sel','caption' => 'Vali'));

		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);
		$tbl = $db->db_get_table($table);
		foreach($tbl['fields'] as $fid => $fdat)
		{
			$fdat['type'] .= '('.$fdat['length'].')';
			$tarr = $arr;
			$tarr['field'] = $fdat['name'];
			$fdat['change'] = html::href(array(
				'url' => $this->mk_my_orb('admin_col', $tarr),
				'caption' => 'Muuda'
			));
			$fdat['sel'] = html::checkbox(array(
					'name' => 'sel[]',
				'value' => $fdat['name']
			));

			$t->define_data($fdat);
		}
		$t->set_default_sortby('name');
		$t->sort_by();

		$tb = get_instance('toolbar');
		$tb->add_button(array(
			'name' => 'new',
			'tooltip' => 'Lisa',
			'url' => $this->mk_my_orb('admin_col', $arr),
			'imgover' => 'new_over.gif',
			'img' => 'new.gif'
		));
		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => 'Kustuta',
			'url' => 'javascript:del()',
			'imgover' => 'delete_over.gif',
			'img' => 'delete.gif'
		));

		$arr['is_del'] = '0';
		$this->vars(array(
			'table' => $t->draw(),
			'reforb' => $this->mk_reforb('submit_admin', $arr),
			'toolbar' => $tb->get_toolbar(),
		));
		return $this->parse();
	}

	function _sht_do_content($arr)
	{
		$dtc = get_instance('awmyadmin/db_sql_query');
		$nr = 0;
		return $dtc->show_query_results($arr['db_id'],'SELECT * FROM '.$arr['table'],$nr);
	}

	function _sht_do_query($arr)
	{
		$this->read_template('query.tpl');
		$tarr = $arr;
		$tarr['no_reforb'] = 1;
		unset($tarr['sql']);

		$dtc = get_instance('awmyadmin/db_sql_query');
		$nr = 0;
		if ($arr['sql'] != '')
		{
			$res = $dtc->show_query_results($arr['db_id'],$arr['sql'],$nr);
		}

		$tb = get_instance('toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));

		$this->vars(array(
			'sql' => $arr['sql'],
			'reforb' => $this->mk_reforb('show_table', $tarr),
			'results' => $res,
			'toolbar' => $tb->get_toolbar()
		));

		return $this->parse();
	}

	function _sht_do_admin_col($arr)
	{
		extract($arr);
		$this->read_template('admin_col.tpl');

		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);

		$tbl = $db->db_get_table($table);

		$tb = get_instance('toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));
		unset($arr['type']);
		$arr['field'] = $field;
		$this->vars(array(
			'name' => $field,
			'type' => $this->picker(strtoupper($tbl['fields'][$field]['type']),$db->db_list_field_types()),
			'length' => $tbl['fields'][$field]['length'],
			'null' => checked($tbl['fields'][$field]['null']),
			'default' => $tbl['fields'][$field]['default'],
			'extra' => $this->picker(strtoupper($tbl['fields'][$field]['flags']), $db->db_list_flags()),
			'toolbar' => $tb->get_toolbar(),
			'reforb' => $this->mk_reforb('submit_admin_col', $arr)
		));
		return $this->parse();
	}

	function submit_admin_col($arr)
	{
		extract($arr);
		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);
		if ($field == '')
		{
			// add
			$db->db_add_col($table, array(
				'name' => $name,
				'type' => $type,
				'length' => $length,
				'null' => ($null ? 'NULL' : 'NOT NULL'),
				'default' => $default,
				'extra' => $extra
			));
			$field = $name;
		}
		else
		{
			// change
			$db->db_change_col($table, $field, array(
				'name' => $name,
				'type' => $type,
				'length' => $length,
				'null' => ($null ? 'NULL' : 'NOT NULL'),
				'default' => $default,
				'extra' => $extra
			));
		}
		return $this->mk_my_orb('admin_col', array(
			'field' => $field,
			'id' => $id,
			'table' => $table,
			'db_id' => $db_id,
			'server_id' => $server_id,
			'sql' => $sql
		));
	}

	function submit_admin($arr)
	{
		extract($arr);

		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);

		if ($is_del)
		{
			$sel = new aw_array($sel);
			foreach($sel->get() as $secol)
			{
				$db->db_drop_col($table,$secol);
			}
		}
		return $this->mk_my_orb('show_table', array(
			'id' => $id,
			'table' => $table,
			'db_id' => $db_id,
			'server_id' => $server_id,
			'sql' => $sql
		));
	}

	function _sht_do_admin_indexes($arr)
	{
		extract($arr);
		$this->read_template('admin_indexes.tpl');

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'db_table_admin'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');
		$t->define_field(array('name' => 'index_name','caption' => 'Nimi','sortable' => 1));
		$t->define_field(array('name' => 'col_name','caption' => 'Tulba nimi','sortable' => 1));
		$t->define_field(array('name' => 'unique','caption' => 'Unikaalne','sortable' => 1));
		$t->define_field(array('name' => 'change','caption' => 'Muuda'));
		$t->define_field(array('name' => 'sel','caption' => 'Vali'));

		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);
		$db->db_list_indexes($table);
		while ($idx = $db->db_next_index())
		{
			$tar = $arr;
			$tar['index'] = $idx['index_name'];
			$idx['change'] = html::href(array(
				'url' => $this->mk_my_orb('admin_index', $tar),
				'caption' => 'Muuda'
			));
			$idx['sel'] = html::checkbox(array(
				'name' => 'sel[]',
				'value' => $idx['index_name']
			));

			$t->define_data($idx);
		}
		$t->set_default_sortby('index_name');
		$t->sort_by();

		$tb = get_instance('toolbar');
		$tb->add_button(array(
			'name' => 'new',
			'tooltip' => 'Lisa',
			'url' => $this->mk_my_orb('admin_index', $arr),
			'imgover' => 'new_over.gif',
			'img' => 'new.gif'
		));
		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => 'Kustuta',
			'url' => 'javascript:del()',
			'imgover' => 'delete_over.gif',
			'img' => 'delete.gif'
		));

		$arr['is_del'] = '0';
		$this->vars(array(
			'table' => $t->draw(),
			'toolbar' => $tb->get_toolbar(),
			'reforb' => $this->mk_reforb('submit_admin_indexes', $arr)
		));
		return $this->parse();
	}

	function _sht_do_admin_index($arr)
	{
		extract($arr);
		$this->read_template('admin_index.tpl');

		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);

		$db->db_list_indexes($table);
		while($idx = $db->db_next_index())
		{
			if ($idx['index_name'] == $index)
			{
				break;
			}
		}

		$tbl = $db->db_get_table($table);
		$fields = $this->make_keys(array_keys($tbl['fields']));

		$tb = get_instance('toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));
		$arr['index'] = $index;
		$this->vars(array(
			'name' => $idx['index_name'],
			'fields' => $this->picker($idx['col_name'], $fields),
			'toolbar' => $tb->get_toolbar(),
			'reforb' => $this->mk_reforb('submit_admin_index', $arr)
		));
		return $this->parse();
	}

	function submit_admin_indexes($arr)
	{
		extract($arr);
	
		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);

		if ($is_del)
		{
			$ar = new aw_array($sel);
			foreach($ar->get() as $idxname)
			{
				$db->db_drop_index($table, $idxname);
			}
		}
		return $this->mk_my_orb('admin_indexes', array(
			'id' => $id,
			'table' => $table,
			'db_id' => $db_id,
			'server_id' => $server_id,
			'sql' => $sql
		));
	}

	function submit_admin_index($arr)
	{
		extract($arr);

		$ob = $this->get_object($id);
		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_id);

		if ($index != "")
		{
			// change = drop && add
			$db->db_drop_index($table, $index);
		}

		// add
		$db->db_add_index($table, array(
			'name' => $name,
			'col' => $field
		));
		$index = $name;

		return $this->mk_my_orb("admin_index", array(
			'id' => $id,
			'table' => $table,
			'db_id' => $db_id,
			'server_id' => $server_id,
			'sql' => $sql,
			'index' => $index
		));
	}
}
?>