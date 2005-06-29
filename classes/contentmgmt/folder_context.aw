<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/folder_context.aw,v 1.1 2005/06/29 09:42:07 kristo Exp $
// folder_context.aw - Kontekst 
/*

@classinfo syslog_type=ST_FOLDER_CONTEXT relationmgr=yes no_comment=1 

@default table=objects
@default group=general

*/

class folder_context extends class_base
{
	function folder_context()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/folder_context",
			"clid" => CL_FOLDER_CONTEXT
		));
	}
}
?>
