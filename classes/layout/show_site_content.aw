<?php

/*

@classinfo syslog_type=ST_SITE_CONTENT

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

*/

class show_site_content extends class_base
{
	function show_site_content()
	{
		$this->init(array(
			'clid' => CL_SITE_CONTENT
		));
	}

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
	// !this shows the object. 
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$pd = get_instance("layout/active_page_data");
		$mned = get_instance("contentmgmt/site_content");

 		if (($txt = $pd->get_text_content()) != "")
		{
			return $txt;
		}
		else
		{
			return $mned->show_documents($pd->get_active_section(), 0);
		}
	}
}
?>
