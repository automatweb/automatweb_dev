<?php

/*

@default table=objects
@default field=meta
@default method=serialize

@property status type=status field=status
@caption Staatus

@property conf type=objpicker clid=CL_DRONLINE_CONF
@caption Konfiguratsioon

@property numlines type=textbox 
@caption Mitu rida

@property from type=date_select 
@caption Alates

@property to type=date_select 
@caption Kuni

*/

class dronline extends class_base
{
	function dronline()
	{
		$this->init(array(
			'tpldir' => 'syslog/dronline',
			'clid' => CL_DRONLINE
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

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}

	function change($arr)
	{
		extract($arr);
		$ret = parent::change($arr);
		
		if ($dro_tab == '')
		{
			$dro_tab = 'dronline';
		}
		unset($arr['class']);
		unset($arr['action']);

		$tbp = get_instance("vcl/tabpanel");
		$tbp->add_tab(array(
			'active' => ($dro_tab == 'dronline' ? true : false),
			'caption' => 'DR. Online',
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'dronline')))
		));

		$tbp->add_tab(array(
			'active' => ($dro_tab == 'stat'  ? true : false),
			'caption' => 'Statistika',
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'stat')))
		));

		$tbp->add_tab(array(
			'active' => ($dro_tab == 'ipblock'  ? true : false),
			'caption' => 'IP Blokk',
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'ipblock')))
		));

		$fn = '_do_'.$dro_tab;
		return $ret.$tbp->get_tabpanel(array(
			'content' => $this->$fn($arr)
		));
	}

	function _do_dronline($arr)
	{
		extract($arr);

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'dronline'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$df = aw_ini_get('config.dateformats');
		$t->define_field(array(
			'name' => 'rec',
			'caption' => 'Nr',
		));
		$t->define_field(array(
			'name' => 'tm',
			'caption' => 'Millal',
			'sortable' => 1,
			'numeric' => 1,
			'type' => 'time',
			'format' => $df[2]
		));
		$t->define_field(array(
			'name' => 'uid',
			'caption' => 'Kes',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'ip',
			'caption' => 'IP',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'action',
			'caption' => 'Mida',
			'sortable' => 1,
		));

		$this->db_query("SELECT * FROM syslog WHERE ".$this->get_where_clause($id)." LIMIT ".$this->get_limit_clause($id));
		while ($row = $this->db_next())
		{
			$t->define_data($row);
		}

		$t->set_default_sortby('tm');
		$t->sort_by();
		return $t->draw();
	}

	function get_where_clause($oid)
	{
		$ob = $this->get_object($oid);
		$conf_o = $this->get_object($ob['meta']['conf']);

		// merge configs
		if ($ob['meta']['from'] != 0)
		{
			$conf_o['meta']['from'] = $ob['meta']['from'];
		}

		if ($ob['meta']['to'] != 0)
		{
			$conf_o['meta']['to'] = $ob['meta']['to'];
		}

		// create sql where part for the defined opts
		$sql = array();

		$mt = $conf_o['meta'];
		if ($mt['from'])
		{
			$sql[] = 'tm >= '.$mt['from'];
		}
		if ($mt['to'])
		{
			$sql[] = 'tm <= '.$mt['to'];
		}
		if ($mt['user'] != '')
		{
			$sql[] = 'uid = \''.$mt['user'].'\'';
		}
		if ($mt['address'] != '')
		{
			$sql[] = 'ip LIKE \'%'.$mt['address'].'%\'';
		}
		if ($mt['textfilter'] != '')
		{
			$sql[] = 'action LIKE \'%'.$mt['textfilter'].'%\'';
		}

		return join(" AND ", $sql);
	}

	function get_limit_clause($id)
	{
		$ob = $this->get_object($id);
		$conf_o = $this->get_object($ob['meta']['conf']);

		// merge configs
		if ($ob['meta']['numlines'] != 0)
		{
			$conf_o['meta']['numlines'] = $ob['meta']['numlines'];
		}

		return $conf_o['meta']['numlines'];
	}

	function _do_ipblock($arr)
	{
		extract($arr);
		return "ipblock";
	}	

	function _do_stat($arr)
	{
		extract($arr);
		return "stat";
	}
}
?>
