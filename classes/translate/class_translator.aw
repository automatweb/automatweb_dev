<?php
// this will be the core of the next gen translation class
class class_translator extends core
{
	function class_translator()
	{
		$this->init();
		//print "initializing translation class<bR>";
	}

	function load_catalog($trid)
	{
		// this should be replaced by a chosen translation
		//print "loading catalog for $trid<br>";
		$trid = aw_global_get("trid");
		if (!empty($trid))
		{
			$tr_obj = new object($trid);	
			$this->trans = $tr_obj->meta("trans");
		};
	}

	function get_by_id($id,$ctx)
	{
		//print "id = $id<br>";
		return $this->trans[$id][$ctx];
	}

	function get($ctx,$arg)
	{
		return "TT " . $arg;
	}

}
?>
