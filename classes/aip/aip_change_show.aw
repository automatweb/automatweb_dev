<?php
// $Header: /home/cvs/automatweb_dev/classes/aip/Attic/aip_change_show.aw,v 1.3 2004/02/11 21:51:14 duke Exp $
/*

@classinfo syslog_type=ST_AIP_CHANGE_SHOW

@default table=objects
@default group=general

@property type type=select field=meta method=serialize
@caption Muudatuse t&uuml;&uuml;p

*/

class aip_change_show extends class_base
{
	function aip_change_show()
	{
		$this->init(array(
			'clid' => CL_AIP_CHANGE_SHOW
		));
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
		$ob = new object($id);

		$ac = get_instance("aip_change");
		return $ac->show_files(array(
			"type" => $ob->prop('type'),
		));
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		if ($prop["name"] == "type")
		{
			$prop['options'] = array("1" => "AIP AMDT", "2" => "AIRAC AIP AMDT","3" => "SUP");
		}
		return PROP_OK;
	}
}
?>
