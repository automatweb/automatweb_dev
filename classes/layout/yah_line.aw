<?php

/*

@classinfo syslog_type=ST_YAH_LINE

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property separator type=textbox size=5 field=meta method=serialize
@caption Eraldaja

@property show_nosubs type=checkbox ch_value=1 field=meta method=serialize
@caption Kas n&auml;idata men&uuml;&uuml;sid, mille all pole dokumente

*/

class yah_line extends class_base
{
	function yah_line()
	{
		$this->init(array(
			'clid' => CL_YAH_LINE
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

	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$pd = get_instance("layout/active_page_data");
		$path = $pd->get_active_path();

		$show = false;

		$names = array();
		$mc = get_instance("menu_cache");
		foreach($path as $oid)
		{
			if ($show)
			{
				$mn = $mc->get_cached_menu($oid);
				$check_subs = ($mc->subs[$mn["oid"]] > 0) || $ob['meta']['show_nosubs'];

				if ($mn["clickable"] == 1 && $check_subs)
				{
					$names[] = $mn["name"];
				}
			}
			if ($oid == $this->cfg["rootmenu"])
			{
				$show = true;
			}
		}

		return join($ob['meta']['separator'], map(' %s ',$names));
	}
}
?>
