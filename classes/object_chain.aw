<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/object_chain.aw,v 2.22 2005/03/21 07:06:27 kristo Exp $
// object_chain.aw - Objektipärjad

/*

@classinfo relationmgr=yes syslog_type=ST_OBJECT_CHAIN no_status=1 no_comment=1

@property objs type=relpicker reltype=RELTYPE_OBJECT multiple=1 table=objects field=meta method=serialize group=general
@caption Vali objektid

@reltype OBJECT value=1
@caption objekt
*/

class object_chain extends class_base
{
	function object_chain()
	{
		$this->init(array(
			"tpldir" => "object_chain",
			"clid" => CL_OBJECT_CHAIN
		));
	}

	function get_objects_in_chain($id)
	{
		$o = obj($id);
		return safe_array($o->meta("objs"));
	}

	/** backwards compatibility - create rels for all objects in objs
	**/
	function callback_pre_edit($arr)
	{
		$objs = $this->get_objects_in_chain($arr["obj_inst"]->id());
		foreach($objs as $obj)
		{
			if (!$arr["obj_inst"]->is_connected_to(array("to" => $obj)))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $obj,
					"reltype" => "RELTYPE_OBJECT"
				));
			}
		}
	}
}
?>
