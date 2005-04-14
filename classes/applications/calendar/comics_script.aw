<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/comics_script.aw,v 1.1 2005/04/14 09:56:55 ahti Exp $
// comics_script.aw - Koomiksi skript 
/*

@classinfo syslog_type=ST_COMICS_SCRIPT no_status=1

@default table=objects
@default group=general

@property name type=textbox
@caption Pealkiri

@property comment type=textarea
@caption Sisukokkuvõte

@property content type=textarea cols=60 rows=20 field=meta
@caption Sisu

*/

class comics_script extends class_base
{
	function comics_script()
	{
		$this->init(array(
			"clid" => CL_COMICS_SCRIPT
		));
	}
}
?>
