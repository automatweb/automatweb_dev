<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/keywords.aw,v 2.0 2001/05/18 13:29:43 duke Exp $
// keywords.aw - dokumentide võtmesõnad
class keywords extends aw_temlate {
	function keywords($args = array())
	{
		$this->db_init();
		$this->tpl_init("automatweb/keywords");
	}
};
?>

