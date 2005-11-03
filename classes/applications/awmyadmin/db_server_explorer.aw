<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/awmyadmin/db_server_explorer.aw,v 1.4 2005/11/03 13:24:49 duke Exp $

/*
	@classinfo syslog_type=ST_DB_SERVER_EXPLORER relationmgr=yes no_status=1 no_comment=1

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property conf type=relpicker reltype=RELTYPE_CONF automatic=1
	@caption Vali konfiguratsioon

	@groupinfo explorer caption="Explorer"
	@default group=explorer

	@layout fs type=hbox group=explorer

	@property tree type=text parent=fs group=explorer store=no no_caption=1
	@property content type=text parent=fs group=explorer store=no no_caption=1
	

	@reltype CONF value=1 clid=CL_DB_VIEW_CONF
	@caption konfiguratsioon
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

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "tree":
				$prop["value"] = $this->do_tree($arr);
				break;

			case "content":
				$prop["value"] = $this->do_content($arr);
				break;
		}

		return PROP_OK;
	}

	function do_tree($arr)
	{
		$ob = $arr["obj_inst"];

		// build the tree of servers.
		$tree = get_instance('vcl/treeview');
		$tree->start_tree(array(
			'root_name' => 'Konfiguratsioon',
			'root_url' => $this->mk_my_orb('change', array('id' => $arr['id'])),
			'root_icon' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
			"type" => TREE_DHTML,
			"persist_state" => true,
			"tree_id" => "dbview"
		));
		
		$db_inst = get_instance(CL_DB_LOGIN);
		$cfg_inst = get_instance(CL_DB_VIEW_CONF);
		$servers = $cfg_inst->get_servers($ob->prop('conf'));
		$databases = $cfg_inst->get_databases_by_server($ob->prop('conf'));

		foreach($servers as $serv_id => $server)
		{
			$tree->add_item(0,array(
				'id' => $serv_id,
				'name' => $server,
				'url' => aw_url_change_var('server_id',$serv_id),
				'icon' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
			));

			// add the databases for the server
			$ar = new aw_array($databases[$serv_id]);
			foreach($ar->get() as $dbid => $dbname)
			{
				$tree->add_item($serv_id,array(
					'id' => $dbid,
					'name' => $dbname,
					'url' => aw_url_change_var("server_id", $serv_id, aw_url_change_var('db_id',$dbid)),
					'icon' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
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
							'url' => aw_url_change_var('table', $tbl, aw_url_change_var('db_id',$dbid,aw_url_change_var('server_id',$serv_id))),
							'icon_url' => $this->cfg['baseurl'].'/automatweb/images/icon_aw.gif',
						));
					}
				}
			}
		}

		return $tree->finalize_tree();
	}


	function do_content($arr)
	{
		if ($arr["request"]["table"])
		{
			return $this->show_table($arr);
		}
		else
		if ($arr["request"]["db_id"])
		{
			return $this->show_database($arr);
		}
		if ($arr["request"]["server_id"])
		{
			return $this->show_server($arr);
		}
	}

	function show_server($arr)
	{
		$server_id = $arr["request"]["server_id"];
		$s_o = obj($server_id);

		$server = get_instance(CL_DB_SERVER_LOGIN);
		$server->login_as($server_id);

		load_vcl('table');
		$t = new aw_table(array('layout' => 'generic'));
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

	function show_database($arr)
	{
		$server_id = $arr["request"]["server_id"];
		$db_id = $arr["request"]["db_id"];

		$s_o = obj($server_id);
		$db_o = obj($db_id);

		$server = get_instance(CL_DB_LOGIN);
		$server->login_as($db_id);

		load_vcl('table');
		$t = new aw_table(array(
			'layout' => 'generic',
		));
		
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
				'numeric' => (is_numeric($tbldat[$fields_tbl][$fname]))
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
		$server_id = $arr["request"]["server_id"];
		$db_id = $arr["request"]["db_id"];
		$table = $arr["request"]["table"];

		$s_o = obj($server_id);
		$db_o = obj($db_id);


		$tbp = get_instance('vcl/tabpanel');

		$tmp = $arr["request"];
		$type = $tmp["type"];
		$tmp['type'] = 'admin';
		$tbp->add_tab(array(
			'active' => (in_array($type,array('admin_col','admin')) ? true : false),
			'caption' => 'Administreeri',
			'link' => $this->mk_my_orb('show_table', $tmp)
		));

		$tmp['type'] = 'admin_indexes';
		$tbp->add_tab(array(
			'active' => (in_array($type,array('admin_indexes','admin_index')) ? true : false),
			'caption' => 'Administreeri indekseid',
			'link' => $this->mk_my_orb('show_table', $tmp)
		));

		$tmp['type'] = 'content';
		$tbp->add_tab(array(
			'active' => ($type == 'content' ? true : false),
			'caption' => 'Sisu',
			'link' => $this->mk_my_orb('show_table', $tmp)
		));

		$tmp['type'] = 'query';
		$tbp->add_tab(array(
			'active' => ($type == 'query' ? true : false),
			'caption' => 'P&auml;ring',
			'link' => $this->mk_my_orb('show_table', $tmp)
		));
		unset($arr['type']);

		$content = '';
		switch($type)
		{
			case 'admin_col':
				$content = $this->_sht_do_admin_col($arr["request"]);
				break;

			case 'admin_indexes':
				$content = $this->_sht_do_admin_indexes($arr["request"]);
				break;

			case 'admin_index':
				$content = $this->_sht_do_admin_index($arr["request"]);
				break;

			case 'content':
				$content = $this->_sht_do_content($arr["request"]);
				break;

			case 'query':
				$content = $this->_sht_do_query($arr["request"]);
				break;

			case 'admin':
			default:
				$content = $this->_sht_do_admin($arr["request"]);
				break;
		}

		return $tbp->get_tabpanel(array('content' => $content));
	}

	function _sht_do_admin($arr)
	{
		extract($arr);
		$this->read_template('admin.tpl');

		load_vcl('table');
		$t = new aw_table(array(
			'prefix' => 'db_table_admin', 
			"layout" => "generic"
		));
		$t->define_field(array('name' => 'name','caption' => 'Nimi','sortable' => 1));
		$t->define_field(array('name' => 'type','caption' => 'T&uuml;&uuml;p','sortable' => 1));
		$t->define_field(array('name' => 'flags','caption' => 'Attribuudid','sortable' => 1));
		$t->define_field(array('name' => 'null','caption' => 'NULL','sortable' => 1));
		$t->define_field(array('name' => 'default','caption' => 'Default','sortable' => 1));
		$t->define_field(array('name' => 'change','caption' => 'Muuda'));
		$t->define_field(array('name' => 'sel','caption' => 'Vali'));

		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($db_id);
		$tbl = $db->db_get_table($table);
		foreach($tbl['fields'] as $fid => $fdat)
		{
			$fdat['type'] .= '('.$fdat['length'].')';
			$tarr = $arr;
			$tarr["type"] = "admin_col";
			$tarr['field'] = $fdat['name'];
			$fdat['change'] = html::href(array(
				'url' => $this->mk_my_orb('change', $tarr),
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

		$tb = get_instance('vcl/toolbar');
		$tb->add_button(array(
			'name' => 'new',
			'tooltip' => 'Lisa',
			'url' => $this->mk_my_orb('change', $arr),
			'img' => 'new.gif'
		));
		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => 'Kustuta',
			'url' => 'javascript:del()',
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
		$dtc = get_instance(CL_DB_SQL_QUERY);
		$nr = 0;
		return $dtc->show_query_results($arr['db_id'],'SELECT * FROM '.$arr['table'],$nr);
	}

	function _sht_do_query($arr)
	{
		$this->read_template('query.tpl');
		$tarr = $arr;
		$tarr['no_reforb'] = 1;
		unset($tarr['sql']);

		$dtc = get_instance(CL_DB_SQL_QUERY);
		$nr = 0;
		if ($arr['sql'] != '')
		{
			$res = $dtc->show_query_results($arr['db_id'],$arr['sql'],$nr);
		}

		$tb = get_instance('vcl/toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
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

		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($db_id);

		$tbl = $db->db_get_table($table);

		$tb = get_instance('vcl/toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.changeform.submit()',
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

	/**  
		
		@attrib name=submit_admin_col params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit_admin_col($arr)
	{
		extract($arr);
		$db = get_instance(CL_DB_LOGIN);
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
		return $this->mk_my_orb('change', array(
			"group" => "explorer",
			'field' => $field,
			'id' => $id,
			'table' => $table,
			'db_id' => $db_id,
			'server_id' => $server_id,
			'sql' => $sql
		));
	}

	/**  
		
		@attrib name=submit_admin params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit_admin($arr)
	{
		extract($arr);

		$db = get_instance(CL_DB_LOGIN);
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
		$t = new aw_table(array('prefix' => 'db_table_admin', "layout" => "generic"));
		$t->define_field(array('name' => 'index_name','caption' => 'Nimi','sortable' => 1));
		$t->define_field(array('name' => 'col_name','caption' => 'Tulba nimi','sortable' => 1));
		$t->define_field(array('name' => 'unique','caption' => 'Unikaalne','sortable' => 1));
		$t->define_field(array('name' => 'change','caption' => 'Muuda'));
		$t->define_field(array('name' => 'sel','caption' => 'Vali'));

		$db = get_instance(CL_DB_LOGIN);
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

		$tb = get_instance('vcl/toolbar');
		$tb->add_button(array(
			'name' => 'new',
			'tooltip' => 'Lisa',
			'url' => $this->mk_my_orb('admin_index', $arr),
			'img' => 'new.gif'
		));
		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => 'Kustuta',
			'url' => 'javascript:del()',
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

		$db = get_instance(CL_DB_LOGIN);
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

		$tb = get_instance('vcl/toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
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

	/**  
		
		@attrib name=submit_admin_indexes params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit_admin_indexes($arr)
	{
		extract($arr);
	
		$db = get_instance(CL_DB_LOGIN);
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

	/**  
		
		@attrib name=submit_admin_index params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit_admin_index($arr)
	{
		extract($arr);

		$db = get_instance(CL_DB_LOGIN);
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
