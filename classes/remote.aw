<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/remote.aw,v 2.1 2002/02/04 17:01:58 duke Exp $
// remote.aw - AW remote control
global $orb_defs;
$orb_defs["remote"] = "xml";

class remote extends aw_template {
	function remote($args = array())
	{
		extract($args);
		$this->db_init();
		$this->tpl_init("remote");
	}

	function test($args = array())
	{


	}
};
?>
