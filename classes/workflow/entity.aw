<?php

/*

@classinfo syslog_type=ST_ENTITY
@classinfo relationmgr=yes

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
		if ($name == "comment")
		{
			return PROP_IGNORE;
		};

                switch($data["name"])
                {

		}
	}

}
?>
