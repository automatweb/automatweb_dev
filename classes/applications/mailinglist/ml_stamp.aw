<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/mailinglist/Attic/ml_stamp.aw,v 1.1 2004/10/29 21:13:22 duke Exp $
// ml_stamp - listide stambid

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property content type=textarea cols=40 rows=15
	@caption Sisu

	@classinfo syslog_type=ST_MAILINGLIST_STAMP
*/

// extra lame klass , ainult selleks, et salvestada kaks muutujat
class ml_stamp extends class_base
{
	function ml_stamp()
	{
		$this->init(array(
			"clid" => CL_ML_STAMP,
		));
	}
};
?>
