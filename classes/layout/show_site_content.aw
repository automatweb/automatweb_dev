<?php
// $Header: /home/cvs/automatweb_dev/classes/layout/show_site_content.aw,v 1.6 2005/04/21 08:54:56 kristo Exp $
/*

@classinfo syslog_type=ST_SITE_CONTENT

@default table=objects
@default group=general

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
