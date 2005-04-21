<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_education.aw,v 1.2 2005/04/21 08:48:48 kristo Exp $
// crm_person_education.aw - Haridus 
/*

@classinfo syslog_type=ST_CRM_PERSON_EDUCATION relationmgr=yes

@default table=objects
@default group=general

*/

class crm_person_education extends class_base
{
	function crm_person_education()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_EDUCATION
		));
	}
};
?>
