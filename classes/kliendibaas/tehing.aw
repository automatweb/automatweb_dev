<?php
// $Header: /home/cvs/automatweb_dev/classes/kliendibaas/Attic/tehing.aw,v 1.2 2003/10/06 14:32:27 kristo Exp $
// tehing.aw - Tehing 
/*

@classinfo syslog_type=ST_TEHING relationmgr=yes

@default table=objects
@default group=general

*/

class tehing extends class_base
{
	function tehing()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "kliendibaas/tehing",
			"clid" => CL_TEHING
		));
	}

	function change($args)
	{
		$args['strs'] = array(
			'type' => 'TEHING',
			'typeStr' => 'Tehing',
			'typestr' => 'tehing',
			'typestrs' => 'tehingu',
		);
		
		$kohtumine = get_instance('kliendibaas/kohtumine');
		$kohtumine->_change($args);
	}	

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
