<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/rate/rate_scale.aw,v 1.6 2004/02/17 10:55:25 duke Exp $

/*

@classinfo syslog_type=ST_RATE_SCALE relationmgr=yes

@groupinfo scale caption=Skaala

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property rate_clid type=select 
@caption Mis objektit&uuml;&uuml;bile skaala kehtib

@property rate_folders type=relpicker multiple=1 reltype=RELTYPE_FOLDER
@caption Mis kataloogidele skaala kehtib

@default group=scale

@property type_nr type=checkbox ch_value=1 
@caption Numbriline skaala

@property nr_from type=textbox size=3 
@caption Alates

@property nr_to type=textbox size=3
@caption Kuni

@property nr_step type=textbox size=3
@caption Aste

@property type_ud type=checkbox ch_value=2
@caption Kasutaja defineeritud skaala

@property ud_scale type=generated generator=get_udscale_entries 
@caption 


@reltype FOLDER value=1 clid=CL_MENU
@caption Kehtiv kataloog

*/

define("RATING_NUMERIC", 1);
define("RATING_TEXT", 2);

class rate_scale extends class_base
{
	function rate_scale()
	{
		$this->init(array(
			'clid' => CL_RATE_SCALE
		));
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
				if (1 != $arr["obj_inst"]->prop("type_nr"))
				{
					return PROP_IGNORE;
				}
				break;

			case "ud_scale":
				if (2 == $arr["obj_inst"]->prop("type_ud"))
				{
					return PROP_IGNORE;
				}
				break;

			case "rate_clid":
				classload("aliasmgr");
				$prop['options'] = aliasmgr::get_clid_picker();
				break;
		}
		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = $arr["obj_inst"];
		$id = $ob->id();
		$this->db_query("DELETE FROM rate2menu WHERE rate_id = '$id'");
		$d = new aw_array($ob->prop('rate_folders'));
		$rate_clid = $ob->prop("rate_clid");
		foreach($d->get() as $fld)
		{
			$this->db_query("INSERT INTO rate2menu(menu_id, rate_id, clid) VALUES('$fld','$id','".$rate_clid."')");
		}

		$this->db_query("DELETE FROM rate2clid WHERE rate_id = '$id'");
		if ($d->count() == 0)
		{
			$this->db_query("INSERT INTO rate2clid(rate_id, clid) VALUES('$id','".$rate_clid."')");
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
		if ($this->object_exists($id))
		{
			$ob = new object($id);
			if ($ob->prop('type_nr') == 1)
			{
				for ($i = $ob->prop('nr_from'); $i <= $ob->prop('nr_to'); $i += $ob->prop('nr_step'))
				{
					$ret[$i] = $i;
				}
			}
		};
		return $ret;
	}
}
?>
