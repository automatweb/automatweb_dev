<?php

classload("cal_event");
class repeater_obj extends cal_event
{
	function repeater_obj()
	{
		$this->init(array(
			'tpldir' => 'cal_event',
			'clid' => CL_REPEATER_OBJ
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
		$this->init(array(
			'tpldir' => 'repeater_obj',
			'clid' => CL_REPEATER_OBJ
		));
		$this->_add_init($arr, 'repeater_obj', 'change.tpl');

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
			$this->_log(ST_REPEATER, SA_CHANGE, "$name ($id)", $id);
		}
		else
		{
			$id = $this->new_object(array(
				'parent' => $parent,
				'name' => $name,
				'class_id' => CL_REPEATER_OBJ
			));
			$this->_log(ST_REPEATER, SA_ADD, "$name ($id)", $id);
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
		return $this->repeaters(array(
			"id" => $id,
			"cycle" => $cycle,
			"hide_menubar" => "hell_yes",
			"use_class" => "repeater_obj",
			"use_method" => "set_time",
    ));	
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
}
?>
