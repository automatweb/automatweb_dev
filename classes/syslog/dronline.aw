<?php

/*

@classinfo syslog_type=ST_DRONLINE

@tableinfo dronline_bg_status index=id master_table=objects master_index=id

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

@property g_default_tab type=select
@caption Default tab

@property save_as_obj type=checkbox ch_value=1
@caption Salvesta p&auml;ring log objektiks

@property save_as_obj_name type=textbox
@caption Log objekti nimi

@property lock_q type=checkbox ch_value=1
@caption Lukusta objektide salvestamine

@property lock_filter type=checkbox ch_value=1
@caption Lukusta p&auml;ringu tegemine

@property bg_queries type=checkbox ch_value=1
@caption Cache p&auml;ringud taustal

@property rerun_queries_at type=objpicker clid=CL_REPEATER_OBJ
@caption Cache uueneb automaatselt vastavalt kordusele

@property rerun_queries type=checkbox ch_value=1
@caption Uuenda p&auml;ringute&nbsp;cache kohe

@property bg_query_status type=text
@caption Tausta p&auml;ringute staatus

@property bg_query_created type=text
@caption Cache viimati muudetud

@groupinfo general caption=Üldine

*/

define("RNG_HOUR", 1);
define("RNG_DAY", 2);
define("RNG_MONTH", 3);
define("RNG_YEAR", 4);

define("DRO_C_NOTCREATED", 0);
define("DRO_C_OUTOFDATE", 1);
define("DRO_C_READY", 2);
define("DRO_C_UPDATING", 3);

