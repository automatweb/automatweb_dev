<?php

/*

@classinfo syslog_type=ST_RATE_SCALE relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo scale caption=Skaala

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property type_nr type=checkbox ch_value=1 field=meta method=serialize group=scale
@caption Numbriline skaala

@property nr_from type=textbox size=3 field=meta method=serialize group=scale
@caption Alates

@property nr_to type=textbox size=3 field=meta method=serialize group=scale
@caption Kuni

@property nr_step type=textbox size=3 field=meta method=serialize group=scale
@caption Aste

@property type_ud type=checkbox ch_value=2 field=meta method=serialize group=scale
@caption Kasutaja defineeritud skaala

@property ud_scale type=generated field=meta method=serialize generator=get_udscale_entries group=scale
@caption 

@property rate_clid type=select field=meta method=serialize
@caption Mis objektit&uuml;&uuml;bile skaala kehtib

@property rate_folders type=relpicker field=meta method=serialize multiple=1 reltype=RELTYPE_FOLDER
@caption Mis kataloogidele skaala kehtib

*/

define("RATING_NUMERIC", 1);
define("RATING_TEXT", 2);

define("RELTYPE_FOLDER",1);

class rate_scale extends class_base
{
	function rate_scale()
	{
		$this->init(array(
			'clid' => CL_RATE_SCALE
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

	function get_udscale_entries($arr)
	{
		extract($arr);
		$ret = array();
		
		return $ret;
	}

	function get_property(&$arr)
	{
		$prop = &$arr["prop"];
		switch($prop['name'])
		{
			case "nr_from":
			case "nr_to":
			case "nr_step":
				if (!$arr["obj"]["meta"]["type_nr"])
				{
					return PROP_IGNORE;
				}
				break;

			case "ud_scale":
				if (!$arr["obj"]["meta"]["type_ud"])
				{
					return PROP_IGNORE;
				}
				break;

			case "rate_clid":
				$prop['options'] = aliasmgr::get_clid_picker();
				break;
		}
		return PROP_OK;
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_FOLDER => "kehtiv kataloog",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_FOLDER)
		{
			return array(CL_PSEUDO);
		}
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$this->db_query("DELETE FROM rate2menu WHERE rate_id = '$id'");
		$d = new aw_array($ob['meta']['rate_folders']);
		foreach($d->get() as $fld)
		{
			$this->db_query("INSERT INTO rate2menu(menu_id, rate_id, clid) VALUES('$fld','$id','".$ob['meta']['rate_clid']."')");
		}

		$this->db_query("DELETE FROM rate2clid WHERE rate_id = '$id'");
		if ($d->count() == 0)
		{
			$this->db_query("INSERT INTO rate2clid(rate_id, clid) VALUES('$id','".$ob['meta']['rate_clid']."')");
		}
	}


	function get_scale_for_obj($oid)
	{
		// read the object
		$ob = $this->get_object($oid);
		// find the correct scale for the object

		$oc = $this->get_object_chain($ob['parent']);
		// first, we check if any menus have rate objects in the menu chain for that object

		foreach($oc as $od)
		{
			$sql = "SELECT * FROM rate2menu WHERE menu_id = '$od[oid]'";
			$this->db_query($sql);
			while ($row = $this->db_next())
			{
				// if we find one, then we check if it only applies for a class
					// if not, we found it!
					// if it does and the clid does not match, then continue
				if (!$row['clid'] || ($row['clid'] == $ob['class_id']))
				{
					return $this->_get_scale($row['rate_id']);
				}
			}
		}

		// if we don't find one, then chect the rate2clid table
			// if found, return
			// if not, error 
		if (($rate = $this->db_fetch_field("SELECT rate_id FROM rate2clid WHERE clid = '$ob[class_id]'", "rate_id")))
		{
			return $this->_get_scale($rate);
		}

		$this->raise_error(ERR_RATE_NOT_FOUND, "rate::get_scale_for_obj($oid) - no rate for object is set!", true, false);
	}

	function _get_scale($id)
	{
		$ret = array();
		$ob = $this->get_object($id);
		if ($ob['meta']['type_nr'] == 1)
		{
			for ($i = $ob['meta']['nr_from']; $i <= $ob['meta']['nr_to']; $i += $ob['meta']['nr_step'])
			{
				$ret[$i] = $i;
			}
		}
		return $ret;
	}
}
?>
