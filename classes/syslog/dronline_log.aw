<?php

/*

@default table=objects

@property status type=status field=status
@caption Staatus

*/

class dronline_log extends class_base
{
	function dronline_log()
	{
		$this->init(array(
			'tpldir' => 'syslog/dronline_log',
			'clid' => CL_DRONLINE_LOG
		));
	}

	function change(&$arr)
	{
		extract($arr);
		$ob = $this->_change_init($arr, 'AW_Log');

		$fn = '_do_'.$ob['meta']['dro_type'];

		$dro = get_instance('syslog/dronline');
		$ret = $dro->$fn(array(
			'query' => $ob['meta']['query'],
			'cur_range' => $ob['meta']['cur_range']
		));

		return $ret;
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

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}
}
?>
