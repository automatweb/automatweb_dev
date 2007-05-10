<?php
// $Header: /home/cvs/automatweb_dev/classes/protocols/file/ocsp.aw,v 1.1 2007/05/10 10:53:10 tarvo Exp $
// ocsp.aw - OCSP p&auml;ring 

class ocsp extends class_base
{
	function ocsp()
	{
		$this->init(array(
			"clid" => CL_OCSP
		));
	}

}
?>
