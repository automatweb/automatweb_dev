<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_input.aw,v 2.0 2001/07/24 20:48:36 duke Exp $
// form_input.aw - Tegeleb vormi sisestustega, hetkel ainult XML.
class form_input extends aw_template
{
	function form_input($args = array())
	{
		$this->db_init();
		$this->tpl_init("forms");
	}

	////
	// !Kuvab uue XML sisendi koostamise jaoks vajaliku ekraanivormi,
	// kust saab valida, milliseid vorme inputis kasutatakse
	function add($args = array())
	{
		extract($args);



	}

	////
	// !Submitib uue sisendi jaoks vajal
	function submit_add($args = array())
	{
		extract($args);

	}

	////
	// !Kuvab XML sisendi muutmise vormi
	function change($args = array())
	{
		extract($args);
	
	}

	////
	// !Submitib sisendi
	function submit_change($args = array())
	{
		extract($args);


	}
};
?>
