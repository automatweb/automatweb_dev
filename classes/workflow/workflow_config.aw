<?php
/*

@classinfo syslog_type=ST_WORKFLOW_CONFIG no_status=1
@classinfo relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property treeview_conf type=relpicker reltype=RELTYPE_TREE_CONFIG editonly=1
@caption Puu konfiguratsioon

@property actor_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_actor
@caption Tegijate juurmenüü

@property action_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_action
@caption Tegevuste juurmenüü

@property entity_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_entity
@caption Olemite juurmenüü

@property process_rootmenu type=relpicker reltype=RELTYPE_ROOTMENU group=config_process
@caption Protsesside juurmenüü

@groupinfo config_actor caption="Tegijate seaded"
@groupinfo config_action caption=Tegevuste seaded"
@groupinfo config_entity caption="Olemite seaded"
@groupinfo config_process caption="Protsesside seaded"

@reltype ROOTMENU value=1 clid=CL_MENU
@caption juurmenüü

@reltype TREE_CONFIG value=2 clid=CL_TREEVIEW
@caption puu seaded



*/

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

	function get_property($args = array())
        {
		$data = &$args["prop"];
		$name = $data["name"];
		$retval = PROP_OK;

		switch($data["name"])
		{
		};
	}
}
?>
