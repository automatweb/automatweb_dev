<?php
/*
@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@default field=meta
@default method=serialize

*/

//define('', );

class menu_separator extends class_base
{
	function menu_separator()
	{
		$this->init(array(
			'tpldir' => 'automatweb/menuedit',
			'clid' => CL_MENU_SEPARATOR,
		));
	}

	function callback_get_rel_types()
	{
		return array(
//			 => 'Taustal olevad objektid',
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
                switch($args['reltype'])
                {
		};
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;

		switch($data['name'])
		{
/*			case '':
				$data[''] = ;
			break;*/
		}
		return $retval;

	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$meta =  &$args['obj']['meta'];
		$retval = PROP_OK;

		switch($data['name'])
		{
		};

		return $retval;
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
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		//$this->read_template('*.tpl');
/*
		$this->vars(array(
			'name' => $ob['name']
		));*/

		return '<hr />';//$this->parse();
	}
}
?>
