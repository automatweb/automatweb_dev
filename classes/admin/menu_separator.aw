<?php
/*
@classinfo relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

*/

class menu_separator extends class_base
{
	function menu_separator()
	{
		$this->init(array(
			'clid' => CL_MENU_SEPARATOR,
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

	function show($arr)
	{
		return '<hr />';
	}
}
?>
