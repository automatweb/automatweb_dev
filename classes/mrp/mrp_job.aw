<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_job.aw,v 1.1 2004/11/15 16:03:39 voldemar Exp $
// mrp_job.aw - Tegevus
/*

@classinfo syslog_type=ST_MRP_JOB relationmgr=yes

@tableinfo mrp_job index=oid master_table=objects master_index=oid

@default group=general
@default table=mrp_job
	@property length type=textbox
	@caption Kestus (s)

	@property resource type=textbox
	@caption Ressurss

	@property project type=textbox
	@caption Projekt

	@property exec_order type=textbox datatype=int
	@caption Töö jrk. nr.

	@property starttime type=datetime_select
	@caption Plaanitud töösseminekuaeg (timestamp)

@default table=objects
	@property comment type=textarea
	@caption Kommentaarid

@default field=meta
@default method=serialize
	@property job_status type=text
	@caption Staatus (tehtud/tegemisel...)

	@property started type=text
	@caption Alustatud

	@property finished type=text
	@caption Lõpetatud

	@property aborted type=checkbox
	@caption Katkestatud

	@property abort_comment type=textarea
	@caption Katkestamise põhjus


// --------------- RELATION TYPES ---------------------

@reltype MRP_RESOURCE value=1 clid=CL_MRP_RESOURCE
@caption Tööks kasutatav ressurss

@reltype MRP_PROJECT value=2 clid=CL_MRP_CASE
@caption Projekt

//@reltype MRP_PRIORITY value=3 clid=CL_PRIORITY
//@caption Töö prioriteet


*/

/*

CREATE TABLE `mrp_job` (
  `oid` int(11) NOT NULL default '0',
  `length` int(10) unsigned NOT NULL default '0',
  `resource` int(10) unsigned default NULL,
  `exec_order` int(10) unsigned NOT NULL default '0',
  `project` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned default NULL,

	PRIMARY KEY  (`oid`),
	UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

class mrp_job extends class_base
{
	function mrp_job()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_job",
			"clid" => CL_MRP_JOB
		));
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}

	function &get_current_workspace ($arr)
	{
		if ($arr["new"])
		{
			$workspace = obj ($arr["request"]["mrp_workspace"]);
		}
		else
		{
			$this_object = $arr["obj_inst"];
			$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT, "class_id" => CL_MRP_CASE));

			foreach ($connections as $connection)
			{
				$project = $connection->to();
				$project_connections = $project->connections_from(array ("type" => RELTYPE_MRP_OWNER, "class_id" => CL_MRP_WORKSPACE));

				foreach ($project_connections as $project_connection)
				{
					$workspace = $project_connection->to();
					break;
				}

				break;
			}
		}

		return $workspace;
	}
}
?>
