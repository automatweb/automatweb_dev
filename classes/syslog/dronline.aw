<?php

/*

@default table=objects
@default field=meta
@default method=serialize
@default group=general

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

@property save_as_obj type=checkbox ch_value=1
@caption Salvesta p&auml;ring log objektiks

@property save_as_obj_name type=textbox
@caption Log objekti nimi

@groupinfo general caption=Üldine

*/

define(RNG_HOUR, 1);
define(RNG_DAY, 2);
define(RNG_MONTH, 3);
define(RNG_YEAR, 4);

class dronline extends class_base
{
	function dronline()
	{
		$this->init(array(
			'tpldir' => 'syslog/dronline',
			'clid' => CL_DRONLINE
		));

		$this->timespans = array(
			RNG_HOUR => array('sql' => "date_format(from_unixtime(tm),'%m%d%y%H')", 'df' => 'd-M-Y / H:00'),
			RNG_DAY => array('sql' => "date_format(from_unixtime(tm),'%m%d%y')", 'df' => 'd-M-Y'),
			RNG_MONTH => array('sql' => "date_format(from_unixtime(tm),'%m%y')", 'df' => 'M-Y'),
			RNG_YEAR => array('sql' => "date_format(from_unixtime(tm),'%y')", 'df' => 'Y')
		);
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
		if ($dro_tab == '')
		{
			$dro_tab = 'dronline';
		}
		if (!$cur_range)
		{
			$cur_range = RNG_DAY;
			unset($arr['cur_range']);
		}

		$arr['extraids'] = array(
			'dro_tab' => $dro_tab,
			'cur_range' => $cur_range
		);
		$ret = parent::change($arr);

		// if no conf object has been set yet, return the change form
		$ob = $this->get_object($id);
		if (!$ob['meta']['conf'])
		{
			return $ret;
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
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'stat_time','cur_range' => $cur_range)))
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

		$tbp->add_tab(array(
			'active' => ($dro_tab == 'show_queries'  ? true : false),
			'caption' => 'Salvestatud p&auml;ringud',
			'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => 'show_queries')))
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
			'name' => 'type',
			'caption' => 'T&uuml;&uuml;p',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'act_id',
			'caption' => 'Tegevus',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'action',
			'caption' => 'Mida',
			'sortable' => 1,
		));

		$ts = aw_ini_get('syslog.types');
		$as = aw_ini_get('syslog.actions');

		if ($query != '')
		{
			$q = $query;
		}
		else
		{
			if ($show_oid)
			{
				$whc = $this->get_where_clause($id,' AND ');
				$whc = " WHERE syslog.oid = '$show_oid' ".$whc;
			}
			else
			{
				$whc = $this->get_where_clause($id);
			}

			$q = "SELECT * FROM syslog ".$whc." ORDER BY tm DESC ".$this->get_limit_clause($id);
			if ($ret_query)
			{
				return $q;
			}
		}

		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$row['type'] = $ts[$row['type']]['name'];
			$row['act_id'] = $as[$row['act_id']]['name'];
			list($row['ip'],) = inet::gethostbyaddr($row['ip']);
			$t->define_data($row);
		}

		$t->set_default_sortby('tm');
		$t->set_default_sorder('DESC');
		$t->sort_by();
		return $t->draw();
	}

	function get_where_clause($oid, $prep = ' WHERE ')
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
			$tfl = explode(',', $mt['textfilter']);
			$sql[] = '('.join(' OR ', map('action LIKE \'%%%s%%\'', $tfl)).')';
		}

		// action filter
		if ($mt['use_filter'])
		{
			$tsql = array();

			$tin = array();
			$ain = array();
			$cin = array();
			// now figure out all the checked vars in the filter
			foreach($mt as $k => $v)
			{
				if (substr($k,0,4) == 'slt_' && $v == 1) // syslog.type
				{
					$tin[] = '\''.substr($k,4).'\'';
				}
				else
				if (substr($k,0,4) == 'sla_' && $v == 1)	// syslog.action
				{
					$ain[] = '\''.substr($k,4).'\'';
				}
				else
				if (substr($k,0,4) == 'slc_' && $v == 1)	// syslog action&type combo
				{
					$_t = explode('_',$k);
					$cin[] = '( type = \''.$_t[1].'\' AND action = \''.$_t[2].'\' )';
				}
			}

			if (count($tin) > 0)
			{
				$tsql[] = 'type IN ('.join(',',$tin).')';
			}
			if (count($ain) > 0)
			{
				$tsql[] = 'act_id IN ('.join(',',$ain).')';
			}
			if (count($cin) > 0)
			{
				$tsql[] = '('.join(' OR ',$cin).')';
			}

			$sql[] = '('.join(' OR ',$tsql).')';
		}

		// blocked ips
		$bip = aw_unserialize($this->get_cval('blockedip'));
		if (is_array($bip) && count($bip) > 0)
		{
			$sql[] = 'ip NOT IN ('.join(',',map('\'%s\'',$bip)).')';
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

		$ret = $conf_o['meta']['numlines'];
		if ($ret != '')
		{
			$ret = ' LIMIT '.$ret;
		}
		return $ret;
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
			'format' => $this->timespans[$cur_range]['df'],
			'nowrap' => 1,
			'width' => '20%'
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1,
			'width' => '10%'
		));
		$t->define_field(array(
			'name' => 'bar',
			'caption' => '%',
			'sortable' => 0,
		));

		$tmsp = $this->timespans[$cur_range]['sql'];

		if ($query != '')
		{
			$q = $query;
		}
		else
		{
			$q = "SELECT count(*) as cnt,$tmsp as tm1, tm 
					FROM syslog
					".$this->get_where_clause($id)."
					GROUP BY tm1
					ORDER BY tm ASC
					".$this->get_limit_clause($id);
		}

		if ($ret_query)
		{
			return $q;
		}

		$this->db_query($q);
		$max = 1;
		$dat = array();
		while($row = $this->db_next())
		{
			$max = max($row['cnt'], $max);
			$dat[] = $row;
		}

		foreach($dat as $row)
		{
			$pr = floor((($row['cnt'] / $max) * 100.0)+0.5);
			$row['bar'] = html::img(array(
				'url' => $this->cfg['baseurl'].'/automatweb/images/bar.gif',
				'height' => 5,
				'width' => ($pr == 0 ? '1' : $pr.'%')
			));
			$t->define_data($row);
		}
		$t->set_default_sortby('tm');
		$t->sort_by();
		$tbl = $t->draw();
		
		if ($query != '')
		{
			// if we are showing from a static query, we can't change it anyway
			return $tbl;
		}

		unset($arr['cur_range']);		
		$this->read_template('sel_range.tpl');
		$this->vars(array(
			'ranges' => $this->picker($cur_range, array(
				RNG_HOUR => 'Tundide l&otilde;ikes', 
				RNG_DAY => 'P&auml;evade l&otilde;ikes',
				RNG_MONTH => 'Kuude l&otilde;ikes',
				RNG_YEAR => 'Aastate l&otilde;ikes'
			)),
			'reforb' => $this->mk_reforb('change', $arr + array('no_reforb' => 1))
		));

		$tb = get_instance('toolbar');
		$tb->add_cdata($this->parse());
		return $tb->get_toolbar().$tbl;
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
			'name' => 'sel',
			'caption' => 'Blokeeri',
			'sortable' => 0,
			'nowrap' => 1,
			'width' => '1',
			'align' => 'center'
		));

		$t->define_field(array(
			'name' => 'ip',
			'caption' => 'IP Aadress',
			'sortable' => 0,
			'nowrap' => 1,
			'width' => 1
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1,
		));
		$t->define_field(array(
			'name' => 'bar',
			'caption' => '%',
			'sortable' => 0,
		));

		if ($query != '')
		{
			$q = $query;
		}
		else
		{
			$q = "SELECT count(*) as cnt,ip 
					FROM syslog
					".$this->get_where_clause($id)."
					GROUP BY ip
					ORDER BY cnt DESC
					".$this->get_limit_clause($id);
		}

		if ($ret_query)
		{
			return $q;
		}

		$this->db_query($q);
		$max = 1;
		$dat = array();
		while($row = $this->db_next())
		{
			$max = max($row['cnt'], $max);
			$dat[] = $row;
		}

		foreach($dat as $row)
		{
			$pr = floor((($row['cnt'] / $max) * 100.0)+0.5);
			$row['bar'] = html::img(array(
				'url' => $this->cfg['baseurl'].'/automatweb/images/bar.gif',
				'height' => 5,
				'width' => ($pr == 0 ? '1' : $pr.'%')
			));

			$row['sel'] = html::checkbox(array(
				'name' => 'block[]',
				'value' => $row['ip']
			));
			
			list($row['ip'],) = inet::gethostbyaddr($row['ip']);
			$row['ip'] = html::href(array(
				'url' => '#',
				'onClick' => 'javascript:window.open("http://'.$row['ip'].'")',
				'caption' => $row['ip']
			));

			$t->define_data($row);
		}
		$t->set_default_sortby('cnt');
		$t->set_default_sorder('desc');
		$t->sort_by();
		$tbl = $t->draw();

		$tb = get_instance('toolbar');
		
		$tb->add_button(array(
			'name' => 'Blokeeri',
			'tooltip' => 'Blokeeri',
			'url' => 'javascript:document.blokk.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));

		$ret = html::form(array(
			'action' => 'reforb.'.$this->cfg['ext'],
			'method' => 'POST',
			'name' => 'blokk',
			'content' => $tb->get_toolbar().$tbl.$this->mk_reforb('submit_block', $arr)
		));
		return $ret;
	}

	function _do_stat_obj_show_oid($arr)
	{
		$tb = get_instance('toolbar');
		$tarr = $arr;
		unset($tarr['show_oid']);

		$tb->add_cdata(html::href(array(
			'url' => $this->mk_my_orb('change', $tarr),
			'caption' => '<span class="awmenuedittabletext">Tagasi objektide statistikasse</span>'
		)));

		return $tb->get_toolbar().$this->_do_dronline($arr);
	}

	function _do_stat_obj($arr)
	{
		extract($arr);

		if ($show_oid)
		{
			return $this->_do_stat_obj_show_oid($arr);
		}

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
			'nowrap' => 1,
			'width' => 1
		));

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
			'sortable' => 1,
		));
		$t->define_field(array(
			'name' => 'oppnar',
			'caption' => 'Detailid',
			'sortable' => 0,
		));
		$t->define_field(array(
			'name' => 'cnt',
			'caption' => 'Mitu',
			'sortable' => 1,
			'numeric' => 1,
		));
		$t->define_field(array(
			'name' => 'bar',
			'caption' => '%',
			'sortable' => 0,
		));

		if ($query != '')
		{
			$q = $query;
		}
		else
		{
			$q = "SELECT count(*) as cnt,ip, objects.name as name,syslog.oid AS oid
					FROM syslog
						LEFT JOIN objects ON objects.oid = syslog.oid
					WHERE syslog.oid IS NOT NULL AND syslog.oid > 0 ".$this->get_where_clause($id,' AND ')."
					GROUP BY oid
					ORDER BY cnt DESC
					".$this->get_limit_clause($id);
		}

		if ($ret_query)
		{
			return $q;
		}

		$this->db_query($q);
		while($row = $this->db_next())
		{
			$max = max($row['cnt'], $max);
			$dat[] = $row;
		}

		foreach($dat as $row)
		{
			$pr = floor((($row['cnt'] / $max) * 100.0)+0.5);
			$row['bar'] = html::img(array(
				'url' => $this->cfg['baseurl'].'/automatweb/images/bar.gif',
				'height' => 5,
				'width' => ($pr == 0 ? '1' : $pr.'%')
			));

			$row['name'] = ' '.html::href(array(
				'url' => $this->cfg['baseurl'].'/'.$row['oid'],
				'caption' => $row['name'],
				'target' => '_blank'
			));
	
			$arr['show_oid'] = $row['oid'];

			$row['oppnar'] = html::href(array(
				'url' => $this->mk_my_orb('change', $arr),
				'caption' => 'Detailid'
			));
			$t->define_data($row);
		}
		$t->set_default_sortby('cnt');
		$t->set_default_sorder('desc');
		$t->sort_by();
		return $t->draw();
	}

	function submit_block($arr)
	{
		extract($arr);

		$old = aw_unserialize($this->get_cval("blockedip"));
		if (!is_array($old))
		{
			$old = array();
		}
		$_sel = new aw_array($block);
		foreach($_sel->get() as $v)
		{
			if (!in_array($v, $old) && inet::is_ip($v))
			{
				$old[] = $v;
			}
		}

		$old_s = serialize($old);
		$this->quote($old_s);
		$this->set_cval('blockedip', $old_s);
		
		unset($arr['block']);
		unset($arr['reforb']);
		unset($arr['class']);
		unset($arr['action']);
		return $this->mk_my_orb('change', $arr);
	}

	function get_property(&$arr)
	{
		$prop = &$arr['prop'];
		$req = $arr['request'];
		if ($prop['name'] == 'save_as_obj')
		{
			$prop['value'] = 0;
			$fl = true;
		}
		else
		if ($prop['name'] == 'save_as_obj_name')
		{
			$prop['value'] = '';
			$fl = true;
		}

		if (in_array($req['dro_tab'],array('ipblock','show_queries')) && $fl)
		{
			return PROP_IGNORE;
		}

		return PROP_OK;
	}

	function set_property(&$arr)
	{
		$prop = &$arr['prop'];
		if ($prop['name'] == 'save_as_obj')
		{
			if ($arr['form_data']['save_as_obj'] == 1)
			{
				// do_save_as_log_obj
				$param = $arr['form_data'];
				$param+=$arr['form_data']['extraids'];
				$param['ret_query'] = true;
				$fn = '_do_'.$arr['form_data']['extraids']['dro_tab'];

				$q = $this->$fn($param);
				$this->quote(&$q);

				$nid = $this->new_object(array(
					'name' => $arr['form_data']['save_as_obj_name'],
					'class_id' => CL_DRONLINE_LOG,
					'parent' => $arr['obj']['parent'],
					'metadata' => array(
						'dro_type' => $arr['form_data']['extraids']['dro_tab'],
						'cur_range' => $arr['form_data']['extraids']['cur_range'],
						'query' => $q
					)
				));
			}

			$prop['value'] = 0;
			return PROP_IGNORE;
		}
		else
		if ($prop['name'] == 'save_as_obj_name')
		{
			$prop['value'] = '';
			return PROP_IGNORE;
		}
		return PROP_OK;
	}

	function _do_show_queries($arr)
	{
		extract($arr);
		
		load_vcl('table');
		$t = new aw_table(array('prefix' => 'dronline'));
		$t->parse_xml_def($this->cfg['basedir'] . '/xml/generic_table.xml');

		$t->define_field(array(
			'name' => 'oid',
			'caption' => '#',
			'sortable' => 1,
			'numeric' => 1,
			'nowrap' => 1
		));

		$t->define_field(array(
			'name' => 'name',
			'caption' => 'Nimi',
			'sortable' => 1,
		));

		$df = aw_ini_get('config.dateformats');
		$t->define_field(array(
			'name' => 'modified',
			'caption' => 'Muudetud',
			'sortable' => 1,
			'numeric' => 1,
			'type' => 'time',
			'format' => $df[2]
		));

		$t->define_field(array(
			'name' => 'modifiedby',
			'caption' => 'Kes Muutis',
			'sortable' => 1,
		));

		$t->define_field(array(
			'name' => 'view',
			'caption' => 'Vaata',
		));

		$ol = $this->list_objects(array(
			'class' => CL_DRONLINE_LOG,
			'return' => ARR_ALL
		));

		foreach($ol as $oid => $od)
		{
			$tarr = $arr;
			$tarr['show_log_obj'] = $od['oid'];

			$od['view'] = html::href(array(
				'url' => $this->mk_my_orb('change', array('id' => $od['oid']), 'dronline_log'),
				'caption' => 'Vaata',
				'target' => '_blank'
			));
			
			$t->define_data($od);
		}

		$t->set_default_sortby('name');
		$t->sort_by();
		return $t->draw();
	}
}
?>
