<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_section.aw,v 1.8 2004/07/02 14:58:58 sven Exp $
// crm_section.aw - Üksus
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_COMPANY, on_disconnect_org_from_section)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PERSON, on_connect_person_to_section)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PERSON, on_disconnect_person_from_section)

@classinfo syslog_type=ST_CRM_SECTION relationmgr=yes

@default table=objects
@default group=general

@property jrk type=textbox size=4
@caption Järk

@reltype SECTION value=1 clid=CL_CRM_SECTION
@caption Alamüksus

@reltype WORKERS value=2 clid=CL_CRM_PERSON
@caption Liige

@reltype PROFESSIONS value=3 clid=CL_CRM_PROFESSION
@caption Roll

@reltype JOB_OFFER value=4 clid=CL_PERSONNEL_MANAGEMENT_JOB_OFFER
@caption Tööpakkumine
*/

class crm_section extends class_base
{
	function crm_section()
	{
		$this->init(array(
			"clid" => CL_CRM_SECTION
		));
	}

	function get_folders_as_object_list($o, $level, $parent)
	{
		// I need all objects that target this one
		// $o - is the sector object
		$conns = $o->connections_to(array(
			"from.class_id" => CL_CRM_PERSON,
		));
		$ol = new object_list();
		foreach($conns as $conn)
		{
			$ol->add($conn->prop("from"));
		};
		return $ol;
	}

	function make_menu_link($o)
	{
		// right, now I need to implement the proper code
		// need to figure out the section!
		$sect = $o->prop("sect");
		return $this->mk_my_orb("show",array("id" => $o->id(),"section" => aw_global_get("section")),CL_CRM_PERSON);
		//return aw_ini_get("baseurl") . "/" . $o->id() . "?oid=" . $sect;
		/*
		print "swching";
		print "<pre>";
		var_dump($o->name());
		print "</pre>";
		*/
	}


	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}
	*/

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	/*
		$id - object id
	*/
	function get_professions($id, $recrusive = false)
	{
		static $rtrn;
		
		if($recrusive == false)
		{
			$obj = new object($id);
			$rtrn = array();
			$conns = $obj->connections_from(array(
				'type' => 'RELTYPE_PROFESSIONS'
			));
			foreach($conns as $conn)
			{
				$rtrn[$conn->prop('to')] = $conn->prop('to.name');
			}
		}
		else
		{	//Case recrusve
			$obj = new object($id);
			$conns = $obj->connections_from(array(
				'type' => 'RELTYPE_PROFESSIONS'
			));
			
			foreach($conns as $conn)
			{
				$rtrn[$conn->prop('to')] = $conn->prop('to.name');
			}
			
			if($sub_sections = $obj->connections_from(array("type" => 1)))
			{
				foreach ($sub_sections as $sub_section)
				{
					$this->get_professions($sub_section->prop("to"), true);
				}
			}
		}
		return $rtrn;
	}
	
	function get_section_job_ids_recrusive($unit_id)
	{
		static $jobs_ids;
		
		$section_obj = &obj($unit_id);
		
		foreach ($section_obj->connections_from(array("type" => RELTYPE_JOB_OFFER)) as $joboffer)
		{
			$jobs_ids[$joboffer->prop("to")] = $section_obj->name();		
		}
	
		//If section has any subsections...get jobs from there too
		if($sub_sections = $section_obj->connections_from(array("type" => 1)))
		{
			foreach ($sub_sections as $sub_section)
			{
				$this->get_section_job_ids_recrusive($sub_section->prop("to"));				
			}
		}
		return $jobs_ids;
	}
	
	function get_section_job_ids($unit_id)
	{
		$section_obj = &obj($unit_id);
		foreach ($section_obj->connections_from(array("type" => RELTYPE_JOB_OFFER)) as $joboffer)
		{
			$jobs_ids[] = $joboffer->prop("to");						
		}
		return $jobs_ids;	
	}


   // Invoked when a connection from organization to section is removed
   // .. this will then remove the opposite connection as well if one exists
   function on_disconnect_org_from_section($arr)
   {
      $conn = $arr["connection"];
      $target_obj = $conn->to();
      if ($target_obj->class_id() == CL_CRM_SECTION)
      {
			if($target_obj->is_connected_to(array('from' => $conn->prop('from'))))
			{
				$target_obj->disconnect(array(
					"from" => $conn->prop("from"),
				));
			}
      }
   }

	// Invoked when a connection is created from person to section
   // .. this will then create the opposite connection.
   function on_connect_person_to_section($arr)
   {
      $conn = $arr["connection"];
      $target_obj = $conn->to();
      if ($target_obj->class_id() == CL_CRM_SECTION)
      {
         $target_obj->connect(array(
            "to" => $conn->prop("from"),
            "reltype" => 2, //crm_section.reltype_section
         ));
      }
   }
	
	function on_disconnect_person_from_section($arr)
	{
      $conn = $arr["connection"];
      $target_obj = $conn->to();
      if ($target_obj->class_id() == CL_CRM_SECTION)
      {
			if($target_obj->is_connected_to(array('to'=>$conn->prop('from'))))
			{
				$target_obj->disconnect(array(
					"from" => $conn->prop("from"),
				));
			}
      }
	}

}
?>
