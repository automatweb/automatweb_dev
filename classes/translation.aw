<?php
// this will be the core of the next gen translation class
class translation extends core
{
	function translation()
	{
		$this->init();
		//print "initializing translation class<bR>";
	}

	function load_catalog($trid)
	{
		// this should be replaced by a chosen translation
		$cat_id = 97179;
		$tr_obj = obj(97179);
		$this->trans = $tr_obj->meta("trans");
	}

	function get_by_id($id)
	{
		return $this->trans[$id];
	}

	function get($ctx,$arg)
	{
		return "TT " . $arg;
	}

}
?>