class dronline extends class_base
{
	function dronline()
	{
		$this->init(array(
			'tpldir' => 'syslog/dronline',
			'clid' => CL_DRONLINE,
		));

		$this->timespans = array(
			RNG_HOUR => array('sql' => "date_format(from_unixtime(tm),'%m%d%y%H')", 'df' => 'd-M-Y / H:00'),
			RNG_DAY => array('sql' => "date_format(from_unixtime(tm),'%m%d%y')", 'df' => 'd-M-Y'),
			RNG_MONTH => array('sql' => "date_format(from_unixtime(tm),'%m%y')", 'df' => 'M-Y'),
			RNG_YEAR => array('sql' => "date_format(from_unixtime(tm),'%y')", 'df' => 'Y')
		);

		$this->date_ranges = array(
			RNG_HOUR => 'Tundide l&otilde;ikes', 
			RNG_DAY => 'P&auml;evade l&otilde;ikes',
			RNG_MONTH => 'Kuude l&otilde;ikes',
			RNG_YEAR => 'Aastate l&otilde;ikes'
		);

		$this->tablist = array(
			'dronline' => 'Online',
			'stat_time' => 'Statistika aja l&otilde;ikes',
			'stat_addr' => 'Statistika aadresside l&otilde;ikes',
			'stat_obj' => 'Statistika objektide l&otilde;ikes',
			'ipblock' => 'IP Blokk',
			'show_queries' => 'Salvestatud p&auml;ringud',
			'general' => 'M&auml;&auml;rangud',
			'tab_conf' => 'P&auml;ringud'
		);

		$this->query_tabs = array(
			'stat_time', 'stat_addr', 'stat_obj'
		);

		$this->statuses = array(
			DRO_C_NOTCREATED => 'Cache tegemata',
			DRO_C_OUTOFDATE => 'Cache vananenud',
			DRO_C_READY => 'Cache valmis',
			DRO_C_UPDATING => 'Cache uuendamisel'
		);

		$today = mktime(0,0,0,date("m"), date("j"), date("Y"));

		$dow = array("0" => "6", "1" => "0", "2" => "1", "3" => "2", "4" => "3", "5" => "4", "6" => "5");

		$this_week = $today - ($dow[date("w")]*24*3600);
		$this_month = $today - ((date("j")-1)*24*3600);
		$this_year = mktime(0,0,0,1,1,date("Y"));

		$this->def_spans = array(
			"1" => array(
				"from" => $today,
				"to" => time()+24*3600, 
				"text" => "T&auml;na"
			),
			"2" => array(
				"from" => ($today-24*3600),
				"to" => $today,
				"text" => "Eile"
			),
			"3" => array(
				"from" => $this_week, 
				"to" => time()+24*3600,
				"text" => "See n&auml;dal"
			),
			"4" => array(
				"from" => ($this_week-(24*3600*7)), 
				"to" => time()+24*3600,
				"text" => "Viimased 2 n&auml;dalat"
			),
			"5" => array(
				"from" => $this_month, 
				"to" => time()+24*3600,
				"text" => "See kuu"
			),
			"6" => array(
				"from" => ($this_month-(24*3600*31)), 
				"to" => time()+24*3600,
				"text" => "Viimased 2 kuud"
			),
			"7" => array(
				"from" => ($this_month-(24*3600*31*6)), 
				"to" => time()+24*3600,
				"text" => "Viimased pool aastat"
			),
			"8" => array(
				"from" => ($this_year), 
				"to" => time()+24*3600,
				"text" => "See aasta"
			),
			"9" => array(
				"from" => ($this_year-(24*3600*356)), 
				"to" => time()+24*3600,
				"text" => "Viimased 2 aastat"
			)
		);
		
		$this->confable_tabs = array('dronline','stat_time', 'stat_addr', 'stat_obj', 'tab_conf');
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
		$ob = $this->get_object($id);

		if ($dro_tab == '')
		{
			$dro_tab = $ob['meta']['g_default_tab'];
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

		// if no conf object has been set yet, return the change form
		$this->mk_path($ob['parent'], "Muuda dronline objekti");
		if (!$ob['meta']['conf'])
		{
			return $this->_do_general($arr);
		}

		// check if we need to do_save_as_obj
		if ($do_save_as_obj != '')
		{
			$param = $arr;
			$param['ret_query'] = true;
			$param['dro_tab'] = $dro_tab;
			$fn = '_do_'.$dro_tab;

			$q = $this->$fn($param);
			$this->quote(&$q);

			$nid = $this->new_object(array(
				'name' => $do_save_as_obj,
				'class_id' => CL_DRONLINE_LOG,
				'parent' => $ob['parent'],
				'metadata' => array(
					'dro_type' => $dro_tab,
					'cur_range' => $cur_range,
					'query' => $q,
					'conf_desc' => $this->get_conf_desc($param)
				)
			));
			unset($arr['do_save_as_obj']);
			header("Location: ".$this->mk_my_orb('change', $arr));
			die();
		}

		unset($arr['class']);
		unset($arr['action']);

		$tbp = get_instance("vcl/tabpanel");

		foreach ($this->tablist as $tabid => $tabname)
		{
			if ($tabid == 'tab_conf')
			{
				if (in_array($dro_tab, $this->confable_tabs) && !$ob['meta']['lock_filter'])
				{
					$tbp->add_tab(array(
						'active' => ($dro_tab == $tabid ? true : false),
						'caption' => $tabname,
						'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => $tabid, 'prev_tab' => $dro_tab)))
					));
				}
			}
			else
			{
				$tbp->add_tab(array(
					'active' => ($dro_tab == $tabid ? true : false),
					'caption' => $tabname,
					'link' => $this->mk_my_orb('change', array_merge($arr, array('dro_tab' => $tabid)))
				));
			}
		}

		$this->from_cache = false;
		// right. now check if we should use the cache and if it exists and if it does, then use the data from cache
		if ($ob['meta']['bg_queries'])
		{
			$cache = aw_unserialize($this->db_fetch_field("SELECT cache_content FROM dronline_bg_status WHERE id = $id","cache_content"));
			if (is_array($cache) && isset($cache[$dro_tab]))
			{
				if ($def_span)
				{
					$arr['data'] = $cache[$dro_tab][$def_span];
				}
				else
				{
					$arr['data'] = $cache[$dro_tab]['nospan'];
				}
				$this->from_cache = true;
			}
		}

		$fn = '_do_'.$dro_tab;
		return /*$this->_do_general($arr).*/$tbp->get_tabpanel(array(
			'content' => $this->$fn($arr)
		));
	}

	function _do_general($arr)
	{
		return parent::change($arr);
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
		if (aw_ini_get("syslog.has_site_id"))
		{
			$t->define_field(array(
				'name' => 'site_id',
				'caption' => 'Saidi ID',
				'sortable' => 1,
			));
		}
		$t->define_field(array(
			'name' => 'oid',
			'caption' => 'OID',
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
				$whc = $this->get_where_clause($id, ' AND ', false, 'dronline', $def_span);
				$whc = " WHERE syslog.oid = '$show_oid' ".$whc;
			}
			else
			{
				$whc = $this->get_where_clause($id, ' WHERE ', false, 'dronline', $def_span);
			}

			$q = "SELECT * FROM syslog ".$whc." ORDER BY tm DESC ".$this->get_limit_clause($id);
			if ($ret_query)
			{
				return $q;
			}

			$numres = $this->db_fetch_field("SELECT count(*) AS cnt FROM syslog ".$whc." ORDER BY tm DESC ".$this->get_limit_clause($id), "cnt");
			if ($numres > 1000 && $this->get_limit_clause($id,true) > 1000)
			{
				return "Tulemus on liiga suur! Maksimaalne kuvatav ridade arv on 1000, kuid tulemuses on $numres rida!";
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
		$tbl = $t->draw();

		return $this->get_sao_tb($arr, $id).$tbl;
	}

	function get_where_clause($oid, $prep = ' WHERE ', $ret_conf_desc = false, $tab_id = '', $def_span = 0)
	{
		$ob = $this->get_object($oid);
		if ($tab_id != '' && $ob['meta']['tab_objs'][$tab_id])
		{
			$conf_o = $this->get_object($ob['meta']['tab_objs'][$tab_id]);
		}
		else
		{
			$conf_o = $this->get_object($ob['meta']['conf']);
		}

		// merge configs
		if ($ob['meta']['from'] > (400*24*3600))
		{
			$conf_o['meta']['from'] = $ob['meta']['from'];
		}

		if ($ob['meta']['to'] > (400*24*3600))
		{
			$conf_o['meta']['to'] = $ob['meta']['to'];
		}

		$conf_o['meta']['def_span'] = $def_span;

		// create sql where part for the defined opts
		$sql = array();

		// configuration description
		$this->read_template("show_conf.tpl");

		$mt = $conf_o['meta'];
		if ($mt['def_span'])
		{
			$sql[] = 'tm >= '.$this->def_spans[$mt['def_span']]['from'];
			$sql[] = 'tm <= '.$this->def_spans[$mt['def_span']]['to'];
			$this->vars(array(
				"desc" => "M&auml;&auml;ratud vahemik:",
				"value" => $this->def_spans[$mt['def_span']]["text"]
			));
			$cd .= $this->parse("LINE");
		}
		else
		{
			if ($mt['from'] > (400*24*3600))
			{
				$sql[] = 'tm >= '.$mt['from'];
				$this->vars(array(
					"desc" => "Alates:",
					"value" => $this->time2date($mt['from'],2)
				));
				$cd .= $this->parse("LINE");
			}
			if ($mt['to'] > (400*24*3600))
			{
				$sql[] = 'tm <= '.$mt['to'];
				$this->vars(array(
					"desc" => "Kuni:",
					"value" => $this->time2date($mt['to'],2)
				));
				$cd .= $this->parse("LINE");
			}
		}
		if ($mt['user'] != '')
		{
			$sql[] = 'uid = \''.$mt['user'].'\'';
			$this->vars(array(
				"desc" => "Kasutaja:",
				"value" => $mt['user']
			));
			$cd .= $this->parse("LINE");
		}
		if ($mt['address'] != '')
		{
			$sql[] = 'ip LIKE \'%'.$mt['address'].'%\'';
			$this->vars(array(
				"desc" => "IP:",
				"value" => $mt['address']
			));
			$cd .= $this->parse("LINE");
		}
		if ($mt['textfilter'] != '')
		{
			$tfl = explode(',', $mt['textfilter']);
			$sql[] = '('.join(' OR ', map('action LIKE \'%%%s%%\'', $tfl)).')';
			$this->vars(array(
				"desc" => "Tegevuse filter:",
				"value" => $mt['textfilter']
			));
			$cd .= $this->parse("LINE");
		}

		if (count($mt['sites']) > 0 && aw_ini_get("syslog.has_site_id"))
		{
			$sql[] = "site_id IN (".join(",",map("%s",$mt['sites'])).")";
			$this->vars(array(
				"desc" => "Saidid:",
				"value" => join(",",$mt['sites'])
			));
			$cd .= $this->parse("LINE");
		}

		// action filter
		if ($mt['use_filter'])
		{
			$tsql = array();

			$tin = array();
			$ain = array();
			$cin = array();
			$t_strs = array();
			$a_strs = array();
			$c_strs = array();

			$sts = aw_ini_get("syslog.types");
			$stas = aw_ini_get("syslog.actions");

			// now figure out all the checked vars in the filter
			foreach($mt as $k => $v)
			{
				if (substr($k,0,4) == 'slt_' && $v == 1) // syslog.type
				{
					$tin[] = '\''.substr($k,4).'\'';
					$t_strs[] = $sts[substr($k,4)]['name'];
				}
				else
				if (substr($k,0,4) == 'sla_' && $v == 1)	// syslog.action
				{
					$ain[] = '\''.substr($k,4).'\'';
					$a_strs[] = $stas[substr($k,4)]['name'];
				}
				else
				if (substr($k,0,4) == 'slc_' && $v == 1)	// syslog action&type combo
				{
					$_t = explode('_',$k);
					$cin[] = '( type = \''.$_t[1].'\' AND action = \''.$_t[2].'\' )';
					$c_strs[] = $sts[$_t[1]]['name']."/".$stas[$_t[2]]['name'];
				}
			}

			if (count($tin) > 0)
			{
				$tsql[] = 'type IN ('.join(',',$tin).')';
				$this->vars(array(
					"desc" => "T&uuml;&uuml;bid:",
					"value" => join(", ", $t_strs)
				));
				$cd .= $this->parse("LINE");
			}
			if (count($ain) > 0)
			{
				$tsql[] = 'act_id IN ('.join(',',$ain).')';
				$this->vars(array(
					"desc" => "Tegevused:",
					"value" => join(", ", $a_strs)
				));
				$cd .= $this->parse("LINE");
			}
			if (count($cin) > 0)
			{
				$tsql[] = '('.join(' OR ',$cin).')';
				$this->vars(array(
					"desc" => "Kombinatsioonid:",
					"value" => join(", ", $c_strs)
				));
				$cd .= $this->parse("LINE");
			}

			$sql[] = '('.join(' OR ',$tsql).')';
		}

		// blocked ips
		$bip = aw_unserialize($this->get_cval('blockedip'));
		if (is_array($bip) && count($bip) > 0)
		{
			$sql[] = 'ip NOT IN ('.join(',',map('\'%s\'',$bip)).')';
			$this->vars(array(
				"desc" => "Blokeeritud IP'd:",
				"value" => join(',',map('\'%s\'',$bip))
			));
			$cd .= $this->parse("LINE");
		}

		if ($ret_conf_desc)
		{
			$this->vars(array(
				"LINE" => $cd
			));
			return $this->parse();
		}

		$ret =  join(" AND ", $sql);
		if ($ret != "")
		{
			return ''.$prep.''.$ret;
		}
		return "";
	}

	function get_limit_clause($id, $ret_num = false)
	{
		$ob = $this->get_object($id);
		$conf_o = $this->get_object($ob['meta']['conf']);

		// merge configs
		if ($ob['meta']['numlines'] != 0)
		{
			$conf_o['meta']['numlines'] = $ob['meta']['numlines'];
		}

		$ret = $conf_o['meta']['numlines'];
		if ($ret_num)
		{
			return $ret;
		}

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

	function _do_stat_time_get_data($arr)
	{
		extract($arr);
		if (!$cur_range)
		{
			$cur_range = RNG_DAY;
		}
		$tmsp = $this->timespans[$cur_range]['sql'];

		if ($query != '')
		{
			$q = $query;
		}
		else
		{
			$q = "SELECT count(*) as cnt,$tmsp as tm1, tm 
					FROM syslog
					".$this->get_where_clause($id, ' WHERE ', false, 'stat_time', $def_span)."
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
			$dat[] = $row;
		}

		return array($cur_range => $dat);
	}

	function _do_stat_time($arr)
	{
		if (!$arr['cur_range'])
		{
			$arr['cur_range'] = RNG_DAY;
		}
		extract($arr);

		if (!isset($data) || !is_array($data))
		{
			$data = $this->_do_stat_time_get_data($arr);
		}

		if ($ret_query)
		{
			return $data;
		}
		$data = $data[$cur_range];

		unset($arr['cur_range']);
		unset($arr['data']);

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

		$max = 1;
		foreach($data as $row)
		{
			$max = max($row['cnt'], $max);
		}

		foreach($data as $row)
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
		$rs = array();
		foreach($this->date_ranges as $ranid => $randesc)
		{
			$this->vars(array(
				"range_url" => $this->mk_my_orb('change', $arr + array('cur_range' => $ranid)),
				"range" => $randesc
			));
			if ($cur_range == $ranid)
			{
				$rs[] = $this->parse("SEL_RANGE");
			}
			else
			{
				$rs[] = $this->parse("RANGE");
			}
		}
		$this->vars(array(
			"RANGE" => join(" | ",$rs)
		));

		$tb = get_instance('toolbar');
		$tb->add_cdata($this->parse());

		$tb->add_end_cdata($this->get_tb_end_cdata($arr, $id));

		$ob = $this->get_object($id);
		if ($arr['query'] != '' || $ob['meta']['lock_q'] == 1)
		{
			return $tbl;
		}
		return $tb->get_toolbar().$tbl.$this->get_js();
	}

	function _do_stat_addr_get_data($arr)
	{
		extract($arr);

		if ($query != '')
		{
			$q = $query;
		}
		else
		{
			$q = "SELECT count(*) as cnt,ip 
					FROM syslog
					".$this->get_where_clause($id, ' WHERE ',false,'stat_addr', $def_span)."
					GROUP BY ip
					ORDER BY cnt DESC
					".$this->get_limit_clause($id);
		}

		if ($ret_query)
		{
			return $q;
		}

		$ret = array();
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$ret[] = $row;
		}
		return $ret;
	}

	function _do_stat_addr($arr)
	{
		extract($arr);

		if (!isset($data) || !is_array($data))
		{
			$data = $this->_do_stat_addr_get_data($arr);
		}
		if ($ret_query)
		{
			return $data;
		}
		unset($arr['data']);

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
		if ($arr['query'] == '')
		{
			$t->define_field(array(
				'name' => 'sel',
				'caption' => html::href(array(
					'url' => 'javascript:selall()',
					'caption' => 'Vali'
				)),
				'sortable' => 0,
				'nowrap' => 1,
				'width' => '1',
				'align' => 'center'
			));
		}

		$max = 1;
		foreach($data as $row)
		{
			$max = max($row['cnt'], $max);
		}

		foreach($data as $row)
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

		$tb->add_end_cdata($this->get_tb_end_cdata($arr, $id, 'blokk'));

		$ob = $this->get_object($id);
		if ($arr['query'] != '' || $ob['meta']['lock_q'] == 1)
		{
			return $tbl;
		}

		unset($arr['def_span']);
		$ret = html::form(array(
			'action' => 'reforb.'.$this->cfg['ext'],
			'method' => 'POST',
			'name' => 'blokk',
			'content' => $tb->get_toolbar().$tbl.$this->mk_reforb('submit_block', $arr)
		));
		$this->read_template("selall.tpl");
		return $this->parse().$ret.$this->get_js();
	}

	function _do_stat_obj_show_oid($arr)
	{
		return $this->_do_dronline($arr);
	}

	function _do_stat_obj_get_data($arr)
	{
		extract($arr);
		if ($query != '')
		{
			$q = $query;
		}
		else
		{
			$q = "SELECT count(*) as cnt,ip, objects.name as name,syslog.oid AS oid
					FROM syslog
						LEFT JOIN objects ON objects.oid = syslog.oid
					WHERE syslog.oid IS NOT NULL AND syslog.oid > 0 ".$this->get_where_clause($id,' AND ',false,'stat_obj', $def_span)."
					GROUP BY oid
					ORDER BY cnt DESC
					".$this->get_limit_clause($id);
		}

		if ($ret_query)
		{
			return $q;
		}

		$ret = array();
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$ret[] = $row;
		}
		return $ret;
	}

	function _do_stat_obj($arr)
	{
		extract($arr);

		if ($show_oid)
		{
			return $this->_do_stat_obj_show_oid($arr);
		}

		if (!isset($data) || !is_array($data))
		{
			$data = $this->_do_stat_obj_get_data($arr);
		}
		if ($ret_query)
		{
			return $data;
		}
		unset($arr['data']);

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


		$max = 1;
		foreach($data as $row)		
		{
			$max = max($row['cnt'], $max);
		}

		foreach($data as $row)
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
		return $this->get_sao_tb($arr,$id).$t->draw();
	}

	function submit_block($arr)
	{
		extract($arr);

		$ipi = get_instance("syslog/ipaddress");

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
				$ipi->get_obj_from_ip(array(
					'ip' => $v
				));
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
		else
		if ($prop['name'] == 'g_default_tab')
		{
			$prop['options'] = $this->tablist;
		}
		else
		if ($prop['name'] == 'bg_query_status' && $arr['obj']['oid'])
		{
			$q = 'SELECT status FROM dronline_bg_status WHERE id = '.$arr['obj']['oid'];
			$prop['value'] = $this->statuses[$this->db_fetch_field($q,"status")];
		}
		else
		if ($prop['name'] == 'rerun_queries')
		{
			$prop['value'] = 'Uuenda p&auml;ringute&nbsp;cache';
		}
		else
		if ($prop['name'] == 'bg_query_created' && $arr['obj']['oid'])
		{
			$q = 'SELECT tm FROM dronline_bg_status WHERE id = '.$arr['obj']['oid'];
			$prop['value'] = $this->time2date($this->db_fetch_field($q,"tm"),2);
		}
		else
		if ($prop['name'] == 'def_span')
		{
			$op = array(0 => "");
			foreach($this->def_spans as $spid => $spdata)
			{
				$op[$spid] = $spdata["text"];
			}
			$prop['options'] = $op;
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
				$param['dro_tab'] = $arr['form_data']['extraids']['dro_tab'];
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
						'query' => $q,
						'conf_desc' => $this->get_conf_desc($param)
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

	function get_conf_desc($arr)
	{
		extract($arr);
		$ret = str_replace("'", "\\'", $this->get_where_clause($id, '', true, $dro_tab, $def_span));
		return $ret;
	}

	function get_js()
	{
		$this->read_template("js_funcs.tpl");
		return $this->parse();
	}

	function get_block_ips($id)
	{
		$ret = array(
			'ipblock' => 'Blokeeri IPd',
		);

		$ob = $this->get_object($id);
		$conf_o = $this->get_object($ob['meta']['conf']);

		if (is_array($conf_o['meta']['ip_block_folders']))
		{
			foreach($conf_o['meta']['ip_block_folders'] as $fld)
			{
				$ret += array($fld => $this->db_fetch_field("SELECT name FROM objects WHERE oid = $fld", "name"));
				$ret += $this->get_objects_below(array(
					'parent' => $fld,
					'class' => CL_PSEUDO,
					'full' => true,
					'ret' => ARR_NAME
				));
			}
		}
		return $ret;
	}

	function get_allow_ips($id)
	{
		$ret = array(
			'ipblock' => 'N&auml;ita IPd',
		);

		$ob = $this->get_object($id);
		$conf_o = $this->get_object($ob['meta']['conf']);

		if (is_array($conf_o['meta']['ip_allow_folders']))
		{
			foreach($conf_o['meta']['ip_allow_folders'] as $fld)
			{
				$ret += array($fld => $this->db_fetch_field("SELECT name FROM objects WHERE oid = $fld", "name"));
				$ret += $this->get_objects_below(array(
					'parent' => $fld,
					'class' => CL_PSEUDO,
					'full' => true,
					'ret' => ARR_NAME
				));
			}
		}
		return $ret;
	}

	function get_tb_end_cdata($arr, $id = false,$formname = 'tmsp')
	{
		$op = array(0 => "");
		foreach($this->def_spans as $spid => $spdata)
		{
			$op[$spid] = $spdata["text"];
		}

		$ru = aw_global_get("REQUEST_URI");
		// block addresses listbox
//		if ($id)
//		{
//			$si = $this->get_block_ips($id);
//			$cdata .= html::select(array(
//				'name' => 'ip_block',
//				'options' => $si,
//				'selected' => $arr['sel_ip_block'],
//			));
//
//			$cdata .= html::select(array(
//				'name' => 'ip_allow',
//				'options' => $this->get_allow_ips($id),
//				'selected' => $arr['sel_ip_allow'],
//			));
//
//			$cdata .= html::href(array(
//				'url' => 'javascript:update_ip(document.'.$formname.')',
//				'caption' => html::img(array(
//					'url' => $this->cfg['baseurl'].'/automatweb/images/icons/save.gif',
//					'border' => '0'
//				))
//			));
//			$ru = preg_replace("/sel_ip_block==[^&$]*/","",$ru);
//			$ru = preg_replace("/sel_ip_allow==[^&$]*/","",$ru);
//		}

		// add timespan listbox
		$cdata .= html::select(array(
			'name' => 'def_span',
			'options' => $op,
			'selected' => $arr['def_span'],
			'onchange' => 'sel_tmsp(document.'.$formname.')'
		)).html::hidden(array(
			'name' => 'def_url',
			'value' => preg_replace("/&{2,}/","&",preg_replace("/def_span==[^&$]*/","",$ru))
		));
		
		// add save_as_obj()
		if ($id)
		{
			$ob = $this->get_object($id);
			if (!$ob['meta']['lock_q'])
			{
				$cdata .= html::span(array(
					'class' => 'awmenuedittabletext',
					'content' => html::href(array(
						'url' => 'javascript:save_as_obj()',
						'caption' => 'Salvesta p&auml;ring'
					))
				));
			}
		}

		if ($formname == 'tmsp')
		{
			$cdata = html::form(array(
				'action' => 'reforb.'.$this->cfg['ext'],
				'name' => 'tmsp',
				'method' => 'POST',
				'content' => $cdata
			));
		}

		return $cdata;
	}

	function get_sao_tb($arr, $id = false)
	{
		if ($arr['query'] != '')
		{
			return '';
		}
		$tb = get_instance('toolbar');
		$tb->add_end_cdata($this->get_tb_end_cdata($arr, $id));
		return $tb->get_toolbar().$this->get_js();
	}

	function get_config_hash($id)
	{
		$str = "";
		foreach($this->confable_tabs as $tab)
		{
			$str .= $this->get_where_clause($id, ' WHERE ',false, $tab).$this->get_limit_clause($id);
		}
		return md5($str);
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$obj = $this->get_object($id);

		if ($obj['meta']['bg_queries'])
		{
			$rerun = $obj['meta']['rerun_queries'];
			// clear run queries flag
			$obj['meta']['rerun_queries'] = false;
			$this->upd_object(array(
				'oid' => $id,
				'metadata' => $obj['meta']
			));
			$inf = $this->db_fetch_row("SELECT * FROM dronline_bg_status WHERE id = $id");

			$current_key = $this->get_config_hash($id);
			if ($current_key != $inf['cache_key'])
			{
				// mark cache as expired
				$this->db_query("
					UPDATE dronline_bg_status 
					SET status = ".DRO_C_OUTOFDATE." 
					WHERE id = $id
				");
			}

			if ($rerun)
			{
				// mark cache as updating
				$this->db_query("
					UPDATE dronline_bg_status 
					SET status = ".DRO_C_UPDATING." 
					WHERE id = $id
				");

				// since caches do not match, add event to scheduler to run bg queries for this object
				$sched = get_instance("scheduler");
				$sched->add(array(
					"event" => $this->mk_my_orb("run_bg_queries", array("id" => $id)),
					"time" => time()
				));
				// but for debugging purposes, do it right bloody now instead of via scheduler
//				$this->run_bg_queries(array("id" => $id));
			}
		}
	}

	function run_bg_queries($arr)
	{
		extract($arr);
		set_time_limit(0);

		$cache = array();

		foreach($this->query_tabs as $tabid)
		{
			$fn = "_do_".$tabid.'_get_data';
			if (method_exists($this, $fn))
			{
				foreach($this->def_spans as $spid => $spdat)
				{
					$arr['def_span'] = $spid;
					if ($tabid == 'stat_time')
					{
						foreach($this->date_ranges as $rng => $rn)
						{
							$arr['cur_range'] = $rng;
							$td = $this->$fn($arr);
							$cache[$tabid][$spid][$rng] = $td[$rng];
						}
					}
					else
					{
						$cache[$tabid][$spid] = $this->$fn($arr);
					}
				}

				unset($arr['def_span']);
				$spid = "nospan";
				if ($tabid == 'stat_time')
				{
					foreach($this->date_ranges as $rng => $rn)
					{
						$arr['cur_range'] = $rng;
						$td = $this->$fn($arr);
						$cache[$tabid][$spid][$rng] = $td[$rng];
					}
				}
				else
				{
					$cache[$tabid][$spid] = $this->$fn($arr);
				}
			}
		}

		$cstr = aw_serialize($cache);
		$this->quote(&$cstr);

		$current_key = $this->get_config_hash($id);
		$this->db_query("
			UPDATE dronline_bg_status 
			SET status = ".DRO_C_READY.", cache_key = '$current_key', cache_content = '$cstr', tm = ".time()." 
			WHERE id = $id
		");
	}

	function check_hidden_conf($tab, $ob)
	{
		if ($ob['meta']['tab_objs'][$tab])
		{
			return $ob['meta']['tab_objs'][$tab];
		}

		// create new
		$drc_inst = get_instance('syslog/dronline_conf');
		$id = $drc_inst->clone(array(
			'from' => $ob['meta']['conf'],
			'parent' => $ob['oid'],
			'name' => $tab
		));

		$ob['meta']['tab_objs'][$tab] = $id;
		$this->upd_object(array(
			'oid' => $ob['oid'],
			'metadata' => $ob['meta']
		));

		return $id;
	}

	function _do_tab_conf($arr)
	{
		extract($arr);
		// no we gots to show syslog conf configuration here. bijaatch
		// I think the easiest way is to create fake delete syslog conf objs for each tab 
		// under this object and then let the user change the conf obj for the current tab
		$ob = $this->get_object($id);

		// check if the object for the prev tab already exists. if it does not, create it
		$tcid = $this->check_hidden_conf($prev_tab, $ob);

		$droc = get_instance("syslog/dronline_conf");
		$droc->embedded = true;
		aw_register_default_class_member('dronline_conf', 'embedded', true);
		return $droc->change(array("id" => $tcid,"group" => $dro_c_tab));
	}
}
?>
