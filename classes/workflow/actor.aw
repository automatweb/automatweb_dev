<?php
/*

@classinfo syslog_type=ST_ACTOR
@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property name type=textbox 
@caption Nimi

@property description type=textarea field=meta method=serialize
@caption Rolli kirjeldus

*/

classload("workflow/workflow_common");
class actor extends workflow_common
{
	function actor()
	{
		$this->init(array(
			'clid' => CL_ACTOR
		));
	}

	function callback_get_rel_types()
	{
		return parent::callback_get_rel_types();
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
