<?php

/*

@default table=objects
@default field=meta
@default method=serialize

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

@property use_filter type=checkbox ch_value=1
@caption Kas kasutada Tegevuste filtrit

@property filter type=generated generator=get_filter group=Tegevused
@caption Filter

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
			$prop['value'] = $ui->get_user_picker();
		}
		return PROP_OK;
	}

	function get_filter()
	{
		$acts = array();
		$this->db_query("SELECT distinct(type) as type FROM syslog");
		while ($row = $this->db_next())
		{
			$rt = 'slt_'.$row['type'];

			$acts[$rt] = array(
				'name' => $rt,
				'caption' => $row['type'],
				'type' => 'checkbox',
				'ch_value' => 1,
				'table' => 'objects',
				'field' => 'meta',
				'method' => 'serialize',
				'group' => 'Tegevused'
			);
		}

		return $acts;
	}
}
?>
