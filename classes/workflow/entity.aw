<?php

/*

@classinfo syslog_type=ST_ENTITY
@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property description type=textarea field=meta method=serialize
@caption Kirjeldus

@property entity_cfgform type=objpicker clid=CL_CFGFORM subclass=CL_DOCUMENT field=meta method=serialize
@caption Olem (konfivorm)

@property entity_process type=objpicker clid=CL_PROCESS field=meta method=serialize
@caption Protsess

@property entity_actor type=objpicker clid=CL_ACTOR field=meta method=serialize
@caption Tegija

*/


classload("workflow/workflow_common");
class entity extends workflow_common
{
	function entity()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			'clid' => CL_ENTITY
		));
	}
	
	function callback_get_rel_types()
	{
		$retval = parent::callback_get_rel_types();
		$retval[RELTYPE_ACTION] = "tegevus";
		$retval[RELTYPE_PROCESS] = "protsess";
		return $retval;
	}

	
	function get_property($args)
        {
                $data = &$args["prop"];
		$name = $data["name"];
                $retval = PROP_OK;
		if ($name == "comment" || $name == "alias" || $name == "jrk")
		{
			return PROP_IGNORE;
		};

                switch($data["name"])
                {

		}
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
}
?>
