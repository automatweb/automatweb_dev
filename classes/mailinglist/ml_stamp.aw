<?php
// $Header: /home/cvs/automatweb_dev/classes/mailinglist/Attic/ml_stamp.aw,v 1.4 2003/04/07 13:59:46 duke Exp $
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
