<?php

classload("calendar/cal_event");
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
		$tb = get_instance('vcl/toolbar');
		
		$tb->add_button(array(
			'name' => 'save',
			'tooltip' => t('Salvesta'),
			'url' => 'javascript:document.add.submit()',
			'imgover' => 'save_over.gif',
			'img' => 'save.gif'
		));

		return $tb->get_toolbar();
	}

	/** called, when adding a new object 
		
		@attrib name=new params=name default="0"
		
		@param parent required acl="add"
		@param alias_to optional
		@param return_url optional
		
		@returns
		
		
		@comment
		parameters:
		parent - the folder under which to add the object
		return_url - optional, if set, the "back" link should point to it
		alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created

	**/
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

	/** this gets called when the user submits the object's form 
		
		@attrib name=submit params=name default="0"
		
		
		@returns
		
		
		@comment
		parameters:
		id - if set, object will be changed, if not set, new object will be created

	**/
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$o = obj($id);
			$o->set_name($name);
			$o->save();
			$this->_log(ST_REPEATER, SA_CHANGE, "$name ($id)", $id);
		}
		else
		{
			$o = obj();
			$o->set_parent($parent);
			$o->set_name($name);
			$o->set_class_id(CL_REPEATER_OBJ);
			$id = $o->save();
			$this->_log(ST_REPEATER, SA_ADD, "$name ($id)", $id);
		}

		if ($alias_to)
		{
			$o = obj($alias_to);
			$o->connect(array(
				"to" => $id
			));
		}

		return $this->mk_my_orb('change', array(
			'id' => $id, 
			'return_url' => urlencode($return_url)
		));
	}

	/** this gets called when the user clicks on change object 
		
		@attrib name=change params=name default="0"
		
		@param id required acl="view;edit"
		@param return_url optional
		@param cycle optional
		
		@returns
		
		
		@comment
		parameters:
		id - the id of the object to change
		return_url - optional, if set, "back" link should point to it

	**/
	/**  
		
		@attrib name=set_time params=name default="0"
		
		@param id required type=int
		@param cycle optional
		
		@returns
		
		
		@comment

	**/
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
}
?>
