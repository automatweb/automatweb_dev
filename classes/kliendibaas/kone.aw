<?php
// $Header: /home/cvs/automatweb_dev/classes/kliendibaas/Attic/kone.aw,v 1.2 2003/10/06 14:32:27 kristo Exp $
// kone.aw - Kõne 
/*

@classinfo syslog_type=ST_KONE relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select
// field=start table=planner group=calendar
@caption Algab 

@property duration type=time_select 
//field=end table=planner group=calendar
@caption Kestab

@property content type=textarea richtext=1 cols=60 rows=30
@caption Sisu


*/

class kone extends class_base
{
	function kone()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "kliendibaas/kone",
			"clid" => CL_KONE
		));
	}

	function change($args)
	{
		$args['strs'] = array(
			'type' => 'KONE',
			'typeStr' => 'Kõne',
			'typestr' => 'kõne',
			'typestrs' => 'kõne',
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
