<?php

class __classname extends class_base
{
	////////////////////////////////////
	// the next functions are REQUIRED for all classes that can be added from menu editor interface
	////////////////////////////////////

	function __classname()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init(array(
			'tpldir' => '__tplfolder',
			'clid' => __classdef
		));
	}

	////
	// !generates the toolbar for this class
	// default toolbar includes only one button - save button
	function mk_toolbar()
	{
		$tb = get_instance('toolbar');
		
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => 'Salvesta',
			'url' => 'javascript:document.add.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));

		return $tb->get_toolbar();
	}

	////
	// !called, when adding a new object 
	// parameters:
	//    parent - the folder under which to add the object
	//    return_url - optional, if set, the "back" link should point to it
	//    alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		// checks ACL, sets the path and reads the template
		$this->_add_init($arr, '__classname', 'change.tpl');

		$this->vars(array(
			'toolbar' => $this->mk_toolbar(),
			'reforb' => $this->mk_reforb('submit', array(
				'parent' => $parent, 
				'alias_to' => $alias_to, 
				'return_url' => $return_url
			))
		));
		return $this->parse();
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				'oid' => $id,
				'name' => $name
			));
			$this->_log('__classname', "Muutis __classname objekti $name ($id)", $id);
		}
		else
		{
			$id = $this->new_object(array(
				'parent' => $parent,
				'name' => $name,
				'class_id' => __classdef
			));
			$this->_log('__classname', "Lisas __classname objekti $name ($id)", $id);
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb('change', array(
			'id' => $id, 
			'return_url' => urlencode($return_url)
		));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		// checks ACL, sets path, reads template and returns the object
		$ob = $this->_change_init($arr, '__classname', 'change.tpl');

		$this->vars(array(
			'name' => $ob['name'],
			'toolbar' => $this->mk_toolbar(),
			'reforb' => $this->mk_reforb('submit', array(
				'id' => $id, 
				'return_url' => urlencode($return_url)
			))
		));

		return $this->parse();
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

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}

	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

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
	// !this is not required 99% of the time, but you can override adding aliases to documents - when the user clicks
	// on "pick this" from the aliasmanager "add existing object" list and this function exists in the class, then it will be called
	// parameters
	//   id - the object to which the alias is added
	//   alias - id of the object to add as alias
/*	function addalias($arr)
	{
		extract($arr);
		// this is the default implementation, don't include this function if you're not gonna change it
		$this->add_alias($id,$alias);
		header('Location: '.$this->mk_my_orb('list_aliases',array('id' => $id),'aliasmgr'));
	}*/
}
?>