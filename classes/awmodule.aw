<?php

classload("form");
class awmodule extends aw_template
{
	function awmodule()
	{
		$this->init("awmodule");
		$this->form = new form;
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent,"Lisa moodul");

		$forms = $this->form->get_flist(array(
			"type" => FTYPE_ENTRY,
			"all_data" => true
		));


		$ob = get_instance("objects");
		$ol = $ob->get_list();

		$tmp = array();
		foreach($forms as $fid => $form)
		{
			if (isset($ol[$form["parent"]]))
			{
				$tmp[$fid] = $ol[$form["parent"]]."/".$form["name"];
			}
		}
		arsort($tmp);
		$this->vars(array(
			"forms" => $this->multiple_option_list(array(), $tmp),
			"reforb" => $this->mk_reforb("submit")
		));
		return $this->parse();
	}
}

?>