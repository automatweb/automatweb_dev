<?php

/*

@default table=objects
@default field=meta
@default method=serialize
@default group=general


@property status type=status field=status
@caption Staatus

@property from type=date_select 
@caption Alates

@property to type=date_select 
@caption Kuni

@property user type=select
@caption kasutaja

@property address type=textbox
@caption IP Aadress

@property email_uid type=textbox
@caption Listi UID

@property email_email type=textbox
@caption Listi email

@property textfilter type=textbox
@caption Mida tegi 

@property numlines type=textbox size=4
@caption Mitu rida

@property sites type=select multiple=1 size=4
@caption Saidid

@property use_filter type=checkbox ch_value=1
@caption Kas kasutada Tegevuste filtrit

@property filter_types type=generated generator=get_filter_types group=types
@caption T&uuml;&uuml;bid

@property filter_actions type=generated generator=get_filter_actions group=actions
@caption Tegevused

@property filter_combo type=generated generator=get_filter_combo group=combo
@caption Tegevused

@property ip_save_folder type=select group=ipfilter
@caption Kataloog, kuhu salvestatakse IP aadressid

@property ip_block_folders type=select multiple=1 group=ipfilter
@caption Blokeeritavate aadresside kataloogid

@property ip_block_folders_subs type=checkbox ch_value=1 group=ipfilter
@caption Kas n&auml;idata ka alamkataloogid

@property ip_allow_folders type=select multiple=1 group=ipfilter
@caption N&auml;idatavate aadresside kataloogid

@property ip_allow_folders_subs type=checkbox ch_value=1 group=ipfilter
@caption Kas n&auml;idata ka alamkataloogid


@groupinfo general caption=Üldine
@groupinfo types caption=T&uuml;&uuml;bid
@groupinfo actions caption=Tegevused
@groupinfo combo caption=Kombineeritud
@groupinfo ipfilter caption=IPfilter

*/

class dronline_conf extends class_base
{
	function dronline_conf()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
	    // if they exist at all. the default folder does not actually exist, 
	    // it just points to where it should be, if it existed
		$this->init(array(
			'tpldir' => 'syslog/dronline_conf',
			'clid' => CL_DRONLINE_CONF
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

	function get_property(&$arr)
	{
		$prop = &$arr['prop'];

		if ($prop['name'] == 'user')
		{
			$ui = get_instance('users');
			$prop['options'] = $ui->get_user_picker(array('add_empty' => true));
		}
		else
		if ($prop['name'] == 'from' && !$arr['obj']['oid'])
		{
			$prop['value'] = mktime(0,0,0,1,1,date("Y"));
		}
		else
		if ($prop['name'] == 'to' && !$arr['obj']['oid'])
		{
			$prop['value'] = time();
		}
		else
		if ($prop['name'] == 'ip_save_folder')
		{
			$ob = get_instance('objects');
			$prop['options'] = $ob->get_list();
		}
		else
		if ($prop['name'] == 'ip_block_folders')
		{
			$ob = get_instance('objects');
			$prop['options'] = $ob->get_list();
		}
		else
		if ($prop['name'] == 'ip_allow_folders')
		{
			$ob = get_instance('objects');
			$prop['options'] = $ob->get_list();
		}
		else
		if (($prop['name'] == 'name' || $prop['name'] == 'comment' || $prop['name'] == 'alias' || $prop['name'] == 'status' || $prop['name'] == 'jrk') && $this->embedded == true)
		{
			return PROP_IGNORE;
		}
		else
		if ($prop['name'] == 'sites')
		{
			if (!aw_ini_get("syslog.has_site_id"))
			{
				return PROP_IGNORE;
			}
			$opts = array('' => 'K&otilde;ik saidid');
			$this->db_query("SELECT distinct(site_id) as site_id FROM syslog");
			while ($row = $this->db_next())
			{
				if ($row['site_id'])
				{
					$opts[$row['site_id']] = $row['site_id'];
				}
			}
			$prop['options'] = $opts;
		}

		return PROP_OK;
	}

	function get_filter_types()
	{
		$acts = array();
		$tps = aw_ini_get("syslog.types");
		foreach($tps as $tpid => $tpd)
		{
			$rt = 'slt_'.$tpid;

			$acts[$rt] = array(
				'name' => $rt,
				'caption' => $tpd['name'],
				'type' => 'checkbox',
				'ch_value' => 1,
				'table' => 'objects',
				'field' => 'meta',
				'method' => 'serialize',
				'group' => 'types'
			);
		}

		return $acts;
	}

	function get_filter_actions()
	{
		$acts = array();
		$tps = aw_ini_get("syslog.actions");
		foreach($tps as $tpid => $tpd)
		{
			$rt = 'sla_'.$tpid;

			$acts[$rt] = array(
				'name' => $rt,
				'caption' => $tpd['name'],
				'type' => 'checkbox',
				'ch_value' => 1,
				'table' => 'objects',
				'field' => 'meta',
				'method' => 'serialize',
				'group' => 'actions'
			);
		}

		return $acts;
	}

	function get_filter_combo()
	{
		$acts = array();
		$tps = aw_ini_get("syslog.types");
		$acts = aw_ini_get("syslog.actions");
		foreach($tps as $tpid => $tpd)
		{
			// add type separator
			$rt = 'slc_sep_'.$tpid;
			$acts[$rt] = array(
				'name' => $rt,
				'caption' => "<b>".$tpd['name']."</b>",
				'group' => 'combo'
			);
			foreach($acts as $acid => $acd)
			{
				// check if this action applies for this type
				$tlist = explode(",", $acd['types']);
				if (in_array($tpid, $tlist))
				{
					$rt = 'slc_'.$tpid.'_'.$acid;
					$acts[$rt] = array(
						'name' => $rt,
						'caption' => $acd['name'],
						'type' => 'checkbox',
						'ch_value' => 1,
						'table' => 'objects',
						'field' => 'meta',
						'method' => 'serialize',
						'group' => 'combo'
					);
				}
			}
		}

		return $acts;
	}

	function callback_mod_tab($arr)
	{
		if ($this->embedded)
		{
			if ($arr['id'] == 'ipfilter')
			{
				return false;
			}
			$uri = aw_global_get("REQUEST_URI");
			$uri = preg_replace("/dro_c_tab=[^&$]*/","",$uri);
			$uri = preg_replace("/&{2,}/","&",$uri);
			$uri .= "&dro_c_tab=".$arr['id'];
			$arr['link'] = $uri;
		}
		return true;
	}

	function submit($arr)
	{
		$ret = parent::submit($arr);
		if ($arr['extraids']['ret_url'] != "")
		{
			$ret = $arr['extraids']['ret_url'];
		}
		return $ret;
	}

	function change($arr)
	{
		$arr['extraids']['ret_url'] = aw_global_get("REQUEST_URI");
		return parent::change($arr);
	}

	////
	// !creates a clone of object $from under folder $parent with name $name
	function clone($arr)
	{
		extract($arr);

		$old = $this->get_object($from);

		$id = $this->new_object(array(
			'parent' => $parent,
			'class_id' => CL_DRONLINE_CONF,
			'status' => 0,
			'name' => $name,
			'metadata' => $old['meta']
		));

		return $id;
	}
}
?>
