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
			'active' => ($dro_tab == 'stat_time'  ? true : false),
			'caption' => 'Statistika aja l&otilde;ikes',
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'stat_time')))
		));

		$tbp->add_tab(array(
			'active' => ($dro_tab == 'stat_addr'  ? true : false),
			'caption' => 'Statistika aadresside l&otilde;ikes',
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'stat_addr')))
		));

		$tbp->add_tab(array(
			'active' => ($dro_tab == 'stat_obj'  ? true : false),
			'caption' => 'Statistika objektide l&otilde;ikes',
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'stat_obj')))
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
			'format' => $df[2],
			'nowrap' => 1
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

		$q = "SELECT * FROM syslog ".$this->get_where_clause($id)." ORDER BY tm DESC LIMIT ".$this->get_limit_clause($id);
		echo "q = $q <Br>";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$t->define_data($row);
		}

		$t->set_default_sortby('tm');
		$t->sort_by();
		return $t->draw();
	}

	function get_where_clause($oid, $prep = 'WHERE')
	{
		$ob = $this->get_object($oid);
		$conf_o = $this->get_object($ob['meta']['conf']);

		// merge configs
		if ($ob['meta']['from'] > (400*24*3600))
		{
			$conf_o['meta']['from'] = $ob['meta']['from'];
		}

		if ($ob['meta']['to'] > (400*24*3600))
		{
			$conf_o['meta']['to'] = $ob['meta']['to'];
		}

		// create sql where part for the defined opts
		$sql = array();

		$mt = $conf_o['meta'];
		if ($mt['from'] > (400*24*3600))
		{
			$sql[] = 'tm >= '.$mt['from'];
		}
		if ($mt['to'] > (400*24*3600))
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

		$ret =  join(" AND ", $sql);
		if ($ret != "")
		{
			return ''.$prep.''.$ret;
		}
		return "";
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
		$this->read_adm_template("block.tpl");
		$old = aw_unserialize($this->get_cval("blockedip"));
		$c = "";
		while(list($k,$v) = each($old))
		{
			$this->vars(array(
				"ip" => $v,
				"id" => $k,
				"checked" => "checked",
			));
			$c .= $this->parse("line");
		};
		$this->vars(array(
			"line" => $c,
			"reforb" => $this->mk_reforb("saveblock", array('id' => $id))
		));
		return $this->parse();
	}	

	function saveblock($arr)
	{
		extract($arr);
		$old = aw_unserialize($this->get_cval("blockedip"));
		$store = array();
		if (is_array($check))
		{
			while(list($k,$v) = each($check))
			{
				$store[] = $old[$k];
			};
		};
		if (inet::is_ip($new))
		{
			$store[] = $new;
		};
		$old_s = serialize($store);
		$this->quote($old_s);
		$this->set_cval('blockedip', $old_s);
		return $this->mk_my_orb("change",array('id' => $id, 'dro_tab' => 'ipblock'));
	}

	function _do_stat_time($arr)
	{
		extract($arr);

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'dronline'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$df = aw_ini_get('config.dateformats');

		$t->define_field(array(
			'name' => 'tm',
			'caption' => 'Vahemik',
			'sortable' => 1,
			'type' => 'time',
			'numeric' => 1,
			'format' => $df[3],
			'nowrap' => 1
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1,
		));

		$q = "SELECT count(*) as cnt,date_format(from_unixtime(tm),'%m%d%y') as tm1, tm 
				FROM syslog
				".$this->get_where_clause($id)."
				GROUP BY tm1
				ORDER BY tm ASC
				LIMIT ".$this->get_limit_clause($id);
		echo "q = $q <br>";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$t->define_data($row);
		}
//		$t->set_default_sortby('cnt');
//		$t->set_default_sorder('desc');
//		$t->sort_by();
		return $t->draw();
	}

	function _do_stat_addr($arr)
	{
		extract($arr);

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'dronline'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$df = aw_ini_get('config.dateformats');

		$t->define_field(array(
			'name' => 'rec',
			'caption' => '#',
			'sortable' => 1,
			'numeric' => 1,
			'nowrap' => 1
		));

		$t->define_field(array(
			'name' => 'ip',
			'caption' => 'IP Aadress',
			'sortable' => 1,
			'nowrap' => 1
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1,
		));

		$q = "SELECT count(*) as cnt,ip 
				FROM syslog
				".$this->get_where_clause($id)."
				GROUP BY ip
				ORDER BY cnt DESC
				LIMIT ".$this->get_limit_clause($id);
		echo "q = $q <br>";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			list($row['ip'],) = inet::gethostbyaddr($row['ip']);
			$t->define_data($row);
		}
//		$t->set_default_sortby('cnt');
//		$t->set_default_sorder('desc');
//		$t->sort_by();
		return $t->draw();
	}

	function _do_stat_obj($arr)
	{
		extract($arr);

		load_vcl('table');
		$t = new aw_table(array('prefix' => 'dronline'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$df = aw_ini_get('config.dateformats');

		$t->define_field(array(
			'name' => 'rec',
			'caption' => '#',
			'sortable' => 1,
			'numeric' => 1,
			'nowrap' => 1
		));

		$t->define_field(array(
			'name' => 'oid',
			'caption' => 'OID',
			'sortable' => 1,
			'nowrap' => 1
		));

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1,
		));

		$q = "SELECT count(*) as cnt,ip, objects.name as name,syslog.oid AS oid
				FROM syslog
					LEFT JOIN objects ON objects.oid = syslog.oid
				WHERE syslog.oid IS NOT NULL AND syslog.oid > 0 ".$this->get_where_clause($id,' AND ')."
				GROUP BY oid
				ORDER BY cnt DESC
				LIMIT ".$this->get_limit_clause($id);
		echo "q = $q <br>";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$t->define_data($row);
		}
//		$t->set_default_sortby('cnt');
//		$t->set_default_sorder('desc');
//		$t->sort_by();
		return $t->draw();
	}
}
?>
