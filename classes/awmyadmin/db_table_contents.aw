<?php

/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property status type=status group=general 
	@caption Staatus

	@property db_base type=objpicker clid=CL_DB_LOGIN 
	@caption Andmebaas

	@property db_table type=select 
	@caption Tabel

	@property per_page type=textbox size=5
	@caption Mitu rida lehel

*/

class db_table_contents extends class_base
{
	var $numeric_types = array('int','tinyint','smallint','mediumint','bigint','float','double');

	function db_table_contents()
	{
		$this->class_base();
		$this->init(array(
			'tpldir' => 'awmyadmin/db_table_contents',
			'clid' => CL_DB_TABLE_CONTENTS
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
		switch($args['prop']['name'])
		{
			case 'db_table':
				$tbls = array();
				$base = get_instance('awmyadmin/db_login');
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
		}
		return PROP_OK;
	}

	function change($arr)
	{
		extract($arr);
		$tbp = get_instance('vcl/tabpanel');
		$tbp->add_tab(array(
			'active' => true,
			'caption' => 'Konfigureeri',
			'link' => $this->mk_my_orb('change', array('id' => $id))
		));
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Sisu',
			'link' => $this->mk_my_orb('content', array('id' => $id))
		));
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Muuda sisu',
			'link' => $this->mk_my_orb('admin_content', array('id' => $id))
		));
		return $tbp->get_tabpanel(array('content' => parent::change($arr)));
	}

	function content($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$this->mk_path($ob['parent'], 'Tabeli sisu');
		$tbp = get_instance('vcl/tabpanel');
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Konfigureeri',
			'link' => $this->mk_my_orb('change', array('id' => $id))
		));
		$tbp->add_tab(array(
			'active' => true,
			'caption' => 'Sisu',
			'link' => $this->mk_my_orb('content', array('id' => $id))
		));
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Muuda sisu',
			'link' => $this->mk_my_orb('admin_content', array('id' => $id))
		));
		load_vcl("table");
		$t = new aw_table(array("prefix" => "db_table_content"));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$db = get_instance('awmyadmin/db_login');
		$db->login_as($ob['meta']['db_base']);

		$tbl = $db->db_get_table($ob['meta']['db_table']);

		foreach($tbl['fields'] as $fn => $fd)
		{
			$t->define_field(array(
				'name' => $fn,
				'caption' => $fn,
				'sortable' => 1,
				'numeric' => (in_array(strtolower($fd['type']),$this->numeric_types) ? true : false)
			));
		}

		$num_rows = $db->db_fetch_field('SELECT count(*) AS cnt FROM '.$ob['meta']['db_table'],'cnt');
		$per_page = $ob['meta']['per_page'];
		$p_tbp = $this->get_pager_tabpanel($ob, $num_rows, $page, $per_page);

		$db->db_query('SELECT * FROM '.$ob['meta']['db_table'].' LIMIT '.($page*$per_page).','.((int)$per_page));
		while ($row = $db->db_next())
		{
			$t->define_data($row);
		}
		$t->sort_by();

		$this->read_template("contents.tpl");
		$this->vars(array(
			'table' => $t->draw(),
		));
		$this->vars(array(
			'TBL' => '',
			'pages' => $p_tbp->get_tabpanel(array('content' => $this->parse('TBL')))
		));
		return $tbp->get_tabpanel(array('content' => $this->parse()));
	}

	function admin_content($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$this->mk_path($ob['parent'], 'Tabeli sisu');
		$tbp = get_instance('vcl/tabpanel');
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Konfigureeri',
			'link' => $this->mk_my_orb('change', array('id' => $id))
		));
		$tbp->add_tab(array(
			'active' => false,
			'caption' => 'Sisu',
			'link' => $this->mk_my_orb('content', array('id' => $id))
		));
		$tbp->add_tab(array(
			'active' => true,
			'caption' => 'Muuda sisu',
			'link' => $this->mk_my_orb('admin_content', array('id' => $id))
		));

		load_vcl("table");
		$t = new aw_table(array("prefix" => "db_table_content"));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$db = get_instance('awmyadmin/db_login');
		$db->login_as($ob['meta']['db_base']);

		$tbl = $db->db_get_table($ob['meta']['db_table']);

		foreach($tbl['fields'] as $fn => $fd)
		{
			$t->define_field(array(
				'name' => $fn,
				'caption' => $fn,
				'sortable' => 1,
				'numeric' => (in_array(strtolower($fd['type']),$this->numeric_types) ? true : false)
			));
		}

		$t->define_field(array(
			'name' => 'sel',
			'caption' => html::href(array(
				'caption' => 'Vali',
				'url' => 'javascript:selall()'
			))
		));

		$t->define_header('Muuda olemasolevaid ridu');

		$num_rows = $db->db_fetch_field('SELECT count(*) AS cnt FROM '.$ob['meta']['db_table'],'cnt');
		$per_page = $ob['meta']['per_page'];
		$p_tbp = $this->get_pager_tabpanel($ob, $num_rows, $page, $per_page);

		$keys = array();
		$db->db_query('SELECT * FROM '.$ob['meta']['db_table'].' LIMIT '.($page*$per_page).','.((int)$per_page));
		while ($row = $db->db_next())
		{
			// put together the where part for this row for the update
			$wherepts = array();
			// now make all cells into textboxes
			foreach($tbl['fields'] as $fn => $fd)
			{
				$wherepts[] = "$fn = '".$row[$fn]."'";
				$row[$fn] = html::textbox(array(
					'name' => 'values['.$row['rec'].']['.$fn.']',
					'value' => $row[$fn],
					'size' => min($fd['length'],50)
				));
			}
			$row['sel'] = html::checkbox(array(
				'name' => 'sel[]',
				'value' => $row['rec'],
			));
			$t->define_data($row);
			$keys[$row['rec']] = join(" AND ", $wherepts);
		}
		$t->sort_by();

		$tb = get_instance('toolbar');
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));
		$tb->add_button(array(
			'name' => 'delete',
			'tooltip' => 'Kustuta',
			'url' => 'javascript:ddel()',
			'imgover' => 'delete_over.gif',
			'img' => 'delete.gif'
		));

		$_tbl = $t->draw();

		$t->define_header('Lisa uus rida');
		$t->clear_data();
		$row = array();
		foreach($tbl['fields'] as $fn => $fd)
		{
			$row[$fn] = html::textbox(array(
				'name' => 'values[new]['.$fn.']',
				'value' => '',
				'size' => min($fd['length'],50)
			));
		}
		$row['sel'] = '&nbsp;';
		$t->define_data($row);
		$_tbl.=$t->draw();

		$this->read_template("admin_contents.tpl");
		$this->vars(array(
			'table' => $_tbl,
			'toolbar' => $tb->get_toolbar(),
			'reforb' => $this->mk_reforb('submit_admin_content', array('id' => $id, 'page' => $page, 'keys' => $keys,'is_del' => '0'))
		));
		$this->vars(array(
			'TBL' => '',
			'pages' => $p_tbp->get_tabpanel(array('content' => $this->parse('TBL')))
		));
		return $tbp->get_tabpanel(array('content' => $this->parse()));
	}

	function get_pager_tabpanel($ob, $num_rows, $page, $per_page)
	{
		$p_tbp = get_instance('vcl/tabpanel');
		$p_tbp->hide_one_tab = false;
		$num_pages = ($per_page > 0 ? $num_rows / $per_page : 1);
		for ($i = 0; $i < $num_pages; $i++)
		{
			$p_tbp->add_tab(array(
				'active' => ($page == $i),
				'caption' => ($i * $per_page).' - '.min((($i+1) * $per_page), $num_rows),
				'link' => $this->mk_my_orb('content', array('id' => $ob['oid'], 'page' => $i))
			));
		}
		return $p_tbp;
	}

	function submit_admin_content($arr)
	{
		extract($arr);
		// this is here,because these things are a part of the sql statement 
		// and should contain ' that should be passed without quoting to the sql where part
		$this->dequote(&$keys);
		$ob = $this->get_object($id);
		$db = get_instance('awmyadmin/db_login');
		$db->login_as($ob['meta']['db_base']);

		$page = (int)$page;
		$per_page = $ob['meta']['per_page'];

		$tbl = $db->db_get_table($ob['meta']['db_table']);

		$sela = new aw_array($sel);

		// now go over all rows that were shown and for each check if the data has changed
		// and if it has, write the data
		$q = 'SELECT * FROM '.$ob['meta']['db_table'].' LIMIT '.($page*$per_page).','.((int)$per_page);
		$db->db_query($q);
		while ($row = $db->db_next())
		{
			$tochange = array();
			foreach($tbl['fields'] as $fn => $fd)
			{
				if ($row[$fn] != $values[$row['rec']][$fn])
				{
					$tochange[] = "$fn = '".$values[$row['rec']][$fn]."'";
				}
			}
			$tochangestr = join(" , ", $tochange);
			if ($tochangestr != "")
			{
				// check that we didn't mark this row to be deleted, because then we must not change it's content, because
				// then the where part will break
				if (!($is_del == 1 && in_array($row['rec'], $sela->get())))
				{
					$q = "UPDATE ".$ob['meta']['db_table']." SET $tochangestr WHERE ".$keys[$row['rec']];
//					echo "q = $q <br>";
					$db->save_handle();
					$db->db_query($q);
					$db->restore_handle();
				}
			}
		}

		if ($is_del)
		{
			foreach($sela->get() as $k)
			{
				$q = "DELETE FROM ".$ob['meta']['db_table']." WHERE ".$keys[$k];
//				echo "q = $q <br>";
				$db->save_handle();
				$db->db_query($q);
				$db->restore_handle();
			}
		}

		// check if we should add new
		$add = false;
		foreach($tbl['fields'] as $fn => $fd)
		{
			if ($values['new'][$fn] != '')
			{
				$add = true;
			}
		}
		
		if ($add)
		{
			$cols = new aw_array();
			$vals = new aw_array();
			foreach($values['new'] as $col => $val)
			{
				$cols->set($col);
				$vals->set( "'".$val."'");
			}

			$q = "INSERT INTO ".$ob['meta']['db_table']."(".$cols->to_sql().") VALUES(".$vals->to_sql().")";
			$db->db_query($q);
		}
		return $this->mk_my_orb('admin_content', array('id' => $id,'page' => $page));
	}
}
?>