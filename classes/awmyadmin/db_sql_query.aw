<?php
/*

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property name type=textbox field=name
	@caption Nimi

	@property status type=status
	@caption Staatus

	@property db_base type=objpicker clid=CL_DB_LOGIN
	@caption Andmebaas

	@property sql type=textarea cols=50 rows=5
	@caption SQL

*/

class db_sql_query extends class_base
{
	function db_sql_query()
	{
		$this->class_base();
		$this->init(array(
			'tpldir' => 'awmyadmin/db_sql_query',
			'clid' => CL_DB_SQL_QUERY
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

	function change($arr)
	{
		extract($arr);
		$chgf = parent::change($arr);

		$ob = $this->get_object($id);

		// do the query and display results
		$num_rows = 0;
		$qres = $this->show_query_results($ob['meta']['db_base'], $ob['meta']['sql'], &$num_rows);

		$tbp = get_instance('vcl/tabpanel');
		$tbp->hide_one_tab = false;
		$tbp->add_tab(array(
			'active' => true,
			'caption' => "Tulemused",
			'link' => $this->mk_my_orb('change', array('id' => $id))
		));

		return $chgf.$tbp->get_tabpanel(array('content' => $qres));
	}

	function show_query_results($db_base, $sql, &$num_rows)
	{
		$this->dequote(&$sql);
		$db = get_instance('awmyadmin/db_login');
		$db->login_as($db_base);

		$num_rows = 0;

		load_vcl("table");
		$t = new aw_table(array("prefix" => "db_table_content"));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$rows_defined = false;
		if (!$db->db_query($sql,false))
		{
			return $this->error_table(&$t,&$db);
		}
		while ($row = $db->db_next())
		{
			if (!$rows_defined)
			{
				foreach($row as $rn => $rv)
				{
					$t->define_field(array(
						'name' => $rn,
						'caption' => $rn,
						'sortable' => 1,
						'numeric' => is_number($rv)	// this will probably fail most of the time, but I see no other way
					));
				}
				$rows_defined = true;
			}
			$t->define_data($row);
			$num_rows++;
		}

		$t->sort_by();
		return $t->draw();
	}

	function error_table(&$t,&$db)
	{
		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi!'
		));
		$t->define_field(array(
			'name' => 'error',
			'caption' => 'Viga!'
		));

		$errdat = $db->db_get_last_error();
		$t->define_data(array(
			'name' => 'error_cmd',
			'error' => $errdat['error_cmd']
		));
		$t->define_data(array(
			'name' => 'error_code',
			'error' => $errdat['error_code']
		));
		$t->define_data(array(
			'name' => 'error_string',
			'error' => $errdat['error_string']
		));
		return $t->draw();
	}
}
?>