<?php
// $Header: /home/cvs/automatweb_dev/classes/core/abstract_datasource.aw,v 1.1 2004/05/12 13:54:49 kristo Exp $
// abstract_datasource.aw - Andmeallikas 
/*

@classinfo syslog_type=ST_ABSTRACT_DATASOURCE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property ds type=relpicker reltype=RELTYPE_DS 
@caption Andmed objektist

@property row_cnt type=text store=no
@caption Mitu rida andmeid

@property name_field type=select 
@caption Nime tulp

@property id_field type=select 
@caption ID tulp

------------------- CSV FILE class options
@property file_has_header type=checkbox ch_value=1
@caption Esimesel real on pealkirjad


@reltype DS value=1 clid=CL_FILE,CL_OTV_DS_POSTIPOISS,CL_OTV_DS_OBJ
@caption andmed objektist

*/

class abstract_datasource extends class_base
{
	function abstract_datasource()
	{
		$this->init(array(
			"tpldir" => "core/abstract_datasource",
			"clid" => CL_ABSTRACT_DATASOURCE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "file_has_header":
				$dso = obj($arr["obj_inst"]->prop("ds"));
				if ($dso->class_id() != CL_FILE)
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "row_cnt":
				$prop["value"] = count($this->get_objects($arr["obj_inst"]));
				break;

			case "name_field":
				$prop["options"] = $this->get_fields($arr["obj_inst"]);
				break;

			case "id_field":
				$prop["options"] = $this->get_fields($arr["obj_inst"]);
				break;
		};
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

	// submenus-from-object support functions

	function get_folders_as_object_list($object, $level, $parent_o)
	{
		$folders = $this->get_folders($object);
		$ol = new object_list();
		foreach($folders as $fld)
		{		
			$i_o = obj();	//obj($fld["id"]);
			$i_o->set_parent($parent_o->id());
			$i_o->set_class_id(CL_ABSTRACT_DATA_CLASS);
			$i_o->set_name($fld[$object->prop("name_field")]);
			
			$ol->add($i_o);
		}

		echo dbg::dump($ol);
		return $ol;
	}

	////////////////////////////////////////////////////////////
	// data access interface
	////////////////////////////////////////////////////////////
	
	/** returns an array of field id => example text for all the fileds in the datasource
	**/
	function get_fields($o)
	{
		// get the real datasource 
		$ds_o = obj($o->prop("ds"));
		$ds_i = $ds_o->instance();

		return $ds_i->get_fields($ds_o);
	}

	/** returns an array of data rows

		@comment
			data rows are arrays, keys are the same as keys returned from get_fields()
	**/
	function get_objects($o)
	{
		$ds_o = obj($o->prop("ds"));
		$ds_i = $ds_o->instance();

		$ret = $ds_i->get_objects($ds_o);
		if ($ds_o->class_id() == CL_FILE && $o->prop("file_has_header"))
		{
			array_pop($ret);
		}
		return $ret;
	}

	/** returns an array of folders from the datasource
	**/
	function get_folders($o)
	{
		$ds_o = obj($o->prop("ds"));
		$ds_i = $ds_o->instance();

		$ret = $ds_i->get_objects($ds_o);
		return $ret;
	}
}
?>
