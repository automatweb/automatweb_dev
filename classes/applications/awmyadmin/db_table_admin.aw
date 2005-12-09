<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/awmyadmin/db_table_admin.aw,v 1.5 2005/12/09 07:54:49 kristo Exp $

/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property db_base type=objpicker clid=CL_DB_LOGIN
	@caption Vali andmebaas

	@property db_table type=select 
	@caption Vali tabel

	@property adminlink type=text
	@caption 

*/

class db_table_admin extends class_base
{
	function db_table_admin()
	{
		$this->init(array(
			'tpldir' => 'awmyadmin/db_table_admin',
			'clid' => CL_DB_TABLE_ADMIN
		));
	}

	function get_property($args)
	{
		switch($args['prop']['name'])
		{
			case 'db_table':
				$tbls = array(-1 => 'Lisa uus');
				$base = get_instance(CL_DB_LOGIN);
				if ($base->login_as($args['obj']['meta']['db_base']))
				{
					$base->db_list_tables();
					while ($tbl = $base->db_next_table())
					{
						$tbls[$tbl] = $tbl;
					}
				}
				$args['prop']['options'] = $tbls;
				break;
			
			case 'adminlink':
				$args['prop']['value'] = html::href(array(
					'url' => $this->mk_my_orb('admin', array('id' => $args['obj']['oid'])),
					'caption' => 'Administreeri'
				));
				break;
		}
		return PROP_OK;
	}

