<?php
/*

@classinfo syslog_type=ST_WORKFLOW_CONFIG
@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property treeview_conf type=objpicker clid=CL_TREEVIEW editonly=1
@caption Puu konfiguratsioon

@property actor_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_actor
@caption Tegijate juurmenüü

@property action_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_action
@caption Tegevuste juurmenüü

@property entity_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_entity
@caption Olemite juurmenüü

@property process_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_process
@caption Protsesside juurmenüü

@groupinfo config_actor caption=Tegijate_konf
@groupinfo config_action caption=Tegevuste_konf
@groupinfo config_entity caption=Olemite_konf
@groupinfo config_process caption=Protsesside_konf



*/

define(RELTYPE_ROOTMENU,1);

class workflow_config extends class_base
{
	function workflow_config()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			'clid' => CL_WORKFLOW_CONFIG
		));
	}

	function callback_get_rel_types()
        {
                return array(
                        RELTYPE_ROOTMENU => "juurmenüü",
                );
        }
	
	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
		switch($args["reltype"])
		{
			case RELTYPE_ROOTMENU:
				$retval = array(CL_PSEUDO);
				break;
		}
		return $retval;
	}

	function get_property($args = array())
        {
		$data = &$args["prop"];
		$name = $data["name"];
		$retval = PROP_OK;
		if ($name == "alias" || $name == "jrk")
		{
			return PROP_IGNORE;
		};

		switch($data["name"])
		{
		};
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
