<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_section.aw,v 1.7 2004/07/02 09:40:32 rtoomas Exp $
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
	function get_professions($id)
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
		return $rtrn;
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