	/**  
		
		@attrib name=admin params=name 
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function admin($arr)
	{
		extract($arr);
		$ob = $this->_change_init($arr, 'Tabeli admin', 'admin.tpl');
		$this->mk_path($ob['parent'], html::href(array(
				'url' => $this->mk_my_orb('change', array('id' => $id)),
				'caption' => 'Muuda'
			)).' / '.html::href(array(
				'url' => $this->mk_my_orb('admin', array('id' => $id)),
				'caption' => 'Administreeri'
			))
		);

		return $this->do_table_admin(array(
			'db_base' => $ob['meta']['db_base'],
			'db_table' => $ob['meta']['db_table'],
			'that' => &$this,
			'id' => $id
		));
	}

	/**  
		
		@attrib name=admin_col params=name 
		
		@param id required
		@param field optional
		
		@returns
		
		
		@comment

	**/
	function admin_col($arr)
	{
		extract($arr);
		$this->read_template('admin_col.tpl');
		$ob = obj($id);
		$this->mk_path($ob->parent(), html::href(array(
				'url' => $this->mk_my_orb('change', array('id' => $id)),
				'caption' => 'Muuda'
			)).' / '.html::href(array(
				'url' => $this->mk_my_orb('admin', array('id' => $id)),
				'caption' => 'Administreeri'
			)).' / '.html::href(array(
				'url' => $this->mk_my_orb('admin_col', array('id' => $id,'field' => $field)),
				'caption' => ($field == '' ? 'Lisa tulp' : "Muuda tulpa $field")
			))
		);

		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($ob->meta('db_base'));

		$tbl = $db->db_get_table($ob->meta('db_table'));

		$tb = get_instance('vcl/toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => t('Salvesta'),
			'url' => 'javascript:document.add.submit()',
			'img' => 'save.gif'
		));
		$this->vars(array(
			'name' => $field,
			'type' => $this->picker(strtoupper($tbl['fields'][$field]['type']),$db->db_list_field_types()),
			'length' => $tbl['fields'][$field]['length'],
			'null' => checked($tbl['fields'][$field]['null']),
			'default' => $tbl['fields'][$field]['default'],
			'extra' => $this->picker(strtoupper($tbl['fields'][$field]['flags']), $db->db_list_flags()),
			'toolbar' => $tb->get_toolbar(),
			'reforb' => $this->mk_reforb('submit_admin_col', array('id' => $id, 'field' => $field))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_admin params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit_admin($arr)
	{
		extract($arr);

		$ob = obj($id);
		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($ob->meta('db_base'));

		if ($is_del)
		{
			$sel = new aw_array($sel);
			foreach($sel->get() as $secol)
			{
				$db->db_drop_col($ob->meta('db_table'),$secol);
			}
		}
		return $this->mk_my_orb('admin', array('id' => $id));
	}

	/**  
		
		@attrib name=submit_admin_col params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit_admin_col($arr)
	{
		extract($arr);
		$ob = obj($id);
		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($ob->meta('db_base'));
		if ($field == '')
		{
			// add
			$db->db_add_col($ob->meta('db_table'), array(
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
			$db->db_change_col($ob->meta('db_table'), $field, array(
				'name' => $name,
				'type' => $type,
				'length' => $length,
				'null' => ($null ? 'NULL' : 'NOT NULL'),
				'default' => $default,
				'extra' => $extra
			));
		}
		return $this->mk_my_orb('admin_col', array('id' => $id, 'field' => $field));
	}

	/**  
		
		@attrib name=admin_indexes params=name 
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function admin_indexes($arr)
	{
		extract($arr);
		$this->read_template('admin_indexes.tpl');
		$ob = obj($id);
		$this->mk_path($ob->parent(), html::href(array(
				'url' => $this->mk_my_orb('change', array('id' => $id)),
				'caption' => 'Muuda'
			)).' / '.html::href(array(
				'url' => $this->mk_my_orb('admin', array('id' => $id)),
				'caption' => 'Administreeri indekseid'
			))
		);


		load_vcl('table');
		$t = new aw_table(array('prefix' => 'db_table_admin'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');
		$t->define_field(array('name' => 'index_name','caption' => 'Nimi','sortable' => 1));
		$t->define_field(array('name' => 'col_name','caption' => 'Tulba nimi','sortable' => 1));
		$t->define_field(array('name' => 'unique','caption' => 'Unikaalne','sortable' => 1));
		$t->define_field(array('name' => 'change','caption' => 'Muuda'));
		$t->define_field(array('name' => 'sel','caption' => 'Vali'));

		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($ob->meta('db_base'));
		$db->db_list_indexes($ob->meta('db_table'));
		while ($idx = $db->db_next_index())
		{
			$idx['change'] = html::href(array(
				'url' => $this->mk_my_orb('admin_index', array('id' => $id, 'index' => $idx['index_name'])),
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
			'url' => $this->mk_my_orb('admin_index', array('id' => $id)),
			'img' => 'new.gif'
		));
		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => 'Kustuta',
			'url' => 'javascript:del()',
			'img' => 'delete.gif'
		));

		$tbp = get_instance('vcl/tabpanel');
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Tulbad',
			'link' => $this->mk_my_orb('admin', array('id' => $id)),
		));
		$tbp->add_tab(array(
			'active' => true,
			'caption' => 'Indeksid',
			'link' => $this->mk_my_orb('admin_indexes', array('id' => $id))
		));
		$this->vars(array(
			'table' => $t->draw(),
			'toolbar' => $tb->get_toolbar(),
			'reforb' => $this->mk_reforb('submit_admin_indexes', array('id' => $id, 'is_del' => '0'))
		));
		$this->vars(array(
			'tabs' => $tbp->get_tabpanel(array('content' => $this->parse('TBC'))),
			'TBC' => ''
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
	
		$ob = obj($id);
		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($ob->meta('db_base'));

		if ($is_del)
		{
			$ar = new aw_array($sel);
			foreach($ar->get() as $idxname)
			{
				$db->db_drop_index($ob->meta('db_table'), $idxname);
			}
		}
		return $this->mk_my_orb('admin_indexes', array('id' => $id));
	}

	/**  
		
		@attrib name=admin_index params=name 
		
		@param id required
		@param index optional
		
		@returns
		
		
		@comment

	**/
	function admin_index($arr)
	{
		extract($arr);
		$this->read_template('admin_index.tpl');
		$ob = obj($id);
		$this->mk_path($ob->parent(), html::href(array(
				'url' => $this->mk_my_orb('change', array('id' => $id)),
				'caption' => 'Muuda'
			)).' / '.html::href(array(
				'url' => $this->mk_my_orb('admin_indexes', array('id' => $id)),
				'caption' => 'Administreeri indekseid'
			)).' / '.html::href(array(
				'url' => $this->mk_my_orb('admin_index', array('id' => $id,'index' => $index)),
				'caption' => ($index == '' ? 'Lisa indeks' : "Muuda indeksit $index")
			))
		);

		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($ob->meta('db_base'));

		$db->db_list_indexes($ob->meta('db_table'));
		while($idx = $db->db_next_index())
		{
			if ($idx['index_name'] == $index)
			{
				break;
			}
		}

		$tbl = $db->db_get_table($ob->meta('db_table'));
		$fields = $this->make_keys(array_keys($tbl['fields']));

		$tb = get_instance('vcl/toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => t('Salvesta'),
			'url' => 'javascript:document.add.submit()',
			'img' => 'save.gif'
		));
		$this->vars(array(
			'name' => $idx['index_name'],
			'fields' => $this->picker($idx['col_name'], $fields),
			'toolbar' => $tb->get_toolbar(),
			'reforb' => $this->mk_reforb('submit_admin_index', array('id' => $id, 'index' => $index))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_admin_index params=name 
		
		
		@returns
		
		
		@comment

	**/
	function submit_admin_index($arr)
	{
		extract($arr);

		$ob = obj($id);
		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($ob->meta('db_base'));

		if ($index != "")
		{
			// change = drop && add
			$db->db_drop_index($ob->meta('db_table'), $index);
		}

		// add
		$db->db_add_index($ob->meta('db_table'), array(
			'name' => $name,
			'col' => $field
		));
		$index = $name;

		return $this->mk_my_orb("admin_index", array("id" => $id, "index" => $index));
	}

	function do_table_admin($arr)
	{
		extract($arr);
		if ($tpl)
		{
			$this->read_template($tpl);
		}

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

		$db = get_instance(CL_DB_LOGIN);
		$db->login_as($db_base);
		$tbl = $db->db_get_table($db_table);
		foreach($tbl['fields'] as $fid => $fdat)
		{
			$fdat['type'] .= '('.$fdat['length'].')';
			$fdat['change'] = html::href(array(
				'url' => $that->mk_my_orb('admin_col', array('id' => $id, 'field' => $fdat['name'])),
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
			'url' => $that->mk_my_orb('admin_col', array('id' => $id)),
			'img' => 'new.gif'
		));
		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => 'Kustuta',
			'url' => 'javascript:del()',
			'img' => 'delete.gif'
		));

		$tbp = get_instance('vcl/tabpanel');
		$tbp->add_tab(array(
			'active' => true,
			'caption' => 'Tulbad',
			'link' => $this->REQUEST_URI
		));
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Indeksid',
			'link' => $that->mk_my_orb('admin_indexes', array('id' => $id))
		));
		$this->vars(array(
			'table' => $t->draw(),
			'reforb' => $that->mk_reforb('submit_admin', array('id' => $id, 'is_del' => '0')),
			'toolbar' => $tb->get_toolbar(),
		));
		$this->vars(array(
			'tabs' => $tbp->get_tabpanel(array('content' => $this->parse('TBC'))),
			'TBC' => ''
		));
		return $this->parse();
	}
}
?>
