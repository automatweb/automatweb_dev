<?php

class contact extends aw_template
{
	function contact()
	{
		$this->init("contact");
	}

	////
	// !shows the form used to enter the contact
	function add($arr)
	{
		extract($arr);

		$con_form = $this->get_cf();

		clsssload("form");
		$f = new form;
		return $f->gen_preview(array(
			"id" => $con_form, 
			"reforb" => $this->mk_reforb("submit_contact", array("parent" => $parent), "contact")
		));
	}

	////
	// !returns the form used for entering contacts.
	function get_cf()
	{
		$cf = get_simple_config("contact_form");
		if (!$cf)
		{
			$this->raise_error(ERR_CONTACT_NOFORM,LC_CONTACT_NO_FORM,true);
		}
		return $cf;
	}
}
?>