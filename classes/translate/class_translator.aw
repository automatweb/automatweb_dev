<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/class_translator.aw,v 1.2 2003/09/24 16:28:45 duke Exp $
// deals with loading the correct translation object
class class_translator extends core
{
	function class_translator()
	{
		$this->init();
	}

	function load_catalog($trid)
	{
		$this->clid = $trid;
		// right now we only load it, if it was requested from the URL
		$trid = aw_global_get("trid");
		if (!empty($trid))
		{
			$tr_obj = new object($trid);	
			$this->trans = $tr_obj->meta("trans");
		};
	}

	function get_by_id($type,$id,$ctx)
	{
		$key = md5($type . $this->clid . $id);
		return $this->trans[$key][$ctx];
	}

	function get($ctx,$arg)
	{
		return "TT " . $arg;
	}

}
?>
